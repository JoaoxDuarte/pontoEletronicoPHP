$(document).ready(function ()
{

    $("#btn-continuar").click(function ()
    {
        validar();
    });

});


function validar()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var oco88888           = $('#teste').val();        // ocorr�ncia : registro parcial
    var oco99999           = $('#teste9').val();       // ocorr�ncia : sem frequencia
    var ocoTracos          = $('#teste_tracos').val(); // ocorr�ncia : '-----'
    var total_de_registros = $('#teste2').val();       // total de registros
    var dias_registrados   = $('#teste3').val();       // dias registrados

    var codigoSemFrequenciaPadrao   = $("#codigoSemFrequenciaPadrao").val();
    var codigoRegistroParcialPadrao = $("#codigoRegistroParcialPadrao").val();
    var codigosTrocaObrigatoria     = $("#codigosTrocaObrigatoria").val();

    if (parseInt(oco88888, 10) > 0 && parseInt(oco99999, 10) > 0 && parseInt(ocoTracos, 10) > 0)
    {
        oTeste.setMsg( 'N�o � permitido confirmar/verificar frequ�ncia homologada com c�digo ' + codigoRegistroParcialPadrao + ', ' + codigoSemFrequenciaPadrao + ' e dias com "' + codigosTrocaObrigatoria + '" como ocorr�ncia na frequ�ncia do servidor!' );
    }
    else if (parseInt(oco88888, 10) > 0 && parseInt(oco99999, 10) > 0)
    {
        oTeste.setMsg( 'N�o � permitido confirmar/verificar frequ�ncia homologada com c�digo ' + codigoRegistroParcialPadrao + ' e ' + codigoSemFrequenciaPadrao + '  como ocorr�nci na frequ�ncia do servidor!' );
    }
    else if (parseInt(oco88888, 10) > 0 && parseInt(ocoTracos, 10) > 0)
    {
        oTeste.setMsg( 'N�o � permitido confirmar/verificar frequ�ncia homologada com c�digo ' + codigoRegistroParcialPadrao + ' e dias com "' + codigosTrocaObrigatoria + '" como ocorr�ncia na frequ�ncia do servidor!' );
    }
    else if (parseInt(oco99999, 10) > 0 && parseInt(ocoTracos, 10) > 0)
    {
        oTeste.setMsg( 'N�o � permitido confirmar/verificar frequ�ncia homologada com c�digo ' + codigoSemFrequenciaPadrao + ' e dias com "' + codigosTrocaObrigatoria + '" como ocorr�ncia na frequ�ncia do servidor!' );
    }
    else if (parseInt(oco88888, 10) > 0)
    {
        oTeste.setMsg( 'N�o � permitido confirmar/verificar frequ�ncia homologada com c�digo ' + codigoRegistroParcialPadrao + ' como ocorr�ncia na frequ�ncia do servidor!' );
    }
    else if (parseInt(oco99999, 10) > 0)
    {
        oTeste.setMsg( 'N�o � permitido confirmar/verificar frequ�ncia homologada com c�digo ' + codigoSemFrequenciaPadrao + ' como ocorr�ncia na frequ�ncia do servidor!' );
    }
    else if (parseInt(ocoTracos, 10) > 0)
    {
        oTeste.setMsg( 'N�o � permitido confirmar/verificar frequ�ncia homologada com "' + codigosTrocaObrigatoria + '" como ocorr�ncia na frequ�ncia do servidor!' );
    }

    if (parseInt(total_de_registros, 10) < parseInt(dias_registrados, 10))
    {
        oTeste.setMsg('Est� faltando dias na ficha do servidor complete para que seja poss�vel homologar!');
    }

    // se houve erro ser�(�o) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    if (bResultado == false)
    {
        return bResultado;
    }
    else
    {
        var siape   = $('#siape').val();
        var lotacao = $('#lotacao').val();
        var mes2    = $('#mes2').val();
        var ano2    = $('#ano2').val();

        var parametros = base64_encode(siape + ":|:" + lotacao + ":|:" + mes2 + ":|:" + ano2 + ":|:" + teste + ":|:" + teste2 + ":|:" + teste3 + ":|:" + teste9 + ":|:" + teste_tracos);

        $('#dados').val(parametros);
        var destino = "javascript:window.location.replace('frequencia_homologar_concluir.php?dados=" + parametros + "');";
        
        showProcessandoAguarde();
        
        $("#form1").attr("onsubmit", "javascript:return true;");
        $("#form1").attr("action", destino);
        $('#form1').submit();
    }

    return false;
}
