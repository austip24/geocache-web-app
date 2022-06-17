<!DOCTYPE html>
<!--
	Name: Austin Pierson
	Course: CSCV337
	Date: 7 May, 2019
	Description: PHP file for google maps application. User can enter latitude, longitude, some distance in miles, select cache_types and difficulties. Submitting this information will allow the user to see the locations of nearby geocaches based on the information. A table will be displayed containing all geogaches and their information, and a marker will be placed at the location on the google map. The user can click on rows in the table, or on markers to observe additional information about the geocache.
-->
<html>
	<head>
	<title>Geocache Locator</title>

	<meta name="viewport" content="initial-scale=1.0">
	<meta charset="utf-8">
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Language" content="en-us" />

	<link rel="stylesheet" href="maps.css" type="text/css" />

	<script src="http://u.arizona.edu/~lxu/cscv337/sp18/hw6/js/prototype.js" type="text/javascript"></script>
	<script src="http://u.arizona.edu/~lxu/cscv337/sp18/hw6/js/scriptaculous.js" type="text/javascript"></script>
	<script src="maps.js" type="text/javascript"></script>
	<script defer src="https://maps.googleapis.com/maps/api/js?key=some-api-key&callback=initMap"></script>
		
	</head>

	<body>
			<?php
				$db = new PDO("mysql:host=150.135.53.5;dbname=test;port=3306", "student", "B3@rD0wn!");

				#query parameters
				$radius = updateRadius();

				if ((isset($_GET["latitude"]) && !empty($_GET["latitude"]))
					&& (isset($_GET["longitude"]) && !empty($_GET["longitude"]))
					&& (isset($_GET["cache_type"]) && !empty($_GET["cache_type"]))
					&& (isset($_GET["difficulty"]) && !empty($_GET["difficulty"])))
				{
					$min_lat = getMinLat($radius);
					$min_lng = getMinLng($radius);

					$max_lat = getMaxLat($radius);
					$max_lng = getMaxLng($radius);

					$cache_type = "'" . $_GET["cache_type"] . "'";
					for ($i = 0; $i < strlen($cache_type); $i++) {
						if ($cache_type[$i] === "_") {
							$cache_type[$i] = "/";
						}
					}

					$difficulty = $_GET["difficulty"];

					#queries database differently depending on cache_type and difficulty entered
					if ($_GET["cache_type"] === "all" && $_GET["difficulty"] === "any") { #all difficulties and cache_types
						$query = "SELECT t.latitude, t.longitude, t.difficulty_rating, c.cache_type
								FROM test_data t
								JOIN cache_types c ON c.type_id = t.cache_type_id
								WHERE t.latitude BETWEEN $min_lat AND $max_lat
								AND t.longitude BETWEEN $min_lng AND $max_lng";
					}
					else if ($_GET["cache_type"] === "all") {	#all cache_types, specific difficulty (1-10)
						$query = "SELECT t.latitude, t.longitude, t.difficulty_rating, c.cache_type
								FROM test_data t
								JOIN cache_types c ON c.type_id = t.cache_type_id
								WHERE t.difficulty_rating = $difficulty
								AND t.latitude BETWEEN $min_lat AND $max_lat
								AND t.longitude BETWEEN $min_lng AND $max_lng";
					}
					else if ($_GET["difficulty"] === "any") {	#all difficulties, specific cache_type
						$query = "SELECT t.latitude, t.longitude, t.difficulty_rating, c.cache_type
								FROM test_data t
								JOIN cache_types c ON c.type_id = t.cache_type_id
								WHERE c.cache_type = $cache_type
								AND t.latitude BETWEEN $min_lat AND $max_lat
								AND t.longitude BETWEEN $min_lng AND $max_lng";
					}
					else {	#specific cache_type and specific difficulty (1-10)
						$query = "SELECT t.latitude, t.longitude, t.difficulty_rating, c.cache_type
								FROM test_data t
								JOIN cache_types c ON c.type_id = t.cache_type_id
								WHERE c.cache_type = $cache_type
								AND t.difficulty_rating = $difficulty
								AND t.latitude BETWEEN $min_lat AND $max_lat
								AND t.longitude BETWEEN $min_lng AND $max_lng";
					}
				

				$results = $db->query($query);

				#place query data into html div for use in javascript
				$rows = $results->fetchAll();
				$data = json_encode($rows);
				echo '<div id="data">' . $data . '</div>';
			}

			?>
			<div id="description">
				<h1>Geocache Locator</h1>
				<p>Enter information below to search for Geocaches.</p>

				<form action="maps.php" id="searchform" method="get">
					<fieldset>
						<div class="form_input">
							<span class="label">Latitude:</span>
							<input type="text" id="lat_inp" name="latitude" size="10">
						</div>

						<div class="form_input">
							<span class="label">Longitude:</span>
							<input type="text" id="lng_inp" name="longitude" size="10">
						</div>

						<div class="form_input">
							<span class="label">Radius(miles):</span>
							<input type="text" id="radius_inp" name="radius" size="10">
						</div>

						<div class="form_input">
							<span class="label">Cache Types:</span>
							<select id="cache_inp" form="searchform" name="cache_type">
								<option value="all">All</option>
								<option value="traditional">Traditional</option>
								<option value="mystery_puzzle">Mystery/Puzzle</option>
								<option value="multi-cache">Multi-Cache</option>
							</select>
						</div>

						<div class="form_input">
							<span class="label">Difficulty:</span>
							<select id="difficulty_inp" form="searchform" name="difficulty">
								<option value="any">Any</option>
								<option value="1">1</option>
								<option value="2">2</option>
								<option value="3">3</option>
								<option value="4">4</option>
								<option value="5">5</option>
								<option value="6">6</option>
								<option value="7">7</option>
								<option value="8">8</option>
								<option value="9">9</option>
								<option value="10">10</option>
							</select>
						</div>
						<input type="submit" value="Search" />
					</fieldset>
				</form>

				<!-- Create Table Dynamically using PHP -->
				<?php $results = $db->query($query);
				if ($results) { ?>
				<h2>Nearby Geocaches</h2>
				<table id="data_table">
					<tr>
						<th>Latitude</th>
						<th>Longitude</th>
						<th>Difficulty</th>
						<th>Cache Type</th>
					</tr>
				
					<?php 
						$id = 0;
						#create rows
						foreach($results as $row) { ?>
						<!-- NOTE: Each row is represented with a numerical id ($id) that is used for javascript events-->
						<tr class="tr_data" id="<?=$id?>" name="tr_data">
							<td><?=$row["latitude"]?></td>
							<td><?=$row["longitude"]?></td>
							<td><?=$row["difficulty_rating"]?></td>
							<td><?=$row["cache_type"]?></td>
						</tr>
					<?php 
						$id = $id + 1;
						} ?>

				</table>
			<?php } 
				else {
			?>
			<p>Enter information to view a table of nearby geocaches. If no table is displayed upon submission then there are no geocaches in your selected area.</p>
		<?php 	} ?>
			</div>

		<div id="map"></div>

	</body>

</html>

<?php
function getMinLat($radius) {	#gets minimum latitude within radius $radius of entered latitude
	$earth_rad = 6378.137; #radius of earth in kilometers
	$pi = pi();
	$meters = (1/ ((2 * $pi / 360) * $earth_rad)) / 1000;

	$min_lat = $_GET["latitude"] - ($radius * $meters);
	return $min_lat;
}

function getMaxLat($radius) {	#gets maximum latitude within radius $radius of entered latitude
	$earth_rad = 6378.137; #radius of earth in kilometers
	$pi = pi();
	$meters = (1/ ((2 * $pi / 360) * $earth_rad)) / 1000;

	$max_lat = $_GET["latitude"] + ($radius * $meters);
	return $max_lat;
}

function getMinLng($radius) {	#gets minimum longitude within radius $radius of entered longitude
	$earth_rad = 6378.137; #radius of earth in kilometers
	$pi = pi();
	$meters = (1/ ((2 * $pi / 360) * $earth_rad)) / 1000;

	$min_lng = $_GET["longitude"] - ($radius * $meters) / cos($_GET["latitude"] * ($pi / 180));
	return $min_lng;
}
function getMaxLng($radius) {	#gets maximum longitude within radius $radius of entered longitude
	$earth_rad = 6378.137; #radius of earth in kilometers
	$pi = pi();
	$meters = (1/ ((2 * $pi / 360) * $earth_rad)) / 1000;

	$max_lng = $_GET["longitude"] + ($radius * $meters) / cos($_GET["latitude"] * ($pi / 180));
	return $max_lng;

}

function convertToMeters($miles) { #converts miles to meters
	$meters = $miles * 1609.34;
	return $meters;
}

#changes radius such that the value will always be a multiple of 5 miles
#additionally, ensures that the radius will be at most 200 miles
#and be at least 5 miles
#if user does not enter a radius, then the radius is defaulted to 10 miles
function updateRadius() { 
	if (isset($_GET['radius']) && !empty($_GET['radius'])) { #radius entered
		$radius = $_GET['radius'];
		if ($radius > 20) {	# if the radius is greater than 20, then set radius to 20. 
			$radius = 20;
			#NOTE: In assignment details it said to maximize radius to 200 miles, but google map would crash when I tried to place markers for all of the geocaches. Also, the table was enormous so it would be hard to navigate/search for the geocaches anyways. So I maxed the radius to 20 miles to make the program more consistent/manageable.
		}
		else if ($radius < 5) {	#less than 5, round up to 5
			$radius = 5;
		}
		else {
			#do modulus operations to calculate nearest multiple of 5 to radius
			if ($radius % 5 === 0) {	#already a multiple of 5
				return convertToMeters($radius);
			}
			else if ($radius % 5 <= 2) {
				$radius = $radius - ($radius % 5);	#round down to nearest multiple of 5
			}
			else {
				$radius = $radius + (5 - $radius % 5); #round up to nearest multiple of 5
			}
		}
	}
	else {	#no radius entered
		$radius = 10;
	}
	return convertToMeters($radius); #converts radius from miles to meters
}
?>
