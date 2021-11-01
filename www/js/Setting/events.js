$(document).ready(function () {
    loadBinders();
});


function loadBinders() {
    $("[data-binder-id]").each(function () {
        var activeBinder = $(this).data("data-binder");
        if(typeof activeBinder != "undefined"){
            activeBinder.destroy();
            $(this).removeData("data-binder");
        }
        $(this).data("data-binder", new Binder({
            area: this,
            checkboxValueChecked: 1,
            checkboxValueUnChecked: 0,
            deleteConfirmation: translate.common.alerts.confirmDelete,
            isValid: function (name, value1, value2) {
                switch (name) {
                    case "startTime":
                    case "endTime":
                    case "closeTime":
                        var re = /^(0?[1-9]|[12][0-9]|3[01])\.(0?[1-9]|1[012])\.(19|20)\d\d ([01]\d|2[0-3]):([0-5]\d)$/;
                        return re.test(value1);
                    case "link":
                        var re = /^(?:(?:https?|ftp):\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,}))\.?)(?::\d{2,5})?(?:[/?#]\S*)?$/i;
                        return value1 == "" || re.test(value1);
                    case "caption":
                        return value1.trim() != "";
                }
                return true;
            }
        }));
    });
}