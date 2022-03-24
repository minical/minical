$(document).ready(function() {
    var language_id = $('#language_id option:selected').val();
    //alert(language_id);
    var language_name = $('#language_id option:selected').text();
    //alert(language_name);
    $('#languagename').val(language_name);
    $('#languagelabel').html(language_name);

    $('.language-translation').DataTable();
 
    var max_fields      = 100000000000; //maximum input boxes allowed
    var wrapper         = $(".input_fields_wrap"); //Fields wrapper
    var add_button      = $(".add_field_button"); //Add button ID
    
    var x = 1; //initlal text box count
    $(add_button).click(function(e){ //on add input button click
        $(".saveBT").css("display","block");
        $('html, body').scrollTop( $(document).height() );
        //$("html, body").animate({ scrollTop: $(document).height() }, "slow");
        e.preventDefault();

        if(x < max_fields){ //max input box allowed
            x++; //text box increment
            $(wrapper).append('<div class="checkFields"><div class="clearfix"></div><div class="clearfix"></div><div class="col-sm-2"></div><div class="col-sm-3"><input type="text" class="form-control" name="phrase_keyword[]" id="phrase_keyword" required></div><!--<div class="col-sm-3"><input type="text" class="form-control" name="default_language_phrase[]" id="default_language_phrase" required></div>--><div class="col-sm-3"><input type="text" class="form-control" name="selected_language_phrase[]" id="selected_language_phrase_'+x+'"></div><a href="#" class="remove_field" data-toggle="tooltip" data-placement="top" title="Remove" ><i class="fa fa-window-close"></i></a><div class="clearfix"></div><div class="clearfix"></div><br></div>'); //add html
        }
    });

    $(wrapper).on("click",".remove_field", function(e){ //user click on remove text
        //alert(x);

        e.preventDefault(); $(this).parent('div').remove(); x--;
        if(x==1){
            $(".saveBT").css("display","none");
        }

    })

    $('#addlanguage').on('click', function () {
    var post_data = {
        'lang_name' : $('input[name="lang_name"]').val()
    };
    if( $('input[name="lang_name"]').val() == ""){
        alert('Please add a Language Name');
        return false;
    }

    $.ajax({
        type   : "POST",
        url    : getBaseURL() + 'settings/translation/add_language_AJAX',
        data   : post_data,
        dataType: "json",
        success: function (data) {
            if(data.success){
                 alert(data.success);
                location.reload();
            }else{
                alert(l('Some error occured! Please try again.'));
            }
        }
    });
    return false;
    });

});
// Get Translation Data with respect to language
function changeTranslationLanguage(language_id)
{   
    var non_translated_phrase = $('.non_translated_key').val();

    if(non_translated_phrase == 1) {
        window.location.href = getBaseURL() + "settings/translation/"+language_id+"?non_translated_phrase";
    } else {
        window.location.href = getBaseURL() + "settings/translation/"+language_id;
    }
}

// Change language status
function changeLanguageStatus(value)
{
    var language_id = $("#language_enable option:selected").attr("language_id");
    $.ajax({
        type: "POST",
        url: getBaseURL() + "settings/translation/change_language_status",
        data: {language_id:language_id,value:value},
        success: function(res)
        {
              alert(res);
              location.reload();
        }
    });
}
function update_translation_phrase(tid ,pid, phrase_value)
{
    var languageID = $('.language-id').val();
    $.ajax({
        type: "POST",
        url: getBaseURL() + "settings/translation/update_translation_phrase",
        data: {tid: tid, pid: pid, phrase_value: phrase_value, language_id: languageID},
        success: function(res)
        {
            if(res.trim() == 'success'){
                if(tid != '' && tid != undefined) {
                    $('.success_msg_'+tid).html('<span class="label label-success translation_msg" style=""><strong>Saved</strong></span>');
                    setTimeout(function(){
                        $('.success_msg_'+tid).html('');
                    },3000);
                } else {
                    $('.success_msg_'+pid).html('<span class="label label-success translation_msg" style=""><strong>Saved</strong></span>');
                    setTimeout(function(){
                        $('.success_msg_'+pid).html('');
                    },3000);
                }
                
            }
        }
    });
}

$(document).on('change', '.non_translated_phrase', function(){
    var status = $(this).is(":checked");
    var language_id = $('#language_id option:selected').val();

    if(status) {
        window.location.href = getBaseURL() + "settings/translation/"+language_id+"?non_translated_phrase";
    } else {
        window.location.href = getBaseURL() + "settings/translation/"+language_id;
    }
});