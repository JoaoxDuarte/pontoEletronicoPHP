
function validar()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var ocor    = $('#ocor');
    var mes     = $('#mes');
    var ano     = $('#ano');
    var dia_ini = $('#dia_ini');
    var dia_fim = $('#dia_fim');

    var mes31dias = "01 03 05 07 08 10 12";
    var mes30dias = "04 06 09 11";

    // codigo de ocorrencia
    if (ocor.val() == "-----")
    {
        oTeste.setMsg('É obrigatório selecionar uma ocorrência!', ocor);
    }

    // dia início
    if (dia_ini.val().length == 0)
    {
        oTeste.setMsg('É obrigatório informar o dia de início!', dia_ini);
    }
    if (mes.val() == '02' && dia_ini.val() > '29' && (((ano.val() % 4) == 0 && (ano.val() % 100) != 0) || ((ano.val() % 400) == 0)))
    {
        oTeste.setMsg("Dia Início inválido para ano bisexto nesse mês!", dia);
    }
    if (mes31dias.indexOf(mes.val()) > 0 && dia_ini.val() > '31')
    {
        oTeste.setMsg("Dia Início inválido para esse mês !", dia_ini);
    }

    // dia término
    if (dia_fim.val().length == 0)
    {
        oTeste.setMsg('É obrigatório informar o dia de término!', dia_fim);
    }
    if (mes.val() == '02' && dia_fim.val() > '29' && (((ano.val() % 4) == 0 && (ano.val() % 100) != 0) || ((ano.val() % 400) == 0)))
    {
        oTeste.setMsg("Dia Término inválido para ano bisexto nesse mês!", dia_fim);
    }
    if (mes30dias.indexOf(mes.val()) > 0 && dia_ini.val() > '30')
    {
        oTeste.setMsg("Dia Término inválido para esse mês !", dia_fim);
    }

    // intervalo
    if (dia_ini.val() > dia_fim.val())
    {
        oTeste.setMsg('Dia fim não pode ser inferior ao início da ocorrência!', dia_ini);
    }

    // ocorrencias - limites
    if ((dia_fim.val() - dia_ini.val() + 1) > limiteDias[ocor.val()])
    {
        oTeste.setMsg('Intervalo inválido para essa ocorrência, máximo de ' + limiteDias[ocor.val()] + ' dias!', dia_ini);
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    if (bResultado == true)
    {
        $('#form1').submit();
    }

    return bResultado;

}

function ve(parm1)
{
    var dia_ini = $('#dia_ini');
    var dia_fim = $('#dia_fim');

    if (dia_ini.val().length == 2)
    {
        dia_fim.focus();
    }
}

function testa(modo)
{
    var modo = (modo == null ? 0 : modo);
    if (modo != 0 || validar() == true)
    {
        $('#form1').submit();
    }
}
