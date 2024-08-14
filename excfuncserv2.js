$(document).ready(function ()
{
    // Set the "bootstrap" theme as the default theme for all Select2
    // widgets.
    //
    // @see https://github.com/select2/select2/issues/2927
    $.fn.select2.defaults.set("theme", "bootstrap");

    var placeholder = "Selecione uma Ocorrência";

    $(".select2-single").select2({
        placeholder: placeholder,
        width: null,
        containerCssClass: ':all:'
    });

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
});


function verificadados()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();
/*
    // dados
    var matricula = $('#matricula');
    var exclusao  = $('#exclusao');

    var bErro    = false;
    var sMsgErro = "Digite/Selecione:\n";

    var mensagem = '';

    // validacao do campo siape
    // testa o tamanho
    mensagem = validaSiape(matricula.val());
    if (mensagem != '')
    {
        oTeste.setMsg( sMsgErro + '.É obrigatório informar a matrícula com no mínimo 7 caracteres!', matricula);
    }

    if (ocupacao.val() == "V")
    {
        oTeste.setMsg((bErro ? '' : sMsgErro) + '.Selecione a situação!');
        bErro = true;
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    if (bResultado == false)
    {
        return bResultado;
    }
    else
    {
*/
        $("#form1").attr("onsubmit", "javascript:return true;");
        $("#form1").attr("action", "excfuncserv3.php");
        $('#form1').submit();
/*
    }
*/
}
