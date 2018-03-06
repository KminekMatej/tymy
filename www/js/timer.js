$(function () {
    $.nette.init();
    var interval = 60000;
    setTimeout(function(){refresh(interval);}, interval);
});

function refresh(timer) {
    if($(".fa-sync").length>0){
        $(".fa-sync").addClass("fa-spin");
    }
     
    $.nette.ajax({
        url: "/?do=navbar-refresh",
        complete: function (payload) {
            reTitle();
            if ($(".fa-sync").length > 0) {
                $(".fa-sync").removeClass("fa-spin");
            }
            if (timer > 0) {
                setTimeout(function(){refresh(timer);}, timer);
            }
            homepageRefresh();
        }
    });
}

function homepageRefresh() {
    if ($("DIV.container.homepage").length > 0) {
        $("NAV UL.navbar-nav:first-child LI.nav-item.dropdown:first-child A.dropdown-item").each(function () {
            count = $(this).find("SPAN.badge").html();
            id = $(this).attr("id").replace("discussion-","discussion-pane-");
            var badge = $("DIV.container.homepage DIV#" + id + ".name SPAN.badge");
            badge.html(count);
            if (count > 0) {
                badge.removeClass("d-none");
            } else {
                badge.addClass("d-none");
            }

        });
    }
}