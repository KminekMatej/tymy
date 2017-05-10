function copyPost(elm){
    var text = $("#"+elm).html();
    clipboard.copy({"text/html":text});
}

function loadPost(elm) {
    var text = $("#" + elm).html();
    CKEDITOR.instances.addPost.setData(text);
    smoothScroll('addPost');
}