<!-- partial:partials/_footer.html -->
<footer class="footer">
    <div class="d-sm-flex justify-content-center justify-content-sm-between">
        <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Admin SPMB SMPS Plus Fajar Sentosa</span>
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

    (function () {
        let pendingForm = null;

        function ensureConfirmDialog() {
            let dialog = document.getElementById('adminConfirmDialog');

            if (dialog) {
                return dialog;
            }

            const wrapper = document.createElement('div');
            wrapper.innerHTML = `
                <div class="admin-confirm-backdrop" id="adminConfirmDialog" aria-hidden="true">
                    <div class="admin-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="adminConfirmTitle">
                        <div class="admin-confirm-icon">
                            <i class="mdi mdi-delete-outline"></i>
                        </div>
                        <div class="admin-confirm-copy">
                            <h5 id="adminConfirmTitle">Konfirmasi Hapus</h5>
                            <p id="adminConfirmText">Data ini akan dihapus.</p>
                        </div>
                        <div class="admin-confirm-actions">
                            <button class="btn btn-outline-secondary" type="button" data-admin-confirm-cancel>Batal</button>
                            <button class="btn admin-danger-btn" type="button" data-admin-confirm-ok>
                                <i class="mdi mdi-delete me-1"></i> Hapus
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(wrapper.firstElementChild);

            dialog = document.getElementById('adminConfirmDialog');
            dialog.querySelector('[data-admin-confirm-cancel]').addEventListener('click', closeConfirmDialog);
            dialog.querySelector('[data-admin-confirm-ok]').addEventListener('click', function () {
                const form = pendingForm;
                closeConfirmDialog();

                if (form) {
                    form.dataset.confirmed = '1';
                    form.submit();
                }
            });
            dialog.addEventListener('click', function (event) {
                if (event.target === dialog) {
                    closeConfirmDialog();
                }
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && dialog.classList.contains('show')) {
                    closeConfirmDialog();
                }
            });

            return dialog;
        }

        function openConfirmDialog(form, message) {
            const dialog = ensureConfirmDialog();
            pendingForm = form;
            dialog.querySelector('#adminConfirmText').textContent = message || 'Data ini akan dihapus.';
            dialog.classList.add('show');
            dialog.setAttribute('aria-hidden', 'false');
            dialog.querySelector('[data-admin-confirm-cancel]').focus();
        }

        function closeConfirmDialog() {
            const dialog = document.getElementById('adminConfirmDialog');

            if (!dialog) {
                return;
            }

            dialog.classList.remove('show');
            dialog.setAttribute('aria-hidden', 'true');
            pendingForm = null;
        }

        document.addEventListener('submit', function (event) {
            const form = event.target.closest('form[data-confirm]');

            if (!form || form.dataset.confirmed === '1') {
                return;
            }

            event.preventDefault();
            openConfirmDialog(form, form.dataset.confirm);
        }, true);
    })();
</script>
<!-- Dashboard demo charts are intentionally disabled for the custom SPMB admin UI. -->
<!-- <script src="<?= base_url('assets/js/Chart.roundedBarCharts.js') ?>"></script> -->
<!-- End custom js for this page-->
</body>

</html>
