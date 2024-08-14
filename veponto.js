$(document).ready(function ()
{
    $("body").keypress(function (e)
    {
        if (e.which == 27 || e.keyCode == 27)
        {
            return validar();
        }
    });

    $("form").keypress(function (e)
    {
        if (e.which == 13 || e.keyCode == 13)
        {
            return validar();
        }
    });

    $("input").keypress(function (e)
    {
        if (e.which == 13 || e.keyCode == 13)
        {
            return validar();
        }
    });

    $("#btn-continuar-mixer").click(function ()
    {
        return validar();
    });
});


function validar(soUm)
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var soUm = (soUm == null ? '' : soUm);

    var siape = $('#pSiape');
    var mes = $('#mes3');
    var ano = $('#ano3');

    var mensagem = '';

    // validacao do campo siape
    // testa o tamanho
    mensagem = validaSiape(siape.val());
    if (mensagem != '')
    {
        oTeste.setMsg(mensagem, siape);
    }

    // testa se o mes informado contem dois digitos
    // e se é um mes válido
    if (soUm == '' || soUm == 'mes')
    {
        mensagem = validaMes(mes.val());
        if (mensagem != '')
        {
            oTeste.setMsg(mensagem, mes);
        }
    }

    // testa se o ano informado contem quatro digitos
    // se não é menor que 2009, e se não é maior que o ano atual
    if (soUm == '' || soUm == 'ano')
    {
        mensagem = validaAno(ano.val(), mes.val());
        if (mensagem != '')
        {
            oTeste.setMsg(mensagem, ano);
        }
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    if (bResultado == false)
    {
        return bResultado;
    }
    else
    {
        showProcessandoAguarde();
        $('#form1').submit();
    }

}

function ve(parm1)
{
    var siape = $('#pSiape');
    var mes = $('#mes3');
    var ano = $('#ano3');
    if (ano.val().length >= 4)
    {
        ano.focus();
        //validar('ano');
    }
    else if (mes.val().length >= 2)
    {
        ano.focus();
        //validar('mes');
    }
    else if (siape.val().length >= 7)
    {
        mes.focus();
        //validar('siape');
    }
}
