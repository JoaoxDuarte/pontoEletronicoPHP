<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


/**
 * @class TabBancoDeCompensacoesView
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : banco_de_horas
 *       Suas descrições e características
 *
 * @Camada    - View
 * @Diretório - src/views
 * @Arquivo   - TabBancoDeCompensacoesView.php
 *
 * @author Edinalvo Rosa
 */
class TabBancoDeCompensacoesView extends formPadrao
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
    public function showQuadroDeSaldo( $siape = null, $mes = null, $ano = null, $status = null)
    {
        ?>
        <div class="row margin-10">
            <?php
            $bSoSaldo         = true;
            $bParcial         = ($status == 'HOMOLOGADO' ? false : true);
            $bImprimir        = false;
            $bExibeResultados = false;
            $relatorioTipo    = '0';
            $tipo             = 0;

            if ( is_null($mes) && is_null($ano))
            {
                $mesFim = date('m');
                $anoFim = date('Y');
            }

            include_once( "veponto_saldos.php" );

            if ( is_null($mes) && is_null($ano))
            {
                $mesFim = date('m');
                $anoFim = date('Y');

                $veponto_saldos = imprimirSaldoCompensacaoDoMes();

                print $veponto_saldos;
            }
            else
            {
                $dados = array(
                    'siape'   => $siape,
                    'mes_fim' => $mes,
                    'ano_fim' => $ano
                );
                    
                $veponto_saldos = str_replace("<a id='show-saldos' style='cursor: hand;'><u>Clique aqui para visualizar todos os meses</u></a>", "", imprimirSaldoCompensacaoDoMes(false,$dados));
            }
                
            print $veponto_saldos;
            
            ?>
        </div>
        <?php
    }
    
} // END class TabBancoDeCompensacoesView
