$(document).ready(function ()
{
    var dados_visualizar = "";

    // Set the "bootstrap" theme as the default theme for all Select2
    // widgets.
    //
    // @see https://github.com/select2/select2/issues/2927
    $.fn.select2.defaults.set("theme", "bootstrap");

    var placeholder = "Selecione uma unidade";

    $(".select2-single").select2({
        placeholder: placeholder,
        width: '100%',
        containerCssClass: ':all:'
    });


    $('[data-load-remote]').on('click',function(e) {
        e.preventDefault();
        var $this = $(this);
        var remote = $this.data('load-remote');

        console.log(remote);

        showProcessandoAguarde();
        
        $('.modal-body-conteudo').html( "<br><br><br><br><br><br>" );
        
        if(remote) {
            dados_visualizar = $this.data('remote-dados');
            console.log(dados_visualizar);
            $($this.data('remote-target')).load(remote, null, 
                function(response){
                    hideProcessandoAguarde();
                });
        }

        // Espera 5 segundos e desativa o loading
        //setTimeout(function(){
        //    spinner.removeClass('active'); 
        //}, 5000);
    });
    
    
    
    /*JS print click handler*/
    $('#btnPrint').on('click', function(){
        //var arquivo_pdf = str.replace(/.\w+$/,"") + '.pdf';
        var arquivo_pdf = "documento.pdf";
        
        showProcessandoAguarde();

        $("#renderPDF").html( $('.modal-body-conteudo').html() );
        $('#renderPDF').createPdf({
            'fileName' : arquivo_pdf
        });

        // Espera 5 segundos e desativa o loading
        setTimeout(function(){
            hideProcessandoAguarde(); 
        }, 5000);
    });


    $('#btnRejeitar').on('click',function(e) {
        e.preventDefault();
        var url = "frequencia_verificar_homologados_devolucao.php";
        var param = { 'dados' : dados_visualizar};

        OpenWindowWithPost(url,"scrollbars=yes","_blank",param);		
    });


    $('#btnVerificado').on('click',function(e) {
        e.preventDefault();
        var $this = $(this);
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


    $('[name="competencias_opcoes"]').on('change', function(e) {
        var oForm = $('#form1');

        oForm.attr("onsubmit", "javascript:return true;");
        oForm.attr("action", "frequencia_verificar_homologados.php");
        oForm.submit();
    });

    
    $("[name=unidades]").css('display', "none");
    $antes = $("[name=unidades_opcoes]").val();
    $($antes).css("display","block");
    
});

function closeIFrame()
{
    $('#dialog-view').dialog('close');
    parent.main.location.reload();
    return false;
}

function OpenWindowWithPost(url, windowoption, name, params)
{
    var form = document.createElement("form");
    
    form.setAttribute("method", "post");
    form.setAttribute("action", url);
    form.setAttribute("target", name);
 
    for (var i in params) 
    {
        if (params.hasOwnProperty(i)) 
        {
            var input = document.createElement('input');
           
            input.type = 'hidden';
            input.name = i;
            input.value = params[i];

            form.appendChild(input);
        }
    }
            
    document.body.appendChild(form);
            
    //note I am using a post.htm page since I did not want to make double request to the page 
    //it might have some Page_Load call which might screw things up.
    //window.open(url, name, windowoption);
            
    form.submit();
            
    document.body.removeChild(form);
}

function verJustificativa(texto)
{
    $('#modalBodyJustificativa').text(texto);
    $('#myModalVisualJustifica').modal();
}

