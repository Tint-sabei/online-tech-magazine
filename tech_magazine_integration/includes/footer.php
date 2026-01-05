</main> 

<style>
    /* Direct CSS to ensure the button is visible and has the peach hover effect */
    .btn-contact-custom {
        border: 2px solid #f8f9fa !important;
        color: #f8f9fa !important;
        background-color: transparent !important;
        transition: all 0.3s ease !important;
        text-decoration: none !important;
        display: inline-block;
    }

    .btn-contact-custom:hover {
        background-color: #FFCCBC !important; /* Peach */
        border-color: #FFCCBC !important;
        color: #D84315 !important;            /* Dark Orange */
        transform: scale(1.05) !important;
    }
</style>

<footer class="bg-dark text-white pt-5 pb-3 mt-5">
    <div class="container text-center">
        <div class="row">
            <div class="col-md-6 mx-auto">
                <h4 class="fw-bold mb-3">Online Tech Magazine</h4>
                <p class="fst-italic">"The best way to predict the future is to invent it."</p>
                <div class="mt-4">
                    <a href="contact_magazine/index.php" class="btn btn-contact-custom rounded-pill px-4 shadow-sm">
                        Contact Our Team
                    </a>
                </div>
            </div>
        </div>
        <hr class="my-4 border-secondary">
        <p class="small text-secondary">&copy; <?= date("Y"); ?> Online Tech Magazine Project. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>