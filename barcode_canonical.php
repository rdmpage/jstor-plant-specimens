<?php

// Make canonical names for scientificName in barcode table

error_reporting(E_ALL);

require_once (dirname(__FILE__) . '/taxon_name_parser.php');

$pdo = new PDO('sqlite:jstor.db');

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

$sql = 'SELECT * FROM barcode WHERE id LIKE "G-%" AND canonical IS NULL';

$sql = 'SELECT * FROM barcode WHERE id ="G-DC-237148/11" AND canonical IS NULL';

$data = do_sqlite_query($sql);

$pp = new Parser();

foreach ($data as $obj)
{
	if (0)
	{
		print_r($obj);
	}

	if (isset($obj->scientificName))
	{
		$r = $pp->parse($obj->scientificName);
		if ($r->scientificName->parsed)
		{		
			echo 'UPDATE barcode SET canonical="' . $r->scientificName->canonical . '" WHERE id="' . $obj->id . '";'	. "\n";
		}
	}

}

