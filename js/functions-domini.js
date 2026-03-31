function mostraDomini(filtri, paginastart) {

    if (paginastart) {
        var pagestart = parseInt(paginastart);
    }

    if (filtri) {
        var filtriarr = filtri.split("||");
        var filtro0 = {dataattivazione: filtriarr[0]};
        var filtro2 = {dominio: filtriarr[1]};
        var filtro3 = {descrizione: filtriarr[2]};
        var filtro4 = {cliente: filtriarr[3]};
        var filtro5 = {prezzo: filtriarr[4]};
    }

    $.ajax({
        type: "POST",
        url: "./domini.php",
        data: "submit=mostradomini",
        dataType: "json",
        success: function (msg) {
            var db = {
                loadData: function (filter) {
                    return $.grep(this.clients, function (client) {
                        return (!filter.dataattivazione || client.dataattivazione.indexOf(filter.dataattivazione) > -1)
                                && (!filter.dominio.toLowerCase() || client.dominio.toLowerCase().indexOf(filter.dominio.toLowerCase()) > -1)
                                && (!filter.descrizione.toLowerCase() || client.descrizione.toLowerCase().indexOf(filter.descrizione.toLowerCase()) > -1)
                                && (!filter.cliente.toLowerCase() || client.cliente.toLowerCase().indexOf(filter.cliente.toLowerCase()) > -1)
                                && (!filter.prezzo.toLowerCase() || client.prezzo.toLowerCase().indexOf(filter.prezzo.toLowerCase()) > -1);
                    });
                }
            };

            window.db = db;

            db.clients = msg.dati;
            jsGrid.locale("it");
            var campi = [
                {name: "dataattivazione", type: "date", title: "<b>Data attivazione</b>", css: "customRow", validate: "required", width: "30"},
                {name: "dominio", type: "text", title: "<b>Nome dominio</b>", css: "customRow", validate: "required", width: "50"},
                {name: "descrizione", type: "textarea", title: "<b>Descrizione</b>", css: "customRow"},
                {name: "cliente", type: "myAutoComplete", title: "<b>Cliente</b>", css: "customRow", validate: "required", width: "50"},
                {name: "prezzo", type: "text", title: "<b>Prezzo</b>", css: "customRow", width: "25"},
                {type: "control", width: "15"}
            ];


            /* autocomplete sui clienti */
            var MyAutoComplete = function (config) {
                jsGrid.Field.call(this, config);
            };

            MyAutoComplete.prototype = new jsGrid.Field({
                filterTemplate: function () {
                    this._attivazione = $("<input id='cliauto'>");
                    return $("<div>").append(this._attivazione);
                },

                filterValue: function () {
                    var cliauto = "";
                    cliauto = $('#cliauto').val();
                    if (cliauto != null) {
                        return cliauto;
                    }
                },

                insertTemplate: function (value) {
                    idcliente = "";
                    var clienti = $.map(msg.clientiautocomplete, function (item) {
                        return {
                            label: item.azienda,
                            id: item.id,
                            azienda: item.azienda
                        };
                    });

                    this._insertPicker = $("<input>").autocomplete({
                        source: clienti,
                        minLength: 0,
                        select: function (event, ui) {
                            return idcliente = ui.item.id + "|||" + ui.item.azienda;

                        }
                    });
                    return $("<div>").append(this._insertPicker);
                },

                insertValue: function () {
                    return idcliente;
                },

                editTemplate: function (value) {
                    if (value != null) {
                        var clientearr = value.split("|||");
                    }
                    
                    if (clientearr[1]) {
                        var valoreinput = clientearr[1];
                    } else {
                        valoreinput = clientearr[0];
                    }
                    
                    idcliente = "0";
                    var clienti = $.map(msg.clientiautocomplete, function (item) {
                        return {
                            label: item.azienda,
                            id: item.id,
                            azienda: item.azienda
                        };
                    });

                    this._editPicker = $("<input>").autocomplete({
                        source: clienti,
                        minLength: 0,
                        select: function (event, ui) {
                            return idcliente = ui.item.id + "|||" + ui.item.azienda;

                        }
                    }).val(valoreinput);

                    return $("<div>").append(this._editPicker);
                },

                editValue: function () {
                    if (idcliente == 0) {
                        return this._editPicker.val();
                    } else {
                        return idcliente;
                    }
                },

                itemTemplate: function (value) {
                    if (value != null) {
                        var cliente = value.split("|||");
                        if (cliente[1]) {
                            return cliente[1];
                        } else {
                            return cliente[0];
                        }
                    }
                }

            });
            jsGrid.fields.myAutoComplete = MyAutoComplete;

            /* datepicker */

            var DateField = function (config) {
                jsGrid.Field.call(this, config);
            };

            DateField.prototype = new jsGrid.Field({
                itemTemplate: function (value) {
                    if (value != null) {
                        var dcdateFormat = moment(value).format("DD/MM/YYYY");
                        return dcdateFormat;
                    }
                },

                filterTemplate: function () {
                    var now = new Date();
                    this._attivazione = $("<input id='frmDate'>").datepicker({dateFormat: "dd/mm/yy"});
                    return $("<div>").append(this._attivazione);
                },

                filterValue: function () {
                    if ($('#frmDate').val() != "") {
                        var data = $('#frmDate').val();
                        if (data != null) {
                            dataarr = data.split('/');
                            return dataarr[2] + "-" + dataarr[1] + "-" + dataarr[0];
                        }
                    }
                },
                insertTemplate: function (value) {
                    return this._insertPicker = $("<input>").datepicker({defaultDate: new Date(value)});
                },

                editTemplate: function (value) {
                    return this._editPicker = $("<input>").datepicker().datepicker("setDate", new Date(value));
                },

                insertValue: function () {
                    return this._insertPicker.datepicker("getDate");
                },

                editValue: function () {
                    return this._editPicker.datepicker("getDate");
                }
            });

            jsGrid.fields.date = DateField;

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
                        url: "./domini.php",
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
                        url: "./domini.php",
                        data: valori + "&submit=insert",
                        dataType: "json",
                        success: function (msg) {
                            mostraDomini("", "");
                        }
                    });
                },
                onItemUpdated: function (args) {
                    var valori = $.param(args.item);
                    $.ajax({
                        type: "POST",
                        url: "./domini.php",
                        data: valori + "&submit=edit",
                        dataType: "json",
                        success: function () {
//                            mostraDomini("", "");
                        }
                    });
                }
            });
        }

    });
    $('.showcont').html("<div id=\"jsGrid\"></div>");
}
