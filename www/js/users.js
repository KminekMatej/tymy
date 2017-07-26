function updateUser(btn, purl){
    if ($(btn).prop("disabled") || $(btn).hasClass("disabled"))
        return;
    var activeTab = $("DIV.container.user DIV.tab-pane.active");
    var values = {};
    
    //check for updated INPUT elements
    activeTab.find("INPUT[data-value]").each(function (){
        if($(this).attr("data-value") != $(this).val()){
            name = $(this).attr("name");
            value = $(this).is(':checkbox') ? $(this).is(":checked") : $(this).val();
            
            var valid = false;
            if(name == "password"){
                valid = isValid(name, value, $("INPUT[name='password-check']").val());
            } else valid = isValid(name, value);
            
            if(!valid){
                $(this).parent("TD").addClass("has-danger");
                throw "Validation error for field " + name;
            } else {
                $(this).parent("TD").removeClass("has-danger");
            }
            
            values[name] = value;
        }
    });
    
    //check for updated SELECT elements
    activeTab.find("SELECT[data-value]").each(function (){
        if($(this).attr("data-value") != $(this).val() && $(this).val() != ""){
            name = $(this).attr("name");
            value = $(this).val();
            
            var valid = false;
            if(name == "password"){
                valid = isValid(name, value, $("INPUT[name='password-check']").val());
            } else valid = isValid(name, value);
            
            if(!valid){
                $(this).parent("TD").addClass("has-danger");
                throw "Validation error for field " + name;
            } else {
                $(this).parent("TD").removeClass("has-danger");
            }
            
            values[name] = value;
        }
    });
    
    //check for updated role buttons
    rolesNew = [];
    rolesOld = [];
    activeTab.find("BUTTON[name='role']").each(function (){
        if($(this).hasClass("active"))
            rolesNew.push($(this).attr("data-role"));
        
        if($(this).attr("data-value"))
            rolesOld.push($(this).attr("data-role"));
        
    });
    
    if($(rolesNew).not(rolesOld).length || $(rolesOld).not(rolesNew).length)
        values["roles"] = rolesNew;
    
    if (!($.isEmptyObject(values) > 0)) {
        $(btn).prop("disabled", true);
        $(btn).attr("disabled", "disabled");
        $.nette.ajax({
            url: purl,
            method: 'POST',
            data: values,
            complete: function (payload) {
                $(btn).prop("disabled", true);
                $(btn).attr("disabled", "disabled");
                $(btn).removeAttr("disabled");
            }
        });
    }
}

function isValid(name, value1, value2 = null){
    switch (name) {
        case "email":
            var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(value1);
        case "phone":
            if(value1=="") return true;
            var re = /^[+]?[()/0-9. -]{9,}$/;
            return re.test(value1);
        case "login":
            var re = /^[\w-]{3,20}$/;
            return re.test(value1);
        case "password":
            var re = /^[^\s]{3,}$/;
            if(re.test(value1))
                return value1 == value2;
            else return false;
    }
    return true;
    
}

function checkRole(btn){
    if($(btn).hasClass("active"))
        $(btn).removeClass("active");
    else
        $(btn).addClass("active");
}

function deleteUser(btn, purl){
    if ($(btn).prop("disabled") || $(btn).hasClass("disabled"))
        return;
    $(btn).prop("disabled", true);
    $(btn).attr("disabled", "disabled");
    $.nette.ajax({url: purl});
}