<?php

return [
    'rate_limit_per_minute' => (int) env('API_RATE_LIMIT_PER_MINUTE', 60),
    'public_rate_limit_per_minute' => (int) env('API_PUBLIC_RATE_LIMIT_PER_MINUTE', 30),
];
