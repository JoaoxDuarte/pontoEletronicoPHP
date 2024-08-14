<?php
// Inicializa a sessão (session_start)
// funcoes de uso geral
include_once( "config.php" );

// Verifica se o usuário tem a permissão para acessar este módulo
verifica_permissao("sRH");

//definindo a competencia de homologacao
$oData = new trata_datasys();
$mes   = $oData->getMesAnterior();
$ano   = $oData->getAnoAnterior();
$comp  = $oData->getCompetHomologacao(); // mes e ano, ex.: 032010

if (isset($_POST['competencias_opcoes']) && !empty($_POST['competencias_opcoes']))
{
    $mes   = substr($_POST['competencias_opcoes'],-2);
    $ano   = substr($_POST['competencias_opcoes'],0,4);
    $setor = strtr($_POST['unidades_opcoes'],array('#'=>''));
}
else
{
    $setor  = $_SESSION['sLotacao'];
    $mes = substr($comp, 0, 2);
    $ano = substr($comp, -4);
}

$upag  = $_SESSION['upag'];

// dados voltar
$_SESSION['voltar_nivel_1'] = 'frequencia_verificar_homologados.php';
$_SESSION['voltar_nivel_2'] = '';
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';

// seleciona descricao do setor
$wnomelota = getUorgDescricao($upag);

// seleciona os servidores com frequencia homologada
$oDBase = DadosDoServidor($ano, $mes, $upag);
$num    = $oDBase->num_rows();


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setJSSelect2();
$oForm->setJSPDF();
$oForm->setJS('js/jquery.mask.min.js');
$oForm->setJS( "frequencia_verificar_homologados.js?v.0.0.0.0.0.2" );
$oForm->setIconeParaImpressao('print');
$oForm->setSubTitulo("Verificar Homologações");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

?>
<div class="container">

    <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

    <?php exibeDescricaoOrgaoUorg($upag); // Exibe Órgão e Uorg com suas descrições padrão: $_SESSION['sLotacao']; ?>

    <div class="row margin-25">
        <form id='form1' name='form1' method='POST' onsubmit="javascript:return false;">
            <div class="row">
                <div class="col-md-2 text-left" style="margin-top: 5px;">
                    <p><b>Mês/Ano: </b></p>
                </div>
                <div class="col-md-2 text-left">
                    <?php CarregaSelectCompetencia($ano, $mes); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2 text-left" style="margin-top: 5px;">
                    <p><b>Selecione a Unidade: </b></p>
                </div>
                <div class="col-md-8 text-left">
                    <?php CarregaSelectUnidades($ano, $mes, $upag, $setor); ?>
                </div>
            </div>
        </form>

        <table class="table table-striped table-bordered text-center table-hover">
            <thead>
                <tr>
                    <th class="text-center" colspan="5" id="compentencia_selecionada"><h4><b><?= tratarHTML($mes) . '/' . tratarHTML($ano); ?></b></h4></th>
                </tr>
            </thead>
            <?php

            if ($num === 0)
            {
                ?>
                <tbody id='registros_selecionados' class='sse_listar'>
                    <tr>
                        <td colspan='4'>
                            <font face='verdana' size='2'>Não há servidores para verificação!</font>
                        </td>
                    </tr>
                </tbody>
                <?php
            }

            ?>
        </table>
        <?php

        if ($num > 0)
        {
            $dados       = array();
            $grupo_setor = "";

            while ($pm = $oDBase->fetch_object())
            {
                if ($grupo_setor != "" && $grupo_setor != $pm->cod_lot)
                {
                    ServidoresPorUnidade($dados,$mes,$ano);
                    $dados = array();
                }
                $grupo_setor = $pm->cod_lot;

                $dados[] = array(
                    'siape'     => $pm->mat_siape,
                    'nome'      => $pm->nome_serv,
                    'cod_lot'   => $pm->cod_lot,
                    'descricao' => $pm->descricao,
                    'status'    => $pm->status,
                    'jornada'   => $pm->jornada,
                );
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
                
                <?php if($comp == $mes.$ano): ?>
                    <button class="btn btn-success" id="btnRejeitar">
                        <span class="glyphicon glyphicon-envelope"></span> Rejeitar
                    </button>&nbsp;&nbsp;
                <?php endif; ?>
                    
            <!-- </div> -->
            <button class="btn btn-default" data-dismiss="modal" aria-hidden="true">Fechar</button>
        </div>
        <div class="modal-body-conteudo text-left">…</div>
        <div class="modal-footer navbar-fixed-bottom text-right">
                
            <?php if($comp == $mes.$ano): ?>
                <button class="btn btn-success">
                    <span class="glyphicon glyphicon-envelope"></span> Rejeitar
                </button>
            <?php endif; ?>
                    
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
 * @info Total de servidores/estagiarios por UPAG
 */
function UnidadesTotalDeServidores($ano, $mes, $upag)
{
    $compet = $ano . $mes;

    $sql = "
    SELECT
        cad.cod_lot, und.descricao, und.cod_uorg_pai, und.uorg_pai,
        SUM(IF(IFNULL(homologados.homologado,'N') NOT IN ('V','S'),1,0)) AS homologados_nao,
        SUM(IF(IFNULL(homologados.homologado,'N') IN ('V','S'),1,0)) AS homologados_sim,
        SUM(IF(IFNULL(homologados.homologado,'N')='V',1,0)) AS homologados_visto,
        COUNT(*) AS total
    FROM
        servativ AS cad
    LEFT JOIN
        tabsetor AS und ON cad.cod_lot = und.codigo
    LEFT JOIN
        homologados ON (cad.mat_siape = homologados.mat_siape) 
                       AND (homologados.compet = :compet)
    WHERE
        cad.excluido = 'N'
        AND cad.cod_sitcad NOT IN ('02','15','08')
        AND und.upag = :upag
        AND DATE_FORMAT(cad.dt_adm,'%Y%m') <= :compet
    GROUP BY
        cad.cod_lot
    ";

    // instancia o banco de dados
    $oDBase = new DataBase('PDO');
    $oDBase->query(
        $sql,
        array(
            array( ":compet", $compet, PDO::PARAM_STR ),
            array( ":upag",   $upag,   PDO::PARAM_STR ),
        )
    );

    return $oDBase;
}


/*
 * @param string $ano  Ano da competência da homologação
 * @param string $mes  Mês da competência da homologação
 * @param string $upag UPAG da unidade do servidor/estagiário
 *
 * @info Lista as unidades e servidores para verificar se foram homologados
 */
function DadosDoServidor($ano, $mes, $upag)
{
    $compet = $ano . $mes;

    $sql = "
    SELECT
        servativ.mat_siape,
        servativ.nome_serv,
        servativ.jornada,
        servativ.cod_lot,
        und.descricao,
        IF(IFNULL(hom.homologado,'N')='N' OR hom.homologado NOT IN ('V','S'),'N','S') AS `status`
    FROM
        servativ
    LEFT JOIN
        tabsetor AS und ON servativ.cod_lot = und.codigo
    LEFT JOIN
        homologados AS hom ON (servativ.mat_siape = hom.mat_siape) AND (hom.compet = :compet)
    WHERE
        servativ.excluido = 'N'
        AND servativ.cod_sitcad NOT IN ('02','15','08')
        AND und.upag = :upag
        AND DATE_FORMAT(servativ.dt_adm,'%Y%m') <= :compet
    ORDER BY
        servativ.cod_lot,
        IF(IFNULL(hom.homologado,'N')='N',1,
            IF(IFNULL(hom.homologado,'N')='S',2,3)),
        servativ.nome_serv
    ";

    // instancia o banco de dados
    $oDBase = new DataBase('PDO');
    $oDBase->query(
        $sql,
        array(
            array( ":compet", $compet, PDO::PARAM_STR ),
            array( ":upag",   $upag,   PDO::PARAM_STR ),
        )
    );

    return $oDBase;
}


/*
 * @param string $ano  Ano da competência da homologação
 * @param string $mes  Mês da competência da homologação
 * @param string $upag UPAG da unidade do servidor/estagiário
 *
 * @info Lista as unidades
 */
function CarregaSelectUnidades($ano, $mes, $upag, $setor=NULL)
{
    $oDBase = UnidadesTotalDeServidores($ano, $mes, $upag);

    ?>
    <select class="form-control select2-single" id='unidades_opcoes' name='unidades_opcoes'>
        <option value=''>Selecione uma unidade</option>
        <option value='todos'>TODAS AS UNIDADES</option>
        <?php

        $num_unidades = $oDBase->num_rows();

        while ($pm = $oDBase->fetch_object())
        {
            $selected = (is_null($setor) ? false : ($pm->cod_lot == $setor));

            ?>
            <option value='#<?= tratarHTML($pm->cod_lot); ?>' <?= ($selected ? 'selected' : ''); ?>>
                <?= tratarHTML(getUorgMaisDescricao($pm->cod_lot)); ?>
            </option>
            <?php
        }

        ?>
    </select>
    <small style="font-size:9px;vertical-align:top;margin-top:0px;padding-left:3px;padding-top:0px;">(<?= number_format($num_unidades,0,',','.'); ?> unidades)</small>
    <?php
}

/*
 * @param array $dados  Dados dos servidores selecionados
 *
 * @info Lista servidores, por unidade, para verificar se foram homologados
 */
function ServidoresPorUnidade($dados,$mes,$ano)
{
    ?>
    <div id="<?= $dados[0]['cod_lot']; ?>" name='unidades' style="display:none;">
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
                    <th class="text-center" style="width: 14%;">A&Ccedil;&Atilde;O</th>
                </tr>
            </thead>
            <tbody id='registros_selecionados' class='sse_listar'>
                <?php

                for ($x=0; $x < count($dados); $x++)
                {
                    ?>
                    <tr>
                        <td class="text-center largura-imp-124px" style="width: 12%;" ><?= tratarHTML(removeOrgaoLotacao($dados[$x]['cod_lot'])); ?></td>
                        <td class="text-center largura-imp-145px" style="width: 14%;"><?= tratarHTML(removeOrgaoMatricula($dados[$x]['siape'])); ?></td>
                        <td class="text-left largura-imp-600px"   style="width: 60%;"><?= tratarHTML($dados[$x]['nome']); ?></td>
                        <td class="text-center largura-imp-140px" style="width: 14%;">
                            <?php

                                switch ($dados[$x]['status'])
                                {
                                    case 'S':
                                        $params = base64_encode(
                                            tratarHTML($dados[$x]['siape']) . ":|:" .
                                            tratarHTML($dados[$x]['cod_lot']) . ":|:" .
                                            tratarHTML($dados[$x]['jornada']) . ":|:" .
                                            tratarHTML($mes) . ":|:" .
                                            tratarHTML($ano)
                                        );

                                        ?>
                                        <a href="#myModalVisual" role="button" class="btn" data-toggle="modal" data-load-remote="frequencia_verificar_homologados_visualizar.php?dados=<?= $params; ?>" data-remote-dados='<?= $params; ?>' data-remote-target="#myModalVisual .modal-body-conteudo">Homologado (Verificar)</a>
                                        <!--
                                        <span class="btn btn-primary" data-toggle="modal" data-target="#appModal" data-remote="frequencia_verificar_homologados_visualizar.php?dados=<?= $params; ?>">Homologado (Verificar)</span>
                                        -->
                                        <?php
                                        break;

                                    case 'V':
                                        ?>
                                        <font style="color:blue;">Concluído</font>
                                        <?php
                                        break;

                                    case 'N':
                                    default:
                                        ?>
                                        <font style="color:red;font-weight:bold">Não Homologado</font>
                                        <?php
                                        break;
                                }
                            ?>
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
