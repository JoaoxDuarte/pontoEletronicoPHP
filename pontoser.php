<?php

include_once( "config.php" );

verifica_permissao("logado");

$cmd = anti_injection($_REQUEST['cmd']);

if (empty($siape) && is_array($_SESSION['sDadosParaVerComprovante']) && empty($cmd))
{
    $siape = getNovaMatriculaBySiape( $_SESSION['sDadosParaVerComprovante']['siape'] );
    $mes   = $_SESSION['sDadosParaVerComprovante']['mes'];
    $ano   = $_SESSION['sDadosParaVerComprovante']['ano'];

    unset($_SESSION['sDadosParaVerComprovante']);
    $_SESSION['sDadosParaVerComprovante'] = NULL;
}
else
{
    $basename = explode("?", basename($_SERVER['HTTP_REFERER']));

    switch ($basename[0])
    {
        case 'gestao_veponto.php':
            $_SESSION['registrar_justificativa'] = false;
        case 'veponto.php':
            $siape = filter_input(INPUT_POST, 'pSiape', FILTER_SANITIZE_STRING);
            $mes   = filter_input(INPUT_POST, 'mes3', FILTER_SANITIZE_STRING);
            $ano   = filter_input(INPUT_POST, 'ano3', FILTER_SANITIZE_STRING);
            break;

        case 'entrada8.php':
            $siape = $_SESSION['sMatricula'];
            $mes   = filter_input(INPUT_POST, 'mes', FILTER_SANITIZE_STRING);
            $ano   = filter_input(INPUT_POST, 'ano', FILTER_SANITIZE_STRING);
            break;

        case 'gravahorario.php':
        case 'regjust.php':
            $_SESSION['registrar_justificativa'] = true;
            $siape = $_SESSION['sMatricula'];
            $mes   = $_SESSION['sDadosParaVerComprovante']['mes'];
            $ano   = $_SESSION['sDadosParaVerComprovante']['ano'];
            break;

        case 'entrada1.php':
            $_SESSION['registrar_justificativa'] = true;
        default:
            $siape = $_SESSION['sMatricula'];
            $mes   = date('m');
            $ano   = date('Y');
            $cmd   = 1;
            break;
    }

    $_SESSION['sDadosParaVerComprovante']= array(
        'siape' => $siape,
        'mes'   => $mes,
        'ano'   => $ano,
    );
}

$siape = getNovaMatriculaBySiape($siape);

$_SESSION["sVePonto"] = $_SERVER['PHP_SELF'];

// historico de navegacao (substituir o $_SESSION['sPaginaRetorno_sucesso']
$sessao_navegacao = new Control_Navegacao();
$sessao_navegacao->initSessaoNavegacao();
$sessao_navegacao->setPagina($_SERVER['REQUEST_URI']);

include_once( "veponto_formulario_servidor.php" );

DataBase::fechaConexao();


if (pagina_de_origem() == 'gravaregfreq2.php')
{
    // Indica se o usuário pode registrar justificativa
    $registrar_justificativa             = $_SESSION['registrar_justificativa'];
    destroi_sessao();
    $_SESSION['registrar_justificativa'] = $registrar_justificativa;
}
