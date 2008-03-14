<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_LogStats
 */


/**
 * Handles an action by the visitor.
 * A request to the piwik.php script is associated with one Action.
 * This class is used to build the Action Name (which can be built from the URL, 
 * or can be directly specified in the JS code, etc.).
 * It also saves the Action when necessary in the DB. 
 * 
 * 
 * About the Action concept
 * ------------------------------------
 * - An action is defined by a name.
 * - The name can be specified in the JS Code in the variable 'action_name'
 * - Handling UTF8 in the action name
 * PLUGIN_IDEA - An action is associated to URLs and link to the URL from the interface
 * PLUGIN_IDEA - An action hit by a visitor is associated to the HTML title of the page that triggered the action
 * 
 * + If the name is not specified, we use the URL(path+query) to build a default name.
 *   For example for "http://piwik.org/test/my_page/test.html" 
 *   the name would be "test/my_page/test.html"
 * 
 * We make sure it is clean and displayable.
 * If the name is empty we set it to a default name.
 *  
 * Specifications
 *  
 * - Download tracking
 * 
 *   * MANUAL Download tracking 
 *      download = http://piwik.org/hellokity.zip
 * 	(name = dir1/file alias name)
 *
 *   * AUTOMATIC Download tracking for a known list of file extensions. 
 *    Make a hit to the piwik.php with the parameter: 
 *      download = http://piwik.org/hellokity.zip
 *  
 *   When 'name' is not specified, 
 * 	if AUTOMATIC and if anchor not empty => name = link title anchor
 * 	else name = path+query of the URL
 *   Ex: myfiles/beta.zip
 *
 * - External link tracking
 * 
 *   * MANUAL External link tracking
 * 	 outlink = http://amazon.org/test
 * 	(name = the big partners / amazon)
 * 
 *   * AUTOMATIC External link tracking
 *      When a link is not detected as being part of the same website 
 *     AND when the url extension is not detected as being a file download
 * 	 outlink = http://amazon.org/test
 * 
 *  When 'name' is not specified, 
 * 	if AUTOMATIC and if anchor not empty => name = link title anchor
 * 	else name = URL
 *   Ex: http://amazon.org/test
 * 
 * 
 * @package Piwik_LogStats
 */
class Piwik_LogStats_Action
{
	private $actionName;
	private $url;
	private $defaultActionName;
	private $nameDownloadOutlink;
	
	/**
	 * 3 types of action, Standard action / Download / Outlink click
	 */
	const TYPE_ACTION   = 1;
	const TYPE_DOWNLOAD = 3;
	const TYPE_OUTLINK  = 2;
	
	/**
	 * @param Piwik_LogStats_Db object
	 */
	function __construct( $db )
	{
		$this->actionName = Piwik_Common::getRequestVar( 'action_name', '', 'string');
		
		$downloadVariableName = Piwik_LogStats_Config::getInstance()->LogStats['download_url_var_name'];
		$this->downloadUrl = Piwik_Common::getRequestVar( $downloadVariableName, '', 'string');
		
		$outlinkVariableName = Piwik_LogStats_Config::getInstance()->LogStats['outlink_url_var_name'];
		$this->outlinkUrl = Piwik_Common::getRequestVar( $outlinkVariableName, '', 'string');
		
		$nameVariableName = Piwik_LogStats_Config::getInstance()->LogStats['download_outlink_name_var'];
		$this->nameDownloadOutlink = Piwik_Common::getRequestVar( $nameVariableName, '', 'string');
		
		$this->url = Piwik_Common::getRequestVar( 'url', '', 'string');
		$this->db = $db;
		$this->defaultActionName = Piwik_LogStats_Config::getInstance()->LogStats['default_action_name'];
	}
	
	/**
	 * Generate the name of the action from the URL or the specified name.
	 * See the class description for more information.
	 */
	private function generateInfo()
	{
		if(!empty($this->downloadUrl))
		{
			$this->actionType = self::TYPE_DOWNLOAD;
			$url = $this->downloadUrl;
			$actionName = $this->nameDownloadOutlink;
		}
		elseif(!empty($this->outlinkUrl))
		{
			$this->actionType = self::TYPE_OUTLINK;
			$url = $this->outlinkUrl;
			//remove the last '/' character if it's present
			if(substr($url,-1) == '/')
			{
				$url = substr($url,0,-1);
			}
			$actionName = $this->nameDownloadOutlink;
			if( empty($actionName) )
			{
				$actionName = $url;
			}
		}
		else
		{
			$this->actionType = self::TYPE_ACTION;
			$url = $this->url;
			$actionName = $this->actionName;
		}		
		
		// the ActionName wasn't specified
		if( empty($actionName) )
		{
			$actionName = trim(Piwik_Common::getPathAndQueryFromUrl($url));
			
			// in case the $actionName is ending with a slash, 
			// which means that it is the index page of a category 
			// we append the defaultActionName 
			// toto/tata/ becomes toto/tata/index 
			if(strlen($actionName) > 0 
				&& $actionName[strlen($actionName)-1] == '/'
			)
			{
				$actionName.=$this->defaultActionName;
			}
		}
		
		/*
		 * Clean the action name
		 */
		 
		// get the delimiter, by default '/'
		$actionCategoryDelimiter = Piwik_LogStats_Config::getInstance()->General['action_category_delimiter'];
		
		// case the name is an URL we dont clean the name the same way
		if(Piwik_Common::isLookLikeUrl($actionName))
		{
			$actionName = trim($actionName);
		}
		else
		{
			// create an array of the categories delimited by the delimiter
			$split = explode($actionCategoryDelimiter, $actionName);
			
			// trim every category
			$split = array_map('trim', $split);
			
			// remove empty categories
			$split = array_filter($split);
			
			// rebuild the name from the array of cleaned categories
			$actionName = implode($actionCategoryDelimiter, $split);
		}
		
		// remove the extra bad characters if any (shouldn't be any at this point...)
		$actionName = str_replace(array("\n", "\r"), '', $actionName);
		
		if(empty($actionName))
		{
			$actionName = $this->defaultActionName;
		}
		
		$this->finalActionName = $actionName;
	}
	
	/**
	 * Returns the idaction of the current action name.
	 * This idaction is used in the visitor logging table to link the visit information 
	 * (entry action, exit action) to the actions.
	 * This idaction is also used in the table that links the visits and their actions.
	 * 
	 * The methods takes care of creating a new record in the action table if the existing 
	 * action name doesn't exist yet.
	 * 
	 * @return int Id action that is associated to this action name in the Actions table lookup
	 */
	function getActionId()
	{
		$this->loadActionId();
		return $this->idAction;
	}
	
	/**
	 * @see getActionId()
	 */
	private function loadActionId()
	{		
		$this->generateInfo();
		
		$name = $this->finalActionName;
		$type = $this->actionType;
		
		$idAction = $this->db->fetch("	SELECT idaction 
							FROM ".$this->db->prefixTable('log_action')
						."  WHERE name = ? AND type = ?", array($name, $type) );
		
		// the action name has not been found, create it
		if($idAction === false)
		{
			$this->db->query("INSERT INTO ". $this->db->prefixTable('log_action'). "( name, type ) 
								VALUES (?,?)",array($name,$type) );
			$idAction = $this->db->lastInsertId();
		}
		else
		{
			$idAction = $idAction['idaction'];
		}
		
		$this->idAction = $idAction;
	}
	
	/**
	 * Records in the DB the association between the visit and this action.
	 * 
	 * @param int idVisit is the ID of the current visit in the DB table log_visit
	 * @param int idRefererAction is the ID of the last action done by the current visit. 
	 * @param int timeSpentRefererAction is the number of seconds since the last action was done. 
	 * 				It is directly related to idRefererAction.
	 */
	 public function record( $idVisit, $idRefererAction, $timeSpentRefererAction)
	 {
	 	$this->db->query("INSERT INTO ".$this->db->prefixTable('log_link_visit_action')
						." (idvisit, idaction, idaction_ref, time_spent_ref_action) VALUES (?,?,?,?)",
					array($idVisit, $this->idAction, $idRefererAction, $timeSpentRefererAction)
					);
	 }
}


