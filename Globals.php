<?php
if (!($INIParseResult = parse_ini_file('Configuration.ini')))
{
	die;
}

define('DB_ADDRESS', $INIParseResult['DatabaseAddress']);
define('DB_USERNAME', $INIParseResult['DatabaseUsername']);
define('DB_PASSWORD', $INIParseResult['DatabasePassword']);
define('DB_PLUGINSDATABASENAME', $INIParseResult['PluginDatabaseName']);

// autoloader dir ... autoloadir hon hon hon
define('COMPOSER_AUTOLOADIR', join(DIRECTORY_SEPARATOR, array('D:\home\site\Composer', 'vendor', 'autoload.php')));
define('CACHE_DIR', 'D:\home\site\Static Cache');

function GetTwigOptions()
{
	require_once 'Environment Interfaces/Cache.php';
	return array('cache' => Cache::GetCacheDir() . DIRECTORY_SEPARATOR . CacheType::Preprocessed, 'auto_reload' => true);
}

function RelativeToAbsolute($RelativePath)
{
	return $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $RelativePath;
}

function SetRedirect($RedirectAddress = '/')
{
	header("Location: $RedirectAddress");
}

function SetRefresh($RedirectAddress = '/', $Timeout = 1)
{
	header("Refresh: $Timeout; URL=$RedirectAddress");
}