$(document).ready(function ()
{
    $('#btn-enviar').on('click', function ()
    {
        var $form1 = $("#form1");

        console.log( 'btn-enviar' );

        $form1.attr("onsubmit", "javascript:return true;");
        $form1.attr("action", "entrada1.php");
        $form1.submit();
    });

    $('input[type="text"]').keyup(function (e)
    {
        e.preventDefault();

        var $this    = $(this);
        var mensagem = "";

        console.log( $this.attr("id") );

        if ($this.attr("id") === 'lSiape' && $this.val().length >= 14)
        {
            if (validarCPF($this.val()))
            {
                $('#lSenha').focus();
            }
            else
            {
                mostraMensagem('CPF inválido!', 'danger');
            }
        }
    });

    $('input[type="password"]').keyup(function (e)
    {
        e.preventDefault();

        var $this     = $(this);
        var maxlength = $this.attr( 'maxlength' );

        if (($this.attr("id") === 'lSenha' && $this.val().length >= maxlength))
        {
            if ($('#txtImagem') !== null)
            {
                $('#txtImagem').focus();
            }
        }
    });

    $('#lSiape').focus();

    $('.cpf').mask('000.000.000-00', {reverse: true});
});
