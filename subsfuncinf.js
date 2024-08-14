$(document).ready(function ()
{
    
    $('form').keypress(function (e)
    {
        if (e.which == 13)
        {
            return validar();
        }
    });

    $('input').keypress(function (e)
    {
        if (e.which == 13)
        {
            return validar();
        }
    });
    
    $('#btn-continuar').click(function ()
    {
        validar();
    });
});

$('#matricula').focus();


function validar()
{
    var matricula = $('#matricula');
    if (matricula.val().length < 7)
    {
        matricula.focus();
        mostraMensagem('Por favor digite a matr�cula com 7 d�gitos', 'danger');
        return false;
    }
    else
    {
        //var jqxhr = $.post("substfunc.php", 
        //    $( "#form1" ).serialize()
        //)
        //.done(function(data) {
        //    $("div.container:eq(1)").html( data );
        //})
        //.fail(function() {
        //    mostraMensagem('Arquivo de destino n�o encontrado!', 'danger');
        //})
        $('#form1').attr('onsubmit', 'javascript:return true;');
        $('#form1').attr('action', 'substfunc.php');
        $("#form1").submit();
        return true;
    }
}
