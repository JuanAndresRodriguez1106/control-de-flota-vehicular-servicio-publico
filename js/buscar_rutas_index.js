// ================================================================
// Maneja la búsqueda de rutas en la landing
// Escucha el botón, llama al PHP y muestra los resultados
// ================================================================

// Espera a que el HTML esté completamente cargado
document.addEventListener('DOMContentLoaded', function () {

    // Selecciona los elementos del HTML que vamos a usar
    let searchBtn = document.getElementById('searchBtn');     /* Botón de buscar */
    let searchInput = document.getElementById('searchInput');   /* Campo de texto */
    let searchResults = document.getElementById('searchResults'); /* Contenedor de resultados */
    let searchTerm = document.getElementById('searchTerm');    /* Span del término buscado */
    let resultsBody = document.getElementById('resultsBody');   /* Tbody de la tabla */

    // ============================================================
    // FUNCIÓN PRINCIPAL DE BÚSQUEDA
    // ============================================================
    function buscar() {

        /* Obtiene el texto que escribió el usuario sin espacios */
        let termino = searchInput.value.trim();

        /* Si el campo está vacío no hace nada */
        if (termino === '') return;

        /* Llama al PHP enviando el término por POST */
        /* encodeURIComponent convierte caracteres especiales como tildes */
        fetch('index.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'q=' + encodeURIComponent(termino)
        })
            .then(function (response) {
                /* Convierte la respuesta a JSON */
                return response.json();
            })
            .then(function (datos) {

                /* Muestra el término buscado en el título de resultados */
                searchTerm.textContent = '"' + termino + '"';

                /* Limpia los resultados anteriores */
                resultsBody.innerHTML = '';

                if (datos.length === 0) {

                    /* Si no hay resultados muestra mensaje */
                    resultsBody.innerHTML = `
                        <tr>
                            <td colspan="5" style="text-align:center; color:#4a7a8a; padding:20px;">
                                No se encontraron resultados para "${termino}"
                            </td>
                        </tr>
                    `;

                } else {

                    /* Si hay resultados dibuja una fila por cada uno */
                    datos.forEach(function (fila) {
                        let tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td><span class="ruta-badge">${fila.nombre_ruta}</span></td>
                            <td>${fila.origen}</td>
                            <td>${fila.destino}</td>
                            <td>${fila.nombre}</td>
                            <td>${fila.conductor}</td>
                        `;
                        resultsBody.appendChild(tr);
                    });
                }

                /* Muestra la tabla de resultados */
                searchResults.style.display = 'block';
            })
            .catch(function (error) {
                /* Si hay error de red lo muestra en consola */
                console.log('Error en la búsqueda:', error);
            });
    }

    // ============================================================
    // EVENTOS
    // ============================================================

    /* Busca cuando el usuario hace clic en el botón */
    searchBtn.addEventListener('click', buscar);

    /* Busca cuando el usuario presiona Enter en el input */
    searchInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            buscar(); /* Llama a la función de búsqueda */
        }
    });


    /* Oculta la tabla cuando el usuario borra el texto del input */
    searchInput.addEventListener('input', function () {
        if (searchInput.value.trim() === '') {
            searchResults.style.display = 'none'; /* Oculta la tabla */
            resultsBody.innerHTML = '';            /* Limpia los resultados */
            searchTerm.textContent = '';           /* Limpia el término */
        }
    });

});