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

$query = "SELECT series_id, pub_year, pub_name, pub_author, pub_sec_author, pub_url, pub_publisher, pub_scale, quad_name, keywords, bookstore_url, servName FROM UGSpubs WHERE keywords LIKE '%geoindex%' ORDER BY TRIM(LEADING 'The ' FROM quad_name)";

//pub_scale = '1:24,000' AND quad_name IS NOT NULL AND pub_url IS NOT NULL ORDER BY quad_name ASC";

$result = $mysqli->prepare($query);


	// If search query, make new sql request
	$result->execute();
	/* bind result variables */
	$result->bind_result($SeriesID, $PubYear, $PubName, $PubAuthor, $PubSecAuthor, $PubURL, $PubPublisher, $PubScale, $QuadName, $KeyWords, $BookstoreURL, $ServiceName);

	// loop through each row in the main pub table
	// and search the urls (attached data table) for matching series_id's, and add that data to the output.
	while ($result->fetch())
		{
			//add comma between primary and secondary authors if any
			if ( empty($PubSecAuthor) ||  is_null($PubSecAuthor) || $PubSecAuthor === null || $PubSecAuthor === 'undefined' || $PubSecAuthor === ' ' ) {
				$PubSecAuthorString = "";
			//echo "trouble at: ".$SeriesID."AHH!  ";
			} else {
				$PubSecAuthorString = ', '. $PubSecAuthor;
			};	
			
			// search $seriesID values for values in urls[] array, and combine them.
			$string = "";		//clear the variable for each iteration
			$popupLink = "";		//clear the variable for each iteration
			$popupContent = "";
			$popupContentDOI = "";			
						
			//create link to DOI landing page at bottom of the modal window
			if ( strpos($SeriesID, 'MO-') !== false ) { 
				$noMoSeriesID = substr($SeriesID, 0, 4);
				$popupContentDOI .= "<br><div id=\\\"downloadLink\\\"><div id=\\\"modalFooter\\\"><a href=\\\"https://doi.org/10.34191/".$noMoSeriesID."\\\" target=\\\"_blank\\\">https://doi.org/10.34191/".$noMoSeriesID."</a></div>";
				
			} else if ( strpos($PubPublisher, 'UGS') !== false || strpos($PubPublisher, 'UGMS') !== false ) { 
				$popupContentDOI .= "<br><div id=\\\"downloadLink\\\"><div id=\\\"modalFooter\\\"><a href=\\\"https://doi.org/10.34191/".$SeriesID."\\\" target=\\\"_blank\\\">https://doi.org/10.34191/".$SeriesID."</a></div>";
				
			} else {
				
			}
			
			//create link to publication for the top of the modal window and DOI link at bottom of modal window
			if ( empty($PubURL) ||  is_null($PubURL) || $PubURL === null || $PubURL === 'undefined' || $PubURL === ' ' ) { 
				$popupContent = "";
			} else {
				$popupContent = "<div id=\\\"downloadLink\\\"><div id=\\\"leftAlign\\\"><a href=\\\"".$PubURL."\\\" target=\\\"_blank\\\">Publication</div><div id=\\\"rightAlign\\\"><img src=\\\"https://geology.utah.gov/docs/images/down-arrow.png\\\" width=\\\"16px\\\"></a></div></div><br><hr>";
				
				//$popupContentDOI .= "<br><div id=\\\"downloadLink\\\"><div id=\\\"modalFooter\\\"><a href=\\\"https://doi.org/10.34191/".$SeriesID."\\\" target=\\\"_blank\\\">https://doi.org/10.34191/".$SeriesID."</a></div>";
				
				$popupLink = "<div id='clickMe' onclick='getElementById(\"modalText\").innerHTML =\"".$popupContent.$popupContentDOI."\"'><img src=\"https://geology.utah.gov/docs/images/down-arrow.png\" width=\"16px\"></div>";
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
					
					/* print "<br>heres the array of matching AttachedData table records corresponding to the current iteration's of PubsTable seriesID<br>";
					print "<pre>";
					print_r($array);
					print "</pre>"; 
					*/

				    //$popupLink = ""; 
					$mapType = "";
					// $array is all records in the attachedData table that match with the SeriesID of the current loop iteration
					foreach($array as $key => $value) {
						/* print "<br>heres the value<br>";
						print "<pre>";
						print_r($urls[$value]);
						print "</pre>"; */
						
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
						/*if ((count($urls) >= 1) && (strpos($urls[$value]['url2'], 'http') !== false)){
							$popupContent .= "<div id=\\\"downloadLink\\\"><div id=\\\"leftAlign\\\"><a href=\\\"". $urls[$value]['url2'] ."\\\" target=\\\"_blank\\\">".$urls[$value]['extra_data']."</div><div id=\\\"rightAlign\\\"><img src=\\\"https://geology.utah.gov/docs/images/down-arrow.png\\\" width=\\\"16px\\\"></a></div></div><br><hr>";
							$popupLink = "<div id='clickMe' onclick='getElementById(\"modalText\").innerHTML =\"".$popupContent."\"'><img src=\"https://geology.utah.gov/docs/images/down-arrow.png\" width=\"16px\"></div>";
						} else {
							$popupContent .= "<div id=\\\"downloadLink\\\"><div id=\\\"leftAlign\\\"><a href=\\\"https://ugspub.nr.utah.gov/publications/". $urls[$value]['url2'] ."\\\" target=\\\"_blank\\\" download>".$urls[$value]['extra_data']."</div><div id=\\\"rightAlign\\\"><img src=\\\"https://geology.utah.gov/docs/images/down-arrow.png\\\" width=\\\"16px\\\"></a></div></div><br><hr>";
							//echo($popupContent);
						}*/
						
						if ((count($urls) >= 1) && (strpos($urls[$value]['url2'], 'http') !== false)){
							$popupContent .= "<div id=\\\"downloadLink\\\"><div id=\\\"leftAlign\\\"><a href=\\\"". $urls[$value]['url2'] ."\\\" target=\\\"_blank\\\">".$urls[$value]['extra_data']."</div><div id=\\\"rightAlign\\\"><img src=\\\"https://geology.utah.gov/docs/images/down-arrow.png\\\" width=\\\"16px\\\"></a></div></div><br><hr>";
							$popupLink = "<div id='clickMe' onclick='getElementById(\"modalText\").innerHTML =\"".$popupContent.$popupContentDOI."\"'><img src=\"https://geology.utah.gov/docs/images/down-arrow.png\" width=\"16px\"></div>";
						} else {
							$popupContent .= "<div id=\\\"downloadLink\\\"><div id=\\\"leftAlign\\\"><a href=\\\"https://ugspub.nr.utah.gov/publications/". $urls[$value]['url2'] ."\\\" target=\\\"_blank\\\" download>".$urls[$value]['extra_data']."</div><div id=\\\"rightAlign\\\"><img src=\\\"https://geology.utah.gov/docs/images/down-arrow.png\\\" width=\\\"16px\\\"></a></div></div><br><hr>";
							//echo($popupContent);
						}
						
						/*if (count($urls) >= 1 && ((strpos($urls[$value]['extra_data'], 'GIS Data - Zip') !== false) || (strpos($urls[$value]['extra_data'], 'GeoTiff - Zip') !== false))){
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
						}*/
						
						if (count($urls) >= 1 && ((strpos($urls[$value]['extra_data'], 'GIS Data - Zip') !== false) || (strpos($urls[$value]['extra_data'], 'GeoTiff - Zip') !== false))){
							$popupLink = "<div id='clickMe' onclick='getElementById(\"modalText\").innerHTML =\"".$popupContent.$popupContentDOI."\"'><img src=\"https://geology.utah.gov/docs/images/down-arrow.png\" width=\"16px\"></div>";
						} else if (count($urls) >= 1){
							$popupLink = "<div id='clickMe' onclick='getElementById(\"modalText\").innerHTML =\"".$popupContent.$popupContentDOI."\"'><img src=\"https://geology.utah.gov/docs/images/down-arrow.png\" width=\"16px\"></div>";
						} //end if

						if (count($urls) >= 1 && ($urls[$value]['extra_data'] == "GeoTiff - Zip")){
							//$popupLink .= "<div style='display: none'>Raster Map</div>";
							$mapType .= "raster ";
						}
						if (count($urls) >= 1 && ($urls[$value]['extra_data'] == "GIS Data - Zip")){
							//$popupLink .= "<div style='display: none'>Vector Map</div>";
							$mapType .= "vector ";
						}
						
					}  //end foreach

					/*// now append the appropriate 'hello' div to the popupLink
					if (strpos($mapType, 'raster') !== false) {
						$popupLink .= "<div style='display: none'>helloRaster</div>";
					}
					if (strpos($mapType, 'vector') !== false) {
						$popupLink .= "<div style='display: none'>helloVector</div>";
					}*/
					
					// now append the appropriate 'hello' div to the popupLink
					if (strpos($mapType, 'raster') !== false) {
						$popupLink .= "<div style='display: none'>Raster Map </div>";
						
					}
					if (strpos($mapType, 'vector') !== false) {
						$popupLink .= "<div style='display: none'>Vector Map </div>";
						
					}

					/*print "<pre>";
					print_r($string);
					print "</pre>";*/
			}  //end if
			
			if ( empty($PubURL) ||  is_null($PubURL) || $PubURL === null || $PubURL === 'undefined' || $PubURL === ' ' ) { 
				$PubName = $PubName;
			} else {
				//$PubName = "<a href='".$PubURL."' target='_blank'><img src='https://geology.utah.gov/docs/images/pdf16x16.gif'></a> - ".$PubName;
				$PubName = $PubName." - <a href='".$PubURL."' target='_blank'>Download</a>";
			}
			
			/*if ( empty($BookstoreURL) ||  is_null($BookstoreURL) || $BookstoreURL === null || $BookstoreURL === 'undefined' || $BookstoreURL === ' ' ) { 
				$BookstoreURLString = "";
			} else if (strpos($SeriesID, 'MO-1') !== false) {
				$BookstoreURLString = "<a href='https://utahmapstore.com/products/MO-1' target='_blank'><img src='https://geology.utah.gov/docs/images/buy.png' width='16'></a>";
			} else if (strpos($SeriesID, 'MO-3') !== false) {
				$BookstoreURLString = "<a href='https://utahmapstore.com/products/MO-3' target='_blank'><img src='https://geology.utah.gov/docs/images/buy.png' width='16'></a>";
			} else	{
				$BookstoreURLString = "<a href='https://utahmapstore.com/products/".$SeriesID."' target='_blank'><img src='https://geology.utah.gov/docs/images/buy.png' width='16'></a>";
			}*/
			
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
			/*'series_id' => $SeriesID,
			'pub_year' => $PubYear,
			'pub_name' => $PubName,
			'pub_author' => $PubAuthor,
			'pub_scale' => $PubScale,
			'quad_name' => $QuadName,
			'keywords' => $KeyWords,
			'pdf_link4AlphList' => $PubURLString,
			'buy_link4AlphList' => $BookstoreURLString,
			'gis_link' => $popupLink*/
			'quad_name' => $QuadName,
			'series_id' => $SeriesID,
			'pub_year' => $PubYear,
			'pub_name' => $PubName,
			'pub_author' => $PubAuthor . $PubSecAuthorString,
			'pub_scale' => $PubScale,
			'keywords' => $KeyWords,
			'buy_link4AlphList' => $BookstoreURLString,
			'linksInPopup' => $popupLink,
			'mapType' => $mapType
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



