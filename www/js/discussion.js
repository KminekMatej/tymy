
function addPost(url) {
    $("DIV.addPost BTN.btn-primary").prop("disabled", true);
    $.nette.ajax({
        type: 'POST',
        url: url,
        data: {'post': CKEDITOR.instances.addPost.getData()},
    }).done(function () {
        $("DIV.addPost BTN.btn-primary").prop("disabled", false);
        CKEDITOR.instances.addPost.setData('');
    });
}

function copyPost(elm) {
    var text = $("#" + elm).html();
    clipboard.copy({"text/html": text});
}

function loadPost(elm) {
    var text = $("#" + elm).html();
    CKEDITOR.instances.addPost.setData(text);
    smoothScroll('addPost');
}