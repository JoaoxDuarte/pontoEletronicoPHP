$(document).ready(function ()
{
    $('form').keypress(function (e)
    {
        if (e.which == 13 || e.keyCode == 13)
        {
            return false;
        }
    });

    $('input').keypress(function (e)
    {
        if (e.which == 13 || e.keyCode == 13)
        {
            verificadados();
        }
    });

    $('#dnu-container .input-group.date').datepicker({
        format: "dd/mm/yyyy",
        language: "pt-BR",
        //daysOfWeekDisabled: "0,6",
        startDate: "01/01/1990",
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

    $('#btn-enviar').click(function ()
    {
        verificadados();
    });

    $('#dnu').focus();

});

hideProcessando();

var interval = 0;

function verificadados()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var dnu  = $('#dnu');
    var dnu2 = $('#dnu2');

    if (dnu.val() == '' || dnu.val().length == 0)
    {
        oTeste.setMsg('O dia é obrigatório!', dnu);
    }
    else if (data_valida(dnu.val()) == false)
    {
        oTeste.setMsg('Data inválida!', dnu);
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    if (bResultado == false)
    {
        return bResultado;
    }

    envia_dados();

    return true;
}

function envia_dados()
{
    var siape  = $('#tSiape').val();
    var nome   = $('#sNome').val();
    var dnu    = $('#dnu').val();
    var codmun = $('#codmun').val();

    var parametros = base64_encode(siape + ":|:" + nome + ":|:" + dnu + ":|:" + codmun);
    $('#dados').val( parametros );

    var destino = "javascript:window.location.replace('autorizacao_trabalho_dia_nao_util_solicitacao_grava.php?dados=" + parametros + "');";

    // mensagem processando
    showProcessandoAguarde();

    $("#form1").attr("onsubmit", "javascript:return true;");
    $("#form1").attr("action", destino);
    $('#form1').submit();
}
