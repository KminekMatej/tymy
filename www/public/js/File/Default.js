$(document).ready(function () {
    $('#renameModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget) // Button that triggered the modal
        $(this).find('.modal-body input[name=oldName]').val(button.data('oldname'));
        $(this).find('.modal-body input#frm-renameForm-name').val(button.data('oldname'));
    });

    $('#fileupload').fileupload({
        dataType: 'json',
        error: function (xhr, ajaxOptions, thrownError) {
            if (xhr.status == 400) {
                alert('Chyba při nahrávání souboru');
            }

        }
    });

    $.tablesorter.addParser({
        id: "czechDT",
        is: function (s) {
            return /\d{1,2}\.\d{1,2}\.\d{1,4} \d{1,2}:\d{1,2}:\d{1,2}/.test(s);
        },
        format: function (s) {
            s = s.replace(/\./g, " ");
            s = s.replace(/:/g, " ");
            s = s.replace(/\./g, " ");
            s = s.split(" ");
            return $.tablesorter.formatFloat(new Date(s[2], s[1] - 1, s[0], s[3], s[4], s[5]).getTime());
        },
        type: "numeric"
    });

    $(".tablesorter").tablesorter({
        theme: 'bootstrap_4',
    });
});
