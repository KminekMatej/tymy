
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
        $("DIV.addPost").attr("data-postId","");
        smoothScroll('addPost');
    });
}

function react(postId, reaction, remove) {
    var badges = $("DIV#row-" + postId + " DIV.reaction-bar .badge");
    var badgeExists = false;

    badges.each(function () {
        var emojiEl = $(this).find(".emoji");
        var countEl = $(this).find(".emoji-count");
        if (emojiEl.html() == reaction) {
            badgeExists = true;
            //either increase or decrease badge count
            var currentCount = parseInt(countEl.html());
            if (remove) { //removal my reaction
                currentCount--;
                $(this).removeClass("border-primary");
            } else {
                currentCount++;
                $(this).addClass("border-primary");
            }

            countEl.html(currentCount);

            if (currentCount == 1) { //hide badge count
                countEl.hide();
            } else if (currentCount > 1) {// display badge count
                countEl.show();
            } else if (currentCount == 0) { //hide whole badge
                $(this).remove();
            }
        }
    });

    if (!badgeExists) { //this badge doesnt exists yet - create it
        var likeBtnHref = $("DIV#row-" + postId + " .like-btn").attr("href") + "&remove=1"; //copy likeBtnHref - but that doesnt contain remove parameter yet (since it hasnt been reloaded yet - so forcefully append the remove param into query)

        $("DIV#row-" + postId + " DIV.reaction-bar").append("<a href=\"" + likeBtnHref + "\" \n\
            class=\"badge badge-light ajax my-1 mr-1 border border-primary\" \n\
            onclick=\"react(" + postId + ", '" + reaction + "', 1)\">\n\
            <span class=\"emoji\">" + reaction + "</span>\n\
            <span class=\"emoji-count\" style=\"display: none\">1</span></a>");
    }

    reloadReactButton(postId);

    return true;
}

function reloadReactButton(postId) {
    var badges = $("DIV#row-" + postId + " DIV.reaction-bar .badge");
    var likeBtn = $("DIV#row-" + postId + " .like-btn");
    var reactionExists = false;

    badges.each(function () {
        if ($(this).hasClass("border-primary")) {//this button contains my reaction
            var emojiEl = $(this).find(".emoji");
            likeBtn.html(emojiEl.html()); //add reaction icon
            likeBtn.addClass("border-primary"); //add reaction icon
            likeBtn.attr("href", $(this).attr("href"));
            likeBtn.attr("onclick", $(this).attr("onclick"));//copy the same href and onclick from this button - as it should do exactly the same thing
            reactionExists = true;
            return false; //break jquery .each loop
        }
    });

    if (!reactionExists) { //restore default values if there is no reaction found
        likeBtn.html('👍'); //add reaction icon
        likeBtn.removeClass("border-primary"); //add reaction icon
        likeBtn.attr("href", likeBtn.attr("data-default-href"));
        likeBtn.attr("onclick", likeBtn.attr("data-default-onclick"));//reset default href and onclick parameters
    }
}