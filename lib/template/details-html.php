<?php
	header("content-type: text/html; charset=UTF-8");
?>
<?php include(CONST_BasePath.'/lib/template/includes/html-header.php'); ?>
	<link href="css/details.css" rel="stylesheet" type="text/css" />
</head>



<?php

	function headline($sTitle)
	{
		echo "<h2>".$sTitle."</h2>\n";
	}

	function osm_link($aFeature)
	{
		$sOSMType = ($aFeature['osm_type'] == 'N'?'node':($aFeature['osm_type'] == 'W'?'way':($aFeature['osm_type'] == 'R'?'relation':'')));
		if ($sOSMType) {
			return '<a href="http://www.openstreetmap.org/browse/'.$sOSMType.'/'.$aFeature['osm_id'].'">'.$sOSMType.' '.$aFeature['osm_id'].'</a>';
		}
		return '';
	}

	function wikipedia_link($aFeature)
	{
		if ($aFeature['wikipedia'])
		{
			list($sWikipediaLanguage,$sWikipediaArticle) = explode(':',$aFeature['wikipedia']);
			return '<a href="http://'.$sWikipediaLanguage.'.wikipedia.org/wiki/'.urlencode($sWikipediaArticle).'">'.$aFeature['wikipedia'].'</a>';
		}
		return '';
	}

	function nominatim_link($aFeature, $sTitle)
	{
		return '<a href="details.php?place_id='.$aFeature['place_id'].'">'.$sTitle.'</a>';
	}

	function format_distance($fDistance)
	{
		return'<abbr class="distance" title="'.$fDistance.'">~'.(round($fDistance,1)).' km</abbr>';
	}

	function kv($sKey,$sValue)
	{
		echo ' <tr><td>' . $sKey . '</td><td>'.$sValue.'</td></tr>'. "\n";
	}


	function hash_to_subtable($aAssociatedList)
	{
		$sHTML = '';
		foreach($aAssociatedList as $sKey => $sValue)
		{
			$sHTML = $sHTML.' <div class="line"><span class="name">'.$sValue.'</span> ('.$sKey.')</div>'."\n";
		}
		return $sHTML;
	}

	// function hash_to_subtable($aAssociatedList)
	// {
	// 	$sHTML = '<table class="table">';
	// 	foreach($aAssociatedList as $sKey => $sValue)
	// 	{
	// 		$sHTML = $sHTML . '<tr><td>'.$sKey.'</td><td class="name">'.$sValue.'</td></tr>'."\n";
	// 	}
	// 	$sHTML = $sHTML . '</table>';
	// 	return $sHTML;
	// }


	function map_icon($sIcon)
	{
		if ($sIcon){
			echo '<img id="mapicon" src="'.CONST_Website_BaseURL.'images/mapicons/'.$sIcon.'.n.32.png'.'" alt="'.$sIcon.'" />';
		}
	}


	function _one_row($aAddressLine){
		$bNotUsed = (isset($aAddressLine['isaddress']) && $aAddressLine['isaddress'] == 'f');

		echo '<tr class="' . ($bNotUsed?'notused':'') . '">';
		echo '  <td class="name">'.(trim($aAddressLine['localname'])?$aAddressLine['localname']:'<span class="noname">No Name</span>').'</td>';
		echo '  <td>' . $aAddressLine['class'].':'.$aAddressLine['type'] . '</td>';
		echo '  <td>' . osm_link($aAddressLine) . '</td>';
		echo '  <td>' . (isset($aAddressLine['admin_level']) ? $aAddressLine['admin_level'] : '') . '</td>';
		// echo '<td>' . (isset($aAddressLine['rank_search_label']) ? $aAddressLine['rank_search_label'] : '') .'</td>';
		// echo ', <span class="area">'.($aAddressLine['fromarea']=='t'?'Polygon':'Point').'</span>';
		echo '  <td>' . format_distance($aAddressLine['distance']).'</td>';;
		echo '  <td>' . nominatim_link($aAddressLine,'details &gt;') . '</td>';;
		echo "</tr>\n";
	}

?>



<body id="details-page">
	<div class="container">
		<div class="row">
			<div class="col-sm-10">
				<h1><?php echo $aPointDetails['localname'] ?></h1>
			</div>
			<div class="col-sm-2 text-right">
				<?php map_icon($aPointDetails['icon']) ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<table id="locationdetails" class="table table-striped">

				<?php

					kv('Name'            , hash_to_subtable($aPointDetails['aNames']) );
					kv('Type'            , $aPointDetails['class'].':'.$aPointDetails['type'] );
					kv('Last Updated'    , $aPointDetails['indexed_date'] );
					kv('Admin Level'     , $aPointDetails['admin_level'] );
					kv('Rank'            , $aPointDetails['rank_search_label'] );
					if ($aPointDetails['calculated_importance']) {
						kv('Importance'    , $aPointDetails['calculated_importance'].($aPointDetails['importance']?'':' (estimated)') );
					}
					kv('Coverage'        , ($aPointDetails['isarea']=='t'?'Polygon':'Point') );
					kv('Centre Point'    , $aPointDetails['lat'].','.$aPointDetails['lon'] );
					kv('OSM'             , osm_link($aPointDetails) );
					if ($aPointDetails['wikipedia'])
					{
						kv('Wikipedia Calculated' , wikipedia_link($aPointDetails) );
					}

					kv('Extra Tags'      , hash_to_subtable($aPointDetails['aExtraTags']) );

				?>

				</table>
			</div>

			<div class="col-md-6">
				<div id="map"></div>
			</div>

		</div>
		<div class="row">
			<div class="col-md-12">

			<h2>Address</h2>

			<table id="address" class="table table-striped table-responsive">
				<thead>
					<tr>
					  <td>Local name</td>
					  <td>Type</td>
					  <td>OSM</td>
					  <td>Admin level</td>
					  <!-- <td>Search rank</td> -->
					  <td>Distance</td>
					  <td></td>
					</tr>
				</thead>
				<tbody>

				<?php

					foreach($aAddressLines as $aAddressLine)
					{	
						_one_row($aAddressLine);
					}
				?>
	
				</tbody>
			</table>


<?php

	if ($aLinkedLines)
	{
		headline('Linked Places');
		echo '<table id="linked" class="table table-striped table-responsive">';
		foreach($aLinkedLines as $aAddressLine)
		{	
			_one_row($aAddressLine);
		}
		echo '</table>';
	}



	if ($aPlaceSearchNameKeywords)
	{
		headline('Name Keywords');
		foreach($aPlaceSearchNameKeywords as $aRow)
		{
			echo '<div>'.$aRow['word_token']."</div>\n";
		}
	}

	if ($aPlaceSearchAddressKeywords)
	{
		headline('Address Keywords');
		foreach($aPlaceSearchAddressKeywords as $aRow)
		{
			echo '<div>'.($aRow['word_token'][0]==' '?'*':'').$aRow['word_token'].'('.$aRow['word_id'].')'."</div>\n";
		}
	}

	if (sizeof($aParentOfLines))
	{
		headline('Parent Of');

		$aGroupedAddressLines = array();
		foreach($aParentOfLines as $aAddressLine)
		{
			if ($aAddressLine['type'] == 'yes') $sType = $aAddressLine['class'];
			else $sType = $aAddressLine['type'];

			if (!isset($aGroupedAddressLines[$sType]))
				$aGroupedAddressLines[$sType] = array();
			$aGroupedAddressLines[$sType][] = $aAddressLine;
		}
		foreach($aGroupedAddressLines as $sGroupHeading => $aParentOfLines)
		{
			$sGroupHeading = ucwords($sGroupHeading);
			echo "<h3>$sGroupHeading</h3>\n";

			echo '<table id="linked" class="table table-striped table-responsive">';
			foreach($aParentOfLines as $aAddressLine)
			{
				_one_row($aAddressLine);
			}
			echo '</table>';
		}
		if (sizeof($aParentOfLines) >= 500) {
			echo '<p>There are more child objects which are not shown.</p>';
		}
	}

	// headline('Other Parts');
	// headline('Linked To');
?>

			</div>
		</div>
	</div>

	<footer>
		<p class="copyright">
			&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors
		</p>
	</footer>


	<script type="text/javascript">

		var nominatim_result = {
			outlinestring: '<?php echo $aPointDetails['outlinestring'];?>',
			lon: <?php echo $aPointDetails['lon'];?>,
			lat: <?php echo $aPointDetails['lat'];?>,
		};

	</script>



	<?php include(CONST_BasePath.'/lib/template/includes/html-footer.php'); ?>
  </body>
</html>
