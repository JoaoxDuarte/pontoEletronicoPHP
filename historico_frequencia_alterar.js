function verificadados()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var ocor        = $('#ocor');
    var dt          = $('#dia');
    var compete     = $('#compete').val();
    var sMes        = $('#mes').val();
    var sAno        = $('#ano').val();
    var dias_no_mes = $('#dias_no_mes').val();

    if (ocor.val() == "-----")
    {
        oTeste.setMsg('É obrigatório selecionar uma ocorrência!', ocor);
    }
    if (dt.val().length == 0)
    {
        oTeste.setMsg('É obrigatório informar a data!', dt);
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
    var dt2 = dt.val().split('/');
    var dia = parseInt(dt.val().substr(0, 2), 10);
    var mes = parseInt(dt.val().substr(3, 2), 10);
    var ano = parseInt(dt.val().substr(6, 4), 10);

    if (parseInt(sMes, 10) != mes || parseInt(sAno, 10) != ano)
    {
        oTeste.setMsg('Competência, ' + sMes + ' / ' + sAno + ', diferente de Mês e/ou Ano da Data informada!', dt);
    }
    else if ((mes >= 1 && mes <= 12) && (ano >= 1901))
    {
        if (dia > dias_no_mes)
        {
            oTeste.setMsg('Dia Fim Inválido para esse mês !', dt);
        }
    }
    else if (data_valida(dt.val()) == false)
    {
        oTeste.setMsg('Data Inválida !', dt);
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
    var ocor       = $('#ocor').val();
    var mat        = $('#mat').val();
    var dia        = $('#dia').val();
    var cmd        = $('#cmd').val();
    var lot        = $('#lot').val();
    var nome       = $('#nome').val();
    var cod_sitcad = $('#cod_sitcad').val();
    var jnd        = ''; //$('#jnd').val();

    var credito = $('#credito').val();
    var debito  = $('#debito').val();
    var outros  = $('#outros').val();

    var destino = "javascript:window.location.replace('historico_frequencia_gravar.php?mat=" + mat + "&dia=" + dia + "&ocor=" + ocor + "&lot=" + lot + "&grupo=outros" + "&cod_sitcad=" + cod_sitcad + "');";

    if (debito.indexOf(ocor) > -1)
    {
        destino = "javascript:window.location.replace('historico_frequencia_alterar_horario.php?mat=" + mat + "&dia=" + dia + "&cmd=" + cmd + "&ocor=" + ocor + "&lot=" + lot + "&grupo=debito" + "&cod_sitcad=" + cod_sitcad + "');";
    }
    if (credito.indexOf(ocor) > -1)
    {
        destino = "javascript:window.location.replace('historico_frequencia_alterar_horario.php?mat=" + mat + "&dia=" + dia + "&cmd=" + cmd + "&ocor=" + ocor + "&lot=" + lot + "&grupo=credito" + "&cod_sitcad=" + cod_sitcad + "');";
    }

    if (outros.indexOf(ocor) > -1)
    {
    }

    if (destino != "")
    {
        $("#form1").attr("action", destino);
        $('#form1').submit();
    }
}
