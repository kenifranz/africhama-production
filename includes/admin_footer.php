<?php
// File: includes/admin_footer.php
$base_path = '/africhama-production';
?>

            </div> <!-- End of container-fluid -->
        </div> <!-- End of content -->
    </div> <!-- End of admin-wrapper -->

    <footer class="admin-footer mt-auto py-3 bg-light">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> Africhama Admin Panel. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item"><a href="<?php echo $base_path; ?>/admin/terms.php">Terms of Service</a></li>
                        <li class="list-inline-item"><a href="<?php echo $base_path; ?>/admin/privacy.php">Privacy Policy</a></li>
                        <li class="list-inline-item"><a href="<?php echo $base_path; ?>/admin/help.php">Help Center</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar on mobile
        document.addEventListener('DOMContentLoaded', function() {
            const toggleSidebar = document.getElementById('toggleSidebar');
            const adminWrapper = document.querySelector('.admin-wrapper');
            
            if (toggleSidebar) {
                toggleSidebar.addEventListener('click', function() {
                    adminWrapper.classList.toggle('sidebar-collapsed');
                });
            }
        });

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Initialize popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl)
        });
    </script>
</body>
</html>