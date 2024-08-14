<?php
include_once("config.php");

verifica_permissao("administracao_central");
//
//$dia = (isset($dia) ? $dia : date('d/m/Y'));
//$cmd = (isset($cmd) ? $cmd : '2');
//$orig = (isset($orig) ? $orig : '1');
//$qlotacao = (isset($qlotacao) ? $qlotacao : $_SESSION["sLotacao"]);
//
//$vDatas = conv_data($dia);
//$mes = dataMes($dia);
//$ano = dataAno($dia);
//$comp = $mes . $ano;
//
//
//if ($mes != date('m'))
//    header("Location: acessonegado.php");
//


$oForm = new formPadrao();
$oForm->setCaminho('Frequência » Acompanhar');
$oForm->setSeparador(0);

$css = array();
$javascript = array();

$css[] = 'css/new/sorter/css/theme.bootstrap_3.min.css';
$javascript[] = 'css/new/sorter/js/jquery.tablesorter.min.js';
$oForm->exibeTopoHTML();

$oDBase = new DataBase('PDO');
$configs = getConfiguracaoSuporte();

$total_servidores = $configs->num_rows();


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
        $(function () {
            $('#myTable').tablesorter();
            $('.ciclos').change(function(){
                $("[name='filterhdn']").val(true);
                $("[name='form-autorizacoes']").submit();
            });
        });
        $(document).on('click', '.total', function () {
            var cicloid = $("[name='hdncicloid']").val();
            window.location.href = 'autorizacao_usufruto.php?ciclo_id=' + cicloid + '&tiposolicitacao=total';
        });
        $(document).on('click', '.parcial', function () {
            var cicloid = $("[name='hdncicloid']").val();
            window.location.href = 'autorizacao_usufruto.php?ciclo_id=' + cicloid + '&tiposolicitacao=parcial';
        });
    });
</script>
<div class="container">

    <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

    <div class="row margin-10">
        <div class="col-md-12 subtitle">
            <h4 class="lettering-tittle uppercase"><strong>Configurações Suporte</strong></h4>
        </div>
    <div class="col-md-12">
            <br>
            <div class="row">
                <fieldset width='100%'>Total de <?= tratarHTML($total_servidores); ?> registros.</fieldset>
                <table id="myTable" class="table table-striped table-bordered text-center table-condensed tablesorter">
                    <thead>
                    <tr>
                        <th class="text-center" style='vertical-align:middle;' width="90%">Campo</th>
                        <th class="text-center" style='vertical-align:middle;'>Ações</th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php while ($configuracao= $configs->fetch_object()): ?>

                    <tr>
                        <td align='left'><?= tratarHTML($configuracao->campo); ?></td>

                        <td align='center'>
                            <a href='suporte_alterar.php?id=<?= tratarHTML($configuracao->campo); ?>'><img border='0' src='<?= _DIR_IMAGEM_; ?>edicao2.jpg' width='16' height='16' align='absmiddle' alt='Horário' title='Editar'></a>

                    </tr>
                    <?php endwhile; ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

