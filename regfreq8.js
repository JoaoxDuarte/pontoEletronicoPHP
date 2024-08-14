$(document).ready(function ()
{
    //
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
        oTeste.setMsg( 'N�o � permitido confirmar/verificar frequ�ncia homologada com c�digo ' + codigoRegistroParcialPadrao + ' e ' + codigoSemFrequenciaPadrao + ' na frequ�ncia do servidor!' );
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
        oTeste.setMsg( 'N�o � permitido confirmar/verificar frequ�ncia homologada com c�digo ' + codigoRegistroParcialPadrao + ' na frequ�ncia do servidor!' );
    }
    else if (parseInt(oco99999, 10) > 0)
    {
        oTeste.setMsg( 'N�o � permitido confirmar/verificar frequ�ncia homologada com c�digo ' + codigoSemFrequenciaPadrao + ' na frequ�ncia do servidor!' );
    }
    else if (parseInt(ocoTracos, 10) > 0)
    {
        oTeste.setMsg( 'N�o � permitido confirmar/verificar frequ�ncia homologada com "' + codigosTrocaObrigatoria + '" como ocorr�ncia na frequ�ncia do servidor!' );
    }

    if (parseInt(total_de_registros, 10) < parseInt(dias_registrados, 10))
    {
        oTeste.setMsg( 'N�o � permitido confirmar/verificar frequ�ncia faltando dias na ficha do servidor, complete para que seja poss�vel concluir a opera��o!' );
    }

    // se houve erro ser�(�o) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    if (bResultado == false)
    {
        return bResultado;
    }
    else
    {
        $('#form1').submit();
    }

    return false;
}
