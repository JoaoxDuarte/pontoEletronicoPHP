$(document).ready(function ()
{
    $('#btn-enviar').on('click', function (e)
    {
        verificadados();
    });

    $(".maskData").mask('##/##/####');
});

function verificadados()
{    
    // dados
    var form_dados = $('#form1').serialize();
    var destino    = "tabela_ocorrencia_de_frequencia_incluir_gravar.php";

    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    var siapecad = new String($('#siapecad').val());
    if(siapecad == ""){
        oTeste.setMsg('O campo código é obrigatório!');
    }

    if(siapecad.length != 5){
        console.log(siapecad);
        oTeste.setMsg('O campo código deve ter 5 caractares!');
    }

    if($('#sDescricao').val() == ""){
        oTeste.setMsg('A campo descrição da ocorrência é obrigatória!');
    }

    if($('#resp').val() == ""){
        oTeste.setMsg('O campo responsável é obrigatório!');
    }

    if($('#sAtivo').val() == ""){
        oTeste.setMsg('O campo ativo é obrigatório!');
    }

    if($('#smap_ocorrencia').val() == ""){
        oTeste.setMsg('O campo smap ocorrência é obrigatório!');
    }

    if($('#cod_ocorr').val() == ""){
        oTeste.setMsg('O campo código da ocorrência é obrigatório!');
    }

    if($('#cod_siape').val() == ""){
        oTeste.setMsg('O campo código siape da ocorrência é obrigatório!');
    }

    if($('#semrem').val() == ""){
        oTeste.setMsg('O campo semrem é obrigatório!');
    }

    if($('#idsiapecad').val() == ""){
        oTeste.setMsg('O campo id siapecad é obrigatório!');
    }

    if($('#grupo').val() == ""){
        oTeste.setMsg('O campo grupo é obrigatório!');
    }

    if($('#tipo').val() == ""){
        oTeste.setMsg('O campo tipo é obrigatório!');
    }

    if($('#situacao').val() == ""){
        oTeste.setMsg('O campo situacao é obrigatório!');
    }

    if($('#justificativa').val() == ""){
        oTeste.setMsg('O campo justificativa é obrigatório!');
    }

    if($('#postergar_pagar_recesso').val() == ""){
        oTeste.setMsg('O campo post. rec. é obrigatório!');
    }

    if($('#tratamento_debito').val() == ""){
        oTeste.setMsg('O campo tratamento débito é obrigatório!');
    }

    if($('#padrao').val() == ""){
        oTeste.setMsg('O campo padrão é obrigatório!');
    }

    if($('#grupo_cadastral').val() == ""){
        oTeste.setMsg('O campo grupo cadastral é obrigatório!');
    }

 /*   if($('#agrupa_debito').val() == ""){
        oTeste.setMsg('O campo agrupa debito é obrigatório!');
    }*/

    if($('#grupo_ocorrencia').val() == ""){
        oTeste.setMsg('O campo grupo ocorrência é obrigatório!');
    }

    if($('#informar_horarios').val() == ""){
        oTeste.setMsg('O campo informar horários é obrigatório!');
    }

    if($('#vigencia_inicio').val() == ""){
        oTeste.setMsg('O campo vigência início é obrigatório!');
    }

    var aplic = new String($('#aplic').val());
    if(aplic == ""){
        oTeste.setMsg('O campo aplicação é obrigatório!');
    }

    if(aplic.length < 10){
        oTeste.setMsg('O campo aplicação deve ter no mínimo 10 caracteres!');
    }

    var implic = new String($('#implic').val());
    if(implic == ""){
        oTeste.setMsg('O campo implicação é obrigatório!');
    }

    if(implic.length < 10){
        oTeste.setMsg('Implicação do código deve ser informado com no mínimo 10 caracteres!');
    }

    var prazo = new String($('#prazo').val());
    if(prazo == ""){
        oTeste.setMsg('O campo prazo é obrigatório!');
    }

    if(prazo.length < 10){
        oTeste.setMsg('Prazo aplicação/implicação do codigo deve ser informado com no mínimo 10 caracteres!');
    }

    var flegal = new String($('#flegal').val());
    if(flegal == ""){
        oTeste.setMsg('O campo fundamento legal é obrigatório!');
    }

    if(flegal.length < 10){
        oTeste.setMsg('Fundamentação legal do código deve ser informado com no mínimo 10 caracteres!');
    }

    var bResultado = oTeste.show();

    if (bResultado == true)
    {
        // mensagem processando
        showProcessandoAguarde();

        //create the ajax request
        $.ajax({
            url: destino,
            type: "POST",
            data: form_dados,
            dataType: "json"

        }).done(function(resultado) {
            console.log(resultado.mensagem);
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
    }

    return bResultado;
}

