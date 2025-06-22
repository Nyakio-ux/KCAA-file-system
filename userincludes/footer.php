            </div> <!-- End of main content -->
        </div> <!-- End of content -->
    </div> <!-- End of wrapper -->

    <!-- jQuery, Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
    
    <!-- Page-specific JS -->
    <?php if (isset($pageScript)): ?>
        <script src="assets/js/<?php echo $pageScript; ?>"></script>
    <?php endif; ?>
</body>
</html>