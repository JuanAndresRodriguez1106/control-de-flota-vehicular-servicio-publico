document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('loginForm');
    const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    const mostrarError = (inputId, mensaje) => {
        const input = document.getElementById(inputId);
        const errorSpan = document.getElementById(`error-${inputId}`);
        input.classList.add('input-error');
        if (errorSpan) errorSpan.textContent = mensaje;
    };

    const limpiarError = (inputId) => {
        const input = document.getElementById(inputId);
        const errorSpan = document.getElementById(`error-${inputId}`);
        input.classList.remove('input-error');
        if (errorSpan) errorSpan.textContent = '';
    };

    const validarCampo = (id) => {
        const valor = document.getElementById(id).value.trim();
        let esValido = true;

        switch (id) {
            case 'correo':
                if (valor === '') {
                    mostrarError(id, 'El correo es obligatorio.');
                    esValido = false;
                } else if (!regexEmail.test(valor)) {
                    mostrarError(id, 'El correo no es válido.');
                    esValido = false;
                }
                break;
            case 'password':
                if (valor === '') {
                    mostrarError(id, 'La contraseña es obligatoria.');
                    esValido = false;
                } else if (valor.length < 6) {
                    mostrarError(id, 'La contraseña debe tener al menos 6 caracteres.');
                    esValido = false;
                }
                break;
        }
        return esValido;
    };

    // forEach VA después de las funciones
    ['correo', 'password'].forEach((fieldId) => {
        document.getElementById(fieldId).addEventListener('blur', () => validarCampo(fieldId));
        document.getElementById(fieldId).addEventListener('input', () => limpiarError(fieldId));
    });

    const validarFormularioCompleto = () => {
        return validarCampo('correo') && validarCampo('password');
    };

    form.addEventListener('submit', (e) => {
        if (!validarFormularioCompleto()) {
            e.preventDefault();
        }
    });
});