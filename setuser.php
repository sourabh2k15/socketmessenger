<?php
session_start();
$_SESSION['user'] = '';
 include_once "db.php";
 $uname = isset($_POST['uname'])?$_POST['uname']:'';
 $ip    = isset($_POST['ip'])?$_POST['ip']:'';
 
 echo $uname." ".$ip;  
 
 $stmt = $link->prepare("INSERT INTO users(user,ip)VALUES(?,?)");
 $stmt->bind_param("ss",$uname,$ip);
 $stmt->execute();
 $stmt->close();
$_SESSION['user'] = $uname;
?> 