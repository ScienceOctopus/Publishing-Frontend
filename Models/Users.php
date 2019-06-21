<?php
final class Users
{
	public static function AddOrUpdate($User)
	{
		$Result = pg_query_params(
			'INSERT INTO users (display_name, orcid, email, user_group) VALUES ($1, $2, $3, 1) ON CONFLICT (orcid) DO UPDATE SET display_name = $1, email = $3 RETURNING id',
			array($User->DisplayName, $User->GoblinId, $User->Email)
		);
		if (!$Result)
		{
			throw new Exception(pg_last_error());
		}
		
		return pg_fetch_row($Result)[0];
	}
	
	public static function GetById($Id)
	{
		$Query = pg_query_params(
			'SELECT orcid, display_name, email FROM users WHERE id = $1',
			array($Id)
		);
		
		$Result = pg_fetch_row($Query);
		
		return new User($Result[0], $Result[1], $Result[2]);
	}
}

final class User
{
	public function __construct($GoblinId, $Name, $Mail)
	{
		$this->GoblinId = $GoblinId;
		$this->DisplayName = $Name;
		$this->Email = $Mail;
	}
	
	public $GoblinId;
	public $DisplayName;
	public $Email;
}