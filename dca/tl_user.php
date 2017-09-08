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
class tl_user_sendpassword extends tl_user {
	
	/**
	 * Returns a button for sending a new password to user
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
		if (!$this->User->hasAccess('tl_user::password', 'alexf'))
		{
			return '';
		}
		
		if ($row['email'] == "") return;
		if ($row['username'] == "") return;
		
		$href .= '&amp;id='.$row['id'];
		
		return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
	}
}

//Set random password by default
$GLOBALS['TL_DCA']['tl_user']['fields']['password']['default'] = \SendPassword\SendPassword::getNewPassword($GLOBALS['TL_CONFIG']['minPasswordLength'], true);

//Require password change by default
$GLOBALS['TL_DCA']['tl_user']['fields']['pwChange']['default'] = true;

//Add button for sending new password
array_insert($GLOBALS['TL_DCA']['tl_user']['list']['operations'],0,array
(
	'sendPassword' => array
	(
		'label'               => &$GLOBALS['TL_LANG']['tl_user']['sendPassword'],
		'href'                => 'key=sendPasswordToUser',
		'class'				  => 'navigation',
		'icon'                => 'system/modules/newsletter/assets/icon.gif',
		'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['tl_user']['sendPasswordConfirm'] . '\'))return false;Backend.getScrollOffset()"',
		'button_callback'     => array('tl_user_sendpassword', 'sendPasswordButton')
	)
));

//Add callbacks
$GLOBALS['TL_DCA']['tl_user']['config']['onload_callback'][] = array('SendPassword', 'sendAllPasswordsToUsers');
$GLOBALS['TL_DCA']['tl_user']['select']['buttons_callback'][] = array('SendPassword', 'getSendAllPasswordsToUserButton');

?>