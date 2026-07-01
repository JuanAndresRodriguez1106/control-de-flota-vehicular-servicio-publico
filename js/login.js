/**
 *login.js
 * Validaciones del formulario de inicio de sesión (cliente).
 * Campos soportados: 'correo', 'password'.
 * Los mensajes de error se muestran en elementos con id `error-<inputId>`.
 * Documentación en español con JSDoc.
 */
document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('loginForm');

    /**
     * Expresión regular para validar el formato básico de un correo electrónico.
     * Verifica que exista texto antes y después de '@', y un dominio con punto.
     * @type {RegExp}
     */
    const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    /**
     * Muestra un mensaje de error debajo del campo indicado y le agrega
     * la clase visual 'input-error' para resaltarlo.
     *
     * @param {string} inputId - ID del campo de entrada (ej. 'correo', 'password').
     * @param {string} mensaje - Texto del error a mostrar al usuario.
     * @returns {void}
     */
    const mostrarError = (inputId, mensaje) => {
        const input = document.getElementById(inputId);
        const errorSpan = document.getElementById(`error-${inputId}`);
        input.classList.add('input-error');
        if (errorSpan) errorSpan.textContent = mensaje;
    };

    /**
     * Elimina el mensaje de error y la clase visual 'input-error' del campo indicado.
     * Se usa típicamente en el evento 'input' para limpiar el error mientras el
     * usuario corrige el valor.
     *
     * @param {string} inputId - ID del campo de entrada a limpiar.
     * @returns {void}
     */
    const limpiarError = (inputId) => {
        const input = document.getElementById(inputId);
        const errorSpan = document.getElementById(`error-${inputId}`);
        input.classList.remove('input-error');
        if (errorSpan) errorSpan.textContent = '';
    };

    /**
     * Valida un campo específico del formulario según su ID.
     * Reglas aplicadas:
     * - 'correo': obligatorio y debe cumplir el formato de regexEmail.
     * - 'password': obligatorio y debe tener al menos 6 caracteres.
     *
     * Si el campo no es válido, invoca mostrarError() con el mensaje correspondiente.
     *
     * @param {string} id - ID del campo a validar ('correo' o 'password').
     * @returns {boolean} true si el campo es válido, false en caso contrario.
     */
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

    /**
     * Asocia los eventos de validación en tiempo real a los campos del formulario:
     * - 'blur': valida el campo cuando el usuario sale de él.
     * - 'input': limpia el error mientras el usuario escribe.
     *
     * forEach VA después de las funciones (para que ya existan al momento
     * de registrar los listeners).
     */
    ['correo', 'password'].forEach((fieldId) => {
        document.getElementById(fieldId).addEventListener('blur', () => validarCampo(fieldId));
        document.getElementById(fieldId).addEventListener('input', () => limpiarError(fieldId));
    });

    /**
     * Valida todos los campos del formulario de inicio de sesión.
     * Ejecuta validarCampo() sobre cada campo requerido; si alguno falla,
     * el formulario completo se considera inválido.
     *
     * @returns {boolean} true si todos los campos son válidos, false si al menos uno falla.
     */
    const validarFormularioCompleto = () => {
        return validarCampo('correo') && validarCampo('password');
    };

    /**
     * Intercepta el envío del formulario para validar antes de enviarlo al servidor.
     * Si la validación falla, cancela el envío con preventDefault().
     *
     * @param {SubmitEvent} e - Evento de envío del formulario.
     * @returns {void}
     */
    form.addEventListener('submit', (e) => {
        if (!validarFormularioCompleto()) {
            e.preventDefault();
        }
    });
});