<?php
include_once( "config.php" );

verifica_permissao("sAPS");

$sLotacao = $_SESSION["sLotacao"];

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    // le dados passados por formulario
    $var1 = anti_injection($_GET["mat"]);
    $lot  = anti_injection($_GET["lot"]);
    $dia  = date('d/m/Y');
}
else
{
    // Valores passados - encriptados
    $dados = explode(":|:", base64_decode($dadosorigem));
    $var1  = $dados[0];
    $lot   = $dados[1];
    $dia   = $dados[2];
}

## INSTANCIA CLASSE
#
# - Le dados cadastrais e de setor
#
// verifica autorizacao
$oJornadaTE = new DefinirJornada();
$oJornadaTE->setSiape($var1);
$oJornadaTE->setLotacao($lot);
$oJornadaTE->setData($dia);
$oJornadaTE->setChefiaAtiva();

$oDBase   = $oJornadaTE->PesquisaJornadaHistorico($var1, $dia);
$oDBaseJH = $oDBase->fetch_object();

// turno estendido e situacao cadastral
$sAutorizadoTE      = ($oDBaseJH->tipo == 'Turno Estendido' ? 'S' : 'N');
$ocupaFuncao        = $oJornadaTE->chefiaAtiva;
$situacao_cadastral = $oJornadaTE->situacao_cadastral;

// jornada
$jornada = $oDBaseJH->jornada; // jornada do servidor (estendida ou normal) no formato 99
$jd      = ($jornada / 5);                   // jornada do servidor (estendida ou normal) por dia (jornada/5)
$j       = formata_jornada_para_hhmm($jd); // jornada do servidor (estendida ou normal) no formato HH:MM
$jnd     = $jornada;

// horários
$entra                               = $oDBaseJH->entra_trab;  // horário de entrada
$intini                              = $oDBaseJH->ini_interv; // horário da saida para o almoco
$intsai                              = $oDBaseJH->sai_interv;     // horário do retorno do almoco
$sai                                 = $oDBaseJH->sai_trab;    // final do expediente
//////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////
///                                                    ///
//   mensagem( 'Serviço suspenso temporariamente!' );  ///
//   voltar(1);                                        ///
///                                                    ///
//////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////
// dados para retorno a este script
$_SESSION['sPaginaRetorno_sucesso2'] = $_SERVER['REQUEST_URI'] . "&mat=$var1&lot=$lot";

// instancia o banco de dados
// seleciona nome do servidor e jornada
$oDBase   = new DataBase('PDO');
$oDBase->query("SELECT a.mat_siape, a.nome_serv, a.cpf, a.cod_lot, b.cod_uorg, b.descricao, b.codmun, a.entra_trab, a.ini_interv, a.sai_interv, a.sai_trab, a.jornada, a.autchef, a.bhoras, a.bh_tipo, a.malt, a.chefia, a.horae, a.motivo FROM servativ AS a LEFT JOIN tabsetor AS b ON a.cod_lot=b.codigo WHERE a.mat_siape='$var1' AND NOT IN ('02','15') AND excluido='N' ");
$nNumRows = $oDBase->num_rows();

if ($nNumRows > 0)
{
    $oServidor = $oDBase->fetch_object();
    $tSiape    = $oServidor->mat_siape;
    $sNome     = $oServidor->nome_serv;
    $sCpf      = $oServidor->cpf;
    $sLotaca   = $oServidor->cod_lot;
    $sUorg     = $oServidor->cod_uorg;
    $malt      = $oServidor->malt;
    $chefia    = $oServidor->chefia;
    $horae     = $oServidor->horae;
    $motivo    = $oServidor->motivo;
    $autchef   = $oServidor->autchef;
    $bhoras    = $oServidor->bhoras;
    $bhtipo    = $oServidor->bh_tipo;
    $wnomelota = $oServidor->descricao;
    $codmun    = $oServidor->codmun;
}
else
{
    //header("Location: mensagem.php?modo=5");
    mensagem("Servidor não está ativo ou inexistente!", null, 1);
}

/* obtem dados da UORG para saber se eh a mesma do usuario */
if ($_SESSION["sAPS"] == "S" && $sLotaca != $lot && $chefia == "N")
{
    //header("Location: mensagem.php?modo=24");
    mensagem("Não é permitido consultar/alterar servidor de outro setor!", null, 1);
}


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho("Horário");
$oForm->setSeparador(0);
$oForm->setLargura('920px');
$oForm->setJQuery();
$oForm->setJS('reghora.js');

$oForm->setSubTitulo("Registro de Hor&aacute;rio de Trabalho");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<form id="form1" name="form1" method="POST" action="gravahorario.php?modo=1" onsubmit="return verificadados()">
    <input type="hidden" name="sUorg" id='sUorg' value='<?= tratarHTML($sUorg); ?>'>
    <input type="hidden" name="sitcad" id='sitcad' value='<?= tratarHTML($situacao_cadastral); ?>'>
    <p align="left" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6; margin-bottom: 0"><strong><font size="2" face="Tahoma">Identificação do Servidor:</font></strong></p>
    <table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#808040" width="100%" id="AutoNumber1">
        <tr>
            <td height="46">
                <p style="margin-top: 0; margin-bottom: 0"><font size="2" face="Tahoma">&nbsp;Nome:</font></p>
                <p style="margin-top: 0; margin-bottom: 0">
                    &nbsp;<input name="sNome" type="text" class='caixa' id="sNome" value='<?= tratarHTML($sNome); ?>' size="60" readonly>
            </td>
            <td align='center' nowrap>
                <p style="margin-top: 0; margin-bottom: 0"><font size="2" face="Tahoma">&nbsp;Mat.Siape:</font></p>
                <p style="margin-top: 0; margin-bottom: 0">
                    &nbsp;<input name="tSiape" type="text" class='caixa' id="tSiape" value='<?= tratarHTML($tSiape); ?>' size="7" readonly>
            </td>
        </tr>
    </table>
    <table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#808040" width="100%">
        <tr>
            <td height="44" rowspan="2" nowrap>
                <p align="left" style="margin-top: 0; margin-bottom: 0"><font size="2" face="Tahoma">&nbsp;Lota&ccedil;&atilde;o atual :
                    <?php
                    if ($sAutorizadoTE == 'S' && $jd < 8 && $ocupaFuncao == 'N' && $situacao_cadastral != '66')
                    {
                        print ' <i>Unidade com Turno Estendido</i>';
                    }
                    ?>
                    </font></p>
                <p align="left" style="margin-top: 0; margin-bottom: 0">
                    &nbsp;<input name="lota" type="text" class='centro' id="lota" value="<?= tratarHTML($sLotaca); ?>" size="11" readonly>
                    &nbsp;-
                    &nbsp;<input name="nomelota" type="text" class='centro' id="nomelota" value="<?= tratarHTML($wnomelota); ?>" size="70" readonly>&nbsp;
            </td>
            <td width="92" height="22"> <p align="center" style="margin-top: 0; margin-bottom: 0"><font size="2" face="Tahoma">Entrada</font></p></td>
            <td colspan="2"><div align="center"><font size="2" face="Tahoma">Intervalo</font></div></td>
            <td width="110" align="center" > <p align="center" style="margin-top: 0; margin-bottom: 0"><font size="2" face="Tahoma">Sa&iacute;da</font></td>
            <td width="259" align="center" ><font size="2" face="Tahoma">Compensação&nbsp;<sup>(1)</sup> Autorizada ?</font></td>
        </tr>
        <?php
        if (($sAutorizadoTE == 'S' || $jd < 8) && $ocupaFuncao == 'N')
        {
            ?>
            <tr>
                <td align="center"><input name="entra" type="text" class='centro' id="entra"  title = " Digite os cinco carecteres diretamente sem pontos!" OnKeyUp="calculaHorario(this);formatar(this, '##:##');" value='<?= tratarHTML(substr($entra, 0, 5)); ?>' size="6" maxlength="5"></td>
                <td width="90" align="center"><input name="intini" type="text" class='centro' id="intini" value='00:00' size="6" maxlength="5" style='border: 0px black solid;' readonly></td>
                <td width="91" align="center"><input name="intsai" type="text" class='centro' id="intsai" value='00:00' size="6" maxlength="5" style='border: 0px black solid;' readonly></td>
                <td width="110" align="center"><input name="sai" type="text" class='centro' id="sai" value='<?= tratarHTML(substr($sai, 0, 5)); ?>' size="6" maxlength="5" style='border: 0px black solid;' readonly></td>
                <td width='259' align='center' >
                    <?php
                    if ($_SESSION["sAPS"] == "S")
                    {
                        echo "
						<select name='bhoras' id='bhoras' align='center'>
						<option value='00'>SELECIONE </option>
						<option value='S' ";
                        if ($bhoras == "S")
                            echo "selected";
                        echo">SIM </option>
						<option value='N' ";
                        if ($bhoras == "N")
                            echo "selected";
                        echo">N&Atilde;O</option>
						</select>";
                        /*
                          ALTERAÇÃO CANCELADA
                          echo "
                          <td width='124' align='center' ><font size='2' face='Tahoma'>
                          <select name='bhoras' id='bhoras' style = 'font-size: 12'>
                          <option value='9'>SELECIONE </option>
                          <option value='0' ";  if($bhtipo == "0") echo "selected"; echo">NÃO</option>
                          <option value='1' "; if($bhtipo == "1") echo "selected"; echo">SIM - No início do expediente</option>
                          <option value='2' "; if($bhtipo == "2") echo "selected"; echo">SIM - No final do expediente</option>
                          <option value='3' "; if($bhtipo == "3") echo "selected"; echo">SIM - Ambos</option>
                          </select></font>";
                         */
                    }
                    else
                    {
                        echo "<input type='hidden' id='bhoras' name='bhoras' value='$bhoras'>";
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
        else
        {
            ?>
            <tr>
                <td><div align="center">
                        <input name="entra" type="text" class='centro' id="entra"  title = " Digite os cinco carecteres diretamente sem pontos!" OnKeyPress="formatar(this, '##:##')"  value='<?= tratarHTML(substr($entra, 0, 5)); ?>' size="6" maxlength="5">
                    </div></td>
                <td width="90"> <div align="center"></div>
                    <div align="center">
                        <input name="intini" type="text" class='centro' id="intini" title = " Digite os cinco carecteres diretamente sem pontos!" OnKeyPress="formatar(this, '##:##')"  value='<?= tratarHTML(substr($intini, 0, 5)); ?>' size="6" maxlength="5">
                    </div></td>
                <td width="91" align="center" ><input name="intsai" type="text" class='centro' id="intsai" title = " Digite os cinco carecteres diretamente sem pontos!" OnKeyPress="formatar(this, '##:##')"  value='<?= tratarHTML(substr($intsai, 0, 5)); ?>' size="6" maxlength="5" ></td>
                <td width="110" align="center" ><input name="sai" type="text" class='centro' id="sai" title = " Digite os cinco carecteres diretamente sem pontos!" OnKeyPress="formatar(this, '##:##')"  value='<?= tratarHTML(substr($sai, 0, 5)); ?>' size="6" maxlength="5"></td>
                <td width='259' align='center' >
                    <?php
                    if ($_SESSION["sAPS"] == "S")
                    {
                        echo "
						<select name='bhoras' id='bhoras' align='center'>
						<option value='00'>SELECIONE </option>
						<option value='S' ";
                        if ($bhoras == "S")
                            echo "selected";
                        echo">SIM </option>
						<option value='N' ";
                        if ($bhoras == "N")
                            echo "selected";
                        echo">N&Atilde;O</option>
						</select>";
                        /*
                          ALTERAÇÃO CANCELADA
                          echo "
                          <td width='124' align='center' ><font size='2' face='Tahoma'>
                          <select name='bhoras' id='bhoras' style = 'font-size: 12'>
                          <option value='9'>SELECIONE </option>
                          <option value='0' ";  if($bhtipo == "0") echo "selected"; echo">NÃO</option>
                          <option value='1' "; if($bhtipo == "1") echo "selected"; echo">SIM - No início do expediente</option>
                          <option value='2' "; if($bhtipo == "2") echo "selected"; echo">SIM - No final do expediente</option>
                          <option value='3' "; if($bhtipo == "3") echo "selected"; echo">SIM - Ambos</option>
                          </select></font>";
                         */
                    }
                    else
                    {
                        echo "<input type='hidden' id='bhoras' name='bhoras' value='$bhoras' readonly>";
                    }
                    ?>
                </td>
            </tr>
            <?php
        }

        $jd = $jd * 60;
        ?>
    </table>
    <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#808040" width="100%" id="AutoNumber1">
        <tr>
            <td style='height: 44px; vertical-align: top; font-family: Tahoma; font-size: 10px;' nowrap><sup>(1)</sup> Compensação de Faltas justificadas, Atrasos ou Saídas Antecipadas.</td>
        </tr>
    </table>
    <input type="hidden" name="logado"   id='logado'   value='<?= tratarHTML($logado); ?>'>
    <input type="hidden" name="dia"      id="dia"      value='<?= tratarHTML($dia); ?>'>
    <input type="hidden" name="imin"     id="imin"     value='<?= "1"; ?>'>
    <input type="hidden" name="imax"     id="imax"     value='<?= "3"; ?>'>
    <input type="hidden" name="jornada"  id="jornada"  value='<?= tratarHTML($jornada); ?>'>
    <input type="hidden" name="jd"       id="jd"       value='<?= tratarHTML($j); ?>'>
    <input type="hidden" name="inisetor" id="inisetor" value='<?= tratarHTML($inisetor); ?>'>
    <input type="hidden" name="fimsetor" id="fimsetor" value='<?= tratarHTML($fimsetor); ?>'>
    <input type="hidden" name="minuto"   id="minuto"   value='60' size="11">
    <input type="hidden" name="horae"    id="horae"    value='<?= tratarHTML($horae); ?>'>
    <input type="hidden" name="motivo"   id="motivo"   value='<?= tratarHTML($motivo); ?>'>
    <input type="hidden" name="jd2"      id="jd2"      value='<?= tratarHTML($jd); ?>'>
    <input type="hidden" name="codmun"   id="codmun"   value='<?= tratarHTML($codmun); ?>'>
    <input type="hidden" name="sAutorizadoTE" id="sAutorizadoTE" value='<?= tratarHTML($sAutorizadoTE); ?>'>
    <input type="hidden" name="ocupaFuncao" id="ocupaFuncao" value="<?= tratarHTML($ocupaFuncao); ?>">
    <input type="hidden" name="sCpf" id='sCpf' value='<?= tratarHTML($sCpf); ?>'>

    <p align="center" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6;"><strong>
            <input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>ok.gif" name="enviar" alt="Submeter os valores" align="center" >
        </strong></p>
    <p align="center" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6;">&nbsp;</p>
    <p align="center" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6">&nbsp;
    </p>
    <?php $jd = $j * 60; ?>
    <font size="2" face="Tahoma"> </font>
</form>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
