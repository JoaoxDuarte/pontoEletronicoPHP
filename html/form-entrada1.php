<div class="container">
    <div class="row">
        <div class="alert alert-info text-center">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            <span class="uppercase"><?= $this->getMensagem($nInd); ?></span>
        </div>
    </div>
    <!-- Linha referente aos horários -->
    <div class="row">
        <div class="col-md-6 hora-atual">
            <?php
            if ($this->getFormEntrada() == 'entrada1')
            {
                include_once( _DIR_INC_ . 'relogio.php' ); // localização /inc
            }
            ?>
            <!--<h4><strong><span class="uppercase"> Hora Atual</span></strong></h4><span class="hora"><span class="glyphicon glyphicon-time"></span> 20:37:40</span>-->
        </div>
        <div class="col-md-6 hora-atual">
            <h4><strong><span class="uppercase"> Horas Trabalhadas</span></strong></h4><span class="hora"><span class="glyphicon glyphicon-time"></span> <?= horas_trabalhadas_ate_o_momento($this->getSiape(), $this->getDataHoje()); ?> </span>
        </div>
    </div>
    <!-- Row Referente aos dados dos funcionários  -->
    <div class="row margin-10">
        <div class="col-md-6 subtitle">
            <h4 class="lettering-tittle uppercase"><strong>Meus Dados</strong></h4>
        </div>
        <div class="col-md-12" id="dados-funcionario">
            <div class="col-md-2">
                <h5><strong>SIAPE</strong></h5>
                <p><?= $this->getSiape(); ?></p>
            </div>
            <div class="col-md-4">
                <h5><strong>NOME</strong></h5>
                <p><?= $this->getNome(); ?></p>
            </div>
            <div class="col-md-3">
                <h5><strong>LOTAÇÃO</strong></h5>
                <p><?= $this->getLotacao(); ?></p>
            </div>
            <div class="col-md-3">
                <h5><strong>Compensaçao</strong></h5>
                <p><strong><?= $this->getAutorizaBHoras() == "S" ? "AUTORIZADA" : "<span class='red' style='color:red;'>NÃO AUTORIZADA</span>"; ?></strong></p>
            </div>
        </div>
    </div>
    <!-- Row Referente aos dados Setor do funcionario  -->
    <div class="row margin-10">
        <div class="col-md-6 subtitle">
            <h4 class="lettering-tittle uppercase"><strong>Dados do seu Setor</strong></h4>
        </div>
        <div class="col-md-12" id="dados-setor">
            <div class="col-md-3">
                <h5><strong>Horario do Setor<?= ($this->getTurnoEstendido() == 'S' ? ' - Unidade com Turno Estendido' : ''); ?></strong></h5>
                <p><strong><?= $this->getHoraInicioINSS(); ?></strong> as <strong><?= $this->getHoraFimINSS(); ?></strong></p>
            </div>
            <div class="col-md-3">
                <h5><strong>Hora de Entrada</strong></h5>
                <p><?= $this->getHoraEntrada(); ?></p>
            </div>
            <div class="col-md-3">
                <h5><strong>Intervalo</strong></h5>
                <p><?= $this->getHoraSaidaAlmoco() . " as " . $this->getHoraVoltaAlmoco(); ?></p>
            </div>
            <div class="col-md-3">
                <h5><strong>Hora de Saida</strong></h5>
                <p><?= $this->getHoraSaida(); ?></p>
            </div>
        </div>
    </div>
    <!-- Row referente a Comparecimento-->
    <div class="row comparecimento">
        <h3>Registro de comparecimento</h3>
    </div>
    <!-- -->
    <div class="row" id="registros">
        <div class="col-md-12">
            <div class="col-md-6 col-md-offset-3">
                <h4>Horários do servidor - <?= $this->getDataHoje(); ?></h4>
            </div>
        </div>
        <div class="col-md-3">
            <h5><strong>Entrada</strong></h5>
            <p><?= ($this->getEntrada() == '' ? $this->getHoraAtual() : $this->getEntrada()) . 'h'; ?></p>
        </div>
        <div class="col-md-6">
            <h5><strong>Intervalo</strong></h5>
            <p>
                <?php
                if ($this->getJornada() == '08:00' || $this->getAutorizaBHoras() == 'S' || $this->getChefiaAtiva() == 'S')
                {
                    echo ($this->getSaidaAlmoco() == '00:00:00' && $this->getFormEntrada() == 'entrada1' ? "<a class='btn btn-primary btn-xs' href=\"javascript:confirma ('Deseja realmente registrar o inicio do intervalo?','entrada2.php')\"><span class=\"glyphicon glyphicon-flag\"></span>  Iniciar </a>" : "");
                }

                echo ' ' . $this->getSaidaAlmoco() . ' as ' . $this->getVoltaAlmoco() . 'h ';

                if ($this->getJornada() == '08:00' || $this->getAutorizaBHoras() == 'S' || $this->getChefiaAtiva() == 'S')
                {
                    echo ($this->getVoltaAlmoco() == '00:00:00' && $this->getFormEntrada() == 'entrada1' ? "<a class='btn btn-danger btn-xs' href=\"javascript:confirma ('Deseja realmente registrar o retorno do intervalo?','entrada3.php')\"><span class=\"glyphicon glyphicon-remove\"></span>  Finalizar</a>" : "");
                }
                ?>
            </p>
        </div>
        <div class="col-md-3">
            <h5><strong>Saída</strong></h5>
            <p>
                <?php

                echo $this->getSaida();
                echo $this->getSaida() == '00:00:00' && $this->getFormEntrada() == 'entrada1' ? " <a class=\"btn btn-success btn-xs\" href=\"javascript:confirma ('Deseja realmente registrar o fim do expediente?','entrada4.php')\"><span class=\"glyphicon glyphicon-ok\"></span> Marcar Saída</a>" : "";
                ?>
            </p>
        </div>
    </div>
    <!-- -->
    <div class="row" id="botoes-iteracao">
        <div class="col-md-4">
            <button class="btn btn-primary btn-block" onclick="window.open('autorizacao_trabalho_dia_nao_util_solicitacao.php?dados=<?= base64_encode($this->getCodigoMunicipio()); ?>');">
                <div class="btn-icon">
                    <span class="glyphicon glyphicon-search"></span>
                </div> Solicitação para trabalho<br>em dia não útil
            </button>
        </div>
        <div class="col-md-4">
            <button class="btn btn-info btn-block"  onclick="window.open('entrada6.php?cmd=1&orig=1&lotacao=<?= $this->getLotacao(); ?>');"><div class="btn-icon">
                    <span class="glyphicon glyphicon-eye-open"></span>
                </div> Visualizar Frequência<br>do mês</button>
        </div>
        <div class="col-md-4">
            <button class="btn btn-warning btn-block" onclick="window.open('entrada8.php?cmd=2&orig=1');"><div class="btn-icon">
                    <span class="glyphicon glyphicon-book"></span>
                </div> Visualizar Meses<br>Anteriores</button>
        </div>
        <div class="col-md-12 text-center margin-10">
            <a class="demonstrativo" href="entrada9.php" target="new">Visualizar demonstrativo de Compensações.</a>
        </div>
    </div>
</div>
