<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");


/**
 * @class DadosServidoresView
 *        Respons�vel por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualiza��o
 *
 * @info TABELA : servativ
 *       Suas descri��es e caracter�sticas
 *
 * @Camada    - View
 * @Diret�rio - src/views
 * @Arquivo   - DadosServidoresView.php
 *
 */
class DadosServidoresView
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


    /**
     * @info Monta listbox de servidores
     * 
     * @param resource $oDados Dados dos servidores
     * @return object
     */
    public function montaSelectDeServidoresLiberacaoIPS($dados=null,$valor=null)
    {
        ?>
        <select class="form-control select2-single" name="siape" id="siape"/>
            <option value=''></option>
            <?php while ($rows = $dados->fetch_object()): ?>
                <option value='<?= $rows->mat_siape; ?>' <?= (!is_null($valor) && $rows->nome_serv == $valor ? ' selected' : ''); ?>>
                    <?= removeOrgaoMatricula($rows->mat_siape) . ' - ' . $rows->nome_serv; ?>
                </option>
            <?php endwhile; ?>
        </select>        
        <?php
    }
}
