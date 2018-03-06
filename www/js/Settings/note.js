$(document).ready(function () {
    $("[data-binder-id]").each(function () {
        $(this).data("data-binder", new Binder({
            area: this,
            isValid: function (name, value1, value2) {
                switch (name) {
                    case "caption":
                        return value1.trim() != "";
                }
                return true;
            }
        }));
    });
});
