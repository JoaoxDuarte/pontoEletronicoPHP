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

if ($_SESSION['sRH'] == "S" && $_SESSION['sOUTRO'] == "N")
{
    header("Location: relfrqsetorp.php");
}

if ($_SESSION['sOUTRO'] == "S")
{
    // tabela de unidades
    $oDBase = new DataBase('PDO');
    $oDBase->query("
		SELECT
			hom.compet, ger.cod_ger, UPPER(ger.nome_ger) AS nome_ger, UPPER(gex.cod_gex) AS cod_gex, UPPER(CONCAT(IF(SUBSTR(und.codigo,3,3)<>'150' AND LEFT(und.codigo,2)<>'01',CONVERT('GERÊNCIA EXECUTIVA ' USING latin1),''),gex.nome_gex)) AS nome_gex,
			und.codigo, UPPER(und.descricao) AS descricao, und.upag, COUNT(*) AS pedentes
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
			AND hom.compet = DATE_FORMAT(DATE_ADD(NOW(), INTERVAL -1 MONTH),'%Y%m')
		GROUP BY und.codigo, hom.compet
		ORDER BY ger.id_ger,IF(LEFT(cad.cod_lot,2)='01',1,IF(SUBSTR(cad.cod_lot,3,3)='150',2,3)),und.codigo, hom.compet DESC
		");

    $codger     = '';
    $codgex     = '';
    $listboxUnd = '';
    while (list($compet, $id_ger, $nome_ger, $cod_gex, $nome_gex, $codigo, $nome_und, $cod_upag, $pendentes) = $oDBase->fetch_array())
    {
        if ($codgex != $cod_gex || $codger != $id_ger)
        {
            if ((!empty($codger) && ($codgex != $cod_gex && $nome_ger != $nome_gex)) || (!empty($codger) && $codger != $id_ger))
            {
                $listboxUnd .= "<option></option>\n";
            }
            if ($codger != $id_ger)
            {
                $listboxUnd .= "<option style='background-color: #E2E2E2; font-family: arial; font-size: 10; font-weight: bold; text-align: center;' disabled='true'> ".tratarHTML($nome_ger)."</option>\n";
                $codger     = $id_ger;
            }
            if ($codgex != $cod_gex && $nome_ger != $nome_gex)
            {
                $listboxUnd .= "<option style='background-color: #E2E2E2; font-family: arial; font-size: 10; font-weight: bold; text-align: center;' disabled='true'>".tratarHTML($nome_gex)."</option>\n";
                $codgex     = $cod_gex;
            }
        }
        $listboxUnd .= "<option value='".tratarHTML($upag)."' " . ($upg == $upag ? "selected" : "") . ">".tratarHTML($codigo)." - ".tratarHTML($nome_und)." " . (substr($nome_und, 0, 18) == 'GERENCIA EXECUTIVA' ? ' (GABINETE)' : '') . "</option>\n";
    }
}


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Relatório » Frequência » Setores Pendentes');
$oForm->setCSS(_DIR_CSS_ . "estilos.css");
$oForm->setJS("
	<script>
		function verificadados()
		{
			// objeto mensagem
			oTeste = new alertaErro();
			oTeste.init();

			// dados
			var upag = $('#upag');

			if (upag=='00000000000000') { oTeste.setMsg( '- O código da UPAG é obrigatório!', upag ); }

			// se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
			var bResultado = oTeste.show();

			return bResultado;
		}

		function ve(parm1)
		{
			var mes = $('#mes');
			var ano = $('#ano');
			var ocor = $('#ocor');
			if (mes.val().length == 2) { ano.focus(); }
			if (ano.val().length == 4) { /*ocor.focus();*/ }
		}
	</script>
	");
$oForm->setOnLoad("$('#mes').focus()");
$oForm->setLargura("950px");
$oForm->setSeparador(0);

$oForm->setSubTitulo("Relatório de Setores Pendentes de Homologa&ccedil;&atilde;o");
$oForm->setObservacaoTopo("Informe a Upag que deseja consultar");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<form method="POST" action="relfrqsetorp.php" onsubmit="return verificadados()" id="form1" name="form1">
    <div style="text-align:center; padding:20px 0px 0px 0px;">
        <div style="border-width:0px; border-spacing:0px; border-collapse:collapse;">
            <select name='upag' size='1' class='drop' id='upag'>
                <?= $listboxUnd; ?>
            </select>
        </div>
    </div>
    <p align="center" style="word-spacing: 0; margin-top: 0; margin-bottom: 0">&nbsp;</p>
    <p align="center" style="word-spacing: 0; margin: 0">
        <input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>ok.gif" name="enviar" alt="Submeter os valores" align="center" >
    </p>
</form>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
