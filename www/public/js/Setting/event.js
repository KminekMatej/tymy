function pickPeriod(period) {
    $("#periodPick").html(translate.common[period]);
    $("#periodPick").attr("data-selected", period);
}

function pickEnd(end) {
    $("#endPick").html(translate.common[end]);
    $("#endPick").attr("data-selected", end);

    if (end == 'after') {
        $("#repeatEndDate").addClass("d-none");
        $("#repeatEndNumber").removeClass("d-none");
        $("#repeatEndNumberLabel").removeClass("d-none");
    } else if (end == 'onDay') {
        $("#repeatEndDate").removeClass("d-none");
        $("#repeatEndNumber").addClass("d-none");
        $("#repeatEndNumberLabel").addClass("d-none");
    }
}

function repeatEvent() {
    var repeatPeriod = parseInt($("#repeatPeriod").val());
    var periodPick = $("#periodPick").attr("data-selected"); //day/week/month/year
    var endPick = $("#endPick").attr("data-selected"); //after/onDay


    var iterator = 0;
    var lastDT = moment($("DIV.settings TABLE").find("TR:last").find("INPUT[name='startTime']").val());

    var lastIteration = parseInt($("#repeatEndNumber").val());
    var latestDate = moment($("#repeatEndDate").val());
    var nextDate = lastDT.clone().add(repeatPeriod, periodPick);

    while (endPick == "onDay" ? nextDate < latestDate : iterator < lastIteration && iterator < 50) { //always do maximally 50 iterations
        iterator++;
        lastDT = duplicateEventRow(repeatPeriod, periodPick); //moment date
        nextDate = lastDT.add(repeatPeriod, periodPick);
    }
}

function removeRow(elm) {
    var row = $(elm).closest("TR");
    if (!row.is(':nth-child(2)') || row.closest("TABLE").find("TR").length > 2) { //keep if its the only row
        row.remove();
    }
}

function duplicateLastRow() {
    var table = $("DIV.settings TABLE");
    var lastRow = table.find("TR:last");
    var newRow = lastRow.clone();
    newRow.find("SELECT[data-name='eventTypeId']").val(lastRow.find("SELECT[data-name='eventTypeId']").val());
    newRow.find("SELECT[data-name='viewRightName']").val(lastRow.find("SELECT[data-name='viewRightName']").val());
    newRow.find("SELECT[data-name='planRightName']").val(lastRow.find("SELECT[data-name='planRightName']").val());
    newRow.find("SELECT[data-name='resultRightName']").val(lastRow.find("SELECT[data-name='resultRightName']").val());
    table.append(newRow);
}

function duplicateEventRow(count, period) {
    var table = $("DIV.settings TABLE");
    var formId = $("DIV.settings FORM").attr("id");

    var lastRow = table.find("TR:last");

    var rowIndex = table.find("TR").length - 1;

    var type = lastRow.find("SELECT[name=type]").val();
    var startTime = moment(lastRow.find("INPUT[data-name=startTime]").val());
    var endTime = moment(lastRow.find("INPUT[data-name=endTime]").val());
    var closeTime = moment(lastRow.find("INPUT[data-name=closeTime]").val());

    duplicateLastRow();

    startTime.add(count, period);
    endTime.add(count, period);
    closeTime.add(count, period);

    lastRow = table.find("TR:last");
    lastRow.find("SELECT[data-name=type]").val(type);
    lastRow.find("INPUT[data-name=startTime]").val(startTime.format("YYYY-MM-DDTHH:mm"));
    lastRow.find("INPUT[data-name=endTime]").val(endTime.format("YYYY-MM-DDTHH:mm"));
    lastRow.find("INPUT[data-name=closeTime]").val(closeTime.format("YYYY-MM-DDTHH:mm"));

    lastRow.find("INPUT, TEXTAREA, SELECT").each(function () {
        var name = $(this).attr("name").split("-")[0];
        $(this).attr("id", formId + '-' + name + '-' + rowIndex);
        $(this).attr("name", name + '-' + rowIndex);
    });
    
    return startTime;
}