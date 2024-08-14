
var interval = 0;

$(document).ready(function ()
{
    $('form').keypress(function (e)
    {
        if (e.which == 13 || e.keyCode == 13)
        {
            return false;
        }
    });

    $('input').keypress(function (e)
    {
        if (e.which == 13 || e.keyCode == 13)
        {
            return false;
        }
    });

    $('#pesquisa').keyup(function ()
    {
        // começa a contar o tempo
        clearInterval(interval);

        // 500ms após o usuário parar de digitar a função é chamada
        interval = window.setTimeout('dadosCadUsuarios()', 500);
    });

    $('#reiniciar_senha').keyup(function ()
    {
        alert('1');
    });
});

/*-------------------------------------------------------\
 |     AJAX - Lista de Usuários                           |
 \-------------------------------------------------------*/
function dadosCadUsuarios()
{
    var x = $('#pesquisa').val();
    var codGER = (x == null ? '' : x);
    codGER = codGER.replace('%', '');

    showProcessandoAguarde(); // mensagem processando

    //create the ajax request
    $.ajax({
        type: "POST",
        url: "usuario_lista_ajax.php", // a pagina que sera cahamda
        data: "pesquisa=" + codGER, // dados enviados a pagina, para ser processada
        timeout: 2000,
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

            //get the user array from the json object
            var idInner = "";
            var idInnerTit = "";

            // Exibe a lista
            idInnerTit += "<table class='table table-striped table-condensed table-bordered text-center'><thead><tr><th class='text-center' style='vertical-align:middle;'><b>SIAPE</b></th><th class='text-center' style='vertical-align:middle;'><b>NOME</b></th><th class='text-center' style='vertical-align:middle;'><b>ÓRGÃO/UNIDADE</b></th><th class='text-center' style='vertical-align:middle;'><b>CPF</b></th><th class='text-center' style='vertical-align:middle;'><b>AÇÃO</b></th></tr></thead><tbody>";

            if (tam == 0)
            {
                idInner += idInnerTit + "<tr><td align='center' colspan='4'><font size='2' face='Verdana'>Sem Registros para Exibir</font></td></tr>";
            }
            else
            {
                var grp_setor = '';
                for (x = 0; x < tam; x++)
                {
                    if (grp_setor == '' || grp_setor != ojson[x].upag)
                    {
                        if (grp_setor != '')
                        {
                            idInner += '</tbody></table><br>';
                        }
                        idInner += "<table class='table table-striped table-condensed table-bordered'><tr colspan='3'><td class='text-left' style='vertical-align:middle;'>" + ojson[x].orgao + " - " + ojson[x].nomeorgao.toUpperCase() + "<br>" + ojson[x].setor + " - " + ojson[x].nomesetor.toUpperCase() + "</td></tr></table>";
                        idInner += idInnerTit;
                        grp_setor = ojson[x].upag;
                    }
                    idInner += "<tr><td align='center' width='10%' height='18'><font size='2' face='Verdana'>" + ojson[x].siape.substring(5, 12) + "</font></td><td align='left' width='42%' height='18'><font size='2' face='Verdana'>&nbsp;" + ojson[x].nome + "</font></td><td align='center' width='17%' height='18'><font size='2' face='Verdana'>" + ojson[x].setor + "</font></td><td align='center' width='10%' height='18'><font size='2' face='Verdana'>" + ojson[x].cpf + "</font></td><td align='center' width='25%' height='18'><font size='2' face='Verdana'><a href=\"javascript:window.location.replace('" + urlDestino + "?siape=" + ojson[x].siape + "&lotacao=" + ojson[x].codigo + "');\">Alterar</a>&nbsp;&nbsp;&nbsp;<a onclick=\"javascript:reiniciarSenha('" + ojson[x].siape + "','" + ojson[x].cpf + "');\" style='cursor:pointer;'>Reiniciar Senha</a></font></td></tr>";
                }

                idInner += "</tbody></table>";
            }
            $('#id_lista').html(idInner);  // insiro o texto
            $('#id_registros').html('(Total de ' + tam + ' listados)');  // insiro o texto
            hideProcessandoAguarde();
        },
        error: function (txt)
        {
            // em caso de erro
            hideProcessandoAguarde();
            mostraMensagem('Houve um problema interno. Tente novamente mais tarde.', 'danger');
        }
    });

    return true;
}


/*-------------------------------------------------------\
 |     AJAX - Lista de Usuários RH                        |
 \-------------------------------------------------------*/
function dadosCadUsuariosRH()
{
    showProcessandoAguarde(); // mensagem processando

    //create the ajax request
    $.ajax({
        type: "POST",
        url: "usuario_rh_lista_ajax.php", // a pagina que sera cahamda
        data: "pesquisa=RH", // dados enviados a pagina, para ser processada
        timeout: 3000,
        dataType: "html",
        beforeSend: function ()
        {
            // enquanto a função esta sendo processada, você
            // pode exibir na tela uma msg de carregando
            //$('#mensagem_aviso').html('<img src="imagem/loading.gif"/>');
        },
        success: function (data)
        {
            hideProcessandoAguarde();

            //get the user array from the json object
            var idInner = data;

            // Exibe a lista
            $('#id_lista').html(idInner);  // inserir o texto
            $('#id_registros').html('(Total de ' + tam + ' listados)');  // inserir o texto
        },
        error: function (txt)
        {
            // em caso de erro
            hideProcessandoAguarde();
            mostraMensagem('Houve um problema interno. Tente novamente mais tarde.', 'danger');
        }
    });

    return true;
}


/*-------------------------------------------------------\
 |     AJAX - Gravar Permissões de Usuários RH            |
 \-------------------------------------------------------*/
function gravarPermissao(mat)
{
    showProcessandoAguarde(); // mensagem processando

    var dados = $('#form1').serialize();

    //create the ajax request
    $.ajax({
        type: "POST",
        url: "usuario_rh_grava_ajax.php", // a pagina que sera cahamda
        data: dados + '&siape=' + mat, // dados enviados a pagina, para ser processada
        timeout: 3000,
        dataType: "html",
        beforeSend: function ()
        {
            // enquanto a função esta sendo processada, você
            // pode exibir na tela uma msg de carregando
            //$('#mensagem_aviso').html('<img src="imagem/loading.gif"/>');
            $('#gravou' + mat).html("<img src='./imagem/loading2.gif' width='14px' border='0'>");
        },
        success: function (data)
        {
            if (data == "Servidor de outra UPAG!")
            {
                $('#gravou' + mat).html("<img src='./imagem/transp1x1.gif' width='14px' border='0'>");
                mostraMensagem('Servidor de outra UPAG!', 'danger');
                hideProcessandoAguarde();
                window.location.reload();
            }
            else
            {
                $('#gravou' + mat).html("<img src='./imagem/checked.gif' width='14px' border='0'>");
                mostraMensagem(data, 'danger');
            }
            hideProcessandoAguarde();
        },
        error: function (txt)
        {
            // em caso de erro
            hideProcessandoAguarde();
            mostraMensagem('Houve um problema interno. Tente novamente mais tarde.', 'danger');
        }
    });

    return true;
}


/*-------------------------------------------------------\
 |     AJAX - Reinicar a senha de um Usuários             |
 \-------------------------------------------------------*/
function reiniciarSenha(mat, cpf)
{
    // dados
    var mat = (mat == null ? "" : mat);
    var cpf = (cpf == null ? "" : cpf);

    bootbox.confirm({
        locale: "br",
        title: "Excluir Registro",
        message: " Tem certeza da reinicialização da senha deste usuário?",
        buttons: {
            confirm: {
                label: "<p style='padding:0px 15px 0px 15px;margin:0px;'>Sim</p>",
                className: 'btn-success'
            },
            cancel: {
                label: "<p style='padding:0px 15px 0px 15px;margin:0px;'>Não</p>",
                className: 'btn-danger'
            }
        },
        callback: function(result) {
            executaReiniciarSenha(result, mat, cpf);
        }
    });
}


/*-------------------------------------------------------\
 |     AJAX - GRAVAR : Reinicar a senha de um Usuários   |
 \-------------------------------------------------------*/
function executaReiniciarSenha(result, mat, cpf)
{
    var form_dados = "siape=" + mat + "&cpf=" + cpf;
    var destino    = "reiniciar1_gravar.php";

    if (result && mat != "")
    {
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
            var tam   = ojson.length; // Número de itens

            console.log( ojson.mensagem );

            hideProcessandoAguarde();

            if (tam == 0 || ojson.mensagem == null)
            {
                mostraMensagem("Registro não localizado!", 'danger');
            }
            else if (ojson.siape == '')
            {
                mostraMensagem(ojson.mensagem, 'danger');
            }
            else
            {
                mostraMensagem(ojson.mensagem, 'success');
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
    }
}
