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
        oTeste.setMsg('O campo c�digo � obrigat�rio!');
    }

    if(siapecad.length != 5){
        console.log(siapecad);
        oTeste.setMsg('O campo c�digo deve ter 5 caractares!');
    }

    if($('#sDescricao').val() == ""){
        oTeste.setMsg('A campo descri��o da ocorr�ncia � obrigat�ria!');
    }

    if($('#resp').val() == ""){
        oTeste.setMsg('O campo respons�vel � obrigat�rio!');
    }

    if($('#sAtivo').val() == ""){
        oTeste.setMsg('O campo ativo � obrigat�rio!');
    }

    if($('#smap_ocorrencia').val() == ""){
        oTeste.setMsg('O campo smap ocorr�ncia � obrigat�rio!');
    }

    if($('#cod_ocorr').val() == ""){
        oTeste.setMsg('O campo c�digo da ocorr�ncia � obrigat�rio!');
    }

    if($('#cod_siape').val() == ""){
        oTeste.setMsg('O campo c�digo siape da ocorr�ncia � obrigat�rio!');
    }

    if($('#semrem').val() == ""){
        oTeste.setMsg('O campo semrem � obrigat�rio!');
    }

    if($('#idsiapecad').val() == ""){
        oTeste.setMsg('O campo id siapecad � obrigat�rio!');
    }

    if($('#grupo').val() == ""){
        oTeste.setMsg('O campo grupo � obrigat�rio!');
    }

    if($('#tipo').val() == ""){
        oTeste.setMsg('O campo tipo � obrigat�rio!');
    }

    if($('#situacao').val() == ""){
        oTeste.setMsg('O campo situacao � obrigat�rio!');
    }

    if($('#justificativa').val() == ""){
        oTeste.setMsg('O campo justificativa � obrigat�rio!');
    }

    if($('#postergar_pagar_recesso').val() == ""){
        oTeste.setMsg('O campo post. rec. � obrigat�rio!');
    }

    if($('#tratamento_debito').val() == ""){
        oTeste.setMsg('O campo tratamento d�bito � obrigat�rio!');
    }

    if($('#padrao').val() == ""){
        oTeste.setMsg('O campo padr�o � obrigat�rio!');
    }

    if($('#grupo_cadastral').val() == ""){
        oTeste.setMsg('O campo grupo cadastral � obrigat�rio!');
    }

 /*   if($('#agrupa_debito').val() == ""){
        oTeste.setMsg('O campo agrupa debito � obrigat�rio!');
    }*/

    if($('#grupo_ocorrencia').val() == ""){
        oTeste.setMsg('O campo grupo ocorr�ncia � obrigat�rio!');
    }

    if($('#informar_horarios').val() == ""){
        oTeste.setMsg('O campo informar hor�rios � obrigat�rio!');
    }

    if($('#vigencia_inicio').val() == ""){
        oTeste.setMsg('O campo vig�ncia in�cio � obrigat�rio!');
    }

    var aplic = new String($('#aplic').val());
    if(aplic == ""){
        oTeste.setMsg('O campo aplica��o � obrigat�rio!');
    }

    if(aplic.length < 10){
        oTeste.setMsg('O campo aplica��o deve ter no m�nimo 10 caracteres!');
    }

    var implic = new String($('#implic').val());
    if(implic == ""){
        oTeste.setMsg('O campo implica��o � obrigat�rio!');
    }

    if(implic.length < 10){
        oTeste.setMsg('Implica��o do c�digo deve ser informado com no m�nimo 10 caracteres!');
    }

    var prazo = new String($('#prazo').val());
    if(prazo == ""){
        oTeste.setMsg('O campo prazo � obrigat�rio!');
    }

    if(prazo.length < 10){
        oTeste.setMsg('Prazo aplica��o/implica��o do codigo deve ser informado com no m�nimo 10 caracteres!');
    }

    var flegal = new String($('#flegal').val());
    if(flegal == ""){
        oTeste.setMsg('O campo fundamento legal � obrigat�rio!');
    }

    if(flegal.length < 10){
        oTeste.setMsg('Fundamenta��o legal do c�digo deve ser informado com no m�nimo 10 caracteres!');
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

