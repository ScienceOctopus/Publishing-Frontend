<?php

if (!isset($_GET['Id']))
{
	http_response_code(400);
	return;
}

session_start();

require_once 'Globals.php';
require_once COMPOSER_AUTOLOADIR;
require_once 'Models/Tags.php';
require_once 'Models/Users.php';
require_once 'Models/Stages.php';
require_once 'Models/Problems.php';
require_once 'Models/Publications.php';
require_once 'Models/Collaborators.php';
require_once 'Environment Interfaces/Session.php';

$Templater = new \Twig\Environment(new \Twig\Loader\Filesystemloader(array(RelativeToAbsolute('Templates'))), GetTwigOptions());

$Publication = Publications::GetById($_GET['Id']);
if ($Publication === null)
{
	http_response_code(404);
	return;
}

$Details = Session::GetLoggedInDetails();
Problems::GetByPublicationAndAugment($Publication);
Stages::GetByProblemAndAugment($Publication->Problem);
Stages::GetByPublicationAndAugment($Publication);
Publications::GetByProblemAndAugmentLinkedPublications($Publication->Problem, $Publication);
Collaborators::GetByPublicationAndAugment($Publication);
Tags::GetByPublicationAndAugment($Publication);

$Templater->display('Show Publication.html', array('Publication' => $Publication, 'LoginDetails' => $Details, 'ActivePublication' => $Publication));