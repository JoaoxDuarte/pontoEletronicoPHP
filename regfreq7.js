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
        oTeste.setMsg('� obrigat�rio informar o motivo da devolu��o com, no m�nimo, 25 caracteres!', motivo );
    }

    // se houve erro ser�(�o) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    return bResultado;
}