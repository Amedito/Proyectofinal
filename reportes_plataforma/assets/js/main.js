// assets/js/main.js
$(document).ready(function() {
  $('.mes').click(function() {
    var mes = $(this).data('mes');
    if (mes != 4) return;
    $.get('reporte.php', { mes: mes }, function(html) {
      $('#reporte').html(html);

      // Inicializar DataTable
      var table = $('#tablaDatos').DataTable({
        pageLength: 10,
        dom: 'Bfrtip',
        buttons: [
          { extend: 'excelHtml5', title: 'Reporte_Abril' },
          { extend: 'pdfHtml5',   title: 'Reporte_Abril' },
          'print'
        ]
      });

      // Modal y formulario
      $('#btnAdd').click(() => $('#modalForm').fadeIn());
      $('.close').click(() => $('#modalForm').fadeOut());

      // Carga de listas
      function cargarListas() {
        $.getJSON('api/list_tiendas.php', tiendas => {
          $('#f_tienda').html('<option value="">Seleccione</option>');
          tiendas.forEach(t => $('#f_tienda').append(`<option>${t}</option>`));
        });
        $.getJSON('api/list_aplicativos.php', apps => {
          $('#f_aplicativo').html('<option value="">Seleccione</option>');
          apps.forEach(a => $('#f_aplicativo').append(`<option>${a}</option>`));
        });
      }
      cargarListas();

      // Dependientes
      $('#f_tienda').change(function() {
        $.get('api/get_supervisor.php', { tienda: $(this).val() }, sup => {
          $('#f_supervisor').html(`<option>${sup}</option>`);
        }, 'json');
      });
      $('#f_aplicativo').change(function() {
        $.get('api/get_circuito.php', { app: $(this).val() }, cir => {
          $('#f_circuito').html(`<option>${cir}</option>`);
        }, 'json');
      });

      // Guardado
      $('#formReporte').submit(function(e) {
        e.preventDefault();
        $.post('api/add_reporte.php', $(this).serialize(), res => {
          if (res.success) {
            alert('Guardado correctamente');
            $('#modalForm').fadeOut();
            // Añadir fila nueva al DataTable
            let vals = $(this).serializeArray().reduce((o,v)=>{o[v.name]=v.value;return o;},{});
            table.row.add([
              table.rows().count()+1,
              vals.tienda, vals.tipo, vals.fecha, vals.hora,
              vals.estado, vals.severidad, vals.aplicativo,
              vals.circuito, vals.supervisor, vals.detalle
            ]).draw(false);
          } else {
            alert('Error al guardar');
          }
        }, 'json');
      });

    });
  });
});
