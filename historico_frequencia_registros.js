
$(document).ready(function () {

    $("#botao_enviar").click(function ()
    {
        return validar();
    });

    $('[data-load-remote-voltar-historico]').on('click',function(e)
    {
        var oForm = $("#form1");
        var $this = $(this);
        var remote = "javascript:window.location.replace('"+$this.data('load-remote-voltar-historico')+"');";

        e.preventDefault();

        console.log(remote);

        oForm.attr("onSubmit", "javascript:return true;");
        oForm.attr("action", remote);
        oForm.submit();
    });

});

function validar()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var teste        = $('#teste').val();        // 88888 registro parcial
    var teste9       = $('#teste9').val();       // 99999 sem frequencia
    var teste_tracos = $('#teste_tracos').val(); // -----
    var teste2       = $('#teste2').val();       // linhas
    var teste3       = $('#teste3').val();       // qtd dias

    var codigoSemFrequenciaPadrao   = $('#codigoSemFrequenciaPadrao').val();
    var codigoRegistroParcialPadrao = $('#codigoRegistroParcialPadrao').val();
    var codigosTrocaObrigatoria     = $('#codigosTrocaObrigatoria').val(); // '-----'

    if (parseInt(teste, 10) > 0 && parseInt(teste9, 10) > 0 && parseInt(teste_tracos, 10) > 0)
    {
        oTeste.setMsg('N�o � permitido homologar frequ�ncia com c�digo ' + codigoRegistroParcialPadrao + ', ' + codigoSemFrequenciaPadrao + '\ne h� dias com "' + codigosTrocaObrigatoria + '" como ocorr�ncia na frequ�ncia do servidor!');
    }
    else if (parseInt(teste, 10) > 0 && parseInt(teste9, 10) > 0)
    {
        oTeste.setMsg('N�o � permitido homologar frequ�ncia com c�digo ' + codigoRegistroParcialPadrao + ' e ' + codigoSemFrequenciaPadrao + ' na frequ�ncia do servidor!');
    }
    else if (parseInt(teste, 10) > 0 && parseInt(teste_tracos, 10) > 0)
    {
        oTeste.setMsg('N�o � permitido homologar frequ�ncia com c�digo ' + codigoRegistroParcialPadrao + '\ne h� dias com "' + codigosTrocaObrigatoria + '" como ocorr�ncia na frequ�ncia do servidor!');
    }
    else if (parseInt(teste9, 10) > 0 && parseInt(teste_tracos, 10) > 0)
    {
        oTeste.setMsg('N�o � permitido homologar frequ�ncia com c�digo ' + codigoSemFrequenciaPadrao + '\ne h� dias com "' + codigosTrocaObrigatoria + '" como ocorr�ncia na frequ�ncia do servidor!');
    }
    else if (parseInt(teste, 10) > 0)
    {
        oTeste.setMsg('N�o � permitido homologar frequ�ncia com c�digo ' + codigoRegistroParcialPadrao + ' na frequ�ncia do servidor!');
    }
    else if (parseInt(teste9, 10) > 0)
    {
        oTeste.setMsg('N�o � permitido homologar frequ�ncia com c�digo ' + codigoSemFrequenciaPadrao + ' na frequ�ncia do servidor!');
    }
    else if (parseInt(teste_tracos, 10) > 0)
    {
        oTeste.setMsg('N�o � permitido homologar frequ�ncia com dias\ne h� dias com "' + codigosTrocaObrigatoria + '" como ocorr�ncia na frequ�ncia do servidor!');
    }

    if (parseInt(teste2, 10) < parseInt(teste3, 10))
    {
        oTeste.setMsg('Est� faltando dias na ficha do servidor complete para que seja poss�vel homologar !');
    }

    // se houve erro ser�(�o) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    if (bResultado == false)
    {
        return bResultado;
    }
    else
    {
        showProcessandoAguarde();
        
        $("#form1").attr("onsubmit", "javascript:return true;");
        $("#form1").attr("action",   "historico_frequencia_concluir.php");
        $('#form1').submit();
    }

    return false;
}
