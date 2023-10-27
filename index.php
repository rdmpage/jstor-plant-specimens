<?php

// interface for viewing progress

// php -S localhost:4000 index.php

error_reporting(E_ALL);

$config = array();

$config['root'] = "/index.php";
$config['title'] = "JSTOR";
$config['headings'] = array("doi", "code", "title", "canonical", "type_status", "family", "gbif", "occurrenceID", "herbarium");

$config['genus'] = array();
$config['genus']['count'] = 'SELECT COUNT(doi) AS c FROM specimen WHERE canonical LIKE <QUERY> AND type_status IS NOT NULL';
$config['genus']['list'] = 'SELECT * FROM specimen WHERE canonical LIKE <QUERY>  AND type_status IS NOT NULL ORDER BY canonical';
$config['genusCoverage'] = 'SELECT doi, gbif, occurrenceUrl, occurrenceID FROM specimen WHERE canonical LIKE <QUERY>  AND type_status IS NOT NULL ORDER BY canonical';

$config['herbarium'] = array();
$config['herbarium']['count'] = 'SELECT COUNT(doi) AS c FROM specimen WHERE herbarium = <QUERY> AND type_status IS NOT NULL';
$config['herbarium']['list'] = 'SELECT * FROM specimen WHERE herbarium = <QUERY> AND type_status IS NOT NULL ORDER BY canonical';
$config['herbariumCoverage'] = 'SELECT doi, gbif, occurrenceUrl, occurrenceID FROM specimen WHERE herbarium = <QUERY> AND type_status IS NOT NULL ORDER BY canonical';

$pdo = new PDO('sqlite:jstor.db');

// how many rows to show per page
$rowsPerPage = 20;


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
function quote_string($str)
{
	$str = '"' . str_replace('"', '""', $str) . '"';
	
	return $str;
}

//----------------------------------------------------------------------------------------
function do_query($query, $count_sql, $sql, $pageNum = 1)
{	
	global $rowsPerPage;
	
	$numrows = 0;
	
	// How many hits?
	$data = do_sqlite_query($count_sql);
	
	$numrows = $data[0]->c;

	// how many pages we have when using paging?
	$maxPage = ceil($numrows/$rowsPerPage);
	
	// counting the offset
	$offset = ($pageNum - 1) * $rowsPerPage;
	
	$sql .= " LIMIT $rowsPerPage OFFSET $offset";
	
	$query_result = new stdclass;
	$query_result->query = $query;
	$query_result->numrows 	= $numrows;
	$query_result->numpages = $maxPage;
	$query_result->page 	= $pageNum;
	$query_result->hits= array();
	
	$data = do_sqlite_query($sql);
	
	foreach ($data as $obj)
	{
		$query_result->hits[] = $obj;
	}
		
	if ($pageNum > 1)
	{
	   $query_result->prev = $pageNum - 1;
	   $query_result->first = 1;
	} 
	else
	{
	   $query_result->prev = 0;
	   $query_result->first = 0;
	}
	
	if ($pageNum < $maxPage)
	{
	   $query_result->next = $pageNum + 1;
	   $query_result->last = $maxPage;

	} 
	else
	{
	   $query_result->next =0;
	   $query_result->last =0;
	}	

	return $query_result;	
}

//--------------------------------------------------------------------------------------------------
function display_genus($query, $pageNum = 1)
{
	global $config;
	
	/*
	echo '<pre>';
	print_r($config);
	echo '</pre>';
	*/
	
	$count_sql = $config['genus']['count'];
	$sql = $config['genus']['list'];
		
	$query_string = $query;
		
	if (preg_match('/^[A-Z]\w+[^\*]$/', $query))
	{
		$query_string .= '*';
	}
	
	$query_string = str_replace("*", "%", $query_string);
	
	$count_sql = str_replace('<QUERY>', quote_string($query_string), $count_sql);
	$sql = str_replace('<QUERY>', quote_string($query_string), $sql);
	
	//echo $sql;
		
	$q = do_query($query, $count_sql, $sql, $pageNum);
	
	//print_r($q);
	

	display_top($query);	
	display_search_box('genus', $query);
	echo '<h2>Showing results for genus "' . $query . '"</h2>';	
	display_pagination('genus', $q);
	display_page($q);
	display_pagination('genus', $q);
	display_coverage('genusCoverage', $query_string);

	display_bottom();
}

//--------------------------------------------------------------------------------------------------
function display_herbarium($query, $pageNum = 1)
{
	global $config;
	global $notes;
	
	/*
	echo '<pre>';
	print_r($config);
	echo '</pre>';
	*/
	
	$count_sql = $config['herbarium']['count'];
	$sql = $config['herbarium']['list'];
		
	$query_string = $query;
	
	$count_sql = str_replace('<QUERY>', quote_string($query_string), $count_sql);
	$sql = str_replace('<QUERY>', quote_string($query_string), $sql);
	
	//echo $sql;
		
	$q = do_query($query, $count_sql, $sql, $pageNum);
	
	//print_r($q);
	

	display_top($query);	
	display_search_box('herbarium', $query);
	echo '<h2>Showing results for herbarium "' . $query . '"</h2>';	
	
	if (isset($notes[$query]))
	{
		echo '<p>' . $notes[$query] . '</p>';
	}
	echo '<p><a href="https://empty-opal.glitch.me/?q=' . $query . '" target="_new">Where is the damned collection?</a></p>';
		
	display_pagination('herbarium', $q);
	display_page($q);
	display_pagination('herbarium', $q);
	display_coverage('herbariumCoverage', $query_string);

	display_bottom();
}

//--------------------------------------------------------------------------------------------------
function display_top($query)
{
	global $config;

	echo '<html>
	<head>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
	
	echo '<style>
body 
{ 
	font-family: sans-serif;
	padding:1em;
}

.off 
{
	color: rgb(192,192,192);
	border:1px solid rgb(192,192,192);
	border-radius:4px;
	padding:4px;
	width:1em;
}

.on 
{
	border:1px solid rgb(128,128,128);
	border-radius:4px;
	padding:4px;
	width:1em;
}

details {
	font-size:0.9em;
}

summary:hover { background:rgb(224,224,224); }

span {	
	padding-left:1em;
	padding-right:1em;
	
	text-align:left;
	
	display:inline-block;
	text-overflow: ellipsis;
	overflow: hidden; 
	white-space: nowrap;
	
	width:4%;
	
	/* border:1px solid black; */
}

.doi {
	text-align:right;
	/* background:orange; */
}

.code {
	width:10%;
}

.gbif {
	width:10%;
}

.occurrenceUrl {
	width:10%;
}

.occurrenceID {
	width:10%;
}


.title {
	width:20%;
}

.family {
	width:10%;
}

.herbarium {
	width:5%;
}

a:link {text-decoration: none;}
a:hover {text-decoration: underline;}

.mapped {
}

.empty {
background-color:#FFCC99;
}

	</style>';
	
	$title = $config['title'];
	
	if ($query != '')
	{
		$title = $query . ' - ' . $title;
	}
	echo '<title>' . $title . '</title>';
	echo '</head>
	<body>';
	
	if ($query != '')
	{
		echo '<a href="' . $config['root'] . '">Home</a>';
	}
	
	echo '<h1>' . $config['title'] . '</h1>';
}

//--------------------------------------------------------------------------------------------------
function display_search_box($mode = "genus", $query="")
{
	global $config;
	
	echo '<div style="float:right;">
			<form method="get" action="' . $config['root'] . '">
			<input type="hidden" name="mode" value="' . $mode . '" >
			<input type="search"  name="q" id="q" value="' . $query . '" placeholder="' . $mode . '">
			<input type="submit" value="Search" >
			</form>
		</div>
	';
}


//--------------------------------------------------------------------------------------------------
function display_coverage($facet, $query)
{
	global $config;
	
	if (isset($config[$facet]))
	{	
		$sql = $config[$facet];
	
		$sql = str_replace('<QUERY>', quote_string($query), $sql);
	
		//echo $sql;
	
		$data = do_sqlite_query($sql);
	
		echo '<h3>Coverage of "' . $query . '"</h3>';
	
		echo '<div>';
	
		foreach ($data as $obj)
		{
			echo '<div style="float:left;width:14px;height:14px;">';
		
			$title = array();
		
			$opacity = 0.1;
			
				
			if (isset($obj->gbif))
			{
				$opacity += 0.2;
				$title[] = 'gbif';
			}

			if (isset($obj->occurrenceUrl))
			{
				$opacity += 0.2;
				$title[] = 'occurrenceUrl';
			}

			if (isset($obj->occurrenceID))
			{
				$opacity += 0.2;
				$title[] = 'occurrenceID';
			}
		
			echo '<a href="' . $config['root'] . '?id=' . urlencode($obj->doi) . '" title="' . join(",", $title) . '">';
		
			echo '<div style="width:12px;height:12px;background-color:green;margin:1px;';
		
		
			echo 'opacity:' . $opacity;
			echo '">';
			echo '</div>';
		
			echo '</a>';
		
		
			echo '</div>';
	
		}
	
		echo '</div>';
	}

}

//--------------------------------------------------------------------------------------------------
function display_page($q)
{
	global $config;

	$keys = $config['headings'];
	
	echo '<div style="width:95%;border:1px solid red;overflow-x:auto;overflow-y:hidden;white-space: nowrap;padding:1em;">';
	
	
	echo '<div>';
	foreach ($keys as $k)
	{
		echo '<span class="' . $k . ' bold">';
		echo $k;
		echo '</span>';
			
	}
	echo '</div>';	
		
	foreach ($q->hits as $hit)
	{
		$citation_terms = array();
		
		$identifier_count = 0;
	
		echo '<details';
		
		// store row id
		echo ' data-id="' . $hit->doi . '"';
		
		// store identifiers that we may do something with, e.g. GBIF
		if (isset($hit->gbif))
		{
			echo ' data-gbif="' . $hit->gbif . '"';
			$identifier_count++;
		}
		
		echo '>';
		echo '<summary class="';
		
		if ($identifier_count > 0)
		{
			echo "mapped";
		}
		else
		{
			echo "empty";
		}
		
		echo '">';
		
		foreach ($keys as $k)
		{
			echo '<span class="' . $k . '">';
			
			if (isset($hit->{$k}))
			{
				switch ($k)
				{				
					case 'doi':
						echo '<a href="' . $config['root'] . '?id=' . urlencode($hit->{$k}) . '">' . $hit->{$k} . '</a>';
						break;					

					case 'code':
						echo '<a href="http://localhost/material-examined/?q=' . urlencode($hit->{$k}) . '" target="_new">' . $hit->{$k} . '</a>';
						break;					

					case 'gbif':
						echo '<a href="https://gbif.org/occurrence/' . $hit->{$k} . '" target="_new">' . $hit->{$k} . '</a>';
						break;					

					case 'occurrenceUrl':
						echo '<a href="' . $hit->{$k} . '" target="_new">' . $hit->{$k} . '</a>';
						break;					

					case 'occurrenceID':
						if (preg_match('/^http/', $hit->{$k}))
						{
							echo '<a href="' . $hit->{$k} . '" target="_new">' . $hit->{$k} . '</a>';
						}
						else
						{
							// make ids URLs if there are rules for this
							switch ($hit->herbarium)
							{
								case 'SING':
									echo '<a href="https://herbaria.plants.ox.ac.uk/bol/sing/record/details/' . $hit->{$k} . '" target="_new">' . $hit->{$k} . '</a>';								
									break;
							
								default:
									echo $hit->{$k};
									break;							
							}						
							
						}
						break;					

					case 'herbarium':
						echo '<a href="' . $config['root'] . '?herbarium=' . urlencode($hit->{$k}) . '">' . str_replace(' ', '&nbsp;', $hit->{$k}) . '</a>';
						break;					

					case 'canonical':
					case 'stored_under_name':
						$parts = explode(' ', trim($hit->{$k}));
						
						echo '<a href="' . $config['root'] . '?genus=' . urlencode($parts[0]) . '">' . $parts[0] . '</a>';
						
						array_shift($parts);
						
						if (count($parts) > 0)
						{
							echo ' ' . join(' ', $parts);
						}
						break;									
				
					default:
						echo $hit->{$k};
						break;
				}
			}
			echo '</span>';		
		}
		
		echo '</summary>';
		
		// get raw citation so we can display it if we want too
		$display_keys = array('title', 'stored_under_name', 'collector', 'date', 'country');
		$display_values = array();
		foreach ($display_keys as $dk)
		{
			if (isset($hit->{$dk}))
			{
				$display_values[] = $hit->{$dk};
			}
		}
		
		echo '<div style="padding:1em;width:50%;white-space:pre-wrap;">' . join(" ", $display_values). '</div>';
		echo '<img style="width:100px;padding:1em;" src="' . $hit->thumbnailUrl . '">';
		
		// more details go here...
		if (isset($hit->gbif))
		{
			echo '<div id="gbif-' . $hit->gbif . '"></div>';
		}
		echo '</details>';
	}
	
	echo '</div>';
}

//--------------------------------------------------------------------------------------------------
function display_pagination($term, $q)
{
	global $config;
	
	//echo '<hr/>';
	echo '<div style="padding-top:10px;padding-bottom:10px;">';
	if ($q->first != 0)
	{
		echo '<a href="' . $config['root'] . '?' . $term . '=' . $q->query . '&page=1"><span class="on">|&lt;</span></a>';	
	}
	else
	{
		echo '<span class="off">|&lt;</span>';
	}
	echo "&nbsp;";
	if ($q->prev != 0)
	{
		echo '<a href="' . $config['root'] . '?' . $term . '=' . $q->query . '&page=' . $q->prev . '"><span class="on">&lt;</span></a>';	
	}
	else
	{
		echo '<span class="off">&lt;</span>';
	}
	echo "&nbsp;";
	if ($q->next != 0)
	{
		echo '<a href="' . $config['root'] . '?' . $term . '=' . $q->query . '&page=' . $q->next . '"><span class="on">&gt;</span></a>';	
	}
	else
	{
		echo '<span class="off">&gt;</span>';
	}
	echo "&nbsp;";
	if ($q->last != 0)
	{
		echo '<a href="?' . $term . '=' . $q->query . '&page=' . $q->last . '"><span class="on">&gt;|</span></a>';	
	}
	else
	{
		echo '<span class="off">&gt;|</span>';
	}
	echo "  Showing page " . $q->page . " of " . $q->numpages . " pages";
	//echo '<hr/>';
	echo '</div>';
}


//--------------------------------------------------------------------------------------------------
function display_bottom()
{
	echo '<script>
</script>';

	echo '</body>
</html>';
}

//--------------------------------------------------------------------------------------------------
function display_stats()
{
	global $config;
	global $notes;
	

	echo '<div>';
	
	$sql = 'SELECT COUNT(doi) AS c FROM specimen WHERE type_status IS NOT NULL';
	$data = do_sqlite_query($sql);
	
	if (count($data) == 1)
	{
		echo "<div>Number of type specimens: <b>" . $data[0]->c . "</b></div>";
	}	
	
	$total = $data[0]->c;
	
	// progress
	$sql = 'SELECT COUNT(doi) AS c FROM specimen WHERE type_status IS NOT NULL';
	$sql .= ' AND gbif IS NOT NULL';
	$data = do_sqlite_query($sql);
	
	if (count($data) == 1)
	{
		echo "<div>Matched to GBIF: <b>" . $data[0]->c . "</b>";		
		echo " (" . round($data[0]->c/$total * 100, 0) . "%)";
		echo "</div>";
	}	

	$herbaria = array();
	
	$sql = 'select count(doi) as c, herbarium from specimen where type_status IS NOT NULL group by herbarium order by c desc;';
	$data = do_sqlite_query($sql);
	
	foreach ($data as $obj)
	{
		$herbaria[$obj->herbarium][0] = $obj->c;
		$herbaria[$obj->herbarium][1] = 0;
		$herbaria[$obj->herbarium][2] = '';
		
		if (isset($notes[$obj->herbarium]))
		{
			$herbaria[$obj->herbarium][2] = $notes[$obj->herbarium];
		}
	}	
	
	$sql = 'select count(doi) as c, herbarium from specimen where gbif is not null and type_status IS NOT NULL group by herbarium order by c desc;';
	$data = do_sqlite_query($sql);
	
	foreach ($data as $obj)
	{
		$herbaria[$obj->herbarium][1] = $obj->c;
	}	
	
	echo '<table>';
	echo '<tr><th>Herbarium</th><th>Types</th><th>GBIF</th><th>%</th><th>Notes</th></tr>';
	foreach ($herbaria as $k => $v)
	{
		echo '<tr>';
		echo '<td>' . $k . '</td>';
		echo '<td align="right">' . $v[0] . '</td>';		
		echo '<td align="right">' . $v[1] . '</td>';
		echo '<td align="right">' . round($v[1]/$v[0] * 100, 0) . '</td>';
		echo '<td>' . $v[2] . '</td>';		
		echo '</tr>';
	}
	echo '</table>';

	echo '</div>';
}



//--------------------------------------------------------------------------------------------------
function default_display()
{
	global $config;
		
	display_top('');
	display_search_box('genus');

	display_stats();
	display_bottom();	
}

//--------------------------------------------------------------------------------------------------
function display_search($query, $mode)
{
	switch ($mode)
	{
		case 'genus':
			display_genus($query, 1);
			break;

		case 'herbarium':
			display_herbarium($query, 1);
			break;

		case 'id':
			display_id($query, 1);
			break;	
			
		default:
			default_display();
			break;
	}
}


//--------------------------------------------------------------------------------------------------
function main()
{
	$query = '';
		
	// If no query parameters 
	if (count($_GET) == 0)
	{
		default_display();
		exit(0);
	}
	
	$pageNum = 1;
	// if $_GET['page'] defined, use it as page number
	if(isset($_GET['page']))
	{
		$pageNum = $_GET['page'];
	}
	
	// Mode
	$mode = 'genus';
	if(isset($_GET['mode']))
	{
		$mode = $_GET['mode'];
		switch ($mode)
		{
			case 'genus':
			case 'herbarium':
				break;
				
			default:
				$mode = 'genus';
		}
	}	

	if (isset($_GET['q']))
	{
		$query = trim($_GET['q']);
		display_search($query, $mode);
	}

	if (isset($_GET['genus']))
	{	
		$genus = trim($_GET['genus']);
		display_genus($genus, $pageNum);
	}
	
	if (isset($_GET['herbarium']))
	{	
		$herbarium = $_GET['herbarium'];
		display_herbarium($herbarium, $pageNum);
	}
	
	if (isset($_GET['id']))
	{	
		$id = $_GET['id'];
		display_id($id);
	}


}

main();

?>


