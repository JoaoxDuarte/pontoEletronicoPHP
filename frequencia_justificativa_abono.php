<?php
// conexao ao banco de dados
// funcoes diversas
include_once( "config.php" );
include_once( "class_form.justificativa.php" );
include_once( "class_ocorrencias_grupos.php" );

verifica_permissao("sRH ou Chefia");

$sLotacao = $_SESSION['sLotacao'];

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
    $cmd        = $dados[2];
    $so_ver     = $dados[3];

    switch ($dados[4])
    {
        case "acompanhar":
        case "acompanhar_ve_ponto":
        case "rh_mes_corrente":
        case "rh_mes_homologacao":
            $grupo = $dados[4];
            break;

        default:
            $grupo = "homologar";
            break;
    }

    $grupo_nome = $grupo;
}

$mat = getNovaMatriculaBySiape($mat);

$comp = dataMes($dia) . dataAno($dia);
$diac = conv_data($dia);


// dados voltar
$destino_retorno = explode('dados=', $_SESSION['voltar_nivel_1']);
if ($grupo == "acompanhar_ve_ponto")
{
    $grupo                      = "acompanhar";
    $destino_retorno            = $_SESSION['voltar_nivel_2'];
    $_SESSION['voltar_nivel_3'] = $dadosorigem;
}
else
{
    $destino_retorno            = "frequencia_" . $grupo . "_registros.php?dados=" . (count($destino_retorno) > 1 ? $destino_retorno[1] : $_SESSION['voltar_nivel_1']);
    $_SESSION['voltar_nivel_2'] = $dadosorigem;
    $_SESSION['voltar_nivel_3'] = '';
}

// dados voltar
$_SESSION['voltar_nivel_4'] = '';
$_SESSION['voltar_nivel_5'] = '';

//    include_once("ilegal5.php");
// instancia o banco de dados
$oDBase = new DataBase('PDO');

// dados do servidor
$oDBase->setDestino('javascript:window.location.replace("' . $destino_retorno . '")');
$oDBase->query("
    SELECT
        cad.nome_serv, cad.jornada, cad.cod_lot, und.descricao, taborgao.denominacao, taborgao.sigla, cad.sigregjur
    FROM
        servativ AS cad
    LEFT JOIN
        tabsetor AS und ON cad.cod_lot = und.codigo
    LEFT JOIN
        taborgao ON LEFT(und.codigo,5) = taborgao.codigo
    WHERE
        cad.mat_siape = :siape
    ", array(
    array(':siape', $mat, PDO::PARAM_STR),
));

$oServidor         = $oDBase->fetch_object();
$nome              = trata_aspas($oServidor->nome_serv);
$jnd1              = $oServidor->jornada;
$jnd               = formata_jornada_para_hhmm($jnd1);
$lotacao           = $oServidor->cod_lot;
$lotacao_descricao = $oServidor->descricao;
$orgao_sigla       = $oServidor->sigla;
$sitcad            = $oServidor->sigregjur;


// instancia grupo de ocorrencia
$objOcorr = new OcorrenciasGrupos();
$grupoOcorrenciasPassiveisDeAbono = $objOcorr->GrupoOcorrenciasPassiveisDeAbono($sitcad);
$codigoSemFrequenciaPadrao        = $objOcorr->CodigoSemFrequenciaPadrao($sitcad);
$codigoAbonoPadrao                = $objOcorr->CodigoAbonoPadrao($sitcad);


// dados da frequência
$oDBase->query("
    SELECT
        pto.oco, pto.idreg, pto.just, pto.justchef
    FROM
        ponto$comp AS pto
    WHERE
        pto.siape = :siape
        AND pto.dia = :dia
    ", array(
    array(':siape', $mat, PDO::PARAM_STR),
    array(':dia', $diac, PDO::PARAM_STR),
));

if ($oDBase->num_rows() == 0)
{
    $oco      = $codigoAbonoPadrao[0]; //'99999';
    $idreg    = 'C';
    $just     = '';
    $justchef = '';
}
else
{
    $oPonto   = $oDBase->fetch_object();
    $oco      = $oPonto->oco;
    $idreg    = $oPonto->idreg;
    $just     = trata_aspas($oPonto->just);
    $justchef = trata_aspas($oPonto->justchef);
}


// dados da frequência
$oDBase->query("
    SELECT
        oco.desc_ocorr
    FROM
        tabocfre AS oco
    WHERE
        oco.siapecad = :oco
    ", array(
    array(':oco', $oco, PDO::PARAM_STR),
));

$oOcorrencia          = $oDBase->fetch_object();
$descricao_ocorrencia = $oOcorrencia->desc_ocorr;

if ( !in_array($oco, $grupoOcorrenciasPassiveisDeAbono) )
{
    $str                = implode(", ", $grupoOcorrenciasPassiveisDeAbono);
    $passiveis_de_abono = substr_replace($str, " ou ", strrpos($str, ", "), 2);

    mensagem(
        "Não é permitido abonar dia com ocorrência"
        . " diferente\\nde " . "$passiveis_de_abono!",
        $destino_retorno
    );
}



## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$title = _SISTEMA_SIGLA_ . ' | Justificativa para Abono de Ocorr&ecirc;ncia';

// css extra
$css = array();

// js extra
$javascript   = array();
$javascript[] = _DIR_JS_ . 'phpjs.js';
$javascript[] = 'frequencia_justificativa_abono.js';

// Topo do formulário
//
$oForm->exibeTopoHTML();
?>

<div class="container margin-20">
    <div class="row margin-10">
        <div class="corpo">

            <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

            <div class="col-md-12 subtitle">
                <h6 class="lettering-tittle uppercase"><strong>Justificativa para Abono de Ocorr&ecirc;ncia</strong></h6>
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
                                <td class="text-center col-xs-2 text-nowrap"><h4><?= tratarHTML(getOrgaoMaisSigla( $lotacao )); ?></h4></td>
                                <td class="text-left col-xs-4"><h4><?= tratarHTML(getUorgMaisDescricao( $lotacao )); ?></h4></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <form action="#"  method='POST' id='form1' name='form1'>

                <input type='hidden' id='cmd'           name='cmd'           value='<?= tratarHTML($cmd); ?>'>
                <input type='hidden' id='grupo'         name='grupo'         value='<?= tratarHTML($grupo_nome); ?>'>
                <input type='hidden' id='mat'           name='mat'           value='<?= tratarHTML($mat); ?>'>
                <input type='hidden' id='nome'          name='nome'          value='<?= tratarHTML($nome); ?>'>
                <input type='hidden' id='lot'           name='lot'           value='<?= tratarHTML($lotacao); ?>'>
                <input type='hidden' id='dia'           name='dia'           value='<?= tratarHTML($dia); ?>'>
                <input type='hidden' id='oco'           name='oco'           value='<?= tratarHTML($oco); ?>'>
                <input type='hidden' id='dados'         name='dados'         value=''>

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

                <div class="form-group col-md-12" id="justificativa_chefia">
                    <div class="col-md-3 col-md-offset-1">
                        <label for="siapecad" class="control-label">Justificativa da Chefia</label>
                    </div>
                    <div class="col-md-8">
                        <textarea name='justchef' cols=80 rows=5 id="justchef" class="form-control"><?= tratarHTML($justchef); ?></textarea>
                    </div>
                </div>

                <div class="form-group col-md-12 text-center">
                    <div class="col-md-2"></div>
                    <?php
                    if (empty($just))
                    {
                        ?>
                        <div class="col-md-4 col-xs-6 col-md-offset-2" style=>
                            <a class="btn btn-success btn-block" id="btn-continuar" role="button">
                                <span class="glyphicon glyphicon-ok"></span> Não há justificativa do Servidor.<br>Continuar?
                            </a>
                        </div>
                        <div class="col-md-2 col-xs-6">
                            <a class="btn btn-danger btn-block" id="btn-voltar" href="<?= tratarHTML($destino_retorno); ?>" role="button" style="padding:16px;">
                                <span class="glyphicon glyphicon-ok"></span> Voltar
                            </a>
                        </div>
                        <?php
                    }
                    else
                    {
                        ?>
                        <div class="col-md-2 col-xs-6 col-md-offset-2">
                            <a class="btn btn-success btn-block" id="btn-continuar" role="button">
                                <span class="glyphicon glyphicon-ok"></span> Continuar
                            </a>
                        </div>
                        <div class="col-md-2 col-xs-6">
                            <a class="btn btn-danger btn-block" id="btn-voltar" href="<?= tratarHTML($destino_retorno); ?>" role="button">
                                <span class="glyphicon glyphicon-ok"></span> Voltar
                            </a>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="col-md-2"></div>
                </div>

            </form>

        </div>
    </div>
</div>
<?php
// Base do formulário
//
$oForm->exibeBaseHTML();
