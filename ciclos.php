<?php
include_once("config.php");

verifica_permissao("sRH ou Chefia");


$dia = (isset($dia) ? $dia : date('d/m/Y'));
$cmd = (isset($cmd) ? $cmd : '2');
$orig = (isset($orig) ? $orig : '1');
$qlotacao = (isset($qlotacao) ? $qlotacao : $_SESSION["sLotacao"]);

$vDatas = conv_data($dia);
$mes = dataMes($dia);
$ano = dataAno($dia);
$comp = $mes . $ano;

if ($mes != date('m'))
    header("Location: acessonegado.php");


$oDBase = new DataBase('PDO');
$oCiclos = seleciona_ciclos();
$total_ciclos = $oCiclos->num_rows();

$oForm = new formPadrao();
$oForm->setCaminho('Frequência » Acompanhar');
$oForm->setSeparador(0);

$css = array();
$javascript = array();

$css[] = 'css/new/sorter/css/theme.bootstrap_3.min.css';
$javascript[] = 'css/new/sorter/js/jquery.tablesorter.min.js';
$oForm->exibeTopoHTML();
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
        });
    });
</script>

<div class="container" style='padding-left:0px;'>

    <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

    <div class="row margin-10">
        <div class="col-md-12 subtitle">
            <h4 class="lettering-tittle uppercase"><strong>Ciclos de Banco de Horas</strong></h4>
        </div>

        <div class="col-md-12">
            <div class="col-md-1 text-right">
                <a class="no-style"
                   href="javascript:window.location.replace('ciclos_cadastrar.php');">
                    <button type="button" class="btn btn-primary btn-xs">
                        <span class="glyphicon glyphicon-plus"></span> Novo
                    </button>
                </a>
            </div>
            <div class="col-md-11 text-right">
                <label for="lot" class="control-label">&nbsp;</label>
            </div>
        </div>

        <div class="col-md-12">
            <div class="row">
                <fieldset width='100%'>Total de <?= tratarHTML($total_ciclos); ?> registros.</fieldset>
                <table id="myTable" class="table table-striped table-bordered text-center table-condensed tablesorter">
                    <thead>
                    <tr>
                        <th class="text-center" style='vertical-align:middle;'>Órgão</th>
                        <th class="text-center" style='vertical-align:middle;'>Data Inicial</th>
                        <th class="text-center" style='vertical-align:middle;'>Data Final</th>
                        <th class="text-center" style='vertical-align:middle;'>Ações</th>
                    </tr>
                    </thead>

                    <tbody>

                    <?php while ($ciclo = $oCiclos->fetch_object()): ?>

                        <?php
                        $dateinicialtable = date_create($ciclo->data_inicio);
                        $datefinaltable   = date_create($ciclo->data_fim);
                        ?>
                        <tr>
                            <td align='center'><?= tratarHTML($ciclo->orgao); ?></td>
                            <td align='center'><?= date_format($dateinicialtable ,"d/m/Y"); ?></td>
                            <td align='center'><?= date_format($datefinaltable ,"d/m/Y"); ?></td>
                            <td align='center'><a href='ciclos_alterar.php?id=<?= tratarHTML($ciclo->id); ?>'><img border='0' src='<?= _DIR_IMAGEM_; ?>edicao2.jpg' width='16' height='16' align='absmiddle' alt='Horário' title='Editar'></a></td>
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


