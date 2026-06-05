<?php
require_once __DIR__ . '/auth.php';

$current_page = basename($_SERVER['PHP_SELF']);
$app_path_prefix = $app_path_prefix ?? '';
$nav_href = static fn(string $path): string => $app_path_prefix . $path;
$nav_active = function ($page) use ($current_page) {
	return $current_page === $page ? ' active' : '';
};
$use_featured_logo = isset($nav_logo_variant) && $nav_logo_variant === 'featured';
$is_admin_user = hh_is_admin_user();
$current_user_id = (int) ($_SESSION['id'] ?? 0);
$brand_logo_path = $nav_href('img/hh-logo-2026-simple.png');
?>

<nav class="navbar navbar-expand-lg site-navbar" aria-label="Main navigation">
	<div class="container">
			<a class="navbar-brand<?= $use_featured_logo ? ' navbar-brand--featured' : '' ?>" href="<?= htmlspecialchars($nav_href('dashboard.php'), ENT_QUOTES) ?>">
				<?php if ($use_featured_logo): ?>
					<span class="hh-brand-mark" aria-hidden="true">
						<span class="hh-brand-mark__initials">HH</span>
					</span>
					<span class="hh-wordmark" aria-label="Hendy's Hunches">
						<span class="hh-wordmark__name">Hendy's</span>
						<span class="hh-wordmark__tag">Hunches</span>
					</span>
				<?php else: ?>
					<span class="site-navbar__logo" aria-hidden="true">
						<img src="<?= htmlspecialchars($brand_logo_path, ENT_QUOTES) ?>" alt="">
					</span>
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
		              <a id="tour-nav-dashboard" class="nav-link<?= $nav_active('dashboard.php') ?>" href="<?= htmlspecialchars($nav_href('dashboard.php'), ENT_QUOTES) ?>">Dashboard</a>
		            </li>
		            <li class="nav-item position-relative">
		              <a id="tour-nav-predictions" class="nav-link<?= $nav_active('predictions.php') ?>" href="<?= htmlspecialchars($nav_href('predictions.php'), ENT_QUOTES) ?>">
						My Predictions
						<!-- <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-success">
						Final
						</span> -->
					  </a>
		            </li>
					<li class="nav-item">
		              <a id="tour-nav-rankings" class="nav-link<?= $nav_active('rankings.php') ?>" href="<?= htmlspecialchars($nav_href('rankings.php'), ENT_QUOTES) ?>">Rankings</a>
		            </li>
					<li class="nav-item dropdown">
									<a id="tour-nav-competition" class="nav-link dropdown-toggle<?= in_array($current_page, ['tournament-groups.php', 'tournament-knockouts.php']) ? ' active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Competition</a>
						<ul class="dropdown-menu">
							<li><a id="tour-nav-groups" class="dropdown-item" href="<?= htmlspecialchars($nav_href('tournament-groups.php'), ENT_QUOTES) ?>">Group stage</a></li>
							<li><a id="tour-nav-knockouts" class="dropdown-item" href="<?= htmlspecialchars($nav_href('tournament-knockouts.php'), ENT_QUOTES) ?>">Knockout stages</a></li>
						</ul>
		            </li>										
		            <li class="nav-item">
		              <a id="tour-nav-fanzone" class="nav-link<?= $nav_active('fanzone.php') ?>" href="<?= htmlspecialchars($nav_href('fanzone.php'), ENT_QUOTES) ?>">Fan Zone</a>
		            </li>
					<li class="nav-item">
		              <a id="tour-nav-guide" class="nav-link<?= $nav_active('how-it-works.php') ?>" href="<?= htmlspecialchars($nav_href('how-it-works.php'), ENT_QUOTES) ?>">How It Works</a>
		            </li>
					<li class="nav-item">
		              <a id="tour-nav-about" class="nav-link<?= $nav_active('about.php') ?>" href="<?= htmlspecialchars($nav_href('about.php'), ENT_QUOTES) ?>">About</a>
					</li>
					<?php if ($is_admin_user): ?>
					<li class="nav-item dropdown">
						<a
							id="tour-nav-admin"
							class="nav-link dropdown-toggle<?= in_array($current_page, ['functions.php', 'results.php', 'configuration.php', 'communications.php', 'setup-wizard.php']) ? ' active' : '' ?>"
							href="#"
							role="button"
							data-bs-toggle="dropdown"
							aria-expanded="false"
						>Admin</a>
						<ul class="dropdown-menu">
							<li><a class="dropdown-item dropdown-item--admin" href="<?= htmlspecialchars($nav_href('overview.php'), ENT_QUOTES) ?>">Application overview</a></li>
							<li><a class="dropdown-item dropdown-item--admin" href="<?= htmlspecialchars($nav_href('admin/functions.php'), ENT_QUOTES) ?>">Game functions</a></li>
							<li><a class="dropdown-item dropdown-item--admin" href="<?= htmlspecialchars($nav_href('admin/results.php'), ENT_QUOTES) ?>">Record results</a></li>
							<li><a class="dropdown-item dropdown-item--admin" href="<?= htmlspecialchars($nav_href('admin/communications.php'), ENT_QUOTES) ?>">Communications</a></li>
							<li><a class="dropdown-item dropdown-item--admin" href="<?= htmlspecialchars($nav_href('admin/configuration.php'), ENT_QUOTES) ?>">Site configuration</a></li>
							<li><a class="dropdown-item dropdown-item--admin" href="<?= htmlspecialchars($nav_href('setup/setup-wizard.php'), ENT_QUOTES) ?>">Installation manager</a></li>
						</ul>
		            </li>
					<?php endif; ?>
		            <li class="nav-item dropdown">
									<a id="tour-nav-account" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php returnAvatar(); ?>
                  </a>
		              <ul class="dropdown-menu">
										<li><a class="dropdown-item" href="<?= htmlspecialchars($nav_href('account.php'), ENT_QUOTES) ?>">Edit my details</a></li>
										<li><a class="dropdown-item" href="<?= htmlspecialchars($nav_href('change-password.php'), ENT_QUOTES) ?>">Change my password</a></li>
										<li><a class="dropdown-item" href="<?= htmlspecialchars($nav_href('predictions.php'), ENT_QUOTES) ?>">Submit my predictions</a></li>
										<li><a class="dropdown-item card-link" href="<?= htmlspecialchars($nav_href('user.php?id=' . $current_user_id), ENT_QUOTES) ?>">View my predictions</a></li>
		                <li>
		                  <hr class="dropdown-divider">
		                </li>
		                <li><a class="dropdown-item" href="<?= htmlspecialchars($nav_href('php/logout.php'), ENT_QUOTES) ?>">Logout</a></li>
		              </ul>
		            </li>
		          </ul>
		        </div>
		      </div>
		    </div>
		  </nav>
