<?php
session_start();
$page_title = 'Compare';

if (!(isset($_SESSION['login']) && $_SESSION['login'] != "")) {
    header("Location: index.php");
    exit();
}

include "php/header.php";
include "php/navigation.php";

?>

<!-- Main Content Section -->
<main id="main" class="main">

    <div class="pagetitle d-flex justify-content-between">
    <nav>
      <h1>Compare Predictions vs Results</h1>
      </nav> 
    </div><!-- End Page Title -->

    <section class="section">	
		<p class="lead">Compare all predictions against current results.</p>
		<!-- <?php compareRO16Values(); ?> -->
    </section>

</main>

<!-- Footer -->
<?php include "php/footer.php" ?>