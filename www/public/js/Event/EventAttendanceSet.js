function genderCame(elm) {
    var area = $(elm).closest("DIV.col-2").next("DIV.col-10");
    clearCheck(area);
    area.find("FIGURE BUTTON.btn-outline-success").each(function () {
        attendanceToggler(true, this);
    });
}

function genderNCame(elm) {
    var area = $(elm).closest("DIV.col-2").next("DIV.col-10");
    clearCheck(area);
    area.find("FIGURE BUTTON.btn-outline-danger").each(function () {
        attendanceToggler(false, this);
    });
}

function clearCheck(area) {
    $(area).find("BUTTON").removeClass("active");
}

function togglePostStatus(elm) {
    var area = $(elm).closest("DIV.result");
    clearCheck(area);
    $(elm).addClass("active");
}

function saveAttendanceResults(btn, purl) {
    if ($(btn).prop("disabled") || $(btn).hasClass("disabled")) {
        return;
    }

    var resultSet = [];
    $("FIGURE.player").each(function () {
        var playerId = $(this).attr("id");
        var postStatusId = $(this).find("DIV.result BUTTON.active").attr("data-status-id");

        if (postStatusId != null) {
            playerData = {};
            playerData.userId = parseInt(playerId.replace("player-", ""));
            playerData.postStatusId = postStatusId;
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

function resultsToggle(show) {
    $(".result").each(function () {
        if (show) {
            $(this).removeClass("d-none");
        } else
            $(this).addClass("d-none");
    });

}