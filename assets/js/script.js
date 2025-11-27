document.addEventListener('DOMContentLoaded', function() {
    // Prosta walidacja formularza rejestracji po stronie klienta
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                alert('Hasła nie są zgodne!');
                event.preventDefault(); // Zatrzymaj wysyłanie formularza
            }

            if (password.length < 6) {
                alert('Hasło musi mieć co najmniej 6 znaków.');
                event.preventDefault();
            }
        });
    }
});