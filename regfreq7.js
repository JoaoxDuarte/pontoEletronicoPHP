function validar()
{
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var siape  = $('#siape');
    var motivo = $('#motivo');

    var mensagem = '';

    // validacao do campo siape
    // testa o tamanho
    mensagem = validaSiape(siape.val());
    if (mensagem != '')
    {
        oTeste.setMsg(mensagem, siape);
    }
    
    if (motivo.val().length < 25)
    {
        oTeste.setMsg('É obrigatório informar o motivo da devolução com, no mínimo, 25 caracteres!', motivo );
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    return bResultado;
}