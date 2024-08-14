$(document).ready(function ()
{
    //
});

function validar()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var dia     = $('#dia').val();
    var mat     = $('#mat').val();
    var jnd     = $('#jnd').val();
    var cmd     = $('#cmd').val();
    var lot     = $('#lot').val();
    var grupo   = $('#grupo').val();
    var compete = $('#compete').val();

    var ocor   = $('#ocor').val();
    var entra  = $('#entra');
    var iniint = $('#iniint');
    var fimint = $('#fimint');
    var saida  = $('#hsaida');

    var hEntra  = time_to_sec(entra.val());
    var hIniInt = time_to_sec(iniint.val());
    var hFimInt = time_to_sec(fimint.val());
    var hSaida  = time_to_sec(saida.val());

    var recesso_debito                = $('#recessoDebito').val();   //'02323';
    var eventosEsportivos             = $('#eventosEsportivos').val(); //62010_62012_62014
    var codigoDebitoInstrutoriaPadrao = $('#codigoDebitoInstrutoriaPadrao').val();   //'02525';
    var grupoOcorrenciasViagem        = $('#grupoOcorrenciasViagem').val;
    //var ocor_teste = '62010 62012 02323 00128';

    // se ocorrência NÃO for do grupo de eventos esportivos, nem recesso debito, nem instrutoria (débito),
    // ou for do grupo viagem a serviço e horário de entrada informado
    if (((eventosEsportivos.indexOf(ocor) == -1) && (recesso_debito.indexOf(ocor) == -1) && (codigoDebitoInstrutoriaPadrao.indexOf(ocor) == -1))
        || ((eventosEsportivos.indexOf(ocor) == -1) && hEntra > 0))
    {
        if (hEntra == 0)
        {
            oTeste.setMsg('Horário de entrada deve ser diferente de 00:00:00!', entra);
        }
        else
        {
            if (hIniInt != 0 && hEntra > hIniInt)
            {
                oTeste.setMsg('Horário de início do intervalo é menor que o início do expediente!', iniint);
            }
            if (hFimInt != 0 && hEntra > hFimInt)
            {
                oTeste.setMsg('Horário de final do intervalo é menor que o final do expediente !', fimint);
            }
            if (hFimInt != 0 && hIniInt > hFimInt)
            {
                oTeste.setMsg('Horário de final do intervalo é menor que o início do intervalo !', iniint);
            }
        }

        if (hSaida == 0)
        {
            oTeste.setMsg('Horário de saida deve ser diferente de 00:00:00 !', saida);
        }
        else
        {
            if (hSaida < hEntra)
            {
                oTeste.setMsg('Horário de fim do expediente é menor que o início do expediente !', saida);
            }
            if (hIniInt != 0 && hIniInt > hSaida)
            {
                oTeste.setMsg('Horário de fim do expediente é menor que o início do intervalo !', iniint);
            }
            if (hFimInt != 0 && hFimInt > hSaida)
            {
                oTeste.setMsg('Horário de fim do expediente é menor que o final do intervalo !', fimint);
            }
        }
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    if (bResultado == true)
    {
        var dados = base64_encode(grupo + ":|:" + mat + ":|:" + compete + ":|:" + dia + ":|:" + jnd + ":|:" + cmd + ":|:" + ocor + ":|:" + entra.val() + ":|:" + iniint.val() + ":|:" + fimint.val() + ":|:" + saida.val() + ":|:" + lot);
        var destino = "javascript:window.location.replace('" + "historico_frequencia_gravar.php?dados=" + dados + "');";

        $("#form1").attr("action", destino);
        $('#form1').submit();
    }

    return bResultado;
}
