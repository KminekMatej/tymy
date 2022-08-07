function duplicateLastOptionRow() {
    var lastRow = $("DIV.settings DIV.option-row:last");
    var captionSelector = "INPUT";
    var valueSelector = "SELECT";
    var lastCaptionInput = lastRow.find(captionSelector);
    var lastRowId = parseInt(lastCaptionInput.attr("name").replace("option_caption_", ""));
    var newRowId = lastRowId + 1;
    var newRow = lastRow.clone();
    newRow.find(captionSelector).attr({
        name: "option_caption_" + newRowId,
        id: "from-pollForm-option_caption_" + newRowId,
        'data-lfv-message-id': "frm-pollForm-option_caption_" + newRowId + "_message",
    }).next("span").attr("id", "frm-pollForm-option_caption_" + newRowId + "_message");
    newRow.find(valueSelector).attr({
        name: "option_type_" + newRowId,
        id: "from-pollForm-option_type_" + newRowId,
        'data-lfv-message-id': "frm-pollForm-option_type_" + newRowId + "_message",
    }).next("span").attr("id", "frm-pollForm-option_type_" + newRowId + "_message");

    newRow.insertAfter(lastRow);
}