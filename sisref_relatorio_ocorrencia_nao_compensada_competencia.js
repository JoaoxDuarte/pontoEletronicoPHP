$(document).ready(function ()
{
    $("#btn-continuar").click(function ()
    {
        showProcessandoAguarde();

        $("#form1").attr("onsubmit", "javascript:return true;");
        $("#form1").attr("action", "sisref_relatorio_ocorrencia_nao_compensada_html.php");
        $('#form1').submit();
    });

    $('#dt-container .input-group.date').datepicker({
        format: "mm/yyyy",
        weekStart: 0,
        startView: 1,
        minViewMode: 1,
        maxViewMode: 2,
        clearBtn: true,
        language: "pt-BR",
        autoclose: true,
        todayHighlight: true,
        toggleActive: true,
        datesDisabled: ['09/01/2009', '30/09/2009']
    }).on('show', function(ev){
        var $this = $(this); //get the offset top of the element
        var eTop  = $this.offset().top; //get the offset top of the element
        $("td.old.disabled.day").css('color', '#e9e9e9');
        $("td.new.disabled.day").css('color', '#e9e9e9');
        $("td.disabled.day").css('color', '#e9e9e9');
        $(".datepicker.datepicker-dropdown.dropdown-menu.datepicker-orient-left.datepicker-orient-top").css('top', (eTop-280));
    });
});
