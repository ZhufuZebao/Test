{{-- 
$model \App\Project
$base64 (string) PNG image 
 --}}
<head>
    <style type="text/css">
    body {
	    font-family: 'ipa-mincho', serif;
    }
    @page {
	 header: page-header;
	 footer: page-footer;
    }
    </style>
</head>
<body>
    <p>{{ $model->name }}</p>

    <img height="180mm" src="data:image/png;base64, {{ $base64 }}" alt="ガントチャート" />

    <htmlpagefooter name="page-footer">
        印刷日時 {{ date('Y年m月d日 H:i') }} 
    </htmlpagefooter>

</body>
