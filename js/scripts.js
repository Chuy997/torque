document.addEventListener("DOMContentLoaded", function () {
    // Validación del formulario de login
    const loginForm = document.querySelector('form[action=""]');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();

            if (username === "" || password === "") {
                e.preventDefault();
                alert("Por favor, complete todos los campos.");
            }
        });
    }

    // Confirmación al agregar torque
    const agregarTorqueForm = document.querySelector('form[action=""]');
    if (agregarTorqueForm) {
        agregarTorqueForm.addEventListener('submit', function (e) {
            const confirmacion = confirm("¿Estás seguro de que deseas agregar este torque?");
            if (!confirmacion) {
                e.preventDefault();
            }
        });
    }

    // Confirmación al actualizar torque
    const actualizarTorqueForm = document.querySelector('form[action=""]');
    if (actualizarTorqueForm) {
        actualizarTorqueForm.addEventListener('submit', function (e) {
            const confirmacion = confirm("¿Estás seguro de que deseas actualizar este torque?");
            if (!confirmacion) {
                e.preventDefault();
            }
        });
    }

    // Confirmación al reactivar torque fuera de uso
    const fueraDeUsoForm = document.querySelector('form[action=""]');
    if (fueraDeUsoForm) {
        fueraDeUsoForm.addEventListener('submit', function (e) {
            const password = document.getElementById('password').value.trim();
            if (password !== '123456') {
                e.preventDefault();
                alert("Contraseña incorrecta.");
            }
        });
    }
});
