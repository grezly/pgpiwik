<?php
/**
 * Misc Thoughts about optimization
 * 
 * - after a day is archived, we delete all the useless information from the log table, keeping only the useful data for weeks/month
 *   maybe we create a new table containing only these aggregate and we can delete the rows of the day in the log table
 */
 
/*
 * Some benchmarks
 * 
 * - with the config parsing + db connection
 * Requests per second:    471.91 [#/sec] (mean)
 * 
 * - with the main algorithm working + one visitor requesting 5000 times
 * Requests per second:    155.00 [#/sec] (mean)
 * 
 * - august 28th, main algo + files in place + one visitor requesting 5000 times
 * Requests per second:    118.55 [#/sec] (mean)
 */
error_reporting(E_ALL|E_NOTICE);
define('PIWIK_INCLUDE_PATH', '.');
define('PIWIK_PLUGINS_PATH', PIWIK_INCLUDE_PATH . '/plugins');
define('PIWIK_DATAFILES_INCLUDE_PATH', PIWIK_INCLUDE_PATH . "/modules/DataFiles");

@ignore_user_abort(true);
@set_time_limit(0);

set_include_path(PIWIK_INCLUDE_PATH 
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/libs/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/plugins/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/modules'
					. PATH_SEPARATOR . get_include_path() );

require_once "Common.php";
require_once "PluginsManager.php";
require_once "LogStats.php";
require_once "LogStats/Config.php";
require_once "LogStats/Action.php";
require_once "Cookie.php";
require_once "LogStats/Db.php";
require_once "LogStats/Visit.php";

$GLOBALS['DEBUGPIWIK'] = false;

ob_start();
printDebug($_GET);
$process = new Piwik_LogStats;
$process->main();
ob_end_flush();
printDebug($_COOKIE);

