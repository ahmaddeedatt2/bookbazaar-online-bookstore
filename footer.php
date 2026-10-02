<?php
// Shared HTML Footer Component
// File: footer.php

// Dynamically compute base URL folder to make absolute paths work in subdirectories
$project_folder = str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', __DIR__));
if ($project_folder !== '' && $project_folder[0] !== '/') {
    $project_folder = '/' . $project_folder;
}
$base_url = rtrim($project_folder, '/') . '/';
?>
    </main> <!-- Closing tag for main container -->

    <!-- Footer -->
    <footer class="footer py-4 mt-auto">
        <div class="container text-center">
            <div class="row align-items-center">
                <div class="col-md-6 text-md-start mb-3 mb-md-0">
                    <p class="mb-0 text-muted-light">&copy; 2026 <strong>BOOK BAZAAR</strong>. All Rights Reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-muted-light"><i class="fa-brands fa-github fs-5"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle with Popper JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    
    <!-- Custom Cart and Interface JavaScript -->
    <script src="<?php echo $base_url; ?>static/js/cart.js"></script>
</body>
</html>
