<?php

include( "../config.php" );

/* Inicia a sessão */
session_start();

$_DURACAO_DA_SESSAO_EM_MINUTOS_ = getDuracaoDaSessaoEmMinutos();

switch ($_SESSION['sModuloPrincipalAcionado'])
{
    case "rh":
    case "sogp":
        $tela_login_sistema             = "rh.php";
        break;

    case "chefia":
        $tela_login_sistema             = "chefia.php";
        break;

    default:
        $tela_login_sistema             = "entrada.php";
        break;
}

$time = time();

if (isset($_POST['contar']) && $_POST['contar'] == 'sim')
{
    // Primeira visita
    if(  !isset($_SESSION['tempolimite']) )
    {
        $_SESSION['tempolimite'] = $time + 60 * $_DURACAO_DA_SESSAO_EM_MINUTOS_;
    }
}

$temporestante = $_SESSION['tempolimite'] - $time;
$hora          = sec_to_time(abs($temporestante),'mm:ss');

// Verifica se jah esgotou o prazo
if ($time > $_SESSION['tempolimite'])
{
    destroi_sessao();
}

echo json_encode(array("hora" => $hora, "tipo" => "warning", "destino" => $tela_login_sistema));

