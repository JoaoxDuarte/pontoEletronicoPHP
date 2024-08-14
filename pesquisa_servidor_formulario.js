$(document).ready(function ()
{
    $("form").keypress(function (e) {
        if (e.witch == 13)
        {
            validar();
        }
    });

    $("#btn-continuar").click(function () {
        validar();
    });

});

function validar()
{
    if ($('#chave').val().length == 0)
    {
        $('#chave').focus();
        mostraMensagem('É obrigatório informar a chave para pesquisa!', 'warning');
        return false;
    }
    else
    {
        // mensagem processando
        showProcessandoAguarde();

        $('#form1').attr('onsubmit', "javascript:return true;");
        $('#form1').attr('action', $('#form_action').val());
        $('#form1').submit();
    }
}
