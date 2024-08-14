$(document).ready(function ()
{
    expandingWindow(841);

    $("form").keypress(function (e)
    {
        if (e.which == 13 || e.keyCode == 13)
        {
            return validar();
        }
    });

    $('input[type="text"]').keyup(function (e)
    {
        if ((this.name === 'ano' && this.value.length >= 4))
        {
            $('#ano').focus();
            validar();
        }
        if ((this.name === 'mes' && this.value.length >= 2))
        {
            $('#ano').focus();
            validar('mes');
        }
    });

    $('#btn-enviar').click(function ()
    {
        validar();
    });

    $('#mes').focus();
});

function validar(soUm)
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    var destino = 'pontoser.php?cmd=1';
    // dados
    var soUm = (soUm == null ? '' : soUm);
    var msgTxt = '';
    var mes = $('#mes');
    var ano = $('#ano');

    // testa se o mes informado contem dois digitos
    // e se é um mes válido
    if (soUm === '' || soUm == 'mes')
    {
        msgTxt = validaMes(mes.val());
        if (msgTxt !== '')
        {
            oTeste.setMsg(msgTxt, mes);
        }
    }

    // testa se o ano informado contem quatro digitos
    // se não é menor que 2009, e se não é maior que o ano atual
    if (soUm === '' || soUm == 'ano')
    {
        msgTxt = validaAno(ano.val(), mes.val());
        if (msgTxt !== '')
        {
            oTeste.setMsg(msgTxt, ano);
        }
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    if (bResultado == false)
    {
        return bResultado;
    }
    else if (bResultado == true && soUm == '')
    {

        // mensagem processando
        showProcessando();

        $("#form1").attr("action", destino);
        $("#form1").submit();
    }
}
