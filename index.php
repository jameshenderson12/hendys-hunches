<?php
// Start the session
session_start();
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Hendy's Hunches: Predictions Game">
    <meta name="author" content="James Henderson">
		<title>Hendy's Hunches: Login</title>
    <?php include "php/config.php" ?>
		<link rel="shortcut icon" href="ico/favicon.ico">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <link href="css/login.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
	</head>

	<body class="d-flex h-100 text-center text-bg-dark">

  	<div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
  		<header class="mb-auto">
  			<div>
  				<h3 class="float-md-start mb-0">Hendy's Hunches</h3>
  				<nav class="nav nav-masthead justify-content-center float-md-end">
  					<a class="nav-link fw-bold py-1 px-0" href="registration.php">Register</a>
  					<a class="nav-link fw-bold py-1 px-0" href="forgot-password.php">Reset Password</a>
            <a class="nav-link fw-bold py-1 px-0" href="#" data-bs-toggle="modal" data-bs-target="#terms">Terms</a>
  				</nav>
  			</div>
  		</header>

  		<main class="px-3">
  			<h1>Welcome</h1>
  			<img src="img/qatar-2022-logo.png" alt="Qatar 2022 edition of Hendy's Hunches" class="w-50 mb-3">

        <form id="login" role="form" method="post" action="php/login.php" class="border border-white p-2 my-2 border-opacity-25">

            <div class="mb-3 row d-flex justify-content-center">
              <label for="username" class="col-sm-2 col-form-label">Username</label>
              <div class="col-sm-8">
                <input type="text" class="form-control" id="username" name="username" value="<?php echo $_COOKIE['remember_me']; ?>" required>
              </div>
            </div>
            <div class="mb-3 row d-flex justify-content-center">
              <label for="password" class="col-sm-2 col-form-label">Password</label>
              <div class="col-sm-8">
                <input type="password" class="form-control" id="password" name="password" required>
              </div>
            </div>

            <div class="form-check form-switch">
              <input id="remember" name="remember" type="checkbox" class="form-check-input" role="switch" value="<?php if(isset($_COOKIE['remember_me'])) { echo 'checked="checked"'; } else { echo ''; } ?>">
              <label class="form-check-label ml-3" for="remember">Remember my username?</label>
            </div>
            <hr />
          <!--<p class="lead">Pit your FIFA World Cup 2022 predictions against others for a chance to earn a prize spot or bragging rights in the rankings.</p>-->
    			<p class="lead">
    				<a href="#" class="btn btn-lg btn-primary fw-bold" type="submit"><i class="fw-bold bi bi-box-arrow-in-right"></i> Log in</a>
    			</p>
        </form>
  		</main>

      <!-- HH Terms Modal -->
      <div class="modal fade" id="terms" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5" id="staticBackdropLabel">Hendy's Hunches: Terms &amp; Conditions</h1>
              <!--<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>-->
            </div>
            <div class="modal-body">
              <img src="img/hh-logo-2018.jpg" class="img-responsive mt-auto" title="Hendy's Hunches Logo" alt="Hendy's Hunches Logo" style="width: 180px; margin-bottom: 10px;">
              <p>By registering to play Hendy's Hunches, you acknowledge that:</p>
              <ul>
                <li>your involvement in this game, and the game itself, is intended only for entertainment; it is not a gambling site</li>
                <li>the game is based on FIFA World Cup Qatar 2022™</li>
                <li>only one registration per person is permitted although family and friends are welcome to participate</li>
                <li>an entry fee of £5 is to be paid prior to 20/11/2022; split for charity (TBC) donation and prize funds</li>
                <li>an unpaid entry fee results in removal from the game</li>
                <li>the number of prize funds, and their amounts, are revealed in due course, awarded to winners after the final tournament fixture and, in the event of a shared winning spot, divided accordingly.</li>
              </ul>
            </div>
            <div class="modal-footer">
              <!--<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>-->
              <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Understood</button>
            </div>
          </div>
        </div>
      </div>

      <footer class="mt-auto">
        <p>Predictions game based on <a href="https://www.fifa.com/fifaplus/en/tournaments/mens/worldcup/qatar2022" class="text-white">FIFA World Cup Qatar 2022™</a></p>
        <p><?php echo $title $version &copy; $year $developer; ?>.</p>
      </footer>

	  </div>
  </body>
</html>
