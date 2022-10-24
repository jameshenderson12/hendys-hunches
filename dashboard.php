<?php
// Start the session
session_start();
if (!(isset($_SESSION['login']) && $_SESSION['login'] != "")) {
	header ("Location: index.php");
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Hendy's Hunches: Predictions Game">
	<meta name="author" content="James Henderson">
    <link rel="icon" href="ico/favicon.ico">

    <title>Hendy's Hunches: Home</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom styles for this template -->
    <link href="css/custom.css" rel="stylesheet">
    <link class="include" rel="stylesheet" type="text/css" href="css/jquery.jqplot.min.css" />
	<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.2.min.js"></script>
	
	<?php include 'php/dashboard-items.php'; ?>
    
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>	
  
    <!-- Navigation menu for lg, md display -->
    <nav class="navbar navbar-inverse navbar-fixed-top col-md-12 hidden-xs">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <!--<a class="navbar-brand" href="#">Project name</a>-->
          <img src="img/hh-logo-v8.png" class="img-responsive" style="margin: 5px 0px 0px; height:45px">
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-left" style="margin-left: 15px;">
            <li class="active"><a href="dashboard.php">Home</a></li>
            <li><a href="predictions.php">My Predictions</a></li>
            <li><a href="rankings.php">Rankings</a></li>
            <li><a href="howitworks.php">How It Works</a></li>
			<li><a href="about.php">About</a></li>                        
          </ul>
          <!--
          <form class="navbar-form navbar-right">
            <input type="text" class="form-control" placeholder="Search...">
          </form>-->
        </div>
        <div class="col-md-12">   
           <?php		 
			// Echo session variables that were set on previous page
			echo "<span id='login'><span style='color: white'><p>Logged in as: <span class='glyphicon glyphicon-user'></span>&nbsp;<span style='font-weight:bold'>" . $_SESSION["firstname"] . " " . $_SESSION["surname"] . "</span> ( <a href='php/logout.php'>Logout</a> )</p></span></span>";
          ?>
        </div>
      </div>
    </nav>
    
    <!-- Navigation menu for xs display -->
    <nav class="navbar navbar-inverse navbar-fixed-top col-md-12 visible-xs hidden-lg hidden-md">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar2" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <!--<a class="navbar-brand" href="#">Project name</a>-->
          <img src="img/hh-logo-v8.png" class="img-responsive" style="margin: 5px 0px 0px; height:45px">
        </div>
        <div id="navbar2" class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-left">
            <li class="active"><a href="dashboard.php"><span class="glyphicon glyphicon-home" aria-hidden="true"></span>&nbsp;&nbsp;Home</a></li>
            <li><a href="predictions.php"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span>&nbsp;&nbsp;My Predictions</a></li>
            <li><a href="rankings.php"><span class="glyphicon glyphicon-th-list" aria-hidden="true"></span>&nbsp;&nbsp;Rankings</a></li>
            <li><a href="howitworks.php"><span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>&nbsp;&nbsp;How It Works</a></li>
			<li><a href="about.php"><span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>&nbsp;&nbsp;About</a></li>            
            <li><a href="php/logout.php"><span class='glyphicon glyphicon-user' aria-hidden="true"></span>&nbsp;&nbsp;Logout</a></li>                        
          </ul>
        </div>
      </div>
    </nav>
    

		<div id="main" class="col-md-12">
          <!--<h1 class="page-header hidden">Home</h1>-->
          <!--<div class="col-md-12 alert alert-warning"></div>-->
          
          <div class="alert alert-danger alert-dismissible" role="alert">
    	      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        	  <strong>Please Note:</strong> Keep your login details handy as you will soon be able to predict for the knockout fixtures.
	      </div>
          
                
           <!-- First Column (3 Span) -->
           <div class="col-md-4 col-lg-3">
                
            	<div id="profile">
                	<div class="panel panel-success">
               			<div class="panel-heading">My Profile</div>
                			<div class="panel-body" style="padding: 0px;">
                            	<div id="profile-img" class="col-md-12 hidden-xs hidden-sm">
                      				<img src="img/stadium-sml.jpg" class="img-responsive" alt="Profile Image">
                    			</div>
                        		<div id="personal-info" class="col-md-12">
                        			<?php displayPersonalInfo(); ?>
                        		</div>                                
                            </div><!-- panel-body -->
	      	        </div><!-- panel -->
    			</div><!-- profile -->
                
            	<div id="latest-recruits">
            	<!--<img data-src="holder.js/200x200/auto/sky" class="img-responsive" alt="Generic placeholder thumbnail">-->
            		<div class="panel panel-success">
               			<div class="panel-heading">Latest Game Information</div>
                			<div class="panel-body text-left">
              					<?php displayLatestInformation(); ?>
	                		</div><!-- panel-body -->
	      			</div><!-- panel -->
    			</div><!-- latest recruits -->
                
            	<div id="charity">            	
            		<div class="panel panel-success">
               			<div class="panel-heading">Charity Fundraising</div>
                			<div class="panel-body text-left">
              					<img src="img/bb-logo-black.jpg" class="img-responsive">
                                <?php displayCharityInformation(); ?>                                
	                		</div><!-- panel-body -->
	      			</div><!-- panel -->
    			</div><!-- charity -->                
            
            	<div id="top-supported" class="hidden-xs">
            		<div class="panel panel-success">
               			<div class="panel-heading">Players' Favourite Teams</div>
                			<div class="panel-body text-left">                            	
              					<div id="chart3" style="height:100%; width:100%;"></div>
	                		</div><!-- panel-body -->
	      	        	</div><!-- panel -->
    			</div><!-- top supported teams -->
                
            	<div id="voted-winner" class="hidden-xs">
           		<div class="panel panel-success">
               			<div class="panel-heading">Voted To Win</div>
                			<div class="panel-body text-left">
              					<div id="chart2" style="height:100%; width:100%;"></div>
	                		</div><!-- panel-body -->
	      	        	</div><!-- panel -->
    			</div><!-- voted winner -->                                              
                
			</div><!-- First Column (3 Span) -->             



			<!-- Second Column (6 Span) -->
			<div class="col-md-8 col-lg-6">                                         
                                  
                <div id="twitter-feed">
					<div class="panel panel-success">
               			<div class="panel-heading">Latest News from Social Media</div>
                			<div class="panel-body text-center">
                            
                            <!-- Facebook Timeline Widget -->                            
<div class="fb-page" data-href="https://www.facebook.com/uefaeuro" data-tabs="timeline" data-width="500" data-height="550" data-small-header="true" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="false"><blockquote cite="https://www.facebook.com/uefaeuro" class="fb-xfbml-parse-ignore"><a href="https://www.facebook.com/uefaeuro">UEFA EURO</a></blockquote></div>                       
                            
              					<!-- Twitter Timeline Widget 
                                <a class="twitter-timeline"  href="https://twitter.com/Euro16Updates" data-widget-id="737917608683507712" data-chrome="noheader nofooter noborders">Tweets by @Euro16Updates</a>
                                <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
                                -->
                                                                                         
	                		</div><!-- panel-body -->
	      	        </div><!-- panel -->
    			</div><!-- Social media feed -->
                
                
            	<div id="msg-board">
                	<div class="panel panel-success">
               			<div class="panel-heading">The Hendy's Hunches Banter Board</div>
                			<div class="panel-body" style="padding: 0px;">
              					<iframe src="mboard/mboard.php" scrolling="no" frameborder="0"></iframe>
                            </div><!-- panel-body -->
	      	        </div><!-- panel -->
    			</div><!-- msg-board -->                                

			</div><!-- Second Column (6 Span) -->
            


			<!-- Third Column (3 Span) -->
			<div class="col-md-8 col-lg-3"> 
            
                <div id="todays-games">
            		<div class="panel panel-success">
               			<div class="panel-heading">Today's Matches</div>
                			<div class="panel-body">
              					<?php displayTodaysFixtures(); ?>
	                		</div><!-- panel-body -->
	      	        </div><!-- panel -->
    			</div><!-- today's games -->
            
            	<div id="top-rankings">
            		<div class="panel panel-success">
               			<div class="panel-heading">Top Players</div>
                			<div class="panel-body text-left">
              					<?php displayTopRankings(); ?>
	                		</div><!-- panel-body -->
	      	        </div><!-- panel -->
    			</div><!-- top rankings -->
            
            	<div id="bottom-rankings">
            	<!--<img src="holder.js/200x200/auto/sky" class="img-responsive" alt="">-->
            	<div class="panel panel-success">
               		<div class="panel-heading">Bottom Players</div>
                		<div class="panel-body text-left">
			            	<?php displayBottomRankings(); ?>
	                	</div><!-- panel-body -->
	      	        </div><!-- panel -->
    			</div><!-- bottom rankings -->  
                
            	<div id="best-movers">
            		<div class="panel panel-success">
               			<div class="panel-heading">Best Movers</div>
                			<div class="panel-body text-left">
			            		<?php displayBestMovers(); ?>
	                		</div><!-- panel-body -->
	      	        </div><!-- panel -->
    			</div><!-- best movers -->

            	<div id="worst-movers">
            		<div class="panel panel-success">
               			<div class="panel-heading">Worst Movers</div>
                			<div class="panel-body text-left">
              					<?php displayWorstMovers(); ?>
	                		</div><!-- panel-body -->
	      	        </div><!-- panel -->
    			</div><!-- worst movers -->                                                                  
                        
            </div><!-- Third Column (3 Span) -->
  
                                    
<script type="text/javascript">
  // Example of data input required...    
    var teams = [
    	[1, 'Aberdeen'],[1, 'Accrington Stanley'],[1, 'AFC Wimbledon'],[3, 'Arsenal'],[1, 'Aston Villa'],[1, 'Coventry City'],[1, 'Cowdenbeath'],
		[6, 'Derby County'],[2, 'Everton'],[1, 'Gillingham'],[2, 'Heart of Midlothian'],[5, 'Hibernian'],[4, 'Leicester City'],[4, 'Liverpool'],
		[1, 'Manchester City'],[2, 'Manchester United'],[1, 'Mansfield Town'],[3, 'Newcastle United'],[22, 'Nottingham Forest'],
		[4, 'Notts County'],[1, 'Oxford United'],[2, 'Rangers'],[1, 'Stockport County'],[1, 'Sheffield Wednesday'],
		[1, 'Tottenham Hotspur'],[1, 'Wigan Athletic']
  	];
	
	var nations = [
		[3, 'Belgium'],[2, 'Croatia'], [14, 'England'],[21, 'France'],[14, 'Germany'],[3, 'Iceland'],
		[6, 'Italy'],[5, 'Portugal'],[2, 'Republic of Ireland'],[1, 'Romania'],[6, 'Spain'],[1, 'Wales']
	];
    
	$(document).ready(function(){
        plot3 = $.jqplot('chart3', [teams], {
			//animate: !$.jqplot.use_excanvas,
            seriesDefaults:{
                renderer:$.jqplot.BarRenderer,
                rendererOptions: {
                    barDirection: 'horizontal'
                },
				pointLabels: { show: true }
            },
            axes: {
                yaxis: {
                    renderer: $.jqplot.CategoryAxisRenderer
                }
            }
        });
    });
	
	$(document).ready(function(){
        plot2 = $.jqplot('chart2', [nations], {
			seriesColors:['#cc3300', '#ffb3b3', '#EEE', '#000080', 'grey', '#4d4dff', '#0000ff', 'purple', 'green', 'yellow', 'red', 'red'],
            seriesDefaults:{
                renderer:$.jqplot.BarRenderer,
                rendererOptions: {
                    barDirection: 'horizontal',
					varyBarColor: true
                },
				pointLabels: { show: true }
            },
            axes: {
                yaxis: {
                    renderer: $.jqplot.CategoryAxisRenderer
                }
            }
        });
    });		
</script>

<script type="text/javascript">
   window.onorientationchange = function() { 
        var orientation = window.orientation; 
            switch(orientation) { 
                case 0: window.location.reload(); 
                break; 
                case 90: window.location.reload(); 
                break; 
                case -90: window.location.reload(); 
                break; } 
    };	
</script>

          
    </div><!-- main-section -->


	<!-- All JQPLOT includes necessary for forming charts -->
    <script class="include" language="javascript" type="text/javascript" src="js/jquery.jqplot.min.js"></script>
    <script class="include" language="javascript" type="text/javascript" src="js/jqplot.pieRenderer.min.js"></script>
	<script class="include" language="javascript" type="text/javascript" src="js/jqplot.barRenderer.min.js"></script>
	<script class="include" language="javascript" type="text/javascript" src="js/jqplot.categoryAxisRenderer.min.js"></script>
    <script type="text/javascript" src="js/jqplot.pointLabels.min.js"></script>
    
    <!-- iFrame resize libraries -->
    <script class="include" language="javascript" type="text/javascript" src="js/iframeResizer.min.js"></script>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>-->
    <script src="js/bootstrap.min.js"></script>
    <!--<script src="js/docs.min.js"></script>-->
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <!--<script src="js/ie10-viewport-bug-workaround.js"></script>-->
    <script language="javascript" type="text/javascript">
	  iFrameResize({
				log                     : true,                  // Enable console logging
				enablePublicMethods     : true,                  // Enable methods within iframe hosted page
				resizedCallback         : function(messageData){ // Callback fn when resize is received
					$('p#callback').html(
						'<b>Frame ID:</b> '    + messageData.iframe.id +
						' <b>Height:</b> '     + messageData.height +
						' <b>Width:</b> '      + messageData.width + 
						' <b>Event type:</b> ' + messageData.type
					);
				},
				messageCallback         : function(messageData){ // Callback fn when message is received
					$('p#callback').html(
						'<b>Frame ID:</b> '    + messageData.iframe.id +
						' <b>Message:</b> '    + messageData.message
					);
					alert(messageData.message);
				},
				closedCallback         : function(id){ // Callback fn when iFrame is closed
					$('p#callback').html(
						'<b>IFrame (</b>'    + id +
						'<b>) removed from page.</b>'
					);
				}
			});
	</script>
<!-- Facebook Feed Script -->
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v2.6";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

<!-- Feedback Script for Modal 

<script>(function(t,e,o,s){var c,n,i;t.SMCX=t.SMCX||[],e.getElementById(s)||(c=e.getElementsByTagName(o),n=c[c.length-1],i=e.createElement(o),i.type="text/javascript",i.async=!0,i.id=s,i.src=["https:"===location.protocol?"https://":"http://","widget.surveymonkey.com/collect/website/js/W61d_2BmwqZfNBspJ6JwJcUkAroZxf5iT0EYiVRY_2BCdFPN9CtYX48a6LjNDKgIm2li.js"].join(""),n.parentNode.insertBefore(i,n))})(window,document,"script","smcx-sdk");</script><a style="font: 12px Helvetica, sans-serif; color: #999; text-decoration: none;" href=https://www.surveymonkey.com/mp/customer-satisfaction-surveys/> Create your own user feedback survey </a>

-->

  </body>
</html>
