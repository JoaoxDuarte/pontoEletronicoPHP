<?php

/**
 * Formulário padrão de exibição de frequência.
 * Para manutenção ou só exibição
 */

include_once( "config.php" );
include_once( "src/controllers/TabBancoDeHorasAcumulosController.php" );
    

switch ($acao_manutencao)
{
    case 'homologar': 
        $form_frequencia_manutencao_registros = "frequencia_homologar_entra.php";
        break;

    case 'rh_mes_corrente':
        $form_frequencia_manutencao_registros = "frequencia_rh_mes_corrente.php";
        break;

    case 'rh_mes_homologacao':
        $form_frequencia_manutencao_registros = "frequencia_rh_mes_homologacao.php";
        break;

    case 'historico_manutencao':
        $form_frequencia_manutencao_registros = "historico_frequencia_registros.php";
        $mat         = $siape;
        $comp        = $mes;
        $year        = $ano;
        $competencia = $mes . $ano;
        break;
}

?>

<script>
    $(document).ready(function () {
        $(document).on('click', '#btn-import-afast', function () {
            importAfastamentos("<?= $form_frequencia_manutencao_registros; ?>", "<?= $acao_manutencao; ?>", "<?= $competencia; ?>");
        });
    });

    /**
     * @info Importa os afastamentos
     */
    function importAfastamentos(destino,grupo,compet) {
        var siape  = $("[name='siape']").val();
        var grupo  = (grupo == null ? "" : grupo);
        var compet = (compet == null ? "" : compet);

        showProcessandoAguarde();

        $.get(
            destino,
            "importafastamentos=true"+
            "&siape=" + siape +
            "&grupo=" + grupo +
            "&compet=" + compet,
            function (data) {
                parsed = JSON.parse(data);

                if (parsed.success) {
                    hideProcessandoAguarde();
                    mostraMensagem('Importação realizada com sucesso!', 'success', null, null);
                    setTimeout(function(){ showProcessandoAguarde(); window.location.reload(); }, 3000);
                }
                else
                {
                    hideProcessandoAguarde();
                    mostraMensagem('Não encontrado dados para Importação!', 'success', null, null);
                    setTimeout(function(){ showProcessandoAguarde(); window.location.reload(); }, 3000);
                }
            });
    }

    function verJustificativa(texto)
    {
        $('#modalBody').text(texto);
        $('#myModal').modal('show');
    }
</script>

<div class="container margin-10" id="form-comparecimento">
    <!-- Row Referente aos dados dos funcionários  -->
    <div class="row margin-10">

        <?php //exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

        <div class="col-md-12 subtitle">
            <h6 class="lettering-tittle uppercase"><strong><?= $titulo_pagina; ?></strong></h6>
        </div>
        <div class="col-md-12 margin-bottom-25"></div>

        <div class="col-md-12" id="dados-funcionario">
            <div class="col-md-2">
                <h5><strong>COMPETÊNCIA</strong></h5>
                <p><b><?= tratarHTML($mes) . '/' . tratarHTML($ano); ?></b></p>
            </div>
            <div class="col-md-2">
                <h5><strong>SIAPE</strong></h5>
                <p><?= tratarHTML(removeOrgaoMatricula($mat)); ?></p>
            </div>
            <div class="col-md-8">
                <h5><strong>NOME</strong></h5>
                <p><?= tratarHTML($sNome); ?></p>
            </div>
        </div>
    </div>
    <!-- Row Referente aos dados Setor do funcionario  -->
    <div class="row margin-10 comparecimento">
        <div class="col-md-12" id="dados-funcionario">
            <div class="col-md-2">
                <h5><strong>ÓRGÃO</strong></h5>
                <p><?= tratarHTML(getOrgaoMaisSigla( $lotacao )); ?></p>
            </div>
            <div class="col-md-8">
                <h5><strong>LOTAÇÃO</strong></h5>
                <p><?= tratarHTML(getUorgMaisDescricao( $lotacao )); ?></p>
            </div>
            <div class="col-md-2">
                <h5><strong>ADMISSÃO</strong></h5>
                <p><?= tratarHTML($anomes_admissao); ?></p>
            </div>
        </div>
    </div>

    <div class="row margin-10">

        <form method="post" action="#" id="form1" name="form1" onsubmit="javascript:return false;" >

            <input type="hidden" id="siape"   name="siape"   value="<?= tratarHTML($mat); ?>">
            <input type="hidden" id="lotacao" name="lotacao" value="<?= tratarHTML($lotacao); ?>">

            <input type="hidden" id="mes2"  name="mes2"  value='<?= tratarHTML($comp); ?>'>
            <input type="hidden" id="ano2"  name="ano2"  value='<?= tratarHTML($year); ?>'>
            <input type="hidden" id="siape_responsavel"  name="siape_responsavel"  value='<?= tratarHTML($siape_responsavel); ?>'>
            <input type="hidden" id="dados" name="dados" value='<?= tratarHTML($dadosorigem); ?>'>

            <input type="hidden" id="teste"        name="teste"        value="<?= tratarHTML($ocorrencias_88888); ?>">
            <input type="hidden" id="teste9"       name="teste9"       value="<?= tratarHTML($ocorrencias_99999); ?>">
            <input type="hidden" id="teste_tracos" name="teste_tracos" value="<?= tratarHTML($ocorrencias_tracos); ?>">
            <input type="hidden" id="teste2"       name="teste2"       value="<?= tratarHTML($linhas); ?>">
            <input type="hidden" id="teste3"       name="teste3"       value="<?= tratarHTML($qdias); ?>">

            <input type="hidden" id="codigoRegistroParcialPadrao" name="codigoRegistroParcialPadrao" value="<?= tratarHTML(implode(', ', $codigoRegistroParcialPadrao)); ?>">
            <input type="hidden" id="codigoSemFrequenciaPadrao"   name="codigoSemFrequenciaPadrao"   value="<?= tratarHTML(implode(', ', $codigoSemFrequenciaPadrao)); ?>">
            <input type="hidden" id="codigosTrocaObrigatoria"     name="codigosTrocaObrigatoria"     value="<?= tratarHTML($codigosTrocaObrigatoria[0]); ?>">

            <?php
            if (!empty(strip_tags($avisos_mensagens)))
            {
                ?>
                <div class="alert alert-danger alert-min text-center">
                    <div class="alert-danger"><small><b><?= $avisos_mensagens; ?></b></small></div>
                </div>
                <?php
            }
            ?>

            <!-- Row Referente aos dados de horário de trabalho do funcionario  -->
            <div class="col-md-12">
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
                            <td class="text-center"><?= tratarHTML($horario_do_setor_inicio) . ' às ' . tratarHTML($horario_do_setor_fim); ?></td>
                            <td class="text-center"><?= tratarHTML($entrada_no_servico); ?></td>
                            <td class="text-center"><?= tratarHTML($saida_para_o_almoco); ?></td>
                            <td class="text-center"><?= tratarHTML($volta_do_almoco); ?></td>
                            <td class="text-center"><?= tratarHTML($saida_do_servico); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="col-md-12">

                <table class="table table-striped table-condensed table-bordered table-hover text-center">
                    <thead>
                        <tr>
                            <?php ImprimirTituloDasColunas($subView=false); ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (is_null($registrosComparecimentoOcorrencia))
                        {
                            ?>
                            <tr>
                                <td colspan="<?= $colunas; ?>"><?= 'Sem registros para exibir!'; ?></td>
                            </tr>
                            <?php
                        }
                        else
                        {
                            foreach ($registrosComparecimentoOcorrencia as $rco)
                            {
                                $oDBase = DadosFrequenciaAuxiliar($mat, $rco['pm_partners']->dia);

                                ImprimirDadosFrequencia($rco, !is_null($oDBase));

                                if (!is_null($oDBase))
                                {
                                    ImprimirDetalhesDoDia($rco, $oDBase, $sAutorizadoTE);
                                }
                            }
                        }

                        $mes   = substr($competencia, 0, 2);
                        $ano   = substr($competencia, 2, 4);
                        $qdias = date("t", mktime(0, 0, 0, $mes, 1, $ano));


                        switch ($acao_manutencao)
                        {
                            case 'homologar':
                                $frequencia_alterar_periodo = base64_encode(
                                    tratarHTML($mat) . ':|:' .
                                    tratarHTML($sNome) . ':|:' .
                                    tratarHTML($lot) . ':|:' .
                                    tratarHTML($jnd) . ':|:' .
                                    tratarHTML($cod_sitcad) .
                                    ':|:2:|:homologar_registros');
                                $frequencia_excluir_dias    = base64_encode(
                                    tratarHTML($mat) . ':|:' .
                                    "01/" . tratarHTML($mes) . "/" . tratarHTML($year) .
                                    ':|:2:|:homologar_registros');
                                break;

                            case 'acompanhar':
                                $frequencia_alterar_periodo = base64_encode(
                                    tratarHTML($mat) . ':|:' .
                                    tratarHTML($sNome) . ':|:' .
                                    tratarHTML($lot) . ':|:' .
                                    tratarHTML($jnd) . ':|:' .
                                    tratarHTML($cod_sitcad) .
                                    ':|:2:|:acompanhar_ve_ponto');
                                $frequencia_excluir_dias    = base64_encode(
                                    tratarHTML($mat) . ':|:' .
                                    "01/" . tratarHTML($mes) . "/" . tratarHTML($year) .
                                    ':|:2:|:acompanhar_ve_ponto');
                                break;

                            case 'rh_mes_corrente':
                            case 'rh_mes_homologacao':
                            case 'historico_manutencao':
                                $frequencia_alterar_periodo = base64_encode(
                                    tratarHTML($mat) . ':|:' .
                                    tratarHTML($sNome) . ':|:' .
                                    tratarHTML($lot) . ':|:' .
                                    tratarHTML($jnd) . ':|:' .
                                    tratarHTML($cod_sitcad) . ':|:2:|:' . 
                                    $acao_manutencao . ':|::|:' .
                                    tratarHTML($mes) . ':|:' .
                                    tratarHTML($ano));

                                $frequencia_excluir_dias    = base64_encode(
                                    tratarHTML($mat) . ':|:' .
                                    "01/" . tratarHTML($mes) . "/" . tratarHTML($year) .
                                    ':|:2:|:'
                                    . $acao_manutencao);
                                break;
                        }
                        ?>
                    </tbody>
                </table>

                <div class="col-md-12" style="position:relative;top:-19px;">
                    <div class="col-md-6 text-left text-nowrap">
                        <strong>D: </strong>Domingo&nbsp;&nbsp;
                        <strong>S: </strong>Sábado&nbsp;&nbsp;
                        <strong>F: </strong>Feriado/Facultativo&nbsp;&nbsp;
                        <small>(Posicione o mouse sobre o dia para ver a descrição)</small>
                    </div>
                    <div class="col-md-6 text-right text-nowrap">
                        <strong>Clique na ocorr&ecirc;ncia com <span class='glyphicon glyphicon-play'></span>, para ver a justificativa do servidor</strong>
                    </div>
                </div>


                <?php

                switch ($acao_manutencao)
                {
                    case 'homologar':
                        $_SESSION['mes_inicial'] = date("m",strtotime("-1 month"));
                        $_SESSION['mes_final'] = date("m",strtotime("-1 month"));
                        $_SESSION['ano_inicial'] = date('Y');
                        $_SESSION['ano_final'] = date('Y');

                        $pSiape = $mat;
                        $mes2 = $comp;
                        $ano2 = $year;
                        $mesFim = $mes;
                        $anoFim = $ano;

                        include_once( "html/form-frequencia-manutencao-homologar.php" );
                        break;

                    case 'acompanhar':
                        $_SESSION['mes_inicial'] = date('m');
                        $_SESSION['mes_final'] = date('m');
                        $_SESSION['ano_inicial'] = date('Y');
                        $_SESSION['ano_final'] = date('Y');

                        include_once( "html/form-frequencia-manutencao-acompanhar.php" );
                        break;

                    case 'rh_mes_corrente':
                        $_SESSION['mes_inicial'] = date('m');
                        $_SESSION['mes_final'] = date('m');
                        $_SESSION['ano_inicial'] = date('Y');
                        $_SESSION['ano_final'] = date('Y');

                        $rh_manutencao_do_mes = "frequencia_rh_mes_corrente.php";
                        include_once( "html/form-frequencia-manutencao-rh.php" );
                        break;

                    case 'rh_mes_homologacao':
                        $_SESSION['mes_inicial'] = date("m",strtotime("-1 month"));
                        $_SESSION['mes_final']   = date("m",strtotime("-1 month"));
                        $_SESSION['ano_inicial'] = date('Y');
                        $_SESSION['ano_final']   = date('Y');

                        $rh_manutencao_do_mes = "frequencia_rh_mes_homologacao.php";
                        include_once( "html/form-frequencia-manutencao-rh.php" );
                        break;

                    case 'historico_manutencao':
                        $_SESSION['mes_inicial'] = date('m');
                        $_SESSION['mes_final']   = date('m');
                        $_SESSION['ano_inicial'] = date('Y');
                        $_SESSION['ano_final']   = date('Y');

                        $pagina_de_retorno_voltar = "historico_frequencia.php";
                        include_once( "html/form-frequencia-historico.php" );
                        break;

                }
                ?>

                <div class="col-md-12 margin-bottom-25"></div>

            </div>

        </form>
        
        <!-- ------------------------------
              SALDOS NO BANCO DE HORAS
        ------------------------------- -->
        <?php if ($acao_manutencao !== "historico_manutencao"): ?> 
        <?php   saldosBancoDeHoras($mat); ?>
        <?php endif; ?>


        <!-- ------------------------------
           SALDOS DE HORAS COMUNS NO MES
        ------------------------------- -->
        <div align="center">
            <?php if ($acao_manutencao !== "historico_manutencao"): ?> 
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
                $tipo             = 0;
                $sMatricula       = $mat;

                //
                // $siape : definido no início do script
                // $mes   : definido no início do script
                // $ano   : definido no início do script
                // $mes2  : definido no início do script
                // $ano2  : definido no início do script

                include_once( "veponto_saldos.php" );

                $print_saldo_horas = str_replace("<a id='show-saldos' style='cursor: hand;'><u>Clique aqui para visualizar todos os meses</u></a>", "", imprimirSaldoCompensacaoDoMes());

                print $print_saldo_horas;

                #
                ##------------------------------------------------------------------------\
                #  FIM DO CALCULO DE HORAS COMUNS                                         |
                ##------------------------------------------------------------------------/

                ?>
            <?php endif; ?>
        </div>
    </div>
</div>


<!-- --------------------------------------------------------
   ABRE JANELA MODAL PARA EXIBIR JUSTIFICATIVA DO SERVIDOR
--------------------------------------------------------- -->
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
<?php




/* ***************************************************** *
 *                                                       *
 *                 FUNÇÕES COMPLEMENTARES                *
 *                                                       *
 * ***************************************************** *
 */

/*
 * @param  boolean  $subView  Indica se há multiocorrências
 * @return  resource  $oDBase  Seleção realizada
 *
 * @info  Recupera dados do servidor
 */
function ImprimirTituloDasColunas($subView=false, $sAutorizadoTE='')
{
    if ($subView == false)
    {
        ?>
        <th class="text-center" style="vertical-align:middle;">Dia</th>
        <th class="text-center" style="vertical-align:middle;">Entrada</th>
        <th class="text-center text-nwrap" style="vertical-align:middle;">Ida Intervalo</th>
        <th class="text-center text-wrap" style="vertical-align:middle;">Volta Intervalo</th>
        <th class="text-center" style="vertical-align:middle;">Saida</th>
        <th class="text-center text-wrap" style="vertical-align:middle;">Jornada do Dia</th>
        <th class="text-center text-wrap" style="vertical-align:middle;">Jornada Prevista</th>
        <th class="text-center text-wrap" style="vertical-align:middle;">Resultado do Dia</th>
        <th class="text-center" style="vertical-align:middle;">Ocorr&ecirc;ncia/Ação</th>
        <?php
    }
    else
    {
        ?>
        <th class="text-center" style="vertical-align:middle;">Entrada</th>
        <th class="text-center text-nwrap" style="vertical-align:middle;">Ida Intervalo</th>
        <th class="text-center text-wrap" style="vertical-align:middle;">Volta Intervalo</th>
        <th class="text-center" style="vertical-align:middle;">Saida</th>
        <th class="text-center text-wrap" style="vertical-align:middle;">Jornada do Dia</th>
        <th class="text-center text-wrap" style="vertical-align:middle;">Jornada Prevista</th>
        <th class="text-center text-wrap" style="vertical-align:middle;">Resultado do Dia</th>
        <th class="text-center" style="vertical-align:middle;">Ocorr&ecirc;ncia</th>
        <?php
    }
}

function ImprimirDadosFrequencia($rco, $detalhes=false)
{
    if ($detalhes === false)
    {
        ?>
        <tr>
            <td class="text-nowrap" style="<?= $rco['color']; ?>" title="<?= $rco['dia-title']; ?>"><?= $rco['dia-value']; ?></td>
        <?php
    }
    else
    {
        ?>
        <tr>
            <td class="text-nowrap" style="<?= $rco['color']; ?>" title="<?= $rco['dia-title']; ?>" title=""
                data-toggle="collapse"
                data-target="#collapse<?= tratarHTML(inverteData($rco['pm_partners']->dia)); ?>">
                    <a href="#." style="text-decoration:underline;">
                        <span id="collapse<?= tratarHTML(inverteData($rco['pm_partners']->dia)); ?>span"
                            class="glyphicon glyphicon-plus"></span>
                    </a>&nbsp;&nbsp;<?= $rco['dia-value']; ?>
            </td>
        <?php
    }

    ?>
        <td style="<?= $rco['color']; ?>;width:100px;"><?= tratarHTML($rco['pm_partners']->entra); ?></td>
        <td style="<?= $rco['color']; ?>;width:100px;"><?= tratarHTML($rco['pm_partners']->intini); ?></td>
        <td style="<?= $rco['color']; ?>;width:100px;"><?= tratarHTML($rco['pm_partners']->intsai); ?></td>
        <td style="<?= $rco['color']; ?>;width:100px;"><?= tratarHTML($rco['pm_partners']->sai); ?></td>
        <td style="<?= $rco['color']; ?>"><?= tratarHTML($rco['pm_partners']->jornd); ?></td>
        <td style="<?= $rco['color']; ?>"><?= tratarHTML($rco['pm_partners']->jornp); ?></td>
        <td style="<?= $rco['color']; ?>"><?= $rco['saldo']; ?></td>
        <td style="<?= $rco['color']; ?>" nowrap>
        <div class="col-md-12">
            <div class="col-md-3" style="padding-right:25px">
                <?= $rco['justificativa-value']; ?>
            </div>
            <div class="col-md-3" style="padding-right:25px">
                <?= $rco['acao-alterar']; ?>
            </div>
            <div class="col-md-3" style="padding-right:25px">
                <?= $rco['acao-abonar']; ?>
            </div>
            <div class="col-md-3">
                <?= $rco['acao-excluir']; ?>
            </div>
            </div>
        </td>
    </tr>
    <?php
}

function ImprimirDetalhesDoDia($rco, $oDBase, $sAutorizadoTE='')
{
    $dia = inverteData( $rco['pm_partners']->dia );

    ?>
    <tr style='padding:0px;margin:0px;border-collapse: collapse;'>
        <td style='padding:0px;margin:0px;border-collapse:collapse;text-align:right;'></td>
        <td style='padding:0px;margin:0px;border-collapse:collapse;text-align:right;' colspan="8">

            <table id="collapse<?= $dia; ?>" class="table table-striped table-bordered text-center collapse out" style='width:100%;margin-top:5px;margin-left:0px;'>
                <thead>
                    <tr>
                        <?php ImprimirTituloDasColunas($subView=true, $sAutorizadoTE); ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="<?= $rco['color']; ?>"><?= $rco['pm_partners']->entra; ?></td>
                        <td style="<?= $rco['color']; ?>"><?= $rco['pm_partners']->intini; ?></td>
                        <td style="<?= $rco['color']; ?>"><?= $rco['pm_partners']->intsai; ?></td>
                        <td style="<?= $rco['color']; ?>"><?= $rco['pm_partners']->sai; ?></td>
                        <td style="<?= $rco['color']; ?>"><?= $rco['pm_partners']->jornd; ?></td>
                        <td style="<?= $rco['color']; ?>"><?= $rco['pm_partners']->jornp; ?></td>
                        <td style="<?= $rco['color']; ?>"><?= $rco['saldo']; ?></td>
                        <td style="<?= $rco['color']; ?>" title="<?= $rco['pm_partners']->dcod; ?>"><?= $rco['justificativa-value']; ?></td>
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


/* @info  Saldo de banco de compensação (horas comuns)
 *
 * @param  void
 * @return  string  HTML
 */
function saldosBancoDeCompensacao()
{
    //
}


/* @info  Saldo do banco de horas
 *
 * @param  void
 * @return  string  HTML
 */
function saldosBancoDeHoras($mat, $ciclo_id=null, $ano=null)
{
    ## Alterado para permitir exibir sempre
    ## <!-- BANCO DE HORAS - SALDO -->
    //if (checkServidorHasAutorization($mat))
    //
        $objTabBancoDeHorasAcumulosController = new TabBancoDeHorasAcumulosController();
        $objTabBancoDeHorasAcumulosController->showQuadroDeSaldo( $mat, $ciclo_id, $ano );
    //}
}
