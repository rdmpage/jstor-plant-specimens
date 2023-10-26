<?php

// Parse SING data

error_reporting(E_ALL);


$basedir = dirname(__FILE__) . '/cache';

$files = scandir($basedir);

// debugging
//$files=array('1.json');


foreach ($files as $filename)
{
	if (preg_match('/.json$/', $filename, $m))
	{	

		$json = file_get_contents($basedir . '/' . $filename);
		
		$obj = json_decode($json);

		//print_r($obj);
		
		foreach ($obj->data as $item)
		{
			$kv = array();
			
			if (isset($item->Id))
			{
				$kv['id'] = 'https://herbaria.plants.ox.ac.uk/bol/sing/record/details/' . $item->Id;			
				$kv['id'] = $item->Id;			
			}

			if (isset($item->Herbarium) && isset($item->Barcode))
			{
				$kv['barcode'] = $item->Herbarium . $item->Barcode;
			}

			if (isset($item->Species))
			{
				$kv['canonical'] = $item->Species;
			}
			
			if (count($kv) == 3)
			{
				echo 'REPLACE INTO barcode(id, barcode, canonical) VALUES("' . $kv['id'] . '", "' . $kv['barcode'] . '", "' . $kv['canonical'] . '");' . "\n";
			}
					
		
		}
	}
}

?>
