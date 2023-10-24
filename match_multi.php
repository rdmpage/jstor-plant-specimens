<?php

// Match plant barcodes using Material Examined

error_reporting(E_ALL);

require_once (dirname(__FILE__) . '/taxon_name_parser.php');

$pdo = new PDO('sqlite:jstor.db');

//----------------------------------------------------------------------------------------
function same_name($names1, $names2)
{
	$same = false;
	
	$n1 = count($names1);
	$n2 = count($names2);
	
	echo "-- " . json_encode($names1) . " " . json_encode($names2) . "\n";
	
	
	for ($i = 0; $i < $n1; $i++)
	{
		for ($j = 0; $j < $n2; $j++)
		{
			// are names the same?
			if (!$same)
			{
				if (strcmp($names1[$i], $names2[$j]) == 0)
				{
					$same = true;
				}
			}
			
			// species/subspecies names, maybe with different gender?
			if (!$same)
			{
				$parts_query  = explode(' ', $names1[$i]);
				$parts_target = explode(' ', $names2[$j]);
			
				if (count($parts_query) >= 2 && count($parts_target) >= 2)
				{
					// species
					if (!$same)
					{
						//echo "-- " . stem_epithet($parts_query[1]) . ' =? ' . stem_epithet($parts_target[1]) . "\n";
					
						// are stemmed epithets the same?
						if (strcmp(stem_epithet($parts_query[1]), stem_epithet($parts_target[1])) == 0)
						{
							$same = true;
						}
					}
				
					// infraspecies
					if (!$same)
					{
						if (count($parts_query) == 3 && count($parts_target)== 3)
						{
							// are stemmed epithets the same?
							if (strcmp(stem_epithet($parts_query[2]), stem_epithet($parts_target[2])) == 0)
							{
								$same = true;
							}
						}
					}	
					
					// species and infraspecies
					if (!$same)
					{
						if (count($parts_query) == 3 && count($parts_target) == 2)
						{
							// are stemmed epithets the same?
							if (strcmp(stem_epithet($parts_query[2]), stem_epithet($parts_target[1])) == 0)
							{
								$same = true;
							}
						}
					}	
					if (!$same)
					{
						if (count($parts_query) == 2 && count($parts_target) == 3)
						{
							// are stemmed epithets the same?
							if (strcmp(stem_epithet($parts_query[1]), stem_epithet($parts_target[2])) == 0)
							{
								$same = true;
							}
						}
					}			
							
							
				}			
			}	
			
			// same genus? (desperate)
			if (!$same)
			{
				$parts_query  = explode(' ', $names1[$i]);
				$parts_target = explode(' ', $names2[$j]);
			
				if (strcmp(stem_epithet($parts_query[0]), stem_epithet($parts_target[0])) == 0)
				{
					$same = true;
				}
			}			
					
		}
	}



	return $same;

}


//----------------------------------------------------------------------------------------
/* 
doi:10.1186/1471-2105-14-16

The stemming (equivalent) in Taxamatch equates 
-a, -is -us, -ys, -es, -um, -as and -os when 
they occur at the end of a species epithet 
(or infraspecies) by changing them all to -a. 
Thus (for example) the epithets “nitidus”, “nitidum”, 
“nitidus” and “nitida” will all be considered 
equivalent following this process.  

To this I've added -se and -sis, -ue and -uis (and more)
*/
function stem_epithet($epithet)
{
	$stem = $epithet;
	$matched = '';
	
	
	// 4
	
    // -atum
    if ($matched == '') {
        if (preg_match('/atum$/', $epithet)) {
            $matched = 'atum';
        }
    }
	
	// 3
	
    // -ata
    if ($matched == '') {
        if (preg_match('/ata$/', $epithet)) {
            $matched = 'ata';
        }
    }	
	
    // -lis
    if ($matched == '') {
        if (preg_match('/lis$/', $epithet)) {
            $matched = 'lis';
        }
    }
	
    // -sis
    if ($matched == '') {
        if (preg_match('/sis$/', $epithet)) {
            $matched = 'sis';
        }
    }
    
    // -uis
    if ($matched == '') {
        if (preg_match('/uis$/', $epithet)) {
            $matched = 'uis';
        }
    }
    
	
	// 2

    // -se
    if ($matched == '') {
        if (preg_match('/se$/', $epithet)) {
            $matched = 'se';
        }
    } 
       
    // -ue
    if ($matched == '') {
        if (preg_match('/ue$/', $epithet)) {
            $matched = 'ue';
        }
    }
    // -is
    if ($matched == '') {
        if (preg_match('/is$/', $epithet)) {
            $matched = 'is';
        }
    }
    // -us
    if ($matched == '') {
        if (preg_match('/us$/', $epithet)) {
            $matched = 'us';
        }
    }
    // -ys
    if ($matched == '') {
        if (preg_match('/ys$/', $epithet)) {
            $matched = 'ys';
        }
    }
    // -es
    if ($matched == '') {
        if (preg_match('/es$/', $epithet)) {
            $matched = 'es';
        }
    }
    // -um
    if ($matched == '') {
        if (preg_match('/um$/', $epithet)) {
            $matched = 'um';
        }
    }
    // -as
    if ($matched == '') {
        if (preg_match('/as$/', $epithet)) {
            $matched = 'as';
        }
    }
    // -os
    if ($matched == '') {
        if (preg_match('/os$/', $epithet)) {
            $matched = 'os';
        }
    }

    // -le
    if ($matched == '') {
        if (preg_match('/le$/', $epithet)) {
            $matched = 'le';
        }
    }

    // stem
    if ($matched != '') {
        $pattern = '/' . $matched . '$/';
        $stem = preg_replace($pattern, 'a', $epithet);
    } else {
        /* Tony's algorithm doesn't handle ii and i */
        // -ii -i 
        if (preg_match('/ii$/', $epithet)) {
            $stem = preg_replace('/ii$/', 'i', $epithet);
        }
    }
    
    //echo "-- stem=$stem\n";

    return $stem;
}

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
function do_sqlite_query($sql)
{
	global $pdo;

	$stmt = $pdo->query($sql);

	$data = array();

	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {

		$item = new stdclass;
		
		$keys = array_keys($row);
	
		foreach ($keys as $k)
		{
			if ($row[$k] != '')
			{
				$item->{$k} = $row[$k];
			}
		}
	
		$data[] = $item;
	}
	
	return $data;	
}


//----------------------------------------------------------------------------------------
// Nothing
$herbaria=array(
'KAW',
'LUKI',
'PRU',
'SALA',
'GUAY',
'MLGU',
'IUK',
'LMU',
'LWKS',
'LWI',
'UTMC',
'V',
'ASC',
'LAGU',
'KAG',
'AHUC',
'ILLS',
'UBT',
'VIT',
'CWU',
'QMEX',
'XALU',
'BRVU',
'DUL',
'BCMEX',
'HUQ',
'MVM',
'KISA',
'YALT',
'EMMA',
'PUR',
'CIB',
'UNSW',
'AH',
'EALA',
'HULE',
'UNEX',
'HEM',
'LSHI',
'TOGO',
'ABFM',
'CDA',
'CHER',
'HUAL',
'INEGI',
'UVAL',
'CLEMS',
'Z',
'CHEP',
'MHES',
'CH',
'CIMI',
'HNMN',
'UPOS',
'WMU',
'HCIB',
'UCAM',
'EBUM',
'HSP',
'STRI',
'UJAT',
'BAF',
'HNBU',
'QUSF',
'MAPR',
'UIU',
'UVIC',
'WNC',
'BAL',
'BUT',
'HA',
'LUBA',
'NCBS',
'EPU',
'KNOX',
'MANK',
'SD');


$herbaria=array(
'HUA',
'SRGH',
'BRIT',
'ENCB',
'IAN',
'MG',
'DUKE',
'ERE',
'FLAS',
'COLO',
'MSB',
'RAB',
'AC',
'NOU',
'ISC',
'CHR',
'LPB',
'HAJB',
'ASU',
'WTU',
'YBI',
'GENT',
'LG',
'USM',
'HO',
'KEP',
'TAN',
'NCU',
'NHT',
'BAB',
'JEPS',
'SPF',
'EAP',
'TEF',
'SAV',
'REG',
'NU',
'BK',
'HOH',
'JAUM',
'MVFA',
'NSK',
'BKL',
'SEV',
'MU',
'BHCB',
'STU',
'PORT',
'INB',
'VALLE',
'XAL',
'LW',
'BCN',
'HEID',
'BRLU',
'MEDEL',
'ETH',
'RENO',
'BLH',
'NA',
'OS',
'PUL',
'GBH',
'CHAPA',
'MOL',
'NMW',
'IBUG',
'LA',
'DAV',
'LEB',
'NEBC',
'AMD',
'SCZ',
'NO',
'PTBG',
'CICY',
'MHU',
'OKLA',
'VAL',
'NT',
'IEB',
'KIP',
'GC',
'UAMIZ',
'YA',
'CAI',
'SBT',
'TUR',
'UBC',
'UCSB',
'MASS',
'LMA',
'QPLS',
'KATH',
'WSY',
'PI',
'SANT',
'TFC',
'CGE',
'CIIDIR',
'ECON',
'JBAG',
'ANSM',
'LSU',
'ABH',
'ID',
'MBM',
'UNM',
'PSO',
'CS',
'ALA',
'DES',
'FMB',
'LOU',
'BKF',
'NEB',
'HNT',
'USFS',
'AIX',
'CHARL',
'FHI',
'UCR',
'NCSC',
'BNRH',
'CHOCO',
'LWS',
'OSH',
'SBBG',
'IND',
'GLM',
'LOJA',
'USCH',
'BJA',
'DNZ',
);

$herbaria = array(
'NBG',
'SGO',
'RM',
'SI',
'LL',
'SP',
'LISU',
'TUB',
'LP',
'NDG',
'LINN',
'SEL',
'H',
'YU',
'GZU',
'CORD',
'PRC',
'LISC',
'WIS',
'EA',
'AD',
'VEN',
'GRA',
'SAM',
'TEX',
'TCD',
'FT',
'KFTA',
'BAA',
'CTES',
'VT',
'MIN',
'CM',
'KW',
'ILL',
'LECB',
'COI',
'WU',
'FR',
'DAO',
'PMA',
'LIL',
'GB',
'QCNE',
'AK',
'OSC',
'QCA',
'MSC',
'CNS',
'ARIZ',
'TBI',
'BC',
'NH',
'DNA'
);

$herbaria=array(
'RB',
'GDC',
'MICH',
'UC',
'MEXU',
'BISH',
'PERTH',
'BRI',
'MA',
'LD',
'COL',
'CANB',
'NSW',
'AMES',
'WAG',
'RSA',
'R',
);

foreach ($herbaria as $herbarium)
{
	$sql = 'SELECT * FROM specimen WHERE herbarium="' . $herbarium . '"';
	$sql .= ' AND type_status IS NOT NULL';
	$sql .= ' AND gbif IS NULL';

	$data = do_sqlite_query($sql);

	$count = 1;

	$failed = array();

	$debug = false;

	foreach ($data as $obj)
	{
		if ($debug)
		{
			print_r($obj);
		}
	
		echo "-- " . $obj->code . "\n";
	
		$url = 'http://localhost/material-examined/service/api.php?code=' . urlencode($obj->code) . '&match';
	
		$json = get($url);
		$response = json_decode($json);
	
		if ($debug)
		{
			print_r($response);
		}
	
		// filter known duplicate datasets
		if (isset($response->hits))
		{
			$hits = array();
		
			foreach ($response->hits as $hit)
			{
				switch ($hit->datasetKey)
				{
					// eat these
					case '9118eee0-42d2-4a27-9ae0-78d49d163a5b':
						break;
			
			
					default:
						$hits[] = $hit;
						break;
				}
		
			}
	
	
			$response->hits = $hits;
		}
	
	
		if ($response->status == 200 && isset($response->hits))
		{
			$num_hits = count($response->hits);
			echo "-- Number of hits: $num_hits\n";
		
			switch ($num_hits)
			{
				case 0:
					$failed[$obj->code] = "No hits";
					break;
				
				case 1:
					$ok = false;
				
					$query = "";
					$target = "";
			
					// tests?
				
					$pp = new Parser();

					// Names in JSTOR
					$query_names = array();
				
					if (isset($obj->stored_under_name))
					{
						$query_names[] = $obj->stored_under_name;
					}

					if (isset($obj->canonical))
					{
						$query_names[] = $obj->canonical;
					
						$query_parts = explode(' ', $obj->canonical);
						if (count($query_parts) == 3)
						{
							// make sure we have binomial
							$query_names[] = $query_parts[0] . ' ' . $query_parts[1];
						}
					}
				
					// Names in GBIF
					$target_names = array();
				
					if (isset($response->hits[0]->species))
					{
						$target_names[] = $response->hits[0]->species;
					}
				
					if (isset($response->hits[0]->scientificName))
					{
						$r = $pp->parse($response->hits[0]->scientificName);
						if ($r->scientificName->parsed)
						{					
							$target_names[] = $r->scientificName->canonical;
						}
					}

					// 1839523584
					if (isset($response->hits[0]->typifiedName))
					{
						$r = $pp->parse($response->hits[0]->typifiedName);
						if ($r->scientificName->parsed)
						{					
							$target_names[] = $r->scientificName->canonical;
						}
					}

					// 437457398			
					if (isset($response->hits[0]->extensions))
					{					
						if (isset($response->hits[0]->extensions->{'http://rs.tdwg.org/dwc/terms/Identification'}))
						{
							foreach ($response->hits[0]->extensions->{'http://rs.tdwg.org/dwc/terms/Identification'} as $identification)
							{
								// echo $identification->{'http://rs.tdwg.org/dwc/terms/scientificName'} . "\n";
							
								if (isset($identification->{'http://rs.tdwg.org/dwc/terms/scientificName'}))
								{					
									$r = $pp->parse($identification->{'http://rs.tdwg.org/dwc/terms/scientificName'});
									if ($r->scientificName->parsed)
									{					
										$target_names[] = $r->scientificName->canonical;
									}	
								}				
							}
						}
					}
							
					$query_names = array_unique($query_names);
					$query_names = array_values($query_names);
				
					$target_names = array_unique($target_names);
					$target_names = array_values($target_names);
				
					if ($debug)
					{
						echo "Names in JSTOR\n";
						print_r($query_names);
					
						echo "Names in GBIF\n";
						print_r($target_names);
					}
				
					$ok = same_name($query_names, $target_names);
				
					//exit();
				
				
					/*
				
					if (!$ok)
					{
						echo "-- Do names match exactly?\n";
						// names match exactly
						if (isset($obj->stored_under_name) && isset($response->hits[0]->species))
						{
							$query = $obj->stored_under_name;
							$target = $response->hits[0]->species;
					
							// subspecies/variety?
							if (isset($response->hits[0]->infraspecificEpithet) && $response->hits[0]->infraspecificEpithet != "")
							{
								$target .= ' ' . $response->hits[0]->infraspecificEpithet;
							}
						
							echo "-- $query|$target\n";
				
							$ok = (strcmp($query, $target) == 0);
						}
					}

					if (!$ok)
					{
						echo "-- Do names match exactly?\n";
						// names match exactly
						if (isset($obj->canonical) && isset($response->hits[0]->species))
						{
							$query = $obj->canonical;
							$target = $response->hits[0]->species;
					
							// subspecies/variety?
							if (isset($response->hits[0]->infraspecificEpithet) && $response->hits[0]->infraspecificEpithet != "")
							{
								$target .= ' ' . $response->hits[0]->infraspecificEpithet;
							}
						
							echo "-- $query|$target\n";
				
							$ok = (strcmp($query, $target) == 0);
						}
					}
				
					// 1998327014
					// GBIF record as both accepted and original name
					if (!$ok)
					{
						// names match exactly
						if (isset($obj->canonical) && isset($response->hits[0]->genericName) && isset($response->hits[0]->specificEpithet))
						{
							$query = $obj->canonical;
							$target = $response->hits[0]->genericName . ' ' . $response->hits[0]->specificEpithet;
					
							// subspecies/variety?
							if (isset($response->hits[0]->infraspecificEpithet) && $response->hits[0]->infraspecificEpithet != "")
							{
								$target .= ' ' . $response->hits[0]->infraspecificEpithet;
							}
						
							//echo "-- $query|$target\n";
				
							$ok = (strcmp($query, $target) == 0);
						}
					}
				

					if (!$ok)
					{
						// names match either genus or epithet
						if (isset($obj->stored_under_name) && isset($response->hits[0]->species))
						{
							$query = $obj->stored_under_name;
							$target = $response->hits[0]->species;
										
							// subspecies/variety?
							if (isset($response->hits[0]->infraspecificEpithet) && $response->hits[0]->infraspecificEpithet != "")
							{
								$target .= ' ' . $response->hits[0]->infraspecificEpithet;
							}						
						
							echo "-- $query|$target\n";
					
							$parts_query = explode(' ', $query);
							$parts_target = explode(' ', $target);
						
							if (count($parts_query) >= 2 && count($parts_target) >= 2)
							{
								if (!$ok)
								{
									// are stemmed epithets the same?
									if (strcmp(stem_epithet($parts_query[1]), stem_epithet($parts_target[1])) == 0)
									{
										$ok = true;
									}
								}
							
								if (!$ok)
								{
									if (count($parts_query) == 3 && count($parts_target)== 3)
									{
										// are stemmed epithets the same?
										if (strcmp(stem_epithet($parts_query[2]), stem_epithet($parts_target[2])) == 0)
										{
											$ok = true;
										}
									}
								}

								if (!$ok)
								{
									// do genera match (we are getting desparate)
									if (strcmp($parts_query[0], $parts_target[0]) == 0)
									{
										$ok = true;
									}
								}
						
							}
						}
					}
				
					// JSTOR is genus only
					if (!$ok)
					{
						// names match exactly
						if (isset($obj->stored_under_name) && preg_match('/^[A-Z]\w+$/', $obj->stored_under_name) && isset($response->hits[0]->species))
						{
							$query = $obj->stored_under_name;
							$target = $response->hits[0]->species;
						
							echo "-- $query|$target\n";
									
							$ok = preg_match('/^' . $query . '/', $target);
						}
					
					}
				
					// GBIF genus-only
					if (!$ok)
					{
						// names match exactly
						if (isset($obj->stored_under_name) && !isset($response->hits[0]->species) && isset($response->hits[0]->genus))
						{
							$query = $obj->stored_under_name;
							$target = $response->hits[0]->genus;
						
							echo "-- $query|$target\n";
									
							$ok = preg_match('/^' . $target . '/', $query);
						}
					}
					*/
						
					if ($ok)
					{			
						echo "UPDATE specimen SET gbif=" . $response->hits[0]->key . " WHERE doi='" . $obj->doi . "';" . "\n";
					
						if (isset($response->hits[0]->occurrenceID))
						{
							echo "UPDATE specimen SET occurrenceID='" . $response->hits[0]->occurrenceID . "' WHERE doi='" . $obj->doi . "';" . "\n";						
						}
					
						// dataset-specific code for a native URL for the specimen
						$url = '';
															
						switch ($response->hits[0]->datasetKey)
						{
							// AK
							case '83ae84cf-88e4-4b5c-80b2-271a15a3e0fc':
								if (isset($response->hits[0]->references))
								{
									$url = $response->hits[0]->references;
								}
								break;

							// WAG
							case '15f819bd-6612-4447-854b-14d12ee1022d':
								if (isset($response->hits[0]->occurrenceID))
								{
									$url = $response->hits[0]->occurrenceID;
								}
								break;
					
					
							default:
								break;
						}
					
						if ($url != '')
						{
							echo "UPDATE specimen SET occurrenceUrl='" . $url . "' WHERE doi='" . $obj->doi . "';" . "\n";						
						}
					}
					else
					{
						$failed[$obj->code] = "No match: " . json_encode($query_names) . " != " . json_encode($target_names) . "\n";
					}
					break;
				
				default:
					$failed[$obj->code] = "Too many hits";
					break;
		
			}
	
		}
	
		// Give server a break every 10 items
		if (($count++ % 10) == 0)
		{
			$rand = rand(1000000, 3000000);
			echo "\n-- ...sleeping for " . round(($rand / 1000000),2) . ' seconds' . "\n\n";
			usleep($rand);
		}
	
	
	}
}


