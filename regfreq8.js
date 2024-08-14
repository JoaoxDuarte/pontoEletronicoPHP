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
    var oco88888           = $('#teste').val();        // ocorrência : registro parcial
    var oco99999           = $('#teste9').val();       // ocorrência : sem frequencia
    var ocoTracos          = $('#teste_tracos').val(); // ocorrência : '-----'
    var total_de_registros = $('#teste2').val();       // total de registros
    var dias_registrados   = $('#teste3').val();       // dias registrados

    var codigoSemFrequenciaPadrao   = $("#codigoSemFrequenciaPadrao").val();
    var codigoRegistroParcialPadrao = $("#codigoRegistroParcialPadrao").val();
    var codigosTrocaObrigatoria     = $("#codigosTrocaObrigatoria").val();

    if (parseInt(oco88888, 10) > 0 && parseInt(oco99999, 10) > 0 && parseInt(ocoTracos, 10) > 0)
    {
        oTeste.setMsg( 'Não é permitido confirmar/verificar frequência homologada com código ' + codigoRegistroParcialPadrao + ', ' + codigoSemFrequenciaPadrao + ' e dias com "' + codigosTrocaObrigatoria + '" como ocorrência na frequência do servidor!' );
    }
    else if (parseInt(oco88888, 10) > 0 && parseInt(oco99999, 10) > 0)
    {
        oTeste.setMsg( 'Não é permitido confirmar/verificar frequência homologada com código ' + codigoRegistroParcialPadrao + ' e ' + codigoSemFrequenciaPadrao + ' na frequência do servidor!' );
    }
    else if (parseInt(oco88888, 10) > 0 && parseInt(ocoTracos, 10) > 0)
    {
        oTeste.setMsg( 'Não é permitido confirmar/verificar frequência homologada com código ' + codigoRegistroParcialPadrao + ' e dias com "' + codigosTrocaObrigatoria + '" como ocorrência na frequência do servidor!' );
    }
    else if (parseInt(oco99999, 10) > 0 && parseInt(ocoTracos, 10) > 0)
    {
        oTeste.setMsg( 'Não é permitido confirmar/verificar frequência homologada com código ' + codigoSemFrequenciaPadrao + ' e dias com "' + codigosTrocaObrigatoria + '" como ocorrência na frequência do servidor!' );
    }
    else if (parseInt(oco88888, 10) > 0)
    {
        oTeste.setMsg( 'Não é permitido confirmar/verificar frequência homologada com código ' + codigoRegistroParcialPadrao + ' na frequência do servidor!' );
    }
    else if (parseInt(oco99999, 10) > 0)
    {
        oTeste.setMsg( 'Não é permitido confirmar/verificar frequência homologada com código ' + codigoSemFrequenciaPadrao + ' na frequência do servidor!' );
    }
    else if (parseInt(ocoTracos, 10) > 0)
    {
        oTeste.setMsg( 'Não é permitido confirmar/verificar frequência homologada com "' + codigosTrocaObrigatoria + '" como ocorrência na frequência do servidor!' );
    }

    if (parseInt(total_de_registros, 10) < parseInt(dias_registrados, 10))
    {
        oTeste.setMsg( 'Não é permitido confirmar/verificar frequência faltando dias na ficha do servidor, complete para que seja possível concluir a operação!' );
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
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
