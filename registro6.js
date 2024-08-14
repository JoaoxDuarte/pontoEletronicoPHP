
var credito            = "";
var debito             = "";
var debitoSoDiaUtil    = "";
var debito_so_dia_util = "";
var recessoCredito     = "";
var recessoDebito      = "";

function verificadados()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var ocor    = $('#ocor');
    var dia     = $('#dia');
    var mat     = $('#mat').val();
    var cmd     = $('#cmd').val();
    var nome    = $('#nome').val();
    var jnd     = $('#jnd').val();
    var diaUtil = $('#diaUtil').val();

    var credito         = $('#codigosCreditos').val();
    var debito          = $('#debitosCompensaveis').val();
    var debitoSoDiaUtil = $('#debitoSoDiaUtil').val();

    var debito_so_dia_util = debito + debitoSoDiaUtil;

    var recessoCredito = $('#recessoCredito').val();
    var recessoDebito  = $('#recessoDebito').val();

    if (ocor.val() == "-----" || ocor.val() == '')
    {
        oTeste.setMsg('� obrigat�rio selecionar uma ocorr�ncia!', ocor);
    }
    if (dia.val().length == 0)
    {
        oTeste.setMsg('O dia est� vazio refa�a a opera��o!', dia);
    }

    //// COMENTADO FACE GREVE DOS MEDICOS
    horsaida  = $('#horsaida').val();
    horsaida2 = $('#horsaida2').val();

    var inirec    = $('#inirec').val();
    var fimrec    = $('#fimrec').val();
    var inirecUso = $('#inirecUso').val();
    var fimrecUso = $('#fimrecUso').val()
    var hoje      = $('#hoje').val();

    if ((recessoDebito.indexOf(ocor.val()) > -1) && (hoje < inirecUso || hoje > fimrecUso))
    {
        oTeste.setMsg("N�o � permitido lan�ar recesso ("+recessoDebito+") fora do per�odo legal!", ocor);
    }
    if ((recessoCredito.indexOf(ocor.val()) > -1) && (hoje < inirec || hoje > fimrec))
    {
        oTeste.setMsg("N�o � permitido lan�ar compensa��o de recesso ("+recessoCredito+") fora do per�odo legal!", ocor);
    }
    if (diaUtil == "N" && (debito_so_dia_util.indexOf(ocor.val()) > -1))
    {
        oTeste.setMsg("N�o � permitido lan�ar a ocorr�ncia " + ocor.val() + ", em dia n�o �til!", ocor);
    }

    // se houve erro ser�(�o) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    if (bResultado == false)
    {
        return bResultado;
    }

    encaminha();

    return true;

}

function encaminha()
{
    // dados
    var destino = "gravaregfreq1.php?modo=6";
    var ocor    = $('#ocor').val();
    var mat     = $('#mat').val();
    var dia     = $('#dia').val();
    var cmd     = $('#cmd').val();
    var nome    = $('#nome').val();
    var jnd     = $('#jnd').val();

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
