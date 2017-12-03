<div class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="logo" href="#"><!--<img src="img/press-02.jpg" alt="">--><h4>KAKOHO</h4></a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-right">
            <li><a href="#pricing" class="scroll">Pricing</a></li>
            <li><a href="#">Sign in</a></li>
          </ul>
        </div><!--/.navbar-collapse -->
      </div>
    </div>
        
    <header>
      <div class="container">
        <div class="row">
          <div class="col-xs-6">
            <a href="#">KAKOHO</a>
          </div>
          <div class="col-xs-6 signin text-right navbar-nav">
            <a href="#">Sign in</a>
          </div>
        </div>
        
        <div class="row header-info">
            <!-- <div id="map" style="width:500px;height:500px"></div> -->
          <div class="col-sm-10 col-sm-offset-1 text-center">
          <div id="map" style="width:500px;height:500px"></div>
            <h1 class="wow fadeIn">우리의 중심은 어딜까?</h1>
            <br />
            <p class="lead wow fadeIn" data-wow-delay="0.5s">가장 합리적인 약속 장소를 골라주는 어플리케이션</p>
            <br />
          
            <div class="row">
            <div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">
              <form role="form" class="form-inline">
              <div class="form-group">
                  <input type="text" class="form-control" id="address" placeholder="주소를 입력해주세요">
              </div>
              <button type="submit" for="address" class="btn btn-primary btn-lg">Email</button>
              </form>
              <br />
              <button type="submit" class="btn btn-primary btn-lg">약속장소 찾기</button>
            </div>
          </div>
            
          </div>
        </div>
      </div>
      </header>
    
  <script>
    var lat = document.getElementById("lat"); // this will select the input with id = lat
    var lng = document.getElementById("lng"); // this will select the input with id = lng
    var info = document.getElementById("info"); // this will select the current div  with id = info
    var ServiceId = document.getElementById("ServiceId"); // this will select the input with id = ServiceId
    var prov = document.getElementById("ProviderId"); // this will select the input with id = ProviderId
    var locations = [];
    var km = 30; // this kilometers used to specify circle wide when use drawcircle function
    var Crcl ; // circle variable
    var map; // map variable
    var mapOptions = {
        zoom: 11,
        center: {lat:37.566535 , lng:126.977969}
    }; // map options
    var markers = []; // markers array ,we will fill it dynamically
    var infoWindow = new google.maps.InfoWindow(); // information window ,we will use for our location and for markers
    // this will initiate when load the page and have all
    function initialize() {
        // set the map to the div with id = map and set the mapOptions as defualt
        map = new google.maps.Map(document.getElementById('map'),
            mapOptions);

        var infoWindow = new google.maps.InfoWindow({map: map});

        // get current location with HTML5 geolocation API.
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var pos = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                lat.value  =  position.coords.latitude ;
                lng.value  =  position.coords.longitude;
                info.nodeValue =  position.coords.longitude;
                // set the current posation to the map and create info window with (Here Is Your Location) sentense
                infoWindow.setPosition(pos);
                infoWindow.setContent('Here Is Your Location.');
                // set this info window in the center of the map
                map.setCenter(pos);
                // draw circle on the map with parameters
                DrowCircle(mapOptions, map, pos, km);

            }, function() {
                // if user block the geolocatoon API and denied detect his location
                handleLocationError(true, infoWindow, map.getCenter());
            });
        } else {
            // Browser doesn't support Geolocation
            handleLocationError(false, infoWindow, map.getCenter());

        }
    }
    // to handle user denied
    function handleLocationError(browserHasGeolocation, infoWindow, pos) {
        infoWindow.setPosition(pos);
        infoWindow.setContent(browserHasGeolocation ?
            'Error: User Has Denied Location Detection.' :
            'Error: Your browser doesn\'t support geolocation.');
    }

    // to draw circle around 30 kilometers to current location
    function DrowCircle(mapOptions, map, pos, km ) {
        var populationOptions = {
            strokeColor: '#FF0000',
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: '#FF0000',
            fillOpacity: 0.35,
            map: map,
            center: pos,
            radius: Math.sqrt(km*500) * 100
        };
        // Add the circle for this city to the map.
        this.Crcl = new google.maps.Circle(populationOptions);
    }
    // this function to get providers with ajax request
    function RelatedLocationAjax() {
        $.ajax({
            type: "POST",
            url: "<?= base_url() ?>services/closest_locations",
            dataType: "json",
            data:"data="+ '{ "latitude":"'+ lat.value+'", "longitude": "'+lng.value+'", "ServiceId": "'+ServiceId.value+'" }',
            success:function(data) {
                // when request is successed add markers with results
                add_markers(data);
            }
        });
    }
    // this function to will draw markers with data returned from the ajax request
    function add_markers(data){
        var marker, i;
        var bounds = new google.maps.LatLngBounds();
        var infowindow = new google.maps.InfoWindow();
        // display how many closest providers avialable
        document.getElementById('info').innerHTML = " Available:" + data.length + " Providers<br>";

        for (i = 0; i < data.length; i++) {
            var coordStr = data[i][2];
            var coords = coordStr.split(",");
            var pt = new google.maps.LatLng(parseFloat(coords[0]), parseFloat(coords[1]));
            bounds.extend(pt);
            marker = new google.maps.Marker({
                position: pt,
                map: map,
                icon: data[i][3],
                address: data[i][1],
                title: data[i][0],
                html: data[i][0] + "<br>" + data[i][1]
            });
            markers.push(marker);
            google.maps.event.addListener(marker, 'click', (function (marker, i) {
                return function () {
                    infowindow.setContent(marker.html);
                    prov.value = data[i][5];
                    infowindow.open(map, marker);
                }
            })
            (marker, i));

        }
        // this is important part , because we tell the map to put all markers inside the circle,
        // so all results will display and centered
        map.fitBounds(this.Crcl.getBounds());
    }

    google.maps.event.addDomListener(window, 'load', initialize);

</script>
<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA9WniMtyW0oPQwWrEHoyTDl2DrMgbH-0Y&callback=initialize">
</script>


    