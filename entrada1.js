$(document).ready(function ()
{
    TeclasDeAtalhoSistema();
    
    $('#myModal').on('shown.bs.modal', function () {
        $('#myInput').trigger('focus');
    });

    $('.salvar_nome').on('click',function(){

        var dados = jQuery('#form1').serialize();
        jQuery.ajax({
            type: "POST",
            url: "entrada1.php",
            data: dados,
            success: function( data )
            {
                jQuery('#nome_do_servidor').html( data );
                mostraMensagem("Alterado com sucesso!", 'succes');
                jQuery('#exampleModal').modal('hide');
            }
        });
    });
});

function letUserExit(destino)
{

    window.location.replace(destino);
    //$('#form1').attr( 'action', destino );
    //$('#form1').submit();

}

var repeticao = 0;

function confirma(msg, dest)
{
    BootstrapDialog.show({
        title: 'Informa&ccedil;&atilde;o',
        message: msg,
        buttons: [{
                label: 'Confirmar',
                cssClass: 'btn-success',
                action: function (dialog)
                {
                    letUserExit(dest);
                }
            }, {
                label: 'Cancelar',
                cssClass: 'btn-danger',
                action: function (dialog)
                {
                    dialog.close();
                }
            }]
    });

}

function confirmaAcessibilidade(msg, dest)
{
    repeticao++;
    
    // verificar quantas repeti��es, por haver 7 $(document).bind('keydown',...)
    if (repeticao == 7)
    {
        repeticao = 0;
        confirma(msg, dest);
    }
}

function EnviaPagina(dest)
{
    //repeticao++;
    // verificar quantas repeti��es, por haver 7 $(document).bind('keydown',...)
    //if (repeticao == 7)
    //{
        repeticao = 0;
        $('#form1').attr('onsubmit', "javascript:return true;");
        $('#form1').attr('action', dest);
        $('#form1').attr('target', '_blank');
        $('#form1').submit();
    //}
}

function TeclasDeAtalhoSistema()
{
    //if ($('#defvis').val() !== 'S')
    //{
    //    return false;
   // }

    var iniciar_almoco   = $("#iniciar_almoco").length;
    var finalizar_almoco = $("#finalizar_almoco").length;
    var fim_expediente   = $("#fim_expediente").length;

    // Binding keys

    // Tecla 2 - registrar a sa�da para o almo�o
    $(document).bind('keydown', 'Alt+2', function teclaAtalho()
    {
        if (iniciar_almoco > 0)
        {
            confirmaAcessibilidade('Deseja realmente registrar o inicio do intervalo?', 'entrada2.php');
        }
    });

    // Tecla 3 - registrar o retorno do almo�o
    $(document).bind('keydown', 'Alt+3', function teclaAtalho()
    {
        if (finalizar_almoco > 0)
        {
            confirmaAcessibilidade('Deseja realmente registrar o retorno do intervalo?', 'entrada3.php');
        }
    });

    // Tecla 4 - registrar fim do expediente
    $(document).bind('keydown', 'Alt+4', function teclaAtalho()
    {
        if (fim_expediente > 0)
        {
            confirmaAcessibilidade('Deseja realmente registrar o fim do expediente?', 'entrada4.php');
        }
    });

    // Tecla d - exibe o formul�rio para solicita��o de trabalho em dia n�o �til
    $(document).bind('keydown', 'Alt+5', function teclaAtalho()
    {
        window.open('autorizacao_trabalho_dia_nao_util_solicitacao.php?dados=' + $('#dados').val());
    });

    // Tecla m - exibe frequ�ncia do m�s corrente
    $(document).bind('keydown', 'Alt+6', function teclaAtalho()
    {
        window.open('pontoser.php?cmd=1&orig=1&lotacao=' + $('#lotacao').val());
    });

    // Tecla a - exibe formul�rio para escolher frequ�ncia de meses anteriores
    $(document).bind('keydown', 'Alt+7', function teclaAtalho()
    {
        window.open('entrada8.php?cmd=2&orig=1');
    });

    // Tecla c - exibe saldos de compensa��es
    $(document).bind('keydown', 'Alt+8', function teclaAtalho()
    {
        window.open('entrada9.php');
    });
}
