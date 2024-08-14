function enviar_dados()
{
    var cmd      = $('#cmd').val();
    var orig     = $('#orig').val();
    var dia      = $('#dia').val();
    var qlotacao = $('#qlotacao').val();

    var parametros = base64_encode(cmd + ":|:" + qlotacao);

    $('#dados').val( parametros );
    var destino = "javascript:window.location.replace('frequencia_homologar.php?dados=" + parametros + "');";
        
    $("#form1").attr("onsubmit", "javascript:return true;");
    $("#form1").attr("action", destino);
    $('#form1').submit();
}