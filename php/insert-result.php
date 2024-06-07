<?php
	include '../php/process.php';
	insertMatchResult();
?>
<!DOCTYPE html>
<html lang="en-GB">
  <head>
    <meta charset="utf-8">
	<meta http-equiv="refresh" content="5;url=../admin/results.php">    
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="ico/favicon.ico">

    <title>Hendy's Hunches: Administration</title>

    <!-- Bootstrap core CSS -->
    <!--<link href="css/bootstrap.css" rel="stylesheet">-->
    <link href="../css/custom.css" rel="stylesheet">
    
    <style>
    body {
      padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
    }
	label {
	  font-size: 1em;
    }	  
	.dropdown-header {
		font-weight: bold;
		font-style: italic;
	}
	.spinner {
		height:60px;
		width:60px;
		margin:0px auto;
		position:relative;
		-webkit-animation: rotation .6s infinite linear;
		-moz-animation: rotation .6s infinite linear;
		-o-animation: rotation .6s infinite linear;
		animation: rotation .6s infinite linear;
		border-left:6px solid rgba(0,174,239,.15);
		border-right:6px solid rgba(0,174,239,.15);
		border-bottom:6px solid rgba(0,174,239,.15);
		border-top:6px solid rgba(0,174,239,.8);
		border-radius:100%;
	}
	
	@-webkit-keyframes rotation {
	from {-webkit-transform: rotate(0deg);}
	to {-webkit-transform: rotate(359deg);}
	}
	
	@-moz-keyframes rotation {
	from {-moz-transform: rotate(0deg);}
	to {-moz-transform: rotate(359deg);}
	}
	
	@-o-keyframes rotation {
	from {-o-transform: rotate(0deg);}
	to {-o-transform: rotate(359deg);}
	}
	
	@keyframes rotation {
	from {transform: rotate(0deg);}
	to {transform: rotate(359deg);}
	}
    </style>

    <!-- Custom styles for this template -->
    <link href="../css/starter-template.css" rel="stylesheet">

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
  
  <?php include "../includes/navbar.php" ?>

      <div class="starter-template">
        <h1>Match Results (Admin)</h1>
        <p class="lead" style="color: red">This page is used to record the match results by administrator only.</p>

	      <h2>Success</h2>
      
      		<div class="row">	            
      		<div class="col-xs-12"> 
        	<p>The match result has been recorded successfully.</p>
            <p>Redirecting to the administration results page...</p><div class="spinner"></div>
            </div><!--col-xs-12-->
       
      	</div><!--row-->
      
      </div><!--starter-template-->
     
      <!-- Site footer -->
      <div class="footer">
      <?php include "../includes/footer.php" ?>
      </div>       
     
    </div><!-- /.container -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
  </body>
</html>