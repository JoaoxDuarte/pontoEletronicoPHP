<?php
// funcoes de uso geral
include_once( "config.php" );

// permissao de acesso
verifica_permissao("sLog");


## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setJSDatePicker();
$oForm->setSubTitulo("Relatório de Auditoria do Sistema");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


?>
<script>
$(document).ready(function () {
    $('#dt-container .input-group.date').datepicker({
        format: "dd/mm/yyyy",
        language: "pt-BR",
        startDate: "<?= '01/01/2018'; ?>",
        endDate: "<?= '31/12/'.$ano; ?>",
        autoclose: true,
        todayHighlight: true,
        toggleActive: true,
        orientation: "bottom auto",
        maxViewMode: 0,
        datesDisabled: ['10/06/2018', '10/21/2018']
    }).on('show', function(ev){
        var $this = $(this); //get the offset top of the element
        var eTop  = $this.offset().top; //get the offset top of the element
        $("td.old.disabled.day").css('color', '#e9e9e9');
        $("td.new.disabled.day").css('color', '#e9e9e9');
        $("td.disabled.day").css('color', '#e9e9e9');
        $(".datepicker.datepicker-dropdown.dropdown-menu.datepicker-orient-left.datepicker-orient-bottom").css('top', (eTop+10));
    });
});
</script>

<div id="container">
    
    <form method="POST" action="rellog.php" id="form1" name="form1">
    
        <div class="row text-left margin-10" id="total_de_registros">
            <p style="padding:0px;margin:0px;vertical-align:bottom;">Esse relatório, demonstrará todas as atividades ocorridas no sistema dentro de um período estipulado. Com base nessas informações, chegar-se-á aos autores de cada ação dentro do sistema.</p>
        </div>

        
        <div class="row col-lg-12 col-md-12 col-xs-12 col-sm-12 text-center margin-25">
            <div class="row col-lg-2 col-md-2 col-xs-2 col-sm-2">
                <label class="label-control">Período:</label>
            </div>
            
            <div class="col-lg-3 col-md-3 col-xs-3 col-sm-3" id="dt-container">
                <div class='input-group date'>
                    <input type='text' id="inicio" name="inicio" placeholder="dd/mm/aaaa" value="<?= tratarHTML($inicio); ?>" class="form-control" autocomplete="off"/>
                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                </div>
            </div>

            <div class="col-lg-3 col-md-3 col-xs-3 col-sm-3" id="dt-container">
                <div class='input-group date'>
                    <input type='text' id="fim" name="fim" placeholder="dd/mm/aaaa" value="<?= tratarHTML($fim); ?>" class="form-control" autocomplete="off"/>
                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                </div>
            </div>
        </div>

        <div class="row col-lg-12 col-md-12 col-xs-12 col-sm-12 text-center margin-25">
            <div class="row col-lg-2 col-md-2 col-xs-2 col-sm-2">
                <label class="label-control">Pesquisar:</label>
            </div>

            <div class="col-md-10 text-left">
                <input type="text" class='form-control' id="chave" name="chave" title="Não informe pontos" size="28" maxlength="28" value='<?= tratarHTML($_POST['chave']); ?>'>
            </div>
        </div>

        <div class="row">
            <div class="col-md-2 col-xs-6 col-md-offset-5 margin-30 margin-bottom-30">
                <button class="btn btn-success btn-block" id="btn-continuar" role="button">
                    <span class="glyphicon glyphicon-search"></span> Pesquisar
                </button>
            </div>
        </div>

    </form>
    
</div>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
