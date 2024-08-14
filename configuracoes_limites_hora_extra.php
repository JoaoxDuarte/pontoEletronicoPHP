<?php
include_once("config.php");

verifica_permissao("hora_extra_limites");

$oForm = new formPadrao();
$oForm->setCaminho('Frequência » Configurações');
$oForm->setSeparador(0);

$css = array();
$javascript = array();

$css[] = 'css/new/sorter/css/theme.bootstrap_3.min.css';
$javascript[] = 'css/new/sorter/js/jquery.tablesorter.min.js';
$oForm->exibeTopoHTML();

$oDBase = new DataBase('PDO');
$configs = parametrosHoraExtra();

$total_configs = $configs->num_rows();


?>
<style>
    .inibir_opcao {
        opacity: 0.15;
        -moz-opacity: 0.15;
        filter: alpha(opacity=15);
    }
</style>
<script>
$(document).ready(function () {
    $('[data-load-remote]').on('click',function(e) {
        var oForm  = $("#form1");
        var $this  = $(this);
        var remote = $this.data('load-remote');
        var dados  = $this.data('remote-dados');

        console.log(remote);
        console.log(dados);

        e.preventDefault();

        $('#id').val( dados );
        
        oForm.attr("onSubmit", "javascript:return true;");
        oForm.attr("action", remote);
        oForm.submit();
    });
});
</script>
<div class="container">

    <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

    <form id='form1' name='form1' method='POST' action="#" onsubmit='return false;'>
        <input type='hidden' id='id' name='id' value=''>
        <div class="row margin-10">
            <div class="col-md-12 subtitle">
                <h4 class="lettering-tittle uppercase"><strong>Parâmetros Limites de Horas - Serviços Extraordinários</strong></h4>
            </div>
            <div class="col-md-12">
                <br>
                <div class="row">
                    <fieldset width='100%'>Total de <?= tratarHTML($total_configs); ?> registros.</fieldset>
                    <table id="myTable" class="table table-striped table-bordered text-center table-condensed tablesorter">
                        <thead>
                        <tr>
                            <th class="text-center" style='vertical-align:middle;'>Campo</th>
                            <th class="text-center" style='vertical-align:middle;'>Valor</th>
                            <th class="text-center" style='vertical-align:middle;'>Ações</th>
                        </tr>
                        </thead>
                        <tbody>

                        <?php while ($configuracao= $configs->fetch_object()): ?>

                            <tr>
                                <td align='left'><?= tratarHTML($configuracao->mensagem); ?></td>
                                <td align='left'><?= tratarHTML($configuracao->valor); ?></td>
                                <td align='center'>
                                    <a href='javascript:void(0)' data-load-remote='configuracoes_limites_hora_extra_alterar.php' data-remote-dados='<?= tratarHTML($configuracao->id); ?>'><img border='0' src='<?= _DIR_IMAGEM_; ?>edicao2.jpg' width='16' height='16' align='absmiddle' alt='Horário' title='Editar'></a>                                 </td>
                            </tr>

                        <?php endwhile; ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
</div>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
