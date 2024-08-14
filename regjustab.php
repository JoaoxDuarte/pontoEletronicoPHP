<?php
include_once( "config.php" );
include_once( "class_ocorrencias_grupos.php" );

verifica_permissao("sAPS");

$sLotacao = $_SESSION['sLotacao'];

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    // le dados passados por formulario
    $nome   = anti_injection($_REQUEST['nome']);
    $mat    = anti_injection($_REQUEST['mat']);
    $dia    = $_REQUEST['dia'];
    $oco    = anti_injection($_REQUEST['oco']);
    $comp   = anti_injection($_REQUEST['comp']);
    $cmd    = anti_injection($_REQUEST['cmd']);
    $so_ver = "";
}
else
{
    // Valores passados - encriptados
    $dados    = explode(":|:", base64_decode($dadosorigem));
    $mat      = $dados[0];
    $nome     = $dados[1];
    $dia      = $dados[2];
    $justchef = iso88591_utf8($dados[3]);
    $oco      = $dados[4];
    $cmd      = $dados[5];
    $so_ver   = $dados[6];
}

$mat = getNovaMatriculaBySiape($mat);

$oDBase = selecionaServidor($mat);
$sitcad = $oDBase->fetch_object()->sigregjur;

## ocorrências grupos
$obj = new OcorrenciasGrupos();
$passiveisDeAbono = $obj->GrupoOcorrenciasPassiveisDeAbono($sitcad, $exige_horarios = true);

$vDatas = date("Y-m-d");
$dt     = conv_data($dia);
$tempo  = dias_decorr($dt, $vDatas);

$pagina_de_origem = pagina_de_origem();
if (isset($_SESSION['sPaginaRetorno_sucesso']) && $_SESSION['sPaginaRetorno_sucesso'] != '')
{
    $_SESSION["sVePonto"] = $_SESSION['sPaginaRetorno_sucesso'];
}

if ($so_ver == 'sim')
{
    $comp = substr($dia, 3, 2) . substr($dia, 6, 4);
}
else
{
    include_once("ilegal5.php");

    if ($oco != '' && in_array($oco, $passiveisDeAbono))
    {
        mensagem("Não é permitido abonar dia com ocorrência diferente\\nde " . implode(',', $passiveisDeAbono) . "!", pagina_de_origem(), 1);
    }
    $comp = date('mY');
}

// instancia o banco de dados
$oDBase = new DataBase('PDO');

// seleciona nome do servidor e jornada
$oDBase->query("SELECT just, justchef FROM ponto$comp WHERE siape = '$mat' and dia = '$dt' ");
$oServidor = $oDBase->fetch_object();
$just      = $oServidor->just;
$justchef  = $oServidor->justchef;

//pegando o ip do usuario
$ip    = getIpReal(); //linha que captura o ip do usuario.


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho("Visualizar Frequência");
$oForm->setSeparador(0);
$oForm->setLargura('795px');

$oForm->setSubTitulo("Justificativa para abono de ocorr&ecirc;ncia");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<script>
    function verificadados()
    {
        if (document.form1.just.value.length < 15)
        {
            alert(' É obrigatório o preenchimento da justificativa com no mínimo 15 caracteres!');
            document.form1.just.focus();
            return false;
        }
    }
</script>

<form method="POST" action=<?= ($so_ver == 'sim' ? '""' : '"gravaregfreq1.php" onsubmit="return verificadados()"'); ?> id="form1" name="form1">
    <input type='hidden' id='modo' name='modo' value='11'>
    <table width="77%" border="1" align="center" cellpadding="0" cellspacing="0" bordercolor="#808040" id="AutoNumber1" style="border-collapse: collapse">
        <tr>
            <td height="20" bgcolor="#DFDFBF"> <p align="center"><b>Siape</b></td>
            <td width="569" height="20" bgcolor="#DFDFBF"><div align="center"><b>Nome</b></div>
                <div align="center"></div>
                <div align="center"></div></td>
            <td width="128" height="20" bgcolor="#DFDFBF"><div align="center"><b>Lota&ccedil;&atilde;o</b></div></td>
        </tr>
        <tr>
            <td width="84">
                <div align="center">
                    <input name="mat" type="text" class='centro' id="mat" OnKeyPress="formatar(this, '##/##/####')" value="<?= tratarHTML(removeOrgaoMatricula( $mat )); ?>" size="9" readonly>
                    <input name="cmd" type="hidden" class='centro' id="cmd"  value="<?= tratarHTML($cmd); ?>" size="9" readonly>
                </div>
            </td>
            <td>
                <p align="left">
                    <input name="nome" type="text" class='Caixa' id="nome" OnKeyPress="formatar(this, '##/##/####')" value="<?= tratarHTML($nome); ?>" size="60" readonly>
                </p>
            </td>
            <td>
                <div align="center">
                    <input name="lotacao" type="text" class='centro' id="lotacao" OnKeyPress="formatar(this, '##/##/####')" value="<?= tratarHTML($sLotacao); ?>" size="15" readonly>
                    <input name="ip" type="hidden" class='centro' id="ip" value="<?= tratarHTML($ip); ?>" size="9" readonly>
                </div>
            </td>
        </tr>
        <tr>
            <td height="20" colspan="3" bgcolor="#DFDFBF"> <p align="center"><b>Justificativa para abono de ocorr&ecirc;ncia</b> </td>
        </tr>
        <tr>
            <td colspan="3"><div align="center"><font face="Tahoma" size="2" color="#333300"><b>
                        <input name="dia" type="text" class='centro' id="dia" value="<?= tratarHTML($dia); ?>" size="10" readonly>
                    </b></font></div>
            </td>
        </tr>
        <?php
        if (!empty($just) || $just != '')
        {
            ?>
            <tr>
                <td colspan="3" bgcolor="#DFDFBF"><div align="center"></div>
                    <div align="center"><font size="2">JUSTIFICATIVA DO SERVIDOR</font></div>
                </td>
            </tr>
            <tr>
                <td colspan="3"><textarea cols=92 rows=3 readonly><?= tratarHTML($just); ?></textarea></td>
            </tr>
            <?php
        }
        ?>
        <tr>
            <td bgcolor="#DFDFBF"><div align="center"><font size="2">ocorr&ecirc;ncia</font></div></td>
            <td colspan="2" bgcolor="#DFDFBF"><div align="center"></div>
                <div align="center"><font size="2">JUSTIFICATIVA DA CHEFIA</font></div>
            </td>
        </tr>
        <tr>
            <td><div align="center">
                    <input name="oco" type="text" class='centro' id="oco" value="<?= tratarHTML($oco); ?>" size="8" readonly></div>
            </td>
            <td colspan="2"><textarea name=just cols=80 rows=5 id="textarea2" <?= ($so_ver == 'sim' ? 'readonly' : ''); ?>><?= tratarHTML($justchef); ?></textarea></td>
        </tr>
    </table>
    <div align='center'>
        <p>
        <table border='0' align='center'>
            <tr>
                <?php
                if ($so_ver != 'sim' && ($_SESSION['sAPS'] == 'S'))
                {
                    ?>
                    <td><input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>ok.gif" name="enviar" alt="Submeter os valores" align="center" ></td>
                    <td>&nbsp;&nbsp;</td>
                    <?php
                }
                ?>
                <td align='left'><?= botao('Voltar', 'javascript:window.history.go(-1);window.location.replace("' . $_SESSION["sVePonto"] . '");'); ?></td>
            </tr>
        </table>
        </p>
    </div>
    <p>&nbsp;</p>
    <p align="center" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6">&nbsp;</p>
    <font size="2" face="Tahoma"> </font>
</form>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
