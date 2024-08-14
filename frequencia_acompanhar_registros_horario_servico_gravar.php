<?php

include_once( "config.php" );
include_once( "inc/class_verifica_horario_trabalho.php" );

verifica_permissao("sAPS");

$dadosorigem = $_REQUEST['dados'];
$dadosform   = $_REQUEST['dadosform'];

$dados   = explode(':|:', base64_decode($dadosorigem));
$tSiape  = $dados[0]; //anti_injection($_REQUEST['tSiape']);
$cpf     = $dados[1]; //anti_injection($_REQUEST['sCpf']);
$jornada = $dados[2]; //anti_injection($_REQUEST['jornada']); //Semanal Ex.: 40

$dados              = explode(':|:', base64_decode($dadosform));
$situacao_cadastral = $dados[0]; //anti_injection($_REQUEST['sitcad');
$entra              = $dados[1]; //anti_injection($_REQUEST['entra']) . ':00';
$intini             = $dados[2]; //anti_injection($_REQUEST['intini']) . ':00';
$intsai             = $dados[3]; //anti_injection($_REQUEST['intsai']) . ':00';
$sai                = $dados[4]; //anti_injection($_REQUEST['sai']) . ':00';
$bhoras             = $dados[5]; //anti_injection($_REQUEST['bhoras']);
$bhtipo             = $dados[6]; //anti_injection($_REQUEST['bhoras']);
$autchef            = $dados[7]; //anti_injection($_REQUEST['bhoras']);
//$jornada            = $dados[8]; //anti_injection($_REQUEST['jornada']); //Semanal Ex.: 40
$sAutorizadoTE      = $dados[9]; //anti_injection($_REQUEST['sAutorizadoTE');
$ocupaFuncao        = $dados[10]; //anti_injection($_REQUEST['ocupaFuncao');

$vHoras     = strftime("%H:%M:%S", time());
$vDatas     = date("Y-m-d");
$ip         = getIpReal(); //linha que captura o ip do usuario.
$sMatricula = $_SESSION["sMatricula"];

// instancia o BD
$oDBase = new DataBase('PDO');

// dados da unidade
$oDBase->setMensagem("Problemas no acesso ao SETOR!");
$oDBase->query("SELECT cad.cod_lot, und.inicio_atend, und.fim_atend
FROM servativ AS cad
LEFT JOIN tabsetor AS und ON cad.cod_lot = und.codigo
WHERE cad.mat_siape = :siape AND excluido = 'N'
", array(
    array(':siape', $tSiape, PDO::PARAM_STR),
));

$lret = $oDBase->num_rows();

$oSetor   = $oDBase->fetch_object();
$lotacao  = $oSetor->cod_lot;
$inisetor = $oSetor->inicio_atend;
$fimsetor = $oSetor->fim_atend;


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setSubTitulo("Registro de Horário de Trabalho");

$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


## validacao
##
#
$mensagem = null;

$oHoras = new CalculaHoras();

$oHoras->setJornada($jornada);            // Em horas diárias ou semanais (40hs ou 08:00hs)
$oHoras->setEntrada($entra);         // 8 horas, início do expediente
$oHoras->setIntervaloInicio($intini); // 12 horas começa o intervalo
$oHoras->setIntervaloFim($intsai);    // 13 horas termina o intervalo
$oHoras->setSaida($sai);           // 17 horas, fim do expediente
$oHoras->setCompensacao($bhoras);

$mensagem       = $oHoras->verificaHorarioDeTrabalho($exibe_mensagem = false);

if (is_string($mensagem) && $mensagem != null)
{
    mensagem($mensagem, $_SESSION['voltar_nivel_2']);
    exit;
}

/* ALTERAÇÃO CANCELADA
  $bhoras = ($bhtipo == "0" ? "N" : "S");
 */
$bhtipo = ($bhoras == "N" ? "0" : "1");

if ($sAutorizadoTE == 'N' || $ocupaFuncao == 'S')
{
    $oDBase->setMensagem("Problemas no acesso ao CADASTRO!");
    $oDBase->query("
    SELECT
        mat_siape, entra_trab, ini_interv, sai_interv, sai_trab, autchef,
        bhoras, bh_tipo
    FROM
        servativ
    WHERE
        cpf = :cpf
        AND mat_siape != :siape
        AND excluido = 'N'
        AND cod_sitcad NOT IN ('02','08','15')
    ", array(
        array(':cpf', $cpf, PDO::PARAM_STR),
        array(':siape', $tSiape, PDO::PARAM_STR),
    ));
    $lret       = $oDBase->num_rows();
    $oServidor2 = $oDBase->fetch_object();
    $Nsai       = $oServidor2->sai_trab;
    $Nentra     = $oServidor2->entra_trab;
    $Nsiape     = $oServidor2->mat_siape;
}
else
{
    $Nsai   = $sai;
    $Nentra = $entra;
}

if ($lret == 1 && (substr($tSiape,0,5) == substr($Nsiape,0,5)))
{
    if ($sai > $Nentra && $entra < $Nsai)
    {
        mensagem("Horário informado é incompatível com o horário da outra matrícula do servidor!", $_SESSION['voltar_nivel_2']);
        exit;
    }
}

$oDBase->setMensagem("Erro na gravação do histórico!");
$oDBase->query("INSERT INTO histcad (mat_siape, defvis, jornada, entra_trab, sai_trab, ini_interv, sai_interv, horae, processo, motivo, dthe, dthefim, autchef, bhoras, bh_tipo, dataalt, horaalt, siapealt, ipalt) (SELECT mat_siape, defvis, jornada, entra_trab, sai_trab, ini_interv, sai_interv, horae, processo, motivo, dthe, dthefim, autchef, bhoras, bh_tipo, :dia AS dataalt, :horas AS horaalt, :siape_operador AS siapealt, :ip AS ipalt FROM servativ WHERE mat_siape = :siape)
", array(
    array(':siape', $tSiape, PDO::PARAM_STR),
    array(':dia', $vDatas, PDO::PARAM_STR),
    array(':horas', $vHoras, PDO::PARAM_STR),
    array(':siape_operador', $sMatricula, PDO::PARAM_STR),
    array(':ip', $ip, PDO::PARAM_STR),
));

$oJornada = new DefinirJornada();
$oJornada->setSiape($tSiape);
$oJornada->setLotacao($lotacao);
$oJornada->setData($vDatas);
$oJornada->jornada = $jornada;

$oJornada->autorizado_te = $sAutorizadoTE;
$oJornada->chefiaAtiva   = $ocupaFuncao;

/* Verifica se o horario  registrado e maior ou menor que o da tabela de setores */
//$sautChef = (($entra < $inisetor) || ($sai > $fimsetor) ? 'S' : 'N');
$sautChef = $autchef;

$oDBase->setMensagem("Falha no registro do horário e/ou autorização da compensação");
if ($sAutorizadoTE == 'S' && $ocupaFuncao == 'N' && $situacao_cadastral != '66')
{
    $oDBase->query("UPDATE servativ SET autchef='" . $sautChef . "', bhoras='" . $bhoras . "', bh_tipo='" . $bhtipo . "', motivo='" . ($sAutorizadoTE == 'S' && $hmotivo != 0 ? '' : 'T') . "' WHERE mat_siape = '" . $tSiape . "' ");
}
else
{
    $oDBase->query("UPDATE servativ SET autchef='" . $sautChef . "', bhoras = '" . $bhoras . "', bh_tipo='" . $bhtipo . "', entra_trab='" . $entra . "', ini_interv='" . $intini . "', sai_interv='" . $intsai . "', sai_trab='" . $sai . "' WHERE mat_siape='" . $tSiape . "' ");
}

// grava horarios no jornada historico
$oJornada->data = $vDatas;
$oJornada->gravaHorario($entra, $intini, $intsai, $sai);

mensagem("Dados registrados com sucesso!<br><br>- Horários de Trabalho;<br>- Autorização de Compensação;<br>- Autorização de Registro Fora do Horário da Unidade.", $_SESSION['voltar_nivel_1']);

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
