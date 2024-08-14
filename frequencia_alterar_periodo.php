<?php
include_once( "config.php" );
include_once( "class_form.telas.php" );

verifica_permissao("sRH ou Chefia");

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    header("Location: acessonegado.php");
}
else
{
    $dados         = explode(":|:", base64_decode($dadosorigem));
    $mat           = $dados[0];
    $nome          = $dados[1];
    $lot           = $dados[2];
    $jnd           = formata_jornada_para_hhmm($dados[3]);
    $cod_sitcad    = $dados[4];
    $cmd           = $dados[5];
    $tipo_acao     = $dados[6]; // tipo da acao, ex.: 'homologar_registros'
    $tipo_inclusao = $dados[7];
    
    if (count($dados) > 8)
    {
        $mes = $dados[8];
        $ano = $dados[9];
    }
}

// dados voltar
$_SESSION['voltar_nivel_2'] = $dadosorigem;
$_SESSION['voltar_nivel_3'] = '';


## define competencia
#
$oData = new trata_datasys;

switch ($tipo_acao)
{
    case 'homologar_registros':
        $mes            = $oData->getMesAnterior();
        $ano            = $oData->getAnoAnterior();
        $script_retorno = 'frequencia_homologar_registros.php?dados=' . $_SESSION['voltar_nivel_1'];
        break;
    
    case 'rh_mes_corrente':
        $mes            = date('m');
        $ano            = date('Y');
        $script_retorno = 'frequencia_rh_mes_corrente_registros.php?dados=' . $_SESSION['voltar_nivel_1'];
        break;
    
    case 'rh_mes_homologacao':
        $mes            = $oData->getMesAnterior();
        $ano            = $oData->getAnoAnterior();
        $script_retorno = 'frequencia_rh_mes_homologacao_registros.php?dados=' . $_SESSION['voltar_nivel_1'];
        break;
     
    case 'historico_manutencao':
        $script_retorno = 'historico_frequencia_registros.php?dados=' . $_SESSION['voltar_nivel_1'];
        break;
    
    default:
        $mes            = $oData->getMes();
        $ano            = $oData->getAno();
        $script_retorno = $_SESSION['voltar_nivel_1'];
        break;
}
$comp = $mes . $ano;

## instancia o banco de dados
$oDBase = new DataBase('PDO');

## classe para montagem do formulario
#
$oForm = new formTelas();
//$oForm->setCaminho('Frequência » ... » Ocorrência » Alteração por ' . ($tipo_inclusao == '' ? 'Período' : 'Dia'));
$oForm->setSubTitulo("Alteração de Ocorr&ecirc;ncia por Per&iacute;odo");

## Dados do servidor
#
$oForm->setFormAction('frequencia_alterar_periodo_gravar.php');
$oForm->setFormOnSubmit('javascript:return false;');
$oForm->initInputHidden();
$oForm->setInputHidden('modo', '10');
$oForm->setInputHidden('compete', $comp);
$oForm->setInputHidden('mes', $mes);
$oForm->setInputHidden('ano', $ano);
$oForm->setInputHidden('jnd', $jnd);
$oForm->setInputHidden('cmd', $cmd);
$oForm->setInputHidden('lot', $lot);
$oForm->setInputHidden('dias_no_mes', numero_dias_do_mes($mes, $ano));
$oForm->setInputHidden('tipo_acao', $tipo_acao);
$oForm->setInputHidden('tipo_inclusao', $tipo_inclusao);

$grupo = agrupa_ocorrencias('__');

$oForm->setInputHidden('credito', $grupo['diferenca_positiva']);
$oForm->setInputHidden('debito', $grupo['diferenca_negativa']);

$oForm->setFormNomeServidor($nome);
$oForm->setFormMatriculaSiape(substr($mat,5,11));

$css   = array();
$css[] = _DIR_CSS_ . 'select2.min.css';
$css[] = _DIR_CSS_ . 'select2-bootstrap.css';

$javascript   = array();
$javascript[] = _DIR_JS_ . 'phpjs.js';
$javascript[] = _DIR_JS_ . 'select2.full.js';
$javascript[] = 'frequencia_alterar_periodo.js?v.0.0.0.0.1';


$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


$oForm->initHTML();
$oForm->setHTML($oForm->getAbreForm());

$oForm->setDadosDoServidor();
$oForm->setHTML($oForm->getInputHidden());
print $oForm->getHTML();

?>
<div align='center'>
    <table class='table table-striped table-condensed table-bordered text-center'>
        <tr>
            <td colspan='3'>
                <p class='tahomaSize_2' align="center" style="margin-top: 0; margin-bottom: 0">
                    Compet&ecirc;ncia:<br>
                    <input name="competencia" type="text" class='form-control text-center' id="competencia"  value="<?= $mes . '/' . $ano; ?>" size="10" readonly style="font-weight:bold;">
                </p>
            </td>
        </tr>
        <tr>
            <td class='col-md-8 text-left' rowspan='2'>
                <p class='tahomaSize_2' align='left' style='margin-top: 0; margin-bottom: 0;padding-left:15px;'>
                    C&oacute;digo da Ocorr&ecirc;ncia:
                </p>
                <div class='col-md-11 tetxt-left'>
                    <?= montaSelectOcorrencias($ocor, '', false, $por_periodo = true, $historico = false, $onchange = '', $grupo='', $mat); ?>
                </div>

                <button type='button' class='btn btn-default' id='btn-ver-tabela-ocorrencias' title='Visualizar Tabela de Ocorrência.'>
                    <span class='glyphicon glyphicon-book' aria-hidden='true'></span>
                </button>
            </td>
        </tr>
        <tr>
            <?php
            if ($tipo_inclusao == '')
            {
                ?>
                <td width='18%'>
                    <p class='tahomaSize_2' align='center' style='margin-top: 0; margin-bottom: 0'>
                        Dia Início:<br>
                        <input name='dia2' type='text' class='form-control' id='dia2' value='' size='2' maxlength='2' onkeyup="javascript:ve(this.value);" title='Digite o dia inicial do período, com dois dígitos!'>
                    </p>
                </td>
                <td width='19%'>
                    <p class='tahomaSize_2' align='center' style='margin-top: 0; margin-bottom: 0'>
                        Dia Fim:<br>
                        <input name='dia' type='text' class='form-control' id='dia' value='' size='2' maxlength='2' onkeyup="javascript:ve(this.value);" title='Digite o dia final do período, com dois dígitos!'>
                    </p>
                </td>
                <?php
            }
            else
            {
                ?>
                <td width='100%' align='center' colspan='2'>
                    <p class='tahomaSize_2' align='center' style='margin-top: 0; margin-bottom: 0'>
                        Dia da Ocorr&ecirc;ncia:<br>
                        <input name='dia2' type='text' class='form-control' id='dia2' value='' size='2' maxlength='2' onkeyup="javascript:ve(this.value);" title='Digite o dia inicial do período, com dois dígitos!'>
                        <input name='dia' id='dia' type='hidden' value=''>
                    </p>
                </td>
                <?php
            }
            ?>
        </tr>
    </table>
</div>
<?php

print $oForm->getFechaForm();

?>
<div class="col-md-12 margin-25">
    <div class="col-md-6 text-right">
        <a class="btn btn-success btn-primary" id="btn-continuar">
            <span class="glyphicon glyphicon-ok"></span> Continuar
        </a>
    </div>
    <div class="col-md-6 text-left">
        <a class="btn btn-primary btn-danger" href="javascript:window.location.replace('<?= tratarHTML($script_retorno); ?>');">
            <span class="glyphicon glyphicon-arrow-left"></span> Voltar
        </a>
    </div>
</div>

<!-- Modal -->
<div class='modal fade' id='tabela-ocorrencias' tabindex='-1' role='dialog' aria-labelledby='myLargeModalLabel' style='overflow:auto;'>
    <div class='modal-dialog modal-lg' role='document'>
        <div class='modal-content'>
            <div class='modal-header'>
                <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
                <h4 class='modal-title' id='myModalLabel'>C&oacute;digos de Ocorr&ecirc;ncias</h4>
            </div>
            <div id='tabela-ocorrencias-body' class='modal-body' style='width:auto;overflow:auto;'>
                ...
            </div>
            <div class='modal-footer'>
                <button type='button' class='btn btn-default' data-dismiss='modal'>Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class='modal fade' id='tabela-ocorrencias-detalhes' tabindex='-1' role='dialog' aria-labelledby='myLargeModalLabel'>
    <div class='modal-dialog modal-lg' role='document'>
        <div class='modal-content'>
            <div class='modal-header'>
                <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
                <h4 class='modal-title' id='myModalLabel'>C&oacute;digo de Ocorr&ecirc;ncia - Detalhes</h4>
            </div>
            <div id='tabela-ocorrencias-detalhes-body' class='modal-body'>
                ...
            </div>
            <div class='modal-footer'>
                <button type='button' class='btn btn-default' data-dismiss='modal'>Fechar</button>
            </div>
        </div>
    </div>
</div>
<?php

$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
