<?php
class Problem
{
	public function __construct($Record)
	{
		$this->ProblemId = $Record['ProblemId'];
		$this->ProblemDescription = $Record['ProblemDescription'];
	}

	public $ProblemId;
	public $ProblemDescription;
}
?>