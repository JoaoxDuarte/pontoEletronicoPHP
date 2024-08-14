$(document).ready(function ()
{
    // Set the "bootstrap" theme as the default theme for all Select2
    // widgets.
    //
    // @see https://github.com/select2/select2/issues/2927
    $.fn.select2.defaults.set("theme", "bootstrap");

    var placeholder = "Selecione uma lotação";

    $(".select2-single").select2({
        placeholder: placeholder,
        width: '100%',
        containerCssClass: ':all:'
    });
    
    $("#btn-enviar").click(function ()
    {
        var lotacao = $('#lotacao').val();

    	if (verificadados())
        {
            AbrirSisref('reghorario_grade.php?lotacao=' + lotacao, 'quadro', 900);
        }
    });
});

function verificadados()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var lotacao = $('#lotacao');

    if (lotacao.val() == '')
    {
        oTeste.setMsg('.Selecione uma unidade!');
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    
    return bResultado;
}

// combobox
$(function ()
{
    //$("#lotacao").combobox();
});
