<?php
return [
    'disable' => env('FIREBASE_DISABLE', ''),
    'token' => env('FIREBASE_TOKEN', ''),
    'apikey' => env('FIREBASE_API_KEY', ''),
    'linkDomain' => env('FIREBASE_LINK_DOMAIN', ''),
    'iosScheduleBundleId' => env('IOS_SCHEDULE_BUNDLE_ID', ''),
    'iosChatBundleId' => env('IOS_CHAT_BUNDLE_ID', ''),
    'androidChatPackageName' => env('ANDROID_CHAT_PACKAGE_NAME', 'site.conit.chat'),
    'androidSchedulePackageName' => env('ANDROID_SCHEDULE_PACKAGE_NAME', 'site.conit.schedule'),
    // 中国側テスト用設定
    'httpProxy' => [
        'proxy' => env('FIREBASE_HTTP_PROXY', ''),
    ],
];
