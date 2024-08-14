<?php
// funcoes de uso geral
include_once( "config.php" );
include_once( "class_form.telas.php" );
include_once( "class_ocorrencias_grupos.php" );

// permissao de acesso
verifica_permissao("sRH ou Chefia");
//verifica_acesso_homologacao();
// valores registrado em sessao
$sMatricula = $_SESSION["sMatricula"];
$magico     = $_SESSION["magico"];

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    // parametros passados por formulario
    $mat           = anti_injection($_REQUEST['mat']);
    $dia           = $_REQUEST['dia'];
    $cmd           = anti_injection($_REQUEST['cmd']);
    $ocor          = anti_injection($_REQUEST['ocor']);
    $lot           = anti_injection($_REQUEST['lot']);
    $grupo         = anti_injection($_REQUEST['grupo']);
    $cod_sitcad    = anti_injection($_REQUEST['cod_sitcad']);
    $justchef      = utf8_decode(trata_aspas($_REQUEST['justchef']));
    $grupoOperacao = anti_injection($_REQUEST['grupoOperacao']);
    $ocor_origem   = anti_injection($_REQUEST['ocor_origem']);
}
else
{
    $dados         = explode(":|:", base64_decode($dadosorigem));
    $mat           = $dados[0];
    $dia           = $dados[1];
    $cmd           = $dados[2];
    $ocor          = $dados[3];
    $lot           = $dados[4];
    $grupo         = $dados[5];
    $cod_sitcad    = $dados[6];
    $justchef      = utf8_decode(trata_aspas($dados[7]));
    $grupoOperacao = $dados[8]; //acompanhar ou homologar
    $ocor_origem   = $dados[9];
}

$mat = getNovaMatriculaBySiape($mat);

$oDBase = selecionaServidor($mat);
$sitcad = $oDBase->fetch_object()->sigregjur;

// instancia grupo de ocorrencia
$oOcorrencia = new OcorrenciasGrupos();
$debito                           = $oOcorrencia->CodigosDebito($sitcad);
$recesso_debito                   = $oOcorrencia->CodigoDebitoRecessoPadrao($sitcad);
$recesso_credito                  = $oOcorrencia->CodigoCreditoRecessoPadrao($sitcad);
$eventos_esportivos               = $oOcorrencia->EventosEsportivos('debito');
$instrutoria_padrao               = $oOcorrencia->CodigoDebitoInstrutoriaPadrao($sitcad);
$grupoOcorrenciasViagem           = $oOcorrencia->GrupoOcorrenciasViagem($sitcad);
$codigoBancoDeHorasDebitoPadrao   = $oOcorrencia->CodigoBancoDeHorasDebitoPadrao($sitcad);


$para_teste_no_js = array_merge($eventos_esportivos, $recesso_debito, $instrutoria_padrao);

if ((in_array($ocor, $recesso_debito)) && (dataUsoDoRecesso($dia) == false))
{
    mensagem("Não é permitido lançar recesso (".implode(', ', $recesso_debito).") fora do período legal!", "frequencia_alterar.php?dados=" . $_SESSION['voltar_nivel_3']);
}
if ((in_array($ocor, $recesso_credito)) && (dataCompensacaoDoRecesso($dia) == false))
{
    mensagem("Não é permitido lançar compensação de recesso (".implode(', ', $recesso_credito).") fora do período legal!", "frequencia_alterar.php?dados=" . $_SESSION['voltar_nivel_3']);
}
if (verifica_se_dia_nao_util($dia, $lot) == true && (in_array($ocor, $debito)))
{
    mensagem("Não é permitido lançar a ocorrência " . $ocor . ", em dia não útil!", "frequencia_alterar.php?dados=" . $_SESSION['voltar_nivel_3']);
}
if (in_array($ocor, $grupoOcorrenciasViagem) && ($_SESSION['sAPS'] == 'S'))
{
    $dados = base64_encode($mat + ":|:" + $dia + ":|:" + $ocor + ":|:" + $lot + ":|:outros" + ":|:" + $cod_sitcad + ":|:" + $justchef + ":|:" + $grupoOperacao + ":|:" + $ocor_origem);
    replaceLink("frequencia_gravar.php?dados=" . $dados);
}

// dados voltar
switch ($grupoOperacao)
{
    case 'acompanhar':
        $_SESSION['voltar_nivel_5'] = $_SERVER['REQUEST_URI'];
        $setDestino                 = $_SESSION['voltar_nivel_4'];
        break;

    case 'acompanhar_ve_ponto':
        $_SESSION['voltar_nivel_4'] = $_SERVER['REQUEST_URI'];
        $setDestino                 = $_SESSION['voltar_nivel_3'];
        break;

    //case 'homologar':
    default:
        $_SESSION['voltar_nivel_4'] = $_SERVER['REQUEST_URI'];
        $setDestino                 = 'frequencia_alterar.php?dados=' . $_SESSION['voltar_nivel_3'];
        break;
}

$diac = conv_data($dia);


## competencia
#
$oData = new trata_datasys;

if ($cmd == "1")
{
    ## mes e ano atual
    #
    $ano  = $oData->getAno();
    $mes  = $oData->getMes();
    $year = $ano;
    $comp = $mes . $year;
}
else
{
    ## mes e ano de homologacao (anterior ao atual)
    #
    $mes  = $oData->getMesHomologacao();
    $ano  = $oData->getAnoHomologacao();
    $year = $ano;
    $comp = $mes . $year;
}

$mes  = dataMes($dia);
$ano  = dataAno($dia);
$year = $ano;
$comp = $mes . $year;

    
// carrega o nome da tabela de trabalho
// se for alteração via módulo histórico
// trás o nome da tabela temporária
$arquivo = nomeTabelaFrequencia($grupoOperacao, $comp);
    

// instancia o banco de dados
$oDBase = new DataBase('PDO');

// dados do servidor - cadastro e frequência do dia
$oDBase->setDestino('javascript:window.location.replace("' . $setDestino . '")');
$oDBase->query("
SELECT
    cad.nome_serv, cad.jornada, cad.cod_lot, pto.just, pto.oco, pto.idreg, 
    pto.justchef, und.descricao, taborgao.denominacao, taborgao.sigla, 
    IFNULL(pto.entra,'00:00:00')  AS entra, 
    IFNULL(pto.intini,'00:00:00') AS intini, 
    IFNULL(pto.intsai,'00:00:00') AS intsai, 
    IFNULL(pto.sai,'00:00:00')    AS sai, 
    pto.oco, oco.desc_ocorr
FROM
    servativ AS cad
LEFT JOIN
    $arquivo AS pto ON cad.mat_siape = pto.siape AND pto.dia = :dia
LEFT JOIN
    tabocfre AS oco ON IFNULL(pto.oco,:ocor) = oco.siapecad
LEFT JOIN
    tabsetor AS und ON cad.cod_lot = und.codigo
LEFT JOIN
    taborgao ON LEFT(und.codigo,5) = taborgao.codigo
WHERE
    cad.mat_siape = :siape    
", array(
    array(':siape', $mat,  PDO::PARAM_STR),
    array(':dia',   $diac, PDO::PARAM_STR),
    array(':ocor',  $ocor, PDO::PARAM_STR),
));

$oDados = $oDBase->fetch_object();

$nome                 = trata_aspas($oDados->nome_serv);
//$lot                  = $oDados->cod_lot;
//$oco                  = $oDados->oco;
$descricao_ocorrencia = $oDados->desc_ocorr;
$idreg                = $oDados->idreg;
$jnd                  = $oDados->jornada;
$jnc                  = formata_jornada_para_hhmm($jnd);
$lotacao              = $oDados->cod_lot;
$lotacao_descricao    = $oDados->descricao;
$orgao_sigla          = $oDados->sigla;

$entra                = $oDados->entra;  // horário de entrada registrado
$iniint               = $oDados->intini; // horário de saida para o almoco
$fimint               = $oDados->intsai; // horário de retorno do almoco
$sai                  = $oDados->sai;    // horário de saida final


// dados do servidor - cadastro e frequência do dia
$oDBase->query("
SELECT
    oco.desc_ocorr
FROM
    tabocfre AS oco 
WHERE
    oco.siapecad = :ocor    
", array(
    array(':ocor',  $ocor, PDO::PARAM_STR),
));

// descrição ocorrencia
$descricao_ocorrencia = $oDBase->fetch_object()->desc_ocorr;



## classe para montagem do formulario padrao
#
$title = _SISTEMA_SIGLA_ . ' | Alterar Registro de Ocorrência';
$oForm = new formPadrao();
$oForm->setJSSelect2();
$oForm->setJS( "js/phpjs.js" );
$oForm->setJS( "js/jquery.mask.min.js" );
$oForm->setJS( "frequencia_alterar_horario.js?v.0.0.0.0.012" );
$oForm->setSubTitulo("Alterar Registro de Ocorr&ecirc;ncia");

// Topo do formul?rio
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


?>
<div class="container margin-20">
    <div class="row margin-10">
        <div class="corpo">
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
                                <td><h4><?= tratarHTML(removeOrgaoMatricula($mat)); ?></h4></td>
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
                <input type='hidden' id='ocor'          name='ocor'          value='<?= tratarHTML($ocor); ?>'>
                <input type='hidden' id='jnd'           name='jnd'           value='<?= tratarHTML($jnd); ?>'>
                <input type='hidden' id='cmd'           name='cmd'           value='<?= tratarHTML($cmd); ?>'>
                <input type='hidden' id='lot'           name='lot'           value='<?= tratarHTML($lot); ?>'>
                <input type='hidden' id='justchef'      name='justchef'      value='<?= tratarHTML($justchef); ?>'>
                <input type='hidden' id='grupo'         name='grupo'         value='<?= tratarHTML($grupo); ?>'>
                <input type='hidden' id='jn'            name='jn'            value='<?= formata_jornada_para_hhmm($jnd); ?>'>
                <input type='hidden' id='mes'           name='mes'           value='<?= tratarHTML($mes); ?>'>
                <input type='hidden' id='ano'           name='ano'           value='<?= tratarHTML($ano); ?>'>
                <input type='hidden' id='dias_no_mes'   name='dias_no_mes'   value='<?= numero_dias_do_mes($mes, $ano); ?>'>
                <input type='hidden' id='hom1'          name='hom1'          value='<?= '1'; ?>'>
                <input type='hidden' id='horsaida'      name='horsaida'      value='<?= tratarHTML($hs); ?>'>
                <input type='hidden' id='horsaida2'     name='horsaida2'     value='<?= tratarHTML($vHoras); ?>'>
                <input type='hidden' id='grupoOperacao' name='grupoOperacao' value='<?= tratarHTML($grupoOperacao); ?>'>
                <input type='hidden' id='ocor_origem'   name='ocor_origem'   value='<?= tratarHTML($ocor_origem); ?>'>
                <input type='hidden' id='codigoBancoDeHorasDebitoPadrao'      name='codigoBancoDeHorasDebitoPadrao'      value='<?= tratarHTML( implode(',', $codigoBancoDeHorasDebitoPadrao) ); ?>'>
                <input type='hidden' id='ocorr_esportivo_recesso_instrutoria' name='ocorr_esportivo_recesso_instrutoria' value='<?= tratarHTML( implode(',', $para_teste_no_js) ); ?>'>

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
                        <input type="text" id="oco" name="oco" size="8" maxlength="8" value="<?= tratarHTML($ocor); ?>" class="form-control" readonly style="width:80px;">
                    </div>
                    <div class="col-md-6">
                        <input type="text" id="desc_ocorr" name="desc_ocorr" size="30" maxlength="30" value="<?= tratarHTML($descricao_ocorrencia); ?>" class="form-control" readonly>
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <div class="col-md-2">&nbsp;</div>
                    <div class="col-md-2 table-bordered xs-pt-10 xs-pb-10">
                        <label for="entra" class="control-label text-center">Hora de Início do Expediente</label>
                        <input type="text" id="entra" name="entra" title="Digite o horário sem pontos no formato 000000!" size="8" maxlength="8" value="<?= tratarHTML($entra); ?>" onkeypress="formatar(this, '##:##:##')" class="form-control text-center">
                    </div>
                    <div class="col-md-2 table-bordered xs-pt-10 xs-pb-10">
                        <label for="iniint" class="control-label text-center">Hora de Início do Intervalo</label>
                        <input type="text" id="iniint" name="iniint" title="Digite o horário sem pontos no formato 000000!" size="8" maxlength="8" value="<?= tratarHTML($iniint); ?>" onkeypress="formatar(this, '##:##:##')" class="form-control text-center">
                    </div>
                    <div class="col-md-2 table-bordered xs-pt-10 xs-pb-10">
                        <label for="fimint" class="control-label text-center">Hora de Retorno do Intervalo</label>
                        <input type="text" id="fimint" name="fimint" title="Digite o horário sem pontos no formato 000000!" size="8" maxlength="8" value="<?= tratarHTML($fimint); ?>" onkeypress="formatar(this, '##:##:##')" class="form-control text-center">
                    </div>
                    <div class="col-md-2 table-bordered xs-pt-30 xs-pb-10">
                        <label for="sai" class="control-label text-center">Horário da Saída</label>
                        <input type="text" id="sai" name="sai" title="Digite o horário sem pontos no formato 000000!" size="8" maxlength="8" value="<?= tratarHTML($sai); ?>" onkeypress="formatar(this, '##:##:##')" class="form-control text-center">
                    </div>
                    <div class="col-md-2">&nbsp;</div>
                </div>

                <div class="form-group col-md-12 text-center">
                    <div class="col-md-2"></div>
                    <div class="col-md-2 col-xs-6 col-md-offset-2">
                        <a class="btn btn-success btn-block" id="btn-continuar" role="button">
                            <span class="glyphicon glyphicon-ok"></span> Continuar
                        </a>
                    </div>
                    <div class="col-md-2 col-xs-6">
                        <a class="btn btn-danger btn-block" id="btn-voltar" href="javascript:window.location.replace('<?= $setDestino; ?>')" role="button">
                            <span class="glyphicon glyphicon-ok"></span> Voltar
                        </a>
                    </div>
                    <div class="col-md-2"></div>
                </div>
                
                <?php if (in_array($ocor, $codigoBancoDeHorasDebitoPadrao)): ?>

                    <div style='text-align:right;width:90%;margin:25px;font-size:9px;border:0px;'>
                        <fieldset style='border:1px solid white;text-align:left;'><legend style='font-size:13px;padding:0px;margin:0px;'><b>&nbsp;Informações&nbsp;</b></legend>
                            <p style='padding:1px;margin:0px;font-size:11px;'><b>Débito PARCIAL:&nbsp;:&nbsp;</b>Requer informação dos horários.</p>
                            <p style='padding:1px;margin:0px;font-size:11px;'><b>Débito TOTAL:&nbsp;:&nbsp;</b>NÃO requer informação dos horários.</p>
                        </fieldset>
                    </div>
                
                <?php endif; ?>

            </form>

        </div>
    </div>
</div>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
