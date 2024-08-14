$(document).ready(function () {

    $('#dt-container .input-group.date').datepicker({
        format: "dd/mm/yyyy",
        startDate: "10/01/2009",
        //endDate: "31/12/2019",
        maxViewMode: 0,
        language: "pt-BR",
        autoclose: true,
        todayHighlight: true,
        toggleActive: true,
        datesDisabled: ['10/06/2018', '10/21/2018']
    }).on('show', function(ev){
        var $this = $(this); //get the offset top of the element
        var eTop  = $this.offset().top; //get the offset top of the element
        $("td.old.disabled.day").css('color', '#e9e9e9');
        $("td.new.disabled.day").css('color', '#e9e9e9');
        $("td.disabled.day").css('color', '#e9e9e9');
        $(".datepicker.datepicker-dropdown.dropdown-menu.datepicker-orient-left.datepicker-orient-top").css('top', (eTop-280));
    });

    $("#btn-salvar-registro").on('click', function (){
        var oForm = $("#form1");

        if (validar())
        {
            oForm.attr('onsubmit', 'javascript:return true;');
            oForm.attr('action', 'grava_inclui_funcserv.php');
            oForm.submit();
        }
    });

    $('#datapt').mask('##/##/####');
});


function validar()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var portaria = $('#portaria');
    var datapt   = $('#datapt');

    if (portaria.val().length == 0)
    {
        oTeste.setMsg('É obrigatório informar os dados da portaria!', portaria);
    }

    if (datapt.val().length < 10)
    {
        oTeste.setMsg('É obrigatório informar a data da portaria no formato dd/mm/aaaa!', datapt);
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    return bResultado;
}
