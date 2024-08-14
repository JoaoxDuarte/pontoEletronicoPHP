<?php
include_once("config.php");
include_once( "class_ocorrencias_grupos.php" );

verifica_permissao("sRH ou Chefia");

$mat  = anti_injection($_REQUEST['mat']);
$dia  = $_REQUEST['dia'];
$cmd  = anti_injection($_REQUEST['cmd']);
$ocor = anti_injection($_REQUEST['ocor']);


getNovaMatriculaBySiape($mat);

$oDBase = selecionaServidor($mat);
$sitcad = $oDBase->fetch_object()->sigregjur;

// ocorrências grupos
$obj = new OcorrenciasGrupos();
$codigosDiaJaRemuneradoSaldoZerado = $obj->CodigosDiaJaRemuneradoSaldoZerado($sitcad);



$_SESSION['sPaginaRetorno_erro'] = $_SERVER['REQUEST_URI'];

//obtem dados do servidor
$oDBase = new DataBase('PDO');

$oJornada = new DefinirJornada;
$oJornada->setSiape($mat);
$oJornada->setLotacao($_SESSION['sLotacao']);
$oJornada->setData($dia);
$oJornada->estabelecerJornada();
$jornada  = $oJornada->jornada;

$nome                      = $oJornada->getNome();    // Nome do servidor registrado no SIAPE
$lot                       = $oJornada->getLotacao(); // Codigo literal da unidade de trabalho do servidor
$sEntradaDefinida          = $oJornada->entrada_no_servico;  // horário de entrada
$sSaidaIntervaloDefinida   = $oJornada->saida_para_o_almoco; // horário da saida para o almoco
$sRetornoIntervaloDefinido = $oJornada->volta_do_almoco;     // horário do retorno do almoco
$sSaidaDefinida            = $oJornada->saida_do_servico;        // final do expediente
// Verifica se é natal, ano novo ou quarta feira de cinzas.
// Carga horária de 6 horas no Natal e Ano Novo de 2009, e 4 horas na Quarta-feira de Cinzas de 2010.
// Já atende ao critério para apuração da carga horária dos dias de ponto facultativo
// (natal e ano novo de 2010, e a quarta feira de cinzas de 2011).
$ano                       = date('Y');

$jnd = ponto_facultativo($dia, $jornada, $ano, $sEntradaDefinida, $sSaidaDefinida, $sSaidaIntervaloDefinida, $sRetornoIntervaloDefinido);

// converte para minutos para uso
// nos testes (javascript)
$jnc = $jnd * 60;

// competencia
$oDataSys = new trata_datasys();
if ($cmd == "1")
{
    $ano  = $oDataSys->getAno();
    $mes  = $oDataSys->getMes();
    $year = $ano;
    $comp = $mes . $year;
}
else
{
    $ano  = $oDataSys->getAno();
    $mes  = $oDataSys->getMesAnterior();
    $year = $oDataSys->getAnoAnterior();
    $comp = $mes . $year;
}

$diac = conv_data($dia);

// pega o horario registrado, se houver
$oDBase->query("SELECT entra, intini, intsai, sai FROM ponto$comp WHERE siape = '$mat' AND dia = '$diac' ");
$oPonto                 = $oDBase->fetch_object();
$sPontoEntrada          = $oPonto->entra;
$sPontoIntervaloInicio  = $oPonto->intini;
$sPontoIntervaloRetorno = $oPonto->intsai;
$sPontoSaida            = $oPonto->sai;

$numrows = $oDBase->num_rows();

// Testando se eh dia util
include_once( "dutil.php" );

if (in_array($ocor, $codigosDiaJaRemuneradoSaldoZerado) && $dutil == 'S')
{
    mensagem("O código " . implode(', ', $codigosDiaJaRemuneradoSaldoZerado) . " (FISCALIZAÇÃO EM CONCURSO/CURSO EM DIAS NÃO ÚTEIS), não pode ser registrado em dias úteis!", null, 1);
}


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Frequência » ... » Registro de Horas Excedentes');
$oForm->setSubTitulo("Registro de Horas Excedentes");
$oForm->setJS('registro14.js');

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<form action="gravaregfreq2.php"  method="post" id="form1" name="form1" onSubmit="return validar()" >
    <input type='hidden' name='modo'    id='modo'    value='3'>
    <input type='hidden' name='compete' id='compete' value='<?= tratarHTML($comp); ?>'>
    <input type='hidden' name='ocor'    id='ocor'    value='<?= tratarHTML($ocor); ?>'>
    <input type='hidden' name='jornada_cargo' id="jornada_cargo" value='<?= tratarHTML($jnd); ?>'>
    <input type='hidden' name='jnd'     id="jnd"     value='<?= tratarHTML($jnd); ?>'>
    <input type='hidden' name='cmd'     id="cmd"     value='<?= tratarHTML($cmd); ?>'>
    <input type="hidden" name="jd2"     id="jd2"     value='<?= tratarHTML($jnc); ?>'>
    <input type="hidden" name="dutil"   id="dutil"   value='<?= tratarHTML($dutil); ?>'>
    <div align="center">
        <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#808040" width="99%" id="AutoNumber1">
            <tr>
                <td colspan="2" class="ft_13_002">
                    Dados do Servidor:
                </td>
            </tr>
        </table>
        <table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#808040" width="99%" id="AutoNumber1">
            <tr>
                <td width="619" height="46" class="tahomaSize_2">
                    Nome:<br>&nbsp;<input type="text" id="nome" name="nome" class='caixa' value='<?= tratarHTML($nome); ?>' size="60" readonly>
                    <div align="center"></div>
                    <div align="center"></div>
                </td>
                <td width="144" align="center" class="tahomaSize_2">
                    Mat.Siape:<br><input type="text" id="mat" name="mat" class='caixa' value='<?= tratarHTML($mat); ?>' size="7" readonly>
                </td>
            </tr>
        </table>
        <table width="99%" border="1" cellpadding="0" cellspacing="0" bordercolor="#808040" id="AutoNumber1" style="border-collapse: collapse; margin-bottom: 0;">
            <tr>
                <td height="39" class="tahomaSize_2" align='center' nowrap>
                    Dia da Ocorr&ecirc;ncia:<br>
                    <input name="dia" type="text" id="dia" class='alinhadoAoCentro' value='<?= tratarHTML($dia); ?>' size="11" readonly>
                </td>
                <td class="tahomaSize_2" align='center' nowrap>
                    Hora de In&iacute;cio do Expediente:<br>
                    <input name="entra" type="text" class='alinhadoAoCentro' id="entra"  OnKeyPress="formatar(this, '##:##:##')"  value='<?= tratarHTML($sPontoEntrada); ?>' size="11" maxlength="8" title='Digite o horário sem pontos no formato 000000!!'>
                </td>
                <td class="tahomaSize_2" align='center' nowrap>
                    Hora de In&iacute;cio do Intervalo:<br>
                    <input name="iniint" type="text" class='alinhadoAoCentro' id="iniint"  OnKeyPress="formatar(this, '##:##:##')"  value='<?= tratarHTML($sPontoIntervaloInicio); ?>' size="11" maxlength="8" title='Digite o horário sem pontos no formato 000000!!'>
                </td>
                <td width="23%" class="tahomaSize_2" align='center' nowrap>
                    Hora de Retorno do Intervalo:<br>
                    <input name="fimint" type="text" class='alinhadoAoCentro' id="fimint"  OnKeyPress="formatar(this, '##:##:##')"  value='<?= tratarHTML($sPontoIntervaloRetorno); ?>' size="11" maxlength="8" title='Digite o horário sem pontos no formato 000000!!'>
                </td>
                <td width="17%" class="tahomaSize_2" align='center' nowrap>
                    Hor&aacute;rio da Sa&iacute;da:<br>
                    <input name="hsaida" type="text" class='alinhadoAoCentro' id="hsaida"  OnKeyPress="formatar(this, '##:##:##')"  value='<?= tratarHTML($sPontoSaida); ?>' size="11" maxlength="8" title='Digite o horário sem pontos no formato 000000!!'>
                </td>
            </tr>
        </table>
        <input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>concluir.gif" name="enviar" alt="incluir ocorrência" align="center" >
    </div>
</form>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
