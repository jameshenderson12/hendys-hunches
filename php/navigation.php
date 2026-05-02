<?php
require_once __DIR__ . '/auth.php';

$current_page = basename($_SERVER['PHP_SELF']);
$nav_active = function ($page) use ($current_page) {
	return $current_page === $page ? ' active' : '';
};
$use_concept_logo = isset($nav_logo_variant) && $nav_logo_variant === 'concept';
$is_admin_user = hh_is_admin_user();
?>

<nav class="navbar navbar-expand-lg site-navbar" aria-label="Main navigation">
	<div class="container">
			<a class="navbar-brand<?= $use_concept_logo ? ' navbar-brand--concept' : '' ?>" href="dashboard.php">
				<?php if ($use_concept_logo): ?>
					<span class="hh-brand-mark" aria-hidden="true">
						<span class="hh-brand-mark__initials">HH</span>
					</span>
					<span class="hh-wordmark" aria-label="Hendy's Hunches">
						<span class="hh-wordmark__name">Hendy's</span>
						<span class="hh-wordmark__tag">Hunches</span>
					</span>
				<?php else: ?>
					<span class="site-wordmark" aria-label="Hendy's Hunches">
						<span class="site-wordmark__name">Hendy's Hunches</span>
						<span class="site-wordmark__tag">Football prediction game</span>
					</span>
				<?php endif; ?>
			</a>
		      <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar2" aria-controls="offcanvasNavbar2">
		        <span class="navbar-toggler-icon"></span>
		      </button>
		      <div class="offcanvas offcanvas-end site-navbar__panel" tabindex="-1" id="offcanvasNavbar2" aria-labelledby="offcanvasNavbar2Label">
		        <div class="offcanvas-header">
		          <h5 class="offcanvas-title" id="offcanvasNavbar2Label">Hendy's Hunches</h5>
		          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
		        </div>
		        <div class="offcanvas-body">
		          <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
		            <li class="nav-item">
		              <a class="nav-link<?= $nav_active('dashboard.php') ?>" href="dashboard.php">Dashboard</a>
		            </li>
		            <li class="nav-item">
		              <a class="nav-link<?= $nav_active('fanzone.php') ?>" href="fanzone.php">Fan Zone</a>
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
		              <a class="nav-link disabled<?= $nav_active('predictions.php') ?>" href="predictions.php">
						Submit Prediction
						<!-- <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-success">
						Final
						</span> -->
					  </a>
		            </li>
					<li class="nav-item">
		              <a class="nav-link<?= $nav_active('rankings.php') ?>" href="rankings.php">Rankings</a>
		            </li>
					<li class="nav-item dropdown">
									<a class="nav-link dropdown-toggle<?= in_array($current_page, ['tournament-groups.php', 'tournament-knockouts.php']) ? ' active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Competition</a>
		              	<ul class="dropdown-menu">
							<li><a class="dropdown-item" href="tournament-groups.php">Group stage</a></li>
							<li><a class="dropdown-item" href="tournament-knockouts.php">Knockout stages</a></li>
						</ul>
		            </li>										
					<li class="nav-item">
		              <a class="nav-link<?= $nav_active('how-it-works.php') ?>" href="how-it-works.php">How It Works</a>
		            </li>
					<li class="nav-item">
						<a class="nav-link<?= $nav_active('about.php') ?>" href="about.php">About</a>
					</li>
		            <li class="nav-item dropdown">
									<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php returnAvatar(); ?>
                  </a>
		              <ul class="dropdown-menu">
										<li><a class="dropdown-item" href="overview.php">Application overview</a></li>
										<li><a class="dropdown-item" href="change-password.php">Change my password</a></li>
										<li><a class="dropdown-item card-link" href="user.php?id=<?php echo $_SESSION['id']; ?>">View my predictions</a></li>
										<?php if ($is_admin_user): ?>
										<li><a class="dropdown-item<?= $nav_active('functions.php') ?>" href="admin/functions.php" style="color: var(--hh-purple)">+ Admin functions</a></li>
										<li><a class="dropdown-item<?= $nav_active('configuration.php') ?>" href="admin/configuration.php" style="color: var(--hh-purple)">+ Site configuration</a></li>
										<li><a class="dropdown-item<?= $nav_active('setup-wizard.php') ?>" href="setup/setup-wizard.php" style="color: var(--hh-purple)">+ Installation manager</a></li>
										<?php endif; ?>
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
