<?php
session_start();

require_once 'Globals.php';
require_once COMPOSER_AUTOLOADIR;
require_once 'Models/Users.php';
require_once 'Models/Publications.php';
require_once 'Environment Interfaces/Session.php';

$Templater = new \Twig\Environment(new \Twig\Loader\Filesystemloader(array('Templates')), GetTwigOptions());

$Details = Session::GetLoggedInDetails();
if (!$Details->LoggedIn)
{
	header('WWW-Authenticate: OAuth', 401);
	return;
}

$Publications = Publications::GetByUser($_SESSION['UserId']);
$RequestedSignoffs = Publications::GetByUserAndSignoffRequested($_SESSION['UserId']);
$Templater->display('Profile.html', array('LoginDetails' => $Details, 'Publications' => $Publications, 'RequestedSignoffs' => $RequestedSignoffs));