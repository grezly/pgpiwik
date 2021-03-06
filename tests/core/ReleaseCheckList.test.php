<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}

//Zend_Loader::loadClass('Piwik_');

class Test_Piwik_ReleaseCheckList extends UnitTestCase
{
    public function test_checkThatConfigurationValuesAreProductionValues()
    {
    	$this->globalConfig = parse_ini_file(PIWIK_PATH_TEST_TO_ROOT . '/config/global.ini.php', true);
//    	var_dump($globalConfig);
    	$this->checkEqual(array('Debug' => 'always_archive_data_day'), '0');
    	$this->checkEqual(array('Debug' => 'always_archive_data_period'), '0');
    	$this->checkEqual(array('Debug' => 'enable_sql_profiler'), '0');
    	$this->checkEqual(array('General' => 'time_before_today_archive_considered_outdated'), '10');
    	$this->checkEqual(array('General' => 'enable_browser_archiving_triggering'), '1');
    	$this->checkEqual(array('General' => 'default_language'), 'en');
    	$this->checkEqual(array('Tracker' => 'record_statistics'), '1');
    	$this->checkEqual(array('Tracker' => 'visit_standard_length'), '1800');
    	$this->checkEqual(array('Tracker' => 'enable_detect_unique_visitor_using_settings'), '1');
    	$this->checkEqual(array('log' => 'logger_message'), array('screen'));
    	$this->checkEqual(array('log' => 'logger_exception'), array('screen'));
    	$this->checkEqual(array('log' => 'logger_error'), array('screen'));
    	$this->checkEqual(array('log' => 'logger_api_call'), null);
    }
    private function checkEqual($key, $valueExpected)
    {
    	$section = key($key);
    	$optionName = current($key);
    	$value = null;
    	if(isset($this->globalConfig[$section][$optionName]))
    	{
	    	$value = $this->globalConfig[$section][$optionName];
    	}
    	$this->assertEqual($value, $valueExpected, "$section -> $optionName was '$value', expected '$valueExpected'");
    }
    
    public function test_checkThatGivenPluginsAreDisabledByDefault()
    {
    	$pluginsShouldBeDisabled = array(
    		'DBStats',
    		'Goals',
    		'Live',
    	);
    	foreach($pluginsShouldBeDisabled as $pluginName)
    	{
	    	if(in_array($pluginName, $this->globalConfig['Plugins']['Plugins']))
	    	{
	    		throw new Exception("Plugin $pluginName is enabled by default but shouldn't.");
	    	}
    	}
    	
    }
    /**
     * test that the profiler is disabled (mandatory on a production server)
     */
    public function test_profilingDisabledInProduction()
    {
    	require_once 'Tracker/Db.php';
    	$this->assertTrue(Piwik_Tracker_Db::isProfilingEnabled() === false, 'SQL profiler should be disabled in production! See Piwik_Tracker_Db::$profiling');
    }
    

	function test_piwikTrackerDebugIsOff()
	{
		$this->assertTrue(!isset($GLOBALS['PIWIK_TRACKER_DEBUG']));
		define('PIWIK_ENABLE_TRACKING', false);
		include PIWIK_PATH_TEST_TO_ROOT . "/piwik.php";
		$this->assertTrue($GLOBALS['PIWIK_TRACKER_DEBUG'] === false);
	}
}

