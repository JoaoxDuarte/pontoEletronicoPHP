<?php
/**
 * Formul�rio padr�o de exibi��o de frequ�ncia.
 * Complemento para homologa��o
 */
?>
<div class="col-md-12 table-bordered" style="position:relative;top:-19px;">
    <div class="form-group margin-10">
        <div class="col-md-1 text-left">
            &nbsp;&nbsp;Opera&ccedil;&otilde;es:&nbsp;
        </div>
        <div class="col-md-3 text-left">
            <a class="btn btn-default btn-block" href="javascript:window.location.replace('frequencia_alterar_periodo.php?dados=<?= $frequencia_alterar_periodo; ?>');" role="button">
                <span class="glyphicon glyphicon-calendar"></span> Altera��o por Per�odo
            </a>
        </div>
    </div>
    <div class="col-md-12 margin-bottom-10"></div>
</div>

<div class="col-md-12">
    <table class="table table-striped table-condensed table-bordered text-center" style="width:100%;">
        <thead>
            <tr>
                <th class="text-center">Observa��o</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center"><textarea id='observacao' name='observacao' cols='130' rows='5'><?= $observacao; ?></textarea></td>
            </tr>
        </tbody>
    </table>
</div>

<div class='col-md-12 text-center margin-bottom-10' style='font-weight:bold;'>Ap�s os ajustes realizados, para efetivar o registro das altera��es, clique em "GRAVAR ALTERA��ES".</div>

<div class="col-md-12">
    <div class="col-md-12">
        <div class="form-group">
            <div class="col-md-3 col-md-offset-1">
                <a class="btn btn-success btn-block" id="botao_enviar" role="button">
                    <span class="glyphicon glyphicon-arrow-down"></span> Gravar Altera��es
                </a>
            </div>
            <div class="col-md-3 col-xs-6">
                <a class="btn btn-info btn-block" id="btn-import-afast" role="button">
                    <span class="glyphicon glyphicon-arrow-down"></span> Importar Afastamentos
                </a>
            </div>
            <div class="col-md-3 col-xs-6">
                <a class="btn btn-danger btn-block" href="#" data-load-remote-voltar-historico='historico_frequencia.php' role="button">
                    <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                </a>
            </div>
        </div>
    </div>
</div>
