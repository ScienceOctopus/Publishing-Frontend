<?php
final class Publications
{
	/* The mapping is only getting the fields required by the callers; extend if necessary */
	
	private static function psql_to_bool($Column)
	{
		// Compare with 't' because that's what is returned in a bool column for true, apparently
		return $Column === 't';
	}
	
	static function GetById($Id)
	{
		$Query = pg_query_params(
			'SELECT stage, title, summary, created_at, review, funding, conflict FROM publications WHERE id = $1',
			array($Id)
		);
		
		$Result = pg_fetch_row($Query);
		
		// User-provided data
		if (!$Result)
		{
			return null;
		}
		
		return new Publication($Id, $Result[0], $Result[1], $Result[2], $Result[3], Publications::psql_to_bool($Result[4]), $Result[5], $Result[6], null);
	}
	
	static function GetByUser($UserId)
	{
		$Query = pg_query_params(
			'SELECT publications.id, publications.title, publications.summary, publications.created_at, publications.review, publications.draft FROM publications, publication_collaborators
WHERE publication_collaborators.publication = publications.id AND publication_collaborators.user = $1',
			array($UserId)
		);
		
		$Results = pg_fetch_all($Query, PGSQL_NUM);
		if (!$Results)
		{
			return array();
		}
		
		return array_map(
			function ($Result)
			{
				// Used in user profile, doesn't need funding or conflict
				return new Publication($Result[0], null, $Result[1], $Result[2], $Result[3], Publications::psql_to_bool($Result[4]), null, null, Publications::psql_to_bool($Result[5]));
			},
			$Results
		);
	}
	
	static function GetByUserAndSignoffRequested($UserId)
	{
		// Assume that signoff revision != publication revision means means that signoff < publication
		// (as opposed to signoffs advancing past publication, which shouldn't happen, heh)
		// It should also be the case that there is exactly one publication_signoff for one publication+pub_collaborator join
		// so there is no need to further filter on e.g. publication_signoff.user/publication
		$Query = pg_query_params(
			'SELECT publications.id, publications.title, publications.summary, publications.created_at, publications.review, publications.draft FROM publications
INNER JOIN publication_collaborators ON publication_collaborators.publication = publications.id
LEFT JOIN publication_signoffs ON publication_signoffs.publication = publications.id
WHERE publication_collaborators.user = $1 AND
((publication_signoffs.id IS NULL) OR (NOT publication_signoffs.revision = publications.revision))',
			array($UserId)
		);
		
		$Results = pg_fetch_all($Query, PGSQL_NUM);
		if (!$Results)
		{
			return array();
		}
		
		return array_map(
			function ($Result)
			{
				// Used in user profile, doesn't need funding or conflict
				return new Publication($Result[0], null, $Result[1], $Result[2], $Result[3], Publications::psql_to_bool($Result[4]), null, null, Publications::psql_to_bool($Result[5]));
			},
			$Results
		);
	}
	
	private static function GetByProblemAndAugmentWith($Query, $Parameters, $Problem)
	{
		$Query = pg_query_params($Query, $Parameters);
		$Results = pg_fetch_all($Query, PGSQL_NUM);
		
		if (!$Results)
		{
			return;
		}
		
		// Create a mapping from stage database id to stage
		// The array index isn't necessarily the database id
		$StageMap = array();
		foreach ($Problem->Stages as $Stage)
		{
			$StageMap[$Stage->Id] = $Stage;
		}
		
		foreach ($Results as $Result)
		{
			// This is for publications in the flow graph, which doesn't need to know about conflicts of interest or funding
			// StageId is populated because its used in the flow graph to filter by reviews for the currently active stage
			$StageMap[$Result[1]]->Publications[] = new Publication($Result[0], $Result[1], $Result[2], $Result[3], $Result[4], Publications::psql_to_bool($Result[5]), null, null, null);
		}
	}
	
	static function GetByProblemAndAugment(&$Problem)
	{
		Publications::GetByProblemAndAugmentWith(
			'SELECT publications.id, publications.stage, publications.title, publications.summary, publications.created_at, publications.review
FROM publications, problems WHERE publications.problem = problems.id AND problems.id = $1 AND NOT publications.review',
			array($Problem->Id),
			$Problem
		);
	}
	
	static function GetByProblemAndAugmentLinkedPublications(&$Problem, $Publication)
	{
		// Collect all publications linked left and right starting from this Publication
		// (do not perform a complete search on the graph: pick a direction and stick to it)
		// However, if Publication is a review, the search needs to start from the reviewed publication, else the right hand side of the graph is lost
		
		$SearchRootPublicationId = $Publication->Id;
		if ($Publication->IsReview)
		{
			$Query = pg_query_params(
				'SELECT publication_before FROM publication_links WHERE publication_after = $1',
				array($Publication->Id)
			);
			
			// Assume that all reviews are linked to a publication in the DB
			$SearchRootPublicationId = pg_fetch_row($Query)[0];
		}
		
		Publications::GetByProblemAndAugmentWith(
						'WITH RECURSIVE publication_tree_after(id, stage, title, summary, created_at, review)
AS (
SELECT id, stage, title, summary, created_at, review
FROM publications
WHERE id = $1
UNION
SELECT publications.id, publications.stage, publications.title, publications.summary, publications.created_at, publications.review
FROM publications, publication_links, publication_tree_after
WHERE publications.id = publication_after AND publication_before = publication_tree_after.id
),

publication_tree_before(id, stage, title, summary, created_at, review) AS (
SELECT id, stage, title, summary, created_at, review
FROM publications
WHERE id = $1
UNION
SELECT publications.id, publications.stage, publications.title, publications.summary, publications.created_at, publications.review
FROM publications, publication_links, publication_tree_before
WHERE publications.id = publication_before AND publication_after = publication_tree_before.id
)

SELECT *
FROM (SELECT * FROM publication_tree_before UNION SELECT * FROM publication_tree_after) AS publication_tree',
			array($SearchRootPublicationId),
			$Problem
		);
	}
}

final class Publication
{
	public function __construct($Id, $StageId, $Title, $Summary, $CreatedAt, $IsReview, $FundingStatement, $InterestingConflicts, $IsDraft)
	{
		$this->Id = $Id;
		$this->StageId = $StageId;
		$this->Title = $Title;
		$this->Summary = $Summary;
		$this->CreatedAt = $CreatedAt;
		$this->IsReview = $IsReview;
		$this->FundingStatement = $FundingStatement;
		$this->InterestingConflicts = $InterestingConflicts;
		$this->IsDraft = $IsDraft;
		
		$this->Collaborators = array();
		$this->Tags = array();
	}

	public $Id;
	public $StageId;
	public $Title;
	public $Summary;
	public $CreatedAt;
	public $IsReview;
	public $FundingStatement;
	public $InterestingConflicts;
	public $IsDraft;
	
	public $Problem;
	public $Stage;
	public $Collaborators;
	public $Tags;
}
?>