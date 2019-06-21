<?php
final class Tags
{
	static function GetByPublicationAndAugment($Publication)
	{
		$Query = pg_query_params(
			'SELECT tags.tag FROM publication_tags, tags WHERE publication_tags.publication = $1 AND publication_tags.tag = tags.id',
			array($Publication->Id)
		);
		
		$Results = pg_fetch_all($Query, PGSQL_NUM);
		if (!$Results)
		{
			return;
		}
		
		foreach ($Results as $Result)
		{
			$Publication->Tags[] = new Tag($Result[0]);
		}
	}
}

final class Tag
{
	public function __construct($Label)
	{
		$this->Label = $Label;
	}
	
	public $Label;
}