$(document).ready(function (){
    
    $('#form1').keypress(function (e)
    {
        if (e == 13)
        {
            return validar();
        }
    });

    $('input').keypress(function (e)
    {
        if (e.which == 13)
        {
            return validar();
        }
    });
    
    $('#btn-continuar-mixer').click(function ()
    {
        if (validar() == true)
        {
            var jqxhr = $.post("pesquisa_ip_html.php", 
                $( "#form1" ).serialize()
            )
            .done(function(data) {
                alert(data);
                $("div.container:eq(1)").html( data );
            })
            .fail(function() {
                mostraMensagem('Arquivo de destino não encontrado!', 'danger');
            })
            
            /*.always(function() {
                    alert( "finished" );
            })*/;
            
            $('#form1').attr('action',"pesquisa_ip_html.php");
            $('#form1').submit();
        }
    });
    
});


function validar(soUm)
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var siape = $('#pSiape');
    var mes   = $('#mes');
    var ano   = $('#ano');

    var soUm = (soUm == null ? '' : soUm);
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

    return bResultado;
}

//
// Verifica se atingiu o tmaanho do campo para passar para o próximo
// aproveitamos para validar a matricula e o mes
//
function ve(parm1)
{
    var siape = $('#pSiape');
    var mes   = $('#mes');
    var ano   = $('#ano');
    
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
    else if (siape.val().length >= 7)
    {
        mes.focus();
        validar('siape');
    }
}
