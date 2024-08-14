
$(document).ready(function() {
    $("#btn-continuar-mixer").click(function ()
    {
        enviar();
    });
});

//
// Valida��o dos campos do formulario
//
function enviar()
{
    var urlDestino = 'historico_frequencia_sessao.php';
    var parametros  = $('#form1').serialize();

    var bResultado = validar();

    //if (bResultado==true)
    //{
        showProcessandoAguarde(); // mensagem processando

        //salvarSessao();

        $('#form1').attr( 'onsubmit', 'javascript:return true;' );
        $('#form1').attr( 'action', urlDestino+"?"+parametros );
        $('#form1').submit();
    //}
    
    return false;
}

function validar(soUm)
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var ano_hoje = $('#ano_hoje');
    var usuario  = $('#usuario');
    var siape    = $('#siape');
    var mes      = $('#mes');
    var ano      = $('#ano');

    var siape_responsavel = $('#siape_responsavel');

    var soUm = (soUm == null ? '' : soUm);
    var mensagem = '';

    // verifica se n�o � a mesma do usu�rio atual
    if (siape.val() == usuario.val().substr(5,7))
    {
            oTeste.setMsg( 'Voc� n�o pode alterar sua pr�pria frequ�ncia!', siape );
    }

    // verifica se n�o � a mesma do usu�rio atual
    if (siape_responsavel.val() == usuario.val().substr(5,7))
    {
            oTeste.setMsg( 'Voc� n�o pode ser o solicitante da altera��o na frequ�ncia!', siape );
    }
    
    // validacao do campo siape
    // testa o tamanho
    mensagem = validaSiape( siape.val() );
    if (mensagem != '') { oTeste.setMsg( mensagem, siape ); }

    // testa se o mes informado contem dois digitos
    // e se � um mes v�lido
    if (soUm == '' || soUm == 'mes')
    {
        mensagem = validaMes( mes.val() );
        if (mensagem != '') { oTeste.setMsg( mensagem, mes ); }
    }

    // testa se o ano informado contem quatro digitos
    // se n�o � menor que 2009, e se n�o � maior que o ano atual
    if (soUm == '' || soUm == 'ano')
    {
        mensagem = validaAno( ano.val(), mes.val() );
        if (mensagem != '') { oTeste.setMsg( mensagem, ano ); }
    }

    // validacao do campo siape_responsavel
    // testa o tamanho
    if (soUm == '' || soUm == 'siape_responsavel')
    {
        mensagem = validaSiape( siape_responsavel.val() );
        if (mensagem != '') { oTeste.setMsg( mensagem+' (RESPONS�VEL)', siape_responsavel ); }
    }

    // se houve erro ser�(�o) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    return bResultado;
}

//
// Verifica se atingiu o tmaanho do campo para passar para o pr�ximo
// aproveitamos para validar a matricula e o mes
//
function ve(parm1)
{
    var siape = $('#siape');
    var mes   = $('#mes');
    var ano   = $('#ano');

    var siape_responsavel = $('#siape_responsavel');

    if (siape_responsavel.val().length >= 7)
    {
            siape_responsavel.focus();
            //validar('siape_responsavel');
    }
    else if (ano.val().length >= 4)
    {
            siape_responsavel.focus();
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

/*-------------------------------------------------------\
|     AJAX - grava dados em sessao                       |
\-------------------------------------------------------*/
function salvarSessao()
{
    var ano_hoje = $('#ano_hoje');
    var usuario  = $('#usuario');
    var siape    = $('#siape');
    var mes      = $('#mes');
    var ano      = $('#ano');

    var siape_responsavel = $('#siape_responsavel');

    var urlDestino = 'historico_frequencia_ajax.php';

    var parametros  = 'ano_hoje=' + ano_hoje.val();
        parametros += '&usuario=' + usuario.val();
        parametros += '&siape=' + siape.val();
        parametros += '&mes=' + mes.val();
        parametros += '&ano=' + ano.val();
        parametros += '&siape_responsavel=' + siape_responsavel.val();

    //create the ajax request
    $.ajax({
            type: "POST",
            url:  urlDestino,
            data: parametros,
            dataType: "json",
            cache: false,
            complete: function(data) {}
    });

    return true;
}
