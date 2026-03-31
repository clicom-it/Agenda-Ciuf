function mostraSartoria(idp) {
    if (idp === '0') {
        $.ajax({
            type: "POST",
            url: "./impostazioni.php",
            data: "submit=mostrasartoria",
            dataType: "json",
            success: function (msg) {
                $('#backprofit').html("");
                var db = {
                    loadData: function (filter) {
                        return $.grep(this.clients, function (client) {
                            return (!filter.nome || client.nome.indexOf(filter.nome) > -1);
                        });
                    }
                };

                window.db = db;

                db.clients = msg.dati;
                jsGrid.locale("it");
                var campi = [
                    {name: "nome", type: "text", title: "<b>Sartoria</b>", css: "customRow", validate: "required"},
                    {type: "control", itemTemplate: function (value, item) {
                            var $result = jsGrid.fields.control.prototype.itemTemplate.apply(this, arguments);
                            var $myButton = $("<i style=\"margin-left: 5px; color: #E4A326;\"class=\"fa fa-folder-open fa-lg\" aria-hidden=\"true\"></i>")
                                    .click(function (e) {
                                        mostraSartoria(item.id);
                                        e.stopPropagation();
                                    });
                            return $result.add($myButton);
                        }}
                ];

                $("#jsGrid").jsGrid({
                    width: "100%",
                    height: "520px",
                    editing: true,
                    inserting: true,
                    autoload: true,
                    sorting: true,
                    paging: true,
                    pageSize: 12,
                    pageButtonCount: 5,
                    controller: db,
                    deleteConfirm: "Stai per cancellare, sei sicuro?",
                    noDataContent: "Nessun record trovato",
                    fields: campi,
                    // cancella item
                    onItemDeleting: function (args) {
                        var id = args.item.id;
                        $.ajax({
                            type: "POST",
                            url: "./impostazioni.php",
                            data: "id=" + id + "&submit=deletesartoria",
                            dataType: "json",
                            success: function () {
                                mostraSartoria(idp);
                            }
                        });
                    },
                    onItemUpdated: function (args) {
                        var valori = $.param(args.item);
                        $.ajax({
                            type: "POST",
                            url: "./impostazioni.php",
                            data: valori + "&submit=editsartoria",
                            dataType: "json",
                            success: function () {
                                mostraSartoria(idp);
                            }
                        });
                    },
                    onItemInserted: function (args) {
                        var valori = $.param(args.item);
                        $.ajax({
                            type: "POST",
                            url: "./impostazioni.php",
                            data: valori + "&submit=insertsartoria",
                            dataType: "json",
                            success: function () {
                                mostraSartoria(idp);
                            }
                        });
                    }
                });
            }

        });
    } else {
        $.ajax({
            type: "POST",
            url: "./impostazioni.php",
            data: "idp=" + idp + "&submit=mostrasartoria",
            dataType: "json",
            success: function (msg) {

                $('#backprofit').html(" :: " + msg.titprofit + " <a href=\"javascript:;\" onclick=\"mostraSartoria('0')\"><i style=\"margin-left: 20px;\"class=\"fa fa-arrow-circle-o-left fa-lg\" aria-hidden=\"true\"></i> Torna indietro</a>");

                var db = {
                    loadData: function (filter) {
                        return $.grep(this.clients, function (client) {
                            return (!filter.nome || client.nome.indexOf(filter.nome) > -1);
                        });
                    }
                };

                window.db = db;

                db.clients = msg.dati;
                jsGrid.locale("it");
                var campi = [
                    {name: "nome", type: "text", title: "<b>Voci Satoria</b>", css: "customRow", validate: "required"},
                    {type: "control"}
                ];

                $("#jsGrid").jsGrid({
                    width: "100%",
                    height: "520px",
                    editing: true,
                    inserting: true,
                    autoload: true,
                    sorting: true,
                    paging: true,
                    pageSize: 12,
                    pageButtonCount: 5,
                    controller: db,
                    deleteConfirm: "Stai per cancellare, sei sicuro?",
                    noDataContent: "Nessun record trovato",
                    fields: campi,
                    // cancella item
                    onItemDeleting: function (args) {
                        var id = args.item.id;
                        $.ajax({
                            type: "POST",
                            url: "./impostazioni.php",
                            data: "id=" + id + "&submit=deletesartoria",
                            dataType: "json",
                            success: function () {
                                mostraSartoria(idp);
                            }
                        });
                    },
                    onItemUpdated: function (args) {
                        var valori = $.param(args.item);
                        $.ajax({
                            type: "POST",
                            url: "./impostazioni.php",
                            data: valori + "&submit=editsartoria",
                            dataType: "json",
                            success: function () {
                                mostraSartoria(idp);
                            }
                        });
                    },
                    onItemInserted: function (args) {
                        var valori = $.param(args.item);
                        $.ajax({
                            type: "POST",
                            url: "./impostazioni.php",
                            data: valori + "&idp= " + idp + "&submit=insertsartoria",
                            dataType: "json",
                            success: function () {
                                mostraSartoria(idp);
                            }
                        });
                    }
                });
            }

        });
    }
    $('.showcont').html("<div id=\"jsGrid\"></div>");
}

function mostraAccessori() {
    
        $.ajax({
            type: "POST",
            url: "./impostazioni.php",
            data: "submit=mostraaccessori",
            dataType: "json",
            success: function (msg) {
                var db = {
                    loadData: function (filter) {
                        return $.grep(this.clients, function (client) {
                            return (!filter.nome || client.nome.indexOf(filter.nome) > -1);
                        });
                    }
                };

                window.db = db;

                db.clients = msg.dati;
                jsGrid.locale("it");
                var campi = [
                    {name: "nome", type: "text", title: "<b>Accessori</b>", css: "customRow", validate: "required"},
                    {type: "control"}
                ];

                $("#jsGrid").jsGrid({
                    width: "100%",
                    height: "520px",
                    editing: true,
                    inserting: true,
                    autoload: true,
                    sorting: true,
                    paging: true,
                    pageSize: 12,
                    pageButtonCount: 5,
                    controller: db,
                    deleteConfirm: "Stai per cancellare, sei sicuro?",
                    noDataContent: "Nessun record trovato",
                    fields: campi,
                    // cancella item
                    onItemDeleting: function (args) {
                        var id = args.item.id;
                        $.ajax({
                            type: "POST",
                            url: "./impostazioni.php",
                            data: "id=" + id + "&submit=deleteaccessori",
                            dataType: "json",
                            success: function () {
                                mostraAccessori();
                            }
                        });
                    },
                    onItemUpdated: function (args) {
                        var valori = $.param(args.item);
                        $.ajax({
                            type: "POST",
                            url: "./impostazioni.php",
                            data: valori + "&submit=editaccessori",
                            dataType: "json",
                            success: function () {
                                mostraAccessori();
                            }
                        });
                    },
                    onItemInserted: function (args) {
                        var valori = $.param(args.item);
                        $.ajax({
                            type: "POST",
                            url: "./impostazioni.php",
                            data: valori + "&submit=insertaccessori",
                            dataType: "json",
                            success: function () {
                                mostraAccessori();
                            }
                        });
                    }
                });
            }

        });
    
    $('.showcont').html("<div id=\"jsGrid\"></div>");
}

/* abiti e tipo abito */
function mostraProfitcenter(idp) {
    if (idp === '0') {
        $.ajax({
            type: "POST",
            url: "./impostazioni.php",
            data: "submit=mostraprofitcenter",
            dataType: "json",
            success: function (msg) {
                $('#backprofit').html("");
                var db = {
                    loadData: function (filter) {
                        return $.grep(this.clients, function (client) {
                            return (!filter.nome || client.nome.indexOf(filter.nome) > -1);
                        });
                    }
                };

                window.db = db;

                db.clients = msg.dati;
                jsGrid.locale("it");
                var campi = [
                    {name: "nome", type: "text", title: "<b>Abito</b>", css: "customRow", validate: "required"},
                    {type: "control", itemTemplate: function (value, item) {
                            var $result = jsGrid.fields.control.prototype.itemTemplate.apply(this, arguments);
                            var $myButton = $("<i style=\"margin-left: 5px; color: #E4A326;\"class=\"fa fa-folder-open fa-lg\" aria-hidden=\"true\"></i>")
                                    .click(function (e) {
                                        mostraProfitcenter(item.id);
                                        e.stopPropagation();
                                    });
                            return $result.add($myButton);
                        }}
                ];

                $("#jsGrid").jsGrid({
                    width: "100%",
                    height: "520px",
                    editing: true,
                    inserting: true,
                    autoload: true,
                    sorting: true,
                    paging: true,
                    pageSize: 12,
                    pageButtonCount: 5,
                    controller: db,
                    deleteConfirm: "Stai per cancellare, sei sicuro?",
                    noDataContent: "Nessun record trovato",
                    fields: campi,
                    // cancella item
                    onItemDeleting: function (args) {
                        var id = args.item.id;
                        $.ajax({
                            type: "POST",
                            url: "./impostazioni.php",
                            data: "id=" + id + "&submit=deleteprofit",
                            dataType: "json",
                            success: function () {
                                mostraProfitcenter(idp);
                            }
                        });
                    },
                    onItemUpdated: function (args) {
                        var valori = $.param(args.item);
                        $.ajax({
                            type: "POST",
                            url: "./impostazioni.php",
                            data: valori + "&submit=editprofit",
                            dataType: "json",
                            success: function () {
                                mostraProfitcenter(idp);
                            }
                        });
                    },
                    onItemInserted: function (args) {
                        var valori = $.param(args.item);
                        $.ajax({
                            type: "POST",
                            url: "./impostazioni.php",
                            data: valori + "&submit=insertprofit",
                            dataType: "json",
                            success: function () {
                                mostraProfitcenter(idp);
                            }
                        });
                    }
                });
            }

        });
    } else {
        $.ajax({
            type: "POST",
            url: "./impostazioni.php",
            data: "idp=" + idp + "&submit=mostraprofitcenter",
            dataType: "json",
            success: function (msg) {

                $('#backprofit').html(" :: " + msg.titprofit + " <a href=\"javascript:;\" onclick=\"mostraProfitcenter('0')\"><i style=\"margin-left: 20px;\"class=\"fa fa-arrow-circle-o-left fa-lg\" aria-hidden=\"true\"></i> Torna indietro</a>");

                var db = {
                    loadData: function (filter) {
                        return $.grep(this.clients, function (client) {
                            return (!filter.nome || client.nome.indexOf(filter.nome) > -1);
                        });
                    }
                };

                window.db = db;

                db.clients = msg.dati;
                jsGrid.locale("it");
                var campi = [
                    {name: "nome", type: "text", title: "<b>Tipo di Abito</b>", css: "customRow", validate: "required"},
                    {type: "control"}
                ];

                $("#jsGrid").jsGrid({
                    width: "100%",
                    height: "520px",
                    editing: true,
                    inserting: true,
                    autoload: true,
                    sorting: true,
                    paging: true,
                    pageSize: 12,
                    pageButtonCount: 5,
                    controller: db,
                    deleteConfirm: "Stai per cancellare, sei sicuro?",
                    noDataContent: "Nessun record trovato",
                    fields: campi,
                    // cancella item
                    onItemDeleting: function (args) {
                        var id = args.item.id;
                        $.ajax({
                            type: "POST",
                            url: "./impostazioni.php",
                            data: "id=" + id + "&submit=deleteprofit",
                            dataType: "json",
                            success: function () {
                                mostraProfitcenter(idp);
                            }
                        });
                    },
                    onItemUpdated: function (args) {
                        var valori = $.param(args.item);
                        $.ajax({
                            type: "POST",
                            url: "./impostazioni.php",
                            data: valori + "&submit=editprofit",
                            dataType: "json",
                            success: function () {
                                mostraProfitcenter(idp);
                            }
                        });
                    },
                    onItemInserted: function (args) {
                        var valori = $.param(args.item);
                        $.ajax({
                            type: "POST",
                            url: "./impostazioni.php",
                            data: valori + "&idp= " + idp + "&submit=insertprofit",
                            dataType: "json",
                            success: function () {
                                mostraProfitcenter(idp);
                            }
                        });
                    }
                });
            }

        });
    }
    $('.showcont').html("<div id=\"jsGrid\"></div>");
}
/**/

//function mostraProfitcenter() {
//    
//        $.ajax({
//            type: "POST",
//            url: "./impostazioni.php",
//            data: "submit=mostraprofitcenter",
//            dataType: "json",
//            success: function (msg) {
//                $('#backprofit').html("");
//                var db = {
//                    loadData: function (filter) {
//                        return $.grep(this.clients, function (client) {
//                            return (!filter.nome || client.nome.indexOf(filter.nome) > -1);
//                        });
//                    }
//                };
//
//                window.db = db;
//
//                db.clients = msg.dati;
//                jsGrid.locale("it");
//                var campi = [
//                    {name: "nome", type: "text", title: "<b>Abito</b>", css: "customRow", validate: "required"},
//                    {type: "control"}
//                ];
//
//                $("#jsGrid").jsGrid({
//                    width: "100%",
//                    height: "520px",
//                    editing: true,
//                    inserting: true,
//                    autoload: true,
//                    sorting: true,
//                    paging: true,
//                    pageSize: 12,
//                    pageButtonCount: 5,
//                    controller: db,
//                    deleteConfirm: "Stai per cancellare, sei sicuro?",
//                    noDataContent: "Nessun record trovato",
//                    fields: campi,
//                    // cancella item
//                    onItemDeleting: function (args) {
//                        var id = args.item.id;
//                        $.ajax({
//                            type: "POST",
//                            url: "./impostazioni.php",
//                            data: "id=" + id + "&submit=deleteprofit",
//                            dataType: "json",
//                            success: function () {
//                                mostraProfitcenter();
//                            }
//                        });
//                    },
//                    onItemUpdated: function (args) {
//                        var valori = $.param(args.item);
//                        $.ajax({
//                            type: "POST",
//                            url: "./impostazioni.php",
//                            data: valori + "&submit=editprofit",
//                            dataType: "json",
//                            success: function () {
//                                mostraProfitcenter();
//                            }
//                        });
//                    },
//                    onItemInserted: function (args) {
//                        var valori = $.param(args.item);
//                        $.ajax({
//                            type: "POST",
//                            url: "./impostazioni.php",
//                            data: valori + "&submit=insertprofit",
//                            dataType: "json",
//                            success: function () {
//                                mostraProfitcenter();
//                            }
//                        });
//                    }
//                });
//            }
//
//        });
//    
//    $('.showcont').html("<div id=\"jsGrid\"></div>");
//}

function mostraMetodi() {
    $.ajax({
        type: "POST",
        url: "./impostazioni.php",
        data: "submit=mostrametodi",
        dataType: "json",
        success: function (msg) {
            var db = {
                loadData: function (filter) {
                    return $.grep(this.clients, function (client) {
                        return (!filter.nome || client.nome.indexOf(filter.nome) > -1);
                    });
                }
            };

            window.db = db;

            db.clients = msg.dati;
            jsGrid.locale("it");
            var campi = [
                {name: "nome", type: "text", title: "<b>Nome metodo</b>", css: "customRow", validate: "required"},
                {name: "tempo", type: "select", items: [
                        {name: "Seleziona tempo", Id: ""},
                        {name: "Immediato", Id: "0"},
                        {name: "30 giorni", Id: "30"},
                        {name: "60 giorni", Id: "60"},
                        {name: "90 giorni", Id: "90"},
                        {name: "120 giorni", Id: "120"},
                        {name: "30/60 giorni", Id: "30-60"},
                        {name: "30/60/90 giorni", Id: "30-60-90"},
                        {name: "30/60/90/120 giorni", Id: "30-60-90-120"},
                        {name: "30/60/90/120/150 giorni", Id: "30-60-90-120-150"},
                        {name: "60/90 giorni", Id: "60-90"},
                        {name: "60/90/120 giorni", Id: "60-90-120"},
                        {name: "60/90/120/150 giorni", Id: "60-90-120-150"},
                        {name: "30/60/90/120/150/180 giorni", Id: "30-60-90-120-150-180"},
                        {name: "30/60/90/120/150/180/210 giorni", Id: "30-60-90-120-150-180-210"},
                        {name: "90/120 giorni", Id: "90-120"},
                        {name: "90/120/150 giorni", Id: "90-120-150"}
                    ], title: "<b>Tempi di pagamento</b>", css: "customRow", valueField: "Id", textField: "name", validate: "required"},
                {type: "control"}
            ];

            $("#jsGrid").jsGrid({
                width: "60%",
                height: "520px",
                editing: true,
                inserting: true,
                autoload: true,
                sorting: true,
                paging: true,
                pageSize: 12,
                pageButtonCount: 5,
                controller: db,
                deleteConfirm: "Stai per cancellare, sei sicuro?",
                noDataContent: "Nessun record trovato",
                fields: campi,
                // cancella item
                onItemDeleting: function (args) {
                    var id = args.item.id;
                    $.ajax({
                        type: "POST",
                        url: "./impostazioni.php",
                        data: "id=" + id + "&submit=deletemetodo",
                        dataType: "json",
                        success: function () {
                        }
                    });
                },
                onItemUpdated: function (args) {
                    var valori = $.param(args.item);
                    $.ajax({
                        type: "POST",
                        url: "./impostazioni.php",
                        data: valori + "&submit=editmetodo",
                        dataType: "json",
                        success: function () {
                        }
                    });
                },
                onItemInserted: function (args) {
                    var valori = $.param(args.item);
                    $.ajax({
                        type: "POST",
                        url: "./impostazioni.php",
                        data: valori + "&submit=insertmetodo",
                        dataType: "json",
                        success: function () {
                        }
                    });
                }
            });
        }

    });
    $('.showcont').html("<div id=\"jsGrid\"></div>");
}

function mostraIVA() {
    $.ajax({
        type: "POST",
        url: "./impostazioni.php",
        data: "submit=mostraiva",
        dataType: "json",
        success: function (msg) {
            var db = {
                loadData: function (filter) {
                    return $.grep(this.clients, function (client) {
                        return (!filter.valore || client.valore.indexOf(filter.valore) > -1);
                    });
                }
            };

            window.db = db;

            db.clients = msg.dati;
            jsGrid.locale("it");
            var campi = [
                {name: "valore", type: "text", title: "<b>Iva</b>", css: "customRow", validate: "required"},
                {name: "predefinito", type: "checkbox", title: "<b>Predefinito</b>", css: "customRow"},
                {name: "codice_iva", type: "text", title: "<b>Codice Iva</b>", css: "customRow"},
                {name: "normativa", type: "text", title: "<b>Normativa</b>", css: "customRow"},
                {type: "control"}
            ];

            $("#jsGrid").jsGrid({
                width: "50%",
                height: "400px",
                editing: true,
                inserting: true,
                autoload: true,
                controller: db,
                deleteConfirm: "Stai per cancellare, sei sicuro?",
                noDataContent: "Nessun record trovato",
                fields: campi,
                // cancella item
                onItemDeleting: function (args) {
                    var id = args.item.id;
                    $.ajax({
                        type: "POST",
                        url: "./impostazioni.php",
                        data: "id=" + id + "&submit=deleteiva",
                        dataType: "json",
                        success: function () {
                        }
                    });
                },
                onItemUpdated: function (args) {
                    var valori = $.param(args.item);
                    $.ajax({
                        type: "POST",
                        url: "./impostazioni.php",
                        data: valori + "&submit=editiva",
                        dataType: "json",
                        success: function () {
                            mostraIVA();
                        }
                    });
                },
                onItemInserted: function (args) {
                    var valori = $.param(args.item);
                    $.ajax({
                        type: "POST",
                        url: "./impostazioni.php",
                        data: valori + "&submit=insertiva",
                        dataType: "json",
                        success: function () {
                            mostraIVA();
                        }
                    });
                }
            });
        }

    });
    $('.showcont').html("<div id=\"jsGrid\"></div>");
}

function mostraUtentipermessi() {
    $.ajax({
        type: "POST",
        url: "./impostazioni.php",
        data: "submit=mostrautentipermessi",
        dataType: "json",
        success: function (msg) {
            var campopass = function (config) {
                jsGrid.Field.call(this, config);
            };

            campopass.prototype = new jsGrid.Field({
                itemTemplate: function () {
                    return "**********";
                },
                insertTemplate: function () {
                    return this._insert = $("<input type=\"password\">");
                },
                editTemplate: function () {
                    return this._edit = $("<input type=\"password\">");
                },
                insertValue: function () {
                    return this._insert.val();
                },
                editValue: function () {
                    return this._edit.val();
                }
            });

            jsGrid.fields.Campopass = campopass;


            var db = {
                loadData: function (filter) {
                    return $.grep(this.clients, function (client) {
                        return (!filter.nome || client.nome.indexOf(filter.nome) > -1)
                                && (!filter.cognome || client.cognome.indexOf(filter.cognome) > -1)
                                && (!filter.codicefiscale || client.codicefiscale.indexOf(filter.codicefiscale) > -1)
                                && (!filter.email || client.email.indexOf(filter.email) > -1)
                                && (!filter.username || client.username.indexOf(filter.username) > -1)
                                && (!filter.telefono || client.telefono.indexOf(filter.telefono) > -1)
                                && (!filter.cellulare || client.cellulare.indexOf(filter.cellulare) > -1);
                    });
                }
            };

            window.db = db;

            db.clients = msg.dati;
            jsGrid.locale("it");
            var campi = [
                {name: "nome", type: "text", title: "<b>Nome</b>", css: "customRow", validate: "required", width: "50"},
                {name: "cognome", type: "text", title: "<b>Cogome</b>", css: "customRow", validate: "required", width: "50"},
                {name: "email", type: "text", title: "<b>Email</b>", css: "customRow", validate: "required", width: "70"},
                {name: "username", type: "text", title: "<b>Username</b>", css: "customRow", validate: "required", width: "70"},
                {name: "password", type: "Campopass", title: "<b>Password</b>", css: "customRow", width: "50"},
                {name: "impostazioni", type: "checkbox", title: "<b>Impostazioni</b>", css: "customRow", width: "50"},
                {name: "dipendenti", type: "checkbox", title: "<b>Dipendenti</b>", css: "customRow", width: "50"},
                {name: "clientifornitori", type: "checkbox", title: "<b>Clienti/Fornitori</b>", css: "customRow", width: "50"},
                {name: "magazzino", type: "checkbox", title: "<b>Magazzino</b>", css: "customRow", width: "50"},
                {name: "preventivi", type: "checkbox", title: "<b>Preventivi</b>", css: "customRow", width: "50"},
                {name: "ddt", type: "checkbox", title: "<b>DDT</b>", css: "customRow", width: "50"},
                {name: "ecommerce", type: "checkbox", title: "<b>E-commerce</b>", css: "customRow", width: "50"},
                {name: "commesse", type: "checkbox", title: "<b>Commesse</b>", css: "customRow", width: "50"},
                {name: "fatture", type: "checkbox", title: "<b>Fatture</b>", css: "customRow", width: "50"},
                {name: "scadenziario", type: "checkbox", title: "<b>Scadenziario</b>", css: "customRow", width: "50"},
                {name: "partitario", type: "checkbox", title: "<b>Partitario</b>", css: "customRow", width: "50"},
                {name: "ore", type: "checkbox", title: "<b>Ore</b>", css: "customRow", width: "50"},
                {name: "statistiche", type: "checkbox", title: "<b>Statistiche</b>", css: "customRow", width: "50"},
                {name: "livello", type: "select", items: [
                        {name: "Admin gestionale", Id: "1"},
                        {name: "Utente gestionale", Id: "2"},
                        {name: "Dipendente", Id: "3"},
                        {name: "Dipendente Admin", Id: "4"},
                    ], title: "<b>Livello</b>", valueField: "Id", textField: "name", css: "customRow", width: "70"},
                {name: "attivo", type: "select", items: [
                        {name: "Disattivo", Id: "0"},
                        {name: "Attivo", Id: "1"}
                    ], title: "<b>Stato</b>", valueField: "Id", textField: "name", css: "customRow", width: "50"},
                {type: "control"}
            ];

            $("#jsGrid").jsGrid({
                width: "100%",
                height: "400px",
                editing: true,
                inserting: true,
                autoload: true,
                sorting: true,
                controller: db,
                deleteConfirm: "Stai per cancellare, sei sicuro?",
                noDataContent: "Nessun record trovato",
                fields: campi,
                // cancella item
                onItemDeleting: function (args) {
                    var id = args.item.id;
                    $.ajax({
                        type: "POST",
                        url: "./impostazioni.php",
                        data: "id=" + id + "&submit=deleteutente",
                        dataType: "json",
                        success: function () {
                        }
                    });
                },
                onItemUpdating: function (args) {
                    if (args.item.password.length > 0) {
                        var pass = CryptoJS.MD5(args.item.password);
                        args.item.password = "";
                    } else {
                        pass = "";
                    }
                    var valori = $.param(args.item);
                    $.ajax({
                        type: "POST",
                        url: "./impostazioni.php",
                        data: valori + "&pass=" + pass + "&submit=editutente",
                        dataType: "json",
                        async: false, // questo stoppa il resto del codice
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                                args.cancel = true;
                            }
                        }
                    });

                },
                onItemInserting: function (args) {
                    if (args.item.password.length > 0) {
                        var pass = CryptoJS.MD5(args.item.password);
                        args.item.password = "";
                    } else {
                        pass = "";
                    }
                    var valori = $.param(args.item);
                    $.ajax({
                        type: "POST",
                        url: "./impostazioni.php",
                        data: valori + "&pass=" + pass + "&submit=insertutente",
                        dataType: "json",
                        async: false, // questo stoppa il resto del codice
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                                args.cancel = true;
                            }
                        }
                    });
                }
            });
        }

    });
    $('.showcont').html("<div id=\"jsGrid\"></div>");
}

/* mostra dati azienda */
function mostraDati() {
    $('.showcont').show().load('./form/form-azienda.php', function () {

        /**/
        $.validator.messages.required = '';
        $("#formdatiazienda").validate({
            rules: {
                email: {
                    email: true
                }
            },
            submitHandler: function () {
                $("#submitformdatiazienda").ready(function () {
                    var datastring = $("#formdatiazienda *").not(".nopost").serialize();                   
                    $.ajax({
                        type: "POST",
                        url: "./impostazioni.php",
                        data: datastring + "&submit=sendformdatiazienda",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                            }
                        }
                    });
                });
            }
        });
    });
}
/* cancella logo */
function delLogo(id) {
    if (confirm("Stai per cancellare il logo, procedo?")) {
        $.ajax({
            type: "POST",
            url: "./impostazioni.php",
            data: "id=" + id + "&submit=dellogo",
            dataType: "json",
            success: function (msg) {
                $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                $('.logo_gest').html("<img src=\"/immagini/nologo.png\" />");
            }
        });
    }
}

/* mostra dati azienda */
function mostraEcommerce() {
    $('.showcont').show().load('./form/form-ecommerce.php', function () {

        /**/
        $.validator.messages.required = '';
        $("#formecommerce").validate({
            submitHandler: function () {
                $("#submitformecommerce").ready(function () {
                    var datastring = $("#formecommerce *").not(".nopost").serialize();
                     if ($('#pass_db').val().length > 0) {
                        var pass = $('#pass_db').val();
                    } else {
                        pass = "";
                    }
                    $.ajax({
                        type: "POST",
                        url: "./impostazioni.php",
                        data: datastring + "&pass_db="+ pass +"&submit=sendformecommerce",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#messaggio').slideToggle('fast').delay(2000).slideToggle('slow');
                                $('#pass_db').val("");
                            }
                        }
                    });
                });
            }
        });
    });
}

function mostraCentridicosto() {
    $.ajax({
        type: "POST",
        url: "./impostazioni.php",
        data: "submit=mostracentridicosto",
        dataType: "json",
        success: function (msg) {
            $('#backprofit').html("");
            var db = {
                loadData: function (filter) {
                    return $.grep(this.clients, function (client) {
                        return (!filter.nome || client.nome.indexOf(filter.nome) > -1);
                    });
                }
            };

            window.db = db;

            db.clients = msg.dati;
            jsGrid.locale("it");
            var campi = [
                {name: "nome", type: "text", title: "<b>Come ci hai trovato</b>", css: "customRow", validate: "required"},
                {type: "control"}
            ];

            $("#jsGrid").jsGrid({
                width: "100%",
                height: "520px",
                editing: true,
                inserting: true,
                autoload: true,
                sorting: true,
                paging: true,
                pageSize: 12,
                pageButtonCount: 5,
                controller: db,
                deleteConfirm: "Stai per cancellare, sei sicuro?",
                noDataContent: "Nessun record trovato",
                fields: campi,
                // cancella item
                onItemDeleting: function (args) {
                    var id = args.item.id;
                    $.ajax({
                        type: "POST",
                        url: "./impostazioni.php",
                        data: "id=" + id + "&submit=deletecentro",
                        dataType: "json",
                        success: function () {
                            mostraCentridicosto();
                        }
                    });
                },
                onItemUpdated: function (args) {
                    var valori = $.param(args.item);
                    $.ajax({
                        type: "POST",
                        url: "./impostazioni.php",
                        data: valori + "&submit=editcentro",
                        dataType: "json",
                        success: function () {
                            mostraCentridicosto();
                        }
                    });
                },
                onItemInserted: function (args) {
                    var valori = $.param(args.item);
                    $.ajax({
                        type: "POST",
                        url: "./impostazioni.php",
                        data: valori + "&submit=insertcentro",
                        dataType: "json",
                        success: function () {
                            mostraCentridicosto();
                        }
                    });
                }
            });
        }

    });

    $('.showcont').html("<div id=\"jsGrid\"></div>");
}

function mostraMotivono() {
    
        $.ajax({
            type: "POST",
            url: "./impostazioni.php",
            data: "submit=mostramotivono",
            dataType: "json",
            success: function (msg) {
                var db = {
                    loadData: function (filter) {
                        return $.grep(this.clients, function (client) {
                            return (!filter.nome || client.nome.indexOf(filter.nome) > -1);
                        });
                    }
                };

                window.db = db;

                db.clients = msg.dati;
                jsGrid.locale("it");
                var campi = [
                    {name: "nome", type: "text", title: "<b>Motivo</b>", css: "customRow", validate: "required"},
                    {type: "control"}
                ];

                $("#jsGrid").jsGrid({
                    width: "100%",
                    height: "520px",
                    editing: true,
                    inserting: true,
                    autoload: true,
                    sorting: true,
                    paging: true,
                    pageSize: 12,
                    pageButtonCount: 5,
                    controller: db,
                    deleteConfirm: "Stai per cancellare, sei sicuro?",
                    noDataContent: "Nessun record trovato",
                    fields: campi,
                    // cancella item
                    onItemDeleting: function (args) {
                        var id = args.item.id;
                        $.ajax({
                            type: "POST",
                            url: "./impostazioni.php",
                            data: "id=" + id + "&submit=deletemotivono",
                            dataType: "json",
                            success: function () {
                                mostraMotivono();
                            }
                        });
                    },
                    onItemUpdated: function (args) {
                        var valori = $.param(args.item);
                        $.ajax({
                            type: "POST",
                            url: "./impostazioni.php",
                            data: valori + "&submit=editmotivono",
                            dataType: "json",
                            success: function () {
                                mostraMotivono();
                            }
                        });
                    },
                    onItemInserted: function (args) {
                        var valori = $.param(args.item);
                        $.ajax({
                            type: "POST",
                            url: "./impostazioni.php",
                            data: valori + "&submit=insertmotivono",
                            dataType: "json",
                            success: function () {
                                mostraMotivono();
                            }
                        });
                    }
                });
            }

        });
    
    $('.showcont').html("<div id=\"jsGrid\"></div>");
}

function mostraBollini() {
    
        $.ajax({
            type: "POST",
            url: "./impostazioni.php",
            data: "submit=mostrabollini",
            dataType: "json",
            success: function (msg) {
                var db = {
                    loadData: function (filter) {
                        return $.grep(this.clients, function (client) {
                            return (!filter.nome || client.nome.indexOf(filter.nome) > -1);
                        });
                    }
                };

                window.db = db;

                db.clients = msg.dati;
                jsGrid.locale("it");
                var campi = [
                    {name: "nome", type: "text", title: "<b>Bollino</b>", css: "customRow", validate: "required"},
                    {type: "control"}
                ];

                $("#jsGrid").jsGrid({
                    width: "100%",
                    height: "520px",
                    editing: true,
                    inserting: true,
                    autoload: true,
                    sorting: true,
                    paging: true,
                    pageSize: 12,
                    pageButtonCount: 5,
                    controller: db,
                    deleteConfirm: "Stai per cancellare, sei sicuro?",
                    noDataContent: "Nessun record trovato",
                    fields: campi,
                    // cancella item
                    onItemDeleting: function (args) {
                        var id = args.item.id;
                        $.ajax({
                            type: "POST",
                            url: "./impostazioni.php",
                            data: "id=" + id + "&submit=deletebollino",
                            dataType: "json",
                            success: function () {
                                mostraBollini();
                            }
                        });
                    },
                    onItemUpdated: function (args) {
                        var valori = $.param(args.item);
                        $.ajax({
                            type: "POST",
                            url: "./impostazioni.php",
                            data: valori + "&submit=editbollino",
                            dataType: "json",
                            success: function () {
                                mostraBollini();
                            }
                        });
                    },
                    onItemInserted: function (args) {
                        var valori = $.param(args.item);
                        $.ajax({
                            type: "POST",
                            url: "./impostazioni.php",
                            data: valori + "&submit=insertbollino",
                            dataType: "json",
                            success: function () {
                                mostraBollini();
                            }
                        });
                    }
                });
            }

        });
    
    $('.showcont').html("<div id=\"jsGrid\"></div>");
}