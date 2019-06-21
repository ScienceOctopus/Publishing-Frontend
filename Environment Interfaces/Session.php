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
		$this->OAuthFlowStartURI = Session::GetGoblinIdAuthorisationURI();
	}

	public $LoggedIn = false;
	public $OAuthFlowStartURI;
}

final class Session
{
	static function GetLoggedInDetails()
	{
		if (!isset($_SESSION['OAuthToken']))
		{
			return new NotLoggedInAccountDetails();
		}

		static $User;
		if ($User === null)
		{
			$User = Users::GetById($_SESSION['UserId']);
		}
		return new LoggedInAccountDetails($User);
	}

	static function GetGoblinIdAuthorisationURI()
	{
		$_SESSION['OAuthState'] = hash('sha512', session_id());

		return WebURI::GoblinIdLogin .
			'?' .
			http_build_query(
				array(
					'state' => $_SESSION['OAuthState'],
					'client_id' => getenv('GOBLINID_OAUTH_CLIENT_ID'),
					'response_type' => 'code',
					'scope' => '/authenticate',
					'redirect_uri' => 'https://' . $_SERVER['SERVER_NAME'] . WebURI::GoblinIdLoginReturn . '?' . http_build_query(
						array(
							'redirect' => $_SERVER['REQUEST_URI']
						)
					)
				)
			);
	}

	static function ExchangeGoblinIdToken($AuthorisationCode)
	{
		if (
			!isset($_GET['state']) ||
			!isset($_SESSION['OAuthState']) ||
			($_GET['state'] != $_SESSION['OAuthState'])
		)
		{
			return false;
		}

		$CURLInstance = curl_init(WebURI::GoblinIdExchangeToken);
		$Headers[] = 'Accept: application/json';
		$Headers[] = 'User-Agent: Octopus (PHP backend)';

		if (isset($_SESSION['OAuthToken']))
		{
			$Headers[] = 'Authorization: Bearer ' . $_SESSION['OAuthToken'];
		}

		curl_setopt($CURLInstance, CURLOPT_HTTPHEADER, $Headers);
		curl_setopt($CURLInstance, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($CURLInstance, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt(
			$CURLInstance,
			CURLOPT_POSTFIELDS,
			http_build_query(
				array(
					'client_id' => getenv('GOBLINID_OAUTH_CLIENT_ID'),
					'client_secret' => getenv('GOBLINID_OAUTH_CLIENT_SECRET'),
					'grant_type' => 'authorization_code',
					'state' => $_SESSION['OAuthState'],
					'code' => $AuthorisationCode
				)
			)
		);

		$Response = json_decode(curl_exec($CURLInstance));
		if (isset($Response->access_token))
		{
			$_SESSION['OAuthToken'] = $Response->access_token;
			return $Response;
		}
		
		return false;
	}
	
	static function GetGoblinIdUserDetails($GoblinId)
	{
		$GoblinIdEmailEndpoint = "https://pub.orcid.org/v2.1/$GoblinId/email";
		
		$CURLInstance = curl_init($GoblinIdEmailEndpoint);
		$Headers = array(
			'Accept: application/vnd.orcid+xml',
			'User-Agent: Octopus (PHP backend)',
			'Authorization: Bearer ' . $_SESSION['OAuthToken']
		);

		curl_setopt($CURLInstance, CURLOPT_HTTPHEADER, $Headers);
		curl_setopt($CURLInstance, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($CURLInstance, CURLOPT_FOLLOWLOCATION, true);

		$XML = new DOMDocument();
		$XML->loadXML(curl_exec($CURLInstance));
		if (!$XML->schemaValidate('Environment Interfaces' . DIRECTORY_SEPARATOR . 'GoblinID Schemas' . DIRECTORY_SEPARATOR . 'record_3.0' . DIRECTORY_SEPARATOR . 'email-3.0.xsd'))
		{
			throw new Exception(libxml_get_errors());
		}
		return $XML;
	}
}
?>