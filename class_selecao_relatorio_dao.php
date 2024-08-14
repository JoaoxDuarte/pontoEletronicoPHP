<?php

/*
 * Classe de Acesso a Unidade
 */
include_once 'config.php';

class SelecaoRelatorioDao
{

    /*
     * Elementos
     */

    private $conect_control_sisref;

    /*
     * Função Construtora
     */

    public function __construct()
    {
        $this->conect_control_sisref = new DataBase('PDO');

    }

    /*
     * Metodo para retornar as Superintendencias Regionais
     */

    public function carregaTodasSR($unidade = '')
    {
        if (strlen($unidade) == 5 && substr($unidade, 2, 3) == '150')
        {
            $where = " AND b.cod_ger = '" . $unidade . "'";
        }
        elseif (strlen($unidade) == 5)
        {
            $unidade[2] = '0';
            $where      = " AND a.cod_gex = '" . $unidade . "'";
        }
        else
        {
            $where = "";
        }
        $sql    = "
			SELECT
				b.id_ger,
				b.cod_ger,
				b.nome_ger,
				a.cod_gex,
				a.nome_gex
			FROM
				tabsetor_ger AS b
			LEFT JOIN
				tabsetor_gex AS a ON b.id_ger=a.regional
			WHERE
				b.id_ger <= '5' AND b.ativo='1' " . $where . "
			GROUP BY
				b.id_ger
			ORDER BY
				b.id_ger
		";
        $result = $this->conect_control_sisref->executar_consulta($sql);
        return $result;

    }

    public function carregaGEXPorSR($unidade, $id_ger)
    {
        if (strlen($unidade) == 5 && substr($unidade, 2, 3) != '150')
        {
            $unidade[2] = '_';
            $where      = " AND a.cod_gex LIKE '" . $unidade . "%'";
        }
        else
        {
            $where = "";
        }
        $sql    = "
			SELECT
				b.id_ger,
				b.cod_ger,
				b.nome_ger,
				a.cod_gex,
				a.nome_gex
			FROM
				tabsetor_gex AS a
			LEFT JOIN
				tabsetor_ger AS b ON a.regional=b.id_ger
			WHERE
				b.id_ger = '$id_ger' AND a.ativo='1' " . $where . "
			ORDER BY
				IF(substr(a.cod_gex,3,3)='150',0,1),a.nome_gex ASC
		";
        $result = $this->conect_control_sisref->executar_consulta($sql);
        return $result;

    }

    /*
     * Metodo para retornar Unidade(s)
     */

    public function carregaUnidadesPorGEX($cod_gex)
    {
        $codUnidade    = $cod_gex;
        $codUnidade[2] = '_';
        $sql           = "
            SELECT
                b.codigo,
                b.descricao,
                b.tb0700
            FROM
                tabsetor AS b
            WHERE
                b.codigo LIKE '" . $codUnidade . "%'
                OR (substr(b.codigo,1,3) = '" . substr($cod_gex, 0, 2) . "1')
                OR (substr(b.codigo,1,3) = '" . substr($cod_gex, 0, 2) . "8')
                AND b.ativo = 'S'
            ORDER BY
                IF(substr(b.codigo,3,3)='150',0,IF(substr(b.codigo,3,1)='1',2,IF(substr(b.codigo,3,1)='8',3,1))),b.codigo ASC
		";

        $result = $this->conect_control_sisref->executar_consulta($sql);

        return $result;
    }

}
