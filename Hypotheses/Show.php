<?php
session_start();
set_include_path(get_include_path() . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT']);

require_once 'Globals.php';
require_once COMPOSER_AUTOLOADIR;
require_once 'Models/Problem.php';
require_once 'Models/Hypothesis.php';
require_once 'Environment Interfaces/Cache.php';
require_once 'Environment Interfaces/Session.php';

if (!isset($_GET['Id']))
{
	http_response_code(400);
	return;
}

$Templater = new \Twig\Environment(new \Twig\Loader\Filesystemloader(array(RelativeToAbsolute('Templates'))), GetTwigOptions());
$SQLLink = new MeekroDB(DB_ADDRESS, DB_USERNAME, DB_PASSWORD, DB_PLUGINSDATABASENAME);

$Result = $SQLLink->queryFirstRow(
	'SELECT * FROM Hypotheses, Problems WHERE Hypotheses.ProblemId = Problems.ProblemId AND Hypotheses.HypothesisId = %i',
	$_GET['Id']
);

if (empty($Result))
{
	http_response_code(404);
	return;
}

$Hypothesis = new Hypothesis($Result);

$Results = $SQLLink->query(
	'SELECT * FROM Hypotheses WHERE Hypotheses.ProblemId = %i',
	$Hypothesis->Problem->ProblemId
);

$Hypotheses = array_map(
	function($Result)
	{
		return new Hypothesis($Result);
	},
	$Results
);

Session::GetLoggedInDetails($Details);
$Templater->display('Show Hypothesis.html', array('Hypothesis' => $Hypothesis, 'Hypotheses' => $Hypotheses, 'LoginDetails' => $Details));
?>