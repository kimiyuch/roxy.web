<?php

use Illuminate\Support\Facades\Schedule;

// Hostpoint has no long-running workers: one cron job runs `schedule:run` every minute,
// and that drains the queue (invitation and new-feedback emails).
Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3')
    ->everyMinute()
    ->withoutOverlapping();
