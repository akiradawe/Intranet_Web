        </main>
    </div>
</div>

<footer class="footer mt-auto py-3 bg-light border-top">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <span class="text-muted">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['site_name'] ?? 'IRCAD Africa Intranet'); ?>. All rights reserved.</span>
            </div>
            <div class="col-md-6 text-end">
                <span class="text-muted">Version 1.0.0</span>
            </div>
        </div>
    </div>
</footer>

<style>
.footer {
    position: fixed;
    bottom: 0;
    width: 100%;
    background-color: var(--bs-footer-bg) !important;
    color: var(--bs-footer-text) !important;
    z-index: 1000;
}

.footer .text-muted {
    color: var(--bs-footer-text) !important;
}

/* Add padding to main content to prevent footer overlap */
main {
    padding-bottom: 60px;
}
</style>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Font Awesome -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html> 