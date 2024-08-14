function verificadados()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var pSiape = $('#pSiape');
    var msg = validaSiape(pSiape.val(), true);

    if (msg != '')
    {
        oTeste.setMsg(msg, pSiape);
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    return bResultado;

}
