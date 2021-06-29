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
    <p>ガントチャート</p>

    <img height="180mm" src="data:image/png;base64, {{ $base64 }}" alt="PNG image" />

    <htmlpagefooter name="page-footer">
        印刷日時 {{ date('Y年m月d日 H:i') }} 
    </htmlpagefooter>

</body>
