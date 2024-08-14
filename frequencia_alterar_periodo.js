$(document).ready(function ()
{

    // Set the \"bootstrap\" theme as the default theme for all Select2
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
        validar();
    });

    $("#btn-ver-tabela-ocorrencias").click(function ()
    {
        verDialogMensagens('tabela-ocorrencias', 'html/form-tabela-ocorrencia-visualizar.php');
    });

});

function verDialogMensagens(id, remote)
{
    if (id != null && id != '')
    {
        if (remote != null && remote != '')
        {
            $("#" + id + "-body").load(remote);
        }

        $('#' + id).modal('show');
    }
}

function validar()
{
    // objeto mensagem
    var oTeste = new alertaErro();
    oTeste.init();

    // dados
    mat           = $('#mat').val();
    dataInicio    = $('#dia2');
    dataTermino   = $('#dia');
    cmd           = $('#cmd').val();
    jnd           = $('#jnd').val();
    compete       = $('#compete').val();
    ocor          = $('#ocor');
    lot           = $('#lot');
    modo          = $('#modo');
    mes           = $('#mes').val();
    ano           = $('#ano').val();
    dias_no_mes   = $('#dias_no_mes').val();
    tipo_acao     = $('#tipo_acao').val();
    tipo_inclusao = $('#tipo_inclusao').val();
    credito       = $('#credito').val();
    debito        = $('#debito').val();

    dataTermino.val((tipo_inclusao == '' ? dataTermino.val() : dataInicio.val()));

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
        if (tipo_inclusao == '')
        {
            oTeste.setMsg('Informe o Dia Início da Ocorrência!', obj);
        }
        else
        {
            oTeste.setMsg('Informe o Dia da Ocorrência!', obj);
        }
    }
    else if (dataInicio.val().length == 1)
    {
        obj = (obj == null ? dataInicio : obj);
        if (tipo_inclusao == '')
        {
            oTeste.setMsg('Informe o Dia Início da Ocorrência com dois dígitos!', obj);
        }
        else
        {
            oTeste.setMsg('Informe o Dia da Ocorrência com dois dígitos!', obj);
        }
    }

    // data de término da ocorrência
    //
    if (dataTermino.val().length == 0 && tipo_inclusao == '')
    {
        obj = (obj == null ? dataTermino : obj);
        oTeste.setMsg('Informe o Dia Fim da Ocorrência!', obj);
    }
    else if (dataTermino.val().length == 1 && tipo_inclusao == '')
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

    if (tipo_inclusao == '')
    {
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
    }
    else
    {
        if (dia_fim > dias_no_mes)
        {
            oTeste.setMsg('Dia da Ocorrência inválido para esse mês!', obj_ini);
        }
        if (dia_ini < 1)
        {
            oTeste.setMsg('Dia da Ocorrência inválido!', obj_ini);
        }
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
    // dados
    mat           = $('#mat').val();
    dataInicio    = $('#dia2').val();
    dataTermino   = $('#dia').val();
    cmd           = $('#cmd').val();
    jnd           = $('#jnd').val();
    compete       = $('#compete').val();
    ocor          = $('#ocor').val();
    lot           = $('#lot').val();
    modo          = $('#modo').val();
    mes           = $('#mes').val();
    ano           = $('#ano').val();
    dias_no_mes   = $('#dias_no_mes').val();
    tipo_acao     = $('#tipo_acao').val();
    tipo_inclusao = $('#tipo_inclusao').val();
    credito       = $('#credito').val();
    debito        = $('#debito').val();

    acao_manutencao = $('#acao_manutencao').val();
    
    $('#dia').val((tipo_inclusao == '' ? dataTermino : dataInicio));

    var parametros = base64_encode(mat + ":|:" + dataInicio 
        + ":|:" + dataTermino + ":|:" + cmd + ":|:" + jnd + ":|:" + compete 
        + ":|:" + ocor + ":|:" + lot + ":|:" + modo + ":|:" + dias_no_mes 
        + ":|:" + tipo_acao + ":|:" + credito + ":|:" + debito);

    var destino = "javascript:window.location.replace('frequencia_alterar_periodo_gravar.php?dados=" + parametros + "');";

    showProcessandoAguarde();
    
    $("#form1").attr("onsubmit", "javascript:return true;");
    $("#form1").attr("action", destino);
    $("#form1").submit();
}

function ve(parm1)
{
    dataInicio = $('#dia2');
    dataTermino = $('#dia');
    if (dataInicio.val().length == 2)
    {
        dataTermino.focus();
    }
}
