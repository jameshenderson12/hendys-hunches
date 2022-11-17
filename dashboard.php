<?php
// Start the session
session_start();
if (!(isset($_SESSION['login']) && $_SESSION['login'] != "")) {
	header ("Location: index.php");
}

	// Create DB connection
	include 'php/db-connect.php';

	// Get team information from the DB	counting occurrences too
	$sql_getprofileinfo1 = "SELECT avatar, faveteam, fieldofwork, location, tournwinner, signupdate, haspaid, currpos FROM live_user_information WHERE username = '".$_SESSION["username"]."'";
	$sql_getprofileinfo2 = "SELECT lastupdate, points_total FROM live_user_predictions_groups WHERE username = '".$_SESSION["username"]."'";

	// Obtain the SQL query result and set corresponding result variables
	$result1 = mysqli_query($con, $sql_getprofileinfo1);
	$userdata1 = mysqli_fetch_assoc($result1);
	$result2 = mysqli_query($con, $sql_getprofileinfo2);
	$userdata2 = mysqli_fetch_assoc($result2);
	// Assign returned data to variables
	$uppCaseFN = ucfirst($userdata1["firstname"]);
	$uppCaseSN = ucfirst($userdata1["surname"]);
	$avatar = $userdata1["avatar"];
	$fieldofwork = $userdata1["fieldofwork"];
	$location = $userdata1["location"];
	$faveteam = $userdata1["faveteam"];
	$tournwinner = $userdata1["tournwinner"];
	$originalsignupdate = $userdata1["signupdate"];
	$haspaid = $userdata1["haspaid"];
	$currpos = ordinal($userdata1["currpos"]);
	$pointstotal = $userdata2["points_total"];
	$convertedDate = date("l jS \of F", strtotime($originalsignupdate));
	//$matchresult = mysqli_fetch_assoc(mysqli_query($con, $sql_getresults));

	/* If table contains no data, then display 'not available message'
	if ((mysqli_num_rows($result1) == 0) || (mysqli_num_rows($result2) == 0)) {
		// Remove all carriage returns and new lines from array values
		printf("<br><p class='text-center'><strong>No information available yet</strong><br>(Until a <a href='predictions.php'>prediction is made</a>)</p><br><br><br>");
	}
	// Else display the user's available data
	else {
		print("<img src='$avatar' id='avatar' class='img-responsive img-rounded img-thumbnail center-block' alt='User Avatar' name='User Avatar' width='70' style='margin-top:-35px'>");
		printf("<p class='text-center' style='font-size: 1.3em;'><strong>" . $_SESSION["firstname"] . " " . $_SESSION["surname"] . "</strong></p>");
		printf("<p class='text-center 'style='font-size: 1.5em; color: #222;'><strong>%s&nbsp;&nbsp;<span style='color:#CCC;'>|</span>&nbsp;&nbsp;%spts</strong></p>", $currpos, $pointstotal);
		print("<ul class='text-left' style='margin-left:5px; padding:0px; list-style-type:none;'>");
		printf("<li><span class='glyphicon glyphicon-heart' aria-hidden='true'></span>&nbsp;&nbsp;Fan of %s</li>", $faveteam);
		printf("<li><span class='glyphicon glyphicon-book' aria-hidden='true'></span>&nbsp;&nbsp;Works in %s</li>", $fieldofwork);
		printf("<li><span class='glyphicon glyphicon-comment' aria-hidden='true'></span>&nbsp;&nbsp;Thinks %s will win</li>", $tournwinner);
//		printf("<li><span class='glyphicon glyphicon-calendar' aria-hidden='true'></span>&nbsp;&nbsp;Signed up on %s</li>", $convertedDate);
		printf("<li><span class='glyphicon glyphicon-pushpin' aria-hidden='true'></span>&nbsp;&nbsp%s (WC2014), %s (EURO2016)</li>", $wc2014rank, $eu2016rank);
		printf("<li><span class='glyphicon glyphicon-gbp' aria-hidden='true'></span>&nbsp;&nbsp;Paid to play? %s</li>", $haspaid);
		print("</ul>");
		print("<p class='text-center'><a href='change-password.php'>Change password</a></p>");
		//print("<p class='pull-right'><a href='rankings.php'>See current rankings...</a></p>");
	}*/
	// Free result set
	mysqli_free_result($result);
	// Close DB connection
	mysqli_close($con);
}
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Hendy's Hunches: Predictions Game">
    <meta name="author" content="James Henderson">
		<title>Hendy's Hunches: Dashboard</title>
    <?php include "php/config.php" ?>
		<link rel="shortcut icon" href="ico/favicon.ico">
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Ubuntu|Lora">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
		<link rel="stylesheet" href="css/default.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
  </head>

  <body>
		<nav class="navbar navbar-expand-lg navbar-dark bg-dark" aria-label="Offcanvas navbar large">
		    <div class="container">
					<img src="ico/favicon.ico" class="img-fluid bg-light mx-2" style="--bs-bg-opacity: 0.80" width="50px">
		      <a class="navbar-brand" href="#">Hendy's Hunches</a>
		      <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar2" aria-controls="offcanvasNavbar2">
		        <span class="navbar-toggler-icon"></span>
		      </button>
		      <div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="offcanvasNavbar2" aria-labelledby="offcanvasNavbar2Label">
		        <div class="offcanvas-header">
		          <h5 class="offcanvas-title" id="offcanvasNavbar2Label">Hendy's Hunches</h5>
		          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
		        </div>
		        <div class="offcanvas-body">
		          <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
		            <li class="nav-item">
		              <a class="nav-link active" aria-current="page" href="dashboard.php">Home</a>
		            </li>
		            <li class="nav-item">
		              <a class="nav-link" href="predictions.php">My Predictions</a>
		            </li>
								<li class="nav-item">
		              <a class="nav-link" href="rankings.php">Rankings</a>
		            </li>
								<li class="nav-item">
		              <a class="nav-link" href="howitworks.php">How It Works</a>
		            </li>
								<li class="nav-item">
									<a class="nav-link" href="about.php">About</a>
								</li>
		            <li class="nav-item dropdown">
									<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
										<?php returnAvatar();	?>
		              </a>
		              <ul class="dropdown-menu">
		                <li><a class="dropdown-item" href="change-password.php">Change password</a></li>
		                <!--<li><a class="dropdown-item" href="#">Another action</a></li>-->
		                <li>
		                  <hr class="dropdown-divider">
		                </li>
		                <li><a class="dropdown-item" href="php/logout.php">Logout</a></li>
		              </ul>
		            </li>
		          </ul>
		        </div>
		      </div>
		    </div>
		  </nav>

			<main class="container px-4 py-4">
	      <h1>My Dashboard</h1>
	      <p class="lead">Use the dashboard to track your progress.</p>
		      <p>COMING SOON</p>
					<p>For now, please <a href="predictions.php">submit your predictions</a> for the 48 group stage fixtures!</p>
	      <div class="row g-4">
					<div class="col-lg-3">
						<nav class="navbar navbar-expand-lg mx-0">
          <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasSideNavbar">
            <!-- Offcanvas header -->
            <div class="offcanvas-header">
              <button type="button" class="btn-close text-reset ms-auto" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>

            <!-- Offcanvas body -->
            <div class="offcanvas-body d-block px-2 px-lg-0">
              <!-- Card START -->
              <div class="card overflow-hidden">
                <!-- Cover image -->
                <div class="h-50px" style="background-image:url(assets/images/bg/01.jpg); background-position: center; background-size: cover; background-repeat: no-repeat;"></div>
                  <!-- Card body START -->
                  <div class="card-body pt-0">
                    <div class="text-center">
                    <!-- Avatar -->
                    <div class="avatar avatar-lg mt-n5 mb-3">
                      <a href="#!"><img class="avatar-img rounded border border-white border-3" src="assets/images/avatar/07.jpg" alt=""></a>
                    </div>
                    <!-- Info -->
                    <h5 class="mb-0"> <a href="#!"><?php echo $uppCaseFN $uppCaseSN ?></a> </h5>
                    <small>Web Developer at Webestica</small>
                    <p class="mt-3">I'd love to change the world, but they won’t give me the source code.</p>
                    <!-- User stat START -->
                    <div class="hstack gap-2 gap-xl-3 justify-content-center">
                      <!-- User stat item -->
                      <div>
                        <h6 class="mb-0">256</h6>
                        <small>Post</small>
                      </div>
                      <!-- Divider -->
                      <div class="vr"></div>
                      <!-- User stat item -->
                      <div>
                        <h6 class="mb-0">2.5K</h6>
                        <small>Followers</small>
                      </div>
                      <!-- Divider -->
                      <div class="vr"></div>
                      <!-- User stat item -->
                      <div>
                        <h6 class="mb-0">365</h6>
                        <small>Following</small>
                      </div>
                    </div>
                    <!-- User stat END -->
                  </div>

                  <!-- Divider -->
                  <hr>

                  <!-- Side Nav START -->
                  <ul class="nav nav-link-secondary flex-column fw-bold gap-2">
                    <li class="nav-item">
                      <a class="nav-link" href="my-profile.html"> <img class="me-2 h-20px fa-fw" src="assets/images/icon/home-outline-filled.svg" alt=""><span>Feed </span></a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="my-profile-connections.html"> <img class="me-2 h-20px fa-fw" src="assets/images/icon/person-outline-filled.svg" alt=""><span>Connections </span></a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="blog.html"> <img class="me-2 h-20px fa-fw" src="assets/images/icon/earth-outline-filled.svg" alt=""><span>Latest News </span></a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="events.html"> <img class="me-2 h-20px fa-fw" src="assets/images/icon/calendar-outline-filled.svg" alt=""><span>Events </span></a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="groups.html"> <img class="me-2 h-20px fa-fw" src="assets/images/icon/chat-outline-filled.svg" alt=""><span>Groups </span></a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="notifications.html"> <img class="me-2 h-20px fa-fw" src="assets/images/icon/notification-outlined-filled.svg" alt=""><span>Notifications </span></a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="settings.html"> <img class="me-2 h-20px fa-fw" src="assets/images/icon/cog-outline-filled.svg" alt=""><span>Settings </span></a>
                    </li>
                  </ul>
                  <!-- Side Nav END -->
                </div>
                <!-- Card body END -->
                <!-- Card footer -->
                <div class="card-footer text-center py-2">
                  <a class="btn btn-link btn-sm" href="my-profile.html">View Profile </a>
                </div>
              </div>
              <!-- Card END -->

            </div>
          </div>
        </nav>
					</div>

					<div class="col-md-8 col-lg-6">
					</div>
					<div class="col-lg-3">
					</div>
      	</div><!--row-->
				<!-- Site footer -->
				<footer class="mt-auto">
					<hr>
					<p class="small fw-light">Predictions game based on <a href="https://www.fifa.com/fifaplus/en/tournaments/mens/worldcup/qatar2022">FIFA World Cup 2022™</a><br><?=$title?> <?=$version?> &copy; <?=$year?> <?=$developer?>.</p>
				</footer>
			</main>

  </body>
</html>
