<?php 

$playback_id = "02015jgMpNu01OAXgMCwXYf9DA1O58nRmmyjEJ5HQE2rC4";

?>


<!DOCTYPE html >
<html lang="en" data-theme="aqua">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <script src="https://cdn.jsdelivr.net/npm/@mux/mux-player"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.14/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>

    <main class="container max-w-7xl mx-auto overflow-hidden">
        <div class="aspect-video my-8">
            <mux-player
                style="width: 100%; height: 100%;"
                stream-type="on-demand"
                playback-id="<?= $playback_id ?>"
            >
            </mux-player>
        </div>
    </main>

</body>
</html>