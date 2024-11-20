<?php 

/*

    Questo script è una tech demo per la generazione di sottotitoli automatici tramite Google Cloud Text-to-Speech API.
    È stato creato per labor medical, ma voglio renderlo buono e fruibile in modo da poterlo riutilizzare in futuro.
    Utilizza la piattaforma Google Cloud Platform per svolgere i seguenti step:
    - Estrazione dell'audio dal video usando ffmpeg
    - Upload dell'audio al bucket di Google Cloud Storage
    - Upload del video al bucket di Google Cloud Storage
    - Estrazione del testo dall'audio usando Google Cloud Speech-to-Text API
    - Traduzione del testo estratto in altre lingue usando Google Cloud Translation API
    - Upload dei file .vtt generati al bucket di Google Cloud Storage

    Per utilizzare questo script, è necessario avere un account Google Cloud Platform e abilitare le API necessarie, ovvero:
    - Cloud Speech-to-Text API
    - Cloud Storage API
    - Cloud Translation API

    Per abilitarle è necessario creare un progetto e poi utilizzare l'utility gcloud per abilitare le API necessarie con i seguenti comandi:

    gcloud services enable speech.googleapis.com
    gcloud services enable storage.googleapis.com
    gcloud services enable translate.googleapis.com

*/

require '../vendor/autoload.php';

use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Translate\V3\TranslationServiceClient;

try{

    $config = parse_ini_file('../.env');

    //? 0 - Crea una cartella dove salvare tutti i file temporanei

        $job_id = uniqid();
        print_color("Avvio del job con ID: " . $job_id, 'blue');
        mkdir($config['VIDEO_PATH'] . "/" . $job_id, 0777, true);

    //? 1 - Estrazione dell'audio dal video usando ffmpeg

        print_color('Step 1: Estrazione dell\'audio dal video', 'blue');

        $ffmpeg = FFMpeg\FFMpeg::create([
            'timeout'          => 3600,
            'ffmpeg.threads'   => 12,
        ]);

        $video = $ffmpeg->open($config['VIDEO_PATH'] . "/" . $config['VIDEO_NAME'] . '.mp4');
        $audio_format = new FFMpeg\Format\Audio\Wav();
        $video->save($audio_format,$config['VIDEO_PATH'] . "/" . $job_id . '/audio.wav');

        print_color("Estrazione dell'audio completata", 'green');

    //? 2 - Upload dell'audio e video al bucket di Google Cloud Storage

        print_color('Step 2: Upload dell\'audio e del video al bucket di Google Cloud Storage', 'blue');

        $video_original_path = $config['VIDEO_PATH'] . "/" . $config['VIDEO_NAME'] . '.mp4';
        $audio_original_path = $config['VIDEO_PATH'] . "/" . $job_id . '/audio.wav';

        $video_uploaded_path = "gs://".$config['BUCKET_NAME']."/".$config['BUCKET_PREFIX']."/".$job_id."/".$config['VIDEO_NAME'].".mp4";
        $audio_uploaded_path = "gs://".$config['BUCKET_NAME']."/".$config['BUCKET_PREFIX']."/".$job_id."/audio.wav";

        $storage = new StorageClient([
            'keyFilePath' => $config['GOOGLE_APPLICATION_CREDENTIALS']
        ]);

        $bucket = $storage->bucket($config['BUCKET_NAME']);

        $bucket->upload(fopen($video_original_path, 'r'), [
            'name' => $config['BUCKET_PREFIX']."/".$job_id."/".$config['VIDEO_NAME'].".mp4"
        ]);

        print_color("Upload del video completato", 'green');

        $bucket->upload(fopen($audio_original_path, 'r'), [
            'name' => $config['BUCKET_PREFIX']."/".$job_id."/audio.wav"
        ]);

        print_color("Upload dell'audio completato", 'green');

    //? 3 - Estrazione del testo dall'audio usando Google Cloud Speech-to-Text API

        print_color('Step 3: Estrazione del testo dall\'audio usando Google Cloud Speech-to-Text API', 'blue');

        $vtt_uploaded_path = "gs://".$config['BUCKET_NAME']."/".$config['BUCKET_PREFIX']."/".$job_id;
        $client = new \GuzzleHttp\Client();
        $token = exec("gcloud auth application-default print-access-token");

        $response = $client->post('https://speech.googleapis.com/v2/projects/' . $config['GOOGLE_CLOUD_PROJECT'] . '/locations/global/recognizers/_:batchRecognize', [
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
                'Authorization' => 'Bearer ' . $token,
            ],
            'json' => [
                'files' => [
                    [
                        'uri' => $audio_uploaded_path
                    ]
                ],
                'config' => [
                    'features' => ['enableWordTimeOffsets' => true],
                    'autoDecodingConfig' => new stdClass(),
                    'model' => 'long',
                    'languageCodes' => ['it-IT']
                ],
                'recognitionOutputConfig' => [
                    'gcsOutputConfig' => ['uri' => $vtt_uploaded_path],
                    'output_format_config' => ['vtt' => new stdClass()]
                ]
            ]
        ]);

        $operationName = json_decode($response->getBody()->getContents(), true)['name'];
        $started_at = new DateTime();
        $started_at_formatted = $started_at->format('H:i:s');
        

        print_color("Operazione avviata alle ore {$started_at_formatted} : " . $operationName, 'cyan');

        do {
            sleep(60); 
            $response = $client->get('https://speech.googleapis.com/v2/' . $operationName, [
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Authorization' => 'Bearer ' . $token,
                ]
            ]);
            $status = json_decode($response->getBody()->getContents(), true);

            if(isset($status['metadata']['progressPercent'])) {
                print_color("Stato dell'operazione: " . $status['metadata']['progressPercent'] . "%", 'yellow');
            } else {
                print_color("Stato dell'operazione: Non ancora avviata.", 'yellow');
            }
            
        } while ($status['metadata']['progressPercent'] != '100');

        $vtt_file_uri_origin = $status['response']['results'][$audio_uploaded_path]['cloudStorageResult']['vttFormatUri'];
        $ended_at = new DateTime();
        $ended_at_formatted = $ended_at->format('H:i:s');

        print_color("Operazione completata con successo alle ore {$ended_at_formatted}", 'cyan');
        print_color("Estrazione del testo completata", 'green');

    //? 4 - Traduzione del testo estratto in altre lingue usando Google Cloud Translation API

        print_color('Step 4: Traduzione del testo estratto in altre lingue usando Google Cloud Translation API', 'blue');

        $vtt_file_name = str_replace(".vtt", ".txt" ,basename($vtt_file_uri_origin));
        $vtt_file_uri = "gs://".$config['BUCKET_NAME']."/".$config['BUCKET_PREFIX']."/".$job_id."/".$vtt_file_name;

        exec("gcloud storage cp $vtt_file_uri_origin $vtt_file_uri");

        $translationServiceClient = new TranslationServiceClient();
        $formattedParent = $translationServiceClient->locationName($config['GOOGLE_CLOUD_PROJECT'], $config['GOOGLE_CLOUD_REGION']);

        $inputConfig = new Google\Cloud\Translate\V3\InputConfig();
        $vtt_gcs_source = new Google\Cloud\Translate\V3\GcsSource();
        $vtt_gcs_source->setInputUri($vtt_file_uri);

        $inputConfigs = [
            $inputConfig
                ->setMimeType('text/plain')
                ->setGcsSource($vtt_gcs_source)   
        ];

        $outputConfig = new Google\Cloud\Translate\V3\OutputConfig();
        $outputUri = new Google\Cloud\Translate\V3\GcsDestination();
        $outputUri->setOutputUriPrefix("gs://".$config['BUCKET_NAME']."/".$config['BUCKET_PREFIX']."/".$job_id."/translated/");
        $outputConfig->setGcsDestination($outputUri);

        $operation = $translationServiceClient->batchTranslateText(
            $formattedParent, 
            'it',
            ['en', 'es', 'fr'],
            $inputConfigs, 
            $outputConfig
        );

        $operation->pollUntilComplete();

        if ($operation->operationSucceeded()) {
            print_color("Traduzione completata", 'green');

            $translationServiceClient->close();

            $storage = new StorageClient([
                'keyFilePath' => $config['GOOGLE_APPLICATION_CREDENTIALS']
            ]);

            $destination = $config['VIDEO_PATH'] . "/" . $job_id . "/index.csv";

            $object = $bucket->object($config['BUCKET_PREFIX']."/".$job_id."/translated/index.csv");
            $object->downloadToFile($destination);

            print_color("Download del file CSV completato", 'green');

            $csv = array_map('str_getcsv', file($destination));

            foreach($csv as $key => $row) {
    
                $language_code = $row[1];
                $file_name = $row[2];

                $subtitle_destination = "gs://".$config['BUCKET_NAME']."/".$config['BUCKET_PREFIX']."/".$job_id."/{$language_code}_subtitles.vtt";
                exec("gcloud storage mv $file_name $subtitle_destination");
            }

            $origin_subtitles_file = "gs://".$config['BUCKET_NAME']."/".$config['BUCKET_PREFIX']."/".$job_id."/it_subtitles.vtt";
            exec("gcloud storage mv $vtt_file_uri_origin $origin_subtitles_file");

            print_color("Operazione completata", 'green');


        } else {
            $error = $operation->getError();
            print_color('Error: ' . $error->getMessage(), 'red');
        }
        

} catch (Exception $e) {

    print_color($e->getMessage(), 'red');
   
    die();
}

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
