<?php
?>
    <!-- Bootstrap JS -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    
    <!-- Scripts personalizados -->
    <script>
    // Funciones comunes
    document.addEventListener('DOMContentLoaded', function() {
        // Cerrar alerts automáticamente después de 5 segundos
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 1s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 1000);
            });
        }, 5000);
    });
    </script>
</body>
</html>