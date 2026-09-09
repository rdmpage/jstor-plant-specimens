<?php

// interface for viewing progress

// php -S localhost:4000 index.php

error_reporting(E_ALL);

$config = array();

$config['root'] = "/index.php";
$config['title'] = "JSTOR";
$config['headings'] = array("doi", "code", "title", "canonical", "type_status", "family", "gbif", "occurrenceID", "herbarium");

$config['genus'] = array();
$config['genus']['count'] = 'SELECT COUNT(*) AS count FROM specimen WHERE canonical LIKE <QUERY> AND type_status IS NOT NULL';
$config['genus']['list'] = 'SELECT * FROM specimen WHERE doi IN (SELECT doi FROM specimen WHERE canonical LIKE <QUERY>  AND type_status IS NOT NULL ORDER BY canonical, doi <LIMIT>) ORDER BY canonical, doi';
$config['genusCoverage'] = 'SELECT gbif IS NOT NULL AS has_gbif, occurrenceUrl IS NOT NULL AS has_url, occurrenceID IS NOT NULL AS has_id FROM specimen WHERE canonical LIKE <QUERY>  AND type_status IS NOT NULL ORDER BY canonical, doi';

$config['herbarium'] = array();
$config['herbarium']['count'] = 'SELECT COUNT(*) AS count FROM specimen WHERE herbarium = <QUERY> AND type_status IS NOT NULL';
$config['herbarium']['list'] = 'SELECT * FROM specimen WHERE doi IN (SELECT doi FROM specimen WHERE herbarium = <QUERY> AND type_status IS NOT NULL ORDER BY canonical, doi <LIMIT>) ORDER BY canonical, doi';
$config['herbariumCoverage'] = 'SELECT gbif IS NOT NULL AS has_gbif, occurrenceUrl IS NOT NULL AS has_url, occurrenceID IS NOT NULL AS has_id FROM specimen WHERE herbarium = <QUERY> AND type_status IS NOT NULL ORDER BY canonical, doi';

$pdo = new PDO('sqlite:jstor.db');

// how many rows to show per page
$rowsPerPage = 20;


$notes = array(
	'ALA' => 'In GBIF as UAM, no shared identifiers https://www.gbif.org/dataset/5c1fdaf6-4a18-4c5d-a84b-a4ba41f077c9',
	'AMES' => 'Not in GBIF',
	'BAA' => 'Tropicos Specimens Non-MO but no shared codes',
	'BCMEX' => 'In GBIF, no shared identifiers',
	'BKF' => 'Not in GBIF',
	'BOL' => 'In GBIF but no barcodes, e.g. https://www.gbif.org/occurrence/3708592735 is one possible match for BOL136662',
	'C' => 'Barcodes such as C10007766 may be stored in the "otherCatalogNumbers" field',
	'CHOCO' => 'In GBIF, no shared identifiers https://www.gbif.org/dataset/26d97e94-6ee9-4d5a-a9b8-7d514ec0345c',
	'CORD' => 'Multiple codes for same occurrence, e.g. CORD 00005267 | CORD 00005268 | CORD 00005269 <a href="https://www.gbif.org/occurrence/2239102626">https://www.gbif.org/occurrence/2239102626</a>',
	'EA' => 'Not in GBIF? but publisher is',
	'F' => 'F0BN009917 matches <a href="https://www.gbif.org/occurrence/1211544277">https://www.gbif.org/occurrence/1211544277</a>, which has institution code "B", there are at least two Field Museum datasets, one based on specimens in Berlin',
	'FI' => 'Not in GBIF',
	'G' => 'Get via direct download which has barcodes, then match on catalogue number',
	//'GOET' => 'Not in GBIF',
	'H' => 'Some in GBIF, but unclear and weird URLs that don\'t work (or do they?), e.g. <a href="http://id.luomus.fi/HA.H3300009" target="_new">http://id.luomus.fi/HA.H3300009</a>',
	'HAL' => 'Not in GBIF, but direct via JACQ, e.g. <a href="https://hal.jacq.org/HAL0099901">https://hal.jacq.org/HAL0099901</a>',
	'HUA' => 'In GBIF but no shared identifiers, see also Tropicos Specimens Non-MO',
	'IFAN' => 'Not in GBIF',
	'KEP' => 'Not in GBIF',
	'LE' => 'Tropicos Specimens Non-MO but no shared codes',
	'LISC' => 'Different images of same specimens',
	'LIL' => 'Not in GBIF',
	'LINN' => 'Not in GBIF',
	'LISU' => 'Not in GBIF but some non-MO Tropicos e.g. LISU208876 matches <a href="https://www.gbif.org/occurrence/4061604832">https://www.gbif.org/occurrence/4061604832</a>.',
	'LP' => 'In GBIF (<a href="https://www.gbif.org/dataset/b4a37621-7556-4705-9349-ede261feebe4">Colección de Herbario LP</a>) but collection and inst not LP, e.g. LP000984 matches <a href="https://www.gbif.org/occurrence/900144540">https://www.gbif.org/occurrence/900144540</a>.',

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
	'SEL' => 'Some specimens in Tropicos Specimens Non-MO',
	'SBBG' => 'JSTOR codes in Darwin Core records',
	'SING' => 'Not in GBIF, hosted by Oxford, maybe direct download',
	'SGO' => 'Not in GBIF?',
	'SP' => 'In GBIF but no barcodes to match on',
	'TCD' => 'Not in GBIF?',
	'TUB' => 'Not in GBIF',
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
	
	if (0)
	{
		echo "\n";
		echo $sql;
		echo "\n";
	}

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
	
	$numrows = $data[0]->count;

	// how many pages we have when using paging?
	$maxPage = ceil($numrows/$rowsPerPage);
	
	// counting the offset
	$offset = ($pageNum - 1) * $rowsPerPage;
	
	if (strpos($sql, '<LIMIT>') !== false)
	{
		$sql = str_replace('<LIMIT>', "LIMIT $rowsPerPage OFFSET $offset", $sql);
	}
	else
	{
		$sql .= " LIMIT $rowsPerPage OFFSET $offset";
	}
	
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
	display_coverage('genusCoverage', $query_string, 'genus', $query);

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
	// Glitch is gone now
	//echo '<p><a href="https://empty-opal.glitch.me/?q=' . $query . '" target="_new">Where is the damned collection?</a></p>';
		
	display_pagination('herbarium', $q);
	display_page($q);
	display_pagination('herbarium', $q);
	display_coverage('herbariumCoverage', $query_string, 'herbarium', $query);

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

	/* Container for the progress bar */
	.progress-container {
		width: 100%;
		background-color: #f3f3f3;
		border-radius: 4px;
		position: relative;
		height: 20px;
	}

	/* Progress bar inside the container */
	.progress-bar {
		height: 100%;
		border-radius: 4px;
		background-color: #BBB;
		width: 0;
	}

	/* Percentage label inside the bar */
	.progress-label {
		position: absolute;
		width: 100%;
		text-align: center;
		/* color: white; */
		font-size: 14px;
		line-height: 20px;
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
// Coverage strip: one tile per specimen, in the same order as the paged result list.
//
// Tiles sit on a fixed grid, so a tile's position alone identifies its row in the result set --
// tile n is result n, which lives on page floor(n / $rowsPerPage) + 1. That means no per-tile <a>
// is needed: the strip is one <path> per coverage level plus a single click handler. For a
// herbarium like K that is a handful of DOM nodes rather than 360,429, and ~61 KB gzipped rather
// than 557 KB.
//
// The paths use relative moves ("m14 0h12v12h-12z") rather than absolute ones because consecutive
// tiles then emit an identical token, which compresses roughly 5x better.
//
// This depends on the coverage query and the list query returning rows in the same order, which is
// why both end in "ORDER BY canonical, doi" -- canonical alone is not unique, and two queries are
// free to break ties differently.
function display_coverage($facet, $query, $term, $term_value)
{
	global $config;
	global $pdo;
	global $rowsPerPage;

	if (!isset($config[$facet]))
	{
		return;
	}

	$sql = str_replace('<QUERY>', quote_string($query), $config[$facet]);

	$perRow = 90;	// tiles per row
	$pitch  = 14;	// grid spacing
	$box    = 12;	// drawn square

	// Which identifiers a tile has, as a bitmask. Kept as a bitmask rather than a plain count so
	// the tooltip can still name them, the way the old per-tile title attribute did.
	$flags = array('has_gbif', 'has_url', 'has_id');

	$paths  = array();	// bitmask => path data
	$last   = array();	// bitmask => [x, y] of the previous tile drawn at that level
	$status = '';		// one hex digit per tile, read by the tooltip
	$n = 0;

	$stmt = $pdo->query($sql);

	while ($row = $stmt->fetch(\PDO::FETCH_ASSOC))
	{
		$bits = 0;
		foreach ($flags as $b => $f)
		{
			if (!empty($row[$f]))
			{
				$bits |= (1 << $b);
			}
		}

		$x = ($n % $perRow) * $pitch;
		$y = (int)($n / $perRow) * $pitch;

		if (!isset($paths[$bits]))
		{
			$paths[$bits] = 'M' . $x . ' ' . $y . 'h' . $box . 'v' . $box . 'h-' . $box . 'z';
		}
		else
		{
			$paths[$bits] .= 'm' . ($x - $last[$bits][0]) . ' ' . ($y - $last[$bits][1])
				. 'h' . $box . 'v' . $box . 'h-' . $box . 'z';
		}

		$last[$bits] = array($x, $y);
		$status .= dechex($bits);
		$n++;
	}

	if ($n == 0)
	{
		return;
	}

	$width  = $perRow * $pitch;
	$height = (int)ceil($n / $perRow) * $pitch;

	echo '<h3>Coverage of "' . htmlspecialchars($query) . '"</h3>';

	echo '<p style="font-size:0.9em;color:#666;">Each tile is one specimen, shaded by how many identifiers it has. Click a tile to jump to the page of results holding it.</p>';

	echo '<svg id="coverage" xmlns="http://www.w3.org/2000/svg"'
		. ' viewBox="0 0 ' . $width . ' ' . $height . '"'
		. ' style="width:100%;max-width:' . $width . 'px;height:auto;cursor:pointer;"'
		. ' data-perrow="' . $perRow . '"'
		. ' data-pitch="' . $pitch . '"'
		. ' data-total="' . $n . '"'
		. ' data-perpage="' . $rowsPerPage . '"'
		. ' data-base="' . htmlspecialchars($config['root'] . '?' . $term . '=' . urlencode($term_value)) . '"'
		. ' data-status="' . $status . '">';

	ksort($paths);

	foreach ($paths as $bits => $d)
	{
		// Same shading as before: 0.1, plus 0.2 for each identifier present.
		$opacity = 0.1 + 0.2 * substr_count(decbin($bits), '1');

		echo '<path fill="green" opacity="' . $opacity . '" d="' . $d . '"/>';
	}

	echo '</svg>';

	echo '<div id="coverage-tip" style="display:none;position:absolute;z-index:10;pointer-events:none;'
		. 'background:rgba(0,0,0,0.8);color:white;padding:4px 8px;border-radius:4px;font-size:0.8em;white-space:nowrap;"></div>';

	echo <<<'SCRIPT'
<script>
(function () {
	var svg = document.getElementById('coverage');
	var tip = document.getElementById('coverage-tip');
	if (!svg) { return; }

	var perRow  = +svg.getAttribute('data-perrow');
	var pitch   = +svg.getAttribute('data-pitch');
	var total   = +svg.getAttribute('data-total');
	var perPage = +svg.getAttribute('data-perpage');
	var base    = svg.getAttribute('data-base');
	var status  = svg.getAttribute('data-status');
	var labels  = ['gbif', 'occurrenceUrl', 'occurrenceID'];

	// Tile index from a mouse position: the grid is regular, so this is arithmetic rather than
	// a hit test against 120,000 elements.
	function indexAt(evt) {
		var pt = svg.createSVGPoint();
		pt.x = evt.clientX;
		pt.y = evt.clientY;
		var p = pt.matrixTransform(svg.getScreenCTM().inverse());
		var col = Math.floor(p.x / pitch);
		var row = Math.floor(p.y / pitch);
		if (col < 0 || col >= perRow || row < 0) { return -1; }
		var i = row * perRow + col;
		return (i < total) ? i : -1;
	}

	function pageOf(i) { return Math.floor(i / perPage) + 1; }

	svg.addEventListener('click', function (e) {
		var i = indexAt(e);
		if (i < 0) { return; }
		window.location = base + '&page=' + pageOf(i);
	});

	svg.addEventListener('mousemove', function (e) {
		var i = indexAt(e);
		if (i < 0) { tip.style.display = 'none'; return; }
		var bits = parseInt(status.charAt(i), 16);
		var got = [];
		for (var b = 0; b < labels.length; b++) {
			if (bits & (1 << b)) { got.push(labels[b]); }
		}
		tip.textContent = '#' + (i + 1) + ' → page ' + pageOf(i)
			+ ' — ' + (got.length ? got.join(', ') : 'no identifiers');
		tip.style.display = 'block';
		tip.style.left = (e.pageX + 14) + 'px';
		tip.style.top  = (e.pageY + 14) + 'px';
	});

	svg.addEventListener('mouseleave', function () { tip.style.display = 'none'; });
}());
</script>
SCRIPT;
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
						//echo '<a href="' . $config['root'] . '?id=' . urlencode($hit->{$k}) . '">' . $hit->{$k} . '</a>';						
						echo '<a href="https://plants.jstor.org/stable/' . $hit->{$k} . '" target="_new">' . $hit->{$k} . '</a>';
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
// Per-herbarium type and GBIF-match counts.
//
// One pass over the covering index specimen_stats(herbarium, type_status, gbif), which keeps this
// off the specimen table itself. COUNT(*) rather than COUNT(doi) matters: doi is not in the index,
// so asking for it forces a row lookup per record and the query goes back to taking seconds.
//
// Note this deliberately does not go through do_sqlite_query(), which drops values that loosely
// compare equal to '' -- on PHP 7 that would silently discard every count of 0.
function get_stats()
{
	global $pdo;
	global $notes;

	$sql = 'SELECT herbarium, COUNT(*) AS types, COUNT(gbif) AS gbif FROM specimen'
		. ' WHERE type_status IS NOT NULL GROUP BY herbarium ORDER BY types DESC';

	$herbaria = array();

	$stats = new stdclass;
	$stats->total = 0;
	$stats->matched = 0;

	foreach ($pdo->query($sql, \PDO::FETCH_ASSOC) as $row)
	{
		$herbarium = $row['herbarium'];

		$herbaria[$herbarium] = array(
			(int)$row['types'],
			(int)$row['gbif'],
			isset($notes[$herbarium]) ? $notes[$herbarium] : ''
		);

		$stats->total   += (int)$row['types'];
		$stats->matched += (int)$row['gbif'];
	}

	$stats->herbaria = $herbaria;

	return $stats;
}

//--------------------------------------------------------------------------------------------------
function display_stats()
{
	global $config;

	$stats = get_stats();

	echo '<div>';

	echo "<div>Number of type specimens: <b>" . $stats->total . "</b></div>";

	echo "<div>Matched to GBIF: <b>" . $stats->matched . "</b>";
	if ($stats->total > 0)
	{
		echo " (" . round($stats->matched / $stats->total * 100, 0) . "%)";
	}
	echo "</div>";

	echo '<table>';
	echo '<tr><th>Herbarium</th><th>Types</th><th>GBIF</th><th>%</th><th>Notes</th></tr>';
	foreach ($stats->herbaria as $k => $v)
	{
		echo '<tr>';
		echo '<td><a href="?herbarium=' . $k . '">' . $k . '</td>';
		echo '<td align="right">' . $v[0] . '</td>';		
		echo '<td align="right">' . $v[1] . '</td>';

		echo '<td width="100">';		
		echo '<div class="progress-container">';
        echo '<div class="progress-bar" style="width: ' . round($v[1]/$v[0] * 100, 0) . '%;">';
        //echo '<span class="progress-label">' . round($v[1]/$v[0] * 100, 0) . '%</span>';
        echo '</div>';
        echo '</div>';
		echo '</td>';
		
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
	

}

main();

?>


