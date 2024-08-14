<?php
include_once( "config.php" );
include_once( "class_ocorrencias_grupos.php" );

verifica_permissao('sRH e sTabServidor');

$sLotacao = $_SESSION['sLotacao'];

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    header("Location: acessonegado.php");
}
else
{
    // Valores passados - encriptados
    $dados  = explode(":|:", base64_decode($dadosorigem));
    $mat    = getNovaMatriculaBySiape($dados[0]);
    $dia    = $dados[1];
    $cmd    = $dados[2];
    $so_ver = $dados[3];
}


$oDBase = selecionaServidor($siape);
$sitcad = $oDBase->fetch_object()->sigregjur;

## ocorrências grupos
$obj = new OcorrenciasGrupos();
$grupoOcorrenciasPassiveisDeAbono = $obj->GrupoOcorrenciasPassiveisDeAbono($sitcad, $exige_horarios=true);


// dados voltar
$_SESSION['voltar_nivel_2'] = $dadosorigem;
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';

// Comentado temporariamente por não sabermos, de antemão, os IPs da aplicação
//include_once('ilegal_grava.php');
// instancia o banco de dados
$oDBase = new DataBase('PDO');

// seleciona nome do servidor e jornada
$oDBase->query("
		SELECT
			cad.nome_serv, cad.jornada, cad.cod_lot, pto.just, pto.oco, pto.idreg, pto.justchef
		FROM
			servativ AS cad
		LEFT JOIN
			" . $_SESSION['sHArquivoTemp'] . " AS pto ON cad.mat_siape = pto.siape
		WHERE
			cad.mat_siape = '" . $mat . "'
			AND pto.dia='" . conv_data($dia) . "'
	");
$oServidor = $oDBase->fetch_object();
$nome      = trata_aspas($oServidor->nome_serv);
$lot       = $oServidor->cod_lot;
$just      = trata_aspas($oServidor->just);
$justchef  = trata_aspas($oServidor->justchef);
$oco       = $oServidor->oco;
$idreg     = $oServidor->idreg;
$jnd1      = $oServidor->jornada;
$jnd       = formata_jornada_para_hhmm($jnd1);

if ($oco != '' && in_array($oco, $grupoOcorrenciasPassiveisDeAbono)) //"00172_00129_55555_62010_62012_88888_99999"
{
    mensagem("Não é permitido abonar dia com ocorrência diferente\\nde " . implode(', ', $grupoOcorrenciasPassiveisDeAbono) . "!", 'javascript:window.location.replace("historico_frequencia_registros.php?dados=' . $_SESSION['voltar_nivel_1'] . '");');
}


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Histórico » ... » Justificativa Abono');
$oForm->setLargura('795px');
$oForm->setSeparador(0);

$oForm->setSubTitulo("Justificativa para Abono de Ocorr&ecirc;ncia");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<script language="javascript">
function verificadados()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var just = $('#just');

    if (just.val().length < 15)
    {
        oTeste.setMsg( 'É obrigatório o preenchimento da justificativa com no mínimo 15 caracteres!', just );
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();
    if (bResultado == false)
    {
        return bResultado;
    }
    else
    {
        $('#form1').submit();
    }
}
</script>

<form method="POST" action="historico_frequencia_gravar_abono.php" onsubmit="return verificadados()" id="form1" name="form1" >
    <table width="90%" border="1" align="center" cellpadding="0" cellspacing="0" bordercolor="#808040" id="AutoNumber1" style="border-collapse: collapse">
        <tr>
            <td height="20" bgcolor="#DFDFBF"> <p align="center"><b>Siape</b></td>
            <td width="569" height="20" bgcolor="#DFDFBF"><div align="center"><b>Nome</b></div>
                <div align="center"></div>
                <div align="center"></div></td>
            <td width="128" height="20" bgcolor="#DFDFBF"><div align="center"><b>Lota&ccedil;&atilde;o</b></div></td>
        </tr>
        <tr>
            <td width="84"><div align="center">
                    <input name="mat" type="text" class='centro' id="mat" OnKeyPress="formatar(this, '##/##/####')"  value="<?= tratarHTML($mat); ?>" size="9" readonly='10' >
                    <input name="cmd" type="hidden" class='centro' id="cmd"  value="<?= tratarHTML($cmd); ?>" size="9" readonly='10' ></div>
            </td>
            <td> <p align="left">
                    <input name="nome" type="text" class='Caixa' id="nome" OnKeyPress="formatar(this, '##/##/####')"  value="<?= tratarHTML($nome); ?>" size="60" readonly='70' >
            </td>
            <td><div align="center">
                    <input name="lotacao" type="text" class='centro' id="lotacao" OnKeyPress="formatar(this, '##/##/####')"  value="<?= tratarHTML($sLotacao); ?>" size="15" readonly='10'  >
                    <input name="ip" type="hidden" class='centro' id="ip"  value="<?= tratarHTML($ip); ?>" size="9" readonly='10' ></div>
            </td>
        </tr>
        <tr>
            <td height="20" colspan="3" bgcolor="#DFDFBF"> <p align="center"><b>Justificativa para abono de ocorr&ecirc;ncia</b> </td>
        </tr>
        <tr>
            <td colspan="3"><div align="center"><font face="Tahoma" size="2" color="#333300"><b>
                        <input name="dia" type="text" class='centro' id="dia"  value="<?= tratarHTML($dia); ?>" size="10" readonly='10' >
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
                    <input name="oco" type="text" class='centro' id="oco"  value="<?= tratarHTML($oco); ?>" size="8" readonly='10' ></div>
            </td>
            <td colspan="2"><textarea name=just cols=80 rows=5 id="textarea2"><?= tratarHTML($justchef); ?></textarea></td>
        </tr>
    </table>
    <div align='center'>
        <p>
        <table border='0' align='center'>
            <tr>
                <td align='left'><?= botao('Concluir', 'javascript:return verificadados();'); ?></td>
                <td>&nbsp;&nbsp;&nbsp;</td>
                <td align='left'><?= botao('Voltar', 'javascript:window.location.replace("historico_frequencia_registros.php?dados=' . $_SESSION['voltar_nivel_1'] . '");'); ?></td>
            </tr>
        </table>
        </p>
    </div>

    <p>&nbsp;</p>
</div>
<p align="center" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6px;">&nbsp;
</p>
<font size="2" face="Tahoma"> </font>
</form>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
