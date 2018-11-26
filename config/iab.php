<?php

/*
|--------------------------------------------------------------------------
| IAB Configuration
|--------------------------------------------------------------------------
|
| You can set your IAB and Auto backup to Amazon S3 credentials.
|
*/

return [

    // IAB login

    'user' => env('IAB_USER'),
    'password' => env('IAB_PASSWORD'),

    // Amazon S3 backup

    's3_backup' => env('IAB_S3_BACKUP', false),
    's3_bucket' => env('IAB_S3_BUCKET'),
    's3_region' => env('AWS_REGION', 'us-east-1'),
    'aws_credentials' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
    ]
];