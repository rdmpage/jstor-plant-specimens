<?php

// Parse GBIF downlaod to get GBIF ids

$headings = array();


$filename = "0009526-230918134249559/occurrence.txt"; // Geneva
$filename = "0024911-231002084531237/occurrence.txt"; // Geneva DC

$row_count = 0;

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
			
			//echo 'UPDATE barcode SET gbif=' . $obj->gbifID . ' WHERE id="' . $obj->occurrenceID . '";' . "\n";	
			echo 'UPDATE barcode SET gbif=' . $obj->gbifID . ' WHERE id="' . $obj->catalogNumber . '";' . "\n";	
			
			/*
			if (isset($obj->species))
			{
				echo 'UPDATE barcode SET canonical="' . $obj->species . '" WHERE id="' . $obj->occurrenceID . '";' . "\n";	
			}
			if (isset($obj->scientificName))
			{
				echo 'UPDATE barcode SET scientificName="' . $obj->scientificName . '" WHERE id="' . $obj->occurrenceID . '";' . "\n";	
			}
			*/
			
			
		}
	}	
	$row_count++;	
	
	if ($row_count > 10)
	{
		//exit();
	}
	
}	

