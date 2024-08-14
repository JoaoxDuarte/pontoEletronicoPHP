$(document).ready(function ()
{
    // Set the "bootstrap" theme as the default theme for all Select2
    // widgets.
    //
    // @see https://github.com/select2/select2/issues/2927
    $.fn.select2.defaults.set("theme", "bootstrap");

    var placeholder = "Selecione uma Ocorrência";

    $(".select2-single").select2({
        placeholder: placeholder,
        width: null,
        containerCssClass: ':all:'
    });

    $("body").keypress(function (e)
    {
        if (e.which == 27 || e.keyCode == 27)
        {
            return verificadados();
        }
    });

    $("form").keypress(function (e)
    {
        if (e.which == 13 || e.keyCode == 13)
        {
            return verificadados();
        }
    });

    $("input").keypress(function (e)
    {
        if (e.which == 13 || e.keyCode == 13)
        {
            return verificadados();
        }
    });

    $("#btn-continuar").click(function ()
    {
        return verificadados();
    });
});

/*
 * Verifica se os dados exigidos foram informados
 *
 */
function verificadados()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var sNome  = $('#sNome');
    var sSiape = $('#sSiape');
    var sSetor = $('#sSetor');

    // testa os dados informados
    if (sSiape.val().length < 7)
    {
        oTeste.setMsg("O SIAPE possui 7 números !", sSiape);
    }
    if (sNome.val().length == 0)
    {
        oTeste.setMsg("O NOME é obrigatório !", sNome);
    }
    //if (sSetor.val().length < 8 ) { oTeste.setMsg( "A Unidade é obrigatória e possui 8 caracteres!", lSetor ); }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    if (bResultado == true)
    {
        // pega todos os campos e prepara os
        // dados para envio e gravação
        // em background
        var dados = $('#form1').serialize();

        $.ajax({
            type: "POST",
            url: "usuario_grava.php", // a pagina que sera chamada
            data: dados, // dados enviados a pagina, para ser processada
            dataType: "json",
            success: function (response)
            {
                var ojson = response.dados;

                // Número de itens
                var tam = ojson.length;

                if (tam == 0)
                {
                    mostraMensagem('Houve um problema interno. tente novamente mais tarde.', 'danger');
                }
                else
                {
                    mostraMensagem(ojson[0].aviso, 'success', 'usuario_lista.php');
                }
            },
            error: function (txt)
            {
                mostraMensagem('Houve um problema interno. Tente novamente mais tarde.', 'danger');
            }
        });
    }

    return false;
}


/*
 * Carrega as informações das permissões
 * registradas no banco de usuarios
 * para exibição na página
 *
 */
function pesquisa(sSiape)
{
    var sSiape = (sSiape == null ? '' : sSiape);

    $.ajax({
        type: "POST",
        url: "usuario_lista_ajax.php", // a pagina que sera cahamda
        data: "pesquisa=" + sSiape + "&alterar=S", // dados enviados a pagina, para ser processada
        dataType: "json",
        success: function (response)
        {
            var ojson = response.dados;

            // Número de itens
            var tam = ojson.length;

            if (tam == 0)
            {
                alert("Usuário não localizado!");
            }
            else
            {
                $('#sSiape').val(ojson[0].siape);
                $('#sNome').val(ojson[0].nome);
                //$('#sSetor').val( ojson[0].setor );
                var sAcessos = ojson[0].acessos;
                for (x = 0; x < sAcessos.length; x++)
                {
                    var xInd = (x + 1);
                    var bChecked = (sAcessos.substr(x, 1) == 'S');
                    $("#C" + (x < 9 ? "0" : "") + xInd).prop("checked", bChecked);
                }
            }
        },
        error: function (txt)
        {
            alert('Houve um problema interno. tente novamente mais tarde.');
        }
    });
}

//-->
