<?php
session_start();

require_once 'Globals.php';
require_once 'Models/Users.php';
require_once 'Environment Interfaces/Session.php';

$Details = Session::GetLoggedInDetails();
if (!$Details->LoggedIn)
{
	header('WWW-Authenticate: OAuth', 401);
	return;
}

if ($Details->User->Email === null)
{
	SetRedirect('/images/avatar.jpg');
	return;
}

$EmailHash = md5($Details->User->Email);
$RequestHost = $_SERVER['HTTP_HOST'];

// Always HTTPS since we "shouldn't ever" get here unless using https:// as all session cookies have cookie_secure on 
$RequestProtocol = 'https';

$CURLInstance = curl_init("https://gravatar.com/avatar/$EmailHash");
$Headers = array("Forwarded: for=_hidden;host=$RequestHost;proto=$RequestProtocol", 'User-Agent: Octopus (PHP backend)');

curl_setopt($CURLInstance, CURLOPT_HTTPHEADER, $Headers);
curl_setopt($CURLInstance, CURLOPT_RETURNTRANSFER, true);
curl_setopt($CURLInstance, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($CURLInstance, CURLOPT_HEADERFUNCTION,
	function($CURL, $Header) use (&$headers)
	{
		header($Header);
		return strlen($Header);
	}
);

echo curl_exec($CURLInstance);