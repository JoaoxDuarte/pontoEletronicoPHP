<?php
/**
 * +-------------------------------------------------------------+
 * | @description : Autorização de Trabalho em Dia Não Útil      |
 * |                                                             |
 * | @author  : Carlos Augusto                                   |
 * | @version : Edinalvo Rosa                                    |
 * +-------------------------------------------------------------+
 * */
// Inicia a sessão e carrega as funções de uso geral
include_once( "config.php" );

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao("sAPS");

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    //header("Location: acessonegado.php");
}
else
{
    $dados    = explode(":|:", base64_decode($dadosorigem));
    $qlotacao = $dados[0];
}

$qlotacao = (empty($qlotacao) ? $_SESSION['sLotacao'] : $qlotacao);

$sMatricula = $_SESSION['sMatricula'];

$vDatas = date("Y-m-d");
$dia    = date("d/m/Y");
$ano    = date('Y');
$comp   = date('nY');

// instancia o banco de dados
$oDBase = new DataBase('PDO');

// pesquisa
$oDBase->query("
SELECT
    und.descricao, taborgao.denominacao, taborgao.sigla
FROM
    tabsetor AS und
LEFT JOIN
    taborgao ON LEFT(und.codigo,5) = taborgao.codigo
WHERE
    und.codigo = :lotacao
ORDER BY
    und.codigo
", array(
    array(':lotacao', $qlotacao, PDO::PARAM_STR),
));
$oSetor            = $oDBase->fetch_object();
$lotacao           = $qlotacao;
$lotacao_descricao = $oSetor->descricao;
$orgao_sigla       = $oSetor->sigla;


// pesquisa
$oDBase->query("
SELECT
    cad.mat_siape, cad.nome_serv, cad.cod_lot, cad.excluido, cad.chefia,
    cad.cod_sitcad, cad.email, und.uorg_pai,
    DATE_FORMAT(dnu.dia, '%d/%m/%Y') AS dt, dnu.dia, dnu.autorizado,
    und.descricao, taborgao.denominacao, taborgao.sigla
FROM
    tabdnu AS dnu
LEFT JOIN
    servativ AS cad ON dnu.siape = cad.mat_siape
LEFT JOIN
    tabsetor AS und ON cad.cod_lot = und.codigo
LEFT JOIN
    taborgao ON LEFT(und.codigo,5) = taborgao.codigo
WHERE
    cad.mat_siape != :siape
    AND cad.excluido = 'N'
    AND dnu.dia >= :data
    AND cad.cod_sitcad NOT IN ('02','15')
    AND ((cad.chefia = 'N' AND cad.cod_lot LIKE :lotacao) OR (cad.chefia = 'S' AND und.uorg_pai LIKE :lotacao))
ORDER BY
    dnu.autorizado, cad.nome_serv, dnu.dia DESC
", array(
    array(':siape', $sMatricula, PDO::PARAM_STR),
    array(':data', $vDatas, PDO::PARAM_STR),
    array(':lotacao', $qlotacao, PDO::PARAM_STR),
));


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$title = _SISTEMA_SIGLA_ . ' | Solicitações de Trabalho em Dia não Útil';

// css extra
$css = array();

// js extra
$javascript = array();

// Topo do formulário
//
$oForm->exibeTopoHTML();
?>

<div class="container margin-20">
    <div class="row margin-10">
        <div class="corpo">

            <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

            <div class="col-md-12 subtitle">
                <h6 class="lettering-tittle uppercase"><strong>Solicitações de Trabalho em Dia não Útil</strong></h6>
            </div>
            <div class="col-md-12 margin-bottom-25"></div>

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
                                <td class="text-center col-xs-2 text-nowrap"><h4><?= tratarHTML(getOrgaoMaisSigla( $lotacao )); ?></h4></td>
                                <td class="text-left col-xs-4"><h4><?= tratarHTML(getUorgMaisDescricao( $lotacao )); ?></h4></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <table class="table table-bordered text-center table-hover">
                <thead>
                    <tr>
                        <th class="text-center">SIAPE</th>
                        <th class="text-center">NOME</th>
                        <th class="text-center">DIA SOLICITADO</th>
                        <th class="text-center">AÇÃO</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $autorizado_sim = 0;
                    $autorizado_nao = 0;

                    while ($pm = $oDBase->fetch_object())
                    {
                        if ($pm->autorizado == 'S')
                        {
                            $autorizado_sim++;
                        }
                        else
                        {
                            $autorizado_nao++;
                            ?>
                            <tr height='18'>
                                <td align='center'><?= tratarHTML(removeOrgaoMatricula( $pm->mat_siape )); ?></td>
                                <td><?= tratarHTML($pm->nome_serv); ?></td>
                                <td align='center'><?= tratarHTML($pm->dt); ?></td>
                                <td align='center' title='Utilize essa opção para autorizar trabalho de servidor em dia não útil.'><a href="javascript:window.location.replace('autorizacao_trabalho_dia_nao_util_gravar.php?dados=<?= criptografa(tratarHTML($pm->mat_siape) . ':|:' . tratarHTML($pm->dia) . ':|:' . tratarHTML($pm->email) . ':|:' . tratarHTML($lotacao) . ':|:' . tratarHTML($orgao_sigla) . ':|:' . tratarHTML($lotacao_descricao)); ?>');">Autorizo</a></td>
                            </tr>
                            <?php
                        }
                    }

                    if ($autorizado_sim > 0 && $autorizado_nao == 0)
                    {
                        ?>
                        <tr height='25'>
                            <td colspan='4' style='vertical-align: middle; text-align: center;'>Não há registro de de solicitações para trabalho em dias não úteis!</td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>

        </div>
    </div>
</div>
<?php
// Base do formulário
//
$oForm->exibeBaseHTML();
