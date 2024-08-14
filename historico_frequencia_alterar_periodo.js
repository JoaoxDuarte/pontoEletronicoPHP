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

    // c�digo da ocorr�ncia
    //
    if (ocor.val() == "-----")
    {
        obj = (obj == null ? ocor : obj);
        oTeste.setMsg('Selecione uma ocorr�ncia!', obj);
    }

    // data de in�cio da ocorr�ncia
    //
    if (dataInicio.val().length == 0)
    {
        obj = (obj == null ? dataInicio : obj);
        oTeste.setMsg('Informe o Dia In�cio da Ocorr�ncia!', obj);
    }
    else if (dataInicio.val().length == 1)
    {
        obj = (obj == null ? dataInicio : obj);
        oTeste.setMsg('Informe o Dia In�cio da Ocorr�ncia com dois d�gitos!', obj);
    }

    // data de t�rmino da ocorr�ncia
    //
    if (dataTermino.val().length == 0)
    {
        obj = (obj == null ? dataTermino : obj);
        oTeste.setMsg('Informe o Dia Fim da Ocorr�ncia!', obj);
    }
    else if (dataTermino.val().length == 1)
    {
        obj = (obj == null ? dataTermino : obj);
        oTeste.setMsg('Informe o Dia Fim da Ocorr�ncia com dois d�gitos!', obj);
    }


    // se houve erro ser�(�o) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    if (bResultado == false)
    {
        return bResultado;
    }

    // objeto mensagem
    oTeste.init();

    // Se data inv�lida: Erro.
    var obj_ini = dataInicio;
    var dia_ini = parseInt(dataInicio.val(), 10);

    var obj_fim = dataTermino;
    var dia_fim = parseInt(dataTermino.val(), 10);

    if (dia_fim > dias_no_mes)
    {
        oTeste.setMsg('Dia Fim da Ocorr�ncia inv�lido para esse m�s!', obj_fim);
    }
    if (dia_ini < 1)
    {
        oTeste.setMsg('Dia In�cio da Ocorr�ncia inv�lido!', obj_ini);
    }
    if (dia_ini > dia_fim)
    {
        oTeste.setMsg('Dia In�cio da Ocorr�ncia MAIOR QUE Dia Fim da Ocorr�ncia!', obj_ini);
    }

    // se houve erro ser�(�o) exibida(s) a(s) mensagem(ens) de erro
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
