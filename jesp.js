/* global URI, location, console, $, Plotly */

var G = {
  'dtConfig': {
    'paging': false,
    'autoWidth': false,
    'table-layout': 'fixed',
    'fixedHeader': true,
    'columnDefs': [
      // https://github.com/rstudio/DT/issues/354
      {
        'orderSequence': ['desc', 'asc'],
        'targets': '_all'
      },
      {
       'searchable': false,
       'orderable': false,
       'targets': 0
      }
    ],
  }
};

G.jespTable = $('#jesp_table');
G.jespTable = G.jespTable.DataTable(G.dtConfig);

// https://datatables.net/examples/api/counter_columns.html
G.jespTable.on( 'order.dt search.dt', function () {
  G.jespTable.column(0, {search:'applied', order:'applied'}).
    nodes().each( function (cell, i) {
      cell.innerHTML = i+1;
    } );
} ).draw();

G.url = new URI(location.href);
G.urlConfig = G.url.search(true);
if (! G.urlConfig.c) { G.urlConfig.c='config.json'; }
console.log(G);

$.getJSON(G.urlConfig.c, drawBubble);

function drawBubble(config) {
  
  console.log(config);
  var canvas = $('#canvas');
  
  var trace1 = {
    x: [1, 2, 3, 4],
    y: [10, 11, 12, 13],
    mode: 'markers',
    marker: {
      size: [40, 60, 80, 100]
    }
  };
  
  var layout = {
    title: 'Marker Size',
    showlegend: false,
    width: canvas.width(),
    height: canvas.height(),
    // plot_bgcolor: '#ffd',
  };
  
  G.jespTable.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
  //console.log(rowIdx, this.data()[2]);
  } );
  console.log(G.jespTable.header());
  $('#jesp_table thead tr th').each(function(){
  //console.log($(this).text());
  }); 
  
  // Plotly.newPlot('canvas', [trace1], layout);

}

