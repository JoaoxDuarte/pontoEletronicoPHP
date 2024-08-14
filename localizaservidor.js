
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
        oTeste.setMsg('A SAIDA é obrigatória!', dtsailocal);
    }

    if (novalocal.val().length == 0)
    {
        oTeste.setMsg('A NOVA LOCALIZAÇÃO é obrigatória !', novalocal);
    }

    if (dtingnlocal.val().length == 0)
    {
        oTeste.setMsg('A DATA DE INGRESSO é obrigatória !', dtingnlocal);
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    if (bResultado == false)
    {
        return bResultado;
    }

    return true;
}
