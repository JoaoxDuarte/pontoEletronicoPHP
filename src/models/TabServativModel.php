<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


/**
 * @class TabServativModel
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : servativ
 *       Suas descrições e características
 *
 * @Camada    - Model
 * @Diretório - src/models
 * @Arquivo   - TabServativModel.php
 *
 */
class TabServativModel
{
    /*
     * Atributos
     */
    public $conexao;

    private $excluir_sitcad        = "'02','08','15'";
    //private $excluir_sitcad_ciclos = "'02','08','15','66'";


    public function __construct()
    {
        # Faz conexão
        $this->conexao = new DataBase();
    }

    
    /* @info  Seleciona servidor/usuário
     *
     * @param  string/null  $chave    Matrícula/CPF do servidor/estagiário
     * @param  string/null  $destino  Destino de retorno em caso de erro
     * @param  string       $limit    Limite de seleção de registro 
     * @return  object/boolean
     *
     * @author Edinalvo Rosa
     */
    public function selecionaServidor($chave = null, $destino = null, $limit = '')
    {
        $result = false;
        $params = array();
        
        if ( !is_null($chave) )
        {
            if (strlen(trim(limpaCPF_CNPJ($chave))) == 11)
            {
                $params[] = array(':cpf', $chave, PDO::PARAM_STR);
                $where  = " AND servativ.cpf = :cpf ";
            }
            else 
            {
                $chave    = getNovaMatriculaBySiape($chave);
                $params[] = array(':siape', $chave, PDO::PARAM_STR);
                $where  = " AND servativ.mat_siape = :siape ";
            }

            if ($_SESSION['sSenhaI'] != 'S' && $destino !== 'entrada.php')
            {
                $params[] = array(":upag", $_SESSION['upag'], PDO::PARAM_STR);
                $where  .= " AND tabsetor.upag = :upag ";
            
                if ($_SESSION['sAPS'] == 'S')
                {
                    $params[] = array(":cod_lot", $_SESSION['sLotacao'], PDO::PARAM_STR);
                    $where  .= " AND servativ.cod_lot = :cod_lot ";
                }
            }
            
            $limit = (empty($limit) ? $limit : " LIMIT ".$limit);
            
            $mensg = 'Problemas no acesso ao Banco de Dados.';
            
            if (is_null($destino))
            {
                $destino = 'entrada.php';
                $mensg   = 'Problemas no acesso a tabela USUARIOS.\\nPor favor tente mais tarde.';
            }
            
            $query = "
            SELECT
                servativ.mat_siape AS siape,
                servativ.cpf,
                servativ.defvis,
                usuarios.prazo,
                usuarios.magico,
                servativ.nome_serv AS nome,
                usuarios.senha,
                usuarios.privilegio,
                tabsetor.codigo AS setor,
                servativ.cod_lot,
                servativ.cod_loc,
                tabsetor.cod_uorg AS uorg,
                tabsetor.upag, 
                usuarios.acesso,
                servativ.cod_sitcad,
                servativ.sigregjur,
                DATE_FORMAT(servativ.dt_ing_lot,'%d/%m/%Y') AS dt_ing_lot,
                DATE_FORMAT(servativ.dt_ing_loc,'%d/%m/%Y') AS dt_ing_loc,
                DATE_FORMAT(servativ.dt_adm,'%Y%m%d') AS dt_adm,
                servativ.area,
                tabsetor.upag,
                tabsetor.descricao,
                servativ.excluido,
                servativ.chefia,
                servativ.entra_trab,
                servativ.ini_interv,
                servativ.sai_interv,
                servativ.sai_trab,
                servativ.autchef,
                servativ.bhoras,
                servativ.horae,
                servativ.motivo,
                servativ.jornada,
                tabsetor.inicio_atend,
                tabsetor.fim_atend,
                tabsetor.codmun,
                IF(SUBSTR(MD5(DATE_FORMAT(servativ.dt_nasc,'%d%m%Y')),1,14)=usuarios.senha,1,0) AS troca_senha,
                servativ.identificacao_apelido,
                servativ.nome_social AS nomesocial,
                servativ.flag_nome_social AS flag,
                servativ.jornada_cargo
            FROM
                servativ
            LEFT JOIN
                usuarios ON servativ.mat_siape = usuarios.siape
            LEFT JOIN
                tabsetor ON servativ.cod_lot = tabsetor.codigo
            LEFT JOIN 
                tabsitcad_prioridade_registro AS prior ON servativ.cod_sitcad = prior.cod_sitcad
            WHERE
                servativ.excluido = 'N'
                AND servativ.cod_sitcad NOT IN (" . $this->excluir_sitcad . ")
                " . $where . "
            ORDER BY
                IF(ISNULL(prior.cod_sitcad),2,1)
            " . $limit;
            
            $this->conexao->setDestino( $destino );
            $this->conexao->setMensagem( $mensg );
            $this->conexao->query( $query, $params );
            
            $result = $this->conexao;
        }
        
        return $result;
    }
    
    
    /* @info  Seleciona dados de servidor e/ou plantonista
     *
     * @param  string  $mat  Matrícula do servidor
     * @return string  Dados do servidor
     *
     * @author Edinalvo Rosa
     */
    public function selecionaDadosServidor_ou_PlantonistaPorMatricula($mat = null)
    {
        $siape = getNovaMatriculaBySiape($mat);

        $query = "
        SELECT 
            servativ.cod_lot, 
            servativ.flag_nome_social, 
            servativ.nome_social, 
            servativ.nome_serv, 
            plantoes_servidores.id, 
            plantoes_servidores.id_plantao, 
            plantoes_servidores.data_criacao, 
            plantoes_servidores.data_encerramento 
        FROM 
            servativ 
        LEFT JOIN 
            plantoes_servidores ON servativ.mat_siape = plantoes_servidores.siape 
                                   AND plantoes_servidores.ativo = 'S' 
                                   AND (NOW() BETWEEN plantoes_servidores.data_criacao 
                                              AND IF(DATE_FORMAT(plantoes_servidores.data_encerramento,'%Y-%m-%d') = '0000-00-00', 
                                                        '9017-12-31', 
                                                        DATE_ADD(plantoes_servidores.data_encerramento, INTERVAL 1 DAY)))
        WHERE 
            mat_siape = :siape
        ";
        
        $params = array(
            array( ":siape", $siape, PDO::PARAM_STR)
        );

        // DataBase
        $this->conexao->setMensagem("Problemas no acesso a Tabela CADASTRO/PLANTONISTAS (matrícula ".removeOrgaoMatricula($siape).") (E200047.".__LINE__.").");
        $this->conexao->query( $query, $params );

        return $this->conexao;
    }
   
    
    /**
     * @info Carrega os dados do SERVATIV para uso no momento que recebe
     *       os dados do Webservice do SIAPE, caso alguns dados venham 
     *       nulo ou em branco usamos estes dados carregados do SERVATIV
     * @param string $mat
     * @return object/boolean
     * 
     * @author Edinalvo Rosa 
     * @version 1.0.0.0 2020-02-22 15.41
     */
    public function updateServerCarregaDadosDoServativ( $mat = null )
    {
        if (is_null($mat) || empty($mat))
        {
            return false;
        }

        $oDBase = new DataBase();

        $mat = getNovaMatriculaBySiape($mat);

        $query = "
        SELECT
            servativ.cpf,
            LEFT(servativ.mat_siape,5) AS orgao,
            servativ.email,
            servativ.defvis,
            servativ.ident_unica,
            servativ.nome_serv,
            servativ.pis_pasep,
            servativ.jornada_cargo,
            servativ.dt_nasc,
            servativ.cod_sitcad,
            servativ.dt_adm,
            servativ.excluido,
            servativ.oco_exclu_oco,
            servativ.oco_exclu_dt,
            servativ.reg_jur_at,
            tabregime.desc_rj,
            servativ.cod_cargo,
            servativ.cod_lot,
            servativ.sigregjur,
            servativ.reg_obito_dt
        FROM
            servativ
        LEFT JOIN
            tabregime ON servativ.reg_jur_at = tabregime.cod_rj
        WHERE
            servativ.mat_siape = :siape
        ";
        
        $params = array(
            array(":siape", $mat, PDO::PARAM_STR)
        );

        $oDBase->setMensagem("Problemas no acesso a Tabela CADASTRO (matrícula ".removeOrgaoMatricula($mat).") (E200048.".__LINE__.")");
        $oDBase->query( $query, $params );

        $result = $oDBase->fetch_object();

        // CASO NO RETORNE VALOR DA CONSULTA
        if (!is_object($result) || empty($result->cpf))
        {
            return false;
        }
        
        return $result;
    }
   
    
    /**
     * @info Grava os recebidos do Webservice do SIAPE no SERVATIV
     * @param string $mat
     * @param array  $dados
     * @return boolean
     * 
     * @author Edinalvo Rosa 
     * @version 1.0.0.0 2020-02-22 16.55
     */
    public function updateServerAtualizarServativ( $mat = null, $dados = null )
    {           
        if (is_null($mat) || empty($mat) || is_null($dados) || !is_array($dados) || count($dados) == 0)
        {
            return false;
        }
        
        $mat = getNovaMatriculaBySiape($mat);
        
        $oDBase = new DataBase();

        // Prepara campos e parametros
        $fields_params = preparaQueryParams($dados, $tabela = 'servativ');

        $params   = $fields_params['params'];
        $params[] = array( ':siape', $mat, PDO::PARAM_STR ); 

        $query  = "UPDATE servativ SET ";
        $query .= $fields_params['fields'];
        $query .= " WHERE mat_siape = :siape ";
        
        $oDBase->setMensagem("Erro na atualização do CADASTRO (E200049.".__LINE__.")");
        $oDBase->query($query, $params);
        
        return ($oDBase->affected_rows() > 0);
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
}
