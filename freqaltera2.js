function validar()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var pSiape = $('#mat');
    var usu    = $('#usu');
    var mes    = $('#mes');
    var ano    = $('#ano');
    var an     = $('#an');

    var mensagem = '';

    // validacao do campo siape
    // testa o tamanho
    mensagem = validaSiape(pSiape.val());
    if (mensagem != '')
    {
        oTeste.setMsg(mensagem, pSiape);
    }

    if (pSiape.val() == usu.val())
    {
        oTeste.setMsg('Voc� n�o pode alterar sua pr�pria frequ�ncia!', pSiape);
    }
    if ((mes.val().length < 2) || (mes.val() < 01 || mes.val() > 12))
    {
        oTeste.setMsg('Mes incorreto!\\nInforme com dois caracteres!', mes);
    }
    if ((ano.val().length < 4) || (ano.val() > an.val()))
    {
        oTeste.setMsg('Ano inv�lido!', ano);
    }

    // se houve erro ser�(�o) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    return bResultado;
}

function ve(parm1)
{
}
