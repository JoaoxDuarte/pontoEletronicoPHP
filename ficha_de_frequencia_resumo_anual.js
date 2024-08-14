$(document).ready(function ()
{
    $("body").keypress(function (e)
    {
        if (e.which == 13 || e.keyCode == 13)
        {
            return verificadados();
        }
    });

    $("form").keypress(function (e)
    {
        if (e.which == 13 || e.keyCode == 13)
        {
            return verificadados();
        }
    });

    $("input").keypress(function (e)
    {
        if (e.which == 13 || e.keyCode == 13)
        {
            return verificadados();
        }
    });
    
    $("#btn-continuar").click(function ()
    {
        return verificadados();
    });

    $("#btn-continuar-mixer").click(function ()
    {
        return verificadados();
    });
});


function verificadados()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var siape = $('#siape');
    var ano   = $('#ano');
    var anoa  = $('#anoa');

    var mensagem = '';

    // validacao do campo siape
    // testa o tamanho
    mensagem = validaSiape(siape.val());
    if (mensagem != '')
    {
        oTeste.setMsg(mensagem, siape);
    }

    if (ano.val().length < 4)
    {
        oTeste.setMsg('O ano é obrigatório!', ano);
    }
    if (ano.val() < 2009 || ano.val() > anoa.val())
    {
        oTeste.setMsg('O ano é inválido!', ano);
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    if (bResultado == false)
    {
        return bResultado;
    }
    else
    {
        $("#form1").attr("action", "ficha_de_frequencia_resumo_anual_html.php");
        $('#form1').submit();
    }
}

function ve()
{
    var siape = $('#siape');
    var ano = $('#ano');
    if (siape.val().length == 7)
    {
        ano.focus();
    }
    return true;
}
