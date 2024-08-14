$(document).ready(function ()
{
    var dados_visualizar = "";

    // Set the "bootstrap" theme as the default theme for all Select2
    // widgets.
    //
    // @see https://github.com/select2/select2/issues/2927
    $.fn.select2.defaults.set("theme", "bootstrap");

    var placeholder = "TODAS AS UNIDADES"; //"Nenhuma das opções";

    $(".select2-single").select2({
        //placeholder: placeholder,
        width: '100%',
        containerCssClass: ':all:'
    });


    $('#prepara_impressao').on('click',function(e) {
        e.preventDefault();

        //showProcessandoAguarde();

        $('.imprimir_texto_link').css( "display", "block" );
        $('.no_print_link').css( "display", "none" );
        
        window.print();
        
        $('.imprimir_texto_link').css( "display", "none" );
        $('.no_print_link').css( "display", "block" );

        //hideProcessandoAguarde();
    });


    $('[data-load-remote]').on('click',function(e) {
        e.preventDefault();
        var $this = $(this);
        var remote = $this.data('load-remote');

        console.log(remote);

        showProcessandoAguarde();

        $('.modal-body-conteudo').html( "" );
        //$('.modal-body-conteudo').html( "<br><br><br><br><br><br>" );

        if(remote) {
            dados_visualizar = $this.data('remote-dados');
            remote += "?"+dados_visualizar;

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

    $('#myModalVisual').on('hidden.bs.modal', function (e) {
        $("#renderPDF").html( "" );
    });

    $('#myModalVisual').on('hide.bs.modal', function (e) {
        $("#renderPDF").html( "" );
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

    /*JS print click handler*/
    $('#btnPrint2').on('click', function(){
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


    $("#btn-continuar").on('click',function(e) {
        e.preventDefault();

        showProcessandoAguarde();

        var oForm = $('#form1');

        oForm.attr("onsubmit", "javascript:return true;");
        oForm.attr("action", "relatorio_frequencia_homologacoes.php");
        oForm.submit();
    });
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


(function($){
    $.fn.createPdf = function(parametros) {

        var config = {
            'fileName':'html-to-pdf'
        };

        if (parametros){
            $.extend(config, parametros);
        }

        var quotes = document.getElementById($(this).attr('id'));

        html2canvas(quotes, {
            onrendered: function(canvas) {
                var pdf = new jsPDF('p', 'pt', 'a4');

                var $width  = 1100; //1240; //2480; // 900;
                var $height = 1440; //1790; //3580; // 980;

                for (var i = 0; i <= quotes.clientHeight/$height; i++) {
                    var srcImg  = canvas;
                    var sX      = 0;
                    var sY      = $height*i;
                    var sWidth  = $width;
                    var sHeight = $height;
                    var dX      = 0;
                    var dY      = 0;
                    var dWidth  = $width;
                    var dHeight = $height;

                    window.onePageCanvas = document.createElement("canvas");
                    onePageCanvas.setAttribute('width', $width);
                    onePageCanvas.setAttribute('height', $height);
                    var ctx = onePageCanvas.getContext('2d');
                    ctx.drawImage(srcImg,sX,sY,sWidth,sHeight,dX,dY,dWidth,dHeight);

                    var canvasDataURL = onePageCanvas.toDataURL("image/png", 1.0);
                    var width         = onePageCanvas.width;
                    var height        = onePageCanvas.clientHeight;

                    if (i > 0) {
                        pdf.addPage();
                    }

                    pdf.setPage(i+1);
                    pdf.addImage(canvasDataURL, 'PNG', 20, 40, (width*.52), (height*.62));
                }

                pdf.save(config.fileName);
            }
        });
    };
})(jQuery);
