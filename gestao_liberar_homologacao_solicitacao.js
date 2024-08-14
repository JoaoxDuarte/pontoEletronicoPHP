$(document).ready(function ()
{

    $("#btn-enviar").click(function (e)
    {
        e.preventDefault();
        if (verificar())
        {
            var justificativa = $('#justificativa').val();
            var unidade       = $('#unidade').val();
            var parametros    = base64_encode(justificativa+':|:'+unidade);

            //$('#dados').val(parametros);
            var destino = "javascript:window.location.replace('gestao_liberar_homologacao_solicitacao_gravar.php?dados=" + parametros + "');";

            $("#form1").attr("onsubmit", "javascript:return true;");
            $("#form1").attr("action", destino);
            $("#form1").submit();
        }
    });
});

function verificar()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var justificativa = $('#justificativa');
    var justificativa_value = trim(justificativa.val());

    if (justificativa_value.length < 15)
    {
        oTeste.setMsg('� obrigat�rio o preenchimento da justificativa com no m�nimo 15 caracteres!', justificativa);
    }

    // se houve erro ser�(�o) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    if (bResultado == false)
    {
        return bResultado;
    }

    return true;
}