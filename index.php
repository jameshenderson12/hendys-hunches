<?php
// Start the session
session_start();
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Hendy's Hunches: Predictions Game">
    <meta name="author" content="James Henderson">
		<title>Hendy's Hunches: Login</title>
		<link rel="shortcut icon" href="ico/favicon.ico">
		<link rel="canonical" href="https://getbootstrap.com/docs/5.2/examples/cover/">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <!--<link href="css/login.css" rel="stylesheet">-->
		<style>
		/* Custom default button */
		.btn-secondary,
		.btn-secondary:hover,
		.btn-secondary:focus {
		  color: #333;
		  text-shadow: none; /* Prevent inheritance from `body` */
		}
		body {
		  /*text-shadow: 0 .05rem .1rem rgba(0, 0, 0, .5);*/
		  /*box-shadow: inset 0 0 5rem rgba(0, 0, 0, .5);*/
			background-image: url(img/football-stadium.jpg);
		}
		main {
			background: rgba(10, 20, 50, 0.5);
			border-radius: 10px;
			padding: 1em;
			box-shadow: inset 0 0 5rem rgba(0, 0, 0, .5);
      text-shadow: 0 .05rem .1rem rgba(0, 0, 0, .5);
		}
		.cover-container {
		  max-width: 42em;
		}
		.nav-masthead .nav-link {
		  color: rgba(255, 255, 255, .5);
		  border-bottom: .25rem solid transparent;
		}
		.nav-masthead .nav-link:hover,
		.nav-masthead .nav-link:focus {
		  border-bottom-color: rgba(255, 255, 255, .25);
		}
		.nav-masthead .nav-link + .nav-link {
		  margin-left: 1rem;
		}
		.nav-masthead .active {
		  color: #fff;
		  border-bottom-color: #fff;
		}
		.bd-placeholder-img {
			font-size: 1.125rem;
			text-anchor: middle;
			-webkit-user-select: none;
			-moz-user-select: none;
			user-select: none;
		}
    .form-check .form-check-input {
	     float: none !important;
    }
    .modal {
      color: #000;
    }
    .modal li {
      text-align: left;
    }
    .modal img {
      text-align: center;
    }
		@media (min-width: 768px) {
			.bd-placeholder-img-lg {
				font-size: 3.5rem;
			}
		}
		.b-example-divider {
			height: 3rem;
			background-color: rgba(0, 0, 0, .1);
			border: solid rgba(0, 0, 0, .15);
			border-width: 1px 0;
			box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
		}
		.b-example-vr {
			flex-shrink: 0;
			width: 1.5rem;
			height: 100vh;
		}
		.bi {
			vertical-align: -.125em;
			fill: currentColor;
		}
		.nav-scroller {
			position: relative;
			z-index: 2;
			height: 2.75rem;
			overflow-y: hidden;
		}
		.nav-scroller .nav {
			display: flex;
			flex-wrap: nowrap;
			padding-bottom: 1rem;
			margin-top: -1px;
			overflow-x: auto;
			text-align: center;
			white-space: nowrap;
			-webkit-overflow-scrolling: touch;
		}
		</style>
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

			<h1>Login</h1>
			<img src="img/qatar-2022-logo.png" alt="Qatar 2022 edition of Hendy's Hunches" class="w-50 mb-3">

      <form id="login" role="form" method="post" action="php/login.php">
          <!--<h2 class="form-login-heading">Title</h2>-->
          <!--<img id="logo" src="img/hh-logo-2018.jpg" alt="Hendy's Hunches Logo" class="center-block img-responsive">-->

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
          <!--
          <p class="text-center">
          <a href="registration.php">Register To Play</a>&nbsp;&nbsp;|&nbsp;&nbsp;
          <a href="forgot-password.php">Reset Password</a>&nbsp;&nbsp;|&nbsp;
          <a href="" data-toggle="modal" data-target="#HHterms">Terms &amp; Conditions</a>
          </p>
        -->
      </form>

			<!--<p class="lead">Pit your FIFA World Cup 2022 predictions against others for a chance to earn a prize spot or bragging rights in the rankings.</p>-->
			<p class="lead">
				<a href="#" class="btn btn-lg btn-secondary fw-bold border-white bg-white" type="submit"><i class="fw-bold bi bi-box-arrow-in-right"></i> Log in</a>
			</p>
		</main>

    <!-- HH Terms Modal -->
    <div class="modal fade" id="terms" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="staticBackdropLabel">Hendy's Hunches: Terms &amp; Conditions</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
		</footer>
		</div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
	</body>
</html>
