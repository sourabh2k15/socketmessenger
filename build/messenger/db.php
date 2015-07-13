<?php
 $user = 'root';
 $pass = 'toor';
 $server = 'localhost';
 $datb = 'socketmsgr';
 
 $link = new mysqli($server,$user,$pass,$datb) or die(mysqli_error());
 
?>