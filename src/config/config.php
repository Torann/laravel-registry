<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Default registry table
    |--------------------------------------------------------------------------
    |
    | Default table name. You may change it to whatever you prefer
    |
    */

    'table' => 'system_registries',

    /*
    |--------------------------------------------------------------------------
    | Cache timestamp
    |--------------------------------------------------------------------------
    |
    | Used for multi-instance web servers. This can be used to ensure
    | the registry for all instances are kept up to date.
    |
    | For Redis: \\Torann\\Registry\\Timestamps\\Redis
    |
    */

    'timestamp_manager' => '',
);
