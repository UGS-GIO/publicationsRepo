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
// we dump the ENTIRE attached data table here, and then we'll add the related fields in to the main table next...
	$query2 = "SELECT series_id, extra_data, pub_url FROM AttachedData/* WHERE extra_data NOT LIKE 'Cross Section' AND extra_data NOT LIKE 'Lithologic Column'*/";
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
  
  /*commenting during testing*/


// NOW WE DO OUR MAIN SQL CALL AND GET THE PUB TABLE
$query = "SELECT series_id, pub_year, pub_name, pub_author, pub_sec_author, pub_url, pub_scale, keywords, bookstore_url FROM publications.UGSpubs WHERE keywords LIKE '%emmd%' ORDER BY pub_year DESC";
//pub_scale = '1:24,000' AND quad_name IS NOT NULL AND pub_url IS NOT NULL ORDER BY quad_name ASC";
$result = $mysqli->prepare($query);

// If search query, make new sql request
	
$result->execute();
/* bind result variables */
$result->bind_result($SeriesID, $PubYear, $PubName, $PubAuthor, $PubSecAuthor, $PubURL, $PubScale, $Keywords, $BookstoreURL);

	// loop through each row in the main pub table
	while ($result->fetch())
	{
		//add comma between primary and secondary authors if any
		if ( empty($PubSecAuthor) ||  is_null($PubSecAuthor) || $PubSecAuthor === null || $PubSecAuthor === 'undefined' || $PubSecAuthor === ' ' ) {
			$PubSecAuthorString = "";
		//echo "trouble at: ".$SeriesID."AHH!  ";
		} else {
			$PubSecAuthorString = ', '. $PubSecAuthor;
		};
		
		// search the urls[] array (attached data table) for this seriesID, and combine the attached data into the pubs list here.
		$popupLink = "";		//clear the variable for each iteration
		$popupContent = "";
		
		if ( empty($PubURL) ||  is_null($PubURL) || $PubURL === null || $PubURL === 'undefined' || $PubURL === ' ' ) { 
			$PubURLString = "";
		} else if (array_search($SeriesID, array_column($urls, 'series_id'))){
			$PubURLString = "<a href='".$PubURL."' target='_blank'><img src='https://geology.utah.gov/docs/images/pdf16x16.gif'></a>";
			$array = array_keys(array_column($urls, 'series_id'), $SeriesID);
			foreach($array as $key => $value) {
				//echo " ik " . $urls[$value]['extra_data'];
				//trying to get rid of https prefix when attachedData has a http in it
				if ((count($urls) >= 1) && (strpos($urls[$value]['url2'], 'http') !== false)){
					$popupContent .= "<div id=\\\"downloadLink\\\"><div id=\\\"leftAlign\\\"><a href=\\\"". $urls[$value]['url2'] ."\\\" target=\\\"_blank\\\">".$urls[$value]['extra_data']."</div><div id=\\\"rightAlign\\\"><img src=\\\"https://geology.utah.gov/docs/images/down-arrow.png\\\" width=\\\"16px\\\"></a></div></div><br><hr>";
					$popupLink = "<div id='clickMe' onclick='getElementById(\"modalText\").innerHTML =\"".$popupContent."\"'><img src=\"https://geology.utah.gov/docs/images/down-arrow.png\" width=\"16px\"></div>";
				} else if (count($urls) >= 1){
					$popupContent .= "<div id=\\\"downloadLink\\\"><div id=\\\"leftAlign\\\"><a href=\\\"https://ugspub.nr.utah.gov/publications/". $urls[$value]['url2'] ."\\\" target=\\\"_blank\\\" download>".$urls[$value]['extra_data']."</div><div id=\\\"rightAlign\\\"><img src=\\\"https://geology.utah.gov/docs/images/down-arrow.png\\\" width=\\\"16px\\\"></a></div></div><br><hr>";
					$popupLink = "<div id='clickMe' onclick='getElementById(\"modalText\").innerHTML =\"".$popupContent."\"'><img src=\"https://geology.utah.gov/docs/images/down-arrow.png\" width=\"16px\"></div>";
				}//end if
			}				
		} else{
			$PubURLString = "<a href='".$PubURL."' target='_blank'><img src='https://geology.utah.gov/docs/images/pdf16x16.gif'></a>"; 
		} 
		
		/*if ( array_search($SeriesID, array_column($urls, 'series_id'))      ) {
				$array = array_keys(array_column($urls, 'series_id'), $SeriesID);
				foreach($array as $key => $value) {
					//echo " ik " . $urls[$value]['extra_data'];
					if ($urls[$value]['extra_data'] == "GIS Data - Zip"){
						$string .= "<a href='https://ugspub.nr.utah.gov/publications/". $urls[$value]['url2'] ."' target='_blank' download><img src='https://geology.utah.gov/docs/images/zip16x16.gif'></a><div style='display: none'>hello</div>";
					}//end if
				}*/  //end foreach
				/*print "<pre>";
				print_r($string);
				print "</pre>";*/
		//}  //end if
		//if ( empty($PubURL) ||  is_null($PubURL) || $PubURL === null || $PubURL === 'undefined' || $PubURL === ' ' ) { 
		//	$PubURLString = "";
		//} else {
			/* $PubURLString = "<a href='".$PubURL."' target='_blank'><img src='https://geology.utah.gov/docs/images/pdf16x16.gif'></a>"; */
		//	$PubURLString = "<a href='".$PubURL."' target='_blank'>PDF</a>";
		//}
		if ( empty($BookstoreURL) ||  is_null($BookstoreURL) || $BookstoreURL === null || $BookstoreURL === 'undefined' || $BookstoreURL === ' ' ) { 
			$BookstoreURLString = "";
		} else {
			$BookstoreURLString = "<a href='https://utahmapstore.com/products/".$SeriesID."' target='_blank'><img src='https://geology.utah.gov/docs/images/buy.png' width='16'></a>";
		}
		
		// ASIGN SQL DATA TO PHP VARIABLES AND PUT IN ARRAY TO SEND TO HTML PAGE
		$alldata[] = array(
			'series_id' => $SeriesID,
			'pub_year' => $PubYear,
			'pub_name' => /*"<a href='" . $PubURL . "' target='_blank'>" . */$PubName/* . "</a>"*/,
			'pub_author' => $PubAuthor . $PubSecAuthorString,
			'pub_scale' => $PubScale,
			'keywords' => $Keywords,
			'pdf_link4AlphList' => $PubURLString,
			'buy_link4AlphList' => $BookstoreURLString,
			'dLpopOver' => $popupContent,
			'attached_data' => $popupLink/*,
			'sLayer' => $ServiceLayer,
			'servName' => $ServiceName,
			'cam_offset' => $Latitude,
			'long' => $Longitude,
			'popupFL' => $PopupFeatureLayer*/
		);

	}
	echo json_encode($alldata);


/* close statement */
$result->close();
/* close connection */
$mysqli->close();
?>



