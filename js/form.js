document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('infoForm');
    const passwordInput = document.getElementById('senha');
    const confirmPasswordInput = document.getElementById('confirmarSenha');
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    const nifDonoInput = document.getElementById('nifDono');
    const nifEmpresaInput = document.getElementById('nifEmpresa');
    const codigoPostalInput = document.getElementById('codigoPostal');
    const telefoneDonoInput = document.getElementById('telefoneDono');
    const telefoneEmpresaInput = document.getElementById('telefoneEmpresa');
    
    // Formatação do código postal
    codigoPostalInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 4) {
            value = value.substring(0, 4) + '-' + value.substring(4, 7);
        }
        e.target.value = value;
    });
    
    // Validação de NIF
    function validateNIF(nifInput) {
        const nif = nifInput.value.trim();
        const nifRegex = /^[0-9]{9}$/;
        
        if (!nifRegex.test(nif)) {
            showError(nifInput, 'O NIF deve conter 9 dígitos numéricos.');
            return false;
        }
        
        // Algoritmo de validação do NIF português
        const firstDigit = parseInt(nif.charAt(0));
        if (![1, 2, 5, 6, 8, 9].includes(firstDigit)) {
            showError(nifInput, 'O primeiro dígito do NIF não é válido.');
            return false;
        }
        
        let sum = 0;
        for (let i = 0; i < 8; i++) {
            sum += parseInt(nif.charAt(i)) * (9 - i);
        }
        
        let controlDigit = 11 - (sum % 11);
        if (controlDigit > 9) controlDigit = 0;
        
        if (controlDigit !== parseInt(nif.charAt(8))) {
            showError(nifInput, 'O NIF não é válido.');
            return false;
        }
        
        clearError(nifInput);
        return true;
    }
    
    // Toggle password visibility
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                this.setAttribute('aria-label', 'Esconder senha');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                this.setAttribute('aria-label', 'Mostrar senha');
            }
        });
    });
    
    // Password strength validation
    passwordInput.addEventListener('input', validatePasswordStrength);
    
    function validatePasswordStrength() {
        const password = passwordInput.value;
        const lengthValid = password.length >= 8;
        const uppercaseValid = /[A-Z]/.test(password);
        const lowercaseValid = /[a-z]/.test(password);
        const numberValid = /[0-9]/.test(password);
        const specialValid = /[^A-Za-z0-9]/.test(password);
        
        document.getElementById('length').classList.toggle('valid', lengthValid);
        document.getElementById('uppercase').classList.toggle('valid', uppercaseValid);
        document.getElementById('lowercase').classList.toggle('valid', lowercaseValid);
        document.getElementById('number').classList.toggle('valid', numberValid);
        document.getElementById('special').classList.toggle('valid', specialValid);
        
        return lengthValid && uppercaseValid && lowercaseValid && numberValid && specialValid;
    }
    
    // Show error message
    function showError(input, message) {
        const errorElement = document.getElementById(`${input.id}-error`);
        errorElement.textContent = message;
        input.classList.add('invalid');
        input.classList.remove('valid');
    }
    
    // Clear error message
    function clearError(input) {
        const errorElement = document.getElementById(`${input.id}-error`);
        errorElement.textContent = '';
        input.classList.remove('invalid');
        input.classList.add('valid');
    }
    
    // Validate email format
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Form submission
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validate required fields
        const requiredInputs = form.querySelectorAll('[required]');
        requiredInputs.forEach(input => {
            if (input.value.trim() === '') {
                showError(input, 'Este campo é obrigatório');
                isValid = false;
            } else {
                clearError(input);
            }
        });
        
        // Validate NIF
        if (nifDonoInput.value.trim() !== '' && !validateNIF(nifDonoInput)) {
            isValid = false;
        }
        
        if (nifEmpresaInput.value.trim() !== '' && !validateNIF(nifEmpresaInput)) {
            isValid = false;
        }
        
        // Validate email format
        const emailDonoInput = document.getElementById('emailDono');
        const emailEmpresaInput = document.getElementById('emailEmpresa');
        
        if (emailDonoInput.value.trim() !== '' && !validateEmail(emailDonoInput.value)) {
            showError(emailDonoInput, 'Formato de email inválido');
            isValid = false;
        }
        
        if (emailEmpresaInput.value.trim() !== '' && !validateEmail(emailEmpresaInput.value)) {
            showError(emailEmpresaInput, 'Formato de email inválido');
            isValid = false;
        }
        
        // Validate password strength
        if (!validatePasswordStrength()) {
            showError(passwordInput, 'A senha não atende aos requisitos de segurança');
            isValid = false;
        }
        
        // Validate password confirmation
        if (passwordInput.value !== confirmPasswordInput.value) {
            showError(confirmPasswordInput, 'As senhas não coincidem');
            isValid = false;
        }
        
        // Validate phone numbers
        const phoneRegex = /^[0-9]{9}$/;
        
        if (telefoneDonoInput.value.trim() !== '' && !phoneRegex.test(telefoneDonoInput.value)) {
            showError(telefoneDonoInput, 'O telefone deve conter 9 dígitos');
            isValid = false;
        }
        
        if (telefoneEmpresaInput.value.trim() !== '' && !phoneRegex.test(telefoneEmpresaInput.value)) {
            showError(telefoneEmpresaInput, 'O telefone deve conter 9 dígitos');
            isValid = false;
        }
        
        // Validate terms checkbox
        const termosCheckbox = document.getElementById('termos');
        if (!termosCheckbox.checked) {
            showError(termosCheckbox, 'Você deve aceitar os termos e condições');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            
            // Scroll to first error
            const firstError = document.querySelector('.error-message:not(:empty)');
            if (firstError) {
                firstError.previousElementSibling.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
});
