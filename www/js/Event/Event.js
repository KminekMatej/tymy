function loadAgenda(purl) {
    var date = $("#calendar").fullCalendar('getDate');

    if ($("DIV.agenda[data-month='" + date.format('YYYY-MM') + "']").length == 0) {
        //this month is not loaded yet - call ajax to load new data
        disableCalendar(true);
        //check previous month exists, if does, the direction is one, if not, direction is -1
        var direction = -1;
        var newDate = date.clone().subtract(1, 'months').format('YYYY-MM');
        if ($("DIV.agenda[data-month='" + newDate + "']").length != 0) { // if previous month agenda already exists, then we should load next month instead
            direction = 1;
            newDate = date.clone().add(1, 'months').format('YYYY-MM');
        }
        $.nette.ajax({
            url: purl + "?date=" + newDate + "&direction=" + direction + "&do=eventLoad",
            complete: function (payload) {
                $('#calendar').fullCalendar('removeEvents');
                $('#calendar').fullCalendar('renderEvents', payload.responseJSON.events, true);
                $("#calendar").fullCalendar('rerenderEvents');
                disableCalendar(false);
                loadAgenda();
            }
        });
    }
    $("DIV.agenda").each(function () {
        $(this).css("display", "none");
    });
    $("DIV.agenda[data-month='" + date.format('YYYY-MM') + "']").css("display", "block");
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