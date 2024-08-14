
$(document).ready(function ()
{
    $('form1').keypress(function (e)
    {
        if (e.which == 27 || e.keyCode == 27)
        {
            return verificadados();
        }
    });

    $('input').keypress(function (e)
    {
        if (e.which == 27 || e.keyCode == 27)
        {
            return verificadados();
        }
    });
});

/*-------------------------------------------------------\
 |     Validação                                          |
 \-------------------------------------------------------*/
function verificadados()
{
    // dados
    var form_dados = $('#form1').serialize();
    var destino    = "confirmareiniciar_rh.php"

    // mensagem processando
    showProcessando();

    //create the ajax request
    $.ajax({
        type: "POST",
        url: destino, // a pagina que sera chamada
        data: form_dados, // dados enviados
        timeout: 3000,
        dataType: "json",
        beforeSend: function ()
        {
            // enquanto a função esta sendo processada, você
            // pode exibir na tela uma msg de carregando
            //$('#mensagem_aviso').html('<img src="imagem/loading.gif"/>');
        },
        success: function (response)
        {
            var ojson = response.dados;
            // Número de itens
            var tam = ojson.length;

            if (tam == 0 || ojson[0].mensagem == null)
            {
                mostraMensagem('Registro não localizado.', 'danger');
            }
            else if (ojson[0].siape == '')
            {
                mostraMensagem(ojson[0].mensagem, 'danger');
            }
            else
            {
                mostraMensagem(ojson[0].mensagem, 'success', voltarOrigem);
                return false;
            }
        },
        error: function (txt)
        {
            // em caso de erro
            hideProcessando();
            mostraMensagem('Houve um problema interno. Tente novamente.', 'danger');
        },
        complete: function (data)
        {
            hideProcessando();
        }

    });

    return true;
}

function ve(parm1)
{
    var cpf      = $('#cpf');
    var idunica  = $('#idunica');
    var siapecad = $('#siapecad');
    
    if (cpf.val().length == 11)
    {
        idunica.focus();
    }
    if (idunica.val().length == 9)
    {
        siapecad.focus();
    }
}
