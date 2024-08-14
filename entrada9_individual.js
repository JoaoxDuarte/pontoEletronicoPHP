$(document).ready(function ()
{
    $("#btn-continuar-mixer").click(function ()
    {
        validar();
    });

});


function validar()
{
    var mat_siape = $('#siape');

    if (mat_siape.val().length < 7)
    {
        mostraMensagem("Matrícula SIAPE é obrigatória, com 7 caracteres!");
        mat_siape.focus();
        return false;
    }
    else
    {
        $('#fomr1').attr('action', 'entrada9.php');
        $('#form1').submit();
    }
}

function ve(parm1)
{
}
