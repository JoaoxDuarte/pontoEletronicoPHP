$(document).ready(function ()
{
    $('input[type="text"]').keyup(function (e)
    {
        e.preventDefault();

        var $this = $(this);

        if ($this.attr("id") === 'cpf' && $this.val().length >= 14)
        {
            return verificados( $this, true );
        }
    });

    $('#btn-enviar').click(function (e)
    {
        e.preventDefault();

        var $this = $("#cpf");

        return verificados( $this );
    });
    
    $('#pSiape').focus();

    $('.cpf').mask('000.000.000-00', {reverse: true});
});


function verificados($this, $so_testar = false )
{
    if ($this.attr("id") === 'cpf' && $this.val().length >= 14)
    {
        if (validarCPF($this.val()))
        {
            if ($so_testar === false)
            {
                var $dados  = base64_encode( $this.val() );
                var destino = "javascript:window.location.replace('reiniciar3.php?dados=" + $dados + "');";
                $("#form1").attr("action", destino);
                $("#form1").submit();
            }
            
            return true;
        }
        else
        {
            mostraMensagem('CPF inválido!', 'danger');
            return false;
        }
    }
}
