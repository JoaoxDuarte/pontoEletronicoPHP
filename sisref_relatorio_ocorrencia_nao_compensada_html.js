$(document).ready(function ()
{
    //assign the sortStart event
    //$("#AutoNumber3").tablesorter(); // call the tablesorter plugin, the magic happens in the markup
    //$("#AutoNumber2").tablesorter();// call the tablesorter plugin, the magic happens in the markup
});

function carregaDadosDescontoImediato()
{
    showProcessando(); // mensagem processando
    var sURL = "sisref_relatorio_ocorrencia_nao_compensada_lista.frqano.php";
    var nContador = 0;
    var nSeq = 0;
    var yy = 0;
    var x = 0;

    var nLista = jsListaSemRemuneracao.length;

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
                data: "siape=" + jsListaSemRemuneracao[x],
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
                            idInner = "<tr onmouseover='pinta(1,this)' onmouseout='pinta(2,this)' height='18'><td align='center'>" + nSeq + "</td><td align='center'>" + ojson[yy].siape + "</td><td nowrap>" + ojson[yy].nome + "</td><td align='center'>" + ojson[yy].cod_siapecad + "</td><td align='center'>" + ojson[yy].cod_siape + "</td><td align='center'>" + ojson[yy].dia_ini + "</td><td align='center'>" + ojson[yy].dia_fim + "</td><td align='center'>" + ojson[yy].dias + "</td></tr>";
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
                        $('#remover2').remove();
                    }
                },
                error: function (erro)
                {
                    $("#matriculas_erro").append(jsListaSemRemuneracao[x]);
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

function carregaDadosNaoCompensados()
{
    showProcessando(); // mensagem processando
    var sURL = "sisref_relatorio_ocorrencia_nao_compensada_lista.naocompensados.php";
    var nContador2 = 0;
    var nSeq = 0;
    var nY2 = 0;
    var nX2 = 0;

    var houveErro = false;

    var nLista = jsListaDescontos.length;

    if (nLista == 0)
    {
        idInner = "<tr onmouseover='pinta(1,this)' onmouseout='pinta(2,this)' height='18'><td align='center' colspan='6'>Sem registros para exibir</td></tr>";
        $("#AutoNumber3 tbody").append(idInner);
        $("#AutoNumber3 tbody").trigger("update");
        hideProcessando();
        $('#remover3').remove();
    }
    else
    {
        for (nX2 = 0; nX2 < nLista; nX2++)
        {
            if (houveErro == true)
            {
                break;
            }
            $.ajax({
                type: "POST",
                url: sURL,
                data: "siape=" + jsListaDescontos[nX2],
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
                    nContador2++;
                    if (tam > 0)
                    {
                        for (nY2 = 0; nY2 < tam; nY2++)
                        {
                            nSeq++;
                            idInner = "<tr onmouseover='pinta(1,this)' onmouseout='pinta(2,this)' height='18'><td align='center'>" + nSeq + "</td><td align='center'>" + ojson[nY2].siape + "</td><td nowrap>" + ojson[nY2].nome + "</td><td align='center'>" + ojson[nY2].cod_siapecad + "</td><td align='center'>" + ojson[nY2].cod_siape + "</td><td align='center'>" + ojson[nY2].dia + "</td><td align='center'>" + ojson[nY2].horas + "</td></tr>";
                            $("#AutoNumber3 tbody").append(idInner);
                            $("#AutoNumber3 tbody").trigger("update");
                        }
                    }
                },
                complete: function (data)
                {
                    $("#siape_processando3").val(nContador2 + " / " + nMatSiape);
                    if (nContador2 >= nLista)
                    {
                        $('#remover3').remove();
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
                    $("#matriculas_erro3").append(jsListaDescontos[nX2]);
                    $("#matriculas_erro3").trigger("update");
                    /*
                     //$('#remover3').remove();
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

/*
 error: function(xhr,erro){
 //$('#remover3').remove();
 //gravandoArquivoPDF();
 var msgTexto = 'Erro ' + xhr.status + ' - ' + xhr.statusText + '<br> Tipo do erro: ' + erro;
 hideProcessando();
 $.dlg({
 content: msgTexto,
 title: 'Erro',
 drag: false
 });
 //houveErro = true;
 return false;
 }
 */

function gravandoArquivoPDF()
{
    $.ajax({
        type: "POST",
        url: "sisref_relatorio_ocorrencia_nao_compensada_imp.php"
    });
}
