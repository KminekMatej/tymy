LiveForm.setOptions({
    controlErrorClass: 'is-invalid',
    controlValidClass: 'is-valid',
    wait: 300,
    onValidate: function (form) {
        var completeErrCount = 0;
        var jqForm = $(form);
        jqForm.find("DIV.tab-pane").each(function () {
            var errorCount = $(this).find(".is-invalid").length;
            var heading = $("A[data-toggle='tab'][href='#" + $(this).attr("id") + "']");
            var badge = heading.find("SPAN.badge-pill");
            badge.html(errorCount);
            if (errorCount) {
                badge.removeClass("d-none");
            } else {
                badge.addClass("d-none");
            }
            completeErrCount += errorCount;
        });

        var saveBtn = jqForm.find("BUTTON[type='submit']");
        if (completeErrCount) {
            saveBtn.removeClass("btn-primary");
            saveBtn.addClass("btn-outline-secondary");
            saveBtn.addClass("disabled");
            saveBtn.attr("disabled", 1);
            saveBtn.attr("title", formError);
        } else {
            saveBtn.removeClass("btn-outline-secondary");
            saveBtn.removeClass("disabled");
            saveBtn.addClass("btn-primary");
            saveBtn.removeAttr("disabled");
            saveBtn.removeAttr("title");
        }
    }
});