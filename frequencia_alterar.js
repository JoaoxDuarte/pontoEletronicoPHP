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


    $("#ocor").change(function ()
    {
        var ocor = $('#ocor').val();
        var exigeJustificativa = $('#exige_justificativa_chefia').val();

        $('#justificativa_chefia').css("display", (ocor !== '' && exigeJustificativa.indexOf(ocor) > -1 ? 'inline' : 'none'));
    });

    $("#btn-continuar-alteracao").click(function ()
    {
        // objeto mensagem
        oTeste = new alertaErro();
        oTeste.init();

        // dados
        var ocor        = $('#ocor');
        var compete     = $('#compete').val();
        var sMes        = $('#mes').val();
        var sAno        = $('#ano').val();
        var dias_no_mes = $('#dias_no_mes').val();

        var dt       = $('#dia');
        var dt_value = dt.val();

        var justchef       = $('#justchef');
        var justchef_value = trim(justchef.val());

        var ocor_origem = $('#ocor_origem');

        exigeJustificativa      = $('#exige_justificativa_chefia').val();
        codigosTrocaObrigatoria = $('#codigosTrocaObrigatoria').val();

        if (ocor.val() == codigosTrocaObrigatoria || ocor.val() == "")
        {
            oTeste.setMsg('É obrigatório selecionar uma ocorrência!', ocor);
        }

        if (dt_value.length == 0)
        {
            oTeste.setMsg('É obrigatório informar a data!', dt);
        }

        if ((ocor.val() != codigosTrocaObrigatoria && ocor.val() != "" && ocor.val() != ocor_origem.val()) && (exigeJustificativa.indexOf(ocor.val()) > -1) && (justchef_value == '' || justchef_value.length < 15))
        {
            oTeste.setMsg('É obrigatório o preenchimento da justificativa da chefia com no mínimo 15 caracteres!', justchef);
        }

        // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
        var bResultado = oTeste.show();
        if (bResultado == false)
        {
            return bResultado;
        }
        else
        {
            // objeto mensagem
            oTeste.init();

            // Se data inválida: Erro.
            var dt2 = dt_value.split('/');
            var dia = parseInt(dt_value.substr(0, 2), 10);
            var mes = parseInt(dt_value.substr(3, 2), 10);
            var ano = parseInt(dt_value.substr(6, 4), 10);

            if (parseInt(sMes, 10) != mes || parseInt(sAno, 10) != ano)
            {
                oTeste.setMsg('Competência, ' + sMes + ' / ' + sAno + ', diferente de Mês e/ou Ano da Data informada!', dt);
            }
            else if ((mes >= 1 && mes <= 12) && (ano >= 1901))
            {
                if (dia > dias_no_mes)
                {
                    oTeste.setMsg('Dia Fim Inválido para esse mês!', dt);
                }
            }
            else if (data_valida(dt_value) == false)
            {
                oTeste.setMsg('Data Inválida!', dt);
            }

            // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
            var bResultado = oTeste.show();
            if (bResultado == false)
            {
                return bResultado;
            }

            EnviarDados();
        }

        return true;

    });

    function EnviarDados()
    {
        // dados
        var ocor          = $('#ocor').val();
        var mat           = $('#mat').val();
        var dia           = $('#dia').val();
        var cmd           = $('#cmd').val();
        var lot           = $('#lot').val();
        var cod_sitcad    = $('#cod_sitcad').val();
        var jnd           = ''; //$('#jnd').val();
        var justchef      = $('#justchef').val();
        var grupoOperacao = $('#grupoOperacao').val();

        var credito = $('#credito').val();
        var debito  = $('#debito').val();

        var ocor_origem = $('#ocor_origem').val();

        var dados   = "";
        var destino = "";
        var grupo   = "";

        var comp = $('#compete').val();
        ;

        var he  = ''; // hora de entrada
        var hie = ''; // início do intervalo do almoço
        var his = ''; // fim do intrervalo do almoço
        var hs  = ''; // hora de saída final

        if (debito.indexOf(ocor) > -1)
        {
            grupo = 'debito';
            dados = base64_encode(
                mat + ":|:" + dia + ":|:" + cmd + ":|:" + ocor + ":|:"
                + lot + ":|:" + grupo + ":|:" + cod_sitcad + ":|:" + justchef
                + ":|:" + grupoOperacao + ":|:" + ocor_origem
                );
            destino = "javascript:window.location.replace('frequencia_alterar_horario.php?dados=" + dados + "');";
        }
        else if (credito.indexOf(ocor) > -1)
        {
            grupo = 'credito';
            dados = base64_encode(
                mat + ":|:" + dia + ":|:" + cmd + ":|:" + ocor + ":|:" + lot
                + ":|:" + grupo + ":|:" + cod_sitcad + ":|:"
                + justchef + ":|:" + grupoOperacao + ":|:" + ocor_origem
                );
            destino = "javascript:window.location.replace('frequencia_alterar_horario.php?dados=" + dados + "');";
        }
        else
        {
            grupo = 'outros';
            dados = base64_encode(
                grupo + ":|:" + mat + ":|:" + comp + ":|:" + dia + ":|:"
                + jnd + ":|:" + cmd + ":|:" + ocor + ":|:" + he + ":|:" + hie
                + ":|:" + his + ":|:" + hs + ":|:" + lot + ":|:" + justchef
                + ":|:" + grupoOperacao + ":|:" + ocor_origem
                );
            destino = "javascript:window.location.replace('frequencia_gravar.php?dados=" + dados + "');";
        }

        if (destino != "")
        {
            $("#form1").attr("action", destino);
            $("#form1").submit();
        }
    }

    function servico_externo()
    {
        var ocor = $('#ocor').val();
        $('#tbJustificativa').css("display", (exigeJustificativa.indexOf(ocor) > -1 ? 'inline' : 'none'));
        $('#oco').val(ocor);
    }

});

