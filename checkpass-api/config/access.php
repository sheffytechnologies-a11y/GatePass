<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Daily access phrase
    |--------------------------------------------------------------------------
    |
    | Each unit gets a spoken phrase per day that a visitor without a
    | smartphone can give at the gate. "Day" is measured in this timezone, so
    | phrases roll over at local midnight rather than UTC midnight.
    |
    | After max_failed_attempts wrong guesses for a unit, verification for that
    | unit is locked until the resident generates a new phrase or the day ends.
    |
    */
    'phrase_timezone' => env('ACCESS_PHRASE_TIMEZONE', 'Africa/Lagos'),

    'max_failed_attempts' => (int) env('ACCESS_PHRASE_MAX_FAILED_ATTEMPTS', 5),

];
