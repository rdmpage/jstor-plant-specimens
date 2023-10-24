<?php

// for large genera we can do approx search, but need min of three letters in species name
// https://plants.jstor.org/search?filter=free_text&so=ps_group_by_genus_species+asc&Query=%28ResourceType%3Aspecimens%29+AND+Genus%3ABegonia+AND+SpecificEpithet%3Aabb*
error_reporting(E_ALL);
require_once (dirname(__FILE__) . '/HtmlDomParser.php');
require_once (dirname(__FILE__) . '/taxon_name_parser.php');

use Sunra\PhpSimple\HtmlDomParser;

$pp = new Parser();

$basedir = dirname(__FILE__) . '/cache';

$files = scandir($basedir);

// debugging
//$files=array('G00445220.html');

//$files=array('G00324296.html');


foreach ($files as $filename)
{
	if (preg_match('/.html$/', $filename, $m))
	{	

		$html = file_get_contents($basedir . '/' . $filename);

		$dom = HtmlDomParser::str_get_html($html);	
		
		$kv = array();
			
		// get article details
		$trs = $dom->find('tr');
		foreach ($dom->find('fieldset[class=dataDetails ui-corner-all] p') as $p)
		{
		
			foreach ($dom->find('label') as $label)
			{
				$key = trim($label->plaintext);
				$key = preg_replace('/\s+:/', '', $key);
				
				//echo $key . "=";
				
				if ($label->next_sibling ())
				{
					$next = $label->next_sibling();
					
					$value = trim($next->plaintext);
					
					if ($value != '')
					{
					
						//echo $value;
					
						switch ($key)
						{
							case 'GBIFCHID':
								$kv['id'] = $value;
								break;

							case 'CH Unique Specimen ID on label':
								$kv['barcode'] = $value;
								break;
							
							case 'Identification':
							case 'Original combination':
								$kv['scientificName'] = $value;
								
								/*
								$r = $pp->parse($value);
								if ($r->scientificName->parsed)
								{	
									$kv['canonical'] = $r->scientificName->canonical;	
								}
								*/
								break;
					
							default:
								break;
						}
					}
				}
				
				
				
				//echo "\n";

				
				// get sibling
				
			}
		}
		
		//print_r($kv);
		
		if (count($kv) == 3)
		{
			echo 'REPLACE INTO barcode(id, barcode, scientificName) VALUES("' . $kv['id'] . '", "' . $kv['barcode'] . '", "' . $kv['scientificName'] . '");' . "\n";
		}
		if (count($kv) == 2)
		{
			echo 'REPLACE INTO barcode(id, barcode) VALUES("' . $kv['id'] . '", "' . $kv['barcode'] . '");' . "\n";
		}
		
		
	}
}

?>
