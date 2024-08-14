
var id   = "";
var nome = "";

$(document).ready(function ()
{
    $("#form1").keypress(function (e) {
        if (e.witch === 13)
        {
            $('#btn-continuar').click();
        }
    });
        
    $("#btn-continuar").click(function () {
        if (validar())
        {
            // mensagem processando
            showProcessandoAguarde();

            $('#form1').attr('onsubmit', "javascript:return true;");
            $('#form1').attr('action', "comparecimento_gecc.php");
            $('#form1').submit();
        }    
    });

    $(document).on('click', '.delete-consulta', function () {
        id   = $(this).attr('data-id');
        nome = $(this).attr('data-nome');

        bootbox.confirm({
            locale: "br",
            title: "Excluir Registro",
            message: " Deseja realmente excluir este registro?", 
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
                executaExclusaoGecc(result);
            }
        });
    });

    $(document).on('click', '.save', function () {
        var siape    = $(this).attr('data-siape');
        var mensagem = validaSiape(siape);

        if(mensagem === "")
        {
            $("[name='siape']").val(siape);
            
            // mensagem processando
            showProcessandoAguarde();

            $(".formsiape").attr('onsubmit', "javascript:return true;");
            $(".formsiape").attr('action', "comparecimento_gecc_registro.php");
            $(".formsiape").submit();
        }
        else
        {
            mostraMensagem(mensagem);
            return false;
        }
    });    
    
    $('#chave').focus();
});

            
function executaExclusaoGecc(result)
{
    if (result)
    {
        showProcessandoAguarde();

        var dados = base64_encode("delete=sim&id=" + id + "&nome=" + nome);
        window.location.href = '?dados=' + dados;
    }
}
    

    
function validar()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    var chave   = $('#chave');
    var escolha = $('#escolha').val();

    if (chave.val().length == 0 && escolha !== 'todos')
    {
        oTeste.setMsg('É obrigatório informar a chave para pesquisa!', chave);
    }
    else if (escolha === 'todos')
    {
        chave.val("");
    }
    
    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    if (bResultado == false)
    {
        return bResultado;
    }
    else
    {
        // mensagem processando
        showProcessando();

        $('#form1').attr('onsubmit', "javascript:return true;");
        $('#form1').attr('action', "comparecimento_gecc.php");
        $('#form1').submit();
    }    
}
