$('#filterSelect').on('click', '.add', function() {
	// Get element number
	var num = parseInt($(this).closest('div').attr('id'));
	var newNum = num+1;
	var newDivId = 'div#'+newNum;

	// Clone div
	var divToCopy = $('div#'+num).clone().attr('id', newNum);

	$(this).closest('div').after(divToCopy);

	//divToCopy.appendTo($('div#'+num));

	$(newDivId).children().each(function() {
		if($(this).is("input"))
		{
			var inputName = $(this).attr('id').split('_')[0];
			var newName = inputName+'_'+newNum;
			$(this).attr('id', newName);
			$(this).attr('name', newName);
			$(this).val('');
		}
		else if($(this).is("select"))
		{
			var inputName = $(this).attr('id').split('_')[0];
			var newName = inputName+'_'+newNum;
			$(this).attr('id', newName);
			$(this).attr('name', newName);

			// Re-disable the filter option box
			if(inputName === 'filter-option')
			{
				$(this).attr('disabled', true);
				$(this).html('<option>Filter Type:</option>');
			}
		}
	});

	// Append a remove tag if this isn't the last filter
	if(num === 1)
	{
		var andOrName = 'filter-andor_' + newNum;
		$(newDivId).prepend("<select id='" + andOrName +"' name='" + andOrName +"'><option value='AND'>AND</option><option value='OR'>OR</option></select>");
		$(newDivId).last().append("<img id='removeButton' class='remove clickable' src='View/html/images/remove.png' width='20' height='20' />");
	}

	// Hide buttons
	$(this).next('img.remove').fadeOut('fast');
	$(this).fadeOut('fast');
});

$('#filterSelect').on('click', '.remove', function() {
	// Get element number
	var num = parseInt($(this).closest('div').attr('id'));
	var currDiv = 'div#'+num;
	var prevNum = num-1;
	var prevDiv = 'div#'+prevNum;

	// Remove div
	$(currDiv).remove();

	// Now re-add the buttons to the last div
	// New closest div
	$(prevDiv).children('#addButton').show();
	$(prevDiv).children('#removeButton').show();
});

$('#filterSelect').on('change', '.colFilter', function() {
	var filterVal = $(this).val();
	
	// Get ID so we know which filter types box to populate
	var idStart = $(this).attr('id').split('_');
	var idNo = idStart[1];
	var targetBox = '#filter-option_' + idNo;

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