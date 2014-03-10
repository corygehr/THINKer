$('#schema').on('change', function() {
	var schemaVal =$('#schema').val();
	var url = "index.php?section=DataPull&subsection=dsSelect&phase=fetchTables&view=json";

	var dataObj = {};

	dataObj['schema'] = schemaVal;

	$.getJSON(url, dataObj)
		.done(function(data) {
			// Output if we get data back
			if(data['DATA']['tables'] !== undefined && data['DATA']['tables'].length > 0) {
				// Clear dropdown
				$('#table').html('<option>Select One:</option>');
				// Add options
				$.each(data['DATA']['tables'], function(i, item) {
					var val = item[0];
					var text = item[1];
					// Add item to table
					$("#table")
						.append($("<option></option>")
						.attr("value", val)
						.text(text));
				});
				// Enable dropdown
				$('#table').attr('disabled', false);
			}
			else {
				$('#table')
					.attr('disabled', true)
					.html('<option>--No tables found in selected schema--</option>');
			}
		})
		.fail(
			$('#table')
				.attr('disabled', true)
				.html('<option>----</option>')
		);
});