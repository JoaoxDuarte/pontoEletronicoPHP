<?php

/**  @package DataBase (Class and Functions)
 * +-------------------------------------------------------------+
 * |                                                             |
 * | Fun  es e Classes                                           |
 * |                                                             |
 * | @package    : class and functions                           |
 * | @copyright  : (C) 2004-2015 INSS                            |
 * | @license    :                                               |
 * | @link       : http://www-sisref                             |
 * | @subpackage :                                               |
 * | @author     :                                               |
 * |                                                             |
 * +-------------------------------------------------------------+
 * |   Conven  es:                                               |
 * |      <> -> indicam parametros                               |
 * |      [] -> indicam parametros obrigat rios                  |
 * +-------------------------------------------------------------+
 **/


/**  @Class
 * +-------------------------------------------------------------+
 * | @class       : DataBase                                     |
 * | @description : operacao em banco de dados                   |
 * |                                                             |
 * | @param  : void                                              |
 * | @return : void                                              |
 * | $method : DataBase          Construtor                      |
 * | $method : setTabela()       Define a tabela para uso        |
 * | $method : setCampos()       Campos da tabela                |
 * | $method : setVoltar()       N mero de p ginas a retornar    |
 * | $method : setDestino()      P gina de destino/retorno       |
 * | $method : setTituloErro()   Titulo da mensagem de erro      |
 * | $method : setMensagem()     Mensagem de erro                |
 * | $method : setSemDBErro()    True: n o exibir  a mensagem    |
 * | $method : setHost()         Servidor de banco de dados      |
 * | $method : setUsuario()      Usu rio do banco de dados       |
 * | $method : setSenha()        Senha do usu rio                |
 * | $method : setDBase()        Banco de dados                  |
 * | $method : setId()           Identificador de conex o MySQL  |
 * | $method : setIdLink()       Identificador de conex o MySQL  |
 * | $method : setConn()         Indica sucesso ou n o (boolean) |
 * | $method : pesquisar()       Executa um SQL                  |
 * | $method : query()           O mesmo que 'pesquisar'         |
 * | $method : execute()         Sin nimo de 'pesquisar'         |
 * | $method : registro()        Resultado da sele  o (objeto)   |
 * | $method : fetch_object)     Resultado da sele  o (objeto)   |
 * | $method : fetch_array()     Resultado da sele  o (array)    |
 * | $method : fetch_assoc()     Resultado da sele  o (array)    |
 * | $method : insert_id()       Incrementar uma sequ ncia       |
 * | $method : num_rows()        N mero de linhas selecionadas   |
 * | $method : affected_rows()   N mero de linhas afetadas       |
 * | $method : num_registros()   N mero de linhas afetadas       |
 * | $method : data_seek()       Posiciona o ponteiro da tabela  |
 * | $method : free_result()     Libera a mem ria                |
 * | $method : close()           Fecha a conex o com o servidor  |
 * | $method : error()           Mensagem de erro do MySQL       |
 * | @usage  : $oData = new DataBase;                            |
 * |           $oData->query('SELECT ...');                      |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : mensagem()    (function.php)                  |
 * |               voltar()      (function.php)                  |
 * +-------------------------------------------------------------+
 **/

/**  @Function
 * +--------------------------------------------------------------------+
 * | Classe.....: DataBase                                              |
 * +--------------------------------------------------------------------+
 **/
class DataBase
{
    /*
     * Propriedades
     */
    private $engine;

    private $id; // mantido por compatibilidade, o mesmo que idResult
    private $idLink;
    private $idResult;
    private $conn;
    private $titulo_erro;
    private $voltar;
    private $destino;
    private $campos;
    private $tabela;
    private $mensagem;
    private $sem_db_erro;

    private $dbError;
    private $dbErrorCode;
    private $resource;

    private $host;
    private $usuario;
    private $senha;
    private $dbase;

    private static $dbinstance;
    private $db;
    private $stmt;

    /*
     * Fun  o Construtora
     */
    public function __construct($engine='PDO')
    {
        $this->engine = $engine;
        $this->setCampos( '*' );
        $this->setTabela( 'servativ' );
        $this->setVoltar( 1 );
        $this->setDestino( '' );
        $this->setSemDBErro( false );
        $this->setTituloErro( "Erro:" );

        // dados para conex o ao banco
        $this->setHostSISREF();
    }

    /*
     * Conex o ao banco de dados
     */
    public function setHostSISREF()
    {
        $db_user = 'sisref_app';
        if (isset($_SESSION['sModuloPrincipalAcionado']))
        {
            $db_user = 'sisref_app';
        }

        // Servidor (host) para conex o
        $db_host = 'localhost';

        switch ($db_host)
        {
            case 'localhost':
            case '0.0.0.0':
            case '10.233.162.28:3306':
				$db_user = 'sisref_app';

                putenv("MYSQL_HOST=localhost");
                putenv("MYSQL_USER=sisref_app");
                putenv("MYSQL_PASSWORD=SisReF2013app");
                putenv("MYSQL_DATABASE=dbpro_11310_sisref");
                putenv("MYSQL_ROOT_PASSWORD=SisReF2013app");
                break;

            default:
				$db_host = 'localhost';
                break;
        }

        //$db_host = "db";

        //FIXME: Verificar se est  dando deploy no rancher ou n o
        $rancher = false;

        if($rancher)
        {
            $this->setHost( 'localhost' );
            $this->setUsuario('sisref_app');
            $this->setSenha('SisReF2013app');
            $this->setDBase('dbpro_11310_sisref');
        }
        else
        {
                putenv("MYSQL_HOST=localhost");
                putenv("MYSQL_USER=sisref_app");
                putenv("MYSQL_PASSWORD=SisReF2013app");
                putenv("MYSQL_DATABASE=dbpro_11310_sisref");
                putenv("MYSQL_ROOT_PASSWORD=SisReF2013app");

            $this->setHost(getenv('MYSQL_HOST'));
			$this->setUsuario( getenv('MYSQL_USER') );  // 'sisref_app'
            $this->setSenha( getenv('MYSQL_PASSWORD')); // 'SisReF2013app'
			$this->setDBase( getenv('MYSQL_DATABASE') ); // 'sisref'
        }
    }

    public static function getConexao($driver, $host, $database, $charset, $user, $password)
    {
        //'mysql', 'localhost','dbpro_11310_sisref','latin1','root',''
        if (!isset(self::$dbinstance))
        {
            //try {
            self::$dbinstance = new PDO(
                "{$driver}:host={$host};dbname={$database};charset={$charset}",
                "{$user}",
                "{$password}",
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION/*,
                    PDO::ATTR_PERSISTENT => true*/
                )
            );

            /*} catch (\PDOException $e) {
                // TODO: melhorar
                echo '<pre>' . $e->getTraceAsString();
            }*/
        }

        return self::$dbinstance;
    }

    public static function fechaConexao()
    {
        self::$dbinstance = null;
    }

    // Conex o
    public function conectar()
    {
        switch ($this->engine)
        {
            case 'mysqli': //exit(print_r($this));
                $this->db = new mysqli( $this->host, $this->usuario, $this->senha, $this->dbase );
                if ($this->db->connect_error)
                {
                    $this->dbError = ($this->db->connect_error ? $this->db->connect_errno . ' - ' . $this->db->connect_error : '');
                    $this->idLink = false;
                    $this->conn = '';
                }
                else
                {
                    $this->dbError = '';
                    $this->idLink = true;
                    $this->db->set_charset('latin1');
                    $con = $this->db->query("SELECT DATABASE()")->fetch_row();
                    $this->conn = $con[0];
                }
                //$this->db->query( "SET NAMES'utf8'");
                //$this->db->query( 'SET character_set_connection=utf8' );
                //$this->db->query( 'SET character_set_client=utf8' );
                //$this->db->query( 'SET character_set_results=utf8' );
                break;

            default:
                $this->idLink = @mysql_connect( $this->host, $this->usuario, $this->senha );
                $this->conn  = @mysql_select_db( $this->dbase, $this->idLink );
                //mysql_query( "SET NAMES'utf8'" );
                //mysql_query( 'SET character_set_connection=utf8' );
                //mysql_query( 'SET character_set_client=utf8' );
                //mysql_query( 'SET character_set_results=utf8' );
                break;
        }
    }

    

    /*
     * Setter's e Getter's
     */
    public function setTabela($tab='servativ') { $this->tabela = $tab; }
    public function getTabela()                { return $this->tabela; }

    public function setCampos($cmps='*') { $this->campos = $cmps; }
    public function getCampos()          { return $this->campos; }

    public function setVoltar($voltar=1) { $this->voltar = $voltar; }
    public function getVoltar()          { return $this->voltar; }

    public function setDestino($destino='') { $this->destino = $destino; }
    public function getDestino()               { return $this->destino; }

    public function setModulo($modulo='') { $this->modulo = $modulo; }
    public function getModulo()           { return $this->grupo[$this->modulo]; }

    public function setTituloErro($titulo_erro='') { $this->titulo_erro = $titulo_erro; }
    public function getTituloErro() { return $this->titulo_erro; }

    public function setMensagem($mensagem='') { $this->mensagem = $mensagem; }
    public function getMensagem() { return $this->mensagem; }

    public function setSemDBErro($sem_db_erro=false) { $this->sem_db_erro = $sem_db_erro; }
    public function getSemDBErro() { return $this->sem_db_erro; }

    public function setHost($host='localhost') { $this->host = $host; }
    public function getHost() { return $this->host; }

    public function setUsuario($usuario='sisref_app') { $this->usuario = $usuario; }
    public function getUsuario() { return $this->usuario; }

    public function setSenha($senha='SisReF2013app') { $this->senha = $senha; }
    public function getSenha() { return $this->senha; }

    public function setDBase($dbase='dbpro_11310_sisref') { $this->dbase = $dbase; }
    public function getDBase() { return $this->dbase; }

    public function setId($value='') { $this->id = $value; }
    public function getId() { return $this->id; }

    public function setIdLink($value='') { $this->idLink = $value; }
    public function getIdLink() { return $this->idLink; }

    public function setIdResult($value='') { $this->idResult = $value; }
    public function getIdResult() { return $this->idResult; }

    public function setSelecionadoDB($value='') { $this->conn = $value; }
    public function getSelecionadoDB() { return $this->conn; }


    /*
     * M dulos operacionais
     */
    public function pesquisar($pesq='', $params = null)
    {
        // conex o
        if ($this->engine == 'PDO')
        {
            $db = null;
            $this->stmt = null;

            try
            {
                $db = self::getConexao(
                    'mysql',
                    'localhost',
                    'dbpro_11310_sisref',
                    'latin1',
                    'sisref_app',
                    'SisReF2013app'
                );

                $this->dbError = '';
                $this->idLink = true;
                $this->conn = $this->dbase;

                $db->beginTransaction();

                try
                {
                    if (is_array($params) && count($params))
                    {
                        $this->stmt = $db->prepare($pesq);

                        foreach ($params as $param)
                        {
                            $this->stmt->bindValue($param[0], $param[1], $param[2]);
                        }

                        //$t1 = -microtime(true);

                        $this->stmt->execute();

                        //$t1 += microtime(true);
                    }
                    else
                    {
                        //$t1 = -microtime(true);
                        $this->stmt = $db->query($pesq);
                        //$t1 += microtime(true);
                    }

                    $db->commit();
                }
                catch (PDOException $e)
                {
                    $db->rollback();
                    $this->dbError     = $e->getMessage();
                    $this->dbErrorCode = $e->getCode();
                }
            }
            catch (PDOException $e)
            {
                $this->dbError = $e->getMessage();
                $this->dbErrorCode = $e->getCode();
            }
            
            if ( !empty($this->dbError))
            {
                if ($this->dbErrorCode == "2002")
                {
                    $_SESSION['mensagem-usuario'] = array(
                        'mensagem'   => '(BD2002) Nenhuma conex o p de ser feita, banco de dados do ' . _SISTEMA_SIGLA_ . '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;inacess vel no momento. Tente mais tarde.',
                        'severidade' => 'danger'
                    );
                
                    replaceLink( "login" );
                    exit();
                }
                else
                {
                    $_SESSION['mensagem-usuario'] = array(
                        'mensagem' => $this->getMensagem() . "<br>" . $this->dbError,
                            'severidade' => 'danger'
                    );
                
                    replaceLink( $this->getDestino() );
                }

                $this->idLink = false;
                $this->conn = '';
            }
        }

        if (empty($this->dbError))
        {
            //$this->idResult = $res;
            if ($this->engine == 'PDO')
            {
                $return = $this->stmt;
            }
            else
            {
                $return = $this->idResult;
            }
        }
        else
        {
            $dberro = ($this->sem_db_erro == true ? '' : '\\n'.$this->dbError);

            if ($this->getMensagem() == 'nulo')
            {
                $return = -1;
                self::fechaConexao();
                $_SESSION['mensagem-usuario'] = array(
                    'mensagem' => 'Houve um erro ao comunicar com o banco de dados do ' . _SISTEMA_SIGLA_ . ' (E0001)!' . ($_SESSION['sMatricula'] == '0910343' ? '\\n'.$this->dbError : ''),
                    'severidade' => 'danger');
                voltar(1);
            }
            else
            {
                if (empty($this->getMensagem()))
                {
                    $destino = $this->getDestino();

                    //mensagem( $this->titulo_erro.$dberro );
                    //mensagem( "Falha na execu  o de um dos procedimentos!\\nInforma  o encaminhada para o Suporte T cnico." );
                    self::fechaConexao();
                    $_SESSION['mensagem-usuario'] = array(
                        'mensagem' => 'Houve um erro ao comunicar com o banco de dados do ' . _SISTEMA_SIGLA_ . ' (E0002)!' . ($_SESSION['sMatricula'] == '0910343' ? '\\n'.$this->dbError : ''),
                        'severidade' => 'danger');
                    if (empty($destino))
                    {
                        voltar(1);
                    }
                    else
                    {
                        replaceLink($destino);
                    }
                }
                else
                {
                    $destino = $this->getDestino();

                    //mensagem( $this->getMensagem().$dberro );
                    //mensagem( $this->getMensagem(), null, 1 );
                    self::fechaConexao();
                    $_SESSION['mensagem-usuario'] = array(
                        'mensagem' => $this->getMensagem() . ($_SESSION['sMatricula'] == '0910343' ? '\\n'.$this->dbError : ''),
                        'severidade' => 'danger');
                    if (empty($destino))
                    {
                        voltar(1);
                    }
                    else
                    {
                        replaceLink($destino);
                    }
                }

                $comunicaErro = '
                    <table style="border: 1px solid #e9e9e9;">
                    <tr style="border: 1px solid #e9e9e9;"><td nowrap>LINHA: </td><td>&nbsp;'.__LINE__.'</td></tr>
                    <tr style="border: 1px solid #e9e9e9;"><td nowrap>ARQUIVO: </td><td>&nbsp;'.__FILE__.'</td></tr>
                    <tr style="border: 1px solid #e9e9e9;"><td nowrap>HOST: </td><td>&nbsp;'.$_SERVER['SERVER_ADDR'].'</td></tr>
                    <tr style="border: 1px solid #e9e9e9;"><td nowrap>DIRETORIO: </td><td>&nbsp;'.__DIR__.'</td></tr>
                    <tr style="border: 1px solid #e9e9e9;"><td nowrap>FUNCAO: </td><td>&nbsp;'.__FUNCTION__.'</td></tr>
                    <tr style="border: 1px solid #e9e9e9;"><td nowrap>CLASSE: </td><td>&nbsp;'.__CLASS__.'</td></tr>
                    <tr style="border: 1px solid #e9e9e9;"><td nowrap>__TRAIT__: </td><td>&nbsp;'.__TRAIT__.'</td></tr>
                    <tr style="border: 1px solid #e9e9e9;"><td nowrap>METODO: </td><td>&nbsp;'.__METHOD__.'</td></tr>
                    <tr style="border: 1px solid #e9e9e9;"><td nowrap>__NAMESPACE__: </td><td>&nbsp;'.__NAMESPACE__.'</td></tr>
                    <tr style="border: 1px solid #e9e9e9;"><td nowrap></td><td>&nbsp;</td></tr>
                    <tr style="border: 1px solid #e9e9e9;"><td nowrap>ERRO: </td>
                    <td>&nbsp;
                ';
                $comunicaErro .= $this->dbError;
                $comunicaErro .= '
                    </td>
                    </tr>
                    <tr style="border: 1px solid #e9e9e9; text-align: top;">
                        <td nowrap>MATRICULA: </td><td>&nbsp;'.tratarHTML($_SESSION['sMatricula']).'</td>
                    </tr>
                    <tr style="border: 1px solid #e9e9e9; text-align: top;">
                        <td nowrap>LOTACAO: </td><td>&nbsp;'.tratarHTML($_SESSION['sLotacao']).'</td>
                    </tr>
                    <tr style="border: 1px solid #e9e9e9; text-align: top;">
                        <td nowrap>SQL: </td><td>&nbsp;'.tratarHTML($pesq).'</td>
                    </tr>
                    <tr style="border: 1px solid #e9e9e9; text-align: top;">
                        <td nowrap>REQUEST_URI: </td><td>&nbsp;'.tratarHTML($_SERVER['REQUEST_URI']).'</td>
                    </tr>
                    <tr style="border: 1px solid #e9e9e9; text-align: top;">
                        <td nowrap>PAGINA DE ORIGEM: </td><td>&nbsp;'.pagina_de_origem().'</td>
                    </tr>
                    <tr style="border: 1px solid #e9e9e9; text-align: top;">
                        <td nowrap>MENSAGEM DO MODULO: </td><td>&nbsp;'.$this->getMensagem().'</td>
                    </tr>
                    </table>
                ';
                comunicaErro( $comunicaErro );
            }

            exit();
        }

        return $return;
    }

    private function mysqli_list_db_tables($db_nome)
    {
        $tables = Array();
        $results = $this->query("
            SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA LIKE '$db_nome'
        ");

        while ($row = $results->fetch_assoc())
        {
            $tables[] = $row[0];
        }

        return $tables;
    }

    public function queryLoop($pesq='',$nVezes=3, $params = null)
    {
        $pesq = trim($pesq);

        for ($loop=0; $loop < $nVezes; $loop++)
        {
            $this->idResult = $this->pesquisar($pesq, $params);

            if ((substr_count($pesq,'UPDATE ') > 0) || (substr_count($pesq,'INSERT ') > 0) || (substr_count($pesq,'DELETE ') > 0))
            {
                if ($this->affected_rows() > 0) { break; }
            }
            else
            {
                break;
            }
        }

        return $this->idResult;
    }
    public function query($pesq='', $params = null) { return $this->pesquisar($pesq, $params); }
    public function executar_consulta($sql='')      { return $this->pesquisar($sql); }
    public function execute($sql='')                { return $this->pesquisar($sql); }
    public function registro($id='')
    {
        if ($this->engine == 'mysqli') { return $this->fetch_object( ($id==''?$this->idResult:$id) ); }
        else                           { return mysql_fetch_object( ($id==''?$this->idResult:$id) ); }
    }

    /*
     * Linha atual como um objeto
     */
    public function fetch_object($id='')
    {
        if ($this->engine == 'PDO')
        {
            return $this->stmt->fetchObject();
        }
        else if ($this->engine == 'mysqli')
        {
            $obj = (is_object($id) ? $id->fetch_object() : $this->idResult->fetch_object());
            return $obj;
        }
        else
        {
            return mysql_fetch_object( ($id==''?$this->idResult:$id) );
        }
    }

    /*
     * Linha atual como uma matriz associativa/num rica
     */
    public function fetch_array($id='')
    {
        if ($this->engine == 'PDO')
        {
            return $this->stmt->fetch(PDO::FETCH_BOTH);
        }
        elseif ($this->engine == 'mysqli')
        {
            $arr = (is_object($id) ? $id->fetch_array(MYSQLI_BOTH) : $this->idResult->fetch_array(MYSQLI_BOTH));
            return $arr;
        }
        else
        {
            return mysql_fetch_array( ($id==''?$this->idResult:$id) );
        }
    }

    /*
     * Linha atual como uma matriz associativa
     */
    public function fetch_assoc($id='')
    {
        if ($this->engine == 'PDO')
        {
            return $this->stmt->fetch(PDO::FETCH_ASSOC);
        }
        else if ($this->engine == 'mysqli')
        {
            $arr = (is_object($id) ? $id->fetch_assoc() : $this->idResult->fetch_assoc());
            return $arr;
        }
        else
        {
            return mysql_fetch_assoc( ($id==''?$this->idResult:$id) );
        }
    }
    public function fetch_row($id='')
    {
        if ($this->engine == 'PDO')
        {
            return $this->stmt->fetch();
        }
        else if ($this->engine == 'mysqli')
        {
            $arr = (is_object($id) ? $id->fetch_row() : $this->idResult->fetch_row());
            return $arr;
        }
        else
        {
            return mysql_fetch_row( ($id==''?$this->idResult:$id) );
        }
    }

    /*
     * ID gerado automaticamente no  ltimo INSERT
     */
    public function insert_id()
    {
        if ($this->engine == 'PDO')         { return self::$db->lastInsertId; }
        else if ($this->engine == 'mysqli') { return $this->idResult->insert_id; }
        else                                { return mysql_insert_id(); }
    }

    /*
     * N mero de linhas resultantes
     */
    public function num_rows($id='')
    {
        if ($this->engine == 'PDO')
        {
            if (method_exists($this->stmt,'rowCount'))
            {
                return $this->stmt->rowCount();
            }
            else
            {
                return 0;
            }
        }
        else if ($this->engine == 'mysqli')
        {
            $arr = (is_object($id) ? $id->num_rows : $this->idResult->num_rows);
            return $arr;
        }
        else
        {
            return mysql_num_rows( ($id==''?$this->idResult:$id) );
        }
    }

    /*
     * N mero de linhas resultantes
     */
    public function num_registros($id='')
    {
        return $this->num_rows( $id );
    }

    /*
     * N mero de linhas resultantes
     */
    public function affected_rows()
    {
        if ($this->engine == 'PDO')
        {
            if (method_exists($this->stmt,'rowCount'))
            {
                return $this->stmt->rowCount();
            }
            else
            {
                return 0;
            }
        }
        else if ($this->engine == 'mysqli') { return $this->db->affected_rows; }
        else                                { return mysql_affected_rows(); }
    }

    /*
     * Posiciona o ponteiro do resultado em uma linha escolhida
     */
    public function data_seek($nreg=0,$id='')
    {
        if ($this->engine == 'PDO')
        {
            $targetRow = $this->stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT, $nreg);
        }
        else if ($this->engine == 'mysqli')
        {
            return $this->db->data_seek($nreg);
        }
        else
        {
            return mysql_data_seek( ($id==''?$this->idResult:$id), $nreg );
        }
    }

    /*
     * Libera a mem ria associada ao resultado
     */
    public function free_result($id='')
    {
        if ($this->engine == 'PDO')         { $this->stmt = null; }
        else if ($this->engine == 'mysqli') { return $this->db->free_result; }
        else                                { return mysql_free_result( ($id=='' && $id!=1?$this->idResult:$id) ); }
    }

    /*
     * Lista tabelas do banco de dados escolhido
     */
    public function list_tables($db_nome='')
    {
        if ($this->engine == 'mysqli'
            || $this->engine == 'PDO') { return $this->mysqli_list_db_tables($db_nome); }
        else                           { return mysql_list_tables($db_nome); }
    }

    /*
     * Fecha uma conex o aberta anteriormente com o banco de dados
     */
    public function close()
    {
        if ($this->engine == 'PDO')         { return null; }
        else if ($this->engine == 'mysqli') { return $this->db->close; }
        else                                { return mysql_close($this->idLink); }
    }

    /*
     * Mensagem de erro query/connect
     */
    public function error()
    {
        if ($this->engine == 'mysqli') { return $this->dbError; }
        else                           { return $this->dbError; }
    }


    /*
     * Mensagem de erro exibir e registrar em log
     */
    private function ExibirErro($tipo, $exception, $so_log = false)
    {
        $trace = $exception->getTrace();

        //this code should log the exception to disk and an error tracking system
        // Encaminha e-mail para o Suporte t cnico
        $mensagem  = "\n\n[" . date("Y-m-d H:i:s") . ":" . time() . "][".$tipo."] ##############################################################################################\n";
        $mensagem .= "Mensagem: "      . $exception->getMessage()       . "\n";
        $mensagem .= "Arquivo: "       . $exception->getFile()          . "\n";
        $mensagem .= "Linha: "         . $exception->getline()          . "\n";
        $mensagem .= "Function: "      . $trace[0]['function']          . "\n";
        $mensagem .= "Class: "         . $trace[0]['class']             . "\n";
        $mensagem .= "Method: "        . $trace[0]['method']            . "\n";
        $mensagem .= "TraceAsString: "  . "\n" . $exception->getTraceAsString() . "\n";
        $mensagem .= "\t\t__TRAIT__:     " . __TRAIT__ . "\n";
        $mensagem .= "\t\t__NAMESPACE__: " . __NAMESPACE__ . "\n";
        $mensagem .= "\t\tUSU RIO: " . $_SESSION['sMatricula'] . "\n";
        $mensagem .= "\t\tLOTACAO: " . $_SESSION['sLotacao'] . "\n";
        $mensagem .= "\t\tSQL:     " . $pesq . "\n";
        $mensagem .= "\t\tREQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
        $mensagem .= "\t\tPAGINA DE ORIGEM: " . pagina_de_origem() . "\n";

        error_log($mensagem, 1, "edinalvo.rosa@gmail.com");
        error_log($mensagem, 3, "___errors.log");

        if ($so_log == false)
        {
            $oForm = new formPadrao();
            $oForm->exibeTopoHTML();
            $oForm->exibeCorpoTopoHTML();

            mensagem("Exception:" . $exception->getMessage(), pagina_de_origem(), null, 'danger');

            $oForm->exibeCorpoBaseHTML();
            $oForm->exibeBaseHTML();
        }
    }

}


##
# CONSULTAS SQL
#


/**  @Class
 * +-------------------------------------------------------------+
 * | @class       : tbPrazo                                      |
 * | @description : verifica o prazo de liberacao do processo    |
 * |                                                             |
 * | @param  : void                                              |
 * | @return : void                                              |
 * | $method : tbPrazo           Construtor                      |
 * | $method : verifica()        Verifica se o per odo   v lido  |
 * | @usage  : $oPrazo = new tbPrazo;                            |
 * |           $oPrazo->verifica();                              |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase      (class_database.php)            |
 * |               mensagem()    (function.php)                  |
 * |               replaceLink() (function.php)                  |
 * +-------------------------------------------------------------+
 **/
class tbPrazo extends DataBase
{
    private $siape;

    function tbPrazo()
    {
        parent::DataBase('PDO');
    }
    function verifica()
    {
        $this->query( "SELECT * FROM tabprazos_prorroga WHERE siape = '".$this->siape."' AND data_limite >= NOW() " );
        $rowsPRORROGA = $this->num_rows();

        $this->query( "SELECT * FROM tabprazos WHERE NOW() >= inicio AND NOW() <= fim ORDER BY fim " );
        $rowsINSCRICAO = $this->num_rows();

        if (empty($rowsINSCRICAO) && empty($rowsPRORROGA))
        {
            mensagem( "Per odo expirado!", $this->getDestino() );
            die();
        }
    }
}
