
$(document).ready(function ()
{
    $('form').keypress(function (e)
    {
        if (e.which == 13)
        {
            verificadados();
        }
    });
    
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

    $("#btn-continuar").click(function ()
    {
        verificadados();
    });

    $('#Ndata1-container .input-group.date').datepicker({
        format: "dd/mm/yyyy",
        language: "pt-BR",
        daysOfWeekDisabled: "0,6",
        orientation: "bottom auto",
        autoclose: true,
        todayHighlight: true,
        toggleActive: true,
        maxViewMode: 0,
        datesDisabled: ['10/06/2018', '10/21/2018']
    }).on('show', function(ev){
        var $this = $(this); //get the offset top of the element
        var eTop  = $this.offset().top; //get the offset top of the element
        $("td.old.disabled.day").css('color', '#e9e9e9');
        $("td.new.disabled.day").css('color', '#e9e9e9');
        $("td.disabled.day").css('color', '#e9e9e9');
        $(".datepicker.datepicker-dropdown.dropdown-menu.datepicker-orient-left.datepicker-orient-bottom").css('top', (eTop+10));
    });

    $('#Ndata2-container .input-group.date').datepicker({
        format: "dd/mm/yyyy",
        language: "pt-BR",
        daysOfWeekDisabled: "0,6",
        orientation: "bottom auto",
        autoclose: true,
        todayHighlight: true,
        toggleActive: true,
        maxViewMode: 0,
        datesDisabled: ['10/06/2018', '10/21/2018']
    }).on('show', function(ev){
        var $this = $(this); //get the offset top of the element
        var eTop  = $this.offset().top; //get the offset top of the element
        $("td.old.disabled.day").css('color', '#e9e9e9');
        $("td.new.disabled.day").css('color', '#e9e9e9');
        $("td.disabled.day").css('color', '#e9e9e9');
        $(".datepicker.datepicker-dropdown.dropdown-menu.datepicker-orient-left.datepicker-orient-bottom").css('top', (eTop+10));
    });
});


function verificadados()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var Ndata1 = $('#Ndata1');
    var Ndata2 = $('#Ndata2');
    var motivo = $('#motivo');

    if (Ndata1.val().length < 10)
    {
        oTeste.setMsg('Por favor informe a data de início (dd/mm/aaaa)', Ndata1);
    }
    else if (data_valida(Ndata1.val()) == false)
    {
        oTeste.setMsg('Data de início inválida', Ndata1);
    }

    if (Ndata2.val().length < 10)
    {
        oTeste.setMsg('Por favor informe a data de encerramento (dd/mm/aaaa)', Ndata2);
    }
    else if (data_valida(Ndata2.val()) == false)
    {
        oTeste.setMsg('Data de encerramento inválida', Ndata2);
    }

    if (motivo.val() == 0)
    {
        oTeste.setMsg('Por favor informe o motivo', motivo);
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    if (bResultado == true)
    {
        var oForm = $('#form1');
        
        // mensagem processando
        showProcessandoAguarde();
        
        oForm.attr('onsubmit', 'javascript:return true');
        oForm.attr('action', 'grava_inclui_funcserv.php');
        oForm.submit();
    }

    return bResultado;
}
