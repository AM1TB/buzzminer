<?php require_once 'db_connection.php'; ?>
<?php require_once 'functions.php'; ?>
<!DOCTYPE HTML>
<!--
	BuzzMiner
	abhinavagrawal.in | gROOT
-->
<html>
	<head>
		<title>BuzzMiner</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<!--[if lte IE 8]><script src="assets/js/ie/html5shiv.js"></script><![endif]-->
		<link rel="stylesheet" href="assets/css/main.css" />
		<link rel="stylesheet" href="assets/css/animate.css" />

		<!--[if lte IE 8]><link rel="stylesheet" href="assets/css/ie8.css" /><![endif]-->
		<script src="Chart.js"></script>
		<script src="assets/js/wow.min.js"></script>
		<script>
              new WOW().init();
        </script>
	</head>
	<body class="landing">
		<div id="page-wrapper">

			<!-- Header -->
				<header id="header" class="alt">
					<h1><a href="index.php">BuzzMiner</a></h1>
					<nav id="nav">
						<ul>
							<li><a href="index.php">General</a></li>
							<li><a href="geo.php">Geographical</a></li>
						</ul>
					</nav>
				</header>

			<!-- Banner -->
				<section id="banner">
					<h2>BuzzMiner</h2>
					<p>Twitter Sentiment-Analysis tool</p>
					<ul class="actions">
						<li><a href="#abi" class="button special">Begin</a></li>
					</ul>
				</section>

			<!-- Main -->
				<section id="main" class="container wow" data-wow-duration="5s">
				<nav id="abi"></nav>
					<section class="box special">
						<div class="box">
						<h3>Type your BUZZword and select how many Tweets to analyse:</h3>
						<hr>
						<form method="post" action="#" class="wow rollIn">
							<div class="row uniform 50%">
								<div class="8u 12u(mobilep)">
									<label for="n">Keyword: </label>
									<input type="text" name="q" placeholder="Keyword" required/>
								</div>
								<div class="4u 12u(mobilep)">
									<label>Number of Tweets:</label>
									<select name="num_tweets">
										<option value="20" selected>5</option>
										<option value="40">10</option>
										<option value="60">15</option>
									</select>
								</div>
							</div>
							<div class="row uniform 50%">
								<div class="6u 12u(narrower)">
									<input type="radio" id="classifier1" name="classifier" value="custom" checked>
									<label for="classifier1">Custom Naive Bayes (positive | negative)</label>
								</div>
								<div class="6u 12u(narrower)">
									<input type="radio" id="classifier2" name="classifier" value="alchemy">
									<label for="classifier2">Alchemy (positive | negative | neutral)</label>
								</div>
							</div>
							<div class="row uniform">
								<div class="12u">
									<ul class="actions align-center">
										<li><button type="submit" name="submit" class="button special fit">Analyze</button></li>
									</ul>
								</div>
							</div>
						</form>
						<?php 
							if(isset($_POST['submit'])){
								echo '<h3>Analyzing Keyword :<code style="animated infinite bounce">' . $_POST['q'] . '</code></h3>';
							}
						?>
						<br>
						<?php if(isset($_POST['submit'])){ ?>
						<div class="box alt">
							<div class="row no-collapse 50% uniform">
								<div class="12u"><canvas id="chart-area1"></canvas></div>
								<div class="6u"><canvas id="chart-area2"></canvas></div>
							</div>
						</div>
						<?php } ?>
						<div id="box">
							<?php
								if(isset($_POST['submit'])){

									ini_set('display_errors', 0);
									require_once 'alchemyapi.php';
									require_once 'Sentiment1.php';
									$custom_sent = new Sentiment1();
									$custom_sent -> train(19000); 
									$alchemyapi = new AlchemyAPI();

									/*use TwitterAPIExchange for handling calls */
									
									require_once('TwitterAPIExchange.php');

									/** Set access tokens here - see: https://dev.twitter.com/apps/ **/
									$oauth_file = file_get_contents("oauthkeys.txt");
									$oauth_arr = explode("\n", $oauth_file);

									$settings = array(
										'oauth_access_token' => $oauth_arr[0],
										'oauth_access_token_secret' => $oauth_arr[1],
										'consumer_key' => $oauth_arr[2],
										'consumer_secret' => $oauth_arr[3]
										);

									//echo $_POST['classifier'];

									if ( isset($_POST['q'])){
										//$query = 'monday';
										$url = 'https://api.twitter.com/1.1/search/tweets.json';
										$x1 = " ";
										$y1 = "%20";
										$z1 = $_POST['q'];
										$qry= str_replace($x1,$y1,$z1);
										$getfield ='?lang=en&count=' . $_POST['num_tweets'] . '&q=' . $qry . '-filter:retweets';
										//$getfield = '?q=manipal';
										$requestMethod = 'GET';
										$twitter = new TwitterAPIExchange($settings);
										//var_dump($twitter);
										$tweets = json_decode($twitter->setGetfield($getfield)
											->buildOauth($url, $requestMethod)
											->performRequest());
										//var_dump($tweets);

										$pos_count=0;
										$neg_count=0;
										$neu_count=0;
										$cust_pos=0;
										$cust_neg=0;

										
										foreach ($tweets -> statuses as $t) {
											// foreach ($tweet as $t){
											//calculate the sentiment of each tweet
											if ($t -> text != ''){
												if($_POST['classifier']=='alchemy'){
													$response = $alchemyapi -> sentiment('text', $t -> text , null);				
													display_tweet($t, $response);
													// store_tweet_db($_POST['q'], $t->text, $response['docSentiment']['type'],$response);

													if ($response['docSentiment']['type']=='neutral'){
														$neu_count++;
													}
													if ($response['docSentiment']['type']=='positive'){
														$pos_count++;
													}
													if ($response['docSentiment']['type']=='negative'){
														$neg_count++;
													}
												} 
												
												else if($_POST['classifier']=='custom'){
													$sentiment = $custom_sent ->classify($t->text);
													display_custom($t,$sentiment);
													if ($sentiment=='pos'){
														$cust_pos++;
													} else {
														$cust_neg++;
													}
												}																			
											} 
										}
									}
									/*if($_POST['classifier']=='alchemy'){
										echo 'positives: ' . $pos_count . '<br>';
										echo 'negatives: ' . $neg_count . '<br>';
										echo 'neutrals: ' . $neu_count . '<br>';
									}*/
										// }	
								}
							?>
						</div>
						
					</div>
					</section>

				</section>

			<!-- Footer -->
				<footer id="footer">
					<ul class="copyright">
						<li>  Developers : <a href="http://abhinavagrawal.in">gROOT</a> and Amit</li>
					</ul>
				</footer>

		</div>

		<!-- Scripts -->
		<script>

			var doughnutData = [
			{
				value: <?php echo ($_POST['classifier']=='alchemy' ? $neg_count : $cust_neg); ?>,
				color:"#F7464A",
				highlight: "#FF5A5E",
				label: "Negative"
			},
			{
				value: <?php echo ($_POST['classifier']=='alchemy' ? $pos_count : $cust_pos); ?>,
				color: "#46BFBD",
				highlight: "#5AD3D1",
				label: "Positive"
			},
			{
				value: <?php echo $neu_count; ?>,
				color: "#949FB1",
				highlight: "#A8B3C5",
				label: "Neutral"
			}


			];

			

			//bar
			var barrData = [
			{
				value: <?php echo ($_POST['classifier']=='alchemy' ? $neg_count : $cust_neg); ?>,
				color:"#F7464A",
				highlight: "#FF5A5E",
				label: "Negative"
			},
			{
				value: <?php echo ($_POST['classifier']=='alchemy' ? $pos_count : $cust_pos); ?>,
				color: "#46BFBD",
				highlight: "#5AD3D1",
				label: "Positive"
			},
			{
				value: <?php echo $neu_count; ?>,
				color: "#949FB1",
				highlight: "#A8B3C5",
				label: "Neutral"
			}


			];


			window.onload = function(){
				var ctx = document.getElementById("chart-area1").getContext("2d");
				window.myDoughnut = new Chart(ctx).Doughnut(doughnutData, {responsive : true});
				var barr = document.getElementById('chart-area2').getContext('2d');
			 	window.myBar=new Chart(barr).Line(barrData, {responsive : true});
			};

			


		</script>
			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/jquery.dropotron.min.js"></script>
			<script src="assets/js/jquery.scrollgress.min.js"></script>
			<script src="assets/js/skel.min.js"></script>
			<script src="assets/js/util.js"></script>
			<!--[if lte IE 8]><script src="assets/js/ie/respond.min.js"></script><![endif]-->
			<script src="assets/js/main.js"></script>

	</body>
</html>