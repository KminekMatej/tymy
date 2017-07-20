
function addPost(url) {
    $("DIV.addPost BUTTON").prop("disabled", true);
    $.nette.ajax({
        type: 'POST',
        url: url,
        data: {'post': CKEDITOR.instances.addPost.getData()},
    }).done(function () {
        $("DIV.addPost BUTTON").prop("disabled", false);
        CKEDITOR.instances.addPost.setData('');
    });
}

function copyPost(elm) {
    var text = $("#" + elm).html();
    clipboard.copy({"text/html": text});
}

function loadPost(postId) {
    var text = $("#content_" + postId).html();
    $("DIV.addPost").attr("data-postId",postId);
    CKEDITOR.instances.addPost.setData(text);
    smoothScroll('addPost');
}

function updatePost(url) {
    $("DIV.addPost BUTTON").prop("disabled", true);
    $.nette.ajax({
        type: 'POST',
        url: url,
        data: {
            'postId': $("DIV.addPost").attr("data-postId"),
            'post': CKEDITOR.instances.addPost.getData(),
        },
    }).done(function () {
        $("DIV.addPost BUTTON").prop("disabled", false);
        $("DIV.addPost").attr("data-postId","");
        CKEDITOR.instances.addPost.setData('');
    });
}

function stickPost(url, postId, sticky) {
    $("DIV.addPost BUTTON").prop("disabled", true);
    $.nette.ajax({
        type: 'POST',
        url: url,
        data: {
            'postId': postId,
            'sticky': sticky,
        },
    }).done(function () {
        $("DIV.addPost BUTTON").prop("disabled", false);
        $("DIV.addPost").attr("data-postId","");
        smoothScroll('addPost');
    });
}