<?php

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Pragma: no-cache"); // HTTP/1.0

include_once("config.php");


if($_SESSION['SIGAC_LOGIN']) {

    $cpf = $_SESSION['SIGAC_CPF_SERVIDOR'];

    $result_sigac = existsServerByCpf($cpf);

    if($result_sigac){
        $_POST['lSiape'] = $result_sigac->siape;
        $_POST['txtImagem'] = $_SESSION['autenticaIMG'];
        $_POST['enviar'] = '';
    }
}

$path_parts       = pathinfo($_SERVER['HTTP_REFERER']);
$pagina_de_origem = $path_parts['basename'];


// dados enviados por formulario
$sCPF = limpaCPF_CNPJ(anti_injection($_POST['lSiape']));


// CASO O LOGIN SEJA VIA SIGAC
if(!$_SESSION['SIGAC_LOGIN']) {
    $lSenha = anti_injection($_POST["lSenha"]);
    $lSenha = substr(md5($lSenha), 0, 14);
} else {
    $lSenha = $result_sigac->senha;
}

$formSenha = $lSenha;
$ModuloPrincipalAcionado = $_SESSION['sModuloPrincipalAcionado'];

// Texto digitado no campo imagem, e transformando em mínúsculo.
// - Para haver distinção entre maiúsculas e minúsculas, retire o
//   strtoupper().
$txtImagem = strtoupper($_POST["txtImagem"]);

// Caracteres que estão na imagem,
// também deixando tudo em minúsculo.
$valorImagem = strtoupper($_SESSION["autenticaIMG"]);

// instancia o BD
$oDBase = new DataBase('PDO');

// Verificando se o texto digitado,
// é igual aos caracteres que estão na imagem
//
// Verifica se o usuário existe
// e suas permissões
//
// $oDBase : será utilizado em todo o script
//$sql = "SELECT * FROM usuarios WHERE siape='$lSiape' AND senha='$lSenha'";
$oDBase->setMensagem("Problemas no acesso ao arquivo de autenticação!");
$oDBase->setDestino('principal.php');

$oDBase->query("
SELECT
    cad.mat_siape AS siape,
    cad.nome_serv AS nome,
    cad.identificacao_apelido,
    cad.cod_uorg AS uorg,
    cad.cod_lot AS setor,
    und.upag,
    usu.acesso,
    usu.senha,
    cad.defvis,
    usu.prazo,
    usu.magico,
    usu.privilegio,
    und.regional,
    und.descricao,
    CONCAT('GERÊNCIA EXECUTIVA ',UPPER(gex.nome_gex)) AS gerencia,
    UPPER(ger.nome_ger) AS superintendencia,
    IFNULL(usu.datapt,'0000-00-00') AS delegacao_inicio,
    IFNULL(usu.dtfim,'0000-00-00') AS delegacao_fim,
    taborgao.codigo AS orgao
FROM
    usuarios AS usu
LEFT JOIN
    servativ AS cad ON usu.siape = cad.mat_siape
LEFT JOIN
    tabsetor AS und ON cad.cod_lot = und.codigo
LEFT JOIN
    tabsetor_gex AS gex ON CONCAT(SUBSTR(cad.cod_lot,1,2),'0',SUBSTR(cad.cod_lot,4,2)) = gex.cod_gex
LEFT JOIN
    tabsetor_ger AS ger ON und.regional = ger.id_ger
LEFT JOIN
    taborgao ON LEFT(und.codigo,5) = taborgao.codigo
LEFT JOIN 
    tabsitcad_prioridade_registro AS prior ON cad.cod_sitcad = prior.cod_sitcad
WHERE
    cad.cpf = :cpf
    AND cad.cod_sitcad NOT IN ('02','08','15')
ORDER BY
    IF(ISNULL(prior.cod_sitcad),2,1)
LIMIT 1
", array(
    array(':cpf', $sCPF, PDO::PARAM_STR),
));

$numrows = $oDBase->num_rows();

$oUsuarios         = $oDBase->fetch_object();
$deficiente_visual = $oUsuarios->defvis;
$troca_senha       = $oUsuarios->prazo;
$magico            = $oUsuarios->magico;

$lSiape                = $oUsuarios->siape;
$sNome                 = $oUsuarios->nome;
$identificacao_apelido = $oUsuarios->identificacao_apelido; // identificacao ou apelido
$sSenha                = $oUsuarios->senha;
$sPrivilegio           = $oUsuarios->privilegio;
$sLotacao              = $oUsuarios->setor;
$sLotacaoDescr         = $oUsuarios->descricao;
$sGerencia             = $oUsuarios->gerencia;
$sSuperintendencia     = $oUsuarios->superintendencia;
$orgao                 = $oUsuarios->orgao;
$uorg                  = $oUsuarios->uorg;
$upag                  = $oUsuarios->upag;
$regional              = $oUsuarios->regional;
$sTripa                = $oUsuarios->acesso;
$sMatricula            = getNovaMatriculaBySiape( $lSiape );

$delegacao_inicio = $oUsuarios->delegacao_inicio;
$delegacao_fim    = $oUsuarios->delegacao_fim;

// testa se encontrou
//if ($numrows>=1 && $txtImagem == $valorImagem && !empty($txtImagem) || $numrows>=1 && $deficiente_visual == "S" && empty($txtImagem)) // é da casa
//{
if (empty($numrows))
{
    $oDBase->free_result();
    $oDBase->close();
    retornaErro('principal.php', 'Usuário inválido!', false);
    exit();
}

if ($formSenha !== $oUsuarios->senha)
{
    $oDBase->free_result();
    $oDBase->close();
    retornaErro('principal.php', 'Senha inválida!', false);
    exit();
}

if ($deficiente_visual != "S" && ($txtImagem != $valorImagem || empty($txtImagem)))
{
    $oDBase->free_result();
    $oDBase->close();
    retornaErro('principal.php', 'Captcha inválido!', false);
    exit();
}


if($_SESSION['SIGAC_LOGIN']) {
    $sigac = $_SESSION['SIGAC_LOGIN'];
    $sigac_token_access = $_SESSION['SIGAC_TOKEN_ACCESS'];
    $sigac_cpf_servidor = $_SESSION['SIGAC_CPF_SERVIDOR'];
}

// é da casa
// elimina resquicios da sessao anterior
session_unset();


$_SESSION['registro_inicio_sessao']   = time(); // armazena o momento em que autenticou
$_SESSION['sModuloPrincipalAcionado'] = $ModuloPrincipalAcionado;

if(!empty($sigac)){
    $_SESSION['SIGAC_LOGIN'] = $sigac;
    $_SESSION['SIGAC_TOKEN_ACCESS'] = $sigac_token_access;
    $_SESSION['SIGAC_CPF_SERVIDOR'] = $sigac_cpf_servidor;
}

// pesquisa qual o mes ativo
// $oDBase : definido na linha 48
$oDBase->setMensagem("Problemas no acesso ao CRONOGRAMA!");
$oDBase->query("
SELECT
    id, compi, compf, date_format(rhi, '%Y%m%d') as rhi,
    date_format(rhf, '%Y%m%d') as rhf, date_format(apsi, '%Y%m%d') as apsi,
    date_format(apsf, '%Y%m%d') as apsf,
    date_format(gbnini, '%Y%m%d') as gbnini,
    date_format(gbninf, '%Y%m%d') as gbninf,
    date_format(outchei, '%Y%m%d') as outchei,
    date_format(outchef, '%Y%m%d') as outchef,
    date_format(rmi, '%Y%m%d') as rmi,
    date_format(rmf, '%Y%m%d') as rmf, date_format(cadi, '%Y%m%d') as cadi,
    date_format(cadf, '%Y%m%d') as cadf, hveraoi, hveraof, ativo, qcinzas
FROM
    tabvalida
WHERE
    ativo='S'
");

$oValida  = $oDBase->fetch_object();
$sMesi    = $oValida->compi;
$sMesf    = $oValida->compf;
$sRhi     = $oValida->rhi;
$sRhf     = $oValida->rhf;
$sApsi    = $oValida->apsi;
$sApsf    = $oValida->apsf;
$sGbnini  = $oValida->gbnini;
$sGbninf  = $oValida->gbninf;
$sOutchei = $oValida->outchei;
$sOutchef = $oValida->outchef;
$sRmi     = $oValida->rmi;
$sRmf     = $oValida->rmf;
$sCadi    = $oValida->cadi;
$sCadf    = $oValida->cadf;
$iniver   = $oValida->hveraoi;
$fimver   = $oValida->hveraof;
$qcinzas  = $oValida->qcinzas;

// fim do Tabvalida
// fim guarda nas variaveis o perfil do usuario
$logado = "SIM";

// Modulos, permissoes, valor
$modulos              = array();
$modulos              = select_permissoes();
$_SESSION['sModulos'] = $modulos;

// guarda em sessao o perfil do usuario
include_once( "usuario_varsession.php" );

// informar tipo da origem
// se rh.php, chefia.php ou entrada.php
$_SESSION['sHOrigem_1'] = "principal.php";

// fim da criacao de sessao
// força mudar a senha
if ($troca_senha == '1')
{
    header("Location: trocasenha_rh.php");
    die();
}


##
# verifica se o usuario está como substituto.
# se o período expirou cancela a permissao
# para atuar como chefe da unidade
#
trata_substituicao($sMatricula);

///////////////////////////////////////////////////////
// verifica se há registros para homologação INIBIDO FACE PREVISÃO DE ALTERAÇÃO DA ROTINA DE REGISTRO DE HOMOLOGAÇÕES
//$oDBase = seleciona_servidores( $link, $sLotacao );
//if ($oDBase->num_rows() > 0 && $_SESSION['sAPS'] == "S")
//{
//	mensagem("Ainda há servidor(es) sem homologação.\\n Por favor, realize a(s) homologação(ões).");
//}

// grava o LOG
registraLog(" logou-se ao sistema (RH/Chefia)"); //, $_SESSION['sMatricula'], $_SESSION['sNome'] );

// redireciona para a tela de abertura
replaceLink("principal_abertura.php");

setTituloApl($sMatricula, $sNome);

$oDBase->free_result();
$oDBase->close();
