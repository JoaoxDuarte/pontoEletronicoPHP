
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


    // validação
    if (ocor.val() == "-----")
    {
        oTeste.setMsg('É obrigatório selecionar uma ocorrência!', ocor);
    }

    if (dia2.val().length == 0)
    {
        oTeste.setMsg('É obrigatório informar a data de início!', dia2);
    }
    else if (data_valida(dia2))
    {
        oTeste.setMsg('Data de início inválida!', dia2);
    }

    if (dia.val().length == 0)
    {
        oTeste.setMsg('É obrigatório informar a data de término!', dia);
    }
    else if (data_valida(dia))
    {
        oTeste.setMsg('Data de término inválida!', dia);
    }

    if (nDias > limiteDias[ocor.val()])
    {
        oTeste.setMsg('Intervalo inválido para essa ocorrência, máximo de ' + limiteDias[ocor.val()] + ' dias!', dia);
    }

    if ((dia2.val().substring(3, 2) != m.val()) || (dia.val().substring(3, 2) != m.val()))
    {
        oTeste.setMsg('Nesta opção só é permitido realizar registro de ocorrência em dias do mês corrente!', dia);
    }
    if (dia.val().substring(0, 2) < dia2.val().substring(0, 2))
    {
        oTeste.setMsg('Dia fim não pode ser inferior ao início da ocorrência!', dia);
    }

    var inirec = $('#inirec').val();
    var fimrec = $('#fimrec').val();
    var dia    = $('#hoje').val();

    if ((recessoDebito.indexOf(ocor.val()) > -1) && (dia < inirec || dia > fimrec))
    {
        oTeste.setMsg('Não é permitido lançar recesso fora do período legal!', ocor);
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
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
