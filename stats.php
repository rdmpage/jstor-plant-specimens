<?php

error_reporting(E_ALL);

$pdo = new PDO('sqlite:jstor.db');

$notes = array(
	'ALA' => 'In GBIF as UAM, no shared identifiers https://www.gbif.org/dataset/5c1fdaf6-4a18-4c5d-a84b-a4ba41f077c9',
	'AMES' => 'Not in GBIF',
	'BAA' => 'Tropicos Specimens Non-MO but no shared codes',
	'BCMEX' => 'In GBIF, no shared identifiers',
	'BKF' => 'Not in GBIF',
	'C' => 'In GBIF but no shared identifiers, e.g. C10007766 is https://www.gbif.org/occurrence/125812836 (?)',
	'CHOCO' => 'In GBIF, no shared identifiers https://www.gbif.org/dataset/26d97e94-6ee9-4d5a-a9b8-7d514ec0345c',
	'CORD' => 'Multiple codes for same occurrence, e.g. CORD 00005267 | CORD 00005268 | CORD 00005269 <a href="https://www.gbif.org/occurrence/2239102626">https://www.gbif.org/occurrence/2239102626</a>',
	'EA' => 'Not in GBIF? but publisher is',
	'F' => 'F0BN009917 matches https://www.gbif.org/occurrence/1211544277, which has institution code "B", so Darwin Core is seriously borked',
	'FI' => 'Not in GBIF',
	'G' => 'Get via direct download which has barcodes, then match on catalogue number',
	//'GOET' => 'Not in GBIF',
	'H' => 'Some in GBIF, but unclear and weird URLs that don\'t work (or do they?), e.g. <a href="http://id.luomus.fi/HA.H3300009" target="_new">http://id.luomus.fi/HA.H3300009</a>',
	'HAL' => 'Not in GBIF, but direct via JACQ, e.g. <a href="https://hal.jacq.org/HAL0099901">https://hal.jacq.org/HAL0099901</a>',
	'HUA' => 'In GBIF but no shared identifiers',
	'IFAN' => 'Not in GBIF',
	'KEP' => 'Not in GBIF',
	'LE' => 'Tropicos Specimens Non-MO but no shared codes',
	'LISC' => 'Different images of same specimens',
	'LIL' => 'Not in GBIF',
	'LINN' => 'Not in GBIF',
	'M' => 'Match using download',
	'MO' => 'Match on image URL using download',
	'MU' => 'Not in GBIF',
	'PRE' => 'Part of BODATSA',
	'PH' => 'Not in GBIF',
	'QCA' => 'Not in GBIF (occassional MOBOT), own web site local ids, images with barcode link, e.g. QCA13468 is https://bioweb.bio/portal/QCAZ/Especimen/513468, page with image is https://bioweb.bio/galeria/Especimen/Foto/444533, image itself is https://multimedia20stg.blob.core.windows.net/especimenes/QCA13468.jpg. Search interface https://bioweb.bio/portal/QCAZ/Especimen/513468',
	'RM' => 'Not in GBIF',
	'S' => 'Not in GBIF?',
	'SI' => 'No types in GBIF?',
	'SAM' => 'Not in GBIF',
	'SBBG' => 'JSTOR codes in Darwin Core records',
	'SING' => 'Not in GBIF, hosted by Oxford, maybe direct download',
	'SGO' => 'Not in GBIF?',
	'SP' => 'In GBIF but no barcodes to match on',
	'TCD' => 'Not in GBIF?',
	'UC' => 'Not in GBIF',
	'US' => 'Match on image URL using download',
	'UVAL' => 'In GBIF via MOBOT, barcodes are collector numbers(!)',
	'WIS' => 'Mostly not in GBIF, except US material)',
	'VEN' => 'Not in GBIF',
	'WSY' => 'Not in GBIF',
);



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

$herbaria = array();

// specimens
$sql = 'select count(doi) as c, herbarium from specimen where type_status IS NOT NULL group by herbarium order by c desc;';
$data = do_sqlite_query($sql);

foreach ($data as $obj)
{
	$herbaria[$obj->herbarium][0] = $obj->c; // number of specimens
	$herbaria[$obj->herbarium][1] = 0;
	$herbaria[$obj->herbarium][2] = '';
	
	if (isset($notes[$obj->herbarium]))
	{
		$herbaria[$obj->herbarium][2] = $notes[$obj->herbarium];
	}
}	

// mapped to GBIF
$sql = 'select count(doi) as c, herbarium from specimen where gbif is not null and type_status IS NOT NULL group by herbarium order by c desc;';
$data = do_sqlite_query($sql);

foreach ($data as $obj)
{
	$herbaria[$obj->herbarium][1] = $obj->c;
}	

echo "Herbarium\tNumber specimens\tNumber mapped to GBIF\tNotes\n";

foreach ($herbaria as $k => $v)
{
	echo $k . "\t";
	echo join("\t", $v);
	echo "\n";
}

?>
