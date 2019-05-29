<?php
final class CacheType
{
	const Preprocessed = 'Preprocessed';
	const Users = 'Users';
}

final class Cache
{
	static public function GetCacheDir()
	{
		return CACHE_DIR;
	}

	static public function UpdateCacheEntry($CacheType, $EntryID, $EntryData)
	{
		$DirectoryName = Cache::GetCacheDir() . DIRECTORY_SEPARATOR . $CacheType;
		if (!is_dir($DirectoryName))
		{
			mkdir($DirectoryName);
		}
		
		$FileName = $DirectoryName . DIRECTORY_SEPARATOR . $EntryID;
		file_put_contents($FileName, $EntryData, LOCK_EX);
	}

	static public function GetCacheEntry($CacheType, $EntryID)
	{
		return @file_get_contents(Cache::GetCacheDir() . DIRECTORY_SEPARATOR . $CacheType . DIRECTORY_SEPARATOR . $EntryID);
	}

	static public function DeleteCache($CacheType, $EntryID)
	{
		$CacheDirectory = Cache::GetCacheDir() . DIRECTORY_SEPARATOR . $CacheType . DIRECTORY_SEPARATOR . $EntryID;
		unlink($CacheDirectory);
	}
}
?>