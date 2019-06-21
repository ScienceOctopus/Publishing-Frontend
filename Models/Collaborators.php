<?php
final class Collaborators
{
	static function GetByPublicationAndAugment(&$Publication)
	{
		$Query = pg_query_params(
			'SELECT role, orcid, display_name, email FROM publication_collaborators, users WHERE publication_collaborators.user = users.id AND publication_collaborators.publication = $1',
			array($Publication->Id)
		);
		
		$Results = pg_fetch_all($Query, PGSQL_NUM);
		if (!$Results)
		{
			return;
		}
		
		foreach ($Results as $Result)
		{
			$Publication->Collaborators[] = new Collaborator($Result[0], new User($Result[1], $Result[2], $Result[3]));
		}
	}
}

final class Collaborator
{
	public function __construct($Role, $User)
	{
		// Breaking with norms here to directly construct a User
		// For performance I guess?
		$this->Role = $Role;
		$this->User = $User;
	}
	
	public $User;
	public $Role;
}