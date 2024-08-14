<?php
/**
 * Carrega dados do servidor e exibe a frequência do mês
 *  Cadastro;
 *  Frequência;
 *  Unidade
 *
 * @version
 * @author Edinalvo Rosa
 */
include_once( "config.php" );
include_once( "class_ocorrencias_grupos.php" );
include_once( "src/controllers/TabBancoDeHorasAcumulosController.php" );

## obtem dados do setor no histórico de jornada
#
$oUORG = DadosHistoricoJornada($siape, $mes, $ano);

if (is_null($oUORG))
{
    ## obtem dados do setor no histórico
    #
    $oUORG = DadosHistoricoLotacao($siape, $mes, $ano);
}

$lot           = $oUORG->cod_lot;
$lot_descricao = $oUORG->descricao;
$orgao_sigla   = $oUORG->sigla;

$inicio_atendimento = $oUORG->inicio_atend;
$fim_atendimento    = $oUORG->fim_atend;

$entra_trab = $oUORG->entra_trab;
$ini_interv = $oUORG->ini_interv;
$sai_interv = $oUORG->sai_interv;
$sai_trab   = $oUORG->sai_trab;

## verifica permissão de acesso e UPAG
#
$bRecursosHumanos   = ($_SESSION['sRH'] == "S");
$bRecursosHumanosSR = ($_SESSION['sRH'] == "S" && substr($_SESSION['sLotacao'], 2, 6) == '150000');
$bDiretoria         = ($_SESSION["sCAD"] == "S");
$bGestoresSISREF    = ($_SESSION["sSenhaI"] == "S");
$bAuditoria         = ($_SESSION['sAudCons'] == 'S' || $_SESSION["sLog"] == "S");
$bSuperintendente   = ($_SESSION['sAPS'] == 'S' && substr($_SESSION['sLotacao'], 2, 6) == '150000');
$bGerenteExecutivo  = ($_SESSION['sAPS'] == 'S' && substr($_SESSION['sLotacao'], 2, 1) == '0' && substr($_SESSION['sLotacao'], 5, 3) == '000');

$pagina_basename    = $_SESSION['sPaginaDeRetorno1'];
$pagina_retorno     = $_SESSION['sPaginaDeRetorno1'];


if ($bDiretoria == true || $bGestoresSISREF == true || $bAuditoria == true || $bSuperintendente == true || $bGerenteExecutivo == true)
{
    // continua
}
else if ($_SESSION['sAPS'] == "S" && $_SESSION['sRH'] == "N" && $lot != $_SESSION['sLotacao'] && $chefe == "N" && $magico < '3')
{
    mensagem("Não é permitido consultar servidor/estagiário de outro setor!", $_SESSION['HTTP_REFERER']);
}
else if ($_SESSION['sRH'] == "S" && $upg != $_SESSION['upag'])
{
    mensagem("Não é permitido consultar frequência de servidor/estagiário de outra UPAG!", $_SESSION['HTTP_REFERER']);
}

// define quantas colunas tem a tabela
if ($basename[0] == 'gestao_veponto.php')
{
    $colunas = '10';
}
else
{
    $colunas = '9';
}

$nome          = "";
$data_admissao = "";

//obtem dados da homologação
$status = verifica_se_mes_homologado($siape, $ano . $mes);

$oDados = DadosCadastro($siape);

if (!is_null($oDados))
{
    if ($oDados->flag == TRUE)
    {
        $nome = $oDados->nomesocial;
    }
    
    if (empty($nome))
    {
        $nome = $oDados->nome;
    }
    
    $data_admissao = $oDados->dt_adm;
}

// informa se deve exibir o cabeçalho da página.
// para uso quando exibir a folha de frequência em janela modal
if ( !isset($sem_header) )
{
    $sem_header = false;
}

// historico de navegacao (substituir o $_SESSION['sPaginaRetorno_sucesso']
$sessao_navegacao = new Control_Navegacao();

// registros da frequencia - comparecimento
$registrosComparecimentoOcorrencia = RegistrosDeFrequencia($siape, $mes, $ano, $cmd);

## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setSubTitulo("Registro de Comparecimento");

// Topo do formulário
//
$oForm->exibeTopoHTML( $sem_header );
$oForm->exibeCorpoTopoHTML();

?>
<script>
    function verJustificativa(texto)
    {
        $('#modalBody').text(texto);
        $('#myModal').modal('show');
    }
</script>

<div class="container">
    <!-- Row Referente aos dados dos funcionários  -->
    <div class="row margin-10">
        <div class="col-md-12" id="dados-funcionario">
            <div class="col-md-3">
                <h5><strong>SIAPE</strong></h5>
                <p><?= tratarHTML(removeOrgaoMatricula($siape)); ?></p>
            </div>
            <div class="col-md-6">
                <h5><strong>NOME</strong></h5>
                <p><?= tratarHTML($nome); ?></p>
            </div>
            <div class="col-md-3">
                <h5><strong>SITUAÇÃO</strong></h5>
                <p><strong><?= tratarHTML($status); ?></strong></p>
            </div>
        </div>
    </div>
    <!-- Row Referente aos dados Setor do funcionario  -->
    <div class="row margin-10 comparecimento">
        <div class="col-md-12" id="dados-funcionario">
            <div class="col-md-3">
                <h5><strong>ÓRGÃO</strong></h5>
                <p><?= tratarHTML(getOrgaoMaisSigla( $lot )); ?></p>
            </div>
            <div class="col-md-6">
                <h5><strong>UNIDADE</strong></h5>
                <p><?= tratarHTML(getUorgMaisDescricao( $lot )); ?></p>
            </div>
            <div class="col-md-3">
                <h5><strong>ADMISSÃO</strong></h5>
                <p><?= tratarHTML($data_admissao); ?></p>
            </div>
        </div>
    </div>

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
                    <td class="text-center"><?= tratarHTML($inicio_atendimento); ?> às <?= tratarHTML($fim_atendimento); ?></td>
                    <td class="text-center"><?= tratarHTML($entra_trab); ?></td>
                    <td class="text-center"><?= tratarHTML($ini_interv); ?></td>
                    <td class="text-center"><?= tratarHTML($sai_interv); ?></td>
                    <td class="text-center"><?= tratarHTML($sai_trab); ?></td>
                </tr>
            </tbody>
        </table>
        <?php


        if (empty($pagina_basename))
        {
            $pagina_basename = $basename[0];
        }

        if ($basename[0] != 'entrada1.php' && !empty($basename[0]))
        {
            if (empty($pagina_retorno))
            {
                $pagina_retorno = ($basename[0] == 'entrada8.php' ? $sessao_navegacao->getPaginaAnterior() : $_SESSION['sHOrigem_1']);
            }

            ?>
            <div class="form-group text-center">
                <div class="col-md-5">&nbsp;</div>
                <div class="col-md-2">
                    <a class="btn btn-danger btn-block" href='javascript:window.location.replace("<?= tratarHTML($pagina_retorno); ?>");' role="button">
                        <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                    </a>
                </div>
                <div class="col-md-5">&nbsp;</div>
            </div>
            <?php

        }

        ?>

        <table class="table table-striped table-bordered text-center table-hover">
            <thead>
                <tr>
                    <th class="text-center" colspan="<?= tratarHTML($colunas); ?>"><h4><b><?= tratarHTML($mes) . '/' . tratarHTML($ano); ?></b></h4></th>
                </tr>
                <tr>
                    <?php ImprimirTituloDasColunas($subView=false, $pagina_basename, $sAutorizadoTE); ?>
                </tr>
            </thead>
            <tbody>
                <?php
                if (is_null($registrosComparecimentoOcorrencia))
                {
                    ?>
                    <tr>
                        <td colspan="<?=tratarHTML($colunas); ?>"><?= 'Sem registros para exibir!'; ?></td>
                    </tr>
                    <?php
                }
                else
                {
                    foreach ($registrosComparecimentoOcorrencia as $rco)
                    {
                        $oDBase = DadosFrequenciaAuxiliar($siape, $rco['pm_partners']->dia);

                        ImprimirDadosFrequencia($rco, $pagina_basename, !is_null($oDBase));

                        if (!is_null($oDBase))
                        {
                            ImprimirDetalhesDoDia($rco, $oDBase, $pagina_basename, $sAutorizadoTE);
                        }
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    <div class="row margin-5">
        <p><strong>D: </strong>Domingo    <strong>S: </strong> Sábado    <strong>F: </strong> Feriado/Facultativo (posicione o mouse sobre o dia para ver a descrição)</p>
    </div>
    <?php

    
    ##------------------------------------------------------------------------\
    #  BANCO DE HORAS - SALDO                                                 |
    ##------------------------------------------------------------------------/
    #
    
    $objTabBancoDeHorasAcumulos = new TabBancoDeHorasAcumulosController();
    $objTabBancoDeHorasAcumulos->showQuadroDeSaldo( $siape, null, $ano );

    ?>
    <div class="row margin-10">

        <?php
        ##------------------------------------------------------------------------\
        #  CALCULO DE HORAS COMUNS                                                |
        #                                                                         |
        #  Atribui o código html resultante a uma variavel "$html"                |
        #  se o valor de "$bExibeResultados" for igual a "true"                   |
        ##------------------------------------------------------------------------/
        #
        $bSoSaldo         = true;
        $bParcial         = ($status == 'HOMOLOGADO' ? false : true);
        $bImprimir        = false;
        $bExibeResultados = false;
        $relatorioTipo    = '0';
        //$mes2 = date('m');
        //$ano2 = date('Y');
        $tipo             = 0;

        //
        // $siape : definido no início do script
        // $mes    : definido no início do script
        // $ano    : definido no início do script
        // $mes2   : definido no início do script
        // $ano2   : definido no início do script

        include_once( "veponto_saldos.php" );
        $dados = array(
            'siape'   => $sSiape,
            'mes_fim' => $mes,
            'ano_fim' => $ano
        );
        $print_saldo_horas = str_replace("<a id='show-saldos' style='cursor: hand;'><u>Clique aqui para visualizar todos os meses</u></a>", "", imprimirSaldoCompensacaoDoMes(false,$dados));
        print $print_saldo_horas;

        #
        ##------------------------------------------------------------------------\
        #  FIM DO CALCULO DE HORAS COMUNS                                         |
        ##------------------------------------------------------------------------/
        ?>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Justificativa do Servidor</h4>
            </div>
            <div id="modalBody" class="modal-body">
                ...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<p></p>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

/**
 * Verifica os destinatários e executa a função que envia o e-mail
 *
 * @param void
 * @param string      $assunto       Assunto do e-mail
 * @param string      $texto         Corpo do e-mail
 * @param string|null $msg_sucesso   Mensagem quando há sucesso
 * @param string|null $msg_erro      Mensagem em caso de erro
 *
 * @return array
 */
function RegistrosDeFrequencia($siape, $mes, $ano, $cmd)
{
    global $data_admissao;

    // Mês e ano corrente
    $comp_invertida = $ano . $mes;
    $comp           = $mes . $ano;

    $sitcad = NULL;

    $ocorrencias_88888  = 0;
    $ocorrencias_99999  = 0;
    $ocorrencias_tracos = 0;


    // Grupos de ocorrências
    $obj = new OcorrenciasGrupos();
    $codigosCompensaveis              = $obj->GrupoOcorrenciasNegativasDebitos($sitcad, $exige_horarios=true);
    $codigosDebito                    = $obj->CodigosDebito($sitcad);
    $codigosCredito                   = $obj->CodigosCredito($sitcad,$temp=true);
    $codigosTrocaObrigatoria          = $obj->CodigosTrocaObrigatoria($sitcad);
    $grupoOcorrenciasPassiveisDeAbono = $obj->GrupoOcorrenciasPassiveisDeAbono($sitcad);

    $codigoRegistroParcialPadrao      = $obj->CodigoRegistroParcialPadrao($sitcad);
    $codigoSemFrequenciaPadrao        = $obj->CodigoSemFrequenciaPadrao($sitcad);

    $siape = getNovaMatriculaBySiape($siape);

    $status_homologacao = verifica_se_mes_homologado($siape, $ano . $mes);

    $registrosComparecimentoOcorrencia = array();

    $oDBase = DadosFrequencia($siape, $mes, $ano);

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

            if ($pm_partners->oco == $codigosTrocaObrigatoria[0]) // '-----'
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
                $sinal        = "<font color='red'> - </font>";
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
                $justificativa_value = "<div title=\"" . $pm_partners->dcod . "\" alt=\"" . $pm_partners->dcod . "\" style='color: #404040;'><img border= '0' src='imagem/transp1x1.gif' width='7' height='7' align='absmiddle'>&nbsp;&nbsp;" . $codigo_da_ocorrencia . "</div>";
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

/**
 * Dados historico de lotação
 *
 * @param object      $oDBase Instancia do banco
 * @param string      $siape  Matrícula do servidor
 * @param string      $mes    Mês corrente
 * @param string      $ano    Ano corrente
 *
 * @return object/null
 */
function DadosHistoricoLotacao($siape, $mes, $ano)
{
    ## instância a base de dados
    #
    $oDBase = new DataBase('PDO');

    $oDBase->setMensagem("Erro no acesso a tabela Histórico de Lotação/Setor (1)");
    $oDBase->query("
    SELECT
        a.cod_lot, b.descricao, b.cod_uorg, b.uorg_pai, b.upag, a.dt_ing_lot,
        a.dt_sai_lot, c.denominacao, c.sigla, b.inicio_atend, b.fim_atend
    FROM
        histlot AS a
    LEFT JOIN
        tabsetor AS b ON a.cod_lot = b.codigo
    LEFT JOIN
        taborgao AS c ON LEFT(b.codigo,5) = c.codigo
    WHERE
        a.siape= :siape
        AND  (DATE_FORMAT(a.dt_ing_lot,'%Y%m') >= :comp OR :comp >= DATE_FORMAT(a.dt_ing_lot,'%Y%m'))
        AND DATE_FORMAT(a.dt_sai_lot,'%Y%m') = '000000'
        AND NOT ISNULL(b.descricao)
    ORDER BY
        a.dt_ing_lot DESC
    ", array(
        array(":siape", $siape, PDO::PARAM_STR),
        array(":comp", $ano . $mes, PDO::PARAM_STR),
    ));

    if ($oDBase->num_rows() > 0)
    {
        return $oDBase->fetch_object();
    }
    else
    {
        return null;
    }

}

/**
 * Dados historico de jornada
 *
 * @param object      $oDBase Instancia do banco
 * @param string      $siape  Matrícula do servidor
 * @param string      $mes    Mês corrente
 * @param string      $ano    Ano corrente
 *
 * @return object/null
 */
function DadosHistoricoJornada($siape, $mes, $ano)
{
    ## instância a base de dados
    #
    $oDBase = new DataBase('PDO');

    $ultimo_dia_do_mes = $ano . '-' . $mes . '-' . numero_dias_do_mes($mes, $ano);


    $siape = getNovaMatriculaBySiape($siape);

    $oDBase->setMensagem("Erro no acesso a tabela Histórico de Jornada (2)");
    $oDBase->query("
    SELECT
        jh.cod_lot, b.descricao, b.cod_uorg, b.uorg_pai, b.upag,
        dt_ing_lot, dt_sai_lot, c.denominacao, c.sigla,
        b.inicio_atend, b.fim_atend, jh.entra_trab, jh.ini_interv,
        jh.sai_interv, jh.sai_trab
    FROM
        jornada_historico AS jh
    LEFT JOIN
        histlot AS a ON a.cod_lot = jh.cod_lot
    LEFT JOIN
        tabsetor AS b ON b.codigo = jh.cod_lot
    LEFT JOIN
        taborgao AS c ON LEFT(b.codigo,5) = c.codigo
    WHERE
        jh.siape = :siape
        AND (jh.data_inicio >= :dia OR :dia >= jh.data_inicio)
        AND NOT ISNULL(b.descricao)
    ORDER BY
        jh.data_inicio DESC, jh.data_registro DESC
    LIMIT
        1
    ", array(
        array(':siape', $siape, PDO::PARAM_STR),
        array(":dia", $ultimo_dia_do_mes, PDO::PARAM_STR)
    ));

    if ($oDBase->num_rows() > 0)
    {
        return $oDBase->fetch_object();
    }
    else
    {
        return null;
    }

}

/**
 * Dados cadastro
 *
 * @param object      $oDBase Instancia do banco
 * @param string      $siape  Matrícula do servidor
 * @param string      $mes    Mês corrente
 * @param string      $ano    Ano corrente
 *
 * @return object/null
 */
function DadosCadastro($siape)
{
    ## instância a base de dados
    #
    $oDBase = new DataBase('PDO');

    $siape = getNovaMatriculaBySiape($siape);

    $oDBase->setMensagem('VePonto: Erro no acesso ao banco de dados!\\nPor favor, tente mais tarde.');
    $oDBase->query('
    SELECT
        a.nome_serv                      AS nome, 
        DATE_FORMAT(a.dt_adm,"%d/%m/%Y") AS dt_adm,
        a.nome_social                    AS nomesocial,
        a.flag_nome_social               AS flag
    FROM
        servativ AS a
    WHERE
        a.mat_siape = :siape
    ORDER BY
        a.mat_siape
    ', array(
        array(":siape", $siape, PDO::PARAM_STR)
    ));

    if ($oDBase->num_rows() > 0)
    {
        return $oDBase->fetch_object();
    }
    else
    {
        return null;
    }

}

/**
 * Dados frequência
 *
 * @param object      $oDBase Instancia do banco
 * @param string      $siape  Matrícula do servidor
 * @param string      $mes    Mês corrente
 * @param string      $ano    Ano corrente
 *
 * @return object/null
 */
function DadosFrequencia($siape, $mes, $ano)
{
    ## inclui dias sem frequência
    #
    inserir_dias_sem_frequencia($siape, date('j'), $mes, $ano, $jornada, $sLotacao, $nome_do_arquivo, $pm->dt_adm);

    ## instância a base de dados
    #
    $oDBase = new DataBase('PDO');

    ## DADOS DA FREQUÊNCIA
    #
    $oDBase->query("
    SELECT
        cad.nome_serv AS nome, DATE_FORMAT(cad.dt_adm,'%d/%m/%Y') AS dt_adm,
        DATE_FORMAT(pto.dia,'%d/%m/%Y') AS dia, pto.entra, pto.intini,
        pto.intsai, pto.sai, pto.jornd, pto.jornp, pto.jorndif, pto.oco,
        REPLACE(pto.just,';',':') AS just,
        REPLACE(pto.justchef,';',':') AS justchef, pto.idreg, pto.ip,
        pto.matchef, pto.siaperh, oco.desc_ocorr AS dcod, und.codigo,
        und.inicio_atend, und.fim_atend, und.cod_uorg, und.upag, und.codmun,
        und.descricao, taborgao.denominacao, taborgao.sigla, cad.cod_sitcad
    FROM
        ponto" . $mes . $ano . " AS pto
    LEFT JOIN
        tabocfre AS oco ON pto.oco = oco.siapecad
    LEFT JOIN
        servativ AS cad ON pto.siape = cad.mat_siape
    LEFT JOIN
        tabsetor AS und ON cad.cod_lot = und.codigo
    LEFT JOIN
        taborgao ON LEFT(und.codigo,5) = taborgao.codigo
    WHERE
        pto.siape = :siape
        AND dia <> '0000-00-00'
    ORDER BY
        pto.dia
    ", array(
        array(':siape', $siape, PDO::PARAM_STR),
    ));

    if ($oDBase->num_rows() > 0)
    {
        return $oDBase;
    }
    else
    {
        return null;
    }
}

function ImprimirTituloDasColunas($subView=false, $basename='',$sAutorizadoTE='')
{
    if ($subView == false)
    {
        ?>
        <th class="text-center" style="vertical-align:middle;">Dia</th>
        <th class="text-center" style="vertical-align:middle;">Entrada</th>
        <th class="text-center" style="vertical-align:middle;">Ida Intervalo</th>
        <th class="text-center" style="vertical-align:middle;">Volta Intervalo</th>
        <th class="text-center" style="vertical-align:middle;">Saída</th>
        <th class="text-center" style="vertical-align:middle;">Resultado</th>
        <th class="text-center" style="vertical-align:middle;">
            <?= ($sAutorizadoTE == 'S' ? 'Turno Previsto' : 'Jornada Prevista'); ?>
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
        <th class="text-center" style="vertical-align:middle;width:143px;">Volta Intervalo</th>
        <th class="text-center" style="vertical-align:middle;width:83px;">Saída</th>
        <th class="text-center" style="vertical-align:middle;width:98px;">Resultado</th>
        <th class="text-center" style="vertical-align:middle;">
            <?= ($sAutorizadoTE == 'S' ? 'Turno Previsto' : 'Jornada Prevista'); ?>
        </th>
        <th class="text-center" style="vertical-align:middle;width:115px;">Saldo do Dia</th>
        <th class="text-center" style="vertical-align:middle;width:105px;">Ocorrência</th>
        <?php
    }
}

function ImprimirDadosFrequencia($rco, $basename='', $detalhes=false)
{
    if ($detalhes === false)
    {
        ?>
        <tr>
            <td class="text-nowrap" style="<?= $rco['color']; ?>" title="<?= tratarHTML($rco['dia-title']); ?>"><?= $rco['dia-value']; ?></td>
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
                <td class="text-center"><?= tratarHTML(define_quem_registrou_descricao($rco['pm_partners'])); ?></td>
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
            <td style="<?= $rco['color']; ?>" title="<?= tratarHTML($rco['ocorrencia-title']); ?>"><?= $rco['ocorrencia-value']; ?></td>
            <?php

            if ($basename == 'gestao_veponto.php')
            {
                ?>
                <td class="text-center"><?= tratarHTML(define_quem_registrou_descricao($rco['pm_partners'])); ?></td>
                <?php
            }

            ?>
        </tr>

        <?php
    }
}

function ImprimirDetalhesDoDia($rco, $oDBase, $basename='',$sAutorizadoTE='')
{
    $dia = inverteData( $rco['pm_partners']->dia );

    ?>
    <tr style='padding:0px;margin:0px;border-collapse: collapse;'>
        <td style='padding:0px;margin:0px;border-collapse:collapse;text-align:right;'></td>
        <td style='padding:0px;margin:0px;border-collapse:collapse;text-align:right;' colspan="8">

            <table id="collapse<?= $dia; ?>" class="table table-striped table-bordered text-center collapse out" style='width:100%;margin-top:5px;margin-left:0px;'>
                <thead>
                    <tr>
                        <?php ImprimirTituloDasColunas($subView=true, $basename, $sAutorizadoTE); ?>
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
                        <td style="<?= $rco['color']; ?>" title="<?= tratarHTML($rco['ocorrencia-title']); ?>"><?= $rco['ocorrencia-value']; ?></td>
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
                            <td style="" title="<?= $rows->dcod; ?>"><?= tratarHTML($rows->oco); ?></td>
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
