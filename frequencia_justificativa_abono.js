$(document).ready(function ()
{

    $("#btn-continuar").click(function ()
    {
        // objeto mensagem
        oTeste = new alertaErro();
        oTeste.init();

        // dados
        var siape = $('#mat').val();
        var dia = $('#dia').val();
        var cmd = $('#cmd').val();
        var oco = $('#oco').val();
        var grupo = $('#grupo').val();

        var justchef = $('#justchef');
        var justchef_value = trim(justchef.val());

        if (justchef_value.length < 15)
        {
            oTeste.setMsg('É obrigatório o preenchimento da justificativa da chefia com no mínimo 15 caracteres!', justchef);
        }

        // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
        var bResultado = oTeste.show();
        if (bResultado == false)
        {
            return bResultado;
        }
        else
        {
            var parametros = base64_encode(siape + ":|:" + dia + ":|:" + cmd + ":|:" + oco + ":|:" + justchef_value + ":|:" + grupo);
            $('#dados').val(parametros);
            var destino = "javascript:window.location.replace('frequencia_gravar_abono.php?dados=" + parametros + "');";

            $("#form1").attr("action", destino);
            $("#form1").submit();
        }

        return true;
    });

});
