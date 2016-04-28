<?php
	function mysql_esc($string){
		global $connection;
		$escaped = mysqli_real_escape_string($connection,$string);
		return $escaped;			
	}

	function confirm_query($resultset) {
		if (!$resultset){
			die("Database query failed.");
		}
	}


	function display_tweet($t, $response){
		//<div class="4u"><span class="image fit"><img src="images/pic04.jpg" alt="" /></span></div>
		
		if($response['docSentiment']['type']=="positive")
			$res="Positive";
		else if($response['docSentiment']['type']=="negative")
			$res="Negative";
		else if($response['docSentiment']['type']=="neutral")
			$res="Neutral";

		echo '<hr><div class="row wow slideInLeft">';
			echo '<div class="1u">';
				echo '<span class="image fit">';
					echo '<img src="' . $t -> user -> profile_image_url .'" alt="profile image" />';
				echo '</span>'; 
			echo '</div>'; //end image
			echo '<div class="11u">';
				echo '<h4> Sentiment: <code>'. $response['docSentiment']['type'] . '</code></h4>';
				echo '<h5>' . $t -> user -> screen_name . '</h5>';
				echo '<p>' . $t -> text . '</p>';
			echo '</div>'; //close desc
		echo '</div>';	
			//$response['docSentiment']['type']
		//echo $t-> id . '<br>';		
	    //echo 'score: '. $response['docSentiment']['score'];
	   // return $display_string;
	}

	function display_custom($t, $response){

		if($response=="pos")
			$res="Positive";
		else if($response=="neg")
			$res="Negative";

		echo '<hr><div class="row wow slideInLeft">';
			echo '<div class="2u">';
				echo '<span class="image fit">';
					echo '<img src="' . $t -> user -> profile_image_url .'" alt="profile image" />';
				echo '</span>'; 
			echo '</div>'; //end image
			echo '<div class="10u">';
				echo '<h4> Sentiment: <code>'. $res . '</code></h4>';
				echo '<h5>' . $t -> user -> screen_name . '</h5>';
				echo '<p>' . $t -> text . '</p>';
			echo '</div>'; //close desc
		echo '</div>';		
	    //echo 'score: '. $response['docSentiment']['score'];
	   // return $display_string;
	}

	function store_tweet_db($db_search,$db_content,$db_sent,$response){
		global $connection;
		$db_search = mysql_esc($db_search);
		$db_content = mysql_esc($db_content);
		$db_sent = mysql_esc($db_sent);
		$query = "INSERT INTO tweets (";
		$query .= "search_term,content,sentiment,sentistrength";
		$query .= ") VALUES (";
		$query .= "'{$db_search}','{$db_content}','{$db_sent}','{$response['docSentiment']['score']}'";
		$query .= ")";		
		$db_result = mysqli_query($connection,$query);
		confirm_query($db_result);
	}



/*global $connection;
	$query = "INSERT INTO tweets (";
	$query .= "search_term,content,sentiment,sentistrength";
	$query .= ") VALUES (";
	$query .= "'{$db_search}','{$db_content}','{$db_sent}','{$response['docSentiment']['score']}'";
	$query .= ")";		
	$db_result = mysqli_query($connection,$query);
	confirm_query($db_result);	*/

?>