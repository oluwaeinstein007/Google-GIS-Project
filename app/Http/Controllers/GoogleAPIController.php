<?php

namespace App\Http\Controllers;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class GoogleAPIController extends Controller
{
    //
    public function getDistancesAndDurations(Request $request){
        $origins = $request->origins;
        $destinations = $request->destinations;
        // Replace YOUR_API_KEY with your actual Google Maps API key
        $apiKey = env('GOOGLE_MAPS_API_KEY');
        $client = new Client();
        $url = 'https://maps.googleapis.com/maps/api/distancematrix/json';

        try {
            $response = $client->get($url, [
                'query' => [
                    'origins' => implode('|', array_map(function($coord) {
                        return $coord['lat'] . ',' . $coord['lng'];
                    }, $origins)),
                    'destinations' => implode('|', array_map(function($coord) {
                        return $coord['lat'] . ',' . $coord['lng'];
                    }, $destinations)),
                    'units' => 'metric', // or 'imperial' depending on your preference
                    'key' => $apiKey
                ]
            ]);

            $responseData = json_decode($response->getBody(), true);

            if (isset($responseData['rows']) && count($responseData['rows']) > 0) {
                foreach ($responseData['rows'] as $rowIndex => $row) {
                    if (isset($row['elements']) && count($row['elements']) > 0) {
                        $element = $row['elements'][0];
                        $distance = $element['distance']['text'];
                        $duration = $element['duration']['text'];
                        echo "Origin: ({$origins[$rowIndex]['lat']}, {$origins[$rowIndex]['lng']}), Destination: ({$destinations[$rowIndex]['lat']}, {$destinations[$rowIndex]['lng']})\n";
                        echo "Distance: $distance\n";
                        echo "Duration: $duration\n";
                        // return response()->json([
                        //     'distance' => $distance,
                        //     'duration' => $duration
                        // ]);
                    }
                }
            } else {
                return response()->json([
                    'error' => 'No rows found in the response.',
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    // Example usage
    // public function example()
    // {
    //     $origins = [
    //         ['lat' => 40.712776, 'lng' => -74.005974],
    //         ['lat' => 34.052235, 'lng' => -118.243683]
    //     ];
    //     $destinations = [
    //         ['lat' => 34.052235, 'lng' => -118.243683],
    //         ['lat' => 40.712776, 'lng' => -74.005974]
    //     ];
    //     return $this->getDistancesAndDurations($origins, $destinations);
    //     $latitude = 37.783333;
    //     $longitude = -122.416667;
    //     return $this->getForecast($latitude, $longitude);
    // }

    public function getForecast(Request $request)
    {
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $apiKey = env('GOOGLE_MAPS_API_KEY');
        // $apiKey = 'AIzaSyCFS0E7BkA80WCBP72icNJF2qJHukH33BI';

        $client = new Client();

        $url = "https://maps.googleapis.com/maps/api/pollen/v2/forecast?location={$latitude},{$longitude}&key={$apiKey}";

        try {
            $response = $client->get($url);
            $data = json_decode($response->getBody(), true);

            // Process the pollen data here
            return response()->json($data);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}
