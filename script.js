// Affiche / masque le mot de passe
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('password-toggle')) {
        const input = document.getElementById(e.target.dataset.target);
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            e.target.textContent = 'Masquer';
        } else {
            input.type = 'password';
            e.target.textContent = 'Afficher';
        }
    }

    // Toggle menu mobile
    if (e.target.closest('.topbar-toggle')) {
        document.querySelector('.sidebar')?.classList.toggle('open');
    }

    // Confirmation de suppression
    if (e.target.closest('.js-confirm-delete')) {
        if (!confirm('Voulez-vous vraiment supprimer cette tâche ? Cette action est irréversible.')) {
            e.preventDefault();
        }
    }
});

// Validation côté client simple pour inscription
document.addEventListener('DOMContentLoaded', function () {
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            const pwd = document.getElementById('password').value;
            const confirmPwd = document.getElementById('confirm_password').value;
            if (pwd !== confirmPwd) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas.');
            }
        });
    }
});
