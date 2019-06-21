<?php
final class Stages
{
	/* The mapping is only getting the fields required by the callers; extend if necessary */
	
	static function GetByProblemAndAugment(&$Problem)
	{
		$Query = pg_query_params(
			'SELECT stages.id, name, singular FROM stages, problem_stages WHERE stages.id = problem_stages.stage AND problem_stages.problem = $1 ORDER BY problem_stages.order',
			array($Problem->Id)
		);
		
		$Results = pg_fetch_all($Query, PGSQL_NUM);
		if (!$Results)
		{
			return;
		}
		
		foreach ($Results as $Result)
		{
			$Problem->Stages[] = new Stage($Result[0], $Result[1], $Result[2]);
		}
	}
	
	static function GetByPublicationAndAugment(&$Publication)
	{
		foreach ($Publication->Problem->Stages as $Stage)
		{
			if ($Stage->Id === $Publication->StageId)
			{
				$Publication->Stage = $Stage;
				return;
			}
		}
		
		throw new Exception('Publication has no linked stage');
	}
}

final class Stage
{
	public function __construct($Id, $Nom, $Sing)
	{
		$this->Id = $Id;
		$this->Name = $Nom;
		$this->SingularName = $Sing;
		
		// See comment in Problems.php. Publications can be potentially empty, so initialise
		$this->Publications = array();
	}
	
	public $Id;
	public $Name;
	public $SingularName;
	
	public $Publications;
}