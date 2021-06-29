<?php

return [
	'mode'                  => 'utf-8',
	'format'                => 'A4',
	'author'                => '',
	'subject'               => '',
	'keywords'              => '',
	'creator'               => 'Laravel Pdf',
	'display_mode'          => 'fullpage',
    'tempDir'               => base_path('storage/app/upload'),

	'font_path' => base_path('resources/fonts/'),
	'font_data' => [
		'rounded-mplus' => [
			'R'  => 'rounded-mplus-1c-regular.ttf',
			'B'  => 'rounded-mplus-1c-bold.ttf',
            'useOTL' => 0xFF,    // required for complicated langs like Persian, Arabic and Chinese
            'useKashida' => 75,  // required for complicated langs like Persian, Arabic and Chinese
		],
		'ipa-mincho' => [
			'R'  => 'ipaexm.ttf',
		],
		// ...add as many as you want.
	]
];
