<?php
include_once("config.php");

verifica_permissao("sRH");

// instancia BD
$oDBase    = new DataBase('PDO');
$oDBaseCad = new DataBase('PDO');

//definindo a competencia de homologacao
$ano = date(Y);

if (date(n) == "1")
{
    $comp = "12";
    $year = $ano - 1;
}
if ((date(n) > "1") && (date(n) < "11"))
{
    $comp = "0" . (date(n) - 1);
    $year = $ano;
}
if (date(n) > "10")
{
    $comp = date(n) - 1;
    $year = $ano;
}

// define lotacoes vinculadas a upag
$sLotacao = $_SESSION['sLotacao'];
$qlotacao = $sLotacao;
$upag     = $_SESSION['upag'];

$oDBase->query("SELECT descricao FROM tabsetor WHERE codigo = '$sLotacao' ");
$wnomelota = $oDBase->fetch_object()->descricao;

$oDBase->query("SELECT a.siape, a.dia, b.upag FROM ponto$comp$year AS a LEFT JOIN usuarios AS b ON a.siape = b.siape WHERE b.upag = '$upag' AND a.dia = '0000-00-00' ORDER BY a.siape ");
$num = $oDBase->num_rows();


## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho("Tabelas");
$oForm->setCSS(_DIR_CSS_ . "estiloIE.css");
$oForm->setOnLoad("javascript: if($('#pSiape')) { $('#pSiape').focus() };");
$oForm->setLargura('950');
$oForm->setSeparador(0);

// Topo do formulário
//
$oForm->setSubTitulo("Relatório de Servidores com Dia Zerado no Mes de Homologa&ccedil;&atilde;o");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#808040" width="100%" id="AutoNumber1">
    <tr>
        <td width="100%"  align='center' style='font-family:verdana; font-size:12pt'>
            <font size="2" face="Tahoma">M&ecirc;s
            <input name="mes" type="text" class='alinhadoAoCentro' id="mes"  value='<?= tratarHTML($comp); ?>' size="7" readonly>
            Ano
            <input name="ano" type="text" class='alinhadoAoCentro' id="ano" value='<?= tratarHTML($year); ?>' size="7" readonly>
            </font>
        </td>
    </tr>
    <tr>
        <td  align='center' style='font-family:verdana; font-size:12pt'>
            <font size="2" face="Tahoma">Lota&ccedil;&atilde;o
            <input name="lot" type="text" class='alinhadoAoCentro' id="lot" value='<?= tratarHTML(getUorgMaisDescricao( $sLotacao )); ?>' size="60" maxlength="60" readonly>
            </font>
        </td>
    </tr>
</table>

<table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#F1F1E2" width="100%" id="AutoNumber2" >
    <tr bgcolor="#DBDBB7">
        <td width="7%" align='center'><b>Matr&iacute;cula</td>
        <td width="36%"><b>&nbsp;Nome</td>
        <td width="9%"><div align="center"><strong>Dia</strong></div></td>
    </tr>
    <?php
    if ($num > 0)
    {
        while ($pm = $oDBase->fetch_array())
        {
            $oDBaseCad->query("SELECT nome_serv, freqh FROM servativ WHERE mat_siape = '" . $pm['siape'] . "' ");
            $oCad = $oDBaseCad->fetch_object();

            $nome  = $oCad->nome_serv;
            $freqh = $oCad->freqh;

            if ($freqh == "S")
            {
                echo "
					<tr onmouseover='pinta(1,this)' onmouseout='pinta(2,this)' height='18'>
						<td align='center'>" . tratarHTML($pm['siape']) . "</td>
						<td>".tratarHTML($nome)."</td>
						<td align='center'>" . tratarHTML($pm['dia']) . "</td>
					</tr>";
            }
        }
    }
    else
    {
        echo "<font face='verdana' size='2'>Não há servidores com dia zerado!</font>";
    }
    ?>
</table>

<table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#F1F1E2" width="100%" id="AutoNumber2" >
    <tr bgcolor="#DBDBB7">
        <td colspan="9" align='center'>&nbsp;</td>
    </tr>
</table>

<p>
    <font size="1">
    No caso de existirem servidores nesta rela&ccedil;&atilde;o dever&aacute; o RH REJEITAR a homologa&ccedil;&atilde;o para que a chefia exclua o dia e providencie a retifica&ccedil;&atilde;o.
    </font>
</p>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
