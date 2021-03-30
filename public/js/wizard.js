innGrid.getGuiders = function(callback) {
    /*
    $.ajax(getBaseURL() + 'wizard/get_current_guider', function(data) {
        
        if(data.completed) {
            callback(false);
            return;
        }

        callback(data.guiders);
    });
    */
};

innGrid.bindGuiders = function(guiders) {
    if(!guiders || !guiders.length) return;
    if(!$(guiders[0].attachTo).length) {
        return setTimeout(innGrid.bindGuiders, 20, guiders);
    }
    
    var popover = $(guiders[0].attachTo).popover({
        title: guiders[0].title,
        placement: guiders[0].placement,
        content: guiders[0].description,
        container: 'body',
        animation: true
    });

    $("html, body").animate({scrollTop: 0}, function() {
        popover.popover('show');
    });

    $(guiders[0].attachTo).click({popover: popover}, function(e) {
        e.data.popover.popover('destroy');
    });

    if(guiders.length > 1) {
        $(guiders[0].attachTo).click(function(e) {
            $.post(getBaseURL() + 'wizard/advance_guider_POST');
            setTimeout(innGrid.bindGuiders, 1000, guiders.slice(1));
        });
    }
};

(function() {
    innGrid.getGuiders(innGrid.bindGuiders);
})();
