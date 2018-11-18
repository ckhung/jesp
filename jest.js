/* global $ */

var dtConfig = {
  'paging': false,
  'autoWidth': false,
  'table-layout': 'fixed',
  'columnDefs': [ {
     'searchable': false,
     'orderable': false,
     'targets': 0
  } ],
  'order': [[ 1, 'asc' ]]
};

// https://datatables.net/examples/api/counter_columns.html
var jest = $('#jest_table');
jest = jest.DataTable(dtConfig);

jest.on( 'order.dt search.dt', function () {
  jest.column(0, {search:'applied', order:'applied'}).
    nodes().each( function (cell, i) {
      cell.innerHTML = i+1;
    } );
  } ).draw();

