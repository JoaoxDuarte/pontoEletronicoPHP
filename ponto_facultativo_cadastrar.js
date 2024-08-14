$(document).ready(function ()
{
    // Set the "bootstrap" theme as the default theme for all Select2
    // widgets.
    //
    // @see https://github.com/select2/select2/issues/2927
    $.fn.select2.defaults.set("theme", "bootstrap");


    // Seta as m�scaras utilizadas no formul�rio
    $(".maskHora").mask('##:##');
    $(".maskData").mask('##/##/####');

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
    if($('#id').length){
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
    }

    // Sele��o do tipo
    $('#tipo').on('change', function(){
        verificarTipo();
    });

    // Sele��o de munic�pios
    $('#lot').on('change', function(){
        selecionarMunicipio();
    });

    
    return false;


});

function verificadados()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    if($('#dia').val() == ""){
        oTeste.setMsg('O dia � obrigat�rio!');
    }

    if($('#mes').val() == ""){
        oTeste.setMsg('O m�s � obrigat�rio!');
    }

    if($('#desc').val() == ""){
        oTeste.setMsg('O descri��o � obrigat�rio!');
    }

    /*if($('#descricao').val() == ""){
        oTeste.setMsg('A observa��o � obrigat�rio!');
    }*/

    if($('#tipo').val() == ""){
        oTeste.setMsg('O tipo � obrigat�rio!');
    }

    if($('#tipo').val() == 'E' || $('#tipo').val() == 'M'){
        if($('#lot').val() == ""){
            oTeste.setMsg('O estado � obrigat�rio!');
        }
    }

    if($('#tipo').val() == 'M'){
        if($('#codmun').val() == ""){
            oTeste.setMsg('O munic�pio � obrigat�rio!');
        }
    }
    
    if($('#data_feriado').val() == ""){
        oTeste.setMsg('O campo data do feriado � obrigat�rio!');
    }

   
   /*  if($('#carga_horaria').val() == ""){
        oTeste.setMsg('O campo carga hor�ria � obrigat�rio!');
    }
    
    if($('#hora_inicio').val() == ""){
        oTeste.setMsg('O campo hor�rio de in�cio � obrigat�rio!');
    }

    if($('#hora_termino').val() == ""){
        oTeste.setMsg('O campo hora de t�rmino � obrigat�rio!');
    }*/

    if($('#grupo').val() == ""){
        oTeste.setMsg('O campo grupo � obrigat�rio!');
    }

    if($('#sigla').val() == ""){
        oTeste.setMsg('O campo sigla � obrigat�rio!');
    }


    var bResultado = oTeste.show();
   
    if (bResultado == true)
    {
        // dados
        var form_dados = $('#form1').serialize();
        var destino    = "gravacriarpontofacultativo.php";

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
            var options = "<option value=''>Selecione um munic�pio</option>";
            for(var key in resultado){
                options += "<option value='"+resultado[key]['numero']+"'>"+resultado[key]['numero'] + ' - ' + resultado[key]['nome']+"</option>";
            }

            $('#codmun').html("");
            $('#codmun').html(options);
        });
    }
}
