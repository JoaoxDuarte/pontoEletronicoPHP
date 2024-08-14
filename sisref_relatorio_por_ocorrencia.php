<?php
// funcoes de uso geral
include_once( "config.php" );

// permissao de acesso
verifica_permissao("relatorio_ocorrencia");

//definindo a competencia de homologacao
$oData = new trata_datasys();
$mes   = $oData->getMesAnterior();
$ano   = $oData->getAnoAnterior();
$comp  = $oData->getCompetHomologacao(); // mes e ano, ex.: 032010

if (isset($_POST['competencias_opcoes']) && !empty($_POST['competencias_opcoes']))
{
    $mes   = substr($_POST['competencias_opcoes'],-2);
    $ano   = substr($_POST['competencias_opcoes'],0,4);
}
else
{
    $mes = substr($comp, 0, 2);
    $ano = substr($comp, -4);
}

// pega o nome do arquivo origem
$pagina_de_origem = pagina_de_origem();

// parametros passados por formulario
$sMatricula = $_SESSION["sMatricula"];
$magico     = $_SESSION["magico"];

// dados para retorno a este script
$_SESSION['sPaginaRetorno_sucesso'] = $_SERVER['REQUEST_URI'];
$_SESSION['sImpPDF']                = "";

// instancia o banco de dados
$oDBase = new DataBase('PDO');


// filtro upag
if ($_SESSION['sSenhaI'] === "S")
{
    $filtro_upag = " codigo = upag ";
}
else if ($_SESSION['sGestaoUPAG'] === "S")
{
    $filtro_upag =  " codigo = upag AND LEFT(upag,5) = '".substr($_SESSION['upag'],0,5) . "' ";
}
else
{
    $filtro_upag = " codigo = '".$_SESSION['upag'] . "' ";
}


// tabela de setor
$selectSetores = "";
$oDBase->query("SELECT codigo, descricao FROM tabsetor WHERE ativo = 'S' AND " . $filtro_upag );

while ($campo         = $oDBase->fetch_array())
{
    $selectSetores .= "<option value=\"$campo[0]\"" . ($_SESSION['upag'] == $campo[0] ? 'selected' : '') . ">". tratarHTML($campo[0]) . " - " . substr(($campo[1] == 'PRESIDENCIA' ? "ADMINISTRAÇÃO CENTRAL" : tratarHTML($campo[1])), 0, 60) . "</option>";
}
// Fim da tabela de setor
// tabela de ocorrencia
$selectOcorrencias = "";
$oDBase->query("SELECT siapecad, desc_ocorr, cod_ocorr FROM tabocfre WHERE ativo = 'S' ORDER BY desc_ocorr ");
while ($campo             = $oDBase->fetch_array())
{
    $selectOcorrencias .= "<option value=\"$campo[0]\">" . tratarHTML($campo[0]) . " - " . substr(tratarHTML($campo[1]), 0, 60) . "</option>";
}
// Fim da tabela de ocorrencia
## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCSS("css/select2.min.css");
$oForm->setCSS("css/select2-bootstrap.css");
$oForm->setJS( "js/select2.full.js");
$oForm->setJS("sisref_relatorio_por_ocorrencia.js?v.0.0.0.0.0.1");
$oForm->setOnLoad("if ($('#mes') != null) { $('#mes').focus(); }");
$oForm->setSubTitulo("Informe a compet&ecirc;ncia e a ocorr&ecirc;ncia que deseja consultar");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

?>
<form id="form1" name="form1" method="POST" action="#" onsubmit="javascript:return false;">
    <input id="mesHoje" name="mesHoje" type="hidden" value="<?= date('m'); ?>">
    <input id="anoHoje" name="anoHoje" type="hidden" value="<?= date('Y'); ?>">

    <table class="table table-condensed table-bordered text-center">
        <tr>
            <td style='width: 10%; border-bottom: 0px; border-right: 0px;'>&nbsp;</td>
            <td class='tahomaSize_2' style='border-bottom: 0px; border-left: 0px; border-right: 0px; width: 50%; text-align: center;' align='center'>
                <div class="col-md-3 text-left">
                    <label class='control-label'>Competência</label>
                    <?php CarregaSelectCompetencia($ano, $mes); ?>
                </div>

                <div class="col-md-12 text-left">
                    <label class='control-label'>UPAG</label>
                    <select id='upag' name='upag' size='1' class="form-control select2-single">
                        <?= $selectSetores; ?>
                    </select>
                </div>

                <div class="col-md-12 text-left margin-25">
                    <label class='control-label'>Ocorrência(s)</label>
                    <select id="ocor" name="ocor[]" size="70" class="form-control select2-single select-ocorr" multiple style='height:100px'>
                        <?= $selectOcorrencias; ?>
                    </select>
                </div>
            </td>
            <td style='width: 10%; border-bottom: 0px; border-left: 0px;'>&nbsp;</td>
        </tr>
    </table>

    <div class="form-group">
        <div class="col-md-12">
            <div class="col-md-2 col-md-offset-5">
                <button type="submit" id="btn-continuar" class="btn btn-success btn-block">
                    <span class="glyphicon glyphicon-ok"></span> Continuar
                </button>
            </div>
        </div>
    </div>

    <div>
        <div style='text-align:right;width:90%;margin:25px;font-size:9px;border:0px;'>
            <fieldset style='border:1px solid white;text-align:left;'>
                <legend style='font-size:12px;padding:0px;margin:0px;'><b>&nbsp;Informações&nbsp;</b></legend>
                <p style='padding:1px;margin:0px;'>
                    <b>UPAG&nbsp;:&nbsp;</b>Selecione uma UPAG que deseja ver ocorrências.
                </p>
                <p style='padding:1px;margin:0px;'>
                    <b>Ocorrência(s)&nbsp;:&nbsp;</b>Clique na caixa abaixo de "Ocorrência(s)" para selecionar a(s) ocorrências.
                    <p style='padding:0px 0px 0px 69px;margin:0px;'>
                        Para selecionar mais de uma ocorrência, segure a tecla CTRL e clique com o mouse sobre os itens desejados.
                    </p>
                    <p style='padding:0px 0px 0px 69px;margin:0px;'>
                        Para apagar uma ocorrência escolhida, clique no "x" à esquerda da ocorrência.
                    </p>
                </p>
            </fieldset>
        </div>
    </div>

</form>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
