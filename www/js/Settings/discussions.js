$(document).ready(function () {
    $("[data-binder-id]").each(function () {
        $(this).data("data-binder", new Binder({
            area: this,
            checkboxValueChecked: 1,
            checkboxValueUnChecked: 0,
            isValid: function (name, value1, value2) {
                switch (name) {
                    case "caption":
                        return value1.trim() != "";
                    case "order":
                        return !isNaN(value1);
                }
                return true;
            }
        }));
    });
});