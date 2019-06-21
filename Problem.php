<?php

if (!isset($_GET['Id']))
{
	http_response_code(400);
	return;
}

session_start();

require_once 'Globals.php';
require_once COMPOSER_AUTOLOADIR;
require_once 'Models/Users.php';
require_once 'Models/Stages.php';
require_once 'Models/Problems.php';
require_once 'Models/Publications.php';
require_once 'Environment Interfaces/Session.php';

$Templater = new \Twig\Environment(new \Twig\Loader\Filesystemloader(array(RelativeToAbsolute('Templates'))), GetTwigOptions());

$Problem = Problems::GetById($_GET['Id']);
if ($Problem === null)
{
	http_response_code(404);
	return;
}

Stages::GetByProblemAndAugment($Problem);
Publications::GetByProblemAndAugment($Problem);
$Details = Session::GetLoggedInDetails();

$Templater->display('Show Problem.html', array('Problem' => $Problem, 'LoginDetails' => $Details));