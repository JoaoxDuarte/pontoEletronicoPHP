<?php

// funcoes de uso geral
include_once( "config.php" );


verifica_permissao("gravar_frequencia");


// dados / parametros
$sLotacao = anti_injection($_SESSION["sLotacao"]);
$pSiape   = anti_injection($_REQUEST["pSiape"]);
$mes      = anti_injection($_REQUEST["mes"]);
$ano      = anti_injection($_REQUEST["ano"]);
//$magico = anti_injection($_SESSION["magico"]);
//$cmd = anti_injection($_REQUEST["cmd"]);

// instancia o banco de dados
$oDBase   = new DataBase('PDO');

/* obtem dados dos servidores */
$numRows = 0;

$pSiape = getNovaMatriculaBySiape($pSiape);


if ($_SESSION["sOUTRO"] == "S" || $_SESSION['sCAD'] == "S" || $_SESSION['sAudCons'] == "S" || $_SESSION['sLog'] == 'S')
{

    $oDBase->query("
    SELECT 
        a.nome_serv, 
        a.entra_trab, 
        a.ini_interv, 
        a.sai_interv, 
        a.sai_trab, 
        a.cod_lot, 
        a.chefia, 
        a.jornada,
        c.descricao   AS lot_descricao,
        b.denominacao AS orgao_denominacao,
        b.sigla       AS orgao_sigla
    FROM 
        servativ AS a
    LEFT JOIN 
        taborgao AS b ON LEFT(a.cod_lot,5) = b.codigo
    LEFT JOIN 
        tabsetor AS c ON a.cod_lot = c.codigo
    WHERE 
        a.mat_siape = :siape
    ",
    array(
        array( 'siape', $pSiape, PDO::PARAM_STR ),
    ));




    $numRows = $oDBase->num_rows();
}
else
{

    if ($_SESSION['sRH'] == 'S' || $_SESSION['sAPS'] == 'S')
    {
        $oDBase->query("
        SELECT 
            a.nome_serv, 
            a.entra_trab, 
            a.ini_interv, 
            a.sai_interv, 
            a.sai_trab, 
            a.cod_lot, 
            a.chefia, 
            a.jornada, 
            c.codmun,
            c.descricao   AS lot_descricao,
            b.denominacao AS orgao_denominacao,
            b.sigla       AS orgao_sigla 
        FROM 
            servativ AS a 
        LEFT JOIN 
            taborgao AS b ON LEFT(a.cod_lot,5) = b.codigo
        LEFT JOIN 
            tabsetor AS c ON a.cod_lot = c.codigo
        WHERE 
            a.mat_siape = :siape
            AND c.upag = :upag
        ",
        array(
            array( 'siape', $pSiape,           PDO::PARAM_STR ),
            array( 'upag',  $_SESSION['upag'], PDO::PARAM_STR ),
        ));
        $numRows = $oDBase->num_rows();
    }
}

if ($numRows == 0)
{
    mensagem("Informações do servidor não encontrada,\nou servidor pertence a outra UPAG!", null, 1);
}

$oServidor         = $oDBase->fetch_object();
$nome              = $oServidor->nome_serv;
$lot               = $oServidor->cod_lot;
$jnd               = $oServidor->jornada;
//$chefe             = $oServidor->chefia;
$codmun            = $oServidor->codmun;
$lot_descricao     = $oServidor->lot_descricao;
$orgao_denominacao = $oServidor->orgao_denominacao;
$orgao_sigla       = $oServidor->orgao_sigla;

$comp = $mes . $ano;

/* obtem dados do usuario */
$oDBase->query("
SELECT 
    a.entra, 
    DATE_FORMAT(a.dia, '%d/%m/%Y') AS dia, 
    a.intini, 
    a.intsai, 
    a.sai, 
    a.jornd, 
    a.jornp, 
    a.jorndif, 
    a.oco, 
    a.just, 
    a.idreg, 
    a.ip, 
    a.ip2, 
    a.ip3, 
    a.ip4, 
    a.ipch, 
    a.iprh,
    b.desc_ocorr,
    d.descricao   AS lot_descricao,
    c.denominacao AS orgao_denominacao,
    c.sigla       AS orgao_sigla
FROM 
    ponto" . $comp . " AS a 
LEFT JOIN 
    servativ AS cad ON a.siape = cad.mat_siape
LEFT JOIN 
    tabocfre AS b ON a.oco = b.siapecad
LEFT JOIN 
    taborgao AS c ON LEFT(cad.cod_lot,5) = c.codigo
LEFT JOIN 
    tabsetor AS d ON cad.cod_lot = d.codigo
WHERE 
    a.siape = :siape
ORDER BY 
    a.dia
",
array(
    array( 'siape', $pSiape, PDO::PARAM_STR ),
));
$nRows = $oDBase->num_rows();

## classe para montagem do formulario padrao
#
//$oForm = new formPadrao();
//$oForm->setCaminho('Utilitários » Auditoria » Identificar IP');
//$oForm->setCSS(_DIR_CSS_ . 'estilos.css');
//$oForm->setLargura("950px");
//$oForm->setSeparador(0);

//$oForm->setSubTitulo("IP de Registro de Frequência");

//$oForm->setIconeParaImpressao("pesquisa_ip_html_imp.php");

// Topo do formulário
//
$idInner = "";
//$idInner = $oForm->montaTopoHTML();
//$idInner .= $oForm->montaCorpoTopoHTML();


$idInner .= "
<div class='container'>
    <!-- Row Referente aos dados dos funcionários  -->
    <div class='row margin-10'>
        <div class='col-md-12' id='dados-funcionario'>
            <div class='col-md-3'>
                <h5><strong>SIAPE</strong></h5>
                <p>" . tratarHTML($pSiape) . "</p>
            </div>
            <div class='col-md-9'>
                <h5><strong>NOME</strong></h5>
                <p>" . tratarHTML($nome) . "</p>
            </div>
        </div>
    </div>
    <!-- Row Referente aos dados Setor do funcionario  -->
    <div class='row margin-10 comparecimento'>
        <div class='col-md-12' id='dados-funcionario'>
            <div class='col-md-3'>
                <h5><strong>ÓRGÃO</strong></h5>
                <p>" . substr($lot, 0, 5) . (empty($orgao_sigla) ? '' : ' -    ' . substr($orgao_sigla, 0, 11)) . "</p>
            </div>
            <div class='col-md-9'>
                <h5><strong>LOTAÇÃO</strong></h5>
                <p>" . substr($lot, 5) . ' - ' . substr($lot_descricao, 0, 62) . "</p>
            </div>
        </div>
    </div>

    <div class='row'>
        <div class='col-md-12' id='dados-funcionario'>
            <h3><strong>" . tratarHTML($mes) . "/" . tratarHTML($ano) . "</strong></h3>
        </div>
    </div>

    <div class='row'>
        <!-- Row Referente aos dados de horário de trabalho do funcionario  -->
	<table class='table table-striped table-condensed table-bordered text-center'>
            <thead>
		<tr>
			<th rowspan='2' class='text-center text-nowrap' style='vertical-align:middle;'>Dia</th>
			<th colspan='2' class='text-center text-nowrap' style='vertical-align:middle;'>Entrada</th>
			<th colspan='2' class='text-center text-nowrap' style='vertical-align:middle;'>Ida intervalo</th>
			<th colspan='2' class='text-center text-nowrap' style='vertical-align:middle;'>Volta Intervalo</th>
			<th colspan='2' class='text-center text-nowrap' style='vertical-align:middle;'>Saida</th>
			<th rowspan='2' class='text-center'             style='vertical-align:middle;'>Ocorrência</th>
			<th rowspan='2' class='text-center'             style='vertical-align:middle;'>Registrado<br>Por</th>
			<th rowspan='2' class='text-center text-nowrap' style='vertical-align:middle;'>IP Chefe</th>
			<th rowspan='2' class='text-center text-nowrap' style='vertical-align:middle;'>IP RH</th>
		</tr>
		<tr>
			<th class='text-center text-nowrap' style='vertical-align:middle;'>Horário</th>
			<th class='text-center text-nowrap' style='vertical-align:middle;'>IP</td>
			<th class='text-center text-nowrap' style='vertical-align:middle;'>Horário</th>
			<th class='text-center text-nowrap' style='vertical-align:middle;'>IP</td>
			<th class='text-center text-nowrap' style='vertical-align:middle;'>Horário</th>
			<th class='text-center text-nowrap' style='vertical-align:middle;'>IP</td>
			<th class='text-center text-nowrap' style='vertical-align:middle;'>Horário</th>
			<th class='text-center text-nowrap' style='vertical-align:middle;'>IP</th>
		</tr>
            </thead>
            <tbody>
";

if ($nRows == 0)
{
    $idInner .= "
    <tr>
        <td colspan='13'><div align='center'><font size='1'>Sem registro para exibir</font></div></td>
    </tr>
    ";
}
else
{
    $umavez      = true;
    
    while ($pm_partners = $oDBase->fetch_object())
    {
        if ($umavez == true)
        {
            $umavez       = false;
            $dia_nao_util = marca_dias_nao_util($mes, $ano, $codmun, $lot);
        }
        $xdia       = $pm_partners->dia;
        $background = $dia_nao_util[$xdia][0];
        $color      = $dia_nao_util[$xdia][1];

        $registrou_descricao = define_quem_registrou_descricao($pm_partners);
        
        $idInner .= "
        <tr style='" . $background . "'>
            <td style='" . $color . "' title='" . tratarHTML($dia_nao_util[$xdia][4]) . "' class='text-center text-nowrap'>" . trim($dia_nao_util[$xdia][2]) . "&nbsp;" . $xdia . $dia_nao_util[$xdia][3] . "</td>
            <td class='text-center'>"    . tratarHTML($pm_partners->entra) . "</td>
            <td class='text-center'>"    . tratarHTML($pm_partners->ip) . "</td>
            <td class='text-center'>"    . tratarHTML($pm_partners->intini) . "</td>
            <td class='text-center'>"    . tratarHTML($pm_partners->ip2) . "</td>
            <td class='text-center'>"    . tratarHTML($pm_partners->intsai) . "</td>
            <td class='text-center'>"    . tratarHTML($pm_partners->ip3) . "</td>
            <td class='text-center'>"    . tratarHTML($pm_partners->sai) . "</td>
            <td class='text-center'>"    . tratarHTML($pm_partners->ip4) . "</td>
            <td class='text-center' alt='" . tratarHTML($pm_partners->desc_ocorr) . "' title='" . tratarHTML($pm_partners->desc_ocorr) . "'><b>" . tratarHTML($pm_partners->oco) . "</b></td>
            <td class='text-left'>"      . tratarHTML($registrou_descricao) . "</td>
            <td class='text-center'>"    . tratarHTML($pm_partners->ipch) . "</td>
            <td class='text-center'>"    . (empty($pm_partners->iprh) ? "&nbsp;" : tratarHTML($pm_partners->iprh)) . "</td>
        </tr>
        ";
    } // fim do while
}

$idInner .= "
            </tbody>
	</table>
    </div>
</div>

<div class='form-group text-center'>
    <div class='col-md-5'>&nbsp;</div>
    <div class='col-md-2'>
        <a class='btn btn-danger btn-block' href='javascript:window.location.replace(\"pesquisa_ip.php\");' role='button'>
            <span class='glyphicon glyphicon-arrow-left'></span> Voltar
        </a>
    </div>
    <div class='col-md-5'>&nbsp;</div>
</div>
<br>
<br>
";

print $idInner;

// Base do formulário
//
//$idInner .= $oForm->montaCorpoBaseHTML();
//$idInner .= $oForm->montaBaseHTML();

$_SESSION['sIMPFormFrequencia'] = $idInner;
