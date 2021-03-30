// for customize popup on shift+right-click
var translation_key = '';
var translation_key_id = '';
var language_length = '';
$(document).ready(function() {
    $('body').on('contextmenu', function(event) {
        if(typeof is_current_user_admin != "undefined" && is_current_user_admin)
        {
            if(event.shiftKey)
            { 
                window.event.returnValue = false;
                $("#show_popup").addClass("show");
                $('#show_popup').css({'top':event.pageY,'left':event.pageX, 'position':'absolute'});
                translation_key = $.trim(event.target.childNodes[0].data);
            }
        }
    });
});

// this is from another SO post...  
$(document).bind("click", function(event) {
    if(event.target != $('.change_translation')[0]){
        $("#show_popup").removeClass("show");
    }
});


$('body').on('click','.change_translation',function(){
    $("#show_popup").removeClass("show");
    if(translation_key!='')
    {
        $(document).openTranslationModal({});
    }
    else
    {
        alert("Can't detect text. Please try again!");
    }
});

/*  Plugin for Translation Modal
 *  
 */
(function ($) {
    

    // initialize
    $("body").append(
            $("<div/>", {
                class: "modal fade",
                id: "translation-modal",
                "tabindex": "-1",
                "role": "dialog",
                "aria-hidden": true,
                style: "z-index: 9999;"
            }).modal({
                show: false,
                backdrop: 'static'
            }).append(
                    $("<div/>", {
                        class: "modal-dialog"
                    }).append(
                    $("<div/>", {
                        class: "modal-content"
                    })
                    )
                )
            );

    var allLanguageData = "";
    var TranslationModal = function (options) {
        var that = this;
        $.ajax({
                type: "POST",
                url: getBaseURL() + "language_translation/get_languages",
                data: {},
                dataType: "json",
                success: function (data) {
                    allLanguageData = data;
                    language_length = data.length;
                    that._initializeTranslationModal(allLanguageData);
                },
            });
    };
    
    TranslationModal.prototype = {
        _init: function () {
          
        },
        _initializeTranslationModal: function (allLanguageData) {
            var that = this;
           // re-initialize by deleting the existing modal
           $("#translation-modal").modal('show');
           $("#translation-modal").find(".modal-content").html("");
            this._populateTransltionModel(allLanguageData);
        },
        
        _populateTransltionModel: function(allLanguageData){
            var thisCall = this;
            var new_languages_options = "";
            var current_lang_id = "";
            new_languages_options = '<option value="" >Select Language</option>';
            var language_select = $('<select/>', {
                            name: 'language_id[]',
                            class: 'col-sm-5 trans_language form-control',
                        });
                        
                allLanguageData.forEach(function (data) {
                    var languages_options = $('<option/>', {
                        value: data.id,
                        text: data.language_name
                    });
                    
                    if(data.current_language == data.language_name) {
                        languages_options.prop('selected', true);
                        current_lang_id = data.id;
                    }
                    language_select.append(languages_options);
                    
                    // for new appended language seletor
                    new_languages_options += '<option value="'+data.id+'" >'+data.language_name+'</option>';
                }); 
                
                
            this._defaultGetData(current_lang_id, translation_key); // get by deafult data if same phrase is available
                
                  
            var new_language_selector_div = '<div class="col-sm-12 language_selected language_selector ">'+
                                                '<select name="language_id[]" class="col-sm-5 trans_language form-control">'+
                                                    new_languages_options+
                                                '</select>'+
                                                '<span class="col-sm-2 trans_span_differ">'+
                                                    ' : '+
                                                '</span>'+
                                                '<input name="phrase_value[]" class="col-sm-5 trans_phrase form-control">'+
                                            '</div>';

            
            $('#translation-modal').find('.modal-content').html(
                $("<div/>", {
                    class: "modal-header",
                    html: "Translation : "+translation_key
                }).append(
                      $("<div/>", {
                          class: "pull-right"
                        }).append(
                            $("<button/>", {
                               type: "button",
                               class: "add_other_lang btn btn-sm btn-primary",
                               html:"Add translation for other language" 
                           }).on('click',function(){
                               
                               if($('.language_selected').length < language_length)
                               {
                                   $('.new_language_selector_div').append(new_language_selector_div);
                               }
                           })
                        )
                    )
                ).append(
                $("<div/>", {
                    class: "modal-body form-horizontal"
                }).append(
                        $("<div/>", {
                          class: "form-group new_language_selector_div "
                }).append(
                      $("<div/>", {
                          class: "col-sm-12 language_selected checkbox_append_div"
                        }).append(
                            language_select  // language select dropdown
                            ).append(
                                $('<span/>', {
                                    class: 'col-sm-2 trans_span_differ',
                                    text: " : "
                                })
                            ).append(
                                $('<input/>', {
                                    name: 'phrase_value[]',
                                    class: 'col-sm-5 trans_phrase form-control',
                                    value: translation_key
                                })

                            )
                        )
                     )).append(
                $("<div/>", {
                    class: "modal-footer"
                }).append(
                     $("<button/>", {
                        type: "button",
                        class: "btn btn-success save_translation",
                        id:"save_translation",
                        html: "Save"
                    }).on('click',function(){
                        var saveDataArray = [];
                        var phraseCheckedKeysArray = [];
                        var lang_ids = document.getElementsByName('language_id[]');
                        var phrase_vals = document.getElementsByName('phrase_value[]');
                        for (var i = 0; i <lang_ids.length; i++) {
                            var json = {};
                            var lang_id=lang_ids[i];
                            var phrase_val=phrase_vals[i];
                            json[lang_id.value] = phrase_val.value;
                            saveDataArray.push(json);
                        }

                        $. each($("input[name='phrase_key']:checked"), function(){
                            phraseCheckedKeysArray.push($(this).val());
                        });
                        
                        var thisVal = $(this);
                        thisVal.prop("disabled", true);
                        $.ajax({
                                type: "POST",
                                url: getBaseURL() + "language_translation/save_lang_translation_data",
                                data: {saveDataArray:saveDataArray, phraseCheckedKeysArray:phraseCheckedKeysArray, current_lang_id:current_lang_id, translation_key:translation_key, translation_key_id: translation_key_id},
                                dataType: "json",
                                success: function (data) {
                                    if(data.success)
                                    {
                                        
                                        thisVal.html("Translations saved!");
                                        setTimeout(function(){
                                            $('#translation-modal').modal('hide');
                                            location.reload();
                                        },1000);
                                    }else{
                                        thisVal.prop("disabled", false);
                                    }
                                },
                            });
                        
                    })
                ).append(
                    $("<button/>", {
                       type: "button",
                       class: "btn btn-danger",
                       'data-dismiss': "modal",
                       html:"Close" 
                   })
                )
            )
    
            $('body').on('change','.trans_language',function(){
                var lang_id = $(this).val();
                var thisVal = $(this);
                
                    $.ajax({
                        type: "POST",
                        url: getBaseURL() + "language_translation/get_translation_data",
                        data: {lang_id:lang_id, current_lang_id:current_lang_id, translation_key:translation_key},
                        dataType: "json",
                        success: function (responseData) {
                            if(responseData && responseData.length > 1)
                            {
                                thisCall._ajaxOnLanguageDropdown(responseData); // get data by ajax , when we change language in dropdown
                                $('.phrase_checkbox_div').prepend('<span class="phrase_desc">Please select the phrase to be translated.</span>');
                            }
                            else
                            {
                                translation_key_id = responseData.phrase_id;
                                $(thisVal).parent().find('.trans_phrase').val(responseData.phrase);
                            }
                        },
                    });
                });
    
        },
        
        _defaultGetData: function(current_lang_id, translation_key){
            var that = this;
            $.ajax({
                type: "POST",
                url: getBaseURL() + "language_translation/get_translation_data",
                data: {lang_id: current_lang_id, current_lang_id: current_lang_id, translation_key: translation_key},
                dataType: "json",
                success: function (responseData) {
                    if (responseData && responseData.length > 1)
                    {
                        that._ajaxOnLanguageDropdown(responseData); // get data by ajax , when we change language in dropdown
                        $('.phrase_checkbox_div').prepend('<span class="phrase_desc">Please select the phrase to be translated.</span>');
                        $('#save_translation').prop('disabled', true);
                    }
                    else if(responseData && responseData.phrase_id) 
                    {
                        translation_key_id = responseData.phrase_id;
                    }
                }
            });
        },
        
        _ajaxOnLanguageDropdown: function(responseData){
            $('.phrase_checkbox_div').remove('');
            $('.new_language_selector_div').prepend('<div class="phrase_checkbox_div"><div/>');
            $('.phrase_checkbox_div').html('');
            for(var k = 0; k < responseData.length; k++)
            {
                var checkbox_div = $("<div/>", {
                                class: "col-sm-12 phrase_checkbox"
                              }).append($("<input/>", {
                            type: 'checkbox',
                            name: 'phrase_key',
                            class: 'col-sm-2',
                            value: responseData[k].id
                        }).on('click',function(){
                            if($("input[name='phrase_key']:checked").length > 0)
                            {
                                $("#save_translation").prop('disabled', false);
                            }	
                            else
                            {
                                $("#save_translation").prop('disabled', true);
                            }
                        })
                    ).append(
                        $('<span/>', {
                            html: responseData[k].phrase_keyword
                        })
                    );
                $('.phrase_checkbox_div').append(checkbox_div);
            }
        }
       
    };
    $.fn.openTranslationModal = function (options) {
        var body = $("body");
        // preventing against multiple instantiations
        alert('Please add translations from admin panel!');
        // $.data(body, 'translationModal',
        //     new TranslationModal(options)
        // );
    } 
    
})(jQuery, window, document);

innGrid.toggleSaveTranslationBtn = function() {
	if($("input[name='phrase_key']:checked").length > 0)
	{
		$("#save_translation").prop('disabled', false);
	}	
	else
	{
		$("#save_translation").prop('disabled', true);
	}
}

$('.change-language').click(function(){
    var language = $(this).attr('id');
    
    $.ajax({
            url: getBaseURL() + "account_settings/change_language",
            type: "POST",
            data: {language : language},
            success: function(resp){
                
                    location.reload();
            }
    });
});