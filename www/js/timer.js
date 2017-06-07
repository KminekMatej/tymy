$(function () {
    $.nette.init();
    
    setTimeout(function(){nav();}, 5000);

    function nav(){
        $.nette.ajax({
            url: "/?do=navbar-refresh",
            complete: function (payload) {
                reTitle();
                setTimeout(function(){nav();}, 5000);
            }
        });
    }
});