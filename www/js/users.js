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
            values[name] = value;
        }
    });
    
    //check for updated SELECT elements
    activeTab.find("SELECT[data-value]").each(function (){
        if($(this).attr("data-value") != $(this).val() && $(this).val() != ""){
            name = $(this).attr("name");
            value = $(this).val();
            values[name] = value;
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