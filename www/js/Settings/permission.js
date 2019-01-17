const btnRegex = /.* btn-outline-(.*) .*/gm;

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
                }
                return true;
            }
        }));
    });
});

function stateCheck(elm){
    if($(elm).hasClass("active")){
        $(elm).removeClass("active");
    } else {
        $(elm).addClass("active");
    }
    if($("[name='statusAllowance']:checked").length == 0) $("[name='statusAllowance'][value='allowed']").prop("checked", true);
    statesChange();
}

function roleCheck(elm){
    if($(elm).hasClass("active")){
        $(elm).removeClass("active");
    } else {
        $(elm).addClass("active");
    }
    if($("[name='roleAllowance']:checked").length == 0) $("[name='roleAllowance'][value='allowed']").prop("checked", true);
    rolesChange();
}

function statesChange(){
    var rule = $("[name='statusAllowance']:checked").val();
    var newClass = "btn-outline-secondary";
    if(rule == "allowed") newClass = "btn-outline-success";
    if(rule == "revoked") newClass = "btn-outline-danger";
    
    $("TD.statuses BUTTON").each(function () {
        $(this).removeClass("btn-outline-secondary");
        $(this).removeClass("btn-outline-success");
        $(this).removeClass("btn-outline-danger");
        $(this).addClass(newClass);
    });
    toggleResults();
}

function rolesChange(){
    var rule = $("[name='roleAllowance']:checked").val();
    var newClass = "btn-outline-secondary";
    if(rule == "allowed") newClass = "btn-outline-success";
    if(rule == "revoked") newClass = "btn-outline-danger";
    
    $("TD.roles BUTTON").each(function () {
        $(this).removeClass("btn-outline-secondary");
        $(this).removeClass("btn-outline-success");
        $(this).removeClass("btn-outline-danger");
        $(this).addClass(newClass);
    });
    toggleResults();
}

function usersChange(){
    if($("[name='userAllowance']:checked").length == 0) $("[name='userAllowance'][value='allowed']").prop("checked", true);
    toggleResults();
}

function toggleResults(){
    $(".row[data-class='allowed'] .user").each(function(){
        var usrAllowedElement = $(this);
        var usrRevokedElement = $(".row[data-class='revoked'] .user[data-id='"+usrAllowedElement.attr('data-id')+"']");
        var allowed = isAllowed( usrAllowedElement.attr("data-roles").split(","), usrAllowedElement.attr("data-status"), usrAllowedElement.attr("data-id"));
        if(allowed){
            if(!usrAllowedElement.is(":visible")){
                usrRevokedElement.hide("300");
                usrAllowedElement.show("300");
            }
        } else {
            if(!usrRevokedElement.is(":visible")){
                usrAllowedElement.hide("300");
                usrRevokedElement.show("300");
            }
        }
    });
}

function isAllowed(roles, status, userId){
    if(revokedByRole(roles) || revokedByStatuses(status) || revokedByUsers(userId)) return false;
    if(allowedByRole(roles) || allowedByStatuses(status) || allowedByUsers(userId)) return true;
    return false;
}

function revokedByRole(roles){
    var rule = $("[name='roleAllowance']:checked").val();
    if(rule != "revoked") return false;
    var result = false;
    $("TD.roles BUTTON.active").each(function () {
        if ($.inArray($(this).attr("data-key"), roles) != -1) {
            result = true;
            return false;
        }
    });
    return result;
}

function allowedByRole(roles){
    var rule = $("[name='roleAllowance']:checked").val();
    if(rule != "allowed") return false;
    var result = false;
    $("TD.roles BUTTON.active").each(function () {
        if ($.inArray($(this).attr("data-key"), roles) != -1) {
            result = true;
            return false;
        }
    });
    return result;
}

function revokedByStatuses(status){
    var rule = $("[name='statusAllowance']:checked").val();
    if(rule != "revoked") return false;
    var result = false;
    $("TD.statuses BUTTON.active").each(function () {
        if ($(this).attr("data-key") == status) {
            result = true;
            return false;
        }
    });
    return result;
}

function allowedByStatuses(status){
    var rule = $("[name='statusAllowance']:checked").val();
    if(rule != "allowed") return false;
    var result = false;
    $("TD.statuses BUTTON.active").each(function () {
        if ($(this).attr("data-key") == status) {
            result = true;
            return false;
        }
    });
    return result;
}

function revokedByUsers(userId){
    var rule = $("[name='userAllowance']:checked").val();
    if(rule != "revoked") return false;
    $("TD.ids INPUT[type='checkbox']:checked").each(function () {
        if ($(this).attr("data-key") == userId) {
            return true;
        }
    });
    return false;
}

function allowedByUsers(userId){
    var rule = $("[name='userAllowance']:checked").val();
    if(rule != "allowed") return false;
    var allowed = false;
    $("TD.ids INPUT[type='checkbox']:checked").each(function () {
        if ($(this).attr("data-key") == userId) {
            allowed = true;
            return false;
        }
    });
    return allowed;
}