$(document).ready(function ()
{

    $("#btn-continuar-horario").click(function ()
    {
        verificadados();
    });

    //$("#btn-ver-tabela-ocorrencias").click(function ()
    //{
    //    calculaHorario();
    //});

});

function verificadados()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    var entra   = $('#entra');
    var intini  = $('#intini');
    var intsai  = $('#intsai');
    var sai     = $('#sai');
    var jd2     = $('#jd2');
    var bhoras  = $('#bhoras');
    var bhtipo  = $('#bhtipo');
    var autchef = $('#autchef');

    var jornada = parseFloat(jd2.val()) * 60;

    if (entra.val().length < 5 || sai.val().length < 5 || intini.val().length < 5 || intsai.val().length < 5)
    {
        if (entra.val().length < 5)
        {
            oTeste.setMsg("A Entrada é obrigatória no formato hh:mm!", entra);
        }

        if (sai.val().length < 5)
        {
            oTeste.setMsg("A saida é obrigatória no formato hh:mm!", sai);
        }

        if (jornada == 480 && intini.val().length < 5)
        {
            oTeste.setMsg("O início do intervalo é obrigatório no formato hh:mm!", intini);
        }

        if ((jornada == 480 || horapausa > 0) && intsai.val().length < 5)
        {
            oTeste.setMsg("O retorno do intervalo é obrigatório no formato hh:mm!", intsai);
        }
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    if (bResultado == false)
    {
        return bResultado;
    }
    else
    {
        var dados = $('#dados').val();
        var dadosform = base64_encode($('#sitcad').val()
                + ":|:" + entra.val()
                + ":|:" + intini.val()
                + ":|:" + intsai.val()
                + ":|:" + sai.val()
                + ":|:" + bhoras.val()
                + ":|:" + bhtipo.val()
                + ":|:" + autchef.val()
                + ":|:" + $('#jornada').val()
                + ":|:" + $('#sAutorizadoTE').val()
                + ":|:" + $('#ocupaFuncao').val());
        var destino = "javascript:window.location.replace('frequencia_acompanhar_registros_horario_servico_gravar.php?dados=" + dados + "&dadosform=" + dadosform + "');";

        $("#form1").attr("onsubmit", "javascript:return true;");
        $("#form1").attr("action", destino);
        $("#form1").submit();
    }

    return false;
}




/*-------------------------------------------------------\
 |     AJAX - Grava horário                               |
 \-------------------------------------------------------*/
function calculaHorario(obj)
{
    // dados
    var entrada = $('#entra').val();
    var form_dados = $('#form1').serialize();

    if (entrada.length == 5)
    {
        // mensagem processando
        showProcessandoAguarde();

        //create the ajax request
        $.ajax({
            type: "POST",
            url: "frequencia_acompanhar_registros_horario_servico_calcula.php", // a pagina que sera chamada
            data: form_dados, // dados enviados
            timeout: 3000,
            dataType: "json",
            beforeSend: function ()
            {
                // enquanto a função esta sendo processada, você
                // pode exibir na tela uma msg de carregando
                //$('#mensagem_aviso').html('<img src="imagem/loading.gif"/>');
            },
            success: function (response)
            {
                var ojson = response.dados;

                // Número de itens
                var tam = ojson.length;

                hideProcessandoAguarde();

                if (tam == 0 || ojson[0].erro == null)
                {
                    mostraMensagem("Registro não localizado!", 'warning');
                }
                else if (ojson[0].erro != '')
                {
                    mostraMensagem(ojson[0].erro, 'danger');
                }
                else
                {
                    $("#sai").val(ojson[0].saida);
                }
            },
            error: function (txt)
            {
                // em caso de erro
                hideProcessandoAguarde();
                mostraMensagem('Houve um problema interno. Tente novamente.', 'danger');
            },
            complete: function (data)
            {
                hideProcessandoAguarde();
            }

        });
    }
    return true;
}
