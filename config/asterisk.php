<?php

return [

    'default' => env("ASTERISK_SERVER", "asterisk"),

    'asterisk' => [
        'host' => env("ASTERISK_HOST", "127.0.0.1"),
        'port' => env("ASTERISK_PORT", "5038"),
        'username' => env("ASTERISK_AMI_USERNAME", "root"),
        'secret' => env("ASTERISK_AMI_SECRET", "secret"),
    ],

];