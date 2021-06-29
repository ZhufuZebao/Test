<html>
<head>
<title>Android</title>
</head>
<body>
<?php
//print_r($val);
//print_r($input);
print_r($data);

?>
<form action="{{ url('/logincheck') }}">
<input type="hidden" value="{!! $data !!}" />
<input type="submit">
</form>
</body>
</html>