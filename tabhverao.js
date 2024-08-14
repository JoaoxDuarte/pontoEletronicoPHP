$(document).ready(function ()
{
    $('#date .input-group.date').datepicker({
        format: "dd/mm/yyyy",
        maxViewMode: 0,
        language: "pt-BR",
        autoclose: true,
        todayHighlight: true,
        toggleActive: true,
        orientation: "bottom",
        datesDisabled: ['10/06/2018', '10/21/2018']
    }).on('show', function(ev){
        var $this = $(this); //get the offset top of the element
        var eTop  = $this.offset().top; //get the offset top of the element
        $("td.old.disabled.day").css('color', '#e9e9e9');
        $("td.new.disabled.day").css('color', '#e9e9e9');
        $("td.disabled.day").css('color', '#e9e9e9');
        $(".datepicker.datepicker-dropdown.dropdown-menu.datepicker-orient-left.datepicker-orient-bottom").css('top', (eTop+10));
    });

    $("#btn-gravar").click(function ()
    {
        return verificadados();
    });
});

// variaveis para uso
// em todo o script
var modo   = null;
var id     = null;
var ano    = null;
var inicio = null;
var fim    = null;

function verificadados()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    modo   = $('#modo');
    id     = $('#id');
    ano    = $('#ano');
    inicio = $('#inicio');
    fim    = $('#fim');

    if (data_valida(inicio.val()) == false)
    {
        oTeste.setMsg('- A data inicial é obrigatória!', inicio);
    }
    if (data_valida(fim.val()) == false)
    {
        oTeste.setMsg('- A data final é obrigatória!', fim);
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    if (bResultado == false)
    {
        return bResultado;
    }

    testa();

    return bResultado;
}

function testa()
{
    var destino = "gravahorario.php?"
        + "modo=" + modo.val() 
        + "&id=" + id.val() 
        + "&ano=" + ano.val() 
        + "&inicio=" + inicio.val() 
        + "&fim=" + fim.val();

    $("#form1").attr("onsubmit", "javascript:return true;");
    $("#form1").attr("action", destino);
    $('#form1').submit();
}
