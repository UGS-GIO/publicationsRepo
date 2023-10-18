<?php
// Include the connect.php file
include ('connect.php');

// Connect to the database
$database = "publications";
$mysqli = new mysqli($hostname, $username, $password, $database);
/* check connection */
if (mysqli_connect_errno())
	{
	printf("Connect failed: %s\n", mysqli_connect_error());
	exit();
}

// PRELIMINARY SQL REQUEST. FROM ATTACHED-DATA TABLE
	$query2 = "SELECT series_id, extra_data, pub_url FROM AttachedData ORDER BY extra_data ASC/* WHERE extra_data NOT LIKE 'Cross Section' AND extra_data NOT LIKE 'Lithologic Column'*/";
	$result2 = $mysqli->prepare($query2);
	$result2->execute();
	$result2->bind_result($series_id, $extra_data, $url2);


	// loop through result and store into temporary array
	while ($result2->fetch()) {
		$urls[] = array(
			'series_id' => $series_id,
			'extra_data' => $extra_data,
			'url2' => $url2
		);
	}
	
	//print "<pre>";
	//print_r($urls);
	//print "</pre>";
	
  $result2->close();
  
  /*commenting during testing*/

$query = "SELECT series_id, pub_year, pub_name, pub_author, pub_sec_author, pub_url, pub_publisher, pub_scale, keywords, bookstore_url, quad_name, servName, cam_offset, lat, longitude, popupFL, pubPrevLink, pubPrevLink2, pubPrevLink3, series FROM UGSpubs WHERE series_id NOT LIKE 'WCD%' AND  ifnull(keywords,'') NOT LIKE '%emmd%' AND ifnull(keywords,'') NOT LIKE '%hmdc%' ORDER BY pub_year DESC, pub_month DESC";

//pub_scale = '1:24,000' AND quad_name IS NOT NULL AND pub_url IS NOT NULL ORDER BY quad_name ASC";

$result = $mysqli->prepare($query);


// If search query, make new sql request
$result->execute();
/* bind result variables */
//$result->bind_result($SeriesID, $PubYear, $PubName, $PubAuthor, $PubURL, $PubScale, $QuadName, $Keywords, $BookstoreURL);
$result->bind_result($SeriesID, $PubYear, $PubName, $PubAuthor, $PubSecAuthor, $PubURL, $PubPublisher, $PubScale, $KeyWords, $BookstoreURL, $QuadName, $ServiceName, $CameraOffset, $Latitude, $Longitude, $PopupFeatureLayer, $PubPrevLink, $pubPrevLink2, $pubPrevLink3, $Series);

// loop through each row in the main pub table
// and search the urls (attached data table) for matching series_id's, and add that data to the output.
while ($result->fetch()){
		
	include ('phpFunctions/authorComma.php');
		
	// search $seriesID values for values in urls[] array, and combine them.
	$string = "";		//clear the variable for each iteration
	$popupLink = "";		//clear the variable for each iteration
	$popupContent = "";
	
	//create link to publication for the top of the modal window
	if ( empty($PubURL) ||  is_null($PubURL) || $PubURL === null || $PubURL === 'undefined' || $PubURL === ' ' ) { 
		$popupContent = "";
	} else {
		$popupContent = "<div id=\\\"downloadLink\\\"><div id=\\\"leftAlign\\\"><a href=\\\"".$PubURL."\\\" target=\\\"_blank\\\">Publication</div><div id=\\\"rightAlign\\\"><img src=\\\"https://geology.utah.gov/docs/images/down-arrow.png\\\" width=\\\"16px\\\"></a></div></div><br><hr>";
		
		$popupLink = "<div id='clickMe' onclick='getElementById(\"modalText\").innerHTML =\"".$popupContent."\"'><img src=\"https://geology.utah.gov/docs/images/down-arrow.png\" width=\"16px\"></div>";
	}

	//add Vector Service or Image Service preview map url if any
	if ($ServiceName == '30x60_Quads' || $ServiceName == 'Other_Quads' || $ServiceName == 'FigureMaps') {
		$popupContent .= "<div id=\\\"downloadLink\\\"><div id=\\\"leftAlign\\\"><a href=\\\"https://geology.utah.gov/apps/intgeomap/index.html?sid=".$SeriesID."&layers=100k\\\" target=\\\"_blank\\\">Interactive Map</div><div id=\\\"rightAlign\\\"><img src=\\\"https://geology.utah.gov/docs/images/map.png\\\" width=\\\"16\\\"></a></div></div><br><hr>";
		//$popupContent .= "<div id=\\\"downloadLink\\\"><div id=\\\"leftAlign\\\"><a href=\\\"https://geology.utah.gov/apps/intgeomap/index.html?scale=318407&lat=".$Latitude."&lng=".$Longitude."&layers=100k%2Cfootprints&elev=171931\\\" target=\\\"_blank\\\">Interactive Map</div><div id=\\\"rightAlign\\\"><img src=\\\"https://geology.utah.gov/docs/images/map.png\\\" width=\\\"16\\\"></a></div></div><br><hr>";
	} else if ($ServiceName == '7_5_Quads' || $ServiceName == 'MD_24K') {
		$popupContent .= "<div id=\\\"downloadLink\\\"><div id=\\\"leftAlign\\\"><a href=\\\"https://geology.utah.gov/apps/intgeomap/index.html?sid=".$SeriesID."&layers=24k\\\" target=\\\"_blank\\\">Interactive Map</div><div id=\\\"rightAlign\\\"><img src=\\\"https://geology.utah.gov/docs/images/map.png\\\" width=\\\"16\\\"></a></div></div><br><hr>";
		//$popupContent .= "<div id=\\\"downloadLink\\\"><div id=\\\"leftAlign\\\"><a href=\\\"https://geology.utah.gov/apps/intgeomap/index.html?scale=76660&lat=".$Latitude."&lng=".$Longitude."&layers=24k%2Cfootprints&elev=38144\\\" target=\\\"_blank\\\">Interactive Map</div><div id=\\\"rightAlign\\\"><img src=\\\"https://geology.utah.gov/docs/images/map.png\\\" width=\\\"16\\\"></a></div></div><br><hr>";
	} else if ($ServiceName == '500k_Statewide') {
		$popupContent .= "<div id=\\\"downloadLink\\\"><div id=\\\"leftAlign\\\"><a href=\\\"https://geology.utah.gov/apps/intgeomap/index.html?sid=".$SeriesID."&layers=500k\\\" target=\\\"_blank\\\">Interactive Map</div><div id=\\\"rightAlign\\\"><img src=\\\"https://geology.utah.gov/docs/images/map.png\\\" width=\\\"16\\\"></a></div></div><br><hr>";
		//$popupContent .= "<div id=\\\"downloadLink\\\"><div id=\\\"leftAlign\\\"><a href=\\\"https://geology.utah.gov/apps/intgeomap/index.html?scale=1805815&lat=".$Latitude."&lng=".$Longitude."&layers=500k%2Cfootprints&elev=1000000\\\" target=\\\"_blank\\\">Interactive Map</div><div id=\\\"rightAlign\\\"><img src=\\\"https://geology.utah.gov/docs/images/map.png\\\" width=\\\"16\\\"></a></div></div><br><hr>";
	} /*else if ($ServiceName == 'MD_24K' && $PopupFeatureLayer != null){
		$popupContent .= "<div id=\\\"downloadLink\\\"><div id=\\\"leftAlign\\\"><a href=\\\"https://geology.utah.gov/apps/publications/map.html?servName=".$ServiceName."&mobLat=".$Latitude."&popupFL=".$PopupFeatureLayer."&lat=".$CameraOffset."&long=".$Longitude."&seriesID=".$SeriesID."&xsection=".$xsection."&lithcolumn=".$lithcolumn."\\\" target=\\\"_blank\\\">Interactive Map</div><div id=\\\"rightAlign\\\"><img src=\\\"https://geology.utah.gov/docs/images/map.png\\\" width=\\\"16\\\"></a></div></div><br><hr>";
	} else if ($ServiceName == 'MD_24K'){
		$popupContent .= "<div id=\\\"downloadLink\\\"><div id=\\\"leftAlign\\\"><a href=\\\"https://geology.utah.gov/apps/publications/map.html?servName=".$ServiceName."&mobLat=".$Latitude."&lat=".$CameraOffset."&long=".$Longitude."&seriesID=".$SeriesID."\\\" target=\\\"_blank\\\"></div>Interactive Map<div id=\\\"rightAlign\\\"><img src=\\\"https://geology.utah.gov/docs/images/map.png\\\" width=\\\"16\\\"></a></div></div><br><hr>";
	}*/ else {
		//$PreviewMapURL = "<something></something>";
		$popupContent .= "";
	};

	// array_search ( variable $needle , array $haystack )  //search for one variable/needle in another/haystack & returns the first key or true
	// array_column($array, 'column_key')   // Return the values from a single column in the input array
	// so below searches IF the SeriedID in the 'series_id' column of the url's array (attatched data table) we created above matches 
	// this is where we take the current seriesID in the pubs table and SEARCH the urls/attached data table to see if there's matching seriesID records
	// IF there is, THEN create an array of those matching values and loop through it to add data to it.
	if ( array_search($SeriesID, array_column($urls, 'series_id'))      ) {
		//  Return all the keys or a subset of the keys of an array
		$array = array_keys(array_column($urls, 'series_id'), $SeriesID);
		
		//print "<br>heres the array of matching AttachedData table records corresponding to the current iteration's of PubsTable seriesID<br>";
		//print "<pre>";
		//print_r($array);
		//print "</pre>"; 

		//$popupLink = ""; 
		$mapType = "";
		// $array is all records in the attachedData table that match with the SeriesID of the current loop iteration
		foreach($array as $key => $value) {
			//print "<br>heres the value<br>";
			//print "<pre>";
			//print_r($urls[$value]);
			//print "</pre>";
			
			//echo " ik " . $urls[$value]['extra_data'];
			if ($urls[$value]['extra_data'] == "Lithologic Column"){
				$lithcolumn = $urls[$value]['url2'];
			} else if ($urls[$value]['extra_data'] == "Cross Section"){
				$xsection = $urls[$value]['url2'];
			} else if (strpos($urls[$value]['url2'], 'http') !== false){
				$string .= "<a href='". $urls[$value]['url2'] ."' target='_blank'>". $urls[$value]['extra_data'] ."</a><br>";
			}
			else {
				$string .= "<a href='https://ugspub.nr.utah.gov/publications/". $urls[$value]['url2'] ."' target='_blank' download>". $urls[$value]['extra_data'] ."</a><br>";

			}
			
			//trying to get rid of https prefix when attachedData has a http in it
			if ((count($urls) >= 1) && (strpos($urls[$value]['url2'], 'http') !== false)){
				$popupContent .= "<div id=\\\"downloadLink\\\"><div id=\\\"leftAlign\\\"><a href=\\\"". $urls[$value]['url2'] ."\\\" target=\\\"_blank\\\">".$urls[$value]['extra_data']."</div><div id=\\\"rightAlign\\\"><img src=\\\"https://geology.utah.gov/docs/images/down-arrow.png\\\" width=\\\"16px\\\"></a></div></div><br><hr>";
				$popupLink = "<div id='clickMe' onclick='getElementById(\"modalText\").innerHTML =\"".$popupContent."\"'><img src=\"https://geology.utah.gov/docs/images/down-arrow.png\" width=\"16px\"></div>";
			} else {
				$popupContent .= "<div id=\\\"downloadLink\\\"><div id=\\\"leftAlign\\\"><a href=\\\"https://ugspub.nr.utah.gov/publications/". $urls[$value]['url2'] ."\\\" target=\\\"_blank\\\" download>".$urls[$value]['extra_data']."</div><div id=\\\"rightAlign\\\"><img src=\\\"https://geology.utah.gov/docs/images/down-arrow.png\\\" width=\\\"16px\\\"></a></div></div><br><hr>";
				//echo($popupContent);
			}
			
			if (count($urls) >= 1 && ((strpos($urls[$value]['extra_data'], 'GIS Data - Zip') !== false) || (strpos($urls[$value]['extra_data'], 'GeoTiff - Zip') !== false))){
				$popupLink = "<div id='clickMe' onclick='getElementById(\"modalText\").innerHTML =\"".$popupContent."\"'><img src=\"https://geology.utah.gov/docs/images/down-arrow.png\" width=\"16px\"></div>";
			} else if (count($urls) >= 1){
				$popupLink = "<div id='clickMe' onclick='getElementById(\"modalText\").innerHTML =\"".$popupContent."\"'><img src=\"https://geology.utah.gov/docs/images/down-arrow.png\" width=\"16px\"></div>";
			} //end if

			if (count($urls) >= 1 && ($urls[$value]['extra_data'] == "GeoTiff - Zip")){
				//$popupLink .= "<div style='display: none'>helloRaster</div>";
				$mapType .= "raster ";
			}
			if (count($urls) >= 1 && ($urls[$value]['extra_data'] == "GIS Data - Zip")){
				//$popupLink .= "<div style='display: none'>helloVector</div>";
				$mapType .= "vector ";
			}
			
		}  //end foreach

		// now append the appropriate 'hello' div to the popupLink
		if (strpos($mapType, 'raster') !== false) {
			$popupLink .= "<div style='display: none'>Raster Map </div>";
		}
		if (strpos($mapType, 'vector') !== false) {
			$popupLink .= "<div style='display: none'>Vector Map </div>";
		}

		//print "<pre>";
		//print_r($string);
		//print "</pre>";
	}  //end if

	//create link to DOI landing page at bottom of the modal window
	if ( strpos($SeriesID, 'MO-') !== false ) { 
		$noMoSeriesID = substr($SeriesID, 0, 4);
		$popupContent .= "<br><div id=\\\"downloadLink\\\"><div id=\\\"modalFooter\\\"><a href=\\\"https://doi.org/10.34191/".$noMoSeriesID."\\\" target=\\\"_blank\\\">https://doi.org/10.34191/".$noMoSeriesID."</a></div>";
		
		$popupLink = "<div id='clickMe' onclick='getElementById(\"modalText\").innerHTML =\"".$popupContent."\"'><img src=\"https://geology.utah.gov/docs/images/down-arrow.png\" width=\"16px\"></div>";
				
	} else if ( strpos($PubPublisher, 'UGS') !== false || strpos($PubPublisher, 'UGMS') !== false ) { 
		$popupContent .= "<br><div id=\\\"downloadLink\\\"><div id=\\\"modalFooter\\\"><a href=\\\"https://doi.org/10.34191/".$SeriesID."\\\" target=\\\"_blank\\\">https://doi.org/10.34191/".$SeriesID."</a></div>";
		
		$popupLink = "<div id='clickMe' onclick='getElementById(\"modalText\").innerHTML =\"".$popupContent."\"'><img src=\"https://geology.utah.gov/docs/images/down-arrow.png\" width=\"16px\"></div>";
	} else {
		
	}

	if ( empty($PubURL) ||  is_null($PubURL) || $PubURL === null || $PubURL === 'undefined' || $PubURL === ' ' ) { 
		$PubName = $PubName;
	} else {
		//$PubName = "<a href='".$PubURL."' target='_blank'><img src='https://geology.utah.gov/docs/images/pdf16x16.gif'></a> - ".$PubName;
		$PubName = $PubName." - <a href='".$PubURL."' target='_blank'>Download</a>";
	}
	/*if ( empty($PubURL) ||  is_null($PubURL) || $PubURL === null || $PubURL === 'undefined' || $PubURL === ' ' ) { 
		$PubURLString = "";
	} else {
		$PubURLString = "<a href='".$PubURL."' target='_blank'><img src='https://geology.utah.gov/docs/images/pdf16x16.gif'></a>";
	}*/
	if ( empty($BookstoreURL) ||  is_null($BookstoreURL) || $BookstoreURL === null || $BookstoreURL === 'undefined' || $BookstoreURL === ' ' ) { 
		$BookstoreURLString = "";
	} else if (strpos($SeriesID, 'MO-') !== false) {
		$noMoSeriesID = substr($SeriesID, 0, 4);
		$BookstoreURLString = "<a href='https://utahmapstore.com/products/".$noMoSeriesID."' target='_blank'><img src='https://geology.utah.gov/docs/images/buy.png' width='16'></a>";
	} else	{
		$BookstoreURLString = "<a href='https://utahmapstore.com/products/".$SeriesID."' target='_blank'><img src='https://geology.utah.gov/docs/images/buy.png' width='16'></a>";
	}
	
	//add Vector Service or Image Service preview map url if any
	/*if ($ServiceName == '30x60_Quads' || $ServiceName == '7_5_Quads' || $ServiceName == 'Other_Quads' || $ServiceName == 'FigureMaps') {
			$PreviewMapURL = " - <a class='fancybox' data-fancybox-type='iframe' href='https://geology.utah.gov/apps/publications/map.html?servName=".$ServiceName."&mobLat=".$Latitude."&popupFL=".$PopupFeatureLayer."&lat=".$CameraOffset."&long=".$Longitude."&seriesID=".$SeriesID."&xsection=".$xsection."&lithcolumn=".$lithcolumn."'><img src='https://geology.utah.gov/docs/images/map.png' width='16'></a>";
	} else if ($ServiceName == 'MD_24K' && $PopupFeatureLayer != null){
			$PreviewMapURL = " - <a class='fancybox' data-fancybox-type='iframe' href='https://geology.utah.gov/apps/publications/map.html?servName=".$ServiceName."&mobLat=".$Latitude."&popupFL=".$PopupFeatureLayer."&lat=".$CameraOffset."&long=".$Longitude."&seriesID=".$SeriesID."&xsection=".$xsection."&lithcolumn=".$lithcolumn."'><img src='https://geology.utah.gov/docs/images/map.png' width='16'></a>";
	} else if ($ServiceName == 'MD_24K'){
			$PreviewMapURL = " - <a class='fancybox' data-fancybox-type='iframe' href='https://geology.utah.gov/apps/publications/map.html?servName=".$ServiceName."&mobLat=".$Latitude."&lat=".$CameraOffset."&long=".$Longitude."&seriesID=".$SeriesID."' target='_blank'><img src='https://geology.utah.gov/docs/images/map.png' width='16'></a>";
	} else {
			//$PreviewMapURL = "<something></something>";
			$PreviewMapURL = "";
	};*/

	/*if ($PubPrevLink != null && $PubURL != null && preg_match("/iPhone|iPad|iPod|webOS/", $_SERVER['HTTP_USER_AGENT'])) {
		$PubPrevLinkURL = " - <a class='fancybox' data-fancybox-type='iframe' href='".$PubPrevLink."'>Publication</a>";
	} else if ($pubPrevLink3 != null){
		$PubPrevLinkURL = " - <a class='fancybox' data-fancybox-type='iframe' href='".$PubPrevLink."'>Booklet</a>&nbsp;&#124;&nbsp;<a class='fancybox' data-fancybox-type='iframe' href='".$pubPrevLink2."'>Plate 2</a><br><a class='fancybox' data-fancybox-type='iframe' href='".$pubPrevLink3."'>Plate 3</a>";
	} else if ($pubPrevLink2 != null && $pubPrevLink2 != "https://geology.utah.gov/apps/hazards"){
		$PubPrevLinkURL = " - <a class='fancybox' data-fancybox-type='iframe' href='".$PubPrevLink."'>Booklet</a>&nbsp;&#124;&nbsp;<a href='".$pubPrevLink2."'>Plate 2</a>";
	} else if ($PubPrevLink != null && $PubURL != null){
		$PubPrevLinkURL = " - <a class='fancybox' data-fancybox-type='iframe' href='".$PubPrevLink."'>Publication</a>";
	} else if ($PubPrevLink == null && $PubURL != null && preg_match("/iPhone|iPad|iPod|webOS/", $_SERVER['HTTP_USER_AGENT'])) {
		$PubPrevLinkURL = " - <a href='".$PubURL."' target='_blank'>Publication</a>";
	} else if ($PubPrevLink == null && $PubURL != null){
		$PubPrevLinkURL = " - <a class='fancybox' data-fancybox-type='iframe' href='".$PubURL."'>Publication</a>";
	} else if ($PubPrevLink == null && $PubURL == null) {
		$PubPrevLinkURL = "<something></something>";
	};*/
	
	//$PubPrevLinkURL = $PubURL; //Here for testing speed w/o the if/then statements
	
	// ASIGN SQL DATA TO PHP VARIABLES AND PUT IN ARRAY TO SEND TO HTML PAGE
	/*$alldata[] = array(
		'series_id' => $SeriesID,
		'pub_year' => $PubYear,
		'pub_name' => $PubName,
		'pub_author' => $PubAuthor,
		'pub_scale' => $PubScale,
		'quad_name' => $QuadName,
		'keywords' => $Keywords,
		'pdf_link4AlphList' => $PubURLString,
		'buy_link4AlphList' => $BookstoreURLString
		'gis_link' => $popupLink
		//'sLayer' => $ServiceLayer,
		//'servName' => $ServiceName,
		//'cam_offset' => $Latitude,
		//'long' => $Longitude,
		//'popupFL' => $PopupFeatureLayer
	);*/
	
	// ASIGN SQL DATA TO PHP VARIABLES AND PUT IN ARRAY TO SEND TO HTML PAGE
	$alldata[] = array(
		'series_id' => $SeriesID,
		'pub_year' => $PubYear,
		'pub_name' => $PubName,
		//'map_preview' => $PreviewMapURL,
		//'pub_preview' => $PubPrevLinkURL,
		'pub_author' => $PubAuthor . $PubSecAuthorString,
		'pub_scale' => $PubScale,
		//'notes' => $PubNotes,
		'keywords' => $KeyWords,
		//'pdf_link4AlphList' => $PubURLString . $PreviewMapURL,
		'buy_link4AlphList' => $BookstoreURLString,
		'series' => $Series,
		'linksInPopup' => $popupLink,
		//'quad_name' => $QuadName,
		//'pub_name_basic' => $PubName,
		//'pub_url' => $PubURL,
		//'pdf_link4AlphList' => "<a href='".$PubURL."' target='_blank'><img src='docs/images/pdf16x16.gif'></a>",
		//'buy_link4AlphList' => "<a href='https://utahmapstore.com/products/".$SeriesID."' target='_blank'><img src='docs/images/buy.png'></a>"
		//'sLayer' => $ServiceLayer,
		//'servName' => $ServiceName,
		//'cam_offset' => $Latitude,
		//'long' => $Longitude,
		//'popupFL' => $PopupFeatureLayer
	);

}

	//echo "<br><br><br><br><br><br>THIS IS THE FINAL DATA DUMP<br>";
	echo json_encode($alldata);
	//print "<pre>";
	//print_r( $alldata );
	//print "</pre>";


/* close statement */
$result->close();
/* close connection */
$mysqli->close();
?>



