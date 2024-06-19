language: {
            "decimal": "",
            "emptyTable": "No hay información",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ Entradas",
            "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
            "infoFiltered": "(Filtrado de _MAX_ total entradas)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ Entradas",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "Sin resultados encontrados",
            "paginate": {
                "first": "Primero",
                "last": "Ultimo",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        "order": [[0, "desc"]],
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": true,
        "responsive": true,
        "fixedHeader": true,
        "scrollX": true,
        "scrollY": true,
        "scrollCollapse": true,
        "fixedColumns": {
            leftColumns: 1,
            rightColumns: 1
        },
        layout: {
            topEnd: function () {
                let btn = document.createElement('button');
                btn.textContent = 'Ir a Carga de Producción';
                btn.classList.add('btn', 'btn-primary');
                btn.addEventListener('click', function () {
                    window.location.href = "{{ route('fabricacion.create') }}";
                });
                return btn;
            },
            topStart: {
                search: {
                    placeholder: 'Buscar en la tabla'
                } // Puedo agregar un botón de búsqueda avanzada
            },

            bottomStart: {  
                pageLength: {
                menu:  [10, 25, 50, "All"]
            }
        },

            bottomEnd: {
                paging: {
                    numbers: 5 // Puedo agregar "simple" para mostrar solo los botones de siguiente y anterior
                }
            }
        }
    });




table.caption('Registro de Fabricación: Tabla de datos de producción.');
// table.ready(function () {
//     table.search.fixed('Clase Familia', '1').draw(); // Filtra la tabla por la columna "Clase Familia" con el valor "1"
// });

$('#mySelect').on('change', function () {
    table.search.fixed('Clase Familia', $(this).value()).draw();
    console.log($(this).value());
});