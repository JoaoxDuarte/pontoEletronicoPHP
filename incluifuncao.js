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
        if (e.which == 27 || e.keyCode == 27)
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

    $('#dt-container .input-group.date').datepicker({
        format: "dd/mm/yyyy",
        language: "pt-BR",
        //daysOfWeekDisabled: "0,6",
        startDate: "01/01/1900",
        autoclose: true,
        todayHighlight: true,
        toggleActive: true
    }).on('show', function(ev){
        var $this = $(this); //get the offset top of the element
        var eTop  = $this.offset().top; //get the offset top of the element
        $("td.old.disabled.day").css('color', '#e9e9e9');
        $("td.new.disabled.day").css('color', '#e9e9e9');
        $("td.disabled.day").css('color', '#e9e9e9');
        $(".datepicker.datepicker-dropdown.dropdown-menu.datepicker-orient-left.datepicker-orient-top").css('top', (eTop-280));
    });

    $("#btn-continuar").click(function ()
    {
        return verificadados();
    });
});

function verificadados()
{
    oTeste = new alertaErro();
    oTeste.init();
/*
    // dados
    var inicio     = $('#inicio');
    var Nnum1      = $('#Nnum1');
    var Ndata1     = $('#Ndata1');
    var Nnum2      = $('#Nnum2');
    var Ndata2     = $('#Ndata2');
    var publicacao = $('#publicacao');

    var bErro = false;
    var sMsgErro = "Digite:\n";

    if (inicio.val().length < 0)
    {
        oTeste.setMsg(sMsgErro + '.A data do documento no formato dd/mm/aaaa!', inicio);
        bErro = true;
    }
    if (Nnum1.val().length == 0)
    {
        oTeste.setMsg(sMsgErro + '.O numero da portaria!', Nnum1);
        bErro = true;
    }
    if (Ndata1.val().length < 10)
    {
        oTeste.setMsg((bErro ? '' : sMsgErro) + '.A data da portaria no formato dd/mm/aaaa!', Ndata1);
        bErro = true;
    }
    if (publicacao.value == '00')
    {
        oTeste.setMsg((bErro ? '' : sMsgErro) + '.Selecionar o meio de publicação!');
        bErro = true;
    }
    if (Nnum2.val().length == 0)
    {
        oTeste.setMsg((bErro ? '' : sMsgErro) + '.O número do meio de publicação!', Nnum2);
        bErro = true;
    }
    if (Ndata2.val().length < 10)
    {
        oTeste.setMsg((bErro ? '' : sMsgErro) + '.A data da publicação no formato dd/mm/aaaa!', Ndata2);
        bErro = true;
    }
*/
    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    if (bResultado == false)
    {
        return bResultado;
    }
    else
    {
        $("#form1").attr("onsubmit", "javascript:return true;");
        $("#form1").attr("action", "grava_inclui_funcserv.php");
        $('#form1').submit();
    }
}
