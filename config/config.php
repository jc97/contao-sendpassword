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

//Configure the callbacks for sending new password on key option in request.
array_insert($GLOBALS['BE_MOD']['accounts']['member'],0,array
(
	'sendPassword' => array('SendPassword', 'sendNewPasswordToSingleMember'),
));
array_insert($GLOBALS['BE_MOD']['accounts']['user'],0,array
(
	'sendPasswordToUser' => array('SendPassword', 'sendNewPasswordToSingleUser'),
));

?>