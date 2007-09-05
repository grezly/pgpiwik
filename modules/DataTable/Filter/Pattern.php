<?php
/**
 * Delete all rows for which the given $columnToFilter do not contain the $patternToSearch
 * This filter is to be used on columns containing strings. 
 * Exemple: fron the keyword report, keep only the rows for which the label contains "piwik"
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Filter 
 */
class Piwik_DataTable_Filter_Pattern extends Piwik_DataTable_Filter
{
	private $columnToFilter;
	private $patternToSearch;
	
	public function __construct( $table, $columnToFilter, $patternToSearch )
	{
		parent::__construct($table);
		$this->patternToSearch = $patternToSearch;
		$this->columnToFilter = $columnToFilter;
		$this->filter();
	}
	
	protected function filter()
	{
		foreach($this->table->getRows() as $key => $row)
		{
			if( !eregi($this->patternToSearch, $row->getColumn($this->columnToFilter)))
			{
				$this->table->deleteRow($key);
			}
		}
	}
}

