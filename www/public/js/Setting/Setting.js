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

function duplicateLastRow() {
    var table = $("DIV.settings TABLE");
    var lastRow = table.find("TR:last");
    var newRow = lastRow.clone();
    newRow.find("SELECT[name='viewRightName']").val(lastRow.find("SELECT[name='viewRightName']").val());
    newRow.find("SELECT[name='planRightName']").val(lastRow.find("SELECT[name='planRightName']").val());
    newRow.find("SELECT[name='resultRightName']").val(lastRow.find("SELECT[name='resultRightName']").val());
    table.append(newRow);
}

function duplicateEventRow(timePeriod) {
    var table = $("DIV.settings TABLE");
    var formId = $("DIV.settings FORM").attr("id");

    var lastRow = table.find("TR:last");

    var rowIndex = table.find("TR").length - 1;

    var type = lastRow.find("SELECT[name=type]").val();
    var startTime = moment(lastRow.find("INPUT[name=startTime]").val());
    var endTime = moment(lastRow.find("INPUT[name=endTime]").val());
    var closeTime = moment(lastRow.find("INPUT[name=closeTime]").val());

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
    lastRow.find("INPUT[name=startTime]").val(startTime.format("YYYY-MM-DDTHH:mm"));
    lastRow.find("INPUT[name=endTime]").val(endTime.format("YYYY-MM-DDTHH:mm"));
    lastRow.find("INPUT[name=closeTime]").val(closeTime.format("YYYY-MM-DDTHH:mm"));

    lastRow.find("INPUT, TEXTAREA, SELECT").each(function () {
        var name = $(this).attr("name");
        $(this).attr("id", formId + '-' + name + '-' + rowIndex);
    });
}

function removeRow(elm) {
    var row = $(elm).closest("TR");
    if (!row.is(':nth-child(2)') || row.closest("TABLE").find("TR").length > 2) {
        row.data("data-binder").destroy();
        row.remove();
    }
}
