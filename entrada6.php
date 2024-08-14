<?php

include_once("config.php");

verifica_permissao("logado");

$caminho_modulo_utilizado = 'Registro de Frequência » Visualizar frequência do mês';

$cmd     = ($_REQUEST['cmd'] == '' ? $_SESSION['entrada1_cmd_2'] : anti_injection($_REQUEST['cmd']));
$orig    = ($_REQUEST['orig'] == '' ? $_SESSION['orig'] : anti_injection($_REQUEST['orig']));
$lotacao = ($_REQUEST['lotacao'] == '' ? $_SESSION['lotacao'] : anti_injection($_REQUEST['lotacao']));

if ($cmd == "1")
{
    $_REQUEST["pSiape"] = $_SESSION['sMatricula'];
    $_REQUEST["mes3"]   = date('m');
    $_REQUEST["ano3"]   = date('Y');
}
elseif ($cmd == "2")
{
    $_REQUEST["pSiape"] = ($_REQUEST["pSiape"] == "" ? $_SESSION['sMatricula'] : anti_injection($_REQUEST["pSiape"]));
    $_REQUEST["mes3"]   = ($_REQUEST["mes3"] == "" ? $_REQUEST['mes'] : anti_injection($_REQUEST["mes3"]));
    $_REQUEST["ano3"]   = ($_REQUEST["ano3"] == "" ? $_REQUEST['ano'] : anti_injection($_REQUEST["ano3"]));
}

$pagina_de_origem = pagina_de_origem();

if (in_array($pagina_de_origem, array('entrada1.php', 'gravaregfreq2.php', 'entrada8.php', 'regjust.php', 'gravahorario.php')))
{
    include_once("veponto_formulario_regponto.php");
}
else
{
    include_once("veponto_formulario.php");
}

DataBase::fechaConexao();

if (pagina_de_origem() == 'gravaregfreq2.php')
{
    $registrar_justificativa             = $_SESSION['registrar_justificativa']; // Indica se o usuário pode registrar justificativa
    destroi_sessao();
    $_SESSION['registrar_justificativa'] = $registrar_justificativa;
}
