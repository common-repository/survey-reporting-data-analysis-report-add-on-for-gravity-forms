<?php
//Autoloader for the plugin
spl_autoload_register(function($className){
	static $arrClassMap = array(
			'Fleek\Gravity\Admin\Setup'             => 'includes/classes/admin/setup.php',
			'Fleek\Gravity\AJAX\Submit'             => 'includes/classes/ajax/submit.php',
			'Fleek\Gravity\Common\Security'         => 'includes/classes/common/security.php',
			'Fleek\Gravity\Common\Setup'            => 'includes/classes/common/setup.php',
			'Fleek\Gravity\GravityForm\Data'        => 'includes/classes/gravityform/data.php',
	);

	if (!isset($arrClassMap[$className]))
		return null;

	$filePath = __DIR__ . DIRECTORY_SEPARATOR . $arrClassMap[$className];

	unset($arrClassMap[$className]);
	return file_exists($filePath) ? include $filePath : null;
}, false, true );