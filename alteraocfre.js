
function verificadados()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var siape  = $('#siape');
    var resp   = $('#resp');
    var sAtivo = $('#sAtivo');
    var aplic  = $('#aplic');
    var implic = $('#implic');
    var prazo  = $('#prazo');
    var flegal = $('#flegal');

    oTeste.setMsgTitulo("Digite/Selecione:\n");

    // validacao do campo siape
    mensagem = validaSiape(siape.val());
    if (mensagem != '')
    {
        oTeste.setMsg(mensagem, siape);
    }

    if (resp.val() == 'ZZ')
    {
        oTeste.setMsg('� obrigat�rio selecionar o respons�vel pela aplica��o da ocorr�ncia!', resp);
    }
    
    if (sAtivo.val() != 'S' && sAtivo.val() != 'N')
    {
        oTeste.setMsg("Ativo deve ser S ou N conforme o caso !", sAtivo);
    }
    
    if (aplic.val().length < 20)
    {
        oTeste.setMsg("Aplica��o � um campo obrigat�rio !", aplic);
    }
    
    if (implic.val().length < 20)
    {
        oTeste.setMsg("Implica��o � um campo obrigat�rio !", implic);
    }
    
    if (prazo.val().length == 0)
    {
        oTeste.setMsg("Prazo � um campo obrigat�rio !", prazo);
    }
    
    if (flegal.val().length == 0)
    {
        oTeste.setMsg("Fundamento legal � um campo obrigat�rio !", flegal);
    }
    
    // se houve erro ser�(�o) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    return bResultado;
}
