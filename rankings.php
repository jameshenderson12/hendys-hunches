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

<style>
  #rankingsTable th, #rankingsTable td {
    text-align: left !important;
  }
  
  div.dt-container .row {
	--bs-gutter-y: 0rem !important;
  }
</style>

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
		<p class="lead">Check your progress against others.</p>
    <!-- <p><strong>Note:</strong> The ability to view others' predictions has been purposefully removed temporarily.</p> -->
		<p class="alert alert-success">Congratulations to our winners Jonathan (1st), Paul (2nd), David (3rd), Ketan (4th) and Romina (5th).</p>
		<!-- Display table of rankings from process.php -->		
		
		<?php displayRankingsEq5(); ?>
    </section>

</main>

<script src="https://cdn.datatables.net/v/bs5/dt-2.0.8/r-3.0.2/datatables.min.js"></script>

<script>
$(document).ready(function() {
    // Initialise DataTables
    $('#rankingsTable').DataTable({
        "responsive": true,
        "paging": false,  // Enable paging to use the lengthMenu
        "searching": true,
        "info": false,  // Disable the "Showing X to Y of Z entries" info
        "pageLength": -1,  // Set the initial page length to Top 3
        //"lengthMenu": [[3, 10, 20, 30, 40, -1], ["Top 3", "Top 10", "Top 20", "Top 30", "Top 40", "All"]],
        "ordering": false,  // Disable column sorting
        "columnDefs": [
            { "width": "10%", "targets": 0 },
            { "width": "15%", "targets": 1 },
            { "width": "60%", "targets": 2 },
            { "width": "15%", "targets": 3 },          
        ]   
    });
});

 $(document).ready(function () {	 
   $("td:eq(0)").css("background","#FFD700");
   $("td:eq(4)").css("background","#C0C0C0");
   $("td:eq(8)").css("background","#CD7F32");
   $("td:eq(12)").css("background","#b1d8b7");
   $("td:eq(16)").css("background","#b1d8b7");
 });
</script>

<!-- Footer -->
<?php include "php/footer.php" ?>   

