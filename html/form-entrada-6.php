<div class="container margin-50" id="form-comparecimento">
    <!-- Row Referente aos dados dos funcionários  -->
    <div class="row margin-10">

        <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

        <div class="col-md-12 subtitle">
            <h4 class="lettering-tittle uppercase"><strong>Registro de Comparecimento</strong></h4>
        </div>
        <div class="col-md-12" id="dados-funcionario">
            <div class="col-md-3">
                <h5><strong>SIAPE</strong></h5>
                <p><?= tratarHTML(removeOrgaoMatricula( $pSiape )); ?></p>
            </div>
            <div class="col-md-6">
                <h5><strong>NOME</strong></h5>
                <p><?= tratarHTML($nome); ?></p>
            </div>
            <div class="col-md-3">
                <h5><strong>SITUAÇÃO</strong></h5>
                <p style="color:red;"><strong><?= tratarHTML($status); ?></strong></p>
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
                <h5><strong>LOTAÇÃO</strong></h5>
                <p><?= tratarHTML(getUorgMaisDescricao( $lot )); ?></p>
            </div>
            <div class="col-md-3">
                <h5><strong>ADMISSAO</strong></h5>
                <p><?= tratarHTML($anomes_admissao); ?></p>
            </div>
        </div>
    </div>

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
                    <td class="text-center">08:00:00 às 20:00:00</td>
                    <td class="text-center">09:00:00</td>
                    <td class="text-center">12:00:00</td>
                    <td class="text-center">13:00:00</td>
                    <td class="text-center">18:00:00</td>
                </tr>
            </tbody>
        </table>
    </div>


    <div class="row margin-10">
        <table class="table table-striped table-bordered text-center table-hover">
            <thead>
                <tr>
                    <th class="text-center" colspan="9"><h4><b><?= tratarHTML($mes) . "/" . tratarHTML($ano); ?></b></h4></th>
                </tr>
                <tr>
                    <th class="text-center">Dia</th>
                    <th class="text-center">Entrada</th>
                    <th class="text-center">Ida Intervalo</th>
                    <th class="text-center">Voltar Intervalo</th>
                    <th class="text-center">Saída</th>
                    <th class="text-center">Resultado</th>
                    <th class="text-center"><?= ($sAutorizadoTE == 'S' ? 'Turno Previsto' : 'Jornada prevista'); ?></th>
                    <th class="text-center">Saldo do Dia</th>
                    <th class="text-center">Ocorrência</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($registrosComparecimentoOcorrencia as $rco)
                {
                    ?>
                    <tr>
                        <td style="<?= $rco['color']; ?>" title="<?= $rco['dia-title']; ?>"><?= $rco['dia-value']; ?></td>
                        <td style="<?= $rco['color']; ?>"><?= tratarHTML($rco['pm_partners']->entra); ?></td>
                        <td style="<?= $rco['color']; ?>"><?= tratarHTML($rco['pm_partners']->intini); ?></td>
                        <td style="<?= $rco['color']; ?>"><?= tratarHTML($rco['pm_partners']->intsai); ?></td>
                        <td style="<?= $rco['color']; ?>"><?= tratarHTML($rco['pm_partners']->sai); ?></td>
                        <td style="<?= $rco['color']; ?>"><?= tratarHTML($rco['pm_partners']->jornd); ?></td>
                        <td style="<?= $rco['color']; ?>"><?= tratarHTML($rco['pm_partners']->jornp); ?></td>
                        <td style="<?= $rco['color']; ?>"><?= $rco['saldo']; ?></td>
                        <td style="<?= $rco['color']; ?>" title="<?= $rco['ocorrencia-title']; ?>"><?= $rco['ocorrencia-value']; ?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
    <div class="row margin-5">
        <p><strong>D: </strong>Domingo    <strong>S: </strong> Sábado    <strong>F: </strong> Feriado/Facultativo (Posicione o mouse sobre o dia para ver a descrição)</p>
    </div>
    <div class="row margin-10">

        <?php

        ##------------------------------------------------------------------------\
        #  CALCULO DE HORAS COMUNS                                                |
        #                                                                         |
        #  Atribui o código html resultante a uma variavel "$html"                |
        #  se o valor de "$bExibeResultados" for igual a "true"                   |
        ##------------------------------------------------------------------------/
        #
        //if ($sSaldo=='0' || $sSaldo=='1')
        //{
            $bSoSaldo         = true;
            $bParcial         = ($status == 'HOMOLOGADO' ? false : true);
            $bImprimir        = false;
            $bExibeResultados = false;
            $relatorioTipo    = '0';
            //$mes2 = date('m');
            //$ano2 = date('Y');
            $tipo             = 0;

            //
            // $pSiape : definido no início do script
            // $mes    : definido no início do script
            // $ano    : definido no início do script
            // $mes2   : definido no início do script
            // $ano2   : definido no início do script
            //include_once( "veponto_saldos.php" );

            print $veponto_saldos;

            $mesFim = $mes;
            $anoFim = $ano;
        //}
        #
        ##------------------------------------------------------------------------\
        #  FIM DO CALCULO DE HORAS COMUNS                                         |
        ##------------------------------------------------------------------------/
        ?>
    </div>
</div>
