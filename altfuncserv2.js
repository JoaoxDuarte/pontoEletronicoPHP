function validar()
{
    // objeto mensagem
    oTeste = new oTeste.setMsgaErro();
    oTeste.init();

    // dados
    var matricula = $('#matricula');
    var inicio    = $('#inicio');
    var Ndoc1     = $('#Ndoc1');
    var Ndoc2     = $('#Ndoc2');
    var Nnum1     = $('#Nnum1');
    var Nnum2     = $('#Nnum2');
    var Ndata1    = $('#Ndata1');
    var Ndata2    = $('#Ndata2');

    // valida o dia
    if (matricula.val().length < 7)
    {
        oTeste.setMsg('Favor digite a matrícula com 7 digitos!', matricula);
    }

    if (inicio.val().length < 10)
    {
        oTeste.setMsg('Favor digite a data da nomeação no formato dd/mm/aaaa', inicio);
    }

    if (Ndoc1.val().length < 2)
    {
        oTeste.setMsg('Favor digite o documento com 2 dígitos', Ndoc1);
    }

    if (Ndoc2.val().length < 2)
    {
        oTeste.setMsg('Favor digite o documento com 2 dígitos', Ndoc2);
    }
    
    if (Nnum1.val().length == 0)
    {
        oTeste.setMsg('Favor digite o número do documento', Nnum1);
    }

    if (Nnum2.val().length == 0)
    {
        oTeste.setMsg('Favor digite o número do documento', Nnum2);
    }

    if (Ndata1.val().length < 10)
    {
        oTeste.setMsg('Favor digite a data do documento no formato dd/mm/aaaa', Ndata1);
    }

    if (Ndata2.val().length < 10)
    {
        oTeste.setMsg('Favor digite a data do documento no formato dd/mm/aaaa', Ndata2);
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    if (bResultado == true)
    {
        $('#form1').submit();
    }

    return bResultado;
}
