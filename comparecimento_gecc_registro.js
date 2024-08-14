$(document).ready(function () {
   
    //$('#date').mask('##/##/####');
    //$('#date2').mask('##/##/####');
    $('#hora_ini').mask('00:00');
    $('#hora_fim').mask('00:00');
    $('#horas').mask('00:00');

    $('#hora_ini').on('keyup', function ()
    {
        var ini = $(this).val();
        var fim = $('#hora_fim').val();
        var tot = $('#horas');
        
        if (ini.length >= 5)
        {
            var segs_ini = time_to_sec(ini);
            var segs_fim = time_to_sec(fim);
            var resultado = 0;

            if (verificaHora($(this)))
            {
                if (segs_fim > segs_ini)
                {
                    resultado = (segs_fim - segs_ini);
                }
                
                console.log(resultado);
                tot.val( sec_to_time(resultado) );
            }
            else
            {
                mostraMensagem("Hora Inicial inválida!", "warning");
            }
        }
    });

    $('#hora_fim').on('keyup', function ()
    {
        var ini = $('#hora_ini').val();
        var fim = $(this).val();
        var tot = $('#horas');
        
        if (fim.length >= 5)
        {
            var segs_ini = time_to_sec(ini);
            var segs_fim = time_to_sec(fim);
            var resultado = 0;

            if (verificaHora($(this)))
            {
                if (segs_fim > segs_ini)
                {
                    resultado = (segs_fim - segs_ini);
                }
                
                console.log(resultado);
                tot.val( sec_to_time(resultado) );
            }
            else
            {
                mostraMensagem("Hora Final inválida!", "warning");
            }
        }
    });

    $("#btn-salvar-registro").on('click',function (e)
    {
        e.preventDefault();

        showProcessandoAguarde();

        // dados
        var oForm   = $('#form1');
        var destino = "comparecimento_gecc_registro_ajax.php";

        chamadaCrypty(oForm).then(function() {
            var dados = $("[name='dados']").val();
            
            $.ajax({
                url: destino,
                type: "POST",
                data: 'dados='+dados,
                dataType: "json"
            }).done(function(resultado) {
                console.log(resultado.mensagem + ' | ' + resultado.tipo);

                if (resultado.tipo == 'success')
                {
                    $("[name='data_ini']").val('');
                    $("[name='data_fim']").val('');
                    $("[name='hora_ini']").val('');
                    $("[name='hora_fim']").val('');
                    $("[name='horas']").val('');
                    $("[name='acrescimo_autorizado']").val('N');
                    $("[name='documento']").val('');
                }

                hideProcessandoAguarde();

                if ($("[name='id']").val() !== "")
                {
                    mostraMensagem(resultado.mensagem, resultado.tipo, "comparecimento_gecc.php", null);
                }
                else
                {
                    mostraMensagem(resultado.mensagem, resultado.tipo, null, null);
                }

            }).fail(function(jqXHR, textStatus ) {
                console.log("Request failed: " + textStatus);
                hideProcessandoAguarde();

            }).always(function() {
                console.log("completou");
                hideProcessandoAguarde();
            });
        });
    });
});

function chamadaCrypty(oForm)
{
    var dados = base64_encode(oForm.serialize());

    console.log( "(criptografia_key) "+dados );
    
    return $.ajax({
        url:  "inc/keys_dados.php",
        type: "POST",
        data: 'dados='+dados,
        dataType: "json"
    }).done(function(resultado) {
        console.log( "(1) "+resultado.dados[0].chave );
        $("[name='dados']").val( resultado.dados[0].chave );
    });
}
