$(document).ready(function ()
{
    // Set the "bootstrap" theme as the default theme for all Select2
    // widgets.
    //
    // @see https://github.com/select2/select2/issues/2927
    $.fn.select2.defaults.set("theme", "bootstrap");

    var placeholder = "Selecione uma Ocorr�ncia";

    $(".select2-single").select2({
        placeholder: placeholder,
        width: null,
        containerCssClass: ':all:'
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

    $("#btn-continuar").on('click', function(e)
    {
    });
});

function verificadados(modo)
{
    var msg = 'Campos com problemas:';
    var obj = null;
    var modo = (modo == null ? 0 : modo);
    var setor = $('#setor');
    var descricao = $('#descricao');
    var mes_ano_homologacao = $('#mes_ano_homologacao');
    var solicitante = $('#solicitante');
    var email_solicitando = $('#email_solicitando');
    var prorrogado_ate = $('#prorrogado_ate');
    var email_destinatarios = $('#email_destinatarios');

    if (modo == 1)
    {
        if (setor.val().length < 6)
        {
            msg += "\n\tC�digo do Setor inv�lido!";
            obj = setor;
        }
        if (solicitante.val().length < 6)
        {
            msg += "\n\tSolicitante, preenchimento obrigat�rio!";
            obj = (obj == null ? solicitante : obj);
        }
        if (email_solicitando.val().length < 6)
        {
            msg += "\n\tC�pia do email do solicitante, obrigat�rio!";
            obj = (obj == null ? email_solicitando : obj);
        }
        if (prorrogado_ate.val().length < 10)
        {
            msg += "\n\tData inv�lida!";
            obj = (obj == null ? prorrogado_ate : obj);
        }
        if (obj != null)
        {
            alert(msg);
            obj.focus();
            return false;
        }
    }

    if (obj == null)
    {
        var destino = 'gestao_libera_homologacao.php';
        var oForm = $('#form1');
        oForm.action = destino + '?modo=' + modo;
        oForm.submit();
    }

    return true;
}

function ve(parm1)
{
    var enviar = $('#enviar');
    var setor = $('#setor');
    var solicitante = $('#solicitante');
    var email_solicitando = $('#email_solicitando');
    var prorrogado_ate = $('#prorrogado_ate');
    if (setor.val().length == 9 || setor.val().length == 14)
    {
        prorrogado_ate.focus();
        exibeDados();
        
        if (setor.val().length >= 9)
        {
            prorrogado_ate.focus();
        }
    }
    if (prorrogado_ate.val().length > 0)
    {
        enviar.focus();
    }
}
