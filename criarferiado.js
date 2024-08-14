$(document).ready(function ()
{
    // Set the "bootstrap" theme as the default theme for all Select2
    // widgets.
    //
    // @see https://github.com/select2/select2/issues/2927
    $.fn.select2.defaults.set("theme", "bootstrap");

    var placeholder = "Selecione";

    $(".select2-single").select2({
        placeholder: placeholder,
        width: null,
        containerCssClass: ':all:'
    });

    // Verifica o tipo
    if($('#id').length){
        verificarTipo();
    }

    // Seleção do tipo
    $('#tipo').on('change', function(){
        verificarTipo();
    });

    // Seleção de municípios
    $('#lot').on('change', function(){
        selecionarMunicipio();
    });

    $("#btn-enviar").on('click', function ()
    {
        console.log('29');
        
        return verificadados();
    });
});

function verificadados()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var dia = $('#dia');
    var mes = $('#mes');

     // valida o dia
     if (dia.val().length == 0)
     {
         oTeste.setMsg('O dia é obrigatório!', dia);
     }
     if (dia.val().length == 1)
     {
         oTeste.setMsg('Informe o dia com 2 números!', dia);
     }
     if (parseInt(dia.val()) > parseInt(nDiasDoMes))
     {
         oTeste.setMsg('Dia inválido!', dia);
     }
 
     if($('#sDescricao').val() == ""){
        oTeste.setMsg('O campo descrição é obrigatório!');
     }

     // valida o mes
     if (mes.val().length == 0)
     {
         oTeste.setMsg("O mês é um campo obrigatório!", mes);
     }
     if (mes.val().length == 1)
     {
         oTeste.setMsg("Informe o mês com 2 números!", mes);
     }
     if (mes.val() > 12)
     {
         oTeste.setMsg("Mes inválido!", mes);
     }

          if($('#desc').val() == ""){
         oTeste.setMsg('O descrição é obrigatório!');
     }
 
     if($('#tipo').val() == ""){
         oTeste.setMsg('O tipo é obrigatório!');
     }
 
     if($('#tipo').val() == 'E' || $('#tipo').val() == 'M'){
         if($('#lot').val() == ""){
             oTeste.setMsg('O estado é obrigatório!');
         }
     }
 
     if($('#tipo').val() == 'M'){
         if($('#codmun').val() == ""){
             oTeste.setMsg('O município é obrigatório!');
         }
     }

     if($('#base_legal').val() == ""){
        oTeste.setMsg('O campo fundamentação legal é obrigatório!');
    }



    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    if (bResultado == true)
    {
        // dados
        var form_dados = $('#form1').serialize();
        var destino    = "gravacriarferiado.php";

        // mensagem processando
        showProcessandoAguarde();

        //create the ajax request
        $.ajax({
            url: destino,
            type: "POST",
            data: form_dados,
            dataType: "json"

        }).done(function(resultado) {
            console.log(resultado.mensagem + ' | ' + resultado.tipo);
            hideProcessandoAguarde();
            mostraMensagem(resultado.mensagem, resultado.tipo);

        }).fail(function(jqXHR, textStatus ) {
            console.log("Houve um problema interno: " + textStatus);
            hideProcessandoAguarde();
            mostraMensagem("Houve um problema interno: " + textStatus, "danger");

        }).always(function() {
            console.log("completou");
            hideProcessandoAguarde();

        });

        return false;
    }

    return bResultado;
}


function verificarTipo(){
    if($('#tipo').val() == 'N'){
        $('#lot').attr("disabled", true);
        $('#lot').val('').trigger('change');

        $('#codmun').html("");
        $('#codmun').attr("disabled", true);
    }

    if($('#tipo').val() == 'E'){
        $('#lot').attr("disabled", false);
        $('#lot').val('').trigger('change');

        $('#codmun').html("");
        $('#codmun').attr("disabled", true);
    }

    if($('#tipo').val() == 'M'){
        $('#lot').attr("disabled", false);
        $('#lot').val('').trigger('change');

        $('#codmun').html("");
        $('#codmun').attr("disabled", false);
    }
}

function selecionarMunicipio(){
    if($('#tipo').val() == 'M'){
        $.ajax({
            url: 'cidades_ajax.php',
            type: "POST",
            data: {uf: $('#lot').val()},
            dataType: "json"
        }).done(function(resultado) {
            var options = "<option value=''>Selecione um município</option>";
            for(var key in resultado){
                options += "<option value='"+resultado[key]['numero']+"'>"+resultado[key]['numero'] + ' - ' + resultado[key]['nome']+"</option>";
            }

            $('#codmun').html("");
            $('#codmun').html(options);
        });
    }
}
