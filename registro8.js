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
        oTeste.setMsg('� obrigat�rio selecionar uma ocorr�ncia!', ocor);
    }
    if (dt.val().length == 0)
    {
        oTeste.setMsg('� obrigat�rio informar a data!', dt);
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
    var dt2 = dt.val().split('/');
    var dia = parseInt(dt.val().substr(0, 2), 10);
    var mes = parseInt(dt.val().substr(3, 2), 10);
    var ano = parseInt(dt.val().substr(6, 4), 10);

    if (parseInt(sMes, 10) != mes || parseInt(sAno, 10) != ano)
    {
        oTeste.setMsg('Compet�ncia, ' + sMes + ' / ' + sAno + ', diferente de M�s e/ou Ano da Data informada!', dt);
    }
    else if ((mes >= 1 && mes <= 12) && (ano >= 1901))
    {
        if (dia > dias_no_mes)
        {
            oTeste.setMsg('Dia Fim Inv�lido para esse m�s !', dt);
        }
    }
    else if (data_valida(dt.val()) == false)
    {
        oTeste.setMsg('Data Inv�lida !', dt);
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
    var destino = "gravaregfreq2.php?modo=3";
    var ocor    = $('#ocor').val();
    var mat     = $('#mat').val();
    var dia     = $('#dia').val();
    var cmd     = $('#cmd').val();
    var nome    = $('#nome').val();
    var jnd     = ''; //$('#jnd').val();

    var credito = $('#codigosCreditos').val();
    var debito  = $('#debitosCompensaveis').val();

    if (debito.indexOf(ocor) > -1)
    {
        destino = 'registro13.php?mat=' + mat + '&dia=' + dia + '&cmd=' + cmd + '&ocor=' + ocor;
    }
    if (credito.indexOf(ocor) > -1)
    {
        destino = 'registro14.php?mat=' + mat + '&dia=' + dia + '&cmd=' + cmd + '&ocor=' + ocor;
    }

    if (destino != "")
    {
        $("#form1").attr("action", destino);
        $('#form1').submit();
    }
}
