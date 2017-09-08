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
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'SendPassword'
));

/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	//Classes
	'SendPassword\SendPassword' => 'system/modules/sendpassword/classes/SendPassword.php',
));

?>