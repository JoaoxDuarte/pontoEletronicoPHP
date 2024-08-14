$(document).ready(function ()
{
    //assign the sortStart event
    $.tablesorter.defaults.sortList = [[2, 0]];
    $("#AutoNumber2").tablesorter();// call the tablesorter plugin, the magic happens in the markup
});

var nSeq = 0;

function carregaDadosRecesso()
{
    showProcessando(); // mensagem processando

    // dados
    var ano = $('#periodo').val();

    var sURL = "sisref_relatorio_recesso_nao_compensado_lista.php";
    var nContador = 0;
    var nSeq = 0;
    var yy = 0;
    var x = 0;

    var nLista = jsLista.length;

    for (x = 0; x < nLista; x++)
    {
        $.ajax({
            type: "POST",
            url: sURL,
            data: "siape=" + jsLista[x] + "&decode=sim" + "&periodo=" + periodo,
            dataType: "json",
            cache: false,
            beforeSend: function ()
            {
                // enquanto a função esta sendo processada, você
                // pode exibir na tela uma msg de carregando
                //$('#mensagem_aviso').html('<img src="imagem/loading.gif"/>');
            },
            success: function (response)
            {
                var ojson = response.dados; //get the jsonObject
                var tam = ojson.length; // Número de itens
                var idInner = "";
                nContador++;
                if (tam > 0)
                {
                    for (yy = 0; yy < tam; yy++)
                    {
                        nSeq++;
                        idInner = "<tr onmouseover='pinta(1,this)' onmouseout='pinta(2,this)' height='18'><td align='center' class='tahomaSize_1'>" + nSeq + "</td><td align='center'>" + ojson[yy].siape + "</td><td>" + ojson[yy].nome + "</td><td align='center'>" + ojson[yy].cod + "</td><td align='center'>" + ojson[yy].total + "</td></tr>";
                        $("#AutoNumber2 tbody").bind().append(idInner);
                        //$("#AutoNumber2 tbody").append( idInner );
                        $("#AutoNumber2 tbody").trigger("update");
                    }
                }
            },
            complete: function (data)
            {
                $("#siape_processando2").val(nContador + " / " + nMatSiape);
                if (nContador >= nLista)
                {
                    var td_eq = 0;
                    for (xSeq = 1; xSeq <= nSeq; xSeq++)
                    {
                        $("#AutoNumber2 tbody tr td:eq(" + td_eq + ")").html(xSeq);
                        td_eq += 5;
                    }
                    $('#remover2').remove();
                    //gravandoArquivoPDF();
                    hideProcessando();
                    $.dlg({
                        content: 'Processo concluído!',
                        title: 'Aviso',
                        drag: false
                    });
                }
            },
            error: function (erro)
            {
                //$('#remover2').remove();
                //gravandoArquivoPDF();
                hideProcessando();
                $.dlg({
                    content: 'Problemas no acesso aos dados!',
                    title: 'erro',
                    drag: false
                });
            }
        });
    }
}


function gravandoArquivoPDF()
{
    $.ajax({
        type: "POST",
        url: "sisref_relatorio_recesso_nao_compensado_imp.php"
    });
}
