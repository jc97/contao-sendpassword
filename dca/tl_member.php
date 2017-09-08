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

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Julian Knorr
 */
class tl_member_sendpassword extends tl_member {
	
	/**
	 * Set an automatically generated username for new members.
	 *
	 * @param $dc
	 */
	public function setUsername($dc)
	{
		// Front end call
		if (!$dc instanceof \DataContainer)
		{
			return;
		}
		
		// Return if there is no active record (override all) or the record is not new
		if (!$dc->activeRecord || $dc->activeRecord->tstamp > 0)
		{
			return;
		}
		
		if ($dc->activeRecord->username == "" && ($dc->activeRecord->firstname != "" || $dc->activeRecord->lastname != ""))
		{
			$username = \SendPassword\SendPassword::getNewMemberUsername($dc->activeRecord->firstname, $dc->activeRecord->lastname);
			$this->Database->prepare("UPDATE tl_member SET username=?, password=? WHERE id=?")
				->execute($username, \Contao\Encryption::hash($dc->activeRecord->password), $dc->id);
		}
	}
	
	/**
	 * Returns a button for sending a new password to member
	 *
	 * @param $row
	 * @param $href
	 * @param $label
	 * @param $title
	 * @param $icon
	 * @param $attributes
	 *
	 * @return string
	 */
	public function sendPasswordButton($row, $href, $label, $title, $icon, $attributes)
	{
		if (!$this->User->hasAccess('tl_member::password', 'alexf'))
		{
			return '';
		}
		
		if ($row['email'] == "") return '';
		if ($row['username'] == "") return '';
		if (!$row['login']) return '';
		
		$href .= '&amp;id='.$row['id'];
		
		return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
	}
}

//Set callback for generating username
array_insert($GLOBALS['TL_DCA']['tl_member']['config']['onsubmit_callback'],0,array
(
	array('tl_member_sendpassword', 'setUsername')
));

//Set random password by default
$GLOBALS['TL_DCA']['tl_member']['fields']['password']['default'] = \SendPassword\SendPassword::getNewPassword($GLOBALS['TL_CONFIG']['minPasswordLength'], true);
//Username have to be optional.
$GLOBALS['TL_DCA']['tl_member']['fields']['username']['eval']['mandatory'] = false;

//Add button for sending new password
array_insert($GLOBALS['TL_DCA']['tl_member']['list']['operations'],0,array
(
	'sendPassword' => array
	(
		'label'               => &$GLOBALS['TL_LANG']['tl_member']['sendPassword'],
		'href'                => 'key=sendPassword',
		'class'				  => 'navigation',
		'icon'                => 'system/modules/newsletter/assets/icon.gif',
		'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['tl_member']['sendPasswordConfirm'] . '\'))return false;Backend.getScrollOffset()"',
		'button_callback'     => array('tl_member_sendpassword', 'sendPasswordButton')
	)
));

//Add callbacks
$GLOBALS['TL_DCA']['tl_member']['config']['onload_callback'][] = array('SendPassword', 'sendAllPasswordsToMembers');
$GLOBALS['TL_DCA']['tl_member']['select']['buttons_callback'][] = array('SendPassword', 'getSendAllPasswordsToMemberButton');

?>