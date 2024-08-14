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

        
    $("#btn-continuar").click(function ()
    {
        if (verificadados())
        {
            $('#form1').submit();
        }
    });

});

function verificadados()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var ocor   = $('#ocor');
    var dt_ini = $('#dt_ini');
    var dt_fim = $('#dt_fim2');
    var mes    = $('#mes');
    var ano    = $('#ano');

    if (ocor.val() === '-----')
    {
        oTeste.setMsg('Selecione a ocorrência!', ocor);
    }

    if (dt_ini.val().length < 2)
    {
        oTeste.setMsg('O dia de início da ocorrência é obrigatório com dois caracteres!', dt_ini);
    }

    if (dt_fim.val().length < 2)
    {
        oTeste.setMsg('O dia de fim da ocorrência é obrigatório com dois caracteres!', dt_fim);
    }

    if ((mes.val() === 02 && dt_fim.val() > 29 && (((ano.val() % 4) === 0 && (ano.val() % 100) !== 0) || ((ano.val() % 400) === 0))))
    {
        oTeste.setMsg('Dia Fim inválido para ano bisexto nesse mês!', dt_fim);
    }

    if ((mes.val() === 02 && dt_fim.val() > 28 && (((ano.val() % 4) !== 0 && (ano.val() % 100) !== 0))))
    {
        oTeste.setMsg('Dia Fim inválido para esse mês!', dt_fim);
    }

    if ((mes.val() === 01 && dt_fim.val() > 31) || (mes.val() === 03 && dt_fim.val() > 31) || (mes.val() === 05 && dt_fim.val() > 31) || (mes.val() === 07 && dt_fim.val() > 31) || (mes.val() === 08 && dt_fim.val() > 31) || (mes.val() === 10 && dt_fim.val() > 31) || (mes.val() === 12 && dt_fim.val() > 31))
    {
        oTeste.setMsg('Dia Fim inválido para esse mês!', dt_fim);
    }

    if ((mes.val() === 04 && dt_fim.val() > 30) || (mes.val() === 06 && dt_fim.val() > 30) || (mes.val() === 09 && dt_fim.val() > 30) || (mes.val() === 11 && dt_fim.val() > 30))
    {
        oTeste.setMsg('Dia Fim inválido para esse mês!', dt_fim);
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    
    return bResultado;
}
