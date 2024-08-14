<?php
include_once( "config.php" );

verifica_permissao("sAPS");

$sLotacao = $_SESSION["sLotacao"];

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    header("Location: acessonegado.php");
}
else
{
    // Valores passados - encriptados
    $dados = explode(":|:", base64_decode($dadosorigem));
    $siape = $dados[0];
    $lot   = $dados[1];
    $dia   = $dados[2];
}

// dados voltar
$_SESSION['voltar_nivel_2'] = "frequencia_acompanhar_registros_horario_servico.php?dados=" . $dadosorigem;
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';
$_SESSION['voltar_nivel_5'] = '';


## INSTANCIA CLASS - JORNADA
#
# - Le dados cadastrais e de setor
#
// verifica autorizacao
$oJornadaTE = new DefinirJornada();
$oJornadaTE->setSiape($siape);
$oJornadaTE->setLotacao($lot);
$oJornadaTE->setData($dia);
$oJornadaTE->setChefiaAtiva();

$oDBase   = $oJornadaTE->PesquisaJornadaHistorico($siape, $dia);
$oDBaseJH = $oDBase->fetch_object();

// turno estendido e situacao cadastral
$sAutorizadoTE = ($oDBaseJH->tipo == 'Turno Estendido' ? 'S' : 'N');
$ocupaFuncao   = $oJornadaTE->chefiaAtiva;

// jornada
$jornada = $oDBaseJH->jornada; // jornada do servidor (estendida ou normal) no formato 99
$jd      = ($jornada / 5);                   // jornada do servidor (estendida ou normal) por dia (jornada/5)
$j       = formata_jornada_para_hhmm($jd); // jornada do servidor (estendida ou normal) no formato HH:MM
$jnd     = $jornada;

// horários
$entra  = $oDBaseJH->entra_trab;  // horário de entrada
$intini = $oDBaseJH->ini_interv; // horário da saida para o almoco
$intsai = $oDBaseJH->sai_interv;     // horário do retorno do almoco
$sai    = $oDBaseJH->sai_trab;    // final do expediente



## classe para montagem do formulario padrao
#
$oForm  = new formPadrao();
$oForm->setSubTitulo("Registro de Hor&aacute;rio de Trabalho,<br>Autorização de Compensação e/ou Registro Fora do Horário da Unidade");

$javascript   = array();
$javascript[] = 'js/phpjs.js';
$javascript[] = "js/jquery.mask.min.js";
$javascript[] = 'frequencia_acompanhar_registros_horario_servico.js';

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

// instancia o banco de dados
$oDBase = new DataBase('PDO');

// seleciona nome do servidor e jornada
$oDBase->query("
SELECT
    a.mat_siape, a.nome_serv, a.cpf, a.cod_sitcad, a.cod_lot, b.cod_uorg,
    b.descricao, b.codmun, a.entra_trab, a.ini_interv, a.sai_interv,
    a.sai_trab, a.jornada, a.autchef, a.bhoras, a.bh_tipo, a.malt, a.chefia,
    a.horae, a.motivo, c.denominacao, c.sigla
FROM
    servativ AS a
LEFT JOIN
    tabsetor AS b ON a.cod_lot = b.codigo
LEFT JOIN
    taborgao AS c ON LEFT(a.cod_lot,5) = c.codigo
WHERE
    a.mat_siape = :siape
    AND (a.cod_sitcad NOT IN ('02','08','15'))
    AND a.excluido = 'N'
", array(
    array(':siape', $siape, PDO::PARAM_STR),
));

$nNumRows = $oDBase->num_rows();

if ($nNumRows > 0)
{
    $oServidor          = $oDBase->fetch_object();
    $tSiape             = $oServidor->mat_siape;
    $sNome              = $oServidor->nome_serv;
    $sCpf               = $oServidor->cpf;
    $sLotaca            = $oServidor->cod_lot;
    $sUorg              = $oServidor->cod_uorg;
    $malt               = $oServidor->malt;
    $chefia             = $oServidor->chefia;
    $horae              = $oServidor->horae;
    $motivo             = $oServidor->motivo;
    $autchef            = $oServidor->autchef;
    $bhoras             = $oServidor->bhoras;
    $bhtipo             = $oServidor->bh_tipo;
    $wnomelota          = $oServidor->descricao;
    $orgao_denominacao  = $oServidor->denominacao;
    $orgao_sigla        = $oServidor->sigla;
    $wnomelota          = $oServidor->descricao;
    $codmun             = $oServidor->codmun;
    $situacao_cadastral = $oServidor->cod_sitcad;
}
else
{
    mensagem("Servidor não está ativo ou inexistente!", null, 1);
}

/* obtem dados da UORG para saber se eh a mesma do usuario */
if ($_SESSION["sAPS"] == "S" && $sLotaca != $lot && $chefia == "N")
{
    mensagem("Não é permitido consultar/alterar servidor de outro setor!", null, 1);
}
?>
<script>
    $('.horas').mask('00:00');
</script>

<form id="form1" name="form1" method="POST" onsubmit="return false;">
    <input type="hidden" name="sUorg"         id='sUorg'         value='<?= tratarHTML($sUorg); ?>'>
    <input type="hidden" name="sitcad"        id='sitcad'        value='<?= tratarHTML($situacao_cadastral); ?>'>
    <input type="hidden" name="logado"        id='logado'        value='<?= tratarHTML($logado); ?>'>
    <input type="hidden" name="dia"           id="dia"           value='<?= tratarHTML($dia); ?>'>
    <input type="hidden" name="imin"          id="imin"          value='<?= "1"; ?>'>
    <input type="hidden" name="imax"          id="imax"          value='<?= "3"; ?>'>
    <input type="hidden" name="jornada"       id="jornada"       value='<?= tratarHTML($jornada); ?>'>
    <input type="hidden" name="jd"            id="jd"            value='<?= tratarHTML($j); ?>'>
    <input type="hidden" name="inisetor"      id="inisetor"      value='<?= tratarHTML($inisetor); ?>'>
    <input type="hidden" name="fimsetor"      id="fimsetor"      value='<?= tratarHTML($fimsetor); ?>'>
    <input type="hidden" name="minuto"        id="minuto"        value='60' size="11">
    <input type="hidden" name="horae"         id="horae"         value='<?= tratarHTML($horae); ?>'>
    <input type="hidden" name="motivo"        id="motivo"        value='<?= tratarHTML($motivo); ?>'>
    <input type="hidden" name="jd2"           id="jd2"           value='<?= tratarHTML($jd); ?>'>
    <input type="hidden" name="codmun"        id="codmun"        value='<?= tratarHTML($codmun); ?>'>
    <input type="hidden" name="sAutorizadoTE" id="sAutorizadoTE" value='<?= tratarHTML($sAutorizadoTE); ?>'>
    <input type="hidden" name="ocupaFuncao"   id="ocupaFuncao"   value="<?= tratarHTML($ocupaFuncao); ?>">
    <input type="hidden" name="sCpf"          id='sCpf'          value='<?= tratarHTML($sCpf); ?>'>
    <input type="hidden" name="bhtipo"        id='bhtipo'        value='<?= tratarHTML($bhtipo); ?>'>
    <input type="hidden" name="dados"         id='dados'         value='<?= tratarHTML(base64_encode($tSiape . ':|:' . $sCpf . ':|:' . $jornada)); ?>'>

    <p style="word-spacing: 0; line-height: 95%; margin-left: 22px; margin-right: 0; margin-top: 6px; margin-bottom: 0" align="left">
        <strong><font size="2" face="Tahoma">Dados do Servidor:</font></strong></p>
    <table class="table table-condensed table-bordered text-center">
        <tbody>
            <tr>
                <td colspan="2" style="text-align:left;width:619px;height:46px">
                    <font class="ft_13_003">&nbsp;Nome</font>
                    &nbsp;<input type="text" id="sNome" name="sNome" class="form-control" size="60" maxlength="60" value="<?= tratarHTML($sNome); ?>" readonly>
                </td>
                <td colspan="1" style="text-align:left;width:144px">
                    <font class="ft_13_003">&nbsp;Matrícula</font>
                    &nbsp;<input type="text" id="tSiape" name="tSiape" class="form-control" size="7" maxlength="7" value="<?= tratarHTML(removeOrgaoMatricula( $tSiape )); ?>" readonly>
                </td>
            </tr>
            <tr>
                <td colspan="1" style="text-align:left;width:220px;height:46px">
                    <font class="ft_13_003">&nbsp;Órgão</font>
                    &nbsp;<input type="text" id="lota" name="lota" class="form-control" size="19" maxlength="19" value="<?= tratarHTML(getOrgaoMaisSigla( $sLotaca )); ?>" size="5" readonly>
                </td>
                <td colspan="2" style="text-align:left;width:619px;height:46px">
                    <font class="ft_13_003">&nbsp;Unidade de Exercício&nbsp;
                    <?php

                    if ($sAutorizadoTE == 'S' && $jd < 8 && $ocupaFuncao == 'N' && $situacao_cadastral != '66')
                    {
                        ?>
                        &nbsp;(<small><i>Unidade com Turno Estendido</i></small>)
                        <?php
                    }

                    ?>
                    </font>
                    &nbsp;<input type="text" id="nomelota" name="nomelota" class="form-control" size="70" maxlength="70" value="<?= tratarHTML(getUorgMaisDescricao( $sLotaca )); ?>" readonly>
                </td>
            </tr>
        </tbody>
    </table>

    <table class="table table-striped table-condensed table-bordered text-center">
        <tbody>
            <tr>
                <td width="90" height="22">
                    <p align="center" style="margin-top: 0; margin-bottom: 0">
                        <font size="2" face="Tahoma">Jornada</font>
                    </p>
                </td>
                <td width="110" height="22">
                    <p align="center" style="margin-top: 0; margin-bottom: 0">
                        <font size="2" face="Tahoma">Entrada</font>
                    </p>
                </td>
                <td colspan="2" width="220">
                    <div align="center">
                        <font size="2" face="Tahoma">Intervalo</font>
                    </div>
                </td>
                <td width="110" align="center" >
                    <p align="center" style="margin-top: 0; margin-bottom: 0">
                        <font size="2" face="Tahoma">Sa&iacute;da</font>
                </td>
                <td width="159" align="center" >
                    <font size="2" face="Tahoma">Compensação&nbsp;<sup>(1)</sup><br>Autorizada?</font>
                </td>
                <td width="159" align="center" >
                    <font size="2" face="Tahoma">Autoriza Registro&nbsp;<sup>(2)</sup><br>Fora do Horário da Unidade?</font>
                </td>
            </tr>

            <!--
            HORÁRIO DE TRABALHO
            -->
            <tr>
                <td align="center">
                    <input type="text" class='form-control text-center' value='<?= $j; ?>' size="3" maxlength="2" readonly>
                </td>
                <?php
                if (($sAutorizadoTE == 'S' || $jd < 8) && $ocupaFuncao == 'N')
                {
                    ?>
                    <td align="center">
                        <input type="text"
                               id="entra" name="entra"
                               class='form-control text-center horas'
                               value='<?= tratarHTML(substr($entra, 0, 5)); ?>'
                               size="6" maxlength="5"
                               title="Digite os cinco carecteres diretamente sem pontos!"
                               OnKeyUp="calculaHorario(this);">
                    </td>
                    <td width="90" align="center">
                        <input type="text"
                               id="intini" name="intini"
                               class='form-control text-center horas'
                               value='00:00'
                               size="6" maxlength="5"
                               style='border: 0px black solid;'
                               readonly>
                    </td>
                    <td width="91" align="center">
                        <input type="text"
                               id="intsai" name="intsai"
                               class='form-control text-center horas'
                               value='00:00'
                               size="6" maxlength="5"
                               style='border: 0px black solid;'
                               readonly>
                    </td>
                    <td width="110" align="center">
                        <input type="text"
                               id="sai" name="sai"
                               class='form-control text-center horas'
                               value='<?= tratarHTML(substr($sai, 0, 5)); ?>'
                               size="6" maxlength="5"
                               style='border: 0px black solid;'
                               readonly>
                    </td>
                    <?php
                }
                else
                {
                    ?>
                    <td>
                        <input type="text"
                               id="entra" name="entra"
                               class='form-control text-center horas'
                               value='<?= tratarHTML(substr($entra, 0, 5)); ?>'
                               size="6" maxlength="5"
                               title="Digite os cinco carecteres diretamente sem pontos!">
                    </td>
                    <td width="90">
                        <input type="text"
                               id="intini" name="intini"
                               class="form-control text-center horas"
                               value='<?= tratarHTML(substr($intini, 0, 5)); ?>'
                               size="6" maxlength="5"
                               title="Digite os cinco carecteres diretamente sem pontos!">
                    </td>
                    <td width="91" align="center" >
                        <input type="text"
                               id="intsai" name="intsai"
                               class="form-control text-center horas"
                               value='<?= tratarHTML(substr($intsai, 0, 5)); ?>'
                               size="6" maxlength="5"
                               title="Digite os cinco carecteres diretamente sem pontos!">
                    </td>
                    <td width="110" align="center" >
                        <input type="text"
                               id="sai" name="sai"
                               class="form-control text-center horas"
                               value='<?= tratarHTML(substr($sai, 0, 5)); ?>'
                               size="6" maxlength="5"
                               title="Digite os cinco carecteres diretamente sem pontos!">
                    </td>
                    <?php
                }

                $jd = $jd * 60;
                ?>
                <td width='159' align='center' >
                    <?php
                    if ($_SESSION["sAPS"] == "S")
                    {
                        ?>
                        <select id='bhoras' name='bhoras' class="form-control text-center">
                            <option value='00'>SELECIONE </option>
                            <option value='N'<?= ($bhoras == "N" ? " selected" : ""); ?>>N&Atilde;O</option>
                            <option value='S'<?= ($bhoras == "S" ? " selected" : ""); ?>>SIM </option>
                        </select>
                        <?php
                        /*
                          ALTERAÇÃO CANCELADA
                          <select name='bhoras' id='bhoras' style = 'font-size: 12'>
                          <option value='9'>SELECIONE </option>
                          <option value='0'<?= ($bhtipo == "0" ? " selected" : ""); ?>>NÃO</option>
                          <option value='1'<?= ($bhtipo == "1" ? " selected" : ""); ?>>SIM - No início do expediente</option>
                          <option value='2'<?= ($bhtipo == "2" ? " selected" : ""); ?>>SIM - No final do expediente</option>
                          <option value='3'<?= ($bhtipo == "3" ? " selected" : ""); ?>>SIM - Ambos</option>
                          </select></font>";
                         */
                    }
                    else
                    {
                        ?>
                        <input type='hidden' id='bhoras' name='bhoras' value='<?= tratarHTML($bhoras); ?>'>
                        <?php
                    }
                    ?>
                </td>
                <td width='159' align='center' >
                    <?php
                    if ($_SESSION["sAPS"] == "S")
                    {
                        ?>
                        <select id='autchef' name='autchef' class="form-control text-center">
                            <option value='00'>SELECIONE </option>
                            <option value='N'<?= ($autchef == "N" ? " selected" : ""); ?>>N&Atilde;O</option>
                            <option value='S'<?= ($autchef == "S" ? " selected" : ""); ?>>SIM </option>
                        </select>
                        <?php
                        /*
                          ALTERAÇÃO CANCELADA
                          <select name='bhoras' id='bhoras' style = 'font-size: 12'>
                          <option value='9'>SELECIONE </option>
                          <option value='0'<?= ($bhtipo == "0" ? " selected" : ""); ?>>NÃO</option>
                          <option value='1'<?= ($bhtipo == "1" ? " selected" : ""); ?>>SIM - No início do expediente</option>
                          <option value='2'<?= ($bhtipo == "2" ? " selected" : ""); ?>>SIM - No final do expediente</option>
                          <option value='3'<?= ($bhtipo == "3" ? " selected" : ""); ?>>SIM - Ambos</option>
                          </select></font>";
                         */
                    }
                    else
                    {
                        ?>
                        <input type='hidden' id='autchef' name='autchef' value='<?= tratarHTML($autchef); ?>'>
                        <?php
                    }
                    ?>
                </td>
            </tr>
        </tbody>
    </table>
    <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#808040" width="100%" id="AutoNumber1">
        <tr>
            <td style='height: 44px; vertical-align: top; font-family: Tahoma; font-size: 10px;' nowrap><sup>(1)</sup> Compensação de faltas justificadas, atrasos ou saídas antecipadas.<br><sup>(2)</sup> Permite o registro da frequência fora do horário de funcionamento da unidade.</td>
        </tr>
    </table>

    <div class="form-group col-md-12 text-center">
        <div class="col-md-2"></div>
        <div class="col-md-2 col-xs-6 col-md-offset-2">
            <a class="btn btn-success btn-block" id="btn-continuar-horario" role="button">
                <span class="glyphicon glyphicon-ok"></span> Gravar
            </a>
        </div>
        <div class="col-md-2 col-xs-6">
            <a class="btn btn-danger btn-block" id="btn-voltar" href="javascript:window.location.replace('<?= $_SESSION['voltar_nivel_1']; ?>')" role="button">
                <span class="glyphicon glyphicon-ok"></span> Voltar
            </a>
        </div>
        <div class="col-md-2"></div>
    </div>

</form>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
