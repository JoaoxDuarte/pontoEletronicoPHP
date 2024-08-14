<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");
include_once( "class_ocorrencias_grupos.php" );


/**
 * @class DadosServidoresModel
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : servativ
 *       Suas descrições e características
 *
 * @Camada    - Model
 * @Diretório - src/models
 * @Arquivo   - DadosServidoresModel.php
 *
 */
class DadosServidoresModel
{
    /*
     * Atributos
     */
    public $conexao;

    private $excluir_sitcad        = "'02','08','15'";
    private $excluir_sitcad_ciclos = "'02','08','15','66'";


    public function __construct()
    {
        # Faz conexão
        $this->conexao = new DataBase();
    }


    /**
     * @info Substitutos em efetiva substituição
     *
     * @param string $setor Setor
     * @param string $data  Data
     * @return object
     */
    public function substitutosEfetivados($setor=null, $data=null)
    {
        //obtem dados dos substitutos em efetiva substituição
        $mats_subst = "";

        // DataBase
        $this->conexao->setMensagem("Problemas no acesso a Tabela SUBSTITUICAO (E200001.".__LINE__.").");
        $this->conexao->query("
        SELECT
            subs.siape
        FROM
            substituicao AS subs
        WHERE
            subs.upai     = :upai
            AND subs.fim >= :fim
            AND subs.situacao = 'A'
        ", array(
            array( ":upai", $setor, PDO::PARAM_STR ),
            array( ":fim",  conv_data($data),  PDO::PARAM_STR ),
        ));

        $num_rows = $this->conexao->num_rows();

        if ($num_rows > 0) {
            while ($pms = $this->conexao->fetch_object()) {
                $mats_subst .= "'" . $pms->siape . "',";
            }
        }

        $mats_subst = strstr($mats_subst, ',', true);

        return $mats_subst;
    }


    /**
     * @info Servidores da unidade
     *       com delegação no SISREF
     *
     * @param string $link  Link
     * @param string $setor Setor
     * @param string $data  Data
     * @return object
     */
    public function servidoresComDelegacao($setor='')
    {
        // DataBase
        $this->conexao->setMensagem("Problemas no acesso a Tabela USUARIOS/SERVATIV (E200002.".__LINE__.").");
        $this->conexao->query("
        SELECT
            usuarios.siape
        FROM
            usuarios
        LEFT JOIN
            servativ ON usuarios.siape = servativ.mat_siape
        WHERE
            servativ.cod_lot = :setor
            AND (DATE_FORMAT(NOW(),'%Y-%m-%d') BETWEEN IF(IFNULL(usuarios.datapt,'9999-99-99')='0000-00-00','9999-99-99',IFNULL(usuarios.datapt,'9999-99-99'))
            AND IF(IFNULL(usuarios.dtfim,'9999-99-99')='0000-00-00','9999-99-99',IFNULL(usuarios.dtfim,'9999-99-99')))
            AND servativ.excluido = 'N'
            AND servativ.cod_sitcad NOT IN (".$this->excluir_sitcad.")
        ", array(
            array(':setor', $setor, PDO::PARAM_STR)
        ));

        if ($this->conexao->num_rows() > 0)
        {
            while ($pms = $this->conexao->fetch_object())
            {
                $mats_subst .= "'" . $pms->siape . "',";
            }
        }

        $mats_subst = strstr($mats_subst, ',', true);

        return $mats_subst;
    }


    /**
     * @info Substitutos em efetiva substituição
     *
     * @param string $setor Setor
     * @param string $data  Data
     * @return object
     */
    public function substitutosEfetivadosOuComDelegacao($setor=null, $data=null)
    {
        //obtem dados dos substitutos em efetiva substituição
        $mats_subst = $this->substitutosEfetivados($setor, $data);

        // servidores com delegação
        $mats_deleg = $this->servidoresComDelegacao($setor);

        $mats_subst .= (empty(trim($mats_subst)) || $mats_subst == "''" ? "" : ",") . $mats_deleg;
        $mats_subst = strstr($mats_subst, ',', true);

        return (empty($mats_subst) ? "''" : $mats_subst);
    }


    /**
     * @info Seleção dos registros
     *
     * @param string $link  Link
     * @param string $setor Setor
     * @param string $data  Data
     * @return object
     */
    public function selecionaServidoresUnidade($link=null, $setor=null, $data=null, $homologacao=null)
    {
        $sMatricula = $_SESSION["sMatricula"];
        $setor      = (is_null($setor) || $setor == '' ? $_SESSION["sLotacao"] : $setor);
        $data       = (is_null($data) || $data == '' ? date("Y-m-d") : $data);

        $comp   = dataMes($data) . dataAno($data);
        $compet = dataAno($data) . dataMes($data);

        //obtem dados dos substitutos em efetiva substituição, ou delegação
        $mats_subst = $this->substitutosEfetivadosOuComDelegacao($setor, $data);

        // order by
        if ( !is_null($homologacao) && $homologacao === true )
        {
            $orderby = " IF(IFNULL(homologados.homologado,'N')='N' OR homologados.homologado NOT IN ('V','S'),1,2), servativ.nome_serv";
        }
        else
        {
            $orderby = " servativ.nome_serv, servativ.cod_sitcad, servativ.entra_trab";
        }
        // servidores por unidade
        $query = "
        SELECT
            ocupantes.resp_lot,
            ocupantes.sit_ocup,
            servativ.mat_siape,
            servativ.nome_serv,
            servativ.cod_lot,
            servativ.excluido,
            servativ.chefia,
            servativ.entra_trab,
            servativ.sai_trab,
            servativ.area,
            servativ.cod_sitcad,
            servativ.jornada,
            servativ.motidev,
            tabsetor.uorg_pai,
            pto.entra,
            DATE_FORMAT(pto.dia, '%d/%m/%Y') AS dia,
            pto.intini,
            pto.intsai,
            pto.sai,
            pto.jornd,
            pto.oco,
            pto.just,
            pto.justchef,
            tabocfre.desc_ocorr,
            servativ.sigregjur,
            CONCAT(servativ.cod_lot,' ',tabsetor.sigla) AS `local`,
            tabsetor.descricao AS local_descricao,
            IF(IFNULL(homologados.homologado,'N')='N' OR homologados.homologado NOT IN ('V','S'),'N','S') AS freqh, 
            IF(INSTR(tabcargo.desc_cargo,'medico') > 0 OR (servativ.cod_lot <> :setor), CONCAT(servativ.cod_lot,' ',tabsetor.sigla),'') AS `local`, 
            CONCAT(tabsetor.descricao,' - ',tabsetor.cidade_lota,'/',tabsetor.uf_lota) AS local_descricao, 
            DATE_FORMAT(homologados.desomologado_data,'%Y-%m-%d') AS desomologado_data, 
            IF(DATE_FORMAT(homologados.homologado_data,'%Y%m%d') >= DATE_FORMAT(homologados.desomologado_data,'%Y%m%d') 
                OR ISNULL(homologados.desomologado_data),'N','S') AS frequencia_devolvida,
            (SELECT CONCAT(servativ2.mat_siape,' ',servativ2.nome_serv,' ',servativ2.cod_lot) 
                FROM servativ AS servativ2 
                    WHERE servativ2.mat_siape = homologados.homologado_siape) AS homologador
        FROM
            servativ
        LEFT JOIN
            ponto" . $comp . " AS pto ON servativ.mat_siape = pto.siape AND pto.dia = :data
        LEFT JOIN
            tabocfre ON pto.oco = tabocfre.siapecad
        LEFT JOIN
            tabsetor ON servativ.cod_lot = tabsetor.codigo
	LEFT JOIN
            tabcargo ON servativ.cod_cargo = tabcargo.cod_cargo
	LEFT JOIN
            homologados ON (servativ.mat_siape = homologados.mat_siape) AND homologados.compet = '" . $compet . "'
        LEFT JOIN
            ocupantes ON servativ.mat_siape = ocupantes.mat_siape
        LEFT JOIN
            tabfunc ON ocupantes.num_funcao = tabfunc.num_funcao
        WHERE
            servativ.mat_siape <> :siape
            AND servativ.excluido = 'N'
            AND servativ.cod_sitcad NOT IN (".$this->excluir_sitcad.")
            AND ((servativ.chefia = 'N' AND servativ.cod_lot = :setor)
                OR (servativ.chefia = 'S' AND tabfunc.resp_lot = 'N' AND ocupantes.sit_ocup = 'T' AND servativ.cod_lot = :setor)
                OR (servativ.chefia = 'S' AND tabfunc.resp_lot = 'S' AND ocupantes.sit_ocup = 'T' AND tabsetor.uorg_pai = :setor)
                OR (servativ.mat_siape IN (".$mats_subst.")))
        GROUP BY
            servativ.mat_siape
        ORDER BY
            " . $orderby . "
        ";

        $params = array(
            array(':siape', $sMatricula, PDO::PARAM_STR),
            array(':setor', $setor,      PDO::PARAM_STR),
            array(':data',  $data,       PDO::PARAM_STR),
        );

        // DataBase
        $this->conexao->setMensagem( "Problemas no acesso a Tabela CADASTRO (E200003.".__LINE__.").");
        $this->conexao->setDestino( pagina_de_origem() );
        $this->conexao->query( $query, $params );

        return $this->conexao;
    }


    /**
     * @info Seleção dos registros
     *
     * @param boolean $autorizacoes_required  Requer autorização
     * @return object
     */
    public function selecionaServidoresUnidadeBancoHoras($autorizacoes_required = false)
    {
        $siape = $_SESSION['sMatricula'];
        $setor = $_SESSION['sLotacao'];
        $data  = date('Y-m-d');

        //obtem dados dos substitutos em efetiva substituição, ou delegação
        $mats_subst = $this->substitutosEfetivadosOuComDelegacao($setor, $data);

        $query = "
        SELECT
            servativ.mat_siape       AS matricula,
            servativ.nome_serv       AS nome,
            servativ.mat_siape       AS siape,
            servativ.horae           AS horae,
            servativ.motivo          AS motivo,
            servativ.limite_horas    AS limite_horas,
            tabcargo.PERMITE_BANCO   AS permite_banco,
            tabsetor.periodo_excecao AS excecao,
            servativ.plantao_medico  AS plantao_medico
        FROM
            servativ
        LEFT JOIN
            tabcargo on servativ.cod_cargo = tabcargo.COD_CARGO
        LEFT JOIN
            tabsetor on servativ.cod_lot = tabsetor.codigo
        LEFT JOIN
            ocupantes ON servativ.mat_siape = ocupantes.mat_siape
        LEFT JOIN
            tabfunc ON ocupantes.num_funcao = tabfunc.num_funcao
        ";

        $query .= (empty($autorizacoes_required) ? " LEFT " : " INNER ");

        $query .= "
        JOIN
            autorizacoes_servidores ON servativ.mat_siape = autorizacoes_servidores.siape
        LEFT JOIN
            ciclos ON ciclos.id = autorizacoes_servidores.ciclo_id
        WHERE
            servativ.mat_siape <> :siape
            AND servativ.excluido = 'N'
            AND servativ.cod_sitcad NOT IN (".$this->excluir_sitcad_ciclos.")
            AND ((servativ.chefia = 'N' AND servativ.cod_lot = :setor)
                OR (servativ.chefia = 'S' AND tabfunc.resp_lot = 'N' AND ocupantes.sit_ocup = 'T' AND servativ.cod_lot = :setor)
                OR (servativ.chefia = 'S' AND tabfunc.resp_lot = 'S' AND ocupantes.sit_ocup = 'T' AND tabsetor.uorg_pai = :setor)
                OR (servativ.mat_siape IN (".$mats_subst.")))
        GROUP BY
            servativ.mat_siape
        ORDER BY
            servativ.nome_serv
        ";

        // DataBase
        $this->conexao->setMensagem( "Problemas no acesso a Tabela CADASTRO/AUTORIZACOES BANCO DE HORAS/CICLOS (E200004.".__LINE__.").");
        $this->conexao->query($query,
            array(
                array(":siape", $siape, PDO::PARAM_STR),
                array(":setor", $setor, PDO::PARAM_STR),
        ));

        return $this->conexao;
    }

    function selecionaServidoresUnidadeBancoHorasUsufruto($ciclo_id)
    {
        $siape = $_SESSION['sMatricula'];
        $setor = $_SESSION['sLotacao'];
        $data  = date('Y-m-d');

        $ocorrenciasGrupos = new OcorrenciasGrupos();
        $odigoBancoDeHorasDebitoPadrao = $ocorrenciasGrupos->CodigoBancoDeHorasDebitoPadrao( $this->getSigRegJur($siape) );

        //obtem dados dos substitutos em efetiva substituição, ou delegação
        $mats_subst = $this->substitutosEfetivadosOuComDelegacao($setor, $data);

        $query = "
        SELECT
            servativ.mat_siape  AS matricula,
            servativ.nome_serv  AS nome,
            servativ.mat_siape  AS siape,
            autorizacoes_servidores_usufruto.data_inicio AS data_inicial,
            autorizacoes_servidores_usufruto.data_fim    AS data_final,
            CONCAT(DATE_FORMAT(autorizacoes_servidores_usufruto.data_inicio ,'%d/%m/%Y'),' - ',DATE_FORMAT(autorizacoes_servidores_usufruto.data_fim ,'%d/%m/%Y')) AS periodo,
            UPPER(autorizacoes_servidores_usufruto.tipo_autorizacao) AS modalidade,
            IFNULL(acumulos_horas.horas,0)    AS acumulo,
            IFNULL(acumulos_horas.usufruto,0) AS usufruto,
	    (IFNULL(acumulos_horas.horas,0) - IFNULL(acumulos_horas.usufruto,0)) AS saldo,
	    CASE
		WHEN (IFNULL(acumulos_horas.horas,0) - IFNULL(acumulos_horas.usufruto,0)) <> 0 AND (autorizacoes_servidores_usufruto.data_inicio > NOW() OR NOW() BETWEEN autorizacoes_servidores_usufruto.data_inicio AND autorizacoes_servidores_usufruto.data_fim) AND IFNULL(pto.oco,'') <> :oco THEN 'manutencao'
		WHEN (28800 - IFNULL(acumulos_horas.usufruto,0)) <> 0 AND (autorizacoes_servidores_usufruto.data_inicio > NOW() OR NOW() BETWEEN autorizacoes_servidores_usufruto.data_inicio AND autorizacoes_servidores_usufruto.data_fim) AND IFNULL(pto.oco,'') <> :oco THEN 'manutencao'
		ELSE 'bloqueia'
	    END AS acao
        FROM
            servativ
        LEFT JOIN 
            ponto" . dataMes($data) . dataAno($data) . " AS pto ON servativ.mat_siape = pto.siape AND pto.dia = DATE_FORMAT(NOW(),'%Y-%m-%d')
        LEFT JOIN
            tabsetor ON servativ.cod_lot = tabsetor.codigo
        LEFT JOIN
            ocupantes ON servativ.mat_siape = ocupantes.mat_siape
        LEFT JOIN
            tabfunc ON ocupantes.num_funcao = tabfunc.num_funcao
        LEFT JOIN
            autorizacoes_servidores_usufruto ON autorizacoes_servidores_usufruto.siape = servativ.mat_siape
        LEFT JOIN
            ciclos ON ciclos.id = autorizacoes_servidores_usufruto.ciclo_id
        LEFT JOIN 
	    acumulos_horas ON servativ.mat_siape = acumulos_horas.siape AND acumulos_horas.ciclo_id = :ciclo_id
        WHERE
            servativ.mat_siape <> :siape
            AND servativ.excluido = 'N'
            AND servativ.cod_sitcad NOT IN (".$this->excluir_sitcad_ciclos.")
            AND ((servativ.chefia = 'N' AND servativ.cod_lot = :setor)
                OR (servativ.chefia = 'S' AND tabfunc.resp_lot = 'N' AND ocupantes.sit_ocup = 'T' AND servativ.cod_lot = :setor)
                OR (servativ.chefia = 'S' AND tabfunc.resp_lot = 'S' AND ocupantes.sit_ocup = 'T' AND tabsetor.uorg_pai = :setor)
                OR (servativ.mat_siape IN (".$mats_subst.")))
            AND ciclos.id = :ciclo_id
        ORDER BY
            servativ.nome_serv, autorizacoes_servidores_usufruto.data_inicio DESC
        ";

        // DataBase
        $this->conexao->setMensagem( "Problemas no acesso a Tabela CADASTRO/USUFRUTO/CICLOS (E200005.".__LINE__.").");
        $this->conexao->query($query,
            array(
                array( ":siape",    $siape,    PDO::PARAM_STR),
                array( ":ciclo_id", $ciclo_id, PDO::PARAM_INT),
                array( ":setor",    $setor,    PDO::PARAM_STR),
                array( ":oco",      $odigoBancoDeHorasDebitoPadrao, PDO::PARAM_STR),
        ));

        return $this->conexao;
    }


    /**
     * @info Seleção do servidor - usufruto
     *
     * @param string $siape Matrícula do servidor
     * @return object
     */
    public function selecionaServidorPorMatricula($siape = '', $ciclos=true)
    {
        if (empty($siape) || is_null($siape))
        {
            $retorno = '';
        }
        else
        {
            $usuario = $_SESSION['sMatricula'];
            $siape   = getNovaMatriculaBySiape($siape);
            $setor   = $_SESSION['sLotacao'];
            $data    = date('Y-m-d');

            $ciclos_sitcad = ($ciclos == true ? $this->excluir_sitcad_ciclos : $this->excluir_sitcad);

            //obtem dados dos substitutos em efetiva substituição, ou delegação
            $mats_subst = $this->substitutosEfetivadosOuComDelegacao($setor, $data);

            $query = "
            SELECT
                servativ.nome_serv AS nome,
                tabsetor.inicio_atend,
                tabsetor.fim_atend,
                tabsetor.codmun,
                tabsetor.cod_uorg,
                tabsetor.upag,
                tabsetor.codmun,
                tabsetor.codigo,
                DATE_FORMAT(servativ.dt_adm,'%d/%m/%Y') AS dt_adm,
                tabsetor.descricao,
                taborgao.denominacao,
                taborgao.sigla,
                servativ.cod_sitcad,
                servativ.sigregjur,
                CONCAT(SUBSTRING(servativ.mat_siape,6,11),' - ',servativ.nome_serv) AS servidor
            FROM
                servativ
            LEFT JOIN
                tabsetor ON servativ.cod_lot = tabsetor.codigo
            LEFT JOIN
                taborgao ON LEFT(tabsetor.codigo,5) = taborgao.codigo
            LEFT JOIN
                ocupantes ON servativ.mat_siape = ocupantes.mat_siape
            LEFT JOIN
                tabfunc ON ocupantes.num_funcao = tabfunc.num_funcao
            WHERE
                servativ.mat_siape <> :usuario
                AND servativ.mat_siape = :siape
                AND servativ.excluido = 'N'
                AND servativ.cod_sitcad NOT IN (".$this->excluir_sitcad_ciclos.")
                AND ((servativ.chefia = 'N' AND servativ.cod_lot = :setor)
                    OR (servativ.chefia = 'S'
                        AND tabfunc.resp_lot = 'N'
                        AND ocupantes.sit_ocup = 'T'
                        AND servativ.cod_lot = :setor)
                    OR (servativ.chefia = 'S'
                        AND tabfunc.resp_lot = 'S'
                        AND ocupantes.sit_ocup = 'T'
                        AND tabsetor.uorg_pai = :setor)
                    OR (servativ.mat_siape IN (".$mats_subst.")))
            ";

            // DataBase
            $this->conexao->setMensagem( "Problemas no acesso a Tabela CADASTRO (matrícula ".removeOrgaoMatricula($siape).") (E200006.".__LINE__.").");
            $this->conexao->query($query, array(
                array(":siape",   $siape,                PDO::PARAM_STR),
                array(":usuario", $usuario,              PDO::PARAM_STR),
                array(":setor",   $_SESSION['sLotacao'], PDO::PARAM_STR),
            ));

            $retorno = $this->conexao->fetch_object();
        }

        return $retorno;
    }

    /* @info  Seleciona os registros de frequência do
     *        mês e ourros dados do servidor
     *
     * @param  string  $siape  Matrícula do servidor/estagiário
     * @param  string  $competencia  Competencia da frequência
     * @return  object  Dados da frequência e outros
     *
     * @author Edinalvo Rosa
     */
    function selecionaRegistroPorMatricula($siape)
    {
        $siape = getNovaMatriculaBySiape($siape);

        // DataBase
        $this->conexao->setMensagem("Problemas no acesso a Tabela CADASTRO (matrícula ".removeOrgaoMatricula($siape).") (E200007.".__LINE__.").");
        $this->conexao->query("
        SELECT
            servativ.nome_serv AS nome,
            tabsetor.inicio_atend,
            tabsetor.fim_atend,
            tabsetor.codmun,
            tabsetor.cod_uorg,
            tabsetor.upag,
            tabsetor.codmun,
            tabsetor.codigo,
            DATE_FORMAT(servativ.dt_adm,'%d/%m/%Y') AS dt_adm,
            tabsetor.descricao,
            taborgao.denominacao,
            taborgao.sigla,
            servativ.cod_sitcad,
            servativ.sigregjur
        FROM
            servativ
        LEFT JOIN
            tabsetor ON servativ.cod_lot = tabsetor.codigo
        LEFT JOIN
            taborgao ON LEFT(tabsetor.codigo,5) = taborgao.codigo
        LEFT JOIN
            ocupantes ON servativ.mat_siape = ocupantes.mat_siape
        LEFT JOIN
            tabfunc ON ocupantes.num_funcao = tabfunc.num_funcao
        WHERE
            servativ.mat_siape = :siape
        ORDER BY
            servativ.mat_siape
        ", array(
            array(":siape", $siape, PDO::PARAM_STR),
        ));

        return $this->conexao->fetch_object();
    }

    /* @info  Seleciona os registros de servidor po cargo
     *
     * @param  string  $cargo  Código do cargo
     * @return  object  Dados
     *
     * @author Edinalvo Rosa
     */
    public function selecionaServidorPorCargo($cargo = '')
    {
        $usuario = $_SESSION['sMatricula'];

        // DataBase
        $this->conexao->setMensagem("Problemas no acesso a Tabela CADASTRO (cargo ".$cargo.") (E200008.".__LINE__.").");
        $this->conexao->query("
        SELECT
            servativ.mat_siape,
            servativ.nome_serv,
            servativ.cod_lot,
            servativ.excluido,
            servativ.chefia,
            servativ.cod_sitcad,
            servativ.jornada,
            servativ.sigregjur
        FROM
            servativ
        WHERE
            servativ.cod_cargo = :cargo
            AND servativ.excluido = 'N'
            AND servativ.cod_sitcad NOT IN (".$this->excluir_sitcad.")
        ORDER BY
            servativ.nome_serv
        ", array(
            array(":cargo", $cargo, PDO::PARAM_STR),
        ));

        return $this->conexao;
    }


    /* @info  Lista servidores com liberação de IPs
     *
     * @param  string  $siape  Matrícula do servidor/estagiário
     * @return  string  Data da admissão
     *
     * @author Edinalvo Rosa
     */
    public function selecionaServidoresLiberacaoIPS()
    {
        $usuario = $_SESSION['sMatricula'];
        $setor   = $_SESSION['sLotacao'];
        $data    = date('Y-m-d');

        //obtem dados dos substitutos em efetiva substituição, ou delegação
        $mats_subst = $this->substitutosEfetivadosOuComDelegacao($setor, $data);

        $query = "
        SELECT
            servidores_autorizacao.id AS id,
            servativ.mat_siape        AS matricula,
            servativ.nome_serv        AS servidor,
            CONCAT(
                DATE_FORMAT(servidores_autorizacao.data_inicio ,'%d/%m/%Y' ),
                ' - ',
                DATE_FORMAT(servidores_autorizacao.data_fim ,'%d/%m/%Y' )
            ) AS periodo,
            servidores_autorizacao.justificativa AS justificativa
        FROM
            ips_autorizacao
        LEFT JOIN
            servidores_autorizacao ON ips_autorizacao.servidor_autorizacao_id = servidores_autorizacao.id
        LEFT JOIN
            servativ ON servativ.mat_siape = servidores_autorizacao.siape
        LEFT JOIN
            tabsetor ON servativ.cod_lot = tabsetor.codigo
        LEFT JOIN
            ocupantes ON servativ.mat_siape = ocupantes.mat_siape
        LEFT JOIN
            tabfunc ON ocupantes.num_funcao = tabfunc.num_funcao
        WHERE
            servativ.mat_siape <> :usuario
            AND servativ.excluido = 'N'
            AND servativ.cod_sitcad NOT IN (".$this->excluir_sitcad.")
            AND ((servativ.chefia = 'N' AND servativ.cod_lot = :setor)
                OR (servativ.chefia = 'S' AND tabfunc.resp_lot = 'N' AND ocupantes.sit_ocup = 'T' AND servativ.cod_lot = :setor)
                OR (servativ.chefia = 'S' AND tabfunc.resp_lot = 'S' AND ocupantes.sit_ocup = 'T' AND tabsetor.uorg_pai = :setor)
                OR (servativ.mat_siape IN (".$mats_subst.")))
        ORDER BY
            servativ.nome_serv
        ";

        $params = array(
            array( ":setor",   $setor,   PDO::PARAM_STR),
            array( ":usuario", $usuario, PDO::PARAM_STR),
        );

        
        // DataBase
        $this->conexao->setMensagem("Problemas no acesso a Tabela AUTORIZACAO IPs (setor ".$setor.") (E200009.".__LINE__.").");
        $this->conexao->query($query, $params);

        return $this->conexao;
    }

    
    /* @info  Pega data de admissão para uso
     *        em inserir_dias_sem_frequencia
     *
     * @param  string  $siape  Matrícula do servidor/estagiário
     * @return  string  Data da admissão
     *
     * @author Edinalvo Rosa
     */
    public function getDataAdmissaoDoServidor($siape)
    {
        $siape = getNovaMatriculaBySiape($siape);

        // DataBase
        $this->conexao->setMensagem("Problemas no acesso a Tabela CADASTRO/ADMISSÃO (matrícula ".removeOrgaoMatricula($siape).") (E200010.".__LINE__.").");
        $this->conexao->query("
        SELECT DATE_FORMAT(cad.dt_adm,'%d/%m/%Y') AS dt_adm
            FROM servativ AS cad
                WHERE cad.mat_siape = :siape
        ",
        array(
            array(':siape', $siape, PDO::PARAM_STR)
        ));

        return $this->conexao->fetch_object()->dt_adm;
    }

    
    /**
     * @info Carrega a carreira ao qual o cargo está vinculado
     * 
     * @param string $siape Matrícula do servidor/estagiário
     * @return string
     */
    public function getCarreira( $siape = null )
    {
        $siape = getNovaMatriculaBySiape($siape);

        // DataBase
        $this->conexao->setMensagem("Problemas no acesso a Tabela CADASTRO/CARREIRA (matrícula ".removeOrgaoMatricula($siape).") (E200037.".__LINE__.").");
        $this->conexao->query("
        SELECT tabcargo.carreira
            FROM servativ AS cad
                LEFT JOIN tabcargo ON cad.cod_cargo = tabcargo.COD_CARGO
                    WHERE cad.mat_siape = :siape
        ",
        array(
            array(':siape', $siape, PDO::PARAM_STR)
        ));

        return $this->conexao->fetch_object()->carreira;
    }

    
    /**
     * @info Carrega o nome do servidor/estagiário
     * 
     * @param string $siape Matrícula do servidor/estagiário
     * @return string
     */
    public function getNomeServidor( $siape = null )
    {
        $siape = getNovaMatriculaBySiape($siape);

        // DataBase
        $this->conexao->setMensagem("Problemas no acesso a Tabela CADASTRO/NOME (matrícula ".removeOrgaoMatricula($siape).") (E200011.".__LINE__.").");
        $this->conexao->query("
        SELECT cad.nome_serv AS nome
            FROM servativ AS cad
                WHERE cad.mat_siape = :siape
        ",
        array(
            array(':siape', $siape, PDO::PARAM_STR)
        ));

        return $this->conexao->fetch_object()->nome;
    }


    /* @info  Pega sigla do regime juridico
     *
     * @param  string  $siape  Matrícula do servidor/estagiário
     * @return  string  Sigla do regime juridico
     *
     * @author Edinalvo Rosa
     */
    public function getSigRegJur( $siape = null )
    {
        $siape = getNovaMatriculaBySiape($siape);

        // DataBase
        $this->conexao->setMensagem("Problemas no acesso a Tabela CADASTRO/REGIME JURÍDICO (matrícula ".removeOrgaoMatricula($siape).") (E200012.".__LINE__.").");
        $this->conexao->query("
        SELECT cad.sigregjur
            FROM servativ AS cad
                WHERE cad.mat_siape = :siape
        ",
        array(
            array(':siape', $siape, PDO::PARAM_STR)
        ));

        return $this->conexao->fetch_object()->sigregjur;
    }
    
}
