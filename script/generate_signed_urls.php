<?php 

    require_once '../vendor/autoload.php';

    use Google\Cloud\Storage\StorageClient;

    $config = parse_ini_file('../.env');

    $job_id = "";

    // Genera i signed url per video e sottotitoli 

    $storage = new StorageClient([
        'keyFilePath' => $config['GOOGLE_APPLICATION_CREDENTIALS']
    ]);

    $bucket = $storage->bucket($config['BUCKET_NAME']);

    $video = $bucket->object($config['BUCKET_PREFIX']."/".$job_id."/".$config['VIDEO_NAME'].".mp4");
    $video_url = $video->signedUrl(new \DateTime('tomorrow'));

    $it_subtitles = $bucket->object($config['BUCKET_PREFIX']."/".$job_id."/it_subtitles.vtt");
    $it_subtitles_url = $it_subtitles->signedUrl(new \DateTime('tomorrow'));

    $en_subtitles = $bucket->object($config['BUCKET_PREFIX']."/".$job_id."/en_subtitles.vtt");
    $en_subtitles_url = $en_subtitles->signedUrl(new \DateTime('tomorrow'));

    $es_subtitles = $bucket->object($config['BUCKET_PREFIX']."/".$job_id."/es_subtitles.vtt");
    $es_subtitles_url = $es_subtitles->signedUrl(new \DateTime('tomorrow'));

    $fr_subtitles = $bucket->object($config['BUCKET_PREFIX']."/".$job_id."/fr_subtitles.vtt");
    $fr_subtitles_url = $fr_subtitles->signedUrl(new \DateTime('tomorrow'));

    $json = [
        'video_url' => $video_url,
        'it_subtitles_url' => $it_subtitles_url,
        'en_subtitles_url' => $en_subtitles_url,
        'es_subtitles_url' => $es_subtitles_url,
        'fr_subtitles_url' => $fr_subtitles_url
    ];

    file_put_contents('urls.json', json_encode($json, JSON_PRETTY_PRINT));
