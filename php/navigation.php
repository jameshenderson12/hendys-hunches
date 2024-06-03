  <!-- ======= Main Navigation ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="home.php" class="logo d-flex align-items-center">
        <img class="rounded" src="images/logos/vp-logo-sq.png" alt="Virtual Placement square logo">
        <span class="d-none d-lg-block">virtual placement</span>
      </a>
      <i class="bi bi-arrow-bar-left toggle-sidebar-btn" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Hide/show navigation panel"></i>
      <!-- <i class="bi bi-list toggle-sidebar-btn"></i> -->
    </div><!-- End Logo -->

    <div class="search-bar">
      <div class="search-form d-flex align-items-center">
        <input type="text" name="searchInput" id="searchInput" placeholder="Search" title="Enter search keyword">
        <button type="button" title="Search" disabled><i class="bi bi-search"></i></button>
      </div>
    </div><!-- End Search Bar -->
    <div id="searchCount" class="badge bg-light text-dark rounded"></div>

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <li class="nav-item d-block d-lg-none">
          <a class="nav-link nav-icon search-bar-toggle" href="#">
            <i class="bi bi-search"></i>
          </a>
        </li><!-- End Search Icon-->




        <li class="nav-item dropdown">

          <a class="nav-link nav-icon disabled text-success" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-bell"></i>
            <span class="visually-hidden">New alerts</span>
            <span id="notifications" class=""></span>
          </a><!-- End Notification Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
          </ul><!-- End Notification Dropdown Items -->

        </li><!-- End Notification Nav -->

        <li class="nav-item dropdown">

          <a class="nav-link nav-icon disabled text-success" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-chat-left-dots"></i>
              <!--<span class="position-absolute top-0 start-100 p-1 border border-success rounded-circle" style="background-color: #98fb98">-->
              <span class="visually-hidden">Chat feature</span>
            </span>
          </a><!-- End Messages Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow messages">
          </ul><!-- End Messages Dropdown Items -->

        </li><!-- End Messages Nav -->




        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <?= $_SESSION['photo'] ?>
            <span class="d-none d-md-block dropdown-toggle ps-2"><?= $_SESSION['name'] ?></span>
          </a><!-- End Profile Iamge Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6><?= $_SESSION['name'] ?></h6>
              <span><?= $_SESSION['jobTitle'] ?></span>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="user-profile.php">
                <i class="bi bi-person"></i>
                <span>My Profile</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="user-preferences.php">
                <i class="bi bi-gear"></i>
                <span>My Preferences</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>
<!--
            <li>
              <a class="dropdown-item d-flex align-items-center" href="pages-faq.php">
                <i class="bi bi-question-circle"></i>
                <span>Need Help?</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>
-->
            <li>
              <a class="dropdown-item d-flex align-items-center" href="includes/logout.inc.php">
                <i class="bi bi-box-arrow-right"></i>
                <span>Log out</span>
              </a>
            </li>

          </ul><!-- End Profile Dropdown Items -->
        </li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->

  </header><!-- End Header -->