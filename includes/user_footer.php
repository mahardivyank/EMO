    </div> <!-- End .container -->

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container text-center">
            <p class="mb-1">&copy; <?= date('Y') ?> <strong>EMO</strong>. All rights reserved.</p>
            <p class="mb-0 small">
                Designed with ❤️ for a private and secure portfolio experience.
            </p>
        </div>
    </footer>

    <!-- Bootstrap Bundle JS (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-3HvZ5Zq2J1bXJbhYEDMj70QkVfsUnWlxQ91Qh2u6sBfGhzpkk5Q7WRFHqa6uA57N"
        crossorigin="anonymous"></script>

    <!-- Optional: Add custom JS below -->
    <script>
        // Example animation trigger or helper JS
        document.addEventListener('DOMContentLoaded', () => {
            const fadeIns = document.querySelectorAll('.fade-in');
            fadeIns.forEach(el => {
                el.classList.add('show');
            });
        });
    </script>

    <!-- You can link your own JS file like this -->
    <!-- <script src="../assets/js/main.js"></script> -->

</body>
</html>
