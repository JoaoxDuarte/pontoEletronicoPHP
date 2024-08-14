<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


/**
 * @class TabEscalasView
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : escalas
 *       Suas descrições e características
 *
 * @Camada    - View
 * @Diretório - inc/models
 * @Arquivo   - TabEscalasView.php
 *
 * @author Edinalvo Rosa
 */
class TabEscalasView extends formPadrao
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
     *
     * @param string $retorno Tipo do resultado
     * @param string $descricao Descrição do item
     * @return void
     */
    public function deleteView($retorno, $descricao)
    {
        switch($retorno)
        {
            case 'success':
                $tipo     = "success";
                $mensagem = "Excluído com sucesso o item " . $descricao . ".";
                break;

            case 'warning':
                $tipo     = "warning";
                $mensagem = "Há Plantão(ões) utilizando esta Escala (" . $descricao . ").";
                break;

            case 'error':
                $tipo     = "danger";
                $mensagem = "NÃO houve sucesso na exclusão do item " . $descricao . ".";
                break;
        }

        retornaInformacao( $mensagem, $tipo);
    }


    /**
     * Formulário de registro dos dados
     *
     * @return HTML
     */
    public function formularioCadastroEscalas()
    {
        ?>
        <?php
    }


    /**
     * @info Números de registros selecionados
     *
     * @param integer $num_rows Números de registros selecionados
     * @return HTML
     */
    public function numeroDeRegistros( $num_rows )
    {
        ?>
        <fieldset class="col-md-3" width='100%'>Total de <?= $num_rows; ?> registros.</fieldset>
        <?php
    }

    /**
     * @info Botão adicionar
     *
     * @return HTML
     */
    public function botaoAdicionar()
    {
        ?>
        <div class="col-md-12 text-right margin-bottom-10">
            <button type="button"
                    class="btn btn-default 'btn_adicionar"
                    style="padding-left:30px;padding-right:30px;">
                Adicionar
            </button>
        </div>
        <?php
    }

    /**
     * @info Botão alterar
     *
     * @return HTML
     */
    public function botaoAlterar( $link )
    {
        ?>
        <div class="col-md-6">
            <a class="btn_Alterar" data-rel='<?= $link; ?>' style="cursor:pointer;">
                <img border='0' src='<?= _DIR_IMAGEM_; ?>edicao2.jpg' width='16' height='16' align='absmiddle' alt='Editar' title='Editar'>
            </a>
        </div>
        <?php
    }

    /**
     * @info Botão excluir
     *
     * @return HTML
     */
    public function botaoExcluir( $valor )
    {
        ?>
        <div class="col-md-2">
            <a class='btn_Excluir' data-rel='<?= $rows->id; ?>' style="cursor:pointer;">
                <img border='0' src='<?= _DIR_IMAGEM_; ?>lixeira2.jpg' width='16' height='16'  alt='Excluir' title='Excluir'>
            </a>
        </div>
        <?php
    }

} // END class TabEscalasView
