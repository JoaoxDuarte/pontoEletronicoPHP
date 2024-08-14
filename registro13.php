<?php

// funcoes de uso geral
include_once( "config.php" );
include_once( "class_form.telas.php" );
include_once( "class_ocorrencias_grupos.php" );

// permissao de acesso
verifica_permissao("sRH ou Chefia");

// verifica a origem da pagina solicitante
//include_once( "ilegal_acesso_ao_registro13.php" );
// pega o nome do arquivo origem
$pagina_de_origem = pagina_de_origem();

// valores registrado em sessao
$sMatricula = $_SESSION["sMatricula"];
$magico     = $_SESSION["magico"];

// parametros passados por formulario
$mat  = tratarHTML($_REQUEST['mat']);
$dia  = tratarHTML($_REQUEST['dia']);
$cmd  = tratarHTML($_REQUEST['cmd']);
$ocor = tratarHTML($_REQUEST['ocor']);

$mat = getNovaMatriculaBySiape($mat);

// dados do servidor
$oDBase = selecionaServidor( $mat );
$oServidor = $oDBase->fetch_object();
$nome   = $oServidor->nome_serv;
$lot    = $oServidor->cod_lot;
$jnd    = $oServidor->jornada;
$sitcad = $oServidor->sigregjur;


// ocorrências grupos
$obj = new OcorrenciasGrupos();
$codigoCreditoRecessoPadrao    = $obj->CodigoCreditoRecessoPadrao($sitcad);
$codigoDebitoRecessoPadrao     = $obj->CodigoDebitoRecessoPadrao($sitcad);
$codigoDebitoInstrutoriaPadrao = $obj->CodigoDebitoInstrutoriaPadrao($sitcad);
$eventosEsportivos             = $obj->EventosEsportivos($todos='debito');
$codigosDebitos                = $obj->GrupoOcorrenciasPassiveisDeAbono($sitcad);
//'00172__62010__62012__02323__02525__55555__99999'


// dados para retorno a este script
$_SESSION['sPaginaRetorno_erro']  = $_SERVER['REQUEST_URI'] . "&sMatricula=$sMatricula&magico=$magico&mat=$mat&dia=$dia&cmd=$cmd&ocor=$ocor";
$_SESSION['sPaginaRetorno_erro2'] = $_SERVER['REQUEST_URI'];

$_SESSION['voltar_nivel_3'] = $_SERVER['REQUEST_URI'] . "&sMatricula=$sMatricula&magico=$magico&mat=$mat&dia=$dia&cmd=$cmd&ocor=$ocor";
$_SESSION['voltar_nivel_4'] = '';

// historico de navegacao (substituir o $_SESSION['sPaginaRetorno_sucesso']
$sessao_navegacao = new Control_Navegacao();
$sessao_navegacao->setPagina($_SERVER['REQUEST_URI'] . "&sMatricula=$sMatricula&magico=$magico&mat=$mat&dia=$dia&cmd=$cmd&ocor=$ocor");


if (in_array($ocor,$codigoDebitoRecessoPadrao) && (dataUsoDoRecesso($dia) == false))
{
    mensagem("Não é permitido lançar recesso (" . implode(', ', $codigoDebitoRecessoPadrao) . ") fora do período legal!", $sessao_navegacao->getPaginaAnterior());
}
if (in_array($ocor,$codigoCreditoRecessoPadrao) && (dataCompensacaoDoRecesso($dia) == false))
{
    mensagem("Não é permitido lançar compensação de recesso (" . implode(', ', $codigoCreditoRecessoPadrao) . ") fora do período legal!", $sessao_navegacao->getPaginaAnterior());
}
if (verifica_se_dia_nao_util($dia, $lot) == true && in_array($ocor,$codigosDebitos))
{
    mensagem("Não é permitido lançar a ocorrência " . $ocor . ", em dia não útil!", $sessao_navegacao->getPaginaAnterior());
}


## competencia
#
$oData = new trata_datasys();

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

## dados da frequencia do servidor
#
$diac   = conv_data($dia);
$oDBase->query("SELECT entra, intini, intsai, sai FROM ponto$comp WHERE siape = :siape AND dia = :dia ",array(
    array( ':siape',   $mat,   PDO::PARAM_STR ),
    array( ':dia',   $diac,   PDO::PARAM_STR )
));
$oPonto = $oDBase->fetch_object();
$entra  = $oPonto->entra;
$iniint = $oPonto->intini;
$fimint = $oPonto->intsai;
$sai    = $oPonto->sai;

// grava a hora em sessão
$_SESSION['ssEntrada']   = $entra;
$_SESSION['ssAlmocoIni'] = $iniint;
$_SESSION['ssAlmocoFim'] = $fimint;
$_SESSION['ssSaida']     = $sai;

## classe para montagem do formulario
#
$oForm = new formTelas;
$oForm->setCaminho('Frequência » ... » Ocorrência');
$oForm->setJS('registro13.js');
$oForm->setSeparador(0);
$oForm->setSeparadorBase(20);

$oForm->setSubTitulo("Edição de Registro de Atrasos e Saídas Antecipadas, Afastamentos de Instrutoria");

## Tela - registro13
#
$oForm->setFormAction('gravaregfreq2.php');
$oForm->setFormOnSubmit('return validar();');
$oForm->initInputHidden();
$oForm->setInputHidden('modo', '2');
$oForm->setInputHidden('compete', $comp);
$oForm->setInputHidden('ocor', $ocor);
$oForm->setInputHidden('jnd', $jnd);
$oForm->setInputHidden('cmd', $cmd);

$oForm->setInputHidden('codigosDebitos', implode(',', $codigosDebitos)); //'00172__62010__62012__02323__02525__55555__99999';
$oForm->setInputHidden('recessoDebito', implode(',', $codigoDebitoRecessoPadrao));  //'02323';
$oForm->setInputHidden('recessoCredito', implode(',', $codigoCreditoRecessoPadrao)); //'02424';
$oForm->setInputHidden('codigoDebitoInstrutoriaPadrao', implode(',', $codigoDebitoInstrutoriaPadrao)); //02525
$oForm->setInputHidden('eventosEsportivos', implode(',', $eventosEsportivos)); // 62010_62012_62014

$oForm->setFormNomeServidor($nome);
$oForm->setFormMatriculaSiape($mat);
$oForm->setFormData($dia);
$oForm->setFormHoraEntrada($entra);
$oForm->setFormHoraSaidaAlmoco($iniint);
$oForm->setFormHoraVoltaAlmoco($fimint);
$oForm->setFormHoraSaida($sai);
$oForm->setFormOcorrencia($ocor);
$oForm->exibeTelaRegistro13();
