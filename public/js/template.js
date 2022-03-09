var checkIfRoomInventroyIsON = false;
$(function () {

    $("#from_date, #to_date").datepicker({ dateFormat: 'yy-mm-dd' });
    // if error message (alert-dissmissable) is present, and user clicks outside
    $(document).on("click", function (e) {
        if (!$(e.target).is('.alert-dismissible'))
        {
            $('.alert-dismissible').fadeOut(800);
        }
    });
    
    // for add new row
	$("#add_more_admin_id").on("click", function() {
        var count = $(this).attr('count');
        innGrid.addNewAdminIdRow(parseInt($(this).attr('count'))+1);
        count++;
        $(this).attr('count', count);
        $('.add_more_admin_id').val(count);
        
	});
    
    // for delete row
    $('body').on("click", ".remove_more_admin_id", function() {
		innGrid.RemoveAdminIdRow($(this).attr('id'));
	});
    
    
});

innGrid.addNewAdminIdRow = function(count) {
    
	var html_content = '<div class="form-group whitelabel_admin_id" id="whitelabel_admin_id_'+count+'">'+
                            '<label for="admin_id" class="col-sm-3 control-label">'+
                                'Admin User Email'+
                            '</label>'+
                            '<div class="col-sm-3">'+
                                '<input type="text" class="form-control admin_user_id" name="admin_user_id_'+count+'" id="admin_user_id_'+count+'" >'+
                            '</div>'+
                            '<div class="col-sm-3">'+
                                '<button type="button" id="'+count+'" class="remove_more_admin_id btn btn-sm btn-danger"><i class="fa fa-minus"></i></button>'+
                            '</div>'+
                        '</div>';
    $('.whitelabel_admin').append(html_content);
    
}

innGrid.RemoveAdminIdRow = function(count) {
    $('#whitelabel_admin_id_'+count).remove();    
    
    var count = 1;
    $('.admin_user_id').each(function(){
        console.log(count);
        $(this).attr('id', 'admin_user_id_'+count);
        $(this).attr('name', 'admin_user_id_'+count);
        count++;
    });
    var count = 1;
    $('.remove_more_admin_id').each(function(){
        $(this).attr('id', count);
        count++;
    });
    var count = 1;
    $('.whitelabel_admin_id').each(function(){
        $(this).attr('id', 'whitelabel_admin_id_'+count);
        count++;
    });
    $("#add_more_admin_id").attr('count', $("#add_more_admin_id").attr('count') - 1);
    $('.add_more_admin_id').val($("#add_more_admin_id").attr('count'));
}

function company_modal(company_data) {
    $("body").append(
            $("<div/>", {
                class: "modal fade",
                id: "company-list-modal",
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
                    }).append(
                        $("<div/>", {
                            class: "modal-header",
                            html: ""
                        })
                        ).append(
                            $("<div/>", {
                                class: "modal-body form-horizontal"
                            }).append(
                                    $("<div/>", {
                                      class: "form-group companies_data"
                            }).append(
                                       company_data 
                                    )
                                 ).append(
                            $("<div/>", {
                                class: "modal-footer"
                            }).append(
                                $("<button/>", {
                                   type: "button",
                                   class: "btn btn-danger",
                                   'data-dismiss': "modal",
                                   html:"Close" 
                               }).on('click', function(){
                                   
                               })
                            )
                        )
                    )
                )
            )
        );
    }

if(window.location.pathname.split("/").pop() == "login")
{
    sessionStorage.removeItem("beforeDays");
    sessionStorage.removeItem("afterDays");
    sessionStorage.removeItem("currentCompanyId");
}

if(sessionStorage.getItem("currentCompanyId") != $('#currentCompanyId').val())
{
    sessionStorage.removeItem("beforeDays");
    sessionStorage.removeItem("afterDays");
    sessionStorage.setItem("currentCompanyId", $('#currentCompanyId').val());
}

window.parent.postMessage({
    'minical-current-url': window.location.href
},"*");