$(function () {
    togglePopovers();
});

function togglePopovers() {
    $(".emoji-toggler").each(function () {
        var rowId = $(this).closest("DIV.post").attr("id").replace("row-","");
        $(this).popover({
            content: $('#emoji-area-' + rowId).html(),
            html: true,
            placement: 'top',
            container: '#row-' + rowId + " .emoji-toggler",
            trigger: 'click',
            whiteList: {
                // Global attributes allowed on any supplied element below.
                '*': ['class', 'dir', 'id', 'lang', 'role'],
                a: ['target', 'href', 'title', 'rel', 'data-toggle', 'onclick'],
                area: [],
                b: [],
                button: ['onclick'],
                br: [],
                col: [],
                code: [],
                div: ['aria-labelledby'],
                em: [],
                hr: [],
                h1: [],
                h2: [],
                h3: [],
                h4: [],
                h5: [],
                h6: [],
                i: [],
                img: ['src', 'srcset', 'alt', 'title', 'width', 'height'],
                li: [],
                ol: [],
                p: [],
                pre: [],
                s: [],
                svg: ['onclick'],
                small: [],
                span: ['onclick'],
                sub: [],
                sup: [],
                strong: [],
                u: [],
                ul: []
            }
        });
    });
}

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

function loadPost(postId) {
    var text = $("DIV.post#row-" + postId + " DIV.text").html();
    $("DIV.addPost").attr("data-postId", postId);
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
        $("DIV.addPost").attr("data-postId", "");
        CKEDITOR.instances.addPost.setData('');
    });
}

function deletePost(id, url) {
    if (!confirm(translate.discussion.alerts.confirmDeletePost)) {
        return;
    }
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
        $("DIV.addPost").attr("data-postId", "");
        smoothScroll('addPost');
    });
}

function react(el) {
    var reaction = $(el).find(".emoji").html() || $(el).html();
    var post = $(el).closest("DIV.post");

    //detect if this is removal
    var remove = false;
    var reactionBar = post.find("DIV.reaction-bar");
    var myBadges = reactionBar.find(".badge.border-primary");
    var badge = null;

    myBadges.each(function () {
        if ($(this).find(".emoji").html() == reaction) {
            remove = true;
            badge = $(this);
            return false; //break jquery .each loop
        }
    });

    var badges = reactionBar.find(".badge");

    if (!badge) {   //badge has not been found in my badges, try to detect it in all badges
        badges.each(function () {
            if ($(this).find(".emoji").html() == reaction) {
                badge = $(this);
            }
        });
    }

    if (badge) {
        var countEl = badge.find(".emoji-count");

        //either increase or decrease badge count
        var currentCount = parseInt(countEl.html());
        if (remove) { //removal my reaction
            currentCount--;
            badge.removeClass("border-primary");
        } else {
            currentCount++;
            badge.addClass("border-primary");
        }
        countEl.html(currentCount);
        if (currentCount == 1) { //hide badge count
            countEl.hide();
        } else if (currentCount > 1) {// display badge count
            countEl.show();
        } else if (currentCount == 0) { //hide whole badge
            badge.remove();
        }
    } else { //this badge doesnt exists yet - create it
        reactionBar.append("<span \n\
            class=\"badge badge-light my-1 mr-1 border border-primary\" \n\
            onclick=\"react(this)\">\n\
            <span class=\"emoji\">" + reaction + "</span>\n\
            <span class=\"emoji-count\" style=\"display: none\">1</span>\n\
        </span>");
    }

    //store the reaction on server
    $.nette.ajax({
        url: reactionBar.attr('data-reaction-url') + '&reaction=' + encodeURIComponent(reaction) + '&remove=' + (remove ? 1 : 0),
    });
}

function toggleEmojis(el) {
    event.stopPropagation();
    var emojiBody = $(el).closest(".emoji-card").find(".emoji-body");
    if(emojiBody.hasClass("fade")){
        emojiBody.removeClass("d-none");
        emojiBody.removeClass("fade");
    } else {
        emojiBody.addClass("fade");
        emojiBody.addClass("d-none");
    }
}