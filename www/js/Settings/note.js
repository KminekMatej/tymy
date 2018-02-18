$(document).ready(function () {
    $("[data-binder-id]").each(function () {
        $(this).data("data-binder", new Binder({
            area: this,
            isValid: function (name, value1, value2) {
                switch (name) {
                    case "email":
                        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                        return re.test(value1);
                    case "phone":
                        if (value1 == "")
                            return true;
                        var re = /^[+]?[()/0-9. -]{9,}$/;
                        return re.test(value1);
                    case "login":
                        var re = /^[\w-]{3,20}$/;
                        return re.test(value1);
                    case "password":
                        var re = /^[^\s]{3,}$/;
                        if (re.test(value1))
                            return value1 == value2;
                        else
                            return false;
                }
                return true;
            }
        }));
    });
});
