<?php
// Start the session
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-119623195-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-119623195-1');
</script>	
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Hendy's Hunches: Predictions Game">
<meta name="author" content="James Henderson">
<link rel="icon" href="ico/favicon.ico">

<title>Hendy's Hunches: Login</title>

<!-- Bootstrap core CSS -->
<link href="css/bootstrap.min.css" rel="stylesheet">

<!-- Custom styles for this template -->
<link href="css/login.css" rel="stylesheet">

<!-- Just for debugging purposes. Don't actually copy this line! -->
<!--[if lt IE 9]><script src="../../docs-assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
  <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
<![endif]-->
</head>

<body>

<div class="container">

    <form id="login" class="form-login" role="form" method="post" action="php/login.php">
        <!--<h2 class="form-login-heading">Title</h2>-->
        <img id="logo" src="img/hh-logo-2018.jpg" alt="Hendy's Hunches Logo" class="center-block img-responsive">
        <div class="center-block text-center">
                <h4><img src="flag-icons/24/russia.png"> 2018 FIFA World Cup Edition</h4>
                
        </div>
        <label for="username" class="sr-only">Username</label>
        <input id="username" name="username" type="text" class="form-control" placeholder="Username" value="<?php
echo $_COOKIE['remember_me']; ?>" required autofocus />
        <!--<font color="orangered" size="+1"><tt><b>*</b></tt></font>-->
        <label for="password" class="sr-only">Password</label>
        <input id="password" name="password" type="password" class="form-control" placeholder="Password" required />
        <!--<font color="orangered" size="+1"><tt><b>*</b></tt></font>-->
        <!--<input type="reset" value="Reset Form" />-->
        <!--<div class="checkbox">
        <label>
        <input type="checkbox" name="remember" value="<?php if(isset($_COOKIE['remember_me'])) {
			  echo 'checked="checked"';
		  }
		  else {
			  echo '';
		  }
		  ?>"> Remember username        
        </label>
        </div>-->
        <button class="btn btn-lg btn-primary btn-block" type="submit">Log In</button>
        <hr />
        <p class="text-center">
        <!--<a href="registration.php">Register To Play</a>&nbsp;&nbsp;|&nbsp;&nbsp; -->
        <a href="forgot-password.php">Reset Password</a>&nbsp;&nbsp;|&nbsp;  
        <a href="" data-toggle="modal" data-target="#HHterms">Terms &amp; Conditions</a>
        </p>
    </form>
    
    
  <!-- Modal -->
  <div id="HHterms" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Hendy's Hunches: Terms &amp; Conditions</h4>
        </div>
        <div class="modal-body" style="font-size: 0.95em;">
          <img src="img/hh-logo-2018.jpg" class="img-responsive center-block" title="Hendy's Hunches Logo" alt="Hendy's Hunches Logo" style="width: 150px; margin-bottom: 10px;">
          <p>By registering to play Hendy's Hunches, you acknowledge that your participation in this game, and the game itself, is only for fun and light-hearted entertainment.</p>
          <p>Only one registration per person is allowed and there is a participation fee of £5 which is to be paid to James Henderson prior to 14th June, 2018. This participation fee comprises a percentage split of charity donation (charity TBC), prize fund and overheads.</p>
          <p>The game is based upon the 2018 FIFA World Cup Russia tournament (all 64 fixtures).</p>
          <p>There will be a minimum of 3 prize funds and this number may be increased depending on the total number of participants. The number of prize funds available, and their amounts, will be indicated in the rankings table shortly after the game commences. Those participants who occupy a prize fund place after the final tournament fixture will receive the corresponding prize amount shortly thereafter. In the event of a shared spot, prizes will be split.</p>
          <p>You are very welcome to invite family and friends to take part but be aware that any unpaid entrance fees will result in a participant being removed from the game.</p>
        </div>
        <!--
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        </div>
        -->
      </div>
    </div>
  </div>
    

</div> <!-- /container -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>
</html>