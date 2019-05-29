<?php
session_start();

require_once 'Globals.php';
require_once COMPOSER_AUTOLOADIR;
require_once 'Models/Problem.php';
require_once 'Environment Interfaces/Cache.php';
require_once 'Environment Interfaces/Session.php';

$Templater = new \Twig\Environment(new \Twig\Loader\Filesystemloader(array('Templates')), GetTwigOptions());
$SQLLink = new MeekroDB(DB_ADDRESS, DB_USERNAME, DB_PASSWORD, DB_PLUGINSDATABASENAME);

$Results = $SQLLink->query(
	'SELECT * FROM Problems LIMIT 10'
);

$Problems = array_map(
	function($Result)
	{
		return new Problem($Result);
	},
	$Results
);

Session::GetLoggedInDetails($Details);
$Templater->display('Index.html', array('Problems' => $Problems, 'LoginDetails' => $Details));
?>