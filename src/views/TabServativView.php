<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


/**
 * @class TabServativView
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : servativ
 *       Suas descrições e características
 *
 * @Camada    - View
 * @Diretório - src/views
 * @Arquivo   - TabServativView.php
 *
 */
class TabServativView
{
    public function __construct()
    {
        //
    }


    /**
     * @info Monta listbox de servidores
     *
     * @param resource $oDados Dados dos servidores
     * @return object
     */
    public function montaSelectDeServidoresUsufruto($dados=null,$valor=null)
    {
        ?>
        <select class="form-control select2-single" name="siape" id="siape"/>
            <option value=''></option>
            <?php while ($rows = $dados->fetch_object()): ?>
                <option value='<?= $rows->siape; ?>' <?= (!is_null($valor) && $rows->siape == $valor ? ' selected' : ''); ?>>
                    <?= removeOrgaoMatricula($rows->siape) . ' - ' . $rows->nome; ?>
                </option>
            <?php endwhile; ?>
        </select>
        <?php
    }
}
