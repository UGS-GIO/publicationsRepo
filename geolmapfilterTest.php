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

$query = "SELECT series_id, pub_year, pub_name, pub_author, pub_url, pub_scale, quad_name, keywords FROM UGSpubs WHERE keywords LIKE '%geoindex%' ORDER BY quad_name ASC";

//pub_scale = '1:24,000' AND quad_name IS NOT NULL AND pub_url IS NOT NULL ORDER BY quad_name ASC";

$result = $mysqli->prepare($query);


	// If search query, make new sql request

	
	$result->execute();
	/* bind result variables */
	$result->bind_result($SeriesID, $PubYear, $PubName, $PubAuthor, $PubURL, $PubScale, $QuadName, $Keywords);

	while ($result->fetch())
		{
		// search $seriesID values for values in urls[] array, and combine them.
			$string = "";		//clear the variable for each iteration
			
			if ( array_search($SeriesID, array_column($urls, 'series_id'))      ) {
					$array = array_keys(array_column($urls, 'series_id'), $SeriesID);
					foreach($array as $key => $value) {
						//echo " ik " . $urls[$value]['extra_data'];
						if ($urls[$value]['extra_data'] == "GIS Data - Zip"){
							$string .= "<a href='https://ugspub.nr.utah.gov/publications/". $urls[$value]['url2'] ."' target='_blank' download><img src='https://geology.utah.gov/docs/images/zip16x16.gif'></a>";
						}//end if
					}  //end foreach
					/*print "<pre>";
					print_r($string);
					print "</pre>";*/
			}  //end if
		
		// ASIGN SQL DATA TO PHP VARIABLES AND PUT IN ARRAY TO SEND TO HTML PAGE
		$alldata[] = array(
			'series_id' => $SeriesID,
			'pub_year' => $PubYear,
			'pub_name' => $PubName,
			'pub_author' => $PubAuthor,
			'pub_scale' => $PubScale,
			'quad_name' => $QuadName,
			'keywords' => $Keywords,
			'pdf_link4AlphList' => "<a href='".$PubURL."' target='_blank'><img src='https://geology.utah.gov/docs/images/pdf16x16.gif'></a>",
			'buy_link4AlphList' => "<a href='https://utahmapstore.com/products/".$SeriesID."' target='_blank'><img src='https://geology.utah.gov/docs/images/buy.png'></a>",
			'gis_link' => $string/*
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



