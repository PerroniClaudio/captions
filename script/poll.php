<?php 

require '../vendor/autoload.php';
$client = new \GuzzleHttp\Client();

$operationName = "projects/317522880619/locations/global/operations/v2-6749a9b1-0000-2103-96ec-f40304387754";
$token = exec("gcloud auth application-default print-access-token");

// do {
//     sleep(2); // Attendi 10 secondi prima di fare il polling
    $response = $client->get('https://speech.googleapis.com/v2/' . $operationName, [
        'headers' => [
            'Content-Type' => 'application/json; charset=utf-8',
            'Authorization' => 'Bearer ' . $token,
        ]
    ]);
    $status = json_decode($response->getBody()->getContents(), true);

    print_r($status);

    // Clear the previous output
//     echo "\033[2J\033[H";
//     print_color("Stato dell'operazione: " . $status['metadata']['progressPercent'] . "%", 'yellow');


// } while ($status['metadata']['progressPercent'] != '100');

function print_color($message, $color) {

    $colors = [
        'red' => '31',
        'green' => '32',
        'yellow' => '33',
        'blue' => '34',
        'purple' => '35',
        'cyan' => '36',
        'white' => '37',
    ];

    echo "\033[" . $colors[$color] . "m" . $message . "\033[0m\n";
}
