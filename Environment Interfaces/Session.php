<?php
require_once 'Globals.php';
require_once COMPOSER_AUTOLOADIR;
require_once 'Environment Interfaces/Cache.php';

final class LoggedInAccountDetails
{
	public $LoggedIn = true;
	public $User;

	public function __construct($Details)
	{
		$this->User = $Details;
	}
}

final class NotLoggedInAccountDetails
{
	public function __construct()
	{
		$this->LoginRedirectURL = http_build_query(array('login' => 1, 'redirect' => $_SERVER['REQUEST_URI']));
	}

	public $LoggedIn = false;
	public $LoginRedirectURL;
}

final class Session
{
	static function GetLoggedInDetails(&$Details = null)
	{
		if (!isset($_SESSION['OAuthToken']))
		{
			$Details = new NotLoggedInAccountDetails();
			return false;
		}

		if (!isset($_SESSION['UserID']))
		{
			session_unset();
			session_destroy();

			$Details = new NotLoggedInAccountDetails();
			return false;
		}

		$Details = new LoggedInAccountDetails(unserialize(Cache::GetCacheEntry(CacheType::Users, $_SESSION['UserID'])));
		return true;
	}

	static function AuthoriseViaORCiD($AdditionalParameters)
	{
		SetRedirect('/login?code=1337&redirect=/');
	}

	static function ExchangeORCiDToken($AuthorisationCode)
	{
		$_SESSION['OAuthToken'] = 1;
		$_SESSION['UserID'] = 1;
		Cache::UpdateCacheEntry(CacheType::Users, 1, serialize(array('avatar_url' => 'https://www.gravatar.com/avatar/cc9c4926a7d769cc4c22d1e0993bbaed', 'name' => 'Stop squidding around')));
		return true;
	}
}
?>