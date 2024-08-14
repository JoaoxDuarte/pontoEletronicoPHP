function carregaDados(sMat, sAno)
{
    spinner.addClass('active');
    
    $.ajax({
        type: "POST",
        url: "ficha_de_frequencia_resumo_anual_html_lista.php",
        data: "siape=" + sMat + "&ano=" + sAno,
        dataType: "html",
        cache: false,
        complete: function (data)
        {
            // types the "type" property changes to "error", "warning", "question", "information" and "confirmation"
            // and the text for the "title" property also changes
            /*
             $.Zebra_Dialog('Processo concluído!', {
             'type':     'information',
             'title':    'Aviso'
             });
             */

            // unblock when remote call returns
            $("#ficha tbody").append(data.responseText);

            // Espera 5 segundos e desativa o loading
            setTimeout(function(){
                spinner.removeClass('active'); 
            }, 2000);
        }
    });
}
