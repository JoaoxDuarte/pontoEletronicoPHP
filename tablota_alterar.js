
$(document).ready(function () {

    // Set the "bootstrap" theme as the default theme for all Select2
    // widgets.
    //
    // @see https://github.com/select2/select2/issues/2927
    $.fn.select2.defaults.set("theme", "bootstrap");

    var placeholder = "Selecione uma op��o";

    $(".select2-single").select2({
        placeholder: placeholder,
        width: '100%',
        containerCssClass: ':all:'
    });
    
    $('#btn-salvar').on('click', function () {
        
        if( verificadados() )
        {
            showProcessandoAguarde();
        
            $('#form1').attr('onsubmit', "javascript:return true;");
            $('#form1').attr('action', "gravaalteralot.php");
            $('#form1').submit();
        }
        
    });
});

function verificadados()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var descricao = $('#descricao');
    var sUorg     = $('#sUorg');
    var upai      = $('#upai');
    var upag      = $('#upag');
    var sUg       = $('#sUg');
    var area      = $('#area');
    var inicio    = $('#inicio');
    var fim       = $('#fim');

    if (descricao.val().length == 0)
    {
        oTeste.setMsg("A Descricao � obrigat�ria !", descricao);
    }
    
    if (sUorg.val().length == 0)
    {
        oTeste.setMsg("A Uorg � um campo obrigat�rio !", sUorg);
    }
    
    if (upag.val().length == 0)
    {
        oTeste.setMsg("A Upag � um campo obrigat�rio !", upag);
    }

    // se houve erro ser�(�o) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    return bResultado;
}

$(".horas").mask('##:##:00');
