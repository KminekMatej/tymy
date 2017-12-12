$(document).ready(function () {
    reTitle();
});

function reTitle() {
    numTitle = $("NAV[data-title]").attr("data-title");
    cnt = (document.title.match(/\|/g) || []).length;
    if (cnt == 1){
        if(numTitle > 0)
            document.title = numTitle + " | " + document.title;
    } else if (numTitle === "0") {
        document.title = document.title.replace(new RegExp(/\d+ \| /), "");
    } else
        document.title = document.title.replace(new RegExp(/\d+/), numTitle);
}

function btnRotate(btn, disable){
    if (btn.length > 0) {
        if (disable) {
            btn.find("i.fa").addClass("fa-spin");
            btn.prop("disabled", true);
            btn.attr("disabled", "disabled");
        } else {
            btn.find("i.fa").removeClass("fa-spin");
            btn.prop("disabled", false);
            btn.removeAttr("disabled");
        }
    }
}