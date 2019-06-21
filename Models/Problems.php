<?php
class Problems
{
	/* The mapping is only getting the fields required by the callers; extend if necessary */
	 
	static function GetByWildcard($PartialMatch)
	{
		$Query = pg_query_params(
			'SELECT id, title, description, updated_at FROM problems WHERE lower(title) LIKE $1 OR lower(description) LIKE $1',
			array("%$PartialMatch%")
		);
		
		$Results = pg_fetch_all($Query, PGSQL_NUM);
		if (!$Results)
		{
			return array();
		}
		
		return array_map(
			function ($Result)
			{
				$Query = pg_query_params(
					'SELECT COUNT(*) FROM problems, publications WHERE problems.id = $1 AND problems.id = publications.problem AND NOT draft GROUP BY problems.id',
					array($Result[0])
				);
				
				$CountResult = pg_fetch_row($Query);
				return new Problem($Result[0], $Result[1], $Result[2], $Result[3], $CountResult[0]);
			},
			$Results
		);
	}
	
	static function GetById($Id)
	{
		$Query = pg_query_params(
			'SELECT title FROM problems WHERE id = $1',
			array($Id)
		);
		
		$Result = pg_fetch_row($Query);
		
		// User-provided data
		if (!$Result)
		{
			return null;
		}
		
		return new Problem($Id, $Result[0], null, null, null);
	}
	
	static function GetByPublicationAndAugment(&$Publication)
	{
		$Query = pg_query_params(
			'SELECT problems.id, problems.title FROM problems, publications WHERE publications.problem = problems.id AND publications.id = $1',
			array($Publication->Id)
		);
		
		$Result = pg_fetch_row($Query);
		$Publication->Problem = new Problem($Result[0], $Result[1], null, null, null);
	}
}

class Problem
{
	public function __construct($Id, $Tit, $Desc, $Updated, $Cnt)
	{
		$this->Id = $Id;
		$this->Title = $Tit;
		$this->Description = $Desc;
		$this->LastModified = $Updated;
		$this->PublicationCount = $Cnt;
		
		// No constraints in database that stages can't be empty, so initialise to something
		// (Augmenting stages won't do anything if there aren't any stages, and we don't want to crash)
		$this->Stages = array();
	}

	public $Id;
	public $Title;
	public $Description;
	public $LastModified;
	public $PublicationCount;
	
	public $Stages;
}
?>