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
        mostraMensagem("� obrigat�ria a destina��o do cr�dito de horas !");
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
