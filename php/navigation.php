<nav class="navbar navbar-expand-lg navbar-dark bg-dark" aria-label="Offcanvas navbar large">
	<div class="container">
			<img src="img/hh-icon-2024.png" class="img-fluid bg-light mx-2" style="--bs-bg-opacity: 0.80" width="50px">
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
		              <a class="nav-link" href="dashboard.php">Dashboard</a>
		            </li>
					<!-- <li class="nav-item position-relative">
					<a class="nav-link" href="tournament-groups.php">
						Groups
						<span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-success">
						new
						</span>
					</a>
					</li>
					<li class="nav-item position-relative">
					<a class="nav-link" href="tournament-knockouts.php">
						Knockouts
						<span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-success">
						new
						</span>
					</a>
					</li> -->

		            <li class="nav-item position-relative">
		              <a class="nav-link" href="predictions.php">
						Submit Predictions
						<span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-success">
						quarter-finals
						</span>
					  </a>
		            </li>
					<li class="nav-item">
		              <a class="nav-link" href="rankings.php">Rankings</a>
		            </li>
					<li class="nav-item dropdown">
									<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Competition</a>
		              	<ul class="dropdown-menu">
							<li><a class="dropdown-item" href="tournament-groups.php">Group stage</a></li>
							<li><a class="dropdown-item" href="tournament-knockouts.php">Knockout stages</a></li>
						</ul>
		            </li>										
					<li class="nav-item">
		              <a class="nav-link" href="how-it-works.php">How It Works</a>
		            </li>
					<li class="nav-item">
						<a class="nav-link" href="about.php">About</a>
					</li>
		            <li class="nav-item dropdown">
									<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php returnAvatar(); ?>
                  </a>
		              <ul class="dropdown-menu">
										<li><a class="dropdown-item" href="overview.php">Application overview</a></li>
										<li><a class="dropdown-item" href="change-password.php">Change my password</a></li>
										<li><a class="dropdown-item card-link" href="user.php?id=<?php echo $_SESSION['id']; ?>">View my predictions</a></li>
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