<?php

return [
    // Must be set to the real client number in E.164 digits only, e.g. 60123456789.
    'whatsapp_number' => env('DARUL_SHOFI_WHATSAPP_NUMBER', ''),
    'whatsapp_display' => env('DARUL_SHOFI_WHATSAPP_DISPLAY', ''),
    'hero_video_max_kilobytes' => env('DARUL_SHOFI_HERO_VIDEO_MAX_KILOBYTES', 102400),
    'ffprobe_binary' => env('DARUL_SHOFI_FFPROBE_BINARY'),
];
