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

if (isset($_POST['Upload']))
{
	$Name = sha1_file($_FILES['Document']['tmp_name']);
	if (
		!move_uploaded_file(
			$_FILES['Document']['tmp_name'],
			RelativeToAbsolute(sprintf('Uploads/%s.pdf', $Name))
		)
	)
	{
		http_response_code(400); // TODO more error checking and better response
		return;
	}
	
	$SQLLink->insert('Hypotheses', array('ProblemId' => $_GET['Id'], 'Title' => $_POST['Title'], 'Summary' => $_POST['Summary'], 'DOI' => $_POST['DOI'], 'DateAdded' => $SQLLink->sqleval('CURRENT_DATE()'), 'DocumentName' => $Name));
	SetRedirect('/hypotheses/show/' .  $SQLLink->insertId());
	return;
}

$Problem = $SQLLink->queryFirstRow(
	'SELECT * FROM Problems WHERE Problems.ProblemId = %i',
	$_GET['Id']
);

if (empty($Problem))
{
	http_response_code(404);
	return;
}

Session::GetLoggedInDetails($Details);
$Templater->display('Add Hypothesis.html', array('Problem' => new Problem($Problem), 'LoginDetails' => $Details));
?>