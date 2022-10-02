$(document).ready(function () {
    $('#invitationContentModal').on('show.bs.modal', function (event) {
        var invitation = $(event.relatedTarget).closest(".invitation");
        var lang = invitation.data("lang");

        var modal = $(this);
        modal.find('.validity').html(invitation.data("validity"));
        modal.find('PRE').each(function () {
            $(this).html($(this).html().replaceAll("CODE", invitation.data("code")));
        });
        modal.find('.modal-footer').html(modal.find('.modal-footer').html().replaceAll("LANG", lang));
        modal.find('.creator').html(invitation.find(".creator").data('fullname'));
        modal.find("PRE").hide();
        modal.find("PRE[data-lang='" + lang + "']").show();
    });

    new ClipboardJS('BUTTON.clipboard-btn');
});
