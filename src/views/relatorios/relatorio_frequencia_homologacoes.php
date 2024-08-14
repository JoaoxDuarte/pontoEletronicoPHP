<?php

// Inicializa a sessão (session_start)
// funcoes de uso geral
include_once( "config.php" );
include_once( "src/controllers/RelatorioFrequenciaHomologacoesController.php" );


// instancia classe(s)
$relatorioFrequenciaHomologacoesController = new RelatorioFrequenciaHomologacoesController();

//definindo a competencia de homologacao
$oData = new trata_datasys();
$mes   = $oData->getMesAnterior();
$ano   = $oData->getAnoAnterior();

if ( $_SERVER['REQUEST_METHOD']=='POST' )
{
    $compet      = getPost('competencias_opcoes');
    $mes         = substr($compet,-2);
    $ano         = substr($compet,0,4);
    $unidade     = strtr(getPost('unidades_opcoes'),array('#'=>''));
    $servidor    = getPost('servidor');
    $homologador = getPost('homologador');
    $homologados = getPost('homologados');
}
else
{
    $compet      = $ano . $mes;
    $unidade     = $_SESSION['sLotacao'];
    $servidor    = "";
    $homologador = "";
    $homologados = "";
}

$situacoes_indicadas  = 0;
$situacoes_indicadas += (empty($compet) ? 0 : 1);
$situacoes_indicadas += (empty($unidade) ? 0 : 1);
$situacoes_indicadas += (empty($servidor) ? 0 : 1);
$situacoes_indicadas += (empty($homologador) ? 0 : 1);
$situacoes_indicadas += (empty($homologados) ? 0 : 1);

$upag  = $_SESSION['upag'];

// dados voltar
$_SESSION['voltar_nivel_1'] = 'relatorio_frequencia_nao_homologados.php';
$_SESSION['voltar_nivel_2'] = '';
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';

// seleciona descricao do setor
$wnomelota = getUorgDescricao( $upag );

$sql    = "";
$params = array();


// seleciona os servidores com frequencia homologada
$dados = new stdClass();
$dados->compet      = $compet;
$dados->mes         = $mes;
$dados->ano         = $ano;
$dados->unidade     = $unidade;
$dados->servidor    = $servidor;
$dados->homologador = $homologador;
$dados->homologados = $homologados;
$dados->upag        = $upag;

$oDBase = DadosDoServidor( $dados );
$num    = $oDBase->num_rows();


if ($num == 0 && $situacoes_indicadas > 1)
{
    $mensagem = "Não foram localizados registros com as condições indicadas!";
}
else if ($num == 0 && $situacoes_indicadas == 1)
{
    $mensagem = "Não foram localizados registros com a condição indicada!";
}
else if ($num == 0)
{
    $mensagem = "Sem registros para exibir!";
}


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setJSSelect2();
$oForm->setJSPDF();
$oForm->setJS('js/jquery.mask.min.js');
$oForm->setJS( "src/views/relatorios/relatorio_frequencia_homologacoes.js?v.0.0.0.0.0.37" );
$oForm->setIconeParaImpressao('print');
$oForm->setSubTitulo("Consulta Homologação de Frequência(s)");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

?>
<div class="container">

    <?php exibeDescricaoOrgaoUorg($upag); // Exibe Órgão e Uorg com suas descrições padrão: $_SESSION['sLotacao']; ?>

    <div class="row margin-25">
        <form id='form1' name='form1' method='POST' onsubmit="javascript:return false;">

            <div class="row margin-bottom-10">
                <div class="col-md-3 text-left" style="margin-top: 5px;">
                    <p><b>Homologados: </b></p>
                </div>
                <div class="col-md-3 text-left">
                    <select class="form-control select2-single" id="homologados" name="homologados" tabindex="-1" aria-hidden="true">
                        <option value=""  <?= ($homologados === ""  ? "selected" : ""); ?>>Todas as opções</option>
                        <option value="N" <?= ($homologados === "N" ? "selected" : ""); ?>>NÃO</option>
                        <option value="S" <?= ($homologados === "S" ? "selected" : ""); ?>>SIM</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 text-left" style="margin-top: 5px;">
                    <p><b>Mês/Ano: </b></p>
                </div>
                <div class="col-md-3 text-left">
                    <?php $relatorioFrequenciaHomologacoesController->CarregaSelectCompetencia($ano, $mes); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 text-left" style="margin-top: 5px;">
                    <p><b>Unidade: </b></p>
                </div>
                <div class="col-md-8 text-left">
                    <?php $relatorioFrequenciaHomologacoesController->CarregaSelectUnidades($ano, $mes, $upag, $unidade); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 text-left" style="margin-top: 5px;">
                    <p><b>Servidor - Nome/Matrícula: </b></p>
                </div>
                <div class="col-md-8 text-left">
                    <input type="text" id="servidor" name="servidor" value="<?= tratarHTML($servidor); ?>" class="form-control">
                    <small style="font-size:9px;vertical-align:top;margin-top:0px;padding-left:3px;padding-top:0px;">(Nome do servidor ou matrícula ou parte do nome ou matrícula)</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 text-left" style="margin-top: 5px;">
                    <p><b>Homologador - Nome/Matrícula: </b></p>
                </div>
                <div class="col-md-8 text-left">
                    <input type="text" id="homologador" name="homologador" value="<?= tratarHTML($homologador); ?>" class="form-control">
                    <small style="font-size:9px;vertical-align:top;margin-top:0px;padding-left:3px;padding-top:0px;">(Nome do servidor ou matrícula ou parte do nome ou matrícula)</small>
                </div>
            </div>

            <div class="form-group margin-30">
                <div class="col-md-8 margin-bottom-30">
                    <div class="col-md-3 col-md-offset-4">
                        <button type="submit" id="btn-continuar" class="btn btn-success btn-block">
                            <span class="glyphicon glyphicon-ok"></span> Executar
                        </button>
                    </div>
                </div>
            </div>
        </form>
        <?php

        if ($mes)
        {
            ?>
            <table class="table table-striped table-bordered text-center table-hover">
                <thead>
                    <tr>
                        <th class="text-center" colspan="5" id="compentencia_selecionada"><h4><b><?= tratarHTML($mes) . '/' . tratarHTML($ano); ?></b></h4></th>
                    </tr>
                </thead>
            </table>
            <?php
        }

        if ($num == 0)
        {
            ?>
            <table class="table table-striped table-bordered text-center table-hover">
                <tbody id='registros_selecionados' class='sse_listar'>
                    <tr>
                        <td colspan='4'>
                            <font face='verdana' size='2'><?= tratarHTML($mensagem); ?></font>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php
        }

        if ($num > 0)
        {
            $dadosServidores = array();
            $grupo_setor     = "";
            $unico_setor     = true;

            while ($pm = $oDBase->fetch_object())
            {
                if ($grupo_setor != "" && $grupo_setor != $pm->cod_lot)
                {
                    ServidoresPorUnidade($dadosServidores,$dados->mes,$dados->ano);
                    $dadosServidores = array();
                    $unico_setor     = false;
                }
                $grupo_setor = $pm->cod_lot;

                $dadosServidores[] = array(
                    'siape'      => $pm->mat_siape,
                    'nome'       => $pm->nome_serv,
                    'cod_lot'    => $pm->cod_lot,
                    'descricao'  => $pm->descricao,
                    'jornada'    => $pm->jornada,
                    'homologado' => $pm->homologado
                );
            }

            if ($unico_setor == true)
            {
                ServidoresPorUnidade($dadosServidores,$dados->mes,$dados->ano);
            }
        }

        ?>
    </div>

</div>

<!-- Aqui o conteúdo será mostrado -->
<div class="modal fade" id="myModalVisual" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-body modal-dialog modal-lg modal-content text-left" style='width:1100px;'>
        <div class="modal-header text-right navbar-fixed-top" style='z-index:90000;'>
            <!-- <div class="btn-group" role="group"> -->
                <button class="btn btn-success text-left" id="btnPrint">
                    <span class="glyphicon glyphicon-print"></span> Imprimir
                </button>
            <!-- </div> -->
            <button class="btn btn-default" data-dismiss="modal" aria-hidden="true">Fechar</button>
        </div>
        <div class="modal-body-conteudo text-left">…</div>
        <div class="modal-footer navbar-fixed-bottom text-right">
            <!-- <div class="btn-group" role="group"> -->
                <button class="btn btn-success text-left" id="btnPrint2">
                    <span class="glyphicon glyphicon-print"></span> Imprimir
                </button>
            <!-- </div> -->
            <button class="btn btn-default" data-dismiss="modal" aria-hidden="true">Fechar</button>
        </div>
    </div>
</div>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();


/* ************************************************ *
 *                                                  *
 *              FUNÇÕES COMPLEMENTARES              *
 *                                                  *
 * ************************************************ */


/*
 * @param string $ano  Ano da competência da homologação
 * @param string $mes  Mês da competência da homologação
 * @param string $upag UPAG da unidade do servidor/estagiário
 *
 * @info Lista as unidades e servidores para verificar se foram homologados
 */
function DadosDoServidor( $dados )
{
    global $sql, $params;

    $oDBase = new DataBase('PDO');

    if (is_object($dados))
    {
        $params = array();
        $params[] = array( ":upag", $dados->upag, PDO::PARAM_STR );

        $sql = "
        SELECT
                homologados.compet,
                servativ.mat_siape,
                servativ.nome_serv,
                servativ.cod_lot,
                servativ.jornada,
                servativ.sigregjur,
                servativ.dt_adm,
                servativ.oco_exclu_dt,
                tabsetor.upag,
                tabsetor.descricao,
                homologados.homologado_siape,
                servativ2.nome_serv AS homologado_nome,
                homologados.homologado_data,
                homologados.desomologado_motivo,
                homologados.desomologado_siape,
                homologados.desomologado_data,
                IF(IFNULL(homologados.homologado,'N')='N' OR homologados.homologado NOT IN ('V','S'),'N','S') AS homologado
        FROM
                servativ
        LEFT JOIN
                homologados ON servativ.mat_siape = homologados.mat_siape
                                AND ((homologados.compet >= DATE_FORMAT(servativ.dt_adm,'%Y%m')) AND (homologados.compet <= IF(servativ.oco_exclu_dt='0000-00-00','999999',DATE_FORMAT(servativ.oco_exclu_dt,'%Y%m'))))
                                " . (empty($dados->compet) ? "" : " AND homologados.compet = '".$dados->compet."'") . "
        LEFT JOIN
                tabsetor ON servativ.cod_lot = tabsetor.codigo
        LEFT JOIN
                servativ AS servativ2 ON homologados.homologado_siape = servativ2.mat_siape
        ";

        $where = Array();
        $where[] = " servativ.excluido = 'N'";
        $where[] = " servativ.cod_sitcad NOT IN ('02','15')";
        $where[] = " tabsetor.upag = :upag";
        //$where[] = " ((homologados.compet >= DATE_FORMAT(servativ.dt_adm,'%Y%m')) AND (homologados.compet <= IF(servativ.oco_exclu_dt='0000-00-00','999999',DATE_FORMAT(servativ.oco_exclu_dt,'%Y%m'))))";


        $order_by = Array();


        // WHERE (FILTRO)
        // filtro competência
        if( empty($dados->compet) )
        {
            //$params[] = array( ":compet", "", PDO::PARAM_STR );
        }
        else
        {
            //$where[]  = " DATE_FORMAT(servativ.dt_adm,'%Y%m') <= :compet";
            //$where[]  = " homologados.compet = :compet";

            //$params[] = array( ":compet", $dados->compet, PDO::PARAM_STR );
        }

        // filtro unidade
        if( !empty($dados->unidade) )
        {
            $where[] = " (CONCAT(servativ.cod_lot,' ',tabsetor.descricao) LIKE '%{$dados->unidade}%')";
            $order_by[] = " servativ.cod_lot";
        }

        // filtro servidor homologado - matrícula ou nome - pode ser parte do nome ou matrícula
        if( !empty($dados->servidor) )
        {
            $where[] = " (CONCAT(servativ.mat_siape,' ',servativ.nome_serv) LIKE '%{$dados->servidor}%')";
        }

        // filtro homologador - matrícula ou nome - pode ser parte do nome ou matrícula
        if( !empty($dados->homologador) )
        {
            $where[] = " (CONCAT(homologados.homologado_siape,' ',servativ2.nome_serv) LIKE '%{$dados->homologador}%')";
        }

        // filtro homologados ou não
        if( !empty($dados->homologados) )
        {
            $where[]  = " (IFNULL(homologados.homologado,'N') = :homologados)";
            $params[] = array( ":homologados", $dados->homologados, PDO::PARAM_STR );
        }

        if( sizeof( $where ) )
        {
            $sql .= " WHERE " . implode( " AND ", $where ) . "";
        }

        // GROUP BY
        $sql .= " GROUP BY servativ.mat_siape ";


        // ORDER BY
        //$order_by[] = " IF(IFNULL(homologados.homologado,'N')='N' OR homologados.homologado NOT IN ('V','S'),1,2)";
        $order_by[] = " servativ.nome_serv";

        if( sizeof( $order_by ) )
        {
            $sql .= " ORDER BY " . implode( ", ", $order_by ) . "";
        }

        // instancia o banco de dados
        $oDBase->query( $sql, $params );
    }

    return $oDBase;
}


/**
 * @info Lista servidores, por unidade, para verificar se foram homologados
 *
 * @param array $dados  Dados dos servidores selecionados
 * @param string $mes
 * @param string $ano
 */
function ServidoresPorUnidade($dados,$mes,$ano)
{
    global $relatorioFrequenciaHomologacoesController;

    $compet = (empty($ano) || empty($mes) ? null : $ano . $mes);

    ?>
    <div id="<?= $dados[0]['cod_lot']; ?>" name='unidades'>
        <table class="table table-striped table-bordered text-center table-hover margin-25">
            <thead>
                <tr style="border:1px solid white;padding:0px;margin:0px;">
                    <td colspan="4" style="border:0px solid white;padding:0px;margin:0px;">
                            <div class="col-md-8 text-left" id="total_de_registros" style="padding:0px;margin:0px;">
                                <p style="padding:0px;margin:0px;vertical-align:bottom;"><b>Unidade: <?= tratarHTML(getUorgMaisDescricao($dados[0]['cod_lot'])); ?></b></p>
                            </div>
                            <div class="col-md-4 text-right" id="total_de_registros" style="vertical-align:bottom;">
                                Total de <?= number_format( count($dados), 0, ',', '.' ); ?> registros.
                            </div>
                    </td>
                </tr>
                <tr>
                    <th class="text-center" style="width: 12%;">UNIDADE</th>
                    <th class="text-center" style="width: 14%;">MATR&Iacute;CULA</th>
                    <th class="text-left"   style="width: 60%;">NOME</th>
                    <th class="text-center" style="width: 14%;">SITUA&Ccedil;&Atilde;O</th>
                </tr>
            </thead>
            <tbody id='registros_selecionados' class='sse_listar'>
                <?php

                for ($x=0; $x < count($dados); $x++)
                {
                    $status = $relatorioFrequenciaHomologacoesController->SituacaoHomologacaoPorMatricula( $dados[$x]['siape'], $dados[$x]['dt_adm'], $dados[$x]['oco_exclu_dt'], $compet );

                    if ($status === 'HOMOLOGADO')
                    {
                        $style = "style='color:blue;font-weith:bold;text-decoration:none;'";
                    }
                    else
                    {
                        $style = "style='color:red;font-weith:bold;text-decoration:none;'";
                    }

                    ?>
                    <tr>
                        <td class="text-center" style="width: 12%;vertical-align:middle;" ><?= tratarHTML(removeOrgaoLotacao($dados[$x]['cod_lot'])); ?></td>
                        <td class="text-center" style="width: 14%;vertical-align:middle;"><?= tratarHTML(removeOrgaoMatricula($dados[$x]['siape'])); ?></td>
                        <td class="text-left"   style="width: 60%;vertical-align:middle;"><?= tratarHTML($dados[$x]['nome']); ?></td>
                        <td class="text-center" style="width: 14%;vertical-align:middle;" nowrap>
                            <div class='imprimir_texto_link' style='display:none;'><a <?= $style; ?>> <?= $status; ?></a></div>
                            <a href="#myModalVisual" role="button" class="btn no_print_link" data-toggle="modal" data-load-remote="veponto_saldos.php" data-remote-dados='tipo=1&pSiape=<?= $dados[$x]['siape']; ?>&extrato=sim' data-remote-target="#myModalVisual .modal-body-conteudo" <?= $style; ?>><!-- <span class="glyphicon glyphicon-eye-open" alt="Visualizar Extrato" title="Visualizar Extrato"></span> --><?= $status; ?></a>
                        </td>
                    </tr>
                    <?php
                }

                ?>
            </tbody>
        </table>
    </div>
    <?php
}
