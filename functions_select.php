<?php

include_once( "config.php" );
include_once( "Siape.php" );
include_once( "class_ocorrencias_grupos.php" );
include_once( "src/controllers/DadosServidoresController.php" );
include_once( "src/controllers/TabServativController.php" );
include_once( "src/controllers/TabUsuariosController.php" );
include_once( "src/controllers/TabBancoDeHorasCiclosController.php" );
include_once( "src/controllers/TabBancoDeHorasAcumulosController.php" );


/**  @package Functions
 * +-------------------------------------------------------------+
 * |                                                             |
 * | SISREF - Funções                                            |
 * |                                                             |
 * | @package    : functions                                     |
 * | @copyright  : (C) 2004-2012 INSS                            |
 * | @license    :                                               |
 * | @link       : http://www-inss                               |
 * | @subpackage :                                               |
 * | @author     :                                               |
 * |                                                             |
 * +-------------------------------------------------------------+
 * |   Convenções:                                               |
 * |      [] -> indicam parametros obrigatórios                  |
 * |      <> -> indicam parametros                               |
 * +-------------------------------------------------------------+
 * */

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : selectAutenticacao                           |
 * | @description : Realiza a seleção dos servidores p/ exibir   |
 * |                                                             |
 * | @param  : [<string>] - $arquivo                             |
 * |                        arquivo destino em caso de erro      |
 * | @return : <numero de linhas>  - Registros resultantes       |
 * | @usage  : selectAutenticacao('xxx.php');                    |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase  (class)                             |
 * +-------------------------------------------------------------+
 * */
function selectAutenticacao($arquivo = '')
{
    /* (Base: USUARIO)  */
    global $sMatricula, $sSenha, $defvis, $prazo, $troca_senha, $magico, $sSenha, $sPrivilegio, $sTripa;

    /* (Base: SERVATIV) */
    global $sNome, $sitcad, $entra, $sai, $iniin, $fimin, $aut, $bhoras, $horae, $motivo, $chefe, $jnd, $j, $jornadaMenor8horas;

    /* (Base: SETOR)    */
    global $sLotacao, $uorg, $upag, $ini, $fim, $codmun;

    $sMatricula = getNovaMatriculaBySiape($sMatricula);

    $sql = "
    SELECT
	cad.defvis, usu.prazo, usu.magico, cad.nome_serv AS nome, usu.senha,
        usu.privilegio, und.codigo AS setor, und.cod_uorg AS uorg, und.upag,
        usu.acesso, cad.cod_sitcad, cad.entra_trab, cad.ini_interv,
        cad.sai_interv, cad.sai_trab, cad.autchef, cad.bhoras, cad.horae,
        cad.motivo, cad.chefia, cad.jornada, und.inicio_atend, und.fim_atend,
        und.codmun,
        IF(SUBSTR(MD5(DATE_FORMAT(cad.dt_nasc,'%d%m%Y')),1,14) = usu.senha,1,0) AS troca_senha
    FROM
        usuarios AS usu
    LEFT JOIN
        servativ AS cad ON usu.siape = cad.mat_siape
    LEFT JOIN
        tabsetor AS und ON cad.cod_lot = und.codigo
    WHERE
        cad.excluido = 'N'
        AND usu.siape = :siape
        AND usu.senha = :senha
    ";

    $params = array(
        array( ":siape", $sMatricula, PDO::PARAM_STR ),
        array( ":senha", $sSenha, PDO::PARAM_STR ),
    );

    $oDBase = new DataBase('PDO');
    $oDBase->setDestino($arquivo);
    $oDBase->setMensagem("Problemas no acesso a Tabela USUARIOS (E000001.".__LINE__.").");
    $result = $oDBase->query($sql, $params);

    if ($result)
    {
        ##
        #  LE OS DADOS DO USUARIO
        #  BD USUARIOS, SERVATIV E SETOR
        ##
        // dados do servidor/estagi?rio
        //(Base: USUARIO)
        $numrows     = $oDBase->num_rows();
        $oUsuario    = $oDBase->fetch_object();
        $defvis      = $oUsuario->defvis;       // devidiente visual
        $prazo       = $oUsuario->prazo;        // se 1 solicita a troca da senha
        $troca_senha = $oUsuario->troca_senha;  // se 1 solicita a troca da senha (senha igual a data de nascimento)
        $magico      = $oUsuario->magico;       // usado para indicar a dispensa do registro de frequência
        $sSenha      = $oUsuario->senha;        // senha de acesso
        $sPrivilegio = $oUsuario->privilegio;   // uso futuro
        $sTripa      = $oUsuario->acesso;       // permissões de acesso

        //(Base: SERVATIV)
        $sNome  = $oUsuario->nome;       // nome do servidor
        $sitcad = $oUsuario->cod_sitcad; // situação cadastral
        $entra  = $oUsuario->entra_trab; // horário estabelecido de entrada ao serviço
        $sai    = $oUsuario->sai_trab;   // horário estabelecido de saída (fim do expediente)
        $iniin  = $oUsuario->ini_interv; // horário estabelecido do início do almoço
        $fimin  = $oUsuario->sai_interv; // horário estabelecido do t?rmino do almoço
        $aut    = $oUsuario->autchef;    // autorização da chefia para trabalho ap?s o horário da empresa
        $bhoras = $oUsuario->bhoras;     // autorização da chefia para registro de horas de compensa??o
        $horae  = $oUsuario->horae;      // registro de horário especial (deficiente, estudante, etc...)
        $motivo = $oUsuario->motivo;     // motivo do horário especial
        $chefe  = $oUsuario->chefia;     // indica se o servidor ocupa fun??o ou est? respondendo pela mesma (titular, substituto ou por delega??o)
        $jnd    = $oUsuario->jornada;    // jornada do servidor

        $j      = formata_jornada_para_hhmm($oUsuario->jornada);

        // - Indica quem possui jornada menor que 40 horas semanais,
        //   independente do turno estendido
        //
        $jornadaMenor8horas = ($jnd < 40);

        //(Base: SETOR)
        $sLotacao = $oUsuario->setor;        // unidade de lotação do servidor
        $uorg     = $oUsuario->uorg;         // unidade organizacional - SIAPE
        $upag     = $oUsuario->upag;         // unidade pagadora (única por gerência) - SIAPE
        $ini      = $oUsuario->inicio_atend; // horário de início do atendimento da unidade
        $fim      = $oUsuario->fim_atend;    // horário de término do atendimento da unidade
        $codmun   = $oUsuario->codmun;       // Código do município
    }
    return $numrows;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : selectTabelaValida                           |
 * | @description : Datas/prazos de execu??o de determinadas     |
 * |                tarefas mensais                              |
 * |                                                             |
 * | @param  : void                                              |
 * | @return : <numero de linhas>  - Registros resultantes       |
 * | @usage  : selectTabelaValida()                              |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase  (class)                             |
 * +-------------------------------------------------------------+
 * */
function selectTabelaValida()
{
    $sql = "
        SELECT
            tbval.id, tbval.compi, tbval.compf,
            DATE_FORMAT(tbval.gbnini, '%Y%m%d') AS gbnini,
            DATE_FORMAT(tbval.gbninf, '%Y%m%d') AS gbninf,
            tbval.hveraoi, tbval.hveraof,
            tbval.ativo, tbval.qcinzas
        FROM
            tabvalida AS tbval
        WHERE
            tbval.ativo = 'S'
    ";

    // instancia a class DataBase
    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela VALIDA (E000002.".__LINE__.").");
    $rx = $oDBase->query($sql);
    if ($rx) {
        $oValida = $oDBase->fetch_object();
        $sMesi   = $oValida->compi;
        $sMesf   = $oValida->compf;
        $sGbnini = $oValida->gbnini;
        $sGbninf = $oValida->gbninf;
        $iniver  = $oValida->hveraoi;
        $fimver  = $oValida->hveraof;
        $qcinzas = $oValida->qcinzas;
    }
    // fim do Tabvalida

    // valida
    $_SESSION['sMesi']   = $sMesi;
    $_SESSION['sMesf']   = $sMesf;
    $_SESSION['sGbnini'] = $sGbnini;
    $_SESSION['sGbninf'] = $sGbninf;
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : seleciona_servidores                         |
 * | @description : Realiza a 'seleção dos servidores p/ exibir  |
 * |                                                             |
 * | @param  : [<string>] - $link                                |
 * |                        id de link com a tabela              |
 * | @param  : [<string>] - $qlotacao                            |
 * |                        unidade de lotacao do servidor       |
 * | @param  : [<string>] - $freqh                               |
 * |                        indica se está homologado            |
 * | @return : <id conn>  - O resultado da 'seleção              |
 * | @usage  : seleciona_servidores($link,'01700000','N');       |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DadosServidoresController  (class)            |
 * +-------------------------------------------------------------+
 * */
function seleciona_servidores($link, $setor, $freqh = 'N', $compet = '', $homologacao=null)
{
    $data = substr($compet,0,4) . '-' . substr($compet,4,2) . '-01';

    $oDados = new DadosServidoresController();
    $oDBase = $oDados->selecionaServidoresUnidade($link, $setor, $data, $homologacao);

    return $oDBase;
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : seleciona_servidoresac                       |
 * | @description : Realiza a 'seleção dos servidores p/ exibir  |
 * |                                                             |
 * | @param  : [<string>] - $link                                |
 * |                        id de link com a tabela              |
 * | @param  : [<string>] - $qlotacao                            |
 * |                        unidade de lotacao do servidor       |
 * | @return : <id conn>  - O resultado da 'seleção              |
 * | @usage  : seleciona_servidoresac($link,'01700000','N');     |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase  (class)                             |
 * +-------------------------------------------------------------+
 * */
function seleciona_servidoresac($link, $qlotacao)
{
    $sMatricula = $_SESSION["sMatricula"];
    $lot        = $_SESSION["sLotacao"];

    // instancia a class DataBase
    $oDBase = new DataBase('PDO');

    //obtem dados dos substitutos em efetiva substituição
    $mats_subst = "''";
    $diad       = date("Y-m-d");

    $oDBase->setMensagem("Problemas no acesso a Tabela SUBSTITUIÇÃO (E000003.".__LINE__.").");
    $oDBase->query("SELECT siape FROM substituicao WHERE upai = '$qlotacao' and fim > '$diad' and situacao = 'A' ");

    if ($oDBase->num_rows() > 0) {
        while ($pms = $oDBase->fetch_object()) {
            $mats_subst .= ($mats_subst == "" ? "" : ",") . "'" . $pms->siape . "'";
        }
    }

    $oDBase->setMensagem("Problemas no acesso a Tabela CADASTRO (E0000096.".__LINE__.").");
    $oDBase->query("
        SELECT
            a.mat_siape, a.nome_serv, a.cod_lot, a.excluido, a.chefia,
            a.entra_trab, a.sai_trab, a.area, a.cod_sitcad, a.jornada,
            b.uorg_pai X
        FROM
            servativ AS a
        INNER JOIN tabsetor AS b ON a.cod_lot = b.codigo
        WHERE a.mat_siape !='$sMatricula'
            and a.excluido='N'
            and a.cod_sitcad NOT IN ('02','08','15','18')
            and ((a.chefia = 'N' and a.cod_lot like '$qlotacao')
                    or (a.chefia = 'S' and b.uorg_pai like '$qlotacao')
                    or (a.mat_siape IN ($mats_subst)))
        ORDER BY
            a.cod_sitcad, a.entra_trab
    ");

    return $oDBase;
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : selecionaServidor                            |
 * | @description : Realiza a seleção do servidor                |
 * |                                                             |
 * | @param  : [<string>] - $siape                               |
 * |                        matr?cula siape do servidor          |
 * | @return : <id conn>  - O resultado da seleção               |
 * | @usage  : selecionaServidor('9999999');                     |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase  (class)                             |
 * +-------------------------------------------------------------+
 * */
function selecionaServidor($siape)
{
    $siape = getNovaMatriculaBySiape($siape);

    // instancia a class DataBase
    $oDBase = new DataBase('PDO');
    $oDBase->setMensagem("Problemas no acesso a Tabela CADASTRO (E000004.".__LINE__.").");
    $oDBase->query('
    SELECT
        servativ.mat_siape,
        servativ.nome_serv,
        servativ.cpf,
        servativ.cod_lot,
        servativ.cod_loc,
        servativ.cod_sitcad,
        servativ.sigregjur,
        DATE_FORMAT(servativ.dt_ing_lot,"%d/%m/%Y") AS dt_ing_lot,
        DATE_FORMAT(servativ.dt_ing_loc,"%d/%m/%Y") AS dt_ing_loc,
        servativ.area,
        tabsetor.upag,
        tabsetor.descricao,
        servativ.excluido,
        servativ.chefia
    FROM
        servativ
    LEFT JOIN
        tabsetor ON servativ.cod_lot = tabsetor.codigo
    WHERE
        servativ.mat_siape = :siape
        AND servativ.cod_sitcad NOT IN ("02","15")
    ',
        array(
            array(':siape', $siape, PDO::PARAM_STR),
        ));

    return $oDBase;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : selecionaPontoServidor                       |
 * | @description : Realiza a seleção do servidor                |
 * |                                                             |
 * | @param  : [<string>] - $siape                               |
 * |                        matrácula siape do servidor          |
 * | @return : <id conn>  - O resultado da seleção               |
 * | @usage  : selecionaServidor('9999999');                     |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase  (class)                             |
 * +-------------------------------------------------------------+
 * */
function selecionaPontoServidor($siape,$dia,$tabela='')
{
  $siape = getNovaMatriculaBySiape($siape);

  if (empty($tabela))
  {
    $tabela = 'ponto' . dataMes($dia) . dataAno($dia);
  }

  // instancia a class DataBase
  $oDBase = new DataBase('PDO');
  $oDBase->setMensagem("Problemas no acesso a Tabela ".strtoupper($tabela)." (matrícula ".removeOrgaoMatricula($siape).") (E000005.".__LINE__.").");
  $oDBase->query("
  SELECT
    pto.dia,
    pto.siape,
    pto.entra,
    pto.intini,
    pto.intsai,
    pto.sai,
    pto.jornd,
    pto.jornp,
    pto.jorndif,
    pto.oco,
    pto.just,
    pto.seq,
    pto.idreg,
    pto.ip,
    pto.ip2,
    pto.ip3,
    pto.ip4,
    pto.justchef,
    pto.ipch,
    pto.iprh,
    pto.matchef,
    pto.siaperh
  FROM
    $tabela AS pto
  WHERE
      pto.siape = :siape
      AND pto.dia = :dia
  ",
  array(
    array(':siape', $siape,          PDO::PARAM_STR),
    array(':dia',   conv_data($dia), PDO::PARAM_STR)
  ));

  return $oDBase;
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : seleciona_servidores_ponto                   |
 * | @description : Realiza a 'seleção dos servidores            |
 * |                para exibir com os horários, se registrados  |
 * |                                                             |
 * | @param  : [<string>] - $link                                |
 * |                        id de link com a tabela              |
 * | @param  : [<string>] - $qlotacao                            |
 * |                        unidade de lotacao desejada          |
 * | @param  : [<string>] - $diad                                |
 * |                        dia desejado                         |
 * | @return : <id conn>  - O resultado da 'seleção              |
 * | @usage  : seleciona_servidoresac($link,'01700000','N');     |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase  (class)                             |
 * +-------------------------------------------------------------+
 * */
function seleciona_servidores_ponto($link, $setor='', $data='')
{
    $oDados = new DadosServidoresController();
    $oDBase = $oDados->selecionaServidoresUnidade($link, $setor, $data);

    return $oDBase;
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : select_dadosgex                              |
 * | @description : Código e nome da gerência                    |
 * |                                                             |
 * | @param  : [<string>] - $sLotacao                            |
 * |                        codigo da unidade                    |
 * | @param  : [<string>] - $codgex                              |
 * |                        codigo da gerência                   |
 * | @param  : [<string>] - $nomegex                             |
 * |                        nome da gerência                     |
 * | @param  : <string>   - $ufgex                               |
 * |                        estado da gerência                   |
 * | @param  : <string>   - $idger                               |
 * |                        identificador superintendência       |
 * | @return : <id conn>  - dados selecionados (id de conex?o)   |
 * | @usage  : $idResult = select_dadosgex(                      |
 * |                         '04001000', $codgex, $nomegex,      |
 * |                         $ufgex, $idger                      |
 * |                      );                                     |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase  (class)                             |
 * +-------------------------------------------------------------+
 * */
function select_dadosgex($sLotacao, $codgex = '', $nomegex = '', $ufgex = '', &$idger = '')
{
    global $codgex, $nomegex, $ufgex, $idger;

    // código da gex e da ger
    $cod_gex  = substr($sLotacao, 0, 2) . '0' . substr($sLotacao, 3, 2);
    $cod_ger  = substr($sLotacao, 0, 5);
    $grupoUnd = substr($sLotacao, 2, 3);

    // instancia a class DataBase
    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela GEX/GER (E000006.".__LINE__.").");
    $oDBase->query("
        SELECT
            gex.cod_gex, UPPER(gex.nome_gex) AS nome_gex, gex.uf, gex.regional
        FROM
            dados_gex AS gex
        LEFT JOIN
            dados_ger AS ger ON gex.regional = ger.id_ger
        WHERE
            gex.cod_gex = :cod_gex" . ($grupoUnd == '150' || $grupoUnd == '001' ? "" : " OR ger.cod_ger = :cod_ger") . "
    ", array(
        array( ":cod_gex", $cod_gex, PDO::PARAM_STR ),
        array( ":cod_ger", $cod_ger, PDO::PARAM_STR ),
    ));

    $num_rows = $oDBase->num_rows();

    if ($num_rows > 0) {
        $oGex = $oDBase->fetch_object();
        $codgex = $oGex->cod_gex;
        $nomegex = $oGex->nome_gex;
        $ufgex = $oGex->uf;
        $idger = $oGex->regional;
    }

    return $oDBase;
}

/**  @Function
 * +-----------------------------------------------------------------+
 * | @function    : seleciona_dados_da_gerencia                      |
 * | @description : Código e nome da gerência                        |
 * |                                                                 |
 * | @param  : [<string>] - $sLotacao                                |
 * |                        codigo da unidade                        |
 * | @return : <object> - dados selecionados                         |
 * | @usage  : $objUnidade = seleciona_dados_da_gerencia('04001000');|
 * | @author : Edinalvo Rosa                                         |
 * |                                                                 |
 * | @dependence : DataBase  (class)                                 |
 * +-----------------------------------------------------------------+
 * */
function seleciona_dados_da_gerencia($lotacao = '')
{
    // instancia a class DataBase
    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela UNIDADES (E000007.".__LINE__.").");
    $oDBase->query("
        SELECT
            und.codigo, und.descricao, und.cod_uorg, und.uorg_pai, und.upag,
            und.ug, und.inicio_atend, und.fim_atend, und.sigla, und.regional,
            und.uf_lota AS uf, ger.nome_ger, ger.nome_ger_resumido,
            ger.upag AS upag_sr
        FROM
            tabsetor AS und
        LEFT JOIN
            tabsetor_ger AS ger ON und.regional = ger.id_ger
        WHERE
            IF(SUBSTR(:setor,3,3) <> '150',
                (und.codigo LIKE CONCAT(SUBSTR(:setor,1,2),'_',SUBSTR(:setor,4,2),'%')),
                    (und.codigo LIKE CONCAT(SUBSTR(:setor,1,5),'%')))
        LIMIT
            1;
	", array(
            array( ":setor", $lotacao, PDO::PARAM_STR ),
        ));

    $oSetor = $oDBase->fetch_object();

    // descricao da unidade grupo master
    switch (substr($lotacao, 0, 5)) {
        case '01000':
        case '01001':
            $unidade_master = 'PRESIDENCIA';
            break;
        case '01100':
            $unidade_master = 'AUDITORIA-GERAL';
            break;
        case '01200':
            $unidade_master = 'PROCURADORIA FEDERAL ESPECIALIZADA';
            break;
        case '01300':
            $unidade_master = 'DIRETORIA DE ORCAMENTO, FINANCAS E LOGISTICA';
            break;
        case '01400':
            $unidade_master = 'DIRETORIA DE SAUDE DO TRABALHADOR';
            break;
        case '01500':
            $unidade_master = 'DIRETORIA DE BENEFICIOS';
            break;
        case '01700':
            $unidade_master = 'DIRETORIA DE GESTAO DE PESSOAS';
            break;
        default:
            if (substr($lotacao, -3) == '150') {
                $unidade_master = "SUPERINTENDENCIA REGIONAL ";
            } elseif ($lotacao == '') {
                $unidade_master = '';
            } else {
                $unidade_master = "GERENCIA-EXECUTIVA ";
            }
            $unidade_master .= $oSetor->nome_ger;
            break;
    }

    $oSetor->unidade_master = $unidade_master;

    return $oSetor;

}

/**  @Function
 * +----------------------------------------------------------------+
 * | @function    : seleciona_dados_da_unidade                      |
 * | @description : Código e nome da gerência                       |
 * |                                                                |
 * | @param  : [<string>] - $sLotacao                               |
 * |                        codigo da unidade                       |
 * | @return : <object> - dados selecionados                        |
 * | @usage  : $objUnidade = seleciona_dados_da_unidade('04001000');|
 * | @author : Edinalvo Rosa                                        |
 * |                                                                |
 * | @dependence : DataBase  (class)                                |
 * +----------------------------------------------------------------+
 * */
function seleciona_dados_da_unidade($lotacao = '')
{
    // instancia a class DataBase
    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela UNIDADES (E000008.".__LINE__.").");
    $oDBase->query("
        SELECT
            und.descricao, und.upag, und.cod_uorg, und.uorg_pai, und.ug,
            und.inicio_atend, und.fim_atend, und.sigla, und.regional,
            und.uf_lota AS uf, upg.gerencia, ger.nome_ger, ger.nome_ger_resumido,
            ger.upag AS upag_sr, und_pai.descricao AS unidade_master,
            taborgao.denominacao, taborgao.sigla
        FROM
            tabsetor AS und
        LEFT JOIN
            tabsetor AS und_pai ON und_pai.codigo = und.uorg_pai
        LEFT JOIN
            taborgao ON LEFT(und.codigo,5) = taborgao.codigo
        LEFT JOIN
            upag AS upg ON und.upag = upg.upag_cod
        LEFT JOIN
            tabsetor_ger AS ger ON und.regional = ger.id_ger
        WHERE
            und.codigo = :setor
	", array(
            array( ":setor", $lotacao, PDO::PARAM_STR )
        ));

    $oSetor = $oDBase->fetch_object();

    return $oSetor;
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : gravar_historico_ponto                       |
 * | @description : Registra em histórico os dados do ponto      |
 * |                antes da alteração                           |
 * |                                                             |
 * | @param  : <string>  - $mat                                  |
 * |                       matricula siape                       |
 * |           <string>  - $diac                                 |
 * |                       data invertida (aaaa-mm-dd)           |
 * | @return : void                                              |
 * | @usage  : gravar_historico_ponto(                           |
 * |             '9999999',                                      |
 * |             '2011-11-10'                                    |
 * |           );                                                |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * +-------------------------------------------------------------+
 * */
function gravar_historico_ponto($mat = '', $diac = '', $oper = 'A')
{
    if ($mat == '' || $diac == '') {
        mensagem("Falha no registro do histórico de frequência!", null, 1);
    }
    else
    {
        sleep(1);

        $mat = getNovaMatriculaBySiape($mat);

        // dados do usuario cadastrador
        $sMatricula = $_SESSION["sMatricula"];

        //linha que captura o ip do usuario.
        $ip = getIpReal();

        // competencia referente a data indicada
        $comp = dataMes($diac) . dataAno($diac);

        // instancia o banco de dados
        $oDBase = new DataBase('PDO');

        // grava os dados anteriores no historico do ponto
        $oDBase->setMensagem("Falha no registro do HISTÓRICO PONTO".$comp." (E000009.".__LINE__.").");
        $oDBase->query("
        INSERT INTO histponto$comp
        SELECT
            dia, siape, entra, intini, intsai, sai, jornd, jornp, jorndif,
            oco, idreg, ip, ip2, ip3, ip4, IFNULL(ipch,'') AS ipch,
            IFNULL(iprh,'') AS iprh, matchef, siaperh, DATE_FORMAT(NOW(),'%Y-%m-%d'),
            DATE_FORMAT(NOW(),'%H:%i:%s'), :usuario, :ip, :oper,
            just, justchef
        FROM
            ponto$comp
        WHERE
            dia = :dia
            AND siape = :siape
        ",
            array(
                array(':siape',   $mat,        PDO::PARAM_STR),
                array(':dia',     $diac,       PDO::PARAM_STR),
                array(':usuario', $sMatricula, PDO::PARAM_STR),
                array(':ip',      $ip,         PDO::PARAM_STR),
                array(':oper',    $oper,       PDO::PARAM_STR),
        ));
    }
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : gravar_historico_servativ                    |
 * | @description : Registra em histórico os dados do ponto      |
 * |                antes da alteração                           |
 * |                                                             |
 * | @param  : <string>  - $mat                                  |
 * |                       matricula siape                       |
 * |           <string>  - $diac                                 |
 * |                       data invertida (aaaa-mm-dd)           |
 * | @return : void                                              |
 * | @usage  : gravar_historico_servativ(                        |
 * |             '9999999',                                      |
 * |             '2011-11-10'                                    |
 * |           );                                                |
 * |                                                             |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * +-------------------------------------------------------------+
 * */
function gravar_historico_servativ($mat = '', $oper = 'A')
{
    if ($mat == '') {
        mensagem("Falha no registro do histórico do cadastro!", null, 1);
    } else {
        $mat = getNovaMatriculaBySiape($mat);

        // dados do usuario
        $sMatricula = $_SESSION["sMatricula"];
        $ip = getIpReal(); //linha que captura o ip do usuario.

        // instancia o banco de dados
        $oDBase = new DataBase('PDO');

        //grava os dados anteriores
        $oDBase->setMensagem("Falha no registro do HISTÓRICO CADASTRO (E000010.".__LINE__.").");
        $oDBase->query("
            INSERT INTO
                histcad
            SELECT
                mat_siape, defvis, cpf, jornada, dt_ing_jorn, entra_trab,
                ini_interv, sai_interv, sai_trab, horae, processo, motivo,
                dthe, dthefim, autchef, bhoras, bh_tipo, NOW(), NOW(),
                :usuario, :ip
            FROM
                servativ
            WHERE
                mat_siape = :siape
        ", array(
            array( ":siape",   $mat, PDO::PARAM_STR ),
            array( ":ip",      $ip, PDO::PARAM_STR ),
            array( ":usuario", $sMatricula, PDO::PARAM_STR ),
        ));
    }
}

/**
 * @param $siape
 * @param $daycurrent
 * @param $table
 * @return int|null
 */
function getOvertimeServidor($siape, $daycurrent, $table)
{
    $siape = getNovaMatriculaBySiape($siape);

    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela ".strtoupper($table)." (E000011.".__LINE__.").");
    $oDBase->query("SELECT * FROM $table WHERE siape = :siape AND dia = :daycurrent", array(
            array(":daycurrent", $daycurrent, PDO::PARAM_STR),
            array(":siape", $siape, PDO::PARAM_STR))
    );

    $result = $oDBase->fetch_assoc();

    $journey_default = time_to_sec(left($result['jornp'], 5));
    $journey_current = time_to_sec(left($result['jornd'], 5));
    $journey_dif     = time_to_sec(left($result['jorndif'], 5));

    if (key_exists('horas_trabalhadas_diferenca_apurada',$_SESSION) && time_to_sec($_SESSION['horas_trabalhadas_diferenca_apurada']) > 0)
    {
        $journey_dif = time_to_sec(left($_SESSION['horas_trabalhadas_diferenca_apurada'], 5));
    }

    //return $journey_current - $journey_default;
    return $journey_dif;

}

/**
 * @param $siape
 * @param $hours
 * @return bool|int|null|PDOStatement|resource
 */
function saveOvertimeInDatabase($siape, $hours, $ciclo_id = null)
{
    $siape = getNovaMatriculaBySiape($siape);

    $oDBase = new DataBase('PDO');

    // PROCURA NA TABELA DE ACUMULOS SE EXISTE ALGUM REGISTRO COM O SIAPE PASSADO COMO PARÂMETRO
    $oDBase->setMensagem("Problemas no acesso a Tabela ACUMULO DE HORAS (E000097.".__LINE__.").");
    $result = $oDBase->query("SELECT * FROM acumulos_horas WHERE siape = :siape AND ciclo_id = :ciclo_id", array(
        array(":siape", $siape, PDO::PARAM_STR),
        array(":ciclo_id", $ciclo_id, PDO::PARAM_INT)
    ));


    // INCREMENTA O VALOR DE HORAS COM O QUE ESTA SENDO PASSADO NO MOMENTO
    $content = $result->fetch(PDO::FETCH_ASSOC);
    $hours = $content['horas'] + $hours;

    /* CASO JÁ EXISTA UM VALOR NA TABELA PARA O SIAPE PASSADO, ELE ATUALIZA O REGISTRO COM O NOVO VALOR DE HORAS
       CASO CONTRATIO ELE CRIA UM NOVO REGISTRO */

    if (empty($content))
    {
        $oDBase->setMensagem("Problemas no acesso a Tabela ACUMULO DE HORAS (E000098.".__LINE__.").");
        $result = $oDBase->query("INSERT INTO acumulos_horas (siape, horas, ciclo_id) VALUES (:siape,:horas,:ciclo_id)", array(
            array(":siape", $siape, PDO::PARAM_STR),
            array(":ciclo_id", $ciclo_id, PDO::PARAM_INT),
            array(":horas", $hours, PDO::PARAM_STR)
        ));
    } else {

        //$hours = floor($hours / 3600);
        //$minutes = floor(($hours / 60) % 60);

        $oDBase->setMensagem("Problemas no acesso a Tabela ACUMULO DE HORAS (E000099.".__LINE__.").");
        $result = $oDBase->query("UPDATE acumulos_horas SET horas = :horas WHERE siape = :siape AND ciclo_id = :ciclo_id", array(
            array(":siape", $siape, PDO::PARAM_STR),
            array(":ciclo_id", $ciclo_id, PDO::PARAM_INT),
            array(":horas", $hours, PDO::PARAM_STR)
        ));
    }

    return $result;
}

/**
 * @param $siape
 * @return bool|int|null|PDOStatement|resource
 */
function saveHistoricalOvertime($siape, $ciclo_id, $acumulo = 0, $usufruto = 0, $dia = null)
{
    $siape = getNovaMatriculaBySiape($siape);
    $dia   = (is_null($dia) ? date("Y-m-d") : conv_data($dia));

    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela ACUMULO DE HORAS/HISTÓRICO (E000100.".__LINE__.").");
    $oDBase->query("SELECT id FROM historico_movimentacoes_acumulos WHERE siape = :siape AND data_movimentacao = :date_value AND ciclo_id = :ciclo_id", array(
        array(":siape",      $siape,    PDO::PARAM_STR),
        array(":ciclo_id",   $ciclo_id, PDO::PARAM_INT),
        array(":date_value", $dia,      PDO::PARAM_STR),
    ));

    if ($oDBase->num_rows() > 0)
    {
        $oDBase->setMensagem("Problemas no acesso a Tabela ACUMULO DE HORAS/HISTÓRICO (E000101.".__LINE__.").");
        $result = $oDBase->query("UPDATE historico_movimentacoes_acumulos SET usufruto = (usufruto + :usufruto), acumulo = (acumulo + :acumulo) WHERE siape = :siape AND data_movimentacao = :date_value AND ciclo_id = :ciclo_id", array(
            array(":siape",      $siape,    PDO::PARAM_STR),
            array(":ciclo_id",   $ciclo_id, PDO::PARAM_INT),
            array(":usufruto",   $usufruto, PDO::PARAM_INT),
            array(":acumulo",    $acumulo,  PDO::PARAM_INT),
            array(":date_value", $dia,      PDO::PARAM_STR)
        ));
    }
    else
    {
        $oDBase->setMensagem("Problemas no acesso a Tabela ACUMULO DE HORAS/HISTÓRICO (E000102.".__LINE__.").");
        $result = $oDBase->query("INSERT INTO historico_movimentacoes_acumulos (data_movimentacao, siape , acumulo , usufruto, ciclo_id) VALUES (:date_value , :siape , :acumulo , :usufruto, :ciclo_id)", array(
            array(":siape",      $siape,    PDO::PARAM_STR),
            array(":ciclo_id",   $ciclo_id, PDO::PARAM_INT),
            array(":usufruto",   $usufruto, PDO::PARAM_INT),
            array(":acumulo",    $acumulo,  PDO::PARAM_INT),
            array(":date_value", $dia,      PDO::PARAM_STR)
        ));
    }

    return $result;
}

/**
 * @param void
 * @return bool|int|null|PDOStatement|resource
 */
function servidorHasAuthorization()
{
    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela CICLOS (E000012.".__LINE__.").");
    $result = $oDBase->query("SELECT * FROM ciclos
                                     JOIN autorizacoes_servidores ON ciclos.id = autorizacoes_servidores.ciclo_id
                                      WHERE autorizacoes_servidores.data_inicio <= CURDATE()
                                        AND autorizacoes_servidores.data_fim >= CURDATE()
                                        AND ciclos.orgao = :orgao
                                        AND autorizacoes_servidores.siape = :matricula", array(
        array(":orgao", substr($_SESSION['sLotacao'], 0, 5), PDO::PARAM_STR),
        array(":matricula", $_SESSION['sMatricula'], PDO::PARAM_STR)));

    return $result->fetchColumn();
}

/**
 * @return DataBase
 */
function seleciona_ciclos()
{
    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela CICLOS (E000013.".__LINE__.").");
    $oDBase->query("SELECT * FROM ciclos WHERE SUBSTR(ciclos.orgao,1,5) = :orgao ORDER BY ciclos.id DESC",
        array(
            array(":orgao", substr($_SESSION['sLotacao'], 0, 5), PDO::PARAM_STR)
        )
    );

    return $oDBase;
}


/**
 * @param $post
 */
function gravar_ciclo($post)
{
    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela CICLOS (E000015.".__LINE__.").");
    $result = $oDBase->query("
    INSERT INTO ciclos (data_inicio, data_fim, orgao)
        VALUES (:data_inicio, :data_fim, :orgao)
    ",
    array(
        array(":data_inicio", conv_data($post['data_inicio']), PDO::PARAM_STR),
        array(":data_fim", conv_data($post['data_final']), PDO::PARAM_STR),
        array(":orgao", getOrgaoByUorg($post['lota']), PDO::PARAM_STR),
    ));

    return $result;
}

/**
 * @param $post
 */
function update_ciclo($post)
{
    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela CICLOS (E000016.".__LINE__.").");
    $result = $oDBase->query("
    UPDATE ciclos
        SET data_fim = :data_fim
            WHERE id = :id
    ",
    array(
        array(":data_fim", conv_data($post['data_final']), PDO::PARAM_STR),
        array(":id", $post['id'], PDO::PARAM_INT),
    ));

    return $result;
}

/**
 * @return DataBase
 */
function seleciona_servidores_list($autorizacoes_required = false)
{
    $oDados = new DadosServidoresController();
    return $oDados->selecionaServidoresUnidadeBancoHoras($autorizacoes_required);
}

/**
 * Adiciona o ip informado como disponivel dentro do setor
 */


function verificaPermissoesAcumulo($siape, $horae, $motivo, $limite, $permite, $excecao, $plantao)
{
    // O primeiro parametro esta aqui s? para auxiliar no debu quando necess?rio xD

    $info = ['blocked' => false, 'titulo' => 'Permitido'];

    // Validação horário especial
    if($horae == 'S')
    {
        switch ($motivo)
        {
            case 'O':
                $info = ['titulo' => 'Motivo: O (Opção 30 Horas)', 'blocked' => true];
                break;

            case 'E':
                $info = ['titulo' => 'Motivo: E (Estudante)', 'blocked' => true];
                break;
        }
    }

    // Validação de limite de 60 horas
    if (!$info['blocked']) {

        if ($limite == "SIM") {
            $info = ['titulo' => 'Motivo: O servidor possui limite de 60 horas para acumular', 'blocked' => true];
        }
    }

    // Valida permissão de acumulo através do cargo
    if (!$info['blocked']) {

        if ($permite == "NAO") {
            $info = ['titulo' => 'Motivo: O cargo do servidor indicado não permite acumular horas', 'blocked' => true];
        }
    }

    if ($excecao == "SIM") {
        $info = ['blocked' => false, 'titulo' => 'Permitido'];
    }


    return $info;
}


function adicionarip($dados)
{
    $query = "INSERT INTO ips_setor (setor , endereco )
                               VALUES (:setor , :endereco)";

    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela IPS UNIDADES (E000017.".__LINE__.").");
    $oDBase->query($query,
        array(array(":setor", $dados['hdnsetor'], PDO::PARAM_STR),
            array(":endereco", $dados['ip'], PDO::PARAM_STR))
    );

    return $oDBase;
}


function listSetoresCombo()
{
    $query = "SELECT DISTINCT tabsetor.codigo AS setor , CONCAT(tabsetor.codigo,' - ',tabsetor.descricao) AS descricao FROM tabsetor
                WHERE tabsetor.upag = :upag
                 ORDER BY tabsetor.descricao ASC";

    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela UNIDADES (E000018.".__LINE__.").");
    $oDBase->query($query,
        array(array(":upag", $_SESSION['upag'], PDO::PARAM_STR))
    );

    return $oDBase;
}


/**
 * @return DataBase
 */
function seleciona_servidores_usufruto_list($ciclo_id)
{
    $query = "SELECT servativ.mat_siape AS matricula ,
                     servativ.nome_serv  AS nome ,
                     usuarios.siape AS siape,
                     autorizacoes_servidores_usufruto.data_inicio AS data_inicial,
                     autorizacoes_servidores_usufruto.data_fim AS data_final,
                     CONCAT(DATE_FORMAT(autorizacoes_servidores_usufruto.data_inicio ,'%d/%c/%Y'),' - ',DATE_FORMAT(autorizacoes_servidores_usufruto.data_fim ,'%d/%c/%Y')) AS periodo,
                     UPPER(autorizacoes_servidores_usufruto.tipo_autorizacao) AS modalidade
			            FROM usuarios
			                   	INNER JOIN servativ on usuarios.siape = servativ.mat_siape
								INNER JOIN autorizacoes_servidores_usufruto ON autorizacoes_servidores_usufruto.siape = usuarios.siape
                                INNER JOIN ciclos ON ciclos.id = autorizacoes_servidores_usufruto.ciclo_id
                                    WHERE usuarios.setor = :lotacao AND
                                          ciclos.id = :ciclo_id";

    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela USUÁRIOS (E000019.".__LINE__.").");
    $oDBase->query($query,
        array(array(":lotacao", $_SESSION['sLotacao'], PDO::PARAM_STR),
            array(":ciclo_id", $ciclo_id, PDO::PARAM_STR))
    );

    return $oDBase;
}


function seleciona_ips_list($setor)
{
    $query = "SELECT * FROM ips_setor WHERE ips_setor.setor = :setor";

    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela IPS UNIDADES (E000020.".__LINE__.").");
    $oDBase->query($query,
        array(array(":setor", $setor, PDO::PARAM_STR))
    );

    return $oDBase;
}

/**
 * @return DataBase
 */
function seleciona_ips_servidor_list()
{
    $oDados = new DadosServidoresController();
    $oDBase = $oDados->selecionaServidoresLiberacaoIPS();

    return $oDBase;
}

/**
 * @param $id
 * @return bool|string
 */
function getIpsBySiape($id)
{
    $query = "SELECT ips_autorizacao.endereco FROM ips_autorizacao
                        INNER JOIN servidores_autorizacao ON ips_autorizacao.servidor_autorizacao_id = servidores_autorizacao.id
                           WHERE servidores_autorizacao.id = :id";

    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela IPS AUTORIZADOS (E000021.".__LINE__.").");
    $oDBase->query($query,
        array(array(":id", $id, PDO::PARAM_STR))
    );

    $resul = "";

    while ($ip = $oDBase->fetch_object()) {
        $resul .= " " . $ip->endereco . " /<br>";
    }

    return substr($resul, 0, -5);

}

/**
 * @return DataBase
 * @info Busca os registros para montar uma box de ciclos
 */
function boxCiclos()
{
    $orgao = getOrgaoByUorg();

    $queryBox = "
        SELECT
            ciclos.id AS id,
            CONCAT(
                DATE_FORMAT(ciclos.data_inicio ,'%d/%m/%Y' ),
                \" - \",
                DATE_FORMAT(ciclos.data_fim ,'%d/%m/%Y' )) AS ciclo,
            DATE_FORMAT(ciclos.data_inicio, '%d/%m/%Y' )   AS data_inicio,
            DATE_FORMAT(ciclos.data_fim, '%d/%m/%Y' )      AS data_fim,
            IF(NOW() BETWEEN ciclos.data_inicio AND ciclos.data_fim,DATE_FORMAT(ciclos.data_inicio, '%d/%m/%Y'),'') AS periodo_select
        FROM
            ciclos
        WHERE
            ciclos.orgao = :orgao
        ORDER BY
            ciclos.data_inicio DESC, ciclos.id DESC
    ";

    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela CICLOS (E000022.".__LINE__.").");
    $oDBase->query($queryBox, array(
        array(":orgao", $orgao, PDO::PARAM_STR)
    ));

    return $oDBase;
}

/**
 * @return DataBase
 * @info Busca os registros para montar uma box de ciclos
 */
function getCiclo($id)
{
    $queryBox = "SELECT
                  DATE_FORMAT(ciclos.data_inicio ,'%d/%m/%Y' ) AS data_inicio,
                  DATE_FORMAT(ciclos.data_fim ,'%d/%m/%Y' )    AS data_fim
                  FROM ciclos
	                 WHERE ciclos.id = :id";

    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela CICLOS (E000023.".__LINE__.").");
    $oDBase->query($queryBox, array(
        array(":id", $id, PDO::PARAM_INT)
    ));

    return $oDBase;
}

/**
 * @param $id_ciclo
 * @param $siape
 * @return array
 * @info verifica se o servidor possui autorização dentro de um ciclo
 */
function checkServidor($ciclo_id, $siape)
{
    $siape = getNovaMatriculaBySiape($siape);

    $queryAcumulo = "
    SELECT
        IF( autorizacoes_servidores.ciclo_id is not NULL,
            CONCAT(
                DATE_FORMAT(autorizacoes_servidores.data_inicio ,'%d/%m/%Y'),
                ' - ',
                DATE_FORMAT(autorizacoes_servidores.data_fim ,'%d/%m/%Y')),'Nenhum Período Autorizado') AS periodo
            FROM autorizacoes_servidores
                WHERE autorizacoes_servidores.siape = :siape AND
                      autorizacoes_servidores.ciclo_id = :ciclo_id";

    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela AUTORIZACOES SERVIDORES (E000024.".__LINE__.").");
    $oDBase->query($queryAcumulo, array(
        array(":siape", $siape, PDO::PARAM_STR),
        array(":ciclo_id", $ciclo_id, PDO::PARAM_INT)
    ));

    return $oDBase->fetch_assoc();

}

/**
 * @param $id_ciclo
 * @return bool
 * @info Verifica se o ciclo pesquisado esta dentro do período atual
 */
function checkCicloCurrent($ciclo_id)
{

    $query = "SELECT * FROM ciclos
                WHERE CURDATE() <= ciclos.data_fim
                AND ciclos.id = :ciclo_id";

    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela CICLOS (E000025.".__LINE__.").");
    $oDBase->query($query, array(
        array(":ciclo_id", $ciclo_id, PDO::PARAM_INT)
    ));

    return ($oDBase->num_rows() > 0);
}

/**
 * @param $siape
 * @return object
 * @info Pega nome do servidor através da sua matricula SIAPE
 */
function getNomeServidor($siape, $com_siape=true)
{
    $siape     = getNovaMatriculaBySiape($siape);
    $matricula = removeOrgaoMatricula($siape);

    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela CADASTRO (E000026.".__LINE__.").");
    $oDBase->query("
        SELECT
            IF(servativ.flag_nome_social=0,
                servativ.nome_serv,
                IF(servativ.nome_social='',
                    servativ.nome_social, servativ.nome_serv)) AS nome
        FROM servativ
            WHERE servativ.mat_siape = :siape
    ",
    array(
        array(":siape", $siape, PDO::PARAM_STR)
    ));

    if ($com_siape == true)
    {
        $resultado = $matricula . " - " . $oDBase->fetch_object()->nome;
    }
    else
    {
        $resultado = $oDBase->fetch_object()->nome;
    }

    return $resultado;
}

/**
 * @param $siape
 * @return object
 * @info Pega nome do servidor através da sua matricula SIAPE
 */
function getNomeServidorCadastro($siape, $com_siape=false)
{
    $siape     = getNovaMatriculaBySiape($siape);
    $matricula = removeOrgaoMatricula($siape);

    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela CADASTRO (E000026.".__LINE__.").");
    $oDBase->query("
        SELECT servativ.nome_serv AS nome
            FROM servativ
                WHERE servativ.mat_siape = :siape
    ",
    array(
        array(":siape", $siape, PDO::PARAM_STR)
    ));

    if ($com_siape == true)
    {
        $resultado = $matricula . " - " . $oDBase->fetch_object()->nome;
    }
    else
    {
        $resultado = $oDBase->fetch_object()->nome;
    }

    return $resultado;
}


/**
 * @param $siape
 * @return object
 * @info Pega nome social do servidor através da sua matricula SIAPE
 */
function getNomeSocialServidor($siape, $com_siape=false)
{
    $siape     = getNovaMatriculaBySiape($siape);
    $matricula = removeOrgaoMatricula($siape);

    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela CADASTRO (E0000103.".__LINE__.").");
    $oDBase->query("
    SELECT
        IF(servativ.nome_social='',servativ.nome_serv,servativ.nome_social) AS nome
        FROM servativ
            WHERE servativ.mat_siape = :siape
    ",
    array(
        array(":siape", $siape, PDO::PARAM_STR)
    ));

    if ($com_siape == true)
    {
        $resultado = $matricula . " - " . $oDBase->fetch_object()->nome;
    }
    else
    {
        $resultado = $oDBase->fetch_object()->nome;
    }

    return $resultado;
}


/**
 * @param $siape
 * @return object
 * @info Pega a unidade do servidor atrav?s da sua matricula SIAPE
 */
function getSetorServidor($siape)
{
    $siape = getNovaMatriculaBySiape($siape);

    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela CADASTRO (E000027.".__LINE__.").");
    $oDBase->query("SELECT
                        cod_lot AS setor
                            FROM servativ
                                WHERE servativ.mat_siape = :siape",
        array(
            array(":siape", $siape, PDO::PARAM_STR)
        )
    );

    return $oDBase->fetch_object()->setor;
}

/**
 * @param $siape
 * @return array
 * @info Pega o servidor através da sua matricula SIAPE
 */
function getServidor($siape)
{
    $siape = getNovaMatriculaBySiape($siape);

    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela CADASTRO (E000028.".__LINE__.").");
    $oDBase->query("SELECT
                        CONCAT(SUBSTRING(servativ.mat_siape,6,11) ,\" - \" ,  servativ.nome_serv) AS nome,
                        DATE_FORMAT(autorizacoes_servidores.data_inicio, '%d/%c/%Y') AS data_inicio,
                        DATE_FORMAT(autorizacoes_servidores.data_fim, '%d/%c/%Y') AS data_fim,
                        servativ.cod_lot AS unidade
                            FROM servativ
                                LEFT JOIN autorizacoes_servidores ON servativ.mat_siape = autorizacoes_servidores.siape
                                    WHERE servativ.mat_siape = :siape",
        array(
            array(":siape", $siape, PDO::PARAM_STR)
        )
    );

    return $oDBase->fetch_assoc();
}

/**
 * @param $siape
 * @return array
 * @info Pega o servidor através da sua matricula SIAPE
 */
function getServidorCiclo($siape, $id=null)
{
    $siape = getNovaMatriculaBySiape($siape);

    $query = "
    SELECT
        CONCAT(SUBSTRING(servativ.mat_siape,6,11) ,\" - \" ,  servativ.nome_serv) AS nome,
        DATE_FORMAT(autorizacoes_servidores.data_inicio, '%d/%c/%Y') AS data_inicio,
        DATE_FORMAT(autorizacoes_servidores.data_fim, '%d/%c/%Y') AS data_fim,
        servativ.cod_lot AS unidade
            FROM servativ
                LEFT JOIN autorizacoes_servidores ON servativ.mat_siape = autorizacoes_servidores.siape
                          " . (is_null($id) ? "" : " AND autorizacoes_servidores.ciclo_id = :id") . "
                    WHERE servativ.mat_siape = :siape";

    $params = array(
        array(":siape", $siape, PDO::PARAM_STR)
    );

    if ( !is_null($id) )
    {
        $params[] = array(":id", $id, PDO::PARAM_INT);
    }

    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Problemas no acesso a Tabela CADASTRO (E000029.".__LINE__.").");
    $oDBase->query( $query, $params );

    return $oDBase->fetch_assoc();
}

/**
 * @param $date_ini
 * @param $date_fim
 * @param $ciclo
 * @return string
 * @info Faz a validação se as datas inicial e final da autorização do servidor, est?o dentro do range de datas do ciclo onde se espera cadastrar
 */
function validaRangeDatesIntoCiclo($date_ini, $date_fim, $ciclo)
{
    // FORMATA AS DATAS VINDAS DO AJAX
    $dateiniformated = conv_data($date_ini);
    $datefimformated = conv_data($date_fim);

    $oDBase = new DataBase('PDO');

    //VALIDA SE AS DATAS DA AUTORIZAÇÃO ESTÃO DENTRO DO CICLO
    $query = "SELECT * FROM ciclos WHERE ciclos.id = :ciclo_id AND
                          (:data_inicio  BETWEEN ciclos.data_inicio AND ciclos.data_fim) AND
                            (:data_fim BETWEEN ciclos.data_inicio AND ciclos.data_fim)";

    $paramns = array(
        array(":ciclo_id", $ciclo, PDO::PARAM_INT),
        array(":data_inicio", $dateiniformated, PDO::PARAM_STR),
        array(":data_fim", $datefimformated, PDO::PARAM_STR));


    // EXECUTA A QUERY
    $oDBase->setMensagem("Problemas no acesso a Tabela CICLOS (E000030.".__LINE__.").");
    $oDBase->query($query, $paramns);

    // CASO EXISTA REGISTRO ? ROTORNADO FALSE O QUE POSSIBILITA O CADASTRO
    if (!empty($oDBase->fetch_assoc()))
        return json_encode(array("success" => true, "bloqueia_cadastro" => false));

    return json_encode(array("success" => true, "bloqueia_cadastro" => true));
}

/**
 * @param $date_ini
 * @param $date_fim
 * @param $ciclo
 * @return string
 */
function validaRangeDatesNotConflite($date_ini, $date_fim, $ciclo, $siape)
{
    // FORMATA AS DATAS VINDAS DO AJAX
    $dateiniformated = conv_data($date_ini);
    $datefimformated = conv_data($date_fim);

    $siape = getNovaMatriculaBySiape($siape);

    $oDBase = new DataBase('PDO');

    //VALIDA SE AS DATAS DA AUTORIZAÇÃO ESTÃO DENTRO DO CICLO
    $query = "SELECT * FROM autorizacoes_servidores_usufruto
		WHERE autorizacoes_servidores_usufruto.ciclo_id = :ciclo_id AND
		      autorizacoes_servidores_usufruto.siape = :siape AND
                          ((:data_inicio  BETWEEN autorizacoes_servidores_usufruto.data_inicio AND autorizacoes_servidores_usufruto.data_fim) OR
                            (:data_fim BETWEEN autorizacoes_servidores_usufruto.data_inicio AND autorizacoes_servidores_usufruto.data_fim))
    ";

    $paramns = array(
        array(":ciclo_id",    $ciclo,           PDO::PARAM_INT),
        array(":siape",       $siape,           PDO::PARAM_STR),
        array(":data_inicio", $dateiniformated, PDO::PARAM_STR),
        array(":data_fim",    $datefimformated, PDO::PARAM_STR)
    );


    // EXECUTA A QUERY
    $oDBase->setMensagem("Problemas no acesso a Tabela AUTORIZACOES USUFRUTO (E000031.".__LINE__.").");
    $oDBase->query($query, $paramns);

    // CASO EXISTA REGISTRO É ReTORNADO TRUE O QUE BLOQUEIA O CADASTRO
    if ($oDBase->num_rows() > 0)
    {
        return json_encode(array("success" => true, "bloqueia_cadastro" => true));
    }

    return json_encode(array("success" => true, "bloqueia_cadastro" => false));
}

/**
 * @param $post
 * @return bool|int|null|PDOStatement|resource
 * @info Cria uma nova autorização para o servidor ou atualiza a existente
 */
function create_or_update_autorizacao($post)
{
    $siape = getNovaMatriculaBySiape($post['siape']);

    // FORMATA AS DATAS VINDAS DO AJAX
    $dateini         = explode("/", $post['data_inicio']);
    $datefim         = explode("/", $post['data_final']);
    $dateiniformated = $dateini[2] . "-" . $dateini[1] . "-" . $dateini[0];
    $datefimformated = $datefim[2] . "-" . $datefim[1] . "-" . $datefim[0];

    $oDBase = new DataBase('PDO');

    //  CONSULTA PARA IDENTIFICAR SE JÁ EXISTE AUTORIZAÇÃO PARA O SERVIDOR DENTRO DE UM CICLO
    $querySearch = "SELECT
                        COUNT(autorizacoes_servidores.siape) AS result
                            FROM autorizacoes_servidores
                                WHERE autorizacoes_servidores.siape = :siape AND
                                      autorizacoes_servidores.ciclo_id = :ciclo_id";
    $paramnsSearch = array(
        array(":siape", $siape, PDO::PARAM_STR),
        array(":ciclo_id", $post['ciclo_id'], PDO::PARAM_INT)
    );

    $oDBase->setMensagem("Problemas no acesso a Tabela AUTORIZACOES SERVIDORES (E000032.".__LINE__.").");
    $result = $oDBase->query($querySearch, $paramnsSearch);

    // SE EXISTIR SERA ATUALIZADO COM OS NOVOS VALORES, CASO CONTRÁRIO SERA CRIADO UM NOVO
    if ($result->fetchColumn()) {

        $queryUpdate = "UPDATE autorizacoes_servidores
                          SET data_inicio = :data_inicio ,
                              data_fim = :data_fim
                                WHERE siape = :siape AND
                                      ciclo_id = :ciclo_id";
        $paramnsUpdate = array(
            array(":siape", $siape, PDO::PARAM_STR),
            array(":ciclo_id", $post['ciclo_id'], PDO::PARAM_INT),
            array(":data_inicio", $dateiniformated, PDO::PARAM_STR),
            array(":data_fim", $datefimformated, PDO::PARAM_STR)
        );

        $oDBase->setMensagem("Problemas no acesso a Tabela AUTORIZACOES SERVIDORES (E000104.".__LINE__.").");
        $oDBase->query($queryUpdate, $paramnsUpdate);

        // histórico
        create_or_update_autorizacao_historico($siape, $post['ciclo_id'], 'A');

        registraLog("Alteração de acumulo realizado");
    } else {

        $queryInsert = "INSERT INTO autorizacoes_servidores (siape , ciclo_id , data_inicio, data_fim)
                               VALUES (:siape , :ciclo_id , :data_inicio, :data_fim)";
        $paramnsInsert = array(
            array(":siape", $siape, PDO::PARAM_STR),
            array(":ciclo_id", $post['ciclo_id'], PDO::PARAM_INT),
            array(":data_inicio", $dateiniformated, PDO::PARAM_STR),
            array(":data_fim", $datefimformated, PDO::PARAM_STR)
        );

        $oDBase->setMensagem("Problemas no acesso a Tabela AUTORIZACOES SERVIDORES (E000105.".__LINE__.").");
        $oDBase->query($queryInsert, $paramnsInsert);

        // histórico
        create_or_update_autorizacao_historico($siape, $post['ciclo_id'], 'I');

        registraLog("Inclusão de acumulo realizado");
    }

    return $result;

}

/**
 * @param $id
 * @return void
 * @info Histórico ao Criar uma nova autorização para o servidor ou atualiza a existente
 */
function create_or_update_autorizacao_historico($siape=null, $id=null, $acao='I')
{
    if ( !is_null($siape) && !is_null($id) )
    {
        $oDBase = new DataBase('PDO');

        $queryInsert = "INSERT INTO `autorizacoes_servidores_historico`
            SELECT 0, siape, ciclo_id, data_inicio, data_fim, :acao, :acao_siape, NOW()
                FROM `autorizacoes_servidores`
                    WHERE siape = :siape
                          AND ciclo_id = :ciclo_id ";

        $paramnsInsert = array(
            array(":siape",      $siape, PDO::PARAM_STR),
            array(":ciclo_id",   $id,    PDO::PARAM_INT),
            array(":acao",       $acao,  PDO::PARAM_STR),
            array(":acao_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
        );

        $oDBase->setMensagem("Problemas no acesso a Tabela AUTORIZACOES SERVIDORES (E000033.".__LINE__.").");
        $oDBase->query($queryInsert, $paramnsInsert);
    }
}

/**
 * @param $post
 * @return bool|int|null|PDOStatement|resource
 * @info Cria uma nova autorização de usufruto para o servidor ou atualiza a existente
 */
function create_or_update_autorizacao_usufruto($post)
{
    // FORMATA AS DATAS VINDAS DO AJAX
    $dateini = explode("/", $post['data_inicio']);
    $datefim = explode("/", $post['data_final']);
    $dateiniformated = $dateini[2] . "-" . $dateini[1] . "-" . $dateini[0];
    $datefimformated = $datefim[2] . "-" . $datefim[1] . "-" . $datefim[0];

    $siape = getNovaMatriculaBySiape($post['siape']);

    $oDBase = new DataBase('PDO');
    $queryInsert = "
        INSERT INTO
            autorizacoes_servidores_usufruto
            (siape, ciclo_id, data_inicio, data_fim, tipo_autorizacao)
            VALUES
            (:siape, :ciclo_id, :data_inicio, :data_fim, :tipo_autorizacao)";
    $paramnsInsert = array(
        array(":siape",            $siape,                    PDO::PARAM_STR),
        array(":ciclo_id",         $post['ciclo_id'],         PDO::PARAM_INT),
        array(":tipo_autorizacao", $post['tipo_solicitacao'], PDO::PARAM_STR),
        array(":data_inicio",      $dateiniformated,          PDO::PARAM_STR),
        array(":data_fim",         $datefimformated,          PDO::PARAM_STR)
    );

    // HISTÓRICO
    $queryInsertHist = "
        INSERT INTO
            autorizacoes_servidores_usufruto_historico
            (id, siape, ciclo_id, data_inicio, data_fim,
             tipo_autorizacao, acao, acao_siape, acao_data)
            VALUES
            (0, :siape, :ciclo_id, :data_inicio, :data_fim,
             :tipo_autorizacao, 'I', :acao_siape, NOW())";
    $paramnsInsertHist = array(
        array(":siape",            $siape,                    PDO::PARAM_STR),
        array(":ciclo_id",         $post['ciclo_id'],         PDO::PARAM_INT),
        array(":data_inicio",      $dateiniformated,          PDO::PARAM_STR),
        array(":data_fim",         $datefimformated,          PDO::PARAM_STR),
        array(":tipo_autorizacao", $post['tipo_solicitacao'], PDO::PARAM_STR),
        array(":acao_siape",       $_SESSION['sMatricula'],   PDO::PARAM_STR),
    );

    $oDBase->setMensagem("Problemas no acesso a Tabela AUTORIZACOES USUFRUTO (Histórico) (E000034.".__LINE__.").");
    $oDBase->query($queryInsertHist, $paramnsInsertHist);

    // Atualiza??o da table de ponto, visto que a autorização ? do tipo total.
    if ($post['tipo_solicitacao'] == 'total') {

        try {

            $oDBase->query("
            SELECT
                tabdestinacao_credito.codigo_de_ocorrencia
            FROM
                tabdestinacao_credito
            WHERE
                tabdestinacao_credito.id_destinacao = 8
            ");

            $ocorrencia = $oDBase->fetch_assoc()['codigo_de_ocorrencia'];


            // FORMATA ARRAY
            $datas = array('datastart' => $post['data_inicio'], 'dataend' => $post['data_final']);
            $dates = getDaysCurrent($datas);

            //  JORNADA DO SERVIDOR
            $jornada_semanal = getJornadaServer($siape);
            $jornada_diaria = formata_jornada_para_hhmm($jornada_semanal);

            //  C?LCULO DE HORAS NECESS?RIAS
            $segundos = time_to_sec(left($jornada_diaria, 5));
            $saldo_desconto = $segundos * count($dates);

            // RECUPERA O SALDO ATUAL DE USUFRUTO, E J? CALCULA O NOVO VALOR A SER SALVO
            $oDBase->query("SELECT acumulos_horas.usufruto AS usufruto FROM acumulos_horas WHERE siape = :siape", array(
                array(":siape", $siape, PDO::PARAM_STR)
            ));

            $saldoAtualUsufruto = intval($oDBase->fetch_assoc()['usufruto']);
            $saldoAtualizadoUsufruto = $saldoAtualUsufruto + $saldo_desconto;

            foreach ($dates AS $date) {
                $table = explode('-', $date);
                $table = "ponto" . $table[1] . $table[0];

                $query = "INSERT INTO $table
                        (`dia`,`siape`,`entra`,`intini`,`intsai`,`sai`,`jornd`,`jornp`,`jorndif`,`oco`,`just`,
                         `seq`,`ip`,`ip2`,`ip3`,`ip4`,`justchef`,`iprh`,`matchef`,`siaperh`)
                         VALUES (:dia, :siape, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00', '00:00', '00:00',
                                 :ocorrencia, NULL, '00', ' ', NULL, NULL, NULL, NULL, NULL, :matsiape, NULL)";
                $paramns = array(
                    array(":siape", $siape, PDO::PARAM_STR),
                    array(":matsiape", $_SESSION['sMatricula'], PDO::PARAM_STR),
                    array(":dia", $date, PDO::PARAM_STR),
                    array(":ocorrencia", $ocorrencia, PDO::PARAM_STR)
                );
                $oDBase->query($query, $paramns);
            }

            $oDBase->setMensagem("Falha ao gravar valores!"); // mensagem em caso de erro no acesso a tabela
            $oDBase->query("UPDATE acumulos_horas SET usufruto = :usufruto WHERE siape = :siape", array(
                array(":siape", $siape, PDO::PARAM_STR),
                array(":usufruto", $saldoAtualizadoUsufruto, PDO::PARAM_STR)
            ));


            $oDBase->query("INSERT INTO historico_movimentacoes_acumulos (data_movimentacao, siape) VALUES (:date_value , :siape)", array(
                array(":siape", $siape, PDO::PARAM_STR),
                array(":date_value", date("Y-m-d"), PDO::PARAM_STR)
            ));

        } catch (Exception $e) {
            echo $e->getMessage();
        }

    }

    $oDBase->query($queryInsert, $paramnsInsert);

    registraLog("Cadastrado autorização de usufruto parcial.");

    return ($oDBase->affected_rows() > 0);
}

/**
 * @param $id
 * @return void
 * @info Histórico ao Criar uma nova autorização para o servidor ou atualiza a existente
 */
function create_or_update_autorizacao_usufruto_historico($siape=null, $id=null, $acao='I')
{
    if ( !is_null($siape) && !is_null($id) )
    {
        $oDBase = new DataBase('PDO');

        $queryInsert = "INSERT INTO `autorizacoes_servidores_usufruto_historico`
            SELECT 0, siape, ciclo_id, data_inicio, data_fim, tipo_autorizacao, :acao, :acao_siape, NOW()
                FROM `autorizacoes_servidores_usufruto`
                    WHERE siape = :siape
                          AND ciclo_id = :ciclo_id ";

        $paramnsInsert = array(
            array(":siape",      $siape, PDO::PARAM_STR),
            array(":ciclo_id",   $id,    PDO::PARAM_INT),
            array(":acao",       $acao,  PDO::PARAM_STR),
            array(":acao_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
        );

        $oDBase->query($queryInsert, $paramnsInsert);
    }
}

/**
 * @return bool
 */
function checkServidorHasAutorization($mat = null)
{
    $oDBase = new DataBase('PDO');

    if (empty($mat) || is_null($mat))
    {
        $mat = $_SESSION['sMatricula'];
    }

    $mat = getNovaMatriculaBySiape($mat);

    //VALIDA SE AS DATAS DA AUTORIZAÇÃO ESTÃO DENTRO DO CICLO
    $query = "SELECT COUNT(autorizacoes_servidores.siape) AS result FROM autorizacoes_servidores
                  WHERE CURDATE() BETWEEN autorizacoes_servidores.data_inicio AND autorizacoes_servidores.data_fim AND
                        autorizacoes_servidores.siape = :siape";

    $paramns = array(
        array(":siape", $mat, PDO::PARAM_STR));

    // EXECUTA A QUERY
    $oDBase->query($query, $paramns);
    $resultado = $oDBase->fetch_object()->result;

    return ($resultado > 0);
}


/**
 * @param $mat
 * @return object|stdClass
 */
function getServidorByMatricula($siape = null)
{
    $oDados = new DadosServidoresController();
    return $oDados->selecionaServidorPorMatricula($siape);
}

/**
 * @param $mat
 * @return object|stdClass
 * @info Retorna se o servidor possui saldo na tabela de acumulos
 */
function verifySaldo($mat)
{
    $oDBase = new DataBase('PDO');

    $mat = getNovaMatriculaBySiape($mat);

    $query = "SELECT IF((SUM(acumulos_horas.horas) -
          SUM(acumulos_horas.usufruto)) > 0,\"possui\",\"false\") AS saldo from acumulos_horas
          where acumulos_horas.siape = :siape";

    $oDBase->query($query,
        array(
            array(":siape", $mat, PDO::PARAM_STR))
    );

    $result = $oDBase->fetch_assoc();

    if ($result['saldo'] == "possui")
        return json_encode(array("success" => true, "bloqueia_cadastro" => false));

    return json_encode(array("success" => true, "bloqueia_cadastro" => true));
}

function verifySaldoTypeAutorizationTotal($dados)
{
    $siape = getNovaMatriculaBySiape($dados['siape']);

    //  JORNADA DO SERVIDOR
    $jornada_semanal = getJornadaServer($siape);
    $jornada_diaria = formata_jornada_para_hhmm($jornada_semanal);

    //  PER?ODO DE SOLICITA??O DE ABONO
    $dias_correntes = getDaysCurrent($dados);
    $total_days = count($dias_correntes);

    //  C?LCULO DE HORAS NECESS?RIAS
    $segundos = time_to_sec(left($jornada_diaria, 5));
    $saldo_necessario = $segundos * $total_days;

    $oDBase = new DataBase('PDO');

    $query = "SELECT SUM(acumulos_horas.horas) - SUM(acumulos_horas.usufruto) AS saldo from acumulos_horas
                  where acumulos_horas.siape = :siape";

    $oDBase->query($query, array(array(":siape", $siape, PDO::PARAM_STR)));

    $result = $oDBase->fetch_assoc();

    if ($result['saldo'] >= $saldo_necessario)
        return json_encode(array("success" => true, "bloqueia_cadastro" => false));

    return json_encode(array("success" => true, "bloqueia_cadastro" => true));
}

/**
 * @param $mat
 * @return mixed
 */
function getJornadaServer($mat)
{
    $mat = getNovaMatriculaBySiape($mat);

    $oDBase = new DataBase('PDO');

    //BUSCA A JORNADA DO SERVIDOR
    $query = "SELECT servativ.jornada AS jornada from servativ
                  where servativ.mat_siape = :siape";

    $oDBase->query($query,
        array(array(":siape", $mat, PDO::PARAM_STR))
    );

    return $oDBase->fetch_assoc()['jornada'];
}

/**
 * @param $dados
 * @return array
 */
function getDaysCurrent($dados)
{
    // FORMATA AS DATAS VINDAS DO AJAX
    $dateini = explode("/", $dados['datastart']);
    $datefim = explode("/", $dados['dataend']);

    $dateiniformated = $dateini[2] . "-" . $dateini[1] . "-" . $dateini[0];
    $datefimformated = $datefim[2] . "-" . $datefim[1] . "-" . $datefim[0];

    $dates = getDatesFromRange($dateiniformated, $datefimformated);
    $uteis = [];

    foreach ($dates as $index => $date) {
        if (!verifica_se_dia_nao_util($date, $_SESSION['sLotacao'])) {
            array_push($uteis, $date);
        }
    }

    return $uteis;
}


/**
 * @param $mat Matrícula do servidor
 * @param $ini Data de início da autorização
 * @param $fim Data de final da autorização
 * @return bool
 */
function checkAutorizationUsufruto($mat, $ini, $fim)
{
    $mat = getNovaMatriculaBySiape($mat);

    $oDBase = new DataBase('PDO');

    //BUSCA AAUTORIZAÇÃO
    $query = "
        SELECT * from autorizacoes_servidores_usufruto
            WHERE autorizacoes_servidores_usufruto.siape = :siape
                  AND autorizacoes_servidores_usufruto.data_inicio = :ini
                  AND autorizacoes_servidores_usufruto.data_fim = :fim
    ";

    $oDBase->query($query,
        array(
            array(":siape", $mat, PDO::PARAM_STR),
            array(":ini",   $ini, PDO::PARAM_STR),
            array(":fim",   $fim, PDO::PARAM_STR))
    );

    if ($oDBase->num_rows())
        return true;

    return false;
}


/**
 * @param $mat
 * @param $ini
 * @param $fim
 * @return bool
 */
function checkAutorizationIsParcial($mat, $ini, $fim)
{
    $mat = getNovaMatriculaBySiape($mat);

    $oDBase = new DataBase('PDO');

    //BUSCA A JORNADA DO SERVIDOR
    $query = "SELECT * from autorizacoes_servidores_usufruto
                  where autorizacoes_servidores_usufruto.siape = :siape AND
                        autorizacoes_servidores_usufruto.data_inicio = :ini AND
                        autorizacoes_servidores_usufruto.data_fim = :fim AND
                        autorizacoes_servidores_usufruto.tipo_autorizacao = 'parcial' AND
                         (CURDATE()  NOT BETWEEN autorizacoes_servidores_usufruto.data_inicio AND autorizacoes_servidores_usufruto.data_fim)";

    $oDBase->query($query,
        array(array(":siape", $mat, PDO::PARAM_STR),
            array(":ini", $ini, PDO::PARAM_STR),
            array(":fim", $fim, PDO::PARAM_STR))
    );

    if ($oDBase->num_rows())
        return true;

    return false;
}

/**
 * @param $post
 * @return DataBase
 */
function create_autorizacao_servidor_ip($post)
{
    $siape = getNovaMatriculaBySiape($post['siape']);

    $ips = explode(",", $post['hdnips']);
    $ips = array_unique($ips);

    // FORMATA AS DATAS VINDAS DO AJAX
    $dateiniformated = conv_data($post['data_inicio']);
    $datefimformated = conv_data($post['data_final']);

    $oDBase = new DataBase('PDO');

    $oDBase->query("INSERT INTO servidores_autorizacao (siape , justificativa , data_inicio, data_fim)
                              VALUES (:siape , :justificativa , :data_inicio, :data_fim)", array(
        array(":siape",         $siape, PDO::PARAM_STR),
        array(":justificativa", $post['justificativa'], PDO::PARAM_STR),
        array(":data_inicio",   $dateiniformated, PDO::PARAM_STR),
        array(":data_fim",      $datefimformated, PDO::PARAM_STR)
    ));

    $oDBase->query("SELECT LAST_INSERT_ID() AS id FROM servidores_autorizacao");
    $id_autorizacao = $oDBase->fetch_assoc()['id'];

    foreach ($ips AS $ip) {
        $oDBase->query("INSERT INTO ips_autorizacao (servidor_autorizacao_id , endereco)
                              VALUES (:servidor_autorizacao_id , :endereco)", array(
            array(":servidor_autorizacao_id", $id_autorizacao, PDO::PARAM_INT),
            array(":endereco",                $ip, PDO::PARAM_STR)
        ));
    }

    return $oDBase;
}

/**
 * @return bool
 */
function checkIpAccess()
{
    $ip = getIpReal();

    $obj = new TabServativController();
    $servidor = $obj->selecionaServidor($_SESSION['sMatricula'], null, 1);

    if ($servidor)
    {
        //$servidor  = selecionaServidor($_SESSION['sMatricula']);
        $servidor  = $servidor->fetch_assoc();
        $matricula = $servidor['siape'];
        $setor     = $servidor['cod_lot'];

        $oDBase = new DataBase('PDO');

        // PRIMEIRA VERIFICAÇÃO NA TABLE DE IPS POR SERVIDOR
        $query = "SELECT ips_autorizacao.endereco FROM servidores_autorizacao
                      INNER JOIN ips_autorizacao ON servidores_autorizacao.id = ips_autorizacao.servidor_autorizacao_id
                          WHERE servidores_autorizacao.siape = :siape
                            AND (ips_autorizacao.endereco = :endereco OR ips_autorizacao.endereco = '*')
                            AND (DATE_FORMAT(NOW(),'%Y%m%d') BETWEEN DATE_FORMAT(servidores_autorizacao.data_inicio,'%Y%m%d') AND DATE_FORMAT(servidores_autorizacao.data_fim,'%Y%m%d'))";
        $params = array(
            array(":siape",   $matricula, PDO::PARAM_STR),
            array(":endereco", $ip,       PDO::PARAM_STR)
        );

        $oDBase->query($query, $params);

        if ($oDBase->num_rows() > 0)
        {
            return true;
        }


        // SEGUNDA VERIFICAÇÃO NA TABLE DE IPS POR SETOR
        $query = "SELECT ips_setor.endereco FROM ips_setor
                      WHERE ips_setor.setor = :setor AND
                            (ips_setor.endereco = :endereco OR ips_setor.endereco = '*')";
        $params = array(
            array(":setor",    $setor, PDO::PARAM_STR),
            array(":endereco", $ip,    PDO::PARAM_STR)
        );

        $oDBase->query($query, $params);

        if ($oDBase->num_rows() > 0)
        {
            return true;
        }
    }

    return false;
}


function checkStatusByOrgao($mat)
{
    $mat = getNovaMatriculaBySiape($mat);

    $oDBase = new DataBase('PDO');
    $oDBase->query("SELECT servativ.cpf , SUBSTRING(servativ.cod_uorg, 1, 5) AS orgao FROM servativ WHERE servativ.mat_siape = :siape",
        array(array(":siape", $mat, PDO::PARAM_STR)));

    $result = $oDBase->fetch_object();

    // CASO NÃO RETORNE VALOR DA CONSULTA
    if (empty($result->cpf))
        return false;

    $obj = new Siape();
    $cpf = $result->cpf;
    $orgao = $result->orgao;

    // RECUPERA OS DADOS FUNCIONAIS
    $dadosFuncionais = $obj->buscarDadosFuncionais($cpf, $orgao);

    // VALIDA O RETORNO DA API
    if (!is_object($dadosFuncionais))
        return false;

    $orgaos_ativos = 0;
    $situacoes_invalidas = array('02', '08', '15', '18');

    if (count($dadosFuncionais->dadosFuncionais->DadosFuncionais) > 1) {

        $dadosFuncionais = $dadosFuncionais->dadosFuncionais->DadosFuncionais;

        foreach ($dadosFuncionais as $index => $dadoFuncional) {

            if (in_array($dadoFuncional->codSitFuncional, $situacoes_invalidas)) { // Org?o inativo
                // Inativo
            } else { // ?rg?o ativo
                $orgaos_ativos++;
            }
        }

        if ($orgaos_ativos > 1)
        {
            return true;
        }
        elseif ($orgaos_ativos < 1)
        {
            return true;
        }
        else
        {
            return false;
        }


    }

    return false;
}


/**
 * @param $mat
 * @return bool
 */
function updateAfastamentosBySiape($mat,$grupo='',$compet='')
{
    $oDBase = selecionaServidor($mat);
    $sitcad = $oDBase->fetch_object()->sigregjur;

    ## ocorrências grupos
    $obj = new OcorrenciasGrupos();
    $codigoSemFrequenciaPadrao = $obj->CodigoSemFrequenciaPadrao($sitcad);

    $mat = getNovaMatriculaBySiape($mat);

    $oDBase = new DataBase('PDO');

    $oDBase->query("SELECT servativ.cpf, servativ.cod_lot FROM servativ WHERE servativ.mat_siape = :siape",
        array(array(":siape", $mat, PDO::PARAM_STR))
    );

    $result = $oDBase->fetch_object();

    // CASO NÃO RETORNE VALOR DA CONSULTA
    if (empty($result->cpf))
        return false;

    $obj = new Siape();


    $cpf   = $result->cpf;
    $uorg  = $result->cod_lot;
    $orgao = getOrgaoByUorg($uorg);

    $dadosAfastamentos = $obj->buscarDadosAfastamentoHistorico($cpf, $orgao);

    if (!is_object($dadosAfastamentos))
        return false;

    // TODOS AFASTAMENTOS
    $afastamentos = $dadosAfastamentos->ArrayDadosAfastamento->dadosAfastamentoPorMatricula->DadosAfastamentoPorMatricula;

    // OCORRÊNCIAS LPA
    if (!empty($afastamentos->lpa)) {
        $condicao = true;
        $cicloLpa = $afastamentos->lpa->DadosLpa;

        //FORMATA AS DATAS
        $dia = substr($cicloLpa->dataIni, 0, 2);
        $mes = substr($cicloLpa->dataIni, 2, 2);
        $ano = substr($cicloLpa->dataIni, 4, 4);
        $data_ini = $ano . "-" . $mes . "-" . $dia;

        $dia = substr($cicloLpa->dataFim, 0, 2);
        $mes = substr($cicloLpa->dataFim, 2, 2);
        $ano = substr($cicloLpa->dataFim, 4, 4);
        $data_fim = $ano . "-" . $mes . "-" . $dia;

        $balisainicial = date('Y') . '-' . $_SESSION['mes_inicial'] . '-01';
        $balisafinal = date('Y') . '-' . $_SESSION['mes_final'] . '-' . numero_dias_do_mes($_SESSION['mes_final'], date('Y'));

        if ($data_fim <= $balisainicial) {
            $condicao = false;
        } else if ($data_ini >= $balisafinal) {
            $condicao = false;
        } else if ($data_ini <= $balisainicial && $data_fim <= $balisafinal) {
            $data_ini = $balisainicial;
        } else if ($data_ini <= $balisainicial && $data_fim >= $balisafinal) {
            $data_ini = $balisainicial;
            $data_fim = $balisafinal;
        } else if ($data_ini >= $balisainicial && $data_ini <= $balisafinal && $data_fim >= $balisafinal) {
            $data_fim = $balisafinal;
        }

        if ($condicao) {

            //PEGA AS DATAS DO RANGE E RECUPERA APENAS OS DIAS UTEIS
            $dates = getDatesFromRange($data_ini, $data_fim);
            $uteis = [];
            foreach ($dates as $index => $date) {
                array_push($uteis, $date);
            }

            foreach ($uteis AS $date_util) {
                $bool  = false;
                $table = explode('-', $date_util);
                $table = "ponto" . $table[1] . $table[0];

                // CONSULTA SE O USUARIO POSSUI ALGUMA OCORRENCIA NO DIA CORRESPONDENTE, OU ALGUMA OCORRNCIA DO TIPO 99999 (SEM FREQUNCIA)
                $query = "SELECT oco FROM $table WHERE siape = '" . $mat . "' AND dia = '" . $date_util . "'";
                $oDBase->query($query);
                $result = $oDBase->fetch_assoc();

                $finaldesemana = false;

                // CONDIES
                if (!$result) {
                    $bool = true;
                } else if (in_array($result['oco'], $codigoSemFrequenciaPadrao)) { // 99999
                    $bool = true;
                }else{
                    $bool = true;
                    $finaldesemana = true;
                }

                // INSERT OCORRENCIAS
                if ($bool) {

                      if ($result) {
                        updateAfastamentosBySiapeAtualizarOcorrenciaHistorico($mat, $date_util, "WSIAPE");
                        $query = "UPDATE $table SET idreg = 'W', oco = :ocorrencia, matchef = :matchef WHERE siape = :siape AND dia = :date_util";

                        $paramns = array(
                            array(":siape", $mat, PDO::PARAM_STR),
                            array(":matchef", "WSIAPE", PDO::PARAM_STR),
                            array(":ocorrencia", strPadOcorrencia($cicloLpa->codOcorrencia), PDO::PARAM_STR),
                            array(":date_util", $date_util, PDO::PARAM_STR),
                        );

                    } else if (!$finaldesemana) {

                        $query = "INSERT INTO $table
                               (`dia`,`siape`,`entra`,`intini`,`intsai`,`sai`,`jornd`,`jornp`,`jorndif`,`oco`,`just`,
                                `seq`,`idreg`,`ip`,`ip2`,`ip3`,`ip4`,`justchef`,`iprh`,`ipch`,`matchef`,`siaperh`)
                                VALUES (:dia, :siape, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00', '00:00', '00:00',
                                        :ocorrencia, NULL, '00', 'W', '', '', '', '', NULL, '', '', :matsiape, NULL)";

                        $paramns = array(
                            array(":siape", $mat, PDO::PARAM_STR),
                            array(":matsiape", "WSIAPE", PDO::PARAM_STR),
                            array(":dia", $date_util, PDO::PARAM_STR),
                            array(":ocorrencia", 'XXXXX', PDO::PARAM_STR)
                        );
                    }

                    $oDBase->query($query, $paramns);
                }
            }
        }
    }

    foreach ($afastamentos AS $afastamento) {

        // FÉRIAS
        if (!empty($afastamento->DadosFerias)) {

            $ciclosFerias = $afastamento->DadosFerias;

            if (count($ciclosFerias) > 1) {

                foreach ($ciclosFerias as $key => $cicloFerias) {
                    $condicao = true;

                    $dia = substr($cicloFerias->dataIni, 0, 2);
                    $mes = substr($cicloFerias->dataIni, 2, 2);
                    $ano = substr($cicloFerias->dataIni, 4, 4);
                    $data_ini = $ano . "-" . $mes . "-" . $dia;

                    $dia = substr($cicloFerias->dataFim, 0, 2);
                    $mes = substr($cicloFerias->dataFim, 2, 2);
                    $ano = substr($cicloFerias->dataFim, 4, 4);
                    $data_fim = $ano . "-" . $mes . "-" . $dia;

                    $balisainicial = date('Y') . '-' . $_SESSION['mes_inicial'] . '-01';
                    $balisafinal = date('Y') . '-' . $_SESSION['mes_final'] . '-' . numero_dias_do_mes($_SESSION['mes_final'], date('Y'));

                    if ($data_fim <= $balisainicial) {
                        $condicao = false;
                    } else if ($data_ini >= $balisafinal) {
                        $condicao = false;
                    } else if ($data_ini <= $balisainicial && $data_fim <= $balisafinal) {
                        $data_ini = $balisainicial;
                    } else if ($data_ini <= $balisainicial && $data_fim >= $balisafinal) {
                        $data_ini = $balisainicial;
                        $data_fim = $balisafinal;
                    } else if ($data_ini >= $balisainicial && $data_ini <= $balisafinal && $data_fim >= $balisafinal) {
                        $data_fim = $balisafinal;
                    }


                    if ($condicao) {

                        $dates = getDatesFromRange($data_ini, $data_fim);

                        foreach ($dates AS $date_util) {
                            $bool = false;
                            $table = explode('-', $date_util);
                            $table = "ponto" . $table[1] . $table[0];


                            // CONSULTA SE O USUARIO POSSUI ALGUMA OCORRENCIA NO DIA CORRESPONDENTE, OU ALGUMA OCORRNCIA DO TIPO 99999 (SEM FREQUNCIA)
                            $query = "SELECT oco, dia FROM $table WHERE siape = '" . $mat . "' AND dia = '" . $date_util . "'";
                            $oDBase->query($query);
                            $result = $oDBase->fetch_assoc();

                            $finaldesemana = false;

                            // CONDIES
                            if (!$result) {
                                $bool = true;
                            } else if (in_array($result['oco'], $codigoSemFrequenciaPadrao)) { // 99999
                                $bool = true;
                            }else{
                                $bool = true;
                                $finaldesemana = true;
                            }

                            // INSERT OCORRENCIAS DE FRIAS
                            if ($bool) {

                                if ($result) {
                                    updateAfastamentosBySiapeAtualizarOcorrenciaHistorico($mat, $date_util, "WSIAPE");
                                    $query = "UPDATE $table SET idreg = 'W', oco = :ocorrencia, matchef = :matchef WHERE siape = :siape AND dia = :date_util";

                                    $paramns = array(
                                        array(":siape", $mat, PDO::PARAM_STR),
                                        array(":matchef", "WSIAPE", PDO::PARAM_STR),
                                        array(":ocorrencia", "00221", PDO::PARAM_STR),
                                        array(":date_util", $date_util, PDO::PARAM_STR),
                                    );

                                } else if (!$finaldesemana) {

                                    $query = "INSERT INTO $table
                                (`dia`,`siape`,`entra`,`intini`,`intsai`,`sai`,`jornd`,`jornp`,`jorndif`,`oco`,`just`,
                                 `seq`,`idreg`,`ip`,`ip2`,`ip3`,`ip4`,`justchef`,`iprh`,`ipch`,`matchef`,`siaperh`)
                                 VALUES (:dia, :siape, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00', '00:00', '00:00',
                                         :ocorrencia, NULL, '00', 'W', '', '', '', '', NULL, '', '', :matsiape, NULL)";


                                    $paramns = array(
                                        array(":siape", $mat, PDO::PARAM_STR),
                                        array(":matsiape", $_SESSION['sMatricula'], PDO::PARAM_STR),
                                        array(":dia", $date_util, PDO::PARAM_STR),
                                        array(":ocorrencia", "00221", PDO::PARAM_STR)
                                    );
                                }

                                $oDBase->query($query, $paramns);
                            }

                        }
                    }
                }

            } else {

                $condicao = true;

                $dia = substr($ciclosFerias->dataIni, 0, 2);
                $mes = substr($ciclosFerias->dataIni, 2, 2);
                $ano = substr($ciclosFerias->dataIni, 4, 4);
                $data_ini = $ano . "-" . $mes . "-" . $dia;

                $dia = substr($ciclosFerias->dataFim, 0, 2);
                $mes = substr($ciclosFerias->dataFim, 2, 2);
                $ano = substr($ciclosFerias->dataFim, 4, 4);
                $data_fim = $ano . "-" . $mes . "-" . $dia;

                $balisainicial = date('Y') . '-' . $_SESSION['mes_inicial'] . '-01';
                $balisafinal = date('Y') . '-' . $_SESSION['mes_final'] . '-' . numero_dias_do_mes($_SESSION['mes_final'], date('Y'));

                if ($data_fim <= $balisainicial) {
                    $condicao = false;
                } else if ($data_ini >= $balisafinal) {
                    $condicao = false;
                } else if ($data_ini <= $balisainicial && $data_fim <= $balisafinal) {
                    $data_ini = $balisainicial;
                } else if ($data_ini <= $balisainicial && $data_fim >= $balisafinal) {
                    $data_ini = $balisainicial;
                    $data_fim = $balisafinal;
                } else if ($data_ini >= $balisainicial && $data_ini <= $balisafinal && $data_fim >= $balisafinal) {
                    $data_fim = $balisafinal;
                }

                if ($condicao) {

                    $dates = getDatesFromRange($data_ini, $data_fim);

                    foreach ($dates AS $date_util) {

                        $bool = false;
                        $table = explode('-', $date_util);
                        $table = "ponto" . $table[1] . $table[0];


                        // CONSULTA SE O USUARIO POSSUI ALGUMA OCORRENCIA NO DIA CORRESPONDENTE, OU ALGUMA OCORRNCIA DO TIPO 99999 (SEM FREQUNCIA)
                        $query = "SELECT oco FROM $table WHERE siape = '" . $mat . "' AND dia = '" . $date_util . "'";
                        $oDBase->query($query);
                        $result = $oDBase->fetch_assoc();

                        $finaldesemana = false;

                        // CONDIES
                        if (!$result) {
                            $bool = true;
                        } else if (in_array($result['oco'], $codigoSemFrequenciaPadrao)) { // 99999
                            $bool = true;
                        }else{
                            $bool = true;
                            $finaldesemana = true;
                        }

                        // INSERT OCORRENCIAS DE FRIAS
                        if ($bool) {

                            if ($result) {
                                updateAfastamentosBySiapeAtualizarOcorrenciaHistorico($mat, $date_util, "WSIAPE");
                                $query = "UPDATE $table SET idreg = 'W', oco = :ocorrencia, matchef = :matchef WHERE siape = :siape AND dia = :date_util";

                                $paramns = array(
                                    array(":siape", $mat, PDO::PARAM_STR),
                                    array(":matchef", "WSIAPE", PDO::PARAM_STR),
                                    array(":ocorrencia", "00221", PDO::PARAM_STR),
                                    array(":date_util", $date_util, PDO::PARAM_STR),
                                );

                            } else if (!$finaldesemana) {

                                $query = "INSERT INTO $table
                                (`dia`,`siape`,`entra`,`intini`,`intsai`,`sai`,`jornd`,`jornp`,`jorndif`,`oco`,`just`,
                                 `seq`,`idreg`,`ip`,`ip2`,`ip3`,`ip4`,`justchef`,`iprh`,`ipch`,`matchef`,`siaperh`)
                                 VALUES (:dia, :siape, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00', '00:00', '00:00',
                                         :ocorrencia, NULL, '00', 'W', '', '', '', '', NULL, '', '', :matsiape, NULL)";

                                $paramns = array(
                                    array(":siape", $mat, PDO::PARAM_STR),
                                    array(":matsiape", $_SESSION['sMatricula'], PDO::PARAM_STR),
                                    array(":dia", $date_util, PDO::PARAM_STR),
                                    array(":ocorrencia", "00221", PDO::PARAM_STR)
                                );
                            }

                            $oDBase->query($query, $paramns);
                        }

                    }
                }
            }
        }

        // OCORRÊNCIAS GERAIS

        if (!empty($afastamentos->ocorrencias)) {

            $cicloOcorrencia = $afastamentos->ocorrencias->DadosOcorrencias;

            if (count($cicloOcorrencia) > 1) {

                foreach ($cicloOcorrencia AS $key => $ciclo) {
                    $condicao = true;

                    //FORMATA AS DATAS
                    $dia = substr($cicloOcorrencia[$key]->dataIni, 0, 2);
                    $mes = substr($cicloOcorrencia[$key]->dataIni, 2, 2);
                    $ano = substr($cicloOcorrencia[$key]->dataIni, 4, 4);
                    $data_ini = $ano . "-" . $mes . "-" . $dia;

                    $dia = substr($cicloOcorrencia[$key]->dataFim, 0, 2);
                    $mes = substr($cicloOcorrencia[$key]->dataFim, 2, 2);
                    $ano = substr($cicloOcorrencia[$key]->dataFim, 4, 4);
                    $data_fim = $ano . "-" . $mes . "-" . $dia;

                    $balisainicial = date('Y') . '-' . $_SESSION['mes_inicial'] . '-01';
                    $balisafinal = date('Y') . '-' . $_SESSION['mes_final'] . '-' . numero_dias_do_mes($_SESSION['mes_final'], date('Y'));


                    if ($data_fim <= $balisainicial) {
                        $condicao = false;
                    } else if ($data_ini >= $balisafinal) {
                        $condicao = false;
                    } else if ($data_ini <= $balisainicial && $data_fim <= $balisafinal) {
                        $data_ini = $balisainicial;
                    } else if ($data_ini <= $balisainicial && $data_fim >= $balisafinal) {
                        $data_ini = $balisainicial;
                        $data_fim = $balisafinal;
                    } else if ($data_ini >= $balisainicial && $data_ini <= $balisafinal && $data_fim >= $balisafinal) {
                        $data_fim = $balisafinal;
                    }


                    if ($condicao) {

                        //PEGA AS DATAS DO RANGE E RECUPERA APENAS OS DIAS UTEIS
                        $dates = getDatesFromRange($data_ini, $data_fim);

                        $uteis = [];
                        foreach ($dates as $index => $date) {
                            array_push($uteis, $date);
                        }

                        foreach ($uteis AS $date_util) {
                            $bool = false;
                            $table = explode('-', $date_util);
                            $table = "ponto" . $table[1] . $table[0];


                            // CONSULTA SE O USUARIO POSSUI ALGUMA OCORRENCIA NO DIA CORRESPONDENTE, OU ALGUMA OCORRNCIA DO TIPO 99999 (SEM FREQUNCIA)
                            $query = "SELECT oco FROM $table WHERE siape = '" . $mat . "' AND dia = '" . $date_util . "'";
                            $oDBase->query($query);
                            $result = $oDBase->fetch_assoc();


                            $finaldesemana = false;

                            // CONDIES
                            if (!$result) {
                                $bool = true;
                            } else if (in_array($result['oco'], $codigoSemFrequenciaPadrao)) { // 99999
                                $bool = true;
                            }else{
                                $bool = true;
                                $finaldesemana = true;
                            }

                            // INSERT OCORRENCIAS
                            if ($bool) {

                                if ($result) {
                                    updateAfastamentosBySiapeAtualizarOcorrenciaHistorico($mat, $date_util, "WSIAPE");
                                    $query = "UPDATE $table SET idreg = 'W', oco = :ocorrencia, matchef = :matchef WHERE siape = :siape AND dia = :date_util";

                                    $paramns = array(
                                        array(":siape", $mat, PDO::PARAM_STR),
                                        array(":matchef", "WSIAPE", PDO::PARAM_STR),
                                        array(":ocorrencia", strPadOcorrencia($cicloOcorrencia[$key]->codOcorrencia), PDO::PARAM_STR),
                                        array(":date_util", $date_util, PDO::PARAM_STR),
                                    );

                                } else if (!$finaldesemana) {

                                    $query = "INSERT INTO $table
                               (`dia`,`siape`,`entra`,`intini`,`intsai`,`sai`,`jornd`,`jornp`,`jorndif`,`oco`,`just`,
                                `seq`,`idreg`,`ip`,`ip2`,`ip3`,`ip4`,`justchef`,`iprh`,`ipch`,`matchef`,`siaperh`)
                                VALUES (:dia, :siape, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00', '00:00', '00:00',
                                        :ocorrencia, NULL, '00', 'W', '', '', '', '', NULL, '', '', :matsiape, NULL)";

                                    $paramns = array(
                                        array(":siape", $mat, PDO::PARAM_STR),
                                        array(":matsiape", "WSIAPE", PDO::PARAM_STR),
                                        array(":dia", $date_util, PDO::PARAM_STR),
                                        array(":ocorrencia", strPadOcorrencia($cicloOcorrencia[$key]->codOcorrencia), PDO::PARAM_STR)
                                    );
                                }

                                $oDBase->query($query, $paramns);
                            }
                        }
                    }
                }


            } else {

                $condicao = true;
                //FORMATA AS DATAS
                $dia = substr($cicloOcorrencia->dataIni, 0, 2);
                $mes = substr($cicloOcorrencia->dataIni, 2, 2);
                $ano = substr($cicloOcorrencia->dataIni, 4, 4);
                $data_ini = $ano . "-" . $mes . "-" . $dia;

                $dia = substr($cicloOcorrencia->dataFim, 0, 2);
                $mes = substr($cicloOcorrencia->dataFim, 2, 2);
                $ano = substr($cicloOcorrencia->dataFim, 4, 4);
                $data_fim = $ano . "-" . $mes . "-" . $dia;

                $balisainicial = date('Y') . '-' . $_SESSION['mes_inicial'] . '-01';
                $balisafinal = date('Y') . '-' . $_SESSION['mes_final'] . '-' . numero_dias_do_mes($_SESSION['mes_final'], date('Y'));


                if ($data_fim <= $balisainicial) {
                    $condicao = false;
                } else if ($data_ini >= $balisafinal) {
                    $condicao = false;
                } else if ($data_ini <= $balisainicial && $data_fim <= $balisafinal) {
                    $data_ini = $balisainicial;
                } else if ($data_ini <= $balisainicial && $data_fim >= $balisafinal) {
                    $data_ini = $balisainicial;
                    $data_fim = $balisafinal;
                } else if ($data_ini >= $balisainicial && $data_ini <= $balisafinal && $data_fim >= $balisafinal) {
                    $data_fim = $balisafinal;
                }

                if ($condicao) {

                    //PEGA AS DATAS DO RANGE E RECUPERA APENAS OS DIAS UTEIS
                    $dates = getDatesFromRange($data_ini, $data_fim);

                    $uteis = [];
                    foreach ($dates as $index => $date) {
                        array_push($uteis, $date);
                    }

                    foreach ($uteis AS $date_util) {
                        $bool = false;
                        $table = explode('-', $date_util);
                        $table = "ponto" . $table[1] . $table[0];


                        // CONSULTA SE O USUARIO POSSUI ALGUMA OCORRENCIA NO DIA CORRESPONDENTE, OU ALGUMA OCORRNCIA DO TIPO 99999 (SEM FREQUNCIA)
                        $query = "SELECT oco FROM $table WHERE siape = '" . $mat . "' AND dia = '" . $date_util . "'";
                        $oDBase->query($query);
                        $result = $oDBase->fetch_assoc();

                        $finaldesemana = false;

                        // CONDIES
                        if (!$result) {
                            $bool = true;
                        } else if (in_array($result['oco'], $codigoSemFrequenciaPadrao)) { // 99999
                            $bool = true;
                        }else{
                            $bool = true;
                            $finaldesemana = true;
                        }

                        // INSERT OCORRENCIAS
                        if ($bool) {

                             if ($result) {
                                updateAfastamentosBySiapeAtualizarOcorrenciaHistorico($mat, $date_util, "WSIAPE");
                                $query = "UPDATE $table SET idreg = 'W', oco = :ocorrencia, matchef = :matchef WHERE siape = :siape AND dia = :date_util";

                                $paramns = array(
                                    array(":siape", $mat, PDO::PARAM_STR),
                                    array(":matchef", "WSIAPE", PDO::PARAM_STR),
                                    array(":ocorrencia", strPadOcorrencia($cicloOcorrencia->codOcorrencia), PDO::PARAM_STR),
                                    array(":date_util", $date_util, PDO::PARAM_STR),
                                );


                            } else if (!$finaldesemana) {

                                $query = "INSERT INTO $table
                               (`dia`,`siape`,`entra`,`intini`,`intsai`,`sai`,`jornd`,`jornp`,`jorndif`,`oco`,`just`,
                                `seq`,`idreg`,`ip`,`ip2`,`ip3`,`ip4`,`justchef`,`iprh`,`ipch`,`matchef`,`siaperh`)
                                VALUES (:dia, :siape, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00', '00:00', '00:00',
                                        :ocorrencia, NULL, '00', 'W', '', '', '', '', NULL, '', '', :matsiape, NULL)";

                                $paramns = array(
                                    array(":siape", $mat, PDO::PARAM_STR),
                                    array(":matsiape", "WSIAPE", PDO::PARAM_STR),
                                    array(":dia", $date_util, PDO::PARAM_STR),
                                    array(":ocorrencia", strPadOcorrencia($cicloOcorrencia->codOcorrencia), PDO::PARAM_STR)
                                );
                            }

                            $oDBase->query($query, $paramns);
                        }
                    }
                }
            }
        }

        // OCORRÊNCIAS LPA
        if (!empty($afastamento->lpa)) {

            $condicao = true;
            $cicloLpa = $afastamento->lpa->DadosLpa;

            //FORMATA AS DATAS
            $dia = substr($cicloLpa->dataIni, 0, 2);
            $mes = substr($cicloLpa->dataIni, 2, 2);
            $ano = substr($cicloLpa->dataIni, 4, 4);
            $data_ini = $ano . "-" . $mes . "-" . $dia;

            $dia = substr($cicloLpa->dataFim, 0, 2);
            $mes = substr($cicloLpa->dataFim, 2, 2);
            $ano = substr($cicloLpa->dataFim, 4, 4);
            $data_fim = $ano . "-" . $mes . "-" . $dia;

            $balisainicial = date('Y') . '-' . $_SESSION['mes_inicial'] . '-01';
            $balisafinal = date('Y') . '-' . $_SESSION['mes_final'] . '-' . numero_dias_do_mes($_SESSION['mes_final'], date('Y'));

            if ($data_fim <= $balisainicial) {
                $condicao = false;
            } else if ($data_ini >= $balisafinal) {
                $condicao = false;
            } else if ($data_ini <= $balisainicial && $data_fim <= $balisafinal) {
                $data_ini = $balisainicial;
            } else if ($data_ini <= $balisainicial && $data_fim >= $balisafinal) {
                $data_ini = $balisainicial;
                $data_fim = $balisafinal;
            } else if ($data_ini >= $balisainicial && $data_ini <= $balisafinal && $data_fim >= $balisafinal) {
                $data_fim = $balisafinal;
            }

            if ($condicao) {


                //PEGA AS DATAS DO RANGE E RECUPERA APENAS OS DIAS UTEIS
                $dates = getDatesFromRange($data_ini, $data_fim);
                $uteis = [];
                foreach ($dates as $index => $date) {
                    array_push($uteis, $date);
                }

                foreach ($uteis AS $date_util) {
                    $bool = false;
                    $table = explode('-', $date_util);
                    $table = "ponto" . $table[1] . $table[0];


                    // CONSULTA SE O USUARIO POSSUI ALGUMA OCORRENCIA NO DIA CORRESPONDENTE, OU ALGUMA OCORRNCIA DO TIPO 99999 (SEM FREQUNCIA)
                    $query = "SELECT oco FROM $table WHERE siape = '" . $mat . "' AND dia = '" . $date_util . "'";
                    $oDBase->query($query);
                    $result = $oDBase->fetch_assoc();

                    $finaldesemana = false;

                    // CONDIES
                    if (!$result) {
                        $bool = true;
                    } else if (in_array($result['oco'], $codigoSemFrequenciaPadrao)) { // 99999
                        $bool = true;
                    }else{
                        $bool = true;
                        $finaldesemana = true;
                    }

                    // INSERT OCORRENCIAS
                    if ($bool) {

                        if ($result) {
                            updateAfastamentosBySiapeAtualizarOcorrenciaHistorico($mat, $date_util, "WSIAPE");
                            $query = "UPDATE $table SET idreg = 'W', oco = :ocorrencia, matchef = :matchef WHERE siape = :siape AND dia = :date_util";

                            $paramns = array(
                                array(":siape", $mat, PDO::PARAM_STR),
                                array(":matchef", "WSIAPE", PDO::PARAM_STR),
                                array(":ocorrencia", strPadOcorrencia($cicloLpa->codOcorrencia), PDO::PARAM_STR),
                                array(":date_util", $date_util, PDO::PARAM_STR),
                            );

                        } else if (!$finaldesemana) {

                            $query = "INSERT INTO $table
                               (`dia`,`siape`,`entra`,`intini`,`intsai`,`sai`,`jornd`,`jornp`,`jorndif`,`oco`,`just`,
                                `seq`,`idreg`,`ip`,`ip2`,`ip3`,`ip4`,`justchef`,`iprh`,`ipch`,`matchef`,`siaperh`)
                                VALUES (:dia, :siape, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00', '00:00', '00:00',
                                        :ocorrencia, NULL, '00', 'W', '', '', '', '', NULL, '', '', :matsiape, NULL)";

                            $paramns = array(
                                array(":siape", $mat, PDO::PARAM_STR),
                                array(":matsiape", "WSIAPE", PDO::PARAM_STR),
                                array(":dia", $date_util, PDO::PARAM_STR),
                                array(":ocorrencia", "XXXXX", PDO::PARAM_STR)
                            );
                        }

                        $oDBase->query($query, $paramns);
                    }
                }
            }
        }

        //RECLUSÃO NÃO TEM COMO IMAGINAR COMO VAI VIR O REGISTRO
    }

    return true;
}


/**
 *
 * @param string        $mat
 * @param string        $codOcorrencia
 * @param string (date) $date_util
 * @param string        $idreg
 * @param string        $registrado_por
 * @return void
 */
function updateAfastamentosBySiapeAtualizarOcorrencia($mat, $codOcorrencia, $date_util, $idreg = null, $registrado_por = null)
{
    $mat            = getNovaMatriculaBySiape($mat);
    $idreg          = (is_null($idreg) || empty($idreg) ? 'W' : $idreg);
    $registrado_por = (is_null($registrado_por) || empty($registrado_por) ? 'WSIAPE' : $registrado_por);

    $table = explode('-', $date_util);
    $table = "ponto" . $table[1] . $table[0];

    $oDBase = new DataBase('PDO');

    $query = "UPDATE $table SET
        idreg   = :idreg,
        oco     = :ocorrencia,
        siaperh = :siaperh
    WHERE
        siape = :siape
        AND dia = :date_util
    ";

    $paramns = array(
        array(":siape",      $mat,                             PDO::PARAM_STR),
        array(":ocorrencia", strPadOcorrencia($codOcorrencia), PDO::PARAM_STR),
        array(":date_util",  conv_data($date_util),            PDO::PARAM_STR),
        array(":idreg",      $idreg,                           PDO::PARAM_STR),
        array(":siaperh",    $registrado_por,                  PDO::PARAM_STR),
    );

    $oDBase->query($query, $paramns);
}


/**
 *
 * @param string        $mat
 * @param string        $codOcorrencia
 * @param string (date) $date_util
 * @param string        $idreg
 * @param string        $registrado_por
 * @return void
 */
function updateAfastamentosBySiapeAtualizarOcorrenciaHistorico($mat, $date_util, $registrado_por = null)
{
    $mat            = getNovaMatriculaBySiape($mat);
    $registrado_por = (is_null($registrado_por) || empty($registrado_por) ? 'WSIAPE' : $registrado_por);

    $table = explode('-', $date_util);
    $histponto = "histponto" . $table[1] . $table[0];
    $table = "ponto" . $table[1] . $table[0];

    $oDBase = new DataBase('PDO');

    $query = "INSERT INTO $histponto
    SELECT
        dia, siape, entra, intini, intsai, sai, jornd, jornp, jorndif, oco,
        idreg, ip, ip2, ip3, ip4, ipch, iprh, matchef, siaperh,
        DATE_FORMAT(NOW(),'%Y-%m-%d'),
        DATE_FORMAT(NOW(),'%H:%i:%s'), :siaperh, '', 'A', just, justchef
    FROM
        $table
    WHERE
        siape = :siape
        AND dia = :date_util
    ";

    $paramns = array(
        array(":siape",      $mat,                             PDO::PARAM_STR),
        array(":date_util",  conv_data($date_util),            PDO::PARAM_STR),
        array(":siaperh",    $registrado_por,                  PDO::PARAM_STR),
    );

    $oDBase->query($query, $paramns);
}


/**
 * @return mixed
 */
function getConfiguracaoBasica()
{

    $oDBase = new DataBase('PDO');
    $oDBase->query("SELECT * FROM config_basico WHERE ativo = 'S'");

    return $oDBase;
}


/**
 * @return DataBase
 */
function parametrosHoraExtra()
{
    $oDBase = new DataBase('PDO');
    $oDBase->query("
    SELECT
        id, campo AS mensagem, minutos AS valor
            FROM config_basico
                WHERE grupo = 'hora_extra'
    ");

    return $oDBase;
}

function getParamentroHoraExtra($id)
{
    $oDBase = new DataBase('PDO');
    $oDBase->query("
    SELECT
        id, campo AS mensagem, minutos AS valor
            FROM config_basico
                WHERE grupo = 'hora_extra'
                      AND id = :id
    ",
        array(
            array(":id", $id, PDO::PARAM_INT)
        ));

    return $oDBase->fetch_assoc();
}


function updateConfiguracoesHoraExtra($var)
{
    $oDBase = new DataBase('PDO');

    $query = ("UPDATE config_basico
                  SET minutos = :valor
                    WHERE id = :id");
    $paramns = array(
        array(":valor", $var['valor'], PDO::PARAM_STR),
        array(":id", $var['id'], PDO::PARAM_INT)
    );

    $oDBase->query($query, $paramns);

    return $oDBase;
}


/**
 * @return DataBase
 */
function parametrosSigac()
{
    $oDBase = new DataBase('PDO');
    $oDBase->query("SELECT * FROM config_sigac");

    return $oDBase;
}

function getParamentroSigac($id)
{
    $oDBase = new DataBase('PDO');
    $oDBase->query("SELECT * FROM config_sigac WHERE id = :id ",
        array(
            array(":id", $id, PDO::PARAM_INT)
        )
    );

    return $oDBase->fetch_assoc();
}

function getCargo($codigo)
{
    $oDBase = new DataBase('PDO');
    $oDBase->query("SELECT * FROM tabcargo WHERE COD_CARGO = :codigo ",
        array(
            array(":codigo", $codigo, PDO::PARAM_STR)
        )
    );

    return $oDBase->fetch_assoc();
}


function updateConfiguracoesSigac($var)
{
    $oDBase = new DataBase('PDO');

    $query = ("UPDATE config_sigac
                  SET valor = :valor
                    WHERE id = :id");
    $paramns = array(
        array(":valor", $var['valor'], PDO::PARAM_STR),
        array(":id", $var['id'], PDO::PARAM_INT)
    );

    $oDBase->query($query, $paramns);

    return $oDBase;
}


function updateCargoFuncao($dados, $codident)
{
    $oDBase = new DataBase('PDO');

    $query = ("UPDATE tabcargo
                  SET COD_CARGO = :cargo , DESC_CARGO = :nome , PERMITE_BANCO = :permite
                    WHERE COD_CARGO = :id");
    $paramns = array(
        array(":cargo", $dados['COD_CARGO'], PDO::PARAM_STR),
        array(":nome", $dados['DESC_CARGO'], PDO::PARAM_STR),
        array(":permite", $dados['PERMITE_BANCO'], PDO::PARAM_STR),
        array(":id", $codident, PDO::PARAM_STR)
    );

    $oDBase->query($query, $paramns);

    return $oDBase;
}

function cadastrarCargoFuncao($dados)
{
    $oDBase = new DataBase('PDO');

    $query = ("INSERT INTO tabcargo (`COD_CARGO`,`DESC_CARGO`,`PERMITE_BANCO`,`SUBSIDIOS`) VALUES (:cargo , :nome , :permite, :subsidios)");

    $paramns = array(
        array(":cargo", $dados['COD_CARGO'], PDO::PARAM_STR),
        array(":nome", $dados['DESC_CARGO'], PDO::PARAM_STR),
        array(":permite", $dados['PERMITE_BANCO'], PDO::PARAM_STR),
        array(":subsidios", $dados['SUBSIDIOS'], PDO::PARAM_STR)
    );

    $oDBase->query($query, $paramns);

    return $oDBase;
}


function existsServerByCpf($cpf)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SELECT usu.senha, SUBSTRING(cad.mat_siape,6,12) AS siape , usu.nome FROM usuarios AS usu
                                       JOIN servativ AS cad ON usu.siape=cad.mat_siape
                                          WHERE cad.cpf= :cpf AND
                                                cad.excluido='N' AND
                                                cad.cod_sitcad NOT IN ('02','15','18')", array(
        array(':cpf', $cpf, PDO::PARAM_STR)
    ));

    return $oDBase->fetch_object();
}

function getCicloBySiape($mat)
{
    $oDBase = new DataBase('PDO');

    if (empty($mat))
        $mat = $_SESSION['sMatricula'];

    $mat = getNovaMatriculaBySiape($mat);

    //VALIDA SE AS DATAS DA autorização ESTÃO DENTRO DO CICLO
    $query = "
    SELECT
        ciclos.id , ciclos.data_inicio , ciclos.data_fim
    FROM ciclos
    JOIN autorizacoes_servidores ON ciclos.id = autorizacoes_servidores.ciclo_id
    WHERE
        CURDATE() BETWEEN autorizacoes_servidores.data_inicio
        AND autorizacoes_servidores.data_fim
        AND autorizacoes_servidores.siape = :siape
    ";

    $paramns = array(
        array(":siape", $mat, PDO::PARAM_STR));

    // EXECUTA A QUERY
    $oDBase->query($query, $paramns);

    return $oDBase->fetch_assoc();
}

function getCicloBySiapeUsufruto($mat)
{
    $objBancoDeHorasCiclos = new TabBancoDeHorasCiclosController();

    return $objBancoDeHorasCiclos->getCicloBySiapeUsufruto( $mat );
}

function getConfiguracao($id)
{

    $oDBase = new DataBase('PDO');
    $oDBase->query("SELECT * FROM config_basico WHERE id = :id ",
        array(
            array(":id", $id, PDO::PARAM_INT)
        )
    );

    return $oDBase->fetch_assoc();


}

function update_configuracoes($var)

{
    $oDBase = new DataBase('PDO');

    $query = ("UPDATE config_basico SET
                                  inicio = :inicio,
                                  fim = :fim,
                                  minutos = :minutos,
                                  exibe = :exibe,
                                  ativo = :ativo,
                                  mensagem = :mensagem,
                                  observacao = :observacao
                                  WHERE id = :id"
    );
    $paramns = array(
        array(":inicio", $var['data_inicial'], PDO::PARAM_STR),
        array(":fim", $var['data_final'], PDO::PARAM_STR),
        array(":minutos", $var['minutos'], PDO::PARAM_STR),
        array(":exibe", $var['exibe'], PDO::PARAM_STR),
        array(":ativo", $var['ativo'], PDO::PARAM_STR),
        array(":mensagem", $var['mensagem'], PDO::PARAM_STR),
        array(":observacao", $var['observacao'], PDO::PARAM_STR),
        array(":id", $var['id'], PDO::PARAM_INT)
    );

    $oDBase->query($query, $paramns);

    return $oDBase;
}

function getConfiguracaoHostAutorizado()
{

    $oDBase = new DataBase('PDO');
    $oDBase->query("SELECT * FROM config_host_autorizado");

    return $oDBase;


}

function getConfiguracaoHostAutorizadoId($id)
{

    $oDBase = new DataBase('PDO');
    $oDBase->query("SELECT * FROM config_host_autorizado WHERE id = :id ",
        array(
            array(":id", $id, PDO::PARAM_INT)
        )
    );

    return $oDBase->fetch_assoc();
}



function update_configuracoes_host($var)

{

    $oDBase = new DataBase('PDO');

    $query = ("UPDATE config_host_autorizado SET
                                  ip_do_host = :ip,
                                  observacao = :obs,
                                  autorizado = :aut
                                  WHERE id = :id"
    );
    $paramns = array(
        array(":ip", $var['ip'], PDO::PARAM_STR),
        array(":obs", $var['obs'], PDO::PARAM_STR),
        array(":aut", $var['aut'], PDO::PARAM_STR),
        array(":id", $var['id'], PDO::PARAM_INT)
    );

    $oDBase->query($query, $paramns);

    return $oDBase;
}

function insert_configuracoes_host($var)
{
    $oDBase = new DataBase('PDO');
    $query = "INSERT INTO config_host_autorizado (`ip_do_host`,`observacao`,`autorizado`) VALUES (:ip, :obs, :aut)";
    $paramns = array(
        array(":ip", $var['ip'], PDO::PARAM_STR),
        array(":obs", $var['obs'], PDO::PARAM_STR),
        array(":aut", $var['aut'], PDO::PARAM_STR),

    );

    $oDBase->query($query, $paramns);

    return $oDBase;
}

function getConfiguracaoHostAutorizadoDelet($var)

{
    $oDBase = new DataBase('PDO');
    $query = "DELETE FROM config_host_autorizado WHERE id = :id";
    $paramns = array(
        array(":id", $var['id'], PDO::PARAM_INT)


    );

    $oDBase->query($query, $paramns);
    return $oDBase;

}

function getConfiguracaoSuporte()
{

    $oDBase = new DataBase('PDO');
    $oDBase->query("SELECT * FROM config_suporte");

    return $oDBase;


}

function getConfiguracaoSuporteAlterar($id)
{


    $oDBase = new DataBase('PDO');
    $oDBase->query("SELECT * FROM config_suporte WHERE campo = :id ",
        array(
            array(":id", $id, PDO::PARAM_STR)
        )
    );

    return $oDBase->fetch_assoc();
}

function update_configuracoes_suporte($var)

{

    $oDBase = new DataBase('PDO');

    $query = ("UPDATE config_suporte SET
                                  ativo = :ativo,
                                  emails = :emails
                                  WHERE campo = :campo"
    );
    $paramns = array(

        array(":ativo", $var['ativo'], PDO::PARAM_STR),
        array(":emails", $var['emails'], PDO::PARAM_STR),
        array(":campo", $var['campo'], PDO::PARAM_STR)
    );

    $oDBase->query($query, $paramns);

    return $oDBase;
}

function updateServativ($mat)
{
    $siape = getNovaMatriculaBySiape($mat['matricula']);

    $oDBase = new DataBase('PDO');

    $query = ("UPDATE servativ SET
                                  flag_nome_social = :flag_nome
                                  WHERE mat_siape = :matricula"
    );

    $paramns = array(

        array(":flag_nome", $mat['flag_nome'], PDO::PARAM_INT),
        array(":matricula", $siape, PDO::PARAM_STR),

    );

    $oDBase->query($query, $paramns);

    return true;
}

/**
 * @param $mat
 * @return mixed
 * @info Recupera o saldo de banco de horas do servidor dentro do ano corrente
 */
function getBalanceIntoYear($mat)
{

    $mat = getNovaMatriculaBySiape($mat);

    $oDBase = new DataBase('PDO');

    $inicio = date('Y').'-01-01';
    $final  = date('Y').'-12-31';

    $query = ("SELECT (SUM(historico_movimentacoes_acumulos.acumulo) - SUM(historico_movimentacoes_acumulos.usufruto)) AS saldo FROM historico_movimentacoes_acumulos
                                  WHERE historico_movimentacoes_acumulos.data_movimentacao >= :inicio AND
                                        historico_movimentacoes_acumulos.data_movimentacao <= :final  AND
                                        historico_movimentacoes_acumulos.siape = :matricula");

    $paramns = array(
        array(":matricula", $mat, PDO::PARAM_STR),
        array(":inicio", $inicio, PDO::PARAM_STR),
        array(":final", $final, PDO::PARAM_STR),
    );

    $oDBase->query($query, $paramns);

    return $oDBase->fetch_assoc()['saldo'];
}

/**
 * @param $mat
 * @return mixed
 * @info Recupera o saldo de banco de horas do servidor dentro do m?s corrente
 */
function getBalanceIntoMonth($mat)
{

    $mat = getNovaMatriculaBySiape($mat);

    $oDBase = new DataBase('PDO');

    $inicio = date('Y').'-'.date('m').'-01';
    $final  = date('Y').'-'.date('m').'-31';

    $query = ("SELECT (SUM(historico_movimentacoes_acumulos.acumulo) - SUM(historico_movimentacoes_acumulos.usufruto)) AS saldo FROM historico_movimentacoes_acumulos
                                  WHERE historico_movimentacoes_acumulos.data_movimentacao >= :inicio AND
                                        historico_movimentacoes_acumulos.data_movimentacao <= :final  AND
                                        historico_movimentacoes_acumulos.siape = :matricula");

    $paramns = array(
        array(":matricula", $mat, PDO::PARAM_STR),
        array(":inicio", $inicio, PDO::PARAM_STR),
        array(":final", $final, PDO::PARAM_STR),
    );

    $oDBase->query($query, $paramns);

    return $oDBase->fetch_assoc()['saldo'];
}


/**
 * @param $tabela
 * @param $limit
 * @return DataBase
 * @info Recupera a matricula e uma breve apresentação para que em seguida seja apresentada na tabela de listagem de servidores na funcionalidade de envio para o SIAPE
 */
function buscaServidoresParaEnvioSiape($tabela, $limit)
{
    ## ocorrências grupos
    $obj = new OcorrenciasGrupos();
    $codigosAgrupadosParaDesconto = $obj->CodigosAgrupadosParaDesconto();

    $oDBase = new DataBase('PDO');

    $query = "SELECT usuarios.siape AS siape , CONCAT(usuarios.nome,' - ',usuarios.siape) AS nomeservidor FROM usuarios
                            JOIN " . $tabela . " ON usuarios.siape = " . $tabela . ".siape
                                WHERE usuarios.upag = :upag AND
                                      " . $tabela . ".oco IN (" . implode(',', $codigosAgrupadosParaDesconto) . ") ";

    $query .= "GROUP BY usuarios.nome ORDER BY usuarios.nome";
    if ($limit == 'sim')
        $query .= " LIMIT 10";

    $oDBase->query($query, array(
        array(":upag", $_SESSION['upag'], PDO::PARAM_STR)
    ));

    return $oDBase;
}

/**
 * @param $tabela
 * @param $limit
 * @return DataBase
 * @info Recupera a matricula e uma breve apresentação para que em seguida seja apresentada na tabela de listagem de servidores na funcionalidade de envio para o SIAPE
 */
function buscaCargos($limit, $filtros)
{
    $oDBase = new DataBase('PDO');

    $query = "SELECT * FROM tabcargo";


    if ($filtros['typesearch'] == 'nome') {
        $query .= " WHERE tabcargo.DESC_CARGO LIKE '%" . $filtros['search'] . "%' ";
    } elseif ($filtros['typesearch'] == 'codigo') {
        $query .= " WHERE tabcargo.COD_CARGO LIKE '%" . $filtros['search'] . "%' ";
    } else {
        $query .= "";
    }

    $query .= " ORDER BY tabcargo.DESC_CARGO";

    if ($limit == 'sim')
        $query .= " LIMIT 10";

    $oDBase->query($query);

    return $oDBase;
}

/**
 * @param $tabela
 * @param $siape
 * @return DataBase
 * @info Recupera as informações básicas do servidor para que assim apresente na tabela de envio para o SIAPE
 */
function getInfoServidor($tabela, $siape)
{

    $siape = getNovaMatriculaBySiape($siape);

    $oDBase = selecionaServidor($siape);
    $sitcad = $oDBase->fetch_object()->sigregjur;

    ## ocorrências grupos
    $obj = new OcorrenciasGrupos();
    $codigosAgrupadosParaDesconto = $obj->CodigosAgrupadosParaDesconto($sitcad);

    $oDBase = new DataBase('PDO');

    $query = "SELECT DATE_FORMAT(" . $tabela . ".dia, '%d/%m/%Y') AS dia,
                     " . $tabela . ".entra,
                     " . $tabela . ".intini,
                     " . $tabela . ".intsai,
                     " . $tabela . ".sai,
                     " . $tabela . ".jornd,
                     " . $tabela . ".jornp,
                     " . $tabela . ".jorndif,
                     " . $tabela . ".oco
                          FROM usuarios
                            JOIN " . $tabela . " ON usuarios.siape = " . $tabela . ".siape
                                WHERE usuarios.upag = :upag AND
                                      usuarios.siape = :siape AND
                                      " . $tabela . ".oco IN (" . implode(',', $codigosAgrupadosParaDesconto) . ")";

    $oDBase->query($query, array(
        array(":upag", $_SESSION['upag'], PDO::PARAM_STR),
        array(":siape", $siape, PDO::PARAM_STR)
    ));

    return $oDBase;
}

/**
 * @param $mat
 * @param $compet
 * @return string
 * @info valida se o servidor já foi homologado no sistema conforme a competência passada
 */
function verifyHomologado($mat, $compet)
{

    $mat = getNovaMatriculaBySiape($mat);

    $oDBase = new DataBase('PDO');


    $ano = substr($compet, 2, 5);
    $mes = substr($compet, 0, 2);

    $query = "SELECT homologados.mat_siape FROM homologados
                    WHERE homologados.mat_siape = :siape AND
                          homologados.compet = :compet AND
                          homologados.homologado_data > homologados.desomologado_data AND
                          homologados.homologado IN ('V','S') ";

    $parametros = array(
        array(":compet", $ano . $mes, PDO::PARAM_STR),
        array(":siape", $mat, PDO::PARAM_STR)
    );

    $oDBase->query($query, $parametros);

    if ($oDBase->affected_rows())
        return "SIM";

    return "Não";
}

/**
 * @param $mat
 * @param $compet
 * @return string
 * @info valida se já foi enviado para o SIAPE os registros do servidor, conforme a competência passada
 */
function verifyIfSendSiape($mat, $compet)
{

    $mat = getNovaMatriculaBySiape($mat);

    $oDBase = new DataBase('PDO');


    $ano = substr($compet, 2, 5);
    $mes = substr($compet, 0, 2);

    $query = "SELECT controle_envio_siape.siape FROM controle_envio_siape
                    WHERE controle_envio_siape.siape = :siape AND
                          controle_envio_siape.competencia = :compet";

    $parametros = array(
        array(":compet", $ano . $mes, PDO::PARAM_STR),
        array(":siape", $mat, PDO::PARAM_STR)
    );

    $oDBase->query($query, $parametros);

    if ($oDBase->affected_rows())
        return "SIM";

    return "Não";
}

/**
 * @param $mat
 * @param $compet
 * @return bool
 * @info Salva o registro de envio para o SIAPE de acordo com a competência e a matricula do servidor
 */
function salvarRegistroEnvioSiape($mat, $compet)
{

    $mat = getNovaMatriculaBySiape($mat);

    $oDBase = new DataBase('PDO');

    $ano = substr($compet, 2, 5);
    $mes = substr($compet, 0, 2);

    $query = "INSERT";

    $parametros = array(
        array(":compet", $ano . $mes, PDO::PARAM_STR),
        array(":siape", $mat, PDO::PARAM_STR)
    );

    //$oDBase->query($query,$parametros);

    return true;
}

function criarXmlConsultaAfastamentosSiape($servidor, $tipo_afastamento = 5, $situacao_afastamento = 5)
{

    $xml = new DOMDocument('1.0');
    $xml->formatOutput = true;

    $wssiape = $xml->createElement("wssiape");
    $xml->appendChild($wssiape);
    $operacao = $xml->createElement("operacao", "CONSULTAR");
    $wssiape->appendChild($operacao);
    $credenciais = $xml->createElement("credenciais");
    $wssiape->appendChild($credenciais);
    $cpf = $xml->createElement("cpf", $servidor->cpf);
    $credenciais->appendChild($cpf);
    $afastamento = $xml->createElement("afastamento");
    $wssiape->appendChild($afastamento);
    $codigoOrgao = $xml->createElement("codigoOrgao", $servidor->orgao);
    $afastamento->appendChild($codigoOrgao);
    $matricula = $xml->createElement("matricula", $servidor->siape);
    $afastamento->appendChild($matricula);
    $tipo = $xml->createElement("tipoAfastamento", $tipo_afastamento);
    $afastamento->appendChild($tipo);
    $situacao = $xml->createElement("situacaoAfastamento", $situacao_afastamento);
    $afastamento->appendChild($situacao);
    $datainicio = $xml->createElement("dataInicio", "20120101");
    $afastamento->appendChild($datainicio);

    return $xml->save("siape/docs/arquivobasesiape.xml");
}

function criarXmlIncluirAfastamentosSiape($servidor, $tipo_afastamento = 5, $situacao_afastamento = 5)
{
    $data = array(
        'operacao' => 'CONSULTAR',
        'credenciais' => array(
            'cpf' => $servidor->cpf
        ),
        'afastamento' => array(
            'codigoOrgao' => $servidor->orgao,
            'matricula' => $servidor->siape,
            'tipoAfastamento' => $tipo_afastamento,
            'situacaoAfastamento' => $situacao_afastamento,
            'dataInicio' => '20120101'  //(formato YYYYMMDD)
            // 'dataFim' => '20120101' //(formato YYYYMMDD)
        ),
        'Signature' => 'http://www.php-tools.de'
    );

    $options = array(
        XML_SERIALIZER_OPTION_INDENT      => "\t",     // indent with tabs
        XML_SERIALIZER_OPTION_LINEBREAKS  => "\n",     // use UNIX line breaks
        XML_SERIALIZER_OPTION_ROOT_NAME   => 'wssiape',// root tag
        XML_SERIALIZER_OPTION_DEFAULT_TAG => 'item'    // tag for values
    );

    $serializer = new XML_Serializer($options);
    $serializer->serialize($data);

    return htmlentities($serializer->getSerializedData());
}


function recuperarServidorParaEnviarSiape($siape)
{
    $oDBase = new DataBase('PDO');
    $query = "SELECT servativ.mat_siape AS siape , servativ.cpf , SUBSTRING(servativ.cod_uorg, 1, 5) AS orgao from servativ WHERE servativ.mat_siape = :siape";
    $parametros = array(array(":siape", $siape, PDO::PARAM_STR));
    $oDBase->query($query, $parametros);
    return $oDBase->fetch_object();
}

function script2($matricula, $orgao)
{

    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto012018 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape =  '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto022018 SET siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto032018 SET siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto042018 SET siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto052018 SET siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto062018 SET siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}

function script3($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto072018 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape =  '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto082018 SET siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto092018 SET siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto102018 SET siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto112018 SET siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto122018 SET siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}

function script4($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto012017 SET siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape =  '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto022017 SET siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape =  '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto032017 SET siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape =  '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto042017 SET siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape =  '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto052017 SET siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape =  '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto062017 SET siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape =  '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}

function script5($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto072017 SET siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape =  '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto082017 SET siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape =  '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto092017 SET siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape =  '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto102017 SET siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape =  '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto112017 SET siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape =  '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto122017 SET siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape =  '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}

function script6($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto012017 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto022017 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto032017 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto042017 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto052017 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto062017 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}

function script7($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto072017 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto082017 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto092017 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto102017 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto112017 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto122017 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}

function script8($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto012018 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto022018 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto032018 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto042018 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto052018 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto062018 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}

function script9($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto072018 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto082018 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto092018 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto102018 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto112018 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto122018 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}

function script10($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto012017 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto022017 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto032017 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto042017 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto052017 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto062017 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}

function script11($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto072017 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto082017 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto092017 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto102017 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto112017 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto122017 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}

function script12($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto012018 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto022018 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto032018 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto042018 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto052018 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto062018 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}

function script13($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto072018 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto082018 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto092018 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto102018 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto112018 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ponto122018 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}


function script14($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto012018 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto022018 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto032018 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto042018 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto052018 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto062018 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}

function script15($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto072018 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto082018 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto092018 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto102018 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto112018 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto122018 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}

function script16($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto012017 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto022017 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto032017 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto042017 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto052017 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto062017 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}

function script17($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto072017 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto082017 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto092017 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto102017 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto112017 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto122017 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}

function script18($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto012018 SET matchef = CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto022018 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto032018 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto042018 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchefmatchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto052018 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto062018 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}

function script19($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto072018 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto082018 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto092018 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto102018 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto112018 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto122018 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}

function script20($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto012017 SET matchef = CONCAT(SUBSTRING('" . $orgao . "',1,5), matchefmatchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto022017 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchefmatchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto032017 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchefmatchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto042017 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchefmatchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto052017 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchefmatchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto062017 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchefmatchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}

function script21($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto072017 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchefmatchefmatchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto082017 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchefmatchefmatchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto092017 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchefmatchefmatchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto102017 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchefmatchefmatchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto112017 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchefmatchefmatchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto122017 SET matchef =  CONCAT(SUBSTRING('" . $orgao . "',1,5), matchefmatchefmatchef) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND matchef IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}

function script22($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto012018 SET siapealt = CONCAT(SUBSTRING('" . $orgao . "',1,5), siapealt) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siapealt IS NOT NULL AND LENGTH(matchef) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto022018 SET siapealt =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siapealt) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siapealt IS NOT NULL AND LENGTH(siapealt) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto032018 SET siapealt =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siapealt) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siapealt IS NOT NULL AND LENGTH(siapealt) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto042018 SET siapealt =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siapealt) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siapealt IS NOT NULL AND LENGTH(siapealt) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto052018 SET siapealt =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siapealt) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siapealt IS NOT NULL AND LENGTH(siapealt) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto062018 SET siapealt =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siapealt) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siapealt IS NOT NULL AND LENGTH(siapealt) = 7; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}

function script23($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto072018 SET siapealt =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siapealt IS NOT NULL AND LENGTH(siapealt) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto082018 SET siapealt =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siapealt IS NOT NULL AND LENGTH(siapealt) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto092018 SET siapealt =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siapealt IS NOT NULL AND LENGTH(siapealt) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto102018 SET siapealt =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siapealt IS NOT NULL AND LENGTH(siapealt) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto112018 SET siapealt =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siapealt IS NOT NULL AND LENGTH(siapealt) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto122018 SET siapealt =  CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "') WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siapealt IS NOT NULL AND LENGTH(siapealt) = 7; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}

function script24($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto012017 SET siapealt = CONCAT(SUBSTRING('" . $orgao . "',1,5), siapealt) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siapealt IS NOT NULL AND LENGTH(siapealt) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto022017 SET siapealt =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siapealt) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siapealt IS NOT NULL AND LENGTH(siapealt) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto032017 SET siapealt =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siapealt) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siapealt IS NOT NULL AND LENGTH(siapealt) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto042017 SET siapealt =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siapealt) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siapealt IS NOT NULL AND LENGTH(siapealt) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto052017 SET siapealt =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siapealt) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siapealt IS NOT NULL AND LENGTH(siapealt) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto062017 SET siapealt =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siapealt) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siapealt IS NOT NULL AND LENGTH(siapealt) = 7; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}

function script25($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto072017 SET siapealt =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siapealt) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siapealt IS NOT NULL AND LENGTH(siapealt) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto082017 SET siapealt =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siapealt) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siapealt IS NOT NULL AND LENGTH(siapealt) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto092017 SET siapealt =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siapealt) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siapealt IS NOT NULL AND LENGTH(siapealt) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto102017 SET siapealt =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siapealt) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siapealt IS NOT NULL AND LENGTH(siapealt) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto112017 SET siapealt =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siapealt) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siapealt IS NOT NULL AND LENGTH(siapealt) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto122017 SET siapealt =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siapealt) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siapealt IS NOT NULL AND LENGTH(siapealt) = 7; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}

function script26($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto012018 SET siaperh = CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto022018 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto032018 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto042018 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto052018 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto062018 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}

function script27($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto072018 SET siaperh = CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto082018 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto092018 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto102018 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperhsiaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto112018 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto122018 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}

function script28($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto012017 SET siaperh = CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto022017 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto032017 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto042017 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto052017 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto062017 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}

function script29($matricula, $orgao)
{
    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto072017 SET siaperh = CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto082017 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto092017 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto102017 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto112017 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histponto122017 SET siaperh =  CONCAT(SUBSTRING('" . $orgao . "',1,5), siaperh) WHERE (siape =  CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "')) AND siaperh IS NOT NULL AND LENGTH(siaperh) = 7; SET FOREIGN_KEY_CHECKS=1;");

    return true;

}


function script30($matricula, $orgao)
{

    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE homologados SET mat_siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), mat_siape) WHERE mat_siape = '" . $matricula . "';SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE homologados SET homologado_siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), homologado_siape) WHERE mat_siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "')");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE frq2017 SET mat_siape = CONCAT(" . $orgao . ",mat_siape) WHERE mat_siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE frq2018 SET mat_siape = CONCAT(" . $orgao . ",mat_siape) WHERE mat_siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histcad SET mat_siape = CONCAT(" . $orgao . ",mat_siape) WHERE mat_siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histcad SET siapealt =  CONCAT(" . $orgao . ",siapealt) WHERE siapealt IS NOT NULL AND mat_siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), siapealt); SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE historic SET mat_siape = CONCAT(" . $orgao . ",mat_siape) WHERE mat_siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE historic SET siape_registro = CONCAT(" . $orgao . ",siape_registro) WHERE siape_registro IS NOT NULL AND mat_siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE substituicao SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE substituicao SET siape_registro = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape_registro) WHERE siape_registro IS NOT NULL AND siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "'); SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE tabdnu SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape IS NOT NULL AND siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE tabdnu SET siape_autorizado = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape_autorizado) WHERE siape_autorizado IS NOT NULL AND siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "'); SET FOREIGN_KEY_CHECKS=1;");


    return true;

}


function script31($matricula, $orgao)
{

    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE acumulos_horas SET siape = CONCAT(" . $orgao . ",siape) WHERE siape = '" . $matricula . "';SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE autorizacoes_servidores SET siape = CONCAT(" . $orgao . ",siape) WHERE siape = '" . $matricula . "';SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE autorizacoes_servidores_usufruto SET siape = CONCAT(" . $orgao . ",siape) WHERE siape = '" . $matricula . "';SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE banco_de_horas SET siape = CONCAT(" . $orgao . ",siape) WHERE siape = '" . $matricula . "';SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE bhoras SET siape = CONCAT(" . $orgao . ",siape) WHERE siape = '" . $matricula . "';SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE control_entrada_saida SET siape = CONCAT(" . $orgao . ",siape) WHERE siape = '" . $matricula . "';SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE control_entrada_saida_todos SET siape = CONCAT(" . $orgao . ",siape) WHERE siape = '" . $matricula . "';SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE control_historico SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "';SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE control_historico SET siape_rh = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape_rh) WHERE siape_rh IS NOT NULL AND siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "');SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE turno_estendido SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape IS NOT NULL; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE turno_estendido SET siape_registro = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape_registro) WHERE siape_registro IS NOT NULL AND siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "'); SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE turno_estendido_historico SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape IS NOT NULL; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE turno_estendido_historico SET siape_registro = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape_registro) WHERE siape_registro IS NOT NULL AND siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),'" . $matricula . "'); SET FOREIGN_KEY_CHECKS=1;");

    return true;

}


function script32($matricula, $orgao)
{

    $oDBase = new DataBase('PDO');
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE control_entrada_saida SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE control_entrada_saida_todos SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE control_historico SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE control_historico SET siape_rh = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape_rh) WHERE siape_rh IS NOT NULL AND siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "'); SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE control_ip SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE control_ip_rapidos SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE control_ip_registro SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE control_ip_vpn SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE control_reghorario SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE dados_gex SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE dedo SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE exclus SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histlot SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE histlot  SET siape_registro = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape_registro) WHERE siape_registro IS NOT NULL AND siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "'); SET FOREIGN_KEY_CHECKS=1;");


    return true;

}


function script33($matricula, $orgao)
{

    $oDBase = new DataBase('PDO');

    //$oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE servativ SET mat_siape = CONCAT(SUBSTRING('" . $orgao . "',mat_siape)) WHERE mat_siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE acesso_aos_modulos SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE _ocorrencias_88888_calculo_diferenca_2017 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE _ocorrencias_88888_calculo_diferenca_2018 SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE acumulos_horas SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE autorizacoes_servidores SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE autorizacoes_servidores_usufruto SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE banco_de_horas SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape)) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE liberupag SET siape_recebe = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape_recebe)) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE liberupag SET siape_registro = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape_registro)) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE bhoras SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");


    return true;

}


function script34($matricula, $orgao)
{

    $oDBase = new DataBase('PDO');

    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE historico_movimentacoes_acumulos SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE historico_observacoes SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE historico_observacoes SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE historico_observacoes SET siaperh = CONCAT(SUBSTRING('" . $orgao . "',1,5),siaperh) WHERE siaperh IS NOT NULL AND siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "'); SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ilegal SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ilegal_desconhecido SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");


    return true;

}


function script35($matricula, $orgao)
{

    $oDBase = new DataBase('PDO');


    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE jornada_historico SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE jornada_historico SET siape_registro = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape_registro) WHERE siape_registro IS NOT NULL AND siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE liberacao_acesso_especial SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE liberacao_acesso_especial SET solicitante_siape = CONCAT('" . $orgao . "',solicitante_siape) WHERE (solicitante_siape IS NOT NULL OR solicitante_siape <> '') AND siape = CONCAT(SUBSTRING('" . $orgao . "',1,5), '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE liberupag SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE servativ_fotos SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE servidores_autorizacao SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE vw_sisrefsae SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape IS NOT NULL AND siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE config_suporte SET siape_registro = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape_registro) WHERE siape_registro = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE liberacao_homologacao SET siape_registro = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape_registro) WHERE siape_registro = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE usuarios SET siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),siape) WHERE siape IS NOT NULL AND siape = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE ocupantes SET MAT_SIAPE = CONCAT(SUBSTRING('" . $orgao . "',1,5),mat_siape) WHERE MAT_SIAPE = '" . $matricula . "'; SET FOREIGN_KEY_CHECKS=1;");
    $oDBase->query("SET FOREIGN_KEY_CHECKS=0;UPDATE servativ SET mat_siape = CONCAT(SUBSTRING('" . $orgao . "',1,5),mat_siape) WHERE mat_siape = '" . $matricula . "';SET FOREIGN_KEY_CHECKS=1;");


    return true;

}


function script1()
{

    $oDBase = new DataBase('PDO');


    $oDBase->query("SET FOREIGN_KEY_CHECKS = 0;

    ALTER TABLE  homologados CHANGE COLUMN desomologado_siape desomologado_siape VARCHAR(12);
    ALTER TABLE   homologados CHANGE COLUMN desomologado_siape desomologado_siape VARCHAR(12);
    ALTER TABLE   homologados CHANGE COLUMN homologado_siape homologado_siape VARCHAR(12);
    ALTER TABLE   frq2017  CHANGE COLUMN mat_siape mat_siape VARCHAR(12);
    ALTER TABLE   frq2018  CHANGE COLUMN mat_siape mat_siape VARCHAR(12);
    ALTER TABLE   histcad CHANGE COLUMN mat_siape mat_siape VARCHAR(12);
    ALTER TABLE   historic CHANGE COLUMN mat_siape mat_siape VARCHAR(12);
    ALTER TABLE   homologados CHANGE COLUMN mat_siape mat_siape VARCHAR(12);
    ALTER TABLE   ocupantes CHANGE COLUMN MAT_SIAPE MAT_SIAPE VARCHAR(12);
    ALTER TABLE   servativ CHANGE COLUMN mat_siape mat_siape VARCHAR(12);
    ALTER TABLE   histponto012018 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   histponto022018 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   histponto032018 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   histponto042018 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   histponto052018 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   histponto062018 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   histponto072018 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   histponto082018 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   histponto092018 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   histponto102018 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   histponto112018 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   ponto012017 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   ponto012018 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   ponto022017 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   ponto022018 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   ponto032017 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   ponto032018 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   ponto042017 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   ponto042018 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   ponto052017 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   ponto052018 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   ponto062017 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   ponto062018 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   ponto072017 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   ponto072018 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   ponto082017 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   ponto082018 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   ponto092017 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   ponto092018 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   ponto102017 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   ponto102018 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   ponto112017 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   ponto112018 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   ponto122017 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   ponto122018 CHANGE COLUMN matchef matchef VARCHAR(12);
    ALTER TABLE   acesso_aos_modulos CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   acumulos_horas CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   autorizacoes_servidores CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   autorizacoes_servidores_usufruto CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   banco_de_horas CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   bhoras CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   control_entrada_saida CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   control_entrada_saida_todos CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   control_historico CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   control_ip CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   control_ip_rapidos CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   control_ip_registro CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   control_ip_vpn CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   control_reghorario CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   dados_gex CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   dedo CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE `exclus` CHANGE COLUMN siape siape VARCHAR(32);
    ALTER TABLE   histlot CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   historico_movimentacoes_acumulos CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   historico_observacoes CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   histponto012018 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   histponto022018 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   histponto032018 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   histponto042018 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   histponto052018 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   histponto062018 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   histponto072018 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   histponto082018 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   histponto092018 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   histponto102018 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   histponto112018 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ilegal CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ilegal_desconhecido CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   jornada_historico CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   liberacao_acesso_especial CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   liberupag CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto012017 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto012018 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto022017 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto022018 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto032017 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto032018 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto042017 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto042018 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto052017 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto052018 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto062017 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto062018 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto072017 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto072018 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto082017 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto082018 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto092017 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto092018 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto102017 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto102018 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto112017 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto112018 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto122017 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto122018 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto012019 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto022019 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto032019 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto042019 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto052019 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto062019 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto072019 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto082019 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto092019 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto102019 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto112019 CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   ponto122019 CHANGE COLUMN siape siape VARCHAR(12);

    ALTER TABLE   servativ_fotos CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   servidores_autorizacao CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   substituicao CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   tabdnu CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   turno_estendido CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   turno_estendido_historico CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   usuarios CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   vw_sisrefsae CHANGE COLUMN siape siape VARCHAR(12);
    ALTER TABLE   tabdnu CHANGE COLUMN siape_autorizado siape_autorizado VARCHAR(12);
    ALTER TABLE   config_suporte CHANGE COLUMN siape_registro siape_registro VARCHAR(12);
    ALTER TABLE   histlot CHANGE COLUMN siape_registro siape_registro VARCHAR(12);
    ALTER TABLE   historic CHANGE COLUMN siape_registro siape_registro VARCHAR(12);
    ALTER TABLE   jornada_historico CHANGE COLUMN siape_registro siape_registro VARCHAR(12);
    ALTER TABLE   liberacao_homologacao CHANGE COLUMN siape_registro siape_registro VARCHAR(12);
    ALTER TABLE   substituicao CHANGE COLUMN siape_registro siape_registro VARCHAR(12);
    ALTER TABLE   turno_estendido CHANGE COLUMN siape_registro siape_registro VARCHAR(12);
    ALTER TABLE   turno_estendido_historico CHANGE COLUMN siape_registro siape_registro VARCHAR(12);
    ALTER TABLE   control_historico CHANGE COLUMN siape_rh siape_rh VARCHAR(12);
    ALTER TABLE   histcad CHANGE COLUMN siapealt siapealt VARCHAR(12);
    ALTER TABLE   histponto012018 CHANGE COLUMN siapealt siapealt VARCHAR(12);
    ALTER TABLE   histponto022018 CHANGE COLUMN siapealt siapealt VARCHAR(12);
    ALTER TABLE   histponto032018 CHANGE COLUMN siapealt siapealt VARCHAR(12);
    ALTER TABLE   histponto042018 CHANGE COLUMN siapealt siapealt VARCHAR(12);
    ALTER TABLE   histponto052018 CHANGE COLUMN siapealt siapealt VARCHAR(12);
    ALTER TABLE   histponto062018 CHANGE COLUMN siapealt siapealt VARCHAR(12);
    ALTER TABLE   histponto072018 CHANGE COLUMN siapealt siapealt VARCHAR(12);
    ALTER TABLE   histponto082018 CHANGE COLUMN siapealt siapealt VARCHAR(12);
    ALTER TABLE   histponto092018 CHANGE COLUMN siapealt siapelt VARCHAR(12);
    ALTER TABLE   histponto102018 CHANGE COLUMN siapealt siapealt VARCHAR(12);
    ALTER TABLE   histponto112018 CHANGE COLUMN siapealt siapealt VARCHAR(12);
    ALTER TABLE   historico_observacoes CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   histponto012018 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   histponto022018 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   histponto032018 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   histponto042018 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   histponto052018 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   histponto062018 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   histponto072018 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   histponto082018 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   histponto092018 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   histponto102018 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   histponto112018 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   ponto012017 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   ponto012018 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   ponto022017 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   ponto022018 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   ponto032017 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   ponto032018 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   ponto042017 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   ponto052017 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   ponto052018 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   ponto062017 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   ponto062018 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   ponto072017 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   ponto072018 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   ponto082017 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   ponto082018 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   ponto092017 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   ponto092018 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   ponto102017 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   ponto102018 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   ponto112017 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   ponto112018 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   ponto122017 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   ponto122018 CHANGE COLUMN siaperh siaperh VARCHAR(12);
    ALTER TABLE   liberacao_acesso_especial CHANGE COLUMN solicitante_siape solicitante_siape VARCHAR(12);SET FOREIGN_KEY_CHECKS = 1; ");


    /* ALTER TABLE   histponto122018 CHANGE COLUMN siaperh siaperh VARCHAR(12);*/
    /* ALTER TABLE   histponto122018 CHANGE COLUMN siapealt siapealt VARCHAR(12);
       ALTER TABLE   _ocorrencias_88888_para_historico_antes_do_calculo CHANGE COLUMN siaperh siaperh VARCHAR(12);
       ALTER TABLE   _ocorrencias_88888_para_historico_antes_do_calculo CHANGE COLUMN siaperh siaperh VARCHAR(12);*/
    /* ALTER TABLE   liberupag CHANGE COLUMN siape_registro siape_registro VARCHAR(12);*/
    /* ALTER TABLE   liberupag CHANGE COLUMN siape_recebe siape_recebe VARCHAR(12);*/
    /* ALTER TABLE   histponto122018 CHANGE COLUMN siape siape VARCHAR(12);*/
    /* ALTER TABLE   _ocorrencias_88888_calculo_diferenca_2017 CHANGE COLUMN siape siape VARCHAR(12);
       ALTER TABLE   _ocorrencias_88888_calculo_diferenca_2018 CHANGE COLUMN siape siape VARCHAR(12);
       ALTER TABLE   _ocorrencias_88888_para_historico_antes_do_calculo CHANGE COLUMN siape siape VARCHAR(12);
       ALTER TABLE   _ocorrencias_88888_para_historico_antes_do_calculo CHANGE COLUMN siape siape VARCHAR(12);*/
    /* ALTER TABLE   histponto122018 CHANGE COLUMN matchef matchef VARCHAR(12);*/
    /* ALTER TABLE   servativ_bkp_20180301 CHANGE COLUMN mat_siape mat_siape VARCHAR(12);
       ALTER TABLE   servativ_bkp_20180312 CHANGE COLUMN mat_siape mat_siape VARCHAR(12);
       ALTER TABLE   _ocorrencias_88888_para_historico_antes_do_calculo CHANGE COLUMN matchef matchef VARCHAR(12);
       ALTER TABLE   _ocorrencias_88888_para_historico_antes_do_calculo CHANGE COLUMN matchef matchef VARCHAR(12);
    */

    return true;
}

/**
 * @param $mat
 * @return bool
 *
 *   Nome
 *   siapecad
 *   identificao nica
 *   nome social
 *   situao funcional,
 *   email
 *   cargo efetivo
 *   jornada do cargo
 *   regime jurdico
 *   admisso,
 *   unidade de exerccio
 *   ingresso na unidade
 *   localizao e ingresso na localizao.
 *   Nome
 *   siapecad
 *   identificao nica
 *   nome social
 *   situao funcional,
 *   email
 *   cargo efetivo
 *   jornada do cargo
 *   regime jurdico
 *   admisso,
 *   unidade de exerccio
 *   ingresso na unidade
 *   localizao e ingresso na localizao.
 *
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->codAtivFun                   . "]<br>"; //=>string ''               (length=0)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->codCargo                     . "]<br>"; //=>string '481004'         (length=6)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->codClasse                    . "]<br>"; //=>string 'B'              (length=1)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->codFuncao                    . "]<br>"; //=>string ''               (length=0)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->codJornada                   . "]<br>"; //=>string '40'             (length=2)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->codNovaFuncao                . "]<br>"; //=>string ''               (length=0)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->codOcorrAposentadoria        . "]<br>"; //=>string ''               (length=0)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->codOcorrExclusao             . "]<br>"; //=>string ''               (length=0)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->codOcorrIngressoOrgao        . "]<br>"; //=>string '01050'          (length=5)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->codOcorrIngressoServPublico  . "]<br>"; //=>string '01100'          (length=5)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->codOcorrIsencaoIR            . "]<br>"; //=>string ''               (length=0)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->codOcorrPSS                  . "]<br>"; //=>string ''               (length=0)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->codOrgao                     . "]<br>"; //=>string '17000'          (length=5)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->codPadrao                    . "]<br>"; //=>string 'IV'             (length=2)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->codSitFuncional              . "]<br>"; //=>string '01'             (length=2)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->codUorgExercicio             . "]<br>"; //=>string '000066682'      (length=9)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->codUorgLotacao               . "]<br>"; //=>string '000066682'      (length=9)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->codUpag                      . "]<br>"; //=>string '17000000067298' (length=14)                                                                             =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->codValeTransporte            . "]<br>"; //=>string '031'            (length=3)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->codigoOrgaoOrigem            . "]<br>"; //=>string '00000'          (length=5)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->cpfChefiaImediata            . "]<br>"; //=>string '72346000787'    (length=11)                                                                             =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->dataExercicioNoOrgao         . "]<br>"; //=>string '01022019'       (length=8)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->dataFimOcorrIsencaoIR        . "]<br>"; //=>string ''               (length=0)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->dataFimOcorrPSS              . "]<br>"; //=>string ''               (length=0)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->dataFimValeAR                . "]<br>"; //=>string ''               (length=0)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->dataIngressoFuncao           . "]<br>"; //=>string ''               (length=0)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->dataIngressoNovaFuncao       . "]<br>"; //=>string ''               (length=0)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->dataIniOcorrIsencaoIR        . "]<br>"; //=>string ''               (length=0)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->dataIniOcorrPSS              . "]<br>"; //=>string ''               (length=0)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->dataIniValeAR                . "]<br>"; //=>string '01022019'       (length=8)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->dataOcorrAposentadoria       . "]<br>"; //=>string ''               (length=0)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->dataOcorrExclusao            . "]<br>"; //=>string ''               (length=0)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->dataOcorrIngressoOrgao       . "]<br>"; //=>string '01022019'       (length=8)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->dataOcorrIngressoServPublico . "]<br>"; //=>string '14122009'       (length=8)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->emailChefiaImediata          . "]<br>"; //=>string 'carlos-augusto.silva@planejamento.gov.br' (length=40)                                  =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->emailServidor                . "]<br>"; //=>string 'NAUANACORREA@HOTMAIL.COM' (length=24)                                                  =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->identUnica                   . "]<br>"; //=>string '017448379'                (length=9)                                                                    =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->matriculaSiape               . "]<br>"; //=>string '1744837'                  (length=7)                                                                    =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->nomeAtivFun                  . "]<br>"; //=>string ''                         (length=0)                                                                    =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->nomeCargo                    . "]<br>"; //=>string 'AGENTE ADMINISTRATIVO'    (length=21)                                                          =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->nomeChefeUorg                . "]<br>"; //=>string 'CARLOS AUGUSTO SILVA'     (length=20)                                                                  =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->nomeClasse                   . "]<br>"; //=>string 'CLASSE B'                 (length=8)                                                                    =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->nomeFuncao                   . "]<br>"; //=>string ''                         (length=0)                                                                    =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->nomeJornada                  . "]<br>"; //=>string '40 HORAS SEMANAIS'        (length=17)                                                            =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->nomeNovaFuncao               . "]<br>"; //=>string ''                         (length=0)                                                                    =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->nomeOcorrAposentadoria       . "]<br>"; //=>string ''                         (length=0)                                                                    =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->nomeOcorrExclusao            . "]<br>"; //=>string ''                         (length=0)                                                                    =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->nomeOcorrIngressoOrgao       . "]<br>"; //=>string 'REFORMA ADMINISTRATIVA'   (length=22)                                                           =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->nomeOcorrIngressoServPublico . "]<br>"; //=>string 'NOMEACAO CARATER EFETIVO,ART.9,ITEM I ,LEI 8112/90' (length=50)                                          =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->nomeOcorrIsencaoIR           . "]<br>"; //=>string ''                         (length=0)                                                                    =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->nomeOcorrPSS                 . "]<br>"; //=>string ''                         (length=0)                                                                    =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->nomeOrgao                    . "]<br>"; //=>string 'MINISTERIO DA ECONOMIA'   (length=22)                                                                 =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->nomeRegimeJuridico           . "]<br>"; //=>string 'ESTATUTARIO'              (length=11)                                                                   =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->nomeSitFuncional             . "]<br>"; //=>string 'ATIVO PERMANENTE'         (length=16)                                                         =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->nomeUorgExercicio            . "]<br>"; //=>string 'CD GERAL DE GES DO PORTF DE PROJETOS' (length=36)                                                       =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->nomeUorgLotacao              . "]<br>"; //=>string 'CD GERAL DE GES DO PORTF DE PROJETOS' (length=36)                                                       =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->nomeUpag                     . "]<br>"; //=>string 'DIRETORIA DE GESTAO DE PESSOAS'       (length=30)                                                    =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->percentualTS                 . "]<br>"; //=>string '0,00'        (length=4)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->pontuacaoDesempenho          . "]<br>"; //=>string ''            (length=0)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->siglaNivelCargo              . "]<br>"; //=>string 'NI'          (length=2)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->siglaOrgao                   . "]<br>"; //=>string 'ME'          (length=2)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->siglaOrgaoOrigem             . "]<br>"; //=>string ''            (length=0)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->siglaRegimeJuridico          . "]<br>"; //=>string 'EST'         (length=3)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->siglaUorgExercicio           . "]<br>"; //=>string 'DGD-CGESP'   (length=9)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->siglaUorgLotacao             . "]<br>"; //=>string 'DGD-CGESP'   (length=9)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->siglaUpag                    . "]<br>"; //=>string 'SE-DGP'      (length=6)                                                                              =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->tipoValeAR                   . "]<br>"; //=>string 'ALIMENTACAO' (length=11)                                                                             =>
 *   $dadosFuncionais->dadosFuncionais->DadosFuncionais->valorValeTransporte          . "]<br>"; //=>string '7,00'        (length=4)
 */
function updateServerBySiape($mat, $exibe_erro = false)
{
    // instancia class
    $oTabServativ = new TabServativController();
    $oTabusuarios = new TabUsuariosController();


    $mat = getNovaMatriculaBySiape($mat);


    // DADOS DO CADASTRO - SERVATIV
    $result = $oTabServativ->updateServerCarregaDadosDoServativ( $mat );

    // CASO NO RETORNE VALOR DA CONSULTA
    if (empty($result->cpf))
    {
        return false;
    }

    $cpf               = $result->cpf;
    $orgao             = $result->orgao;
    $sitcad            = $result->cod_sitcad;
    $cad_email         = $result->email;
    $cad_defvis        = $result->defvis;
    $cad_ident_unica   = $result->ident_unica;
    $cad_nome_serv     = $result->nome_serv;
    $cad_pis_pasep     = $result->pis_pasep;
    $cad_jornada_cargo = $result->jornada_cargo;
    $cad_dt_nasc       = $result->dt_nasc;
    $cad_cod_sitcad    = $result->cod_sitcad;
    $cad_dt_adm        = $result->dt_adm;
    $cad_excluido      = $result->excluido;
    $cad_oco_exclu_oco = $result->oco_exclu_oco;
    $cad_oco_exclu_dt  = $result->oco_exclu_dt;
    $cad_reg_jur_at    = $result->reg_jur_at;
    $cad_desc_rj       = $result->desc_rj;
    $cad_cod_cargo     = $result->cod_cargo;
    $cad_cod_lot       = $result->cod_lot;
    $cad_sigregjur     = $result->sigregjur;
    $cad_reg_obito_dt  = $result->reg_obito_dt;

    // WEB SERVICE SIAPE
    if (!class_exists('SoapClient')) {
        return false;
    }

    $obj = new Siape();


    // RECUPERA OS DADOS PESSOAIS
    $dadosPessoais = $obj->buscarDadosPessoais($cpf, $orgao);

    // VALIDA O RETORNO DA API - DADOS PESSOAIS
    if (!is_object($dadosPessoais))
    {
        return false;
    }

    $nome           = $dadosPessoais->nome; // usuarios // serativ
    $cod_def_fisica = $dadosPessoais->codDefFisica; // serativ
    $pis_pasep      = $dadosPessoais->numPisPasep; // serativ
    $data_nasc      = $dadosPessoais->dataNascimento; // serativ // usuario


    // RECUPERA OS DADOS FUNCIONAIS
    $dadosFuncionais = $obj->buscarDadosFuncionais($cpf, $orgao);

    // VALIDA O RETORNO DA API - DADOS FUNCIONAIS
    if (!is_object($dadosFuncionais))
    {
        return false;
    }

    $data_adm              = $dadosFuncionais->dadosFuncionais->DadosFuncionais->dataOcorrIngressoOrgao; //serativ
    $identificacao_unica   = $dadosFuncionais->dadosFuncionais->DadosFuncionais->identUnica;  // serativ
    //$situacao_funcional = $dadosFuncionais->dadosFuncionais->DadosFuncionais->dataOcorrExclusao ? '02' : $dadosFuncionais->dadosFuncionais->DadosFuncionais->codSitFuncional; // serativ
    $email                 = $dadosFuncionais->dadosFuncionais->DadosFuncionais->emailServidor; // serativ
    $cargo_efetivo         = $dadosFuncionais->dadosFuncionais->DadosFuncionais->codCargo;// serativ
    $siglaRegimeJuridico   = $dadosFuncionais->dadosFuncionais->DadosFuncionais->siglaRegimeJuridico; // serativ
    $jornada_cod           = $dadosFuncionais->dadosFuncionais->DadosFuncionais->codJornada;// serativ
    $cod_uorg_exercicio    = $dadosFuncionais->dadosFuncionais->DadosFuncionais->codUorgExercicio;// serativ
    $cod_upag              = $dadosFuncionais->dadosFuncionais->DadosFuncionais->codUpag;// serativ
    $codOrgaoUorgExercicio = $dadosFuncionais->dadosFuncionais->DadosFuncionais->codOrgao.$dadosFuncionais->dadosFuncionais->DadosFuncionais->codUorgExercicio;
    $excluido              = ($dadosFuncionais->dadosFuncionais->DadosFuncionais->dataOcorrExclusao || $dadosFuncionais->dadosFuncionais->DadosFuncionais->dataOcorrAposentadoria ? 'S' : 'N');
    
    if ($dadosFuncionais->dadosFuncionais->DadosFuncionais->dataOcorrExclusao)
    {
        $situacao_funcional = $dadosFuncionais->dadosFuncionais->DadosFuncionais->codSitFuncional;
        $codOcorrExclusao   = $dadosFuncionais->dadosFuncionais->DadosFuncionais->codOcorrExclusao;
        $dataOcorrExclusao  = $dadosFuncionais->dadosFuncionais->DadosFuncionais->dataOcorrExclusao;
    }
    else if ($dadosFuncionais->dadosFuncionais->DadosFuncionais->dataOcorrAposentadoria)
    {
        $situacao_funcional = $dadosFuncionais->dadosFuncionais->DadosFuncionais->codOcorrAposentadoria ? '02' : $dadosFuncionais->dadosFuncionais->DadosFuncionais->codSitFuncional; // serativ
        $codOcorrExclusao   = $dadosFuncionais->dadosFuncionais->DadosFuncionais->codOcorrAposentadoria;
        $dataOcorrExclusao  = $dadosFuncionais->dadosFuncionais->DadosFuncionais->dataOcorrAposentadoria;
    }
    else if ($dadosFuncionais->dadosFuncionais->DadosFuncionais->nomeSitFuncional == 'INSTITUIDOR PENSAO')
    {
        $situacao_funcional = '15';
        $codOcorrExclusao   = $dadosFuncionais->dadosFuncionais->DadosFuncionais->codOcorrExclusao;
        $dataOcorrExclusao  = $dadosFuncionais->dadosFuncionais->DadosFuncionais->dataOcorrExclusao;
        $dataRegObito       = $dadosFuncionais->dadosFuncionais->DadosFuncionais->dataOcorrExclusao;
    }
    
    
    // verifica se há valores válidos retornados
    $email                 = (is_null($email)                 || empty(trim($email))                 ? $cad_email         : $email);
    $identificacao_unica   = (is_null($identificacao_unica)   || empty(trim($identificacao_unica))   ? $cad_ident_unica   : $identificacao_unica);
    $nome                  = (is_null($nome)                  || empty(trim($nome))                  ? $cad_nome_serv     : $nome);
    $pis_pasep             = (is_null($pis_pasep)             || empty(trim($pis_pasep))             ? $cad_pis_pasep     : $pis_pasep);
    $jornada_cod           = (is_null($jornada_cod)           || empty(trim($jornada_cod))           ? $cad_jornada_cargo : $jornada_cod);
    $data_nasc             = (is_null($data_nasc)             || empty(trim($data_nasc))             ? $cad_dt_nasc       : dataWSSiapeMySQL($data_nasc));
    $data_adm              = (is_null($data_adm)              || empty(trim($data_adm))              ? $cad_dt_adm        : dataWSSiapeMySQL($data_adm));
    $excluido              = (is_null($excluido)              || empty(trim($excluido))              ? $cad_excluido      : $excluido);
    $codOcorrExclusao      = (is_null($codOcorrExclusao)      || empty(trim($codOcorrExclusao))      ? $cad_oco_exclu_oco : $codOcorrExclusao);
    $dataOcorrExclusao     = (is_null($dataOcorrExclusao)     || empty(trim($dataOcorrExclusao))     ? $cad_oco_exclu_dt  : dataWSSiapeMySQL($dataOcorrExclusao));
    $siglaRegimeJuridico   = (is_null($siglaRegimeJuridico)   || empty(trim($siglaRegimeJuridico))   ? $cad_sigregjur     : $siglaRegimeJuridico);
    $cargo_efetivo         = (is_null($cargo_efetivo)         || empty(trim($cargo_efetivo))         ? $cad_cod_cargo     : $cargo_efetivo);
    $codOrgaoUorgExercicio = (is_null($codOrgaoUorgExercicio) || empty(trim($codOrgaoUorgExercicio)) ? $cad_cod_lot       : $codOrgaoUorgExercicio);
    $dataRegObito          = (is_null($dataRegObito)          || empty(trim($dataRegObito))          ? $cad_reg_obito_dt  : dataWSSiapeMySQL($dataRegObito));

    
    // SITUAÇÃO FUNCIONAL
    $situacao_funcional = (is_null($situacao_funcional) || empty(trim($situacao_funcional) || !is_string($situacao_funcional)) ? $cad_cod_sitcad : $situacao_funcional);

    if (!is_string($situacao_funcional) || empty(trim($situacao_funcional)))
    {
        $situacao_funcional = $cad_cod_sitcad;
    }


    // DEFICIENTE VISUAL
    if ($cad_defvis == "S")
    {
        $cod_def_fisica = $cad_defvis;
    }
    else
    {
        $cod_def_fisica = (is_null($cod_def_fisica) || empty(trim($cod_def_fisica) || !is_string($cod_def_fisica)) ? $cad_defvis : substr($cod_def_fisica,0,1));

        if ($cod_def_fisica !== "N" && $cod_def_fisica !== "S")
        {
            $cod_def_fisica = "N";
        }
    }

    $oDBase = new DataBase('PDO');

    //ATUALIZA A TABLE SERVATIV E USUARIOS
    $oDBase->setMensagem("Problemas no acesso a Tabela de REGIME JURÍDICO (E000085.".__LINE__.")");
    $oDBase->query("SELECT tabregime.cod_rj FROM tabregime WHERE tabregime.desc_rj = :desc_rj ", array(
        array( ':desc_rj', $siglaRegimeJuridico, PDO::PARAM_STR ),
    ));
    $oTabRegime = $oDBase->fetch_object();
    $cod_regime_juridico = $oTabRegime->cod_rj;
    $cod_rej = ($oDBase->num_rows() ? $cad_reg_jur_at : $cod_regime_juridico);


    /* Essa parte atualiza a lotação do usuário */

    $oDBase2 = new DataBase('PDO');
    $oDBase2->setMensagem("Problemas no acesso a Tabela de UNIDADES (E000085.".__LINE__.")");
    $oDBase2->query("SELECT codigo, upag FROM tabsetor WHERE tabsetor.codigo = :codigo", array(
        array( ":codigo", $codOrgaoUorgExercicio, PDO::PARAM_STR )
    ));

    $result2 = $oDBase2->fetch_object();

    if ($result2) {
        $oDBase->setMensagem("Problemas no acesso a Tabela do CADASTRO (E000085.".__LINE__.")");
        $oDBase->query("SELECT servativ.chefia, servativ.cod_lot FROM servativ WHERE mat_siape = :siape ", array(
            array( ":siape", $mat, PDO::PARAM_STR )
        ));
        $estadoServidor = $oDBase->fetch_object();

        if ($estadoServidor->chefia == 'N' && $estadoServidor->cod_lot != $result2->codigo) {
            $oDBase->setMensagem("Erro na atualização do CADASTRO (E000085.".__LINE__.")");
            $oDBase->query("UPDATE servativ SET cod_loc = :cod_loc , cod_lot = :cod_lot, cod_uorg = :cod_uorg WHERE mat_siape = :siape ", array(
                array( ":cod_loc",   $result2->codigo, PDO::PARAM_STR ),
                array( ":cod_lot",   $result2->codigo, PDO::PARAM_STR ),
                array( ":cod_uorg",  $result2->codigo, PDO::PARAM_STR ),
                array( ":siape",     $mat,             PDO::PARAM_STR ),
            ));

            $oDBase->setMensagem("Erro na atualização do USUÁRIO (E000085.".__LINE__.")");
            $oDBase->query("UPDATE usuarios SET setor = :setor, upag = :upag WHERE siape = :siape ", array(
                array( ":setor", $result2->codigo, PDO::PARAM_STR ),
                array( ":upag",  $result2->upag,   PDO::PARAM_STR ),
                array( ":siape", $mat,             PDO::PARAM_STR ),
            ));

            $oDBase->setMensagem("Erro na atualização do HISTÓRICO DE MOVIMENTAÇÃO (E000085.".__LINE__.")");
            $oDBase->query("
                INSERT INTO histlot SET
                    siape          = :siape,
                    cod_lot        = :cod_lot,
                    dt_ing_lot     = NOW(),
                    cod_loc        = :cod_loc,
                    dt_ing_loc     = NOW(),
                    cod_uorg       = :cod_uorg,
                    cod_uorg_loc   = :cod_uorg_loc,
                    siape_registro = 'WSIAPE',
                    data_registro  = NOW(),
                    siape_alterou  = '',
                    data_alterou   = '0000-00-00 00:00:00'
            ", array(
                array( ":siape",        $mat,             PDO::PARAM_STR ),
                array( ":cod_lot",      $result2->codigo, PDO::PARAM_STR ),
                array( ":cod_loc",      $result2->codigo, PDO::PARAM_STR ),
                array( ":cod_uorg",     $result2->codigo, PDO::PARAM_STR ),
                array( ":cod_uorg_loc", $result2->codigo, PDO::PARAM_STR ),
            ));
        }
    }

    /* Essa parte finaliza */


    /* ************************************************* *
     *
     *                ATUALIZA O CADASTRO
     *
     * ************************************************* *
     */
    $dados_cadastro = array();

    /* EMAIL               */ verifyDadosUpdateServerBySiape($dados_cadastro, $result, 'email',                $email );
    /* DEFICIENTE VISUAL   */ verifyDadosUpdateServerBySiape($dados_cadastro, $result, 'defvis',               $cod_def_fisica );
    /* IDENTIFICACAO UNICA */ verifyDadosUpdateServerBySiape($dados_cadastro, $result, 'ident_unica',          $identificacao_unica );
    /* NOME                */ verifyDadosUpdateServerBySiape($dados_cadastro, $result, 'nome_serv',            $nome );
    /* PIS/PASEP           */ verifyDadosUpdateServerBySiape($dados_cadastro, $result, 'pis_pasep',            $pis_pasep );
    /* JORNADA DO CARGO    */ verifyDadosUpdateServerBySiape($dados_cadastro, $result, 'jornada_cargo',        $jornada_cod );
    /* DATA  NASCIMENTO    */ verifyDadosUpdateServerBySiape($dados_cadastro, $result, 'dt_nasc',              conv_data($data_nasc) );
    /* SITUACAO FUNCIONAL  */ verifyDadosUpdateServerBySiape($dados_cadastro, $result, 'cod_sitcad',           $situacao_funcional );
    /* SITUACAO FUNCIONAL  */ verifyDadosUpdateServerBySiape($dados_cadastro, $result, 'cod_serv',             $situacao_funcional );
    /* DATA DE ADMISSAO    */ verifyDadosUpdateServerBySiape($dados_cadastro, $result, 'dt_adm',               conv_data($data_adm) );
    /* EXCLUIDO            */ verifyDadosUpdateServerBySiape($dados_cadastro, $result, 'excluido',             $excluido );
    /* DATA OBITO          */ verifyDadosUpdateServerBySiape($dados_cadastro, $result, 'reg_obito_dt',         $dataRegObito );
    /* CODIGO DE EXC SIAPE */ verifyDadosUpdateServerBySiape($dados_cadastro, $result, 'oco_exclu_oco',        $codOcorrExclusao );
    /* DATA DA EXC SIAPE   */ verifyDadosUpdateServerBySiape($dados_cadastro, $result, 'oco_exclu_dt',         conv_data($dataOcorrExclusao) );
    /* DIPLOMA LEGAL EXC   */ verifyDadosUpdateServerBySiape($dados_cadastro, $result, 'oco_exclu_dl_cod',     '' );
    /* DIPLOMA LEGAL NUM   */ verifyDadosUpdateServerBySiape($dados_cadastro, $result, 'oco_exclu_dl_num',     '' );
    /* DIPLOMA LEGAL DATA  */ verifyDadosUpdateServerBySiape($dados_cadastro, $result, 'oco_exclu_dl_dt_publ', '0000-00-00' );
    /* COD/DESCR REG JUR   */ verifyDadosUpdateServerBySiape($dados_cadastro, $result, 'reg_jur_at',           $cod_rej );
    /* CODIGO DO CARGO     */ verifyDadosUpdateServerBySiape($dados_cadastro, $result, 'cod_cargo',            $cargo_efetivo );
    /* SIGLA REG JUR       */ verifyDadosUpdateServerBySiape($dados_cadastro, $result, 'sigregjur',            $siglaRegimeJuridico );

    $oTabServativ->updateServerAtualizarServativ( $mat, $dados_cadastro );
    
    /* ************************************************* *
     *
     * ATUALIZA USUARIOS
     * ATUALIZA FUNÇÕES
     * ATUALIZA HISTÓRICO DE FUNÇÕES
     *
     * ************************************************* *
     */
    if ($result->excluido !== $excluido && $excluido === 'S')
    {
        // VERIFICA SE OCUPANTE DE FUNÇÕES - dados da função (se ocupante)
        $ocupante_rows = getOcupantesPorID($mat);

        if ($ocupante_rows > 0)
        {
            // ATUALIZA DADOS - OCUPANTE DE FUNÇÕES - HISTÓRICO
            updateHistoricoOcupantesPorID($mat, $dataOcorrExclusao, $situacao_funcional);

            // INSERIR DADOS -  OCUPANTE DE FUNÇÕES - HISTÓRICO
            insertHistoricoOcupantesPorID($mat, $dataOcorrExclusao, $situacao_funcional);

            // ATUALIZA DADOS - OCUPANTE DE FUNÇÕES - HISTÓRICO 
            deleteOcupantesPorID($mat);
        }
    
        // GRAVA EXCLUSAO SERVATIV
        insertExclusaoPorID($mat, $situacao_funcional, $codOcorrExclusao, $dataOcorrExclusao);

        // DESATIVA USUÁRIOS - desativa usuario trocando a senha atual
        desativaUsuariosPorID($mat);
    }
    else
    {
        // ATUALIZA USUARIOS
        $oDBase->setMensagem("Erro na atualização do USUÁRIOS (E000085.".__LINE__.")");
        $oDBase->query("UPDATE usuarios SET nome = :nome WHERE siape = :siape ", array(
            array( ":siape", $mat,  PDO::PARAM_STR ),
            array( ":nome",  $nome, PDO::PARAM_STR ),
        ));
    }

    return true;
}


/**
 * @info Verifica os dados informados
 *
 * @param array $array
 * @param string $old
 * @param array $new
 * @return array
 */
function verifyDadosUpdateServerBySiape(&$array, $result=null, $campo=null, $new=null)
{
    if (!is_null($campo) && !empty($campo) && !is_null($new) && !empty($new) && !is_null($result) && $result->{$campo} !== $new)
    {
        $array += array( $campo => $new );
    }
}


/**
 * @info VERIFICA SE OCUPANTE DE FUNÇÕES
 *
 * @param string $mat
 * @return integer
 */
function getOcupantesPorID($mat = null)
{
    $oDBase = new DataBase();
    $oDBase->setMensagem("Problema no acesso a Tabela OCUPANTES DE FUNÇÃO (E000130.".__LINE__.")");
    $oDBase->query( "
    SELECT
        id,
        SIT_OCUP,
        NUM_FUNCAO
    FROM
        ocupantes
    WHERE
        mat_siape = :mat_siape
        AND dt_fim = '0000-00-00'
    ", array(
        array( ':mat_siape', $mat, PDO::PARAM_STR )
    ));

    $affected_rows = $oDBase->affected_rows();

    return $affected_rows;
}


/**
 * @info ATUALIZA DADOS : OCUPANTE DE FUNÇÕES - HISTÓRICO
 *
 * @param string $mat
 * @param string $data_de_exclusao
 * @param string $situacao_cadastral
 * @return integer
 */
function updateHistoricoOcupantesPorID($mat = null, $data_de_exclusao = null, $situacao_cadastral = null)
{
    $oDBase = new DataBase();
    $oDBase->setMensagem("Erro na atualização da Tabela de HISTÓRICO FUNÇÕES (E000130.".__LINE__.")");
    $oDBase->query( "
    UPDATE historic
    SET
        dt_fim         = :dt_fim,
        cod_serv       = :cod_serv,
        siape_registro = :usuario
    WHERE
        mat_siape = :mat_siape
        AND dt_fim = '0000-00-00'
    ", array(
        array( ':mat_siape', $mat,                    PDO::PARAM_STR ),
        array( ':dt_fim',    $data_de_exclusao,       PDO::PARAM_STR ),
        array( ':usuario',   $_SESSION['sMatricula'], PDO::PARAM_STR ),
        array( ':cod_serv',  $situacao_cadastral,     PDO::PARAM_STR )
    ));

    $affected_rows = $oDBase->affected_rows();

    return $affected_rows;
}


/**
 * @info INSERIR DADOS : OCUPANTE DE FUNÇÕES - HISTÓRICO
 *
 * @param string $mat
 * @param string $data_de_exclusao
 * @param string $situacao_cadastral
 * @return integer
 */
function insertHistoricoOcupantesPorID($mat = null, $data_de_exclusao = null, $situacao_cadastral = null)
{
    $oDBase = new DataBase();
    $oDBase->setMensagem("Erro na atualização da Tabela de HISTÓRICO FUNÇÕES (E000130.".__LINE__.")");
    $oDBase->query( "
    INSERT INTO historic
    SELECT
        mat_siape, nome_serv, sit_ocup, num_funcao, resp_lot, cod_doc1,
        num_doc1, dt_doc1, cod_doc2, num_doc2, dt_doc2, cod_doc3, num_doc3,
        dt_doc3, cod_doc4, num_doc4, dt_doc4, dt_altera, dt_inicio, :dt_fim,
        :cod_serv, dt_atual, decir, dtdecir, :usuario, NOW()
    FROM
        ocupantes
    WHERE
        mat_siape = :mat_siape
        AND dt_fim = '0000-00-00'
    ", array(
        array( ':mat_siape', $mat,                    PDO::PARAM_STR ),
        array( ':dt_fim',    $data_de_exclusao,       PDO::PARAM_STR ),
        array( ':usuario',   $_SESSION['sMatricula'], PDO::PARAM_STR ),
        array( ':cod_serv',  $situacao_cadastral,     PDO::PARAM_STR )
    ));

    $affected_rows = $oDBase->affected_rows();

    return $affected_rows;
}


/**
 * @info APAGAR DADOS : OCUPANTE DE FUNÇÕES
 *
 * @param string $mat
 * @return integer
 */
function deleteOcupantesPorID($mat = null)
{
    $oDBase = new DataBase();
    $oDBase->setMensagem("Erro na atualização da Tabela de OCUPANTES DE FUNÇÃO (E000130.".__LINE__.")");
    $oDBase->query( "
    DELETE FROM ocupantes
    WHERE
        mat_siape = :mat_siape
        AND dt_fim = '0000-00-00'
    ", array(
        array( ':mat_siape', $mat, PDO::PARAM_STR ),
    ));

    $affected_rows = $oDBase->affected_rows();

    return $affected_rows;
}


/**
 * @info Registra exclusão do registro no EXCLUS
 * 
 * @param string $mat
 * @param string $situacao_cadastral
 * @param string $codigo_de_exclusao
 * @param string $data_de_exclusao
 * @return integer
 */
function insertExclusaoPorID($mat = null, $situacao_cadastral = null, $codigo_de_exclusao = null, $data_de_exclusao = null)
{
    $oDBase = new DataBase();
    $oDBase->setMensagem("Erro na exclusão da Tabela de SERVATIV (matrícula ".$mat.") (E000130.".__LINE__.")");
    $oDBase->query("
    INSERT INTO
        exclus
    SET
        siape     = :siape,
        cod_serv  = :cod_serv,
        cod_ocorr = :oco_exclu_oco,
        dt_ocorr  = :oco_exclu_dt,
        tp_doc    = '',
        num_doc   = '',
        dt_doc    = '0000-00-00',
        cartorio  = '',
        dt_obito  = '0000-00-00',
        reg_obito = '',
        fol_obito = '',
        liv_obito = '',
        cod_orgao = '',
        dt_exped  = :oco_exclu_dt
    ",
    array(
        array( ':siape',         $mat,                PDO::PARAM_STR ),
        array( ':cod_serv',      $situacao_cadastral, PDO::PARAM_STR ),
        array( ':oco_exclu_oco', $codigo_de_exclusao, PDO::PARAM_STR ),
        array( ':oco_exclu_dt',  $data_de_exclusao,   PDO::PARAM_STR ),
    ));

    $affected_rows = $oDBase->affected_rows();

    return $affected_rows;
}


/**
 * @info Desativa usuários trocando a senha atual
 * 
 * @param string $mat
 * @return integer
 */
function desativaUsuariosPorID($mat)
{
    $oDBase = new DataBase();
    $oDBase->setMensagem("Erro na atualização da Tabela USUÁRIOS (E000130.".__LINE__.")");
    $oDBase->query("
    UPDATE
        usuarios
    SET
        senha = 'e11abc3849bc57'
    WHERE
        siape = :siape
    ",
    array(
        array( ':siape', $mat, PDO::PARAM_STR ),
    ));

    $affected_rows = $oDBase->affected_rows();

    return $affected_rows;
}
