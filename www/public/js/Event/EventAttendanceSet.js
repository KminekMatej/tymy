function genderToggle(elm, statusId) {
    var area = $(elm).closest("DIV.col-2").next("DIV.col-10");
    clearCheck(area);
    area.find("FIGURE BUTTON.statusBtn" + statusId).each(function () {
        $(this).addClass("active");
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

    if (show) {
        $(".cancel-btn").removeClass("d-none");
        $(".results-btn").addClass("d-none");
    } else {
        $(".cancel-btn").addClass("d-none");
        $(".results-btn").removeClass("d-none");
    }
}

function planToggle(show) {
    $(".plan-others").each(function () {
        if (show) {
            $(this).removeClass("d-none");
        } else
            $(this).addClass("d-none");
    });

    if (show) {
        $(".cancel-btn").removeClass("d-none");
        $(".plan-btn").addClass("d-none");
    } else {
        $(".cancel-btn").addClass("d-none");
        $(".plan-btn").removeClass("d-none");
    }
}