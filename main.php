<?php
/**
 * Main.php
 *
 * This is an example of the main page of a website. Here
 * users will be able to login. However, like on most sites
 * the login form doesn't just have to be on the main page,
 * but re-appear on subsequent pages, depending on whether
 * the user has logged in or not.
 *
 * Written by: Jpmaster77 a.k.a. The Grandmaster of C++ (GMC)
 * Last Updated: June 15, 2011 by Ivan Novak
 */
include("include/session.php");
$page = "main.php";
/**
 * User has already logged in, so display relavent links, including
 * a link to the admin center if the user is an administrator.
 */
	if($session->logged_in){
	    //echo "logged in" ;
	    header('Location: index.php');
	}
	else{
?>

<html>
  <head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>Player Analytics</title>
    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Add custom CSS here -->
    <link href="css/signin.css" rel="stylesheet">
  </head>
  <body>
	<div id="main">
	  <div id="login">
	    <div class="container">
            <?php
            /**
             * User not logged in, display the login form.
             * If user has already tried to login, but errors were
             * found, display the total number of errors.
             * If errors occurred, they will be displayed.
             */
            if($form->num_errors > 0){
               echo "<div class=\"alert alert-danger\">".$form->num_errors." error(s) found</div>";
            }
            ?>
		  <form class="form-signin" action="include/process.php" method="POST">
		    <h2 class="form-signin-heading">Please sign in</h2>
		    <?php echo $form->error("user"); ?>
		    <input type="text" name="user" maxlength="30" class="form-control" placeholder="Username" value="<?php echo $form->value("user"); ?>" required autofocus>
		    <?php echo $form->error("pass"); ?>
		    <input type="password" name="pass" maxlength="30" class="form-control" placeholder="Password" value="<?php echo $form->value("pass"); ?>" required>
		    <input type="hidden" name="sublogin" value="1">
		    <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
		  </form>
      	  <p style="text-align:center"><br/><a href="forgotpass.php">Forgot Password?</a></p>
		</div> <!-- /container -->
	  </div><!-- #login -->
<?php
}
?>
	</div><!-- #main -->
    <script type="text/javascript">
        jQuery(function($){
            <?php
            if(isset($_GET['hash'])){
                $hash = $_GET['hash'];
            } else {
                $hash = '';
            }
            ?>
            jp_hash = ('<?php echo $hash; ?>'.length)?'<?php echo $hash; ?>':window.location.hash;
            if(jp_hash){
                $.ajax({
                    type: "POST",
                    url: 'process.php',
                    data: 'login_with_hash=1&hash='+jp_hash,
                    success: function(msg){
                        if(msg){
                            alert(msg);
                            window.location.href = "main.php";
                        } else {
                            alert("Invalid Hash");
                        }
                    }
                });
            }
        });
    </script>
  </body>
</html>