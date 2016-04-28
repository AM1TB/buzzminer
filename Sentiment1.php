<?php


class Sentiment1 {


	private $dictionary = array();
	private $classes = array('pos','neg');
	private $classWordCount = array('pos'=>0,'neg'=>0); //w.c. per class
	private $classTweetCount = array('pos'=>0,'neg'=>0); 
	private $wordCount = 0;
	private $tweetCount = 0;
	private $priors = array('pos' => 0.5,'neg' => 0.5);


	public function displayFields() {
		//this function is purely for testing purposes.
		//print_r($this->classWordCount);
		//print_r($this->dictionary);
		print_r($this-> classWordCount);
		echo "<br>";
		print_r($this-> wordCount);
		echo "<br>";
		print($this-> tweetCount);
		echo "<br>";
		print_r($this -> classTweetCount);
		echo "<br>";
		print_r($this -> dictionary);

	}

	public function tokenize($tweet){
		$tweet = strtolower($tweet);
		//remove usernames from the tweet
		$tweet = preg_replace("/@([A-Za-z0-9_]{1,15})/", ' ', $tweet);
		//remove hashtags
		$tweet = preg_replace("/(#\w+)/", ' ', $tweet);
		$tweet = trim($tweet);
		$tokens = preg_split("/[\s,]*\\\"([^\\\"]+)\\\"[\s,]*|" . "[\s,]*'([^']+)'[\s,]*|" . "[\s,]+/", $tweet);
		//$tokens = explode(" \n\t", $tweet);
		return $tokens;	
	}

	/* ---------- May have to round the percentages...*/
	//takes a tweet as input and returns its classification
	public function classify($tweet){
		//create array of words in tweet
		$tokens = $this -> tokenize($tweet);
		$totalCount = 0;
		//create array to store counts for each class (pos,neg,neu)
		$counts = array();

		//loop through each class
		foreach ($this -> classes as $class){

			//set default for class = 1
			$counts[$class] = 1;

			//loop through each word in tweet and check if they're in the dictionary
			foreach ($tokens as $token){
				if (isset($this -> dictionary[$token][$class])){
					$count = $this -> dictionary[$token][$class];
				} else {
					$count = 0; //word is not in dictionary
				}
				//keep multiplicative tally for the counts per class, +1 for Laplace smoothing
				$counts[$class] *= ($count + 1);
			}
			//multiply the count for the class by its prior probability
			$counts[$class] = $counts[$class] * $this -> priors[$class];
		}
		
		foreach ($this -> classes as $class) {
			$totalCount += $counts[$class];
		}
		//give each class count as a percentage
		foreach ($this -> classes as $class){
			$counts[$class] = $counts[$class]/$totalCount;
		}
		//sort the counts array so the class with the highest count is first
		arsort($counts);
		//return the first key in the sorted counts array (corresponds with the class)
		$tweetClass = key($counts);
		return $tweetClass;

	}


	//$limit = 5000;
	function train ($limit=0) {
		//open database for positive and negative training data
		include 'db_connection.php';
		require_once 'functions.php';

		//get all negative data
		$queryneg = "SELECT * FROM negtrain";
		$resultneg = mysqli_query($connection,$queryneg);
		confirm_query($resultneg);
		//return $resultneg;

		$class = 'neg';
		$i = 0;
		while($negTrainData = mysqli_fetch_assoc($resultneg)) {
			//if limit is set (for testing purposes) break when limit breached
			if ($i > $limit && $limit >0){
				break;
			}
			//print($negTrainData['content']);
			$i++;
			$this -> tweetCount++;
			$this -> classTweetCount[$class]++;
			$tokens = $this -> tokenize($negTrainData['content']);
			//print_r($tokens);
			foreach ($tokens as $token) {
				if(!isset($this -> dictionary[$token][$class])){
					$this -> dictionary[$token][$class]=0;
				}
				$this -> dictionary[$token][$class]++;
				$this -> classWordCount[$class]++;
				$this -> wordCount++;

			}
			//print($i . "\n");
		}
		mysqli_free_result($resultneg);

		//get all positive data
		$query = "SELECT * FROM postrain";
		$resultpos = mysqli_query($connection, $query);
		confirm_query($resultpos);
		//return $resultneg;
		$class = 'pos';
		$j=0;
		while($posTrainData = mysqli_fetch_assoc($resultpos)) {
			//if limit is set (for testing purposes) break when limit breached
			if ($j > $limit && $limit >0){
				break;
			}
			$j++;
			$this -> tweetCount++;
			$this -> classTweetCount[$class]++;
			$tokens = $this -> tokenize($posTrainData['content']);
			foreach ($tokens as $token) {
				if(!isset($this -> dictionary[$token][$class])){
					$this -> dictionary[$token][$class]=0;
				}
				$this -> dictionary[$token][$class]++;
				$this -> classWordCount[$class]++;
				$this -> wordCount++;

			}
		}
		mysqli_free_result($resultpos);

		if (isset($connection)) {
	 		mysqli_close($connection);
		}


	} //end of train()
}

?>