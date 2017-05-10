function loadAgenda(purl) {
    var date = $("#calendar").fullCalendar('getDate');
    var month = ("0" + (date.month() + 1)).slice(-2);
    var year = date.year();

    if ($("DIV.agenda[data-month='" + year + "-" + month + "']").length == 0) {    
        //this month is not loaded yet - call ajax to load new data
        disableCalendar(true);
        if (date.month() == 12) {
            datePlus = (year + 1) + "-01";
            dateMinus = year + "-11";
        } else if (date.month() == 1) {
            datePlus = year + "-02";
            dateMinus = (year - 1) + "-12";
        } else {
            datePlus = year + "-" + ("0" + (date.month() + 2)).slice(-2);
            dateMinus = year + "-" + ("0" + (date.month())).slice(-2);
        }
        if ($("DIV.agenda[data-month='" + datePlus + "']").length == 0) {
            $.nette.ajax({
                url: purl+"?date=" + datePlus + "&direction=1",
                complete: function (payload) {
                    $('#calendar').fullCalendar('removeEvents');
                    $('#calendar').fullCalendar('renderEvents', payload.responseJSON.events, true);
                    loadAgenda();
                }
            });
        } else if ($("DIV.agenda[data-month='" + dateMinus + "']").length == 0) {
            $.nette.ajax({
                url: purl+"?date=" + dateMinus + "&direction=-1",
                complete: function (payload) {
                    $('#calendar').fullCalendar('removeEvents');
                    $('#calendar').fullCalendar('renderEvents', payload.responseJSON.events, true);
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