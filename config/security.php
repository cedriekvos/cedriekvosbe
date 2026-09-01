<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Vulnerability Alert Recipient
    |--------------------------------------------------------------------------
    |
    | The email address that receives Composer vulnerability alerts. When this
    | is empty, the hourly check still runs but no alert email is sent.
    |
    */

    'alert_recipient' => env('SECURITY_ALERT_RECIPIENT'),

];
