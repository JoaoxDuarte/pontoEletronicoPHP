$(document).ready(function ()
{
    // Set the "bootstrap" theme as the default theme for all Select2
    // widgets.
    //
    // @see https://github.com/select2/select2/issues/2927
    $.fn.select2.defaults.set("theme", "bootstrap");

    var placeholder = "Selecione uma Ocorrência";

    $(".select2-single").select2({
        placeholder: placeholder,
        width: null,
        containerCssClass: ':all:'
    });

    $("#btn-continuar").click(function ()
    {
        $("#form1").attr("action", "cadastro_inclusao_grava.php");
        $('#form1').submit();
    });

    $('#dt-container .input-group.date').datepicker({
        format: "dd/mm/yyyy",
        language: "pt-BR",
        //daysOfWeekDisabled: "0,6",
        startDate: "01/01/1900",
        autoclose: true,
        todayHighlight: true,
        toggleActive: true
    }).on('show', function(ev){
        var $this = $(this); //get the offset top of the element
        var eTop  = $this.offset().top; //get the offset top of the element
        $("td.old.disabled.day").css('color', '#e9e9e9');
        $("td.new.disabled.day").css('color', '#e9e9e9');
        $("td.disabled.day").css('color', '#e9e9e9');
        $(".datepicker.datepicker-dropdown.dropdown-menu.datepicker-orient-left.datepicker-orient-top").css('top', (eTop-280));
    });

    $('#dt-container').on('changeDate', function() {
        ve(this);
    });

    $('#wlota').change(function() {
        var valor = this.value;
        var str = $( "#loca option:selected" ).val();

        if (str == '00000000' || str == '0000000000000')
        {
            $('#loca option[value="' + valor + '"]').attr("selected",true);
            $('#loca').trigger('change');
        }
    });

    // IMPLEMENTAÇÃO DAS FUNCIONALIDADES REFERENTES AO SIAPE

    $(document).on('click', '#display-button-import', function () {
        $('.import').show();
    });
    $(document).on('click', '.import-go', function () {

        var cpf = $('.cpf-import').val();
        var botao = $('.import-go');
        var modal = $('.import');

        // VALIDA SE O CAMPO NÃO ESTÁ VAZIO
        if (cpf === '') {
            mostraMensagem('É necessário informar o CPF!', 'warning');
            return false;
        }

        botao.attr('disabled', 'disabled');
        botao.html('Importando...');

        // RECUPERA AS INFORMAÇÕES DO SERVIDOR ATRAVÉS DA API DO SIAPE
        $.get(
            "cadastro_inclusao.php",
            "getInfoByApiSiape=true&cpf=" + cpf,
            function (data)
            {
                if (data)
                {
                    parsed = JSON.parse(data);

                    if (parsed.success == false)
                    {
                        $('.cpf-import').val("");
                        botao.html('Importar');
                        botao.removeAttr('disabled');
                        mostraMensagem('CPF inválido!', 'warning');
                        return false;
                    }

                    if (parsed.success == true)
                    {
                        autoCompleteInputs(parsed.response);
                        botao.html('Importar');
                        botao.removeAttr('disabled');
                        modal.css('display','none');
                    }
                }
                else
                {
                    mostraMensagem('Problema no acesso ao SIAPE!', 'warning');
                    $('.cpf-import').val("");
                    botao.html('Importar');
                    botao.removeAttr('disabled');
                }
            }
        );
    });

});


function ve(parm1)
{
    // dados
    var wdatinss = $('#wdatinss');
    var datjorn  = $('#datjorn');
    var datlot   = $('#datlot');
    var datloca  = $('#datloca');

    if (wdatinss.val().length == 10)
    {
        if (datjorn.val().trim() == '' || datjorn.val().trim() == '00/00/0000')
        {
            datjorn.val( $('#wdatinss').val() );
        }
        if (datlot.val().trim() == '' || datlot.val().trim() == '00/00/0000')
        {
            datlot.val( $('#wdatinss').val() );
        }
        if (datloca.val().trim() == '' || datloca.val().trim() == '00/00/0000')
        {
            datloca.val( $('#wdatinss').val() );
        }
    }
/*
    // dados
    var wnome        = $('#wnome');
    var tSiape       = $('#tSiape');
    var Siapecad     = $('#Siapecad');
    var idunica      = $('#idunica');
    var Situacao     = $('#Situacao');
    var Situacao_val = $('select[name=Situacao]').val();
    var wcargo       = $('#wcargo');
    var wcargo_val   = $('select[name=wcargo]').val();
    var nivel        = $('#nivel');
    var Regjur       = $('#Regjur');
    var Regjur_val   = $('select[name=Regjur]').val();
    var wdatinss     = $('#wdatinss');
    var Jornada      = $('#Jornada');
    var datjorn      = $('#datjorn');
    var defvis       = $("#defvis");
    var defvis_val   = $("input[name='defvis']:checked").val();
    var pis          = $('#pis');
    var cpf          = $('#cpf');
    var dtnasc       = $('#dtnasc');
    var wlota        = $('#wlota');
    var datlot       = $('#datlot');

    if (wnome.val().length == 60) {
        tSiape.focus();
    }
    if (tSiape.val().length == 7) {
        Siapecad.focus();
    }
    if (Siapecad.val().length == 8) {
        idunica.focus();
    }
    if (idunica.val().length == 9) {
        Situacao.focus();
    }
    if (Situacao_val != '00') {
        email.focus();
    }
    if (email.length == 50) {
        wcargo.focus();
    }
    if (Situacao_val != '66' && wcargo_val != '000000') {
        nivel.focus();
    }
    if (nivel.val().length == 2) {
        Regjur.focus();
    }
    if (Regjur_val != 0) {
        wdatinss.focus();
    }
    if (wdatinss.val().length == 10) {
        Jornada.focus();
    }
    if (Jornada.val().length == 2) {
        datjorn.focus();
    }
    if (datjorn.val().length == 10) {
        defvis.focus();
    }
    if (defvis_val == 'S' || defvis_val == 'N') {
        pis.focus();
    }
    if (pis.val().length == 11) {
        cpf.focus();
    }
    if (cpf.val().length == 11) {
        dtnasc.focus();
    }
    if (dtnasc.val().length == 10) {
        wlota.focus();
    }
    if (wlota.val() == '00000000' || wlota.val() == '0000000000000') {
        datlot.focus();
    }
    if (datlot.val().length == 10) {
        loca.focus();
    }
    if (loca.val() == '00000000' || loca.val() == '0000000000000') {
        datloca.focus();
    }
*/
}

/**
 * @param src
 * @param mask
 * @info formata de acordo com a máscara passada
 */
function formatar(src, mask)
{
    var i = src.value.length;
    var saida = mask.substring(0,1);
    var texto = mask.substring(i);
    if (texto.substring(0,1) != saida)
    {
        src.value += texto.substring(0,1);
    }
}

/**
 * @info autopreenche os campos do formulario de cadastro de
 * @param dados
 */
function autoCompleteInputs(dados) {
    $("#wnome").val(dados.nome);
    $("#tSiape").val(dados.matricula);
    $("#idunica").val(dados.identificacao_unica);
    $("#email").val(dados.email);
    $("#Jornada_cargo").val(dados.jornada_cod);
    $("#Jornada").val(dados.jornada_cod);
    $("#pis").val(dados.pis_pasep);
    $("#cpf").val(dados.cpf);
    $("#nivel").val(dados.nivel);
    $("#dtnasc").val( formatDate(dados.data_nasc ) );
    $("#wdatinss").val( formatDate(dados.admissao) );
    $("#datloca").val( formatDate(dados.ingresso_localizacao) );
    $("#datlot").val( formatDate(dados.ingresso_unidade) );
    $("#datjorn").val( formatDate(dados.ingresso_jornada) );
    $("#datlot").val( formatDate(dados.ingresso_lotacao) );
    $("#Regjur").select2('val',dados.regime);
    $("#wlota").select2().val(dados.unidade_exercicio).trigger("change");
    $("#loca").select2().val(dados.localizacao).trigger("change");
	//$("#Regjur option:contains(" + dados.regime_juridico_nome +")").attr('selected', 'selected').trigger("change");

    if(dados.situacao_funcional != "") {
        if ($("#Situacao option[value=" + dados.situacao_funcional + "]").length != 0) {
            $("#Situacao").select2().val(dados.situacao_funcional).trigger("change");
        }
    }

    if(dados.cargo_efetivo != "") {
        if ($("#wcargo option[value=" + dados.cargo_efetivo + "]").length != 0) {
            $("#wcargo").select2().val(dados.cargo_efetivo).trigger("change");
        }
    }

    if(dados.cod_def_fisica !== ""){
        $("#defvis").val("S").trigger("change");
    }
}

/**
 *
 * @param date
 * @info formata data
 * @returns {string}
 */
function formatDate(date){
    var dia,mes,ano;

    dia = date.substring(0,2);
    mes = date.substring(2,4);
    ano = date.substring(4,8);

    return dia + "/" + mes + "/" + ano;
}
