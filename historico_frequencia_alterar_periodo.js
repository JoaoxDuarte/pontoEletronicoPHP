// variaveis para uso
// em todo o script
var mat         = null;
var dataInicio  = null;
var dataTermino = null;
var cmd         = null;
var jnd         = null;
var compete     = null;
var ocor        = null;
var lot         = null;
var sMes        = null;
var sAno        = null;
var dias_no_mes = null;
var modo        = null;

function validar()
{
    // objeto mensagem
    var oTeste = new alertaErro();
    oTeste.init();

    // dados
    mat         = $('#mat').val();
    dataInicio  = $('#dia2');
    dataTermino = $('#dia');
    cmd         = $('#cmd').val();
    jnd         = $('#jnd').val();
    compete     = $('#compete').val();
    ocor        = $('#ocor');
    lot         = $('#lot');
    modo        = $('#modo');
    mes         = $('#mes').val();
    ano         = $('#ano').val();
    dias_no_mes = $('#dias_no_mes').val();

    var obj = null;

    // código da ocorrência
    //
    if (ocor.val() == "-----")
    {
        obj = (obj == null ? ocor : obj);
        oTeste.setMsg('Selecione uma ocorrência!', obj);
    }

    // data de início da ocorrência
    //
    if (dataInicio.val().length == 0)
    {
        obj = (obj == null ? dataInicio : obj);
        oTeste.setMsg('Informe o Dia Início da Ocorrência!', obj);
    }
    else if (dataInicio.val().length == 1)
    {
        obj = (obj == null ? dataInicio : obj);
        oTeste.setMsg('Informe o Dia Início da Ocorrência com dois dígitos!', obj);
    }

    // data de término da ocorrência
    //
    if (dataTermino.val().length == 0)
    {
        obj = (obj == null ? dataTermino : obj);
        oTeste.setMsg('Informe o Dia Fim da Ocorrência!', obj);
    }
    else if (dataTermino.val().length == 1)
    {
        obj = (obj == null ? dataTermino : obj);
        oTeste.setMsg('Informe o Dia Fim da Ocorrência com dois dígitos!', obj);
    }


    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    if (bResultado == false)
    {
        return bResultado;
    }

    // objeto mensagem
    oTeste.init();

    // Se data inválida: Erro.
    var obj_ini = dataInicio;
    var dia_ini = parseInt(dataInicio.val(), 10);

    var obj_fim = dataTermino;
    var dia_fim = parseInt(dataTermino.val(), 10);

    if (dia_fim > dias_no_mes)
    {
        oTeste.setMsg('Dia Fim da Ocorrência inválido para esse mês!', obj_fim);
    }
    if (dia_ini < 1)
    {
        oTeste.setMsg('Dia Início da Ocorrência inválido!', obj_ini);
    }
    if (dia_ini > dia_fim)
    {
        oTeste.setMsg('Dia Início da Ocorrência MAIOR QUE Dia Fim da Ocorrência!', obj_ini);
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    if (bResultado == false)
    {
        return bResultado;
    }

    testa();

    return true;
}


function testa()
{
    var destino = "javascript:window.location.replace('historico_frequencia_alterar_periodo_gravar.php?" 
        + "mat=" + mat 
        + "&dia2=" + dataInicio.val() 
        + "&dia=" + dataTermino.val() 
        + "&cmd=" + cmd 
        + "&jnd=" + jnd 
        + "&compete=" + compete 
        + "&ocor=" + ocor.val() 
        + "&lot=" + lot.val() 
        + "&modo=" + modo.val() 
        + "&dias_no_mes=" + dias_no_mes.val() 
        + "');";

    $("#form1").attr("action", destino);
    $('#form1').submit();
}

function ve(parm1)
{
    dataInicio  = $('#dia2');
    dataTermino = $('#dia');
    if (dataInicio.val().length == 2)
    {
        dataTermino.focus();
    }
}
