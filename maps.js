/*
	Name: Austin Pierson
	Course: CSCV337
	Date: 7 May, 2019
	Description: JS file for google maps application. See PHP file for information on the application's functionality.
*/

var map;
var markers = [];

//holds an array of images to go into infoWindow
//var thumbnails = [];

function initMap() {
	//initial posiition of the map
	var pos = {lat: 32.253, lng: -110.912};
	map = new google.maps.Map(document.getElementById("map"), {
		zoom: 10,
		center: pos
	});

	//parse data obtained from database
	var data = JSON.parse($("data").innerHTML);
	dataMarkers(data);

	//Pans google map to general vacinity of markers
	var latLng = new google.maps.LatLng(data[0].latitude, data[0].longitude);
	map.panTo(latLng);
}

//creates markers at geocache locations, clicking markers/clicking corresponding row opens an infoWindow with geocache information
function dataMarkers(data) {
	//index used for markers[] array...this also corresponds with the row number in the table	
	var index = 0;

	Array.prototype.forEach.call(data, function(data) {	//loops through parsed data
		//creates new marker
		var marker = new google.maps.Marker({
			position: new google.maps.LatLng(data.latitude, data.longitude),
			map: map,
			title: data.cache_type + ", Difficulty: " + data.difficulty_rating
		});

		//adds marker to global array of markers
		markers.push(marker);

		//creates a new infoWindow
		var infoWindow = new google.maps.InfoWindow();
		//getPhotos(data.latitude, data.longitude);
		var contentString = "<h1>" + data.latitude + ", " + data.longitude + "</h1>" + 
						"<h1>" + data.cache_type + ", Difficulty: " + data.difficulty_rating + "</h1>" +
						"<h3>Photos taken near this location:</h3>";
		
		//adds images to contentString
		/*for (var i = 0; i < thumbnails.length; i++) {
			contentString = contentString + thumbnails[i];
		}*/


		//opens infoWindow when marker is clicked
		google.maps.event.addListener(marker, 'click', (function(marker) {  
           return function() {
            	infoWindow.setContent(contentString);    
            	infoWindow.open(map, marker);
           }
         })(marker));  

		//opens infoWindow when row is clicked
		var tr = document.getElementById(index);
		google.maps.event.addDomListener(tr, 'click', (function(index){
			return function() {
				rowClick(index);
			}
		})(index)
		);

		index++;
	})
}

//triggers marker's event that corresponds to selected row
function rowClick(index) {
	google.maps.event.trigger(markers[index], "click");
}

/*
//Ajax request
function getPhotos(latitude, longitude) {
	var flickrAPIkey = "d232846631c07715e1f652ed5c6f9c66";
	new Ajax.Request("http://api.flickr.com/services/rest/?",
		{
			method: "GET",
			parameters: {api_key: flickrAPIkey, method: 'flickr.photos.search', lat: latitude, lon: longitude},
			onSuccess: createThumbnail,
			onFailure: ajaxFailed,
			onException: ajaxFailed
		});
}

//Create an array of thumbnails based on tags in flickr link
function createThumbnail(ajax) {
	var photos = ajax.responseXML.getElementsByTagName("photos");

	var farm_id;
	var server_id;
	var id;
	var secret;
	var size;

	thumbnails = [];
	//loops through all tags or 12 photos (whichever comes first)
	for (var i = 0; i < photos.length; i++) {
		farm_id = photos[i].getAttribute("farm");
		server_id = photos[i].getAttribute("server");
		id = photos[i].getAttribute("id");
		secret = photos[i].getAttribute("secret");
		size = photos[i].getAttribute("size");

		var url = "https://" + farm_id + ".staticflickr.com/" + server_id + "/" + id + "_" + secret + "_t.jpg";
		url = "<img src=" + url + " alt=\"photos\" >";
		
		//add created url (in the form of an html <img>) to thumbnails array
		thumbnails.push(url);
		if (i === 12) {
			return;
		}
	}
	console.log(thumbnails);
}

//if ajax request fails
function ajaxFailed(ajax, exception) {	// called when rank information fails to be uploaded, displays error information below origin/meaning section
	var msg = "Error making Ajax request: <br>";

	if (exception) {
		msg += "Exception: " + exception.message;
	}
	else {
		msg += "Server status:<br>" + ajax.status + " " + ajax.statusText + "<br><br>Server response text:<br>" + ajax.responseText;
	}

	alert(msg);
}

*/
