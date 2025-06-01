// assets/js/tablesort.js
// Filtros columna a columna para DataTables

$(document).ready(function() {
  var table = $('#tablaDatos').DataTable();

  // Clonar encabezado para filtros
  $('#tablaDatos thead tr').clone(true).appendTo('#tablaDatos thead');
  $('#tablaDatos thead tr:eq(1) th').each(function (i) {
    var title = $(this).text().trim();
    if (['N°','Fecha','Hora','Detalle'].includes(title)) {
      $(this).html('<input type="text" placeholder="Buscar '+title+'" style="width:100%"/>');
      $('input', this).on('keyup change clear', function () {
        if (table.column(i).search() !== this.value) {
          table.column(i).search(this.value).draw();
        }
      });
    } else {
      $(this).html('<select style="width:100%"><option value="">Todos</option></select>');
      table.column(i).data().unique().sort().each(function (d) {
        var txt = $('<div>').text(d).html();
        $('select', table.column(i).header()).append('<option value="'+txt+'">'+txt+'</option>');
      });
      $('select', this).on('change', function () {
        var val = $.fn.dataTable.util.escapeRegex($(this).val());
        table.column(i).search(val ? '^'+val+'$' : '', true, false).draw();
      });
    }
  });
  $('#tablaDatos thead tr:eq(1)').addClass('filters');
});

