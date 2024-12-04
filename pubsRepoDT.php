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
	
$result2->close();

$query = "SELECT series_id, pub_year, pub_name, pub_author, pub_sec_author, pub_url, pub_publisher, pub_scale, keywords, bookstore_url, quad_name, servName, cam_offset, lat, longitude, popupFL, pubPrevLink, pubPrevLink2, pubPrevLink3, series FROM UGSpubs WHERE series_id NOT LIKE 'WCD%' AND  ifnull(keywords,'') NOT LIKE '%emmd%' AND ifnull(keywords,'') NOT LIKE '%hmdc%' ORDER BY pub_year DESC, pub_month DESC";

$result = $mysqli->prepare($query);


// If search query, make new sql request
$result->execute();

// bind result variables
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
	if ( (count($urls) >= 1) || empty($PubURL) ||  is_null($PubURL) || $PubURL === null || $PubURL === 'undefined' || $PubURL === ' ' ) { 
		$popupContent = "";
	} else {
		$popupContent = "<div id=\\\"downloadLink\\\"><div id=\\\"leftAlign\\\"><a href=\\\"".$PubURL."\\\" target=\\\"_blank\\\">Publication</div><div id=\\\"rightAlign\\\"><img src=\\\"https://geology.utah.gov/docs/images/down-arrow.png\\\" width=\\\"16px\\\"></a></div></div><br><hr>";
		
		$popupLink = "<div id='clickMe' onclick='getElementById(\"modalText\").innerHTML =\"".$popupContent."\"'><img src=\"https://geology.utah.gov/docs/images/down-arrow.png\" width=\"16px\"></div>";
	}

	//add Vector Service or Image Service preview map url if any
	if ($ServiceName == '30x60_Quads' || $ServiceName == 'Other_Quads' || $ServiceName == 'FigureMaps') {
		$popupContent .= "<div id=\\\"downloadLink\\\"><div id=\\\"leftAlign\\\"><a href=\\\"https://geology.utah.gov/apps/intgeomap/index.html?sid=".$SeriesID."&layers=100k\\\" target=\\\"_blank\\\">Interactive Map</div><div id=\\\"rightAlign\\\"><img src=\\\"https://geology.utah.gov/docs/images/map.png\\\" width=\\\"16\\\"></a></div></div><br><hr>";
	} else if ($ServiceName == '7_5_Quads' || $ServiceName == 'MD_24K') {
		$popupContent .= "<div id=\\\"downloadLink\\\"><div id=\\\"leftAlign\\\"><a href=\\\"https://geology.utah.gov/apps/intgeomap/index.html?sid=".$SeriesID."&layers=24k\\\" target=\\\"_blank\\\">Interactive Map</div><div id=\\\"rightAlign\\\"><img src=\\\"https://geology.utah.gov/docs/images/map.png\\\" width=\\\"16\\\"></a></div></div><br><hr>";
	} else if ($ServiceName == '500k_Statewide') {
		$popupContent .= "<div id=\\\"downloadLink\\\"><div id=\\\"leftAlign\\\"><a href=\\\"https://geology.utah.gov/apps/intgeomap/index.html?sid=".$SeriesID."&layers=500k\\\" target=\\\"_blank\\\">Interactive Map</div><div id=\\\"rightAlign\\\"><img src=\\\"https://geology.utah.gov/docs/images/map.png\\\" width=\\\"16\\\"></a></div></div><br><hr>";
	} else {
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
		$mapType = "";
		
		// $array is all records in the attachedData table that match with the SeriesID of the current loop iteration
		foreach($array as $key => $value) {
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
			}
			
			if (count($urls) >= 1 && ((strpos($urls[$value]['extra_data'], 'GIS Data - Zip') !== false) || (strpos($urls[$value]['extra_data'], 'GeoTiff - Zip') !== false))){
				$popupLink = "<div id='clickMe' onclick='getElementById(\"modalText\").innerHTML =\"".$popupContent."\"'><img src=\"https://geology.utah.gov/docs/images/down-arrow.png\" width=\"16px\"></div>";
			} else if (count($urls) >= 1){
				$popupLink = "<div id='clickMe' onclick='getElementById(\"modalText\").innerHTML =\"".$popupContent."\"'><img src=\"https://geology.utah.gov/docs/images/down-arrow.png\" width=\"16px\"></div>";
			} //end if

			if (count($urls) >= 1 && ($urls[$value]['extra_data'] == "GeoTiff - Zip")){
				$mapType .= "raster ";
			}
			if (count($urls) >= 1 && ($urls[$value]['extra_data'] == "GIS Data - Zip")){
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
	}  //end if

	//create link to DOI landing page
	if ( strpos($SeriesID, 'MO-') !== false ) { 
		$noMoSeriesID = substr($SeriesID, 0, 4);
		$doiLink = "https://doi.org/10.34191/".$noMoSeriesID;			
	} else if ( (strpos($PubPublisher, 'UGS') !== false || strpos($PubPublisher, 'UGMS') !== false) && strpos($SeriesID, 'HD-') === false) { 
		$doiLink = "https://doi.org/10.34191/".$SeriesID;
	} else {
		
	}

	if ( empty($PubURL) ||  is_null($PubURL) || $PubURL === null || $PubURL === 'undefined' || $PubURL === ' ' ) { 
		$PubName = $PubName;
	} else {
		//$PubName = "<a href='".$PubURL."' target='_blank'><img src='https://geology.utah.gov/docs/images/pdf16x16.gif'></a> - ".$PubName;
		$PubName = "<div class='pubTitle'><a href='".$PubURL."' target='_blank'>".$PubName." <img src='https://geology.utah.gov/docs/images/pdf16x16.gif'></a></div><div class='smallDOI'><a href='".$doiLink."' target='_blank'>https://doi.org/10.34191/".$SeriesID."</a></div>";
	}
	if ( empty($BookstoreURL) ||  is_null($BookstoreURL) || $BookstoreURL === null || $BookstoreURL === 'undefined' || $BookstoreURL === ' ' ) { 
		$BookstoreURLString = "";
	} else if (strpos($SeriesID, 'MO-') !== false) {
		$noMoSeriesID = substr($SeriesID, 0, 4);
		$BookstoreURLString = "<a href='https://utahmapstore.com/products/".$noMoSeriesID."' target='_blank'><img src='https://geology.utah.gov/docs/images/buy.png' width='16'></a>";
	} else	{
		$BookstoreURLString = "<a href='https://utahmapstore.com/products/".$SeriesID."' target='_blank'><img src='https://geology.utah.gov/docs/images/buy.png' width='16'></a>";
	}
	
	// ASIGN SQL DATA TO PHP VARIABLES AND PUT IN ARRAY TO SEND TO HTML PAGE
	$alldata[] = array(
		'series_id' => $SeriesID,
		'pub_year' => $PubYear,
		'pub_name' => $PubName,
		'pub_author' => $PubAuthor . $PubSecAuthorString,
		'pub_scale' => $PubScale,
		'keywords' => $KeyWords,
		'buy_link4AlphList' => $BookstoreURLString,
		'series' => $Series,
		'linksInPopup' => $popupLink
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



