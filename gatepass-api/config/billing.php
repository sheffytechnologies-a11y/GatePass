<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Subscription grace period
    |--------------------------------------------------------------------------
    |
    | Number of days after a subscription's current_period_end before it is
    | flagged past_due and write actions (adding units/residents) are blocked
    | for estate_admin users. Read access is never blocked.
    |
    */
    'grace_period_days' => env('BILLING_GRACE_PERIOD_DAYS', 3),

];
