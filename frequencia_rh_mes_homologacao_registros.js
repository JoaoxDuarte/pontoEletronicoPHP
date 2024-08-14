$(document).ready(function ()
{
    $('[data-load-remote]').on('click',function(e) {
        var oForm   = $("#form1");
        var $this   = $(this);
        var destino = "frequencia_rh_mes_homologacao_registros.php";
        var dados   = $this.data('load-remote-dados');
          
        console.log(dados);
        
        e.preventDefault();
       
        $('#dados').val( dados );
           
        oForm.attr("onSubmit", "javascript:return true;");
        oForm.attr("action", destino);
        oForm.submit();
    });



    $('[data-load-remote-voltar]').on('click',function(e) {
        var $this  = $(this);
        var remote = $this.data('load-remote');
        var dados  = $this.data('load-remote-dados');

        e.preventDefault();

        console.log(dados);

        showProcessandoAguarde();

        $('body').load(
            remote,
            null,
            function(response, status, xhr){
                if ( status == "error" ) 
                {
                    //var msg = "Sorry but there was an error: ";
                }
                hideProcessandoAguarde();
            }
        );
    });


    var $antes = "";

    $('[name=unidades_opcoes]').change(function() {
        
        if ($antes !== "")
        {
            $($antes).css("display","none");
        }
        
        if (this.value === 'todos')
        {
            $("[name=unidades]").css('display', "block");
        }
        else
        {
            $("[name=unidades]").css('display', "none");
        }
        
        $antes = this.value;
        
        $($antes).css("display","block");
        return false;
    });


    $("[name=unidades]").css('display', "none");
    $antes = $("[name=unidades_opcoes]").val();
    $($antes).css("display","block");
    
});
