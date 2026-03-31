(function(jsGrid) {

    jsGrid.locales.it = {
        grid: {
            noDataContent: "Nessun record trovato",
            deleteConfirm: "Stai per cancellare, sei sicuro?",
            pagerFormat: "Pagine: {first} {prev} {pages} {next} {last} &nbsp;&nbsp; {pageIndex} di {pageCount}",
            pagePrevText: "<",
            pageNextText: ">",
            pageFirstText: "<<",
            pageLastText: ">>",
            loadMessage: "Sto caricando...",
            invalidMessage: "Dati inseriti errati !"
        },

        loadIndicator: {
            message: "Sto caricando..."
        },

        fields: {
            control: {
                searchModeButtonTooltip: "Ricerca",
                insertModeButtonTooltip: "Aggiungi un record",
                editButtonTooltip: "Modifica",
                deleteButtonTooltip: "Elimina",
                searchButtonTooltip: "Cerca",
                clearFilterButtonTooltip: "Pulisci",
                insertButtonTooltip: "Aggiungi",
                updateButtonTooltip: "Aggiorna",
                cancelEditButtonTooltip: "Annulla"
            }
        },

        validators: {
            required: { message: "Campo obbligatorio" },
            rangeLength: { message: "Lunghezza del valore fuori dal range definito" },
            minLength: { message: "Lunghezza campo troppo corta" },
            maxLength: { message: "Lunghezza campo troppo lunga" },
            pattern: { message: "Il valore del campo non corrisponde alla configurazione definita" },
            range: { message: "Il valore del campo è al di fuori dal range definito" },
            min: { message: "Il valore del campo è troppo corto" },
            max: { message: "Il valore del campo è troppo lungo" }
        }
    };

}(jsGrid, jQuery));

