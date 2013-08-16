<?php

define('TIMESPAN', 60*60*5);
define('SOURCEURL', "http://www.subito.it/annunci-lazio/vendita/offerte-lavoro/");
define('BASEURL', "http://www.subito.it/");

$keywords = array("php", "programmatore", "ruby", "rails", "ror", "html", "css", "promoter");




$doc = new DOMDocument();
$previousSetting = libxml_use_internal_errors(true);// the previuos value of libxml_use_internal_errors
$content = preg_replace('/\<br( )*\/>/', " ", file_get_contents(SOURCEURL));
$doc->loadHTML($content);
libxml_use_internal_errors($previousSetting); // set the initial value of libxml_use_internal_errors

$xpath = new DOMXpath($doc);
$ads = $xpath->query("//*/ul[@class='list']/li");

$keys = array('date', 'descr');

foreach ($ads as $ad) {
	$isInteresting = false;
	$adDivs = $ad->getElementsByTagName("div");
	foreach ($adDivs as $adDiv) {
		if($adDiv->hasAttribute('class') && $adDiv->getAttribute('class') == $keys[0]){
			$date = $adDiv->nodeValue;
			$today = date("d M Y");
			$yesterday = date("d M Y", strtotime("-1 day"));
			$pattern = array('/Oggi/i', '/Ieri/i');
			$repl = array($today, $yesterday);

			$dateFormatted = preg_replace($pattern, $repl, $date);
		}
		
		if($adDiv->hasAttribute('class') && $adDiv->getAttribute('class') == $keys[1]){
			$description = trim($adDiv->nodeValue);
			// echo $description, PHP_EOL;
			foreach ($keywords as $keyword) {
				if(preg_match("/$keyword/i", $description)){
					$isInteresting = true;
					$links = $adDiv->getElementsByTagName('a');
					$linksArr = array();
					foreach($links as $link){
						if($link->hasAttribute('href')){
							$linkRaw = $link->getAttribute('href');
							if(strpos($linkRaw, BASEURL) === 0){
								$linksArr[] =  $link->getAttribute('href');
							}else{
								$linksArr[] = BASEURL . $link->getAttribute('href');
							}
							
						}
					}
					$adLinks = implode(", ", $linksArr);
					break;
				}
			}
			
			// echo $isInteresting ? "interesting" : "bored";
			// echo PHP_EOL;
		}
	}
	// echo $dateFormatted, " "; 
	// echo time() - strtotime($dateFormatted);
	// echo $isInteresting ? "interesting" : "bored";
	// echo PHP_EOL;

	$now = time();
	if($now - strtotime($dateFormatted) < TIMESPAN && $isInteresting){
		 // echo ": ", $description, " ", $adLinks, PHP_EOL;
		require 'class.phpmailer.php';
		 if(mail("veontomo@gmail.com", "annuncio di lavoro interessante", $description ."\n". $adLinks)){
		 	echo "mail is sent";
		 }else{
		 	echo "mail is not sent";
		 };

	}
	// else{
	// 	// echo "nothing interesting", PHP_EOL;
	// }
	}



?>


