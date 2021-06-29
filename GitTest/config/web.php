<?php

return [

    'cache' => [
        'email_verification' => [
            // メール検証有効期限（分）
            'expiration_minutes' => env('EMAIL_VERIFICATION_EXPIRATION_MINUTES', 30),
        ]
    ],

    'baseUrl' => env('APP_DIR', ''),
    'termsOfUse' => env('APP_TERMS_OF_USE', ''),

    // アップロード画像の格納場所
    'imageUpload' => [
        'disk' => env('APP_UPLOAD_DISK', 's3'),
        // アップロード画像のサイズ制限（M）
        'sizeLimit' => env('APP_IMAGE_SIZE_LIMIT', 5),
    ],
    // SkyWayのAPIキー
    'skyway' => [
        'secretkey' => env('APP_SKYWAY_SECRET_KEY', ''),
    ],
    'pdfUpload' => [
        'disk' => 'local'
    ],
    'vision' =>  env('VISION', 'Ver1.0.0'),
    //firebase v1 base_url
    'baseUrlId'=>env('BASE_URL_ID'),

    // ページサイズ設定
    'pageSize' => [
        'chat' => [
            'content' => env('MIX_PAGE_SIZE_CHAT_CONTENT', 100),
            'contact' => env('MIX_PAGE_SIZE_CHAT_CONTACT', 100),
            'project' => env('MIX_PAGE_SIZE_CHAT_PROJECT', 100),
            'task' => env('MIX_PAGE_SIZE_CHAT_TASK', 100),
            'fileList' => env('MIX_PAGE_SIZE_CHAT_FILE_LIST', 100),
        ]
    ],
    'db_database_doc' => env('DB_DATABASE_DOC', ''),
];
