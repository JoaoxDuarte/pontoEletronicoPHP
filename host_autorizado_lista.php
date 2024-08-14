<?php
include_once("config.php");

verifica_permissao("administracao_central");

$oForm = new formPadrao();
$oForm->setCaminho('Frequência » Acompanhar');
$oForm->setSeparador(0);

$css = array();
$javascript = array();

$css[] = 'css/new/sorter/css/theme.bootstrap_3.min.css';
$javascript[] = 'css/new/sorter/js/jquery.tablesorter.min.js';
$oForm->exibeTopoHTML();


$oDBase = new DataBase('PDO');
$configs = getConfiguracaoHostAutorizado();

$total_servidores = $configs->num_rows();

if(!empty($_GET['autorizacao'])){


    $oDBase->query("DELETE FROM config_host_autorizado WHERE id = :id",
        array(array(":id", $_GET['id'], PDO::PARAM_INT),

            ));
    registraLog("Deletou o registro ".$_GET['id']);

}

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
        
        $(".btn_adicionar_host").on('click', function () {
            window.location.href = 'host_inclusao.php';
        });

            $(".btn_Excluir").click( function(event) {
                var apagar = confirm('Deseja realmente excluir este registro?');
                if (apagar){
                    var dados = $(this).attr('data-rel');

                    $.ajax({
                        type: "GET",
                        url: "host_autorizado_lista.php?autorizacao=true&id=" + dados,

                        success: function( data )
                        {
                            alert('Excluído com sucesso!');
                             window.location.href="host_autorizado_lista.php";

                        }
                    });

                    // aqui vai a instrução para apagar registro
                }else{
                    event.preventDefault();
                }
            });

    });
</script>
<div class="container">

    <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

    <div class="row margin-10">
        <div class="col-md-12 subtitle">
            <h4 class="lettering-tittle uppercase"><strong>Configurações do host autorizado</strong></h4>
        </div>

    <div class="col-md-12">

            <br>
            <div class="row">
                <fieldset class="col-md-2"width='100%'>Total de <?= tratarHTML($total_servidores); ?> registros.</fieldset>
                <div class="col-md-10 text-right margin-bottom-10">
                    <button type="button" class="btn btn-default btn_adicionar_host" data-dismiss="modal"><span class="glyphicon glyphicon-ok"></span> Adicionar</button>
                </div>
                <table id="myTable" class="table table-striped table-bordered text-center table-condensed tablesorter">
                    <thead>
                    <tr>
                        <th class="text-center" style='vertical-align:middle;'>IP</th>
                        <th class="text-center" style='vertical-align:middle;'>Observação</th>
                        <th class="text-center" style='vertical-align:middle;'>Autorizado</th>
                        <th class="text-center" style='vertical-align:middle;'>Ações</th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php while ($configuracao= $configs->fetch_object()): ?>

                    <tr>
                        <td align='left'><?= tratarHTML($configuracao->ip_do_host); ?></td>
                        <td align='left'><?= tratarHTML($configuracao->observacao); ?></td>
                        <td align='left'><?= tratarHTML(($configuracao->autorizado=='S'?'Sim':'Não')); ?></td>
                        <td align='center' class="col-md-1">
                            <div class="col-md-3">
                            <a href='host_alterar.php?id=<?= tratarHTML($configuracao->id); ?>'><img border='0' src='<?= _DIR_IMAGEM_; ?>edicao2.jpg' width='16' height='16' title='Editar'></a>
                            </div>
                            <div class="col-md-2">
                            <a class='btn_Excluir' data-rel='<?= tratarHTML($configuracao->id); ?>'><img border='0' src='<?= _DIR_IMAGEM_; ?>lixeira2.jpg' width='16' height='16'  alt='Excluir' title='Excluir'></a>
                            </div>
                        </td>
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

