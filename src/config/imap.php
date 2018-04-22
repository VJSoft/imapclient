<?php

return [

    'default' => 'default',

    'accounts' => [
        'default' => [
            'host'  => env('IMAP_HOST', 'localhost'),
            'port'  => env('IMAP_PORT', 993),
            'encryption'    => env('IMAP_ENCRYPTION', 'ssl'), // Supported: false, 'ssl', 'tls'
            'validate_cert' => env('IMAP_VALIDATE_CERT', true),

//            'username' => env('IMAP_USERNAME', 'root@example.com'),
//            'password' => env('IMAP_PASSWORD', ''),

        //todo: tuk da slojim integraciqta s User class-a.
            'username' => env('IMAP_USERNAME', 'root@example.com'),
            'password' => env('IMAP_PASSWORD', ''),
        ],

        'gmail' => [
            'host' => 'imap.gmail.com',
            'port' => 993,
            'encryption' => 'ssl', // Supported: false, 'ssl', 'tls'
            'validate_cert' => true,
            'username' => 'example@gmail.com',
            'password' => 'PASSWORD',
        ],

        'another' => [
            'host' => '',
            'port' => 993,
            'encryption' => false, // Supported: false, 'ssl', 'tls'
            'validate_cert' => true,
            'username' => '',
            'password' => '',
        ]
    ],
    'icons' => [
        "Inbox" => "fa fa-inbox",
        "Sent" => "glyphicon glyphicon-share",
        "Drafts" => "fa fa-envelope-o",
        "Spam" => "fa fa-ban",
        "Trash" => "fa fa-trash-o",
        "Archive" => "fa fa-trash-o",
        "Junk" => "fa fa-trash-o",
    ],
    'order_assoc' => [
        "Inbox" => 0,
        "Sent" => 1,
        "Drafts" => 2,
        "Spam" => 3,
        "Trash" => 4,
        "Archive" => 5,
        "Junk" => 6
    ],
    'order' => ["Inbox", "Sent", "Drafts", "Spam", "Trash", "Archive", "Junk"],
];
