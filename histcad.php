<?php
include_once( "config.php" );

verifica_permissao("sRH");

$mat    = anti_injection($_REQUEST["mat"]);
$inicio = anti_injection($_REQUEST["ini"]);
$final  = anti_injection($_REQUEST["fin"]);

// instancia o BD
$oDBase = new DataBase('PDO');

$oDBase->query("SELECT * FROM histcad WHERE mat_siape = :mat_siape AND dataalt >= :dataalt AND dataalt <= :dataaltd ",array(
    array(':mat_siape', $mat, PDO::PARAM_STR),
    array(':dataalt', $inicio, PDO::PARAM_STR),
    array(':dataaltd', $final, PDO::PARAM_STR),
));


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho("Relatórios » Gerencial » Ocupantes de Fun&ccedil;&otilde;es");
$oForm->setJQuery();
$oForm->setCSS(_DIR_CSS_ . "estilos.css");
$oForm->setJS(_DIR_JS_ . "sorttable.js");
$oForm->setOnLoad("javascript: if($('#pSiape')) { $('#pSiape').focus() };");
$oForm->setLargura('920px');
$oForm->setSeparador(0);

$oForm->setSubTitulo("Dados hist&oacute;ricos de Cadastro");

$oForm->setObservacaoTpo(">O relat&oacute;rio demonstra os dados originais dos registros cadastrais que sofreram altera&ccedil;&atilde;o");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<form  action="histcad.php" method="POST" id="form1" name="form1" onSubmit="return validar()">
    <input type="hidden" id="modo" name="modo" value="<?= $modo; ?>" >
    <input type="hidden" id="corp" name="corp" value="<?= $corp; ?>">
    <table class="thin sortable draggable" border="1" width="100%" cellspacing="0" cellpadding="0" bordercolordark="white" bordercolorlight="#336699" id="AutoNumber2">
        <tr bgcolor='#008000'>
            <td width="3%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">SIAPE</font></b></td>
            <td width="3%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">DEFVIS</font></b></td>
            <td width="3%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">JORNADA</font></b></td>
            <td width="3%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">ENTRADA</font></b></td>
            <td width="3%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">I_INTERV.</font></b></td>
            <td width="3%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">F_INTERV.</font></b></td>
            <td width="3%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">SAI</font></b></td>
            <td width="5%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">HORARIO ESP.</font></b></td>
            <td width="3%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">MOTIVO</font></b></td>
            <td width="3%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">INI_HE</font></b></td>
            <td width="3%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">FIM_HE</font></b></td>
            <td width="3%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">AUT_CHEF</font></b></td>
            <td width="3%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">BHORAS</font></b></td>
            <td width="3%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">DATA ALT.</font></b></td>
            <td width="3%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">HORA ALT.</font></b></td>
            <td width="3%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">SIAPE ALT.</font></b></td>
            <td width="6%"  align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">IP_ALT</font></b></td>
        </tr>
        <?php
        while ($pm_partners = $oDBase->fetch_array())
        {
            $anohe = substr($pm_partners['dthe'], 0, 4);
            $meshe = substr($pm_partners['dthe'], 5, 2);
            $diahe = substr($pm_partners['dthe'], 8, 2);

            $anohe1 = substr($pm_partners['dthefim'], 0, 4);
            $meshe1 = substr($pm_partners['dthefim'], 5, 2);
            $diahe1 = substr($pm_partners['dthefim'], 8, 2);

            $ano1 = substr($pm_partners['dataalt'], 0, 4);
            $mes1 = substr($pm_partners['dataalt'], 5, 2);
            $dia1 = substr($pm_partners['dataalt'], 8, 2);
            ?>
            <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)'>
                <td width="3%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['mat_siape']); ?></font></td>
                <td width="3%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['defvis']); ?></font></td>
                <td width="3%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['jornada']); ?></font></td>
                <td width="3%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['entra_trab']); ?></font></td>
                <td width="3%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['ini_interv']); ?></font></td>
                <td width="3%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['sai_interv']); ?></font></td>
                <td width="3%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['sai_trab']); ?></font></td>
                <td width="2%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['horae']); ?></font></td>
                <td width="2%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['motivo']); ?></font></td>
                <td width="3%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($diahe . '/' . $meshe . '/' . $anohe); ?></font></td>
                <td width="3%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($diahe1 . '/' . $meshe1 . '/' . $anohe1); ?></font></td>
                <td width="3%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['autchef']); ?></font></td>
                <td width="2%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['bhoras']); ?></font></td>
                <td width="3%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($dia1 . '/' . $mes1 . '/' . $ano1); ?></font></td>
                <td width="3%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['horaalt']); ?></font></td>
                <td width="3%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['siapealt']); ?></font></td>
                <td width="6%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['ipalt']); ?></font></td>
            </tr>tratarHTML(
            <?php
        } // fim do while
        ?>
    </table>

    <p align="left" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6px; margin-bottom: 0px;">
        <font size="1">
        - Siape = siape do servidor alterado<br>
        - Defvis = Indicador de deficiente visual<br>
        - Jornada = Jornada do cargo<br>
        - Entrada = Hor&aacute;rio de entrada definido pela chefia<br>
        - I_interv = Horario de in&iacute;cio de intervalo definido pela chefia<br>
        - F_interv = Horario de fim do intetervalo definido pela chefia<br>
        - Sai = Hor&aacute;rio de sa&iacute;da definido pela chefia<br>
        - Horario esp = Indica que o servidor tem horario especial<br>
        - Motivo = Motivo da concess&atilde;o do hor&aacute;rio especial<br>
        - Ini_he = Data de in&iacute;cio do hor&aacute;rio especial<br>
        - Fim_he = Data de t&eacute;rmino do hor&aacute;rio especial<br>
        - Aut_chef = Indicador de que a chefia autorizou sa&iacute;da ap&oacute;s 19:00<br>
        - Bhoras = Indicador de que a chefia autorizou a compensa&ccedil;&atilde;o de horas<br>
        - Dataalt = Data da altera&ccedil;&atilde;o do registro<br>
        - Horaalt = Hora da altera&ccedil;&atilde;o do registro<br>
        - Siapealt = Matricula siape do usu&aacute;rio que alterou os dados<br>
        - Ip_alt = N&uacute;mero do IP da m&aacute;quina que foi realizada a altera&ccedil;&atilde;o.<br>
        </font>
    </p>
</form>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
