<footer id="footer" class="footer mt-4">
    <div class="copyright">
        <?php echo $config['app_name'] . " v" . $config['app_version'] . " © " . date("Y") . " <strong>" . $config['org_name'] . "</strong>. " . "Funded by " . $config['app_funder'] . "."; ?>
        <!--&copy; Copyright <strong><span>NiceAdmin</span></strong>. All Rights Reserved-->
    </div>
</footer><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="vendor/apexcharts/apexcharts.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="vendor/chart.js/chart.umd.js"></script>
  <script src="vendor/echarts/echarts.min.js"></script>
  <script src="vendor/progressbar/progressbar.js"></script>
  <script src="vendor/simple-datatables/simple-datatables.js"></script>
  <!-- Async/Custom/jQuery Files -->  
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
  <script async src="vendor/bootbox/bootbox.min.js"></script>
  <script async src="vendor/lodash/lodash.min.js"></script>
  <script async src="vendor/highlight-text/highlight-text.js"></script>
  <script async src="js/confetti.js"></script>
  <script async src="js/multi-step-form.js"></script>
  <!-- Template Main JS File -->
  <script src="js/main.js"></script>

<!-- Modal for Badge Earning Congratulations -->
<div class="modal fade" id="congratsModal" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="congratsModalLabel" aria-hidden="true">
    <div class="confetti-container">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="congratsModalLabel">Reward Earned</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center mt-3">
                <i class="bi bi-check-circle-fill" style="font-size: 60px; color: green;"></i>
                <h1>Congratulations!</h1>
                <p class="fs-5">You've just earned a reward for completing Episode #{episode.number}!</p>
                <p>Episode Title</p>
                <img src="images/logos/vp-logo-sq.png" class="w-25 img-fluid" alt="...">
                <!--
                <div class="row g-0 bg-body-secondary position-relative">
                    <div class="col-md-4 mb-md-0 p-md-4">
                        <img src="images/logos/vp-logo-sq.png" class="w-100" alt="...">
                    </div>
                    <div class="col-md-8 p-4 ps-md-0">
                        <i class="bi bi-check-circle-fill" style="font-size: 60px; color: green;"></i>
                        <h1>Congratulations!</h1>
                        <p class="fs-5">You've just earned yourself a badge for completing this episode!</p>
                    </div>
                </div>-->
                <p class="mt-3">This has been added to your Dashboard so it is stored for you.</p>
            </div>
            <div class="modal-footer">
                  <!-- <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button> -->
                  <button type="button" class="btn btn-primary" data-bs-dismiss="modal" aria-label="Close" id=""><i class="bi bi-arrow-right"></i> Continue</button>
              </div>
            </div>
        </div>
    </div>
</div>


<script>

</script>
  
  </body>
</html>