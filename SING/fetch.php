<?php

// Harvest from SING

error_reporting(E_ALL);

$cache_dir = dirname(__FILE__) . '/cache';

$count = 1;

//----------------------------------------------------------------------------------------
function get($url, $user_agent='', $content_type = '')
{	
	$data = null;

	$opts = array(
	  CURLOPT_URL =>$url,
	  CURLOPT_FOLLOWLOCATION => TRUE,
	  CURLOPT_RETURNTRANSFER => TRUE,
	  
		CURLOPT_SSL_VERIFYHOST=> FALSE,
		CURLOPT_SSL_VERIFYPEER=> FALSE,
	  
	);

	if ($content_type != '')
	{
		
		$opts[CURLOPT_HTTPHEADER] = array(
			"Accept: " . $content_type, 
			"User-agent: Mozilla/5.0 (iPad; U; CPU OS 3_2_1 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Mobile/7B405" 
		);
		
	}
	
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$info = curl_getinfo($ch); 
	
	$http_code = $info['http_code'];
	
	curl_close($ch);
	
	return $data;
}

//----------------------------------------------------------------------------------------


for ($i = 1892; $i < 2835; $i++)
{
	$url = 'https://herbaria.plants.ox.ac.uk/bol/sing/ExploreData/ExtData?dataType=SpecimenRecordView&limit=100&page=' . $i;
	
	echo $url . "\n";
	
	$filename = $cache_dir . '/' . $i . '.json';
	
	if (!file_exists($filename))
	{	
		$json = get($url ,'', '*/*');
		file_put_contents($filename, $json);
		
		// Give server a break every 10 items
		if (($count++ % 5) == 0)
		{
			$rand = rand(1000000, 3000000);
			echo "\n-- ...sleeping for " . round(($rand / 1000000),2) . ' seconds' . "\n\n";
			usleep($rand);
		}	
		
	}

}

?>
