<?php
class Hypothesis
{
	public function __construct($Record)
	{
		$this->HypothesisId = $Record['HypothesisId'];
		$this->Title = $Record['Title'];
		$this->Summary = $Record['Summary'];
		$this->DOI = $Record['DOI'];
		$this->DateAdded = $Record['DateAdded'];
		$this->DocumentName = $Record['DocumentName'];
		
		if (isset($Record['ProblemDescription']))
		{
			$this->Problem = new Problem($Record);
		}
	}

	public $HypothesisId;
	public $Title;
	public $Summary;
	public $DOI;
	public $DateAdded;
	public $DocumentName;
	
	public $Problem;
}
?>