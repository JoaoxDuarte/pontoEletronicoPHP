$(document).ready(function ()
{
    $('form1').keypress(function (e)
    {
        if (e == 13)
        {
            return autenticacaoAcesso();
        }
    });

    $('#dialog-saldos').dialog({
        autoOpen: false,
        modal: true,
        closeOnEscape: false,
        height: 500,
        width: 665
    });
    $('#show-saldos').click(function ()
    {
        showProcessando();
        var url = 'veponto_saldos_regponto.php';
        var dadosPonto = $('#veponto_saldos').val();
        $.ajax({
            type: 'POST',
            url: url,
            data: 'dadosPonto=' + dadosPonto,
            dataType: 'html',
            cache: false,
            complete: function (data)
            {
                // unblock when remote call returns
                $('#dialog-saldos').html(data.responseText);
                $('#dialog-saldos').dialog('open');
                hideProcessando();
            }
        });
    });
});
