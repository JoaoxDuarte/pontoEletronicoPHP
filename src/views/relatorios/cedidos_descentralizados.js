
$(document).ready(function() {
    //assign the sortStart event
    //$("#AutoNumber3").tablesorter(); // call the tablesorter plugin, the magic happens in the markup
    //$("#AutoNumber2").tablesorter();// call the tablesorter plugin, the magic happens in the markup
});

var source;
var tela = null;
var registros = 0;

window.onload = function setDataSource()
{
    startTask();
}

function startTask()
{
    showProcessandoAguarde();
    tela = new newTelaProgresso();
    tela.init();

    var tb           = $("#registros_selecionados");
    var bt_cancelar  = $("#interromper");
    var bt_reiniciar = $("#reiniciar");

    var dados = $("#dados").val();
    var filtrar = $("#dados_filtrar").val().toLowerCase();

    if (!!window.EventSource)
    {
        source = new EventSource("src/views/relatorios/cedidos_descentralizados_sse.php?dados="+dados+"&filtrar="+filtrar);

        source.addEventListener("message", function(e)
        {
            var result = JSON.parse( e.data );

            if(e.lastEventId === 'CLOSE')
            {
                source.close();
                tela.close();
                hideProcessandoAguarde();
                bt_cancelar.css( 'display', 'none' );
                bt_reiniciar.css( 'display', 'block' );
                if (registros == 0)
                {
                    addLinhas('N&atilde;o h&aacute; servidores cedidos/descentralizados sem frequ&ecirc;ncia!!');
                }
            }
            else
            {
                registros++;
                tela.setProgress( result.progress );
                tela.setRegistros( 'Registros: ' + result.seq + '/' + result.total );
                addLinhas( result );
            }
        }, false);

        source.addEventListener("open", function(e)
        {
            tela.open();
            tb.html( '' );
        }, false);

        source.addEventListener("error", function(e)
        {
            tela.close();
            source.close();
            hideProcessandoAguarde();
            addLinhas('Erro!');
        }, false);
    }
    else
    {
        tela.close();
        hideProcessandoAguarde();
    }
}

function stopTask()
{
    var registros    = $('#total_de_registros');
    var tb           = $("#registros_selecionados");
    var bt_cancelar  = $("#interromper");
    var bt_reiniciar = $("#reiniciar");

    tela.close();
    hideProcessandoAguarde();

    bt_cancelar.css( 'display', 'none' );
    bt_reiniciar.css( 'display', 'block' );

    tb.html( '<tr height="18"><td align="center" colspan="6">Interrompido!</td></tr>' + tb.html() );
    tb.html( tb.html() + '<tr height="18"><td align="center" colspan="5">Interrompido!</td></tr>' );

    source.close();
}

function addLinhas(obj)
{
    var registros = $('#total_de_registros');
    var tb        = $("#registros_selecionados");

    var dados = base64_encode(obj.mat_siape+':|:'+obj.cod_lot+':|:'+obj.jornada);

    if (typeof obj === "string")
    {
        registros.html( '' );
        tb.html( tb.html() + '<tr height="18"><td align="center" colspan="6">'+obj+'</td></tr>' );
    }
    else
    {
        registros.html( 'Total de ' + obj.total + ' registros.' );
        tb.html( tb.html() + '\
<tr style="height:18px;">\n\
    <td style="text-align:center">'+obj.seq+'</td>\n\
    <td align="center">'+obj.mat_siape+'</td>\n\
    <td style="text-align:left;text-indent:4px;">'+obj.nome_serv+'</td>\n\
    <td style="text-align:center;cursor:help;" alt="'+obj.unidade_descricao+'" title="'+obj.unidade_descricao+'">'+obj.cod_lot+'</td>\n\
    <td style="text-align:center;cursor:help;" alt="'+obj.descsitcad+'" title="'+obj.descsitcad+'">'+obj.sitcad+'</td>\n\
    <td style="text-align:center;"><a href="regfreq8.php?dados='+dados+'">Verificar</a></td>\n\
</tr>' );
    }
}
