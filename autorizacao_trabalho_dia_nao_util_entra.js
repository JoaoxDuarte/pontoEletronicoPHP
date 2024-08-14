function enviar_dados()
{
    var qlotacao = $('#qlotacao').val();

    var parametros = base64_encode(qlotacao);

    $('#dados').val( parametros );
    var destino = "javascript:window.location.replace('autorizacao_trabalho_dia_nao_util.php?dados=" + parametros + "');";

    $("#form1").attr("action", destino);
    $('#form1').submit();
}