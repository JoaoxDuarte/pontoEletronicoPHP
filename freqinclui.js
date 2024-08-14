$(document).ready(function ()
{
    // Set the "bootstrap" theme as the default theme for all Select2
    // widgets.
    //
    // @see https://github.com/select2/select2/issues/2927
    $.fn.select2.defaults.set("theme", "bootstrap");

    var placeholder = "Selecione uma Ocorr�ncia";

    $(".select2-single").select2({
        placeholder: placeholder,
        width: null,
        containerCssClass: ':all:'
    });

    $("#btn-continuar").on('click', function () 
    {
        if(validar())
        {
            // mensagem processando
            showProcessando();

            $(".formsiape").attr('onsubmit', "javascript:return true;");
            $(".formsiape").attr('action', "comparecimento_gecc_registro.php");
            $(".formsiape").submit();
        }
        else
        {
            mostraMensagem(mensagem);
            return false;
        }
    });    
});

function validar()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var pSiape = $('#pSiape');
    var usu    = $('#usu');
    var mes    = $('#mes');
    var ano    = $('#ano');
    var an     = $('#an');

    var mensagem = '';

    // validacao do campo siape
    // testa o tamanho
    mensagem = validaSiape(pSiape.val());
    if (mensagem != '')
    {
        oTeste.setMsg( '� obrigat�rio informar a matr�cula com no m�nimo 7 caracteres!', pSiape);
    }

    if (pSiape.val() == usu.val())
    {
        oTeste.setMsg('Voc� n�o pode alterar sua pr�pria frequ�ncia!', pSiape);
    }

    if ((mes.val().length < 2) || (mes.val() < 01 || mes.val() > 12))
    {
        oTeste.setMsg('Mes incorreto!! Informe com dois caracteres!', mes);
    }
    
    if ((ano.val().length < 4) || (ano.val() > an.val()))
    {
        oTeste.setMsg('Ano inv�lido!', ano);
    }

    // se houve erro ser�(�o) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    return bResultado;
}

function ve(parm1)
{
}
