<?php
// funcoes de uso geral
include_once( "config.php" );

// Verifica se o usuário tem a permissão para acessar este módulo
// RH, Auditoria
verifica_permissao("relatorio_ocorrencia");

// dados
$upagau   = $_REQUEST['upag'];
$upgrh    = $_SESSION['upag'];
$sLotacao = $_SESSION['sLotacao'];

// código da upag
$upg = ($_SESSION['sOUTRO'] == "S" ? $upagau : $upgrh);

// instancia o banco de dados
$oDBase = new DataBase('PDO');
$oDBase->setMensagem("Erro na consulta");

// seleção de nome da SOGP
$oDBase->query("
	SELECT
		UPPER(ger.nome_ger) AS nome_ger,
		UPPER(gex.cod_gex) AS cod_gex,
		UPPER(CONCAT(IF(SUBSTR(und.codigo,3,3)<>'150' AND LEFT(und.codigo,2)<>'01',CONVERT('GERÊNCIA EXECUTIVA ' USING latin1),''),gex.nome_gex)) AS nome_gex,
		und.codigo,
		UPPER(und.descricao) AS descricao
	FROM servativ AS cad
	LEFT JOIN tabsetor AS und ON cad.cod_lot = und.codigo
	LEFT JOIN tabsetor_gex AS gex ON
		IF(SUBSTR(und.codigo,4,2)='00',CONCAT(LEFT(und.codigo,2),'001'), IF(SUBSTR(und.codigo,3,3)='150',LEFT(und.codigo,5),CONCAT(LEFT(und.codigo,2),'0',SUBSTR(und.codigo,4,2)) )) = gex.cod_gex
	LEFT JOIN tabsetor_ger AS ger ON gex.regional = ger.id_ger
	WHERE und.cod_uorg = '$upg'
	ORDER BY und.cod_uorg
	LIMIT 1
	");

$oSetor = $oDBase->fetch_object();
$sogp   = $oSetor->descricao;
$gex    = $oSetor->nome_gex;
$sr     = $oSetor->nome_ger;

// seleção de setores pendentes de homologação
$oDBase->query("
	SELECT
		hom.compet, ger.cod_ger, ger.nome_ger, gex.cod_gex, gex.nome_gex,
		und.codigo, und.descricao, und.upag, COUNT(*) AS pedentes
	FROM servativ AS cad
	LEFT JOIN homologados AS hom ON cad.mat_siape = hom.mat_siape
	LEFT JOIN tabsetor AS und ON cad.cod_lot = und.codigo
	LEFT JOIN tabsetor_gex AS gex ON
		IF(SUBSTR(und.codigo,4,2)='00',CONCAT(LEFT(und.codigo,2),'001'),
			IF(SUBSTR(und.codigo,3,3)='150',LEFT(und.codigo,5),CONCAT(LEFT(und.codigo,2),'0',SUBSTR(und.codigo,4,2))
		)) = gex.cod_gex
	LEFT JOIN tabsetor_ger AS ger ON gex.regional = ger.id_ger
	WHERE
		und.ativo = 'S'
		AND und.tfreq = 'N'
		AND hom.homologado = 'N'
		AND und.upag = '$upg'
		AND hom.compet = DATE_FORMAT(DATE_ADD(NOW(), INTERVAL -1 MONTH),'%Y%m')
	GROUP BY und.codigo, hom.compet
	ORDER BY ger.id_ger,IF(LEFT(cad.cod_lot,2)='01',1,IF(SUBSTR(cad.cod_lot,3,3)='150',2,3)),und.codigo, hom.compet DESC
	");
$num_rows = $oDBase->num_rows();

//definindo a competencia de homologacao
$oData = new trata_datasys;
$ano   = $oData->getAnoHomologacao();
$comp  = $oData->getMesHomologacao();


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Relatório » Frequência » Setores Pendentes');
$oForm->setCSS(_DIR_CSS_ . "estiloIE.css");
$oForm->setCSS("
	<style>
		.setores { text-align:center; font-weight:bold; padding:7px 0px 7px 0px; }
		.sem-borda { border:0px solid white; }
		.ftTahoma12px { font-size:12px; font-family:Tahoma; }
	</style>
	");
$oForm->setLargura("950px");
$oForm->setSeparador(0);

$oForm->setSubTitulo("Relatório de Setores Pendentes de Homologa&ccedil;&atilde;o");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#808040" width="100%" id="AutoNumber1">
    <tr>
        <td width="100%"  align='center' class="ftTahoma12px">
            M&ecirc;s
            <input type="text" id="mes" name="mes" class='alinhadoAoCentro' value='<?= tratarHTML($comp); ?>' size="7" readonly>
            Ano
            <input type="text" id="ano" name="ano" class='alinhadoAoCentro' value='<?= tratarHTML($ano); ?>' size="7" readonly>
        </td>
    </tr>
    <tr>
        <td class="ftTahoma12px setores">
            <?= tratarHTML($sr); ?><br>
            <?= tratarHTML($gex); ?><br>
            <?= tratarHTML($sogp); ?><br>
        </td>
    </tr>
</table>
<table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" width="100%" id="AutoNumber2" >
    <tr bgcolor="#DBDBB7">
        <td width="6%" align='center'><b>Seq.</td>
        <td width="10%" align='center'><b>Lota&ccedil;&atilde;o</td>
        <td width="71%"><b>&nbsp;Descri&ccedil;&atilde;o</td>
        <td width="13%" align="center"><strong>A&ccedil;&atilde;o</strong></td>
    </tr>
    <?php
    $contador = 0;
    while ($pm       = $oDBase->fetch_object())
    {
        $contador++;
        echo "
			<tr onmouseover='pinta(1,this)' onmouseout='pinta(2,this)' height='18'>
			<td align='center'>" . tratarHTML($contador) . "</td>
			<td align='center'>" . tratarHTML($pm->codigo) . "</td>
			<td>" . tratarHTML($pm->descricao) . "</td>";
        //if ($_SESSION['sOUTRO']=="S")
        //{
        //	echo "<td align='center'><a href='emiteaviso.php?modo=1&lot=".$pm->codigo."'></a></td>";
        //}
        //else
        //{
        echo "<td align='center'><a href='emiteaviso.php?modo=1&lot=" . tratarHTML($pm->codigo) . "&upag=" . tratarHTML($upg) . "'>Avisar</a></td>";
        //}
        echo "</tr>";
    }

    if ($num_rows == 0)
    {
        echo "
			<tr onmouseover='pinta(1,this)' onmouseout='pinta(2,this)' height='40px'>
			<td align='center' colspan='4'>Não há registro(s), nesta GEX, de unidade(s) pendente(s) de homologação</td>
			</tr>
			";
    }
    ?>
</table>
<?php
// Base do formulário
//
	$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
