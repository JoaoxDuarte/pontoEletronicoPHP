<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


/**
 * @class TabUsuariosModel
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : tabsetor
 *       Suas descrições e características
 *
 * @Camada    - Model
 * @Diretório - src/models
 * @Arquivo   - TabUsuariosModel.php
 *
 */
class TabUsuariosModel
{
  /*
   * Atributos
   */
  public $conexao;


  public function __construct()
  {
    # Faz conexão
    $this->conexao = new DataBase();
  }


  /**
   * @info Seleção dos registros
   *
   * @param string $siape Matrícula do servidor
   * @return object
   */
    public function dadosUsuarioPorID($siape=null)
    {
        // seleciona os registros para homologação
        $query = "
        SELECT
            usuarios.siape,
            usuarios.nome,
            usuarios.acesso,
            usuarios.setor,
            usuarios.privilegio,
            usuarios.senha,
            usuarios.prazo,
            usuarios.magico,
            usuarios.upag,
            usuarios.defvis,
            usuarios.portaria,
            usuarios.datapt,
            usuarios.ptfim,
            usuarios.dtfim,
            usuarios.recalculo,
            usuarios.refaz_frqano,
            usuarios.nome_soundex
        FROM
            usuarios
        WHERE
            usuarios.siape = :siape
        ";
        
        $paramns = array(
            array( ':siape', getNovaMatriculaBySiape($siape), PDO::PARAM_STR ),
        );

        $this->conexao->query($query, $paramns);

        return $this->conexao;
    }


    /**
     * @info Alterar dados do usuário
     *
     * @param object $dados Dados do usuário
     * @param string $siape Matrícula do servidor
     * @return resource
     */
    public function update($mat = null, $dados = null)
    {
        if (is_null($mat) || empty($mat) || is_null($dados) || !is_array($dados) || count($dados) == 0)
        {
            return false;
        }

        $mat = getNovaMatriculaBySiape($mat);

        $oDBase = new DataBase();

        // Prepara campos e parametros
        $fields_params = preparaQueryParams($dados, $tabela = 'usuarios');

        $params   = $fields_params['params'];
        $params[] = array( ':siape', $mat, PDO::PARAM_STR );

        $query  = "UPDATE usuarios SET ";
        $query .= $fields_params['fields'];
        $query .= " WHERE siape = :siape ";

        $oDBase->setMensagem("Erro na atualização do USUARIOS (E200064.".__LINE__.")");
        $oDBase->query($query, $params);

        return ($oDBase->affected_rows() > 0);
    }


    /**
     * @info Incluir dados do usuário
     *
     * @param object $dados Dados da UORG
     * @return resource
     */
    public function insert($dados)
    {
        $query = "
        INSERT INTO usuarios
            SET
                siape  = :siape,
                nome   = :nome,
                acesso = :acesso,
                setor  = :setor,
                senha  = :senha,
                prazo  = :prazo,
                upag   = :upag,
                defvis = :defvis
        ";
        
        $paramns = array(
            array(':siape',  getNovaMatriculaBySiape($siape), PDO::PARAM_STR),
            array(':nome',   $dados->nome_serv, PDO::PARAM_STR),
            array(':acesso', 'NNSNNNNNNNNNN',   PDO::PARAM_STR),
            array(':setor',  $dados->cod_lot,   PDO::PARAM_STR),
            array(':senha',  $ssenhat,          PDO::PARAM_STR),
            array(':prazo',  '1',               PDO::PARAM_STR),
            array(':upag',   $dados->upag,      PDO::PARAM_STR),
            array(':defvis', $dados->defvis,    PDO::PARAM_STR),
        );

        $this->conexao->query($query, $paramns);

        return $this->conexao;
    }
}
