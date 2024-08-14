<?php

include_once( "config.php" );

//verifica_permissao( "ver_ponto" );

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    // le dados passados por formulario
    $pSiape   = anti_injection($_REQUEST["pSiape"]);
    $mes      = anti_injection($_REQUEST["mes"]);
    $ano      = anti_injection($_REQUEST["ano"]);
    $sLotacao = anti_injection($_REQUEST["sLotacao"]);
    $cmd      = anti_injection($_REQUEST["cmd"]);
    $so_ver   = ($_REQUEST["so_ver"] == '' ? 'nao' : anti_injection($_REQUEST["so_ver"]));
}
else
{
    // Valores passados - encriptados
    $dados    = explode(":|:", base64_decode($dadosorigem));
    $pSiape   = $dados[0];
    $mes      = $dados[1];
    $ano      = $dados[2];
    $sLotacao = $dados[3];
    ;
    $cmd      = $dados[4];
    $so_ver   = $dados[5]; //($dados[5]==''?'nao':$dados[5]);
}

$sLotacao = (empty($sLotacao) ? $_SESSION["sLotacao"] : $sLotacao);
$magico   = $_SESSION["magico"];

switch ($cmd)
{
    case "2": $cmd = 1;
        break;
    case "3": $cmd = 3;
        break;
}

// dados para retorno a este script
$_SESSION['sPaginaRetorno_sucesso'] = (substr_count($_SESSION['sPaginaRetorno_sucesso'], 'regfreqgex.php') > 0 ? $_SESSION['sPaginaRetorno_sucesso'] : $_SERVER['REQUEST_URI']);

/* obtem dados dos servidores */
$oTbDados  = new DataBase('PDO');
$oTbDados->query("SELECT nome_serv, entra_trab, ini_interv, sai_interv, sai_trab, cod_lot, chefia, jornada FROM servativ WHERE mat_siape = '$pSiape' ");
$oServidor = $oTbDados->fetch_object();
$nome      = $oServidor->nome_serv;
$lot       = $oServidor->cod_lot;
$jnd       = $oServidor->jornada;
$chefe     = $oServidor->chefia;
$comp      = $mes . $ano;

/* obtem dados da upag para saber se é a mesma do usuario */
//
$oTbDados->query("SELECT upag FROM tabsetor WHERE codigo = '$lot' ");
$oSetor = $oTbDados->fetch_object();
$upg    = $oSetor->upag;

if ($cmd == "1")
{
    $qlotacao = anti_injection($_REQUEST["sLotacao"]);
}
else
{
    $qlotacao = $_SESSION["sLotacao"];
}

$qlotacao = (empty($qlotacao) ? $sLotacao : $qlotacao);

$bRecursosHumanos   = ($_SESSION['sRH'] == "S");
$bRecursosHumanosSR = ($_SESSION['sRH'] == "S" && substr($_SESSION['sLotacao'], 2, 6) == '150000');
$bDiretoria         = ($_SESSION["sCAD"] == "S");
$bGestoresSISREF    = ($_SESSION["sSenhaI"] == "S");
$bAuditoria         = ($_SESSION['sAudCons'] == 'S' || $_SESSION["sLog"] == "S");
$bSuperintendente   = ($_SESSION['sAPS'] == 'S' && substr($_SESSION['sLotacao'], 2, 6) == '150000');
$bGerenteExecutivo  = ($_SESSION['sAPS'] == 'S' && substr($_SESSION['sLotacao'], 2, 1) == '0' && substr($_SESSION['sLotacao'], 5, 3) == '000');

if ($bDiretoria == true || $bGestoresSISREF == true || $bAuditoria == true || $bSuperintendente == true || $bGerenteExecutivo == true)
{

}
elseif ($_SESSION['sAPS'] == "S" && $_SESSION['sRH'] == "N" && $lot != $qlotacao && $chefe == "N" && $magico < '3')
{
    //header("Location: mensagem.php?modo=24");
    //exit();
    mensagem("Não é permitido consultar/alterar servidor de outro setor!", "veponto.php", 1);
}
elseif ($_SESSION['sRH'] == "S" && $upg != $_SESSION['upag'])
{
    //header("Location: mensagem.php?modo=25");
    //exit();
    mensagem("Não é permitido consultar ponto de servidor de outra upag!", "veponto.php", 1);
}

trocaParametroREQUEST_URI("sLotacao", $qlotacao);
$_SESSION["sVePonto"]     = $_SERVER['REQUEST_URI'];
$caminho_modulo_utilizado = 'Frequência » Acompanhar/Homologar » Registro de comparecimento';

include_once( "veponto_formulario.php" );
