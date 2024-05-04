<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Pollen Data</title>
    <style>
        #map {
            height: 400px;
            width: 100%;
        }
    </style>
</head>

<body>
    <h1>Pollen Data Map</h1>
    <div id="map"></div>

    <script>
        function initMap() {
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 4,
                center: {
                    lat: 37.7749,
                    lng: -122.4194
                }, // Default center (San Francisco)
            });

            // Load the Weather library explicitly
            const weatherLayer = new google.maps.weather.WeatherLayer({
                map: map,
            });

            // Load the Pollen library explicitly
            const pollenLayer = new google.maps.weather.PollenLayer({
                map: map,
            });

            google.maps.event.addListener(pollenLayer, "click", function(event) {
                const location = event.featureDetails.location;
                const pollenLevels = event.featureDetails.pollenLevels;

                // Display pollen levels in a paragraph
                const pollenInfo = document.createElement('p');
                pollenInfo.innerHTML = `Pollen levels at ${location}: ${pollenLevels}`;
                document.body.appendChild(pollenInfo);

                console.log("Pollen levels at", location, ":", pollenLevels);
            });
        }
    </script>

    <!-- Replace YOUR_API_KEY with your actual Google Maps API key -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCFS0E7BkA80WCBP72icNJF2qJHukH33BI&callback=initMap">
    </script>

</body>

</html>
