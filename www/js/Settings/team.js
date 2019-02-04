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
});

function hexToRgb(hex) {
    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
}

function txtClr(rgb){
    return ((rgb.r*0.299 + rgb.g*0.587 + rgb.b*0.114) > 186) ? "#000000" : "#ffffff";
}