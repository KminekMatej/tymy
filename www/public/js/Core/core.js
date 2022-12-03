$(document).ready(function () {
    var defaultView = (localStorage.getItem("fcDefaultView") != null ? localStorage.getItem("fcDefaultView") : "month");
    var defaultStartDate = (localStorage.getItem("fcDefaultStartDate") != null ? moment(localStorage.getItem("fcDefaultStartDate")) : moment());
    $('#calendar').fullCalendar({
        defaultView: defaultView,
        locale: locale,
        contentHeight: 350,
        fixedWeekCount: false,
        displayEventTime: false,
        events: '/event/feed',
        eventRender: function (event, element) {
            element.find('.fc-title').attr("title", event.description);
            if (event.end < Date.now())
                element.addClass("past");
        },
        header: {
            left: 'title',
            center: '',
            right: 'today prev,next'
        },
        viewRender: function (view) {
            localStorage.setItem("fcDefaultView", view.name);
            localStorage.setItem("fcDefaultStartDate", view.currentRange.start.format());
            localStorage.setItem("fcDefaultEndDate", view.currentRange.end.format());
        },
        loading: function (isLoading, view) {
            if (isLoading) {
                $('#calendar DIV.fc-header-toolbar BUTTON.fc-prev-button').prop('disabled', true);
                $('#calendar DIV.fc-header-toolbar BUTTON.fc-next-button').prop('disabled', true);
            } else {
                $('#calendar DIV.fc-header-toolbar BUTTON.fc-prev-button').prop('disabled', false);
                $('#calendar DIV.fc-header-toolbar BUTTON.fc-next-button').prop('disabled', false);
            }
        }
    });
    $('#calendar').fullCalendar('gotoDate', defaultStartDate);
    $('#calendar DIV.fc-header-toolbar').addClass("card-header");
    $('#calendar DIV.fc-button-group').addClass("btn-group");
    $('#calendar DIV.fc-header-toolbar BUTTON').each(function () {
        $(this).addClass("btn btn-light btn-light-bordered");
    });
});