$(function () {
    $("#frm-teamConfigForm-requiredFields").bsMultiSelect();
    $("INPUT[data-toggle='colorpicker']").each(function () {
        var bgColor = $(this).attr("data-color");
        var rgb = hexToRgb($(this).attr("data-color"));
        $(this).css("backgroundColor", bgColor);
        $(this).css("color", txtClr(rgb));
        $(this).ColorPicker({
            color: bgColor,
            onSubmit: function (hsb, hex, rgb, el) {
                $(el).val('#' + hex);
                $(el).ColorPickerHide();
            },
            onChange: function (hsb, hex, rgb) {
                var el = $(this).data('colorpicker').el;
                $(el).val('#' + hex);
                $(el).css('backgroundColor', '#' + hex);
                $(el).css('color', txtClr(rgb));
            }
        }

        );
    });

    IconPicker.Init({
        // Required: You have to set the path of IconPicker JSON file to "jsonUrl" option. e.g. '/content/plugins/IconPicker/dist/iconpicker-1.5.0.json'
        jsonUrl: '/public/resources/fa-iconpicker/iconpicker-1.5.0.json', // Optional: Change the buttons or search placeholder text according to the language.
        searchPlaceholder: 'Search Icon',
        showAllButton: 'Show All',
        cancelButton: 'Cancel',
        noResultsFound: 'No results found.', // v1.5.0 and the next version
        sborderRadius: '20px', // v1.5.0 and the next versions
    });
    IconPicker.Run('.iconpicker');
});

function hexToRgb(hex) {
    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
}

function txtClr(rgb) {
    return ((rgb.r * 0.299 + rgb.g * 0.587 + rgb.b * 0.114) > 186) ? "#000000" : "#ffffff";
}

function moveUp(elm, selector = 'TR'){
    var thisRow = $(elm).closest(selector);
    var prevRow = thisRow.prevAll(selector + ":first");
    thisRow.after(prevRow);
    recountOrder(thisRow.parent(), selector);
    
}

function moveDown(elm, selector = 'TR'){
    var thisRow = $(elm).closest(selector);
    var nextRow = thisRow.nextAll(selector + ":first");
    thisRow.before(nextRow);
    recountOrder(thisRow.parent(), selector);
}

function recountOrder(parent, selector = 'TR') {
    var first = true;
    var order = 0;
    parent.children(selector).each(function () {
        if (first) {
            $(this).find(".fa-arrow-up:first").hide();
        } else {
            $(this).find(".fa-arrow-up:first").show();
        }
        if($(this).next().length == 0){
            $(this).find(".fa-arrow-down:first").hide();
        } else {
            $(this).find(".fa-arrow-down:first").show();
        }
        $(this).find("INPUT.order").val(order);
        order++;
        first = false;
    });
}