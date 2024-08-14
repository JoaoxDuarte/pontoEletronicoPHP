<?php
include_once("config.php");

$lSiape = anti_injection($_POST["lSiape"]);
$lSenha = anti_injection($_POST["lSenha"]);
$lSenha = substr(md5($lSenha), 0, 14);

// cria uma sessao
session_start();

// instancia BD
$oDBase = new DataBase('PDO');

// Texto digitado no campo imagem, e transformando tudo em mínúsculo, caso queria que haja distinção de maiúsculas e minúsculas, só retire o strtoupper().
$txtImagem = strtoupper($_POST["txtImagem"]);

// Caracteres que estão na imagem, também deixando tudo em minúsulo.
$valorImagem = strtoupper($_SESSION["autenticaIMG"]);

// Verificando se o texto digitado, for igual aos caracteres que estão na imagem
$sql = "
	SELECT
		cad.defvis, usu.prazo, usu.magico, cad.nome_serv AS nome, usu.senha,
		usu.privilegio, cad.cod_lot AS setor, und.upag, usu.acesso,
		usu.siape, und.regional
	FROM usuarios AS usu
	LEFT JOIN servativ AS cad ON usu.siape = cad.mat_siape
	LEFT JOIN tabsetor AS und ON cad.cod_lot = und.codigo
	WHERE siape='$lSiape' AND senha='$lSenha'
	ORDER BY usu.siape ";

//$sql = "SELECT * FROM usuarios WHERE siape='$lSiape' AND senha='$lSenha'";
$result     = $oDBase->query($sql);
$numrows    = $oDBase->num_rows();
$tbusuarios = $oDBase->fetch_array();
$defvis     = $tbusuarios["defvis"];
$prazo      = $tbusuarios["prazo"];
$magico     = $tbusuarios["magico"];

$sMatricula  = $lSiape;
$sNome       = $tbusuarios["nome"];
$sSenha      = $tbusuarios["senha"];
$sPrivilegio = $tbusuarios["privilegio"];
$sLotacao    = $tbusuarios["setor"];
$upag        = $tbusuarios["upag"];
$sTripa      = $tbusuarios["acesso"];

$sRegional = $tbusuarios["regional"];

// testa se encontrou
if ($numrows >= 1 && $txtImagem == $valorImagem && !empty($txtImagem) || $numrows >= 1 && $defvis == "S" && empty($txtImagem)) // é da casa
{
    // elimina resquicios da sessao anterior
    session_unset();

    // pesquisa qual o mes ativo
    $oDBase->query("SELECT id, compi, compf, DATE_FORMAT(rhi, '%Y%m%d') AS rhi, DATE_FORMAT(rhf, '%Y%m%d') AS rhf, DATE_FORMAT(apsi, '%Y%m%d') AS apsi, DATE_FORMAT(apsf, '%Y%m%d') AS apsf, date_format(gbnini, '%Y%m%d') AS gbnini, DATE_FORMAT(gbninf, '%Y%m%d') AS gbninf, DATE_FORMAT(outchei, '%Y%m%d') AS outchei, DATE_FORMAT(outchef, '%Y%m%d') AS outchef, DATE_FORMAT(rmi, '%Y%m%d') AS rmi, DATE_FORMAT(rmf, '%Y%m%d') AS rmf, DATE_FORMAT(cadi, '%Y%m%d') AS cadi, DATE_FORMAT(cadf, '%Y%m%d') AS cadf, hveraoi, hveraof, ativo, qcinzas FROM tabvalida USE INDEX (comp) WHERE ativo='S' ");

    $tbvalida = $oDBase->fetch_array();

    $sMesi    = $tbvalida["compi"];
    $sMesf    = $tbvalida["compf"];
    $sRhi     = $tbvalida["rhi"];
    $sRhf     = $tbvalida["rhf"];
    $sApsi    = $tbvalida["apsi"];
    $sApsf    = $tbvalida["apsf"];
    $sGbnini  = $tbvalida["gbnini"];
    $sGbninf  = $tbvalida["gbninf"];
    $sOutchei = $tbvalida["outchei"];
    $sOutchef = $tbvalida["outchef"];
    $sRmi     = $tbvalida["rmi"];
    $sRmf     = $tbvalida["rmf"];
    $sCadi    = $tbvalida["cadi"];
    $sCadf    = $tbvalida["cadf"];
    $iniver   = $tbvalida["hveraoi"];
    $fimver   = $tbvalida["hveraof"];
    $qcinzas  = $tbvalida["qcinzas"];

    // fim do Tabvalida
    // guarda nas variaveis o perfil do usuario
    $sRH             = substr($sTripa, 0, 1);
    $sAPS            = substr($sTripa, 1, 1);
    $sGBNIN          = substr($sTripa, 2, 1);
    $sOUTRO          = substr($sTripa, 3, 1);
    $sMEDICO         = substr($sTripa, 4, 1);
    $sCAD            = substr($sTripa, 5, 1);
    $sRelRH          = substr($sTripa, 6, 1);
    $sRelGer         = substr($sTripa, 7, 1);
    $sTabPrazo       = substr($sTripa, 8, 1);
    $sTabServidor    = substr($sTripa, 9, 1);
    $sSenhaI         = substr($sTripa, 10, 1);
    $sLog            = substr($sTripa, 11, 1);
    $sAudCons        = substr($sTripa, 12, 1);
    $sSIC            = substr($sTripa, 13, 1);
    $sBrasil         = substr($sTripa, 14, 1);
    $sUF             = substr($sTripa, 15, 1);
    $sGEX            = substr($sTripa, 16, 1);
    $sSR             = substr($sTripa, 17, 1);
    $sEstrategica    = substr($sTripa, 18, 1);
    $sLancarExcessao = substr($sTripa, 19, 1);

    // fim guarda nas variaveis o perfil do usuario
    $logado = "SIM";

    // cria a seção do usuario e identifica suas permissões
    $_SESSION['sMatricula'] = $sMatricula;

    //session_register("sMatricula");
    $_SESSION['logado']   = $logado;
    $_SESSION['sNome']    = $sNome;
    $_SESSION['sSenha']   = $sSenha;
    $_SESSION['sLotacao'] = $sLotacao;
    $_SESSION['upag']     = $upag;
    $_SESSION['regional'] = $sRegional;

    $_SESSION['sRH']             = $sRH;
    $_SESSION['sAPS']            = $sAPS;
    $_SESSION['sGBNIN']          = $sGBNIN;
    $_SESSION['sOUTRO']          = $sOUTRO;
    $_SESSION['sMEDICO']         = $sMEDICO;
    $_SESSION['sCAD']            = $sCAD;
    $_SESSION['sRelRH']          = $sRelRH;
    $_SESSION['sRelGer']         = $sRelGer;
    $_SESSION['sTabPrazo']       = $sTabPrazo;
    $_SESSION['sTabServidor']    = $sTabServidor;
    $_SESSION['sSenhaI']         = $sSenhaI;
    $_SESSION['sLog']            = $sLog;
    $_SESSION['sAudCons']        = $sAudCons;
    $_SESSION['sSIC']            = $sSIC;
    $_SESSION['sBrasil']         = $sBrasil;
    $_SESSION['sUF']             = $sUF;
    $_SESSION['sGEX']            = $sGEX;
    $_SESSION['sSR']             = $sSR;
    $_SESSION['sEstrategica']    = $sEstrategica;
    $_SESSION['sLancarExcessao'] = $sLancarExcessao;

    $_SESSION['sMesi']    = $sMes;
    $_SESSION['sMesf']    = $sMesf;
    $_SESSION['sRhi']     = $sRh;
    $_SESSION['sRhf']     = $sRhf;
    $_SESSION['sApsi']    = $sApsi;
    $_SESSION['sApsf']    = $sApsf;
    $_SESSION['sGbnini']  = $sGbnin;
    $_SESSION['sGbninf']  = $sGbninf;
    $_SESSION['sOutchei'] = $sOutche;
    $_SESSION['sOutchef'] = $sOutchef;
    $_SESSION['sRmi']     = $sRm;
    $_SESSION['sRmf']     = $sRmf;
    $_SESSION['sCadi']    = $sCadi;
    $_SESSION['sCadf']    = $sCadf;
    $_SESSION['magico']   = $magico;
    $_SESSION['iniver']   = $iniver;
    $_SESSION['iniver']   = $fimver;
    $_SESSION['qcinzas']  = $qcinzas;

    // fim da criacao de sessao
    //  '': Administração Central
    // '1': SR Sudeste I
    // '2': SR Sudeste II
    // '3': SR Sul
    // '4': SR Nordeste
    // '5': SR Norte Centro-Oeste
    $liberado = '';

    $oDBase->query("SELECT regional, gerar_gravacao FROM monitor.monitor_autorizado WHERE siape = '" . $_SESSION['sMatricula'] . "' AND autorizado = 'S' ORDER BY regional, siape");
    $nLins = $oDBase->num_rows();

    if ($nLins > 0)
    {
        list( $liberado, $gravar ) = $oDBase->fetch_array();
    }

    $_SESSION['liberado_acesso'] = $liberado;
    $_SESSION['liberado_gravar'] = $gravar;

    if ($liberado == '')
    {
        mensagem('Acesso não autorizado!', 'relatorio_paralisacoes_login.php');
        die();
    }
    elseif ($liberado == '0' && $gravar == 'S')
    {
        replaceLink('relatorio_paralisacoes_opcoes.php');
    }

    replaceLink('relatorio_paralisacoes_quadro.php');
}
else
{

    mensagem('Usuário Não Identificado!', 'relatorio_paralisacoes_login.php');
}
?>
<script>
    top.document.title = 'SISREF - Sistema de Registro Eletrônico de Frequência | <?= date("d/m/Y"); ?> | Usuário: <?= tratarHTML($sMatricula) . " " . tratarHTML($sNome); ?>';
    document.status = '';
</script>
