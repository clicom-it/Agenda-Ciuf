function goup() {
    $('body,html').animate({
        scrollTop: 0
    }, 1000);
    return false;
}

/* parametri da url tipo php */
function getUrlParameter(sParam)
{
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++)
    {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam)
        {
            return sParameterName[1];
        }
    }
}

/* toglie blank line da stringa */
function removeline(str) {
    var stringa = str.replace(/^\s*$[\n\r]{1,}/gm, '');    
    return stringa;
}

$(document).ready(function () {

    /* jquery ui tooltip per form */
    $(document).tooltip({
        position: {
            my: "left bottom",
            at: "left top",
            using: function (position, feedback) {
                $(this).css(position);
                $("<div>")
                        .addClass("arrow")
                        .addClass(feedback.vertical)
                        .addClass(feedback.horizontal)
                        .appendTo(this);
            }
        }
    });
// form controllo login
    $.validator.messages.required = '';
    $("#formlogin").validate({
        submitHandler: function () {
            $("#login").ready(function () {
                var username = $("#username").val();
                var password = CryptoJS.MD5($("#password").val());
                $.ajax({
                    type: "POST",
                    url: "./index.php",
                    data: "username=" + username + "&password=" + password + "&submit=login",
                    dataType: "json",
                    success: function (msg)
                    {
                        if (msg.msg === "1") {
                            location.href = "./mrgest.php";
                        } else if (msg.msg === "0") {
                            $("#errorelogin").fadeIn('fast').html(msg.print).delay(2000).fadeOut('slow');
                            $("#formlogin").trigger('reset');
                        }
                    }
                });
            });
        }
    });
// form recupera password
    $("#formrecupera").validate({
        messages: {
            email: ""
        },
        submitHandler: function () {
            $("#recupera").ready(function () {
                var email = $("#email").val();
                $.ajax({
                    type: "POST",
                    url: "./index.php",
                    data: "email=" + email + "&submit=recupera",
                    dataType: "json",
                    success: function (msg)
                    {
                        if (msg.msg === "1") {
                            $("#noerrorerecupera").fadeIn('fast').html(msg.print).delay(2000).fadeOut('slow');
                            $("#formrecupera").trigger('reset');
                        } else if (msg.msg === "0") {
                            $("#errorerecupera").fadeIn('fast').html(msg.print).delay(2000).fadeOut('slow');
                            $("#formrecupera").trigger('reset');
                        }
                    }
                });
            });
        }
    });
    // form cambia dati login
    $("#formcambialogin").validate({
        submitHandler: function () {
            $("#cambialogin").ready(function () {
                var id = $("#cambiaid").val();
                var user = $("#cambiauser").val();
                var pass = $("#cambiapassword").val();
                if (pass) {
                    var password = CryptoJS.MD5(pass);
                } else {
                    password = "";
                }
                $.ajax({
                    type: "POST",
                    url: "./mrgest.php",
                    data: "id=" + id + "&user=" + user + "&pass=" + password + "&submit=cambialogin",
                    dataType: "json",
                    success: function (msg)
                    {
                        $("#noerrorecambialogin").fadeIn('fast').html(msg.msg).delay(1000).fadeOut('slow');
                        $('#boxcambialogin').delay(1500).fadeOut('slow');
                    }
                });
            });
        }
    });
});

function mostradiv(div) {
    $('#' + div).toggle();
}

// crea filtro per jsgrid

function createTextFilterTemplate(propertyName, initialFilter) {
    return function () {
        /*DEFAULT*/
        if (!this.filtering)
            return "";

        var grid = this._grid,
                $result = this.filterControl = this._createTextBox();

        if (this.autosearch) {
            $result.on("keypress",
                    function (e) {
                        if (e.which === 13) {
                            grid.search();
                            e.preventDefault();
                        }
                    });
        }
        /* END DEFAULT*/
        var gridFilter = initialFilter;
        if (gridFilter && gridFilter[propertyName]) {
            $result.val(gridFilter[propertyName]);
        }
        return $result;
    }
}

function createSelectFilterTemplate(propertyName, initialFilter) {
    return function () {
        /*DEFAULT*/
        if (!this.filtering)
            return "";

        var grid = this._grid,
                $result = this.filterControl = this._createSelect();

        if (this.autosearch) {
            $result.on("keypress",
                    function (e) {
                        if (e.which === 13) {
                            grid.search();
                            e.preventDefault();
                        }
                    });
        }
        /* END DEFAULT*/
        var gridFilter = initialFilter;
        if (gridFilter && gridFilter[propertyName]) {
            $result.val(gridFilter[propertyName]);
        }
        return $result;
    }
}

function pad(num, size) {
    num = num.toString();
    while (num.length < size) num = "0" + num;
    return num;
}

function getOreSettimana(idatelier, settimana_dal, settimana_al) {
    $.ajax({
        type: "POST",
        url: "./dipendenti.php",
        data: "idatelier=" + idatelier + "&settimana_dal=" + settimana_dal + "&settimana_al=" + settimana_al + "&submit=getOreSettimana",
        dataType: "json",
        success: function (result) {
            //console.log(result);
            for (var i = 0; i < result.dipendenti.length; i++) {
                $('#ore-'+result.dipendenti[i].id).html(result.dipendenti[i].somma_ore);
                if(parseFloat(result.dipendenti[i].somma_ore) != parseFloat(result.dipendenti[i].ore_settimana)) {
                    $('#ore-'+result.dipendenti[i].id).css('color', '#ff0000');
                } else {
                    $('#ore-'+result.dipendenti[i].id).css('color', 'green');
                }
            }
        }
    });
}

function inArray(needle, haystack) {
    var length = haystack.length;
    for(var i = 0; i < length; i++) {
        if(haystack[i] == needle) return true;
    }
    return false;
}