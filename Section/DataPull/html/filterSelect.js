$('.colFilter').on('change', function() {
	var filterVal = $(this).val();
	
	// Get ID so we know which filter types box to populate
	var idStart = $(this).attr('id').split('_');
	var idNo = idStart[1];
	var targetBox = '#filter_option_' + idNo;

	var url = "index.php?section=DataPull&subsection=filterSelect&phase=fetchFilterTypes&view=json";

	var dataObj = {};

	dataObj['column'] = filterVal;

	$.getJSON(url, dataObj)
		.done(function(data) {
			// Output if we get data back
			if(data['DATA']['filters'] !== undefined) {
				// Clear dropdown
				$(targetBox).html('<option>Select One:</option>');
				// Add options
				$.each(data['DATA']['filters'], function(i, item) {
					// Add item to table
					$(targetBox)
						.append($("<option></option>")
						.attr("value", i)
						.text(item));
				});
				// Enable dropdown
				$(targetBox).attr('disabled', false);
			}
			else {
				$(targetBox)
					.attr('disabled', true)
					.html('<option>--Invalid Column Specified--</option>');
			}
		})
		.fail(
			$(targetBox)
				.attr('disabled', true)
				.html('<option>----</option>')
		);
});