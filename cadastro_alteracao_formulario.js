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

    $("#btn-continuar").click(function ()
    {
        showProcessandoAguarde();

        $("#form1").attr("onsubmit", "javscript:return true;");
        $("#form1").attr("action", "cadastro_alteracao_grava.php");
        $('#form1').submit();
    });

    $("#btn-voltar-alteracao").click(function ()
    {
        replaceLink("cadastro_alteracao.php");
    });

    $('#dt-container .input-group.date').datepicker({
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

    $('#dt-container').on('changeDate', function() {
        ve(this);
    });

    $('#wlota').change(function() {
        var valor = this.value;
        var str = $( "#loca option:selected" ).val();

        if (str == '00000000' || str == '0000000000000')
        {
            $('#loca option[value="' + valor + '"]').attr("selected",true);
            $('#loca').trigger('change');
        }
    });
    $('#Jornada').change(function() {
        //$('#mothe').val('').change();
        //$('#dthe').val('00/00/0000');
    });



});


function ve(parm1)
{
    // dados
    var wdatinss = $('#wdatinss');
    var datjorn  = $('#datjorn');
    var datlot   = $('#datlot');
    var datloca  = $('#datloca');

    if (wdatinss.val().length == 10)
    {
        if (datjorn.val().trim() == '' || datjorn.val().trim() == '00/00/0000')
        {
            datjorn.val( $('#wdatinss').val() );
        }
        if (datlot.val().trim() == '' || datlot.val().trim() == '00/00/0000')
        {
            datlot.val( $('#wdatinss').val() );
        }
        if (datloca.val().trim() == '' || datloca.val().trim() == '00/00/0000')
        {
            datloca.val( $('#wdatinss').val() );
        }
    }
}
