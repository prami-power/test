document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.querySelector('#togglePassword');
    const passwordInput = document.querySelector('#password'); // Renamed variable for clarity

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function (e) {
            // Toggle the type attribute
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Toggle the eye slash icon
            this.classList.toggle('fa-eye-slash');
            this.classList.toggle('fa-eye'); // Ensure both classes are toggled
        });
    } else {
        console.error("Password toggle elements not found.");
    }
});