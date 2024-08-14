function validar()
{
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var obj  = null;
    var msg  = "\nATEN��O!\n";
    var ocor = $('#ocor');
    //var dia2 = $('#dia2');
    var ano  = $('#ano');
    var dia  = $('#dia');
    var mes  = $('#mes');

    // verifica se houve sele��o de
    // algum c�digo de ocorr�ncia
    if (ocor.val().substring(0, 1) == "-")
    {
        oTeste.setMsg( "Selecionar uma ocorr�ncia.", ocor );
    }

    // verifica se foi informada
    // alguma data para a ocorr�ncia
    if (dia.val().length == 0)
    {
        oTeste.setMsg( "Informar a data da ocorr�ncia.", dia );
    }
    else if (!data_valida(dia.val()))
    {
        oTeste.setMsg( "Data informada inv�lida.", dia );
    }
    else if (dia.val().substring(6, 10) != ano.val())
    {
        oTeste.setMsg( "Ano inv�lido.", ano );
    }

    // se houve erro ser�(�o) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    return bResultado;
}


function testa()
{
    // dados
    var destino = "gravaregfreq2.php?modo=1";
    var ocor    = $('#ocor').val();
    var mat     = $('#mat').val();
    var dia     = $('#dia').val();
    var cmd     = $('#cmd').val();
    var nome    = $('#nome').val();
    var jnd     = $('#jnd').val();

    var credito = $('#codigosCreditos').val();
    var debito  = $('#debitosCompensaveis').val();

    if (debito.indexOf(ocor) > -1)
    {
        destino = 'registro13.php?'
            + 'mat=' + mat + '&dia=' + dia + '&cmd=' + cmd + '&ocor=' + ocor;
    }
    if (credito.indexOf(ocor) > -1)
    {
        destino = 'registro14.php?'
            + 'mat=' + mat + '&dia=' + dia + '&cmd=' + cmd + '&ocor=' + ocor;
    }

    if (destino != "")
    {
        $("#form1").attr("action", destino);
        $('#form1').submit();
    }
}
