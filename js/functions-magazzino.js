function mostraMagazzino(filtri, paginastart) {

    if (paginastart) {
        var pagestart = parseInt(paginastart);
    }

    if (filtri) {
        var filtriarr = filtri.split("||");
        var filtro0 = {codice: filtriarr[0]};
        var filtro1 = {codiceest: filtriarr[1]};
        var filtro2 = {titolo: filtriarr[2]};
        var filtro3 = {descrizione: filtriarr[3]};
        var filtro4 = {um: filtriarr[4]};
        var filtro5 = {qta: filtriarr[5]};
        var filtro6 = {fornitore: filtriarr[6]};
        var filtro7 = {prezzo: filtriarr[7]};
    }

    $.ajax({
        type: "POST",
        url: "./magazzino.php",
        data: "submit=mostramagazzino",
        dataType: "json",
        success: function (msg) {
            var db = {
                loadData: function (filter) {
                    return $.grep(this.clients, function (client) {
                        return (!filter.codice.toLowerCase() || client.codice.toLowerCase().indexOf(filter.codice.toLowerCase()) > -1)
                                && (!filter.titolo.toLowerCase() || client.titolo.toLowerCase().indexOf(filter.titolo.toLowerCase()) > -1)
                                && (!filter.descrizione.toLowerCase() || client.descrizione.toLowerCase().indexOf(filter.descrizione.toLowerCase()) > -1)
                                && (!filter.um.toLowerCase() || client.um.toLowerCase().indexOf(filter.um.toLowerCase()) > -1)
                                && (!filter.qta.toLowerCase() || client.qta.toLowerCase().indexOf(filter.qta.toLowerCase()) > -1)
                                && (!filter.fornitore.toLowerCase() || client.fornitore.toLowerCase().indexOf(filter.fornitore.toLowerCase()) > -1)
                                && (!filter.codiceest.toLowerCase() || client.codiceest.toLowerCase().indexOf(filter.codiceest.toLowerCase()) > -1)
                                && (!filter.prezzo.toLowerCase() || client.prezzo.toLowerCase().indexOf(filter.prezzo.toLowerCase()) > -1);
                    });
                }
            };

            window.db = db;

            db.clients = msg.dati;
            jsGrid.locale("it");
            var campi = [
                {name: "codice", type: "text", title: "<b>Codice</b>", css: "customRow", width: "30"},
                {name: "titolo", type: "text", title: "<b>Titolo</b>", css: "customRow", validate: "required", width: "50"},
                {name: "descrizione", type: "text", title: "<b>Descrizione</b>", css: "customRow", width: "80"},
                {name: "um", type: "text", title: "<b>U.M.</b>", css: "customRow", width: "20"},
                {name: "qta", type: "text", title: "<b>Quantità</b>", css: "customRow", width: "20"},
                {name: "fornitore", type: "myAutoComplete", title: "<b>Fornitore</b>", css: "customRow", width: "50"},
                {name: "codiceest", type: "text", title: "<b>Codice fornitore</b>", css: "customRow", width: "30"},
                {name: "prezzo", type: "text", title: "<b>Prezzo</b>", css: "customRow", width: "25"},
                {type: "control", width: "15"}
            ];


            /* autocomplete sui clienti */
            var MyAutoComplete = function (config) {
                jsGrid.Field.call(this, config);
            };

            MyAutoComplete.prototype = new jsGrid.Field({
                filterTemplate: function () {
                    this._attivazione = $("<input id='fornauto'>");
                    return $("<div>").append(this._attivazione);
                },

                filterValue: function () {
                    var fornauto = "";
                    fornauto = $('#fornauto').val();
                    if (fornauto != null) {
                        return fornauto;
                    }
                },

                insertTemplate: function (value) {
                    idfornitore = "";
                    var fornitori = $.map(msg.fornitoriautocomplete, function (item) {
                        return {
                            label: item.azienda,
                            id: item.id,
                            azienda: item.azienda
                        };
                    });

                    this._insertPicker = $("<input>").autocomplete({
                        source: fornitori,
                        minLength: 0,
                        select: function (event, ui) {
                            return idfornitore = ui.item.id + "|||" + ui.item.azienda;

                        }
                    });
                    return $("<div>").append(this._insertPicker);
                },

                insertValue: function () {
                    return idfornitore;
                },

                editTemplate: function (value) {
                    if (value != null) {
                        var fornitorearr = value.split("|||");
                    }

                    if (fornitorearr[1]) {
                        var valoreinput = fornitorearr[1];
                    } else {
                        valoreinput = fornitorearr[0];
                    }

                    idfornitore = "0";
                    var fornitori = $.map(msg.fornitoriautocomplete, function (item) {
                        return {
                            label: item.azienda,
                            id: item.id,
                            azienda: item.azienda
                        };
                    });

                    this._editPicker = $("<input>").autocomplete({
                        source: fornitori,
                        minLength: 0,
                        select: function (event, ui) {
                            return idfornitore = ui.item.id + "|||" + ui.item.azienda;

                        }
                    }).val(valoreinput);

                    return $("<div>").append(this._editPicker);
                },

                editValue: function () {
                    if (idfornitore == 0) {
                        return this._editPicker.val();
                    } else {
                        return idfornitore;
                    }
                },

                itemTemplate: function (value) {
                    if (value != null) {
                        var fornitore = value.split("|||");
                        if (fornitore[1]) {
                            return fornitore[1];
                        } else {
                            return fornitore[0];
                        }
                    }
                }

            });
            jsGrid.fields.myAutoComplete = MyAutoComplete;



            /**/

            $("#jsGrid").jsGrid({
                width: "100%",
                height: "580px",
                filtering: true,
                inserting: true,
                editing: true,
                sorting: true,
                paging: true,
                autoload: true,
                pageSize: 16,
                pageIndex: pagestart,
                pageButtonCount: 5,
                controller: db,
                deleteConfirm: "Stai per cancellare, sei sicuro?",
                noDataContent: "Nessun record trovato",
                fields: campi,
                // cancella item
                onItemDeleting: function (args) {
                    var idd = args.item.id;
                    $.ajax({
                        type: "POST",
                        url: "./magazzino.php",
                        data: "id=" + idd + "&submit=delete",
                        dataType: "json",
                        success: function () {
                        }
                    });
                },
                // inserisci item
                onItemInserted: function (args) {
                    var valori = $.param(args.item);
                    $.ajax({
                        type: "POST",
                        url: "./magazzino.php",
                        data: valori + "&submit=insert",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === 'ko') {
                                alert("Codice articolo già presente");
                                mostraMagazzino("", "");
                                return false;
                            } else {
                                mostraMagazzino("", "");
                            }
                        }
                    });
                },
                onItemUpdated: function (args) {
                    var valori = $.param(args.item);
                    $.ajax({
                        type: "POST",
                        url: "./magazzino.php",
                        data: valori + "&submit=edit",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === 'ko') {
                                alert("Codice articolo già presente");
                                mostraMagazzino("", "");
                                return false;
                            } else {
                                mostraMagazzino("", "");
                            }
                        }
                    });
                }
            });
        }

    });
    $('.showcont').html("<div id=\"jsGrid\"></div>");
}
