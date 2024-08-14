<?php
/**
 * +-------------------------------------------------------------+
 * | @description : Liberar Homologação                          |
 * |                                                             |
 * |                   FUNÇÕES COMPLEMENTARES                    |
 * |                                                             |
 * | @author  : Edinalvo Rosa                                    |
 * +-------------------------------------------------------------+
 * */

// funcoes de uso geral
include_once( "config.php" );


/* @info  Monta a tabela com as solicitações da unidade
 *
 * @param  string  $html_rows  Html com linhas das solicitações
 * @param  string  $wdatinss   Data limite da prorrogação
 * @return  string  Html com linhas das solicitações
 *
 * @author  Edinalvo Rosa
 */
function formularioHomologacaoSolicitacoes($html_rows="", $wdatinss="")
{
    ?>
    <div class="container">
        <div class="row">

            <form id="form1" name="form1" method="POST" action="#" onsubmit="javascript:return false;">

                <input type='hidden' id="modo"  name='modo'  value='5'>
                <input type='hidden' id="dados" name='dados' value=''>

                <div class="col-md-12">
                    <div class="row">
                        <table class="table text-center">
                            <thead>
                                <tr>
                                    <th class="text-center" style='vertical-align:middle;'>ÓRGÃO</th>
                                    <th class="text-center" style='vertical-align:middle;'>UPAG</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center text-nowrap"><h4><?= tratarHTML(getOrgaoMaisSigla( $_SESSION['sLotacao'] )); ?></h4></td>
                                    <td class="text-center"><h4><?= tratarHTML(getUorgMaisDescricao( $_SESSION['upag'] )); ?></h4></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-2 col-lg-offset-3" id="dt-container">
                        <label class="control-label " for="dnu">
                            Data Limite
                        </label>
                        <div class="input-group date">
                            <input type="text"
                                   class='form-control'
                                   id="prorrogado_ate"
                                   name="prorrogado_ate"
                                   size="10"
                                   maxlength="10"
                                   value='<?= tratarHTML($wdatinss); ?>'
                                   OnKeyPress="formatar(this, '##/##/####')"
                                   style="background-color:transparent;width:105px;" />
                            <span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="control-label" for="dnu">&nbsp;</label>
                        <button type="button" id="btn-enviar" name="enviar" class="btn btn-success btn-block">
                            <span class="glyphicon glyphicon-ok"></span> Gravar
                        </button>
                    </div>
                </div>
            </div>

            <div class="row margin-30">
                <div class="subtitle">
                    <h5 class="uppercase"><strong>Situação das Solicitações</strong></h5>
                </div>
                <table class="table table-striped table-condensed table-bordered table-hover text-center">
                    <thead>
                        <tr>
                            <th class="text-center">Competência</th>
                            <th class="text-center">Lotação</th>
                            <th class="text-center">Solicitado Em</th>
                            <th class="text-center">Justificativa</th>
                            <th class="text-center">Liberar
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="checkTodos" name="checkTodos">
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php

                    print $html_rows;

                    ?>
                    </tbody>
                </table>

            </form>

        </div>
    </div>
    <?php
}


/* @info  Monta a tabela com as solicitações da unidade
 *
 * @param  string  $setor Unidade solicitante
 * @return  string  Html com linhas das solicitações
 *
 * @author  Edinalvo Rosa
 */
function montaTabelaSolicitacoes($setor)
{
    $oDBase = selecionaSolicitacoesUnidade();

    $html_row  = "#f8f8f1";
    $disabled  = "";
    $html_rows = "";

    if ($oDBase->num_rows() > 0)
    {
        $grupo = "";

        while ($pm = $oDBase->fetch_object())
        {
            if(empty($grupo) || $grupo != $pm->setor)
            {
                $params   = base64_encode($pm->id);
                $collapse = $pm->setor . substr($pm->compet,0,4) . substr($pm->compet,4,2);

                $disabled = (inverteData($pm->dia) > date("Ymd") ? "" : "disabled");
                $html_row = ($html_row != "#f8f8f1" ? "#f8f8f1" : "#e8e8d0");

                $html_rows .= '<tr>';

                if ($pm->qtd > 1 && $grupo != $pm->setor)
                {
                    $html_rows .= '<td class="text-nowrap" style="" title="" title=""';
                    $html_rows .= 'data-toggle="collapse"';
                    $html_rows .= 'data-target="#collapse' . $collapse . '">';
                    $html_rows .= '<a href="#." style="text-decoration:underline;">';
                    $html_rows .= '<span id="collapse' . $collapse . 'span" class="glyphicon glyphicon-plus"></span>';
                    $html_rows .= '</a>&nbsp;&nbsp;&nbsp;' . substr($pm->compet,4,2).'/'.substr($pm->compet,0,4) . '</td>';
                }
                else
                {
                    $html_rows .= '<td>' . substr($pm->compet,4,2).'/'.substr($pm->compet,0,4) . '</td>';
                }

                $html_rows .= '<td>' . getUorgMaisDescricao($pm->setor) . '</td>';
                $html_rows .= '<td>' . databarra($pm->data_registro) . '</td>';
                $html_rows .= '<td style="text-align:top;padding:0px;margin:0px;">';
                $html_rows .= '    <a href="#myModalVisual" role="button" class="btn"';
                $html_rows .= '       data-toggle="modal" ';
                $html_rows .= '       data-load-remote="gestao_liberar_homologacao_visualizar.php?dados=' . $params . '"';
                $html_rows .= '       data-remote-dados="' . $params . '"';
                $html_rows .= '       data-remote-target="#myModalVisual ';
                $html_rows .= '       .modal-body-conteudo">Ver Justificativa</a>';
                $html_rows .= '</td>';
                $html_rows .= '<td>';
                $html_rows .= '    <div class="custom-control custom-checkbox" id="'.$pm->id.'">';

                if (empty($pm->situacao))
                {
                    $html_rows .= '    <input type="checkbox" id="liberar_homologacao" name="liberar_homologacao[]" class="custom-control-input" value="'.$pm->id.'">';
                }
                else
                {
                    $html_rows .= '    <b>Liberado</b>';
                }
                $html_rows .= '    </div>';
                $html_rows .= '</td>';
                $html_rows .= '</tr>';
            }

            if ($pm->qtd > 1 && $grupo != $pm->setor)
            {
                $html_rows .= ImprimirDetalhesSolicitacoesReabertura($pm,$oDBase);
            }

            $grupo = $pm->setor;
        } // fim do while
    }
    else
    {
        $html_rows .= '<tr>';
        $html_rows .= '<td colspan="4">Nenhuma solicitação encontrada</td>';
        $html_rows .= '</tr>';
    }

    return $html_rows;
}


/* @info  Imprimir detalhes solicitacoes reabertura
 *
 * @param  object $rco    Dados detalhes
 * @param  result $oDBase Dados principais
 *
 * @return  HTML
 *
 * @author  Edinalvo Rosa
 */
function ImprimirDetalhesSolicitacoesReabertura($pm, $oDBase)
{
    $collapse = $pm->setor . substr($pm->compet,0,4) . substr($pm->compet,4,2);

    $html_rows  = "";
    $html_rows .= "<tr style='padding:0px;margin:0px;border-collapse: collapse;'>";
    $html_rows .= "<td style='padding:0px;margin:0px;border-collapse:collapse;text-align:right;'></td>";
    $html_rows .= "<td style='padding:0px;margin:0px;border-collapse:collapse;text-align:right;' colspan='5'>";
    $html_rows .= "<table id='collapse" . $collapse . "' class='table table-striped table-bordered text-center collapse out' style='width:100%;margin-top:5px;margin-left:0px;'>";
    $html_rows .= "<thead>";
    $html_rows .= "<tr>";
    $html_rows .= "<th class=\"text-center\">Competência</th>";
    $html_rows .= "<th class=\"text-center\">Lotação</th>";
    $html_rows .= "<th class=\"text-center\">Solicitado Em</th>";
    $html_rows .= "<th class=\"text-center\">Justificativa</th>";
    $html_rows .= "<th class=\"text-center\">Situação</th>";
    $html_rows .= "</tr>";
    $html_rows .= "</thead>";
    $html_rows .= "<tbody>";

    while ($rows = $oDBase->fetch_object())
    {
        $params = base64_encode($rows->id);

        $html_rows .= "<tr>";
        $html_rows .= '<td>' . substr($pm->compet,4,2).'/'.substr($pm->compet,0,4) . '</td>';
        $html_rows .= "<td>" . getUorgMaisDescricao($rows->setor) . "</td>";
        $html_rows .= "<td>" . databarra($rows->data_registro) . "</td>";
        $html_rows .= '<td style="text-align:top;padding:0px;margin:0px;">';
        $html_rows .= '    <a href="#myModalVisual" role="button" class="btn"';
        $html_rows .= '       data-toggle="modal" ';
        $html_rows .= '       data-load-remote="gestao_liberar_homologacao_visualizar.php?dados=' . $params . '"';
        $html_rows .= '       data-remote-dados="' . $params . '"';
        $html_rows .= '       data-remote-target="#myModalVisual ';
        $html_rows .= '       .modal-body-conteudo">Ver Justificativa</a>';
        $html_rows .= '</td>';
        $html_rows .= '<td><b>' . $rows->situacao . '</b></td>';
        $html_rows .= "</tr>";
    }

    $html_rows .= "</tbody>";
    $html_rows .= "</table>";
    $html_rows .= "</td>";
    $html_rows .= "</tr>";

    return $html_rows;
}


/* @info  Seleciona registros de solicitação anterior
 *
 * @param  string  $setor  Unidade solicitante
 * @return  resource  $oDBase  Dados selecionados
 *
 * @author  Edinalvo Rosa
 */
function selecionaSolicitacoesUnidade()
{
    // Competência atual (mês e ano)
    $data = new trata_datasys();
    $ano    = $data->getAnoHomologacao();
    $mes    = $data->getMesHomologacao();
    $compet = $ano . $mes;

    $oDBase = new DataBase();

    $oDBase->query("
    SELECT
        homologacao_dilacao_prazo.id,
        homologacao_dilacao_prazo.compet,
        homologacao_dilacao_prazo.setor,
        homologacao_dilacao_prazo.data_registro,
        homologacao_dilacao_prazo.justificativa,
        IF((ISNULL(homologacao_dilacao_prazo.deliberacao)
            OR homologacao_dilacao_prazo.deliberacao = ''),'','Liberado') AS situacao,
        (SELECT COUNT(*)
            FROM homologacao_dilacao_prazo AS b
                WHERE b.setor = homologacao_dilacao_prazo.setor
                      AND b.compet = '201901') AS qtd
    FROM
        homologacao_dilacao_prazo
    LEFT JOIN
        tabsetor ON homologacao_dilacao_prazo.setor = tabsetor.codigo
    WHERE
        tabsetor.upag = :upag
        AND homologacao_dilacao_prazo.compet = :compet
        ##AND (ISNULL(homologacao_dilacao_prazo.deliberacao)
        ##    OR homologacao_dilacao_prazo.deliberacao = '')
    ORDER BY
        homologacao_dilacao_prazo.compet,
        tabsetor.upag,
        tabsetor.codigo,
        homologacao_dilacao_prazo.data_registro DESC;
    ", array(
        array(":compet", $compet,           PDO::PARAM_STR),
        array(":upag",   $_SESSION['upag'], PDO::PARAM_STR),
    ));

    return $oDBase;
}


/* @info  Janela modal para exibir justificativas
 *
 * @param  void
 * @return  void
 *
 * @author  Edinalvo Rosa
 */
function modalJustificativa()
{
    ?>
    <!-- Aqui o conteúdo será mostrado -->
    <div id="myModalVisual" class="modal fade" role="dialog">
      <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><small>JUSTIFICATIVA</small></h4>
          </div>
          <div class="modal-body-conteudo text-left" style="text-align:justify;">
            <p></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
          </div>
        </div>

      </div>
    </div>
    <?php
}
