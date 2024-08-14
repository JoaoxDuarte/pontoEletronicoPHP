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
            useCORS: true,
            onrendered: function(canvas) {
                /*
                var top     = 5;
                var left    = -40;
                var largura = 1260;
                var altura  = 1400;
                var reducao = .53;
                */
                var top     = 35;
                var left    = -200;
                var largura = 2060;
                var altura  = 1420;
                var reducao = .53;

                var pdf = new jsPDF('p', 'pt', 'a4');

                for (var i = 0; i <= quotes.clientHeight/altura; i++) {
                    var srcImg  = canvas;
                    var sX      = 0;
                    var sY      = altura*i;
                    var sWidth  = largura;
                    var sHeight = altura;
                    var dX      = 0;
                    var dY      = 0;
                    var dWidth  = largura;
                    var dHeight = altura;

                    window.onePageCanvas = document.createElement("canvas");
                    onePageCanvas.setAttribute('width', largura);
                    onePageCanvas.setAttribute('height', altura);
                    var ctx = onePageCanvas.getContext('2d');
                    ctx.drawImage(srcImg,sX,sY,sWidth,sHeight,dX,dY,dWidth,dHeight);

                    var canvasDataURL = onePageCanvas.toDataURL("image/png", 1.0);
                    var width         = onePageCanvas.width;
                    var height        = onePageCanvas.clientHeight;

                    if (i > 0) {
                        pdf.addPage();
                    }

                    pdf.setPage(i+1);
                    pdf.addImage(canvasDataURL, 'PNG', left, top, (width*reducao), (height*reducao));
                }

                pdf.save(config.fileName);

                quotes.innerHTML = "";
            }
        });
    };
})(jQuery);
