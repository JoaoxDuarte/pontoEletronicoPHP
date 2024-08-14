<?php

//
// Monta os sql's
//
class sql_seleciona
{

    var $siape;
    var $competencia;
    var $retorna_dados;
    var $dia_escolhido;
    var $unidade_escolhida;
    var $ocorrencia_escolhida;
    var $cargo_escolhido;

    // inicializa a classe e
    // sua variaveis
    function sql_seleciona($unidade_escolhida = '', $ocorrencia_escolhida = '', $dia_escolhido = '', $cargo_escolhido = '', $competencia = '', $siape = '')
    {
        $this->setSiape($siape);
        $this->setCompetencia($competencia);
        $this->setRetornaDados(false);
        $this->setDiaEscolhido($dia_escolhido);
        $this->setUnidadeEscolhida($unidade_escolhida);
        $this->setOcorrenciaEscolhida($ocorrencia_escolhida);
        $this->setCargoEscolhido($cargo_escolhido);

    }

    // define matricula siape
    function setSiape($siape = '')
    {
        $this->siape = $siape;

    }

    function getSiape()
    {
        return $this->siape;

    }

    // define dia escolhido
    function setCompetencia($competencia = '')
    {
        $this->competencia = $competencia;

    }

    function getCompetencia()
    {
        return $this->competencia;

    }

    // indica se haverá retorno de dados
    function setRetornaDados($acao = false)
    {
        $this->retorna_dados = $acao;

    }

    function getRetornaDados()
    {
        return $this->retorna_dados;

    }

    // define dia escolhido
    function setDiaEscolhido($dia_escolhido = '')
    {
        $this->dia_escolhido = $dia_escolhido;

    }

    function getDiaEscolhido()
    {
        return $this->dia_escolhido;

    }

    // define a unidade escolhida
    function setUnidadeEscolhida($unidade = '')
    {
        $this->retorna_dados = $unidade;

    }

    function getUnidadeEscolhida()
    {
        return $this->retorna_dados;

    }

    // define a ocorrencia escolhida
    function setOcorrenciaEscolhida($ocorrencia_escolhida = '')
    {
        $this->ocorrencia_escolhida = $ocorrencia_escolhida;

    }

    function getOcorrenciaEscolhida()
    {
        return $this->ocorrencia_escolhida;

    }

    // define cargo escolhido
    function setCargoEscolhido($cargo_escolhido = '')
    {
        $this->cargo_escolhido = $cargo_escolhido;

    }

    function getCargoEscolhido()
    {
        return $this->cargo_escolhido;

    }

    //
    // monta o sql para selecionar os servidores
    //
		function servidores()
    {
        $retorna_dados     = $this->getRetornaDados();
        $unidade_escolhida = $this->getUnidadeEscolhida();
        $sql               = "
			SELECT DISTINCTROW
				b.mat_siape, b.nome_serv, d.nome_ger, c.cod_gex, c.nome_gex, b.cod_lot, IF(IFNULL(g.lot_nsiape,9)=9,e.descricao,g.lot_nsiape) AS cod_lot_descricao, b.cod_cargo, f.desc_cargo AS cargo_descricao, i.sit_ocup, u.cod_funcao, b.entra_trab, e.upag, b.cod_uorg, d.id_ger, b.nivel
			FROM servativ AS b
			LEFT JOIN lotacao_nova AS g ON b.cod_uorg = g.uorg_anterior
			LEFT JOIN dados_gex AS c ON IF(SUBSTR(b.cod_lot,4,2)='00',CONCAT(LEFT(b.cod_lot,2),'001'),IF(SUBSTR(b.cod_lot,3,3)='150',LEFT(b.cod_lot,5),CONCAT(LEFT(b.cod_lot,2),'0',SUBSTR(b.cod_lot,4,2)))) = c.cod_gex
			LEFT JOIN dados_ger AS d ON c.regional = d.id_ger
			LEFT JOIN tabsetor AS e ON b.cod_lot = e.codigo AND e.ativo = 'S'
			LEFT JOIN tabcargo AS f ON b.cod_cargo = f.cod_cargo
			LEFT JOIN ocupantes AS i ON b.mat_siape = i.mat_siape
			LEFT JOIN tabfunc AS u ON i.num_funcao = u.num_funcao
			WHERE b.excluido = 'N' AND b.cod_sitcad NOT IN ('02','08','15','66') ";

        if (!empty($unidade_escolhida))
        {
            if (substr($unidade_escolhida, 0, 1) == 's')
            {
                $sql .= "AND c.regional = '" . substr($unidade_escolhida, 1, 1) . "' ";
            }
            elseif (substr($unidade_escolhida, 0, 1) == 'g')
            {
                $sql .= "AND e.upag = '" . substr($unidade_escolhida, 1, 9) . "' ";
            }
            else
            {
                $sql .= "AND b.cod_uorg = '" . $unidade_escolhida . "' ";
            }
        }
        /*
          if (!empty($cargo_escolhido))
          {
          switch ($cargo_escolhido)
          {
          case 'medico':   $sql .= "AND f.desc_cargo LIKE '%medico%' "; break;
          case 'analista': $sql .= "AND f.desc_cargo LIKE '%analista do seguro social%' "; break;
          case 'tecnico':  $sql .= "AND f.desc_cargo LIKE '%tecnico do seguro social%' "; break;
          default:         $sql .= "AND f.cod_cargo = '$escolha_cargo' "; break;
          }
          }
         */
        $sql .= "
			GROUP BY b.mat_siape
			ORDER BY d.id_ger, IF(LEFT(b.cod_lot,2)='01',0,IF(SUBSTR(b.cod_lot,3,3)='150',1,2)), b.cod_lot, b.nome_serv ";
        return $sql;

    }

    //
    // monta o sql para selecionar as ocorrenicas
    //
		function ocorrencias()
    {
        $siape                = $this->getSiape();
        $competencia          = $this->getCompetencia();
        $retorna_dados        = $this->getRetornaDados();
        $dia_escolhido        = $this->getDiaEscolhido();
        $ocorrencia_escolhida = $this->getOcorrenciaEscolhida();
        $sql                  = "
			SELECT a.entra, a.oco, CONCAT(a.oco,' - ',h.desc_ocorr) as descricao
			FROM ponto$competencia AS a
			LEFT JOIN tabocfre  AS h ON a.oco = h.siapecad
			WHERE a.dia = '$dia_escolhido' AND a.siape = '$siape' ";
        if ($ocorrencia_escolhida != '99999' && $ocorrencia_escolhida != 'total')
        {
            $sql .= "AND a.oco = '$ocorrencia_escolhida' ";
        }
        $sql .= "ORDER BY a.siape, a.dia ";
        return $sql;

    }

    //
    // monta o sql para selecionar as unidades
    //
		function unidades()
    {
        $sql = "
			SELECT und.cod_uorg, und.descricao, und.upag, ger.nome_ger, gex.cod_gex, gex.nome_gex, gex.regional
			FROM tabsetor AS und
			LEFT JOIN tabsetor_gex AS gex ON und.upag = gex.upag
			LEFT JOIN tabsetor_ger AS ger ON und.regional = ger.id_ger
			LEFT JOIN tabfunc AS fun ON und.codigo = fun.cod_lot
			WHERE
				und.ativo = 'S'
			";

        if ($_SESSION['sBrasil'] == "S")
        {

        }
        else if ($_SESSION['sSR'] == "S")
        {
            $sql .= "AND ger.id_ger='" . $_SESSION['sRegional'] . "' ";
        }
        else if ($_SESSION['sUF'] == "S")
        {
            $sql .= "AND LEFT(und.codigo,2) = '" . substr($_SESSION['sLotacao']) . "' ";
        }
        else if ($_SESSION['sGEX'] == "S")
        {
            $sql .= "AND und.upag='" . $_SESSION['upag'] . "' ";
        }


        $sql .= "
				AND fun.resp_lot='S'
				AND 0 <> ANY (SELECT COUNT(*) FROM servativ AS cad WHERE cad.cod_lot = und.codigo AND excluido='N' AND cod_sitcad NOT IN ('02','08','15','66'))
			ORDER BY
				ger.id_ger,
				IF(LEFT(und.codigo,2)='01',und.codigo,99999999999999),
				IF(SUBSTR(und.codigo,3,3)='150',0,CONCAT(LEFT(und.codigo,2),SUBSTR(und.codigo,4,2))),
				IF(SUBSTR(und.codigo,3,3)='150',0,
				IF(SUBSTR(und.codigo,3,1)='0',1,
				IF(SUBSTR(und.codigo,3,1)='2',2,
				IF(SUBSTR(und.codigo,3,1)='3',3,
				IF(SUBSTR(und.codigo,3,1)='4',4,
				IF(SUBSTR(und.codigo,6,3)='521',4,
				IF(SUBSTR(und.codigo,3,1)='5',6,
				IF(SUBSTR(und.codigo,3,1)='6',7,
				IF(SUBSTR(und.codigo,3,1)='7',8,
				IF(SUBSTR(und.codigo,3,1)='9',9,
				IF(SUBSTR(und.codigo,3,1)='1',10,
				IF(SUBSTR(und.codigo,3,1)='8',11, 99)))))))))))), und.codigo
			";

        return $sql;

    }

}

/* -----------------------------------------------------------------------\
  | função: para uso do smap                                               |
  \----------------------------------------------------------------------- */

function exec_query($sql = '', $id = 1)
{
    $db = new DataBase('PDO');
    $db->setMensagem("Erro no acesso ao SMAP!");
    switch ($id)
    {
        // conexão com o SMAP
        case 1:
            $db->setHost("10.120.0.153");
            $db->setUsuario("smap_consulta");
            $db->setSenha("smap!");
            $db->setDBase("controle_greve_medicos");
            break;

        // conexão com o SISREF
        case 3:
            $db->setHost("10.120.2.5");
            $db->setUsuario("sisref");
            $db->setSenha("SisReF2013app");
            $db->setDBase("sisref");
            break;
    }
    if (empty($sql))
    {
        mensagem("Erro: SQL ausente!");
        return -1;
    }
    $res = $db->query($sql);
    return $res;

}

function conectar($id = 3)
{
    // compatibilidade

}

function converteData($db = 'MYSQL', $local = 'PTBR', $data)
{
    return databarra($data);

}
