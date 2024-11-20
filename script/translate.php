<?php 

require '../vendor/autoload.php';
$config = parse_ini_file('../.env');

use Google\Cloud\Translate\V3\TranslationServiceClient;
use Google\Cloud\Storage\StorageClient;

$vtt_file_uri_origin = "gs://labor_medical/subtitiles_demo/673dabc54d5dd/audio_transcript_6749a9b6-0000-2103-96ec-f40304387754.vtt";

// Use the gcloud command to copy the file and change its extension
exec("gcloud storage cp $vtt_file_uri_origin gs://labor_medical/subtitiles_demo/673dabc54d5dd/audio_transcript_6749a9b6-0000-2103-96ec-f40304387754.txt");

$vtt_file_uri = "gs://labor_medical/subtitiles_demo/673dabc54d5dd/audio_transcript_6749a9b6-0000-2103-96ec-f40304387754.txt";


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
$outputUri->setOutputUriPrefix("gs://labor_medical/subtitiles_demo/673dabc54d5dd/translated/");
$outputConfig->setGcsDestination($outputUri);

$operation = $translationServiceClient->batchTranslateText(
    $formattedParent, 
    'it',
    ['en', 'es'],
    $inputConfigs, 
    $outputConfig
);

$operation->pollUntilComplete();


if ($operation->operationSucceeded()) {

  

} else {
    $error = $operation->getError();
    print('Error: ' . $error->getMessage());
}

$translationServiceClient->close();




$storage = new StorageClient([
    'keyFilePath' => $config['GOOGLE_APPLICATION_CREDENTIALS']
]);

$bucket = $storage->bucket($config['BUCKET_NAME']);
$file_location = "gs://labor_medical/subtitiles_demo/673dabc54d5dd/translated/index.csv";

$object = $bucket->object('subtitiles_demo/673dabc54d5dd/translated/index.csv');
$object->downloadToFile('../storage/673dabc54d5dd/index.csv');

$csv = array_map('str_getcsv', file('../storage/673dabc54d5dd/index.csv'));

foreach($csv as $key => $row) {
    
    $language_code = $row[1];
    $file_name = $row[2];

    $destination = "gs://labor_medical/subtitiles_demo/673dabc54d5dd/$language_code" . "_subtitles.vtt";
    exec("gcloud storage mv $file_name $destination");
    
}

exec("gcloud storage mv $vtt_file_uri_origin gs://labor_medical/subtitiles_demo/673dabc54d5dd/it_subtitles.vtt");
