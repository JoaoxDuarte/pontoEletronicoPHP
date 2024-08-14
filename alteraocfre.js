
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
        oTeste.setMsg('É obrigatório selecionar o responsável pela aplicação da ocorrência!', resp);
    }
    
    if (sAtivo.val() != 'S' && sAtivo.val() != 'N')
    {
        oTeste.setMsg("Ativo deve ser S ou N conforme o caso !", sAtivo);
    }
    
    if (aplic.val().length < 20)
    {
        oTeste.setMsg("Aplicação é um campo obrigatório !", aplic);
    }
    
    if (implic.val().length < 20)
    {
        oTeste.setMsg("Implicação é um campo obrigatório !", implic);
    }
    
    if (prazo.val().length == 0)
    {
        oTeste.setMsg("Prazo é um campo obrigatório !", prazo);
    }
    
    if (flegal.val().length == 0)
    {
        oTeste.setMsg("Fundamento legal é um campo obrigatório !", flegal);
    }
    
    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    return bResultado;
}
