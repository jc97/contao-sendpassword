<?php
/**
 * sendpassword
 * Extension for Contao Open Source CMS, Copyright (C) Leo Feyer
 *
 * Copyright (C) 2017 Julian Knorr
 *
 * @package			sendpassword
 * @author			Julian Knorr <git@jknorr.eu>
 * @copytight		Copyright (C) 2017 Julian Knorr
 * @date			2017
 * @license			LGPL-3.0
 */

namespace SendPassword;

/**
 * Provides methods for generating passwords and usernames and sending new passwords to users
 *
 * @author Julian Knorr
 */
class SendPassword extends \System
{
	const BACKEND = 1;
	const FRONTEND = 2;
	
	/**
	 * Import database object
	 */
	public function __construct()
	{
		$this->import("BackendUser", "User");
		parent::__construct();
	}
	
	/**
	 * Send a password to user by email
	 *
	 * @param string $email     The email address
	 * @param string $name      The name
	 * @param string $username  The username
	 * @param string $password  The password (plaintext)
	 * @param int    $interface Indicates whether the user is a back end user or a front end member
	 * @param null   $language  The language
	 *
	 * @return bool
	 */
	protected function send($email, $name, $username, $password, $interface, $language = null)
	{
		self::loadLanguageFile("sendpassword", $language);
		$objEmail = new \Email();
		$objEmail->from = $GLOBALS['TL_ADMIN_EMAIL'];
		$objEmail->fromName = $GLOBALS['TL_ADMIN_NAME'];
		if ($interface === self::FRONTEND) {
			$objEmail->subject = sprintf($GLOBALS['TL_LANG']['sendpassword']['sendpassword_subject_frontend'], \Idna::decode(\Environment::get('host')));
			$objEmail->text = sprintf($GLOBALS['TL_LANG']['sendpassword']['sendpassword_text_frontend'], $name, \Idna::decode(\Environment::get('host')), $username, $password);
		} else if ($interface === self::BACKEND) {
			$objEmail->subject = sprintf($GLOBALS['TL_LANG']['sendpassword']['sendpassword_subject_backend'], \Idna::decode(\Environment::get('host')));
			$objEmail->text = sprintf($GLOBALS['TL_LANG']['sendpassword']['sendpassword_text_backend'], $name, \Idna::decode(\Environment::get('host')), $username, $password);
		}
		$success = $objEmail->sendTo($email);
		
		if ($success) {
			$this->log("A new password to $username ($email) was send (".($interface === self::FRONTEND ? 'Frontend' : 'Backend').")", __METHOD__, TL_GENERAL);
			return true;
		} else {
			$this->log("Sending a new password to $username ($email) failed (".($interface === self::FRONTEND ? 'Frontend' : 'Backend').")", __METHOD__, TL_ERROR);
			return false;
		}
	}
	
	/**
	 * Generates a new random password for a member and send this password to the member by email
	 *
	 * @param int $memberId The id of the member
	 *
	 * @return bool
	 */
	protected function sendNewPasswordToMember($memberId)
	{
		$objMember = \MemberModel::findByPk($memberId);
		
		if ($objMember === null || $objMember->email == "" || !$objMember->login || $objMember->username == "") return false;
		
		$password = self::getNewPassword($GLOBALS['TL_CONFIG']['minPasswordLength'], false);
		
		$objMember->password = \Encryption::hash($password);
		$objMember->save();
		
		return $this->send($objMember->email, $objMember->firstname." ".$objMember->lastname, $objMember->username, $password, self::FRONTEND, $objMember->language);
	}
	
	/**
	 * Generates a new random password for an user and send this password to the user by email
	 *
	 * @param int $userId The id of the user
	 *
	 * @return bool
	 */
	protected function sendNewPasswordToUser($userId)
	{
		$objUser = \UserModel::findByPk($userId);
		
		if ($objUser === null || $objUser->email == "" || $objUser->username == "") return false;
		
		$password = self::getNewPassword($GLOBALS['TL_CONFIG']['minPasswordLength'], true);
		
		$objUser->password = \Encryption::hash($password);
		$objUser->pwChange = 1;
		$objUser->save();
		
		return $this->send($objUser->email, $objUser->name, $objUser->username, $password, self::BACKEND, $objUser->language);
	}
	
	/**
	 * Generates a new random password for a member and send this password to the member by email.
	 * The id is determined from the request. Call this method by configuration in config.php
	 *
	 * @return bool
	 */
	public function sendNewPasswordToSingleMember()
	{
		if ($this->User->hasAccess('tl_member::password', 'alexf')) {
			$this->sendNewPasswordToMember(\Input::get('id'));
		}
		\Controller::redirect($this->getReferer());
	}
	
	/**
	 * Generates a new random password for an user and send this password to the user by email.
	 * The id is determined from the request. Call this method by configuration in config.php
	 *
	 * @return bool
	 */
	public function sendNewPasswordToSingleUser()
	{
		if ($this->User->hasAccess('tl_user::password', 'alexf')) {
			$this->sendNewPasswordToUser(\Input::get('id'));
		}
		\Controller::redirect($this->getReferer());
	}
	
	/**
	 * Use this method as buttons_callback for select operation in DataContainer of tl_member. It adds a sendPasswords
	 * button.
	 *
	 * @param array          $arrButtons
	 * @param \DataContainer $dc
	 *
	 * @return mixed
	 */
	public function getSendAllPasswordsToMemberButton($arrButtons, $dc)
	{
		if ($this->User->isAdmin) $arrButtons['sendPasswords'] = '<input type="submit" name="sendPasswordsToMember" id="sendPasswordsToMember" class="tl_submit" onclick="return confirm(\''.$GLOBALS['TL_LANG']['tl_member']['sendPasswordsConfirm'].'\')" value="'.specialchars($GLOBALS['TL_LANG']['tl_member']['sendPasswords']).'">';
		return $arrButtons;
	}
	
	/**
	 * Use this method as buttons_callback for select operation in DataContainer of tl_user. It adds a sendPasswords
	 * button.
	 *
	 * @param array          $arrButtons
	 * @param \DataContainer $dc
	 *
	 * @return mixed
	 */
	public function getSendAllPasswordsToUserButton($arrButtons, $dc)
	{
		if ($this->User->isAdmin) $arrButtons['sendPasswords'] = '<input type="submit" name="sendPasswordsToUser" id="sendPasswordsToUser" class="tl_submit" onclick="return confirm(\''.$GLOBALS['TL_LANG']['tl_user']['sendPasswordsConfirm'].'\')" value="'.specialchars($GLOBALS['TL_LANG']['tl_user']['sendPasswords']).'">';
		return $arrButtons;
	}
	
	/**
	 * Use this method as onload_callback to send new passwords to all selected members.
	 *
	 * @param $dc
	 */
	public function sendAllPasswordsToMembers($dc)
	{
		if (TL_MODE != 'BE') return;
		
		if (!$dc instanceof \DataContainer) {
			\Controller::redirect($this->getReferer());
		}
		
		if (\Input::post('FORM_SUBMIT') == 'tl_select') {
			if (isset($_POST['sendPasswordsToMember'])) {
				if (!$this->User->isAdmin) {
					\Controller::redirect($this->getReferer());
				}
				$ids = \Input::post('IDS');
				foreach ($ids as $id) {
					$this->sendNewPasswordToMember($id);
				}
				\Controller::redirect($this->getReferer());
			}
		}
	}
	
	/**
	 * Use this method as onload_callback to send new passwords to all selected users.
	 *
	 * @param $dc
	 */
	public function sendAllPasswordsToUsers($dc)
	{
		if (TL_MODE != 'BE') return;
		
		if (!$dc instanceof \DataContainer) {
			\Controller::redirect($this->getReferer());
		}
		
		if (\Input::post('FORM_SUBMIT') == 'tl_select') {
			if (isset($_POST['sendPasswordsToUser'])) {
				if (!$this->User->isAdmin) {
					\Controller::redirect($this->getReferer());
				}
				$ids = \Input::post('IDS');
				foreach ($ids as $id) {
					$this->sendNewPasswordToUser($id);
				}
				\Controller::redirect($this->getReferer());
			}
		}
	}
	
	/**
	 * Generates a new username from first name and last name.
	 * This method checks if the username is already in use and adds a number if necessary.
	 *
	 * @param string $firstname The firstname
	 * @param string $lastname  The lastname
	 *
	 * @return string|bool An username or false on error
	 */
	public static function getNewMemberUsername($firstname, $lastname)
	{
		setlocale(LC_ALL, "en_US.utf8");
		
		$firstname = self::innerTrim($firstname);
		$lastname = self::innerTrim($lastname);
		$firstname = str_replace(" ", "_", $firstname);
		$lastname = str_replace(" ", "_", $lastname);
		
		if ($firstname == "" && $lastname == "") return false;
		else if ($lastname == "") $username = $firstname;
		else if ($firstname == "") $username = $lastname;
		else $username = $firstname.'.'.$lastname;
		
		$username = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $username));
		$append = 0;
		
		while (\MemberModel::countByUsername($username.($append > 0 ? '-'.$append : '')) > 0) {
			$append++;
		}
		
		$username .= ($append > 0 ? '-'.$append : '');
		
		return $username;
	}
	
	/**
	 * Generates a new random password.
	 *
	 * @param int  $length            The length of the password to generate
	 * @param bool $specialCharacters Indicates whether the password contains special characters
	 *
	 * @return string A random password
	 */
	public static function getNewPassword($length, $specialCharacters = false)
	{
		$characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
		if ($specialCharacters) {
			$characters .= "!?.,;:-_@#+<>[]{}()/&%=*";
		}
		$password = "";
		$maxCharacterIndex = strlen($characters) - 1;
		for ($i = 0; $i < $length; $i++) {
			$index = mt_rand(0, $maxCharacterIndex);
			$password .= $characters[$index];
		}
		return $password;
	}
	
	/**
	 * Like trim(), but also replace all sequences of whitespaces inside the string, not only at the end.
	 *
	 * Caution: Also \t will be replaced by a single space.
	 *
	 * @param string $string The string to trim
	 *
	 * @return string The trimmed string
	 */
	protected static function innerTrim($string)
	{
		return preg_replace('/\s+/', ' ', $string);
	}
}

?>