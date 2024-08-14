$(document).ready(function ()
{
    $("#btn-continuar").click(function ()
    {
        return validar();
    });
});

function validar()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var siape = $('#siape');

    var mensagem = '';

    // validacao do campo siape
    // testa o tamanho
    mensagem = validaSiape(siape.val());
    if (mensagem != '')
    {
        oTeste.setMsg(mensagem, siape);
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    if (bResultado == false)
    {
        return bResultado;
    }
    else
    {
        $("#form1").attr("onsubmit", "javascript:return true;");
        $("#form1").attr("action", "movimentaservidor.php");
        $('#form1').submit();
    }
}
