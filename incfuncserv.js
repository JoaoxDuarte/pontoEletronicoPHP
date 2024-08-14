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

    $("#ocupacao_select").select2({disabled:true});

    $("body").keypress(function (e)
    {
        if (e.which == 13 || e.keyCode == 13)
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

    $("#novafuncao").on('change', function (e)
    {
        return verifica_situacao(this);
    });

    $("#btn-continuar").click(function ()
    {
        return verificadados();
    });
});


function verificadados()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var novafuncao = $('#novafuncao');
    var matricula  = $('#matricula');
    var ocupacao   = $('#ocupacao');

    var bErro    = false;
    var mensagem = '';
    var sMsgErro = '';
    //var sMsgErro = "Digite/Selecione:\n";

    if (novafuncao.val() == '00000')
    {
        oTeste.setMsg(sMsgErro + '.Selecione uma função!');
        bErro = true;
    }

    // validacao do campo siape
    // testa o tamanho
    mensagem = validaSiape(matricula.val());
    if (mensagem != '')
    {
        oTeste.setMsg((bErro ? '' : sMsgErro) + '.' +mensagem);
        bErro = true;
    }

    if (ocupacao.val() == "V")
    {
        oTeste.setMsg((bErro ? '' : sMsgErro) + '.Selecione a situação!');
        bErro = true;
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    if (bResultado == false)
    {
        return bResultado;
    }
    else
    {
        $("#form1").attr("action", "incluifuncao.php");
        $('#form1').submit();
    }
}

function verifica_situacao(obj)
{
    // dados
    var novafuncao_text  = $("#"+obj.name+" :selected").text();
    var novafuncao_value = $("#"+obj.name).val();
    //var ocupacao_text = $("#ocupacao :selected").text();
    var ocupacao_value  = $("#ocupacao_select").val();

    console.log(ocupacao_value);

    if (novafuncao_text.indexOf("(Titular)") > -1)
    {
        ocupacao_value = "T";
        $('[name="ocupacao_select"]').val( "T" )
    }
    else if (novafuncao_text.indexOf("(Substituto)") > -1)
    {
        ocupacao_value = "S";
        $('[name="ocupacao_select"]').val( "S" );
    }
    else if (novafuncao_text.indexOf("(Respondendo)") > -1)
    {
        ocupacao_value = "R";
        $('[name="ocupacao_select"]').val( "R" );
    }
    else
    {
        ocupacao_value = "V";
        $('[name="ocupacao_select"]').val( "V" );
    }

    console.log(ocupacao_value);

    $('#ocupacao').val( ocupacao_value );
    $('#ocupacao_select option[value="' + ocupacao_value + '"]').attr("selected",true);
    $('#ocupacao_select').trigger('change');

    $('[name="matricula"]').focus();
}
