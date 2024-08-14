
function verificadados()
{
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var ocor = $('#ocor');

    if (ocor.val() == '-----')
    {
        oTeste.setMsg( "É obrigatório selecionar ocorrência!", ocor );
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    return bResultado;
}

function testa()
{
    if (verificadados() == true)
    {
        var modoreg = "";
        var destino = "historico_registro14.php";

        var ocor  = $('#ocor').val();
        var dados = $('#dados').val();

        var credito = $('#codigosCreditos').val();     //'33333__02424__02626__02828__03030';
        var debito  = $('#debitosCompensaveis').val(); //'00172__62010__62012__02525';
        var outros  = $('#ocorrenciaLimiteDias').val(); //"00105.:.00108.:.00109.:.00122.:.00169";

        if (debito.indexOf(ocor) > -1)
        {
            modoreg = "13"; /* registro13.php */
        }
        else if (credito.indexOf(ocor) > -1)
        {
            modoreg = "14"; /* registro14.php */
        }
        else if (outros.indexOf(ocor) > -1)
        {
            modoreg = "15";
            destino = "historico_gravaregfreq2.php"; /* registro15.php */
        }

        if (modoreg != "")
        {
            var destino = "javascript:window.location.replace('" + destino
                + '?dados=' + dados
                + '&ocor=' + ocor
                + '&modoreg=' + modoreg
                + "&modo=4')";
            $("#form1").attr("action", destino);
        }

        $('#form1').submit();
    }
}
