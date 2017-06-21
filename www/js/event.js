function loadAgenda(purl) {
    var date = $("#calendar").fullCalendar('getDate');
    var month = ("0" + (date.month() + 1)).slice(-2);
    var year = date.year();

    if ($("DIV.agenda[data-month='" + year + "-" + month + "']").length == 0) {    
        //this month is not loaded yet - call ajax to load new data
        disableCalendar(true);
        if (date.month() == 11) {
            datePlus = (year + 1) + "-01";
            dateMinus = year + "-11";
        } else if (date.month() == 0) {
            datePlus = year + "-02";
            dateMinus = (year - 1) + "-12";
        } else {
            datePlus = year + "-" + ("0" + (date.month() + 2)).slice(-2);
            dateMinus = year + "-" + ("0" + (date.month())).slice(-2);
        }
        if ($("DIV.agenda[data-month='" + datePlus + "']").length == 0 && purl) {
            $.nette.ajax({
                url: purl+"?date=" + datePlus + "&direction=1&do=eventLoad",
                complete: function (payload) {
                    $('#calendar').fullCalendar('removeEvents');
                    $('#calendar').fullCalendar('renderEvents', payload.responseJSON.events, false);
                    $('#calendar').fullCalendar('rerenderEvents');
                    loadAgenda();
                }
            });
        } else if ($("DIV.agenda[data-month='" + dateMinus + "']").length == 0 && purl) {
            $.nette.ajax({
                url: purl+"?date=" + dateMinus + "&direction=-1&do=eventload",
                complete: function (payload) {
                    $('#calendar').fullCalendar('removeEvents');
                    $('#calendar').fullCalendar('renderEvents', payload.responseJSON.events, false);
                    $('#calendar').fullCalendar('rerenderEvents');
                    loadAgenda();
                }
            });
        }
    } else {
        disableCalendar(false);
    }
    $("DIV.agenda").each(function () {
        $(this).css("display", "none");
    });
    $("DIV.agenda[data-month='" + year + "-" + month + "']").css("display", "block");
    $("#calendar").css("visibility", "visible");
}

function disableCalendar(disable) {
    if (disable === true) {
        $('#calendar DIV.fc-header-toolbar BUTTON.fc-prev-button').prop('disabled', true);
        $('#calendar DIV.fc-header-toolbar BUTTON.fc-next-button').prop('disabled', true);
    } else if(disable === false) {
        $('#calendar DIV.fc-header-toolbar BUTTON.fc-prev-button').prop('disabled', false);
        $('#calendar DIV.fc-header-toolbar BUTTON.fc-next-button').prop('disabled', false);
    }
}

function updateAttendance(btn, purl) {
    if ($(btn).prop("disabled") || $(btn).hasClass("disabled"))
        return;
    var id = $(btn).closest("DIV.btn-group").attr("id");
    $("DIV.btn-group#" + id + " BUTTON").removeClass("active");
    $(btn).addClass("active");
    var note = $("DIV.btn-group#" + id + " INPUT").val() ? $("DIV.btn-group#" + id + " INPUT").val() : "";
    disableActionRow(id, true);
    $.nette.ajax({
        url: purl + "&desc=" + note,
        complete: function (payload) {
            disableActionRow(id, false);
        }
    });
}

function disableActionRow(id, disable){
    $("DIV.btn-group#" + id + " BUTTON").prop("disabled", disable);
    if(disable){
        $("DIV.btn-group#" + id + " INPUT").attr("disabled","disabled");
    } else {
        $("DIV.btn-group#" + id + " INPUT").removeAttr("disabled");
    }
}

function togglePast(){
    if($(".evnt-past").is(":visible")){
        $(".evnt-past").hide(300);
        $("DIV.agenda DIV.card-header I.fa").removeClass("fa-low-vision");
        $("DIV.agenda DIV.card-header I.fa").addClass("fa-eye");
    } else {
        $(".evnt-past").show(300);
        $("DIV.agenda DIV.card-header I.fa").removeClass("fa-eye");
        $("DIV.agenda DIV.card-header I.fa").addClass("fa-low-vision");
    }
    
}