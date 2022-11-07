function duplicateLastOptionRow() {
    var templateRow = $("DIV.settings DIV.option-row-template");
    var idSelector = "INPUT[type=hidden]";
    var captionSelector = "INPUT[type=text]";
    var valueSelector = "SELECT";
    var newRowId = Math.random().toString(36).slice(2); //some random hash
    var newRow = templateRow.clone();
    newRow.removeClass("option-row-template");
    newRow.removeClass("d-none");
    newRow.addClass("option-row");
    newRow.attr("data-row-id", newRowId);
    newRow.find(idSelector).attr({
        name: "option_id_" + newRowId,
        value: newRowId,
        'data-lfv-message-id': "frm-pollForm-option_type_" + newRowId + "_message",
    });
    newRow.find(captionSelector).attr({
        name: "option_caption_" + newRowId,
        id: "from-pollForm-option_caption_" + newRowId,
        'data-lfv-message-id': "frm-pollForm-option_caption_" + newRowId + "_message",
    }).val("").next("span").attr("id", "frm-pollForm-option_caption_" + newRowId + "_message");
    newRow.find(valueSelector).attr({
        name: "option_type_" + newRowId,
        id: "from-pollForm-option_type_" + newRowId,
        'data-lfv-message-id': "frm-pollForm-option_type_" + newRowId + "_message",
    }).next("span").attr("id", "frm-pollForm-option_type_" + newRowId + "_message");

    lastOptionRow = $("DIV.settings DIV.option-row:last");
    newRow.insertAfter(lastOptionRow.length ? lastOptionRow : $("DIV.settings DIV.option-row-template"));
}

function removeOptionRow(elm) {
    var optionRow = $(elm).closest("DIV.option-row");
    optionRow.find("INPUT[type=hidden]").attr("value", "null");
    optionRow.addClass("d-none");
}