<?php

// Parse GBIF occurrence file to get GBIF ids and related information

error_reporting(E_ALL);

require_once (dirname(__FILE__) . '/taxon_name_parser.php');

$headings = array();

$row_count = 0;

$filename = "junk/0006337-230918134249559/occurrence.txt";
$filename = "junk/0005866-230918134249559/occurrence.txt";

$filename = "0011098-230918134249559/occurrence.txt"; // BM
//$filename = "0005866-230918134249559/occurrence.txt"; // NMNH
//$filename = "0012338-230918134249559/occurrence.txt"; // NMNH

$filename = "0012346-230918134249559/occurrence.txt"; // P
$filename = "0020513-231002084531237/occurrence.txt"; // CAS
$filename = "0021070-231002084531237/occurrence.txt"; // E

$filename = "0022667-231002084531237/occurrence.txt"; // GOET

$filename = "0008890-230918134249559/occurrence.txt"; // MO

$filename = "0023097-231002084531237/occurrence.txt"; // M


$pp = new Parser();

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
		
			if (0)
			{
				print_r($obj);	
				exit();
			}
			
			$record = null;
			
			$go = true;
			
			// taxonomic filtering?
			$go = ($obj->kingdom == 'Plantae');
			
			if ($go)
			{
				$record = new stdclass;
				
				// defaults
				if (isset($obj->occurrenceID))
				{
					$record->id = $obj->occurrenceID;
				}
				
				if (!isset($record->id))
				{
					$record->id =  'gbif:' . $obj->gbifID;
				}

				if (isset($obj->gbifID))
				{
					$record->gbif = $obj->gbifID;
				}

				if (isset($obj->scientificName))
				{
					$record->scientificName = $obj->scientificName;
					
					$r = $pp->parse($record->scientificName );
					if ($r->scientificName->parsed)
					{					
						$record->scientificName = $r->scientificName->canonical;
					}
					
				}

				if (isset($obj->species))
				{
					$record->canonical = $obj->species;
				}				
			
				switch ($obj->datasetKey)
				{
					// M Staatliche Naturwissenschaftliche Sammlungen Bayerns					
					case '7b9a08ea-f762-11e1-a439-00145eb45e9a': 
						if (isset($obj->catalogNumber))
						{
							if (preg_match('/M-(\d+)/', $obj->catalogNumber, $m))
							{
								$record->barcode = 'M' . $m[1];
							}					
						}
						break;
						
					// BM
					case '7e380070-f762-11e1-a439-00145eb45e9a': 
						if (isset($obj->catalogNumber))
						{
							if (preg_match('/^BM\d+/', $obj->catalogNumber))
							{
								$record->barcode = $obj->catalogNumber;
							}					
						}
						break;
						
					// CAS
					case 'f934f8e2-32ca-46a7-b2f8-b032a4740454':
						if (isset($obj->catalogNumber))
						{
							$record->barcode = 'CAS' . $obj->catalogNumber;
						}
						break;
						
					case 'bf2a4bf0-5f31-11de-b67e-b8a03c50a862':
						if (isset($obj->catalogNumber))
						{
							$record->barcode = $obj->catalogNumber;
						}
						break;
						
					// GOET
					case 'b89a7f02-021d-4e7a-b19f-575d10578a6d':
						if (isset($obj->catalogNumber))
						{
							if (preg_match('/(^G[^\s]+)\s\//', $obj->catalogNumber, $m))
							{
								$record->barcode = $m[1];
							}
						}
						break;
												
					// Kew
					case 'cd6e21c8-9e8a-493a-8a76-fbf7862069e5': 
						if (isset($obj->catalogNumber))
						{
							if (preg_match('/^K\d+/', $obj->catalogNumber))
							{
								$record->barcode = $obj->catalogNumber;
							}					
						}
						break;
						
					// M
					case '7b9a08ea-f762-11e1-a439-00145eb45e9a':
						if (isset($obj->catalogNumber))
						{
							if (preg_match('/(^M[^\s]+)\s\//', $obj->catalogNumber, $m))
							{
								$record->barcode = $m[1];
							}
						}
						break;
						
						
					// P 
					case 'b5cdf794-8fa4-4a85-8b26-755d087bf531':
						if (isset($obj->catalogNumber))
						{
							if (preg_match('/^P\d+/', $obj->catalogNumber))
							{
								$record->barcode = $obj->catalogNumber;
							}					
						}
						break;
						
						
										
					default:						
						break;
				
				}

				//print_r($record);
				
			
				$keys = array();
				$values = array();

				foreach ($record as $k => $v)
				{
					$keys[] = '"' . $k . '"'; // must be double quotes

					if (is_array($v))
					{
						$values[] = "'" . str_replace("'", "''", json_encode(array_values($v))) . "'";
					}
					elseif(is_object($v))
					{
						$values[] = "'" . str_replace("'", "''", json_encode($v)) . "'";
					}
					elseif (preg_match('/^POINT/', $v))
					{
						$values[] = "ST_GeomFromText('" . $v . "', 4326)";
					}
					else
					{				
						$values[] = "'" . str_replace("'", "''", $v) . "'";
					}					
				}

				//$sql = 'INSERT INTO barcode (' . join(",", $keys) . ') VALUES (' . join(",", $values) . ') ON CONFLICT DO NOTHING;';					
				$sql = 'REPLACE INTO barcode (' . join(",", $keys) . ') VALUES (' . join(",", $values) . ');';					
				$sql .= "\n";
				echo $sql;
			}
		}
	}	
	$row_count++;	
	
	if ($row_count > 10)
	{
		//exit();
	}
	
}	

