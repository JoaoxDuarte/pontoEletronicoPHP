$(document).ready(function ()
{
    $('form1').keypress(function (e)
    {
        if (e == 13)
        {
            return verificadados();
        }
    });

    $('input').keypress(function (e)
    {
        if (e.which == 13)
        {
            return verificadados();
        }
    });
});

function verificadados()
{
    // objeto mensagem
    var oTeste = new alertaErro();
    oTeste.init();

    // dados
    var msg   = '';
    var siape = $('#pSiape');
    var tipo  = $('#tipo');

    var destino = "javascript:window.location.replace('reiniciar3_rh.php?pSiape=" + siape.value + "');";

    // valida o dia
    msg = validaSiape(siape.value, true);
    if (msg != '')
    {
        oTeste.setMsg(msg, siape);
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    if (bResultado == true)
    {
        $("#form1").attr("action", destino);
        $('#form1').submit();
    }

    return bResultado;
}
