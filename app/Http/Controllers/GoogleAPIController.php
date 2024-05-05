<?php

namespace App\Http\Controllers;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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


    public function getForecastWithDataHandling(Request $request)
    {
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $apiKey = env('GOOGLE_MAPS_API_KEY');

        $client = new \GuzzleHttp\Client();

        $url = "https://maps.googleapis.com/maps/api/pollen/v2/forecast?location={$latitude},{$longitude}&key={$apiKey}";

        try {
            $response = $client->get($url);
            $data = json_decode($response->getBody(), true);
            return response()->json($data);
                // Process the pollen data here
                $processedData = $this->processPollenData($data);

                return response()->json($processedData);
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                return response()->json([
                'error' => $e->getMessage(),
                ], $e->getCode());
            } catch (\Exception $e) {
                return response()->json([
                'error' => 'An unexpected error occurred.',
                ], 500);
            }
    }

    public function processPollenData($data)
    {

    // Extract pollen types and risk levels from the response
    $pollen_types = [];
    $risk_levels = [];
    foreach ($data['pollen_types'] as $pollen_type) {
        $pollen_types[] = $pollen_type['type'];
        $risk_levels[] = $pollen_type['risk_level'];
    }

    // Create a processed data dictionary
    $processed_data = [
        'pollen_types' => $pollen_types,
        'risk_levels' => $risk_levels,
    ];

    return $processed_data;
    }


    //Get distances and durations of each n origin to m destinations
    public function getDistTimeMatrix(Request $request){
        $origins = $request->origins;
        $destinations = $request->destinations;
        // Replace YOUR_API_KEY with your actual Google Maps API key
        $apiKey = env('GOOGLE_MAPS_API_KEY');
        $client = new Client();
        $url = 'https://maps.googleapis.com/maps/api/distancematrix/json';

        try {
            $responseData = [];

            foreach ($origins as $originIndex => $origin) {
                foreach ($destinations as $destinationIndex => $destination) {
                    $response = $client->get($url, [
                        'query' => [
                            'origins' => $origin['lat'] . ',' . $origin['lng'],
                            'destinations' => $destination['lat'] . ',' . $destination['lng'],
                            'units' => 'metric', // or 'imperial' depending on your preference
                            'key' => $apiKey
                        ]
                    ]);

                    $distanceDurationData = json_decode($response->getBody(), true);
                    $responseData[$originIndex][$destinationIndex]['distance'] = $distanceDurationData['rows'][0]['elements'][0]['distance']['text'];
                    $responseData[$originIndex][$destinationIndex]['duration'] = $distanceDurationData['rows'][0]['elements'][0]['duration']['text'];
                    $responseData[$originIndex][$destinationIndex]['origin'] = $origin;
                    $responseData[$originIndex][$destinationIndex]['destination'] = $destination;
                }
            }


        Excel::store($responseData, 'distance_matrix.xlsx');

            return response()->json($responseData);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }
    // public function getDistTimeMatrix(Request $request)
    // {
    //     $origins = $request->origins;
    //     $destinations = $request->destinations;
    //     $apiKey = env('GOOGLE_MAPS_API_KEY');

    //     try {
    //         $responseData = $this->calculateDistances($origins, $destinations, $apiKey);

    //         // Check if responseData is empty before saving
    //         if (!empty($responseData)) {
    //             Excel::store($responseData, 'distance_matrix.xlsx');
    //             return response()->json([
    //                 'message' => 'Distance matrix calculated and saved to excel file successfully.',
    //                 'data' => $responseData,
    //             ]);
    //         } else {
    //             return response()->json([
    //                 'error' => 'No data available to save in excel file.',
    //             ]);
    //         }
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error' => $e->getMessage(),
    //         ], $e->getCode());
    //     }
    // }

    // private function calculateDistances($origins, $destinations, $apiKey)
    // {
    //     $client = new Client();
    //     $url = 'https://maps.googleapis.com/maps/api/distancematrix/json';

    //     $responseData = [];

    //     foreach ($origins as $originIndex => $origin) {
    //         foreach ($destinations as $destinationIndex => $destination) {
    //             $response = $client->get($url, [
    //                 'query' => [
    //                     'origins' => $origin['lat'] . ',' . $origin['lng'],
    //                     'destinations' => $destination['lat'] . ',' . $destination['lng'],
    //                     'units' => 'metric', // or 'imperial' depending on your preference
    //                     'key' => $apiKey
    //                 ]
    //             ]);

    //             $distanceDurationData = json_decode($response->getBody(), true);

    //             // Check if data exists before adding to responseData
    //             if (isset($distanceDurationData['rows'][0]['elements'][0])) {
    //                 $responseData[$originIndex][$destinationIndex]['distance'] = $distanceDurationData['rows'][0]['elements'][0]['distance']['text'];
    //                 $responseData[$originIndex][$destinationIndex]['duration'] = $distanceDurationData['rows'][0]['elements'][0]['duration']['text'];
    //                 $responseData[$originIndex][$destinationIndex]['origin'] = $origin;
    //                 $responseData[$originIndex][$destinationIndex]['destination'] = $destination;
    //             }
    //         }
    //     }

    //     return $responseData;
    // }




    //Get optimal origin
    public function getOptimalOrigin(Request $request) {
        $responseData = $this->getDistTimeMatrix($request)->original;

        $optimalOrigin = null;
        $minTotalDuration = PHP_INT_MAX;

        // Calculate total duration from each origin to all destinations
        foreach ($responseData as $originData) {
            $totalDuration = 0;
            foreach ($originData as $destinationData) {
                $totalDuration += strtotime($destinationData['duration']);
            }
            if ($totalDuration < $minTotalDuration) {
                $minTotalDuration = $totalDuration;
                $optimalOrigin = $originData[0]['origin'];
            }
        }

        return response()->json([
            'optimal_origin' => $optimalOrigin,
            'min_total_duration' => $minTotalDuration,
        ]);
    }





}
