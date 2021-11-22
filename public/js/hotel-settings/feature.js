//todo: Improve validation and error checking
innGrid.saveAllFeatures = function () {
    var featureData = [];
    $(".feature-tr").each(function()
    {
        var feature = $(this);
        var showOnWebsite = '0';
        var featureID = feature.attr('id');
        var featureName = feature.find('[name="feature-name"]').val();

        if (feature.find('.can-be-sold-online-checkbox').prop('checked') === true) {
            showOnWebsite = '1';
        }

        featureData.push({
            feature_id: featureID,
            feature_name: featureName,
            show_on_website: showOnWebsite
        });

    });

    $.ajax({
        url: getBaseURL() + 'settings/room_inventory/save_features_AJAX',
        type: "POST",
        dataType: 'json',
        data: JSON.stringify(featureData),
        success: function(response){
            alert(l('All features saved'));
        }

    });
};

$(function() {
    var previous;
    $('#add-feature-button').click(function () {
        $.post(getBaseURL() + 'settings/room_inventory/get_new_feature_form_AJAX', function (data) {
            $('.rooms tbody').prepend(data);
        });
    });

    $(document).on('click', '.all-can-be-sold-online-checkbox', function() {
        var checked = $(this).prop('checked');
        $('.can-be-sold-online-checkbox').each(function() {
            $(this).prop('checked', checked);
        })
    });

    $(document).on('click', '.delete-feature-button', function () {
        var feature = $(this).closest(".feature-tr");
        var featureID = feature.attr('id');
        var featureName = feature.find("[name='feature-name']").val();

        //Set custom buttons for delete dialog
        var r = confirm(l('Are you sure you want to delete ') + featureName + '?');
        if (r == true) {
            $.post(getBaseURL() + 'settings/room_inventory/delete_feature_AJAX', {
                feature_id: featureID
            }, function (results) {
                if (results.isSuccess == true){
                    feature.remove();  //delete line of X button
                }
            }, 'json');
        }
    });


    $('#save-all-features-button').on("click", function () {
        innGrid.saveAllFeatures();
        mixpanel.track("Save all features button clicked in settings");
    });

});