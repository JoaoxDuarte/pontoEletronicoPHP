
function validar()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var ocor  = $('#ocor');
    var m     = $('#m');
    var dia   = $('#dia');
    var dia2  = $('#dia2');
    var ano   = $('#ano');
    var anoh  = $('#anoh');
    var cmd   = $('#cmd');
    var saida = $('#hsaida');

    var recessoDebito = $('#recessoDebito').val();

    var nDias = (parseInt(dia.val().substring(0, 2)) - parseInt(dia2.val().substring(0, 2)));


    // valida��o
    if (ocor.val() == "-----")
    {
        oTeste.setMsg('� obrigat�rio selecionar uma ocorr�ncia!', ocor);
    }

    if (dia2.val().length == 0)
    {
        oTeste.setMsg('� obrigat�rio informar a data de in�cio!', dia2);
    }
    else if (data_valida(dia2))
    {
        oTeste.setMsg('Data de in�cio inv�lida!', dia2);
    }

    if (dia.val().length == 0)
    {
        oTeste.setMsg('� obrigat�rio informar a data de t�rmino!', dia);
    }
    else if (data_valida(dia))
    {
        oTeste.setMsg('Data de t�rmino inv�lida!', dia);
    }

    if (nDias > limiteDias[ocor.val()])
    {
        oTeste.setMsg('Intervalo inv�lido para essa ocorr�ncia, m�ximo de ' + limiteDias[ocor.val()] + ' dias!', dia);
    }

    if ((dia2.val().substring(3, 2) != m.val()) || (dia.val().substring(3, 2) != m.val()))
    {
        oTeste.setMsg('Nesta op��o s� � permitido realizar registro de ocorr�ncia em dias do m�s corrente!', dia);
    }
    if (dia.val().substring(0, 2) < dia2.val().substring(0, 2))
    {
        oTeste.setMsg('Dia fim n�o pode ser inferior ao in�cio da ocorr�ncia!', dia);
    }

    var inirec = $('#inirec').val();
    var fimrec = $('#fimrec').val();
    var dia    = $('#hoje').val();

    if ((recessoDebito.indexOf(ocor.val()) > -1) && (dia < inirec || dia > fimrec))
    {
        oTeste.setMsg('N�o � permitido lan�ar recesso fora do per�odo legal!', ocor);
    }

    // se houve erro ser�(�o) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    return bResultado;
}

function ve(parm1)
{
    var dia  = $('#dia');
    var dia2 = $('#dia2');

    if (dia2.val().length == 10)
    {
        dia.focus();
    }
}
