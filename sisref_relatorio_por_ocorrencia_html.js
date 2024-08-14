$(document).ready(function ()
{
    //assign the sortStart event
    //$("#AutoNumber2").tablesorter();// call the tablesorter plugin, the magic happens in the markup
});


function carregaDadosOcorrencia()
{
    showProcessando(); // mensagem processando
    var sURL = "sisref_relatorio_por_ocorrencia_lista.frqano.php";
    var nContador = 0;
    var nSeq = 0;
    var yy = 0;
    var x = 0;

    var nLista = jsLista.length;

    if (nLista == 0)
    {
        idInner = "<tr onmouseover='pinta(1,this)' onmouseout='pinta(2,this)' height='18'><td align='center' colspan='6'>Sem registros para exibir</td></tr>";
        $("#AutoNumber2 tbody").append(idInner);
        $("#AutoNumber2 tbody").trigger("update");
        hideProcessando();
        $('#remover2').remove();
    }
    else
    {
        for (x = 0; x < nLista; x++)
        {
            $.ajax({
                type: "POST",
                url: sURL,
                data: "siape=" + jsLista[x] + "&ocorr=" + jsOcorr,
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
                            idInner = "<tr onmouseover='pinta(1,this)' onmouseout='pinta(2,this)' height='18'><td align='center'>" + nSeq + "</td><td align='center'>" + ojson[yy].siape + "</td><td nowrap>" + ojson[yy].nome + "</td><td align='center'>" + ojson[yy].dia_ini + "</td><td align='center'>" + ojson[yy].dia_fim + "</td><td align='center'>" + ojson[yy].dias + "</td></tr>";
                            $("#AutoNumber2 tbody").append(idInner);
                            $("#AutoNumber2 tbody").trigger("update");
                        }
                    }
                },
                complete: function (data)
                {
                    $("#siape_processando2").val(nContador + " / " + nMatSiape);
                    if (nContador >= nLista)
                    {
                        hideProcessando();
                        $('#remover2').remove();
                    }
                },
                error: function (erro)
                {
                    $("#matriculas_erro").append(jsLista[x]);
                    $("#matriculas_erro").trigger("update");
                    /*
                     //$('#remover2').remove();
                     //gravandoArquivoPDF();
                     hideProcessando();
                     $.dlg({
                     content: 'Problemas no acesso aos dados!',
                     title: 'Erro',
                     drag: false
                     });
                     */
                    return false;
                }
            });
        }
    }
}
