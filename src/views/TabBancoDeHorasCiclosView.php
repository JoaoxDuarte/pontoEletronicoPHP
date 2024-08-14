<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");


/**
 * @class TabBancoDeHorasCiclosView
 *        Respons�vel por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualiza��o
 *
 * @info TABELA : ciclos
 *       Suas descri��es e caracter�sticas
 *
 * @Camada    - View
 * @Diret�rio - src/views
 * @Arquivo   - TabBancoDeHorasCiclosView.php
 *
 * @author Edinalvo Rosa
 */
class TabBancoDeHorasCiclosView extends formPadrao
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
     * @info Exibe o formul�rio
     *
     * @param void
     * @return void
     */
    public function showFormularioLista($oCiclos=null, $origem=null)
    {
        $total_ciclos = $oCiclos->num_rows();

        ?>
        <table id="myTable" class="table table-striped table-bordered text-center table-condensed tablesorter">
            <div class="col-md-12">
                <div class="col-md-12 text-right">
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

            <tr height='20'>
                <td class='bgtitulo' align="center" colspan="4" nowrap>&nbsp;CICLOS DE BANCO DE HORAS&nbsp;</td>
            </tr>

            <tr>
                <th class="text-center" style='vertical-align:middle;'>�rg�o</th>
                <th class="text-center" style='vertical-align:middle;'>Data Inicial</th>
                <th class="text-center" style='vertical-align:middle;'>Data Final</th>
                <th class="text-center" style='vertical-align:middle;'>A��es</th>
            </tr>

            <tbody>

                <?php while ($ciclo = $oCiclos->fetch_object()): ?>

                    <?php
                    $dateinicialtable = date_create($ciclo->data_inicio);
                    $datefinaltable   = date_create($ciclo->data_fim);
                    ?>
                    <tr>
                        <td align='center'><?= tratarHTML($ciclo->orgao); ?></td>
                        <td align='center'><?= tratarHTML(date_format($dateinicialtable ,"d/m/Y")); ?></td>
                        <td align='center'><?= tratarHTML(date_format($datefinaltable ,"d/m/Y")); ?></td>
                        <td align='center'>
                            <a href='ciclos_alterar.php?id=<?= tratarHTML($ciclo->id); ?><?= (is_null($origem) ? "" : "&org"); ?>'><img border='0' src='<?= _DIR_IMAGEM_; ?>edicao2.jpg' width='16' height='16' align='absmiddle' alt='Hor�rio' title='Editar'></a>
                        </td>
                    </tr>

                <?php endwhile; ?>

            </tbody>
        </table>
        <?php
    }

    //////////////////////////////////////////////////////////
    //                                                      //
    // Os m�todos abaixo est�o para avalia��o ou uso futuro //
    //                                                      //
    //////////////////////////////////////////////////////////

    /**
     * Formul�rio de registro dos dados
     *
     * @return HTML
     */
    public function formularioCadastroBancoDeHorasCiclos()
    {
        ?>
        <?php
    }

    
    /**
     *
     * @param string $retorno Tipo do resultado
     * @param string $descricao Descri��o do item
     * @return void
     */
    public function deleteView($retorno, $descricao)
    {
        switch($retorno)
        {
            case 'success':
                $tipo     = "success";
                $mensagem = "Exclu�do com sucesso o item " . $descricao . ".";
                break;

            case 'warning':
                $tipo     = "warning";
                $mensagem = "H� Plant�o(�es) utilizando esta Escala (" . $descricao . ").";
                break;

            case 'error':
                $tipo     = "danger";
                $mensagem = "N�O houve sucesso na exclus�o do item " . $descricao . ".";
                break;
        }

        retornaInformacao( $mensagem, $tipo);
    }


    /**
     * @info N�meros de registros selecionados
     *
     * @param integer $num_rows N�meros de registros selecionados
     * @return HTML
     */
    public function numeroDeRegistros( $num_rows=0 )
    {
        ?>
        <fieldset class="col-md-3" width='100%'>Total de <?= $num_rows; ?> registros.</fieldset>
        <?php
    }

    /**
     * @info Bot�o adicionar
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
     * @info Bot�o alterar
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
     * @info Bot�o excluir
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
    
} // END class TabBancoDeHorasCiclosView
