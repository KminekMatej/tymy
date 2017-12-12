function prependHttp(url) {
    if(url.trim() == "") return "";
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
    if(link != "")
        window.open(prependHttp(link), "_blank");
}

function duplicateLastRow(){
    var table = $("DIV.container.settings TABLE");
    var lastRow = table.find("TR:last");
    var newRow = lastRow.clone();
    table.append(newRow);
}

function duplicateEventRow(timePeriod){
    var table = $("DIV.container.settings TABLE");
    var lastRow = table.find("TR:last");
    
    var type = lastRow.find("SELECT[name=type]").val();
    var startTime = moment(lastRow.find("INPUT[name=startTime]").val(), "DD.MM.YYYY HH:mm");
    var endTime = moment(lastRow.find("INPUT[name=endTime]").val(), "DD.MM.YYYY HH:mm");
    var closeTime = moment(lastRow.find("INPUT[name=closeTime]").val(), "DD.MM.YYYY HH:mm");
    
    duplicateLastRow();
    
    switch (timePeriod) {
        case 'day':
            startTime.add(1, "days");
            endTime.add(1, "days");
            closeTime.add(1, "days");
            break;
        case 'week':
            startTime.add(7, "days");
            endTime.add(7, "days");
            closeTime.add(7, "days");
            break;
        case 'month':
            startTime.add(1, "months");
            endTime.add(1, "months");
            closeTime.add(1, "months");
            break;
    }
    lastRow = table.find("TR:last");
    lastRow.find("SELECT[name=type]").val(type);
    lastRow.find("INPUT[name=startTime]").val(startTime.format("DD.MM.YYYY HH:mm"));
    lastRow.find("INPUT[name=endTime]").val(endTime.format("DD.MM.YYYY HH:mm"));
    lastRow.find("INPUT[name=closeTime]").val(closeTime.format("DD.MM.YYYY HH:mm"));
}

function removeRow(elm){
    var row = $(elm).closest("TR");
    if(!row.is(':nth-child(2)') || row.closest("TABLE").find("TR").length > 2)
        row.remove();
}