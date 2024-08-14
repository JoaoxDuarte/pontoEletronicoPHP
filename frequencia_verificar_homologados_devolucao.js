$(document).ready(function ()
{
    CKEDITOR.replace('motivo', {
        toolbarGroups: [
            { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
            { name: 'tools' },
            { name: 'document',    groups: [ 'mode', 'document', 'doctools' ] },
            { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
            /*{ name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align' ] },*/
            { name: 'styles' },
            /*{ name: 'colors' },*/
        ],
        resize_enabled: false,
    });
    
    $('#btn-continuar').on('click',function(e) {
        e.preventDefault();

        if (validar() == true)
        {
            var oForm   = $('#form1');
            var dados   = oForm.serialize();
            var destino = 'frequencia_verificar_homologados_devolucao_enviar_email.php';
            
            console.log(dados);
            
            showProcessandoAguarde();
            
            $.ajax({
                url: destino,
                type: "POST",
                data: dados,
                dataType: "json"

            }).done(function(resultado) {
                console.log(resultado.mensagem + ' | ' + resultado.tipo);
                hideProcessandoAguarde();
                mostraMensagem( resultado.mensagem, resultado.tipo, null, null );

            }).fail(function(jqXHR, textStatus ) {
                console.log("Request failed: " + textStatus);
                hideProcessandoAguarde();

            }).always(function() {
                console.log("completou");
                hideProcessandoAguarde();

            });
        }
        
        return false;
    });
    
    $('#btn-fechar-janela').on('click',function(e) {
        e.preventDefault();
        window.close();
    });
    
});


function validar()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var motivo = $("#motivo");

    motivo.val(CKEDITOR.instances.motivo.getData());

    // validacao do campo siape
    // testa o tamanho
    if (motivo.val().length < 25)
    {
        oTeste.setMsg( 'É obrigatório informar o motivo da devolução com 25 caracteres!', motivo );
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    return bResultado;
}
