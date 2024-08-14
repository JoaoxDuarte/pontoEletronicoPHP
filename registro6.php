<?php
include_once("config.php");
include_once( "class_ocorrencias_grupos.php" );

verifica_permissao("sAPS");

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    $mat     = anti_injection($_REQUEST["mat"]);
    $nome    = anti_injection($_REQUEST["nome"]);
    $lotacao = anti_injection($_REQUEST["sLotacao"]);
    $ocor    = anti_injection($_REQUEST["ocor"]);
    $cmd     = 1;
    $dia     = $_REQUEST["dia"];
    $hs      = anti_injection($_REQUEST["hs"]);
    $jnd     = anti_injection($_REQUEST["jnd"]) / 5;
    $diac    = conv_data($dia);
}
else
{
    $dados   = explode(":|:", base64_decode($dadosorigem));
    $mat     = $dados[0];
    $nome    = $dados[1];
    $lotacao = $dados[2];
    $ocor    = $dados[3];
    $cmd     = $dados[4]; // $cmd = 1;
    $dia     = $dados[5];
    $hs      = $dados[6];
    $jnd     = $dados[7];
    //$jnd  = $dados[7] / 5;
    $diac    = conv_data($dia);
}

// historico de navegacao (substituir o $_SESSION['sPaginaRetorno_sucesso']
$sessao_navegacao = new Control_Navegacao();
$sessao_navegacao->setPagina($_SERVER['REQUEST_URI']);

// ocorrências grupos
$obj = new OcorrenciasGrupos();
$CodigosCompensaveis        = $obj->GrupoOcorrenciasNegativasDebitos($sitcad=null, $exige_horarios=true);
$CodigosCredito             = $obj->CodigosCredito($sitcad=null,$temp=false);
$DebitoSoDiaUtil            = $obj->CodigosCompensaveis($sitcad=NULL);
$CodigoCreditoRecessoPadrao = $obj->CodigoCreditoRecessoPadrao($sitcad=NULL);
$CodigoDebitoRecessoPadrao  = $obj->CodigoDebitoRecessoPadrao($sitcad=NULL);

// instancia BD
$oDBase = new DataBase('PDO');

## periodo recesso
#
$oData      = new trata_datasys();
$anoAtual   = $oData->getAno();
$anoProximo = $oData->getAnoSeguinte();

$$oDBase->query("SELECT periodo, recesso_inicio, recesso_fim, recesso_inicio_compensacao, recesso_fim_compensacao FROM tabrecesso_fimdeano WHERE periodo= :periodo ", array(
    array( ':periodo', $anoAtual . '/' . $anoProximo, PDO::PARAM_STR )
));

$oRecesso      = $oDBase->fetch_object();
$recessoi      = $oRecesso->recesso_inicio_compensacao;
$recessof      = $oRecesso->recesso_fim_compensacao;
$recessoUsoIni = $oRecesso->recesso_inicio;
$recessoUsoFim = $oRecesso->recesso_fim;

// hora atual
$vHoras = strftime("%H:%M:%S", time());

// dia util
$diaUtil = (verifica_se_dia_nao_util($dia, $lotacao) == true ? 'N' : 'S');

// competencia
$comp = date('mY');

## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Frequência » ... » Ocorrência');
$oForm->setSubTitulo("Registro de Ocorr&ecirc;ncia");

// Topo do formulário
//
$oForm->setJS('registro6.js');

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

?>
<form action="gravaregfreq1.php?modo=6" method="POST" id="form1" name="form1" onSubmit="return verificadados()"  >

    <input type='hidden' id='diaUtil'   name='diaUtil'   value='<?= tratarHTML($diaUtil); ?>'>
    <input type='hidden' id='compete'   name='compete'   value='<?= tratarHTML($comp); ?>'>
    <input type='hidden' id='horsaida'  name='horsaida'  value='<?= tratarHTML($hs); ?>'>
    <input type='hidden' id='horsaida2' name='horsaida2' value='<?= tratarHTML($vHoras); ?>'>
    <input type='hidden' id='jnd'       name='jnd'       value='<?= tratarHTML($jnd); ?>'>
    <input type='hidden' id="inirec"    name='inirec'    value='<?= tratarHTML($recessoi); ?>'>
    <input type='hidden' id="fimrec"    name='fimrec'    value='<?= tratarHTML($recessof); ?>'>
    <input type='hidden' id="inirecUso" name='inirecUso' value='<?= tratarHTML($recessoUsoIni); ?>'>
    <input type='hidden' id="fimrecUso" name='fimrecUso' value='<?= tratarHTML($recessoUsoFim); ?>'>
    <input type='hidden' id="hoje"      name='hoje'      value='<?= tratarHTML($diac); ?>'>
    <input type='hidden' id="lotacao"   name='lotacao'   value='<?= tratarHTML($lotacao); ?>'>
    <input type='hidden' id="cmd"       name='cmd'       value='<?= tratarHTML($cmd); ?>'>

    <input type='hidden' id="debitosCompensaveis" name='debitosCompensaveis' value="<?= implode(',', $CodigosCompensaveis); ?>">
    <input type='hidden' id="codigosCreditos"     name='codigosCreditos'     value="<?= implode(',', $CodigosCredito); ?>">
    <input type='hidden' id="debitoSoDiaUtil"     name='debitoSoDiaUtil'     value="<?= implode(',', $DebitoSoDiaUtil); ?>">
    <input type='hidden' id="recessoCredito"      name='recessoCredito'      value="<?= implode(',', $CodigoCreditoRecessoPadrao); ?>">
    <input type='hidden' id="recessoDebito"       name='recessoDebito'       value="<?= implode(',', $CodigoDebitoRecessoPadrao); ?>">

    <p align="center" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6; margin-bottom: 0">
        <b>
        <h3>
        <div align="center">
            <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#808040" width="95%" id="AutoNumber1">
                <tr>
                    <td colspan="2" class="ft_13_002">
                        Dados do Servidor:
                    </td>
                </tr>
            </table>
            <table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#808040" width="95%" id="AutoNumber1">
                <tr>
                    <td width="619" height="46" class="tahomaSize_2">
                        Nome:<br><input type="text" id="nome" name="nome" class='caixa' value='<?= tratarHTML($nome); ?>' size="60" readonly>
                        <div align="center"></div>
                        <div align="center"></div>
                    </td>
                    <td width="144" align="center" class="tahomaSize_2">
                        Mat.Siape:<br><input type="text" id="mat" name="mat" class='caixa' value='<?= tratarHTML($mat); ?>' size="7" readonly>
                    </td>
                </tr>
            </table>
            <table width="95%" border="1" cellpadding="0" cellspacing="0" bordercolor="#808040" id="AutoNumber1" style="border-collapse: collapse; margin-bottom: 0;">
                <tr>
                    <td width="81%" height="39" align="left" class="tahomaSize_2">
                        C&oacute;digo da Ocorr&ecirc;ncia:<br>
                        <select name="ocor" size="1" class="drop" id="ocor">
                            <?php

                            // tabela de ocorrencia
                            $oDBase->query("SELECT siapecad, desc_ocorr, cod_ocorr FROM tabocfre WHERE resp IN ('CH' ,'AB') AND (siapecad != '00195') AND ativo = 'S' ORDER BY desc_ocorr ");
                            while ($campo = $oDBase->fetch_object())
                            {
                                echo "<option VALUE=\"" . tratarHTML($campo->siapecad) . "\"";
                                echo ($campo->siapecad == $ocor ? ' selected' : '') . ">";
                                echo tratarHTML($campo->siapecad) . " - " . tratarHTML(substr($campo->desc_ocorr, 0, 60)) . " -  " . (empty($campo->desc_ocorr) ? "Seleciona uma ocorrência" : "SIRH") . " " . tratarHTML($campo->cod_ocorr);
                                echo "</option>";
                            }
                            // Fim da tabela de ocorrencia

                            ?>
                        </select>

                        <a href= "javascript:Abre('tabocfre.php',1060,350)"><img border= '0' src='<?= _DIR_IMAGEM_; ?>pesquisa.gif' width='17' height='17' align='absmiddle' alt='Visualizar detalhes da ocorrência.'></a>
                    </td>
                    <td width="19%" align="center" class="tahomaSize_2">
                        Dia da Ocorr&ecirc;ncia:<br><input type="text" id="dia" name="dia" class='centro' value='<?= tratarHTML($dia); ?>' size="11" readonly>
                    </td>
                </tr>
            </table>
        </div>

        <div align='center'>
            <p>
            <table border='0' align='center'>
                <tr>
                    <td align='center'><?= botao('Continuar', 'javascript:return verificadados();'); ?></td>
                    <td align='center'>&nbsp;&nbsp;</td>
                    <td align='center'><?= botao('Voltar', 'javascript:voltar(1,"' . $sessao_navegacao->getPagina(0) . '");'); ?></td>
                </tr>
            </table>
            </p>
        </div>
    </p>

</form>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
