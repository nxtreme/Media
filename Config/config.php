<?php

return [
    /*
    |--------------------------------------------------------------------------
    | The path where the media files will be uploaded
    |--------------------------------------------------------------------------
    */
    'files-path' => '/assets/media/',
    /*
    |--------------------------------------------------------------------------
    | Specify all the allowed file extensions a user can upload on the server
    |--------------------------------------------------------------------------
    */
    'allowed-types' => '.jpg,.png',
    /*
    |--------------------------------------------------------------------------
    | Determine the max file size upload rate
    | Defined in MB
    |--------------------------------------------------------------------------
    */
    'max-file-size' => '5',

    /*
    |--------------------------------------------------------------------------
    | Determine the max total media folder size
    |--------------------------------------------------------------------------
    | Expressed in bytes
    */
    'max-total-size' => 1000000000,

    'website-connections' => [
        'aucklandlive',
        'artgallery',
        'mysql', // <--- This needs to be later removed as its there to save any breaking changes for other project
    ],
];
