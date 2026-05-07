
<?php
$app_path_prefix = $app_path_prefix ?? '';
$asset_prefix = $app_path_prefix;
?>
<footer id="footer" class="footer mt-4">
    <div class="copyright">        
        <p>Predictions game based on <a href="<?=$competition_url?>" class="text-white"><?=$competition?></a><br><?=$title?> <?=$version?> &copy; <?=$year?> <?=$developer?>.</p>        
    </div>
</footer><!-- End Footer -->

<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <!-- <script src="vendor/apexcharts/apexcharts.min.js"></script> -->
  <script src="<?= htmlspecialchars($asset_prefix . 'vendor/bootstrap/js/bootstrap.bundle.min.js', ENT_QUOTES) ?>"></script>
  <!-- <script src="vendor/chart.js/chart.umd.js"></script>
  <script src="vendor/echarts/echarts.min.js"></script> -->
  <script src="<?= htmlspecialchars($asset_prefix . 'vendor/progressbar/progressbar.js', ENT_QUOTES) ?>"></script>
  <!-- <script async src="vendor/bootbox/bootbox.min.js"></script>
  <script async src="vendor/lodash/lodash.min.js"></script> -->
  <!-- <script async src="vendor/highlight-text/highlight-text.js"></script> -->
  <script async src="<?= htmlspecialchars($asset_prefix . 'js/confetti.js', ENT_QUOTES) ?>"></script>
  <script src="<?= htmlspecialchars($asset_prefix . 'js/multi-step-form.js', ENT_QUOTES) ?>"></script>
  <!-- Template Main JS File -->
  <script src="<?= htmlspecialchars($asset_prefix . 'js/main.js', ENT_QUOTES) ?>"></script>
  <!-- <script>
    // Initialize Bootstrap tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
  </script> -->

  </body>
</html>
