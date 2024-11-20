<?php 

$sources = json_decode(file_get_contents('./script/urls.json'), true);

$video_url = $sources['video_url'];
$it_subtitles_url = $sources['it_subtitles_url'];
$en_subtitles_url = $sources['en_subtitles_url'];
$es_subtitles_url = $sources['es_subtitles_url'];
$fr_subtitles_url = $sources['fr_subtitles_url'];

?>

<!DOCTYPE html>
<html lang="en" data-theme="aqua">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.14/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@vime/core@^5/themes/default.css" />

    <script type="module" src="https://cdn.jsdelivr.net/npm/@vime/core@^5/dist/vime/vime.esm.js"></script>
</head>
<body>
    <main class="container max-w-7xl mx-auto overflow-hidden">
        <div class="aspect-video my-8">
            <vm-player playsinline>
                <vm-video cross-origin="true">
                    <source data-src="<?= $video_url ?>" type="video/mp4" />
                    <track default kind="captions" src="<?= $it_subtitles_url ?>" srclang="it" label="Italiano" />
                    <track kind="captions" src="<?= $en_subtitles_url ?>" srclang="en" label="English" />
                    <track kind="captions" src="<?= $es_subtitles_url ?>" srclang="es" label="Español" />
                    <track kind="captions" src="<?= $fr_subtitles_url ?>" srclang="fr" label="Français" />
                </vm-video>
                <vm-default-ui></vm-default-ui>
            </vm-player>
        </div>
    </main>
</body>
</html>
