<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_ExampleFeedburner
 */

class Piwik_ExampleFeedburner extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			'name' => 'Example Feedburner',
			'description' => 'Example Plugin: How to display your Feedburner subscriber in a Widget in the Dashboard?',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
	}

	function install()
	{
		try{
			Piwik_Query('ALTER TABLE '.Piwik::prefixTable('site'). " ADD feedburnerName TEXT DEFAULT NULL");
		} catch(Zend_Db_Statement_Exception $e){
			// pgsql code error 42701: duplicate column
			// if there is another error we throw the exception, otherwise it is OK as we are simply reinstalling the plugin
			if(!ereg('42701',$e->getMessage()))
			{
				throw $e;
			}
		}
	}
	
	function uninstall()
	{
		Piwik_Query('ALTER TABLE '.Piwik::prefixTable('site'). " DROP feedburnerName");
	}
}

Piwik_AddWidget('Example Widgets', 'Feedburner statistics', 'ExampleFeedburner', 'feedburner');

class Piwik_ExampleFeedburner_Controller extends Piwik_Controller
{

	/**
	 * Simple feedburner statistics output
	 *
	 */
	function feedburner()
	{
		$view = new Piwik_View('ExampleFeedburner/templates/feedburner.tpl');
		$idSite = Piwik_Common::getRequestVar('idSite',1,'int');
		$feedburnerFeedName = Piwik_FetchOne('SELECT feedburnerName FROM '.Piwik::prefixTable('site').
								' WHERE idsite = ?', $idSite );
		if(empty($feedburnerFeedName))
		{
			$feedburnerFeedName = 'Piwik';
		}
		$view->feedburnerFeedName = $feedburnerFeedName;
		$view->idSite = $idSite;
		echo $view->render();
	}
	
	/**
	 * Function called to save the Feedburner ID entered in the form
	 *
	 */
	function saveFeedburnerName()
	{
		// we save the value in the DB for an authenticated user
		if(Piwik::getCurrentUserLogin() != 'anonymous')
		{
			Piwik_Query('UPDATE '.Piwik::prefixTable('site').' 
						 SET feedburnerName = ? WHERE idsite = ?', 
				array(Piwik_Common::getRequestVar('name','','string'), Piwik_Common::getRequestVar('idSite',1,'int'))
				);
		}
	}
}
