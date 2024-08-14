<?php
include_once( "config.php" );

verifica_permissao("logado");


//está com problema precisa identificar
$cmd = $_REQUEST["c"];

if ($cmd == "3")
{
    header("Location: acessonegado.php");
}

$sLotacao = $_SESSION["sLotacao"];

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    // le dados passados por formulario
    $nome   = anti_injection($_REQUEST["nome"]);
    $mat    = anti_injection($_REQUEST["mat"]);
    $dia    = $_REQUEST["dia"];
    $oco    = anti_injection($_REQUEST["oco"]);
    $idreg  = anti_injection($_REQUEST["rg"]);
    $dt     = conv_data($dia);
    $comp   = anti_injection($_REQUEST["comp"]);
    $just   = anti_injection($_REQUEST["just"]);
    $jnd1   = anti_injection($_REQUEST["jnd"]);
    $jnd    = formata_jornada_para_hhmm($jnd1);
    $so_ver = anti_injection($_REQUEST["so_ver"]);
}
else
{
    // Valores passados - encriptados
    $dados    = explode(":|:", base64_decode($dadosorigem));
    $mat      = $dados[0];
    $nome     = iso88591_utf8($dados[1]);
    $comp     = $dados[2];
    $dia      = $dados[3];
    $dt       = conv_data($dia);
    $just     = iso88591_utf8($dados[4]);
    $oco      = $dados[5];
    $idreg    = $dados[6];
    $cmd      = $dados[7];
    $jnd1     = $dados[8];
    $jnd      = formata_jornada_para_hhmm($jnd1);
    $so_ver   = $dados[9];
    $justchef = iso88591_utf8($dados[10]);
}

// instancia o banco de dados
$oDBase = new DataBase('PDO');

// seleciona nome do servidor e jornada
$oDBase->query("SELECT nome_serv, jornada FROM servativ WHERE mat_siape = '$mat' ");
$oServidor = $oDBase->fetch_object();
$nome      = $oServidor->nome_serv;
$jnd1      = $oServidor->jornada;

## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho("Visualizar Frequência");
$oForm->setSeparador(0);
$oForm->setLargura('795px');

$oForm->setSubTitulo("Justificativa para ocorr&ecirc;ncia");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<form method="POST" action="" id="form1" name="form1">
    <table width="100%" border="1" align="center" cellpadding="0" cellspacing="0" bordercolor="#808040" id="AutoNumber1" style="border-collapse: collapse">
        <tr>
            <td height="20" bgcolor="#DFDFBF"><p align="center"><b>Siape</b></td>
            <td width="378" height="20" bgcolor="#DFDFBF"><div align="center"><b>Nome</b></div>
                <div align="center"></div>
                <div align="center"></div></td>
            <td width="95" height="20" bgcolor="#DFDFBF"><div align="center"><b>Lota&ccedil;&atilde;o</b></div></td>
        </tr>
        <tr>
            <td width="90"><div align="center">
                    <input name="siape" type="text" class='centro' id="siape" OnKeyPress="formatar(this, '##/##/####')"  value="<?= tratarHTML($mat); ?>" size="15" readonly>
                </div></td>
            <td> <p align="left">
                    <input name="nome" type="text" class='Caixa' id="nome" OnKeyPress="formatar(this, '##/##/####')"  value="<?= tratarHTML($nome); ?>" size="64" readonly>
            </td>
            <td><div align="center">
                    <input name="lotacao" type="text" class='centro' id="lotacao" OnKeyPress="formatar(this, '##/##/####')" value="<?= tratarHTML($sLotacao); ?>" size="15" readonly></div>
            </td>
        </tr>
        <tr>
            <td height="20" colspan="3" bgcolor="#DFDFBF"> <p align="center"><b>Justificativa para ocorr&ecirc;ncia</b></td>
        </tr>
        <tr>
            <td colspan="3"><div align="center"><font face="Tahoma" size="2" color="#333300"><b>
                        <input name="dia" type="text" class='centro' id="dia"  value="<?= tratarHTML($dia); ?>" size="10" readonly>
                    </b></font></div>
            </td>
        </tr>
    </table>
    <table width="100%" border="1" align="center" cellpadding="0" cellspacing="0" bordercolor="#808040" id="AutoNumber1" style="border-collapse: collapse">
        <tr>
            <td width="16%" height="20" bgcolor="#DFDFBF"><div align="center"><font size="2">Ocorr&ecirc;ncia</font></div></td>
            <td width="84%" height="20" bgcolor="#DFDFBF"> <div align="center"></div>
                <div align="center"><font size="2">Justificativa do Servidor</font></div></td>
        </tr>
        <tr>
            <td height="114">
                <div align="center">
                    <input name="oco" type="text" class='centro' id="oco"  value="<?= tratarHTML($oco); ?>" size="10" readonly></div>
            </td>
            <td>
                <div align="center">
                    <textarea name=just cols=80 rows=5 id="textarea"  readonly><?= tratarHTML($just); ?></textarea></div>
            </td>
        </tr>
        <?php
        if ($justchef != "")
        {
            ?>
            <tr>
                <td>&nbsp;</td>
                <td colspan="1" bgcolor="#DFDFBF"><p align="center"><font size="2">Justificativa da Chefia</font></p></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td colspan="1">&nbsp;<textarea name=justchef cols=80 rows=5 id="justchef" readonly><?= tratarHTML($justchef); ?></textarea></td>
            </tr>
            <?php
        }
        ?>
    </table>
    <?php
    if ($so_ver != 'sim' && ($_SESSION['sAPS'] == 'S'))
    {
        $registro8 = base64_encode($mat . ':|:' . $nome . ':|:' . $dia . ':|:' . $oco . ':|:' . $sLotacao . ':|:' . $idreg . ':|:' . $cmd . ':|:' . $jnd);
        $veponto3  = base64_encode($mat . ':|:' . $dia);

        ?>
        <p>
            Alterar ocorr&ecirc;ncia:<a href="registro8.php?dados=<?= tratarHTML($registro8); ?>"><img border="0" src="<?= _DIR_IMAGEM_; ?>edicao2.jpg" width="16" height="16" align="absmiddle" alt="Alterar ocorrência"></a>
            Excluir ocorr&ecirc;ncia:<a href="veponto3.php?dados=<?= tratarHTML($veponto3); ?>"><img border="0" src="<?= _DIR_IMAGEM_; ?>lixeira2.jpg" width="16" height="16" align="absmiddle" alt="Excluir ocorrência"></a>
        </p>
        <?php
    }
    ?>
    <div align='center'>
        <p>
        <table border='0' align='center'>
            <tr>
                <td align='left'><?= botao('Voltar', 'javascript:window.history.go(-1);window.location.replace("' . $_SESSION["sVePonto"] . '");'); ?></td>
            </tr>
        </table>
        </p>
    </div>
</form>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
