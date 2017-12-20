
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
    var text = $("#post-" + postId).html();
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

function deletePost(id, url) {
    postElm = $("DIV#row-" + id)
    postElm.hide('slow', function () {
        postElm.remove();
    });
    $.nette.ajax({
        type: 'POST',
        url: url,
        data: {
            'postId': id,
        },
    }).done(function () {
        
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