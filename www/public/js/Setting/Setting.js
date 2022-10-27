function prependHttp(url) {
    if (url.trim() == "")
        return "";
    if (!/^(?:f|ht)tps?\:\/\//.test(url)) {
        url = "http://" + url;
    }
    return url;
}

function map() {
    var place = $("INPUT[name='place']").val();
    window.open('https://www.google.com/maps/search/?api=1&query=' + encodeURI(place), "_blank");
}

function link() {
    var link = $("INPUT[name='link']").val();
    if (link != "")
        window.open(prependHttp(link), "_blank");
}
