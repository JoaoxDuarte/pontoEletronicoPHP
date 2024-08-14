$(document).ready(function ()
{
    // call the tablesorter plugin, the magic happens in the markup
    $.tablesorter.defaults.sortList = [[2, 0]];
    $("#AutoNumber2").tablesorter();
});

var tam = 0;
var nSeq = 0;
var nLista = 0;

function carregaDadosSiapecad()
{
    showProcessando(1); // mensagem processando

    var sURL = "sisref_relatorio_comando_siapecad_lista.php";
    var nContador = 0;
    var nSeq = 0;
    var yy = 0;
    var x = 0;

    nLista = jsLista.length;

    for (x = 0; x < nLista; x++)
    {
        $.ajax({
            type: "POST",
            url: sURL,
            data: "siape=" + jsLista[x],
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
                tam = ojson.length; // Número de itens
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
                    var td_eq = 0;
                    for (xSeq = 1; xSeq < nSeq; xSeq++)
                    {
                        $("#AutoNumber2 tbody tr td:eq(" + td_eq + ")").html(xSeq);
                        td_eq += 8;
                    }
                    //$("#AutoNumber2 tbody").attr( "style", "display: ;");
                    //$('#remover2').remove();
                    gravandoArquivoPDF();
                    //hideProcessando(1);
                    /*
                     $("#AutoNumber2 tbody")
                     .tablesorter({widthFixed: true, widgets: ['zebra'], sortForce: [[2,0]] })
                     .tablesorterPager({container: $("#pager")
                     });
                     */
                    /*
                     $.dlg({
                     content: 'Processo concluído!',
                     title: 'Aviso',
                     css: {
                     width: '50%',
                     height: '50%'
                     },
                     drag: false
                     });
                     */
                }
            },
            error: function (erro)
            {
                //$('#remover2').remove();
                //gravandoArquivoPDF();
                hideProcessando(1);
                /*
                 $.dlg({
                 content: 'Problemas no acesso aos dados!',
                 title: 'Erro',
                 drag: false
                 });
                 */
                x = nLista;
                yy = tam;
                return false;
            }
        });
    }
}


function gravandoArquivoPDF()
{
    $.ajax({
        type: "POST",
        url: "sisref_relatorio_comando_siapecad_imp.php",
        beforeSend: function ()
        {
            $("#siape_processando2").val("Gerando o arquivo PDF");
        },
        complete: function (data)
        {
            $('#remover2').remove();
            $("#AutoNumber2 tbody").attr("style", "display: ;");
            hideProcessando(1);
        },
        error: function (erro)
        {
            $('#remover2').remove();
            hideProcessando(1);
            x = nLista;
            yy = tam;
            return false;
        }
    });
}
