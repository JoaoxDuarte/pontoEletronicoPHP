<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


/**
 * @class TabRecessoFimDeAnoView
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : tabrecesso_fimdeano
 *       Suas descrições e características
 *
 * @Camada    - View
 * @Diretório - src/views
 * @Arquivo   - TabRecessoFimDeAnoView.php
 *
 * @author Edinalvo Rosa
 */
class TabRecessoFimDeAnoView extends formPadrao
{
    /*
    * Atributos
    */
    /* @var OBJECT */ public $conexao = NULL;

    /**
     * @constructor
     *
     * Construtor da classe
     */
    public function __construct()
    {
        parent::formPadrao();
    }


    /**
     * @info Exibe o formulário
     *
     * @param array  $dados_recesso Dados do recesso no período
     * @param string $periodo       Perído do recesso
     * @return void
     */
    public function showRecessoQuadroDemonstrativo( $dados_recesso = null, $periodo = '')
    {
        ?>
            <table class="table table-striped table-bordered text-center" style="width:80%;margin-left:10%;margin-right:15%;">
                <thead>
                    <tr>
                        <th class="text-center" colspan='4'>Recesso de Fim de Ano - (<?= $dados_recesso[0]['periodo']; ?>)</th>
                    </tr>
                    <tr>
                        <th class="text-center col-md-3" colspan='1' style='vertical-align:middle;'>Competência</th>
                        <th class="text-center col-md-3" colspan='1' style='vertical-align:middle;'>Horas Excedentes (mês)</th>
                        <th class="text-center col-md-3" colspan='1' style='vertical-align:middle;'>Horas de Recesso</th>
                        <th class="text-center col-md-3" colspan='1' style='vertical-align:middle;' >Sub-Total</th>
                    </tr>
                </thead>
                <tbody>
                    
                    <?php 
                    
                    for ($ind = 0; $ind < count($dados_recesso); $ind++)
                    {
                        $credito  = "+".$dados_recesso[$ind]['credito'];
                        $debito   = (substr($dados_recesso[$ind]['debito'],0,1) == '-' ? '' : '-') . $dados_recesso[$ind]['debito'];
                        $subtotal = (substr($dados_recesso[$ind]['subtotal'],0,1) == '-' ? '' : '+') . $dados_recesso[$ind]['subtotal'];
                        
                        $negativo_credito  = (substr($debito,0,1)   == '-' ? 'color:red' : '');
                        $negativo_subtotal = (substr($subtotal,0,1) == '-' ? 'color:red' : '');
                    
                        if ($dados_recesso[$ind]['compet'] == 'Total')
                        {
                            ?>
                            <tr>
                                <th class="text-center" colspan='1' style='vertical-align:middle;'>TOTAIS</th>
                                <th class="text-center" colspan='1' style='vertical-align:middle;'><?= tratarHTML((time_to_sec($credito) == 0 ? '--------' : $credito)); ?></th>
                                <th class="text-center" colspan='1' style='vertical-align:middle;<?= $negativo_credito;  ?>'><?= tratarHTML((time_to_sec($debito) == 0 ? '--------' : $debito)); ?></th>
                                <th class="text-center" colspan='1' style='vertical-align:middle;<?= $negativo_subtotal; ?>'><?= tratarHTML((time_to_sec($subtotal) == 0 ? '--------' : $subtotal)) . " <sup><font style='font-size:8px;color:black;'>(1)</font></sup>"; ?></th>
                            </tr>
                            <?php
                        }
                        else
                        {
                            ?>
                            <tr>
                                <th class="text-center" colspan='1' style='vertical-align:middle;'><?= $dados_recesso[$ind]['compet']; ?></th>
                                <td class="text-center" colspan='1' style='vertical-align:middle;'><?= tratarHTML((time_to_sec($credito) == 0 ? '--------' : $credito)); ?></td>
                                <td class="text-center" colspan='1' style='vertical-align:middle;<?= $negativo_credito;  ?>'><?= tratarHTML((time_to_sec($debito) == 0 ? '--------' : $debito)); ?></td>
                                <td class="text-center" colspan='1' style='vertical-align:middle;<?= $negativo_subtotal; ?>'><?= tratarHTML((time_to_sec($subtotal) == 0 ? '--------' : $subtotal)); ?></td>
                            </tr>
                            <?php
                        }
                    }
                    
                    ?>
                   <tr>
                       <td colspan="4">
                           <div style='position:relative;top:-10px;width:100%;padding:0px;margin:0px;background-color:transparent;text-align:right;'><small><sup>(1)</sup>Legenda:&nbsp;</small>&nbsp;<small>(+) Recesso compensado; (-) Horas a Compensar.</small></div>
                       </td>
                   </tr>
                        
                </tbody>
            </table>
        <?php
    }

} // END class TabRecessoFimDeAnoView
