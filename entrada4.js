$(document).ready(function ()
{
    $("#btn-enviar").click(function ()
    {
        return verificadados();
    });
});

function verificadados()
{
    var destinacao = $('#destinacao');

    if (destinacao.val() == 0)
    {
        mostraMensagem("É obrigatória a destinação do crédito de horas !");
        destinacao.focus();
        return false;
    }
    else
    {
        $('#form1').attr('onsubmit', 'javascript:return true;');
        $('#form1').attr('action', 'gravaregfreq2.php');
        $('#form1').submit();
    }
}
