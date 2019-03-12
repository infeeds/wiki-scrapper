<?php
function paedia($keyword){
	$wiki = '';

  //checking if keyword valid
	if(preg_match('/^[\w]$/', $keyword)){

    //requesting via wikipedia REST API
    $url = 'http://en.wikipedia.org/w/api.php?action=parse&page='.$keyword.'&format=json&prop=text&section=0';
		
    //initializing curl
    $ch = curl_init($url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_USERAGENT, 'Infeeds Sniper'); //Infeeds default search engine agent
		$c = curl_exec($ch);

    //decoding received data
    $json = json_decode($c);

    //continue if data available
    if($json !='' && isset($json->{'parse'})){
			$title = $json->{'parse'}->{'title'};
			$content = $json->{'parse'}->{'text'}->{'*'};
			$pattern = '#<p>(.*)</p>#Us';
			if(preg_match($pattern, $content, $matches)){ //ripping only required data
				if($matches[1]!=''){
					$con = preg_replace_callback("/\[[^)]+\]/", function($m){return '';}, $matches[1]);

          //formatting matched data for cleaner preview
					$wiki = '<h2>'.$title.'</h2>'.strip_tags($con).'</p><src>Source: <a href="https://en.wikipedia.org/wiki/'.$keyword.'" target="_blank">Wikipedia</a></src>';
				}
			}
      
    //if wikipedia API returns nothing
		}else{

      //requesting DBPedia REST API; returns XML
			$url = 'http://lookup.dbpedia.org/api/search.asmx/KeywordSearch?QueryString='.urlencode($keyword).'&MaxHits=1';
			
      //filtering received data
      $xml = simplexml_load_file($url);
			$tie = $xml->Result->Label;
			$uri = $xml->Result->URI;
			$body = $xml->Result->Description;
			if($body !=''){
        //formatting requested data for cleaner preview
        $wiki = '<h2>'.$tie.'</h2><p>'.$body.'</p><src>Source: <a href="'.$uri.'" target="_blank">DBpedia</a></src>';	
			}
		}
	}
	return $wiki;
}

echo paedia("batman");
?>
