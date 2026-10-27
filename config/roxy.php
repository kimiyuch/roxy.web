<?php

return [

    // Where screenshots live: `local` in development, `exoscale` (S3, Switzerland) in production.
    'disk' => env('ROXY_DISK', 'local'),

    // Screenshots are fitted inside this edge on upload; MCP gets them at the smaller one,
    // since larger images cost the agent tokens without helping it see more.
    'screenshot_max_edge' => 2560,
    'mcp_image_max_edge' => 1568,
    'max_screenshots' => 5,
    'max_upload_kb' => 8192,

    // How long a magic link in an invitation email works, and how long the one-time code
    // it hands to the widget (in the URL fragment) stays valid.
    'invite_link_days' => 14,
    'login_code_minutes' => 15,

    // Caps on the captured context, applied server-side whatever the widget sends.
    'max_console_entries' => 20,
    'max_network_entries' => 20,
    'max_html_length' => 4000,

];
