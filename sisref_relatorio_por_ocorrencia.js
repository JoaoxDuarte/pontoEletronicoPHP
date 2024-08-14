var ocorrencia = [];

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

    $(".select2-single.select-ocorr").select2({
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

    $('.select-ocorr').change(function() {
        ocorrencia.push($(this).val());
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


function verificadados()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var competencias_opcoes  = $('#competencias_opcoes');
    var mes  = competencias_opcoes.val().substr(-2);
    var ano  = competencias_opcoes.val().substr(0,4);

    var upag  = $('#upag');
    var cUpag = upag.val();
    var nUpag = parseInt(cUpag,10);

    
    var ocor  = $("#ocor");
    var cOcor = ocorrencia;
    var nOcor = parseInt(cOcor,10);

    // valida o mês informado
    mensagem = validaMes(mes);
    if (mensagem.length > 0)
    {
        oTeste.setMsg('- ' + mensagem, competencias_opcoes);
    }

    mensagem = validaAno(ano, mes);
    if (mensagem.length > 0)
    {
        oTeste.setMsg('- ' + mensagem, competencias_opcoes);
    }

    if ((Number.isInteger(nUpag) == false) || (nUpag == 0) || (cUpag.length < 9) || (cUpag.length > 14))
    {
        oTeste.setMsg('- A UPAG deve ser selecionada!', upag);
    }

    // Valida se há pelo menos uma ocorrência selecionada.
    if (ocorrencia.length === 0)
    {
        oTeste.setMsg('- A Ocorrência deve ser selecionada!', ocor);
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    if (bResultado == false)
    {
        return bResultado;
    }
    else
    {
        showProcessandoAguarde();
        
        $("#form1").attr("action", "sisref_relatorio_por_ocorrencia_html.php");
        $("#form1").attr("onsubmit", "javascript:return true");
        $('#form1').submit();
    }
}

function ve(parm1)
{
    var competencias_opcoes  = $('#competencias_opcoes');
    var mes  = competencias_opcoes.val().substr(-2);
    var ano  = competencias_opcoes.val().substr(0,4);

    if (mes.length == 2)
    {
        competencias_opcoes.focus();
    }

    if (ano.length == 4)
    {
        //ocor.focus();
    }
}