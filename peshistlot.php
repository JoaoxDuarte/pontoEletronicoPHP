<?php
include_once("config.php");

verifica_permissao('sRH');

$chave = anti_injection($_POST['chave']);

// isntancia BD
$oDBase = new DataBase('PDO');

if ($chave == "")
{
    $nome = "";
}
else
{
    $chave = getNovaMatriculaBySiape($chave);

    $oDBase->query("SELECT nome_serv, cod_lot FROM servativ WHERE mat_siape = '$chave' ");
    $oServidor = $oDBase->fetch_object();
    $nome      = $oServidor->nome_serv;
    $lot       = $oServidor->cod_lot;
}


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Cadastro » Movimentar » Manter Histórico');
$oForm->setOnLoad("javascript: if($('#chave')) { $('#chave').focus() };");
$oForm->setSeparador(0);

$oForm->setSubTitulo("Relat&oacute;rio de Movimenta&ccedil;&atilde;o");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

?>
<form method="POST" action="peshistlot.php" id="form1" name="form1">
    <input type="hidden" name="modo" value="<?= tratarHTML($modo); ?>" >
    <input type="hidden" name="corp" value="<?= tratarHTML($corp); ?>">
    <table border="0" width="100%" cellspacing="0" cellpadding="0" bordercolordark="white" bordercolorlight="#336699">
        <tr>
            <td colspan="3">
                <p style="word-spacing: 0; margin: 0"></p>
                <p align="center" style="word-spacing: 0; margin: 0"><h3> <div align="center"><b></b></div></p>
                    <p style="word-spacing: 0; margin: 0"></p>
            </td>
        </tr>
        <tr>
            <td class="corpo" width="100%" colspan="3"><p align="center" style="word-spacing: 0; margin: 0">&nbsp;</p></td>
        </tr>
        <tr>
            <td height="18" colspan="3">
                <p align="center" style="word-spacing: 0; margin: 0">
                <p align="center" style="word-spacing: 0; margin: 0"><font size="1" face="Tahoma">Siape</font></p>
                </p>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <p align="center" style="word-spacing: 0; margin: 0">
                    <input type="text" class="caixa" name="chave" title="Não informe pontos" size="12">
                </p>
                <p align="center" style="word-spacing: 0; margin: 0"></p>
            </td>
        </tr>
    </table>
    <p align="center" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6; margin-bottom: 0"><input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>ok.gif" name="enviar" alt="Submeter os valores" align="center" ></p>
</form>

<table width="100%" border="1">
    <tr>
        <td width="79%"><font size="1">Nome: <?= tratarHTML($nome); ?></font></td>
        <td width="21%"><font size="1">&nbsp;Siape: <?= tratarHTML(removeOrgaoMatricula( $chave )); ?></font></td>
    </tr>
</table>
<table width="100%" border="1" align="center" cellpadding="0" cellspacing="0" bordercolorlight="#336699" bordercolordark="white" >
    <tr bgcolor='#008000'>
        <td width="43%" ><b><font color="#FFFFFF" face="Tahoma" size="1">DESCRI&Ccedil;&Atilde;O</font></b></td>
        <td width="10%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">SETOR</font></b></td>
        <td width="10%" align="center"><b><font color="#FFFFFF" size="1" face="Tahoma">ENTRADA</font></b></td>
        <td width="9%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">SAÍDA</font></b></td>
        <td colspan="3" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">A&Ccedil;&Atilde;O</font></b></td>
    </tr>
    <?php

    if (isset($_POST['chave']))
    {
        $var1     = urldecode($chave);
        $pesquisa = "SELECT a.siape, a.cod_lot, b.codigo, b.descricao, date_format(dt_ing_lot, '%d/%m/%Y') as dt_ing_lot, date_format(dt_sai_lot, '%d/%m/%Y') as dt_sai_lot FROM histlot AS a LEFT JOIN tabsetor AS b ON a.cod_lot = b.codigo WHERE a.siape LIKE '$var1' ORDER BY dt_ing_lot desc ";
        $oDBase->query($pesquisa);

        while ($pm_partners = $oDBase->fetch_array())
        {
            ?>
            <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)'>
                <td width="43%" align="center"><font size="1" face="Tahoma"><div align="left"><?= tratarHTML($pm_partners['descricao']); ?></font></div></td>
                <td width="10%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['codigo']); ?></font></td>
                <td width="10%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['dt_ing_lot']); ?></font></td>
                <td width="9%"  align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['dt_sai_lot']); ?></font></td>
                <td width="9%"  align="center"><a href="histlotinc.php?siape=<?= tratarHTML($pm_partners['siape']); ?>">INCLUIR</a></td>
                <td width="10%" align="center"><font size="1" face="Tahoma"><div align="center"><a href="histlotal.php?siape=<?= tratarHTML($pm_partners['siape']); ?>&codigo=<?= tratarHTML($pm_partners['codigo']); ?>&dting=<?= tratarHTML($pm_partners['dt_ing_lot']); ?>">ALTERAR</a></div></td>
                <td width="9%"align="center"><font size="1" face="Tahoma"><div align="center"><a href="gravahistlot.php?modo=3&siape=<?= tratarHTML($pm_partners['siape']); ?>&codigo=<?= tratarHTML($pm_partners['codigo']); ?>&dting=<?= tratarHTML($pm_partners['dt_ing_lot']); ?>">EXCLUIR</a></div></td>
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
