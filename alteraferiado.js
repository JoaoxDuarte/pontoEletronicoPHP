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

    $("#btn-enviar").on('click', function ()
    {        
        return verificadados();
    });

    // Verifica o tipo
// if($('#id').length){
    if($('#tipo').val() == 'N'){
        $('#lot').attr("disabled", true);
        $('#lot').val('').trigger('change');

        $('#codmun').html("");
        $('#codmun').attr("disabled", true);
    }

    if($('#tipo').val() == 'E'){
        $('#lot').attr("disabled", false);

        $('#codmun').html("");
        $('#codmun').attr("disabled", true);
    }

    if($('#tipo').val() == 'M'){
        $('#lot').attr("disabled", false);
        $('#codmun').attr("disabled", false);
    }
    //}

    // Seleção do tipo
    $('#tipo').on('change', function(){
        verificarTipo();
    });

    // Seleção de municípios
    $('#lot').on('change', function(){
        selecionarMunicipio();
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

    if($('#sDescricao').val() == ""){
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

    if($('#flegal').val() == ""){
        oTeste.setMsg('O campo fundamentação legal é obrigatório!');
    }


    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    if (bResultado == true)
    {
        var dados = $('#form1').serialize();
        console.log('aqui');
        
        showProcessandoAguarde();
        
        $.ajax({
            url: "gravaalteraferiado.php",
            type: "POST",
            data: dados,
            dataType: "json"

        }).done(function(resultado) {
            console.log(resultado.mensagem + ' | ' + resultado.tipo);
            hideProcessandoAguarde();
            mostraMensagem(resultado.mensagem, resultado.tipo, null, null);

        }).fail(function(jqXHR, textStatus ) {
            console.log("Request failed: " + textStatus);
            hideProcessandoAguarde();

        }).always(function() {
            console.log("completou");
            hideProcessandoAguarde();

        });
        
        //$('#form1').submit();
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
