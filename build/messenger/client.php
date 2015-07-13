<?php
    session_start();
    if(isset($_SESSION['user'])){
        include_once"db.php";
        $uname = $_SESSION['user'];
        $client_ip = '';
        $stmt = $link->prepare("SELECT ip FROM users WHERE user=?");
        $stmt->bind_param('s',$uname);
        $stmt->bind_result($client_ip);
        $stmt->execute();
        $stmt->fetch();
        $stmt->close();
        if($client_ip){
?>    
<html> 
    <head>
        <title>Anonymous Messenger</title>
        <link rel="stylesheet" href="css/client.css">
        <link rel="icon" href="images/favicon.png"/>
        <script src="js/jquery.js"></script>
        <script src="js/connection.js"></script>
        <script>
            var server = "<?php echo $_SERVER['SERVER_ADDR']?>";
            var currentip = '';
        </script>
    </head>
    <body>
        <div class="container">
            <div class="sidebar">
            <div class="band">Messenger</div>    
            <?php
                $iterator = 0;
                $users = array();    
                $result = $link->query("SELECT * FROM users");
                while($row = $result->fetch_object()){
                    echo "<div onclick='openmsg(this)' id='user".$iterator."' data-ip='$row->ip' class='usertag'><img class='circle' src='images/catpics/".($iterator%7).".jpg' /><span>".$row->user."</span></div>";
                    if($row->user==$_SESSION['user']){
                        echo"<script type='text/javascript'> currentip = '".$row->ip."';</script>";
                    }
                    $iterator++;
                }    
            ?>
           </div>
           <div class="msgpanel">
                <div class="nameband" id='nameband'>
                    <div class="band">
                        <span class='recname' id='recname' ><?php echo $_SESSION['user']?></span>
                        <span class="link" onclick="logout()">logout</span>
                    </div>
                </div>
                <div class="msgframe" id='msgframe'>
                </div>
           </div>
        </div>
        <div class='msgdiv'><input id='msg' type="text" name='msg' placeholder="type your message in here" class="msginput"></div>
    </body>
</html>
<?php }
    }else{
        header('Location:index.php?logout');
    }
?>