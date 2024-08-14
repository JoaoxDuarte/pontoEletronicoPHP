function validar()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var ocor    = $('#ocor').val();
    var entra   = $('#entra');
    var iniint  = $('#iniint');
    var fimint  = $('#fimint');
    var saida   = $('#hsaida');

    var hEntra  = time_to_sec(entra.val());
    var hIniInt = time_to_sec(iniint.val());
    var hFimInt = time_to_sec(fimint.val());
    var hSaida  = time_to_sec(saida.val());

    var recesso_debito                = $('#recessoDebito').val();
    var codigoDebitoInstrutoriaPadrao = $('#codigoDebitoInstrutoriaPadrao').val();

    if ((recesso_debito.indexOf(ocor) == -1) && (codigoDebitoInstrutoriaPadrao.indexOf(ocor) == -1))
    {
        if (hEntra == 0)
        {
            oTeste.setMsg('Hor�rio de entrada deve ser diferente de 00:00:00!', entra);
        }
        else
        {
            if (hIniInt > 0 && hEntra > hIniInt)
            {
                oTeste.setMsg('Hor�rio de in�cio do intervalo � menor que o in�cio do expediente!', iniint);
            }
            if (hFimInt > 0 && hEntra > hFimInt)
            {
                oTeste.setMsg('Hor�rio de final do intervalo � menor que o final do expediente!', fimint);
            }
            if (hFimInt > 0 && hIniInt > hFimInt)
            {
                oTeste.setMsg('Hor�rio de final do intervalo � menor que o in�cio do intervalo!', iniint);
            }
        }

        if (hSaida > 0 && hEntra > hSaida)
        {
            oTeste.setMsg('Hor�rio de fim do expediente � menor que o in�cio do expediente!', saida);
        }

        if (hFimInt > 0 && hFimInt > hSaida)
        {
            oTeste.setMsg('Hor�rio de fim do expediente � menor que o final do intervalo!', fimint);
        }
    }

    // se houve erro ser�(�o) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    if (bResultado == true)
    {
        $('#form1').submit();
    }

    return bResultado;
}
