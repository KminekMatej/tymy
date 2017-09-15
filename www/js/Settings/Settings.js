function updateRow(purl, selector) {
    var btn = $(selector).find("BUTTON.update");
    if ($(btn).prop("disabled") || $(btn).hasClass("disabled"))
        return;
    var changes = getChangedInputs(selector);
    $.extend(changes, getChangedSelects(selector));
    $.extend(changes, getChangedTextareas(selector));

    if (!($.isEmptyObject(changes) > 0)) {
        btnDisable(btn, true);
        $.nette.ajax({
            url: purl,
            method: 'POST',
            data: changes,
            complete: function (payload) {
                btnDisable(btn, false);
                btnClass(btn, false);
                commitChanges(selector);
            }
        });
    }
}

function updateRows(purl, selector) {
    var allChanges = {};
    var pseudoId = 0;
    $(selector).find("TR[data-id]").each(function () {
        pseudoId++;
        var area = $(this);
        var changes = getChangedInputs(area);
        $.extend(changes, getChangedSelects(area));
        $.extend(changes, getChangedTextareas(area));
        if (!($.isEmptyObject(changes) > 0)) {
            var id = parseInt($(this).attr("data-id")) || pseudoId;
            changes.id = id;
            allChanges[id] = changes;
        }
    });
    var btns = $(selector).find("BUTTON.update");
    if (!($.isEmptyObject(allChanges) > 0)) {
        
        btns.each(function () {
            btnDisable($(this), true);
        });
        
        $.nette.ajax({
            url: purl,
            method: 'POST',
            data: allChanges,
            complete: function (payload) {
                btns.each(function () {
                    btnDisable($(this), false);
                    btnClass($(this), false);
                });
                btnDisable($("BUTTON.update.all"), false);
                btnClass($("BUTTON.update.all"), false);
                commitChanges(selector);
            }
        });
    } else {
        btns.each(function () {
            btnDisable($(this), false);
            btnClass($(this), false);
        });
        btnDisable($("BUTTON.update.all"), false);
        btnClass($("BUTTON.update.all"), false);
    }
}

function getChangedInputs(area) {
    var values = {};
    $(area).find("INPUT[data-value]").each(function () {
        name = $(this).attr("name");
        value = $(this).is(':checkbox') ? $(this).is(":checked") : $(this).val();
        
        if (name == "link")
            value = prependHttp(value);
        validate(this, name, value);
        if ($(this).attr("data-value") != $(this).val()) {
            if (name == "startTime" || name == "closeTime" || name == "endTime") {
                value = moment(value, "DD.MM.YYYY HH:mm").toISOString();
            }
            values[name] = value;
        }
    });
    return values;
}

function getChangedTextareas(area) {
    var values = {};
    $(area).find("TEXTAREA[data-value]").each(function () {
        name = $(this).attr("name");
        value = $(this).val();
        validate(this, name, value);
        if ($(this).attr("data-value") != $(this).val()) {
            values[name] = value;
        }
    });
    return values;
}

function getChangedSelects(area) {
    var values = {};
    $(area).find("SELECT[data-value]").each(function () {
        name = $(this).attr("name");
        value = $(this).val();
        validate(this, name, value);
        if ($(this).attr("data-value") != $(this).val() && $(this).val() != "") {
            values[name] = value;
        }
    });
    return values;
}

function commitChanges(area){
    $(area).find("[data-value]").each(function () {
        $(this).attr("data-value", $(this).val());
    });
}

function del(purl, selector) {
    var btn = $(selector).find("BUTTON.delete");
    var id = $(selector).attr("data-id");
    if ($(btn).prop("disabled") || $(btn).hasClass("disabled") || typeof(id)=="undefined")
        return;
    btnDisable($(btn), true);
    var layout = $("DIV.container.settings").attr("data-layout");
    if (window.confirm("Smazat událost " + id + " ?")) {
        $.nette.ajax({
            url: purl,
            data: {
                layout: layout
            },
            complete: function (payload) {
                if(layout == "list"){
                    $(selector).remove();
                }
            }
        });
    }
    
}

function validate(elm, name, value1, value2 = null) {
    var valid = true;
    switch (name) {
        case "startTime":
        case "endTime":
        case "closeTime":
            var re = /^(0?[1-9]|[12][0-9]|3[01])\.(0?[1-9]|1[012])\.(19|20)\d\d ([01]\d|2[0-3]):([0-5]\d)$/;
            valid = re.test(value1);
            break;
        case "link":
            var re = /^(?:(?:https?|ftp):\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,}))\.?)(?::\d{2,5})?(?:[/?#]\S*)?$/i;
            valid = value1 == "" || re.test(value1);
            break;
        case "caption":
            valid = value1.trim() != "";
            break;
    }
    
    if (!valid) {
        $(elm).addClass("is-invalid");
        throw "Validation error for field " + name;
    } else {
        $(elm).removeClass("is-invalid");
}

}

function prependHttp(url) {
    if(url.trim() == "") return "";
    if (!/^(?:f|ht)tps?\:\/\//.test(url)) {
        url = "http://" + url;
    }
    return url;
}

function map() {
    var place = $("INPUT[name='place']").val();
    window.open('https://www.google.com/maps/search/?api=1&query=' + encodeURI(place), "_blank");
}

function link() {
    var link = $("INPUT[name='link']").val();
    window.open(link, "_blank");
}

function chng(selector) {
    var changed = false;
    $(selector).find("[data-value]").each(function () {
        if ($(this).attr("data-value") != $(this).val())
            changed = true;
    });
    btnClass($(selector).find("BUTTON.update"), changed);
    btnClass($("BUTTON.update.all"), changed);
}

function btnClass(btn, changed) {
    if (btn.length > 0) {
        if (changed) {
            btn.removeClass("btn-primary");
            btn.addClass("btn-outline-primary");
        } else {
            btn.removeClass("btn-outline-primary");
            btn.addClass("btn-primary");
        }
    }
}

function btnDisable(btn, disable){
    if (btn.length > 0) {
        if (disable) {
            btn.prop("disabled", true);
            btn.attr("disabled", "disabled");
        } else {
            btn.prop("disabled", false);
            btn.removeAttr("disabled");
        }
    }
}

function duplicate(timePeriod){
    var table = $("DIV.container.settings TABLE");
    var lastRow = table.find("TR:last");
    
    var type = lastRow.find("SELECT[name=type]").val();
    var startTime = moment(lastRow.find("INPUT[name=startTime]").val(), "DD.MM.YYYY HH:mm");
    var endTime = moment(lastRow.find("INPUT[name=endTime]").val(), "DD.MM.YYYY HH:mm");
    var closeTime = moment(lastRow.find("INPUT[name=closeTime]").val(), "DD.MM.YYYY HH:mm");
    
    switch (timePeriod) {
        case 'day':
            startTime.add(1, "days");
            endTime.add(1, "days");
            closeTime.add(1, "days");
            break;
        case 'week':
            startTime.add(7, "days");
            endTime.add(7, "days");
            closeTime.add(7, "days");
            break;
        case 'month':
            startTime.add(1, "months");
            endTime.add(1, "months");
            closeTime.add(1, "months");
            break;
    }
    var newRow = lastRow.clone();
    newRow.find("SELECT[name=type]").val(type);
    newRow.find("INPUT[name=startTime]").val(startTime.format("DD.MM.YYYY HH:mm"));
    newRow.find("INPUT[name=endTime]").val(endTime.format("DD.MM.YYYY HH:mm"));
    newRow.find("INPUT[name=closeTime]").val(closeTime.format("DD.MM.YYYY HH:mm"));
    table.append(newRow);
}

function removeRow(elm){
    var row = $(elm).closest("TR");
    if(!row.is(':nth-child(2)') || row.closest("TABLE").find("TR").length > 2)
        row.remove();
}