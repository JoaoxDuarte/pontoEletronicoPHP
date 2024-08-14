
$(document).ready(function ()
{
    $('form1').keypress(function (e)
    {
        if (e == 13)
        {
            if (verificadados(voltarOrigem))
            {
                replaceLink(voltarOrigem);
                return true;
            }
            return false;
        }
    });

    $('input').keypress(function (e)
    {
        if (e.which == 13)
        {
            if (verificadados(voltarOrigem))
            {
                replaceLink(voltarOrigem);
                return true;
            }
            return false;
        }
    });
});

function verificadados(voltarOrigem)
{
    // dados
    var form_dados = $('#form1').serialize();
    var destino = "trocasenha_gravar.php";

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
                mostraMensagem("Registro não localizado!");
            }
            else if (ojson[0].siape == '')
            {
                mostraMensagem(ojson[0].mensagem);
            }
            else
            {
                mostraMensagem(ojson[0].mensagem, 'success', voltarOrigem);
                return true;
            }
        },
        error: function (txt)
        {
            // em caso de erro
            hideProcessando();
            mostraMensagem('Houve um problema interno. Tente novamente.');
        },
        complete: function (data)
        {
            hideProcessando();
        }

    });

    return false;
}

function ve(parm1)
{
    var senhaatual = $('#senhaatual');
    var senhanova = $('#senhanova');
    var senhanova_confirmar = $('#senhanova_confirmar');

    if (senhaatual.val().length == 8)
    {
        senhanova.focus();
    }
    if (senhanova.val().length == 8)
    {
        senhanova_confirmar.focus();
    }
}

