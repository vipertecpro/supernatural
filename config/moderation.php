<?php

return [
    'report_rate_limit_per_minute' => (int) env('REPORT_RATE_LIMIT_PER_MINUTE', 5),
    'appeal_rate_limit_per_minute' => (int) env('APPEAL_RATE_LIMIT_PER_MINUTE', 3),
    'appeal_window_days' => (int) env('MODERATION_APPEAL_WINDOW_DAYS', 30),
    'notification_delivery_max_attempts' => (int) env('NOTIFICATION_DELIVERY_MAX_ATTEMPTS', 3),
];
