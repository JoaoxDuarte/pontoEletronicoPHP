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
    $("#lota").prop("disabled", true);

    $('#dt-container .input-group.date').datepicker({
        format: "dd/mm/yyyy",
        language: "pt-BR",
        //daysOfWeekDisabled: "0,6",
        startDate: "01/01/1900",
        autoclose: true,
        todayHighlight: true,
        toggleActive: true
    });

    $('#dt_inglot .input-group.date.datepicker').datepicker({
        format: "dd/mm/yyyy",
        language: "pt-BR",
        //daysOfWeekDisabled: "0,6",
        autoclose: true,
        todayHighlight: true,
        toggleActive: true,
        enableOnReadonly: false
    });

    $("#btn-continuar").click(function ()
    {
        return verificadados();
    });
});


function verificadados()
{
    // objeto mensagem
    //oTeste = new alertaErro();
    //oTeste.init();

    // dados
    //var dtsai    = $('#dtsai');
    //var novalota = $('#novalota');
    //var dtingn   = $('#dtingn');

    /*
    if (dtsai.val().length == 0)
    {
        oTeste.setMsg("A SAIDA é obrigatória !", dtsai);
    }
    if (novalota.val() == '00000000' || novalota.val() == '0000000000000')
    {
        oTeste.setMsg("A NOVA UNIDADE é obrigatória !", novalota);
    }
    if (dtingn.val().length == 0)
    {
        oTeste.setMsg("A DATA DE INGRESSO é obrigatória !", dtingn);
    }
    */

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    //var bResultado = oTeste.show();
    //if (bResultado == false)
    //{
    //    return bResultado;
    //}
    //else
    //{
        $("#form1").attr("onsubmit", "javascript:return true;");
        $("#form1").attr("action", "gravamovservidor.php");
        $('#form1').submit();
    //}
}
