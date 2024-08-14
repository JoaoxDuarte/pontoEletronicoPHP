<?php
// Inicializa a sessão (session_start)
// funcoes de uso geral
include_once( "config.php" );

// Verifica se o usuário tem a permissão para acessar este módulo
verifica_permissao("sRH");

// instancia o banco de dados
$oDBase = new DataBase('PDO');

// parametro formulario
$siapecad = anti_injection($_REQUEST['siapecad']);
$escolha  = anti_injection($_REQUEST['escolha']);
$chave    = anti_injection($_REQUEST['chave']);
$modal    = anti_injection($_REQUEST['modal']);

// pesquisa/seleção
$oDBase->query("
SELECT
    siapecad, smap_ocorrencia, desc_ocorr, cod_ocorr, cod_siape, resp, aplic, implic, prazo, flegal, ativo,
    semrem, idsiapecad, grupo, tipo, situacao, justificativa, postergar_pagar_recesso, tratamento_debito, padrao,
    grupo_cadastral, agrupa_debito, grupo_ocorrencia, informar_horarios, vigencia_inicio, vigencia_fim, abonavel
FROM
    tabocfre
WHERE
    siapecad = :siapecad
",
array(
    array( ':siapecad', $siapecad, PDO::PARAM_STR ),
));

$oDados = $oDBase->fetch_object();

$responsavel       = array();
$responsavel['AB'] = "RH / Chefia";
$responsavel['RH'] = "Recurso Humanos";
$responsavel['CH'] = "Chefia";
$responsavel['SI'] = "SISREF";



## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setSubTitulo("Alterar Dados de Códigos de Ocorrências");
$oForm->setJS('js/jquery.mask.min.js');
$oForm->setJS("tabela_ocorrencia_de_frequencia_alterar.js?time=".date('YmdHis'));

$grupos = getSelectGrupos($oDados->grupo);
$tipos = getSelectTipos($oDados->tipo);
$tratamentosDebito = getSelectTratamentosDebito($oDados->tratamento_debito);
$padroes = getSelectPadroes($oDados->padrao);
$gruposCadastrais = getSelectGruposCadastrais($oDados->grupo_cadastral);
$gruposOcorrencias = getSelectGruposOcorrencias($oDados->grupo_ocorrencia);
$situacoes = getSelectSituacoes($oDados->situacao);

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

?>
<form method="POST" id="form1" name="form1" action="#" onsubmit="javascript:return false;">

    <table class='table table-striped table-condensed text-center'>
        <tr>
            <td>
                <label>CÓDIGO</label>
                <input id="siapecad" name="siapecad" type="text" value="<?= tratarHTML($oDados->siapecad); ?>" size="10"  class="form-control" readonly>
            </td>
            <td>
                <label>DESCRIÇÃO DA OCORRÊNCIA</label>
                <input id="sDescricao" name="sDescricao" type="text" value="<?= tratarHTML(trata_aspas($oDados->desc_ocorr)); ?>" size="70"  class="form-control" readonly>
            </td>
            <td>
                <label>RESPONSÁVEL</label>
                <select id="resp" name="resp" class="form-control">
                    <option value="AB" <?= ($oDados->resp == "AB" ? "selected" : ""); ?>>RH / Chefia</option>
                    <option value="RH" <?= ($oDados->resp == "RH" ? "selected" : ""); ?>>Recurso Humanos</option>
                    <option value="CH" <?= ($oDados->resp == "CH" ? "selected" : ""); ?>>Chefia</option>
                    <option value="SI" <?= ($oDados->resp == "SI" ? "selected" : ""); ?>>SISREF</option>
                    <option value="WS" <?= ($oDados->resp == "WS" ? "selected" : ""); ?>>SIAPE</option>
                </select>
            </td>
            <td>
                <label>ATIVO</label>
                <select id="sAtivo" name="sAtivo" class="form-control">
                    <option value="S" <?= ($oDados->ativo == 'S' ? "selected" : ""); ?>>Sim</option>
                    <option value="N" <?= ($oDados->ativo != "S" ? "selected" : ""); ?>>Não</option>
                </select>
            </td>
        </tr>
    </table>

    <table class='table table-striped table-condensed text-center'>
        <tr>
            <td>
                <label>SMAP OCORRENCIA</label>
                <input id="smap_ocorrencia" name="smap_ocorrencia" type="text" value="<?= tratarHTML($oDados->smap_ocorrencia); ?>"  class="form-control">
            </td>
            <td>
                <label>CODIGO OCORRENCIA</label>
                <input id="cod_ocorr" name="cod_ocorr" type="text" value="<?= tratarHTML(trata_aspas($oDados->cod_ocorr)); ?>"  class="form-control">
            </td>
            <td>
                <label>CODIGO SIAPE</label>
                <input id="cod_siape" name="cod_siape" type="text" value="<?= tratarHTML($oDados->cod_siape); ?>"  class="form-control">
            </td>
            <td>
                <label>SEMREM</label>
                <select id="semrem" name="semrem" class="form-control">
                    <option value="S" <?= ($oDados->semrem == 'S' ? "selected" : ""); ?>>Sim</option>
                    <option value="N" <?= ($oDados->semrem != "S" ? "selected" : ""); ?>>Não</option>
                </select>
            </td>
            <td>
                <label>ID SIAPECAD</label>
                <select id="idsiapecad" name="idsiapecad" class="form-control">
                    <option value="S" <?= ($oDados->idsiapecad == 'S' ? "selected" : ""); ?>>Sim</option>
                    <option value="N" <?= ($oDados->idsiapecad != "S" ? "selected" : ""); ?>>Não</option>
                </select>
            </td>
            <td>
                <label>GRUPO</label>
                <select id="grupo" name="grupo" class="form-control">
                    <?php echo $grupos; ?>
                </select>
            </td>
        </tr>
    </table>

    <table class='table table-striped table-condensed text-center'>
        <tr>
            <td>
                <label>TIPO</label>
                <select id="tipo" name="tipo" class="form-control">
                    <?php echo $tipos; ?>
                </select>
            </td>
            <td>
                <label>SITUAÇÃO</label>
                <select id="situacao" name="situacao" class="form-control">
                    <?php echo $situacoes; ?>
                </select>
            </td>
            <td>
                <label>JUSTIFICATIVA</label>
                <select id="justificativa" name="justificativa" class="form-control">
                    <option value="S" <?= ($oDados->justificativa == 'S' ? "selected" : ""); ?>>Sim</option>
                    <option value="N" <?= ($oDados->justificativa != "S" ? "selected" : ""); ?>>Não</option>
                </select>
            </td>
            <td>
                <label>POST. REC.</label>
                <select id="postergar_pagar_recesso" name="postergar_pagar_recesso" class="form-control">
                    <option value="S" <?= ($oDados->postergar_pagar_recesso == 'S' ? "selected" : ""); ?>>Sim</option>
                    <option value="N" <?= ($oDados->postergar_pagar_recesso != "S" ? "selected" : ""); ?>>Não</option>
                </select>
            </td>
        </tr>
    </table>

    <table class='table table-striped table-condensed text-center'>
        <tr>
            <td>
                <label>TRATAMENTO DEBITO</label>
                <select id="tratamento_debito" name="tratamento_debito" class="form-control">
                    <?php echo $tratamentosDebito; ?>
                </select>
            </td>
            <td>
                <label>PADRAO</label>
                <select id="padrao" name="padrao" class="form-control">
                    <?php echo $padroes; ?>
                </select>
            </td>
            <td>
                <label>GRUPO CADASTRAL</label>
                <select id="grupo_cadastral" name="grupo_cadastral" class="form-control">
                    <?php echo $gruposCadastrais; ?>
                </select>
            </td>
            <td>
                <label>AGRUPA DEBITO</label>
                <input name="agrupa_debito" type="text" value="<?= tratarHTML(trata_aspas($oDados->agrupa_debito)); ?>"  class="form-control">
            </td>
            <td>
                <label>GRUPO OCORRENCIA</label>
                <select id="grupo_ocorrencia" name="grupo_ocorrencia" class="form-control">
                    <?php echo $gruposOcorrencias; ?>
                </select>
            </td>
            <td>
                <label>ABONÁVEL</label>
                <select id="abonavel" name="abonavel" class="form-control">
                    <option value="N" <?php echo $oDados->abonavel != 'S' ? 'selected' : '' ?> >Não</option>
                    <option value="S" <?php echo $oDados->abonavel == 'S' ? 'selected' : '' ?> >Sim</option>
                </select>
            </td>
        </tr>
    </table>

    <table class='table table-striped table-condensed text-center'>
        <tr>
            <td>
                <label>INF. HOR.</label>
                <select id="informar_horarios" name="informar_horarios" class="form-control">
                    <option value="S" <?= ($oDados->informar_horarios == 'S' ? "selected" : ""); ?>>Sim</option>
                    <option value="N" <?= ($oDados->informar_horarios != "S" ? "selected" : ""); ?>>Não</option>
                </select>
            </td>
            <td>
                <label>VIGENCIA INICIO</label>
                <input id="vigencia_inicio" name="vigencia_inicio" type="text" value="<?= tratarHTML(databarra($oDados->vigencia_inicio)); ?>"  class="form-control maskData">
            </td>
            <td>
                <label>VIGENCIA FIM</label>
                <input id="vigencia_fim" name="vigencia_fim" type="text" value="<?= tratarHTML(trata_aspas(databarra($oDados->vigencia_fim))); ?>"  class="form-control maskData">
            </td>
        </tr>
    </table>


    <table class='table table-striped table-condensed text-center'>
        <tr>
            <td>
                <label>APLICAÇÃO</label>
                <textarea id="aplic" name="aplic" cols=40 rows=10 class="form-control"><?= tratarHTML(trata_aspas($oDados->aplic)); ?></textarea>
            </td>
            <td>
                <label>IMPLICAÇÃO</label>
                <textarea id="implic" name="implic" cols=40 rows=10 class="form-control"><?= tratarHTML(trata_aspas($oDados->implic)); ?></textarea>
            </td>
            <td>
                <label>PRAZOS</label>
                <textarea id="prazo" name="prazo" cols=30 rows=10 class="form-control"><?= tratarHTML($oDados->prazo); ?> </textarea>
            </td>
            <td>
                <label>FUNDAMENTO LEGAL</label>
                <textarea id="flegal" name="flegal" cols=30 rows=10 class="form-control"><?= tratarHTML(trata_aspas($oDados->flegal)); ?></textarea>
            </td>
        </tr>
    </table>

    <div align='center'>
        <table border='0' align='center'>
            <tr>
                <td align='center'>
                    <div class="col-md-12 text-center">
                        <a class="btn btn-success btn-block" id="btn-enviar">
                            <span class="glyphicon glyphicon-ok"></span> Gravar
                        </a>
                    </div>
                </td>
                <td align='center'>
                    <div class="col-md-12 text-center">
                        <a class="btn btn-primary btn-danger" id="btn-voltar" href="javascript:location.replace('tabela_ocorrencia_de_frequencia_visualizar.php?escolha=&chave=');">
                            <span class="glyphicon glyphicon-ok"></span> Voltar
                        </a>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</form>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();


function getSelectGrupos($fGrupo){
    $grupos = array('Ambos','Estagiario','Servidor','Sistema','Empregado');
    $options = "<option value='' selected>Selecione</option>";
    
    foreach($grupos as $grupo){
        $options .= "<option value='{$grupo}' ".($grupo == $fGrupo ? 'selected' : '').">{$grupo}</option>";
    }
    
    return $options;
}


function getSelectTipos($fTipo){
    $tipos = array('diferenca_negativa','diferenca_positiva','diferenca_zerada','indefinida','jornada_negativa','todos_zerados','mista_jornada_negativa_horas_zeradas');
    $options = "<option value='' selected>Selecione</option>";
    
    foreach($tipos as $tipo){
        $options .= "<option value='{$tipo}' ".($tipo == $fTipo ? 'selected' : '').">{$tipo}</option>";
    }
    
    return $options;
}

function getSelectTratamentosDebito($fTratamentoDebito){
    $tratamentosDebito = array('Nenhum','Compensavel','Desconto Imediato');
    $options = "<option value='' selected>Selecione</option>";
    
    foreach($tratamentosDebito as $tratamentoDebito){
        $options .= "<option value='{$tratamentoDebito}' ".($tratamentoDebito == $fTratamentoDebito ? 'selected' : '').">{$tratamentoDebito}</option>";
    }
    
    return $options;
}

function getSelectPadroes($fPadrao){
    $padroes = array('Nao','Abono','Consulta Medica','Credito','Debito','Frequencia Normal','Hora Extra','Instrutoria Debito','Instrutoria Credito','Registro Parcial','Sem Frequencia','Recesso Debito','Recesso Credito','Viagem a Servico','Sem Vinculo','Falta Justificada','Banco de Horas Credito','Banco de Horas Debito','Ativsindical credito','Ativsindical debito','SobreAviso credito','SobreAviso debito','Carreira Descentralizada');
    $options = "<option value='' selected>Selecione</option>";
    
    foreach($padroes as $padrao){
        $options .= "<option value='{$padrao}' ".($padrao == $fPadrao ? 'selected' : '')." >{$padrao}</option>";
    }
    
    return $options;
}

function getSelectGruposCadastrais($fGrupoCadastral){
    $gruposCadastrais = array('Todos','EST','RJU','CLT','ETG','EST/RJU','EST/CLT','EST/ETG','RJU/CLT','RJU/ETG','CLT/ETG','EST/RJU/CLT','EST/RJU/ETG','EST/CLT/ETG','RJU/CLT/ETG','EST/RJU/CLT/ETG');
    $options = "<option value='' selected>Selecione</option>";
    
    foreach($gruposCadastrais as $grupoCadastral){
        $options .= "<option value='{$grupoCadastral}' ".($grupoCadastral == $fGrupoCadastral ? 'selected' : '')."  >{$grupoCadastral}</option>";
    }
    
    return $options;
}


function getSelectGruposOcorrencias($fGrupoOcorrencia){
    $gruposOcorrencias = array('Afastamento','Afastamento com remuneracao','Afastamento sem remuneracao','Licenca','Licenca com remuneracao','Licenca sem remuneracao','Dia ja remunerado','Viagem a Servico','Outros','Abandono de Cargo','Servico Externo','Ferias');
    $options = "<option value='' selected>Selecione</option>";

    foreach($gruposOcorrencias as $grupoOcorrencia){
        $options .= "<option value='{$grupoOcorrencia}' ".($grupoOcorrencia == $fGrupoOcorrencia ? 'selected' : '')." >{$grupoOcorrencia}</option>";
    }
    
    return $options;
}

function getSelectSituacoes($fSituacao){
    $oDBase = new DataBase();
    $oDBase->query('SELECT * FROM tabocfre_situacao');
    
    $options = "<option value='' selected>Selecione</option>";
    while($situacao = $oDBase->fetch_object()){
        $options .= "<option value='{$situacao->id}' ".($situacao->id == $fSituacao ? 'selected' : '')." >{$situacao->id} - {$situacao->ocorrencia_situacao}</option>";
    }

    return $options;
}
