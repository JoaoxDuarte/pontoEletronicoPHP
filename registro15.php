<?php
include_once("config.php");
include_once( "class_ocorrencias_grupos.php" );

verifica_permissao("sAPS");

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    $mat  = anti_injection($_REQUEST["mat"]);
    $nome = anti_injection($_REQUEST["nome"]);
    $ocor = anti_injection($_REQUEST["ocor"]);
    $jnd  = anti_injection($_REQUEST["jnd"]);
    $cmd  = anti_injection($_REQUEST["cmd"]);
}
else
{
    $dados = explode(":|:", base64_decode($dadosorigem));
    $mat   = $dados[0];
    $ocor  = $dados[1];
    $nome  = $dados[2];
    $jnd   = $dados[3];
    $cmd   = $dados[4];
}

$oDBase = selecionaServidor($mat);
$sitcad = $oDBase->fetch_object()->sigregjur;

// ocorrências grupos
$obj = new OcorrenciasGrupos();
$codigoDebitoRecessoPadrao = $obj->CodigoDebitoRecessoPadrao($sitcad);
$ocorrenciasPorPeriodo     = $obj->OcorrenciasPorPeriodo($sitcad, $resp = 'CH');
$correnciaLimiteDias       = $obj->OcorrenciaLimiteDias($sitcad);


// ocorrencias com limites de dias, e este limite
$arrayMsg = "";
foreach($correnciaLimiteDias[0] as $val)
{
  $arrayMsg .= "limiteDias['".$val."'] = ".$correnciaLimiteDias[1][$val].';'.chr(13)."".chr(10);
}

$hoje = date('Y-d-m');

// instancia banco de dados
$oDBase = new DataBase('PDO');
$oDBase->setMensagem("Erro: ");
$oDBase->setDestino(pagina_de_origem());

$oDBase->query("SELECT recessoi, recessof FROM tabvalida WHERE ativo = 'S' ");
$oRecesso = $oDBase->fetch_object();
$recessoi = $oRecesso->recessoi;
$recessof = $oRecesso->recessof;

$oData = new trata_datasys;

if ($cmd == "1")
{
    $ano  = $oData->getAno();
    $mes  = $oData->getMes();
    $year = $ano;
    $comp = $mes . $year;
}
else
{
    $ano  = $oData->getAno();
    $mes  = $oData->getMesAnterior();
    $year = $oData->getAnoAnterior();
    $comp = $mes . $year;
}

// dados: horario e jornada
$vHoras = strftime("%H:%M:%S", time());
$jn     = $jnd / 5;


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Frequência » Acompanhar » Registrar Ocorrências por Período');
$oForm->setSubTitulo("Registro de Ocorr&ecirc;ncias por Período");
$oForm->setOnLoad("document.all['dia2'].focus()");

// Topo do formulário
//
$oForm->setJS('registro15.js');

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

?>
<script>
  var limiteDias = new Array();
  <?= $arrayMsg; ?>
</script>

<form method="POST" action="gravaregfreq2.php?modo=4" id="form1" name="form1" onSubmit="return validar()" >
    <p align="left" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6; margin-bottom: 0">
        <strong>
            <font size="2" face="Tahoma">
                Dados do Servidor:
                <input type='hidden' id='compete'   name='compete'   value='<?= tratarHTML($comp); ?>'>
                <input type='hidden' id='anoh'      name='anoh'      value='<?= tratarHTML($year); ?>'>
                <input type='hidden' id='cmd'       name='cmd'       value='<?= tratarHTML($cmd); ?>'>
                <input type='hidden' id='ano'       name='ano'       value='<?= tratarHTML($ano); ?>'>
                <input type='hidden' id='m'         name='m'         value='<?= tratarHTML($mes); ?>'>
                <input type='hidden' id='mes'       name='mes'       value='<?= tratarHTML($mes); ?>'>
                <input type='hidden' id='horsaida'  name='horsaida'  value='<?= tratarHTML($hs); ?>'>
                <input type='hidden' id='horsaida2' name='horsaida2' value='<?= tratarHTML($vHoras); ?>'>
                <input type='hidden' id="jnd"       name='jnd'       value='<?= tratarHTML($jn); ?>'>
                <input type='hidden' id="hom1"      name='hom1'      value='1'>
                <input type='hidden' id="inirec"    name='inirec'    value='<?= tratarHTML($recessoi); ?>'>
                <input type='hidden' id="fimrec"    name='fimrec'    value='<?= tratarHTML($recessof); ?>'>
                <input type='hidden' id="hoje"      name='hoje'      value='<?= tratarHTML($hoje); ?>'>

                <input type='hidden' id="recessoDebito" name='recessoDebito' value="<?= tratarHTML(implode(',', $codigoDebitoRecessoPadrao)); ?>">
            </font>
        </strong>
    </p>
    <div align="center">
        <table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#808040" width="99%" id="AutoNumber1">
            <tr>
                <td width="473" height="46">
                    <p style="margin-top: 0; margin-bottom: 0"><font size="2" face="Tahoma">
                        Nome:</font></p>
                    <p style="margin-top: 0; margin-bottom: 0">
                        <input name="nome" type="text" class='caixa' id="nome" value='<?= tratarHTML($nome); ?>' size="60" readonly="60">
                    <div align="center"></div>
                    <div align="center"></div>
                </td>
                <td width="153" align="center">
                    <p align="center" style="margin-top: 0; margin-bottom: 0"><font size="2" face="Tahoma">Mat.Siape:</font></p>
                    <p align="center" style="margin-top: 0; margin-bottom: 0">
                        <input name="mat" type="text" class='caixa' id="mat" value='<?= tratarHTML(removeOrgaoMatricula( $mat )); ?>' size="7" readonly>
                </td>
            </tr>
        </table>
        <table width="99%" border="1" cellpadding="0" cellspacing="0" bordercolor="#808040" id="AutoNumber1" style="border-collapse: collapse; margin-bottom: 0;">
            <tr>
                <td width="56%" height="42">
                    <p align="center" style="margin-top: 0; margin-bottom: 0"><font size="2" face="Tahoma">C&oacute;digo da Ocorr&ecirc;ncia:</font></p>
                    <p align="center" style="margin-top: 0; margin-bottom: 0">
                        <select name="ocor" size="1" class="drop" id="ocor">
                            <?php

                            // tabela de ocorrencia

                            // exibe a lista de ocorrencias
                            while ($campo = $ocorrenciasPorPeriodo->fetch_object())
                            {
                                echo "<option value=\"" . tratarHTML($campo->siapecad) . "\"";
                                if ($campo->siapecad == $ocor)
                                {
                                    echo " selected";
                                }
                                echo " >" . tratarHTML($campo->siapecad) . " - " . tratarHTML(substr($campo->desc_ocorr, 0, 60)) . " -  " . (empty($campo->desc_ocorr) ? "Seleciona uma ocorrência" : "SIRH") . " " . tratarHTML($campo->cod_ocorr) . "</option>";
                            }
                            // Fim da tabela de ocorrencia

                            ?>
                        </select>
                        <a href= "javascript:Abre('tabocfre.php',1060,350)"><img border= '0' src='<?= _DIR_IMAGEM_; ?>pesquisa.gif' width='17' height='17' align='absmiddle' alt='Visualizar detalhes da ocorrência.'></a>
                        <font size="2" face="Tahoma"> </font>
                </td>
                <td width="22%">
                    <p align="center" style="margin-top: 0; margin-bottom: 0"><font size="2" face="Tahoma">Inicio da Ocorr&ecirc;ncia:</font></p>
                    <p align="center" style="margin-top: 0; margin-bottom: 0">
                        <input name="dia2" type="text" class='Caixa' id="dia2"  OnKeyPress="formatar(this, '##/##/####')" size="11" maxlength="10" onkeyup="javascript:ve(this.value);">
                </td>
                <td width="22%">
                    <p align="center" style="margin-top: 0; margin-bottom: 0"><font size="2" face="Tahoma">Fim da Ocorr&ecirc;ncia:</font></p>
                    <p align="center" style="margin-top: 0; margin-bottom: 0">
                        <input name="dia" type="text" class='Caixa' id="dia"  onKeyPress="formatar(this, '##/##/####')" size="11" maxlength="10" >
                </td>
            </tr>
        </table>
    </div>
    <br>
    <br>
    <input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>concluir.gif" name="enviar" alt="incluir ocorrência" align="center" >
</form>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
