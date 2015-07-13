<?php
  session_start();
  if(isset($_GET['logout'])){
      include_once "db.php";
      $stmt = $link->prepare("DELETE FROM users WHERE user = ?");
      $stmt->bind_param('s',$_SESSION['user']);
      $stmt->execute();
      $stmt->close();
      session_destroy();
      header('Location: index.php');
  }
  else if(isset($_SESSION['user'])&&$_SESSION['user']!==''){
      header('Location: client.php');
  }
  else{
?>
<html>
    <head>
        <title>(: Welcome _/\_ dudes !!</title>
        <link rel="icon" href="images/favicon.png"/>
        <link rel="stylesheet" href="css/index.css">
    </head>
    <body>
        <div class="container">
            <div class="entry">
                <div class="logo"><img src="images/logo.png"></div>
                <div class="tag">Anonymous Messenger</div>
                <div class="formcontent">
                    <div>
                     <input type='text'   name='uname' id='uname' placeholder='choose a nickname'>
                     <input type='hidden' name='ip' id='ip' value="<?php echo $_SERVER['REMOTE_ADDR']?>">
                    </div>    
                    <input type='submit' onclick='submit()' value='join chat'>
                </div>    
            </div>
            <div class="brand">
                <img src="images/mac.png">
            </div>    
        </div>    
        <script src="js/jquery.js"></script>
        <script>
            function val(el){
                return document.getElementById(el).value;
            }
            
            function submit(){
                $.ajax({
                    type :'post',
                    url  :'setuser.php',
                    data :{uname:val('uname'),ip:val('ip')},
                    success: function(data){
                        console.log(data);
                        location.href = 'client.php';
                    },
                    error:function(){
                        console.log("some error occurred");
                    }
                });
            }
        </script>
    </body>
</html>
<?php 
      }?>