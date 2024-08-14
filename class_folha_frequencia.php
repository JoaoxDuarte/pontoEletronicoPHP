<?php
/**
 * @info Carrega dados do servidor e exibe a frequência do mês
 *         Cadastro;
 *         Frequência;
 *         Unidade;
 *
 * @version
 * @author Edinalvo Rosa
 */
include_once( "config.php" );
include_once( "class_ocorrencias_grupos.php" );


/* @class
 *
 */
class FolhaFrequencia
{
    private $siape; // string Matrícula do servidor/estagiário
    private $mes;   // string Mês da competência desejada
    private $ano;   // string Ano da competência desejada

    private $objCadastro;   // object Dados cadastrais
    private $objFrequencia; // object Dados da frequencia
    private $objJornada;    // object Dados referente a jornada do dia

    public function __construct(array $config = NULL)
    {
        // se não passou parametro
        $this->setSiape( $config['siape'] );
        $this->setMes( $config['mes'] );
        $this->setAno( $config['ano'] );
    }

    public function CarregaDados()
    {
        $this->objCadastro   = $this->Cadastro()->fetch_object();
        $this->objFrequencia = $this->Frequencia()->fetch_object();
        $this->objJornada    = $this->Jornada()->fetch_object();
    }

    public function setSiape($value=NULL)
    {
        $this->siape = (is_null($value) || empty($value) ? $this->getSiape() : $value);
    }

    public function getSiape()
    {
        if (is_null($this->siape) || empty($this->siape))
        {
            $this->siape = $_SESSION['sMatricula'];
        }

        return $this->siape;
    }

    public function setAno($value=NULL)
    {
        $this->ano = (is_null($value) || empty($value) ? $this->getAno() : $value);
    }

    public function getAno()
    {
        if (is_null($this->ano) || empty($this->ano))
        {
            $this->ano = date('Y');
        }

        return $this->ano;
    }

    public function setMes($value=NULL)
    {
        $this->mes = (is_null($value) || empty($value) ? $this->getMes() : $value);
    }

    public function getMes()
    {
        if (is_null($this->mes) || empty($this->mes))
        {
            $this->mes = date('m');
        }

        return $this->mes;
    }

    public function Filtro()
    {
        $compet = $this->getAno().$this->getMes();

        $filtro = array();

        // administrador Brasil
        if ($_SESSION['sSenhaI'] === 'S')
        {
            $filtro['where']  = " AND true";
            $filtro['params'] = array(
                array( ':siape',  $this->getSiape(), PDO::PARAM_STR ),
                array( ':compet', $compet,           PDO::PARAM_STR ),
            );

            return $filtro;
        }
        // RH autorizado (UPAG)
        if ($_SESSION['sRH'] === 'S')
        {
            $filtro['where']  = " AND tabsetor.upag = :upag ";
            $filtro['params'] = array(
                array( ':siape',  $this->getSiape(), PDO::PARAM_STR ),
                array( ':upag',   $_SESSION['upag'], PDO::PARAM_STR ),
                array( ':compet', $compet,           PDO::PARAM_STR ),
            );

            return $filtro;
        }
        // Chefia imediata (UORG e UORG imediatamente subordinadas)
        if ($_SESSION['sAPS'] === 'S' AND $_SESSION['sRH'] !== 'S')
        {
            $filtro['where']  = "
                AND (servativ.cod_lot = :setor
                     OR tabsetor.cod_uorg_pai = :setor) ";
            $filtro['params'] = array(
                array( ':siape',  $this->getSiape(),     PDO::PARAM_STR ),
                array( ':setor',  $_SESSION["sLotacao"], PDO::PARAM_STR ),
                array( ':compet', $compet,               PDO::PARAM_STR ),
            );

            return $filtro;
        }

        // dados padrão
        $filtro['where']  = " AND false";
        $filtro['params'] = array(
            array( ':siape',  $this->getSiape(), PDO::PARAM_STR ),
            array( ':compet', $compet,           PDO::PARAM_STR ),
        );

        return $filtro;
    }

    public function Cadastro(array $config = NULL)
    {
        if (is_array($config))
        {
            $this->siape = $config['siape'];
            $this->mes   = $config['mes'];
            $this->ano   = $config['ano'];
        }

        $this->siape = getNovaMatriculaBySiape( $this->siape );
        
        $dadosFiltro = $this->Filtro();
        $filtro = $dadosFiltro['where'];
        $params = $dadosFiltro['params'];

        $sql = "
        SELECT
            servativ.mat_siape AS siape,
            servativ.nome_serv AS nome,
            servativ.cod_lot   AS setor,
            servativ.excluido,
            servativ.chefia,
            servativ.entra_trab,
            servativ.sai_trab,
            tabsetor.inicio_atend,
            tabsetor.fim_atend,
            servativ.area,
            servativ.cod_sitcad,
            servativ.jornada,
            DATE_FORMAT(servativ.dt_adm, '%d/%m/%Y') AS dt_adm,
            tabsetor.uorg_pai,
            IF(IFNULL(homologados.homologado,'N') NOT IN ('V','S'),'N','S') AS `status`
        FROM
            servativ
        LEFT JOIN
            tabsetor ON servativ.cod_lot = tabsetor.codigo
        LEFT JOIN
            homologados ON (servativ.mat_siape = homologados.mat_siape)
                           AND (homologados.compet = :compet)
        WHERE
            servativ.excluido = 'N'
            AND servativ.cod_sitcad NOT IN ('02','08','15')
            AND servativ.mat_siape = :siape
            ".$filtro."
        ORDER BY
            servativ.nome_serv, servativ.cod_sitcad, servativ.entra_trab
        ";

        // instancia a class DataBase
        $oDBase = new DataBase('PDO');
        $oDBase->query( $sql, $params );

        return $oDBase;
    }

    public function Jornada($siape=NULL,$dia=NULL)
    {
        $this->setSiape($siape);
        $dia   = (is_null($dia) ? date('Y-m-t') : $dia);

        $oDBase = new DefinirJornada();

        return $oDBase->PesquisaJornadaHistorico($this->getSiape(), $dia);
    }

    public function Frequencia($siape=NULL,$mes=NULL,$ano=NULL)
    {
        $siape = $this->getSiape($siape);
        $mes   = $this->getMes($mes);
        $ano   = $this->getAno($ano);

        $comp = $mes . $ano;

        if ($mes == date('m') && $ano == date('Y'))
        {
            $dia = date('j');
        }
        else
        {
            $dia = date('t');
        }

        ## inclui dias sem frequência
        inserir_dias_sem_frequencia($siape, $dia, $mes, $ano, $jornada, $_SESSION['sLotacao'], $nome_do_arquivo, $pm->dt_adm);

        $dadosFiltro = $this->Filtro();
        $filtro = $dadosFiltro['where'];
        $params = $dadosFiltro['params'];

        $sql = "
        SELECT
            ponto.siape,
            DATE_FORMAT(ponto.dia,'%d/%m/%Y') AS dia,
            ponto.entra,
            ponto.intini,
            ponto.intsai,
            ponto.sai,
            ponto.jornd,
            ponto.jornp,
            ponto.jorndif,
            ponto.oco,
            REPLACE(ponto.just,';',':') AS just,
            REPLACE(ponto.justchef,';',':') AS justchef,
            ponto.idreg,
            ponto.ip,
            ponto.matchef,
            ponto.siaperh,
            tabocfre.desc_ocorr AS dcod,
            tabsetor.codigo,
            tabsetor.inicio_atend,
            tabsetor.fim_atend,
            tabsetor.cod_uorg,
            tabsetor.upag,
            tabsetor.codmun,
            tabsetor.descricao,
            taborgao.denominacao,
            taborgao.sigla,
            servativ.cod_sitcad,
            DATE_FORMAT(servativ.dt_adm,'%d/%m/%Y') AS dt_adm,
            IF(IFNULL(homologados.homologado,'N') NOT IN ('V','S'),'N','S') AS `status`
        FROM
            ponto$comp AS ponto
        LEFT JOIN
            tabocfre ON ponto.oco = tabocfre.siapecad
        LEFT JOIN
            servativ ON ponto.siape = servativ.mat_siape
        LEFT JOIN
            homologados ON (servativ.mat_siape = homologados.mat_siape)
                           AND (homologados.compet = :compet)
        LEFT JOIN
            tabsetor ON servativ.cod_lot = tabsetor.codigo
        LEFT JOIN
            taborgao ON LEFT(tabsetor.codigo,5) = taborgao.codigo
        WHERE
            ponto.siape = :siape
            ".$filtro."
        ORDER BY
            ponto.siape, ponto.dia
        ";

        // instancia a class DataBase
        $oDBase = new DataBase('PDO');
        $oDBase->query( $sql, $params );

        if ($oDBase->num_rows() > 0)
        {
            return $oDBase;
        }
        else
        {
            return null;
        }
    }

    /**
     * Verifica os destinatários e executa a função que envia o email
     *
     * @param void
     * @param string      $assunto       Assunto do e-mail
     * @param string      $texto         Corpo do e-mail
     * @param string|null $msg_sucesso   Mensagem quando há sucesso
     * @param string|null $msg_erro      Mensagem em caso de erro
     *
     * @return array
     */
    public function RegistrosDeFrequencia(array $dados = NULL)
    {
        global $data_admissao;

        $siape = (is_null($dados['siape']) ? $this->getSiape() : $dados['siape']);
        $mes   = (is_null($dados['mes']) ? $this->getMes() : $dados['mes']);
        $ano   = (is_null($dados['ano']) ? $this->getAno() : $dados['ano']);
        $cmd   = (is_null($dados['cmd']) ? "" : $dados['cmd']);

        // Mês e ano corrente
        $comp_invertida = $ano . $mes;
        $comp           = $mes . $ano;

        $sitcad = NULL;

        $ocorrencias_88888  = 0;
        $ocorrencias_99999  = 0;
        $ocorrencias_tracos = 0;


        // $codigosCompensaveis :
        //          Diferença registrada como débitos que podem ser
        //          compensadas até o mês seguinte ao mês  do registro
        //          da ocorrência
        //
        // $codigosDebito :
        //          Diferença registrada como débitos que serão lançados
        //          em folha de pagamento, no mês seguinte ao da ocorrência
        //
        // $codigosCredito :
        //          Diferença registrada como Crédito
        //          compensando/quitando débitos
        //
        // $codigosTrocaObrigatoria :
        //          Estas "ocorrências" devem ser alteradas pela chefia
        //          imediata para ocorrências que informem qual  ação deve ser
        //          adotada compensando/quitando débitos
        //
        // $grupoOcorrenciasPassiveisDeAbono :
        //          Ocorrências que podem ser abonadas
        //
        // $codigoSemFrequenciaPadrao :
        //          Ocorrências padrão para registro parcial
        //
        // $codigoRegistroParcialPadrao :
        //          Ocorrências padrão para sem frequência
        //

        $oDBase = selecionaServidor( $this->getSiape() );
        $sitcad = $oDBase->fetch_object()->sigregjur;

        // Grupos de ocorrências
        $obj = new OcorrenciasGrupos();

        $codigosCompensaveis              = $obj->GrupoOcorrenciasNegativasDebitos($sitcad, $exige_horarios=true);
        $codigosDebito                    = $obj->CodigosDebito($sitcad);
        $codigosCredito                   = $obj->CodigosCredito($sitcad);
        $codigosTrocaObrigatoria          = $obj->CodigosTrocaObrigatoria($sitcad);
        $grupoOcorrenciasPassiveisDeAbono = $obj->GrupoOcorrenciasPassiveisDeAbono($sitcad);
        $codigoSemFrequenciaPadrao        = $obj->CodigoSemFrequenciaPadrao($sitcad);
        $codigoRegistroParcialPadrao      = $obj->CodigoRegistroParcialPadrao($sitcad);


        $siape = getNovaMatriculaBySiape($siape);

        $status_homologacao = verifica_se_mes_homologado($siape, $ano . $mes);

        $registrosComparecimentoOcorrencia = array();

        $oDBase = $this->Frequencia($siape, $mes, $ano);

        if ((is_null($oDBase) === false) && ($oDBase->num_rows() > 0))
        {
            $umavez    = true;
            $atribuido = false;

            while ($pm_partners = $oDBase->fetch_object())
            {
                if ($atribuido == false)
                {
                    $sNome                   = $pm_partners->nome;
                    $horario_do_setor_inicio = $pm_partners->inicio_atend;
                    $horario_do_setor_fim    = $pm_partners->fim_atend;
                    $cmun                    = $pm_partners->codmun;
                    $uorg                    = $pm_partners->cod_uorg;
                    $upag                    = $pm_partners->upag;
                    $anomes_admissao         = $pm_partners->dt_adm;
                    $lotacao                 = $pm_partners->codigo;
                    $lotacao_descricao       = $pm_partners->descricao;
                    $orgao_sigla             = $pm_partners->sigla;
                    $cod_sitcad              = $pm_partners->cod_sitcad;

                    $oJornada            = new DefinirJornada();
                    $oDBaseJH            = $oJornada->PesquisaJornadaHistorico($siape, '01/' . $mes . '/' . $ano);
                    $oHorario            = $oDBaseJH->fetch_object();
                    $entrada_no_servico  = $oHorario->entra_trab;
                    $saida_para_o_almoco = $oHorario->ini_interv;
                    $volta_do_almoco     = $oHorario->sai_interv;
                    $saida_do_servico    = $oHorario->sai_trab;
                    $jnd                 = $oHorario->jornada;

                    $atribuido = true;
                }

                if (in_array($pm_partners->oco, $codigoRegistroParcialPadrao))
                {
                    $ocorrencias_88888++;
                }

                if (in_array($pm_partners->oco, $codigoSemFrequenciaPadrao))
                {
                    $ocorrencias_99999++;
                }

                if ($pm_partners->oco == $codigosTrocaObrigatoria[0]) //'-----'
                {
                    $ocorrencias_tracos++;
                }

                ## Prepara os dados para exibir
                #
                if ($umavez == true)
                {
                    $umavez       = false;
                    $dia_nao_util = marca_dias_nao_util($mes, $ano, $pm_partners->codmun, $pm_partners->codigo);
                }
                $xdia       = $pm_partners->dia;
                $background = $dia_nao_util[$xdia][0];
                $color      = $dia_nao_util[$xdia][1];


                if (in_array($pm_partners->oco, $codigosTrocaObrigatoria))
                {
                    $pm_partners_oco = "<font color='red'><b>" . $pm_partners->oco . "</b></font>";
                }
                else
                {
                    $pm_partners_oco = $pm_partners->oco;
                }


                ## destaques de com cores
                #  e sinais de '+' ou '-'
                #
                $font_i_color = "";
                $font_f_color = "";
                $sinal        = '&nbsp;';

                // elimina "/" e ":", depois define o tipo como inteiro
                // para garantir o resultado do teste a seguir
                $jornada_dif = alltrim(sonumeros($pm_partners->jorndif), '0');
                settype($jornada_dif, 'integer');


                if (!empty($jornada_dif) && in_array($pm_partners->oco, $codigosCompensaveis))
                {
                    $font_i_color = "<font color='red'>";
                    $font_f_color = "</font>";
                    $sinal        = " - ";
                }
                else if (!empty($jornada_dif) && in_array($pm_partners->oco, $codigosDebito))
                {
                    $font_i_color = "<font color='red'>(";
                    $font_f_color = ")</font>";
                    $sinal        = "";
                }
                else if (!empty($jornada_dif) && in_array($pm_partners->oco, $codigosCredito))
                {
                    $sinal = " + ";
                }

                if (in_array($pm_partners->oco, $codigosTrocaObrigatoria))
                {
                    $codigo_da_ocorrencia = "<font color='red'><b>" . $pm_partners->oco . "</b></font>";
                }
                else
                {
                    $codigo_da_ocorrencia = $pm_partners->oco;
                }


                ## Ação: JUSTIFICAR (VER JUSTIFICATIVA)
                #
                $sDados = $siape
                    . ":|:" . utf8_iso88591(str_replace('"', '', $sNome))
                    . ":|:" . $lotacao
                    . ":|:" . $comp
                    . ":|:" . $pm_partners->dia
                    . ":|:" . utf8_iso88591(str_replace('"', '', $pm_partners->just))
                    . ":|:" . $pm_partners->oco
                    . ":|:" . $cmd;


                // justificativas só podem ser registradas ou alteradas antes da homologação
                if (($mes_homologado == 'HOMOLOGADO') || (isset($_SESSION['registrar_justificativa']) && $_SESSION['registrar_justificativa'] == false))
                {
                    $sDados .= ":|:sim";
                }
                else
                {
                    $sDados .= ":|:";
                }
                $sDados .= ":|:" . utf8_iso88591(str_replace('"', '', $pm_partners->justchef));

                $dados = base64_encode($sDados);

                // matricula, dia, situação cadastral, cmd,
                // so ver justificativa e acompanhar
                $justificativa = base64_encode($siape . ":|:" . $pm_partners->dia . ':|:' . $cmd . ':|:nao:|:acompanhar_ve_ponto');

                // indica se há justificativa registrada
                if (($mes_homologado != 'HOMOLOGADO') && ($_SESSION['sMatricula'] === $siape) && $_SESSION['registrar_justificativa'] == true)
                {
                    $justificativa_value = ($pm_partners->just == "" ? "" : "<img src='" . _DIR_IMAGEM_ . "arrow.gif' width='5' height='7' border='0' alt=''>&nbsp;") . "<a id='dia" . substr($pm_partners->dia, 0, 2) . "' href='regjust.php?dados=" . $dados . "'>" . $pm_partners_oco . "</a>";
                }
                else if (($pm_partners->just != ""))
                {
                    $texto_de_justificativa = wordwrap(preparaTextArea($pm_partners->just), 70, "\n", true);
                    $justificativa_value    = "<img border= '0' src='imagem/arrow.gif' width='7' height='7' align='absmiddle'>";
                    $justificativa_value    .= "&nbsp;&nbsp;<a href=\"javascript:verJustificativa('" . wordwrap(preparaTextArea($pm_partners->just), 70, "\\n", true) . "');\" title=\"" . $pm_partners->dcod . "\n" . $texto_de_justificativa . "\" alt=\"" . $pm_partners->dcod . "\n" . $texto_de_justificativa . "\" style='color: #404040;'>" . $pm_partners_oco . "</a>";
                }
                else
                {
                    $justificativa_value = "<div title=\"" . $pm_partners->dcod . "\" alt=\"" . $pm_partners->dcod . "\" style='color: #404040;'><img border= '0' src='imagem/ativar_off.gif' width='7' height='7' align='absmiddle'>&nbsp;&nbsp;" . $codigo_da_ocorrencia . "</div>";
                }


                ## Ação: ALTERAR
                #
                // matricula, nome, dia, ocorrência, lotação,
                // identificacao do registrador, cmd, jornada,
                // so ver justificativa, situação cadastral e acompanhar registros
                $frequencia_alterar = base64_encode($mat
                    . ':|:' . $sNome
                    . ':|:' . $pm_partners->dia
                    . ':|:' . $pm_partners->oco
                    . ':|:' . $lotacao
                    . ':|:' . $pm_partners->idreg
                    . ':|:2:|:' . $jnd
                    . ':|:' . $cod_sitcad
                    . ':|:acompanhar_ve_ponto');

                $acao_alterar = "<a href=\"javascript:window.location.replace('frequencia_alterar.php?dados=" . $frequencia_alterar . "');\" style='color: #404040;'>Alterar</a>";


                ## Ação: ABONAR
                #
                if (in_array($pm_partners->oco, $grupoOcorrenciasPassiveisDeAbono))
                {
                    $acao_abonar = "<a href=\"javascript:window.location.replace('frequencia_justificativa_abono.php?dados=" . $justificativa . "');\" style='color: #404040;'>Abonar</a>";
                }
                else
                {
                    $acao_abonar = "<a href='javascript:return false;' style='cursor: none; text-decoration: none; color: #f8f8f8;' disabled>Abonar</a>";
                }


                ## Ação: EXCLUIR
                #
                if (strtr($dia_nao_util[$xdia][2], array('<font color=red><b>' => '', ' </b></font>' => '')) != '')
                {
                    $acao_excluir = "<a href=\"javascript:window.location.replace('frequencia_excluir.php?dados=" . base64_encode($mat . ":|:" . $pm_partners->dia . ':|:acompanhar_ve_ponto') . "');\" style='color: #404040;'>Excluir</a>";
                }
                else
                {
                    $acao_excluir = "<a href='javascript:void(0);' style='cursor: none; text-decoration: none; color: #f8f8f8;' disabled>Excluir</a>";
                }

                $registrado_por = define_quem_registrou_descricao($pm_partners, $situacao_cadastral, $comp_invertida);

                if ($pm_partners->justchef != "")
                {
                    $dados          = base64_encode($siape . ":|:" . $nome . ":|:" . $pm_partners->dia . ":|:" . utf8_iso88591(str_replace('"', '', $pm_partners->justchef)) . ":|:" . $pm_partners->oco . ":|:" . $cmd . ":|:sim");
                    $registrado_por = "<img src='" . _DIR_IMAGEM_ . "arrow.gif' width='5' height='7' border='0' alt=''>&nbsp;<a href= 'regjustab.php?dados=" . $dados . "'>" . $registrado_por . "</a>";
                }

                $registrosComparecimentoOcorrencia[] = array(
                    'background'           => $background,
                    'color'                => $color,
                    'dia-title'            => $dia_nao_util[$xdia][4],
                    'dia-value'            => trim($dia_nao_util[$xdia][2]) . '&nbsp;' . trim($xdia . $dia_nao_util[$xdia][3]),
                    'pm_partners'          => $pm_partners,
                    'saldo'                => $sinal . ' ' . $font_i_color . $pm_partners->jorndif . $font_f_color,
                    'ocorrencia-title'     => $pm_partners->dcod . "\n" . $pm_partners->just,
                    'ocorrencia-value'     => $justificativa_value,
                    'justificativa-value'  => $justificativa_value,
                    'acao-alterar'         => $acao_alterar,
                    'acao-abonar'          => $acao_abonar,
                    'acao-excluir'         => $acao_excluir,
                    'codigo_da_ocorrencia' => $codigo_da_ocorrencia
                );
            } // fim do while
        }

        return $registrosComparecimentoOcorrencia;
    }


    public function DocAbre()
    {
        ?>
        <div class="container margin-80">
        <?php
    }

    public function DocFecha()
    {
        ?>
        </div>
        <footer class="footer navbar-fixed-bottom">
            <div class="container">
                <p class="text-muted">Sistema de Registro Eletrônico de Frequência | <?=  _SISTEMA_TITULO_ORGAO_; ?></p>
            </div>
        </footer>
        <?php
    }

    public function DocTitulo($value=NULL)
    {
        $titulo = (is_null($value) ? "Registro de Comparecimento" : $value);

        ?>
        <div class="row">
            <div class="col-md-12 subtitle">
                <h4 class="lettering-tittle uppercase"><strong><?= tratarHTML($titulo); ?></strong></h4>
            </div>
        </div>
        <?php
    }

    public function DocIdentificacao()
    {
        switch ($this->objCadastro->status)
        {
            case 'S':
            case 'V':
                $status = "<b>HOMOLOGADO</b>";
                break;

            case 'N':
            default:
                $status = "<font color=red><b>NÃO HOMOLOGADO</b></font>";
                break;

        }

        ?>
        <!-- Row Referente aos dados dos funcionários  -->
        <div class="row margin-10">
            <div class="col-md-12" id="dados-funcionario">
                <div class="col-md-3">
                    <h5><strong>SIAPE</strong></h5>
                    <p><?= tratarHTML($this->objCadastro->siape); ?></p>
                </div>
                <div class="col-md-6">
                    <h5><strong>NOME</strong></h5>
                    <p><?= tratarHTML($this->objCadastro->nome); ?></p>
                </div>
                <div class="col-md-3">
                    <h5><strong>SITUAÇÃO</strong></h5>
                    
                    <?php if ($this->objCadastro->status == 'S' || $this->objCadastro->status == 'V'): ?>

                        <p style="font-weight:bold;"><strong><?= tratarHTML($status); ?></strong></p>
                        
                    <?php else: ?>

                        <p style="font-weight:bold;color:red;"><strong><?= tratarHTML($status); ?></strong></p>
                        
                    <?php endif; ?>
                        
                </div>
            </div>
        </div>
        <?php
    }

    public function DocUnidade()
    {
        ?>
        <!-- Row Referente aos dados Setor do funcionario  -->
        <div class="row margin-10 comparecimento">
            <div class="col-md-12" id="dados-funcionario">
                <div class="col-md-3">
                    <h5><strong>ÓRGÃO</strong></h5>
                    <p><?= tratarHTML(getOrgaoMaisSigla($this->objCadastro->setor)); ?></p>
                </div>
                <div class="col-md-6">
                    <h5><strong>LOTAÇÃO</strong></h5>
                    <p><?= tratarHTML(getUorgMaisDescricao($this->objCadastro->setor)); ?></p>
                </div>
                <div class="col-md-3">
                    <h5><strong>ADMISSAO</strong></h5>
                    <p><?= tratarHTML($this->objCadastro->dt_adm); ?></p>
                </div>
            </div>
        </div>
        <?php
    }
    public function DocHorariosDefinidos()
    {
        ?>
        <div class="row margin-10">
            <!-- Row Referente aos dados de horário de trabalho do funcionario  -->
            <table class="table table-striped table-condensed table-bordered text-center">
                <thead>
                    <tr>
                        <th class="text-center text-nowrap col-md-2" style="vertical-align:middle;" rowspan='2'>HORÁRIO DO SETOR</th>
                        <th class="text-center text-nowrap" style="vertical-align:middle;" colspan='4'>HORÁRIO DO SERVIDOR</th>
                    </tr>
                    <tr>
                        <th class="text-center text-nowrap col-md-2" style="vertical-align:middle;">ENTRADA</th>
                        <th class="text-center text-nowrap col-md-2" style="vertical-align:middle;">INÍCIO DO ALMOÇO</th>
                        <th class="text-center text-nowrap col-md-2" style="vertical-align:middle;">FIM DO ALMOÇO</th>
                        <th class="text-center text-nowrap col-md-2" style="vertical-align:middle;">SAÍDA</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">
                            <?= $this->objCadastro->inicio_atend; ?>
                            às
                            <?= $this->objCadastro->fim_atend; ?>
                        </td>
                        <td class="text-center"><?= tratarHTML($this->objJornada->entra_trab); ?></td>
                        <td class="text-center"><?= tratarHTML($this->objJornada->ini_interv); ?></td>
                        <td class="text-center"><?= tratarHTML($this->objJornada->sai_interv); ?></td>
                        <td class="text-center"><?= tratarHTML($this->objJornada->sai_trab); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function DocRegistros($colunas = 9)
    {
        // registros da frequencia - comparecimento
        $registrosComparecimentoOcorrencia = $this->RegistrosDeFrequencia([
            'siape' => $this->getSiape(),
            'mes'   => $this->getMes(),
            'ano'   => $this->getAno(),
            'cmd'   => $cmd
        ]);

        ?>
        <table class="table table-striped table-bordered text-center table-hover">
            <thead>
                <tr>
                    <th class="text-center" colspan="<?= $colunas; ?>"><h4><b><?= $this->getMes() . '/' . $this->getAno(); ?></b></h4></th>
                </tr>
                <tr>
                    <?php $this->ImprimirTituloDasColunas($subView=false, $basename, $sAutorizadoTE); ?>
                </tr>
            </thead>
            <tbody>
                <?php

                if (is_null($registrosComparecimentoOcorrencia))
                {
                    ?>
                    <tr>
                        <td colspan="<?= tratarHTML($colunas); ?>"><?= 'Sem registros para exibir!'; ?></td>
                    </tr>
                    <?php
                }
                else
                {
                    foreach ($registrosComparecimentoOcorrencia as $rco)
                    {
                        $oDBase = DadosFrequenciaAuxiliar($siape, $rco['pm_partners']->dia);

                        $this->ImprimirDadosFrequencia($rco, $pagina_basename, !is_null($oDBase));

                        if (!is_null($oDBase))
                        {
                            $this->ImprimirDetalhesDoDia($rco, $oDBase, $pagina_basename, $sAutorizadoTE);
                        }
                    }
                }

                ?>
            </tbody>
        </table>
        <div class="row" style="position:relative;top:-20px;left:15px;">
            <p><strong>D: </strong>Domingo    <strong>S: </strong> Sábado    <strong>F: </strong> Feriado/Facultativo (Posicione o mouse sobre o dia para ver a descrição)</p>
        </div>
        <?php
    }


    /**
     *  @info Cálculo de saldos de horas comuns
     *
     *  @param integer $colunas Números de colunas da tabela
     *  @return HTML
     *
     * @author Edinalvo Rosa
     */
    public function DocSaldosBancoDeHoras()
    {
        $print_saldo_banco_de_horas = "";

        print $print_saldo_banco_de_horas;
    }


    ##------------------------------------------------------------------------\
    #  CALCULO DE HORAS COMUNS                                                |
    #                                                                         |
    #  Atribui o código html resultante a uma variavel "$html"                |
    #  se o valor de "$bExibeResultados" for igual a "true"                   |
    ##------------------------------------------------------------------------/
    public function DocSaldos($colunas = 9)
    {
        $bSoSaldo         = true;
        $bParcial         = ($this->objCadastro->status != 'S'
                             && $this->objCadastro->status != 'V');
        $bImprimir        = false;
        $bExibeResultados = false;
        $relatorioTipo    = '0';
        $tipo             = 0;

        //
        // $siape : definido no início do script
        // $mes    : definido no início do script
        // $ano    : definido no início do script
        // $mes2   : definido no início do script
        // $ano2   : definido no início do script
        $pSiape = $this->getSiape();
        $mes    = $this->getMes();
        $ano    = $this->getAno();

        include_once( "veponto_saldos.php" );

        $dados = array(
            'siape'   => $sSiape,
            'mes_fim' => $mesFim,
            'ano_fim' => $anoFim,
            'horas'   => $aHorasComuns,
        );

        $print_saldo_horas = str_replace("<a id='show-saldos' style='cursor: hand;'><u>Clique aqui para visualizar todos os meses</u></a>", "", imprimirSaldoCompensacaoDoMes($extrato = false, $dados));
        print $print_saldo_horas;
    }

    public function ImprimirTituloDasColunas($subView=false, $basename='',$sAutorizadoTE='')
    {
        if ($subView == false)
        {
            ?>
            <th class="text-center" style="vertical-align:middle;">Dia</th>
            <th class="text-center" style="vertical-align:middle;">Entrada</th>
            <th class="text-center" style="vertical-align:middle;">Ida Intervalo</th>
            <th class="text-center" style="vertical-align:middle;">Voltar Intervalo</th>
            <th class="text-center" style="vertical-align:middle;">Saída</th>
            <th class="text-center" style="vertical-align:middle;">Resultado</th>
            <th class="text-center" style="vertical-align:middle;">
                <?= ($sAutorizadoTE == 'S' ? 'Turno Previsto' : 'Jornada prevista'); ?>
            </th>
            <th class="text-center" style="vertical-align:middle;">Saldo do Dia</th>
            <th class="text-center" style="vertical-align:middle;">Ocorrência</th>
            <?php

            if ($basename == 'gestao_veponto.php')
            {
                ?>
                <th class="text-center" style="vertical-align:middle;">Registrado Por</th>
                <?php
            }
        }
        else
        {
            ?>
            <th class="text-center" style="vertical-align:middle;width:80px;">Entrada</th>
            <th class="text-center" style="vertical-align:middle;width:120px;">Ida Intervalo</th>
            <th class="text-center" style="vertical-align:middle;width:143px;">Voltar Intervalo</th>
            <th class="text-center" style="vertical-align:middle;width:83px;">Saída</th>
            <th class="text-center" style="vertical-align:middle;width:98px;">Resultado</th>
            <th class="text-center" style="vertical-align:middle;">
                <?= ($sAutorizadoTE == 'S' ? 'Turno Previsto' : 'Jornada prevista'); ?>
            </th>
            <th class="text-center" style="vertical-align:middle;width:115px;">Saldo do Dia</th>
            <th class="text-center" style="vertical-align:middle;width:105px;">Ocorrência</th>
            <?php
        }
    }

    public function ImprimirDadosFrequencia($rco, $basename='', $detalhes=false)
    {
        if ($detalhes === false)
        {
            ?>
            <tr>
                <td class="text-nowrap" style="<?= $rco['color']; ?>" title="<?= $rco['dia-title']; ?>"><?= $rco['dia-value']; ?></td>
                <td style="<?= $rco['color']; ?>"><?= tratarHTML($rco['pm_partners']->entra); ?></td>
                <td style="<?= $rco['color']; ?>"><?= tratarHTML($rco['pm_partners']->intini); ?></td>
                <td style="<?= $rco['color']; ?>"><?= tratarHTML($rco['pm_partners']->intsai); ?></td>
                <td style="<?= $rco['color']; ?>"><?= tratarHTML($rco['pm_partners']->sai); ?></td>
                <td style="<?= $rco['color']; ?>"><?= tratarHTML($rco['pm_partners']->jornd); ?></td>
                <td style="<?= $rco['color']; ?>"><?= tratarHTML($rco['pm_partners']->jornp); ?></td>
                <td style="<?= $rco['color']; ?>"><?= $rco['saldo']; ?></td>
                <td style="<?= $rco['color']; ?>" title="<?= tratarHTML($rco['ocorrencia-title']); ?>"><?= $rco['ocorrencia-value']; ?></td>
                <?php

                if ($basename == 'gestao_veponto.php')
                {
                    ?>
                    <td class="text-center"><?= define_quem_registrou_descricao($rco['pm_partners']); ?></td>
                    <?php
                }

                ?>
            </tr>
            <?php
        }
        else
        {
            ?>
            <tr>
                <td class="text-nowrap" style="<?= $rco['color']; ?>" title="<?= $rco['dia-title']; ?>" title=""
                    data-toggle="collapse"
                    data-target="#collapse<?= inverteData($rco['pm_partners']->dia); ?>">
                    <a href="#." style="text-decoration:underline;">
                        <span id="collapse<?= inverteData($rco['pm_partners']->dia); ?>span" class="glyphicon glyphicon-plus"></span>
                    </a>&nbsp;&nbsp;<?= $rco['dia-value']; ?>
                </td>
                <td style="<?= $rco['color']; ?>"><?= tratarHTML($rco['pm_partners']->entra); ?></td>
                <td style="<?= $rco['color']; ?>"><?= tratarHTML($rco['pm_partners']->intini); ?></td>
                <td style="<?= $rco['color']; ?>"><?= tratarHTML($rco['pm_partners']->intsai); ?></td>
                <td style="<?= $rco['color']; ?>"><?= tratarHTML($rco['pm_partners']->sai); ?></td>
                <td style="<?= $rco['color']; ?>"><?= tratarHTML($rco['pm_partners']->jornd); ?></td>
                <td style="<?= $rco['color']; ?>"><?= tratarHTML($rco['pm_partners']->jornp); ?></td>
                <td style="<?= $rco['color']; ?>"><?= $rco['saldo']; ?></td>
                <td style="<?= $rco['color']; ?>" title="<?= tratarHTML($rco['ocorrencia-title']); ?>"><?= tratarHTML($rco['ocorrencia-value']); ?></td>
                <?php

                if ($basename == 'gestao_veponto.php')
                {
                    ?>
                    <td class="text-center"><?= define_quem_registrou_descricao($rco['pm_partners']); ?></td>
                    <?php
                }

                ?>
            </tr>

            <?php
        }
    }

    public function ImprimirDetalhesDoDia($rco, $oDBase, $basename='',$sAutorizadoTE='')
    {
        $dia = inverteData( $rco['pm_partners']->dia );

        ?>
        <tr style='padding:0px;margin:0px;border-collapse: collapse;'>
            <td style='padding:0px;margin:0px;border-collapse:collapse;text-align:right;'></td>
            <td style='padding:0px;margin:0px;border-collapse:collapse;text-align:right;' colspan="8">

                <table id="collapse<?= tratarHTML($dia); ?>" class="table table-striped table-bordered text-center collapse out" style='width:100%;margin-top:5px;margin-left:0px;'>
                    <thead>
                        <tr>
                            <?php $this->ImprimirTituloDasColunas($subView=true, $basename, $sAutorizadoTE); ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="<?= $rco['color']; ?>"><?= tratarHTML($rco['pm_partners']->entra); ?></td>
                            <td style="<?= $rco['color']; ?>"><?= tratarHTML($rco['pm_partners']->intini); ?></td>
                            <td style="<?= $rco['color']; ?>"><?= tratarHTML($rco['pm_partners']->intsai); ?></td>
                            <td style="<?= $rco['color']; ?>"><?= tratarHTML($rco['pm_partners']->sai); ?></td>
                            <td style="<?= $rco['color']; ?>"><?= tratarHTML($rco['pm_partners']->jornd); ?></td>
                            <td style="<?= $rco['color']; ?>"><?= tratarHTML($rco['pm_partners']->jornp); ?></td>
                            <td style="<?= $rco['color']; ?>"><?= $rco['saldo']; ?></td>
                            <td style="<?= $rco['color']; ?>" title="<?= tratarHTML($rco['ocorrencia-title']); ?>"><?= tratarHTML($rco['ocorrencia-value']); ?></td>
                        </tr>
                        <?php

                        while ($rows = $oDBase->fetch_object())
                        {
                            ?>
                            <tr>
                                <td style=""><?= tratarHTML($rows->entra);   ?></td>
                                <td style=""><?= tratarHTML($rows->intini);  ?></td>
                                <td style=""><?= tratarHTML($rows->intsai);  ?></td>
                                <td style=""><?= tratarHTML($rows->sai);     ?></td>
                                <td style=""><?= tratarHTML($rows->jornd);   ?></td>
                                <td style=""><?= tratarHTML($rows->jornp);   ?></td>
                                <td style=""><?= tratarHTML($rows->jorndif); ?></td>
                                <td style="" title="<?= tratarHTML($rows->dcod); ?>"><?= tratarHTML($rows->oco); ?></td>
                            </tr>
                            <?php
                        }

                        ?>
                    </tbody>
                </table>

            </td>
        </tr>
        <?php
    }
}
