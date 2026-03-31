$(document).ready(function () {
    $(document).ajaxStart(function () {
        showProgress();
        $.ajax({
            type: "POST",
            url: "./controllosessione.php",
            data: "submit=controllo",
            global: false,
            dataType: "json",
            success: function (msg)
            {
                if (msg.msg === "1") {
                } else if (msg.msg === "0") {
                    location.href = "./index.php";
                    return false;
                }
            }
        });
    }).ajaxStop(function () {
        hideProgress();
    });
});
function showProgress() {
    $('.bkg_black_loading').show();
    $('body').append('<div id="progress" style="z-index: 999; color: #ffffff;"><img src="./immagini/loader.gif" style="vertical-align: middle;" /> Loading...</div>');
    $('#progress').center();
}
function hideProgress() {
    $('#progress').remove();
    $('.bkg_black_loading').hide();
}
jQuery.fn.center = function () {
    this.css("position", "absolute");
    this.css("top", ($(window).height() - this.height()) / 2 + $(window).scrollTop() + "px");
    this.css("left", ($(window).width() - this.width()) / 2 + $(window).scrollLeft() + "px");
    return this;
}