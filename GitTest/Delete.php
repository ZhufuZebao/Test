<?php
	$id = $_GET['id'];
	$link = mysqli_connect('localhost','root','root');
	if(!$link){
		exit('连接数据库失败');
	}
	mysqli_set_charset($link,'utf8');
	mysqli_select_db($link,'test');
	$sql = "delete from user where id=$id";
	$result = mysqli_query($link,$sql);
	if($result && mysqli_affected_rows($link)){
		echo '删除成功';
	}else{
		echo '删除失败';
	}