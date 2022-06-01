function duplicateLastRow(){
    var lastRow = $("DIV.settings DIV[data-option]:last");
    var newRow = lastRow.clone();
    newRow.insertAfter(lastRow);
}

function createNewRow(){
    duplicateLastRow();
    var lastRow = $("DIV.settings DIV[data-option]:last");
    lastRow.attr("data-binder-id",-1);
    
    lastRow.data("data-binder", new Binder({
            area: lastRow,
        }));
    lastRow.data("data-binder").changeSaveButtonClass(true);
    lastRow.find("[data-value]").attr("data-value","null");
    lastRow.find("[data-value]").attr("data-value","null");
}