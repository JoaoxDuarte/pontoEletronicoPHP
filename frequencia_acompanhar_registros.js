$(document).ready(function ()
{
    $('[data-load-acompanhar-frequencia-enviar]').on('click',function(e) {
        var $this = $(this);

        e.preventDefault();

        showProcessandoAguarde();
        
        window.location.replace( $this.data('load-acompanhar-frequencia-enviar') );
    });

    $('[data-load-acompanhar-frequencia-voltar]').on('click',function(e) {
        var $this = $(this);
        var remote = $this.data('load-acompanhar-frequencia-voltar');

        e.preventDefault();

        window.location.replace( remote );
    });

    $('[data-load-acompanhar-frequencia-covid19]').on('click',function(e) {
        var $this = $(this);
        var remote = $this.data('load-acompanhar-frequencia-covid19');

        e.preventDefault();

        window.location.replace( remote );
    });
});