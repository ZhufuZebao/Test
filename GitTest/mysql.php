<?php
	//连接数据库
	$link = mysqli_connect("localhost","root","root");
	//判断是否连接成功
	if(!$link){
		exit('数据库连接失败');
	}
	//设置字符集
	mysqli_set_charset($link,'utf8'); 
	//选择数据库
	mysqli_select_db($link,'test');
	//准备sql语句
	$sql = "select * from user";
	//发送sql语句
	$res = mysqli_query($link,$sql);
	
	//处理结果集
	while($result = mysqli_fetch_assoc($res)){
		var_dump($result);
	}
	/*$result = mysqli_fetch_assoc($res);
	var_dump($result);*/
	//关闭数据库
	mysqli_close($link);
	
/*	
	$result = mysql_query($link,$sql)	//返回一个对象
	mysqli_fetch_assoc($result)			//一个一个往下读的，返回的时候一个一维的关联数组
	mysqli_fetch_array($result)			//返回一个有索引又有关联的数组
	mysql_num_rows($result)				//返回查询时候的结果集的总条数
	mysqli_affected_rows($link)			//返回你修改的，删除，添加的时候的受影响行数
	mysqli_insert_id($link)				//返回的是你插入的当前的数据的自增的id
*/	
