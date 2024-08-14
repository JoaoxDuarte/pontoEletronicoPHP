$(document).ready(function ()
{
    $('#dt-container .input-group.date').datepicker({
        format: "dd/mm/yyyy",
        language: "pt-BR",
        orientation: "bottom auto",
        daysOfWeekDisabled: "0,6",
        startDate: "01/01/1900",
        autoclose: true,
        todayHighlight: true,
        toggleActive: true,
        maxViewMode: 0,
        datesDisabled: ['10/06/2018', '10/21/2018']
    }).on('show', function(ev){
        var $this = $(this); //get the offset top of the element
        var eTop  = $this.offset().top; //get the offset top of the element
        $("td.old.disabled.day").css('color', '#e9e9e9');
        $("td.new.disabled.day").css('color', '#e9e9e9');
        $("td.disabled.day").css('color', '#e9e9e9');
        $(".datepicker.datepicker-dropdown.dropdown-menu.datepicker-orient-left.datepicker-orient-bottom").css('top', (eTop+10));
    });

    $("#checkTodos").click(function()
    {
        $('input:checkbox').not(this).prop('checked', this.checked);
    });

    $("#btn-enviar").on('click', function(e)
    {
        e.preventDefault();
        verificadados();
    });
    
    $('[data-load-remote]').on('click',function(e) {
        e.preventDefault();
        var $this = $(this);
        var remote = $this.data('load-remote');
        
        if(remote) {
            dados_visualizar = $this.data('remote-dados');
            $($this.data('remote-target')).load(remote);
        }
    });
   
});

hideProcessando();

function verificadados()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();
    
    // dados
    var dt_limite = $('#prorrogado_ate');

    showProcessandoAguarde();

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    if (bResultado === false)
    {
        return bResultado;
    }
    else
    {
        var oForm   = $('#form1');
        var destino = 'gestao_liberar_homologacao_gravar.php';
        var dados   = oForm.serialize();
        
        $.ajax({
            
            url:  destino,
            type: "POST",
            data: dados,
            dataType: "json"

        }).done(function(resultado) {
            
            console.log(resultado.mensagem + ' | ' + resultado.tipo);
            mostraMensagem(resultado.mensagem, resultado.tipo, null, null);

            if (resultado.tipo === 'success')
            {
                $.each( resultado.id, function( key, value ) {
                    var valor = value;
                    console.log( $('[value="'+valor+'"]').val());
                    //$('[value="'+valor+'"]').remove();
                    $('[id="'+valor+'"]').html("<b>Liberado</b>");
                });
                //$('[data-id="2"]').remove();
                //$('tbody').html( $html );
            }

            hideProcessandoAguarde();
            return false;

        }).fail(function(jqXHR, textStatus ) {
            
            console.log("Request failed: " + textStatus);
            hideProcessandoAguarde();
            return false;

        }).always(function() {
            
            console.log("completou");
            hideProcessandoAguarde();
            return false;
       
        });
    }

    return true;
}

function verificaSelecionouUnidade()
{
    var checado = false;

    $('[id="liberar_homologacao"]').each(function()
    {
        if (checado === false && $(this).is(":checked"))
        {
            checado = true;
        }
    });
    
    return checado;
}
