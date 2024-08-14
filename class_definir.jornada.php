<?php

set_time_limit(800);

include_once( 'config.php' );
include_once( "inc/class_verifica_horario_trabalho.php" );

/**  @package DefinirJornada (Class and Functions)
 * +---------------------------------------------------------------------------+
 * |                                                                           |
 * | Funções e Classes                                                         |
 * |                                                                           |
 * | @package    : class and functions                                         |
 * | @copyright  : (C) 2012 INSS                                               |
 * | @license    :                                                             |
 * | @link       : http://www-inss                                             |
 * | @subpackage :                                                             |
 * | @author     :                                                             |
 * |                                                                           |
 * +---------------------------------------------------------------------------+
 * |   Convenções:                                                             |
 * |      [] -> indicam parametros obrigatórios                                |
 * |      <> -> indicam parametros                                             |
 * +---------------------------------------------------------------------------+
 * */

/**  @Class
 * +---------------------------------------------------------------------------+
 * | @class       : DefinirJornada                                             |
 * | @description : Trata informações de jornada                               |
 * |                turno estendido para APSs                                  |
 * |                                                                           |
 * | @param  : void                                                            |
 * | @return : void                                                            |
 * | @usage  : $oDados = new DefinirJornada;                                   |
 * |                                                                           |
 * | @author : Edinalvo Rosa                                                   |
 * |                                                                           |
 * | @dependence : mensagem()    (function.php)                                |
 * |               voltar()      (function.php)                                |
 * +---------------------------------------------------------------------------+
 * */
class DefinirJornada
{

    ## instancia banco de dados

    public $oDBSisref;
    public $oDBSupervisao;

    ## dados do servidor (SERVATIV)
    public $nome;
    public $cpf;
    public $siape; // matricula do servidor
    public $chefe; // indica se o servidor é ocupante de funcao (SIAPE)
    public $data_ingresso_orgao; // data de admissao no Órgão
    public $lotacao; // unidade de trabalho do servidor
    public $tb0700;
    public $uorg;
    public $data_ingresso_lotacao;
    public $situacao_cadastral;
    public $anomes_admissao; // ano e mes da admissao/ingresso no orgao
    public $anomes_exclusao; // ano e mes da exclusao (exoneracao/aposentadoria/falecimento)
    public $cargo; // cargo do servidor
    public $cargo_descricao; // descricao do cargo
    public $hora_especial;       // indica definicao de horário especial (HE)
    public $motivo_hespecial;    // qual o tipo de HE
    public $processo_hespecial;  // número do processo formalizando o HE
    public $data_hespecial;      // data de início do HE
    public $data_hespecial_fim;  // data de finalização do HE
    public $registrarForaHorarioEmpresa; // indica se o servidor pode registrar frequência
                                         // após o horario final do setor
    public $entrada_no_servico;  // horário de entrada ao serviço
    public $saida_para_o_almoco; // horário da saida para o almoco
    public $volta_do_almoco;     // horário do retorno do almoco
    public $saida_do_servico;    // final do expediente
    public $jornada; // jornada do servidor (estendida ou normal) no formato 99
    public $data_ingresso_jornada;
    public $data_final_jornada;
    public $banco_compensacao;
    public $banco_compensacao_tipo;
    public $excluido;
    public $sigregjur;

    ## dados da tabela TABLOT
    public $descricao;           // descricao da lotacao
    public $upag;
    public $uorg_pai;
    public $horario_do_setor_inicio;
    public $horario_do_setor_fim;
    public $codigo_do_municipio;

    ## dados da tabela OCUPANTES e/ou SUBSTITUTOS
    public $chefiaAtiva; // indica se o servidor é ocupante titular/substituto

    ## dados do turno estendido - tabela TURNO_ESTENDIDO_SUPERVISAO
    ## informação externa - Sistema Supervisão
    public $supervisao_unidade;
    public $supervisao_autorizacao;
    public $supervisao_efetivacao;
    public $supervisao_encerramento;
    public $autorizado_te; // indica se a unidade está
                           // autorizada para turno estendido

    #    ## dados da ocorrencia
    public $data; // dia, mês e ano da ocorrencia
    public $dia;  // dia da ocorrencia
    public $mes;  // mês da ocorrencia
    public $ano;  // ano da ocorrencia

    ## uso interno para retorno ao formulário de origem
    public $destino;
    public $voltar;
    public $jd;  // jornada do servidor (estendida ou normal) por dia (jornada/5)
    public $j;   // jornada do servidor (estendida ou normal) no formato HH:MM
    public $jnd; // armazena a jornada do servidor (formato 99) - compatibilidade
    public $erroGravaHorario;

    ##
    #  Construtor
    #
    function DefinirJornada()
    {
        $this->oDBSisref = new DataBase('PDO');
        $this->oDBSisref->setHostSISREF();
    }

    ##
    #
    #  Metodos SETs e GETs
    #
    ##
    // instancia as bases de dados
    function getDBaseSISREF()
    {
        return $this->oDBSisref;
    }

    function getDBaseSupervisao()
    {
        return $this->oDBSupervisao;
    }

    // dados do servidor (SERVATIV)
    function setSiape($siape = '')
    {
        $this->siape = getNovaMatriculaBySiape($siape);
    }

    function getSiape()
    {
        return $this->siape;
    }

    function setNome($var = '')
    {
        $this->nome = $var;
    }

    function getNome()
    {
        return $this->nome;
    }

    function setLotacao($lotacao = '')
    {
        $this->lotacao = ($lotacao == '' ? $_SESSION['sLotacao'] : $lotacao);
    }

    function getLotacao()
    {
        return $this->lotacao;
    }

    function getDataLotacao()
    {
        return $this->data_ingresso_lotacao;
    }

    function setTb0700($lotacao = '')
    {
        $this->tb0700 = $lotacao;
    }

    function getTb0700()
    {
        return $this->tb0700;
    }

    function setChefe($var = '')
    {
        $this->chefe = $var;
    }

    function getChefe()
    {
        return $this->chefe;
    }

    function getBancoCompensacao()
    {
        return $this->banco_compensacao;
    }

    function getBancoCompensacaoTipo()
    {
        return $this->banco_compensacao_tipo;
    }

    // horários
    function setEntradaNoServico($hora = '00:00:00')
    {
        $this->entrada_no_servico = $hora;
    }

    function getEntradaNoServico()
    {
        return $this->entrada_no_servico;
    }

    function setSaidaParaAlmoco($hora = '00:00:00')
    {
        $this->saida_para_o_almoco = $hora;
    }

    function getSaidaParaAlmoco()
    {
        return $this->saida_para_o_almoco;
    }

    function setVoltaDoAlmoco($hora = '00:00:00')
    {
        $this->volta_do_almoco = $hora;
    }

    function getVoltaDoAlmoco()
    {
        return $this->volta_do_almoco;
    }

    function setSaidaDoServico($hora = '00:00:00')
    {
        $this->saida_do_servico = $hora;
    }

    function getSaidaDoServico()
    {
        return $this->saida_do_servico;
    }

    // Jornada
    function setJornada($jornada = 'N')
    {
        $this->jornada = $jornada;
    }

    function getJornada()
    {
        return $this->jornada;
    }

    function getDataJornada()
    {
        return $this->data_ingresso_jornada;
    }

    function setJD($var = '')
    {
        $this->jd = $var;
    }

    function getJD()
    {
        return $this->jd;
    }

    function setJ($var = '')
    {
        $this->j = $var;
    }

    function getJ()
    {
        return $this->j;
    }

    function setJND($var = '')
    {
        $this->jnd = $var;
    }

    function getJND()
    {
        return $this->jnd;
    }

    // uso interno para retorno ao formulário de origem
    function setDestino($destino = '')
    {
        $this->destino = $destino;
    }

    function getDestino()
    {
        return $this->destino;
    }

    function setVoltar($var = 1)
    {
        $this->voltar = $var;
    }

    function getVoltar()
    {
        return $this->voltar;
    }

    function setErroGravaHorario($erro = '')
    {
        if ($erro != '')
        {
            $this->erroGravaHorario = $erro;
        }
    }

    function getErroGravaHorario()
    {
        return $this->erroGravaHorario;
    }

    ## Data
    #
    function setData($data = '')
    {
        $data       = ($data == '' ? date('d/m/Y') : $data);
        $datatrans  = data2arrayBR($data);
        $this->data = $datatrans[0] . "/" . $datatrans[1] . "/" . $datatrans[2];
        $this->setDia($datatrans[0]);
        $this->setMes($datatrans[1]);
        $this->setAno($datatrans[2]);
    }

    function getData()
    {
        return $this->data;
    }

    function setDia($dia = '')
    {
        if ($dia == '')
        {
            $datatrans = data2arrayBR();
            $dia       = $datatrans[0];
        }
        $this->dia = $dia;
    }

    function getDia()
    {
        return $this->dia;
    }

    function setMes($mes = '')
    {
        if ($mes == '')
        {
            $datatrans = data2arrayBR();
            $mes       = $datatrans[1];
        }
        $this->mes = $mes;
    }

    function getMes()
    {
        return $this->mes;
    }

    function setAno($ano = '')
    {
        if ($ano == '')
        {
            $datatrans = data2arrayBR();
            $ano       = $datatrans[2];
        }
        $this->ano = $ano;
    }

    function getAno()
    {
        return $this->ano;
    }


    function setSigRegJur($value = '')
    {
        $this->sigregjur = $value;
    }

    function getSigRegJur()
    {
        return $this->sigregjur;
    }

    ## @metodo
    #
    # jornada de trabalho
    #
    function estabelecerJornada()
    {
        $this->leDadosServidor();
        $this->leSupervisao();

        // turno estendido
        $oDBaseJH = $this->PesquisaJornadaHistorico($this->getSiape(), $this->getData());
        if ($oDBaseJH->num_rows() > 0)
        {
            $oTurno                    = $oDBaseJH->fetch_object();
            $this->entrada_no_servico  = $oTurno->entra_trab;
            $this->saida_para_o_almoco = $oTurno->ini_interv;
            $this->volta_do_almoco     = $oTurno->sai_interv;
            $this->saida_do_servico    = $oTurno->sai_trab;
            $jornada                   = $oTurno->jornada;

            $this->jornada = $jornada;
            $this->jd      = ($jornada / 5);
            $this->j       = formata_jornada_para_hhmm($this->jd);
            $this->jnd     = $jornada;
        }
    }

    ## @metodo
    #
    # obtem dados do servidor
    # nome, codigo da lotacao,
    # jornada de trabalho
    #
    function leDadosServidor()
    {
        $oDBase = new DataBase('PDO');

        // obtem dados dos servidores
        // nome, codigo da lotacao, jornada de trabalho e se é ocupante de função
        // instancia o banco de dados
        // seleciona nome do servidor e jornada
        $oDBase->query("
            SELECT
                servativ.nome_serv,
                servativ.cod_lot,
                servativ.chefia,
                servativ.jornada,
                servativ.entra_trab,
                servativ.ini_interv,
                servativ.sai_interv,
                servativ.sai_trab,
                DATE_FORMAT(servativ.dt_adm,'%d/%m/%Y')       AS dt_adm,
                DATE_FORMAT(servativ.oco_exclu_dt,'%d/%m/%Y') AS oco_exclu_dt,
                servativ.cod_sitcad,
                servativ.bhoras,
                servativ.bh_tipo,
                servativ.horae,
                servativ.processo,
                servativ.motivo,
                servativ.dthe,
                servativ.dthefim,
                servativ.sigregjur,
                tabsetor.upag,
                tabsetor.uorg_pai,
                tabsetor.inicio_atend,
                tabsetor.fim_atend,
                tabsetor.descricao,
                servativ.dt_ing_lot,
                servativ.autchef,
                servativ.dt_ing_jorn,
                tabsetor.cod_uorg,
                tabsetor.codmun,
                servativ.cpf,
                tabsetor.tb0700,
                servativ.cod_cargo         AS cargo,
                IFNULL(tabcargo.desc_cargo,'') AS cargo_descricao,
                servativ.excluido
            FROM
                servativ
            LEFT JOIN
                tabsetor ON servativ.cod_lot = tabsetor.codigo
            LEFT JOIN
                tabcargo ON servativ.cod_cargo = tabcargo.cod_cargo
            WHERE
                servativ.mat_siape = :siape
        ", array(
            array(':siape', $this->getSiape(), PDO::PARAM_STR)
        ));

        $nNumRows = $oDBase->num_rows();

        if ($nNumRows == 0)
        {
            //header("Location: mensagem.php?modo=5");
            mensagem("Servidor não está ativo ou inexistente!", null, 1);
        }
        else
        {
            $oServidor = $oDBase->fetch_object();

            $this->nome                   = $oServidor->nome_serv;
            $this->cpf                    = $oServidor->cpf;
            $this->uorg                   = $oServidor->cod_uorg;
            $this->lotacao                = $oServidor->cod_lot;
            $this->tb0700                 = $oServidor->tb0700;
            $this->descricao              = $oServidor->descricao;
            $this->data_ingresso_lotacao  = $oServidor->dt_ing_lot;
            $this->situacao_cadastral     = $oServidor->cod_sitcad;
            $this->sigregjur              = $oServidor->sigregjur;
            $this->banco_compensacao      = $oServidor->bhoras;
            $this->banco_compensacao_tipo = $oServidor->bh_tipo;

            $this->excluido = $oServidor->excluido;

            $this->data_ingresso_orgao   = $oServidor->dt_adm;
            $this->data_ingresso_lotacao = $oServidor->dt_ing_lot;
            $this->data_ingresso_jornada = $oServidor->dt_ing_jorn;

            $this->anomes_admissao = $oServidor->dt_adm;
            $this->anomes_exclusao = $oServidor->oco_exclu_dt;

            $this->cargo           = $oServidor->cod_cargo;
            $this->cargo_descricao = $oServidor->cargo_descricao;

            $this->chefe = $oServidor->chefia;

            $this->hora_especial               = $oServidor->horae;
            $this->processo_hespecial          = $oServidor->processo;
            $this->motivo_hespecial            = $oServidor->motivo;
            $this->data_hespecial              = $oServidor->dthe;
            $this->data_hespecial_fim          = $oServidor->dthefim;
            $this->upag                        = $oServidor->upag;
            $this->uorg_pai                    = $oServidor->uorg_pai;
            $this->horario_do_setor_inicio     = $oServidor->inicio_atend;
            $this->horario_do_setor_fim        = $oServidor->fim_atend;
            $this->registrarForaHorarioEmpresa = $oServidor->autchef;
            $this->codigo_do_municipio         = $oServidor->codmun;

            $this->setChefiaAtiva(); // indica se o servidor eh ocupante de funcao (SIAPE)

            $this->entrada_no_servico  = $oServidor->entra_trab; // horário de entrada
            $this->saida_para_o_almoco = $oServidor->ini_interv; // horário da saida para o almoco
            $this->volta_do_almoco     = $oServidor->sai_interv; // horário do retorno do almoco
            $this->saida_do_servico    = $oServidor->sai_trab;   // final do expediente

            $this->data_ingresso_jornada = $oServidor->dt_ing_jorn;

            // jornada do servidor (estendida ou normal) no formato 99
            if ($this->chefiaAtiva == 'S')
            {
                $this->jornada = ($this->hora_especial == 'S' &&
                                    ($this->motivo_hespecial == 'D'
                                     || $this->motivo_hespecial == 'J') ? $oServidor->jornada : 40);
            }
            else
            {
                $this->jornada = $oServidor->jornada;
            }

            // jornada do servidor (estendida ou normal) por dia (jornada/5)
            $this->jd  = ($this->jornada / 5);

            // jornada do servidor (estendida ou normal) no formato HH:MM
            $this->j   = formata_jornada_para_hhmm($this->jd);

            $this->jnd = $this->jornada;
        }
    }

    ## @metodo
    #
    # Verifica se ocupa função - SISREF
    # ou em efetiva substituição
    #
    function setChefiaAtiva()
    {
        // dados
        $sChefia = 'N';
        $xData   = conv_data($this->getData());

        $oDBase = new DataBase('PDO');

        ##
        # - Se o servidor for titular de alguma função a seleção trará um
        #   registro com SIT_OCUP='T', e nada será realizado.
        # - Se o servidor for somente substituto e estiver efetivado a seleção
        #   trará um registro com SIT_OCUP='S' e SUBSTITUINDO='S', e
        #   será alterado o campo CHEFIA para 'S' no SERVATIV, e as permissões para chefia.
        #
        $oDBase->query("
        SELECT
            chf.mat_siape, chf.sit_ocup, IF(IFNULL(subs.siape,'')='','N','S') AS substituindo
        FROM
            ocupantes AS chf
        LEFT JOIN
            substituicao AS subs ON chf.num_funcao = subs.numfunc
        LEFT JOIN
            tabfunc AS func ON chf.num_funcao = func.num_funcao
        WHERE
            chf.mat_siape = :siape
            AND (
                   (
                        :xdata1 >= IF(subs.inicio='0000-00-00','9999-99-99',subs.inicio) AND :xdata2 <= IF(subs.fim='0000-00-00','9999-99-99',subs.fim) AND subs.situacao = 'A'
                    )
                    OR
                    (
                        :xdata3 >= IF(chf.dt_inicio='0000-00-00','9999-99-99',chf.dt_inicio) AND :xdata4 <= IF(chf.dt_fim='0000-00-00','9999-99-99',chf.dt_fim) AND chf.sit_ocup = 'T'
                    )
                )
        ORDER BY
            chf.mat_siape
        ", array(
            array(':siape', $this->getSiape(), PDO::PARAM_STR),
            array(':xdata1', $xData, PDO::PARAM_STR),
            array(':xdata2', $xData, PDO::PARAM_STR),
            array(':xdata3', $xData, PDO::PARAM_STR),
            array(':xdata4', $xData, PDO::PARAM_STR),
        ));
        $nRows = $oDBase->num_rows();

        if ($nRows == 0)
        {
            $sChefia = 'N'; // Atualizar ID chefia no SERVATIV
        }
        else
        {
            while ($oFuncao = $oDBase->fetch_object())
            {
                $sSituacaoOcup = ($oFuncao->sit_ocup == "" ? "x" : $oFuncao->sit_ocup);
                $sSubstituindo = $oFuncao->substituindo; // O valor S ou N será atribuido ao campo Chefia do SERVATIV e alteração da permissão
                $sChefia       = (substr_count('T_R_I_V', $sSituacaoOcup) > 0 ? 'S' : ($sSituacaoOcup == 'x' ? 'N' : $sSubstituindo)); // Atualizar ID chefia no SERVATIV
                if (substr_count('T_R_I_V', $sSituacaoOcup) > 0)
                {
                    break;
                }
            }
        }

        $this->chefiaAtiva = $sChefia;
    }

    function getChefiaAtiva()
    {
        return $this->chefiaAtiva;
    }

    ## @metodo
    #
    # Carrega os dados registrados na adesao
    # ao turno estendido - Supervisao
    #
    function leSupervisao( $data_inicio_jornada=null )
    {
        // dados do servativ
        $siape   = $this->siape;
        $lotacao = $this->lotacao;
        $sUorg   = $this->uorg;
        $jornada = $this->jornada;

        // instancia banco de dados
        $oDBase = new DataBase('PDO');
        $oDBase->setMensagem("nulo");

        // se já verificou a situacao da unidade no sistema
        // supervisao, na data atual, então não realiza novo acesso
        //
        $oDBase->query("
            SELECT codigo
            FROM tabsetor
            WHERE codigo = :lotacao AND DATE_FORMAT(acessou_sistema_supervisao,'%Y-%m-%d') = DATE_FORMAT(NOW(),'%Y-%m-%d') ", array(
            array(':lotacao', $lotacao, PDO::PARAM_STR)
            )
        );

        if ($oDBase->num_rows() == 0)
        {
            // verifica o webservice do supervisao
            // verificando se há nova unidade no turno
            // ou exclusão de alguma já cadastrada
            //
            /*
            if (file_exists("http://www-reat/controle/webservice/sisref_autorizacao.php"))
            {
                $ch      = curl_init();
                $timeout = 0;

                curl_setopt($ch, CURLOPT_URL, "http://www-reat/controle/webservice/sisref_autorizacao.php?unidade_id=" . $lotacao . "&senha=" . _SENHA_SUPERVISAO_WEBSERVICE_);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

                $jsonSupervisao = curl_exec ($ch);
                curl_close($ch);

                //$jsonSupervisao   = @file_get_contents("http://www-reat/controle/webservice/sisref_autorizacao.php?unidade_id=" . $lotacao . "&senha=" . _SENHA_SUPERVISAO_WEBSERVICE_);
                $lista_supervisao = json_decode($jsonSupervisao);
                $nRowsWebService  = count($lista_supervisao);
            }
            else 
            {
                $nRowsWebService = 0;
            }
            */
            $nRowsWebService = 0;
            
            if ($nRowsWebService > 0)
            {
                $oWebService = $lista_supervisao[0];
                if ($oWebService->unidade_id != '' && $lotacao != '')
                {
                    $oDBase->query(
                        "SELECT unidade_id FROM turno_estendido_supervisao WHERE unidade_id= :lotacao AND autorizacao= :autorizacao AND data_efetivacao= :data_efetivacao", array(
                        array(':lotacao', $lotacao, PDO::PARAM_STR),
                        array(':autorizacao', $oWebService->autorizacao, PDO::PARAM_STR),
                        array(':data_efetivacao', $oWebService->data_efetivacao, PDO::PARAM_STR)
                        )
                    );

                    if ($oDBase->num_rows() == 0)
                    {
                        // desativa outras datas
                        $oDBase->query("
                        UPDATE turno_estendido_supervisao
                        SET
                            ativo = 'N'
                        WHERE
                            unidade_id = :lotacao", array(
                            array(':lotacao', $lotacao, PDO::PARAM_STR)
                        ));

                        // inclui nova data
                        $oDBase->query("
                        INSERT turno_estendido_supervisao
                        SET
                            unidade_id        = :lotacao,
                            autorizacao       = :autorizacao,
                            processo_sisref   = :processo_sisref,
                            data_parecer      = :data_parecer,
                            data_efetivacao   = :data_efetivacao,
                            data_encerramento = '0000-00-00',
                            data_do_registro  = NOW(),
                            ativo             = 'S' ", array(
                            array(':lotacao', $lotacao, PDO::PARAM_STR),
                            array(':autorizacao', $oWebService->autorizacao, PDO::PARAM_STR),
                            array(':processo_sisref', $oWebService->processo_sisref, PDO::PARAM_STR),
                            array(':data_parecer', $oWebService->data_parecer, PDO::PARAM_STR),
                            array(':data_efetivacao', $oWebService->data_efetivacao, PDO::PARAM_STR)
                        ));
                    }
                }
            }

            $oDBase->query("
            UPDATE tabsetor
            SET
                acessou_sistema_supervisao = NOW()
            WHERE
                codigo = :lotacao ", array(
                array(':lotacao', $lotacao, PDO::PARAM_STR)
            ));
        }

        // Verifica se há prorrogação emergencial, para período de adaptação dos servidores
        $oManter = $this->verificaSeProrrogaTurnoEstendido($lotacao, $this->getData());

        // Dados via WebService - Supervisao
        $oDBaseTES = $this->PesquisaTurnoEstendidoSupervisao($lotacao, $this->getData());
        if (is_null($oDBaseTES))
        {
            $this->supervisao_autorizacao = 0;
        }
        else
        {
            $nRowsSupervisao = $oDBaseTES->num_rows();
            $oSupervisao     = $oDBaseTES->fetch_object();

            $this->supervisao_unidade     = $oSupervisao->unidade;
            $this->supervisao_autorizacao = ($oManter->num_rows > 0 ? 1 : $oSupervisao->autorizacao);
            $this->supervisao_efetivacao  = ($oManter->num_rows > 0 ? $oManter->data_efetivacao : $oSupervisao->data_efetivacao);
        }

        // Servidor detentor de função (titular ou em substituição), se tiver
        // horário especial estabelecido por perícia médica, ou determinação
        // da justiça, não terá carga horária de 8hs, como é o padrão, e sim
        // a jornada estabelecida pela perícia ou determinação judicial.
        $manter_jornada_horario_especial = (substr_count('D_J',(empty($this->motivo_hespecial) ? 'x' : $this->motivo_hespecial)) > 0);

        switch ($this->supervisao_autorizacao)
        {
            // Turno Estendido Autorizado
            case '1':
                $this->autorizado_te = 'S';
                $this->jornada = ($this->chefiaAtiva == 'S' ? ($manter_jornada_horario_especial == true ? $jornada : 40) : ($jornada == 20 && $this->situacao_cadastral !='66' ? 20 : ($jornada < 30 ?   $jornada : 30)));
                $data_efetivacao = databarra($this->supervisao_efetivacao);
                break;

            // Turno Estendido Suspenso
            case '2':
                $this->autorizado_te = 'N';
                $this->jornada = ($this->chefiaAtiva == 'S' ? ($manter_jornada_horario_especial == true ? $jornada : 40) : ($jornada == 20 && $this->situacao_cadastral !='66' ? 20 : $jornada));
                $data_efetivacao = databarra($this->supervisao_efetivacao);
                break;

            // Servidores em unidades sem Turno Estendido
            default:
                $this->autorizado_te = 'N';
                $this->jornada = ($this->chefiaAtiva == 'S' ? ($manter_jornada_horario_especial == true ? $jornada : 40) : ($jornada == 20 && $this->situacao_cadastral !='66' ? 20 : $jornada));
                $data_efetivacao = (is_null($data_inicio_jornada) ? date('d/m/Y') : $data_inicio_jornada);
                break;
        }

        $this->gravaHorarioJornadaHistorico($data_efetivacao);
    }

    ## @metodo
    #
    # Grava Historico de Jornada/Turno Estendido
    #
    function gravaHorarioJornadaHistorico($data_inicio = '', $entrada = '', $saidaAlmoco = '', $voltaAlmoco = '', $saida = '')
    {
        //dados
        $siape   = trim($this->siape);
        $lotacao = trim($this->lotacao);
        $jornada = $this->jornada;

        //$data_inicio = ($data_inicio == '' || $entrada == '' ? ($this->getData() == '' ? date('d/m/Y') : $this->getData()) : $data_inicio);
        $data_inicio = ($data_inicio == '' ? ($this->getData() == '' ? date('d/m/Y') : $this->getData()) : $data_inicio);

        $data_hoje = date('Y-m-d');

        // le jornada historico
        $oDBase = $this->PesquisaJornadaHistorico($siape, $data_inicio);

        if ($oDBase->num_rows() == 0)
        {
            // inclui registro inicial
            $data_inicio = ($data_inicio == '' || $entrada == '' ? ($this->getData() == '' ? date('d/m/Y') : $this->getData()) : $data_inicio);
            $this->inserirJornadaHistorico($siape, $lotacao, $jornada, $data_inicio, $entrada, $saidaAlmoco, $voltaAlmoco, $saida);
        }
        else
        {
            // le jornada historico
            $oJornada       = $oDBase->fetch_object();
            $jh_data_inicio = $oJornada->data_inicio;
            $jh_cod_lot     = $oJornada->cod_lot;
            $jh_jornada     = $oJornada->jornada;
            $jh_entra_trab  = $oJornada->entra_trab;
            $jh_ini_interv  = $oJornada->ini_interv;
            $jh_sai_interv  = $oJornada->sai_interv;
            $jh_sai_trab    = $oJornada->sai_trab;
            $jh_tipo        = $oJornada->tipo;
            $jh_num_funcao  = $oJornada->num_funcao;
            $jh_processo    = $oJornada->processo;

            // define o tipo da jornada
            // se horário especial informa o processo
            // retorna um array com dois elementos
            // [0] Tipo da jornada
            // [1] Informação/Número do Processo
            //
            $tipo_da_jornada = $this->carregaTipoDaJornada();

            // se a lotação for a mesma
            $entrada = (time_to_sec($entrada) == 0 ? (time_to_sec($jh_entra_trab) == 0 ? $this->entrada_no_servico : $jh_entra_trab) : $entrada );
            if ($jornada > 30)
            {
                if (time_to_sec($entrada) == 0)
                {
                    $saidaAlmoco = '00:00:00';
                    $voltaAlmoco = '00:00:00';
                    $saida       = '00:00:00';
                }
                else
                {
                    $saidaAlmoco     = (time_to_sec($saidaAlmoco) == 0 ? (time_to_sec($jh_ini_interv) == 0 ? $this->saida_para_o_almoco : $jh_ini_interv) : $saidaAlmoco );
                    $voltaAlmoco     = (time_to_sec($voltaAlmoco) == 0 ? (time_to_sec($jh_sai_interv) == 0 ? $this->volta_do_almoco : $jh_sai_interv) : $voltaAlmoco );
                    $intervaloAlmoco = (time_to_sec($voltaAlmoco) - time_to_sec($saidaAlmoco));
                    $saida           = right(sec_to_time(time_to_sec($entrada) + $intervaloAlmoco + time_to_sec(formata_jornada_para_hhmm($jornada))), 8);
                }
            }
            else
            {
                $saidaAlmoco = '00:00:00';
                $voltaAlmoco = '00:00:00';
                $oDBaseTE    = $this->PesquisaTurnoEstendido($siape, $lotacao, $data_inicio);
                if ($oDBaseTE->num_rows() > 0 && time_to_sec($entrada) == 0)
                {
                    $oHorarios = $oDBaseTE->fetch_object();
                    $entrada   = $oHorarios->entra_trab;
                    $saida     = $oHorarios->sai_trab;
                }
                $saida = right(sec_to_time(time_to_sec($entrada) + time_to_sec(formata_jornada_para_hhmm($jornada))), 8);
            }

            // limite de horario de entrada e saida
            $limites_inss   = horariosLimiteINSS();
            $entrada_minima = time_to_sec($limites_inss['entrada']['horario']); // definir entrada só a partir deste horário, ex.: 6:30
            $entrada_maxima = time_to_sec($limites_inss['saida']['horario']); // limite máximo até este horário, ex.: 22:00
            $entrada_maxima = $entrada_maxima - time_to_sec('06:00:00'); // horário mínimo para definição de saída, ex.: 16:00
            // testa se houve mudança em alguma
            // das informações sobre a jornada
            if (($jh_cod_lot == $lotacao) && ($jh_jornada == $jornada) && (time_to_sec($jh_entra_trab) == time_to_sec($entrada)) && (time_to_sec($jh_ini_interv) == time_to_sec($saidaAlmoco)) && (time_to_sec($jh_sai_interv) == time_to_sec($voltaAlmoco)) && (time_to_sec($jh_sai_trab) == time_to_sec($saida)) && ($jh_tipo == $tipo_da_jornada[0]) && ($jh_num_funcao == $this->getChefiaAtiva()) && ($jh_processo == $tipo_da_jornada[1]))
            {
                // não situação diferente entre a atual e a anterior
            }
            else
            {
                // inclui registro inicial
                // o date(..) indica a data atual
                // informando a necessidade de um
                // teste antes de gravar os dados
                $this->inserirJornadaHistorico($siape, $lotacao, $jornada, $data_inicio, $entrada, $saidaAlmoco, $voltaAlmoco, $saida);
            }
        }

        $this->jornada = $jornada; // jornada do servidor (estendida ou normal) no formato 99
        $this->jd      = ($this->jornada / 5);                   // jornada do servidor (estendida ou normal) por dia (jornada/5)
        $this->j       = formata_jornada_para_hhmm($this->jd); // jornada do servidor (estendida ou normal) no formato HH:MM
        $this->jnd     = $this->jornada;
    }

    ## @metodo
    #
    # Grava horário
    #
    function gravaHorario($entra_trab = '00:00:00', $ini_interv = '00:00:00', $sai_interv = '00:00:00', $sai_trab = '00:00:00')
    {
        $data_inicio = conv_data($this->data);

        // estabelece jornada
        $this->estabelecerJornada();

        //dados
        $siape   = ltrim(rtrim($this->siape));
        $lotacao = ltrim(rtrim($this->lotacao));
        $jornada = $this->jornada;

        ## validacao
        ##
        #
                $mensagem = null;

        $oHoras = new CalculaHoras();

        $oHoras->setJornada($this->jornada);      // Em horas diárias ou semanais (40hs ou 08:00hs)
        $oHoras->setEntrada($entra_trab);         // 8 horas, início do expediente
        $oHoras->setIntervaloInicio($ini_interv); // 12 horas começa o intervalo
        $oHoras->setIntervaloFim($sai_interv);    // 13 horas termina o intervalo
        $oHoras->setSaida($sai_trab);             // 17 horas, fim do expediente
        $oHoras->setCompensacao("sem teste");     // "sem teste" para não testar autorização
        
        // exibe mensagem de erro, se houver
        $mensagem = $oHoras->verificaHorarioDeTrabalho($exibe_mensagem = false);
        
        if (is_string($mensagem) && $mensagem != null)
        {
            mensagem($mensagem, $_SESSION['sPaginaRetorno_sucesso']);
        }

        $this->entrada_no_servico  = $entra_trab; // horário de entrada
        $this->saida_para_o_almoco = $ini_interv; // horário da saida para o almoco
        $this->volta_do_almoco     = $sai_interv; // horário do retorno do almoco
        $this->saida_do_servico    = $sai_trab;   // final do expediente
        
        // Grava Historico de Jornada/Turno Estendido
        $this->inserirJornadaHistorico($siape, $lotacao, $jornada, $data_inicio, $this->entrada_no_servico, $this->saida_para_o_almoco, $this->volta_do_almoco, $this->saida_do_servico, date('d/m/Y'));
    }

    ## @metodo
    #
    # pesquisa nos dados migrados do sistema supervisão
    #
    function PesquisaTurnoEstendidoSupervisao($lotacao = '', $dia = '')
    {
        $oDBase = null;

        if ($lotacao != '' && $dia != '')
        {
            $oDBase = new DataBase('PDO');
            $oDBase->query("
                SELECT
                    tes.unidade_id AS unidade, IFNULL(tes.autorizacao,'') AS autorizacao, IF(IFNULL(data_efetivacao,'0000-00-00')='0000-00-00','9999-99-99',data_efetivacao) AS data_efetivacao
                FROM
                    turno_estendido_supervisao AS tes
                WHERE
                    tes.unidade_id = :lotacao AND (:dia >= tes.data_efetivacao)
                ORDER BY
                    tes.data_efetivacao DESC, tes.data_do_registro DESC
                LIMIT 1
        ", array(
                array(':lotacao', $lotacao, PDO::PARAM_STR),
                array(':dia', conv_data($dia), PDO::PARAM_STR)
            ));
        }

        return $oDBase;
    }

    /*
     * seleciona o horário de trabalho definido
     * pela chefia imediata para o turno estendido
     *
     */
    function PesquisaTurnoEstendido($siape = '', $lotacao = '', $dia = '')
    {
        $oDBase = new DataBase('PDO');
        if ($siape != '' && $lotacao != '' && $dia != '')
        {
            $oDBase->query("
            SELECT
                IFNULL(te.entra_trab,'00:00:00') AS entra_trab, IFNULL(te.ini_interv,'00:00:00') AS ini_interv, IFNULL(te.sai_interv,'00:00:00') AS sai_interv, IFNULL(te.sai_trab,'00:00:00') AS sai_trab
            FROM
                turno_estendido AS te
            WHERE
                te.siape = :siape AND te.cod_lot = :cod_lot AND :bool >= te.data_inicio
            ORDER BY
                te.data_inicio DESC, te.data_registro DESC
            LIMIT 1;
        ", array(
            array(':siape', $siape, PDO::PARAM_STR),
            array(':cod_lot', $lotacao, PDO::PARAM_STR),
            array(':bool', conv_data($dia), PDO::PARAM_STR)
        ));
        }

        return $oDBase;
    }

    ## @metodo
    #
    # seleciona a jornada e o horário de
    # trabalho definido pela chefia imediata
    #
    function PesquisaJornadaHistorico($siape=NULL, $dia=NULL)
    {
        $dia    = (is_null($dia) ? date('d/m/Y') : $dia);

        $oDBase = null;

        if ( !is_null($siape) && !empty($siape) )
        {
            $oDBase = new DataBase('PDO');
            $oDBase->query("
                SELECT
                    jh.data_inicio,
                    jh.cod_lot,
                    jh.jornada,
                    IFNULL(jh.entra_trab,'00:00:00') AS entra_trab,
                    IFNULL(jh.ini_interv,'00:00:00') AS ini_interv,
                    IFNULL(jh.sai_interv,'00:00:00') AS sai_interv,
                    IFNULL(jh.sai_trab,'00:00:00')   AS sai_trab,
                    IFNULL(jh.tipo,'') AS tipo,
                    jh.num_funcao,
                    jh.processo
                FROM
                    jornada_historico AS jh
                WHERE
                    jh.siape = :siape AND (jh.data_inicio >= :dia OR :dia >= jh.data_inicio)
                ORDER BY
                    jh.data_inicio DESC, jh.data_registro DESC
                LIMIT
                    1
            ", array(
                array(':siape', $siape, PDO::PARAM_STR),
                array(':dia', conv_data($dia), PDO::PARAM_STR)
                )
            );
        }

        return $oDBase;
    }

    ## @metodo
    #
    # pesquisa se há registros com dados iguais
    #
    function PesquisaUltimosHorariosJornada($siape = '', $jornada = '')
    {
        $oDBase = new DataBase('PDO');
        if ($siape != '' && $jornada != '')
        {
            $oDBase->query("
                SELECT
                    IF(IFNULL(jh.entra_trab,'00:00:00')<>'00:00:00',jh.entra_trab,IFNULL(cad.entra_trab,'00:00:00')) AS entra_trab,
                    IF(IFNULL(jh.ini_interv,'00:00:00')<>'00:00:00',jh.ini_interv,IFNULL(cad.ini_interv,'00:00:00')) AS ini_interv,
                    IF(IFNULL(jh.sai_interv,'00:00:00')<>'00:00:00',jh.sai_interv,IFNULL(cad.sai_interv,'00:00:00')) AS sai_interv,
                    IF(IFNULL(jh.sai_trab,'00:00:00')  <>'00:00:00',jh.sai_trab,  IFNULL(cad.sai_trab,  '00:00:00')) AS sai_trab,
                    IFNULL(jh.tipo,'') AS tipo,
                    jh.num_funcao,
                    jh.processo
                FROM
                    jornada_historico AS jh
                LEFT JOIN 
                    servativ AS cad ON jh.siape = cad.mat_siape
                WHERE
                    jh.siape = :siape
                    AND jh.jornada = :jornada
                    AND jh.entra_trab <> '00:00:00'
                ORDER BY
                    jh.data_inicio DESC,
                    jh.data_registro DESC
                LIMIT
                    1;
            ",array(
                array(':siape',   $siape,   PDO::PARAM_STR),
                array(':jornada', $jornada, PDO::PARAM_STR)
            ));
        }

        return $oDBase->fetch_object();
    }

    ## @metodo
    #
    # pesquisa se há registros com dados iguais
    #
    function PesquisaJornadaHistoricoIguais($siape = '', $lotacao_informada = '', $lotacao_registrada = '', $jornada = '', $entrada = '00:00:00', $saidaAlmoco = '00:00:00', $voltaAlmoco = '00:00:00', $saida = '00:00:00')
    {
        $oDBase = new DataBase('PDO');
        if ($siape != '' && $lotacao_informada != '' && $lotacao_registrada != '' && $jornada != '')
        {
            // define o tipo da jornada
            // se horário especial informa o processo
            // retorna um array com dois elementos
            // [0] Tipo da jornada
            // [1] Informação/Número do Processo
            //
            $tipo_da_jornada = $this->carregaTipoDaJornada();

            $oDBase->query("
                SELECT
                    jh.siape
                FROM
                    jornada_historico AS jh
                WHERE
                    (jh.siape = :siape) AND
                    ((:abc = :dfg) AND
                    (jh.jornada = :jornada) AND
                    (jh.entra_trab = :entra_trab     OR jh.entra_trab = '00:00:00') AND
                    (jh.ini_interv = :ini_interv OR jh.ini_interv = '00:00:00') AND
                    (jh.sai_interv = :sai_interv OR jh.sai_interv = '00:00:00') AND
                    (jh.sai_trab   = :sai_trab       OR jh.sai_trab   = '00:00:00') AND
                    (jh.tipo = :tipo) AND
                    (jh.num_funcao = :num_funcao) AND
                    (jh.processo = :processo))
                ORDER BY
                    jh.data_inicio DESC, jh.data_registro DESC
                LIMIT
                    1;
            ",array(
                array(':siape', $siape, PDO::PARAM_STR),
                array(':abc', $lotacao_informada, PDO::PARAM_STR),
                array(':dfg', $lotacao_registrada, PDO::PARAM_STR),
                array(':jornada', $jornada, PDO::PARAM_STR),
                array(':entra_trab', $entrada, PDO::PARAM_STR),
                array(':ini_interv', $saidaAlmoco, PDO::PARAM_STR),
                array(':sai_interv', $voltaAlmoco, PDO::PARAM_STR),
                array(':sai_trab', $saida, PDO::PARAM_STR),
                array(':tipo', $tipo_da_jornada[0], PDO::PARAM_STR),
                array(':num_funcao', $this->getChefiaAtiva(), PDO::PARAM_STR),
                array(':processo', $tipo_da_jornada[1], PDO::PARAM_STR)
            ));
        }

        return $oDBase;
    }

    ## @metodo
    #
    # registra as alterações dos dados referente a
    # jornada, horários, lotação, funcao (atuando)
    # quando não houver registro anterior
    #
    function inserirJornadaHistorico($siape = '', $lotacao = '', $jornada = '', $data_inicio = '', $entrada = '00:00:00', $saidaAlmoco = '00:00:00', $voltaAlmoco = '00:00:00', $saida = '00:00:00', $data_hoje = '')
    {
        $oDBase = new DataBase('PDO');

        if ($siape != '' && $data_inicio != '' && $lotacao != '' && $jornada != '')
        {
            $siape = getNovaMatriculaBySiape($siape);

            // dados do cadastro
            $oDBase->query("
                SELECT
                    nome_serv  AS nome,
                    entra_trab AS horario_de_entrada,
                    ini_interv AS saida_para_almoco,
                    sai_interv AS retorno_do_almoco,
                    sai_trab   AS horario_de_saida,
                    cod_lot    AS lotacao
                FROM
                    servativ
                WHERE
                    mat_siape = :siape
            ", array(
                array(':siape', $siape, PDO::PARAM_STR)
            ));
            $oServativ = $oDBase->fetch_object();
    
            $nome        = $oServativ->nome;
            $entrada     = (is_null($entrada)     || empty($entrada)     ? $oServativ->horario_de_entrada : $entrada);
            $saidaAlmoco = (is_null($saidaAlmoco) || empty($saidaAlmoco) ? $oServativ->saida_para_almoco  : $saidaAlmoco);
            $voltaAlmoco = (is_null($voltaAlmoco) || empty($voltaAlmoco) ? $oServativ->retorno_do_almoco  : $voltaAlmoco);
            $saida       = (is_null($saida)       || empty($saida)       ? $oServativ->horario_de_saida   : $saida);
            $lotacao     = (is_null($lotacao)     || empty($lotacao)     ? $oServativ->lotacao            : $lotacao);
            $data_hoje   = (is_null($data_hoje)   || empty($data_hoje)   ? date('Y-m-d')                  : $data_hoje);
            
            // define o tipo da jornada
            // se horário especial informa o processo
            // retorna um array com dois elementos
            // [0] Tipo da jornada
            // [1] Informação/Número do Processo
            //
            $tipo_da_jornada = $this->carregaTipoDaJornada();

            // registrado por
            // - se chefia, RH ou o sistema quando
            //   o servidor registrar a frequencia
            $siape_registro = ($_SESSION['sAPS'] == 'S' || $_SESSION['sRH'] == 'S' ? $_SESSION['sMatricula'] : '');

            // estabelece a data inicial
            $data_inicio = conv_data($data_inicio);

            // gravando vw_sisrefsae / sisage
            $cargo_descricao = ($this->cargo_descricao == "" ? "sem_descricao" : $cargo_descricao);
            if ((substr_count("02_08_15_18_66", $this->situacao_cadastral) == 0))
            {
                $oDBase->setMensagem("Erro na leitura da tabela vw_sisrefsae!");
                $oDBase->query("
                    SELECT siape
                    FROM vw_sisrefsae
                    WHERE siape = :siape 
                ", array(
                    array(':siape', $siape, PDO::PARAM_STR)
                ));

                if ($oDBase->num_rows() == 0)
                {
                    $oDBase->setMensagem("Erro em inclusão na tabela vw_sisrefsae!");
                    $oDBase->query("
                        INSERT
                            vw_sisrefsae
                        SET
                            siape              = :siape,
                            nome               = :nome,
                            horario_de_entrada = :horario_de_entrada,
                            saida_para_almoco  = :saida_para_almoco,
                            retorno_do_almoco  = :retorno_do_almoco,
                            horario_de_saida   = :horario_de_saida,
                            lotacao            = :lotacao,
                            turno_estendido    = :turno_estendido", array(
                        array(':siape',              $siape,               PDO::PARAM_STR),
                        array(':nome',               $nome,                PDO::PARAM_STR),
                        array(':horario_de_entrada', $entrada,             PDO::PARAM_STR),
                        array(':saida_para_almoco',  $saidaAlmoco,         PDO::PARAM_STR),
                        array(':retorno_do_almoco',  $voltaAlmoco,         PDO::PARAM_STR),
                        array(':horario_de_saida',   $saida,               PDO::PARAM_STR),
                        array(':lotacao',            $lotacao,             PDO::PARAM_STR),
                        array(':turno_estendido',    $this->autorizado_te, PDO::PARAM_STR),
                    ));
                }
                else
                {
                    $oDBase->setMensagem("Erro em alteração na tabela vw_sisrefsae!");
                    $oDBase->query("
                        UPDATE
                            vw_sisrefsae
                        SET
                            nome               = :nome,
                            horario_de_entrada = :horario_de_entrada,
                            saida_para_almoco  = :saida_para_almoco,
                            retorno_do_almoco  = :retorno_do_almoco,
                            horario_de_saida   = :horario_de_saida,
                            lotacao            = :lotacao,
                            turno_estendido    = :turno_estendido
                        WHERE
                            siape = :siape ", array(
                        array(':nome',               $nome,                PDO::PARAM_STR),
                        array(':horario_de_entrada', $entrada,             PDO::PARAM_STR),
                        array(':saida_para_almoco',  $saidaAlmoco,         PDO::PARAM_STR),
                        array(':retorno_do_almoco',  $voltaAlmoco,         PDO::PARAM_STR),
                        array(':horario_de_saida',   $saida,               PDO::PARAM_STR),
                        array(':lotacao',            $lotacao,             PDO::PARAM_STR),
                        array(':turno_estendido',    $this->autorizado_te, PDO::PARAM_STR),
                        array(':siape',              $siape,               PDO::PARAM_STR)
                    ));
                }
            }

            // gravando
            $oDBase->query("
                INSERT
                    jornada_historico
                SET
                    tipo           = :tipo,
                    siape          = :siape,
                    jornada        = :jornada,
                    cod_lot        = :cod_lot,
                    cod_uorg       = :cod_uorg,
                    processo       = :processo,
                    num_funcao     = :num_funcao,
                    data_inicio    = :data_inicio,
                    data_fim       = '0000-00-00',
                    entra_trab     = :entrada_trab,
                    ini_interv     = :ini_interv,
                    sai_interv     = :sai_interv,
                    sai_trab       = :sai_trab,
                    data_registro  = NOW(),
                    siape_registro = :siape_registro", array(
                array(':tipo',           $tipo_da_jornada[0],     PDO::PARAM_STR),
                array(':siape',          $siape,                  PDO::PARAM_STR),
                array(':jornada',        $jornada,                PDO::PARAM_STR),
                array(':cod_lot',        $lotacao,                PDO::PARAM_STR),
                array(':cod_uorg',       $this->uorg,             PDO::PARAM_STR),
                array(':processo',       (isset($tipo_da_jornada[1]) ? $tipo_da_jornada[1] : ''), PDO::PARAM_STR),
                array(':num_funcao',     $this->getChefiaAtiva(), PDO::PARAM_STR),
                array(':data_inicio',    $data_inicio,            PDO::PARAM_STR),
                array(':entrada_trab',   $entrada,                PDO::PARAM_STR),
                array(':ini_interv',     $saidaAlmoco,            PDO::PARAM_STR),
                array(':sai_interv',     $voltaAlmoco,            PDO::PARAM_STR),
                array(':sai_trab',       $saida,                  PDO::PARAM_STR),
                array(':siape_registro', $siape_registro,         PDO::PARAM_STR)
            ));
        }

        return $oDBase;
    }

    ## @metodo
    #
    # define o tipo da jornada, se
    # horário especial e informa o processo
    #
    function carregaTipoDaJornada()
    {
        if ($this->autorizado_te == 'S')
        {
            $tipo = 'Turno Estendido';
        }
        elseif ($this->hora_especial == 'S')
        {
            switch ($this->motivo_hespecial)
            {
                case 'J': $tipo = 'Decisão Judicial';
                    break;
                case 'E': $tipo = 'Estudante';
                    break;
                case 'D': $tipo = 'Decisão Médica';
                    break;
                case 'A': $tipo = 'Amamentação';
                    break;
                case 'O': $tipo = 'Opção 30 horas';
                    break;
                default: $tipo = '';
                    break;
            }
            $processo = $this->processo_hespecial;
        }
        else
        {
            $tipo = '';
        }

        return array($tipo, $processo);
    }

    ## @metodo
    #
    # define o tipo da jornada, se
    # horário especial e informa o processo
    #
    function verificaSeProrrogaTurnoEstendido($lotacao, $dia)
    {
        $oManter->num_rows        = 0;
        $oManter->data_efetivacao = '';
        /*
          Em 01/12/2014 às 14:05 horas, josen.filho@inss.gov.br escreveu:

          Prezado Edinalvo,

          Considerando solicitação do Superintendente Regional Nordeste e para adequação de agenda e horário dos servidores, solicitamos manter as Agências Lagoinha/BA e São Lourenço da Mata/PE em turno estendido - REAT até o dia 12/dezembro, com reversão para horário/funcionamento normal a partir do dia 15/dezembro.

          Att.,
          José Nunes Filho
          Diretor de Gestão de Pessoas
        */
        $oDBase                   = new DataBase('PDO');
        $oDBase->query(
            "SELECT codigo
            FROM tabsetor
            WHERE codigo = :lotacao AND IFNULL(DATE_FORMAT(manter_turno_estendido_ate,'%Y-%m-%d'),'0000-00-00') >= DATE_FORMAT(NOW(),'%Y-%m-%d') ", array(array(':lotacao', $lotacao, PDO::PARAM_STR))
        );

        if ($oDBase->num_rows() > 0)
        {
            $oDBase->query("
            SELECT
                IF(IFNULL(data_efetivacao,'0000-00-00')='0000-00-00','9999-99-99',data_efetivacao) AS data_efetivacao
            FROM
                turno_estendido_supervisao AS tes
            WHERE
                tes.unidade_id = :lotacao AND (:dia >= tes.data_efetivacao) AND IFNULL(tes.autorizacao,'2') <> 2
            ORDER BY
                tes.data_efetivacao DESC, tes.data_do_registro DESC
            LIMIT 1
            ", array(
                array(':lotacao', $lotacao, PDO::PARAM_STR),
                array(':dia', conv_data($dia), PDO::PARAM_STR)
                )
            );
            $oManter->num_rows        = $oDBase->num_rows();
            $oManter->data_efetivacao = $oDBase->fetch_object()->data_efetivacao;
        }

        return $oManter;
    }

}
