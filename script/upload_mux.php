<?php 

include "vendor/autoload.php";
$config = parse_ini_file('.env');

$muxConfig = MuxPhp\Configuration::getDefaultConfiguration()->setUsername($config['MUX_TOKEN_ID'])->setPassword($config['MUX_TOKEN_SECRET']);

$assetsApi = new MuxPhp\Api\AssetsApi(
    new GuzzleHttp\Client(),
    $muxConfig
);

$subtitles = new MuxPhp\Models\AssetGeneratedSubtitleSettings(["language_code" => "it", "name" => "default"]);
$input = new MuxPhp\Models\InputSettings(["url" => 'https://admin.labormedical.it/test/captions/storage/CRS-1698_vid_lm_vvv.mp4', "generated_subtitles" => [$subtitles]]);
$createAssetRequest = new MuxPhp\Models\CreateAssetRequest([
    "input" => $input,
    'playback_policy' => [MuxPhp\Models\PlaybackPolicy::_PUBLIC]
]);

$assetResult = $assetsApi->createAsset($createAssetRequest);
$asssetid = $assetResult->getData()->getPlaybackIds()[0]->getId();

echo $asssetid;