function updatePoll(btn, purl) {
    if ($(btn).prop("disabled") || $(btn).hasClass("disabled"))
        return;
    var minItems = $("DIV.poll").attr("data-min-items");
    var maxItems = $("DIV.poll").attr("data-max-items");
    var value = "";
    var type = "";
    var values = {};
    var voteCount = 0;
    $("DIV.poll DIV.option").each(function (){
        var optId = parseInt($(this).attr("id"));
        if($(this).find("INPUT").attr("type") == "text"){
            //option is text
            value = $(this).find("INPUT").val();
            if(value != ""){
                values[optId] = {type: "stringValue", value: value};
                voteCount++;
            }
        }else if($(this).find("INPUT").attr("type") == "number") {
            //option is number
            value = $(this).find("INPUT").val();
            if(value != ""){
                values[optId] = {type: "numericValue", value: parseInt(value)};
                voteCount++;
            }
        }else if($(this).find("DIV.btn-group")) {
            //option is boolean
            var btnChecked = $(this).find("DIV.btn-group BUTTON.active");
            if(btnChecked.length){
                values[optId] = {type: "booleanValue", value: btnChecked.attr("data-value")};
                voteCount++;
            } 
        }
    });
    
    if(voteCount > maxItems){
        $("DIV.poll DIV.card-block").prepend("<div class='alert alert-danger' role='alert'><strong>Chyba!</strong> Překročen maximální počet hlasů ("+maxItems+")!</div>");
        return;
    }
    if(voteCount < minItems){
        $("DIV.poll DIV.card-block").prepend("<div class='alert alert-danger' role='alert'><strong>Chyba!</strong> Nedosažen minimální počet hlasů ("+minItems+")!</div>");
        return;
    }
    
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
            stats();
        }
    });
}

function checkBool(btn){
    var radioLayout = $("DIV.poll").attr("data-radio-layout") === "true";
    if(radioLayout){
        $("DIV.poll DIV.option BUTTON").each(function (){
            $(this).removeClass("active");
        });
    } else {
        $(btn).closest("DIV.btn-group").find("BUTTON").each(function () {
            $(this).removeClass("active");
        });
    }
    $(btn).addClass("active");
}

function stats(){
    var votesCount = $("DIV#snippet--poll-results TR[data-vote]").length;
    if($("DIV#snippet--poll-results").length == 0 || votesCount == 0)
        return false;
    $("DIV#snippet--poll-results TR#stats").remove();
    var stats = {};
    
    $("DIV#snippet--poll-results TR[data-vote]").each(function (){
        gender = $(this).attr("data-gender");
        status = $(this).attr("data-status");
        $(this).find("TD[data-option-type]").each(function() {
            type = $(this).attr("data-option-type");
            optionId = $(this).attr("data-option-id");
            value = $(this).attr("data-option-value");
            if(typeof(stats["id"+optionId]) == "undefined"){
                stats["id"+optionId] = {};
                stats["id"+optionId].sum = 0;
                stats["id"+optionId].true = 0;
                stats["id"+optionId].false = 0;
                stats["id"+optionId].type = type;
                stats["id"+optionId].optionId = optionId;
                stats["id"+optionId].votes = 0;
            }
            
            switch (type) {
                case "TEXT":
                    if(value.trim() != "")
                        stats["id" + optionId].votes += 1;
                    break;
                case "NUMBER":
                    if (!isNaN(parseInt(value))) {
                        stats["id" + optionId].sum += parseInt(value);
                        stats["id" + optionId].votes += 1;
                    }
                    break;
                case "BOOLEAN":
                    if (value === "true") {
                        stats["id" + optionId].true += 1;
                        stats["id" + optionId].votes += 1;
                    } else if (value === "false") {
                        stats["id" + optionId].false += 1;
                        stats["id" + optionId].votes += 1;
                    }
                    break;
            }
        });
        
    });
    statsHtml = "<tr id='stats'><td>Počet hlasů:"+votesCount+"</td>";

    for (option in stats) {
        if (stats.hasOwnProperty(option)) {
            switch (stats[option].type) {
                case "TEXT":
                    votesSumTxt = "";
                    statsHtml += "<td data-option-id='"+stats[option].optionId+"'>Hlasováno "+stats[option].votes+"x ("+Math.round((stats[option].votes / votesCount)*100) +"%)</td>";
                    break;
                case "NUMBER":
                    statsHtml += "<td data-option-id='"+stats[option].optionId+"'>Hlasováno "+stats[option].votes+"x ("+Math.round((stats[option].votes / votesCount)*100) +"%)<br/>Σ = "+Math.round(stats[option].sum * 100) / 100 +"<br/>ϕ = "+ Math.round((stats[option].sum/votesCount) * 100) / 100 +"</td>";
                    break;
                case "BOOLEAN":
                    statsHtml += "<td data-option-id='"+stats[option].optionId+"'>Hlasováno "+stats[option].votes+"x ("+Math.round((stats[option].votes / votesCount)*100) +"%)<br/>"+stats[option].true +"x ANO ("+Math.round((stats[option].true/votesCount)*100)+"%)<br/>"+stats[option].false +"x NE ("+Math.round((stats[option].false/votesCount)*100)+"%)</td>";
                    break;
            }
        }
    }
    statsHtml += "</tr>";
    
    $("DIV#snippet--poll-results TABLE").append(statsHtml);
}