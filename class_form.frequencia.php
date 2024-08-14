<?php

## @package class
#

// Inicializa a sessão (session_start)
// funcoes de uso geral
include_once( "config.php" );
include_once( "class_ocorrencias_grupos.php" );
include_once( "src/controllers/TabHomologadosController.php" );


## @class
#+-------------------------------+
#| Formulario Siape              |
#+-------------------------------+
#
class formFrequencia extends formPadrao
{
    ## banco de dados
    #
    var $oDBase;
    var $oDBaseID;
    var $dados_result;
    var $oDBSisref;
    var $oDBSupervisao;

    ## dados formulario
    #
    var $form_action;      // script de destino
    var $form_submit;      // javascript (funcao,etc)
    var $origem;           // script de origem
    var $destino;          // script de retorno
    var $voltar;           // qtd de páginas para retorno
    var $historygo;        // qtd de páginas para retorno (compatibilidade)
    var $nome_do_arquivo;  // script de retorno (compatibilidade)
    var $sessao_navegacao; // object

    ## dados frequencia
    #
    var $ano_hoje;
    var $usuario;
    var $codigo_ocorrencia;
    var $tipo_operacao;
    var $total_dias_mes;
    var $data;
    var $dia;
    var $mes;
    var $ano;
    var $dia_util;
    var $deduz_almoco;
    var $entrada;
    var $saida;
    var $inicio_intervalo;
    var $fim_intervalo;
    var $entrada_no_servico;  // horário de entrada - compatibilidade com a class extinta
    var $saida_para_o_almoco; // horário da saida para o almoco - compatibilidade com a class extinta
    var $volta_do_almoco;     // horário do retorno do almoco - compatibilidade com a class extinta
    var $saida_do_servico;    // final do expediente - compatibilidade com a class extinta
    var $jornada; // jornada do servidor (estendida ou normal) no formato 99
    var $jd;      // jornada do servidor (estendida ou normal) por dia (jornada/5)
    var $j;       // jornada do servidor (estendida ou normal) no formato HH:MM
    var $jnd;
    var $dt_jornada;
    var $horas_calculada;
    var $registro_servidor;
    var $excluido;
    var $ocorrencias_total;
    var $ocorrencias_total_horas;
    var $quarta_feira_cinzas;
    var $limite_jornada_corrida;

    ## dados cadastro
    #
    var $oDadosCadastro;
    var $cpf;
    var $siape;               // matricula do servidor
    var $nome;
    var $lotacao;
    var $descricao;
    var $lotacao_request;
    var $anomes_admissao;
    var $anomes_exclusao;
    var $situacao_cadastral;
    var $chefia;
    var $chefiaAtiva; // indica se o servidor eh ocupante de funcao (SISREF)
    var $horario_especial;
    var $horario_especial_data;
    var $horario_especial_processo;
    var $horario_especial_motivo;
    var $registrarForaHorarioEmpresa;
    var $cadastro_entrada;
    var $cadastro_saida;
    var $cadastro_inicio_intervalo;
    var $cadastro_fim_intervalo;
    var $cadastro_jornada;
    var $banco_compensacao;
    var $banco_compensacao_tipo;
    var $homologacao_status;

    ## turno estendido
    #
    var $supervisao_unidade;      // unidade solicitante
    var $supervisao_autorizacao;  // autorizacao do superintendente: 0 (indefinido), 1 (sim), 2 (nao)
    var $supervisao_efetivacao;   // data da concessao
    var $supervisao_encerramento; // data do cancelamento
    var $autorizado_te;           // se turno estendido
    var $turno_estendido;         // jornada (hh:mm)

    ## dados setor
    #
    var $setor_uorg;
    var $setor_upag;
    var $setor_descricao;
    var $setor_uorg_pai;
    var $cod_municipio;
    var $inicio_atendimento;
    var $fim_atendimento;
    var $horario_do_setor_inicio;
    var $horario_do_setor_fim;
    var $indefinida;
    var $todos_zerados;
    var $jornada_negativa;
    var $diferenca_zerada;
    var $diferenca_positiva;
    var $diferenca_negativa;
    var $diferenca_de_jornada;

    ## @constructor
    #+----------------------------+
    #| Construtor da classe       |
    #+----------------------------+
    #
    function formFrequencia()
    {
        parent::formPadrao();
        $this->setLimiteJornadaCorrida('07:01');
        $this->setConexaoBD('sisref');
        $this->initOrigem();
        $this->initOcorrenciasTotal();
        $this->initOcorrenciasTotalHoras();
        $this->initHorasCalculada();
        $this->numeroDiasDoMes();
        $this->setDeduzAlmoco();
        $this->setVoltar(0);
        $this->setHistoryGo(1);
        $this->setRegistroServidor('N');
        $this->sessao_navegacao = new Control_Navegacao();
    }

    ## @metodo
    # Conexao Banco de Dados
    #
    function setConexaoBD($banco = '')
    {
        $obj          = (is_object($banco) ? $banco : '');
        $this->oDBase = new DataBase('PDO');
        $this->oDBase->setMensagem("Tabela " . $tabela_base . " (E300001.".__LINE__.").");
        switch ($banco)
        {
            case 'supervisao':
                $this->oDBase->setHostSupervisao();
                break;
            case 'sisref':
                $this->oDBase->setHostSISREF();
                break;
            default:
                if ($obj != '')
                {
                    $this->oDBase = $obj;
                }
                break;
        }
        return $this->oDBase;
    }

    function getConexaoBD()
    {
        return $this->oDBase;
    }

    ## @metodo
    # Banco de Dados - resultados
    #
    function setResultBD($result = 0)
    {
        $this->dados_result = $result;
    }

    function getResultBD()
    {
        return $this->dados_result;
    }

    ## @metodo
    # Conta Ocorrencias
    #
    function initOcorrenciasTotal()
    {
        $this->ocorrencias_total = array();
    }

    function setOcorrenciasTotal($ocorrencia = '88888', $total = 0)
    {
        $oDBase = selecionaServidor( $this->getSiape() );
        $sitcad = $oDBase->fetch_object()->sigregjur;

        if ($ocorrencia == '88888')
        {
            ## ocorrências grupos
            //$obj = new OcorrenciasGrupos();
            //$ocorrencia = $obj->CodigoSemFrequenciaPadrao($sitcad)[0];
        }

        $this->ocorrencias_total[$ocorrencia] = $total;
    }

    function getOcorrenciasTotal($ind = '88888')
    {
        $oDBase = selecionaServidor( $this->getSiape() );
        $sitcad = $oDBase->fetch_object()->sigregjur;

        if ($ind == '88888')
        {
            ## ocorrências grupos
            //$obj = new OcorrenciasGrupos();
            //$ind = $obj->CodigoSemFrequenciaPadrao($sitcad)[0];
        }

        return $this->ocorrencias_total[$ind];
    }

    function getTamOcorrenciasTotal()
    {
        return count($this->ocorrencias_total);
    }

    ## @metodo
    # Somas horas Ocorrencias
    #
    function initOcorrenciasTotalHoras()
    {
        $this->ocorrencias_total_horas = array();
    }

    function setOcorrenciasTotalHoras($ocorrencia = '88888', $total = '00:00')
    {
        $oDBase = selecionaServidor( $this->getSiape() );
        $sitcad = $oDBase->fetch_object()->sigregjur;

        if ($ocorrencia == '88888')
        {
            ## ocorrências grupos
            //$obj           = new OcorrenciasGrupos();
            //$ocorrencia    = $obj->CodigoSemFrequenciaPadrao($sitcad)[0];
        }

        $this->ocorrencias_total_horas[$ocorrencia] = $total;
    }

    function getOcorrenciasTotalHoras($ind = '88888')
    {
        $oDBase = selecionaServidor( $this->getSiape() );
        $sitcad = $oDBase->fetch_object()->sigregjur;

        if ($ind == '88888')
        {
            ## ocorrências grupos
            //$obj = new OcorrenciasGrupos();
            //$ind = $obj->CodigoSemFrequenciaPadrao($sitcad)[0];
        }

        return $this->ocorrencias_total_horas[$ind];
    }

    function getTamOcorrenciasTotalHoras()
    {
        return count($this->ocorrencias_total_horas);
    }

    ## @metodo
    # limite de jornada corrida
    #
    function setLimiteJornadaCorrida($var = '')
    {
        $this->limite_jornada_corrida = $var;
    }

    function getLimiteJornadaCorrida()
    {
        return $this->limite_jornada_corrida;
    }

    ## @metodo
    # - definir jornada, após verificação se a unidade está em
    #   turno estendido e se ponto facultativo, feriados ou fim de semana
    #
    function defineJornadaDoServidor()
    {
        $jornada = $this->pontoFacultativo('3');  ## - jornada do servidor, por cargo ou horário especial

        # - verifica se dia ponto facultativo e atribui a jornada
        #   correta para o dia (natal, ano novo, quarta-feira de cinzas)
        $turno_estendido = $this->turnoEstendido('3'); ## turno estendido - jornada

        ## se a jornada do servidor for maior que a do turno estendido e turno estendido diferente de zeros,
        #  retornamos a do turno estendido, caso contrário retornamos a jornada do servidor registrada no cadastro,
        $jornada = formata_jornada_para_hhmm(($jornada > $turno_estendido && $turno_estendido != '00:00' ? $turno_estendido : $jornada));

        ## ocupantes de função
        if ($this->getChefiaAtiva() == 'S')
        {
            // - Se titular da função ou em efetiva
            //   substituição, a jornada eh de 40hs
            $jornada = formata_jornada_para_hhmm(40);
        }
        $this->setJornada($jornada);
    }

    ## @metodo
    # código da ocorrência
    #
    function setCodigoOcorrencia($var = '')
    {
        $this->codigo_ocorrencia = $var;
    }

    function getCodigoOcorrencia()
    {
        return $this->codigo_ocorrencia;
    }

    ## @metodo
    # Paginas solicitantes
    #
    function initOrigem()
    {
        $this->origem = array();
    }

    function setOrigem($origem = '', $ind = '')
    {
        if ($ind == '')
        {
            $this->origem[] = $origem;
        }
        else
        {
            $this->origem[$ind] = $origem;
        }
    }

    function getOrigem($ind = 0)
    {
        return $this->origem[$ind];
    }

    ## @metodo
    # Indica se o almoço será deduzido
    #
    function setDeduzAlmoco($var = true)
    {
        $this->deduz_almoco = $var;
    }

    function getDeduzAlmoco()
    {
        return $this->deduz_almoco;
    }

    ## @metodo
    # Ano - Data Atual
    #
    function setAnoHoje($ano = '')
    {
        $this->ano_hoje = $ano;
    }

    function getAnoHoje()
    {
        return $this->ano_hoje;
    }

    ## @metodo
    # Usuario
    #
    function setUsuario($usuario = '')
    {
        $this->usuario = $usuario;
    }

    function getUsuario()
    {
        return $this->usuario;
    }

    ## @metodo
    # Data da frequencia
    #
    function setData($data = '')
    {
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

    ## @metodo
    # Dia da frequencia
    #
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

    ## @metodo
    # Mes da frequencia
    #
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

    ## @metodo
    # Ano da frequencia
    #
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

    ## @metodo
    # Total de dias no mes
    #
    function setTotalDiasMes($dias = 0)
    {
        $this->total_dias_mes = $dias;
    }

    function getTotalDiasMes()
    {
        return $this->total_dias_mes;
    }

    ## @metodo
    # Siape
    #
    function setSiape($siape = '')
    {
        $this->siape = $siape;
    }

    function getSiape()
    {
        return getNovaMatriculaBySiape( $this->siape );
    }

    ## @metodo
    # CPF
    #
    function setCPF($siape = '')
    {
        $this->oDadosCadastro->cpf = $siape;
    }

    function getCPF()
    {
        return $this->oDadosCadastro->cpf;
    }

    ## @metodo
    # Tipo da operacao a realizar
    #
    function setTipoOperacao($cmd = '')
    {
        $this->cmd = $cmd;
    }

    function getTipoOperacao()
    {
        return $this->cmd;
    }

    ## @metodo
    # Nome
    #
    function setNome($nome = '')
    {
        $this->oDadosCadastro->nome = $nome;
    }

    function getNome()
    {
        return $this->oDadosCadastro->nome;
    }

    ## @metodo
    # Lotacao
    #
    function setLotacao($lotacao = '')
    {
        $this->oDadosCadastro->lotacao = $lotacao;
    }

    function getLotacao()
    {
        return $this->oDadosCadastro->lotacao;
    }

    ## @metodo
    # Data jornada
    #
    function setDataLotacao($dt = '')
    {
        $this->oDadosCadastro->data_ingresso_lotacao = $dt;
    }

    function getDataLotacao()
    {
        return $this->oDadosCadastro->data_ingresso_lotacao;
    }

    ## @metodo
    # Lotacao Descricao
    #
    function setLotacaoDescricao($descricao = '')
    {
        $this->oDadosCadastro->descricao = $descricao;
    }

    function getLotacaoDescricao()
    {
        return $this->oDadosCadastro->descricao;
    }

    ## @metodo
    # Lotacao request
    #
    function setLotacaoRequest($request = '')
    {
        $this->lotacao_request = $request;
    }

    function getLotacaoRequest()
    {
        return $this->lotacao_request;
    }

    ## @metodo
    # Chefia
    #
    function setChefia($chefia = '')
    {
        $this->oDadosCadastro->chefe = $chefia;
    }

    function getChefia()
    {
        return $this->oDadosCadastro->chefe;
    }

    ## @metodo
    # Chefia Ativa - Se titular, e quando substituto se está
    #                atuando como chefe substituindo efetivamente
    #
    function setChefiaAtiva($chefia = '')
    {
        $this->oDadosCadastro->chefiaAtiva = $chefia;
    }

    function getChefiaAtiva()
    {
        return $this->oDadosCadastro->chefiaAtiva;
    }

    ## @metodo
    # Autoriza registro fora do limites de Horario da Empresa
    # 07:00:00 às 19:00:00
    #
    function setRegistrarForaHorarioEmpresa($valor = 'S')
    {
        $this->oDadosCadastro->registrarForaHorarioEmpresa = $valor;
    }

    function getRegistrarForaHorarioEmpresa()
    {
        return $this->oDadosCadastro->registrarForaHorarioEmpresa;
    }

    ## @metodo
    # Entrada
    #
    function setEntrada($entrada = '')
    {
        $this->entrada = $entrada;
    }

    function getEntrada()
    {
        return $this->entrada;
    }

    ## @metodo
    # Inicio intervalo
    #
    function setInicioIntervalo($inicio_intervalo = '')
    {
        $this->inicio_intervalo = $inicio_intervalo;
    }

    function getInicioIntervalo()
    {
        return $this->inicio_intervalo;
    }

    ## @metodo
    # Fim intervalo
    #
    function setFimIntervalo($fim_intervalo = '')
    {
        $this->fim_intervalo = $fim_intervalo;
    }

    function getFimIntervalo()
    {
        return $this->fim_intervalo;
    }

    ## @metodo
    # Saida
    #
    function setSaida($saida = '')
    {
        $this->saida = $saida;
    }

    function getSaida()
    {
        return $this->saida;
    }

    ## @metodo
    # Cadastro - Entrada
    #
    function setCadastroEntrada($entrada = '')
    {
        $this->oDadosCadastro->entrada_no_servico = $entrada;
    }

    function getCadastroEntrada()
    {
        return $this->oDadosCadastro->entrada_no_servico;
    }

    ## @metodo
    # Cadastro - Inicio intervalo
    #
    function setCadastroInicioIntervalo($inicio_intervalo = '')
    {
        $this->oDadosCadastro->saida_para_o_almoco = $inicio_intervalo;
    }

    function getCadastroInicioIntervalo()
    {
        return $this->oDadosCadastro->saida_para_o_almoco;
    }

    ## @metodo
    # Cadastro - Fim intervalo
    #
    function setCadastroFimIntervalo($fim_intervalo = '')
    {
        $this->oDadosCadastro->volta_do_almoco = $fim_intervalo;
    }

    function getCadastroFimIntervalo()
    {
        return $this->oDadosCadastro->volta_do_almoco;
    }

    ## @metodo
    # Cadastro - Saida
    #
    function setCadastroSaida($saida = '')
    {
        $this->oDadosCadastro->saida_do_servico = $saida;
    }

    function getCadastroSaida()
    {
        return $this->oDadosCadastro->saida_do_servico;
    }

    ## @metodo
    # horas calculada
    #
    function initHorasCalculada()
    {
        $this->horas_calculada = array();
    }

    function setHorasCalculada($horas_calculada = '')
    {
        $this->horas_calculada[] = $horas_calculada;
    }

    function getHorasCalculada($ind = 0)
    {
        return $this->horas_calculada[$ind];
    }

    function setQuartaFeiraCinzas($logico = false)
    {
        $this->quarta_feira_cinzas = $logico;
    }

    function getQuartaFeiraCinzas()
    {
        return $this->quarta_feira_cinzas;
    }

    ## @metodo
    # Jornada
    #
    function setJornada($jornada = '')
    {
        $this->oDadosCadastro->jornada = $jornada;
    }

    function getJornada()
    {
        return $this->oDadosCadastro->jornada;
    }

    function setJND($jornada = '')
    {
        $this->oDadosCadastro->jnd = $jornada;
    }

    function getJND()
    {
        return $this->oDadosCadastro->jnd;
    }

    function setJ($jornada = '')
    {
        $this->oDadosCadastro->j = $jornada;
    }

    function getJ()
    {
        return $this->oDadosCadastro->j;
    }

    function setJD($jornada = '')
    {
        $this->oDadosCadastro->jd = $jornada;
    }

    function getJD()
    {
        return $this->oDadosCadastro->jd;
    }

    function setCadastroJornada($jornada = '')
    {
        $this->cadastro_jornada = $jornada;
    }

    function getCadastroJornada()
    {
        return $this->cadastro_jornada;
    }

    function setDataJornada($dt_jornada = '')
    {
        $this->oDadosCadastro->data_ingresso_jornada = $dt_jornada;
    }

    function getDataJornada()
    {
        return $this->oDadosCadastro->data_ingresso_jornada;
    }

    ## @metodo
    # Turno Estendido
    #
    function setTurnoEstendido($ativo = 'N')
    {
        $this->turno_estendido = ($ativo == 'S' ? $ativo : 'N');
    }

    function getTurnoEstendido()
    {
        return $this->turno_estendido;
    }

    ## @metodo
    # Inicio atendimento
    #
    function setInicioAtendimento($inicio_atendimento = '')
    {
        $this->oDadosCadastro->horario_do_setor_inicio = $inicio_atendimento;
    }

    function getInicioAtendimento()
    {
        return $this->oDadosCadastro->horario_do_setor_inicio;
    }

    ## @metodo
    # Fim atendimento
    #
    function setFimAtendimento($fim_atendimento = '')
    {
        $this->oDadosCadastro->horario_do_setor_fim = $fim_atendimento;
    }

    function getFimAtendimento()
    {
        return $this->oDadosCadastro->horario_do_setor_fim;
    }

    ## @metodo
    # Codigo municipio
    #
    function setCodigoMunicipio($cod_municipio = '')
    {
        $this->oDadosCadastro->codigo_do_municipio = $cod_municipio;
    }

    function getCodigoMunicipio()
    {
        return $this->oDadosCadastro->codigo_do_municipio;
    }

    ## @metodo
    # setor uorg
    #
    function setSetorUorg($setor_uorg)
    {
        $this->oDadosCadastro->uorg = $setor_uorg;
    }

    function getSetorUorg()
    {
        return $this->oDadosCadastro->uorg;
    }

    ## @metodo
    # setor upag
    #
    function setSetorUpag($setor_upag)
    {
        $this->oDadosCadastro->upag = $setor_upag;
    }

    function getSetorUpag()
    {
        return $this->oDadosCadastro->upag;
    }

    ## @metodo
    # setor descricao
    #
    function setSetorDescricao($setor_descricao)
    {
        $this->setor_descricao = $setor_descricao;
    }

    function getSetorDescricao()
    {
        return $this->setor_descricao;
    }

    ## @metodo
    # setor - uorg pai
    #
    function setSetorUorgPai($setor_uorg_pai)
    {
        $this->oDadosCadastro->uorg_pai = $setor_uorg_pai;
    }

    function getSetorUorgPai()
    {
        return $this->oDadosCadastro->uorg_pai;
    }

    ## @metodo
    # nome do arquivo a trabalhar ponto/temporario
    #
    function setNomeDoArquivo($arquivo = '')
    {
        $this->nome_do_arquivo = $arquivo;
    }

    function getNomeDoArquivo()
    {
        return $this->nome_do_arquivo;
    }

    ## @metodo
    # destino em caso de erro
    #
    function setDestino($destino = '')
    {
        $this->destino = $destino;
    }

    function getDestino()
    {
        return $this->destino;
    }

    ## @metodo
    # voltar em caso de erro
    #
    function setVoltar($var = 1)
    {
        $this->voltar = $var;
    }

    function getVoltar()
    {
        return $this->voltar;
    }

    ## @metodo
    # quantidade de página para retroceder no histórico de url
    #
    function setHistoryGo($historygo = '')
    {
        $this->historygo = $historygo;
    }

    function getHistoryGo()
    {
        return $this->historygo;
    }

    ## @metodo
    # dia util
    #
    function setDiaUtil($dia_util = 'S')
    {
        $this->dia_util = $dia_util;
    }

    function getDiaUtil()
    {
        return $this->dia_util;
    }

    ## @metodo
    # action do form
    #
    function setFormAction($action = '#')
    {
        $this->form_action = $action;
    }

    function getFormAction()
    {
        return $this->form_action;
    }

    ## @metodo
    # submit do form
    #
    function setFormSubmit($submit = '')
    {
        $this->form_submit = ($submit == "" ? "" : $submit);
    }

    function getFormSubmit()
    {
        return $this->form_submit;
    }

    ## @metodo
    # banco de compensacao
    #
    function setBancoCompensacao($banco_compensacao = 'N')
    {
        $this->oDadosCadastro->banco_compensacao = $banco_compensacao;
    }

    function getBancoCompensacao()
    {
        return $this->oDadosCadastro->banco_compensacao;
    }

    ## @metodo
    # banco de compensacao tipo
    #
    function setBancoCompensacaoTipo($banco_compensacao_tipo = '0')
    {
        $this->oDadosCadastro->banco_compensacao_tipo = $banco_compensacao_tipo;
    }

    function getBancoCompensacaoTipo()
    {
        return $this->oDadosCadastro->banco_compensacao_tipo;
    }

    ## @metodo
    # homologacao - status
    #
    function setHomologacaoStatus($homologacao_status = 'N')
    {
        $this->homologacao_status = $homologacao_status;
    }

    function getHomologacaoStatus()
    {
        return $this->homologacao_status;
    }

    ## @metodo
    # data da admissao
    #
    function setDataIngressoNoOrgao($value = '')
    {
        $this->oDadosCadastro->data_ingresso_orgao = $value;
    }

    function getDataIngressoNoOrgao()
    {
        return $this->oDadosCadastro->data_ingresso_orgao;
    }

    ## @metodo
    # data da admissao
    #
    function setAnoMesAdmissao($anomes_admissao = '')
    {
        $this->oDadosCadastro->anomes_admissao = $anomes_admissao;
    }

    function getAnoMesAdmissao()
    {
        return $this->oDadosCadastro->anomes_admissao;
    }

    ## @metodo
    # data exclusao
    #
    function setAnoMesExclusao($anomes_exclusao = '')
    {
        $this->oDadosCadastro->anomes_exclusao = $anomes_exclusao;
    }

    function getAnoMesExclusao()
    {
        return $this->oDadosCadastro->anomes_exclusao;
    }

    ## @metodo
    # horario especial
    #
    function setHorarioEspecial($horario_especial = 'N')
    {
        $this->oDadosCadastro->hora_especial = $horario_especial;
    }

    function getHorarioEspecial()
    {
        return $this->oDadosCadastro->hora_especial;
    }

    ## @metodo
    # horario especial data
    #
    function setHorarioEspecialData($horario_especial_data = '')
    {
        $this->oDadosCadastro->data_hespecial = $horario_especial_data;
    }

    function getHorarioEspecialData()
    {
        return $this->oDadosCadastro->data_hespecial;
    }

    ## @metodo
    # horario especial data
    #
    function setHorarioEspecialDataFim($horario_especial_data = '')
    {
        $this->oDadosCadastro->data_hespecial_fim = $horario_especial_data;
    }

    function getHorarioEspecialDataFim()
    {
        return $this->oDadosCadastro->data_hespecial_fim;
    }

    ## @metodo
    # horario especial processo
    #
    function setHorarioEspecialProcesso($horario_especial_processo = '')
    {
        $this->oDadosCadastro->processo_hespecial = $horario_especial_processo;
    }

    function getHorarioEspecialProcesso()
    {
        return $this->oDadosCadastro->processo_hespecial;
    }

    ## @metodo
    # horario especial motivo
    #
    function setHorarioEspecialMotivo($horario_especial_motivo = '')
    {
        $this->oDadosCadastro->motivo_hespecial = $horario_especial_motivo;
    }

    function getHorarioEspecialMotivo()
    {
        return $this->oDadosCadastro->motivo_hespecial;
    }

    ## @metodo
    # Situacao cadastral
    #
    function setSituacaoCadastral($situacao_cadastral = '')
    {
        $this->oDadosCadastro->situacao_cadastral = $situacao_cadastral;
    }

    function getSituacaoCadastral()
    {
        return $this->oDadosCadastro->situacao_cadastral;
    }

    ## @metodo
    # Registrado por servidor
    #
    function setRegistroServidor($registro_servidor = '')
    {
        $this->registro_servidor = $registro_servidor;
    }

    function getRegistroServidor()
    {
        return $this->registro_servidor;
    }

    ## @metodo
    # Excluído
    #
    function setExcluido($value = '')
    {
        $this->oDadosCadastro->excluido = $value;
    }

    function getExcluido()
    {
        return $this->oDadosCadastro->excluido;
    }

    ## @metodo
    # Sigla Regime Jurídico
    #
    function setSigRegJur($value = '')
    {
        $this->oDadosCadastro->sigregjur = $value;
    }

    function getSigRegJur()
    {
        return $this->oDadosCadastro->sigregjur;
    }

    function get()
    {
        return "";
    }

    ## @metodo
    # obtem dados do servidor
    # nome, codigo da lotacao,
    # jornada de trabalho e se é ocupante de função
    #
    function loadDadosServidor()
    {
        $oJornada = new DefinirJornada();
        $oJornada->setDestino($this->getDestino());
        $oJornada->setVoltar($this->getHistoryGo());
        $oJornada->setSiape($this->getSiape());
        $oJornada->setLotacao($this->getLotacao());
        $oJornada->setData($this->getData());
        $oJornada->estabelecerJornada();
        $this->oDadosCadastro = $oJornada;
        $this->setTurnoEstendido($this->oDadosCadastro->autorizado_te);
    }

    ## @metodo
    # dados do setor
    #
    function loadDadosSetor()
    {
        $this->oDBase->setDestino($this->getDestino());
        $this->oDBase->setVoltar($this->getHistoryGo());

        $this->oDBase->setMensagem("Problemas no acesso a Tabela UNIDADES (E30002.".__LINE__.").");
        $this->oDBase->query("
            SELECT
                und.descricao, und.upag, und.cod_uorg, und.uorg_pai, und.inicio_atend, und.fim_atend, und.codmun
            FROM
                tabsetor AS und
            WHERE
                und.codigo = :lotacao
        ", array(
            array( ':lotacao', $this->getLotacao(), PDO::PARAM_STR )
        ));

        $oSetor = $this->oDBase->fetch_object();

        $this->setInicioAtendimento($oSetor->inicio_atend);
        $this->setFimAtendimento($oSetor->fim_atend);
        $this->setCodigoMunicipio($oSetor->codmun);
        $this->setSetorUorg($oSetor->uorg);
        $this->setSetorUpag($oSetor->upag);
        $this->setSetorDescricao($oSetor->descricao);
        $this->setSetorUorgPai($oSetor->uorg_pai);
    }

    ## @metodo
    # Grava horário
    #
    function gravaHorario($entra_trab = '00:00:00', $ini_interv = '00:00:00', $sai_interv = '00:00:00', $sai_trab = '00:00:00')
    {
        $this->oDadosCadastro->gravaHorario($entra_trab, $ini_interv, $sai_interv, $sai_trab);
    }

    ## @metodo
    # dados do ponto
    #
    function loadDadosPonto($data = "")
    {
        // dados
        $siape = $this->getSiape();
        $mes   = $this->getMes();
        $ano   = $this->getAno();

        $data            = ($data == "" ? "pto.dia <> '0000-00-00' " : "pto.dia = '" . conv_data($data) . "' ");
        $nome_do_arquivo = $this->getNomeDoArquivo();

        $this->oDBase->setDestino($this->getDestino());
        $this->oDBase->setVoltar($this->getHistoryGo());

        $this->oDBase->setMensagem("Problemas no acesso a Tabela de FREQUÊNCIA " . $mes . '/' . $ano . " (E30003.".__LINE__.").");
        $result = $this->oDBase->query("
            SELECT
                pto.entra,
                DATE_FORMAT(pto.dia, '%d/%m/%Y') AS dia,
                pto.intini,
                pto.intsai,
                pto.sai,
                pto.jornd,
                pto.jornp,
                pto.jorndif,
                pto.oco,
                pto.just,
                pto.idreg,
                oco.desc_ocorr AS dcod,
                und.codmun,
                und.codigo,
                pto.idreg,
                pto.ip,
                pto.matchef,
                pto.siaperh
            FROM
                " . $nome_do_arquivo . " AS pto
            LEFT JOIN
                tabocfre AS oco ON pto.oco = oco.siapecad
            LEFT JOIN
                servativ AS cad ON pto.siape = cad.mat_siape
            LEFT JOIN
                tabsetor AS und ON cad.cod_lot = und.codigo
            WHERE
                pto.siape = '" . $siape . "'
                AND " . $data . "
                AND DATE_FORMAT(pto.dia,'%Y%m') = '" . $ano . $mes . "'
            ORDER BY
                pto.dia
        ");

        $this->setResultBD($result);
        $this->setConexaoBD($this->oDBase);
    }

    ## @metodo
    # copias os dados para um arquivo temporario
    #
    function copiaPontoParaTemporario()
    {
        // dados
        $siape           = $this->getSiape();
        $mes             = $this->getMes();
        $ano             = $this->getAno();
        $nome_do_arquivo = $this->getNomeDoArquivo();

        // le dados
        $this->oDBase->setDestino($this->getDestino());
        $this->oDBase->setVoltar($this->getHistoryGo());
        $this->oDBase->setMensagem("Problemas no acesso a Tabela de FREQUÊNCIA/HISTÓRICO " . $mes . '/' . $ano . " (E30004.".__LINE__.").");

        $tabela_base = "ponto" . $mes . $ano;

        if (existeDBTabela( $tabela_base ))
        {
            // cria a tabela com a estrutura sem os dados
            $this->oDBase->query("
                CREATE TABLE " . $nome_do_arquivo . "
                SELECT
                    pto.*,
                    'N' AS acao_executada
                FROM
                    " . $tabela_base . " AS pto
                WHERE
                    pto.siape = :siape
                ",
                array(
                    array( ':siape', $siape, PDO::PARAM_STR ),
                ));

            $this->oDBase->query("
                ALTER TABLE " . $nome_do_arquivo . "
                    ADD PRIMARY KEY (`dia`, `siape`),
                    ADD INDEX `siape` (`siape`),
                    ADD INDEX `dia` (`dia`),
                    ADD INDEX acao_executada (acao_executada)
                ");
        }
    }

    ## @metodo
    # copias os dados arquivo temporario para o ponto
    #
    function copiaTemporarioParaPonto()
    {
        // dados
        $siape = $this->getSiape();
        $data  = conv_data($this->getData());
        $mes   = $this->getMes();
        $ano   = $this->getAno();

        // le dados
        $oDBaseHist = new DataBase('PDO');
        $oDBaseHist->setDestino($this->getDestino());
        $oDBaseHist->setVoltar($this->getHistoryGo());

        $oDBase     = new DataBase('PDO');
        $oDBase->setDestino($this->getDestino());
        $oDBase->setVoltar($this->getHistoryGo());

        $oDBaseHist->setMensagem("Problemas no acesso a Tabela de HISTÓRICO " . $mes . '/' . $ano . " (E30005.".__LINE__.").");
        $oDBaseHist->query("
            SELECT
                dia,
                siape,
                entra,
                intini,
                intsai,
                sai,
                jornd,
                jornp,
                jorndif,
                oco,
                just,
                seq,
                idreg,
                ip,
                ip2,
                ip3,
                ip4,
                justchef,
                ipch,
                iprh,
                matchef,
                siaperh,
                acao_executada
            FROM
                " . $this->getNomeDoArquivo() . "
            WHERE
                siape = '" . $siape . "'
                AND DATE_FORMAT(dia,'%Y%m') = '" . $ano . $mes . "'
                AND acao_executada <> 'N'
        ");

        $this->gravaHistPonto();

        while ($oPonto = $oDBaseHist->fetch_object())
        {
            // dados do ponto temporario
            $setValores = "
                dia      = '" . $oPonto->dia . "',
                siape    = '" . $oPonto->siape . "',
                entra    = '" . $oPonto->entra . "',
                intini   = '" . $oPonto->intini . "',
                intsai   = '" . $oPonto->intsai . "',
                sai      = '" . $oPonto->sai . "',
                jornd    = '" . $oPonto->jornd . "',
                jornp    = '" . $oPonto->jornp . "',
                jorndif  = '" . $oPonto->jorndif . "',
                oco      = '" . $oPonto->oco . "',
                just     = '" . $oPonto->just . "',
                seq      = '" . $oPonto->seq . "',
                idreg    = '" . $oPonto->idreg . "',
                ip       = '" . $oPonto->ip . "',
                ip2      = '" . $oPonto->ip2 . "',
                ip3      = '" . $oPonto->ip3 . "',
                ip4      = '" . $oPonto->ip4 . "',
                justchef = '" . $oPonto->justchef . "',
                ipch     = '" . $oPonto->ipch . "',
                iprh     = '" . $oPonto->iprh . "',
                matchef  = '" . $oPonto->matchef . "',
                siaperh  = '" . $oPonto->siaperh . "'
            ";

            $oDBase->setMensagem("Problemas no acesso a Tabela de FREQUÊNCIA " . $mes . '/' . $ano . " (E30026.".__LINE__.").");
            $oDBase->query("SELECT * FROM ponto" . $mes . $ano . " WHERE siape = '" . $siape . "' AND dia = '" . $oPonto->dia . "' ");
            $existe_registro = $oDBase->num_rows();

            if ($existe_registro == 0)
            {
                $oDBase->setMensagem("Problemas no acesso a Tabela de FREQUÊNCIA " . $mes . '/' . $ano . " (E30027.".__LINE__.").");
                $oDBase->query("INSERT ponto" . $mes . $ano . " SET " . $setValores);
            }
            elseif ($oPonto->acao_executada == "S")
            {
                gravar_historico_ponto($siape, $oPonto->dia, 'A');
                $oDBase->setMensagem("Problemas no acesso a Tabela de FREQUÊNCIA " . $mes . '/' . $ano . " (E30028.".__LINE__.").");
                $oDBase->query("UPDATE ponto" . $mes . $ano . " SET " . $setValores . " WHERE siape = '" . $siape . "' AND dia='" . $oPonto->dia . "' ");
            }
            $oDBase->setMensagem("Problemas no acesso a Tabela de FRQ " . $ano . " (E30029.".__LINE__.").");
            $oDBase->query("UPDATE usuarios SET recalculo='S', refaz_frqano='S' WHERE siape='" . $siape . "' ");
        }

        $oDBase->setMensagem("Problemas no acesso a Tabela de FREQUÊNCIA " . $mes . '/' . $ano . " (E30030.".__LINE__.").");
        $oDBase->query("DROP TABLE IF EXISTS " . $this->getNomeDoArquivo());
    }

    ## @metodo
    # grava em historico (log) as alterações realizadas
    #
    function gravaHistPonto()
    {
        // dados
        $siape = $this->getSiape();
        $data  = conv_data($this->getData());
        $mes   = $this->getMes();
        $ano   = $this->getAno();

        //pegando o ip do usuario
        $ip    = getIpReal(); //linha que captura o ip do usuario.

        sleep(1);

        // le dados
        $this->oDBase->setDestino($this->getDestino());
        $this->oDBase->setVoltar($this->getHistoryGo());

        $this->oDBase->setMensagem("Problemas no acesso a Tabela de FREQUÊNCIA/HISTÓRICO " . $mes . '/' . $ano . " (E30007.".__LINE__.").");
        $this->oDBase->query("
            INSERT
                histponto" . $mes . $ano . "
            SELECT DISTINCTROW
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
                pto.idreg,
                pto.ip,
                pto.ip2,
                pto.ip3,
                pto.ip4,
                pto.ipch,
                pto.iprh,
                IFNULL(pto.matchef,'') AS matchef,
                IFNULL(pto.siaperh,'') AS siaperh,
                DATE_FORMAT(NOW(),'%Y-%m-%d') AS diaalt,
                DATE_FORMAT(NOW(),'%H:%i:%s') AS horaalt,
                '" . $siape . "' AS siapealt,
                '" . $ip . "' AS ipalt,
                'A' AS idaltexc,
                pto.just,
                pto.justchef
            FROM
                " . $this->getNomeDoArquivo() . " AS htemp
            LEFT JOIN
                ponto" . $mes . $ano . " AS pto ON htemp.siape = pto.siape AND htemp.dia = pto.dia
            WHERE
                htemp.siape = '" . $siape . "'
                AND DATE_FORMAT(htemp.dia,'%Y%m') = '" . $ano . $mes . "'
                AND htemp.acao_executada <> 'N'
                AND IFNULL(pto.dia,'') <> ''
        ");
    }


    ## @metodo
    # inclusao de sabados e domindos,
    # feriados nacionais, estaduais, municipais
    # e dias sem registro de frequência
    #
    function inserirDiasSemFrequencia($grava99999 = true, $mes_homologacao = false)
    {
        $oDBase = selecionaServidor( $this->getSiape() );
        $sitcad = $oDBase->fetch_object()->sigregjur;

        ## ocorrências grupos
        $obj = new OcorrenciasGrupos();
        $codigoSemFrequenciaPadrao      = $obj->CodigoSemFrequenciaPadrao($sitcad);
        $codigoFrequenciaNormalPadrao   = $obj->CodigoFrequenciaNormalPadrao($sitcad);
        $codigoBancoDeHorasDebitoPadrao = $obj->CodigoBancoDeHorasDebitoPadrao($sitcad);

        // dados
        $mat             = $this->getSiape();
        $dia             = $this->getDia();
        $mes             = $this->getMes();
        $ano             = $this->getAno();
        $jornada         = $this->getJornada();
        $lotacao         = $this->getLotacao();
        $nome_do_arquivo = $this->getNomeDoArquivo();

        if ($mes_homologacao == true)
        {
            $oData = new trata_datasys();
            $ano   = $oData->getAnoHomologacao();
            $mes   = $oData->getMesHomologacao();
        }

        $ano = substr("0000" . (empty($ano) ? date("Y") : $ano), -4);
        $mes = substr("00" . (empty($mes) ? date("n") : $mes), -2);

        if ($this->getOrigem(1) == "historico_frequencia.php" || $mes_homologacao == true)
        {
            $dia = date("t", mktime(0, 0, 0, $mes, 1, $ano));
        }
        else
        {
            $dia = (empty($dia) ? date('d') : $dia);
            $dia = date('d/m/Y', mktime(0, 0, 0, date("m"), $dia - ($dia == 1 ? 0 : 1), date("Y")));
            $ano = substr($dia, -4);
            $mes = substr($dia, 3, 2);
            $dia = substr($dia, 0, 2);
        }

        $diaFim = $dia;
        $diaIni = 1;

        $nome_do_arquivo = ( empty($nome_do_arquivo) ? "ponto$mes$ano" : $nome_do_arquivo);

        // conexao com a base de dados
        $this->oDBase->setDestino($this->getDestino());
        $this->oDBase->setVoltar($this->getHistoryGo());

        // instancia definir joranda
        $oDefinirJornada = new DefinirJornada();

        for ($nInd = $diaIni; $nInd <= $diaFim; $nInd++)
        {
            $dia = date($ano . '-' . $mes . '-' . substr("0" . $nInd, -2));

            $this->setData(databarra($dia));

            $oBaseJornada = $oDefinirJornada->PesquisaJornadaHistorico($mat, $this->getData());
            $oJornada     = $oBaseJornada->fetch_object();
            $this->setJornada($oJornada->jornada);
            $jornada      = $this->formataJornadaParaHHMM();

            if (eh_ponto_facultativo($this->getData()) == true)
            {
                $jornada = $this->pontoFacultativo(); // verifica se dia ponto facultativo
                // e atribui a jornada correta para o dia
            }

            $bDiaUtil = $this->verificaSeDiaUtil(); // feriado nacional, estadual ou municipal e sábado ou domingo
            // retorna N se feriado ou fim de semana, caso contrário S
            // verifica qual a situacao do dia
            if ($bDiaUtil == false)
            {
                $ocor  = $codigoFrequenciaNormalPadrao[0]; //"00000"; // frequência normal
                $jornp = '00:00';
            }
            else
            {
                $ocor  = $codigoSemFrequenciaPadrao[0]; //"99999"; // sem frequência
                $jornp = $jornada;
                if ($grava99999 == false)
                {
                    continue;
                }
            }

            // se a data atual igual a data para inserção
            // e não for dia útil e estiver autorizado trabalho
            // neste dia, então não registra ocorrência
            if ($dia == date('Y-m-d'))
            {
                $autoriza = autorizacaoDiaNaoutil($dia, $mat);
            }
            else
            {
                $autoriza = "N";
            }

            //verifica se o dia foi registrado pelo servidor
            if ($autoriza == "N")
            {
                //verifica se o dia já foi registrado
                $this->oDBase->setMensagem("Problemas no acesso a Tabela de FREQUÊNCIA/HISTÓRICO " . $mes . '/' . $ano . " (E30008.".__LINE__.").");
                $this->oDBase->query(
                    "SELECT a.entra, DATE_FORMAT(a.dia, '%d/%m/%Y') AS dia, a.intini, a.intsai, a.sai, a.jornd, a.jornp, a.jorndif, a.oco, a.just, a.idreg, a.ip, a.ip2, a.ip3, a.ip4, a.ipch, a.iprh FROM " . $nome_do_arquivo . " AS a WHERE siape = :siape AND dia = :dia ORDER BY dia ", array(
                    array(":siape", $mat, PDO::PARAM_STR),
                    array(":dia",   $dia, PDO::PARAM_STR)
                    )
                );
                $linhas = $oDBase->num_rows();

                if ($linhas == 0)
                {
                    $historico = "historico_temp_" . $mat;
                    $sql       = "INSERT INTO $nome_do_arquivo SET dia= :dia, siape= :siape, jornp= :jornp, jorndif= :jorndif, oco= :ocor, idreg='X', ip='', ip2='', ip3='', ip4='', ipch='', iprh= ''" . ($nome_do_arquivo == $historico ? ", acao_executada='I' " : " ");
                    $this->oDBase->setMensagem("Problemas no acesso a Tabela de FREQUÊNCIA/HISTÓRICO " . $mes . '/' . $ano . " (E30009.".__LINE__.").");
                    $this->oDBase->query($sql, array(
                        array(":dia",     $dia,   PDO::PARAM_STR),
                        array(":siape",   $mat,   PDO::PARAM_STR),
                        array(":jornp",   $jornp, PDO::PARAM_STR),
                        array(":jorndif", $jornp, PDO::PARAM_STR),
                        array(":ocor",    $ocor,  PDO::PARAM_STR)
                        )
                    );
                }
            }
        }
    }

    ## @metodo
    # validacao dos dados
    # passados por formulario
    # tipo == 1: ocorrencia e horarios
    #
    function validaParametros($tipo = '0')
    {
        $oDBase = selecionaServidor( $this->getSiape() );
        $sitcad = $oDBase->fetch_object()->sigregjur;

        ## ocorrências grupos
        $obj = new OcorrenciasGrupos();
        $codigoDebitoRecessoPadrao     = $obj->CodigoDebitoRecessoPadrao($sitcad);
        $codigoDebitoInstrutoriaPadrao = $obj->CodigoDebitoInstrutoriaPadrao($sitcad);
        $grupoOcorrenciasViagem        = $obj->GrupoOcorrenciasViagem($sitcad);
        $codigoFrequenciaNormalPadrao  = $obj->CodigoFrequenciaNormalPadrao($sitcad);
        $eventosEsportivos             = $obj->EventosEsportivos();
        $estagiariosReduzMetade        = $obj->EstagiariosReduzMetade();
        $saldoZerado                   = $obj->SaldoZerado($sitcad);
        $saldoDiferenca                = $obj->SaldoDiferenca($sitcad,$viagem=false);
        $saldoDiferencaHistorico       = $obj->SaldoDiferenca($sitcad,$viagem=true);
        $horariosZerados               = $obj->HorariosZerados($sitcad,$viagem=true);
        $horariosZeradosHistorico      = $obj->HorariosZerados($sitcad,$viagem=false);


        // data
        $data = $this->getData();

        // horários informados
        $entrada       = time_to_sec($this->getEntrada());
        $almoco_inicio = time_to_sec($this->getInicioIntervalo());
        $almoco_fim    = time_to_sec($this->getFimIntervalo());
        $saida         = time_to_sec($this->getSaida());

        $this->loadDadosServidor();
        $jornada = $this->getJornada();

        $nome  = $this->getNome();    // Nome do servidor registrado no SIAPE
        $lot   = $this->getLotacao(); // Codigo literal da unidade de trabalho do servidor
        $entra = $this->getCadastroEntrada();  // horário de entrada
        $iniin = $this->getCadastroInicioIntervalo(); // horário da saida para o almoco
        $fimin = $this->getCadastroFimIntervalo();     // horário do retorno do almoco
        $sai   = $this->getCadastroSaida();    // final do expediente
        $jnd   = $jornada;
        $j     = formata_jornada_para_hhmm($jnd);

        // le dados do setor
        $this->loadDadosSetor();

        // ocupantes de função
        $ocupaFuncao = $this->getChefiaAtiva(); // $this->getChefiaAtiva();

        if ($ocupaFuncao == 'S')
        {
            // - Se titular da função ou em efetiva
            //   substituição, a jornada eh de 40hs
            $jnd = 40;
            $j   = formata_jornada_para_hhmm($jnd); // compatibilidade
            $this->setJornada($jnd);
            $this->setJ($j);
        }

        // le dados da frequencia
        $this->loadDadosPonto($data);
        $oPontoResult = $this->getConexaoBD();
        $nRows        = $oPontoResult->num_rows();

        if ($nRows > 0 && ((substr_count($_SESSION['sHArquivoTemp'], 'historico_temp_') == 0 && in_array($this->getCodigoOcorrencia(), $codigoDebitoInstrutoriaPadrao) == false) && $entrada == 0))
        {
            $oPonto        = $oPontoResult->fetch_object();
            $entrada       = time_to_sec($oPonto->entra);
            $almoco_inicio = time_to_sec($oPonto->intini);
            $almoco_fim    = time_to_sec($oPonto->intsai);
            $saida         = time_to_sec($oPonto->sai);
        }
        elseif ($entrada != 0)
        {
            $entrada       = ($almoco_inicio == 0 && $saida == 0 ? 0 : $entrada);
            $almoco_inicio = ($entrada == 0 || $almoco_inicio == 0 ? 0 : $almoco_inicio);
            $almoco_fim    = ($entrada == 0 || $almoco_inicio == 0 ? 0 : $almoco_fim);
            $saida         = ($entrada == 0 && $saida == 0 ? 0 : $saida);
        }
        else
        {
            $entrada       = 0;
            $almoco_inicio = 0;
            $almoco_fim    = 0;
            $saida         = 0;
        }

        // re-atribui horários informados
        $this->setEntrada(right(sec_to_time($entrada), 8));           // horário de entrada
        $this->setInicioIntervalo(right(sec_to_time($almoco_inicio), 8)); // horário da saida para o almoco
        $this->setFimIntervalo(right(sec_to_time($almoco_fim), 8));    // horário do retorno do almoco
        $this->setSaida(right(sec_to_time($saida), 8));                // final do expediente

        // Verifica se é natal, ano novo ou quarta feira de cinzas.
        $this->pontoFacultativo();

        // Verifica se dia útil
        $destino   = $this->getDestino(); // destino em caso de erro
        $this->setDestino(($tipo == 1 && $destino == '' ? $_SESSION['sHOrigem_4'] : ($tipo == 2 && $destino == '' ? $_SESSION['sHOrigem_3'] : $destino))); // destino em caso de erro
        $this->verificaSeDiaUtil();
        $this->setDestino(($destino == '' ? $_SESSION['sPaginaRetorno_erro'] : $destino)); // destino em caso de erro

        // validacao dos campos
        $validacao = new valida();
        $validacao->setExibeMensagem(false);
        $validacao->setDestino($this->getDestino());
        $validacao->setVoltar($this->getHistoryGo());

        // ocorrencia
        $ocor = ($this->getCodigoOcorrencia() == '' ? 'x' : $this->getCodigoOcorrencia());

        $jornada = $this->getJornada();

        $dia_util = $this->getDiaUtil();

        // ocorrencias
        if (substr_count($_SESSION['sHArquivoTemp'], 'historico_temp_') > 0 && $entrada != 0 && in_array($ocor,$grupoOcorrenciasViagem))
        {
            $ocorrencia_Zerar     = $horariosZeradosHistorico;
            $ocorrencia_Diferenca = $saldoDiferencaHistorico;
        }
        else if ($entrada != 0 && in_array($ocor,$codigoFrequenciaNormalPadrao))
        {
            // Frequência Normal
            //$ocorrencia_Zerar     = array();
            //$ocorrencia_Diferenca = array();
        }
        else
        {
            $ocorrencia_Zerar     = $horariosZerados;
            $ocorrencia_Diferenca = $saldoDiferenca;
        }
        $ocorrencia_estagiario   = $estagiariosReduzMetade;
        $ocorrencia_SemDiferenca = $saldoZerado;
        $ocorrencia_ReduzMetade  = $estagiariosReduzMetade; // 02929: Normal; 12929: Igual a 00172; e 32929: Igual a 33333


        // VALIDAÇÃO
        $limite_horas_no_dia = time_to_sec('23:59:59');

        if ($entrada > $limite_horas_no_dia)
        {
            $validacao->setMensagem('- Horário de início do expediente não pode ser maior que 23:59:59!\\n');
        }
        else if ($almoco_inicio > $limite_horas_no_dia)
        {
            $validacao->setMensagem('- Horário de início do intervalo não pode ser maior que 23:59:59!\\n');
        }
        else if ($almoco_fim > $limite_horas_no_dia)
        {
            $validacao->setMensagem('- Horário de retorno do intervalo não pode ser maior que 23:59:59!\\n');
        }
        else if ($saida > $limite_horas_no_dia)
        {
            $validacao->setMensagem('- Horário de saída não pode ser maior que 23:59:59!\\n');
        }

        $validacao->exibeMensagem(); // exibe mensagem de erro se houver


        // se não há registro de horário e trata-se
        // de ocorrencia de viagem em objeto de serviço
        if ($entrada == 0 && in_array($ocor, $ocorrencia_Zerar))
        {
            $this->initHorasCalculada();
            $this->setHorasCalculada($ocor);   // código da ocorrência
            $this->setHorasCalculada('00:00'); // horas trabalhadas no dia
            $this->setHorasCalculada('00:00'); // jornada prevista para o dia
            $this->setHorasCalculada('00:00'); // diferenca no dia
        }
        else if (in_array($ocor, $ocorrencia_SemDiferenca))
        {
            // Calculo da diferença no dia
            $aDados = $this->calculaHorasTrabalhadas();
            $this->initHorasCalculada();
            $this->setHorasCalculada($ocor);      // código da ocorrência
            $this->setHorasCalculada($aDados[1]); // horas trabalhadas no dia
            $this->setHorasCalculada($aDados[2]); // jornada prevista para o dia
            $this->setHorasCalculada('00:00');    // diferenca no dia
        }
        else if (substr_count($ocorrencia_SemJornada, $ocor) > 0)
        {
            $this->initHorasCalculada();
            $this->setHorasCalculada($ocor);   // código da ocorrência
            $this->setHorasCalculada('00:00'); // horas trabalhadas no dia
            $this->setHorasCalculada('00:00'); // jornada prevista para o dia
            $this->setHorasCalculada('00:00'); // diferenca no dia
            $this->setJornada('00:00');
        }
        /*
          elseif (in_array($ocor, $ocorrencia_ReduzMetade))
          {
          $this->initHorasCalculada();
          $this->setHorasCalculada( $ocor );   // código da ocorrência
          $this->setHorasCalculada( '00:00' ); // horas trabalhadas no dia
          $this->setHorasCalculada( '00:00' ); // jornada prevista para o dia
          $this->setHorasCalculada( '00:00' ); // diferenca no dia
          $this->setJornada( '00:00' );
          }
         */
        // há registro de horário
        else
        {
            if (in_array($ocor, $ocorrencia_Diferenca) && in_array($this->getCodigoOcorrencia(), $codigoDebitoInstrutoriaPadrao) == false) //'02525'
            {
                $limite_minimo_hora_almoco = time_to_sec('01:00');
                $sLimiteJornadaCorrida     = time_to_sec($this->getLimiteJornadaCorrida());
                $intervalo                 = ($almoco_fim > $almoco_inicio ? ($almoco_fim - $almoco_inicio) : 0);
                $jorndia                   = ($saida > $entrada ? ($saida - $entrada) : 0);
                $jornada_realizada         = ($jorndia - $intervalo);

                if ($jornada_realizada > $sLimiteJornadaCorrida && (($almoco_inicio != 0 && $entrada > $almoco_inicio) || ($almoco_fim != 0 && $entrada > $almoco_fim) || $entrada > $saida))
                {
                    $validacao->setMensagem('- Hora do inicio do expediente não pode ser maior que os horários seguintes!\\n');
                }
                if (($almoco_inicio != 0 && $almoco_inicio >= $almoco_fim) || $almoco_inicio > $saida)
                {
                    $validacao->setMensagem('- Hora do início do intervalo deve ser menor que fim do intervalo e fim do expediente!\\n');
                }
                if ($almoco_fim > $saida)
                {
                    $validacao->setMensagem('- Hora do fim do intervalo deve ser menor que fim do expediente!\\n');
                }


                $diferencaHoras = ($almoco_fim - $almoco_inicio );

                if ($jornada_realizada > $sLimiteJornadaCorrida && ($this->getRegistroServidor() == 'S' && (($almoco_fim > 0 || $almoco_inicio > 0) && $intervalo < $limite_minimo_hora_almoco)))
                {
                    $validacao->setMensagem('- O intervalo deve ser igual ou maior que uma hora!\\n'); // define mensagem de erro
                }
                if ($jornada_realizada > $sLimiteJornadaCorrida && ($this->getRegistroServidor() == 'N' && (($almoco_fim > 0 || $almoco_inicio > 0) && $intervalo < $limite_minimo_hora_almoco)))
                {
                    $validacao->setMensagem("- Registrada " . sec_to_time($jornada_realizada, 'hh:mm') . " de jornada realizada e intervalo do almoço inválido!\\n"); // define mensagem de erro
                }
                if (($almoco_fim > 0 || $almoco_inicio > 0) && $intervalo < $limite_minimo_hora_almoco)
                {
                    $validacao->setMensagem('- O intervalo não pode ser menor que uma hora!\\n'); // define mensagem de erro
                }

                $data_invertida = $this->getAno() . '-' . $this->getMes() . '-' . $this->getDia();

                $oDBase         = new DataBase('PDO');

                $oDBase->setMensagem("Problemas no acesso a Tabela FACULTATIVO (E30010.".__LINE__.").");
                $oDBase->query("SELECT dia FROM tabfacultativo172 WHERE codigo_debito = '" . $ocor . "' AND dia='" . $data_invertida . "' ");

                if (in_array($ocor, $eventosEsportivos) && $oDBase->num_rows() == 0)
                {
                    $msg6 = array(
                        '62010' => 'aos dias 15, 20, 25 e 28 de 06/2010, e, dia 02.07.2010, jogos do Brasil na Copa do Mundo 2010',
                        '62012' => 'aos dias 20, 21 e 22 de 06/2012, RIO+20',
                        '62014' => 'aos dias 12, 13, 16, 17, 18, 19, 20, 23, 24, 25, 26 e 30 do mês 06/2014, e, 01, 04, 08 e 09 do mês 07/2014'
                    );
                    $validacao->setMensagem("- Código '" . $ocor . "', uso restrito " . $msg6[$ocor] . "!\\n");
                }
                else if (in_array($ocor, $eventosEsportivos) && $entrada == 0 && $almoco_inicio == 0 && $almoco_fim == 0 && $saida == 0)
                {
                    $tipo = '0';
                }
                else if (in_array($ocor,$codigoDebitoRecessoPadrao) && $jornada_realizada == 0)
                {

                }
                else
                {
                    // código de ocorrência
                    $validacao->ocorrencia($ocor);

                    // inicio do expediente
                    $validacao->horario(right(sec_to_time($entrada), 8), "- Horário de início do expediente inválido/não informado !");

                    // intervalo do almoço
                    if ($this->getRegistroServidor() == 'S' && $tipo != '1' && $tipo != '2')
                    {
                        // início do intervalo
                        $validacao->horario(right(sec_to_time($almoco_inicio), 8), "- Horário de início do intervalo inválido/não informado !");

                        // retorno do intervalo
                        $validacao->horario(right(sec_to_time($almoco_fim), 8), "- Horário de retorno do intervalo inválido/não informado !");
                    }

                    // fim do expediente
                    $validacao->horario(right(sec_to_time($saida), 8), "- Horário de saída inválido/não informado !");
                }
            }
            else if (in_array($ocor, $ocorrencia_estagiario) && $this->getSituacaoCadastral() != '66')
            {
                $validacao->setMensagem("- Código '" . $ocor . "', aplicado apenas a estagiário!\\n");
            }
            else
            {
                $tipo = '0';
            }

            // Recalculo das horas do dia alterado, horas no intervalo para quatro marcações
            // calculo das horas do dia, calculo da dif do dia
            $oResultado       = $this->processaOcorrencias();
            $this->initHorasCalculada();
            $this->setHorasCalculada($oResultado->ocorrencia); // código da ocorrência
            $this->setHorasCalculada($oResultado->jornada_realizada); // horas trabalhadas no dia
            $this->setHorasCalculada($oResultado->jornada_prevista); // jornada prevista para o dia
            $this->setHorasCalculada($oResultado->jornada_diferenca); // diferenca no dia

            // Verificando se a jornada do dia é inferior a do cargo e gravando se for menor
            $jornada_do_dia   = $oResultado->jornada_realizada;
            $jornada_prevista = $oResultado->jornada_prevista;

            // verifica se as horas trabalhadas estão
            // superior ou inferior a jornada prevista
            // tipo == 1: verifica se está maior
            // tipo == 2: verifica se está menor


            // Verificando se a jornada do dia é inferior a do cargo e gravando se for menor
            $jornada_do_dia   = $oResultado->jornada_realizada;
            $jornada_prevista = $oResultado->jornada_prevista;

            if ($tipo == '1' && time_to_sec($jornada_do_dia) <= time_to_sec($jornada_prevista) && $this->getDiaUtil() == "S")
            {
                $validacao->setMensagem("- Resultado do Horário informado não é superior à jornada prevista do servidor!\\n\\tResultado: " . $jornada_do_dia . ";\\n\\tJornada prevista: " . $jornada_prevista);
            }
            if ($tipo == '2' && $jornada_do_dia >= $jornada_prevista && $this->getDiaUtil() == "S")
            {
                $validacao->setMensagem("- Resultado do Horário informado não é inferior à jornada prevista do servidor!\\n\\tResultado: " . $jornada_do_dia . ";\\n\\tJornada prevista: " . $jornada_prevista);
            }

            $validacao->exibeMensagem(); // exibe mensagem de erro se houver
       }
    }

    ## @metodo
    # calcula horas trabalhadas no dia
    # estabelecendo diferenca e ocorrencia
    #
    function calculaHorasTrabalhadas_old()
    {
        $oDBase = selecionaServidor( $this->getSiape() );
        $sitcad = $oDBase->fetch_object()->sigregjur;

        ## ocorrências grupos
        $obj = new OcorrenciasGrupos();
        $codigoFrequenciaNormalPadrao = $obj->CodigoFrequenciaNormalPadrao($sitcad);
        $codigoCreditoPadrao          = $obj->CodigoCreditoPadrao($sitcad);
        $codigoDebitoPadrao           = $obj->CodigoDebitoPadrao($sitcad);


        # dados
        #
        $data          = $this->getData();
        $data_registro = conv_data($data);
        $data_hoje     = date('Y-m-d');
        $entrada       = $this->getEntrada();
        $almoco_inicio = $this->getInicioIntervalo();
        $almoco_fim    = $this->getFimIntervalo();
        $saida         = $this->getSaida();
        $this->pontoFacultativo();

        // formataJornadaParaHHMM(): formata a jornada para HH:MM sendo
        // informado 40 (horas semanais) ou 08:00 (horas diárias)
        //
        $jornada_prevista = ($this->getDiaUtil() == 'S' ? $this->formataJornadaParaHHMM() : '00:00' );

        //$limite_hora_extra = ($this->getBancoCompensacao() == 'S' ? '02:00' : '00:00'); // limite de horas extras por dia
        $limite_hora_extra = '02:00'; // limite de horas extras por dia
        $limite_hora_dia   = adicionaHoras($this->formataJornadaParaHHMM(), $limite_hora_extra); // limite de horas totais no dia

        // diferenca entre saida do almoco e retorno
        $duracao_do_almoco = ($almoco_inicio > $almoco_fim ? "00:00" : diferencaHoras($almoco_inicio, $almoco_fim) );

        $duracao_expediente = diferencaHoras($entrada, $saida); // horas entre o inicio e fim do expediente
        $jornada_do_dia     = diferencaHoras($duracao_do_almoco, $duracao_expediente); //calculo da jornada do dia

        $HorarioEspecial       = $this->getHorarioEspecial();
        $HorarioEspecialMotivo = $this->getHorarioEspecialMotivo();

        $sLimiteJornadaCorrida = $this->getLimiteJornadaCorrida();

        if ($duracao_do_almoco == '00:00' && $jornada_do_dia > $sLimiteJornadaCorrida)
        {
            # Retornamos ao teste anterior, não há respaldo legal
            # para a jornada corrida de oito horas no caso de servidor estudante
            #
            $duracao_do_almoco = '03:00'; //($this->getJornada() == "08:00" || $this->getBancoCompensacao() == 'S' || $this->getRegistroServidor() == 'N' ? '03:00' : '00:00');
            $jornada_do_dia    = diferencaHoras($duracao_do_almoco, $duracao_expediente); //($this->getBancoCompensacao() == 'S' && $this->getRegistroServidor() == 'S' ? diferencaHoras( $duracao_do_almoco, $duracao_expediente ) : $this->getJornada());
        }

        $diferenca = diferencaHoras($jornada_do_dia, $jornada_prevista);

        if ($jornada_do_dia == $jornada_prevista)
        {
            $diferenca         = ($this->getDiaUtil() == 'S' && $this->getQuartaFeiraCinzas() == false ? '00:00' : $jornada_prevista);
            $codigo_ocorrencia = ($this->getDiaUtil() == 'S' && $this->getQuartaFeiraCinzas() == false ? $codigoFrequenciaNormalPadrao[0] : $codigoCreditoPadrao[0]);
        }
        elseif ($jornada_do_dia > $jornada_prevista)
        {
            if ($this->getBancoCompensacao() == 'S' || $this->getRegistroServidor() == 'N') // && ($data_registro == $data_hoje))
            {
                if ($this->getDiaUtil() == 'S') // && $this->getQuartaFeiraCinzas() == false)
                {
                    $diferenca = ($diferenca > $limite_hora_extra ? $limite_hora_extra : $diferenca);
                }
                else //if ($this->getQuartaFeiraCinzas() == false)
                {
                    $diferenca = ($jornada_do_dia > $limite_hora_dia ? $limite_hora_dia : $jornada_do_dia);
                }
                $codigo_ocorrencia = $codigoCreditoPadrao[0];
            }
            else
            {
                $diferenca         = '00:00';
                $codigo_ocorrencia = $codigoFrequenciaNormalPadrao[0];
            }
        }
        else
        {
            if ($this->getDiaUtil() == 'S')
            {
                //Implementar busca para saber se é dia da copa
                $oDBase            = new DataBase('PDO');
                $oDBase->setMensagem("Problemas no acesso a Tabela FACULTATIVO (E300011.".__LINE__.").");
                $oDBase->query("SELECT codigo_debito FROM tabfacultativo172 WHERE dia = :dia AND ativo = 'S' ",
                        array(
                            array( ':dia', conv_data($data), PDO::PARAM_STR ),
                        ));
                $codigo_ocorrencia = ($oDBase->num_rows() == 0 ? $codigoDebitoPadrao[0] : $oDBase->fetch_object()->codigo_debito);
            }
            else
            {
                $diferenca         = ($jornada_do_dia > $limite_hora_dia ? $limite_hora_dia : $jornada_do_dia);
                $codigo_ocorrencia = $codigoCreditoPadrao[0];
            }
        }

        $aHoras = array($codigo_ocorrencia, substr($jornada_do_dia, 0, 5), substr($jornada_prevista, 0, 5), substr($diferenca, 0, 5));
        return $aHoras;
    }

    /*
     * processa os dados conforme a ocorrencia informada.
     * - se o resultados dos cálculos dos horários informados
     *   forem incompatíveis com o código de ocorrência indicado
     *   o sistema retorna a ocorrência adequada e informa que os
     *   dados não condizem com a ocorrência escolhida pelo usuário.
     * - se não foi indicada ocorrência, o sistema retorna a ocorrência
     *   adequada aos resultados dos cálculos.
     *   Ex.: 00000 (frequencia normal)
     *               se a diferença encontrada for 00:00, ou compensação não autorizada,
     *               o resultado será 00:00;
     *        33333 (compensação)
     *               quando houver horas excedentes e a compensação for autorizada;
     *        00172 (horas negativas)
     *               quando houver horas devidas por atraso, saída antecipada, etc.
     *               Estas horas poderão ser compensadas se a chefia imediata autorizar.
     *
     */
    function processaOcorrencias()
    {
        // grupo de ocorrencias
        //
			// indefinida........: Tratamento indefinido, requer intervenção do usuário (Chefia/SGOP);
        // jornada_negativa..: Se houver horários registrados serão zerados, a jornada prevista é
        //                     registrada e  diferença da jornada será negativa;
        // diferenca_zerada..: Se houver dados serão mantidos, senão os valores serão zeros.
        //                     A diferença da jornada será '00:00';
        // diferenca_negativa: Cálcula a jornada realizada e deduz a jornada prevista para obter a
        //                     diferença.
        //                     - Diferença nula, altera a ocorrência para '00000';
        //                     - Diferença positiva, altera a ocorrência para '33333';
        //                     - Diferença negativa, se a ocorrência informada não for deste grupo,
        //                       altera para ocorrência '00172';
        // diferenca_positiva: Calcula a jornada realizada e deduz a jornada prevista para obter a
        //                     diferença devida.
        //                     - Diferença nula, será indicada a ocorrência '00000';
        //                     - Diferença positiva, se a ocorrência informada não for deste grupo,
        //                       será indicada a ocorrência '33333';
        //                     - Diferença negativa, será indicada a ocorrência '00172';
        //
        $oDBase = selecionaServidor( $this->getSiape() );
        $sitcad = $oDBase->fetch_object()->sigregjur;

        ## ocorrências grupos
        $obj = new OcorrenciasGrupos();
        $grupoOcorrenciasViagem       = $obj->GrupoOcorrenciasViagem($sitcad);
        $codigoFrequenciaNormalPadrao = $obj->CodigoFrequenciaNormalPadrao($sitcad);


        $oDBase = new DataBase('PDO');
        $oDBase->setMensagem("Problemas no acesso a Tabela OCORRÊNCIAS (E30012.".__LINE__.").");
        $oDBase->query("SELECT siapecad, tipo FROM tabocfre WHERE ativo = 'S' ORDER BY tipo, siapecad ");

        $diferenca_positiva = array();
        $indefinida         = array();
        $todos_zerados      = array();
        $jornada_negativa   = array();
        $diferenca_zerada   = array();
        $diferenca_positiva = array();
        $diferenca_negativa = array();

        while ($oOcorrencia = $oDBase->fetch_object())
        {
            if (substr_count($_SESSION['sHArquivoTemp'], 'historico_temp_') > 0 && time_to_sec($this->getEntrada()) != 0 && in_array($oOcorrencia->siapecad, $grupoOcorrenciasViagem))
            {
                $diferenca_positiva[] = $oOcorrencia->siapecad;
            }
            elseif (time_to_sec($this->getEntrada()) != 0 && in_array($oOcorrencia->siapecad, $codigoFrequenciaNormalPadrao))
            {
                $diferenca_zerada[] = $oOcorrencia->siapecad;
            }
            else
            {
                switch ($oOcorrencia->tipo)
                {
                    case 'indefinida':
                        $indefinida[] = $oOcorrencia->siapecad;
                        break;
                    case 'todos_zerados':
                        $todos_zerados[] = $oOcorrencia->siapecad;
                        break;
                    case 'jornada_negativa':
                        $jornada_negativa[] = $oOcorrencia->siapecad;
                        break;
                    case 'diferenca_zerada':
                        $diferenca_zerada[] = $oOcorrencia->siapecad;
                        break;
                    case 'diferenca_positiva':
                        $diferenca_positiva[] = $oOcorrencia->siapecad;
                        break;
                    case 'diferenca_negativa':
                        $diferenca_negativa[] = $oOcorrencia->siapecad;
                        break;
                }
            }
        }

        $diferenca_de_jornada = array_merge($diferenca_negativa, $diferenca_positiva);

        $this->indefinida         = $indefinida;
        $this->todos_zerados      = $todos_zerados;
        $this->jornada_negativa   = $jornada_negativa;
        $this->diferenca_zerada   = $diferenca_zerada;
        $this->diferenca_positiva = $diferenca_positiva;
        $this->diferenca_negativa = $diferenca_negativa;

        $this->diferenca_de_jornada = $diferenca_de_jornada;

        ## DADOS
        #
        // formataJornadaParaHHMM(): formata a jornada para HH:MM sendo
        // informado 40 (horas semanais) ou 08:00 (horas diárias)
        //
        $dados_idreg       = define_quem_registrou($this->getLotacao());
        $dados_jornada     = ($this->getDiaUtil() == 'S' ? $this->formataJornadaParaHHMM() : '00:00' );
        $codigo_ocorrencia = ($this->getCodigoOcorrencia() == '' ? 'x' : $this->getCodigoOcorrencia());

        $oResultado->entra  = (time_to_sec($this->getEntrada())         == 0 ? '00:00:00' : $this->getEntrada());
        $oResultado->intini = (time_to_sec($this->getInicioIntervalo()) == 0 ? '00:00:00' : $this->getInicioIntervalo());
        $oResultado->intsai = (time_to_sec($this->getFimIntervalo())    == 0 ? '00:00:00' : $this->getFimIntervalo());
        $oResultado->sai    = (time_to_sec($this->getSaida())           == 0 ? '00:00:00' : $this->getSaida());

        // calcula jornada realizada e diferenca
        $aHoras = $this->calculaHorasTrabalhadas();

        $oHoras->codigo_ocorrencia = $aHoras[0];
        $oHoras->jornada_realizada = $aHoras[1];
        $oHoras->jornada_prevista  = $aHoras[2];
        $oHoras->jornada_diferenca = $aHoras[3];

        $oResultado->ocorrencia           = $oHoras->codigo_ocorrencia;
        $oResultado->ocorrencia_calculada = $oHoras->codigo_ocorrencia;
        $oResultado->jornada_realizada    = formata_jornada_para_hhmm($oHoras->jornada_realizada);
        $oResultado->jornada_prevista     = formata_jornada_para_hhmm($oHoras->jornada_prevista);
        $oResultado->jornada_diferenca    = formata_jornada_para_hhmm($oHoras->jornada_diferenca);


        ## ocorrencia indefinida
        #
        if (in_array($codigo_ocorrencia, $indefinida))
        {
            $oResultado->ocorrencia        = $codigo_ocorrencia;
            $oResultado->jornada_realizada = "00:00";
            $oResultado->jornada_diferenca = "00:00";
        }

        ## ocorrencias que os valores são zerados e
        #  registra jornada negativa do servidor
        #
        elseif (in_array($codigo_ocorrencia, $jornada_negativa))
        {
            $oResultado->entra             = "00:00";
            $oResultado->intini            = "00:00";
            $oResultado->intsai            = "00:00";
            $oResultado->sai               = "00:00";
            $oResultado->ocorrencia        = $codigo_ocorrencia;
            $oResultado->jornada_realizada = "00:00";
            $oResultado->jornada_diferenca = $oResultado->jornada_prevista;
        }

        ## ocorrencias que todos os horários são zerados
        #
        elseif (in_array($codigo_ocorrencia, $todos_zerados) || (in_array($codigo_ocorrencia, $grupoOcorrenciasViagem) && $dados_idreg == 'C' && substr_count($_SESSION['sHArquivoTemp'], 'historico_temp_') == 0) || (substr_count($_SESSION['sHArquivoTemp'], 'historico_temp_') > 0 && time_to_sec($this->getEntrada()) == 0))
        {
            $oResultado->entra             = "00:00";
            $oResultado->intini            = "00:00";
            $oResultado->intsai            = "00:00";
            $oResultado->sai               = "00:00";
            $oResultado->ocorrencia        = $codigo_ocorrencia;
            $oResultado->jornada_realizada = "00:00";
            $oResultado->jornada_prevista  = "00:00";
            $oResultado->jornada_diferenca = "00:00";
        }

        ## ocorrencias que a diferença é zerada
        #
        elseif (in_array($codigo_ocorrencia, $diferenca_zerada) || (in_array($codigo_ocorrencia, $grupoOcorrenciasViagem) && $dados_idreg == 'C' && substr_count($_SESSION['sHArquivoTemp'], 'historico_temp_') == 0) || (substr_count($_SESSION['sHArquivoTemp'], 'historico_temp_') > 0 && time_to_sec($this->getEntrada()) == 0))
        {
            $oResultado->ocorrencia        = $codigo_ocorrencia;
            $oResultado->jornada_realizada = ($oResultado->jornada_realizada == '00:00' ? $oResultado->jornada_prevista : $oResultado->jornada_realizada);
            $oResultado->jornada_diferenca = "00:00";
        }

        ## ocorrencias diferenca de jornada
        #
        elseif (in_array($codigo_ocorrencia, $diferenca_de_jornada))
        {
            $oResultado->ocorrencia = $codigo_ocorrencia;
        }

        return $oResultado;
    }

    ## @metodo
    # calcula horas trabalhadas no dia
    # estabelecendo se a ocorrencia é
    # positiva ou negativa
    #
    function calculaHorasTrabalhadas()
    {
        $oDBase = selecionaServidor( $this->getSiape() );
        $sitcad = $oDBase->fetch_object()->sigregjur;

        ## ocorrências grupos
        $obj = new OcorrenciasGrupos();
        $codigoFrequenciaNormalPadrao     = $obj->CodigoFrequenciaNormalPadrao($sitcad);
        $codigoSemFrequenciaPadrao        = $obj->CodigoSemFrequenciaPadrao($sitcad);
        $faltaJustificadaPadrao           = $obj->FaltaJustificadaPadrao($sitcad);
        $codigoCreditoPadrao              = $obj->CodigoCreditoPadrao($sitcad);
        $codigoDebitoPadrao               = $obj->CodigoDebitoPadrao($sitcad);
        $codigoBancoDeHorasCreditoPadrao  = $obj->CodigoBancoDeHorasCreditoPadrao($sitcad);
        $grupoOcorrenciasViagem           = $obj->GrupoOcorrenciasViagem($sitcad);
        $codigos_reduz_metade_todos       = $obj->EstagiariosReduzMetade('todos');
        $codigos_reduz_metade_debito      = $obj->EstagiariosReduzMetade('debito');
        $codigos_reduz_metade_zerado      = $obj->EstagiariosReduzMetade('zerado');
        $eventosEsportivos                = $obj->EventosEsportivos($todos = null);
        $programaDeGestaoINSS_PGSP        = $obj->ProgramaDeGestaoINSS($sitcad, $sigla = 'PGSP');
        $programaDeGestaoINSS_PGSPCredito = $obj->ProgramaDeGestaoINSS($sitcad, $sigla = 'PGSP', $debito = false); //"90302";
        $programaDeGestaoINSS_PGSPDebito  = $obj->ProgramaDeGestaoINSS($sitcad, $sigla = 'PGSP', $debito = true);  //"90372";

        # dados
        #
        $this->pontoFacultativo();

        // inicialização
        $oHoras->codigo_ocorrencia = $codigoFrequenciaNormalPadrao[0];
        $oHoras->jornada_realizada = "00:00";
        $oHoras->jornada_prevista  = "00:00";
        $oHoras->jornada_diferenca = "00:00";

        // formataJornadaParaHHMM(): formata a jornada para HH:MM sendo
        // informado 40 (horas semanais) ou 08:00 (horas diárias)
        //
        $dados_idreg   = define_quem_registrou($this->getLotacao());
        $dados_jornada = ($this->getDiaUtil() == 'S' ? $this->formataJornadaParaHHMM() : '00:00' );
        $dados_oco     = ($this->getCodigoOcorrencia() == '' ? 'x' : $this->getCodigoOcorrencia());

        ## DADOS
        #
        $entrada       = time_to_sec(left($this->getEntrada(), 5));
        $almoco_inicio = time_to_sec(left($this->getInicioIntervalo(), 5));
        $almoco_fim    = time_to_sec(left($this->getFimIntervalo(), 5));
        $saida         = time_to_sec(left($this->getSaida(), 5));

        $compensacao_autorizada = $this->getBancoCompensacao();

        if ($entrada == 0)
        {
            $almoco_inicio = 0;
            $almoco_fim    = 0;
            $saida         = 0;
        }
        elseif (($entrada > 0 && $entrada <= $almoco_inicio) && $saida == 0)
        {
            $saida         = $almoco_inicio;
            $almoco_fim    = 0;
            $almoco_inicio = 0;
        }
        elseif ($almoco_inicio == 0 || $almoco_fim == 0 || $almoco_inicio > $almoco_fim)
        {
            $almoco_inicio = 0;
            $almoco_fim    = 0;
        }
        elseif ($saida == 0)
        {
            $entrada       = 0;
            $almoco_inicio = 0;
            $almoco_fim    = 0;
        }

        // PARTICIPANTE DO PROGRAMA DE GESTÃO
        //      SEMI-PRESENCIAL (PGSP)
        //      TELETRABALHO (CEAP)
        $participante = participanteProgramaGestao($this->getSiape(), $this->getData());
        $programa = $participante["programa"];


        ## limites básicos definidos
        #
        $limites_inss = horariosLimiteINSS();

        // Duração mínima do almoço
        $almoco_duracao_minima = time_to_sec($limites_inss['limite_duracao_minima_almoco']['horario']);

        // Duração maxíma do almoço
        $almoco_duracao_maxima = time_to_sec($limites_inss['limite_duracao_maxima_almoco']['horario']);

        // Máximo de horas excedentes no dia
        //
        // MINISTÉRIO DO PLANEJAMENTO, DESENVOLVIMENTO E GESTÃO
        // SECRETARIA DE GESTÃO DE PESSOAS E RELAÇÕES DO TRABALHO NO SERVIÇO PÚBLICO
        // ORIENTAÇÃO NORMATIVA Nº 2, DE 24 DE JUNHO DE 2016
        //   Estabelece orientações sobre a aceitação de estagiários
        //   no âmbito da Administração Pública federal direta,
        //   autárquica e fundacional.
        // CAPÍTULO II - DO ESTÁGIO
        //   Art. 12, § 3º
        //   "É vedada a realização de carga horária diária superior à prevista no caput
        //    deste artigo, ressalvada a compensação de falta justificada, limitada a
        //    1 (uma) hora por jornada."
        //
        if ($this->getSituacaoCadastral() == '66')
        {
            $horas_excedentes_maximo = time_to_sec($limites_inss['limite_horas_excedentes_por_dia_estagiarios']['horario']);
        }
        else
        {
            $horas_excedentes_maximo = time_to_sec($limites_inss['limite_horas_excedentes_por_dia']['horario']);
        }

        // Limite de jornada corrida (sem o almoço)
        $jornada_corrida_limite = time_to_sec($limites_inss['limite_jornada_corrida']['horario']);

        #
        ## FIM limites básicos definidos

        // feriado nacional, estadual ou municipal
        if ($this->getDiaUtil() == 'N' && eh_ponto_facultativo($this->getData()) == false)
        {
            $jornada_prevista = 0;

            // máximo de 2hs horas-extras por dia
            if (in_array($dados_oco, $grupoOcorrenciasViagem) == false)
            {
                $horas_excedentes_maximo = (time_to_sec($this->formataJornadaParaHHMM()) + $horas_excedentes_maximo); // Máximo de horas excedentes no dia
            }
        }
        else
        {
            // verifica se dia ponto facultativo, altera jornada
            $dados->jornada = ponto_facultativo($this->getData(), $dados_jornada, '', $this->getEntrada(), $this->getSaida(), $this->getInicioIntervalo(), $this->getFimIntervalo(), $this->getSiape());

            $dados_jornada = $dados->jornada;

            if (in_array($dados_oco, $grupoOcorrenciasViagem) == true)
            {
                // máximo de 2hs horas-extras por dia
            }
            else if (in_array($dados_oco, $grupoOcorrenciasViagem) == false)
            {
                if (eh_ponto_facultativo($this->getData()) == true)
                {
                    // "$this->getJ()" é a jornada do servidor em dias úteis, que no caso
                    // dos dias facultativos integrais são consideradas para o limite das horas excedentes
                    $horas_excedentes_maximo = (time_to_sec($this->getJ()) + $horas_excedentes_maximo); // Máximo de horas excedentes no dia
                }
            }

            $jornada_prevista = time_to_sec($dados_jornada);

            if (in_array($dados_oco, $codigos_reduz_metade_todos) && $this->getSituacaoCadastral() == '66')
            {
                $jornada_prevista = ($jornada_prevista / 2);
            }
        }


        if (in_array($programa, $programaDeGestaoINSS_PGSP))
        {
            $jornada_prevista = time_to_sec( formata_jornada_para_hhmm($participante["jornada"]) );
        }


        ## JORNADA REALIZADA
        #

        // horas entre inicio e fim do expediente, sem contar o almoço
        // - Total será igual a zero se horário de saída menor que de entrada
        //
        $tempo_total_sem_almoco = (($saida > $entrada) ? ($saida - $entrada) : 0);

        // diferenca entre saida para o almoco e retorno do almoço
        // - Total será igual a zero se retorno do almoço menor que de saída para o almoço
        //
        $tempo_de_almoco = (($almoco_fim > $almoco_inicio) ? ($almoco_fim - $almoco_inicio) : 0);


        if (($tempo_total_sem_almoco > $jornada_corrida_limite) && ($tempo_de_almoco < $almoco_duracao_minima))
        {
            $jornada_realizada = ($tempo_total_sem_almoco - $almoco_duracao_maxima); // calculo da jornada do dia
        }
        elseif ($tempo_total_sem_almoco == 0)
        {
            $jornada_realizada = 0;
        }
        else
        {
            $jornada_realizada = ($tempo_total_sem_almoco - $tempo_de_almoco); // calculo da jornada do dia
        }


        ## JORNADA PREVISTA E DIFERENÇA
        #
        if ($jornada_prevista > $jornada_realizada)
        {
            $jornada_diferenca = ($jornada_prevista - $jornada_realizada);
            if (time_to_sec(left($this->getEntrada(), 5)) == 0 && time_to_sec(left($this->getInicioIntervalo(), 5)) == 0 &&
                time_to_sec(left($this->getSaida(), 5)) == 0 && time_to_sec(left($this->getFimIntervalo(), 5)) == 0)
            {
                $codigo_ocorrencia = ($dados_idreg == 'X' ? $codigoSemFrequenciaPadrao[0] : $faltaJustificadaPadrao[0]);
            }
            else
            {
                $this->oDBase->setMensagem("Problemas no acesso a Tabela CADASTRO (E30013.".__LINE__.").");
                $this->oDBase->query( "
                    SELECT
                        cat.carreira
                    FROM
                        servativ AS cad
                    LEFT JOIN
                        tabcargo AS cat ON cad.cod_cargo = cat.cod_cargo
                    WHERE
                        mat_siape = :siape
                    ORDER BY
                        mat_siape
                    ",
                    array(
                        array( ':siape', $this->getSiape(), PDO::PARAM_STR ),
                    ));
                $pericia_medica = ($this->oDBase->num_rows() == 0 ? false : ($this->oDBase->fetch_object()->carreira == 'PERICIA MEDICA'));

                //Implementar busca para saber se é dia da copa
                $this->oDBase->setMensagem("Problemas no acesso a Tabela FACULTATIVO (E30014.".__LINE__.").");
                $this->oDBase->query( "
                    SELECT
                        codigo_debito
                    FROM
                        tabfacultativo172
                    WHERE
                        dia = :dia
                        AND ativo = 'S'
                    ",
                    array(
                        array( ':dia', conv_data( $this->getData() ), PDO::PARAM_STR ),
                    ));

                //$copa = $oDBase->num_rows();
                //$codigo_ocorrencia = ($copa == 0 ? '00172' : $oDBase->fetch_object()->codigo_debito);
                $codigo_ocorrencia = ($this->oDBase->num_rows() == 0 || $pericia_medica == true ? $codigoDebitoPadrao[0] : $this->oDBase->fetch_object()->codigo_debito);

                $codigo_ocorrencia = (in_array($dados_oco, $codigos_reduz_metade_debito) ? $dados_oco : $codigoDebitoPadrao[0]); // '12929'
            }

            if (in_array($programa, $programaDeGestaoINSS_PGSP))
            {
                $codigo_ocorrencia = $programaDeGestaoINSS_PGSPDebito[0]; //"90372";
                $jornada_prevista  = '08:00';
                $jornada_diferenca = '00:00';
                $this->setJornada('08:00');
            }
        }
        elseif (($jornada_prevista < $jornada_realizada) && ($compensacao_autorizada == 'S' || substr_count($this->diferenca_positiva, $dados_oco) > 0))
        {
            $jornada_diferenca = ($jornada_realizada - $jornada_prevista);
            $jornada_diferenca = ($jornada_diferenca > $horas_excedentes_maximo ? $horas_excedentes_maximo : $jornada_diferenca);
            $codigo_ocorrencia = ($jornada_diferenca == 0 ? $codigoFrequenciaNormalPadrao[0] : (substr_count($this->diferenca_positiva, $dados_oco) > 0 ? $dados_oco : $codigoCreditoPadrao[0]));

            if (in_array($programa, $programaDeGestaoINSS_PGSP))
            {
                $codigo_ocorrencia = $programaDeGestaoINSS_PGSPCredito[0]; //"90302";
                $jornada_prevista  = '08:00';
                $jornada_diferenca = '00:00';
                $this->setJornada('08:00');
            }
        }
        else
        {
            $jornada_diferenca = 0;
            $codigo_ocorrencia = (in_array($dados_oco, $codigos_reduz_metade_zerado) ? $dados_oco : $codigoFrequenciaNormalPadrao[0]); // '02929'

            if (in_array($programa, $programaDeGestaoINSS_PGSP))
            {
                $codigo_ocorrencia = $programaDeGestaoINSS_PGSPCredito[0]; //"90302";
                $jornada_prevista  = '08:00';
                $jornada_diferenca = '00:00';
                $this->setJornada('08:00');
            }
        }

        $aHoras = array(
            $codigo_ocorrencia,
            formata_jornada_para_hhmm(sec_to_time($jornada_realizada)),
            formata_jornada_para_hhmm(sec_to_time($jornada_prevista)),
            formata_jornada_para_hhmm(sec_to_time($jornada_diferenca))
        );

        return $aHoras;
    }

    ## @metodo
    # verificar se a data é de um
    # final de semana (sábado ou domingo)
    #
    function verificaSeDiaUtil($bMsgErro = true)
    {
        $oDBase = selecionaServidor( $this->getSiape() );
        $sitcad = $oDBase->fetch_object()->sigregjur;

        // Grupos de ocorrências
        $obj = new OcorrenciasGrupos();
        $debito = $obj->GrupoOcorrenciasNegativasDebitos($sitcad, $exige_horarios = true);


        $dia_util = true;

        // ocorrencia
        $ocor     = $this->getCodigoOcorrencia();
        $ocor     = ($ocor == '' ? 'x' : $this->getCodigoOcorrencia());
        $dia_util = ($this->verificaFimDeSemana() == false && $this->verificaSeFeriado() == false);

        $this->setDiaUtil(($dia_util == true ? "S" : "N"));

        if ($this->getDiaUtil() == "N" && in_array($ocor, $debito))
        {
            if ($bMsgErro == true)
            {
                mensagem("Não é permitido lançar a ocorrência " . $ocor . ", em dia não útil!", $this->getDestino(), $this->getHistoryGo());
            }
        }

        return $dia_util;
    }

    ## @metodo
    # verificar se a data é de um
    # final de semana (sábado ou domingo)
    #
    function verificaFimDeSemana()
    {
        // dados
        $dia = $this->getDia();
        $mes = $this->getMes();
        $ano = $this->getAno();

        $dia_da_semana = date("w", mktime(0, 0, 0, $mes, $dia, $ano));
        $fim_de_semana = ($dia_da_semana == "0" || $dia_da_semana == "6");
        $this->setDiaUtil(($fim_de_semana == true ? "N" : "S"));

        return $fim_de_semana;
    }

    ## @metodo
    # verificar se a data é de um
    # feriado nacional, estadual ou municipal
    #
    function verificaSeFeriado()
    {
        // dados
        $data    = conv_data($this->getData());
        $ano     = $this->getAno();
        $lotacao = $this->getLotacao();

        $unidade_inss        = (empty($lotacao) ? $_SESSION['sLotacao'] : $lotacao);
        $codigo_do_municipio = $this->getCodigoMunicipio();

        if ($codigo_do_municipio == "")
        {
            // unidade
            $oDBase = new DataBase('PDO');
            $oDBase->setMensagem("Problemas no acesso a Tabela UNIDADES (E30015.".__LINE__.").");
            $oDBase->query(
                "SELECT codmun FROM tabsetor WHERE codigo = :unidade_inss", array(array(':unidade_inss', $unidade_inss, PDO::PARAM_STR))
            );
            $codigo_do_municipio = $oDBase->fetch_object()->codmun;
        }

        // conexao com a base de dados
        unset($oDBase);
        $oDBase = new DataBase('PDO');
        $oDBase->setDestino($this->getDestino());
        $oDBase->setVoltar($this->getHistoryGo());

        // feriados nacionais,estaduais e municipais
        $oDBase->setMensagem("Problemas no acesso a Tabela FERIADOS (E30016.".__LINE__.").");
        $oDBase->query(
            "SELECT dia, mes, `desc`, tipo, codmun
				FROM feriados_$ano
				WHERE data_feriado= :data_feriado AND
					(tipo LIKE 'N' OR (`tipo` LIKE 'E' AND lot LIKE :unidade_inss) OR (tipo LIKE 'M' AND codmun = :codigo_do_municipio)) ", array(
            array(':data_feriado', $data, PDO::PARAM_STR),
            array(':unidade_inss', substr($unidade_inss, 0, 2) . "%", PDO::PARAM_STR),
            array(':codigo_do_municipio', $codigo_do_municipio, PDO::PARAM_STR)
            )
        );
        $numrows = $oDBase->num_rows();

        $feriado = ($numrows > 0);
        $this->setDiaUtil(($feriado == true ? "N" : "S"));

        return $feriado;
    }

    ## @metodo
    # ponto facultativo
    #
    # A jornada ajustada se ponto facultativo, no formato hh:mm.
    # Verfifica se é natal, ano novo ou quarta feira de cinzas.
    # Sempre retorna horas para o dia, ex. 08:00, 06:00.
    #
    function pontoFacultativo($acao = '0')
    {
        // dados
        $dia          = $this->getData();
        $ano          = $this->getAno();
        $entra        = $this->getCadastroEntrada();
        $sai          = $this->getCadastroSaida();
        $iint         = $this->getCadastroInicioIntervalo();
        $vint         = $this->getCadastroFimIntervalo();
        $jornada      = $this->getJornada();

        $deduz_almoco = false; //$this->getDeduzAlmoco();

        // data informada
        $dthoje       = conv_data($dia);

        // verificamos se a jornada e o horário de
        // serviço estão iguais
        // Ex.: jornada de 6 horas e o horário de serviço
        //      registrado como "07:00 | 00:00 | 00:00 | 14:00",
        //      neste caso o horário de serviço totaliza 7 horas
        //
        $this->compararJornadaComHorarioDefinido($acao);

        // formatamos a jornada informada para HH:MM
        // sendo no formato 40 ou mesmo 08:00
        //
        $jornada_dia = $this->formataJornadaParaHHMM();

        // indica se eh quarta-feira de cinzas
        //
        $this->setQuartaFeiraCinzas(false);

        // conexao
        $oDBase = new DataBase('PDO');
        $oDBase->setDestino($this->getDestino());
        $oDBase->setVoltar($this->getHistoryGo());

        $oDBase->setMensagem("Problemas no acesso a Tabela FERIADOS/FACULTATIVOS (E30017.".__LINE__.").");
        $oDBase->query(
            "SELECT carga_horaria, data_feriado, hora_inicio, hora_termino, grupo
				FROM feriados_ponto_facultativo
				WHERE data_feriado = :data_feriado", array(array(':data_feriado', $dthoje, PDO::PARAM_STR))
        );

        if ($oDBase->num_rows() > 0)
        {
            $oCarga      = $oDBase->fetch_object();
            $jornada_dia = ($oCarga->carga_horaria == '' ? $jornada_dia : $oCarga->carga_horaria);

            $qcinzas = (substr($oCarga->grupo, 0, 6) == 'cinzas' ? true : false);

            /* *******************************************
             *                                           *
             * NATAL E ANO NOVO DE 2013                  *
             *                                           *
             ******************************************* */
            if (substr_count('natal2013.:.anonovo2013', $oCarga->grupo) > 0)
            {
                //calculo a jornada do dia
                $jornada_maxima     = time_to_sec($oCarga->carga_horaria);
                $entrada_cadastrada = time_to_sec($entra);
                $fim_do_expediente  = time_to_sec($oCarga->hora_termino);

                if ($entrada_cadastrada >= $fim_do_expediente)
                {
                    $jornada_dia = 0;
                }
                elseif ($entrada_cadastrada >= 23400 && $entrada_cadastrada < $fim_do_expediente) // 23400 = 06:30
                {
                    $tempo       = ($fim_do_expediente - $entrada_cadastrada);
                    $jornada_dia = ($tempo > $jornada_maxima ? $jornada_maxima : $tempo);
                }
                else
                {
                    $jornada_dia = $jornada_maxima;
                }
                $jornada_dia = sec_to_time($jornada_dia);
                $jornada_dia = right($jornada_dia, 8);
            }

            /* *******************************************
             *                                           *
             * QUARTA-FEIRA DE CINZAS DE 2013            *
             *                                           *
             ******************************************* */
            elseif (substr_count('cinzas2013', $oCarga->grupo) > 0)
            {
                // limite de horario de entrada funcionamento, início e fim (Órgão padrão)
                $limites_inss                         = horariosLimiteINSS();
                $limite_hora_fim_funcionamento_padrao = $limites_inss['fim_funcionamento_padrao']['horario'];

                // conexao tabsetor
                $oDBSetor = new DataBase('PDO');
                $oDBSetor->setDestino($this->getDestino());
                $oDBSetor->setVoltar($this->getHistoryGo());

                $oDBSetor->setMensagem("Problemas no acesso a Tabela UNIDADES (E30018.".__LINE__.").");
                $oDBSetor->query(
                    'SELECT IF(TIME_TO_SEC(IFNULL(und.fim_atend,"00:00"))=0,:limite,und.fim_atend) AS fim_atend
						FROM tabsetor AS und
						WHERE und.codigo = :codigo', array(
                    array(':limite', $limite_hora_fim_funcionamento_padrao . ':00', PDO::PARAM_STR),
                    array(':codigo', $this->getLotacao(), PDO::PARAM_STR)
                    )
                );

                if ($oDBSetor->num_rows() > 0)
                {
                    $fim_atend = time_to_sec($oDBSetor->fetch_object()->fim_atend);
                }
                else
                {
                    $fim_atend = time_to_sec($limite_hora_fim_funcionamento_padrao);
                }

                //calculo a jornada do dia
                $saida_cadastrada     = time_to_sec($sai);
                $inicio_do_expediente = time_to_sec($oCarga->hora_inicio);
                $jornada_maxima       = ($fim_atend > $inicio_do_expediente ? ($fim_atend - $inicio_do_expediente) : 0); //time_to_sec($oCarga->carga_horaria);

                if ($saida_cadastrada > $inicio_do_expediente)
                {
                    $tempo       = ($saida_cadastrada - $inicio_do_expediente);
                    $jornada_dia = ($tempo > $jornada_maxima ? $jornada_maxima : $tempo);
                }
                elseif ($saida_cadastrada > 3600 && $saida_cadastrada <= $inicio_do_expediente) // 3600 = 1 hora
                {
                    $jornada_dia = 0;
                }
                else
                {
                    $jornada_dia = $jornada_maxima;
                }
                $jornada_dia = sec_to_time($jornada_dia);
                $jornada_dia = right($jornada_dia, 8);
            }

            /********************************************
             *                                           *
             * NATAL E ANO NOVO DE 2010                  *
             * QUARTA-FEIRA DE CINZAS DE 2011            *
             *                                           *
             ******************************************* */
            elseif (substr_count('natal2010.:.anonovo2010.:.cinzas2011', $oCarga->grupo) > 0)
            {
                if ($qcinzas == true)
                {
                    $this->setQuartaFeiraCinzas($qcinzas);
                    $horarioesp = $oCarga->hora_inicio;
                }
                else
                {
                    $this->setQuartaFeiraCinzas(false);
                    $horarioesp = $oCarga->hora_termino;
                }

                //calculo do intervalo do dia
                if (($entra > $iint) || ($entra > $vint) || ($iint > $vint) || ($deduz_almoco == false))
                {
                    $interv = '00:00:00';
                }
                else
                {
                    if ($qcinzas == true)
                    {
                        $vint = ($vint < $horarioesp ? $horarioesp : $vint);
                        $iint = ($iint < $horarioesp ? $horarioesp : ($iint > $vint ? $vint : $iint));
                    }
                    else
                    {
                        $vint = ($vint > $horarioesp ? $horarioesp : $vint);
                        $iint = ($iint > $vint ? $vint : $iint);
                    }
                    $interv = diferencaHoras($iint, $vint);
                }

                // jornada oficial
                // se a diferença entre o horário de entrada e o horário de saída
                // for menor que a jornada, recalculamos o horário de saida
                //$sai = adicionaHoras( $entra, $jornada );
                // calculo da jornada do dia
                if ($qcinzas == true)
                {
                    $sai   = ($sai < $horarioesp || $entra == $sai ? $horarioesp : $sai);
                    $entra = ($entra > $sai ? $sai : ($entra < $horarioesp ? $horarioesp : $entra));
                }
                else
                {
                    $sai   = ($sai > $horarioesp || $entra == $sai || $entra >= $horarioesp ? $horarioesp : $sai);
                    $entra = ($entra > $sai ? $sai : $entra);
                }
                $jornada_dia = diferencaHoras($interv, diferencaHoras($entra, $sai));
            }
            else
            {
                $this->setQuartaFeiraCinzas($qcinzas);
                $jornada_dia = ($oCarga->carga_horaria == '' || $oCarga->carga_horaria == '00:00' ? $jornada_dia : $oCarga->carga_horaria);
            }
        }

        $jornada = ($jornada_dia > $jornada ? $jornada : $jornada_dia);

        $this->setJornada($jornada);
        return $jornada;
    }

    ## @metodo
    # turno estendido
    #
    # A jornada ajustada ao turno estendido, no formato hh:mm.
    # Se a unidade estiver autorizada ao turno estendido
    #
    function turnoEstendido($acao = '0')
    {
        // verificamos se a jornada e o horário de
        // serviço estão iguais
        // Ex.: jornada de 6 horas e o horário de serviço
        //      registrado como "07:00 | 00:00 | 00:00 | 14:00",
        //      neste caso o horário de serviço totaliza 7 horas
        //
        //$this->compararJornadaComHorarioDefinido($acao);
        $this->oDadosCadastro->j = $this->formataJornadaParaHHMM();

        return $this->oDadosCadastro->j;
    }

    ## @metodo
    # formata a jornada HH:MM
    #
    function formataJornadaParaHHMM()
    {
        $jornada = formata_jornada_para_hhmm($this->getJornada());
        return $jornada;
    }

    ## @metodo
    # compara a jornada com o horário de
    # serviço definido para verificar se
    # estão iguais
    # Ex.: jornada de 6 horas e o horário de serviço
    #      registrado como "07:00 | 00:00 | 00:00 | 14:00",
    #      neste caso o horário de serviço totaliza 7 horas
    #
    function compararJornadaComHorarioDefinido($acao = '0')
    {
        // formatamos a jornada informada para HH:MM
        // sendo no formato 40 ou mesmo 08:00
        //
        $jornada_dia = $this->formataJornadaParaHHMM();

        $nHorasInicioFim = diferencaHoras($this->getCadastroEntrada(), $this->getCadastroSaida());
        $nIntervalo      = diferencaHoras($this->getCadastroInicioIntervalo(), $this->getCadastroFimIntervalo());
        $HorasJornada    = diferencaHoras($nIntervalo, $nHorasInicioFim);

        if ($HorasJornada != $jornada_dia)
        {
            if ($this->getCadastroJornada != 40 && $this->chefiaAtiva == 'S')
            {
                // não realizamos o teste se ocupante de função ou efetivo substituto, e jornada menor que 40 horas
            }
            else
            {
                switch ($acao)
                {
                    case "0":
                        if ($this->getTurnoEstendido() == 'S')
                        {
                            //mensagem( "Incorreto o Horário de serviço no cadastro!\\nTurno estendido divergente do horário de serviço definido!\\nSolicite alteração do horário de serviço à chefia imediata!", $this->getDestino(), 1 );
                        }
                        else
                        {
                            //mensagem( "Incorreto o Horário do servidor no cadastro!\\nJornada em desacordo com o horário de serviço definido!\\nSolicite alteração do horário de serviço à chefia imediata!", $this->getDestino(), 1 );
                        }
                        break;
                    case "1":
                        //replaceLink("mensagem2.php?modo=43&mat=$sMatricula&lot=$sLotacao");
                        break;
                    default:
                        break;
                }
            }
        }
    }

    ## @metodo
    # numero de ocorrencias no mes
    #
    function ocorrenciasTotal()
    {
        // dados
        $siape           = $this->getSiape();
        $nome_do_arquivo = $this->getNomeDoArquivo();

        // conexao com a base de dados
        $oDBase = new DataBase('PDO');
        $oDBase->setDestino($this->getDestino());
        $oDBase->setVoltar($this->getHistoryGo());

        $oDBase->setMensagem("Problemas no acesso a Tabela FREQUÊNCIA (E30019.".__LINE__.").");
        $oDBase->query("SELECT oco AS ocorrencia, count(oco) AS total FROM $nome_do_arquivo WHERE siape = :siape GROUP BY oco ");
        array(
            array(':siape', $siape, PDO::PARAM_STR),
        );

        while ($oOcorrencias = $oDBase->fetch_object())
        {
            $this->setOcorrenciasTotal($oOcorrencias->ocorrencia, $oOcorrencias->total);
        }
    }

    ## @metodo
    # total de horas por ocorrencia no mes
    #
    function ocorrenciasTotalHoras()
    {
        // dados
        $siape           = $this->getSiape();
        $nome_do_arquivo = $this->getNomeDoArquivo();

        // conexao com a base de dados
        $oDBase = new DataBase('PDO');
        $oDBase->setDestino($this->getDestino());
        $oDBase->setVoltar($this->getHistoryGo());

        $oDBase->setMensagem("Problemas no acesso a Tabela FREQUÊNCIA (E30020.".__LINE__.").");
        $oDBase->query( "
            SELECT DISTINCT
                pto.oco                                            AS ocorrencia,
                LEFT(SEC_TO_TIME(SUM(TIME_TO_SEC(pto.jorndif))),5) AS total
            FROM
                $nome_do_arquivo AS pto
            WHERE
                pto.siape = :siape
                AND NOT (pto.dia = ANY (
                            SELECT tabfacultativo172.dia
                            FROM tabfacultativo172
                            WHERE
                                pto.dia = tabfacultativo172.dia
                                AND tabfacultativo172.codigo_debito = pto.oco
                                AND tabfacultativo172.jogo_do_brasil = 'S'))
            GROUP BY
                pto.oco
            ORDER BY
                pto.oco
        ",
        array(
            array(':siape', $siape, PDO::PARAM_STR),
        ));

        while ($oOcorrencias = $oDBase->fetch_object())
        {
            $this->setOcorrenciasTotalHoras( $oOcorrencias->ocorrencia, $oOcorrencias->total );
        }
    }


    ## @metodo
    # totaliza as ocorrencias no mes
    #
    function numeroDiasDoMes()
    {
        // dados
        $mes = $this->getMes();
        $ano = $this->getAno();
        $this->setTotalDiasMes(date("t", mktime(0, 0, 0, $mes, 1, $ano)));
    }

    ## @metodo
    # verifica se homologado
    #
    function verificaSeHomologado()
    {
        // instâncias
        $objHomologadosController = new TabHomologadosController();
        $status = $objHomologadosController->retornaSeHomologado( $this->getSiape(), $this->getMes(), $this->getAno());

        $this->setHomologacaoStatus($status);
    }

    ## @metodo
    # verifica se a data está dentro do período
    # do recesso, para uso e para compensação
    #
    function verificaPeriodoDoRecesso()
    {
        $oDBase = selecionaServidor( $this->getSiape() );
        $sitcad = $oDBase->fetch_object()->sigregjur;

        // Grupos de ocorrências
        $obj = new OcorrenciasGrupos();

        $codigoCreditoRecessoPadrao = $obj->CodigoCreditoRecessoPadrao($sitcad);
        $codigoDebitoRecessoPadrao  = $obj->CodigoDebitoRecessoPadrao($sitcad);


        // dados
        $ocor = $this->getCodigoOcorrencia();
        $data = $this->getData();

        // se 02323 e dia dentro do periodo autorizado do recesso, ou
        // se 02424 e data dentro do período para compensação
        if ((in_array($ocor,$codigoCreditoRecessoPadrao)) && (dataUsoDoRecesso($data) == false))
        {
            mensagem("Não é permitido lançar recesso (" . implode(',', $codigoCreditoRecessoPadrao) . ") fora do período legal!", $this->getDestino());
        }
        else if ((in_array($ocor,$codigoDebitoRecessoPadrao)) && (dataCompensacaoDoRecesso($data) == false))
        {
            mensagem("Não é permitido lançar compensação de recesso (".implode(',', $codigoDebitoRecessoPadrao).") fora do período legal!", $this->getDestino());
        }
    }

    ## @metodo
    # verifica se a data está dentro do período
    # da Copa do Mundo 2014, para uso e compensação
    #
    function verificaPeriodoDaCopa2014($exibe_msg = false)
    {
        // dados
        $ocor           = $this->getCodigoOcorrencia();
        $data_invertida = $this->getAno() . '-' . $this->getMes() . '-' . $this->getDia();

        $ocorrencia_credito = '92014';
        $ocorrencia_debito  = '62014';

        $msg6 = array(
            $ocorrencia_debito => 'aos dias 12, 13, 16, 17, 18, 19, 20, 23, 24, 25, 26 e 30 do mês 06/2014, e, 01, 04, 08 e 09 do mês 07/2014'
        );

        $oDBase = new DataBase('PDO');
        $oDBase->setDestino($this->getDestino());
        $oDBase->setVoltar($this->getHistoryGo());

        $oDBase->setMensagem("Problemas no acesso a Tabela FACULTATIVO (E30021.".__LINE__.").");
        $oDBase->query("SELECT dia FROM tabfacultativo172 WHERE codigo_debito = '" . $ocor . "' AND IF('" . $ocorrencia_credito . "'='" . $ocor . "',(dia = " . $data_invertida . "),(" . $data_invertida . " >= compensacao_inicio AND " . $data_invertida . " <= compensacao_fim)) ORDER BY dia LIMIT 1 ");

        $oCopa = $oDBase->fetch_object();

        // se 62014 e dias dos jogos da Copa do Mundo 2014, ou
        // se 92014 e data dentro do período para compensação
        if (($ocorrencia_debito == $ocor) && ($oDBase->num_rows() == 0))
        {
            $mensagem = "Código '" . $ocor . "', uso restrito " . $msg6[$ocor] . "!";
        }
        else if (($ocorrencia_credito == $ocor) && ($oDBase->num_rows() == 0))
        {
            $mensagem = "Não é permitido lançar compensação de recesso (" . $ocorrencia_credito . ") fora do período legal!";
        }

        if ($exibe_msg == true)
        {
            mensagem($mensagem, $this->getDestino());
        }
        else
        {
            return $mensagem;
        }
    }

    ## @metodo
    #+----------------------------+
    #| Exibe o formulário         |
    #+----------------------------+
    #
    function exibeForm()
    {
        $oDBase = selecionaServidor( $this->getSiape() );
        $sitcad = $oDBase->fetch_object()->sigregjur;

        // Grupos de ocorrências
        $obj = new OcorrenciasGrupos();

        // Diferença registrada como débitos que podem ser compensadas até o
        // mês seguinte ao mês do registro da ocorrência
        $codigosCompensaveis = $obj->GrupoOcorrenciasNegativasDebitos($sitcad, $exige_horarios=true);

        // Diferença registrada como débitos que serão lançados em folha de
        // pagamento, no mês seguinte ao da ocorrência
        $codigosDebito = $obj->CodigosDebito($sitcad);

        // Diferença registrada como Crédito commpensando/quitando débitos
        $codigosCredito = $obj->CodigosCredito($sitcad);

        // Estas "ocorrências" devem ser alteradas pela chefia imediata para
        // ocorrências que informem qual ação deve ser adotada
        $codigosTrocaObrigatoria = $obj->CodigosTrocaObrigatoria($sitcad);

        // Ocorrências que podem ser abonadas
        $grupoOcorrenciasPassiveisDeAbono = $obj->GrupoOcorrenciasPassiveisDeAbono($sitcad);

        // Ocorrências padrão para registro parcial e sem frequência
        $codigoSemFrequenciaPadrao   = $obj->CodigoSemFrequenciaPadrao($sitcad);
        $codigoRegistroParcialPadrao = $obj->CodigoRegistroParcialPadrao($sitcad);

        $grupoOcorrenciasViagem      = $obj->GrupoOcorrenciasViagem($sitcad);

        $creditos_e_viagem = array_merge($codigosCredito, $grupoOcorrenciasViagem);
        sort($creditos_e_viagem);


        parent::exibeTopoHTML();
        parent::exibeCorpoTopoHTML();
        $nome               = $this->getNome();
        $_SESSION['sHNome'] = ($nome == '' ? $_SESSION['sHNome'] : $nome);

        if ($this->getOrigem(1) == "historico_frequencia.php")
        {
            // validacao dos campos
            $validacao = new valida();
            $validacao->setExibeMensagem(true);
            $validacao->setDestino($this->getDestino());
            $validacao->siape($this->getSiape());   // se matrícula inválida retorna para destino
            $validacao->siaperh($this->getSiape()); // o usuário não pode alterar sua própria frequência, retorna para destino
            $validacao->mes($this->getMes()); // se mes inválido retorna para destino
            $validacao->ano($this->getAno()); // se ano inválido retorna para destino
            $validacao->upagrh($this->getSetorUpag()); // se upag diferente do usuario retorna para destino
            // testa se o mes/ano informado é igual ao
            // mes/ano corrente ou o imediatamente anterior
            // e exibe mensagem de erro se igual
            // Ex.: - Mês e ano atual: 10/2010
            //      - Mês e ano imediatamente anterior: 09/2010
            //      - Mês e ano informado: 10/2010
            //      Exibe mensagem de erro, pois a competência informada é igual a competência corrente.
            $oData     = new trata_datasys;
            $sMesAno   = $oData->getCompetCompensado(); // mes anterior ao da homologação
            $sAnoMes   = substr($sMesAno, 2, 4) . substr($sMesAno, 0, 2);

            $sCompInv = $this->getAno() . $this->getMes();

            if ($sCompInv < '200910' || $sCompInv > $sAnoMes)
            {
                mensagem("A inclusão de ocorrências no histórico deverá ser utilizada apenas para competências de 10/2009 em diante, limitando-se sempre ao mês anterior ao da homologação (hoje: " . substr($sMesAno, 0, 2) . '/' . substr($sMesAno, 2, 4) . ").", $this->getOrigem(1));
            }
        }


        // texto (observacao)
        $oDBase      = new DataBase('PDO');
        $oDBase->setDestino($this->getDestino());
        $oDBase->setVoltar($this->getHistoryGo());

        $oDBase->setMensagem("Problemas no acesso a Tabela HISTÓRICO (E30022.".__LINE__.").");
        $oDBase->query("SELECT observacao, siaperh, DATE_FORMAT(registrado_em,'%d/%m/%Y %H:%m') AS registrado_em FROM historico_observacoes WHERE siape='" . $this->getSiape() . "' AND compet='" . $this->getAno() . $this->getMes() . "' ");
        $oObservacao = $oDBase->fetch_object();
        $observacao  = $oObservacao->observacao;

        // verifica se já existe o siape e
        // data de registro no texto da observação
        // mantemos o horario registrado antes
        $autoria_registro = "-Alteração realizada por: " . $_SESSION['sMatricula'] . " Em: " . date('d/m/Y') . ".\n";

        /*
          $testa_se_ha_registro = substr(ltrim(rtrim($observacao)),-strlen($autoria_registro));
          if ($testa_se_ha_registro != $autoria_registro)
          {
         */
        $observacao = $observacao . $autoria_registro;
        /*
          }
         */

        print "
			<style>
				A { font-size: 10px; font-family: verdana, arial, helvetica; font-weight: normal; color: #454545; }
				.ftFormFreq-tit-bc { font-family: verdana,arial,tahoma; font-size: 7pt; font-weight: bold; color: #000000; background-color: #DFDFBF; text-align: center; }
				.ftFormFreq-tit-bc-3 { font-family: verdana,arial,tahoma; font-size: 7pt; font-weight: bold; color: #000000; background-color: #DFDFBF; text-align: center; }
				.ftFormFreq-bc-1 { font-size: 7.5pt; font-weight: bold; color: #000000; background-color: #DFDFBF; text-align: center; vertical-align: middle; }
				.ftFormFreq-cn-1 { font-family: verdana,arial,tahoma; font-size: 7.5pt; color: #000000; text-align: center; vertical-align: middle; height: 15; }
				.ftFormFreq-cn-2 { font-family: verdana,arial,tahoma; font-size: 7.5pt; color: #000000; text-align: center; vertical-align: middle; height: 15; }
				.ftFormFreq-c { font-size: 7.5pt; color: #000000; background-color: #FFFFFF; text-align: center; vertical-align: middle; }
				.ftFormFreq-bc { font-size: 7.5pt; color: #000000; font-weight: bold; background-color: #FFFFFF; text-align: center; vertical-align: middle; }
				.ftFormFreq-c-2 { font-size: 8pt; color: #000000; background-color: #FFFFFF; text-align: center; vertical-align: middle; }
				.ftFormFreq-c-3 { font-size: 9pt; color: #000000; background-color: #FFFFFF; text-align: center; vertical-align: middle; }
				.ftFormFreq-bc-2 { font-family: Tahoma,verdana,arial; font-size: 11pt; font-weight: bold; color: #333300; background-color: #FFFFFF; text-align: center; vertical-align: middle; }
				.ftFormFreq-bc-4 { font-family: Arial;  font-size: 9pt; color: #005984; background-color: #FFFFFF; font-weight: bold; text-align: left; vertical-align: middle; }
			</style>";

        $form_action = $this->getFormAction();
        $form_submit = $this->getFormSubmit();

        print "
			<form method='post' action='" . $form_action . "' " . ($form_submit == "" ? "" : "onSubmit='$form_submit'") . " id='form1' name='form1' >
			<table class='tablew2' width='100%' border='0' align='center' cellpadding='0' cellspacing='0' style='border-collapse: collapse'>
			<tr>
			<td>
			<table class='tablew21' width='100%' border='1' align='center' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse'>
			<tr>
			<td colspan='5' valign='middle' height='25' class='ftFormFreq-bc-2'>" . tratarHTML($this->getMes()) . "/" . tratarHTML($this->getAno()) . "</td>
			</tr>";

      //obtendo dados da frequencia para exibir
      $this->loadDadosPonto();
      $nResult = $this->getResultBD();

      $oDBase2 = $this->setConexaoBD();

      $total_de_registros = $oDBase2->num_rows($nResult);

      // contas quantos dias tem ocorrencia
      // 88888 , 99999 e '-----'
      $this->ocorrenciasTotal();
      $ocorrencias_88888  = $this->getOcorrenciasTotal( $codigoRegistroParcialPadrao[0] ); // 88888
      $ocorrencias_99999  = $this->getOcorrenciasTotal( $codigoSemFrequenciaPadrao[0] );   // 99999
      $ocorrencias_tracos = $this->getOcorrenciasTotal( $codigosTrocaObrigatoria[0] );  //'-----'

      // quantidades de dias no mes
      $dias_registrados = $this->numeroDiasDoMes();

      // verificação dos dados
      $problemas = "";

      if ($ocorrencias_88888 > 0 || $ocorrencias_99999 > 0 || $ocorrencias_tracos > 0 || $dias_registrados > $total_de_registros)
      {
          $problemas .= "<tr><td colspan='5'><div align='center' style='font-size: 10pt; color: red; font-weight: bold; font-family: tahoma;'>";

          if ($dias_registrados > $total_de_registros)
          {
              $problemas .= "»» Falta(m) dia(s) na ficha do servidor ««";
          }
          if (($ocorrencias_88888 > 0 || $ocorrencias_99999 > 0) && $dias_registrados > $total_de_registros)
          {
              $problemas .= "<br>";
          }

          if ($ocorrencias_88888 > 0 && $ocorrencias_99999 > 0 && $ocorrencias_tracos > 0)
          {
              $problemas .= "»» Há ocorrência(s) com código " . implode(', ', $codigoRegistroParcialPadrao) . ", " . implode(', ', $codigoSemFrequenciaPadrao) . " e \"" . $codigosTrocaObrigatoria[0] . "\" na ficha do servidor ««";
          }
          else if ($ocorrencias_88888 > 0 && $ocorrencias_99999 > 0)
          {
              $problemas .= "»» Há ocorrência(s) com código " . implode(', ', $codigoRegistroParcialPadrao) . " e " . implode(', ', $codigoSemFrequenciaPadrao) . " na ficha do servidor ««";
          }
          else if ($ocorrencias_88888 > 0 && $ocorrencias_tracos > 0)
          {
              $problemas .= "»» Há ocorrência(s) com código " . implode(', ', $codigoRegistroParcialPadrao) . " e \"" . $codigosTrocaObrigatoria[0] . "\" na ficha do servidor ««";
          }
          else if ($ocorrencias_99999 > 0 && $ocorrencias_tracos > 0)
          {
              $problemas .= "»» Há ocorrência(s) com código " . implode(', ', $codigoSemFrequenciaPadrao) . " e \"" . $codigosTrocaObrigatoria[0] . "\" na ficha do servidor ««";
          }
          elseif ($ocorrencias_tracos > 0)
          {
              $problemas .= "»» Há ocorrência(s) com código \"" . $codigosTrocaObrigatoria[0] . "\" na ficha do servidor ««";
          }
          elseif ($ocorrencias_88888 > 0)
          {
              $problemas .= "»» Há ocorrência(s) com código " .implode(', ', $codigoRegistroParcialPadrao) . " na ficha do servidor ««";
          }
          elseif ($ocorrencias_99999 > 0)
          {
              $problemas .= "»» Há ocorrência(s) com código " .implode(', ', $codigoSemFrequenciaPadrao) . " na ficha do servidor ««";
          }
          $problemas .= "</div></td></tr>";
      } // fim do if

      print $problemas;

      print "
			<tr>
			<td height='20' class='ftFormFreq-tit-bc'>SIAPE</td>
			<td height='20' class='ftFormFreq-tit-bc' width='54%' colspan='3'>NOME</td>
			<td height='20' class='ftFormFreq-tit-bc'>LOTACAO</td>
			</tr>
			<tr>
			<td height='25' width='10%' align='center'><input type='text' id='siape' name='siape' class='centro' value='" . $this->getSiape() . "' size='10' readonly></td>
			<td height='25' colspan='3' align='centro'>&nbsp;<input type='text' id='nome' name='nome' class='Caixa' value='" . $this->getNome() . "' size='65' readonly>&nbsp;</td>
			<td height='25' width='14%' align='center'><input type='text' id='lotacao' name='lotacao' class='centro' value='" . $this->getLotacao() . "' size='13' readonly></td>
			</tr>";

        $hora_especial = $this->getHorarioEspecial();

        print "
			<tr>
			<td colspan='1' rowspan='2' class='ftFormFreq-tit-bc-3'>Horário do Setor</td><td height='20' colspan='3' class='ftFormFreq-tit-bc-3'>Horário do Servidor</td>
			<td height='20' rowspan='2' class='ftFormFreq-tit-bc-3'>Horário Especial</td>
			</tr>
			<tr>
			<td width='18%' height='20' class='ftFormFreq-tit-bc-3'>Entrada</td>
			<td width='36%' height='20' class='ftFormFreq-tit-bc-3'>Intervalo</td>
			<td width='18%' height='20' class='ftFormFreq-tit-bc-3'>Saída</td>
			</tr>
			<tr>
			<td height='25' colspan='1' align='center' nowrap>&nbsp;<input type='text' id='inicio' name='inicio' class='centro' value='" . $this->getInicioAtendimento() . "' size='8' readonly>&nbsp;<font class='ftFormFreq-c-3'>&agrave;s</font>&nbsp;<input type='text' id='fim' name='fim' class='centro' value='" . $this->getFimAtendimento() . "' size='8' readonly>&nbsp;</td>
			<td height='25' align='center'>&nbsp;<input type='text' id='entrada' name='entrada' class='centro' value='" . $this->getCadastroEntrada() . "' size='10' readonly>&nbsp;</td>
			<td height='25' align='center'>&nbsp;<input type='text' id='interve' name='interve' class='centro' value='" . $this->getCadastroInicioIntervalo() . "' size='10' readonly>&nbsp;<font class='ftFormFreq-c-3'>&agrave;s</font>&nbsp;<input type='text' id='intervs' name='intervs' class='centro' value='" . $this->getCadastroFimIntervalo() . "' size='10' readonly>&nbsp;</td>
			<td height='25' align='center'>&nbsp;<input name='saida' type='text' class='centro' id='saida' value='" . $this->getCadastroSaida() . "' size='10' readonly>&nbsp;</td>
			<td height='25' colspan='1' class='ftFormFreq-c'><b>" . ($hora_especial == "S" ? "SIM, $processo_hespecial" : "NAO") . "</b></td>
			</tr>
			</table>
			</td>
			</tr>";

        print "
			<tr>
			<td colspan='4'>
			<table class='tablew21' width='100%' border='1' align='center' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse'>
			<tr>
			<td width='12%' height='22' class='ftFormFreq-bc-1'>Dia</td>
			<td width='9%' class='ftFormFreq-bc-1'>Entrada</td>
			<td width='9%' class='ftFormFreq-bc-1'>Ida intervalo</td>
			<td width='9%' class='ftFormFreq-bc-1'>Volta Intervalo</td>
			<td width='9%' class='ftFormFreq-bc-1'>Saida</td>
			<td width='9%' class='ftFormFreq-bc-1'>Jornada do dia</td>
			<td width='10%' class='ftFormFreq-bc-1'>Jornada prevista</td>
			<td width='9%' class='ftFormFreq-bc-1'>Resultado do dia</td>
			<td width='8%' class='ftFormFreq-bc-1'>Ocorrencia</td>";

        $umavez = true;
        while ($pm     = $oDBase2->fetch_object($nResult))
        {
            // envia: mat, dia, nome, lot, idreg, c/cmd, oco, jnd, usu, an, operacao, entra, intini, intsai, sai, dtjnd
            $registro8  = "historico_registro8.php?dados=" . base64_encode($pm->dia . ":|:" . $this->getNome() . ":|:" . $pm->oco . ":|:" . $pm->entra . ":|:" . $pm->intini . ":|:" . $pm->intsai . ":|:" . $pm->sai . ":|:" . $pm->codigo);
            $registro15 = "historico_registro15.php?dados=" . base64_encode($pm->dia . ":|:" . $this->getNome() . ":|:" . $pm->oco . ":|:" . $pm->entra . ":|:" . $pm->intini . ":|:" . $pm->intsai . ":|:" . $pm->sai . ":|:" . $pm->codigo);

            if ($umavez == true)
            {
                $umavez       = false;
                $dia_nao_util = marca_dias_nao_util($this->getMes(), $this->getAno(), $pm->codmun, $pm->codigo);
            }
            $xdia       = $pm->dia;
            $background = $dia_nao_util[$xdia][0];
            $color      = $dia_nao_util[$xdia][1];

            $font_i_color = "";
            $sinal        = '&nbsp;';
            $font_f_color = "";

            // elimina "/" e ":", depois define o tipo como inteiro
            // para garantir a resultado do teste a seguir
            $jornada_dif = alltrim(sonumeros($pm->jorndif), '0');
            settype($jornada_dif, 'integer');

            if (!empty($jornada_dif) && in_array($pm->oco, $codigosCompensaveis))
            {
                $font_i_color = "<font color='red'>";
                $sinal        = " - ";
                $font_f_color = "</font>";
            }
            else if (!empty($jornada_dif) && in_array($pm->oco,$creditos_e_viagem))
            {
                $sinal = " + ";
            }

            print "
				<tr onmouseover='pinta(1,this)' onmouseout='pinta(2,this)' style='" . $background . "'>
				<td width='250' class='ftFormFreq-cn-1' style='" . $color . "' title='" . $dia_nao_util[$xdia][4] . "'>" . rtrim(ltrim($dia_nao_util[$xdia][2])) . '&nbsp;' . $xdia . $dia_nao_util[$xdia][3] . "</td>
				<td class='ftFormFreq-cn-1' style='" . $color . "'>" . tratarHTML($pm->entra) . "</td><td class='ftFormFreq-cn-1' style='" . $color . "'>" . tratarHTML($pm->intini) . "</td>
				<td class='ftFormFreq-cn-1' style='" . $color . "'>" . tratarHTML($pm->intsai) . "</td><td class='ftFormFreq-cn-1' style='" . $color . "'>" . tratarHTML($pm->sai) . "</td>
				<td class='ftFormFreq-cn-1' style='" . $color . "'>" . tratarHTML($pm->jornd) . "</td><td class='ftFormFreq-cn-1' style='" . $color . "'>" . tratarHTML($pm->jornp) . "</td>
				<td class='ftFormFreq-cn-1' style='" . $color . "; text-align: center;'>
				<table border='0' cellpadding='0' cellspacing='0'>
				<tr>
				<td class='ftFormFreq-cn-1' style='" . $color . "; text-align: center;' width='13'>" . $font_i_color . $sinal . $font_f_color . "</td>
				<td class='ftFormFreq-cn-1' style='" . $color . "; text-align: center;' width='37'>" . $font_i_color . $pm->jorndif . $font_f_color . "</td>
				</tr>
				</table>
				</td>
				<td class='ftFormFreq-cn-1' style='" . $color . "' title='" . $pm->dcod . "' nowrap>&nbsp;<a href='#semdestno' style='text-decoration: none;'>" . $pm->oco . "</a> - <a href=\"javascript:window.location.replace('" . $registro8 . "');\">Alterar</a>&nbsp;</td>
				</tr>";
        } // fim do while
        //if (substr_count("0877772_0877863", $_SESSION['sMatricula']) > 0)
        //{
        print "
				</table>
				<table class='ftFormFreq-cn-1' width='100%' border='1' align='center' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse'>
					<tr>
					<td class='ftFormFreq-bc-4' style='text-align: left; height: 25px;'>&nbsp;»»&nbsp;<a href=\"javascript:window.location.replace('" . $registro15 . "');\" class='ftFormFreq-bc-4'>ALTERAÇÃO POR PERÍODO</a>&nbsp;««&nbsp;</td>
					</tr>
				</table>";
        //}
        //else
        //{
        //	print "
        //	</table>";
        //}
        //ROTINA DE TOTALIZAÇÃO DAS HORAS
        $total_horas = rotina_de_totalizacao_de_horas($this->getSiape(), $this->getMes(), true, $this->getNomeDoArquivo());

        $nbspace = '&nbsp;';

        $totais   = array();
        $totais[] = array("Total do mes", $total_horas->comuns[1], $total_horas->comuns[0]);
        if ($total_horas->recesso[1] != 0)
        {
            $totais[] = array("Total de horas de recesso anual", $total_horas->recesso[1], $total_horas->recesso[0]);
        }
        if ($total_horas->instrutoria[1] != 0)
        {
            $totais[] = array("Total de horas de instrutoria", $total_horas->instrutoria[1], $total_horas->instrutoria[0]);
        }
        if ($total_horas->extras[1] != 0)
        {
            $totais[] = array("Total de Horas-extras", $total_horas->extras[1], $total_horas->extras[0]);
        }

        // total de horas
        print "
			<table class='ftFormFreq-cn-1' width='100%' border='1' align='center' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse'>";

        for ($i = 0; $i < count($totais); $i++)
        {
            $sinal = "";
            if (substr($totais[$i][1], 0, 1) == '-')
            {
                $sinal         = "<font color='red'> " . substr($totais[$i][1], 0, 1) . " </font>";
                $totais[$i][1] = "<font color='red'>" . substr($totais[$i][1], 1, 5) . "</font>";
            }
            else if (substr($totais[$i][1], 0, 1) == '+')
            {
                $sinal         = substr($totais[$i][1], 0, 1);
                $totais[$i][1] = substr($totais[$i][1], 1, 5);
            }
            $totais[$i][1] = "<table border='0' cellpadding='0' cellspacing='0'><tr><td class='ftFormFreq-cn-1' style='text-align: center;' width='13'>" . $sinal . "</td><td class='ftFormFreq-cn-1' style='text-align: center;' width='37'>" . $totais[$i][1] . "</td></tr></table>";

            print "
				<tr>
				<td class='ftFormFreq-cn-1'><b>" . $totais[$i][0] . "</b></td>
				<td class='ftFormFreq-cn-1' width='81'>" . $totais[$i][1] . "</td>
				<td class='ftFormFreq-cn-1' width='92'>&nbsp;" . $totais[$i][2] . "&nbsp;</td>
				</tr>";
        }

        print "
			</table>
			</td>
			</tr>
			<tr>
			<td colspan='4' style='border-top: 0 solid #808040; border-left: 0 solid #808040; border-right: 0 solid #808040; border-bottom: 0 solid #808040;'>
			<table class='tablew21' width='100%' border='0' align='center' cellpadding='0' cellspacing='0' id='AutoNumber1' style='border-collapse: collapse;'>
			<tr>
			<td style='font-size: 8px;'>
			<font color='red'><b>D: </b></font>Domingo&nbsp;&nbsp;&nbsp;&nbsp;
			<font color='red'><b>S: </b></font>Sabado&nbsp;&nbsp;&nbsp;&nbsp;
			<font color='red'><b>F: </b></font>Feriado/Facultativo&nbsp;&nbsp;&nbsp;&nbsp;
			<!--
			<font color='blue'><b>A: </b></font>Véspera de Ano Novo&nbsp;&nbsp;&nbsp;&nbsp;
			<font color='blue'><b>N: </b></font>Véspera de Natal&nbsp;&nbsp;&nbsp;&nbsp;
			<font color='blue'><b>Q: </b></font>Quarta-feira de Cinzas&nbsp;&nbsp;&nbsp;&nbsp;
			//-->
			(Posicione o mouse sobre o dia para ver a descricao)
			</td></tr></table></td></tr><tr><td colspan='4'></td>
			</tr>
			</table>";

        print "
			<br>
			<table width='80%' border='0' align='center' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse'>
			<tr>
			<td colspan='2' bgcolor='#DFDFBF'><div align='center'><font size='2'>Observação</font></div></td>
			</tr>
			<tr>
			<td colspan='2'><textarea id='observacao' name='observacao' cols='110' rows='5'>" . tratarHTML($observacao) . "</textarea></td>
			</tr>
			</table>";

      print "
			<div align='center'>
			<p>
			<input type='hidden' name='teste8' id='teste8' value='" . tratarHTML($ocorrencias_88888) . "'>
			<input type='hidden' name='teste9' id='teste9' value='" . tratarHTML($ocorrencias_99999) . "'>
			<input type='hidden' name='teste_dias' id='teste_dias' value='" . tratarHTML($dias_registrados) . "'>
			<input type='hidden' name='teste_diasmes' id='teste_diasmes' value='" . tratarHTML($qdias) . "'>
			<table border='0' align='center'>
			<tr>
			<td colspan='3'><!-- <font size='2'><strong>" . $this->getObservacaoTopo() . "</strong></font><br><br> //--></td>
			</tr>
			<tr>
			<td align='right'>" . botao('Concluir', 'javascript:' . $this->getFormSubmit() . ';') . "</td>
			<td>&nbsp;</td>
			<td align='left'>" . botao('Voltar', 'javascript:' . ($this->getHistoryGo() == 0 ? '' : 'window.history.go(-1);') . 'window.location.replace("' . $this->getOrigem(1) . '");') . "</td>
			</tr>
			</table>
			</p>
			</div>
			</form>";

        parent::exibeCorpoBaseHTML();
        parent::exibeBaseHTML();
    }

    ## @metodo
    #+----------------------------------+
    #| Exibe o formulário para registro |
    #| dos horários de frequencia       |
    #+----------------------------------+
    #
    function exibeFormRegistro14()
    {
        parent::exibeTopoHTML();
        parent::exibeCorpoTopoHTML();
        $nome               = $this->getNome();
        $_SESSION['sHNome'] = ($nome == '' ? $_SESSION['sHNome'] : $nome);

        // conexao com a base de dados
        $this->setDestino($_SESSION['sHOrigem_3']);
        $this->setVoltar($this->getHistoryGo());

        $this->loadDadosServidor();
        $this->loadDadosSetor();

        // verifica periodo do recesso
        $this->verificaPeriodoDoRecesso();

        // le o registro dos horarios no dia solicitado
        $this->loadDadosPonto($this->getData());
        $nResult = $this->getResultBD();

        $oDBase2 = $this->setConexaoBD();

        $oPonto = $oDBase2->fetch_object($nResult);
        $entra  = $oPonto->entra;
        $iniint = $oPonto->intini;
        $fimint = $oPonto->intsai;
        $sai    = $oPonto->sai;

        $numrows = $oDBase2->num_rows($nResult);

        if ($numrows == 0)
        {
            //header("Location: mensagem.php?modo=16&mat=$mat&nome=$nome&jnd=$jnc");
        }
        //registro12.php?mat=$mat&nome=$nome

        $this->verificaSeDiaUtil();

        $jornada = $this->formataJornadaParaHHMM();

        $form_action = $this->getFormAction();
        $form_submit = $this->getFormSubmit();

        print "
			<form method='post' action='" . $form_action . "' " . ($form_submit == "" ? "" : "onSubmit='$form_submit'") . " id='form1' name='form1' >
			<input type='hidden' name='compete' id='compete' value='" . tratarHTML($this->getMes()) . tratarHTML($this->getAno()) . "'>
			<input type='hidden' name='ocor'    id='ocor'    value='" . tratarHTML($this->getCodigoOcorrencia()) . "'>
			<input type='hidden' name='jornada_cargo' id='jornada_cargo' value='" . $jornada . "'>
			<input type='hidden' name='jnd'     id='jnd'     value='" . $jornada . "'>
			<input type='hidden' name='cmd'     id='cmd'     value='" . tratarHTML($this->getTipoOperacao()) . "'>
			<input type='hidden' name='jd2'     id='jd2'     value='" . ($jornada * 60) . "'>
			<input type='hidden' name='dutil'   id='dutil'   value='" . $this->getDiaUtil() . "'>
			<div align='center'>
			<table border='0' cellpadding='0' cellspacing='0' style='border-collapse: collapse' bordercolor='#808040' width='99%' id='AutoNumber1'>
			<tr>
			<td colspan='2' class='ft_13_002'>Dados do Servidor:</td>
			</tr>
			</table>
			<table border='1' cellpadding='0' cellspacing='0' style='border-collapse: collapse' bordercolor='#808040' width='99%' id='AutoNumber1'>
			<tr>
			<td width='619' height='46' class='tahomaSize_2'>
			Nome:<br>&nbsp;<input type='text' id='nome' name='nome' class='caixa' value='" . tratarHTML($this->getNome()) . "' size='60' readonly><div align='center'></div><div align='center'></div></td>
			<td width='144' height='46' align='center' class='tahomaSize_2'>
			Mat.Siape:<br>&nbsp;<input type='text' id='mat' name='mat' class='caixa' value='" . tratarHTML($this->getSiape()) . "' size='7' readonly></td>
			</tr>
			</table>

			<table width='99%' border='1' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse; margin-bottom: 0;'>
			<tr>
			<td width='81%' height='46' class='tahomaSize_2'>
			C&oacute;digo da Ocorr&ecirc;ncia:<br>";

        // tabela de ocorrencia
        $grupo_ocor = "'CH','AB'";
        if ($_SESSION['sRH'] == 'S')
        {
            $grupo_ocor .= ",'RH'";
        }

        // ocorrencia
        $ocor = $this->getCodigoOcorrencia();

        $oDBase = new DataBase('PDO');
        $oDBase->setVoltar($this->getHistoryGo());

        $oDBase->setMensagem("Problemas no acesso a Tabela FACULTATIVO (E30023.".__LINE__.").");
        $oDBase->query("SELECT codigo_debito FROM tabfacultativo172 WHERE dia='" . conv_data($this->getData()) . "' AND ativo='S' ");
        $codigo_excluir = ($oDBase->num_rows() > 0 ? "'00195'" : "'00195','" . $oDBase->fetch_object()->codigo_debito . "'");

        $oDBase->setMensagem("Problemas no acesso a Tabela OCORRÊNCIAS (E30024.".__LINE__.").");
        $oDBase->query("SELECT siapecad, desc_ocorr, cod_ocorr FROM tabocfre WHERE siapecad='$ocor' AND resp IN ($grupo_ocor) and (siapecad NOT IN ($codigo_excluir)) and ativo = 'S' ORDER BY desc_ocorr ");
        $campo = $oDBase->fetch_object();
        // Fim da tabela de ocorrencia

        print "
			&nbsp;<input type='text' id='' name='' class='caixa' value='" . tratarHTML($campo->siapecad). " - " . substr(tratarHTML($campo->desc_ocorr), 0, 60) . " - " . tratarHTML($campo->cod_ocorr) . "' size='78' readonly>
			</td>
			<td width='19%' height='46'>
				<p align='center' style='margin-top: 0; margin-bottom: 0'><font size='2' face='Tahoma'>
				Dia da Ocorr&ecirc;ncia:</font></p>
				<p align='center' style='margin-top: 0; margin-bottom: 0'>
				<input type='text' id='dia' name='dia' class='centro' value='" . $this->getData() . "' size='11' readonly>
			</td>
			</tr>
			</table>
			<table width='99%' border='0' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse; margin-bottom: 0;'>
			<tr>
			<td class='tahomaSize_2' style='width: 25%; text-align: center; vertical-align: middle; border-left: 1px solid #808040; border-right: 1px solid #808040; border-bottom: 1px solid #808040;' nowrap>
			&nbsp;Hora de In&iacute;cio do Expediente:<br>&nbsp;<input name='entra' type='text' class='alinhadoAoCentro' id='entra'  OnKeyPress=\"formatar(this, '##:##:##')\"  value='" . $entra . "' size='11' maxlength='8' title='Digite o horário sem pontos no formato 000000!!'></td>
			<td class='tahomaSize_2' style='width: 25%;text-align: center; vertical-align: middle; border-left: 1px solid #808040; border-right: 1px solid #808040; border-bottom: 1px solid #808040;' nowrap>
			&nbsp;Hora de In&iacute;cio do Intervalo:<br>&nbsp;<input name='iniint' type='text' class='alinhadoAoCentro' id='iniint'  OnKeyPress=\"formatar(this, '##:##:##')\"  value='" . $iniint . "' size='11' maxlength='8' title='Digite o horário sem pontos no formato 000000!!'></td>
			<td class='tahomaSize_2' style='width: 25%;text-align: center; vertical-align: middle; border-left: 1px solid #808040; border-right: 1px solid #808040; border-bottom: 1px solid #808040;' nowrap>
			&nbsp;Hora de Retorno do Intervalo:<br>&nbsp;<input name='fimint' type='text' class='alinhadoAoCentro' id='fimint'  OnKeyPress=\"formatar(this, '##:##:##')\"  value='" . $fimint . "' size='11' maxlength='8' title='Digite o horário sem pontos no formato 000000!!'></td>
			<td class='tahomaSize_2' style='width: 25%;text-align: center; vertical-align: middle; border-left: 1px solid #808040; border-right: 1px solid #808040; border-bottom: 1px solid #808040;' nowrap>
			&nbsp;Hor&aacute;rio da Sa&iacute;da:<br>&nbsp;<input name='hsaida' type='text' class='alinhadoAoCentro' id='hsaida'  OnKeyPress=\"formatar(this, '##:##:##')\"  value='" . $sai . "' size='11' maxlength='8' title='Digite o horário sem pontos no formato 000000!!'></td>
			</tr>
			</table>
			<br>
			<table border='0' align='center'>
			<tr>
			<td align='right'>" . botao('Continuar', 'javascript:return testa();') . "</td>
			<td>&nbsp;</td>
			<td align='left'>" . botao('Voltar', 'javascript:' . ($this->getHistoryGo() == 0 ? '' : 'window.history.go(-1);') . 'window.location.replace("' . $this->getOrigem(3) . '");') . "</td>
			</tr>
			</table>
			</p>
			</div>
			</form>";

        parent::exibeCorpoBaseHTML();
        parent::exibeBaseHTML();
    }

    ## @metodo
    #+----------------------------------+
    #| Exibe o formulário para registro |
    #| dos horários de frequencia       |
    #+----------------------------------+
    #
    function exibeFormRegistro15()
    {
        parent::exibeTopoHTML();
        parent::exibeCorpoTopoHTML();
        $nome               = $this->getNome();
        $_SESSION['sHNome'] = ($nome == '' ? $_SESSION['sHNome'] : $nome);

        // conexao com a base de dados
        $this->setDestino($_SESSION['sHOrigem_2']);
        $this->setVoltar($this->getHistoryGo());

        $this->loadDadosServidor();
        $this->loadDadosSetor();

        // verifica periodo do recesso
        $this->verificaPeriodoDoRecesso();

        // le o registro dos horarios no dia solicitado
        $this->loadDadosPonto($this->getData());
        $nResult = $this->getResultBD();

        $oDBase2 = $this->setConexaoBD();

        $oPonto = $oDBase2->fetch_object($nResult);
        $entra  = $oPonto->entra;
        $iniint = $oPonto->intini;
        $fimint = $oPonto->intsai;
        $sai    = $oPonto->sai;

        $numrows = $oDBase2->num_rows($nResult);

        if ($numrows == 0)
        {
            //header("Location: mensagem.php?modo=16&mat=$mat&nome=$nome&jnd=$jnc");
        }
        //registro12.php?mat=$mat&nome=$nome

        $this->verificaSeDiaUtil();

        $jornada = $this->formataJornadaParaHHMM();

        $form_action = $this->getFormAction();
        $form_submit = $this->getFormSubmit();

        print "
			<form method='post' action='" . $form_action . "' " . ($form_submit == "" ? "" : "onSubmit='$form_submit'") . " id='form1' name='form1' >
			<input type='hidden' name='ano'     id='ano'     value='" . tratarHTML($this->getAno()) . "'>
			<input type='hidden' name='mes'     id='mes'     value='" . tratarHTML($this->getMes()) . "'>
			<input type='hidden' name='jornada_cargo' id='jornada_cargo' value='" . tratarHTML($jornada) . "'>
			<input type='hidden' name='jnd'     id='jnd'     value='" . tratarHTML($jornada) . "'>
			<input type='hidden' name='cmd'     id='cmd'     value='" . tratarHTML($this->getTipoOperacao()) . "'>
			<input type='hidden' name='jd2'     id='jd2'     value='" . ($jornada * 60) . "'>
			<input type='hidden' name='dutil'   id='dutil'   value='" . tratarHTML($this->getDiaUtil()) . "'>

			<div align='center'>
			<table border='0' cellpadding='0' cellspacing='0' style='border-collapse: collapse' bordercolor='#808040' width='97%' id='AutoNumber1'>
			<tr>
			<td colspan='2' class='ft_13_002'>Dados do Servidor:</td>
			</tr>
			</table>
			<table border='1' cellpadding='0' cellspacing='0' style='border-collapse: collapse' bordercolor='#808040' width='97%' id='AutoNumber1'>
			<tr>
			<td width='70%' height='46' class='tahomaSize_2'>
			Nome:<br>&nbsp;<input type='text' id='nome' name='nome' class='caixa' value='" . tratarHTML($this->getNome()) . "' size='60' readonly><div align='center'></div><div align='center'></div></td>
			<td width='10%' height='46' align='center' class='tahomaSize_2'>
			Mat.Siape:<br>&nbsp;<input type='text' id='mat' name='mat' class='centro' value='" . tratarHTML($this->getSiape()) . "' size='7' readonly></td>
			<td width='20%' align='center' class='tahomaSize_2'>
			Competência:<br>&nbsp;<input type='text' id='compete' name='compete' class='centro' value='" . tratarHTML($this->getMes()) . '/' . tratarHTML($this->getAno()) . "' size='9' readonly></td>
			</tr>
			</table>

			<table width='97%' border='1' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse; margin-bottom: 0;'>
			<tr>
			<td width='80%' height='46' class='tahomaSize_2'>
			C&oacute;digo da Ocorr&ecirc;ncia:<br>";

        // tabela de ocorrencia
        $grupo_ocor = "'CH','AB'";
        if ($_SESSION['sRH'] == 'S')
        {
            $grupo_ocor .= ",'RH'";
        }

        // ocorrencia
        $ocor              = '-----';
        $this->getCodigoOcorrencia();
        $codigo_selecionar = "'-----','00000','00111','00124','00128','00129','00136','00137','00167','00168','00169','02323','02525','02727','55555'";

        // instancia
        $oDBase = new DataBase('PDO');
        $oDBase->setVoltar($this->getHistoryGo());

        $oDBase->setMensagem("Problemas no acesso a Tabela OCORRÊNCIAS (E30025.".__LINE__.").");
        $oDBase->query("SELECT siapecad, desc_ocorr, cod_ocorr FROM tabocfre WHERE resp IN ($grupo_ocor) and (siapecad IN ($codigo_selecionar)) and ativo = 'S' ORDER BY desc_ocorr ");
        $html   .= "<select id='ocor' name='ocor' size='1' class='drop'>";
        while ($campo  = $oDBase->fetch_object())
        {
            $html .= "<option value=\"" . tratarHTML($campo->siapecad) . "\"";
            $html .= ($ocor == $campo->siapecad ? 'selected' : '') . ">";
            $html .= tratarHTML($campo->siapecad) . " - " . substr(tratarHTML($campo->desc_ocorr), 0, 60) . " - " . (empty($campo->desc_ocorr) ? "Selecione uma ocorrência" : "SIRH ") . tratarHTML($campo->cod_ocorr) . " </option>";
        }
        // Fim da tabela de ocorrencia
        $html .= "</select>";
        $html .= "<a href= \"javascript:Abre('tabocfre.php',1060,350)\"><img border= '0' src='" . _DIR_IMAGEM_ . "pesquisa.gif' width='17' height='17' align='absmiddle' alt='Visualizar detalhes da ocorrência.'></a><font size='2' face='Tahoma'> </font></td>";
        print $html;
        // Fim da tabela de ocorrencia

        print "
			<td width='10%'>
				<p align='center' style='margin-top: 0; margin-bottom: 0'><font size='2' face='Tahoma'>
				Dia Início:</font></p>
				<p align='center' style='margin-top: 0; margin-bottom: 0'>
				<input type='text' id='dia_ini' name='dia_ini' class='centro' value='' size='2' OnKeyup='javascript:ve(this.value);'></p>
			</td>
			<td width='10%' height='46px'>
				<p align='center' style='margin-top: 0; margin-bottom: 0'><font size='2' face='Tahoma'>
				Dia Térmimo:</font></p>
				<p align='center' style='margin-top: 0; margin-bottom: 0'>
				<input type='text' id='dia_fim' name='dia_fim' class='centro' value='' size='2'></p>
			</td>
			</tr>
			</table>
			<br>
			<table border='0' align='center'>
			<tr>
			<td align='right'>" . botao('Continuar', 'javascript:return testa();') . "</td>
			<td>&nbsp;</td>
			<td align='left'>" . botao('Voltar', 'javascript:' . ($this->getHistoryGo() == 0 ? '' : 'window.history.go(-1);') . 'window.location.replace("' . $this->getOrigem(2) . '");') . "</td>
			</tr>
			</table>
			</p>
			</div>
			</form>";

        parent::exibeCorpoBaseHTML();
        parent::exibeBaseHTML();
    }

    ## @metodo
    #+----------------------------+
    #| Exibe o(s) saldo(s)        |
    #+----------------------------+
    #
    function exibeSaldos($html = "")
    {
        parent::exibeTopoHTML();
        parent::exibeCorpoTopoHTML();
        $nome               = $this->getNome();
        $_SESSION['sHNome'] = ($nome == '' ? $_SESSION['sHNome'] : $nome);

        $this->loadDadosServidor();
        $this->loadDadosSetor();

        $hora_especial = $this->getHorarioEspecial();

        print "
			<style>
				A { font-size: 10px; font-family: verdana, arial, helvetica; font-weight: normal; color: #454545; }
				.ftFormFreq-tit-bc { font-family: verdana,arial,tahoma; font-size: 7pt; font-weight: bold; color: #000000; background-color: #DFDFBF; text-align: center; }
				.ftFormFreq-tit-bc-3 { font-family: verdana,arial,tahoma; font-size: 7pt; font-weight: bold; color: #000000; background-color: #DFDFBF; text-align: center; }
				.ftFormFreq-bc-1 { font-size: 7.5pt; font-weight: bold; color: #000000; background-color: #DFDFBF; text-align: center; vertical-align: middle; }
				.ftFormFreq-cn-1 { font-family: verdana,arial,tahoma; font-size: 7.5pt; color: #000000; text-align: center; vertical-align: middle; height: 15; }
				.ftFormFreq-cn-2 { font-family: verdana,arial,tahoma; font-size: 7.5pt; color: #000000; text-align: center; vertical-align: middle; height: 15; }
				.ftFormFreq-c { font-size: 7.5pt; color: #000000; background-color: #FFFFFF; text-align: center; vertical-align: middle; }
				.ftFormFreq-bc { font-size: 7.5pt; color: #000000; font-weight: bold; background-color: #FFFFFF; text-align: center; vertical-align: middle; }
				.ftFormFreq-c-2 { font-size: 8pt; color: #000000; background-color: #FFFFFF; text-align: center; vertical-align: middle; }
				.ftFormFreq-c-3 { font-size: 9pt; color: #000000; background-color: #FFFFFF; text-align: center; vertical-align: middle; }
				.ftFormFreq-bc-2 { font-family: Tahoma,verdana,arial; font-size: 11pt; font-weight: bold; color: #333300; background-color: #FFFFFF; text-align: center; vertical-align: middle; }
			</style>

			<table class='tablew21' width='100%' border='1' align='center' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse'>
			<tr>
			<td colspan='5' valign='middle' height='25' class='ftFormFreq-bc-2'>" . $this->getMes() . "/" . $this->getAno() . "</td>
			</tr>
			<tr>
			<td height='20' class='ftFormFreq-tit-bc'>SIAPE</td>
			<td height='20' class='ftFormFreq-tit-bc' width='54%' colspan='3'>NOME</td>
			<td height='20' class='ftFormFreq-tit-bc'>LOTACAO</td>
			</tr>
			<tr>
			<td height='25' width='10%' align='center'><input type='text' id='siape' name='siape' class='centro' value='" . tratarHTML($this->getSiape()) . "' size='10' readonly></td>
			<td height='25' colspan='3' align='centro'>&nbsp;<input type='text' id='nome' name='nome' class='Caixa' value='" . tratarHTML($this->getNome()) . "' size='65' readonly>&nbsp;</td>
			<td height='25' width='14%' align='center'><input type='text' id='lotacao' name='lotacao' class='centro' value='" . tratarHTML($this->getLotacao()) . "' size='13' readonly></td>
			</tr>
			<tr>
			<td colspan='1' rowspan='2' class='ftFormFreq-tit-bc-3'>Horário do Setor</td><td height='20' colspan='3' class='ftFormFreq-tit-bc-3'>Horário do Servidor</td>
			<td height='20' rowspan='2' class='ftFormFreq-tit-bc-3'>Horário Especial</td>
			</tr>
			<tr>
			<td width='18%' height='20' class='ftFormFreq-tit-bc-3'>Entrada</td>
			<td width='36%' height='20' class='ftFormFreq-tit-bc-3'>Intervalo</td>
			<td width='18%' height='20' class='ftFormFreq-tit-bc-3'>Saída</td>
			</tr>
			<tr>
			<td height='25' colspan='1' align='center' nowrap>&nbsp;<input type='text' id='inicio' name='inicio' class='centro' value='" . tratarHTML($this->getInicioAtendimento()) . "' size='8' readonly>&nbsp;<font class='ftFormFreq-c-3'>&agrave;s</font>&nbsp;<input type='text' id='fim' name='fim' class='centro' value='" . tratarHTML($this->getFimAtendimento()) . "' size='8' readonly>&nbsp;</td>
			<td height='25' align='center'>&nbsp;<input type='text' id='entrada' name='entrada' class='centro' value='" . tratarHTML($this->getCadastroEntrada()) . "' size='10' readonly>&nbsp;</td>
			<td height='25' align='center'>&nbsp;<input type='text' id='interve' name='interve' class='centro' value='" . tratarHTML($this->getCadastroInicioIntervalo()) . "' size='10' readonly>&nbsp;<font class='ftFormFreq-c-3'>&agrave;s</font>&nbsp;<input type='text' id='intervs' name='intervs' class='centro' value='" . tratarHTML($this->getCadastroFimIntervalo()) . "' size='10' readonly>&nbsp;</td>
			<td height='25' align='center'>&nbsp;<input name='saida' type='text' class='centro' id='saida' value='" . tratarHTML($this->getCadastroSaida()) . "' size='10' readonly>&nbsp;</td>
			<td height='25' colspan='1' class='ftFormFreq-c'><b>" . ($hora_especial == "S" ? "SIM, " . tratarHTML($processo_hespecial) : "NAO") . "</b></td>
			</tr>
			</table>" . $html;

        parent::exibeCorpoBaseHTML();
        parent::exibeBaseHTML();
    }
}
