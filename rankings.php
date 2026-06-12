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
        <p class="lead mb-0">Check the table. Chase the pack.</p>
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
        "autoWidth": false,
        "paging": false,  // Enable paging to use the lengthMenu
        "searching": true,
        "info": false,  // Disable the "Showing X to Y of Z entries" info
        "pageLength": -1,  // Set the initial page length to Top 3
        //"lengthMenu": [[3, 10, 20, 30, 40, -1], ["Top 3", "Top 10", "Top 20", "Top 30", "Top 40", "All"]],
        "ordering": false,  // Disable column sorting
        "columnDefs": [
            { "width": "12%", "targets": 0 },
            { "width": "68%", "targets": 1 },
            { "width": "20%", "targets": 2 }
        ]   
    });

    const searchNode = $('#rankingsTable').closest('.dt-container').find('.dt-search').first().detach();
    const searchLabel = searchNode.find('label').first();
    const searchInput = searchNode.find('input').first();
    const tableHeadings = $('#rankingsTable thead th');
    const tableContainer = $('#rankingsTable').closest('.dt-container');

    searchInput.attr('placeholder', 'Enter a player\'s name');
    searchInput.attr('aria-label', 'Enter a player\'s name');

    if (searchLabel.length) {
      searchLabel.contents().filter(function() {
        return this.nodeType === 3;
      }).remove();
      searchLabel.prepend('<span>Find a player</span>');
    }

    $('#rankingsSearchMount').append(searchNode);

    tableContainer.children('.row').each(function() {
      const row = $(this);
      const hasControls = row.find('.dt-search, .dt-length, .dt-info, .dt-paging').length > 0;
      const hasTable = row.find('table, .dt-layout-table, .table-responsive').length > 0;
      const hasVisibleText = $.trim(row.text()).length > 0;

      if (!hasControls && !hasTable && !hasVisibleText) {
        row.addClass('rankings-layout-row--empty');
      }
    });

    const syncRankingsHeadings = function() {
      const compact = window.matchMedia('(max-width: 575.98px)').matches;
      const labels = compact ? ['RK', 'PLAYER', 'PTS'] : ['RANK', 'PLAYER', 'POINTS'];

      tableHeadings.each(function(index) {
        if (labels[index]) {
          $(this).text(labels[index]);
        }
      });
    };

    const refreshRankingsLayout = function() {
      window.requestAnimationFrame(function() {
        syncRankingsHeadings();
        rankingsTable.columns.adjust();
        if (rankingsTable.responsive) {
          rankingsTable.responsive.recalc();
        }
      });
    };

    $(window).on('resize orientationchange pageshow', function() {
      window.setTimeout(refreshRankingsLayout, 120);
    });

    refreshRankingsLayout();
});

</script>

<!-- Footer -->
<?php include "php/footer.php" ?>   
