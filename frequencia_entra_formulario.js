$(document).ready(function ()
{
    // Set the "bootstrap" theme as the default theme for all Select2
    // widgets.
    //
    // @see https://github.com/select2/select2/issues/2927
    $.fn.select2.defaults.set("theme", "bootstrap");

    var placeholder = "Selecione uma lotação";

    if($('#qlotacao option').length == 1) {
        clickContinuar();
    };

    $(".select2-single").select2({
        placeholder: placeholder,
        width: '100%',
        containerCssClass: ':all:'
    });
    
    $("#btn-continuar").click(clickContinuar);
});

function clickContinuar(){
	var cmd        = $('#cmd').val();
	var orig       = $('#orig').val();
	var dia        = $('#dia').val();
	var qlotacao   = $('#qlotacao').val();
        
        var parametros = "";

        switch ($('#form_caminho').val())
        {
            case "Acompanhar": 
                parametros = base64_encode( cmd + ":|:" + orig + ":|:" + qlotacao + ":|:" + dia );
                break;
            case "Homologar": 
                parametros = base64_encode( cmd + ":|:" + qlotacao + ":|:" + dia );
                break;
            default: 
                parametros = base64_encode( qlotacao + ":|:" + dia );
                break;
        }

	var destino = "javascript:window.location.replace('"+$("#form_destino").val()+"?dados="+parametros+"');";

        $('#dados').val( parametros );
        
        showProcessandoAguarde();

        $('#form1').attr('onsubmit', "onSubmit='javascript:return true;'");
        $("#form1").attr("action", destino);
        $('#form1').submit();
}
