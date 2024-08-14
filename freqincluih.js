function validar()
{
    var pSiape = $('#pSiape');
    var mes    = $('#mes');
    var ano    = $('#ano');
    var an     = $('#an');
    var usu    = $('#usu');

    var mensagem = '';

    // validacao do campo siape
    // testa o tamanho
    mensagem = validaSiape(pSiape.val());
    if (mensagem != '')
    {
        oTeste.setMsg( 'É obrigatório informar a matrícula com no mínimo 7 caracteres!', pSiape);
    }

    if (pSiape.val() == usu.val())
    {
        oTeste.setMsg( 'Você não pode alterar sua própria frequência!', pSiape);
    }
    
    if ((mes.val().length < 2) || (mes.val() < 01 || mes.val() > 12))
    {
        oTeste.setMsg( 'Mes incorreto!\\nInforme com dois caracteres!', mes);
    }

    if ((ano.val().length < 4) || (ano.val() > an.val()))
    {
        oTeste.setMsg( 'Ano inválido!', ano);
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    return bResultado;
}

function ve(parm1)
{
}
