<?php
	$link = mysqli_connect('localhost','root','root');
	if(!$link){
		exit('数据库连接失败');
	}
	mysqli_set_charset($link,'utf8');
	mysqli_select_db($link,'test');
	$sql = "select * from user";
	$sqlObject = mysqli_query($link,$sql);
	echo '<table width = "600" border = "1">';
		while($result = mysqli_fetch_assoc($sqlObject)){
			echo '<tr>';
				echo '<td>'.$result['id'].'</td>';
				echo '<td>'.$result['userName'].'</td>';
				echo '<td>'.$result['passWord'].'</td>';
				echo '<td>'.$result['realName'].'</td>';
				echo '<td>'.$result['sex'].'</td>';
				echo '<td>'.$result['age'].'</td>';
				echo '<td><a href = "Delete.php?id='.$result['id'].'">删除</a> / <a href = "Update.php?id='.$result['id'].'">修改</a></td>';
			echo '</tr>';
		}
		
	echo '</table>';
	
	mysqli_close($link);