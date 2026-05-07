<!-- partial:partials/_footer.html -->
<footer class="footer">
    <div class="d-sm-flex justify-content-center justify-content-sm-between">
        <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Admin PPDB SMPS Plus Fajar Sentosa</span>
        <span class="float-none float-sm-end d-block mt-1 mt-sm-0 text-center">YAPAS</span>
    </div>
</footer>
<!-- partial -->
</div>
<!-- main-panel ends -->
</div>
<!-- page-body-wrapper ends -->
<!-- plugins:js -->
<script src="<?= base_url('assets/vendors/js/vendor.bundle.base.js') ?>"></script>
<script src="<?= base_url('assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js') ?>"></script>
<!-- endinject -->
<!-- Plugin js for this page -->
<script src="<?= base_url('assets/vendors/chart.js/chart.umd.js') ?>"></script>
<script src="<?= base_url('assets/vendors/progressbar.js/progressbar.min.js') ?>"></script>
<script src="<?= base_url('assets/vendors/datatables.net/jquery.dataTables.js') ?>"></script>
<script src="<?= base_url('assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js') ?>"></script>
<!-- End plugin js for this page -->
<!-- inject:js -->
<script src="<?= base_url('assets/js/off-canvas.js') ?>"></script>
<script src="<?= base_url('assets/js/template.js') ?>"></script>
<script src="<?= base_url('assets/js/settings.js') ?>"></script>
<script src="<?= base_url('assets/js/hoverable-collapse.js') ?>"></script>
<script src="<?= base_url('assets/js/todolist.js') ?>"></script>
<!-- endinject -->
<!-- Custom js for this page-->
<script src="<?= base_url('assets/js/jquery.cookie.js') ?>" type="text/javascript"></script>
<script>
    (function () {
        if (!window.jQuery || !jQuery.fn.DataTable) {
            return;
        }

        jQuery('.admin-data-table').DataTable({
            autoWidth: false,
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            order: [],
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                zeroRecords: 'Data tidak ditemukan',
                paginate: {
                    first: 'Awal',
                    last: 'Akhir',
                    next: 'Berikutnya',
                    previous: 'Sebelumnya'
                }
            }
        });
    })();
</script>
<!-- Dashboard demo charts are intentionally disabled for the custom PPDB admin UI. -->
<!-- <script src="<?= base_url('assets/js/Chart.roundedBarCharts.js') ?>"></script> -->
<!-- End custom js for this page-->
</body>

</html>
