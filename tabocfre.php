<?php
include_once("config.php");

verifica_permissao("sRH ou Chefia");

// dados/parametros
$modo = anti_injection($_REQUEST['modo']);
$corp = anti_injection($_REQUEST['corp']);

// instanciaBD
$oDBase = new DataBase('PDO');


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
//$oForm->setFlexSelect();
$oForm->setCaminho('Tabelas » Ocorrências » Consultar / Alterar');
$oForm->setSubTitulo("Pesquisa de C&oacute;digos de Ocorr&ecirc;ncias");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<form method="POST" action="tabocfre.php" id="form1" name="form1">
    <input type="hidden" name="modo" value="<?= tratarHTML($modo); ?>" >
    <input type="hidden" name="corp" value="<?= tratarHTML($corp); ?>">
    <table border="0" width="100%" cellspacing="0" cellpadding="0" bordercolordark="white" bordercolorlight="#336699">
        <tr>
            <td class="corpo" width="100%" colspan="3"> <p align="center" style="word-spacing: 0; margin: 0"><font face="Tahoma" size="1">
                    <input type="radio" id="escolha" name="escolha" value="siapecad" checked onclick="document.all['chave'].focus()"> Por Código Siapecad
                    <input type="radio" id="escolha" name="escolha" value="descricao" onclick="document.all['chave'].focus()"> Por descricao
                    <input type="radio"  id="escolha"name="escolha" value="sirh" onclick="document.all['chave'].focus()"> Por C&oacute;digo Sirh
                    <input type="radio"  id="escolha"name="escolha" value="siape" onclick="document.all['chave'].focus()"> Por C&oacute;digo Siape</font>
            </td>
        </tr>
        <tr>
            <td width="29%"><p style="word-spacing: 0; margin: 0"></p></td>
            <td width="37%"><p style="word-spacing: 0; margin: 0">&nbsp;</p></td>
            <td width="34%"><p style="word-spacing: 0; margin: 0"></p></td>
        </tr>
        <tr>
            <td width="29%"><p style="word-spacing: 0; margin: 0"></td>
            <td class="corpo" width="37%">
                <p align="center" style="word-spacing: 0; margin: 0">
                    <font size="1" face="Tahoma">Chave </font><input type="text" id="chave" name="chave" class="caixa" title="Não informe pontos" size="28">
                </p>
            </td>
            <td width="34%"><p style="word-spacing: 0; margin: 0"></p></td>
        </tr>
    </table>
    <p align="center" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6; margin-bottom: 0"><input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>ok.gif" name="enviar" alt="Submeter os valores" align="center" ></p>
</form>

<table border="1" width="100%" cellspacing="0" cellpadding="0" bordercolordark="white" bordercolorlight="#336699" >
    <tr bgcolor='#008000'>
        <td colspan="2"  align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">AÇÕES</font></b></td>
        <td width="6%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">SIAPECAD</font></b></td>
        <td width="48%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">DESCRIÇÃO</font></b></td>
        <td width="7%" align="center"><b><font color="#FFFFFF" size="1" face="Tahoma">RESPONSAVEL</font></b></td>
        <td width="8%" align="center"><b><font color="#FFFFFF" size="1" face="Tahoma">SIRH</font></b></td>
        <td width="7%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">SIAPE</font></b></td>
        <td width="11%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">ATIVO</font></b></td>
    </tr>
    <?php
    $pesquisa = "";
    if (isset($_POST["chave"]))
    {
        $var1 = anti_injection($_POST["chave"]);
        $var2 = anti_injection($_POST["escolha"]);

        switch ($var2)
        {
            case "siapecad": $pesquisa = "SELECT * FROM tabocfre WHERE siapecad = '$var1' AND ativo = 'S' ";
                break;
            case "descricao": $pesquisa = "SELECT * FROM tabocfre WHERE desc_ocorr LIKE '%$var1%' AND ativo = 'S' ";
                break;
            case "sirh": $pesquisa = "SELECT * FROM tabocfre WHERE cod_ocorr LIKE '%$var1%' AND ativo = 'S' ";
                break;
            case "siape": $pesquisa = "SELECT * FROM tabocfre WHERE cod_siape LIKE '%$var1%' AND ativo = 'S' ";
                break;
        }

        $oDBase->query($pesquisa);
        while ($pm_partners = $oDBase->fetch_array())
        {
            $siapecad   = $pm_partners['siapecad'];
            $desc_ocorr = $pm_partners['desc_ocorr'];
            $resp       = $pm_partners['resp'];
            $cod_ocorr  = $pm_partners['cod_ocorr'];
            $cod_siape  = $pm_partners['cod_siape'];
            $ativo      = $pm_partners['ativo'];

            $altera_ocorrencia = "'#' disabled";
            if (($_SESSION['sRH'] == "S" || $_SESSION['sTabServidor'] == "S"))
            {
                $altera_ocorrencia = "'alteraocfre.php?siapecad=" . $siapecad . "'";
            }

            switch ($resp)
            {
                case 'AB':
                    $responsavel = 'RH/Chefia';
                    break;

                case 'RH':
                    $responsavel = 'Recursos Humanos';
                    break;

                case 'CH':
                    $responsavel = 'Chefia';
                    break;

                case 'SI':
                    $responsavel = 'SISREF';
                    break;

                default:
                    $responsavel = '&nbsp;';
                    break;
            }

            ?>
            <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)'>
                <td width="7%"  align="center"><font size="1" face="Tahoma">&nbsp;<a href=<?= tratarHTML($altera_ocorrencia); ?>>Alterar</a>&nbsp;</font></td>
                <td width="7%"  align="center"><font size="1" face="Tahoma">&nbsp;<a href="veocfre.php?siapecad=<?= tratarHTML($siapecad); ?>">Visualizar</a>&nbsp;</font></td>
                <td width="7%"  align="center"><font size="1" face="Tahoma"><?= tratarHTML($siapecad); ?></font></td>
                <td width="48%" align="left"><font size="1" face="Tahoma">&nbsp;&nbsp;<?= ($desc_ocorr == '' ? '' : tratarHTML($desc_ocorr)); ?></font></td>
                <td width="7%"  align="left" nowrap><font size="1" face="Tahoma">&nbsp;<?= tratarHTML($responsavel); ?>&nbsp;</font></td>
                <td width="8%"  align="center"><font size="1" face="Tahoma">&nbsp;<?= tratarHTML($cod_ocorr); ?>&nbsp;</font></td>
                <td width="7%"  align="center"><font size="1" face="Tahoma">&nbsp;<?= tratarHTML($cod_siape); ?>&nbsp;</font></td>
                <td width="7%" align="center"><font size="1" face="Tahoma">&nbsp;<?= tratarHTML($ativo); ?>&nbsp;</font></td>
            </tr>
            <?php
        } // fim do while
    }
    ?>
</table>
<?php
// Base do formulário
//
	$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
