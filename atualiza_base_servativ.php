<?php

/*
 * Atualiza as tabelas CADMES (SIAPE) no SISREF.
 *   - Tabela cadmes, atualiza dados cadastrais no SISREF;
 */

include_once( '../../sisref/class_database.php' );
include_once( '../../sisref/inc/email_lib.php' );
include_once( '../../sisref/config.php' );

set_time_limit(108000);

/*
 * ATUALIZA SISREF/SERVATIV com os principais dados do SIAPECAD/CADMES
 */
$objAtualizaServativ = new AtualizaServativ();
$objAtualizaServativ->executa();

/*
 * ATUALIZA campo EMAIL do SISREF/SERVATIV
 * a partir dos dados do LDAP
 */
$objAtualizaEmailPorLDAP = new AtualizaEmailPorLDAP();
$objAtualizaEmailPorLDAP->executa();

/*
 * APAGA TODOS OS ARQUIVOS "historico_temp_???????"
 */
$objApagaTabelasHistoricoTemp = new ApagaTabelasHistoricoTemp();
$objApagaTabelasHistoricoTemp->executa();

exit();

/**  @Class
 * +--------------------------------------------------------------------+
 * | @Class       : ConexaoBD                                           |
 * | @description : Conecta-se ao banco de dados                        |
 * | Autor     : Edinalvo Rosa                                          |
 * +--------------------------------------------------------------------+
 * */
class ConexaoBD
{

    public $linkSISREF;

    public function __construct()
    {
        // Conexão com o banco de dados SISREF
        $this->linkSISREF = new mysqli('10.120.2.5', 'sisref_crontab', 'SisReF2015crontab', 'siapecad');
        if ($this->linkSISREF->connect_error)
        {
            comunicaErro("Falha na conexão com Banco de Dados:<br> (" . $this->linkSISREF->connect_errno . ') ' . $this->linkSISREF->connect_error);
        }

    }

}

/**  @Class
 * +--------------------------------------------------------------------+
 * | @Class    : AtualizaServativ                                       |
 * | @description : Atualiza o cadastro SISREF/SERVATIV a partir do     |
 * |                cadastro SIAPECAD/CADMES (SIAPE)                    |
 * | Autor     : Edinalvo Rosa                                          |
 * +--------------------------------------------------------------------+
 * */
class AtualizaServativ extends ConexaoBD
{

    private $conexao;

    public function __construct()
    {
        // Conexão com o banco de dados SISREF
        $this->conexao = new ConexaoBD();

    }

    public function executa()
    {
        // monta seleção dos dados SIAPE
        $sql = "
		SELECT
			cad.siape,
			cad.nome,
			cad.cpf,
			cad.pis_pasep,
			cad.mae,
			cad.sexo,
			cad.cod_sit_serv,
			cad.nivel,
			cad.dt_nasc,
			cad.id_origem,
			cad.sig_reg_jur,
			cad.x_cat_fun,
			cad.cargemp_classe,
			cad.cargemp_refnivpad,
			cad.funcao_sig,
			cad.funcao_cod_nivel,
			CONCAT(cad.oco_ingorg_grp,cad.oco_ingorg_oco) AS fingorg,
			cad.oco_ingorg_dt,
			cad.oco_ingorg_dl_cod,
			cad.oco_ingorg_dl_num,
			cad.oco_ingorg_dl_dt_publ,
			CONCAT(cad.oco_exclu_grp,cad.oco_exclu_oco) AS ocoexclui,
			IF(IFNULL(cad.oco_exclu_dt,'0000-00-00')='0000-00-00',IFNULL(cad.oco_inat_dt,'0000-00-00'),cad.oco_exclu_dt) AS oco_exclu_dt,
			cad.oco_exclu_dl_cod,
			cad.oco_exclu_dl_num,
			cad.oco_exclu_dl_dt_publ,
			cad.oco_afast_grp,
			cad.oco_afast_oco,
			cad.oco_afast_dt_ini,
			cad.oco_afast_dt_term,
			cad.reg_obito_num_registro,
			cad.reg_obito_dt,
			cad.id_unica
		FROM
			siapecad.cadmes AS cad
		ORDER BY
			cad.siape
		";

        if ($rsSIAPE = $this->conexao->linkSISREF->query($sql))
        {
            $contar = 0;

            // extraindo registros do SIAPE
            while ($cadSiape = $rsSIAPE->fetch_assoc())
            {
                $wMatricula_siape = $cadSiape['siape'];

                $digito_verificador   = valDVmod11('0' . $wMatricula_siape);
                $wIdentificacao_unica = '0' . $wMatricula_siape . $digito_verificador;

                $wCpf   = $cadSiape['cpf'];
                $wSexo  = $cadSiape['sexo'];
                $wCargo = $cadSiape['x_cat_fun'];
                $wNivel = $cadSiape['nivel'];

                $wNivel = ($cadSiape['cod_sit_serv'] != "66" ? $cadSiape['nivel'] : ($cadSiape['funcao_cod_nivel'] == "00001" ? 'NS' : 'NI'));

                $wClasse    = $cadSiape['cargemp_classe'];
                $wPadrao    = strtr(ltrim(rtrim($cadSiape['cargemp_refnivpad'])), array("0" => ""));
                $wPis_pasep = $cadSiape['pis_pasep'];

                $wObito_data                        = $cadSiape['reg_obito_dt'];
                $wMae_servidor                      = strtr(ltrim(rtrim($cadSiape['mae'])), array("'" => "`", '"' => "`"));
                ;
                $wFuncao_sigla                      = $cadSiape['funcao_sig'];
                $wFuncao_codigo                     = $cadSiape['funcao_cod_nivel'];
                $wExclusao_data                     = $cadSiape['oco_exclu_dt'];
                $wNome_servidor                     = strtr(ltrim(rtrim($cadSiape['nome'])), array("'" => "`", '"' => "`"));
                $wData_nascimento                   = $cadSiape['dt_nasc'];
                $wExclusao_codigo                   = $cadSiape['ocoexclui'];
                $wMatricula_siapecad                = $cadSiape['id_origem'];
                $wSituacao_cadastral                = $cadSiape['cod_sit_serv'];
                $wIngresso_orgao_data               = $cadSiape['oco_ingorg_dt'];
                $wIngresso_orgao_forma              = $cadSiape['fingorg'];
                $wSigla_regime_juridico             = $cadSiape['sig_reg_jur'];
                $wExclusao_diploma_legal            = $cadSiape['oco_exclu_dl_cod'];
                $wExclusao_diploma_legal_num        = $cadSiape['oco_exclu_dl_num'];
                $wExclusao_diploma_legal_data       = $cadSiape['oco_exclu_dl_dt_publ'];
                $wIngresso_orgao_diploma_legal      = $cadSiape['oco_ingorg_dl_cod'];
                $wIngresso_orgao_diploma_legal_num  = strtr(ltrim(rtrim($cadSiape['oco_ingorg_dl_num'])), array("'" => "`", '"' => "`"));
                $wIngresso_orgao_diploma_legal_data = $cadSiape['oco_ingorg_dl_dt_publ'];

                // testa se ja existe
                $sql = '
				SELECT
					cad.mat_siape, cad.nome_serv, cad.cpf, cad.pis_pasep, cad.mae, cad.sexo,
					cad.cod_sitcad, cad.nivel, cad.dt_nasc, cad.sigregjur,
					cad.cod_cargo, cad.cod_classe, cad.cod_padrao, cad.nfunc, cad.dt_adm,
					cad.cod_serv, cad.dipl_inss, cad.n_sip_inss, cad.dt_dip_ins,
					cad.ident_unica, cad.mat_siapecad, cad.reg_obito_dt, cad.oco_exclu_dt,
					cad.oco_exclu_oco, cad.oco_exclu_dl_cod, cad.oco_exclu_dl_num,
					cad.oco_exclu_dl_dt_publ, cad.f_ing_org
				FROM
					servativ AS cad
				WHERE
					cad.mat_siape = "' . $wMatricula_siape . '"
					AND (
						cpf != "' . $wCpf . '" OR
						mae != "' . $wMae_servidor . '" OR
						sexo != "' . $wSexo . '" OR
						nivel != "' . $wNivel . '" OR
						nfunc != "' . $wFuncao_codigo . '" OR
						dt_adm != "' . $wIngresso_orgao_data . '" OR
						dt_nasc != "' . $wData_nascimento . '" OR
						cod_serv != "' . $wSituacao_cadastral . '" OR
						mat_siape != "' . $wMatricula_siape . '" OR
						nome_serv != "' . $wNome_servidor . '" OR
						pis_pasep != "' . $wPis_pasep . '" OR
						sigregjur != "' . $wSigla_regime_juridico . '" OR
						dipl_inss != "' . $wIngresso_orgao_diploma_legal . '" OR
						cod_cargo != "' . $wCargo . '" OR
						f_ing_org != "' . $wIngresso_orgao_forma . '" OR
						n_sip_inss != "' . $wIngresso_orgao_diploma_legal_num . '" OR
						cod_classe != "' . $wClasse . '" OR
						cod_padrao != "' . $wPadrao . '" OR
						dt_dip_ins != "' . $wIngresso_orgao_diploma_legal_data . '" OR
						cod_sitcad != "' . $wSituacao_cadastral . '" OR
						ident_unica != "' . $wIdentificacao_unica . '" OR
						mat_siapecad != "' . $wMatricula_siapecad . '" OR
						reg_obito_dt != "' . $wObito_data . '" OR
						oco_exclu_dt != "' . $wExclusao_data . '" OR
						oco_exclu_oco != "' . $wExclusao_codigo . '" OR
						oco_exclu_dl_cod != "' . $wExclusao_diploma_legal . '" OR
						oco_exclu_dl_num != "' . $wExclusao_diploma_legal_num . '" OR
						oco_exclu_dl_dt_publ != "' . $wExclusao_diploma_legal_data . '"
					)
				';

                if ($rsSISREF = $this->conexao->linkSISREF->query($sql))
                {
                    if ($rsSISREF->num_rows > 0)
                    {
                        $cadSisref = $rsSISREF->fetch_assoc();

                        // situacao cadastral
                        if (($wSituacao_cadastral == '') || ($wSituacao_cadastral == '00') || ($wSituacao_cadastral == '01' && $cadSisref['cod_sitcad'] == '08') || ($wSituacao_cadastral == '08' && $cadSisref['cod_sitcad'] == '01') || ($wSituacao_cadastral == '02' && $cadSisref['cod_sitcad'] == '01'))
                        {
                            $wSituacao_cadastral = $cadSisref['cod_sitcad'];
                        }

                        //echo "Atualizando -> siape $wMatricula_siape<br>";
                        $campos = '';
                        $campos .= ($cadSisref['cpf'] != $wCpf && !empty($wCpf) ? "cpf = '" . $wCpf . "', " : "");
                        $campos .= ($cadSisref['mae'] != $wMae_servidor && !empty($wMae_servidor) ? "mae = '" . $wMae_servidor . "', " : "");
                        $campos .= ($cadSisref['sexo'] != $wSexo && !empty($wSexo) ? "sexo = '" . $wSexo . "', " : "");
                        $campos .= ($cadSisref['nivel'] != $wNivel && !empty($wNivel) ? "nivel = '" . $wNivel . "', " : "");
                        $campos .= ($cadSisref['nfunc'] != $wFuncao_codigo && !empty($wFuncao_codigo) ? "nfunc = '" . $wFuncao_codigo . "', " : "");
                        $campos .= ($cadSisref['dt_adm'] != $wIngresso_orgao_data && !empty($wIngresso_orgao_data) ? "dt_adm = '" . $wIngresso_orgao_data . "', " : "");
                        $campos .= ($cadSisref['dt_nasc'] != $wData_nascimento && !empty($wData_nascimento) ? "dt_nasc = '" . $wData_nascimento . "', " : "");
                        $campos .= ($cadSisref['cod_serv'] != $wSituacao_cadastral && !empty($wSituacao_cadastral) ? "cod_serv = '" . $wSituacao_cadastral . "', " : "");
                        $campos .= ($cadSisref['mat_siape'] != $wMatricula_siape && !empty($wMatricula_siape) ? "mat_siape = '" . $wMatricula_siape . "', " : "");
                        $campos .= ($cadSisref['nome_serv'] != $wNome_servidor && !empty($wNome_servidor) ? "nome_serv = '" . $wNome_servidor . "', " : "");
                        $campos .= ($cadSisref['pis_pasep'] != $wPis_pasep && !empty($wPis_pasep) ? "pis_pasep = '" . $wPis_pasep . "', " : "");
                        $campos .= ($cadSisref['sigregjur'] != $wSigla_regime_juridico && !empty($wSigla_regime_juridico) ? "sigregjur = '" . $wSigla_regime_juridico . "', " : "");
                        $campos .= ($cadSisref['dipl_inss'] != $wIngresso_orgao_diploma_legal && !empty($wIngresso_orgao_diploma_legal) ? "dipl_inss = '" . $wIngresso_orgao_diploma_legal . "', " : "");
                        $campos .= ($cadSisref['cod_cargo'] != $wCargo && !empty($wCargo) ? "cod_cargo = '" . $wCargo . "', " : "");
                        $campos .= ($cadSisref['f_ing_org'] != $wIngresso_orgao_forma && !empty($wIngresso_orgao_forma) ? "f_ing_org = '" . $wIngresso_orgao_forma . "', " : "");
                        $campos .= ($cadSisref['n_sip_inss'] != $wIngresso_orgao_diploma_legal_num && !empty($wIngresso_orgao_diploma_legal_num) ? "n_sip_inss = '" . $wIngresso_orgao_diploma_legal_num . "', " : "");
                        $campos .= ($cadSisref['cod_classe'] != $wClasse && !empty($wClasse) ? "cod_classe = '" . $wClasse . "', " : "");
                        $campos .= ($cadSisref['cod_padrao'] != $wPadrao && !empty($wPadrao) ? "cod_padrao = '" . $wPadrao . "', " : "");
                        $campos .= ($cadSisref['dt_dip_ins'] != $wIngresso_orgao_diploma_legal_data && !empty($wIngresso_orgao_diploma_legal_data) ? "dt_dip_ins = '" . $wIngresso_orgao_diploma_legal_data . "', " : "");
                        $campos .= ($cadSisref['cod_sitcad'] != $wSituacao_cadastral && !empty($wSituacao_cadastral) ? "cod_sitcad = '" . $wSituacao_cadastral . "', " : "");
                        $campos .= ($cadSisref['ident_unica'] != $wIdentificacao_unica && !empty($wIdentificacao_unica) ? "ident_unica  = '" . $wIdentificacao_unica . "', " : "");
                        $campos .= ($cadSisref['mat_siapecad'] != $wMatricula_siapecad && !empty($wMatricula_siapecad) ? "mat_siapecad = '" . $wMatricula_siapecad . "', " : "");
                        $campos .= ($cadSisref['reg_obito_dt'] != $wObito_data && !empty($wObito_data) ? "reg_obito_dt = '" . $wObito_data . "', " : "");
                        $campos .= ($cadSisref['oco_exclu_dt'] != $wExclusao_data && !empty($wExclusao_data) ? "oco_exclu_dt = '" . $wExclusao_data . "', " : "");
                        $campos .= ($cadSisref['oco_exclu_oco'] != $wExclusao_codigo && !empty($wExclusao_codigo) ? "oco_exclu_oco = '" . $wExclusao_codigo . "', " : "");
                        $campos .= ($cadSisref['oco_exclu_dl_cod'] != $wExclusao_diploma_legal && !empty($wExclusao_diploma_legal) ? "oco_exclu_dl_cod = '" . $wExclusao_diploma_legal . "', " : "");
                        $campos .= ($cadSisref['oco_exclu_dl_num'] != $wExclusao_diploma_legal_num && !empty($wExclusao_diploma_legal_num) ? "oco_exclu_dl_num = '" . $wExclusao_diploma_legal_num . "', " : "");
                        $campos .= ($cadSisref['oco_exclu_dl_dt_publ'] != $wExclusao_diploma_legal_data && !empty($wExclusao_diploma_legal_data) ? "oco_exclu_dl_dt_publ = '" . $wExclusao_diploma_legal_data . "', " : "");

                        $campos = (substr(trim($campos), -1) === ',' ? substr(trim($campos), 0, strlen(trim($campos)) - 1) . " " : $campos);

                        if (!empty($campos))
                        {
                            $sql = 'UPDATE servativ SET ' . $campos . ' WHERE mat_siape = "' . $wMatricula_siape . '" ';

                            $rsSERVATIV = $this->conexao->linkSISREF->query($sql);
                            //$rsSERVATIV->close();
                        }
                    }
                }
                else
                {
                    comunicaErro("Erro na seleção SISREF:<br> (" . $this->linkSISREF->connect_errno . ') ' . $this->linkSISREF->connect_error);
                }

                $contar++;

                //if ($contar == 100)
                //{
                //	die('1');
                //}
            }
        }
        else
        {
            comunicaErro("Erro na seleção SIAPE:<br> (" . $this->linkSISREF->connect_errno . ') ' . $this->linkSISREF->connect_error);
        }

        $rsSIAPE->close(); /* free result set */

        // fazendo batimentos
        $sql      = "UPDATE servativ SET reg_jur_at = '3' WHERE sigregjur = 'ETG' ";
        $rsSISREF = $this->conexao->linkSISREF->query($sql);

        $sql      = "UPDATE servativ SET reg_jur_at = '2' WHERE sigregjur = 'EST' ";
        $rsSISREF = $this->conexao->linkSISREF->query($sql);

        //$rsSISREF->close(); /* free result set */

    }

}

/**  @Class
 * +--------------------------------------------------------------------+
 * | @Class    : ApagaTabelasHistoricoTemp                              |
 * | @description : Apaga tabelas temporárias utilizadas para atualizar |
 * |                as tabelas dos pontos em cada competencia           |
 * | Autor     : Edinalvo Rosa                                          |
 * +--------------------------------------------------------------------+
 * */
class ApagaTabelasHistoricoTemp extends ConexaoBD
{

    private $conexao;

    public function __construct()
    {
        // Conexão com o banco de dados SISREF
        $this->conexao = new ConexaoBD();

    }

    public function executa()
    {
        // Leitura do banco de dados
        // Apaga todos os arquivos "historico_temp_???????"
        $tb_nome = 'historico_temp_';

        $sql = '
		SELECT
			table_name
		FROM
			information_schema.tables
		WHERE
			table_name LIKE "' . $tb_nome . '%"
		';

        if ($rsSISREF = $this->conexao->linkSISREF->query($sql))
        {
            while ($row = $rsSISREF->fetch_assoc())
            {
                if ((substr($row['table_name'], 0, 15) == $tb_nome) && (soNumeros($row['table_name']) != ''))
                {
                    $sql = 'DROP TABLE ' . $row['table_name'];
                    $this->conexao->linkSISREF->query($sql);
                }
            }
        }

    }

}

/**  @Class
 * +--------------------------------------------------------------------+
 * | @Class    : AtualizaEmailPorLDAP                                   |
 * | @description : Atualiza o email do servidor na tabela SERVATIV     |
 * |                a partir do LDAP                                    |
 * | Autor     : Edinalvo Rosa                                          |
 * +--------------------------------------------------------------------+
 * */
class AtualizaEmailPorLDAP extends ConexaoBD
{

    private $conexao;

    public function __construct()
    {
        // Conexão com o banco de dados SISREF
        $this->conexao = new ConexaoBD();

    }

    public function executa()
    {
        // siape
        $sql = 'SELECT cad.siape, cad.nome, cad.cpf, IF(IFNULL(cad.pis_pasep,"")<>"",cad.pis_pasep,IF(IFNULL(cad2.pis_pasep,"")<>"",cad2.pis_pasep,"")) AS pis_pasep, cad2.pis_pasep AS pis_pasep2, cad.pis_pasep AS pis_pasep1 FROM siapecad.cadmes AS cad LEFT JOIN servativ AS cad2 ON cad.siape = cad2.mat_siape WHERE cad2.excluido = "N" AND cad2.cod_sitcad NOT IN ("02","08","15") ORDER BY cad.nome ';

        if ($rsSIAPE = $this->conexao->linkSISREF->query($sql))
        {
            if ($rsSIAPE->num_rows > 0)
            {
                $contar = 1;
                while ($dados  = $rsSIAPE->fetch_assoc())
                {
                    //$email = $this->carregaEmail( $dados['cpf'] );
                    //$sql = "UPDATE servativ SET email = '" . $email . "' WHERE mat_siape='" . $dados['siape'] . "' ";
                    //$rsSISREF2 = $this->conexao->linkSISREF->query( $sql );

                    $dadosLDAP = $this->carregaDados($dados['cpf']);

                    $nit_ldap   = (is_null($dadosLDAP['nit']) ? '' : soNumeros($dadosLDAP['nit']));
                    $nit_siape  = (is_null($dados['nit_siape']) ? '' : soNumeros($dados['nit_siape']));
                    $nit_sisref = (is_null($dados['nit_sisref']) ? '' : soNumeros($dados['nit_sisref']));

                    if (strlen($nit_ldap) < 11 || $dadosLDAP['nit'] == '00000000000' || $dadosLDAP['nit'] == '00000000002')
                    {
                        if (strlen($nit_siape) < 11 || $dados['nit_siape'] == '00000000000')
                        {
                            $nit_gravar = (strlen($nit_sisref) < 11 || $dados['nit_sisref'] == '00000000000' ? '' : $dados['nit_sisref']);
                        }
                        else
                        {
                            $nit_gravar = $dados['nit_siape'];
                        }
                    }
                    else
                    {
                        $nit_gravar = $dadosLDAP['nit'];
                    }

                    if ($nit_gravar != $dadosLDAP['nit'])
                    {
                        $sql = "
						INSERT vw_siape_email_nit
						SET
							siape      = '" . $dados['siape'] . "',
							nome       = '" . $dados['nome'] . "',
							cpf        = '" . $dados['cpf'] . "',
							nit_siape  = '" . $dados['nit_siape'] . "',
							nit_sisref = '" . $dados['nit_sisref'] . "',
							nit_ldap   = '" . $dadosLDAP['nit'] . "',
							nit_gravar = '" . $nit_gravar . "',
							email      = '" . $dadosLDAP['email'] . "' ";
                        //$rsSISREF2 = $this->conexao->linkSISREF->query( $sql );
                    }

                    // SERVATIV
                    $sql       = "
					UPDATE servativ
					SET
						cpf       = '" . $dados['cpf'] . "',
						nome      = '" . $dados['nome'] . "',
						email     = '" . $dadosLDAP['email'] . "',
						pis_pasep = '" . $nit_gravar . "'
					WHERE
						mat_siape = '" . $dados['siape'] . "' ";
                    $rsSISREF2 = $this->conexao->linkSISREF->query($sql);
                }
            }
        }

    }

    public function carregaEmail($myLdapVAR = '')
    {
        $dadosLDAP = $this->carregaDados($myLdapVAR);
        return $dadosLDAP['email'];

    }

    public function carregaDados($myLdapVAR = '')
    {
        $email_name = '';

        if ($myLdapVAR != '')
        {
            // Variáveis referentes ao ldap para pesquisa/alteração
            $myLdapHost  = "ldap://cnsldapdf.prevnet"; //"ldap://mmldap.prevnet";
            $myLdapBase  = "dc=gov,dc=br";
            $myLdapAttrs = array('dn', 'employeeNumber', 'cn', 'Cpf', 'NIT', 'ou', 'mail', 'title', 'telephoneNumber', 'l', 'rgUF', 'accountStatus', 'uid', 'geridRole');

            // conectar no LDAP
            $myLdapConn = @ldap_connect($myLdapHost) or die("ERROR: ldap_connect - $myLdapHost\n");
            @ldap_set_option($myLdapConn, LDAP_OPT_PROTOCOL_VERSION, 3);

            // Conecta anonimamente para pesquisar usuario
            $myLdapBind = @ldap_bind($myLdapConn) or die("ERROR: ldap_bind - anonimamente.\n");

            if ($myLdapBind)
            {
                // localizar usuário no LDAP
                $myLdapFilter = "(Cpf=$myLdapVAR)";
                $myLdapSearch = @ldap_search($myLdapConn, $myLdapBase, $myLdapFilter, $myLdapAttrs) or die("ERRO: ldap_search\n");

                if ($myLdapSearch)
                {
                    // obter o DN que irá autenticar no LDAP
                    $myLdapFirst = @ldap_first_entry($myLdapConn, $myLdapSearch);
                    $ldapDN      = @ldap_get_dn($myLdapConn, $myLdapFirst);

                    /*
                      cn
                      uid
                      accountStatus
                      cpf
                      NIT
                      rgUf
                      employeeNumber
                      geridRole
                      mail
                      title
                      ou
                      l
                      telephoneNumber

                      ...

                      $ldapAttributes = ldap_get_attributes($myLdapConn,$myLdapFirst);

                      print "
                      <tr>
                      <td>&nbsp;" . $ldapAttributes["cpf"][0]  . "&nbsp;</td>
                      <td>&nbsp;" . $ldapAttributes["NIT"][0]  . "&nbsp;</td>
                      <td>&nbsp;" . $ldapAttributes["cn"][0]   . "&nbsp;</td>
                      <td>&nbsp;" . $ldapAttributes["mail"][0] . "</td>
                      </tr>";

                     */

                    $ldapAttributes = ldap_get_attributes($myLdapConn, $myLdapFirst);

                    $email    = $ldapAttributes["mail"][0];
                    $arrEmail = explode('@', $email);
                    if ((count($arrEmail) === 1 && !empty($email)) || (count($arrEmail) > 1 && $arrEmail[1] === 'previdencia.gov.br'))
                    {
                        $email = $arrEmail[0] . '@inss.gov.br';
                    }

                    $ldapDados          = array();
                    $ldapDados['email'] = $email;
                    $ldapDados['nit']   = $ldapAttributes["NIT"][0];
                }
            }

            // Desconectar do LDAP
            @ldap_unbind($myLdapConn);

            // Desconectar do LDAP
            return $ldapDados;
        }

    }

}
