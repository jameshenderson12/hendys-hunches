<?php
session_start();
$page_title = 'Rankings';

require_once __DIR__ . '/php/auth.php';
hh_require_login('index.php');

include "php/header.php";
include "php/navigation.php";

?>

<!-- Main Content Section -->
<main id="main" class="main">

    <div class="page-hero page-hero--rankings">
      <div>
        <p class="eyebrow">Leaderboard</p>
        <h1>Rankings</h1>
        <p class="lead mb-0">Check the table, chase the pack and see who made the final prize spots.</p>
      </div>
      <div class="page-hero__actions page-hero__actions--search">
        <div id="rankingsSearchMount" class="rankings-hero-search"></div>
      </div>
    </div><!-- End Page Title -->

    <section class="section rankings-page">
    <!-- <p><strong>Note:</strong> The ability to view others' predictions has been purposefully removed temporarily.</p> -->
		<!-- <p class="alert alert-success rankings-page__notice"><i class="bi bi-trophy-fill"></i> Congratulations to our winners Jonathan (1st), Paul (2nd), David (3rd), Ketan (4th) and Romina (5th).</p> -->
		<!-- Display table of rankings from process.php -->		
		
		<div class="rankings-panel">
			<?php displayRankingsEq5(); ?>
		</div>
    </section>

</main>

<script src="https://cdn.datatables.net/v/bs5/dt-2.0.8/r-3.0.2/datatables.min.js"></script>

<script>
$(document).ready(function() {
    // Initialise DataTables
    const rankingsTable = $('#rankingsTable').DataTable({
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

    const searchNode = $('#rankingsTable').closest('.dt-container').find('.dt-search').first().detach();
    const searchLabel = searchNode.find('label').first();
    const searchInput = searchNode.find('input').first();

    searchInput.attr('placeholder', 'Enter a player\'s name');
    searchInput.attr('aria-label', 'Enter a player\'s name');

    if (searchLabel.length) {
      searchLabel.contents().filter(function() {
        return this.nodeType === 3;
      }).remove();
      searchLabel.prepend('<span>Find a player</span>');
    }

    $('#rankingsSearchMount').append(searchNode);
});

 $(document).ready(function () {
   const prizeRows = [
     'rankings-row--gold',
     'rankings-row--silver',
     'rankings-row--bronze',
     'rankings-row--prize',
     'rankings-row--prize'
   ];

   prizeRows.forEach(function(className, index) {
     $('#rankingsTable tbody tr').eq(index).addClass(className);
   });
 });
</script>

<!-- Footer -->
<?php include "php/footer.php" ?>   
