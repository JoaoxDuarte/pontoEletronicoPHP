<?php
/**
 * +-------------------------------------------------------------+
 * | @description : Solicita Autorização Trabalho Dia Não Útil   |
 * |                                                             |
 * | @author  : Edinalvo Rosa                                    |
 * +-------------------------------------------------------------+
 * */
// funcoes de uso geral
include_once( "config.php" );

// permissao de acesso
verifica_permissao("sAPS"); //verifica_permissao("sRH ou Chefia");

$unidade = anti_injection($_REQUEST['unidade']);

$lotacao = (is_null($unidade) || empty($unidade) ? $_SESSION['sLotacao'] : $unidade);


$title = _SISTEMA_SIGLA_ . ' | Solicitação de Dilação de Prazo para Homologação';

## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setJSDialogProcessando();
$oForm->setJS( "js/phpjs.js" );
$oForm->setJS("gestao_liberar_homologacao_solicitacao.js?v.0.0.0.0.0.3");

$oForm->setSubTitulo("Solicitação de Dilação de Prazo para Homologação");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


// seleciona registros de solicitação anterior
$html_rows = montaTabelaSolicitacoes($lotacao);

// pagina atual
$_SESSION['voltar_nivel_1'] = $_SERVER['REQUEST_URI'];

?>
<div class="container">
    <div class="row">

        <form id="form1" name="form1" method="POST" action="#" onsubmit="javascript:return false;">

            <input type='hidden' id="modo"    name='modo'    value='5'>
            <input type='hidden' id="dados"   name='dados'   value=''>
            <input type='hidden' id="unidade" name='unidade' value='<?= $lotacao; ?>'>

            <div class="col-md-12">
                <div class="row">
                    <table class="table text-center">
                        <thead>
                            <tr>
                                <th class="text-center" style='vertical-align:middle;'>ÓRGÃO</th>
                                <th class="text-center" style='vertical-align:middle;'>LOTAÇÃO</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center text-nowrap"><h4><?= tratarHTML(getOrgaoMaisSigla( $lotacao )); ?></h4></td>
                                <td class="text-center"><h4><?= tratarHTML(getUorgMaisDescricao( $lotacao )); ?></h4></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-12">
                <div class="col-md-12 ">
                    <label class="control-label">Justificativa:</label>
                    <textarea class="form-control" id="justificativa" name="justificativa" rows="4" cols="81"></textarea>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="col-md-2 col-md-offset-5">
                        <label class="control-label " for="dnu">&nbsp;</label>
                        <button type="button" id="btn-enviar" name="enviar" class="btn btn-success btn-block">
                            <span class="glyphicon glyphicon-ok"></span> Gravar
                        </button>
                    </div>
                </div>
            </div>

        </form>

    </div>
    <div class="row margin-30">
        <div class="subtitle">
            <h5 class="uppercase"><strong>Situação das Solicitações</strong></h5>
        </div>
        <table class="table table-striped table-condensed table-bordered table-hover text-center">
            <thead>
                <tr>
                    <th class="text-center">Competência</th>
                    <th class="text-center">Matrícula</th>
                    <th class="text-center">Ocupante</th>
                    <th class="text-center">Solicitado Em</th>
                    <th class="text-center">Situação</th>
                    <th class="text-center">Liberada Até</th>
                </tr>
            </thead>
            <tbody>
                <?= $html_rows; ?>
            </tbody>
        </table>
    </div>
</div>
<?php

DataBase::fechaConexao();

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();



/* *****************************************************
 *                                                     *
 *               FUNÇÕES COMPLEMENTARES                *
 *                                                     *
 ***************************************************** */

/* @info  Seleciona registros de solicitação anterior
 *
 * @param  string  $setor  Unidade solicitante
 * @return  resource  $oDBase  Dados selecionados
 * @author  Edinalvo Rosa
 */
function selecionaSolicitacoesUnidade($setor)
{
    $oDBase = new DataBase();

    $oDBase->query("
    SELECT
        homologacao_dilacao_prazo.id,
        homologacao_dilacao_prazo.compet,
        homologacao_dilacao_prazo.siape,
        servativ.nome_serv AS nome,
        homologacao_dilacao_prazo.setor,
        tabsetor.descricao,
        homologacao_dilacao_prazo.justificativa,
        homologacao_dilacao_prazo.data_registro,
        homologacao_dilacao_prazo.siape_deliberacao,
        IFNULL(homologacao_dilacao_prazo.deliberacao,'PEDENTE') AS deliberacao,
        homologacao_dilacao_prazo.motivo_indeferimento,
        homologacao_dilacao_prazo.homologacao_limite,
        homologacao_dilacao_prazo.data_deliberacao
    FROM
        homologacao_dilacao_prazo
    LEFT JOIN
        servativ ON homologacao_dilacao_prazo.siape = servativ.mat_siape
    LEFT JOIN
        tabsetor ON homologacao_dilacao_prazo.setor = tabsetor.codigo
    WHERE
        homologacao_dilacao_prazo.setor = :setor
    ORDER BY
        homologacao_dilacao_prazo.compet DESC,
        homologacao_dilacao_prazo.data_registro DESC,
        homologacao_dilacao_prazo.siape
    ", array(
        array(":setor", $setor, PDO::PARAM_STR),
    ));

    return $oDBase;
}


/* @info  Monta a tabela com as solicitações da unidade
 *
 * @param  string  $setor Unidade solicitante
 * @return  resource  $oDBase  Dados selecionados
 * @author  Edinalvo Rosa
 */
function montaTabelaSolicitacoes($setor)
{
    $oDBase = selecionaSolicitacoesUnidade($setor);

    $html_row  = "#f8f8f1";
    $disabled  = "";
    $html_rows = "";

    while ($pm = $oDBase->fetch_object())
    {
        $aut           = ($pm->autorizado == "N" ? "AGUARDANDO AUTORIZAÇÃO" : "AUTORIZADO");
        $autorizado_em = ($pm->data_autorizado == '' ? '--------------------' : $pm->data_autorizado);

        $disabled = (inverteData($pm->dia) > date("Ymd") ? "" : "disabled");
        $html_row = ($html_row != "#f8f8f1" ? "#f8f8f1" : "#e8e8d0");

        $html_rows .= '<tr>';
        $html_rows .= '<td>' . substr($pm->compet,4,2).'/'.substr($pm->compet,0,4) . '</td>';
        $html_rows .= '<td title="'.$pm->nome.'" alt="'.$pm->nome.'" style="cursor:context-menu;">' . removeOrgaoMatricula($pm->siape) . '</td>';
        $html_rows .= '<td>' . $pm->sit_ocup . '</td>';
        $html_rows .= '<td>' . ($pm->data_registro == '0000-00-00' ? '----------' : databarra($pm->data_registro)) . '</td>';
        $html_rows .= '<td title="'.$pm->motivo_indeferimento.'" alt="'.$pm->motivo_indeferimento.'" style="cursor:context-menu;">' . $pm->deliberacao . '</td>';
        $html_rows .= '<td>' . ($pm->homologacao_limite == '0000-00-00' ? '----------------' : databarra($pm->homologacao_limite)) . '</td>';
        $html_rows .= '</tr>';
    } // fim do while

    return $html_rows;
}
