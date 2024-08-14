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

$_SESSION['voltar_nivel_1'] = 'gerais_lista.php';
$_SESSION['voltar_nivel_2'] = '';
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';
$_SESSION['voltar_nivel_5'] = '';


$oForm = new formPadrao();
$oForm->setCaminho('Utilitários » Configurações » Gerais');
$oForm->setSeparador(0);

$css = array();
$javascript = array();

$css[] = 'css/new/sorter/css/theme.bootstrap_3.min.css';
$javascript[] = 'css/new/sorter/js/jquery.tablesorter.min.js';
$oForm->exibeTopoHTML();


$oDBase = new DataBase('PDO');
$configs = getConfiguracaoBasica();

$total_servidores = $configs->num_rows();



?>
<style>
    .inibir_opcao {
        opacity: 0.15;
        -moz-opacity: 0.15;
        filter: alpha(opacity=15);
    }
</style>

<div class="container">

    <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

    <div class="row margin-10">
        <div class="col-md-12 subtitle">
            <h4 class="lettering-tittle uppercase"><strong>Configurações básicas</strong></h4>
        </div>
    <div class="col-md-12">
            <br>
            <div class="row">
                <fieldset width='100%'>Total de <?= tratarHTML($total_servidores); ?> registros.</fieldset>
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

                        <?php $style = ($configuracao->grupo == 'hora_extra' || $configuracao->grupo == 'plantao' ? " style='font-weight:bold;'" : ''); ?>

                        <tr>
                            <td align='left' <?= ($configuracao->grupo == 'hora_extra' || $configuracao->grupo == 'plantao' ? " style='font-weight:bold;'" : ''); ?>><?= tratarHTML(ajustar_acentos(str_to_utf8($configuracao->observacao))); ?></td>
                            <td align='center' <?= ($configuracao->grupo == 'hora_extra' || $configuracao->grupo == 'plantao' ? " style='font-weight:bold;'" : ''); ?>><?= tratarHTML($configuracao->minutos); ?></td>
                            <td align='center'>
                                <a href='gerais_alterar.php?id=<?= tratarHTML($configuracao->id); ?>'><img border='0' src='<?= _DIR_IMAGEM_; ?>edicao2.jpg' width='16' height='16' align='absmiddle' alt='Horário' title='Editar'></a>

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
