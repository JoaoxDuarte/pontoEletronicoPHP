<?php
/**
 * Formulário padrão de exibição de frequência.
 * Complemento para homologação
 */
?>
<div class="col-md-12 table-bordered" style="position:relative;top:-19px;">
    <div class="form-group margin-10">
        <div class="col-md-1 text-left">
            &nbsp;&nbsp;Opera&ccedil;&otilde;es:&nbsp;
        </div>
        <div class="col-md-3 text-left">
            <a class="btn btn-default btn-block" href="javascript:window.location.replace('frequencia_alterar_periodo.php?dados=<?= $frequencia_alterar_periodo; ?>');" role="button">
                <span class="glyphicon glyphicon-calendar"></span> Alteração por Período
            </a>
        </div>
    </div>
    <div class="col-md-12 margin-bottom-10"></div>
</div>

<div class="col-md-12">
    <div class="col-md-12">
            <div class="col-md-3 col-xs-6">
                <a class="btn btn-info btn-block" id="btn-import-afast" role="button">
                    <span class="glyphicon glyphicon-arrow-down"></span> Importar Afastamentos
                </a>
            </div>
            <div class="col-md-3 col-xs-6">
                <a class="btn btn-danger btn-block" href='javascript:window.location.replace("<?= $rh_manutencao_do_mes; ?>?dados=<?= $destino_voltar; ?>");' role="button">
                    <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                </a>
            </div>
        </div>
    </div>
</div>
