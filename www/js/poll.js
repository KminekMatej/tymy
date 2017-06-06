function updatePoll(btn, purl) {
    if ($(btn).prop("disabled") || $(btn).hasClass("disabled"))
        return;
    var value = "";
    var type = "";
    var values = {};
    $("DIV.poll DIV.option").each(function (){
        var optId = parseInt($(this).attr("id"));
        if($(this).find("INPUT").attr("type") == "text"){
            //option is text
            value = $(this).find("INPUT").val();
            if(value != "") values[optId] = {type: "stringValue", value: value};
        }else if($(this).find("INPUT").attr("type") == "number") {
            //option is number
            value = $(this).find("INPUT").val();
            if(value != "") values[optId] = {type: "numericValue", value: parseInt(value)};
        }else if($(this).find("DIV.btn-group")) {
            //option is boolean
            var btnChecked = $(this).find("DIV.btn-group BUTTON.active");
            if(btnChecked.length) values[optId] = {type: "booleanValue", value: btnChecked.attr("data-value")};
        }
    });
    
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

function checkBool(btn){
    $(btn).closest("DIV.btn-group").find("BUTTON").each(function (){
        $(this).removeClass("active");
    });
    $(btn).addClass("active");
}