<?php

return [
    'quarantine_disk' => env('MEDIA_QUARANTINE_DISK', 'local'),
    'max_upload_kilobytes' => (int) env('MEDIA_MAX_UPLOAD_KILOBYTES', 10240),
    'mime_types' => [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/webp' => ['webp'],
        'audio/mpeg' => ['mp3'],
        'audio/ogg' => ['ogg'],
        'video/mp4' => ['mp4'],
        'application/pdf' => ['pdf'],
    ],
    'providers' => [
        'youtube' => ['hosts' => ['youtube.com', 'www.youtube.com', 'youtu.be'], 'embed_host' => 'www.youtube-nocookie.com'],
        'vimeo' => ['hosts' => ['vimeo.com', 'www.vimeo.com'], 'embed_host' => 'player.vimeo.com'],
        'spotify' => ['hosts' => ['open.spotify.com'], 'embed_host' => 'open.spotify.com'],
        'soundcloud' => ['hosts' => ['soundcloud.com', 'www.soundcloud.com'], 'embed_host' => 'w.soundcloud.com'],
    ],
];
