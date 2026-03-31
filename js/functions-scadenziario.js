function roundTo(value, decimals) {
    var i = value * Math.pow(10, decimals);
    i = Math.round(i);
    return i / Math.pow(10, decimals);
}

function mostraScadenze(tipo) {
    $.ajax({
        type: "POST",
        url: "./scadenziario.php",
        data: "tipo=" + tipo + "&submit=mostrascadenze",
        dataType: "json",
        success: function (msg) {
            var db = {
                loadData: function (filter) {
                    return $.grep(this.clients, function (client) {
                        return (!filter.numerofattura || client.numerofattura.indexOf(filter.numerofattura) > -1)
                                && (!filter.datafattura || client.datafattura.indexOf(filter.datafattura) > -1)
                                && (!filter.tipo || client.tipo.indexOf(filter.tipo) > -1)
                                && (!filter.daticliente.toLowerCase() || client.daticliente.toLowerCase().indexOf(filter.daticliente.toLowerCase()) > -1)
                                && (!filter.metodopagamento.toLowerCase() || client.metodopagamento.toLowerCase().indexOf(filter.metodopagamento.toLowerCase()) > -1)
                                && (!filter.importoscadenza || client.importoscadenza.indexOf(filter.importoscadenza) > -1)
                                && (!filter.notescadenza.toLowerCase() || client.notescadenza.toLowerCase().indexOf(filter.notescadenza.toLowerCase()) > -1)
                                && (!filter.stato || client.stato.indexOf(filter.stato) > -1)
                                && (!filter.datascadenza.from || new Date(client.datascadenza) >= filter.datascadenza.from)
                                && (!filter.datascadenza.to || new Date(client.datascadenza) <= filter.datascadenza.to);
                    });
                }
            };

            window.db = db;

            db.clients = msg.dati;
            jsGrid.locale("it");
            var campi = [
                {name: "numerofattura", type: "text", title: "<b>Num. Fatt.</b>", css: "customRow", editing: false, width: "40"},
                {name: "datafattura", type: "text", title: "<b>Data Fatt.</b>", css: "customRow", editing: false, width: "40"},
                {name: "tipo", type: "select", items: [
                            {name: "Seleziona tipo", Id: ""},
                            {name: "Vendita", Id: "0"},
                            {name: "Vendita PC", Id: "5"},
                            {name: "Fattura P.A.", Id: "4"},
                            {name: "Nota di credito", Id: "3"},
                            {name: "Nota di credito PC", Id: "6"}
                        ], title: "<b>Tipo</b>", valueField: "Id", textField: "name", css: "customRow", width: "60"},
                {name: "daticliente", type: "text", title: "<b>Dati Cliente</b>", css: "customRow", editing: false, width: "300"},
                {name: "metodopagamento", type: "text", title: "<b>Metodo di pagamento</b>", css: "customRow", editing: false},
                {name: "datascadenza", type: "date", title: "<b>Data scadenza</b>", css: "customRow", editing: false, width: "60"},
                {name: "importoscadenza", type: "text", title: "<b>Importo</b>", css: "customRow", editing: false, width: "50"},
                {name: "notescadenza", type: "text", title: "<b>Note</b>", css: "customRow"},
                {name: "stato", type: "select", items: [
                        {name: "Seleziona stato", Id: ""},
                        {name: "Non pagata", Id: "0"},
                        {name: "Pagata", Id: "1"}
                    ], title: "<b>Stato</b>", css: "customRow", valueField: "Id", textField: "name", validate: "required", width: "50", selectedIndex: 1},
                {type: "control", deleteButton: false}
            ];

            var DateField = function (config) {
                jsGrid.Field.call(this, config);
            };

            DateField.prototype = new jsGrid.Field({
                sorter: function (date1, date2) {
                    return new Date(date1) - new Date(date2);
                },

                itemTemplate: function (value) {
                    return new Date(value).toLocaleDateString();
                },

                filterTemplate: function () {
                    var now = new Date();
                    this._fromPicker = $("<input>").datepicker({defaultDate: now.setFullYear(now.getFullYear() - 1)});
                    this._toPicker = $("<input>").datepicker({defaultDate: now.setFullYear(now.getFullYear() + 1)});
                    return $("<div>").append(this._fromPicker).append(this._toPicker);
                },

                filterValue: function () {
                    return {
                        from: this._fromPicker.datepicker("getDate"),
                        to: this._toPicker.datepicker("getDate")
                    };
                }
            });

            jsGrid.fields.date = DateField;

            $("#jsGrid").jsGrid({
                width: "100%",
                height: "600px",
                filtering: true,
                editing: true,
                inserting: false,
                autoload: true,
                sorting: true,
                autosearch: true,
                paging: true,
                pageSize: 15,
                pageButtonCount: 5,
                controller: db,
                deleteConfirm: "Stai per cancellare, sei sicuro?",
                noDataContent: "Nessun record trovato",
                fields: campi,
                onItemUpdated: function (args) {
                    var valori = $.param(args.item);
                    $.ajax({
                        type: "POST",
                        url: "./scadenziario.php",
                        data: valori + "&submit=editscadenza",
                        dataType: "json",
                        success: function () {
                        }
                    });
                },
                onDataLoaded: function (args) {
                    var rows = args.grid.data;
                    total_price = 0;
                    for (row in rows)
                    {
                        curRow = rows[row];
                        total_price += parseFloat(curRow.importoscadenza);

                    }
                    $('#totalescadenze').html(roundTo(total_price, 2) + " &euro;");
                },
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
                {name: "preventivi", type: "checkbox", title: "<b>Preventivi</b>", css: "customRow", width: "50"},
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