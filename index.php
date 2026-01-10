<?php
// Start the session
session_start();
?>
<!DOCTYPE html>
<html lang="en-GB">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Hendy's Hunches: Predictions Game">
    <meta name="author" content="James Henderson">
		<title>Login - Hendy's Hunches</title>
    <?php include "php/config.php" ?>
		<link rel="shortcut icon" href="ico/favicon.ico">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="css/registration.css" rel="stylesheet">
    <link href="css/multi-step-form.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <style>
      .fade-in-image { animation: fadeIn 2s; }

      @keyframes fadeIn {
        0% { opacity: 0; }
        100% { opacity: 1; }
      }
    </style>
	</head>

	<body class="d-flex h-100 text-center text-bg-dark">

  	<div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">

      <header class="">
        <div>
          <h3 class="float-md-start mb-0">Hendy's Hunches</h3>
          <nav class="nav nav-masthead justify-content-center float-md-end">
            <a class="nav-link fw-bold py-1 px-0" href="registration.php">Register</a>
  					<a class="nav-link fw-bold py-1 px-0" href="forgot-password.php">Reset Password</a>
            <a class="nav-link fw-bold py-1 px-0" href="#" data-bs-toggle="modal" data-bs-target="#terms">Terms</a>
          </nav>
        </div>
      </header>
      

      <img src="img/james-scotland-ed-lg.png" alt="James in Scotland kit" class="col-md-5 col-5 img-fluid fade-in-image mx-auto d-block login-hero">      
  		<main class="px-3">
  			<h1><img src="img/germany-518638_640.png" alt="German nation flag" class="img-fluid col-1 mx-2 mb-2" style="">Germany 2024</h1>
        <!-- <h3>Login</h3> -->

        <!-- <form id="login" role="form" class="d-flex flex-column needs-validation" method="POST" action="php/login.php" style="	border: 1px solid #AAA; border-radius: 0.35rem; min-height: inherit; height: inherit;">

          <div class="mb-3 row d-flex justify-content-center px-5">
            <label for="username" class="form-label col-5 col-lg-3">Username</label>
            <input type="text" class="form-control col-6" id="username" name="username" required>
            <div class="invalid-feedback">
              Please provide your username.
            </div>
            <label for="password" class="form-label col-5 col-lg-3">Password</label>
            <input type="password" class="form-control col-6" id="password" name="password" required>
            <div class="invalid-feedback">
              Please provide your password.
            </div>     

          </div> -->

          <form id="loginForm" role="form" class="needs-validation" method="POST" action="php/login.php" style="border: 1px solid #AAA; border-radius: 0.35rem; min-height: inherit; height: inherit;">
            <div class="mb-3 row justify-content-center px-3 px-md-5">
                <div class="col-12 col-md-10 col-lg-8">
                    <div class="row mb-3">
                        <label for="username" class="col-12 col-md-4 col-form-label">Username</label>
                        <div class="col-12 col-md-8">
                            <input type="text" class="form-control" id="username" name="username" style="width: 100%" required autocomplete="username" autofocus>
                            <div class="invalid-feedback">
                                Please provide your username.
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label for="password" class="col-12 col-md-4 col-form-label">Password</label>
                        <div class="col-12 col-md-8">
                            <div class="input-group">
                              <input type="password" class="form-control" id="password" name="password" style="width: 100%" required autocomplete="current-password">
                              <button class="btn btn-outline-light" type="button" id="toggleLoginPwd" aria-label="Show password">
                                <i class="bi bi-eye-slash-fill"></i>
                              </button>
                            </div>
                            <div class="invalid-feedback">
                                Please provide your password.
                            </div>
                            <div class="text-start small mt-2">
                              <a href="forgot-password.php" class="text-white">Forgot password?</a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                      <div class="col-12 col-md-3">                        
                      </div>
                      <div class="col-12 col-md-9">
                        <button type="submit" class="btn btn-primary w-100 mt-0"><i class="fw-bold bi bi-box-arrow-in-right"></i> Log in</button>
                      </div>
                      <div class="col-12 col-md-3"></div>
                      <div class="col-12 col-md-9">
                        <hr>
                        <div class="text-start small mt-2">
                          <a href="forgot-password.php" class="text-white">Forgot password?</a>
                        </div>
                      </div>
                    </div>                    
                </div>
            </div>
            <!-- <iframe src="https://free.timeanddate.com/countdown/i8k6yqvc/n4511/cf11/cm0/cu3/ct0/cs1/ca0/co0/cr0/ss0/cacfff/cpc0f0/pct/tcfff/fs100/szw320/szh135/iso2024-06-14T20:00:00/bacfff/pa5" allowtransparency="true" frameborder="0" width="244" height="42" class="mt-5 mx-auto"></iframe>  -->
        </form>
        
  		</main>
      
      

      <!-- HH Terms Modal -->
      <div class="modal fade" id="terms" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
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
                <li>the game is based on <?=$competition?></li>
                <li>only one registration per person is permitted although family and friends are welcome to participate</li>
                <li>an entry fee of £<?=$signup_fee_formatted?> is to be paid prior to <?=$signup_close_date?>; split for charity donation and prize funds</li>
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
        <!-- <img src="img/germany-2024-logo-md.png" alt="Hendy's Hunches logo for Germany 2024" class="w-25"> -->
        <p class="small fw-light">Predictions game based on <a href="<?=$competition_url?>" class="text-white"><?=$competition?></a><br><?=$title?> <?=$version?> &copy; <?=$year?> <?=$developer?>.</p>
      </footer>

	  </div>
    <script>
      const toggleLoginPwd = document.querySelector('#toggleLoginPwd');
      const loginPassword = document.querySelector('#password');

      if (toggleLoginPwd && loginPassword) {
        toggleLoginPwd.addEventListener('click', () => {
          const type = loginPassword.getAttribute('type') === 'password' ? 'text' : 'password';
          loginPassword.setAttribute('type', type);
          const icon = toggleLoginPwd.querySelector('i');
          if (icon) {
            icon.classList.toggle('bi-eye');
          }
        });
      }
    </script>
  </body>
</html>
