<?php
include_once("config.php");
include_once( "src/controllers/TabEscalasController.php" );

verifica_permissao("escalas");

$oEscalas = new TabEscalasController();

if(!empty($_GET['autorizacao']))
{
    $oEscalas->delete( $_GET['id'] );
    exit;
}

$dados = $oEscalas->registrosEscalas();

$oForm = new formPadrao();
$oForm->setCSS('css/new/sorter/css/theme.bootstrap_3.min.css');
$oForm->setJS('css/new/sorter/js/jquery.tablesorter.min.js');
$oForm->setSubTitulo( "Tabela de Cadastro de Escalas" );

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


?>
<script>
    var dados = "";

    $(document).ready(function () {

        $(".btn_adicionar").on('click', function () {
            showProcessandoAguarde();
            window.location.href = 'tabescalas_incluir.php';
        });

        $(".btn_Alterar").click( function(event) {
            var destino = $(this).attr('data-rel');

            console.log(destino);

            showProcessandoAguarde();
            window.location.href = destino;
        });

        $(".btn_Excluir").click( function(event) {
            dados = $(this).attr('data-rel');

            bootbox.confirm({
                locale: "br",
                title: "Excluir Registro",
                message: " Deseja realmente excluir este registro?", 
                buttons: {
                    confirm: {
                        label: "<p style='padding:0px 15px 0px 15px;margin:0px;'>Sim</p>",
                        className: 'btn-success'
                    },
                    cancel: {
                        label: "<p style='padding:0px 15px 0px 15px;margin:0px;'>Não</p>",
                        className: 'btn-danger'
                    }
                },
                callback: function(result) {
                    executaExclusaoEscala(result);
                }
            });
        });
    });
            
    function executaExclusaoEscala(result)
    {
        if (result)
        {
            showProcessandoAguarde();

            $.ajax({
                
                url: "tabescalas.php",
                type: "GET",
                data: "autorizacao=true&id=" + dados,
                dataType: 'json'

            }).done(function(resultado) {
                
                console.log(resultado.mensagem,resultado.tipo);
                hideProcessandoAguarde();
                mostraMensagem( resultado.mensagem, resultado.tipo, 'tabescalas.php' );

            }).fail(function(jqXHR, textStatus ) {
                
                console.log("Request failed: " + textStatus);
                hideProcessandoAguarde();

            }).always(function() {
            
                console.log("completou");
                hideProcessandoAguarde();
            });
        }
    }
</script>

<div class="container">

    <div class="row margin-10">

        <div class="col-md-10 col-md-offset-1">

            <br>
            <div class="row">
                <div class="col-md-12 text-right margin-bottom-10">
                    <button type="button" class="btn btn-default btn_adicionar" style="padding-left:30px;padding-right:30px;">Adicionar</button>
                </div>
                <fieldset class="col-md-3" width='100%'>Total de <?= $dados->num_rows(); ?> registros.</fieldset>
                <table id="myTable" class="table table-striped table-bordered table-hover text-center table-condensed tablesorter">
                    <thead>
                    <tr>
                        <th class="text-center" style='vertical-align:middle;'>ID</th>
                        <th class="text-center" style='vertical-align:middle;'>Trabalhar</th>
                        <th class="text-center" style='vertical-align:middle;'>Folgar</th>
                        <th class="text-center" style='vertical-align:middle;width:30%;'>Descrição</th>
                        <th class="text-center" style='vertical-align:middle;'>Jornada</th>
                        <th class="text-center" style='vertical-align:middle;'>Ativo</th>
                        <th class="text-center" style='vertical-align:middle;'>Ações</th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php while ($rows = $dados->fetch_object()): ?>

                    <tr>
                        <td style="text-align:center;width:1%"><?= tratarHTML($rows->id); ?></td>
                        <td style="text-align:center;width:1%"><?= tratarHTML($rows->trabalhar); ?></td>
                        <td style="text-align:center;width:1%"><?= tratarHTML($rows->folgar); ?></td>
                        <td style="text-align:left;width:50%"><?= tratarHTML($rows->descricao); ?></td>
                        <td style="text-align:center;width:1%"><?= tratarHTML($rows->jornada); ?></td>
                        <td align='center'><?= ($rows->ativo=='S'?'Sim':'Não'); ?></td>

                        <td align='right'>
                            <div class="col-md-6">
                                <a class="btn_Alterar" data-rel='tabescalas_alterar.php?id=<?= tratarHTML($rows->id); ?>' style="cursor:pointer;"><span class="glyphicon glyphicon-pencil" alt='Editar' title='Editar'></span></a>
                            </div>
                            <div class="col-md-2">
                                <a class='btn_Excluir' data-rel='<?= tratarHTML($rows->id); ?>' style="cursor:pointer;"><span class="glyphicon glyphicon-trash" alt='Excluir' title='Excluir'></span></a>
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
