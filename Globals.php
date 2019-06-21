<?php

/* header_remove('Cache-Control');
header_remove('Pragma');
header_remove('Expires');
header('Cache-Control: max-age=10'); */

pg_pconnect(getenv('POSTGRESQLCONNSTR_DATABASE'));

// autoloader dir ... autoloadir hon hon hon
define('COMPOSER_AUTOLOADIR', join(DIRECTORY_SEPARATOR, array('D:\home\site\Composer', 'vendor', 'autoload.php')));
define('CACHE_DIR', 'D:\home\site\Static Cache');

final class WebURI
{
	const Home = '/';
	const Upload = '/upload';
	const Search = '/search';
	const Problem = '/problem';
	const Publication = '/publication';
	const Profile = '/profile';
	const GoblinIdLogin = 'https://orcid.org/oauth/authorize';
	const GoblinIdLoginReturn = '/login';
	const GoblinIdExchangeToken = 'https://orcid.org/oauth/token';
	const Avatar = '/avatar';
	const Logout = '/login?logout=1';
	const Explore = '/search';
	const FAQ = '/faq';
	const Moar = '/about';
}

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