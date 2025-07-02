$(document).ready(function() {
    $('.footer').css({
        minWidth: $('#content table').first().width() + 20 + 'px'
    });

    // js determine if url has import
    if (window.location.href.includes('import')) {
        $("body").addClass('import');
    }
    
    if (window.location.href.includes('&select')) {
        $("body").addClass('select-data-page');
    }
});
