$(document).ready(function () {
    $("[data-binder-id]").each(function () {
        $(this).data("data-binder", new Binder({
            area: this,
            isValid: function (name, value1, value2) {
                switch (name) {
                    case "caption":
                        return value1.trim() != "";
                    case "minItems":
                        return !isNaN(value1) && value1 <= value2;
                    case "maxItems":
                        return !isNaN(value1) && value1 >= value2;
                    case "order":
                        return !isNaN(value1);

                }
                return true;
            }
        }));
    });
});

function duplicateLastRow(){
    var lastRow = $("DIV.container.settings DIV[data-option]:last");
    var newRow = lastRow.clone();
    newRow.insertAfter(lastRow);
}

function createNewRow(){
    duplicateLastRow();
    var lastRow = $("DIV.container.settings DIV[data-option]:last");
    lastRow.attr("data-binder-id",-1);
    
    lastRow.data("data-binder", new Binder({
            area: lastRow,
        }));
    lastRow.data("data-binder").changeSaveButtonClass(true);
    lastRow.find("[data-value]").attr("data-value","null");
    lastRow.find("[data-value]").attr("data-value","null");
}