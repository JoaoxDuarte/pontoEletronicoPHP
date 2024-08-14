<?php
// conexao ao banco de dados
// funcoes diversas
include_once( "config.php" );
include_once( "class_form.justificativa.php" );

verifica_permissao("sRH ou Chefia");
verifica_acesso_homologacao();

$sLotacao = $_SESSION["sLotacao"];

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    header("Location: acessonegado.php");
}
else
{
    // Valores passados - encriptados
    $dados      = explode(":|:", base64_decode($dadosorigem));
    $mat        = $dados[0];
    $dia        = $dados[1];
    $cod_sitcad = $dados[2];
    $cmd        = $dados[3];
    $so_ver     = $dados[4];
}

$mat = getNovaMatriculaBySiape($mat);

// dados voltar
$_SESSION['voltar_nivel_2'] = $dadosorigem;
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';

$competencia = dataMes($dia) . dataAno($dia);

// instancia o banco de dados
$oDBase = new DataBase('PDO');

// seleciona nome do servidor e jornada
$oDBase->query("
	SELECT
		cad.nome_serv, cad.jornada, cad.cod_lot, pto.just, pto.oco,
        pto.idreg, pto.justchef, und.descricao, taborgao.denominacao,
        taborgao.sigla, cad.sigregjur
	FROM
		servativ AS cad
	LEFT JOIN
		ponto$competencia AS pto ON cad.mat_siape = pto.siape
    LEFT JOIN
        tabsetor AS und ON cad.cod_lot = und.codigo
    LEFT JOIN
        taborgao ON LEFT(und.codigo,5) = taborgao.codigo
    WHERE
        cad.mat_siape = :siape
		AND pto.dia = :dia
	", array(
    array(':siape', $mat, PDO::PARAM_STR),
    array(':dia', conv_data($dia), PDO::PARAM_STR),
));

if ($oDBase->num_rows() > 0)
{
    $oServidor         = $oDBase->fetch_object();
    $nome              = trata_aspas($oServidor->nome_serv);
    $lot               = $oServidor->cod_lot;
    $just              = trata_aspas($oServidor->just);
    $justchef          = trata_aspas($oServidor->justchef);
    $oco               = $oServidor->oco;
    $idreg             = $oServidor->idreg;
    $jnd1              = $oServidor->jornada;
    $jnd               = formata_jornada_para_hhmm($jnd1);
    $lotacao           = $oServidor->cod_lot;
    $lotacao_descricao = $oServidor->descricao;
    $orgao_sigla       = $oServidor->sigla;
    $sitcad            = $oServidor->sigregjur;
}


// seleciona a descrição da ocorrência
$oDBase->query("
    SELECT
        oco.desc_ocorr
    FROM
        tabocfre AS oco
    WHERE
        oco.siapecad = :siapecad
    ", array(
    array(":siapecad", $oco, PDO::PARAM_STR)
));

$oOcorrencia          = $oDBase->fetch_object();
$descricao_ocorrencia = $oOcorrencia->desc_ocorr;

$mes_homologado = verifica_se_mes_homologado($mat, $competencia);

$_SESSION['justificativa_chefia']   = $justchef;
$_SESSION['justificativa_servidor'] = $just;

if ($so_ver != 'sim' && ($_SESSION['sAPS'] == 'S'))
{
    $frequencia_excluir    = base64_encode($mat . ':|:' . $dia . ':|:' . $cod_sitcad . ':|:5');
    $frequencia_alterar    = base64_encode($mat . ':|:' . $nome . ':|:' . $dia . ':|:' . $oco . ':|:' . $sLotacao . ':|:' . $idreg . ':|:' . $cmd . ':|:' . $jnd . ':|:' . $cod_sitcad . ':|:homologar_registros');
    $destino_botao_avancar = "javascript:window.location.replace('frequencia_alterar.php?dados=" . $frequencia_alterar . "');";
}
$destino_botao_voltar = "javascript:window.location.replace('frequencia_homologar_registros.php?dados=" . $_SESSION['voltar_nivel_1'] . "');";


$title = _SISTEMA_SIGLA_ . ' | Justificativa para Ocorr&ecirc;ncia';

$css = array();

$javascript = array();

include("html/html-base.php");
include("html/header.php");
?>
<script language="javascript">
    function verificadados()
    {
        if (document.form1.justchef.value.length < 15)
        {
            //alertaNaPagina(' É obrigatório o preenchimento da justificativa com no mínimo 15 caracteres!');
            mostraMensagem('É obrigatório o preenchimento da justificativa com no mínimo 15 caracteres!', 'danger');
            document.form1.justchef.focus();
            return false;
        }
    }
</script>

<div class="container">
    <div class="row" style="padding-top:90px;">
        <div class="corpo">

            <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

            <div class="col-md-12 subtitle">
                <h6 class="lettering-tittle uppercase"><strong>Justificativa para Ocorr&ecirc;ncia</strong></h6>
            </div>
            <div class="col-md-12 margin-bottom-25"></div>

            <div class="col-md-12">
                <div class="row">
                    <table class="table text-center">
                        <thead>
                            <tr>
                                <th class="text-center text-nowrap" style='vertical-align:middle;'>SIAPE</th>
                                <th class="text-center" style='vertical-align:middle;'>NOME</th>
                                <th class="text-center" style='vertical-align:middle;'>ÓRGÃO</th>
                                <th class="text-center" style='vertical-align:middle;'>LOTAÇÃO</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><h4><?= tratarHTML(removeOrgaoMatricula( $mat )); ?></h4></td>
                                <td class="text-left col-xs-4"><h4><?= tratarHTML($nome); ?></h4></td>
                                <td class="text-center col-xs-2 text-nowrap"><h4><?= tratarHTML(getOrgaoMaisSigla( $sLotacao )); ?></h4></td>
                                <td class="text-left col-xs-4"><h4><?= tratarHTML(getUorgMaisDescricao( $sLotacao )); ?></h4></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <form id="form1" name="form1" method="POST" action="javascript:verificadados();">

                <input type='hidden' id='cmd'     name='cmd'     value='<?= tratarHTML($cmd); ?>'>
                <input type='hidden' id='grupo'   name='grupo'   value='<?= tratarHTML($grupo_nome); ?>'>
                <input type='hidden' id='dados'   name='dados'   value='<?= tratarHTML($dadosorigem); ?>'>
                <input type="hidden" id="siape"   name="siape"   value="<?= tratarHTML($mat); ?>">
                <input type="hidden" id="nome"    name="nome"    value="<?= tratarHTML($nome); ?>">
                <input type="hidden" id="lotacao" name="lotacao" value="<?= tratarHTML($sLotacao); ?>">
                <input type="hidden" id="dia"     name="dia"     value="<?= tratarHTML($dia); ?>">
                <input type="hidden" id="oco"     name="oco"     value="<?= tratarHTML($oco); ?>">

                <div class="form-group col-md-12">
                    <div class="col-md-3 col-md-offset-1">
                        <label for="dia" class="control-label">Dia</label>
                    </div>
                    <div class="col-md-2">
                        <input type="text" id="dia" name="dia" size="10" maxlength="10" value="<?= tratarHTML($dia); ?>" class="form-control" readonly>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <div class="col-md-3 col-md-offset-1">
                        <label for="oco" class="control-label">Ocorrência</label>
                    </div>
                    <div class="col-md-1">
                        <input type="text" id="oco" name="oco" size="8" maxlength="8" value="<?= tratarHTML($oco); ?>" class="form-control" readonly style="width:80px;">
                    </div>
                    <div class="col-md-6">
                        <input type="text" id="desc_ocorr" name="desc_ocorr" size="30" maxlength="30" value="<?= tratarHTML($descricao_ocorrencia); ?>" class="form-control" readonly>
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <div class="col-md-3 col-md-offset-1">
                        <label for="siapecad" class="control-label">Justificativa do Servidor</label>
                    </div>
                    <div class="col-md-8">
                        <textarea name='just' cols=80 rows=5 id="just" class="form-control" disabled><?= tratarHTML($just); ?></textarea>
                    </div>
                </div>

                <div class="form-group col-md-12 text-center">
                    <div class="col-md-2"></div>
                    <div class="col-md-2 col-xs-6 col-md-offset-2">
                        <a class="btn btn-success btn-block" id="btn-reiniciar-senha" href="<?= tratarHTML($destino_botao_avancar); ?>" role="button">
                            <span class="glyphicon glyphicon-ok"></span> Continuar
                        </a>
                    </div>
                    <div class="col-md-2 col-xs-6">
                        <a class="btn btn-danger btn-block" id="btn-reiniciar-senha" href="<?= tratarHTML($destino_botao_voltar); ?>" role="button">
                            <span class="glyphicon glyphicon-ok"></span> Voltar
                        </a>
                    </div>
                    <div class="col-md-2"></div>
                </div>

            </form>
        </div>
    </div>
</div>
<?php
if ($mes_homologado != 'HOMOLOGADO')
{
    ?>
    <script> $('#justchef').focus();</script>
    <?php
}

include("html/footer.php");

DataBase::fechaConexao();
