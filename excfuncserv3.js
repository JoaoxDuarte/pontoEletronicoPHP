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

    $('#dt-container .input-group.date').datepicker({
        format: "dd/mm/yyyy",
        language: "pt-BR",
        //daysOfWeekDisabled: "0,6",
        startDate: "01/01/1900",
        autoclose: true,
        todayHighlight: true,
        toggleActive: true
    });
});


function verificadados()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();
/*
    // dados
    var Nnum3      = $('#Nnum3');
    var Ndata3     = $('#Ndata3');
    var publicacao = $('#publicacao');
    var Nnum4      = $('#Nnum4');
    var Ndata4     = $('#Ndata4');
    var fim        = $('#fim');

    var bErro    = false;
    var sMsgErro = "Digite:\n";

    if (Nnum3.val().length == 0)
    {
        oTeste.setMsg(sMsgErro + '.O numero da portaria!', Nnum3);
        bErro = true;
    }
    if (Ndata3.val().length < 10)
    {
        oTeste.setMsg((bErro ? '' : sMsgErro) + '.A data da portaria no formato dd/mm/aaaa!', Ndata3);
        bErro = true;
    }
    if (publicacao.val() == '00')
    {
        oTeste.setMsg((bErro ? '' : sMsgErro) + '.Selecionar o meio de publicação!');
        bErro = true;
    }
    if (Nnum4.val().length == 0)
    {
        oTeste.setMsg((bErro ? '' : sMsgErro) + '.O número do meio de publicação!', Nnum4);
        bErro = true;
    }
    if (Ndata4.val().length < 10)
    {
        oTeste.setMsg((bErro ? '' : sMsgErro) + '.A data da publicação no formato dd/mm/aaaa!', Ndata4);
        bErro = true;
    }
    if (fim.val().length < 10)
    {
        oTeste.setMsg((bErro ? '' : sMsgErro) + '.A data de fim do exercício da função!', fim);
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
        $("#form1").attr("action", "grava_inclui_funcserv.php");
        $('#form1').submit();
/*
    }
*/
}
