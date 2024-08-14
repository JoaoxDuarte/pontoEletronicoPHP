<?php

// Inicializa a sessão (session_start)
// funcoes de uso geral
include_once( "config.php" );

// Verifica se o usuário tem a permissão para acessar este módulo
verifica_permissao("sRH");


if (!empty($_GET['importafastamentos']))
{
    $result = updateAfastamentosBySiape($_GET['siape']);
    echo json_encode(array("success" => $result));
    die;
}


//definindo a competencia de homologacao
$mes   = date('m');
$ano   = date('Y');

if (isset($_POST['competencias_opcoes']) && !empty($_POST['competencias_opcoes']))
{
    $mes   = substr($_POST['competencias_opcoes'],-2);
    $ano   = substr($_POST['competencias_opcoes'],0,4);
    $setor = strtr($_POST['unidades_opcoes'],array('#'=>''));
}
else
{
    $setor  = $_SESSION['sLotacao'];
}

$upag  = $_SESSION['upag'];

// dados voltar
$_SESSION['voltar_nivel_1'] = 'frequencia_rh_mes_corrente.php';
$_SESSION['voltar_nivel_2'] = '';
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';

// seleciona descricao do setor
$wnomelota = getUorgDescricao($upag);

// seleciona os servidores com frequencia homologada
$oDBase = DadosDoServidor($ano, $mes, $upag);
$num    = $oDBase->num_rows();

$totalSetores = totalSetoresPorOrgao($upag);


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setJSSelect2();
$oForm->setJSPDF();
$oForm->setJS('js/jquery.mask.min.js');
$oForm->setJS( "frequencia_rh_mes_corrente.js" );
$oForm->setIconeParaImpressao('print');
$oForm->setSubTitulo("Manutenção Frequência - Mês Corrente");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML($width='1300px;');

?>
<div class="container">

    <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

    <?php exibeDescricaoOrgaoUorg($upag); // Exibe Órgão e Uorg com suas descrições padrão: $_SESSION['sLotacao']; ?>

    <div class="row margin-25">
        <form id='form1' name='form1' method='POST' onsubmit="javascript:return false;">
            <input type="hidden" id="dados" name="dados" value="">

            <div class="row">
                <div class="col-md-2 text-left" style="margin-top: 5px;">
                    <p><b>Mês/Ano: </b></p>
                </div>
                <div class="col-md-2 text-left">
                    <?php CarregaSelectCompetenciaFixa($ano, $mes); ?>
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
            <?php

            if ($num === 0)
            {
                ?>
                <tbody id='registros_selecionados' class='sse_listar'>
                    <tr>
                        <td colspan='4'>
                            <font face='verdana' size='2'>Não há servidores registrados!</font>
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
                if (($grupo_setor != "" && $grupo_setor != $pm->cod_lot) || $totalSetores == 1)
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
                <button class="btn btn-success" id="btnRejeitar">
                    <span class="glyphicon glyphicon-envelope"></span> Rejeitar
                </button>&nbsp;&nbsp;
            <!-- </div> -->
            <button class="btn btn-default" data-dismiss="modal" aria-hidden="true">Fechar</button>
        </div>
        <div class="modal-body-conteudo text-left">…</div>
        <div class="modal-footer navbar-fixed-bottom text-right">
            <button class="btn btn-success">
                <span class="glyphicon glyphicon-envelope"></span> Rejeitar
            </button>
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
        servativ.cod_lot,
        tabsetor.descricao,
        tabsetor.cod_uorg_pai,
        tabsetor.uorg_pai,
        SUM(IF(IFNULL(homologados.homologado,'N') NOT IN ('V','S'),1,0)) AS homologados_nao,
        SUM(IF(IFNULL(homologados.homologado,'N') IN ('V','S'),1,0))     AS homologados_sim,
        SUM(IF(IFNULL(homologados.homologado,'N')='V',1,0))              AS homologados_visto,
        COUNT(*) AS total
    FROM
        servativ
    LEFT JOIN
        tabsetor ON servativ.cod_lot = tabsetor.codigo
    LEFT JOIN
        homologados ON (servativ.mat_siape = homologados.mat_siape) AND (homologados.compet = :compet)
    WHERE
        servativ.excluido = 'N'
        AND servativ.mat_siape <> :usuario
        AND servativ.cod_sitcad NOT IN ('02','15','08')
        AND tabsetor.upag = :upag
        AND DATE_FORMAT(servativ.dt_adm,'%Y%m') <= :compet
    GROUP BY
        servativ.cod_lot
    ";

    // instancia o banco de dados
    $oDBase = new DataBase('PDO');
    $oDBase->query(
        $sql,
        array(
            array( ":compet",  $compet, PDO::PARAM_STR ),
            array( ":upag",    $upag,   PDO::PARAM_STR ),
            array( ":usuario", $_SESSION['sMatricula'],   PDO::PARAM_STR ),
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
        IF(IFNULL(homologados.homologado,'N') NOT IN ('V','S'),'N','S') AS `status`
    FROM
        servativ
    LEFT JOIN
        tabsetor AS und ON servativ.cod_lot = und.codigo
    LEFT JOIN
        homologados ON (servativ.mat_siape = homologados.mat_siape) AND (homologados.compet = :compet)
    WHERE
        servativ.excluido = 'N'
        AND servativ.mat_siape <> :usuario
        AND servativ.cod_sitcad NOT IN ('02','15','08')
        AND und.upag = :upag
        AND DATE_FORMAT(servativ.dt_adm,'%Y%m') <= :compet
    ORDER BY
        servativ.cod_lot,
        IF(IFNULL(homologados.homologado,'N') NOT IN ('V','S'),1,
            IF(IFNULL(homologados.homologado,'N')='S',2,3)),
        servativ.nome_serv
    ";

    // instancia o banco de dados
    $oDBase = new DataBase('PDO');
    $oDBase->query(
        $sql,
        array(
            array( ":compet", $compet, PDO::PARAM_STR ),
            array( ":upag",   $upag,   PDO::PARAM_STR ),
            array( ":usuario", $_SESSION['sMatricula'],   PDO::PARAM_STR ),
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
            <tbody id='registros_selecionados'>
                <?php

                for ($x=0; $x < count($dados); $x++)
                {
                    $params = base64_encode(
                        tratarHTML($dados[$x]['siape']) . ":|:" .
                        tratarHTML($dados[$x]['cod_lot']) . ":|:" .
                        tratarHTML($dados[$x]['jornada']) . ":|:" .
                        tratarHTML($mes) . ":|:" .
                        tratarHTML($ano)
                    );

                    ?>
                    <tr>
                        <td class="text-center largura-imp-124px" style="width: 12%;" ><?= tratarHTML(removeOrgaoLotacao($dados[$x]['cod_lot'])); ?></td>
                        <td class="text-center largura-imp-145px" style="width: 14%;"><?= tratarHTML(removeOrgaoMatricula($dados[$x]['siape'])); ?></td>
                        <td class="text-left largura-imp-600px"   style="width: 60%;"><?= tratarHTML($dados[$x]['nome']); ?></td>
                        <td class="text-center largura-imp-150px" style="width: 14%;">

                            <a href="javascript:window.location.replace('frequencia_rh_mes_corrente_registros.php?dados=<?= $params; ?>')" style="text-decoration:none;cursor:pointer;">Manutenção</a>

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


/*
 * @param void
 *
 * @info Lista mes e ano
 */
function CarregaSelectCompetenciaFixa($ano=NULL, $mes=NULL)
{
    $mes    = (is_null($mes) ? date('m') : $mes);
    $ano    = (is_null($ano) ? date('Y') : $ano);
    $compet = $mes . '/' . $ano;

    ?>
    <input type="hidden" id="competencias_opcoes" name="competencias_opcoes" value="<?= substr($opcao,-4).substr($opcao,0,2); ?>">
    <select class="form-control select2-single" disabled>
        <option value='<?= tratarHTML($ano.$mes); ?>' selected><?= tratarHTML($compet); ?></option>
    </select>
    <?php
}
