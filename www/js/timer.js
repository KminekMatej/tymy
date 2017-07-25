$(function () {
    $.nette.init();
    
    setTimeout(function(){nav();}, 5000);
});

function nav() {
    $.nette.ajax({
        url: "/?do=navbar-refresh",
        complete: function (payload) {
            reTitle();
            setTimeout(function () {
                nav();
            }, 5000);
            homepageRefresh();
        }
    });
}

function homepageRefresh() {
    if ($("DIV.container.homepage")) {
        $("NAV UL.navbar-nav LI.nav-item.dropdown:first-child A.dropdown-item").each(function () {
            count = $(this).find("SPAN.badge").html();
            id = $(this).attr("data-id")
            $("DIV.container.homepage DIV.name#" + id + " SPAN.badge").html(count);
            if (count > 0) {
                $("DIV.container.homepage DIV.name#" + id).removeClass("hidden-badge");
            } else {
                $("DIV.container.homepage DIV.name#" + id).addClass("hidden-badge");
            }

        });
    }
}