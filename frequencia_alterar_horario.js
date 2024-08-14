$(document).ready(function ()
{

    $("#btn-continuar").click(function ()
    {
        // objeto mensagem
        oTeste = new alertaErro();
        oTeste.init();

        // dados
        var dia      = $('#dia').val();
        var mat      = $('#mat').val();
        var jnd      = $('#jnd').val();
        var cmd      = $('#cmd').val();
        var lot      = $('#lot').val();
        var justchef = $('#justchef').val();
        var grupo    = $('#grupo').val();
        var compete  = $('#compete').val();
        var ocor     = $('#ocor').val();

        var entra  = $('#entra');
        var iniint = $('#iniint');
        var fimint = $('#fimint');
        var saida  = $('#sai');

        var hEntra  = (entra.val()  == '' ? '00:00:00' : entra.val());
        var hIniInt = (iniint.val() == '' ? '00:00:00' : iniint.val());
        var hFimInt = (fimint.val() == '' ? '00:00:00' : fimint.val());
        var hSaida  = (saida.val()  == '' ? '00:00:00' : saida.val());

        var grupoOperacao = $('#grupoOperacao').val();
        var ocor_origem   = $('#ocor_origem').val();
        
        var ocorr_esportivo_recesso_instrutoria = $('#ocorr_esportivo_recesso_instrutoria').val();
        var codigoBancoDeHorasDebitoPadrao      = $('#codigoBancoDeHorasDebitoPadrao').val();
        
        //console.log(codigoBancoDeHorasDebitoPadrao);
        //console.log(ocorr_esportivo_recesso_instrutoria);
        //console.log(ocorr_esportivo_recesso_instrutoria.indexOf(ocor));
        //console.log("["+""+time_to_sec(hEntra)+""+"]");
        
        if (ocorr_esportivo_recesso_instrutoria.indexOf(ocor) == -1 && codigoBancoDeHorasDebitoPadrao.indexOf(ocor) == -1)
        {
            if (hEntra == "00:00:00")
            {
                oTeste.setMsg('Horário de entrada deve ser diferente de 00:00:00 !', entra);
            }
            else
            {
                if (hIniInt != '00:00:00' && hIniInt < hEntra)
                {
                    oTeste.setMsg('Horário de início do intervalo é menor que o início do expediente !', iniint);
                }

                if (hFimInt != '00:00:00' && hFimInt < hEntra)
                {
                    oTeste.setMsg('Horário de final do intervalo é menor que o final do expediente !', fimint);
                }

                if (hIniInt > hFimInt)
                {
                    oTeste.setMsg('Horário de final do intervalo é menor que o início do intervalo !', iniint);
                }
            }

            if (hSaida == "00:00:00")
            {
                oTeste.setMsg('Horário de saida deve ser diferente de 00:00:00 !', saida);
            }
            else
            {

                if (hSaida < hEntra)
                {
                    oTeste.setMsg('Horário de fim do expediente é menor que o início do expediente !', saida);
                }

                if (hIniInt != '00:00:00' && hIniInt > hSaida)
                {
                    oTeste.setMsg('Horário de fim do expediente é menor que o início do intervalo !', iniint);
                }

                if (hFimInt != '00:00:00' && hFimInt > hSaida)
                {
                    oTeste.setMsg('Horário de fim do expediente é menor que o final do intervalo !', fimint);
                }
            }
        }

        // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
        var bResultado = oTeste.show();
        if (bResultado == true)
        {
            var dados = grupo + ":|:" + mat + ":|:" + compete + ":|:" + dia
                + ":|:" + jnd + ":|:" + cmd + ":|:" + ocor
                + ":|:" + entra.val() + ":|:" + iniint.val() + ":|:" + fimint.val()
                + ":|:" + saida.val() + ":|:" + lot + ':|:' + justchef
                + ':|:' + grupoOperacao + ':|:' + ocor_origem;

            var destino = "javascript:window.location.replace('"
                + "frequencia_gravar.php?dados="
                + base64_encode(dados) + "');";

            $("#form1").attr("action", destino);
            $("#form1").submit();
        }

        return bResultado;
    });
});
