function genderCame(elm){
    var area = $(elm).closest("DIV.col-2").next("DIV.col-10");
    clearCheck(area);
    area.find("FIGURE BUTTON.btn-outline-success").each(function(){
        attendanceToggler(true, this);
    });
}

function genderNCame(elm){
    var area = $(elm).closest("DIV.col-2").next("DIV.col-10");
    clearCheck(area);
    area.find("FIGURE BUTTON.btn-outline-danger").each(function(){
        attendanceToggler(false, this);
    });
}

function clearCheck(area){
    $(area).find("BUTTON").each(function(){
        if($(this).hasClass("btn-danger")){
            $(this).removeClass("btn-danger");
            $(this).addClass("btn-outline-danger");
        }
        if($(this).hasClass("btn-success")){
            $(this).removeClass("btn-success");
            $(this).addClass("btn-outline-success");
        }
    });
}

function came(elm){
    attendanceToggler(true, elm);
}

function ncame(elm){
    attendanceToggler(false, elm);
}

function attendanceToggler(arrived, elm){
    var area = $(elm).closest("DIV.btn-group");
    clearCheck(area);
    $(elm).removeClass("btn-outline-success");
    $(elm).removeClass("btn-outline-danger");
    if(arrived){
        $(elm).addClass("btn-success");
    } else {
        $(elm).addClass("btn-danger");
    }
}

function saveAttendanceResults(btn, purl){
 if ($(btn).prop("disabled") || $(btn).hasClass("disabled"))
        return;
       var resultSet = [];
    $("FIGURE.player").each(function(){
        var playerId = $(this).attr("id");
        var yes = $(this).find("DIV.result BUTTON.btn-success").length > 0;
        var no = $(this).find("DIV.result BUTTON.btn-danger").length > 0;
        if(yes){
            playerResult = "YES";
        } else if(no){
            playerResult = "NO";
        } else {
            playerResult = null;
        }
        if(playerResult != null){
            playerData = {};
            playerData.userId = parseInt(playerId.replace("player-",""));
            playerData.postStatus = playerResult;
            resultSet.push(playerData);
        }
    });
    if (resultSet.length > 0) {
        btnRotate(btn, true);
        $.nette.ajax({
            url: purl,
            type: "POST",
            data: {resultSet},
            complete: function (payload) {
                btnRotate(btn, false);
            }
        });
    } else {
        resultsToggle(false);
    }
}

function resultsToggle(show){
    $(".result").each(function(){
        if(show){
            $(this).removeClass("d-none");
        } else 
            $(this).addClass("d-none");
    });
    
}