<?php
session_start();
$page_title = 'Rankings';

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
      <h1>Rankings</h1>
        <!-- <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="home.php">Home</a></li>
          <li class="breadcrumb-item"><a href="#">Care Episodes</a></li>          
          <li class="breadcrumb-item active">Part #3 - 11.30</li>
        </ol> -->
      </nav> 
    </div><!-- End Page Title -->

    <section class="section">
		<p class="lead">Check your progress against others</p>
		<!-- <p class="alert alert-success">Congratulations to our winners Chloe (1st), Howard (2nd) and Andrew (3rd).</p> -->
		<!-- Display table of rankings from process.php -->		
		
		<?php displayRankingsXX(); ?>
    </section>

</main>

<script src="vendor/simple-datatables/simple-datatables.js"></script>
<script>
  $(document).ready(function () {
      const dataTable = new simpleDatatables.DataTable("#rankingsTable");
  });
// $(document).ready(function () {	 
//   $("td:eq(0)").css("background","#FFD700").css("text-align", "center").append("£50"); // Create 'rank 1' cell 
//   $("td:eq(3)").css("background","#C0C0C0").css("text-align", "center").append("£35"); // Create 'rank 2' cell
//   $("td:eq(6)").css("background","#CD7F32").css("text-align", "center").append("£23"); // Create 'rank 3' cell	  
// });
</script> 

<!-- Footer -->
<?php include "php/footer.php" ?>   

