$(document).ready(function ()
{
    $('[name="btn-enviar"]').on('click', function (e)
    {
        if (verificadados() == true)
        {
            enviar_dados();
        }
    });

    $('.cpf').mask('000.000.000-00', {reverse: true});
});

/*-------------------------------------------------------\
 |     Valida��o                                          |
 \-------------------------------------------------------*/
function verificadados()
{
    // dados
    var cpf   = $('#cpf').val();
    var siape = $('#siape').val();

    msg = validaSiape(siape);

    if (validarCPF( cpf ) == false)
    {
        msg = (msg == '' ? '' : '. ' + msg + '<br>') + '. CPF informado, inv�lido!';
    }

    if (msg != '')
    {
        mostraMensagem( msg, 'danger');
        return false;
    }

    return true;
}

/*-------------------------------------------------------\
 |     Enviar os dados para grava��o                     |
 \------------------------------------------------------*/
function enviar_dados()
{
    // dados
    var form_dados = $('#form1').serialize();
    var destino    = "reiniciar1_gravar.php";

    // mensagem processando
    showProcessandoAguarde();

    //create the ajax request
    $.ajax({

        type: "POST",
        url: destino,
        data: form_dados,
        dataType: "json"

    }).done(function(response) {
        console.log(response.length);

        var ojson = response[0];
        var tam   = ojson.length; // N�mero de itens

        console.log( ojson.mensagem );

        hideProcessandoAguarde();

        if (tam == 0 || ojson.mensagem == null)
        {
            mostraMensagem("Registro n�o localizado!", 'danger');
        }
        else if (ojson.siape == '')
        {
            mostraMensagem(ojson.mensagem, 'danger');
        }
        else
        {
            mostraMensagem(ojson.mensagem, 'success', voltarOrigem);
            return false;
        }

    }).fail(function(jqXHR, textStatus ) {

        console.log("Request failed: " + textStatus);
        hideProcessandoAguarde();
        mostraMensagem('Houve um problema interno: ' + textStatus + '.', 'danger');

    }).always(function() {

        console.log("completou");
        hideProcessandoAguarde();

    });

    return true;
}
