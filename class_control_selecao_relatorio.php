<?php

/*
 * Classe de controle da Unidade
 */

include_once("config.php");
include_once("class_selecao_relatorio_dao.php");

class ControlSelecaoRelatorio
{
    /*
     * Elementos
     */

    private $SelecaoRelatorioDao;

    /*
     * Construtor
     */
    function __construct()
    {
        $this->SelecaoRelatorioDao = new SelecaoRelatorioDao;
    }

    /*
     * Metodo para retornar a(s) superintendência(s)
     */
    public function carregaTodasSR($unidade = '')
    {
        return $this->SelecaoRelatorioDao->carregaTodasSR($unidade);
    }

    /*
     * Metodo para retornar a(s) gerência(s)-executiva(s)
     */
    public function carregaGEXPorSR($unidade, $id_ger)
    {
        return $this->SelecaoRelatorioDao->carregaGEXPorSR($unidade, $id_ger);

    }

    /*
     * Metodo para retornar unidade(s)
     */

    public function carregaUnidadesPorGEX($cod_gex)
    {
        return $this->SelecaoRelatorioDao->carregaUnidadesPorGEX($cod_gex);

    }

}
