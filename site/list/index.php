<?php
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', 1);
ini_set("date.timezone","PRC");
define('ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('APP_PATH', ROOT . '../../application/list/');
define('PUBLIC_PATH', ROOT . '../../public/');
define('PUBLIC_DATA_PATH', PUBLIC_PATH . 'data/');
define('PUBLIC_CFG_PATH', PUBLIC_PATH . 'config.inc.php');
define('PUBLIC_CFG', 'public_cfg');

set_include_path(ROOT . '../../library/'
	.PATH_SEPARATOR . APP_PATH
	.PATH_SEPARATOR . get_include_path());
require_once 'Lamb/Loader.php';
$loader = Lamb_Loader::getInstance();
$loader->registerNamespaces('List')->registerNamespaces('Ttk');
//registry
$aCfg = require_once('config.inc.php');
Lamb_Registry::set(CONFIG, $aCfg);
Lamb_Registry::set(PUBLIC_CFG, require_once(PUBLIC_CFG_PATH));
			
Lamb_App::getInstance()->setControllorPath($aCfg['controllor_path'])
						->setErrorHandler(new Ttk_ErrorHandler)
						->setRouter(new Lamb_App_NormalRouter)
						->setSqlHelper(new Lamb_Mssql_Sql_Helper)
						->run();