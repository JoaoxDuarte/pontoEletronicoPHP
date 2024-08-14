<?php

// conexao ao banco de dados
// funcoes diversas
include_once( "config.php" );
include_once( "class_form.telas.php" );
include_once( "class_ocorrencias_grupos.php" );

verifica_permissao("sRH ou Chefia");

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    header("Location: acessonegado.php");
}
else
{
    $dados         = explode(":|:", base64_decode($dadosorigem));
    $mat           = $dados[0];
    $nome          = $dados[1];
    $dia           = $dados[2];
    $ocor          = $dados[3];
    $lot           = $dados[4];
    $idreg         = $dados[5];
    $cmd           = $dados[6];
    $jnd           = $dados[7];
    $cod_sitcad    = $dados[8];
    $grupoOperacao = $dados[9]; //acompanhar ou homologar
}

$mat = getNovaMatriculaBySiape($mat);

// dados voltar
switch ($grupoOperacao)
{
    case 'acompanhar_ve_ponto':
        $_SESSION['voltar_nivel_3'] = $_SESSION['voltar_nivel_2'];
        $_SESSION['voltar_nivel_4'] = "frequencia_alterar.php?dados=" . $dadosorigem;
        break;

    case 'acompanhar':
        $_SESSION['voltar_nivel_4'] = "frequencia_alterar.php?dados=" . $dadosorigem;
        $_SESSION['voltar_nivel_5'] = '';
        break;

    case 'rh_mes_corrente':
    case 'rh_mes_homologacao':
        $_SESSION['voltar_nivel_3'] = $dadosorigem;
        $_SESSION['voltar_nivel_4'] = "frequencia_alterar.php?dados=" . $dadosorigem;
        $_SESSION['voltar_nivel_5'] = '';
        break;

    case "historico_manutencao":
        $_SESSION['voltar_nivel_3'] = $dadosorigem;
        $_SESSION['voltar_nivel_4'] = "frequencia_alterar.php?dados=" . $dadosorigem;
        $_SESSION['voltar_nivel_5'] = '';
        break;

    //case 'homologar':
    default:
        $_SESSION['voltar_nivel_3'] = $dadosorigem;
        $_SESSION['voltar_nivel_4'] = '';
        break;
}


$aJornada = explode(':', $jnd);
if (count($aJornada) > 1)
{
    $jnd = ($aJornada[0] * 5);
}

if ($grupoOperacao != "historico_manutencao")
{
    include_once( "ilegal4.php" );
}
else
{
    include_once('ilegal_grava.php');
}

## define competencia
#
$data = data2arrayBR($dia);
$mes  = $data[1];
$ano  = $data[2];
$year = $ano;
$comp = $mes . $year;


// carrega o nome da tabela de trabalho
// se for alteração via módulo histórico
// trás o nome da tabela temporária
$arquivo = nomeTabelaFrequencia($grupoOperacao, $comp);


## instancia o banco de dados
$oDBase = new DataBase('PDO');

// seleciona nome do servidor e jornada
$oDBase->query("
SELECT
    cad.nome_serv, cad.jornada, cad.cod_lot, pto.just, pto.oco, pto.idreg, pto.justchef, und.descricao, taborgao.denominacao, taborgao.sigla, cad.sigregjur
FROM
    servativ AS cad
LEFT JOIN
    $arquivo AS pto ON cad.mat_siape = pto.siape
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
    $oServidor = $oDBase->fetch_object();

    $nome              = trata_aspas($oServidor->nome_serv);
    $lot               = $oServidor->cod_lot;
    $oco               = $oServidor->oco;
    $idreg             = $oServidor->idreg;
    $jnd1              = $oServidor->jornada;
    $jnd               = formata_jornada_para_hhmm($jnd1);
    $lotacao           = $oServidor->cod_lot;
    $lotacao_descricao = $oServidor->descricao;
    $orgao_sigla       = $oServidor->sigla;
    $sitcad            = $oServidor->sigregjur;

    if ($_SESSION['justificativa_chefia'] == '')
    {
        $_SESSION['justificativa_chefia']   = trata_aspas($oServidor->justchef);
        $_SESSION['justificativa_servidor'] = trata_aspas($oServidor->just);
    }
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


// instancia grupo de ocorrencia
$obj = new OcorrenciasGrupos();

$ocorrenciasExigeJustificativa    = $obj->OcorrenciasExigeJustificativa($sitcad);
$grupoOcorrenciasNegativasDebitos = $obj->GrupoOcorrenciasNegativasDebitos($sitcad);
$codigosCredito                   = $obj->CodigosCredito($sitcad);
$codigosTrocaObrigatoria          = $obj->CodigosTrocaObrigatoria($sitcad);
$codigoBancoDeHorasCreditoPadrao  = $obj->CodigoBancoDeHorasCreditoPadrao($sitcad);
$codigoBancoDeHorasDebitoPadrao   = $obj->CodigoBancoDeHorasDebitoPadrao($sitcad);


$codigosDeCredito = array_merge($codigosCredito, $codigoBancoDeHorasCreditoPadrao);
$codigosDeDebito  = array_merge($grupoOcorrenciasNegativasDebitos,  $codigoBancoDeHorasDebitoPadrao);

$exige_justificativa_chefia = (in_array($oco, $ocorrenciasExigeJustificativa));

$mes_homologado = verifica_se_mes_homologado($mat, $competencia);

if ($so_ver != 'sim' && ($_SESSION['sAPS'] == 'S'))
{
    $frequencia_excluir    = base64_encode($mat . ':|:' . $dia . ':|:' . $cod_sitcad . ':|:5');
    $frequencia_alterar    = base64_encode($mat . ':|:' . $nome . ':|:' .
        $dia . ':|:' . $oco . ':|:' . $sLotacao . ':|:' . $idreg . ':|:' .
        $cmd . ':|:' . $jnd . ':|:' . $cod_sitcad . ':|:' .
        $grupoOperacao . ($grupoOperacao == 'historico_manutencao' ? '' :  '_registros')
    );
    $destino_botao_avancar = "javascript:verificadados();";
}

switch ($grupoOperacao)
{
    case 'acompanhar_ve_ponto':
        $destino_botao_voltar = "javascript:window.location.replace('" . tratarHTML($_SESSION['voltar_nivel_2']) . "');";
        break;

    case 'acompanhar':
        $destino_botao_voltar = "javascript:window.location.replace('" . tratarHTML($_SESSION['voltar_nivel_1']) . "');";
        break;

    case 'rh_mes_corrente':
        $destino_botao_voltar = "javascript:window.location.replace('frequencia_rh_mes_corrente_registros.php?dados=" . $_SESSION['rh_mes_corrente_registros'] . "');";
        break;

    case 'rh_mes_homologacao':
        $destino_botao_voltar = "javascript:window.location.replace('frequencia_rh_mes_homologacao_registros.php?dados=" . $_SESSION['rh_mes_homologacao_registros'] . "');";
        break;

    case "historico_manutencao":
        $destino_botao_voltar = "javascript:window.location.replace('historico_frequencia_registros.php?dados=" . $_SESSION['historico_manutencao'] . "');";
        break;

    case 'homologar':
    default:
        $destino_botao_voltar = "javascript:window.location.replace('frequencia_homologar_registros.php?dados=" . $_SESSION['voltar_nivel_1'] . "');";
        break;
}



## classe para montagem do formulario padrao
#
$title = _SISTEMA_SIGLA_ . ' | Alterar Ocorr&ecirc;ncia';

$oForm = new formPadrao();
$oForm->setCSS( 'css/select2.min.css' );
$oForm->setCSS( 'css/select2-bootstrap.css' );
$oForm->setJS( 'js/phpjs.js' );
$oForm->setJS( 'js/select2.full.js' );
$oForm->setJS( 'frequencia_alterar.js' );

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

?>
<div class="container margin-20">
    <div class="row margin-10">
        <div class="corpo">

            <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

            <div class="col-md-12 subtitle">
                <h6 class="lettering-tittle uppercase"><strong>Alterar Ocorr&ecirc;ncia</strong></h6>
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
                                <td><h4><?= tratarHTML( removeOrgaoMatricula($mat) ); ?></h4></td>
                                <td class="text-left col-xs-4"><h4><?= tratarHTML($nome); ?></h4></td>
                                <td class="text-center col-xs-2 text-nowrap"><h4><?= tratarHTML(getOrgaoMaisSigla( $lot )); ?></h4></td>
                                <td class="text-left col-xs-4"><h4><?= tratarHTML(getUorgMaisDescricao( $lot )); ?></h4></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <form action="#"  method='POST' id="form1" name='form1'>

                <input type='hidden' id='mat'           name='mat'           value='<?= tratarHTML($mat); ?>'>
                <input type='hidden' id='modo'          name='modo'          value='alterar'>
                <input type='hidden' id='compete'       name='compete'       value='<?= tratarHTML($comp); ?>'>
                <input type='hidden' id='mes'           name='mes'           value='<?= tratarHTML($mes); ?>'>
                <input type='hidden' id='ano'           name='ano'           value='<?= tratarHTML($ano); ?>'>
                <input type='hidden' id='jnd'           name='jnd'           value='<?= tratarHTML($jnd); ?>'>
                <input type='hidden' id='jn'            name='jn'            value='<?= formata_jornada_para_hhmm($jnd); ?>'>
                <input type='hidden' id='cmd'           name='cmd'           value='<?= tratarHTML($cmd); ?>'>
                <input type='hidden' id='lot'           name='lot'           value='<?= tratarHTML($lot); ?>'>
                <input type='hidden' id='dias_no_mes'   name='dias_no_mes'   value='<?= numero_dias_do_mes($mes, $ano); ?>'>
                <input type='hidden' id='hom1'          name='hom1'          value='<?= '1'; ?>'>

                <input type='hidden' id='horsaida'      name='horsaida'      value='<?= tratarHTML($hs); ?>'>
                <input type='hidden' id='horsaida2'     name='horsaida2'     value='<?= tratarHTML($vHoras); ?>'>
                <input type='hidden' id='grupoOperacao' name='grupoOperacao' value='<?= tratarHTML($grupoOperacao); ?>'>

                <input type='hidden' id='credito'       name='credito'       value='<?=  tratarHTML(implode(',',$codigosDeCredito)); ?>'>
                <input type='hidden' id='debito'        name='debito'        value='<?= tratarHTML(implode(',',$codigosDeDebito)); ?>'>
                <input type='hidden' id='outros'        name='outros'        value=''>
                <input type='hidden' id='ocor_origem'   name='ocor_origem'   value='<?= tratarHTML($ocor); ?>'>

                <input type="hidden" id="codigosTrocaObrigatoria"    name="codigosTrocaObrigatoria"    value="<?= tratarHTML(implode(',',$codigosTrocaObrigatoria)); ?>">
                <input type='hidden' id='exige_justificativa_chefia' name='exige_justificativa_chefia' value='<?= tratarHTML(implode(',',$ocorrenciasExigeJustificativa)); ?>'>


                <input type='hidden' id='dados_ocorrencia' name='dados_ocorrencia' value='<?= base64_encode(tratarHTML($mat) . ':|:' . tratarHTML($dia) . ':|:' . tratarHTML($cmd) . ':|:' . tratarHTML($ocor)); ?>'>
                <input type='hidden' id='dados_grupo' name='dados_grupo' value='<?= base64_encode('outros|credito|debito'); ?>'>
                <input type='hidden' id='cod_sitcad'  name='cod_sitcad' value='<?= tratarHTML($cod_sitcad); ?>'>

                <div class="form-group col-md-12">
                    <div class="col-md-3 col-md-offset-1">
                        <label for="siapecad" class="control-label">Dia da Ocorrência</label>
                    </div>
                    <div class="col-md-2">
                        <input name='dia' type='text' id='dia' class="form-control" value='<?= tratarHTML($dia); ?>' size='11' maxlength='10' readonly>
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <div class="col-md-3 col-md-offset-1">
                        <label class="control-label">Código da Ocorrência</label>
                    </div>
                    <div class="col-md-8">
                        <?= montaSelectOcorrencias($ocor, '', false, false, false, '', $grupoOperacao, $mat); ?>
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <div class="col-md-3 col-md-offset-1">
                        <label for="siapecad" class="control-label">Justificativa do Servidor</label>
                    </div>
                    <div class="col-md-8">
                        <textarea name='just' cols=80 rows=5 id="just" class="form-control" disabled><?= tratarHTML($_SESSION['justificativa_servidor']); ?></textarea>
                    </div>
                </div>

                <div class="form-group col-md-12" id="justificativa_chefia" style='display:<?= ($idreg == 'C' && $exige_justificativa_chefia == true ? '' : 'none'); ?>'>
                    <div class="col-md-3 col-md-offset-1">
                        <label for="siapecad" class="control-label">Justificativa da Chefia</label>
                    </div>
                    <div class="col-md-8">
                        <textarea name='justchef' cols=80 rows=5 id="justchef" class="form-control"><?= tratarHTML(trata_aspas($oServidor->justchef)); ?></textarea>
                    </div>
                </div>

                <div class="form-group col-md-12 text-center">
                    <div class="col-md-2"></div>
                    <?php
                    if ($_SESSION['sAPS'] == 'S' && empty($_SESSION['justificativa_servidor']))
                    {
                            ?>
                            <div class="col-md-4 col-xs-6 col-md-offset-2" style=>
                                <a class="btn btn-success btn-block" id="btn-continuar-alteracao" role="button">
                                    <span class="glyphicon glyphicon-ok"></span> Não Há Justificativa do Servidor.<br>Continuar Alteração ?
                                </a>
                            </div>
                            <div class="col-md-2 col-xs-6">
                                <a class="btn btn-danger btn-block" id="btn-voltar" href="<?= tratarHTML($destino_botao_voltar); ?>" role="button" style="padding:16px;">
                                    <span class="glyphicon glyphicon-ok"></span> Voltar
                                </a>
                            </div>
                            <?php
                    }
                    else
                    {
                            ?>
                            <div class="col-md-2 col-xs-6 col-md-offset-2">
                                <a class="btn btn-success btn-block" id="btn-continuar-alteracao" role="button">
                                    <span class="glyphicon glyphicon-ok"></span> Continuar
                                </a>
                            </div>
                            <div class="col-md-2 col-xs-6">
                                <a class="btn btn-danger btn-block" id="btn-voltar" href="<?= tratarHTML($destino_botao_voltar); ?>" role="button">
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
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
