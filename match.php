<?php

// Match plant barcodes using Material Examined

error_reporting(E_ALL);

ini_set('memory_limit', '-1');

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
						echo "-- " . stem_epithet($parts_query[1]) . ' =? ' . stem_epithet($parts_target[1]) . "\n";
					
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
	
	// 6
    // -ulatum
    if ($matched == '') {
        if (preg_match('/ulatum$/', $epithet)) {
            $matched = 'ulatum';
        }
    }

	
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

$herbarium = 'AK';
$herbarium = 'SAV';
$herbarium = 'WAG';
$herbarium = 'RSA';
$herbarium = 'CNS';
$herbarium = 'GH';
$herbarium = 'SAV';
$herbarium = 'NBG';
$herbarium = 'RB';
$herbarium = 'COI';
$herbarium = 'A';
$herbarium = 'LL';
//$herbarium = 'P';
//$herbarium = 'LISC';
$herbarium = 'MPU';
//$herbarium = 'BISH';
//$herbarium = 'COL';
//$herbarium = 'DNA';

$herbarium = 'JE';
$herbarium = 'MIN';
$herbarium = 'W';
$herbarium = 'AD';
//$herbarium = 'MBM';
$herbarium = 'BR';
$herbarium = 'PERTH';
$herbarium = 'MEXU';
$herbarium = 'RB';
$herbarium = 'R';
$herbarium = 'CLEMS';
$herbarium = 'LISC';
$herbarium = 'FR';
$herbarium = 'RENO';
$herbarium = 'E';
$herbarium = 'MEL';
$herbarium = 'MEXU';
$herbarium = 'BISH';
$herbarium = 'SAM';
$herbarium = 'P';
//$herbarium = 'BRI';
$herbarium = 'NSW';
$herbarium = 'MA';
$herbarium = 'PRE';
$herbarium = 'E';
$herbarium = 'NY';
//$herbarium = 'B';
//$herbarium = 'MEL';
//$herbarium = 'CHR';
//$herbarium = 'S';
//$herbarium = 'CAS';
//$herbarium = 'HBG';

$sql = 'SELECT * FROM specimen WHERE herbarium="' . $herbarium . '"';

// Done: A
//$sql = 'SELECT * FROM specimen WHERE herbarium LIKE "C%"';
$sql .= ' AND type_status IS NOT NULL';
$sql .= ' AND gbif IS NULL';
//$sql .= ' LIMIT 10000';

//$sql = 'SELECT * FROM specimen WHERE code="WAG0002980"';

//$sql = 'SELECT * FROM specimen WHERE code="QRS23733"';

if (0)
{
	$code = 'GH00025987';
	$code = 'S06-18568';
	$code = 'BRIT23714';
	$code = 'E00499897';
	$code = 'P00752498';
	$code = 'BR0000008818751';
	$code = 'P00089308';
	$code = 'A00050474';
	$code = 'L0039369';
	$code = 'BM000564421';
	$code = 'AD95802057';
	$code = 'MEL95286';
	$code = 'BISH1001549';
	$sql = 'SELECT * FROM specimen WHERE code="' . $code . '"';
}

if (0)
{
	$genus = 'Hornstedtia';
	$genus = 'Kaempferia';
	$genus = 'Acacia';
	$genus = 'Begonia';
	//$genus = 'Ficus';
	$genus = 'Macrosolen';
	
	
	
	$sql = 'SELECT * FROM specimen WHERE gbif IS NULL AND type_status IS NOT NULL AND canonical LIKE "' . $genus . '%"';
	//$sql .= ' AND herbarium="P"';
}

if (0)
{
	$canonical = "Abutilon bakerianum";
	$canonical = "Zygotritonia crocea";

	$sql = 'SELECT * FROM specimen WHERE gbif IS NULL AND type_status IS NOT NULL AND canonical = "' . $canonical . '"';
	//$sql .= ' AND herbarium="P"';
}


if (0)
{
	$country = 'Vietnam';
	$country = 'Thailand';
	$country = 'Indonesia';
	$country = 'China';
	
	$sql = 'SELECT * FROM specimen WHERE gbif IS NULL AND type_status IS NOT NULL AND country = "' . $country . '"';
	//$sql .= ' AND herbarium="MEXU"';
}



if (0)
{
	$family = 'ZINGIBERACEAE';
	$family = 'ORCHIDACEAE';
	$family = 'MALVACEAE';
	$family = 'EUPHORBIACEAE';
	$family = 'FABACEAE';
	
	$sql = 'SELECT * FROM specimen WHERE gbif IS NULL AND type_status IS NOT NULL AND family = "' . $family . '"';
	//$sql .= ' AND herbarium="MEXU"';
}


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

				// Duplictaes in RB
				case '98d7a49c-5776-41eb-a928-b6f5174eadd1':
					break;
			
				// Duplicates in MEXU
				case '7dcaa915-d51a-4376-b2b3-08fd7aca3f23':
				case '8016518a-f762-11e1-a439-00145eb45e9a';
					break;
					
				// Duplicates in NY
				case '07044577-bd82-4089-9f3a-f4a9d2170b2e':
					break;
					
				// Bizarre case where institution is NY in non-NY
				// dataset https://www.gbif.org/occurrence/417057002
				case 'f5de707d-eba1-4f1a-b1df-1a7fa27b1bc7':
					if ($herbarium == 'NY')
					{
					
					}
					else
					{
						$hits[] = $hit;
					}
					break;
					
				// MOBOT but not MO
				case 'e053ff53-c156-4e2e-b9b5-4462e9625424':
					if ($herbarium == 'NY')
					{
					
					}
					else
					{
						$hits[] = $hit;
					}
					break;					
					
				// Duplicates in P
				case 'f9be5570-6943-49b8-b317-9780af40effb':
					break;
			
				// Plazi duplicates messing with original Herbarium
				// e.g. https://www.gbif.org/occurrence/4068313301
				case 'a668ab97-169a-43d3-8656-2d97ef8c7bde':
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


print_r($failed);
