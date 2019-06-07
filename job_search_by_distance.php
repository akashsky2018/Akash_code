<!doctype html>

<?php 
$distance=$_GET['distance'];
$lat=$_GET['lat'];
$lang=$_GET['lang'];
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script type="text/javascript" src="http://www.google.com/jsapi"></script>

      <script type="text/javascript">
        google.load("maps", "3",{other_params:"sensor=false&libraries=geometry"});
      </script>

<body style="margin:0px; padding:0px;" >
 
 <select id="radius_km">
     
    <?php 
   echo "<option selected = 'selected' value='".$distance."'> ".$distance."</option>";
     ?>
 </select>
 <div id="map_canvas"  style="width:700px; height:700px;">
    </div>
    
    
    
    
    
    
    

    
<script>        
  var lat=<?php echo $lat; ?>      
  var lang=<?php echo $lang; ?>      
  var map = null;
  var radius_circle = null;
  var markers_on_map = [];
  
  var all_locations = [
	  {type: "job", name: "Job 1", lat: lat, lng: lang},
	 
  ];

  //initialize map on document ready
  $(document).ready(function(){
      var latlng = new google.maps.LatLng(lat, lang); //you can use any location as center on map startup
      var myOptions = {
        zoom: 14,
        center: latlng,
        mapTypeControl: true,
        mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU},
        navigationControl: true,
        mapTypeId: google.maps.MapTypeId.ROADMAP
      };
      map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
	  
	  google.maps.event.addListener(map, 'click', showCloseLocations);
  });
  
  function showCloseLocations(e) {
  	var i;
  	var radius_km = $('#radius_km').val();
  	var address = $('#address').val();

  	//remove all radii and markers from map before displaying new ones
  	if (radius_circle) {
  		radius_circle.setMap(null);
  		radius_circle = null;
  	}
  	for (i = 0; i < markers_on_map.length; i++) {
  		if (markers_on_map[i]) {
  			markers_on_map[i].setMap(null);
  			markers_on_map[i] = null;
  		}
  	}
	
  	var address_lat_lng = e.latLng;
  	radius_circle = new google.maps.Circle({
  		center: address_lat_lng,
  		radius: radius_km * 1000,
  		clickable: false,
  		map: map
  	});
	if(radius_circle) map.fitBounds(radius_circle.getBounds());
  	for (var j = 0; j < all_locations.length; j++) {
  		(function (location) {
  			var marker_lat_lng = new google.maps.LatLng(location.lat, location.lng);
  			var distance_from_location = google.maps.geometry.spherical.computeDistanceBetween(address_lat_lng, marker_lat_lng); //distance in meters between your location and the marker
  			if (distance_from_location <= radius_km * 1000) {
  				var new_marker = new google.maps.Marker({
  					position: marker_lat_lng,
  					map: map,
  					title: location.name
  				});
  				google.maps.event.addListener(new_marker, 'click', function () {
  					alert(location.name + " is " + distance_from_location + " meters from my location");
  				});
  				markers_on_map.push(new_marker);
  			}
  		})(all_locations[j]);
  	}
  }

</script>
    
    
</body>