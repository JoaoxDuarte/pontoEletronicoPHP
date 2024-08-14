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
        mostraMensagem('Por favor digite a matrícula com 7 dígitos', 'danger');
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
        //    mostraMensagem('Arquivo de destino não encontrado!', 'danger');
        //})
        $('#form1').attr('onsubmit', 'javascript:return true;');
        $('#form1').attr('action', 'substfunc.php');
        $("#form1").submit();
        return true;
    }
}
