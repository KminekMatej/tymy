$(document).ready(function () {
    $('#renameModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget) // Button that triggered the modal
        $(this).find('.modal-body input[name=oldName]').val(button.data('oldname'));
        $(this).find('.modal-body input#frm-renameForm-name').val(button.data('oldname'));
    });
});
