<?php
// funcoes de uso geral
include_once( "config.php" );

// permissao de acesso
verifica_permissao("sLog");

// instancia BD
$oDBase = new DataBase('PDO');

## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho('Tabelas » Lotações » Manutenção');
$oForm->setCSS(_DIR_CSS_ . 'estilos.css');
$oForm->setJS(_DIR_JS_ . 'jquery.js');
$oForm->setJS(_DIR_JS_ . 'sorttable.js');
$oForm->setJS(_DIR_JS_ . 'check_data.js');
$oForm->setJS(_DIR_JS_ . 'cal2.js');
$oForm->setJS(_DIR_JS_ . 'cal_conf2.js');
$oForm->setSeparador(0);

$oForm->setSubTitulo("Consulta Operações Ilegais");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<script>
    function validar()
    {
        // objeto mensagem
        oTeste = new alertaErro();
        oTeste.init();

        // dados
        var inicio = $('#inicio');
        var final  = $('#final');

        if (inicio.value.length == 0)
        {
            oTeste.setMsg('É obrigatório informar a data de início de pesquisa!', inicio);
        }
        if (final.value.length == 0)
        {
            oTeste.setMsg('É obrigatório informar o final da pesquisa!', final);
        }

        // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
        var bResultado = oTeste.show();

        return bResultado;
    }
</script>

<form  action="pesilegal.php" method="POST" id="form1" name="form1" onSubmit="return validar()">
    <input type="hidden" name="modo" value="<?= tratarHTML($modo); ?>" >
    <input type="hidden" name="corp" value="<?= tratarHTML($corp); ?>">
    <table border="0" width="100%" cellspacing="0" cellpadding="0" bordercolordark="white" bordercolorlight="#336699">
        <tr>
            <td class="corpo" width="100%" colspan="3">
                <p align="center" style="word-spacing: 0; margin: 0">
                    <font face="Tahoma" size="1">
                    <input type="radio" id="escolha" name="escolha" value="alteracao" checked> Altera&ccedil;&atilde;o de registro&nbsp;&nbsp;&nbsp;
                    <input type="radio" id="escolha" name="escolha" value="exclusao"> Exclus&atilde;o de registro&nbsp;&nbsp;&nbsp;
                    <input type="radio" id="escolha" name="escolha" value="abono"> Abono de registro
                    <input type="radio" id="escolha" name="escolha" value="pfora"> Registro por fora
                    </font>
                </p>
            </td>
        </tr>
        <tr>
            <td width="29%"><p style="word-spacing: 0; margin: 0"></td>
            <td width="37%"><p align="center" style="word-spacing: 0; margin: 0">&nbsp;</td>
            <td width="34%"><p style="word-spacing: 0; margin: 0"></td>
        </tr>
        <tr>
            <td width="29%"><p style="word-spacing: 0; margin: 0">&nbsp; </td>
            <td class="corpo" width="37%">
                <p align="center" style="word-spacing: 0; margin: 0">
                    <font size="2">Período</font>
                    <small><a href="javascript:showCal('perrelcon1')"><img src='<?= _DIR_IMAGEM_; ?>calendario.gif' border='0'  width='15' height='15'></a></small>
                    <input class="caixa" type="text" name="inicio" OnKeyUp="mascara_data(this.value, this.name, '0')" size="10" maxlength="10" value = "" onkeypress='if (event.keyCode < 48 || event.keyCode > 57)
                                event.returnValue = false;'>
                    &nbsp;a&nbsp;<small><a href="javascript:showCal('perrelcon2')"><img src='<?= _DIR_IMAGEM_; ?>calendario.gif' border='0' width='15' height='15'></a></small>
                    <input class="caixa" type="text" name="final" OnKeyUp="mascara_data(this.value, this.name, '0')" size="10" maxlength="10" value = "" onkeypress='if (event.keyCode < 48 || event.keyCode > 57)
                                event.returnValue = false;'>
                    &nbsp;
            </td>
            <td width="34%"><p style="word-spacing: 0; margin: 0"></td>
        </tr>
    </table>
    <p align="center" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6; margin-bottom: 0"><input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>ok.gif" name="enviar" alt="Submeter os valores" align="center" ></p>
</form>

<table class="thin sortable draggable" border="1" width="100%" cellspacing="0" cellpadding="0" bordercolordark="white" bordercolorlight="#336699" id="AutoNumber2">
    <tr bgcolor='#008000'>
        <td  align="center"><b><font color="#FFFFFF" size="1" face="Tahoma">DATA</font></b></td>
        <td width="9%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">SIAPE</font></b></td>
        <td width="45%" ><div align="center"><b><font color="#FFFFFF" size="1" face="Tahoma">OPERA&Ccedil;&Atilde;O</font></b></div></td>
        <td width="10%" align="center"><b><font color="#FFFFFF" size="1" face="Tahoma">MAQUINA</font></b></td>
        <td width="10%" align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">HORA</font></b></td>
        <td width="16%"  align="center"><b><font color="#FFFFFF" face="Tahoma" size="1">LOTAÇÃO</font></b></td>
    </tr>
<?php
$pesquisa = "";
$var2     = anti_injection($_POST["escolha"]);
$inicio   = conv_data($_POST["inicio"]);
$fim      = conv_data($_POST["final"]);

if ($var2 == "alteracao")
{
    $pesquisa = "SELECT  siape, operacao, date_format(datag, '%d/%m/%Y') as datag, hora, maquina, setor from ilegal where operacao like '%alterar%'  and datag >= '$inicio' and datag <= '$fim' ";
}
elseif ($var2 == "exclusao")
{
    $pesquisa = "SELECT siape, operacao, date_format(datag, '%d/%m/%Y') as datag, hora, maquina, setor from ilegal where operacao like '%excluir%'  and datag >= '$inicio' and datag <= '$fim'";
}
elseif ($var2 == "abono")
{
    $pesquisa = "SELECT siape, operacao, date_format(datag, '%d/%m/%Y') as datag, hora, maquina, setor from ilegal where operacao like '%abonar%'  and datag >= '$inicio' and datag <= '$fim'";
}
elseif ($var2 == "pfora")
{
    $pesquisa = "SELECT siape, operacao, date_format(datag, '%d/%m/%Y') as datag, hora, maquina, setor from ilegal where operacao like '%fora%' and datag >= '$inicio' and datag <= '$fim'";
}

$oDBase->query($pesquisa);
while ($pm_partners = $oDBase->fetch_array())
{
    ?>
    <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)'>
        <td width="7%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['datag']); ?></font></td>
        <td width="9%" align="center"><font size="1" face="Tahoma"><?= ($pm_partners['siape'] < 1 ? "000000000000" : tratarHTML($pm_partners['siape'] )); ?></font></td>
        <td width="45%"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['operacao']); ?></font></td>
        <td width="10%" align="center"><font size="1" face="Tahoma"><?= tratarHTML($pm_partners['maquina']); ?></font></td>
        <td width="10%" align="center"><font size="1" face="Tahoma"><?= ($pm_partners['hora'] < 1 ? "00:00:00" : tratarHTML($pm_partners['hora'])); ?></font></td>
        <td width="16%" align="center"><font size="1" face="Tahoma"><?= ($pm_partners['setor'] < 1 ? "00000000000000" : tratarHTML($pm_partners['setor'])); ?></font></td>
    </tr>
    <?php
} // fim do while
?>
</table>
<p align="center">&nbsp;</p>
<p>&nbsp;</p>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
