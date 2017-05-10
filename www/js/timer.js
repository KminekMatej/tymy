$(function () {
    $.nette.init();
    
    setTimeout(function(){nav();}, 5000);

    function nav(){
        $.nette.ajax({
            url: "/?do=navbar-refresh",
            complete: function (payload) {
                setTimeout(function(){nav();}, 5000);
            }
        });
    }
});