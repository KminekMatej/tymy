$(document).ready(function () {
    reTitle();
});

function reTitle() {
    numTitle = $("NAV[data-title]").attr("data-title");
    cnt = (document.title.match(/\|/g) || []).length;
    if (cnt == 1)
        if(numTitle > 0)
            document.title = numTitle + " | " + document.title;
    else if (numTitle === "0")
        document.title = document.title.replace(new RegExp(/\d+ \| /), "");
    else
        document.title = document.title.replace(new RegExp(/\d+/), numTitle);
}