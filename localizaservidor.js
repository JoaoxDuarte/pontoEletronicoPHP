
function verificadados()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var dtsailocal = $('#dtsailocal');
    var novalocal = $('#novalocal');
    var dtingnlocal = $('#dtingnlocal');

    if (dtsailocal.val().lenght == 0)
    {
        oTeste.setMsg('A SAIDA � obrigat�ria!', dtsailocal);
    }

    if (novalocal.val().length == 0)
    {
        oTeste.setMsg('A NOVA LOCALIZA��O � obrigat�ria !', novalocal);
    }

    if (dtingnlocal.val().length == 0)
    {
        oTeste.setMsg('A DATA DE INGRESSO � obrigat�ria !', dtingnlocal);
    }

    // se houve erro ser�(�o) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    if (bResultado == false)
    {
        return bResultado;
    }

    return true;
}
