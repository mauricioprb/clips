<?php

declare(strict_types=1);

return [

    'morning_start' => env('TIMESHEET_MORNING_START', '08:00'),

    'afternoon_start' => env('TIMESHEET_AFTERNOON_START', '14:00'),

    'morning_max_minutes' => (int) env('TIMESHEET_MORNING_MAX_MINUTES', 240),

];
