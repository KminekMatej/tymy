$(document).ready(function () {
    $("[data-binder-id]").each(function () {
        $(this).data("data-binder", new Binder({
            area: this,
            checkboxValueChecked: 1,
            checkboxValueUnChecked: 0,
            isValid: function (name, value1, value2) {
                switch (name) {
                    case "email":
                        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                        return re.test(value1);
                    case "phone":
                        if (value1 == "")
                            return true;
                        var re = /^[+]?[()/0-9. -]{9,}$/;
                        return re.test(value1);
                    case "login":
                        var re = /^[\w-]{3,20}$/;
                        return re.test(value1);
                    case "password":
                        var re = /^[^\s]{3,}$/;
                        if (re.test(value1))
                            return value1 == value2;
                        else
                            return false;
                }
                return true;
            }
        }));
    });
    $(function () {
        $('#fileupload').fileupload({
            dataType: 'json',
            disableImageResize: /Android(?!.*Chrome)|Opera/
                    .test(window.navigator && navigator.userAgent),
            imageMaxWidth: 40,
            imageMaxHeight: 50,
            imageCrop: true, // Force cropped images
            imageType: 'image/png',
            formData: {
                id: $("[data-binder-id]").attr("data-binder-id")
            },
            always: function () {
                var imgsrc = $("IMG.user_pic").attr("src") + "?random="+new Date().getTime();
                $("IMG.user_pic").attr("src", imgsrc);
                $('DIV.progress-bar').removeClass("progress-bar-striped");
                $('DIV.progress-bar').removeClass("progress-bar-animated");
                $('DIV.progress-bar').addClass("bg-success");
            },
            error: function(xhr, ajaxOptions, thrownError){
                if(xhr.status == 400){
                    alert(translate.team.errors.imgFileCorrupted);
                }
                
            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('DIV.progress-bar').css(
                        'width',
                        progress + '%'
                        );
            }
        });
    });
});

function checkRole(btn){
    if($(btn).hasClass("active"))
        $(btn).removeClass("active");
    else
        $(btn).addClass("active");
}