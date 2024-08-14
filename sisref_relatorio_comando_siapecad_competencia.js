function validar(soUm)
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var soUm = (soUm == null ? '' : soUm);
    var msgTxt = '';
    var mes = $('#mes');
    var ano = $('#ano');

    // testa se o mes informado contem dois digitos
    // e se � um mes v�lido
    if (soUm == '' || soUm == 'mes')
    {
        msgTxt = validaMes(mes.val());
        if (msgTxt != '')
        {
            oTeste.setMsg(msgTxt, mes);
        }
    }

    // testa se o ano informado contem quatro digitos
    // se n�o � menor que 2009, e se n�o � maior que o ano atual
    if (soUm == '' || soUm == 'ano')
    {
        msgTxt = validaAno(ano.val(), mes.val());
        if (msgTxt != '')
        {
            oTeste.setMsg(msgTxt, ano);
        }
    }

    // se houve erro ser�(�o) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    return bResultado;
}

function ve(parm1)
{
    var mes = $('#mes');
    var ano = $('#ano');
    
    if (ano.val().length >= 4)
    {
        ano.focus();
        validar('ano');
    }
    else if (mes.val().length >= 2)
    {
        ano.focus();
        validar('mes');
    }
}
