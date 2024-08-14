<?php

    include_once( "config.php" );
    include_once("class_ocorrencias_grupos.php");
    include_once("src/controllers/DadosServidoresController.php");
    include_once("src/controllers/TabSetoresController.php");

    verifica_permissao("sAPS");

    // Valores passados - encriptados
    $dadosorigem = $_REQUEST['dados'];

    if (empty($dadosorigem))
    {
        header("Location: acessonegado.php");
    }
    else
    {
        $dados                              = explode(":|:", base64_decode($dadosorigem));
        $cmd                                = $dados[0];
        $orig                               = $dados[1];
        $qlotacao                           = $dados[2];
        $dia                                = $dados[3];
        $pos                                = strpos($_SERVER['REQUEST_URI'], '&');
        $_SESSION['sPaginaRetorno_sucesso'] = $_SERVER['REQUEST_URI']
            . ($pos === false ? "?" : "&")
            . "dia=$dia&cmd=$cmd&orig=$orig";
    }

    // dados voltar
    $_SESSION['voltar_nivel_1'] = "frequencia_acompanhar_registros.php?dados=" . $dadosorigem;
    $_SESSION['voltar_nivel_2'] = '';
    $_SESSION['voltar_nivel_3'] = '';
    $_SESSION['voltar_nivel_4'] = '';
    $_SESSION['voltar_nivel_5'] = '';

    $dia      = (!empty($dia) ? $dia : date('d/m/Y'));
    $cmd      = (isset($cmd)  ? $cmd : '2');
    $orig     = (isset($orig) ? $orig : '1');
    $qlotacao = (isset($qlotacao) ? $qlotacao : $_SESSION["sLotacao"]);

    $vDatas = conv_data($dia);

    // definicao da competencia
    $mes  = dataMes($dia);
    $ano  = dataAno($dia);
    $comp = $mes . $ano;

    if ($mes != date('m'))
    {
        header("Location: acessonegado.php");
    }

    
// seleciona os registros para homologação
    $oSetores          = new TabSetoresController();
    $oSetor            = $oSetores->dadosUnidadePorCodigo($qlotacao);
    $descricao_lotacao = $oSetor->descricao;
    $descricao_sigla   = $oSetor->sigla;


    // seleciona os registros para homologação
    $oDadosServidores = new DadosServidoresController();
    $oServidores      = $oDadosServidores->selecionaServidoresUnidade($link, $qlotacao, $vDatas);
    $total_servidores = $oServidores->num_rows();


    ## classe para montagem do formulario padrao
    #
    $oForm = new formPadrao();
    $oForm->setCSS( 'css/new/sorter/css/theme.bootstrap_3.min.css' );
    $oForm->setJS( 'css/new/sorter/js/jquery.tablesorter.min.js' );
    $oForm->setJS( "frequencia_acompanhar_registros.js?v.0.0.0.0.15" );

    $oForm->setSubTitulo("Acompanhamento di&aacute;rio de Registro de Frequ&ecirc;ncia do m&ecirc;s corrente");

    // Topo do formulário
    //
    $oForm->exibeTopoHTML();
    $oForm->exibeCorpoTopoHTML($width='1300px;');

    // // monta a caixa de dialog para exibicao da mensagem/pagina
    //preparaDialogView(1020, 470, 'top');

?>
    <div class="container" style='padding-left:0px;margin-left:0%;'>

        <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

        <div class="row margin-10">

            <?php if ($_SESSION['sResponsavelPorMaisDeUmaUnidade'] == "S" || $_SESSION["sLancarExcessao"] == "S" || $_SESSION["sSenhaI"] == "S"): ?>
                    
                <div class="col-md-12 col-lg-offset-1">
                    <div class="col-md-12 text-right">
                        <a class="btn btn-danger" href="javascript:void(0);" data-load-acompanhar-frequencia-voltar="frequencia_acompanhar_entra.php?dados=<?= $dadosorigem; ?>">
                            <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                        </a>
                    </div>
                </div>

            <?php endif; ?>

            <div class="col-md-12 text-left">
                <h6>
                    <div class="col-md-12">
                        <div class="col-md-11 text-left">
                            <label for="dia" class="control-label">Dia:</label>
                            <label for="dia" class="control-label"><big><?= tratarHTML($dia); ?></big></label>
                            <label for="orgao" class="control-label col-md-offset-1">Órgão:</label>
                            <label for="orgao" class="control-label"><?= tratarHTML(getOrgaoMaisSigla( $qlotacao )); ?></label>
                            <label for="uorg" class="control-label col-md-offset-2" style="padding-left:35px;">UORG: <?= tratarHTML(getUorgMaisDescricao( $qlotacao )); ?></label>
                        </div>
                </h6>
            </div>

            <div class="col-md-12">
                <div class="row">
                    
                    <table id="myTable" class="table table-striped text-center table-hover table-condensed tablesorter">
                        <thead>

                        <!-- COVID-19 -->
                        <tr style="text-align: left;">
                            <td colspan="12" style="text-align: left;vertical-align: bottom;">
                                <fieldset width='100%'>Total de <?= tratarHTML($total_servidores); ?> registros.</fieldset>
                            </td>
                            <td colspan="3" style="text-align: left;vertical-align: bottom;">
                                <div class="col-md-8">
                                    <div class="col-md-10 text-left">
                                        <label for="lot" class="control-label">&nbsp;</label>
                                    </div>
                                    <div class="col-md-3 text-right">
                                        <a class="btn btn-default" href="javascript:void(0);" data-load-acompanhar-frequencia-covid19="frequencia_inclusao_por_lote.php?dados=<?= $dadosorigem; ?>">
                                            <span class="glyphicon glyphicon-list-alt"></span> Incluir por Lote COVID-19
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <!-- Fim COVID-19 -->
                        
                        <tr>
                            <th class="text-center table-bordered" style='vertical-align:middle;'>SIAPE</th>
                            <th class="text-center table-bordered" style='vertical-align:middle;'>Cad</th>
                            <th class="text-center table-bordered" style='vertical-align:middle;'>Nome</th>
                            <th class="text-center table-bordered" style='vertical-align:middle;'>Horário<br>de Serviço</th>
                            <th class="text-center table-bordered" style='vertical-align:middle;'>Ocupa<br>Função</th>
                            <th class="text-center table-bordered" style='vertical-align:middle;'>Sit</th>
                            <th class="text-center table-bordered" style='vertical-align:middle;'>Jornada</th>
                            <th class="text-center table-bordered" style='vertical-align:middle;'>Entrada</th>
                            <th class="text-center table-bordered" style='vertical-align:middle;'>Intervalo<br>Início</th>
                            <th class="text-center table-bordered" style='vertical-align:middle;'>Intervalo<br>Fim</th>
                            <th class="text-center table-bordered" style='vertical-align:middle;'>Saída</th>
                            <th class="text-center table-bordered" style='vertical-align:middle;'>Horas<br>no Dia</th>
                            <th class="text-center table-bordered" style='vertical-align:middle;'>Ocorr&ecirc;ncia</th>
                            <th class="text-center table-bordered" style='vertical-align:middle;'>Abono</th>
                            <th class="text-center table-bordered" style='vertical-align:middle;'>Registro</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php

                        while ($pm = $oServidores->fetch_object())
                        {
                            // dados
                            $ent           = $pm->entra;
                            $inti          = $pm->intini;
                            $ints          = $pm->intsai;
                            $sai           = $pm->sai;
                            $jd            = $pm->jornd;
                            $oco           = $pm->oco;
                            $oco_descricao = $pm->desc_ocorr;
                            $sitcad        = $pm->sigregjur;
                            $sit           = ($pm->cod_sitcad == "66" ? 'ETG' : $pm->sigregjur);

                            // verifica autorizacao
                            $oJornadaTE = new DefinirJornada;
                            $oJornadaTE->setSiape($pm->mat_siape);
                            $oJornadaTE->setLotacao($qlotacao);
                            $oJornadaTE->setData($vDatas);
                            $oJornadaTE->setChefiaAtiva();
                            $oJornadaTE->estabelecerJornada();

                            $titleTitulo      = "ALTERAR HORÁRIO DE SERVIÇO\n\n";
                            $titleJornada     = ($oJornadaTE->autorizado_te == 'S' && $oJornadaTE->chefiaAtiva == 'N' && $sit != 'ETG' && $pm->jornada > 30 ? "Turno estendido\n\n" : "");
                            //$titleJornada     = ($oJornadaTE->autorizado_te == 'S' && $oJornadaTE->chefiaAtiva == 'N' && $sit != 'ETG' && $pm->jornada > 30 ? "Turno estendido" : "Jornada: ".$oJornadaTE->jornada." horas")."\n\n";
                            $titleEntrada     = "Entrada.......: ";
                            $titleSaiAlmoco   = "Saída Almoço..: ";
                            $titleVoltaAlmoco = "Retorno Almoço:  ";
                            $titleSaida       = "Saída...........: ";

                            $title = $titleTitulo . $titleJornada . $titleEntrada . substr($oJornadaTE->entrada_no_servico, 0, 5) . "\n";
                            if (($oJornadaTE->autorizado_te == 'S' && $oJornadaTE->chefiaAtiva == 'N' && $sit != 'ETG') || ($oJornadaTE->jnd < 40 && $oJornadaTE->chefiaAtiva == 'N'))
                            {

                            }
                            elseif ($oJornadaTE->autorizado_te == 'S' && $oJornadaTE->chefiaAtiva == 'S' && $sit != 'ETG')
                            {
                                $title .= $titleSaiAlmoco . substr($oJornadaTE->saida_para_o_almoco, 0, 5) . "\n";
                                $title .= $titleVoltaAlmoco . substr($oJornadaTE->volta_do_almoco, 0, 5) . "\n";
                            }
                            else
                            {
                                if ($sit == 'ETG' || ($oJornadaTE->jnd < 40 && $oJornadaTE->chefiaAtiva == 'N'))
                                {

                                }
                                else
                                {
                                    $title .= $titleSaiAlmoco . substr($oJornadaTE->saida_para_o_almoco, 0, 5) . "\n";
                                    $title .= $titleVoltaAlmoco . substr($oJornadaTE->volta_do_almoco, 0, 5) . "\n";
                                }
                            }
                            $title .= $titleSaida . substr($oJornadaTE->saida_do_servico, 0, 5);


                            ## ############################################################## ##
                            #                                                                  #
                            #  CADASTRO - Prepara o link para visualizar o cadastro            #
                            #                                                                  #
                            ## ############################################################## ##
                            $visualizar_cadastro = "cadastro_consulta_formulario.php?dados=" . criptografa(getNovaMatriculaBySiape(tratarHTML($pm->mat_siape)) . ":|:" . tratarHTML($pm->cod_lot) . ':|:acompanhar');


                            ## ############################################################## ##
                            #                                                                  #
                            #  FREQUÊNCIA - Prepara o link para manutenção da frequência       #
                            #               mensal do servidor/estagiário                      #
                            #                                                                  #
                            ## ############################################################## ##
                            $manutencao_frequencia_do_mes = "frequencia_acompanhar_registros_veponto.php?dados=" . base64_encode(tratarHTML($pm->mat_siape) . ":|:" . tratarHTML($mes) . ":|:" . tratarHTML($ano) . ":|:" . tratarHTML($pm->cod_lot) . ":|:" . $cmd);


                            ## ############################################################## ##
                            #                                                                  #
                            #  HORÁRIO - Prepara o link para manutenção do horário de trabalho #
                            #            do servidor/estagiário                                #
                            #                                                                  #
                            ## ############################################################## ##
                            $horario_de_trabalho = "frequencia_acompanhar_registros_horario_servico.php?dados=" . base64_encode(tratarHTML($pm->mat_siape) . ':|:' . tratarHTML($qlotacao) . ':|:' . tratarHTML($vDatas));


                            ## ############################################################## ##
                            #                                                                  #
                            #  REGISTRAR POR PERÍODO - Prepara o link                          #
                            #                                                                  #
                            ## ############################################################## ##
                            $frequencia_alterar_periodo = base64_encode(tratarHTML($pm->mat_siape) . ':|:' . tratarHTML($pm->nome_serv) . ':|:' . tratarHTML($pm->cod_lot) . ':|:' . $oJornadaTE->getJND() . ':|:' . tratarHTML($pm->cod_sitcad) . ':|:2:|:acompanhar_registros');
                            $registrar_por_periodo      = "javascript:return false;";
                            $registrar_por_periodo      = 'javascript:window.location.replace("frequencia_alterar_periodo.php?dados=' . $frequencia_alterar_periodo . '");';

                            ## ############################################################## ##
                            #                                                                  #
                            #  EXCLUSÃO POR DIAS - Prepara o link                              #
                            #                                                                  #
                            ## ############################################################## ##
                            $frequencia_excluir_dias  = base64_encode(tratarHTML($mat) . ':|:' . "01/" . tratarHTML($mes) . "/" . tratarHTML($year) . ':|:2:|:acompanhar_registros');
                            $excluir_registro_por_dia = "veponto3.php?dados" . base64_encode(tratarHTML($pm->mat_siape) . ":|:" . tratarHTML($dia));
                            $excluir_registro_por_dia = "javascript:return false;";



                            ## ############################################################## ##
                            #                                                                  #
                            #  EXCLUSÃO - Prepara o link                                       #
                            #                                                                  #
                            ## ############################################################## ##
                            $excluir_registro = "gravaregfreq2.php?modo=5&dados=" . base64_encode(tratarHTML($mes) . tratarHTML($ano) . ':|:' . tratarHTML($pm->mat_siape) . ':|:' . tratarHTML($vDatas));
                            $excluir_registro = "javascript:return false;";


                            ## ############################################################## ##
                            #                                                                  #
                            #  ALTERAR/INCLUIR POR DIA - Prepara o link                        #
                            #                                                                  #
                            ## ############################################################## ##
                            //$ocor = ''; // registro6.php eh para inclusão de ocorrência, a partir do acompanhar
                            $cmd           = 1;
                            $idreg_chefia  = 'C';
                            $registro6ou12 = base64_encode(tratarHTML($pm->mat_siape) . ':|:' . tratarHTML($pm->nome_serv) . ':|:' . date('d/m/Y') . ':|:' . tratarHTML($pm->oco) . ':|:' . tratarHTML($pm->cod_lot) . ':|:' . tratarHTML($idreg_chefia) . ':|:' . tratarHTML($cmd) . ':|:' . $oJornadaTE->getJND() . ':|:' . tratarHTML($pm->cod_sitcad) . ':|:acompanhar');
                            //$registro6ou12 = "javascript:return false;";
                            $registro6ou12 = 'frequencia_alterar.php?dados=' . $registro6ou12;


                            ## ############################################################## ##
                            #                                                                  #
                            #  REGISTRAR CONSULTA MÉDICA/EXAMES - Prepara o link               #
                            #                                                                  #
                            ## ############################################################## ##
                            $idreg_chefia  = 'C';
                            $consulta_medica = 'comparecimento_consulta_medica.php?dados=' . criptografa(tratarHTML($pm->mat_siape) . ':|:' . date('d/m/Y') . ':|:' . tratarHTML($idreg_chefia) . ':|:acompanhar');


                            ## ############################################################## ##
                            #                                                                  #
                            #  REGISTRAR PARTICIPAÇÃO - GECC - Prepara o link                  #
                            #                                                                  #
                            ## ############################################################## ##
                            $idreg_chefia  = 'C';
                            $participacao_gecc = 'comparecimento_gecc.php?dados=' . criptografa(tratarHTML($pm->mat_siape) . ':|:' . date('d/m/Y') . ':|:' . tratarHTML($idreg_chefia) . ':|:acompanhar');



                            ## ############################################################## ##
                            #                                                                  #
                            #  ABONO - Prepara o link                                          #
                            #                                                                  #
                            ## ############################################################## ##

                            $dia_nao_util = verifica_se_dia_nao_util($dia, $qlotacao);

                            $objOcorr = new OcorrenciasGrupos();
                            $passiveis_de_abono = $objOcorr->GrupoOcorrenciasPassiveisDeAbono($sitcad);

                            if (($oco == '' || ($oco != '' && in_array($oco, $passiveis_de_abono))) && $dia_nao_util === false)
                            {
                                $link_abonar = "<a href='javascript:void(0);' data-load-acompanhar-frequencia-enviar='javascript:window.location.replace(\"frequencia_justificativa_abono.php?dados=" . base64_encode(tratarHTML($pm->mat_siape) . ":|:" . $dia . ":|:1:|::|:acompanhar") . "\");'' style='color: " . $font_color . "'>Abonar</a>";
                            }
                            else
                            {
                                $link_abonar = "<a href='javascript:return false;' style='cursor: none; text-decoration: none; color: #f8f8f8;' title=' " . tratarHTML($pm->justchef) . "' alt='" . tratarHTML($pm->justchef) . "' disabled>Abonar</a>";
                            }

                            // cor vermelha se ocorrência
                            $font_color = ($oco != "" ? "#000000" : "#FF0000");

                            if ($sr_gerencial == 'sim')
                            {
                                $manutencao_frequencia_do_mes = "frequencia_acompanhar_registros_veponto.php?dados=" . base64_encode(tratarHTML($pm->mat_siape) . ':|:' . $mes . ':|:' . $ano . ':|:' . tratarHTML($pm->cod_lot) . ':|:' . $cmd . ':|:sim');
                                ?>
                                <tr height='18' class="table-hover">
                                    <!--
                                    VISUALIZAR FREQUÊNCIA DO MÊS
                                    //-->
                                    <td align='center' valign='top' class="table-bordered">
                                        &nbsp;<a href="javascript:window.location.replace('<?= $manutencao_frequencia_do_mes; ?>');" title="Visualizar Frequência do Mês"><font color='<?= $font_color; ?>'><?= tratarHTML(removeOrgaoMatricula($pm->mat_siape)); ?></font></a>&nbsp;
                                    </td>
                                    <!--
                                    VISUALIZAR O CADASTRO
                                    //-->
                                    <td align='center' class="table-bordered">
                                        &nbsp;<a href='<?= $visualizar_cadastro; ?>'><font color='<?= $font_color; ?>'><img border='0' src= '<?= _DIR_IMAGEM_; ?>visualizar.gif' width='16' height='16' align='absmiddle' alt='Visualizar o Cadastro'></a></font>&nbsp;
                                    </td>
                                    <!--
                                    NOME DO SERVIDOR
                                    //-->
                                    <td valign='top' class="table-bordered" nowrap>
                                        <div style='border-left: 3px solid transparent;'><font color='<?= $font_color; ?>'><?= tratarHTML($pm->nome_serv); ?></div>
                                    </td>
                                    <!--
                                    ALTERAR HORÁRIO DO SERVIDOR
                                    //-->
                                    <td align='center' class="table-bordered">
                                        &nbsp;<a href='#'><font color='<?= $font_color; ?>'><img border='0' src='<?= _DIR_IMAGEM_; ?>edicao2.jpg' width='16' height='16' align='absmiddle' alt='Horário' title='<?= str_replace('ALTERAR ', '', tratarHTML($title)); ?>'></a></font>&nbsp;
                                    </td>
                                    <!--
                                    INDICA SE O SERVIDOR É OCUPANTE DE FUNÇÃO
                                    //-->
                                    <td align='center' class="table-bordered">
                                        &nbsp;<font color='<?= ($oJornadaTE->chefiaAtiva == 'S' ? '#004e9b' : $font_color); ?>'><?= ($oJornadaTE->chefiaAtiva == 'S' ? 'S' : ''); ?></font>&nbsp;
                                    </td>
                                    <!--
                                    SITUAÇÃO CADASTRAL, JORNADA, HORÁRIOS REGISTRADOS, HORAS REGISTRADAS E OCORRÊNCIA
                                    //-->
                                    <td align='center' class="table-bordered">&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($sit); ?></font>&nbsp;</td>
                                    <td align='center' class="table-bordered">&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($pm->jornada); ?></font>&nbsp;</td>
                                    <td align='center' class="table-bordered">&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($ent); ?></font>&nbsp;</td>
                                    <td align='center' class="table-bordered">&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($inti); ?></font>&nbsp;</td>
                                    <td align='center' class="table-bordered">&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($ints); ?></font>&nbsp;</td>
                                    <td align='center' class="table-bordered">&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($sai); ?></font>&nbsp;</td>
                                    <td align='center' class="table-bordered">&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($jd); ?></font>&nbsp;</td>
                                    <td align='center' class="table-bordered" alt='<?= tratarHTML($oco_descricao); ?>' title='<?= tratarHTML($oco_descricao); ?>' style='cursor: help;'>&nbsp;<font color  ='<?= $font_color; ?>'><?= tratarHTML($oco); ?></font>&nbsp;</td>
                                    <td align='center' class="table-bordered" title=''>&nbsp;&nbsp;</td>
                                    <td align='center' class="table-bordered" nowrap>&nbsp;&nbsp;</td>
                                </tr>
                                <?php
                            }
                            else
                            {
                                ?>
                                <tr class="table-hover" height='18'>
                                    <!--
                                    VISUALIZAR FREQUÊNCIA DO MÊS
                                    //-->
                                    <td align='center' valign='top' class="table-bordered">
                                        &nbsp;<a href="javascript:void(0);" data-load-acompanhar-frequencia-enviar="<?= $manutencao_frequencia_do_mes; ?>" title="Visualizar/Alterar Frequência do Mês"><font color='<?= $font_color; ?>'><?= tratarHTML(removeOrgaoMatricula($pm->mat_siape)); ?></font></a>&nbsp;
                                    </td>
                                    <!--
                                    VISUALIZAR O CADASTRO
                                    //-->
                                    <td align='center' class="table-bordered">
                                        &nbsp;<a href="javascript:void(0);" data-load-acompanhar-frequencia-enviar="<?= $visualizar_cadastro; ?>"><font color='<?= $font_color; ?>'><img border='0' src= '<?= _DIR_IMAGEM_; ?>visualizar.gif' width='16' height='16' align='absmiddle' alt='Visualizar o Cadastro'></a></font>&nbsp;
                                    </td>
                                    <!--
                                    NOME DO SERVIDOR
                                    //-->
                                    <td valign='top' class="table-bordered" nowrap>
                                        <div class='text-left' style='border-left: 3px solid transparent;'><font color='<?= $font_color; ?>'><?= tratarHTML($pm->nome_serv); ?></div>
                                    </td>
                                    <!--
                                    ALTERAR HORÁRIO DO SERVIDOR
                                    //-->
                                    <td align='center' class="table-bordered">
                                        &nbsp;<a href="javascript:void(0);" data-load-acompanhar-frequencia-enviar='<?= $horario_de_trabalho; ?>'><font color='<?= $font_color; ?>'><img border='0' src='<?= _DIR_IMAGEM_; ?>edicao2.jpg' width='16' height='16' align='absmiddle' alt='Alterar horario' title='<?= tratarHTML($title); ?>'></a></font>&nbsp;
                                    </td>
                                    <!--
                                    INDICA SE O SERVIDOR É OCUPANTE DE FUNÇÃO
                                    //-->
                                    <td align='center' class="table-bordered">
                                        &nbsp;<font color='<?= ($oJornadaTE->chefiaAtiva == 'S' ? '#004e9b' : $font_color); ?>'><?= ($oJornadaTE->chefiaAtiva == 'S' ? 'S' : ''); ?></font>&nbsp;
                                    </td>
                                    <!--
                                    SITUAÇÃO CADASTRAL, JORNADA, HORÁRIOS REGISTRADOS, HORAS REGISTRADAS E OCORRÊNCIA
                                    //-->
                                    <td align='center' class="table-bordered">&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($sit); ?></font>&nbsp;</td>
                                    <td align='center' class="table-bordered">&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($pm->jornada); ?></font>&nbsp;</td>
                                    <td align='center' class="table-bordered">&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($ent); ?></font>&nbsp;</td>
                                    <td align='center' class="table-bordered">&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($inti); ?></font>&nbsp;</td>
                                    <td align='center' class="table-bordered">&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($ints); ?></font>&nbsp;</td>
                                    <td align='center' class="table-bordered">&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($sai); ?></font>&nbsp;</td>
                                    <td align='center' class="table-bordered">&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($jd); ?></font>&nbsp;</td>
                                    <td align='center' class="table-bordered" title='<?= tratarHTML($oco_descricao); ?>' alt='<?= tratarHTML($oco_descricao); ?>' style='cursor: help;'>&nbsp;<font color  ='<?= $font_color; ?>'><?= tratarHTML($oco); ?></font>&nbsp;</td>
                                    <!--
                                    ABONO
                                    //-->
                                    <td align='center' class="table-bordered" title='Utilize essa opção para abono dos atrasos, saídas antecipadas, faltas justificadas, dias úteis sem frequência ou registro parcial.'>
                                        &nbsp;<?= $link_abonar; ?>&nbsp;
                                    </td>
                                    <!--
                                    REGISTRAR POR DIA, REGISTRAR POR PERÍODO E EXCLUIR OCORRÊNCIA
                                    //-->
                                    <td align='center' class="table-bordered" nowrap>
                                        &nbsp;<a href="javascript:void(0);" data-load-acompanhar-frequencia-enviar='<?= $registro6ou12; ?>'>
                                            <font color='#000000'><img border='0' src='<?= _DIR_IMAGEM_; ?>edicao2.jpg' width='16' height='16' align='absmiddle' title='Registrar por dia' alt='Registrar por dia'></font></a>

                                        - <a href="javascript:void(0);" data-load-acompanhar-frequencia-enviar='<?= $registrar_por_periodo; ?>' title='Registrar por período' alt='Registrar por período'><font color='#000000'><img border='0'  src='<?= _DIR_IMAGEM_; ?>calendario-escuro.gif' width='16' height='16' align='absmiddle' title='Registrar por período' alt='Registrar por período'></font></a>

                                        <!-- - <a href='<?= $excluir_registro_por_dia; ?>' disabled><font color='#000000'><img border='0' src='<?= _DIR_IMAGEM_; ?>exclu.png' width='16' height='16' align='absmiddle' alt='Excluir ocorrências'  class='inibir_opcao'></font></a>-->

                                        - <a href="javascript:void(0);" data-load-acompanhar-frequencia-enviar='<?= $consulta_medica; ?>'><font color='#000000'><img border='0'  src='<?= _DIR_IMAGEM_; ?>icone_consultas_medicas.png' width='16' height='16' align='absmiddle' title='Registrar comparecimento a consulta médica ou exame' alt='Registrar comparecimento a consulta médica ou exame'></font></a>

                                        - <a href="javascript:void(0);" data-load-acompanhar-frequencia-enviar='<?= $participacao_gecc; ?>'><font color='#000000'><img border='0'  src='<?= _DIR_IMAGEM_; ?>BotaoEvento4.png' width='16' height='16' align='absmiddle' title='Registrar Gratificação por Encargo de Curso ou Concurso' alt='Registrar Gratificação por Encargo de Curso ou Concurso'></font></a>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                        </tbody>
                    </table>
                    
                </div>

            </div>

            <?php if ($_SESSION['sResponsavelPorMaisDeUmaUnidade'] == "S" || $_SESSION["sLancarExcessao"] == "S" || $_SESSION["sSenhaI"] == "S"): ?>
                    
                <div class="col-md-12 col-lg-offset-1">
                    <div class="col-md-12 text-right">
                        <a class="btn btn-danger" href="javascript:void(0);" data-load-acompanhar-frequencia-voltar="frequencia_acompanhar_entra.php?dados=<?= $dadosorigem; ?>">
                            <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                        </a>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </div>
<?php

    // Base do formulário
    //
    $oForm->exibeCorpoBaseHTML();
    $oForm->exibeBaseHTML();
