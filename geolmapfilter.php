<!DOCTYPE html>
<html lang="en">

<head>
	<title id='Description'>Filtered Geol Maps</title>
	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html;" charset="utf-8" />
	

	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jq-3.3.1/jszip-2.5.0/dt-1.10.22/af-2.3.5/b-1.6.4/b-colvis-1.6.4/b-html5-1.6.4/b-print-1.6.4/cr-1.5.2/fc-3.3.1/fh-3.1.7/kt-2.5.3/r-2.2.6/rg-1.1.2/rr-1.2.7/sc-2.0.3/sb-1.0.0/sp-1.2.0/sl-1.3.1/datatables.min.css"/>
 
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
	<script type="text/javascript" src="https://cdn.datatables.net/v/dt/jq-3.3.1/jszip-2.5.0/dt-1.10.22/af-2.3.5/b-1.6.4/b-colvis-1.6.4/b-html5-1.6.4/b-print-1.6.4/cr-1.5.2/fc-3.3.1/fh-3.1.7/kt-2.5.3/r-2.2.6/rg-1.1.2/rr-1.2.7/sc-2.0.3/sb-1.0.0/sp-1.2.0/sl-1.3.1/datatables.min.js"></script>
	
	
	
      


	<script type="text/javascript">
		$(document).ready(function() {
			var table = $('#example').DataTable( {
				"ajax": {
					"url": "geolmapfilterTest.php",
					"dataSrc": ""
				},
			    "columns": [
					{ "data": "quad_name" },
					{ "data": "pub_name" },
					{ "data": "series_id" },
					{ "data": "pdf_link4AlphList" },
					{ "data": "gis_link" },
					{ "data": "buy_link4AlphList" },
					{ "data": "keywords"}
				],
				"columnDefs": [
					{"targets": 6,
					"searchable": true,
					"visible": false}
				],
				"paging": false,
				"fixedHeader": true
			});
			console.log(table);
			new $.fn.dataTable.FixedHeader( table, {
				// options
			} );
			//things to search "keyword "geoindex" and then each layer has "7.5", "intermediate", "1x2", "irregular""
			$('#75').click(function()  {
				table.column(6).search('24k').draw();
			})
			$('#30x60').click(function()  {
				table.column(6).search('intermediate').draw();
			})
			$('#1x2').click(function()  {
				table.column(6).search('1x2').draw();
			})
			$('#irreg').click(function()  {
				table.column(6).search('irregular').draw();
			})
			$('#clear').click(function()  {
				table.column(6).search('').draw();
			})
		});
	</script>


</head>

<body class='default'>
<button id="75">7.5' Quads</button>
<button id="30x60">Intermediate Scale</button>
<button id="1x2">1x2 Quads</button>
<button id="irreg">Other Maps</button>
<button id="clear">Clear Filter</button>

	<table id="example" class="display" style="width:100%">
        <thead>
            <tr>
                <th>Quadrangle/Area</th>
                <th>Map Name</th>
                <th>Series #</th>
                <th>PDF File</th>
                <th>GIS Zip File</th>
                <th>Purchase</th>
				<th>Keywords</th>
			</tr>
        </thead>
    </table>

</html>
