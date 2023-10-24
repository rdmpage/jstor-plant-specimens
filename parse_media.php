<?php

// Parse GBIF media file to get barcodes from image details

$headings = array();

$row_count = 0;

$filename = "0005866-230918134249559/multimedia.txt";

$filename = "0008890-230918134249559/multimedia.txt";


$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$line = trim(fgets($file_handle));
		
	$row = explode("\t",$line);
	
	$go = is_array($row) && count($row) > 1;
	
	if ($go)
	{
		if ($row_count == 0)
		{
			$headings = $row;		
		}
		else
		{
			$obj = new stdclass;
		
			foreach ($row as $k => $v)
			{
				if ($v != '')
				{
					$obj->{$headings[$k]} = $v;
				}
			}
		
			//print_r($obj);	
			
			$record = null;
			
			$go = true;
			
			// taxonomic filtering?
			
			if ($go)
			{
				$record = new stdclass;
								
				// defaults
				if (isset($obj->gbifID))
				{
					$record->gbif = $obj->gbifID;
				}
				
				// NMNH
				if (isset($obj->description))
				{
					if (preg_match('/Barcode\s+(\d+)/', $obj->description, $m))
					{
						$record->barcode = 'US' . $m[1];
					}
				}
			
				// NMNH
				if (isset($obj->title))
				{
					if (preg_match('/^(\d+)(_\d+)?\.[a-z]+/', $obj->title, $m))
					{
						if (!isset($record->barcode))
						{
							$record->barcode = 'US' . $m[1];
						}
					}
				}
				
				// MO				
				if (isset($obj->identifier) && preg_match('/\/(MO-\d+)/', $obj->identifier, $m))
				{
					$record->barcode = $m[1];
				}				
				
				if (isset($record->barcode) && isset($record->gbif))
				{
					echo 'UPDATE barcode SET barcode="' . $record->barcode . '" WHERE gbif=' . $record->gbif . ';' . "\n";
				}

			}
		}
	}	
	$row_count++;	
	
	if ($row_count > 10)
	{
		//exit();
	}
	
}	

