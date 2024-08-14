$(document).ready(function ()
{
    $("body").keypress(function (e)
    {
        if (e.which == 27 || e.keyCode == 27)
        {
            return validar();
        }
    });

    $("form").keypress(function (e)
    {
        if (e.which == 13 || e.keyCode == 13)
        {
            return validar();
        }
    });

    $("input").keypress(function (e)
    {
        if (e.which == 13 || e.keyCode == 13)
        {
            return validar();
        }
    });
    
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
        $("#form1").attr("action", "cadastro_exclusao_cancela_formulario.php");
        $('#form1').submit();
    }
}

function ve(parm1)
{
    var siape = $('siape').val();
    if (siape.length >= 7)
    {
        validar();
    }
}
