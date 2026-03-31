//function mostraAppuntamenti(anno, idatelier) {
//    $('.showcont').show().load('./form/form-statisticheappuntamento.php', function () {
//        /**/
//        if (anno && idatelier) {
//            $.ajax({
//                type: "POST",
//                url: "./statistiche.php",
//                data: "anno=" + anno + "&idatelier=" + idatelier + "&submit=submitstatisticheappuntamenti",
//                dataType: "json",
//                success: function (msg) {
//                    if (msg.msg === "ko") {
//                        alert(msg.msgko);
//                    } else {
//                        /* dati grafico acquistato / non acquistato */
//                        var labels = new Array();
//                        var valori = new Array();
//                        $.each(msg.datigrafico, function (index, value) {
//                            labels.push(index);
//                            valori.push(value);
//                        });
//                        /**/
//                        var canvas = "<div style=\"width: 50%; !important\"><canvas id=\"myChart\"></canvas></div>";
//                        var canvas2 = "<div style=\"width: 50%; !important\"><canvas id=\"myChart2\"></canvas></div>";
//                        $('#graficoanno1').html("").append(msg.statistiche + canvas + msg.statistiche2 + canvas2);
//                        var ctx = document.getElementById("myChart");
//                        var myChart = new Chart(ctx, {
//                            type: 'pie',
//                            data: {
//                                labels: labels,
//                                datasets: [{
//                                        label: 'numero',
//                                        data: valori,
//                                        backgroundColor: [
//                                            "#009b00",
//                                            "#FF6384"
//                                        ],
//                                        borderColor: '#E9E9E9',
//                                        borderWidth: 3
//                                    }]
//                            },
//                            options: {
//                                tooltips: {
//                                    callbacks: {
//                                        label: function (tooltipItem, data) {
//                                            var allData = data.datasets[tooltipItem.datasetIndex].data;
//                                            var tooltipLabel = data.labels[tooltipItem.index];
//                                            var tooltipData = allData[tooltipItem.index];
//                                            var total = 0;
//                                            for (var i in allData) {
//                                                total += allData[i];
//                                            }
//                                            var tooltipPercentage = Math.round((tooltipData / total) * 100);
//                                            return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
//                                        }
//                                    }
//                                }
//                            }
//                        });
//                        /**/
//                        /* dati grafico motivo non acquisto */
//                        var labels2 = new Array();
//                        var valori2 = new Array();
//                        $.each(msg.datigraficono, function (index, value) {
//                            labels2.push(index);
//                            valori2.push(value);
//                        });
//                        /**/
//                        var ctx2 = document.getElementById("myChart2");
//                        var myChart2 = new Chart(ctx2, {
//                            type: 'pie',
//                            data: {
//                                labels: labels2,
//                                datasets: [{
//                                        label: 'numero',
//                                        data: valori2,
//                                        backgroundColor: [
//                                            "#009b00",
//                                            "#FF6384",
//                                            "#36A2EB",
//                                            "#FFCE56",
//                                            "#ab11de",
//                                            "#de7600",
//                                            "#de0000",
//                                            "#0039de"
//                                        ],
//                                        borderColor: '#E9E9E9',
//                                        borderWidth: 3
//                                    }]
//                            },
//                            options: {
//                                tooltips: {
//                                    callbacks: {
//                                        label: function (tooltipItem, data) {
//                                            var allData = data.datasets[tooltipItem.datasetIndex].data;
//                                            var tooltipLabel = data.labels[tooltipItem.index];
//                                            var tooltipData = allData[tooltipItem.index];
//                                            var total = 0;
//                                            for (var i in allData) {
//                                                total += allData[i];
//                                            }
//                                            var tooltipPercentage = Math.round((tooltipData / total) * 100);
//                                            return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
//                                        }
//                                    }
//                                }
//                            }
//                        });
//
//                        /* mese per mese */
//                        $('#graficoanno1').append("<br /><br /><hr style=\"width: 80%; float: left;\"><div class=\"chiudi\"></div>");
//                        for (var i = 1; i <= 12; i++) {
//                            var labels = new Array();
//                            var valori = new Array();
//                            if (msg.datimese[i]) {
//                                $.each(msg.datimese[i], function (index, value) {
//                                    labels.push(index);
//                                    valori.push(value);
//                                });
//
//                                var canvasm = "<div style=\"width: 70%; !important\"><canvas id=\"myChartm" + i + "\"></canvas></div>";
//                                $('#graficoanno1').append("<br /><br />Dettaglio Appuntamenti per Mese <b>" + msg.mesi[i - 1] + " " + msg.anno + "</b> " + canvasm + "<br /><br />");
//                                var ctxm = document.getElementById("myChartm" + i + "");
//                                var myChart = new Chart(ctxm, {
//                                    type: 'bar',
//                                    data: {
//                                        labels: labels,
//                                        datasets: [{
//                                                label: 'N°',
//                                                data: valori,
//                                                backgroundColor: 'rgba(46, 101, 161, 0.3)',
//                                                borderColor: 'rgba(46, 101, 161,1)',
//                                                borderWidth: 1
//                                            }]
//                                    },
//                                    options: {
//                                        scales: {
//                                            yAxes: [{
//                                                    ticks: {
//                                                        beginAtZero: true,
//                                                        min: 0
//                                                    }
//                                                }]
//                                        }
//                                    }
//                                });
//                            }
//                        }
//
//                    }
//                    /* fine else */
//                }
//            });
//        } // altro anno
//        $.validator.messages.required = '';
//        $("#statisticheanno").validate({
//            submitHandler: function () {
//                $("#submitstatisticheanno").ready(function () {
//                    var datastring = $("#statisticheanno *").not(".nopost").serialize();
//                    $.ajax({
//                        type: "POST",
//                        url: "./statistiche.php",
//                        data: datastring + "&submit=submitstatisticheappuntamenti",
//                        dataType: "json",
//                        success: function (msg) {
//                            if (msg.msg === "ko") {
//                                alert(msg.msgko);
//                            } else {
//                                $('#statisticheanno').trigger('reset');
//                                $('#idatelier').val('');
//                                /*
//                                 */
//                                /* dati grafico */
//                                /* dati grafico acquistato / non acquistato */
//                                var labels = new Array();
//                                var valori = new Array();
//                                $.each(msg.datigrafico, function (index, value) {
//                                    labels.push(index);
//                                    valori.push(value);
//                                });
//                                /**/
//                                var canvas = "<div style=\"width: 50%; !important\"><canvas id=\"myChart\"></canvas></div>";
//                                var canvas2 = "<div style=\"width: 50%; !important\"><canvas id=\"myChart2\"></canvas></div>";
//                                $('#graficoanno1').html("").append(msg.statistiche + canvas + msg.statistiche2 + canvas2);
//                                var ctx = document.getElementById("myChart");
//                                var myChart = new Chart(ctx, {
//                                    type: 'pie',
//                                    data: {
//                                        labels: labels,
//                                        datasets: [{
//                                                label: 'numero',
//                                                data: valori,
//                                                backgroundColor: [
//                                                    "#009b00",
//                                                    "#FF6384"
//                                                ],
//                                                borderColor: '#E9E9E9',
//                                                borderWidth: 3
//                                            }]
//                                    },
//                                    options: {
//                                        tooltips: {
//                                            callbacks: {
//                                                label: function (tooltipItem, data) {
//                                                    var allData = data.datasets[tooltipItem.datasetIndex].data;
//                                                    var tooltipLabel = data.labels[tooltipItem.index];
//                                                    var tooltipData = allData[tooltipItem.index];
//                                                    var total = 0;
//                                                    for (var i in allData) {
//                                                        total += allData[i];
//                                                    }
//                                                    var tooltipPercentage = Math.round((tooltipData / total) * 100);
//                                                    return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
//                                                }
//                                            }
//                                        }
//                                    }
//                                });
//                                /**/
//                                /* dati grafico motivo non acquisto */
//                                var labels2 = new Array();
//                                var valori2 = new Array();
//                                $.each(msg.datigraficono, function (index, value) {
//                                    labels2.push(index);
//                                    valori2.push(value);
//                                });
//                                /**/
//                                var ctx2 = document.getElementById("myChart2");
//                                var myChart2 = new Chart(ctx2, {
//                                    type: 'pie',
//                                    data: {
//                                        labels: labels2,
//                                        datasets: [{
//                                                label: 'numero',
//                                                data: valori2,
//                                                backgroundColor: [
//                                                    "#009b00",
//                                                    "#FF6384",
//                                                    "#36A2EB",
//                                                    "#FFCE56",
//                                                    "#ab11de",
//                                                    "#de7600",
//                                                    "#de0000",
//                                                    "#0039de"
//                                                ],
//                                                borderColor: '#E9E9E9',
//                                                borderWidth: 3
//                                            }]
//                                    },
//                                    options: {
//                                        tooltips: {
//                                            callbacks: {
//                                                label: function (tooltipItem, data) {
//                                                    var allData = data.datasets[tooltipItem.datasetIndex].data;
//                                                    var tooltipLabel = data.labels[tooltipItem.index];
//                                                    var tooltipData = allData[tooltipItem.index];
//                                                    var total = 0;
//                                                    for (var i in allData) {
//                                                        total += allData[i];
//                                                    }
//                                                    var tooltipPercentage = Math.round((tooltipData / total) * 100);
//                                                    return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
//                                                }
//                                            }
//                                        }
//                                    }
//                                });
//
//                                /* mese per mese */
//                                $('#graficoanno1').append("<br /><br /><hr style=\"width: 80%; float: left;\"><div class=\"chiudi\"></div>");
//                                for (var i = 1; i <= 12; i++) {
//                                    var labels = new Array();
//                                    var valori = new Array();
//                                    if (msg.datimese[i]) {
//                                        $.each(msg.datimese[i], function (index, value) {
//                                            labels.push(index);
//                                            valori.push(value);
//                                        });
//
//                                        var canvasm = "<div style=\"width: 70%; !important\"><canvas id=\"myChartm" + i + "\"></canvas></div>";
//                                        $('#graficoanno1').append("<br /><br />Dettaglio Appuntamenti per Mese <b>" + msg.mesi[i - 1] + " " + msg.anno + "</b> " + canvasm + "<br /><br />");
//                                        var ctxm = document.getElementById("myChartm" + i + "");
//                                        var myChart = new Chart(ctxm, {
//                                            type: 'bar',
//                                            data: {
//                                                labels: labels,
//                                                datasets: [{
//                                                        label: 'N°',
//                                                        data: valori,
//                                                        backgroundColor: 'rgba(46, 101, 161, 0.3)',
//                                                        borderColor: 'rgba(46, 101, 161,1)',
//                                                        borderWidth: 1
//                                                    }]
//                                            },
//                                            options: {
//                                                scales: {
//                                                    yAxes: [{
//                                                            ticks: {
//                                                                beginAtZero: true,
//                                                                min: 0
//                                                            }
//                                                        }]
//                                                }
//                                            }
//                                        });
//                                    }
//                                }
//                            }
//                            /* fine else */
//                        }
//                    });
//                });
//            }
//        });
//        /* form comparativo */
//        $.validator.messages.required = '';
//        $("#statisticheanno2").validate({
//            submitHandler: function () {
//                $("#submitstatisticheanno2").ready(function () {
//                    var datastring = $("#statisticheanno2 *").not(".nopost").serialize();
//                    $.ajax({
//                        type: "POST",
//                        url: "./statistiche.php",
//                        data: datastring + "&submit=submitstatisticheappuntamenti",
//                        dataType: "json",
//                        success: function (msg) {
//                            if (msg.msg === "ko") {
//                                alert(msg.msgko);
//                            } else {
//                                $('#statisticheanno2').trigger('reset');
//                                $('#idatelier2').val('');
//                                /* 
//                                 */
//                                /* dati grafico */
//                                /* dati grafico acquistato / non acquistato */
//                                var labels = new Array();
//                                var valori = new Array();
//                                $.each(msg.datigrafico, function (index, value) {
//                                    labels.push(index);
//                                    valori.push(value);
//                                });
//                                /**/
//                                var canvas = "<div style=\"width: 50%; !important\"><canvas id=\"myChartdx\"></canvas></div>";
//                                var canvas2 = "<div style=\"width: 50%; !important\"><canvas id=\"myChart2dx\"></canvas></div>";
//                                $('#graficoanno2').html("").append(msg.statistiche + canvas + msg.statistiche2 + canvas2);
//                                var ctx = document.getElementById("myChartdx");
//                                var myChart = new Chart(ctx, {
//                                    type: 'pie',
//                                    data: {
//                                        labels: labels,
//                                        datasets: [{
//                                                label: 'numero',
//                                                data: valori,
//                                                backgroundColor: [
//                                                    "#009b00",
//                                                    "#FF6384"
//                                                ],
//                                                borderColor: '#E9E9E9',
//                                                borderWidth: 3
//                                            }]
//                                    },
//                                    options: {
//                                        tooltips: {
//                                            callbacks: {
//                                                label: function (tooltipItem, data) {
//                                                    var allData = data.datasets[tooltipItem.datasetIndex].data;
//                                                    var tooltipLabel = data.labels[tooltipItem.index];
//                                                    var tooltipData = allData[tooltipItem.index];
//                                                    var total = 0;
//                                                    for (var i in allData) {
//                                                        total += allData[i];
//                                                    }
//                                                    var tooltipPercentage = Math.round((tooltipData / total) * 100);
//                                                    return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
//                                                }
//                                            }
//                                        }
//                                    }
//                                });
//                                /**/
//                                /* dati grafico motivo non acquisto */
//                                var labels2 = new Array();
//                                var valori2 = new Array();
//                                $.each(msg.datigraficono, function (index, value) {
//                                    labels2.push(index);
//                                    valori2.push(value);
//                                });
//                                /**/
//                                var ctx2 = document.getElementById("myChart2dx");
//                                var myChart2 = new Chart(ctx2, {
//                                    type: 'pie',
//                                    data: {
//                                        labels: labels2,
//                                        datasets: [{
//                                                label: 'numero',
//                                                data: valori2,
//                                                backgroundColor: [
//                                                    "#009b00",
//                                                    "#FF6384",
//                                                    "#36A2EB",
//                                                    "#FFCE56",
//                                                    "#ab11de",
//                                                    "#de7600",
//                                                    "#de0000",
//                                                    "#0039de"
//                                                ],
//                                                borderColor: '#E9E9E9',
//                                                borderWidth: 3
//                                            }]
//                                    },
//                                    options: {
//                                        tooltips: {
//                                            callbacks: {
//                                                label: function (tooltipItem, data) {
//                                                    var allData = data.datasets[tooltipItem.datasetIndex].data;
//                                                    var tooltipLabel = data.labels[tooltipItem.index];
//                                                    var tooltipData = allData[tooltipItem.index];
//                                                    var total = 0;
//                                                    for (var i in allData) {
//                                                        total += allData[i];
//                                                    }
//                                                    var tooltipPercentage = Math.round((tooltipData / total) * 100);
//                                                    return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
//                                                }
//                                            }
//                                        }
//                                    }
//                                });
//
//                                /* mese per mese */
//                                $('#graficoanno2').append("<br /><br /><hr style=\"width: 80%; float: left;\"><div class=\"chiudi\"></div>");
//                                for (var i = 1; i <= 12; i++) {
//                                    var labels = new Array();
//                                    var valori = new Array();
//                                    if (msg.datimese[i]) {
//                                        $.each(msg.datimese[i], function (index, value) {
//                                            labels.push(index);
//                                            valori.push(value);
//                                        });
//
//                                        var canvasm = "<div style=\"width: 70%; !important\"><canvas id=\"myChartmdx" + i + "\"></canvas></div>";
//                                        $('#graficoanno2').append("<br /><br />Dettaglio Appuntamenti per Mese <b>" + msg.mesi[i - 1] + " " + msg.anno + "</b> " + canvasm + "<br /><br />");
//                                        var ctxm = document.getElementById("myChartmdx" + i + "");
//                                        var myChart = new Chart(ctxm, {
//                                            type: 'bar',
//                                            data: {
//                                                labels: labels,
//                                                datasets: [{
//                                                        label: 'N°',
//                                                        data: valori,
//                                                        backgroundColor: 'rgba(46, 101, 161, 0.3)',
//                                                        borderColor: 'rgba(46, 101, 161,1)',
//                                                        borderWidth: 1
//                                                    }]
//                                            },
//                                            options: {
//                                                scales: {
//                                                    yAxes: [{
//                                                            ticks: {
//                                                                beginAtZero: true,
//                                                                min: 0
//                                                            }
//                                                        }]
//                                                }
//                                            }
//                                        });
//                                    }
//                                }
//                            }
//                            /* fine else */
//                        }
//                    });
//                });
//            }
//        });
//    });
//}

function mostraAppuntamenti(anno, idatelier) {
    $('.showcont').show().load('./form/form-statisticheappuntamento.php', function () {
        /**/
        if (anno && idatelier) {
            var idatelier_csv = idatelier;
            $.ajax({
                type: "POST",
                url: "./statistiche.php",
                data: "anno=" + anno + "&idatelier=" + idatelier + "&submit=submitstatisticheappuntamenti",
                dataType: "json",
                success: function (msg) {
                    if (msg.msg === "ko") {
                        alert(msg.msgko);
                    } else {
                        /* dati grafico acquistato / non acquistato */
                        var labels = new Array();
                        var valori = new Array();
                        $.each(msg.datigrafico, function (index, value) {
                            labels.push(index);
                            valori.push(value);
                        });
                        /**/
                        var canvas = "<div style=\"width: 50%; !important\"><canvas id=\"myChart\"></canvas></div>";
                        var canvas2 = "<div style=\"width: 50%; !important\"><canvas id=\"myChart2\"></canvas></div>";
                        $('#graficoanno1').html("").append(msg.statistiche + canvas + msg.statistiche2 + canvas2);
                        var ctx = document.getElementById("myChart");
                        var myChart = new Chart(ctx, {
                            type: 'pie',
                            data: {
                                labels: labels,
                                datasets: [{
                                        label: 'numero',
                                        data: valori,
                                        backgroundColor: [
                                            "#009b00",
                                            "#FF6384"
                                        ],
                                        borderColor: '#E9E9E9',
                                        borderWidth: 3
                                    }]
                            },
                            options: {
                                tooltips: {
                                    callbacks: {
                                        label: function (tooltipItem, data) {
                                            var allData = data.datasets[tooltipItem.datasetIndex].data;
                                            var tooltipLabel = data.labels[tooltipItem.index];
                                            var tooltipData = allData[tooltipItem.index];
                                            var total = 0;
                                            for (var i in allData) {
                                                total += allData[i];
                                            }
                                            var tooltipPercentage = Math.round((tooltipData / total) * 100);
                                            return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
                                        }
                                    }
                                }
                            }
                        });
                        /**/
                        /* dati grafico motivo non acquisto */
                        var labels2 = new Array();
                        var valori2 = new Array();
                        $.each(msg.datigraficono, function (index, value) {
                            labels2.push(index);
                            valori2.push(value);
                        });
                        /**/
                        var ctx2 = document.getElementById("myChart2");
                        var myChart2 = new Chart(ctx2, {
                            type: 'pie',
                            data: {
                                labels: labels2,
                                datasets: [{
                                        label: 'numero',
                                        data: valori2,
                                        backgroundColor: [
                                            "#009b00",
                                            "#FF6384",
                                            "#36A2EB",
                                            "#FFCE56",
                                            "#ab11de",
                                            "#de7600",
                                            "#de0000",
                                            "#0039de"
                                        ],
                                        borderColor: '#E9E9E9',
                                        borderWidth: 3
                                    }]
                            },
                            options: {
                                tooltips: {
                                    callbacks: {
                                        label: function (tooltipItem, data) {
                                            var allData = data.datasets[tooltipItem.datasetIndex].data;
                                            var tooltipLabel = data.labels[tooltipItem.index];
                                            var tooltipData = allData[tooltipItem.index];
                                            var total = 0;
                                            for (var i in allData) {
                                                total += allData[i];
                                            }
                                            var tooltipPercentage = Math.round((tooltipData / total) * 100);
                                            return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
                                        }
                                    }
                                }
                            }
                        });

                        /* mese per mese */
                        $('#graficoanno1').append("<br /><br /><hr style=\"width: 80%; float: left;\"><div class=\"chiudi\"></div>");
                        for (var i = 1; i <= 12; i++) {
                            var labels = new Array();
                            var valori = new Array();
                            if (msg.datimese[i]) {
                                $.each(msg.datimese[i], function (index, value) {
                                    labels.push(index);
                                    valori.push(value);
                                });

                                var canvasm = "<div style=\"width: 70%; !important\"><canvas id=\"myChartm" + i + "\"></canvas></div>";
                                $('#graficoanno1').append("<br /><br />Dettaglio Appuntamenti per Mese <b>" + msg.mesi[i - 1] + " " + msg.anno + "</b> " + canvasm + "<br /><br />");
                                var ctxm = document.getElementById("myChartm" + i + "");
                                var myChart = new Chart(ctxm, {
                                    type: 'bar',
                                    data: {
                                        labels: labels,
                                        datasets: [{
                                                label: 'N°',
                                                data: valori,
                                                backgroundColor: 'rgba(46, 101, 161, 0.3)',
                                                borderColor: 'rgba(46, 101, 161,1)',
                                                borderWidth: 1
                                            }]
                                    },
                                    options: {
                                        scales: {
                                            yAxes: [{
                                                    ticks: {
                                                        beginAtZero: true,
                                                        min: 0
                                                    }
                                                }]
                                        }
                                    }
                                });
                            }
                        }

                    }
                    /* fine else */
                }
            });
        } // altro anno
        $.validator.messages.required = '';
        $("#statisticheanno").validate({
            submitHandler: function () {
                $("#submitstatisticheanno").ready(function () {
                    var datastring = $("#statisticheanno *").not(".nopost").serialize();
                    var anno_csv = $("#statisticheanno input[name='anno']").val();
                    var idatelier_csv = $("#statisticheanno #idatelier").val();
                    $.ajax({
                        type: "POST",
                        url: "./statistiche.php",
                        data: datastring + "&submit=submitstatisticheappuntamenti",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#statisticheanno').trigger('reset');
                                $('#idatelier').val('');
                                /*
                                 */
                                /* dati grafico */
                                /* dati grafico acquistato / non acquistato */
                                var labels = new Array();
                                var valori = new Array();
                                $.each(msg.datigrafico, function (index, value) {
                                    labels.push(index);
                                    valori.push(value);
                                });
                                /**/
                                var canvas = "<div style=\"width: 50%; !important\"><canvas id=\"myChart\"></canvas></div>";
                                var canvas2 = "<div style=\"width: 50%; !important\"><canvas id=\"myChart2\"></canvas></div>";
                                $('#graficoanno1').html("").append(msg.statistiche + canvas + msg.statistiche2 + canvas2);
                                var ctx = document.getElementById("myChart");
                                var myChart = new Chart(ctx, {
                                    type: 'pie',
                                    data: {
                                        labels: labels,
                                        datasets: [{
                                                label: 'numero',
                                                data: valori,
                                                backgroundColor: [
                                                    "#009b00",
                                                    "#FF6384"
                                                ],
                                                borderColor: '#E9E9E9',
                                                borderWidth: 3
                                            }]
                                    },
                                    options: {
                                        tooltips: {
                                            callbacks: {
                                                label: function (tooltipItem, data) {
                                                    var allData = data.datasets[tooltipItem.datasetIndex].data;
                                                    var tooltipLabel = data.labels[tooltipItem.index];
                                                    var tooltipData = allData[tooltipItem.index];
                                                    var total = 0;
                                                    for (var i in allData) {
                                                        total += allData[i];
                                                    }
                                                    var tooltipPercentage = Math.round((tooltipData / total) * 100);
                                                    return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
                                                }
                                            }
                                        }
                                    }
                                });
                                /**/
                                /* dati grafico motivo non acquisto */
                                var labels2 = new Array();
                                var valori2 = new Array();
                                $.each(msg.datigraficono, function (index, value) {
                                    labels2.push(index);
                                    valori2.push(value);
                                });
                                /**/
                                var ctx2 = document.getElementById("myChart2");
                                var myChart2 = new Chart(ctx2, {
                                    type: 'pie',
                                    data: {
                                        labels: labels2,
                                        datasets: [{
                                                label: 'numero',
                                                data: valori2,
                                                backgroundColor: [
                                                    "#009b00",
                                                    "#FF6384",
                                                    "#36A2EB",
                                                    "#FFCE56",
                                                    "#ab11de",
                                                    "#de7600",
                                                    "#de0000",
                                                    "#0039de"
                                                ],
                                                borderColor: '#E9E9E9',
                                                borderWidth: 3
                                            }]
                                    },
                                    options: {
                                        tooltips: {
                                            callbacks: {
                                                label: function (tooltipItem, data) {
                                                    var allData = data.datasets[tooltipItem.datasetIndex].data;
                                                    var tooltipLabel = data.labels[tooltipItem.index];
                                                    var tooltipData = allData[tooltipItem.index];
                                                    var total = 0;
                                                    for (var i in allData) {
                                                        total += allData[i];
                                                    }
                                                    var tooltipPercentage = Math.round((tooltipData / total) * 100);
                                                    return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
                                                }
                                            }
                                        }
                                    }
                                });

                                /* mese per mese */
                                $('#graficoanno1').append("<br /><br /><hr style=\"width: 80%; float: left;\"><div class=\"chiudi\"></div>");
                                if (msg.datimese != '') {
                                    $('#btn-esporta').prop('href', "./statistiche.php?submit=submitstatisticheappuntamenti&csv=1&anno=" + anno_csv + "&idatelier=" + idatelier_csv);
                                    $('#btn-esporta').show();
                                } else {
                                    $('#btn-esporta').hide();
                                }
                                for (var i = 1; i <= 12; i++) {
                                    var labels = new Array();
                                    var valori = new Array();
                                    if (msg.datimese[i]) {
                                        $.each(msg.datimese[i], function (index, value) {
                                            labels.push(index);
                                            valori.push(value);
                                        });

                                        var canvasm = "<div style=\"width: 70%; !important\"><canvas id=\"myChartm" + i + "\"></canvas></div>";
                                        $('#graficoanno1').append("<br /><br />Dettaglio Appuntamenti per Mese <b>" + msg.mesi[i - 1] + " " + msg.anno + "</b> " + canvasm + "<br /><br />");
                                        var ctxm = document.getElementById("myChartm" + i + "");
                                        var myChart = new Chart(ctxm, {
                                            type: 'bar',
                                            data: {
                                                labels: labels,
                                                datasets: [{
                                                        label: 'N°',
                                                        data: valori,
                                                        backgroundColor: 'rgba(46, 101, 161, 0.3)',
                                                        borderColor: 'rgba(46, 101, 161,1)',
                                                        borderWidth: 1
                                                    }]
                                            },
                                            options: {
                                                scales: {
                                                    yAxes: [{
                                                            ticks: {
                                                                beginAtZero: true,
                                                                min: 0
                                                            }
                                                        }]
                                                }
                                            }
                                        });
                                    }
                                }
                            }
                            /* fine else */
                        }
                    });
                });
            }
        });
        /* form comparativo */
        $.validator.messages.required = '';
        $("#statisticheanno2").validate({
            submitHandler: function () {
                $("#submitstatisticheanno2").ready(function () {
                    var datastring = $("#statisticheanno2 *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./statistiche.php",
                        data: datastring + "&submit=submitstatisticheappuntamenti",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#statisticheanno2').trigger('reset');
                                $('#idatelier2').val('');
                                /* 
                                 */
                                /* dati grafico */
                                /* dati grafico acquistato / non acquistato */
                                var labels = new Array();
                                var valori = new Array();
                                $.each(msg.datigrafico, function (index, value) {
                                    labels.push(index);
                                    valori.push(value);
                                });
                                /**/
                                var canvas = "<div style=\"width: 50%; !important\"><canvas id=\"myChartdx\"></canvas></div>";
                                var canvas2 = "<div style=\"width: 50%; !important\"><canvas id=\"myChart2dx\"></canvas></div>";
                                $('#graficoanno2').html("").append(msg.statistiche + canvas + msg.statistiche2 + canvas2);
                                var ctx = document.getElementById("myChartdx");
                                var myChart = new Chart(ctx, {
                                    type: 'pie',
                                    data: {
                                        labels: labels,
                                        datasets: [{
                                                label: 'numero',
                                                data: valori,
                                                backgroundColor: [
                                                    "#009b00",
                                                    "#FF6384"
                                                ],
                                                borderColor: '#E9E9E9',
                                                borderWidth: 3
                                            }]
                                    },
                                    options: {
                                        tooltips: {
                                            callbacks: {
                                                label: function (tooltipItem, data) {
                                                    var allData = data.datasets[tooltipItem.datasetIndex].data;
                                                    var tooltipLabel = data.labels[tooltipItem.index];
                                                    var tooltipData = allData[tooltipItem.index];
                                                    var total = 0;
                                                    for (var i in allData) {
                                                        total += allData[i];
                                                    }
                                                    var tooltipPercentage = Math.round((tooltipData / total) * 100);
                                                    return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
                                                }
                                            }
                                        }
                                    }
                                });
                                /**/
                                /* dati grafico motivo non acquisto */
                                var labels2 = new Array();
                                var valori2 = new Array();
                                $.each(msg.datigraficono, function (index, value) {
                                    labels2.push(index);
                                    valori2.push(value);
                                });
                                /**/
                                var ctx2 = document.getElementById("myChart2dx");
                                var myChart2 = new Chart(ctx2, {
                                    type: 'pie',
                                    data: {
                                        labels: labels2,
                                        datasets: [{
                                                label: 'numero',
                                                data: valori2,
                                                backgroundColor: [
                                                    "#009b00",
                                                    "#FF6384",
                                                    "#36A2EB",
                                                    "#FFCE56",
                                                    "#ab11de",
                                                    "#de7600",
                                                    "#de0000",
                                                    "#0039de"
                                                ],
                                                borderColor: '#E9E9E9',
                                                borderWidth: 3
                                            }]
                                    },
                                    options: {
                                        tooltips: {
                                            callbacks: {
                                                label: function (tooltipItem, data) {
                                                    var allData = data.datasets[tooltipItem.datasetIndex].data;
                                                    var tooltipLabel = data.labels[tooltipItem.index];
                                                    var tooltipData = allData[tooltipItem.index];
                                                    var total = 0;
                                                    for (var i in allData) {
                                                        total += allData[i];
                                                    }
                                                    var tooltipPercentage = Math.round((tooltipData / total) * 100);
                                                    return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
                                                }
                                            }
                                        }
                                    }
                                });

                                /* mese per mese */
                                $('#graficoanno2').append("<br /><br /><hr style=\"width: 80%; float: left;\"><div class=\"chiudi\"></div>");
                                for (var i = 1; i <= 12; i++) {
                                    var labels = new Array();
                                    var valori = new Array();
                                    if (msg.datimese[i]) {
                                        $.each(msg.datimese[i], function (index, value) {
                                            labels.push(index);
                                            valori.push(value);
                                        });

                                        var canvasm = "<div style=\"width: 70%; !important\"><canvas id=\"myChartmdx" + i + "\"></canvas></div>";
                                        $('#graficoanno2').append("<br /><br />Dettaglio Appuntamenti per Mese <b>" + msg.mesi[i - 1] + " " + msg.anno + "</b> " + canvasm + "<br /><br />");
                                        var ctxm = document.getElementById("myChartmdx" + i + "");
                                        var myChart = new Chart(ctxm, {
                                            type: 'bar',
                                            data: {
                                                labels: labels,
                                                datasets: [{
                                                        label: 'N°',
                                                        data: valori,
                                                        backgroundColor: 'rgba(46, 101, 161, 0.3)',
                                                        borderColor: 'rgba(46, 101, 161,1)',
                                                        borderWidth: 1
                                                    }]
                                            },
                                            options: {
                                                scales: {
                                                    yAxes: [{
                                                            ticks: {
                                                                beginAtZero: true,
                                                                min: 0
                                                            }
                                                        }]
                                                }
                                            }
                                        });
                                    }
                                }
                            }
                            /* fine else */
                        }
                    });
                });
            }
        });
    });
}

function mostraProvenienza(anno, idatelier) {
    $('.showcont').show().load('./form/form-statisticheprovenienza.php', function () {
        /**/
        if (anno && idatelier) {
            $.ajax({
                type: "POST",
                url: "./statistiche.php",
                data: "anno=" + anno + "&idatelier=" + idatelier + "&submit=submitstatisticheprovenienza",
                dataType: "json",
                success: function (msg) {
                    if (msg.msg === "ko") {
                        alert(msg.msgko);
                    } else {
                        var canvas2 = "<div style=\"width: 50%; !important\"><canvas id=\"myChart2\"></canvas></div>";
                        $('#graficoanno1').html("").append(msg.statistiche2 + canvas2);
                        /**/
                        /* dati grafico motivo non acquisto */
                        var labels2 = new Array();
                        var valori2 = new Array();
                        $.each(msg.datiprovenienza, function (index, value) {
                            labels2.push(index);
                            valori2.push(value);
                        });
                        /**/
                        var ctx2 = document.getElementById("myChart2");
                        var myChart2 = new Chart(ctx2, {
                            type: 'pie',
                            data: {
                                labels: labels2,
                                datasets: [{
                                        label: 'numero',
                                        data: valori2,
                                        backgroundColor: [
                                            "#009b00",
                                            "#FF6384",
                                            "#36A2EB",
                                            "#FFCE56",
                                            "#ab11de",
                                            "#de7600",
                                            "#de0000",
                                            "#0039de"
                                        ],
                                        borderColor: '#E9E9E9',
                                        borderWidth: 3
                                    }]
                            },
                            options: {
                                tooltips: {
                                    callbacks: {
                                        label: function (tooltipItem, data) {
                                            var allData = data.datasets[tooltipItem.datasetIndex].data;
                                            var tooltipLabel = data.labels[tooltipItem.index];
                                            var tooltipData = allData[tooltipItem.index];
                                            var total = 0;
                                            for (var i in allData) {
                                                total += allData[i];
                                            }
                                            var tooltipPercentage = Math.round((tooltipData / total) * 100);
                                            return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
                                        }
                                    }
                                }
                            }
                        });

                        /* mese per mese */
                        $('#graficoanno1').append("<br /><br /><hr style=\"width: 80%; float: left;\"><div class=\"chiudi\"></div>");
                        for (var i = 1; i <= 12; i++) {
                            var labels = new Array();
                            var valori = new Array();
                            if (msg.datimese[i]) {
                                $.each(msg.datimese[i], function (index, value) {
                                    labels.push(index);
                                    valori.push(value);
                                });

                                var canvasm = "<div style=\"width: 70%; !important\"><canvas id=\"myChartm" + i + "\"></canvas></div>";
                                $('#graficoanno1').append("<br /><br />Dettaglio Provenienza per Mese <b>" + msg.mesi[i - 1] + " " + anno + "</b> " + canvasm + "<br /><br />");
                                var ctxm = document.getElementById("myChartm" + i + "");
                                var myChart = new Chart(ctxm, {
                                    type: 'bar',
                                    data: {
                                        labels: labels,
                                        datasets: [{
                                                label: 'N°',
                                                data: valori,
                                                backgroundColor: 'rgba(46, 101, 161, 0.3)',
                                                borderColor: 'rgba(46, 101, 161,1)',
                                                borderWidth: 1
                                            }]
                                    },
                                    options: {
                                        scales: {
                                            yAxes: [{
                                                    ticks: {
                                                        beginAtZero: true,
                                                        min: 0
                                                    }
                                                }]
                                        }
                                    }
                                });
                            }
                        }

                    }
                    /* fine else */
                }
            });
        } // altro anno
        $.validator.messages.required = '';
        $("#statisticheanno").validate({
            submitHandler: function () {
                $("#submitstatisticheanno").ready(function () {
                    var datastring = $("#statisticheanno *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./statistiche.php",
                        data: datastring + "&submit=submitstatisticheprovenienza",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#statisticheanno').trigger('reset');
                                $('#idatelier').val('');
                                var canvas2 = "<div style=\"width: 50%; !important\"><canvas id=\"myChart2\"></canvas></div>";
                                $('#graficoanno1').html("").append(msg.statistiche2 + canvas2);
                                /**/
                                /* dati grafico motivo non acquisto */
                                var labels2 = new Array();
                                var valori2 = new Array();
                                $.each(msg.datiprovenienza, function (index, value) {
                                    labels2.push(index);
                                    valori2.push(value);
                                });
                                /**/
                                var ctx2 = document.getElementById("myChart2");
                                var myChart2 = new Chart(ctx2, {
                                    type: 'pie',
                                    data: {
                                        labels: labels2,
                                        datasets: [{
                                                label: 'numero',
                                                data: valori2,
                                                backgroundColor: [
                                                    "#009b00",
                                                    "#FF6384",
                                                    "#36A2EB",
                                                    "#FFCE56",
                                                    "#ab11de",
                                                    "#de7600",
                                                    "#de0000",
                                                    "#0039de"
                                                ],
                                                borderColor: '#E9E9E9',
                                                borderWidth: 3
                                            }]
                                    },
                                    options: {
                                        tooltips: {
                                            callbacks: {
                                                label: function (tooltipItem, data) {
                                                    var allData = data.datasets[tooltipItem.datasetIndex].data;
                                                    var tooltipLabel = data.labels[tooltipItem.index];
                                                    var tooltipData = allData[tooltipItem.index];
                                                    var total = 0;
                                                    for (var i in allData) {
                                                        total += allData[i];
                                                    }
                                                    var tooltipPercentage = Math.round((tooltipData / total) * 100);
                                                    return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
                                                }
                                            }
                                        }
                                    }
                                });

                                /* mese per mese */
                                $('#graficoanno1').append("<br /><br /><hr style=\"width: 80%; float: left;\"><div class=\"chiudi\"></div>");
                                for (var i = 1; i <= 12; i++) {
                                    var labels = new Array();
                                    var valori = new Array();
                                    if (msg.datimese[i]) {
                                        $.each(msg.datimese[i], function (index, value) {
                                            labels.push(index);
                                            valori.push(value);
                                        });

                                        var canvasm = "<div style=\"width: 70%; !important\"><canvas id=\"myChartm" + i + "\"></canvas></div>";
                                        $('#graficoanno1').append("<br /><br />Dettaglio Provenienza per Mese <b>" + msg.mesi[i - 1] + " " + msg.anno + "</b> " + canvasm + "<br /><br />");
                                        var ctxm = document.getElementById("myChartm" + i + "");
                                        var myChart = new Chart(ctxm, {
                                            type: 'bar',
                                            data: {
                                                labels: labels,
                                                datasets: [{
                                                        label: 'N°',
                                                        data: valori,
                                                        backgroundColor: 'rgba(46, 101, 161, 0.3)',
                                                        borderColor: 'rgba(46, 101, 161,1)',
                                                        borderWidth: 1
                                                    }]
                                            },
                                            options: {
                                                scales: {
                                                    yAxes: [{
                                                            ticks: {
                                                                beginAtZero: true,
                                                                min: 0
                                                            }
                                                        }]
                                                }
                                            }
                                        });
                                    }
                                }

                            }
                            /* fine else */
                        }
                    });
                });
            }
        });
        /* form comparativo */
        $.validator.messages.required = '';
        $("#statisticheanno2").validate({
            submitHandler: function () {
                $("#submitstatisticheanno2").ready(function () {
                    var datastring = $("#statisticheanno2 *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./statistiche.php",
                        data: datastring + "&submit=submitstatisticheprovenienza",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#statisticheanno2').trigger('reset');
                                $('#idatelier2').val('');
                                var canvas2 = "<div style=\"width: 50%; !important\"><canvas id=\"myChartdx\"></canvas></div>";
                                $('#graficoanno2').html("").append(msg.statistiche2 + canvas2);
                                /**/
                                /* dati grafico motivo non acquisto */
                                var labels2 = new Array();
                                var valori2 = new Array();
                                $.each(msg.datiprovenienza, function (index, value) {
                                    labels2.push(index);
                                    valori2.push(value);
                                });
                                /**/
                                var ctx2 = document.getElementById("myChartdx");
                                var myChart2 = new Chart(ctx2, {
                                    type: 'pie',
                                    data: {
                                        labels: labels2,
                                        datasets: [{
                                                label: 'numero',
                                                data: valori2,
                                                backgroundColor: [
                                                    "#009b00",
                                                    "#FF6384",
                                                    "#36A2EB",
                                                    "#FFCE56",
                                                    "#ab11de",
                                                    "#de7600",
                                                    "#de0000",
                                                    "#0039de"
                                                ],
                                                borderColor: '#E9E9E9',
                                                borderWidth: 3
                                            }]
                                    },
                                    options: {
                                        tooltips: {
                                            callbacks: {
                                                label: function (tooltipItem, data) {
                                                    var allData = data.datasets[tooltipItem.datasetIndex].data;
                                                    var tooltipLabel = data.labels[tooltipItem.index];
                                                    var tooltipData = allData[tooltipItem.index];
                                                    var total = 0;
                                                    for (var i in allData) {
                                                        total += allData[i];
                                                    }
                                                    var tooltipPercentage = Math.round((tooltipData / total) * 100);
                                                    return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
                                                }
                                            }
                                        }
                                    }
                                });

                                /* mese per mese */
                                $('#graficoanno2').append("<br /><br /><hr style=\"width: 80%; float: left;\"><div class=\"chiudi\"></div>");
                                for (var i = 1; i <= 12; i++) {
                                    var labels = new Array();
                                    var valori = new Array();
                                    if (msg.datimese[i]) {
                                        $.each(msg.datimese[i], function (index, value) {
                                            labels.push(index);
                                            valori.push(value);
                                        });

                                        var canvasm = "<div style=\"width: 70%; !important\"><canvas id=\"myChartdxm" + i + "\"></canvas></div>";
                                        $('#graficoanno2').append("<br /><br />Dettaglio Provenienza per Mese <b>" + msg.mesi[i - 1] + " " + msg.anno + "</b> " + canvasm + "<br /><br />");
                                        var ctxm = document.getElementById("myChartdxm" + i + "");
                                        var myChart = new Chart(ctxm, {
                                            type: 'bar',
                                            data: {
                                                labels: labels,
                                                datasets: [{
                                                        label: 'N°',
                                                        data: valori,
                                                        backgroundColor: 'rgba(46, 101, 161, 0.3)',
                                                        borderColor: 'rgba(46, 101, 161,1)',
                                                        borderWidth: 1
                                                    }]
                                            },
                                            options: {
                                                scales: {
                                                    yAxes: [{
                                                            ticks: {
                                                                beginAtZero: true,
                                                                min: 0
                                                            }
                                                        }]
                                                }
                                            }
                                        });
                                    }
                                }

                            }
                            /* fine else */
                        }
                    });
                });
            }
        });
    });
}
function mostraAbiti(anno, idatelier) {
    $('.showcont').show().load('./form/form-statisticheabiti.php', function () {
        /**/
        if (anno && idatelier) {
            $.ajax({
                type: "POST",
                url: "./statistiche.php",
                data: "anno=" + anno + "&idatelier=" + idatelier + "&submit=submitstatisticheabiti",
                dataType: "json",
                success: function (msg) {
                    if (msg.msg === "ko") {
                        alert(msg.msgko);
                    } else {
                        var canvas2 = "<div style=\"width: 50%; !important\"><canvas id=\"myChart2\"></canvas></div>";
                        $('#graficoanno1').html("").append(msg.statistiche2 + canvas2);
                        /**/
                        /* dati grafico abiti */
                        var labels2 = new Array();
                        var valori2 = new Array();
                        $.each(msg.datiprovenienza, function (index, value) {
                            labels2.push(index);
                            valori2.push(value);
                        });
                        /**/
                        var ctx2 = document.getElementById("myChart2");
                        var myChart2 = new Chart(ctx2, {
                            type: 'pie',
                            data: {
                                labels: labels2,
                                datasets: [{
                                        label: 'numero',
                                        data: valori2,
                                        backgroundColor: [
                                            "#009b00",
                                            "#FF6384",
                                            "#36A2EB",
                                            "#FFCE56",
                                            "#ab11de",
                                            "#de7600",
                                            "#de0000",
                                            "#0039de"
                                        ],
                                        borderColor: '#E9E9E9',
                                        borderWidth: 3
                                    }]
                            },
                            options: {
                                tooltips: {
                                    callbacks: {
                                        label: function (tooltipItem, data) {
                                            var allData = data.datasets[tooltipItem.datasetIndex].data;
                                            var tooltipLabel = data.labels[tooltipItem.index];
                                            var tooltipData = allData[tooltipItem.index];
                                            var total = 0;
                                            for (var i in allData) {
                                                total += allData[i];
                                            }
                                            var tooltipPercentage = Math.round((tooltipData / total) * 100);
                                            return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
                                        }
                                    }
                                }
                            }
                        });

                        /* mese per mese */
                        $('#graficoanno1').append("<br /><br /><hr style=\"width: 80%; float: left;\"><div class=\"chiudi\"></div>");
                        for (var i = 1; i <= 12; i++) {
                            var labels = new Array();
                            var valori = new Array();
                            if (msg.datimese[i]) {
                                $.each(msg.datimese[i], function (index, value) {
                                    labels.push(index);
                                    valori.push(value);
                                });

                                var canvasm = "<div style=\"width: 70%; !important\"><canvas id=\"myChartm" + i + "\"></canvas></div>";
                                $('#graficoanno1').append("<br /><br />Dettaglio Abiti venduti per Mese <b>" + msg.mesi[i - 1] + " " + msg.anno + "</b> " + canvasm + "<br /><br />");
                                var ctxm = document.getElementById("myChartm" + i + "");
                                var myChart = new Chart(ctxm, {
                                    type: 'bar',
                                    data: {
                                        labels: labels,
                                        datasets: [{
                                                label: 'N°',
                                                data: valori,
                                                backgroundColor: 'rgba(46, 101, 161, 0.3)',
                                                borderColor: 'rgba(46, 101, 161,1)',
                                                borderWidth: 1
                                            }]
                                    },
                                    options: {
                                        scales: {
                                            yAxes: [{
                                                    ticks: {
                                                        beginAtZero: true,
                                                        min: 0
                                                    }
                                                }]
                                        }
                                    }
                                });
                            }
                        }

                    }
                    /* fine else */
                }
            });
        } // altro anno
        $.validator.messages.required = '';
        $("#statisticheanno").validate({
            submitHandler: function () {
                $("#submitstatisticheanno").ready(function () {
                    var datastring = $("#statisticheanno *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./statistiche.php",
                        data: datastring + "&submit=submitstatisticheabiti",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#statisticheanno').trigger('reset');
                                $('#idatelier').val('');
                                var canvas2 = "<div style=\"width: 50%; !important\"><canvas id=\"myChart2\"></canvas></div>";
                                $('#graficoanno1').html("").append(msg.statistiche2 + canvas2);
                                /**/
                                /* dati grafico abiti */
                                var labels2 = new Array();
                                var valori2 = new Array();
                                $.each(msg.datiprovenienza, function (index, value) {
                                    labels2.push(index);
                                    valori2.push(value);
                                });
                                /**/
                                var ctx2 = document.getElementById("myChart2");
                                var myChart2 = new Chart(ctx2, {
                                    type: 'pie',
                                    data: {
                                        labels: labels2,
                                        datasets: [{
                                                label: 'numero',
                                                data: valori2,
                                                backgroundColor: [
                                                    "#009b00",
                                                    "#FF6384",
                                                    "#36A2EB",
                                                    "#FFCE56",
                                                    "#ab11de",
                                                    "#de7600",
                                                    "#de0000",
                                                    "#0039de"
                                                ],
                                                borderColor: '#E9E9E9',
                                                borderWidth: 3
                                            }]
                                    },
                                    options: {
                                        tooltips: {
                                            callbacks: {
                                                label: function (tooltipItem, data) {
                                                    var allData = data.datasets[tooltipItem.datasetIndex].data;
                                                    var tooltipLabel = data.labels[tooltipItem.index];
                                                    var tooltipData = allData[tooltipItem.index];
                                                    var total = 0;
                                                    for (var i in allData) {
                                                        total += allData[i];
                                                    }
                                                    var tooltipPercentage = Math.round((tooltipData / total) * 100);
                                                    return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
                                                }
                                            }
                                        }
                                    }
                                });

                                /* mese per mese */
                                $('#graficoanno1').append("<br /><br /><hr style=\"width: 80%; float: left;\"><div class=\"chiudi\"></div>");
                                for (var i = 1; i <= 12; i++) {
                                    var labels = new Array();
                                    var valori = new Array();
                                    if (msg.datimese[i]) {
                                        $.each(msg.datimese[i], function (index, value) {
                                            labels.push(index);
                                            valori.push(value);
                                        });

                                        var canvasm = "<div style=\"width: 70%; !important\"><canvas id=\"myChartm" + i + "\"></canvas></div>";
                                        $('#graficoanno1').append("<br /><br />Dettaglio Abiti venduti per Mese <b>" + msg.mesi[i - 1] + " " + msg.anno + "</b> " + canvasm + "<br /><br />");
                                        var ctxm = document.getElementById("myChartm" + i + "");
                                        var myChart = new Chart(ctxm, {
                                            type: 'bar',
                                            data: {
                                                labels: labels,
                                                datasets: [{
                                                        label: 'N°',
                                                        data: valori,
                                                        backgroundColor: 'rgba(46, 101, 161, 0.3)',
                                                        borderColor: 'rgba(46, 101, 161,1)',
                                                        borderWidth: 1
                                                    }]
                                            },
                                            options: {
                                                scales: {
                                                    yAxes: [{
                                                            ticks: {
                                                                beginAtZero: true,
                                                                min: 0
                                                            }
                                                        }]
                                                }
                                            }
                                        });
                                    }
                                }

                            }
                            /* fine else */
                        }
                    });
                });
            }
        });
        /* form comparativo */
        $.validator.messages.required = '';
        $("#statisticheanno2").validate({
            submitHandler: function () {
                $("#submitstatisticheanno2").ready(function () {
                    var datastring = $("#statisticheanno2 *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./statistiche.php",
                        data: datastring + "&submit=submitstatisticheabiti",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#statisticheanno2').trigger('reset');
                                $('#idatelier2').val('');
                                var canvas2 = "<div style=\"width: 50%; !important\"><canvas id=\"myChartdx\"></canvas></div>";
                                $('#graficoanno2').html("").append(msg.statistiche2 + canvas2);
                                /**/
                                /* dati grafico abiti */
                                var labels2 = new Array();
                                var valori2 = new Array();
                                $.each(msg.datiprovenienza, function (index, value) {
                                    labels2.push(index);
                                    valori2.push(value);
                                });
                                /**/
                                var ctx2 = document.getElementById("myChartdx");
                                var myChart2 = new Chart(ctx2, {
                                    type: 'pie',
                                    data: {
                                        labels: labels2,
                                        datasets: [{
                                                label: 'numero',
                                                data: valori2,
                                                backgroundColor: [
                                                    "#009b00",
                                                    "#FF6384",
                                                    "#36A2EB",
                                                    "#FFCE56",
                                                    "#ab11de",
                                                    "#de7600",
                                                    "#de0000",
                                                    "#0039de"
                                                ],
                                                borderColor: '#E9E9E9',
                                                borderWidth: 3
                                            }]
                                    },
                                    options: {
                                        tooltips: {
                                            callbacks: {
                                                label: function (tooltipItem, data) {
                                                    var allData = data.datasets[tooltipItem.datasetIndex].data;
                                                    var tooltipLabel = data.labels[tooltipItem.index];
                                                    var tooltipData = allData[tooltipItem.index];
                                                    var total = 0;
                                                    for (var i in allData) {
                                                        total += allData[i];
                                                    }
                                                    var tooltipPercentage = Math.round((tooltipData / total) * 100);
                                                    return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
                                                }
                                            }
                                        }
                                    }
                                });

                                /* mese per mese */
                                $('#graficoanno2').append("<br /><br /><hr style=\"width: 80%; float: left;\"><div class=\"chiudi\"></div>");
                                for (var i = 1; i <= 12; i++) {
                                    var labels = new Array();
                                    var valori = new Array();
                                    if (msg.datimese[i]) {
                                        $.each(msg.datimese[i], function (index, value) {
                                            labels.push(index);
                                            valori.push(value);
                                        });

                                        var canvasm = "<div style=\"width: 70%; !important\"><canvas id=\"myChartdxm" + i + "\"></canvas></div>";
                                        $('#graficoanno2').append("<br /><br />Dettaglio Abiti venduti per Mese <b>" + msg.mesi[i - 1] + " " + msg.anno + "</b> " + canvasm + "<br /><br />");
                                        var ctxm = document.getElementById("myChartdxm" + i + "");
                                        var myChart = new Chart(ctxm, {
                                            type: 'bar',
                                            data: {
                                                labels: labels,
                                                datasets: [{
                                                        label: 'N°',
                                                        data: valori,
                                                        backgroundColor: 'rgba(46, 101, 161, 0.3)',
                                                        borderColor: 'rgba(46, 101, 161,1)',
                                                        borderWidth: 1
                                                    }]
                                            },
                                            options: {
                                                scales: {
                                                    yAxes: [{
                                                            ticks: {
                                                                beginAtZero: true,
                                                                min: 0
                                                            }
                                                        }]
                                                }
                                            }
                                        });
                                    }
                                }

                            }
                            /* fine else */
                        }
                    });
                });
            }
        });
    });
}
/* statistiche acessori */
function mostraAccessori(anno, idatelier) {
    $('.showcont').show().load('./form/form-statisticheaccessori.php', function () {
        /**/
        if (anno && idatelier) {
            $.ajax({
                type: "POST",
                url: "./statistiche.php",
                data: "anno=" + anno + "&idatelier=" + idatelier + "&submit=submitstatisticheaccessori",
                dataType: "json",
                success: function (msg) {
                    if (msg.msg === "ko") {
                        alert(msg.msgko);
                    } else {
                        var canvas2 = "<div style=\"width: 50%; !important\"><canvas id=\"myChart2\"></canvas></div>";
                        $('#graficoanno1').html("").append(msg.statistiche2 + canvas2);
                        /**/
                        /* dati grafico abiti */
                        var labels2 = new Array();
                        var valori2 = new Array();
                        $.each(msg.datiprovenienza, function (index, value) {
                            labels2.push(index);
                            valori2.push(value);
                        });
                        /**/
                        var ctx2 = document.getElementById("myChart2");
                        var myChart2 = new Chart(ctx2, {
                            type: 'pie',
                            data: {
                                labels: labels2,
                                datasets: [{
                                        label: 'numero',
                                        data: valori2,
                                        backgroundColor: [
                                            "#009b00",
                                            "#FF6384",
                                            "#36A2EB",
                                            "#FFCE56",
                                            "#ab11de",
                                            "#de7600",
                                            "#de0000",
                                            "#0039de"
                                        ],
                                        borderColor: '#E9E9E9',
                                        borderWidth: 3
                                    }]
                            },
                            options: {
                                tooltips: {
                                    callbacks: {
                                        label: function (tooltipItem, data) {
                                            var allData = data.datasets[tooltipItem.datasetIndex].data;
                                            var tooltipLabel = data.labels[tooltipItem.index];
                                            var tooltipData = allData[tooltipItem.index];
                                            var total = 0;
                                            for (var i in allData) {
                                                total += allData[i];
                                            }
                                            var tooltipPercentage = Math.round((tooltipData / total) * 100);
                                            return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
                                        }
                                    }
                                }
                            }
                        });

                        /* mese per mese */
                        $('#graficoanno1').append("<br /><br /><hr style=\"width: 80%; float: left;\"><div class=\"chiudi\"></div>");
                        for (var i = 1; i <= 12; i++) {
                            var labels = new Array();
                            var valori = new Array();
                            if (msg.datimese[i]) {
                                $.each(msg.datimese[i], function (index, value) {
                                    labels.push(index);
                                    valori.push(value);
                                });

                                var canvasm = "<div style=\"width: 70%; !important\"><canvas id=\"myChartm" + i + "\"></canvas></div>";
                                $('#graficoanno1').append("<br /><br />Dettaglio Accessori venduti per Mese <b>" + msg.mesi[i - 1] + " " + msg.anno + "</b> " + canvasm + "<br /><br />");
                                var ctxm = document.getElementById("myChartm" + i + "");
                                var myChart = new Chart(ctxm, {
                                    type: 'bar',
                                    data: {
                                        labels: labels,
                                        datasets: [{
                                                label: 'N°',
                                                data: valori,
                                                backgroundColor: 'rgba(46, 101, 161, 0.3)',
                                                borderColor: 'rgba(46, 101, 161,1)',
                                                borderWidth: 1
                                            }]
                                    },
                                    options: {
                                        scales: {
                                            yAxes: [{
                                                    ticks: {
                                                        beginAtZero: true,
                                                        min: 0
                                                    }
                                                }]
                                        }
                                    }
                                });
                            }
                        }

                    }
                    /* fine else */
                }
            });
        } // altro anno
        $.validator.messages.required = '';
        $("#statisticheanno").validate({
            submitHandler: function () {
                $("#submitstatisticheanno").ready(function () {
                    var datastring = $("#statisticheanno *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./statistiche.php",
                        data: datastring + "&submit=submitstatisticheaccessori",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#statisticheanno').trigger('reset');
                                $('#idatelier').val('');
                                var canvas2 = "<div style=\"width: 50%; !important\"><canvas id=\"myChart2\"></canvas></div>";
                                $('#graficoanno1').html("").append(msg.statistiche2 + canvas2);
                                /**/
                                /* dati grafico abiti */
                                /* dati grafico abiti */
                                var labels2 = new Array();
                                var valori2 = new Array();
                                $.each(msg.datiprovenienza, function (index, value) {
                                    labels2.push(index);
                                    valori2.push(value);
                                });
                                /**/
                                var ctx2 = document.getElementById("myChart2");
                                var myChart2 = new Chart(ctx2, {
                                    type: 'pie',
                                    data: {
                                        labels: labels2,
                                        datasets: [{
                                                label: 'numero',
                                                data: valori2,
                                                backgroundColor: [
                                                    "#009b00",
                                                    "#FF6384",
                                                    "#36A2EB",
                                                    "#FFCE56",
                                                    "#ab11de",
                                                    "#de7600",
                                                    "#de0000",
                                                    "#0039de"
                                                ],
                                                borderColor: '#E9E9E9',
                                                borderWidth: 3
                                            }]
                                    },
                                    options: {
                                        tooltips: {
                                            callbacks: {
                                                label: function (tooltipItem, data) {
                                                    var allData = data.datasets[tooltipItem.datasetIndex].data;
                                                    var tooltipLabel = data.labels[tooltipItem.index];
                                                    var tooltipData = allData[tooltipItem.index];
                                                    var total = 0;
                                                    for (var i in allData) {
                                                        total += allData[i];
                                                    }
                                                    var tooltipPercentage = Math.round((tooltipData / total) * 100);
                                                    return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
                                                }
                                            }
                                        }
                                    }
                                });

                                /* mese per mese */
                                $('#graficoanno1').append("<br /><br /><hr style=\"width: 80%; float: left;\"><div class=\"chiudi\"></div>");
                                for (var i = 1; i <= 12; i++) {
                                    var labels = new Array();
                                    var valori = new Array();
                                    if (msg.datimese[i]) {
                                        $.each(msg.datimese[i], function (index, value) {
                                            labels.push(index);
                                            valori.push(value);
                                        });

                                        var canvasm = "<div style=\"width: 70%; !important\"><canvas id=\"myChartm" + i + "\"></canvas></div>";
                                        $('#graficoanno1').append("<br /><br />Dettaglio Accessori venduti per Mese <b>" + msg.mesi[i - 1] + " " + msg.anno + "</b> " + canvasm + "<br /><br />");
                                        var ctxm = document.getElementById("myChartm" + i + "");
                                        var myChart = new Chart(ctxm, {
                                            type: 'bar',
                                            data: {
                                                labels: labels,
                                                datasets: [{
                                                        label: 'N°',
                                                        data: valori,
                                                        backgroundColor: 'rgba(46, 101, 161, 0.3)',
                                                        borderColor: 'rgba(46, 101, 161,1)',
                                                        borderWidth: 1
                                                    }]
                                            },
                                            options: {
                                                scales: {
                                                    yAxes: [{
                                                            ticks: {
                                                                beginAtZero: true,
                                                                min: 0
                                                            }
                                                        }]
                                                }
                                            }
                                        });
                                    }
                                }

                            }
                            /* fine else */
                        }
                    });
                });
            }
        });
        /* form comparativo */
        $.validator.messages.required = '';
        $("#statisticheanno2").validate({
            submitHandler: function () {
                $("#submitstatisticheanno2").ready(function () {
                    var datastring = $("#statisticheanno2 *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./statistiche.php",
                        data: datastring + "&submit=submitstatisticheaccessori",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#statisticheanno2').trigger('reset');
                                $('#idatelier2').val('');
                                var canvas2 = "<div style=\"width: 50%; !important\"><canvas id=\"myChartdx\"></canvas></div>";
                                $('#graficoanno2').html("").append(msg.statistiche2 + canvas2);
                                /**/
                                /* dati grafico abiti */
                                var labels2 = new Array();
                                var valori2 = new Array();
                                $.each(msg.datiprovenienza, function (index, value) {
                                    labels2.push(index);
                                    valori2.push(value);
                                });
                                /**/
                                var ctx2 = document.getElementById("myChartdx");
                                var myChart2 = new Chart(ctx2, {
                                    type: 'pie',
                                    data: {
                                        labels: labels2,
                                        datasets: [{
                                                label: 'numero',
                                                data: valori2,
                                                backgroundColor: [
                                                    "#009b00",
                                                    "#FF6384",
                                                    "#36A2EB",
                                                    "#FFCE56",
                                                    "#ab11de",
                                                    "#de7600",
                                                    "#de0000",
                                                    "#0039de"
                                                ],
                                                borderColor: '#E9E9E9',
                                                borderWidth: 3
                                            }]
                                    },
                                    options: {
                                        tooltips: {
                                            callbacks: {
                                                label: function (tooltipItem, data) {
                                                    var allData = data.datasets[tooltipItem.datasetIndex].data;
                                                    var tooltipLabel = data.labels[tooltipItem.index];
                                                    var tooltipData = allData[tooltipItem.index];
                                                    var total = 0;
                                                    for (var i in allData) {
                                                        total += allData[i];
                                                    }
                                                    var tooltipPercentage = Math.round((tooltipData / total) * 100);
                                                    return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
                                                }
                                            }
                                        }
                                    }
                                });

                                /* mese per mese */
                                $('#graficoanno2').append("<br /><br /><hr style=\"width: 80%; float: left;\"><div class=\"chiudi\"></div>");
                                for (var i = 1; i <= 12; i++) {
                                    var labels = new Array();
                                    var valori = new Array();
                                    if (msg.datimese[i]) {
                                        $.each(msg.datimese[i], function (index, value) {
                                            labels.push(index);
                                            valori.push(value);
                                        });

                                        var canvasm = "<div style=\"width: 70%; !important\"><canvas id=\"myChartdxm" + i + "\"></canvas></div>";
                                        $('#graficoanno2').append("<br /><br />Dettaglio Accessori venduti per Mese <b>" + msg.mesi[i - 1] + " " + msg.anno + "</b> " + canvasm + "<br /><br />");
                                        var ctxm = document.getElementById("myChartdxm" + i + "");
                                        var myChart = new Chart(ctxm, {
                                            type: 'bar',
                                            data: {
                                                labels: labels,
                                                datasets: [{
                                                        label: 'N°',
                                                        data: valori,
                                                        backgroundColor: 'rgba(46, 101, 161, 0.3)',
                                                        borderColor: 'rgba(46, 101, 161,1)',
                                                        borderWidth: 1
                                                    }]
                                            },
                                            options: {
                                                scales: {
                                                    yAxes: [{
                                                            ticks: {
                                                                beginAtZero: true,
                                                                min: 0
                                                            }
                                                        }]
                                                }
                                            }
                                        });
                                    }
                                }

                            }
                            /* fine else */
                        }
                    });
                });
            }
        });
    });
}

/* statistiche sartoria */
function mostraSartoria(anno, idatelier) {
    $('.showcont').show().load('./form/form-statistichesartoria.php', function () {
        /**/
        if (anno && idatelier) {
            $.ajax({
                type: "POST",
                url: "./statistiche.php",
                data: "anno=" + anno + "&idatelier=" + idatelier + "&submit=submitstatistichesartoria",
                dataType: "json",
                success: function (msg) {
                    if (msg.msg === "ko") {
                        alert(msg.msgko);
                    } else {
                        var canvas2 = "<div style=\"width: 50%; !important\"><canvas id=\"myChart2\"></canvas></div>";
                        $('#graficoanno1').html("").append(msg.statistiche2 + canvas2);
                        /**/
                        /* dati grafico abiti */
                        var labels2 = new Array();
                        var valori2 = new Array();
                        $.each(msg.datiprovenienza, function (index, value) {
                            labels2.push(index);
                            valori2.push(value);
                        });
                        /**/
                        var ctx2 = document.getElementById("myChart2");
                        var myChart2 = new Chart(ctx2, {
                            type: 'pie',
                            data: {
                                labels: labels2,
                                datasets: [{
                                        label: 'numero',
                                        data: valori2,
                                        backgroundColor: [
                                            "#009b00",
                                            "#FF6384",
                                            "#36A2EB",
                                            "#FFCE56",
                                            "#ab11de",
                                            "#de7600",
                                            "#de0000",
                                            "#0039de",
                                            "#ff0000",
                                            "#0000ff",
                                            "#2d2d2d",
                                            "#333399",
                                            "#66FFCC",
                                            "#FFFF33",
                                            "#99FF00",
                                            "#FFCCFF",
                                            "#660000",
                                            "#006699",
                                            "#CC6600",
                                            "#999900",
                                            "#6666FF"
                                        ],
                                        borderColor: '#E9E9E9',
                                        borderWidth: 3
                                    }]
                            },
                            options: {
                                tooltips: {
                                    callbacks: {
                                        label: function (tooltipItem, data) {
                                            var allData = data.datasets[tooltipItem.datasetIndex].data;
                                            var tooltipLabel = data.labels[tooltipItem.index];
                                            var tooltipData = allData[tooltipItem.index];
                                            var total = 0;
                                            for (var i in allData) {
                                                total += allData[i];
                                            }
                                            var tooltipPercentage = Math.round((tooltipData / total) * 100);
                                            return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
                                        }
                                    }
                                }
                            }
                        });

                        /* mese per mese */
                        $('#graficoanno1').append("<br /><br /><hr style=\"width: 80%; float: left;\"><div class=\"chiudi\"></div>");
                        for (var i = 1; i <= 12; i++) {
                            var labels = new Array();
                            var valori = new Array();
                            if (msg.datimese[i]) {
                                $.each(msg.datimese[i], function (index, value) {
                                    labels.push(index);
                                    valori.push(value);
                                });

                                var canvasm = "<div style=\"width: 70%; !important\"><canvas id=\"myChartm" + i + "\"></canvas></div>";
                                $('#graficoanno1').append("<br /><br />Dettaglio Accessori venduti per Mese <b>" + msg.mesi[i - 1] + " " + msg.anno + "</b> " + canvasm + "<br /><br />");
                                var ctxm = document.getElementById("myChartm" + i + "");
                                var myChart = new Chart(ctxm, {
                                    type: 'bar',
                                    data: {
                                        labels: labels,
                                        datasets: [{
                                                label: 'N°',
                                                data: valori,
                                                backgroundColor: 'rgba(46, 101, 161, 0.3)',
                                                borderColor: 'rgba(46, 101, 161,1)',
                                                borderWidth: 1
                                            }]
                                    },
                                    options: {
                                        scales: {
                                            yAxes: [{
                                                    ticks: {
                                                        beginAtZero: true,
                                                        min: 0
                                                    }
                                                }]
                                        }
                                    }
                                });
                            }
                        }

                    }
                    /* fine else */
                }
            });
        } // altro anno
        $.validator.messages.required = '';
        $("#statisticheanno").validate({
            submitHandler: function () {
                $("#submitstatisticheanno").ready(function () {
                    var datastring = $("#statisticheanno *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./statistiche.php",
                        data: datastring + "&submit=submitstatistichesartoria",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                //$('#statisticheanno').trigger('reset');
                                //$('#idatelier').val('');
                                var canvas2 = "<div style=\"width: 50%; !important\"><canvas id=\"myChart2\"></canvas></div>";
                                $('#graficoanno1').html("").append(msg.statistiche2 + canvas2);
                                /**/
                                /* dati grafico abiti */
                                var labels2 = new Array();
                                var valori2 = new Array();
                                $.each(msg.datiprovenienza, function (index, value) {
                                    labels2.push(index);
                                    valori2.push(value);
                                });
                                /**/
                                var ctx2 = document.getElementById("myChart2");
                                var myChart2 = new Chart(ctx2, {
                                    type: 'pie',
                                    data: {
                                        labels: labels2,
                                        datasets: [{
                                                label: 'numero',
                                                data: valori2,
                                                backgroundColor: [
                                                    "#009b00",
                                                    "#FF6384",
                                                    "#36A2EB",
                                                    "#FFCE56",
                                                    "#ab11de",
                                                    "#de7600",
                                                    "#de0000",
                                                    "#0039de",
                                                    "#ff0000",
                                                    "#0000ff",
                                                    "#2d2d2d",
                                                    "#333399",
                                                    "#66FFCC",
                                                    "#FFFF33",
                                                    "#99FF00",
                                                    "#FFCCFF",
                                                    "#660000",
                                                    "#006699",
                                                    "#CC6600",
                                                    "#999900",
                                                    "#6666FF"
                                                ],
                                                borderColor: '#E9E9E9',
                                                borderWidth: 3
                                            }]
                                    },
                                    options: {
                                        tooltips: {
                                            callbacks: {
                                                label: function (tooltipItem, data) {
                                                    var allData = data.datasets[tooltipItem.datasetIndex].data;
                                                    var tooltipLabel = data.labels[tooltipItem.index];
                                                    var tooltipData = allData[tooltipItem.index];
                                                    var total = 0;
                                                    for (var i in allData) {
                                                        total += allData[i];
                                                    }
                                                    var tooltipPercentage = Math.round((tooltipData / total) * 100);
                                                    return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
                                                }
                                            }
                                        }
                                    }
                                });

                                /* mese per mese */
                                $('#graficoanno1').append("<br /><br /><hr style=\"width: 80%; float: left;\"><div class=\"chiudi\"></div>");
                                for (var i = 1; i <= 12; i++) {
                                    var labels = new Array();
                                    var valori = new Array();
                                    if (msg.datimese[i]) {
                                        $.each(msg.datimese[i], function (index, value) {
                                            labels.push(index);
                                            valori.push(value);
                                        });

                                        var canvasm = "<div style=\"width: 70%; !important\"><canvas id=\"myChartm" + i + "\"></canvas></div>";
                                        $('#graficoanno1').append("<br /><br />Dettaglio Accessori venduti per Mese <b>" + msg.mesi[i - 1] + " " + msg.anno + "</b> " + canvasm + "<br /><br />");
                                        var ctxm = document.getElementById("myChartm" + i + "");
                                        var myChart = new Chart(ctxm, {
                                            type: 'bar',
                                            data: {
                                                labels: labels,
                                                datasets: [{
                                                        label: 'N°',
                                                        data: valori,
                                                        backgroundColor: 'rgba(46, 101, 161, 0.3)',
                                                        borderColor: 'rgba(46, 101, 161,1)',
                                                        borderWidth: 1
                                                    }]
                                            },
                                            options: {
                                                scales: {
                                                    yAxes: [{
                                                            ticks: {
                                                                beginAtZero: true,
                                                                min: 0
                                                            }
                                                        }]
                                                }
                                            }
                                        });
                                    }
                                }

                            }
                            /* fine else */
                        }
                    });
                });
            }
        });
        /* form comparativo */
        $.validator.messages.required = '';
        $("#statisticheanno2").validate({
            submitHandler: function () {
                $("#submitstatisticheanno2").ready(function () {
                    var datastring = $("#statisticheanno2 *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./statistiche.php",
                        data: datastring + "&submit=submitstatistichesartoria",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                $('#statisticheanno2').trigger('reset');
                                $('#idatelier2').val('');
                                var canvas2 = "<div style=\"width: 50%; !important\"><canvas id=\"myChartdx\"></canvas></div>";
                                $('#graficoanno2').html("").append(msg.statistiche2 + canvas2);
                                /**/
                                /* dati grafico abiti */
                                var labels2 = new Array();
                                var valori2 = new Array();
                                $.each(msg.datiprovenienza, function (index, value) {
                                    labels2.push(index);
                                    valori2.push(value);
                                });
                                /**/
                                var ctx2 = document.getElementById("myChartdx");
                                var myChart2 = new Chart(ctx2, {
                                    type: 'pie',
                                    data: {
                                        labels: labels2,
                                        datasets: [{
                                                label: 'numero',
                                                data: valori2,
                                                backgroundColor: [
                                                    "#009b00",
                                                    "#FF6384",
                                                    "#36A2EB",
                                                    "#FFCE56",
                                                    "#ab11de",
                                                    "#de7600",
                                                    "#de0000",
                                                    "#0039de",
                                                    "#ff0000",
                                                    "#0000ff",
                                                    "#2d2d2d",
                                                    "#333399",
                                                    "#66FFCC",
                                                    "#FFFF33",
                                                    "#99FF00",
                                                    "#FFCCFF",
                                                    "#660000",
                                                    "#006699",
                                                    "#CC6600",
                                                    "#999900",
                                                    "#6666FF"
                                                ],
                                                borderColor: '#E9E9E9',
                                                borderWidth: 3
                                            }]
                                    },
                                    options: {
                                        tooltips: {
                                            callbacks: {
                                                label: function (tooltipItem, data) {
                                                    var allData = data.datasets[tooltipItem.datasetIndex].data;
                                                    var tooltipLabel = data.labels[tooltipItem.index];
                                                    var tooltipData = allData[tooltipItem.index];
                                                    var total = 0;
                                                    for (var i in allData) {
                                                        total += allData[i];
                                                    }
                                                    var tooltipPercentage = Math.round((tooltipData / total) * 100);
                                                    return tooltipLabel + ': ' + tooltipData + ' (' + tooltipPercentage + '%)';
                                                }
                                            }
                                        }
                                    }
                                });

                                /* mese per mese */
                                $('#graficoanno2').append("<br /><br /><hr style=\"width: 80%; float: left;\"><div class=\"chiudi\"></div>");
                                for (var i = 1; i <= 12; i++) {
                                    var labels = new Array();
                                    var valori = new Array();
                                    if (msg.datimese[i]) {
                                        $.each(msg.datimese[i], function (index, value) {
                                            labels.push(index);
                                            valori.push(value);
                                        });

                                        var canvasm = "<div style=\"width: 70%; !important\"><canvas id=\"myChartdxm" + i + "\"></canvas></div>";
                                        $('#graficoanno2').append("<br /><br />Dettaglio Accessori venduti per Mese <b>" + msg.mesi[i - 1] + " " + msg.anno + "</b> " + canvasm + "<br /><br />");
                                        var ctxm = document.getElementById("myChartdxm" + i + "");
                                        var myChart = new Chart(ctxm, {
                                            type: 'bar',
                                            data: {
                                                labels: labels,
                                                datasets: [{
                                                        label: 'N°',
                                                        data: valori,
                                                        backgroundColor: 'rgba(46, 101, 161, 0.3)',
                                                        borderColor: 'rgba(46, 101, 161,1)',
                                                        borderWidth: 1
                                                    }]
                                            },
                                            options: {
                                                scales: {
                                                    yAxes: [{
                                                            ticks: {
                                                                beginAtZero: true,
                                                                min: 0
                                                            }
                                                        }]
                                                }
                                            }
                                        });
                                    }
                                }

                            }
                            /* fine else */
                        }
                    });
                });
            }
        });
    });
}

function puliscidata() {
    if ($('#datap').val() === "") {
        $('#data').val("");
    }
}

function puliscidata2() {
    if ($('#datap2').val() === "") {
        $('#data2').val("");
    }
}

function statisticheAdmin() {
    $('.showcont').show().load('./form/form-statisticheadmin.php', function () {

        $("#formstatisticheadmin").validate({
            rules: {
                diraff: {
                    required: function () {
                        return $('#idatelier').val() == '';
                    }
                }
            },
            submitHandler: function () {
                $("#submitformstatisticheadmin").ready(function () {
                    var datastring = $("#formstatisticheadmin *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./statistiche.php",
                        data: datastring + "&submit=submitstatisticheadmin",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                /* dati appuntamenti */
                                if (msg.dati) {
                                    var db = {
                                        loadData: function (filter) {
                                            return $.grep(this.clients, function (client) {
                                                return (!filter.atelier.toLowerCase() || client.atelier.toLowerCase().indexOf(filter.atelier.toLowerCase()) > -1)
                                                        && (!filter.totaleappuntamenti || client.totaleappuntamenti.indexOf(filter.totaleappuntamenti) > -1)
                                                        && (!filter.totaleappuntamentichiusi || client.totaleappuntamentichiusi.indexOf(filter.totaleappuntamentichiusi) > -1)
                                                        && (!filter.totaleappuntamenti_disdetti || client.totaleappuntamenti_disdetti.indexOf(filter.totaleappuntamenti_disdetti) > -1)
                                                        && (!filter.acquistato || client.acquistato.indexOf(filter.acquistato) > -1)
                                                        && (!filter.nonacquistato || client.nonacquistato.indexOf(filter.nonacquistato) > -1)
                                                        && (!filter.totalespesa || client.totalespesa.indexOf(filter.totalespesa) > -1)
                                                        && (!filter.totaleincassato || client.totaleincassato.indexOf(filter.totaleincassato) > -1)
                                                        && (!filter.daincassare || client.daincassare.indexOf(filter.daincassare) > -1)
                                                        && (!filter.mediaspesa || client.mediaspesa.indexOf(filter.mediaspesa) > -1)
                                                        && (!filter.utp || client.utp.indexOf(filter.utp) > -1);
                                            });
                                        }
                                    };

                                    window.db = db;

                                    db.clients = msg.dati;
                                    jsGrid.locale("it");

                                    var campi = [
                                        {name: "atelier", type: "text", title: "<b>Atelier</b>", css: "customRow"},
                                        {name: "totaleappuntamenti", type: "number", title: "<b>Tot. appuntamenti</b>", css: "customRow"},
                                        {name: "totaleappuntamentichiusi", type: "number", title: "<b>Appuntamenti svolti</b>", css: "customRow"},
                                        {name: "totaleappuntamenti_disdetti", type: "number", title: "<b>Appuntamenti disdetti</b>", css: "customRow"},
                                        {name: "acquistato", type: "number", title: "<b>Acquistato</b>", css: "customRow"},
                                        {name: "nonacquistato", type: "number", title: "<b>NON acquistato</b>", css: "customRow"},
                                        {name: "totalespesa", type: "number", title: "<b>Totale incassi (&euro;)</b>", css: "customRow"},
                                        {name: "mediaspesa", type: "number", title: "<b>Media singolo acquisto (&euro;)</b>", css: "customRow"},
                                        {name: "totaleincassato", type: "number", title: "<b>Totale incassato (&euro;)</b>", css: "customRow"},
                                        {name: "daincassare", type: "number", title: "<b>Totale da incassare (&euro;)</b>", css: "customRow"},
                                        {name: "utp", type: "number", title: "<b>Pezzi venduti per scontrino</b>", css: "customRow"}

                                    ];

                                    $("#jsGrid").jsGrid({
                                        width: "100%",
//                                        height: "520px",
                                        filtering: true,
                                        editing: false,
                                        sorting: true,
                                        paging: true,
                                        autoload: true,
                                        pageSize: 12,
                                        pageButtonCount: 5,
                                        controller: db,
                                        deleteConfirm: "Stai per cancellare, sei sicuro?",
                                        noDataContent: "Nessun record trovato",
                                        fields: campi
                                    });
                                } else {
                                    $('#contienistat').html("<strong>Statistiche Appuntamenti</strong><br /><br />Nessun risultato trovato");
                                }
                                /* dati dipendenti */
                                if (msg.datidip) {
                                    var db = {
                                        loadData: function (filter) {
                                            return $.grep(this.clients, function (client) {
                                                return (!filter.atelier.toLowerCase() || client.atelier.toLowerCase().indexOf(filter.atelier.toLowerCase()) > -1)
                                                        && (!filter.dipendente.toLowerCase() || client.dipendente.toLowerCase().indexOf(filter.dipendente.toLowerCase()) > -1)
                                                        && (!filter.totappdip || client.totappdip.indexOf(filter.totappdip) > -1)
                                                        && (!filter.totappchiusidip || client.totappchiusidip.indexOf(filter.totappchiusidip) > -1)
                                                        && (!filter.totappacqdip || client.totappacqdip.indexOf(filter.totappacqdip) > -1)
                                                        && (!filter.totappnoacqdip || client.totappnoacqdip.indexOf(filter.totappnoacqdip) > -1)
                                                        && (!filter.totspesadip || client.totspesadip.indexOf(filter.totspesadip) > -1)
                                                        && (!filter.mediaspesadip || client.mediaspesadip.indexOf(filter.mediaspesadip) > -1);
                                            });
                                        }
                                    };

                                    window.db = db;

                                    db.clients = msg.datidip;
                                    jsGrid.locale("it");

                                    var campi = [
                                        {name: "atelier", type: "text", title: "<b>Atelier</b>", css: "customRow"},
                                        {name: "dipendente", type: "text", title: "<b>Dipendente</b>", css: "customRow"},
                                        {name: "totappdip", type: "number", title: "<b>Tot. appuntamenti</b>", css: "customRow"},
                                        {name: "totappchiusidip", type: "number", title: "<b>Appuntamenti svolti</b>", css: "customRow"},
                                        {name: "totappacqdip", type: "number", title: "<b>Acquistato</b>", css: "customRow"},
                                        {name: "totappnoacqdip", type: "number", title: "<b>NON acquistato</b>", css: "customRow"},
                                        {name: "totspesadip", type: "number", title: "<b>Totale incassi (&euro;)</b>", css: "customRow"},
                                        {name: "mediaspesadip", type: "number", title: "<b>Media Incassi (&euro;)</b>", css: "customRow"}

                                    ];

                                    $("#jsGrid8").jsGrid({
                                        width: "100%",
//                                        height: "520px",
                                        filtering: true,
                                        editing: false,
                                        sorting: true,
                                        paging: true,
                                        autoload: true,
                                        pageSize: 12,
                                        pageButtonCount: 5,
                                        controller: db,
                                        deleteConfirm: "Stai per cancellare, sei sicuro?",
                                        noDataContent: "Nessun record trovato",
                                        fields: campi
                                    });
                                } else {
                                    $('#contienistat8').html("<strong>Statistiche Appuntamenti</strong><br /><br />Nessun risultato trovato");
                                }
                                /* dati provenienza */
                                if (msg.datiprov) {
                                    var db = {
                                        loadData: function (filter) {
                                            return $.grep(this.clients, function (client) {
                                                return (!filter.atelier.toLowerCase() || client.atelier.toLowerCase().indexOf(filter.atelier.toLowerCase()) > -1)
                                                        && (!filter.provenienza.toLowerCase() || client.provenienza.toLowerCase().indexOf(filter.provenienza.toLowerCase()) > -1)
                                                        && (!filter.totaleprovenienza || client.totaleprovenienza.indexOf(filter.totaleprovenienza) > -1)
                                                        && (!filter.totspesa || client.totaleprovenienza.indexOf(filter.totspesa) > -1);
                                            });
                                        }
                                    };

                                    window.db = db;

                                    db.clients = msg.datiprov;
                                    jsGrid.locale("it");

                                    var campi = [
                                        {name: "atelier", type: "text", title: "<b>Atelier</b>", css: "customRow"},
                                        {name: "provenienza", type: "text", title: "<b>Provenienza</b>", css: "customRow"},
                                        {name: "totaleprovenienza", type: "number", title: "<b>Totale</b>", css: "customRow"},
                                        {name: "totspesa", type: "number", title: "<b>Totale incassi</b>", css: "customRow"}

                                    ];

                                    $("#jsGrid5").jsGrid({
                                        width: "100%",
//                                        height: "520px",
                                        filtering: true,
                                        editing: false,
                                        sorting: true,
                                        paging: true,
                                        autoload: true,
                                        pageSize: 12,
                                        pageButtonCount: 5,
                                        controller: db,
                                        deleteConfirm: "Stai per cancellare, sei sicuro?",
                                        noDataContent: "Nessun record trovato",
                                        fields: campi
                                    });
                                } else {
                                    $('#contienistat5').html("<strong>Statistiche Provenienza</strong><br /><br />Nessun risultato trovato");
                                }
                                /* dati motivo no acquisto */
                                if (msg.datimotnoacq) {
                                    var db = {
                                        loadData: function (filter) {
                                            return $.grep(this.clients, function (client) {
                                                return (!filter.atelier.toLowerCase() || client.atelier.toLowerCase().indexOf(filter.atelier.toLowerCase()) > -1)
                                                        && (!filter.nomenoacquisto.toLowerCase() || client.nomenoacquisto.toLowerCase().indexOf(filter.nomenoacquisto.toLowerCase()) > -1)
                                                        && (!filter.totalenoacq || client.totalenoacq.indexOf(filter.totalenoacq) > -1);
                                            });
                                        }
                                    };

                                    window.db = db;

                                    db.clients = msg.datimotnoacq;
                                    jsGrid.locale("it");

                                    var campi = [
                                        {name: "atelier", type: "text", title: "<b>Atelier</b>", css: "customRow"},
                                        {name: "nomenoacquisto", type: "text", title: "<b>Motivo del non acquisto</b>", css: "customRow"},
                                        {name: "totalenoacq", type: "number", title: "<b>Totale</b>", css: "customRow"}

                                    ];

                                    $("#jsGrid6").jsGrid({
                                        width: "100%",
//                                        height: "520px",
                                        filtering: true,
                                        editing: false,
                                        sorting: true,
                                        paging: true,
                                        autoload: true,
                                        pageSize: 12,
                                        pageButtonCount: 5,
                                        controller: db,
                                        deleteConfirm: "Stai per cancellare, sei sicuro?",
                                        noDataContent: "Nessun record trovato",
                                        fields: campi
                                    });
                                } else {
                                    $('#contienistat6').html("<strong>Statistiche Motivo non acquisto</strong><br /><br />Nessun risultato trovato");
                                }
                                /* statistiche sugli abiti */
                                if (msg.datiabiti) {
                                    var db = {
                                        loadData: function (filter) {
                                            return $.grep(this.clients, function (client) {
                                                return (!filter.atelier.toLowerCase() || client.atelier.toLowerCase().indexOf(filter.atelier.toLowerCase()) > -1)
                                                        && (!filter.tipoabito.toLowerCase() || client.tipoabito.toLowerCase().indexOf(filter.tipoabito.toLowerCase()) > -1)
                                                        && (!filter.numeroabiti || client.numeroabiti.indexOf(filter.numeroabiti) > -1)
                                                        && (!filter.prezzoabiti || client.prezzoabiti.indexOf(filter.prezzoabiti) > -1)
                                                        && (!filter.mediaprezzoabiti || client.mediaprezzoabiti.indexOf(filter.mediaprezzoabiti) > -1);
                                            });
                                        }
                                    };

                                    window.db = db;

                                    db.clients = msg.datiabiti;
                                    jsGrid.locale("it");

                                    var campi = [
                                        {name: "atelier", type: "text", title: "<b>Atelier</b>", css: "customRow"},
                                        {name: "tipoabito", type: "text", title: "<b>Tipo abito</b>", css: "customRow"},
                                        {name: "numeroabiti", type: "number", title: "<b>Numero tot. abiti</b>", css: "customRow"},
                                        {name: "prezzoabiti", type: "number", title: "<b>Prezzo totale (&euro;)</b>", css: "customRow"},
                                        {name: "mediaprezzoabiti", type: "number", title: "<b>Media prezzo vendita (&euro;)</b>", css: "customRow"}

                                    ];

                                    $("#jsGrid2").jsGrid({
                                        width: "100%",
//                                        height: "520px",
                                        filtering: true,
                                        editing: false,
                                        sorting: true,
                                        paging: true,
                                        autoload: true,
                                        pageSize: 12,
                                        pageButtonCount: 5,
                                        controller: db,
                                        deleteConfirm: "Stai per cancellare, sei sicuro?",
                                        noDataContent: "Nessun record trovato",
                                        fields: campi
                                    });
                                } else {
                                    $('#contienistat2').html("<br /><br /><strong>Statistiche Abiti</strong><br /><br />Nessun risultato trovato");
                                }
                                /* statistiche sugi modelli abiti */
                                if (msg.datimodabito) {
                                    var db = {
                                        loadData: function (filter) {
                                            return $.grep(this.clients, function (client) {
                                                return (!filter.atelier.toLowerCase() || client.atelier.toLowerCase().indexOf(filter.atelier.toLowerCase()) > -1)
                                                        && (!filter.nomemodabito.toLowerCase() || client.nomemodabito.toLowerCase().indexOf(filter.nomemodabito.toLowerCase()) > -1)
                                                        && (!filter.totmodabiti || client.totmodabiti.indexOf(filter.totmodabiti) > -1)
                                                        && (!filter.prezzomodabito || client.prezzomodabito.indexOf(filter.prezzomodabito) > -1)
                                                        && (!filter.mediamodabito || client.mediamodabito.indexOf(filter.mediamodabito) > -1);
                                            });
                                        }
                                    };

                                    window.db = db;

                                    db.clients = msg.datimodabito;
                                    jsGrid.locale("it");

                                    var campi = [
                                        {name: "atelier", type: "text", title: "<b>Atelier</b>", css: "customRow"},
                                        {name: "nomemodabito", type: "text", title: "<b>Modello abito</b>", css: "customRow"},
                                        {name: "totmodabiti", type: "number", title: "<b>Numero tot. mod. abiti</b>", css: "customRow"},
                                        {name: "prezzomodabito", type: "number", title: "<b>Prezzo totale (&euro;)</b>", css: "customRow"},
                                        {name: "mediamodabito", type: "number", title: "<b>Media prezzo vendita (&euro;)</b>", css: "customRow"}

                                    ];

                                    $("#jsGrid7").jsGrid({
                                        width: "100%",
//                                        height: "520px",
                                        filtering: true,
                                        editing: false,
                                        sorting: true,
                                        paging: true,
                                        autoload: true,
                                        pageSize: 12,
                                        pageButtonCount: 5,
                                        controller: db,
                                        deleteConfirm: "Stai per cancellare, sei sicuro?",
                                        noDataContent: "Nessun record trovato",
                                        fields: campi
                                    });
                                } else {
                                    $('#contienistat7').html("<br /><br /><strong>Statistiche Modelli Abiti</strong><br /><br />Nessun risultato trovato");
                                }
                                /* statistiche sugli accessori */
                                if (msg.datiaccessori) {
                                    var db = {
                                        loadData: function (filter) {
                                            return $.grep(this.clients, function (client) {
                                                return (!filter.atelier.toLowerCase() || client.atelier.toLowerCase().indexOf(filter.atelier.toLowerCase()) > -1)
                                                        && (!filter.nomeaccessorio.toLowerCase() || client.nomeaccessorio.toLowerCase().indexOf(filter.nomeaccessorio.toLowerCase()) > -1)
                                                        && (!filter.totaleacc || client.totaleacc.indexOf(filter.totaleacc) > -1)
                                                        && (!filter.totprezzoacc || client.totprezzoacc.indexOf(filter.totprezzoacc) > -1)
                                                        && (!filter.totmediaacc || client.totmediaacc.indexOf(filter.totmediaacc) > -1);
                                            });
                                        }
                                    };

                                    window.db = db;

                                    db.clients = msg.datiaccessori;
                                    jsGrid.locale("it");

                                    var campi = [
                                        {name: "atelier", type: "text", title: "<b>Atelier</b>", css: "customRow"},
                                        {name: "nomeaccessorio", type: "text", title: "<b>Accessorio</b>", css: "customRow"},
                                        {name: "totaleacc", type: "number", title: "<b>Numero tot. accessori</b>", css: "customRow"},
                                        {name: "totprezzoacc", type: "number", title: "<b>Prezzo totale (&euro;)</b>", css: "customRow"},
                                        {name: "totmediaacc", type: "number", title: "<b>Media prezzo vendita (&euro;)</b>", css: "customRow"}

                                    ];

                                    $("#jsGrid3").jsGrid({
                                        width: "100%",
//                                        height: "520px",
                                        filtering: true,
                                        editing: false,
                                        sorting: true,
                                        paging: true,
                                        autoload: true,
                                        pageSize: 12,
                                        pageButtonCount: 5,
                                        controller: db,
                                        deleteConfirm: "Stai per cancellare, sei sicuro?",
                                        noDataContent: "Nessun record trovato",
                                        fields: campi
                                    });
                                } else {
                                    $('#contienistat3').html("<br /><br /><strong>Statistiche Accessori</strong><br /><br />Nessun risultato trovato");
                                }
                                /* statistiche sartoria */
                                if (msg.datisartoria) {
                                    var db = {
                                        loadData: function (filter) {
                                            return $.grep(this.clients, function (client) {
                                                return (!filter.atelier.toLowerCase() || client.atelier.toLowerCase().indexOf(filter.atelier.toLowerCase()) > -1)
                                                        && (!filter.nomesartoria.toLowerCase() || client.nomesartoria.toLowerCase().indexOf(filter.nomesartoria.toLowerCase()) > -1)
                                                        && (!filter.totalesart || client.totalesart.indexOf(filter.totalesart) > -1)
                                                        && (!filter.totprezzosart || client.totprezzosart.indexOf(filter.totprezzosart) > -1)
                                                        && (!filter.totcostisart || client.totcostisart.indexOf(filter.totcostisart) > -1)
                                                        && (!filter.guadagno || client.guadagno.indexOf(filter.guadagno) > -1);
                                            });
                                        }
                                    };

                                    window.db = db;

                                    db.clients = msg.datisartoria;
                                    jsGrid.locale("it");

                                    var campi = [
                                        {name: "atelier", type: "text", title: "<b>Atelier</b>", css: "customRow"},
                                        {name: "nomesartoria", type: "text", title: "<b>Riparazione</b>", css: "customRow"},
                                        {name: "totalesart", type: "number", title: "<b>Numero tot. riparazioni</b>", css: "customRow"},
                                        {name: "totprezzosart", type: "number", title: "<b>Prezzo totale (&euro;)</b>", css: "customRow"},
                                        {name: "totcostisart", type: "number", title: "<b>Costo totale (&euro;)</b>", css: "customRow"},
                                        {name: "guadagno", type: "number", title: "<b>Guadagno/Perdita (&euro;)</b>", css: "customRow"}

                                    ];

                                    $("#jsGrid4").jsGrid({
                                        width: "100%",
//                                        height: "520px",
                                        filtering: true,
                                        editing: false,
                                        sorting: true,
                                        paging: true,
                                        autoload: true,
                                        pageSize: 12,
                                        pageButtonCount: 5,
                                        controller: db,
                                        deleteConfirm: "Stai per cancellare, sei sicuro?",
                                        noDataContent: "Nessun record trovato",
                                        fields: campi
                                    });
                                } else {
                                    $('#contienistat4').html("<br /><br /><strong>Statistiche Sartoria</strong><br /><br />Nessun risultato trovato");
                                }
                                /* statistiche sui bollini */
                                if (msg.datibollini) {
                                    var db = {
                                        loadData: function (filter) {
                                            return $.grep(this.clients, function (client) {
                                                return (!filter.atelier.toLowerCase() || client.atelier.toLowerCase().indexOf(filter.atelier.toLowerCase()) > -1)
                                                        && (!filter.tipoabitib.toLowerCase() || client.tipoabitib.toLowerCase().indexOf(filter.tipoabitib.toLowerCase()) > -1)
                                                        && (!filter.bollinib.toLowerCase() || client.bollinib.toLowerCase().indexOf(filter.bollinib.toLowerCase()) > -1)
                                                        && (!filter.numeroabitib || client.numeroabitib.indexOf(filter.numeroabitib) > -1);
                                            });
                                        }
                                    };

                                    window.db = db;

                                    db.clients = msg.datibollini;
                                    jsGrid.locale("it");

                                    var campi = [
                                        {name: "atelier", type: "text", title: "<b>Atelier</b>", css: "customRow"},
                                        {name: "tipoabitib", type: "text", title: "<b>Tipo abito</b>", css: "customRow"},
                                        {name: "bollinib", type: "text", title: "<b>Bollino</b>", css: "customRow"},
                                        {name: "numeroabitib", type: "number", title: "<b>Numero tot. abiti</b>", css: "customRow"}

                                    ];

                                    $("#jsGrid9").jsGrid({
                                        width: "100%",
//                                        height: "520px",
                                        filtering: true,
                                        editing: false,
                                        sorting: true,
                                        paging: true,
                                        autoload: true,
                                        pageSize: 12,
                                        pageButtonCount: 5,
                                        controller: db,
                                        deleteConfirm: "Stai per cancellare, sei sicuro?",
                                        noDataContent: "Nessun record trovato",
                                        fields: campi
                                    });
                                } else {
                                    $('#contienistat9').html("<br /><br /><strong>Statistiche Bollini Abiti</strong><br /><br />Nessun risultato trovato");
                                }
                            }
                        }
                    });
                    $('#contienistat').html("<strong>Statistiche Appuntamenti</strong><br /><br /><div id=\"jsGrid\"></div>");
                    $('#contienistat8').html("<br /><br /><strong>Statistiche Dipendenti</strong><br /><br /><div id=\"jsGrid8\"></div>");
                    $('#contienistat5').html("<br /><br /><strong>Statistiche Provenienza</strong><br /><br /><div id=\"jsGrid5\"></div>");
                    $('#contienistat6').html("<br /><br /><strong>Statistiche Motivo non acquisto</strong><br /><br /><div id=\"jsGrid6\"></div>");
                    $('#contienistat2').html("<br /><br /><strong>Statistiche Abiti</strong><br /><br /><div id=\"jsGrid2\"></div>");
                    $('#contienistat7').html("<br /><br /><strong>Statistiche Modelli Abiti</strong><br /><br /><div id=\"jsGrid7\"></div>");
                    $('#contienistat9').html("<br /><br /><strong>Statistiche Bollini Abiti</strong><br /><br /><div id=\"jsGrid9\"></div>");
                    $('#contienistat3').html("<br /><br /><strong>Statistiche Accessori</strong><br /><br /><div id=\"jsGrid3\"></div>");
                    $('#contienistat4').html("<br /><br /><strong>Statistiche Sartoria</strong><br /><br /><div id=\"jsGrid4\"></div>");
                });
            }
        });
    });
}

function attdiraff(valore) {
    if (valore != "") {
        $('#diraff').prop("disabled", true);
        $('#diraff').removeClass("error");
    } else {
        $('#diraff').prop("disabled", false);
    }
}

function attidat(valore) {
    if (valore != "") {
        $('#idatelier').prop("disabled", true);
    } else {
        $('#idatelier').prop("disabled", false);
    }
}

function mostraConversioni(anno, idatelier) {
    $('.showcont').show().load('./form/form-statisticheconversioni.php', function () {
        /**/
        if (anno && idatelier) {
            $.ajax({
                type: "POST",
                url: "./statistiche.php",
                data: "anno=" + anno + "&idatelier=" + idatelier + "&submit=submitstatisticheconversioni",
                dataType: "json",
                success: function (msg) {
                    if (msg.msg === "ko") {
                        alert(msg.msgko);
                    } else {
                        $('#graficoanno1').html("").append(msg.statistiche);
                    }
                }
            });
        } // altro anno
        $.validator.messages.required = '';
        $("#statisticheanno").validate({
            submitHandler: function () {
                $("#submitstatisticheanno").ready(function () {
                    var datastring = $("#statisticheanno *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./statistiche.php",
                        data: datastring + "&submit=submitstatisticheconversioni",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                //$('#statisticheanno').trigger('reset');
                                //$('#idatelier').val('');
                                $('#graficoanno1').html("").append(msg.statistiche);
                            }
                            /* fine else */
                        }
                    });
                });
            }
        });
        /* form comparativo */
        $.validator.messages.required = '';
        $("#statisticheanno2").validate({
            submitHandler: function () {
                $("#submitstatisticheanno2").ready(function () {
                    var datastring = $("#statisticheanno2 *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./statistiche.php",
                        data: datastring + "&submit=submitstatisticheconversioni",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                //$('#statisticheanno2').trigger('reset');
                                //$('#idatelier2').val('');
                                $('#graficoanno2').html("").append(msg.statistiche2);
                            }
                            /* fine else */
                        }
                    });
                });
            }
        });
    });
}

function mostraConversioniDip() {
    $('.showcont').show().load('./form/form-statisticheconversioni_dip.php', function () {
        $.validator.messages.required = '';
        $("#formstatisticheconvdip").validate({
            submitHandler: function () {
                $("#submitformstatisticheconvdip").ready(function () {
                    var datastring = $("#formstatisticheconvdip *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./statistiche.php",
                        data: datastring + "&submit=submitstatisticheconversioni_dip",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                //$('#statisticheanno').trigger('reset');
                                //$('#idatelier').val('');
                                $('#graficoanno1').html("").append(msg.statistiche);
                                if (msg.statistiche != '') {
                                    $('#btn-esporta').prop('href', "./statistiche.php?submit=submitstatisticheconversioni_dipcsv&" + datastring);
                                    $('#btn-esporta').show();
                                } else {
                                    $('#btn-esporta').hide();
                                }
                            }
                            /* fine else */
                        }
                    });
                });
            }
        });
        /* form comparativo */
        $.validator.messages.required = '';
        $("#statisticheanno2").validate({
            submitHandler: function () {
                $("#submitstatisticheanno2").ready(function () {
                    var datastring = $("#statisticheanno2 *").not(".nopost").serialize();
                    $.ajax({
                        type: "POST",
                        url: "./statistiche.php",
                        data: datastring + "&submit=submitstatisticheconversioni",
                        dataType: "json",
                        success: function (msg) {
                            if (msg.msg === "ko") {
                                alert(msg.msgko);
                            } else {
                                //$('#statisticheanno2').trigger('reset');
                                //$('#idatelier2').val('');
                                $('#graficoanno2').html("").append(msg.statistiche2);
                            }
                            /* fine else */
                        }
                    });
                });
            }
        });
    });
}

function mostraEsporta() {
    $('.showcont').show().load('./form/form-esporta.php', function () {

    });
}