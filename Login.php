<?php
session_start();

require_once 'Globals.php';
require_once 'Models/Users.php';
require_once 'Environment Interfaces/Session.php';

if (isset($_GET['logout']) && $_GET['logout'])
{
	session_unset();
	session_destroy();

	SetRedirect();
	return;
}

$HasRedirect = isset($_GET['redirect']);

if (isset($_GET['code']))
{
	$ExchangeResponse = Session::ExchangeGoblinIdToken($_GET['code']);
	if (!$ExchangeResponse)
	{	
		session_unset();
		session_destroy();
		
		http_response_code(500);
		return;
	}
	
	$DisplayName = $ExchangeResponse->name;
	$GoblinId = $ExchangeResponse->orcid;
	
	// Make another visit to the Goblin to solicit user details
	$UserEmail = Session::GetGoblinIdUserDetails($GoblinId)->getElementsByTagName('emails')[0]->getElementsByTagName('email');
	$Email = ($UserEmail->count() === 0) ? null : $UserEmail[0]->getElementsByTagName('email')[0]->nodeValue;
	
	$User = new User($GoblinId, $DisplayName, $Email);
	try
	{
		$UserId = Users::AddOrUpdate($User);
		$_SESSION['UserId'] = $UserId;
	}
	catch (Exception $UpsertError)
	{
		session_unset();
		session_destroy();
		
		http_response_code(500);
		throw $UpsertError;
	}

	if ($HasRedirect)
	{
		SetRedirect('https://' . $_SERVER['SERVER_NAME'] . urldecode($_GET['redirect']));
		return;
	}
}

http_response_code(400);