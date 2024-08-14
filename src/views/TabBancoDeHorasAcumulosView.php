<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


/**
 * @class TabBancoDeHorasAcumulosView
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : acumulos_horas
 *       Suas descrições e características
 *
 * @Camada    - View
 * @Diretório - src/views
 * @Arquivo   - TabBancoDeHorasAcumulosView.php
 *
 * @author Edinalvo Rosa
 */
class TabBancoDeHorasAcumulosView extends formPadrao
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
     * @info Exibe quadro de saldo
     *
     * @param string $siape
     * @return void
     */
    public function showQuadroDeSaldo( $result = null)
    {
        $horas    = convertSecondsToHours($result[0]['horas']);
        $usufruto = convertSecondsToHours($result[0]['usufruto']);
        $saldo    = convertSecondsToHours($result[0]['saldo']);
        
        if (is_array($result))
        {
            ?>
            <div class="row margin-10">
                <table class="table table-striped table-bordered text-center table-hover">
                    <thead>
                        <tr>
                            <th class="text-center" colspan="9">
                                <h4><b>Relatório do Banco de Horas para o Servidor</b></h4>
                            </th>
                        </tr>
                        <tr>
                            <th class="text-center">Período Inicial</th>
                            <th class="text-center">Período Final</th>
                            <th class="text-center">Total de Horas Acumuladas</th>
                            <th class="text-center">Total de Horas Usufruídas</th>
                            <th class="text-center">Saldo Final do Banco de Horas	</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= tratarHTML($result[0]['data_inicio']); ?></td>
                            <td><?= tratarHTML($result[0]['data_fim']); ?></td>
                            <td><?= tratarHTML($horas); ?></td>
                            <td><?= tratarHTML($usufruto); ?></td>
                            <td><?= tratarHTML($saldo); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php
        }
    }
    
} // END class TabBancoDeHorasAcumulosView
