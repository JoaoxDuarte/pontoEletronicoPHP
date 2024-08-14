<?php
include_once("config.php");

verifica_permissao("tabela_prazos");

// parametros enviados por formulario
$id                      = anti_injection($_REQUEST['id']);
$horario_de_verao_inicio = anti_injection($_REQUEST['hvi']);
$horario_de_verao_fim    = anti_injection($_REQUEST['hvf']);

// instancia banco de dados
$oDBase = new DataBase('PDO');
$oDBase->setDestino(pagina_de_origem());

if (empty($id))
{
    $ano_inicio = date('Y');
    $periodo = $ano_inicio . ' / ' . ($ano_inicio + 1);

    $oDBase->query("
    SELECT 
        base_legal, estados_incluidos 
    FROM 
        tabhorario_verao 
    ORDER BY 
        id DESC
    LIMIT
        1 
    ", 
    array(
        array(':id', $id, PDO::PARAM_STR)
    ));
    $hverao = $oDBase->fetch_object();
    
    $base_legal = $hverao->base_legal;
    $estados    = $hverao->estados_incluidos;
}
else
{
    $oDBase->query("
    SELECT 
        id, periodo, hverao_inicio, hverao_fim, 
        base_legal, estados_incluidos, ativo 
    FROM 
        tabhorario_verao 
    WHERE 
        id = :id ", 
    array(
        array(':id', $id, PDO::PARAM_STR)
    ));
    $hverao = $oDBase->fetch_object();
    
    $periodo    = $hverao->periodo;
    $inicio     = databarra($hverao->hverao_inicio);
    $fim        = databarra($hverao->hverao_fim);
    $base_legal = $hverao->base_legal;
    $estados    = $hverao->estados_incluidos;
    $ativo      = $hverao->ativo;
}


## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho("Utilitários » Gestores » Prazos - Horário de Verão");
$oForm->setJSDatePicker();
$oForm->setLargura('950');
$oForm->setSeparador(0);

$oForm->setJS('tabhverao.js?v.0.0.0.1');

// Topo do formulário
//
$oForm->setSubTitulo("Manutenção da Tabela de Hor&aacute;rio de Ver&atilde;o");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<style type="text/css">
    td { font-family:verdana; font-size:7pt }
</style>

<div class="portlet-body form">
    <form method="POST" action="#" id="form1" name="form1" onsubmit="javascript:return false;">
        <input type='hidden' id='modo' name='modo' value='3'>
        <input type='hidden' id='id' name='id' value='<?= tratarHTML($id); ?>'>
        
        <div class="row">
            <div class="col-lg-2 col-md-2 col-xs-2 col-sm-2 margin-10">
                <font class="ft_13_003">Período:</font>
                &nbsp;<input type="text"
                             id="ano" name="ano"
                             class="form-control uppercase"
                             size="10" maxlength="10"
                             value="<?= tratarHTML($periodo); ?>"
                             readonly />
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3 col-md-3 col-xs-3 col-sm-3 margin-10" id="date">
                <font class="ft_13_003">In&iacute;cio do hor&aacute;rio:</font>
                <div class='input-group date'>
                    <input type='text' 
                           id="inicio" name="inicio" 
                           placeholder="dd/mm/aaaa" 
                           autocomplete="off" 
                           class="form-control"
                           value="<?= tratarHTML($inicio); ?>" />
                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3 col-md-3 col-xs-3 col-sm-3 margin-10" id="date">
                <font class="ft_13_003">Fim do hor&aacute;rio:</font>
                <div class='input-group date'>
                    <input type='text' 
                           id="fim" name="fim" 
                           placeholder="dd/mm/aaaa" 
                           autocomplete="off" 
                           class="form-control"
                           value="<?= tratarHTML($fim); ?>" />
                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6 margin-10">
                <font class="ft_13_003">Base Legal:</font>
                &nbsp;<input type="text"
                             id="base_legal" name="base_legal"
                             class="form-control uppercase"
                             size="100" maxlength="255"
                             value="<?= tratarHTML($base_legal); ?>" />
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-6 col-md-6 col-xs-6 col-sm-6 margin-10">
                <font class="ft_13_003">Estados Incluídos:</font>
                &nbsp;<input type="text"
                             id="estados" name="estados"
                             class="form-control uppercase"
                             size="100" maxlength="255"
                             value="<?= tratarHTML($estados); ?>" />
            </div>
        </div>
        
        <?php if (empty($id)): ?>
        
            <input type='hidden' id="ativo" name="ativo" value="S">
        
        <?php else: ?>

            <div class="row">
                <div class="col-lg-3 col-md-3 col-xs-3 col-sm-3 margin-10">
                    <font class="ft_13_003">Ativo?</font>
                    <select id="ativo" name="ativo" class="form-control select2-single">
                        <option value='N' <?= ($ativo != 'S' ? ' selected' : ''); ?>>N&atilde;o</option>
                        <option value='S' <?= ($ativo == 'S' || ($acao != 'Alteração') ? ' selected' : ''); ?>>Sim</option>
                    </select>
                </div>
            </div>
        
        <?php endif; ?>
        
        <div class="row">
            <br>
            <div class="form-group col-md-12 text-center">
                <div class="col-md-2"></div>
                <div class="col-md-2 col-xs-4">
                    <a class="btn btn-success btn-block" id="btn-gravar" role="button">
                        <span class="glyphicon glyphicon-ok"></span> Salvar
                    </a>
                </div>
                <div class="col-md-2 col-xs-4">
                    <a class="btn btn-danger btn-block" id="btn-voltar"
                       href="javascript:window.location.replace('tabvalida.php?aba=seg')" role="button">
                        <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                    </a>
                </div>
                <div class="col-md-2"></div>
            </div>
        </div>
    </form>
</div>
    <?php
// Base do formulário
//
    $oForm->exibeCorpoBaseHTML();
    $oForm->exibeBaseHTML();
    