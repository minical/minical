innGrid.saveAllTaxTypes = function () {
	$(".tax-type-tr").each(function()
	{
		var taxTypeTr = $(this);
		var taxTypeId = taxTypeTr.attr('id');

		var taxTypeName = taxTypeTr.find('[name="tax-type-name"]').val();
		var taxRate = taxTypeTr.find('[name="tax-rate"]').val();
        var isPercentage = taxTypeTr.find('[name="is-percentage"]').val();
		var isPriceBracket = taxTypeTr.find('.open-price-brackets').hasClass('active');
		var isTaxInclusive = taxTypeTr.find('input[name="is-tax-inclusive"]').prop('checked');
        console.log(taxTypeName + taxRate);
        if (taxTypeName == 'New Tax Type') {
            alert(l('Please fill in tax type'));
            taxTypeTr.find('[name="tax-type-name"]').focus();
            return false;
        }
        if (parseFloat(taxRate) <= 0 && !isPriceBracket) {
            alert(l('Please enter a tax rate'));
            taxTypeTr.find('[name="tax-rate"]').focus();
            return false;
        }

		//Populate updates to standard room type information
		$.post(getBaseURL() + 'settings/accounting/update_tax_type', {
				tax_type_id: taxTypeId,
				tax_type: taxTypeName,
				tax_rate: taxRate,				
				is_percentage: isPercentage,
				is_tax_inclusive: isTaxInclusive

			}, function (result) {
				//To Do: error checking.				
				//This is incomplete because it doesn't check whether the update_rate posts are complete
				//To Do: complete checking with update-rates
				if (typeof innGrid.taxTypeSavedCount !== 'undefined') {
					innGrid.taxTypeSavedCount ++;
					if (innGrid.taxTypeSavedCount === $('.tax-type-tr').size())
					{
						alert(l('All tax types saved'));
					}
				}
		}, 'json');
	//To do error checking.
	});
}

$(function() {

    $('#add-tax-type').click(function () {
		$.post(getBaseURL() + 'settings/accounting/create_tax_type', function (data){
			$('#tax-types').append(data);
		});		
	});
		

	$('#save-all-tax-types-button').on("click", function () {	
		innGrid.taxTypeSavedCount = 0;		
		innGrid.saveAllTaxTypes();
	});
	
	$('.add-price-bracket').click(function () {
		$('input.end-range').each(function(){
			if($(this).val() == "Infinity") $(this).val('');
		});
		addPriceBracket({to: 'Infinity'});
	});
	
    $(document).on('click', '.delete-tax-type', function () {		
		var that = this;
		var taxTypeName = $(that).parent().parent().find('.tax-type-name-editable').text();
		
		//Set custom buttons for delete dialog
		$("#confirm_delete_dialog")
		.html(l('Are you sure you want to remove charge type')+ '<span class="charge-type-name-editable">' + taxTypeName + '</span>?')
		.dialog({
			title: l('Delete Tax Type'),
			buttons: {				
				"Confirm Delete":function() {					
					$.post(getBaseURL() + 'settings/accounting/delete_tax_type', {
						tax_type_id: $(that).attr('id')
						}, function (results) {							
							if (results.isSuccess == true){
									$(that).parent().parent().remove();  //delete line of X button
									//alert(results.message);
								}
								else {
									//alert(results.message);
								}
							}, 'json');
					$(this).dialog("close");
				},
				"Cancel": function() {
					$(this).dialog("close");
				}
			}
		});
		
		$("#confirm_delete_dialog").dialog("open");
	});
	
});
function addPriceBracket (data) {
	var $row = $('<tr/>')
			.append($('<td/>')
				.append($('<input/>', {
					name: 'start-range[]',
					class: 'start-range form-control required m-117',
					type: 'text',
					required: true,
					value: data && data.from ? data.from : ''
				}))
			)
			.append($('<td/>')
				.append($('<input/>', {
					name: 'end-range[]',
					class: 'end-range form-control required m-117',
					type: 'text',
					required: true,
					value: data && data.to ? data.to : ''
				}))
			)
			.append($('<td/>')
				.append($('<input/>', {
					name: 'tax-rate[]',
					class: 'tax-rate form-control required m-117',
					type: 'text',
					required: true
				}))
			)
			.append($('<td/>')
				.append($('<select/>', {
					name: 'is-percentage[]',
					class: 'is-percentage form-control required m-117',
					style: 'min-width: 80px',
					required: true
				})
					.append($('<option/>', {text: '%', value: '1'}))
					.append($('<option/>', {text: $('.tax-price-brackets-table').data('currency'), value: '0'}))
				)
			)
			.append($('<td/>')
				.append($('<span/>', {
					class: 'remove-price-bracket',
					style: 'cursor: pointer'
				})
					.append($('<i/>', {class: 'fa fa-times-circle fa-2x', 'aria-hidden': true}))
				)
			);
	$('.tax-price-brackets-table').find('tbody').append($row);
}

$('#price-brackets-modal').on('click', '.remove-price-bracket', function() {
	$(this).parents('tr').remove();
	$('.tax-price-brackets-table').find('tbody').find('tr:first-child').find('input.start-range').val('1');
	$('.tax-price-brackets-table').find('tbody').find('tr:last-child').find('input.end-range').val('Infinity');
});

$('#price-brackets-modal').on('click', '.remove-all-price-brackets', function() {
	$('.tax-price-brackets-table').find('tbody').find('tr').remove();
});

$('#tax-types').on('click', '.open-price-brackets', function() {
	
	$('.tax-price-brackets-table').find('tbody').find('tr').remove();
	
    var tax_type_id = $(this).parents('tr').attr('id');
	
	$('.tax-price-form').find('[name="tax_type_id"]').val(tax_type_id);
	
    $.ajax({
	    type: "POST",
	    url: getBaseURL() + 'settings/accounting/get_price_brackets',
	    dataType : 'json',
	    data: {"tax_type_id": tax_type_id},
	    success: function(priceBrackets) {
			if(priceBrackets && priceBrackets.length > 0) {
				for (var $i = 0; $i < priceBrackets.length; $i++) {
					addPriceBracket();
					var $lastRow = $('.tax-price-brackets-table').find('tbody').find('tr:last-child');

					$lastRow.find('.start-range').val(priceBrackets[$i].start_range);
					
					var end_range = priceBrackets[$i].end_range;
					end_range = end_range.replace("99999999", "Infinity");
					
					$lastRow.find('.end-range').val(end_range);
					$lastRow.find('.tax-rate').val(priceBrackets[$i].tax_rate);
					$lastRow.find('.is-percentage').val(priceBrackets[$i].is_percentage);
				}
			} else {
				addPriceBracket({from: 1});
				addPriceBracket({to: 'Infinity'});
			}
	    }
    });
});


$('.tax-price-form').on('submit', function(e) {
    e.preventDefault();
	
	var formData = $('.tax-price-form').serialize();
	formData = formData.replace("Infinity", "99999999");
	
	var tax_type_id = $('.tax-price-form').find('[name="tax_type_id"]').val();
	
	var bracketCount = $('.tax-price-brackets-table').find('tbody').find('tr').length;
	
	$.ajax({
		url: getBaseURL() + 'settings/accounting/add_price_brackets',
		type: "POST",
		dataType : 'json',
		data: formData,
		success: function(data) {
			if(data.success && bracketCount > 0) {
				$('.tax-type-tr#'+tax_type_id).find('[name="tax-rate"]').prop('disabled', true);
				$('.tax-type-tr#'+tax_type_id).find('.open-price-brackets').addClass('active');
			} else if(bracketCount == 0) {
				$('.tax-type-tr#'+tax_type_id).find('[name="tax-rate"]').prop('disabled', false);
				$('.tax-type-tr#'+tax_type_id).find('.open-price-brackets').removeClass('active');
			}
			$('#price-brackets-modal').modal('hide');
		}
	});
	return false;
});


