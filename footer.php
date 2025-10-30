<?php
// /var/www/html/torque/footer.php
?>
    </div> <!-- cierra wrapper abierto en header.php -->

    <footer class="mt-4 py-3 border-top border-secondary bg-black text-center">
        <small class="text-secondary">
            © <?= date('Y') ?> Torque Manager · Modo oscuro · <?= htmlspecialchars($_SESSION['username'] ?? 'Invitado', ENT_QUOTES, 'UTF-8') ?>
        </small>
    </footer>

    <!-- Bootstrap 5 JS (bundle con Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
            crossorigin="anonymous"></script>
</body>
</html>
