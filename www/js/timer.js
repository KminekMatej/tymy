$(function () {
    $.nette.init();
    
    setTimeout(function(){nav();}, 60000);
});

function nav() {
    $.nette.ajax({
        url: "/?do=navbar-refresh",
        complete: function (payload) {
            reTitle();
            setTimeout(function () {
                nav();
            }, 60000);
            homepageRefresh();
        }
    });
}

function homepageRefresh() {
    if ($("DIV.container.homepage").length > 0) {
        $("NAV UL.navbar-nav LI.nav-item.dropdown:first-child A.dropdown-item").each(function () {
            count = $(this).find("SPAN.badge").html();
            id = $(this).attr("id").replace("discussion-","discussion-pane-");
            var badge = $("DIV.container.homepage DIV#" + id + ".name SPAN.badge");
            badge.html(count);
            if (count > 0) {
                badge.removeClass("hidden-badge");
            } else {
                badge.addClass("hidden-badge");
            }

        });
    }
}