<?php
	$id = $_GET['id'];
	$link = mysqli_connect('localhost','root','root');
	if(!$link){
		exit('连接数据库失败');
	}
	mysqli_set_charset($link,'utf8');
	mysqli_select_db($link,'test');
	$sql = "select * from user where id = $id";
	$sqlObject = mysqli_query($link,$sql);
	var_dump($sqlObject);
	$raws = mysqli_fetch_assoc($sqlObject);
	var_dump($raws);
?>
<html>
	<form action = "doupdate.php">
		<input type = "hidden" value = "<?php echo $id;?>" name = "id"/>
		姓名：<input type = "text" value = "<?php echo $raws['userName']?>"/><br/>
		密码：<input type = "text" value = "<?php echo $raws['passWord']?>"/><br/>
		real：<input type = "text" value = "<?php echo $raws['realName']?>"/><br/>
		性别：<input type = "text" value = "<?php echo $raws['sex']?>"/><br/>
		年龄：<input type = "text" value = "<?php echo $raws['age']?>"/><br/>
		<input type = "submit" value = "执行修改"/>
	</form>
</html>