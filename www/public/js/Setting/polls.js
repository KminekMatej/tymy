$(document).ready(function () {
    $("[data-binder-id]").each(function () {
        $(this).data("data-binder", new Binder({
            area: this,
            checkboxValueChecked: 1,
            checkboxValueUnChecked: 0,
            deleteConfirmation: translate.common.alerts.confirmDelete,
            isValid: function (name, value1, value2) {
                switch (name) {
                    case "caption":
                        return value1.trim() != "";
                    case "maxItems":
                        return (value1.trim() == "" && value2.trim() == "") || value1 >= value2; //value1 = maxItem, value2 = minItem
                    case "minItems":
                        return (value1.trim() == "" && value2.trim() == "") || value1 <= value2; //value1 = minItem, value2 = maxItem
                }
                return true;
            }
        }));
    });
});