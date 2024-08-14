<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

verifica_permissao("administracao_central");

$aba          = (isset($_REQUEST['aba']) && !empty($_REQUEST['aba']) ? $_REQUEST['aba'] : 'tabelas');
$tabela       = (isset($_POST['tabela']) && !empty($_POST['tabela']) ? $_POST['tabela'] : 'servativ');
$upag_selecao = (isset($_POST['upag_selecao']) && !empty($_POST['upag_selecao']) ? $_POST['upag_selecao'] : $_SESSION['upag']);

$_SESSION['upag_selecao'] = $upag_selecao;

// MONTA BÕTOES TABELAS
$tab_dados = array();
$tab_dados[] = array( "grp" => 1, "id" => "servativ",                         "title" => "SERVATIV",                      "width" => "230px", "padding-right" => "5px;" );
$tab_dados[] = array( "grp" => 1, "id" => "usuarios",                         "title" => "USUARIOS",                      "width" => "230px", "padding-right" => "5px;" );
$tab_dados[] = array( "grp" => 1, "id" => "ocupantes",                        "title" => "OCUPANTES",                     "width" => "230px", "padding-right" => "5px;" );
$tab_dados[] = array( "grp" => 1, "id" => "tabsetor",                         "title" => "TABSETOR",                      "width" => "230px", "padding-right" => "5px;" );
$tab_dados[] = array( "grp" => 1, "id" => "tabfunc",                          "title" => "TABFUNC",                       "width" => "230px", "padding-right" => "5px;" );
$tab_dados[] = array( "grp" => 1, "id" => "ips_setor",                        "title" => "IPS SETOR",                     "width" => "230px", "padding-right" => "5px;" );
$tab_dados[] = array( "grp" => 2, "id" => "substituicao",                     "title" => "SUBSTITUIÇÔES",                 "width" => "230px", "padding-right" => "5px;clear:left;" );
$tab_dados[] = array( "grp" => 2, "id" => "banco_de_horas",                   "title" => "SALDOS COMPENSAÇÕES",           "width" => "230px", "padding-right" => "5px;" );
$tab_dados[] = array( "grp" => 2, "id" => "autorizacoes_servidores",          "title" => "AUTORIZAÇÕES ACÚMULO",          "width" => "230px", "padding-right" => "5px;" );
$tab_dados[] = array( "grp" => 2, "id" => "acumulos_horas",                   "title" => "ACUMULOS HORAS",                "width" => "230px", "padding-right" => "5px;" );
$tab_dados[] = array( "grp" => 2, "id" => "autorizacoes_servidores_usufruto", "title" => "AUTORIZAÇÕES USUFRUTO",         "width" => "230px", "padding-right" => "5px;" );
$tab_dados[] = array( "grp" => 9, "id" => "servidores_autorizacao_ip",        "title" => "SERVIDORES AUTORIZAÇÃO DE IPS", "width" => "280px", "padding-right" => "5px;clear:left;" );


$grp   = 0;
$grupo  = '<div class="form-group col-md-12 text-left">';
$grupo .= "<SELECT id='tabela' name='tabela' class='form-control select2-single' title='Selecione uma opção!' onChange=\"javascript:submitFormulario(event,'ocentral.php')\">";

for ($x=0; $x < count($tab_dados); $x++)
{
    $grupo .= '<option value="'.$tab_dados[$x]['id'].'"' . ($tabela == $tab_dados[$x]['id'] ? ' selected' : '') . '>'.$tab_dados[$x]['title'].'</option>';
}

$grupo .= '</SELECT>';
$grupo .= '</div>';


$oDBase = selecionaUpags();


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setDataTables();
$oForm->setCSS( 'css/select2.min.css' );
$oForm->setCSS( 'css/select2-bootstrap.css' );
$oForm->setJS( 'js/select2.full.js' );
$oForm->setJS( 'js/jquery.mask.min.js');

$oForm->setSubTitulo( "Órgão Central - Administração SISREF" );

$oForm->exibeTopoHTML();
$oForm->printTituloTopoJanela( "Órgão Central" );
$oForm->exibeCorpoTopoHTML($width='1500px');


?>
<style>
#corlabel ul li a{
    color: #0a0a0a!important;
}
/* Style the tab */
.tab {
    overflow: hidden;
    border: 1px solid #ccc;
    background-color: #f1f1f1;
}

/* Style the buttons that are used to open the tab content */
.tab button {
    background-color: inherit;
    float: left;
    border: none;
    outline: none;
    cursor: pointer;
    padding: 14px 16px;
    transition: 0.3s;
}

/* Change background color of buttons on hover */
.tab button:hover {
    background-color: #ddd;
}

/* Create an active/current tablink class */
.tab button.active {
    background-color: #ccc;
}

/* Style the tab content */
.tabcontent {
    display: none;
    padding: 6px 12px;
    border: 1px solid #ccc;
    border-top: none;
}
</style>

<script>
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

        $("#btn-continuar").on('click',function(e) {
        var oForm = $("#form1");
        var $this = $(this);

        e.preventDefault();

        oForm.attr("onSubmit", "javascript:return true;");
        oForm.attr("action", "ocentral_frequencia_gravar.php");
        oForm.submit();
    });

    <?= $js; ?>
    
    //$('#siape').focus();
    
    $('#dia').mask('00/00/0000');
    $('#entra').mask('00:00:00');
    $('#iniint').mask('00:00:00');
    $('#fimint').mask('00:00:00');
    $('#sai').mask('00:00:00');
    $('#jornd').mask('00:00');
    $('#jornp').mask('00:00');
    $('#jorndif').mask('00:00');
    $('#oco').mask('00000');
});

function openCity(evt, cityName) 
{
    // Declare all variables
    var i, tabcontent, tablinks;
    var tabs = [];
            
    // Get all elements with class="tabcontent" and hide them
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    // Get all elements with class="tablinks" and remove the class "active"
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    // Show the current tab, and add an "active" class to the button that opened the tab
    document.getElementById(cityName).style.display = "block";
    evt.currentTarget.className += " active";
}

function submitFormulario(e,action)
{
    var oForm = $("#formTabelas");

    e.preventDefault();

    showProcessandoAguarde();
    
    oForm.attr("onSubmit", "javascript:return true;");
    oForm.attr("action", action);
    oForm.submit();
}
</script>

<div id="corlabel" class="content" >
    <div class="tab">
        <button class="tablinks uppercase" onclick="openCity(event, 'alterar')">Alterar Horários</button>
        <button class="tablinks uppercase" onclick="openCity(event, 'tabelas')">Tabelas</button>
    </div>
</div>
        
<div id="alterar" class="tabcontent" style="display:none">
<div class="container" style="text-align:left;margin:0px;padding:0px;">
    <!-- ---------------------------------------------------
         ALTERAR HORÁRIOS
    ---------------------------------------------------- -->
    <div class="col-md-12 table-bordered">
        <div class="corpo col-md-12">
            <div class="col-md-12 subtitle">
                <h6 class="lettering-tittle uppercase"><strong>Alterar Horários</strong></h6>
            </div>
            <div class="col-md-12 margin-bottom-25"></div>

            <form id="form1" name='form1' method='POST' action="#" onsubmit="javascript:return false;">
            <input type='hidden' id='aba' name='aba' value='alterar'>

            <div class="form-group col-md-6">
            <div class="form-group col-md-12">
                <div class="col-md-6 margin-10">
                    <label for="dia" class="control-label">Matrícula</label>
                </div>
                <div class="col-md-5">
                    <input type="text" id="siape" name="siape" size="12" maxlength="12" value="" class="form-control">
                </div>
            </div>

            <div class="form-group col-md-12">
                <div class="col-md-6 margin-10">
                    <label for="dia" class="control-label">Dia</label>
                </div>
                <div class="col-md-5">
                    <input type="text" id="dia" name="dia" size="10" maxlength="10" value="" class="form-control">
                </div>
            </div>

            <div class="form-group col-md-12">
                <div class="col-md-7 margin-10">
                    <label for="entra" class="control-label">Hora de Início do Expediente</label>
                </div>
                <div class="col-md-5">
                    <input type="text" id="entra" name="entra" title="Digite o horário sem pontos no formato 000000!" size="8" maxlength="8" value="" class="form-control text-center">
                </div>
            </div>

            <div class="form-group col-md-12">
                <div class="col-md-7 margin-10">
                    <label for="iniint" class="control-label">Hora de Início do Intervalo</label>
                </div>
                <div class="col-md-5">
                    <input type="text" id="iniint" name="iniint" title="Digite o horário sem pontos no formato 000000!" size="8" maxlength="8" value="" class="form-control text-center">
                </div>
            </div>

            <div class="form-group col-md-12">
                <div class="col-md-7 margin-10">
                    <label for="fimint" class="control-label">Hora de Retorno do Intervalo</label>
                </div>
                <div class="col-md-5">
                    <input type="text" id="fimint" name="fimint" title="Digite o horário sem pontos no formato 000000!" size="8" maxlength="8" value="" class="form-control text-center">
                </div>
            </div>

            <div class="form-group col-md-12">
                <div class="col-md-7 margin-10">
                    <label for="sai" class="control-label text-center">Horário da Saída</label>
                </div>
                <div class="col-md-5">
                    <input type="text" id="sai" name="sai" title="Digite o horário sem pontos no formato 000000!" size="8" maxlength="8" value="" class="form-control text-center">
                </div>
            </div>
            </div>

            <div class="form-group col-md-6 margin-80">
            <div class="form-group col-md-12 margin-25">
                <div class="col-md-5 margin-10">
                    <label for="entra" class="control-label">Jornada Realizada</label>
                </div>
                <div class="col-md-5">
                    <input type="text" id="jornd" name="jornd" title="Digite a jornada realizad no dia!" size="8" maxlength="8" value="" class="form-control text-center">
                </div>
            </div>
            <div class="form-group col-md-12">
                <div class="col-md-5 margin-10">
                    <label for="entra" class="control-label">Jornada Prevista</label>
                </div>
                <div class="col-md-5">
                    <input type="text" id="jornp" name="jornp" title="Digite a jornada prevista para este dia!" size="8" maxlength="8" value="" class="form-control text-center">
                </div>
            </div>
            <div class="form-group col-md-12">
                <div class="col-md-5 margin-10">
                    <label for="entra" class="control-label">Jornada Diferença</label>
                </div>
                <div class="col-md-5">
                    <input type="text" id="jorndif" name="jorndif" title="Digite a diferença de jornada apurada no dia!" size="8" maxlength="8" value="" class="form-control text-center">
                </div>
            </div>
            <div class="form-group col-md-12">
                <div class="col-md-5 margin-10">
                    <label for="sai" class="control-label text-center">Ocorrência</label>
                </div>
                <div class="col-md-5">
                    <input type="text" id="oco" name="oco" size="5" maxlength="5" value="" class="form-control text-center">
                </div>
            </div>
            </div>

            <div class="form-group col-md-12 text-center">
                <a class="btn btn-success btn-block" id="btn-continuar" role="button">
                    <span class="glyphicon glyphicon-ok"></span> Continuar
                </a>
            </div>


            </form>
        </div>
    </div>
</div>
</div>    
        <!-- ---------------------------------------------------
             SELECIONAR TABELA
        ---------------------------------------------------- -->
    <div id="tabelas" class="tabcontent" style="display:none">
    <div class="col-md-12 table-bordered">
        <form id="formTabelas" name='formTabelas' method='POST' action="#" onsubmit="javascript:return false;">
            <input type='hidden' id='aba' name='aba' value='tabelas'>
            <input type='hidden' id='tabelax' name='tabelax' value='<?= $tabela; ?>'>

            <div class="col-md-12">
                <div class="col-md-1 text-nowrap" style="padding-top:14px;">
                    <strong>Selecionar Tabela</strong>
                </div>
                <div class="col-md-4" style="padding-top:7px;">
                    <strong><?= $grupo; ?></strong>
                </div>
            </div>

            <div class="col-md-12">
                <div class="col-md-1 text-nowrap" style="padding-top:7px;">
                    <strong>Selecionar UPAG</strong>
                </div>
                <div class="col-md-9" style="padding-left:29px;padding-bottom:10px;">
                    <?php montaSelectUPAGs($name='upag_selecao', $upag_selecao, "javascript:submitFormulario(event,'ocentral.php#".$tabela."')"); ?>
                </div>
            </div>
        </form>
    </div>

<!-- ---------------------------------------------------
     EXIBIR TABELA
---------------------------------------------------- -->
     <a href="#<?= $tabela; ?>"></a>
            <div class="col-md-12 subtitle">
                <h6 class="lettering-tittle uppercase"><strong>Tabela: <?= $tabela . ' (' . getOrgaoByUorg($_SESSION['upag_selecao']).')'; ?></strong></h6>
            </div>
            <?php

            if ( !empty($tabela) )
            {
                include_once( 'ocentral_tabela_'.$tabela.'.php');
            }

            ?>
</div>

<script>
    var i   = 0;
    var aba = '<?= $aba; ?>';
    
    switch (aba)
    {
        case 'alterar': i = 0; break;
        case 'tabelas': i = 1; break;
    }
            
    // Get all elements with class="tabcontent" and hide them
    tabcontent = document.getElementsByClassName("tabcontent");
    for (x = 0; x < tabcontent.length; x++) {
        tabcontent[x].style.display = "none";
    }
    tabcontent[i].style.display = "block";

    // Get all elements with class="tablinks" and remove the class "active"
    tablinks = document.getElementsByClassName("tablinks");
    tablinks[i].className = tablinks[i].className.replace("tablinks", "tablinks active");
</script>
<?php

$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

exit();