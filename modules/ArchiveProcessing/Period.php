<?php

class Piwik_ArchiveProcessing_Period extends Piwik_ArchiveProcessing
{
	function __construct()
	{
	}
	
	private function archiveNumericValuesGeneral($aNames, $operationToApply)
	{
		if(!is_array($aNames))
		{
			$aNames = array($aNames);
		}
		
		// fetch the numeric values and apply the operation on them
		$results = array();
		foreach($this->archives as $archive)
		{
			foreach($aNames as $name)
			{
				if(!isset($results[$name]))
				{
					$results[$name] = 0;
				}
				$valueToSum = $archive->getNumeric($name);
				
				if($valueToSum !== false)
				{
					switch ($operationToApply) {
						case 'sum':
							$results[$name] += $valueToSum;	
							break;
						case 'max':
							$results[$name] = max($results[$name], $valueToSum);		
							break;
						case 'min':
							$results[$name] = min($results[$name], $valueToSum);		
							break;
						default:
							throw new Exception("Operation not applicable.");
							break;
					}								
				}
			}
		}
		
		// build the Record Numeric objects
		$records = array();
		foreach($results as $name => $value)
		{
			$records[$name] = new Piwik_ArchiveProcessing_Record_Numeric(
													$name, 
													$value
												);
		}
		
		// if asked for only one field to sum
		if(count($records) == 1)
		{
			return $records[$name];
		}
		
		// returns the array of records once summed
		return $records;
	}
	
	public function archiveNumericValuesSum( $aNames )
	{
		return $this->archiveNumericValuesGeneral($aNames, 'sum');
	}
	
	public function archiveNumericValuesMax( $aNames )
	{
		return $this->archiveNumericValuesGeneral($aNames, 'max');
	}
	
	public function archiveDataTable( $aRecordName )
	{
		if(!is_array($aRecordName))
		{
			$aRecordName = array($aRecordName);
		}
		
		$records[] = array();
		foreach($aRecordName as $recordName)
		{
			$table = $this->getRecordDataTableSum($recordName);
			$records[$recordName] = new Piwik_ArchiveProcessing_Record_Blob_Array($recordName, $table->getSerialized());
//			echo $table;
		}
		return $records;
	}
	

	protected function getRecordDataTableSum( $name )
	{
		$table = new Piwik_DataTable;
		foreach($this->archives as $archive)
		{
			$archive->preFetchBlob($name);
					
			$datatableToSum = $archive->getDataTable($name);
			
			$archive->loadSubDataTables($name, $datatableToSum);
			
			$table->addDataTable($datatableToSum);
			
			$archive->freeBlob($name);
		}
		return $table;
	}
	
	
	protected function compute()
	{		
		$this->archives = $this->archivesSubperiods;
		
		$this->archiveNumericValuesMax( 'max_actions' ); 
		$toSum = array(
			'nb_uniq_visitors', 
			'nb_visits',
			'nb_actions', 
			'sum_visit_length',
			'bounce_count',
		);
		$this->archiveNumericValuesSum($toSum);
		
		Piwik_PostEvent('ArchiveProcessing_Period.compute', $this);		
		
		//delete all DataTable instanciated
//		Piwik_DataTable_Manager::getInstance()->deleteAll();
		
	}
}