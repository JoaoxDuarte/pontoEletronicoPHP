<?php
    include_once( "config.php" );
    
    verifica_permissao("sAPS");

// pega o nome do arquivo origem
    $pagina_de_origem = pagina_de_origem();
    
    $diaa = $_REQUEST["dia"];
    $orig = anti_injection($_REQUEST["orig"]);
    $cmd  = anti_injection($_REQUEST["cmd"]);
    
    if ($cmd == "1")
    {
        $qlotacao = anti_injection($_REQUEST["qlotacao"]);
    }
    else
    {
        $qlotacao = $_SESSION["sLotacao"];
    }

// dados para retorno a este script
    $_SESSION['sPaginaRetorno_sucesso'] = $_SERVER['REQUEST_URI'] . "&dia=$diaa&cmd=$cmd&orig=$orig&qlotacao=$qlotacao";

// historico de navegacao (substituir o $_SESSION['sPaginaRetorno_sucesso']
    $sessao_navegacao = new Control_Navegacao();
    $sessao_navegacao->initSessaoNavegacao();
    $sessao_navegacao->setPagina($_SERVER['REQUEST_URI'] . "?dia=$dia&cmd=$cmd&orig=$orig&qlotacao=$qlotacao");
    /*
      for ($i=0; $i < $sessao_navegacao->ContaPaginas(); $i++)
      {
      print $sessao_navegacao->getPagina($i).'<br>';
      }
      $sessaoControleNavegacao = $_SESSION['sessaoControleNavegacao'];
      for ($i=0; $i < count($sessaoControleNavegacao); $i++)
      {
      print $sessaoControleNavegacao[$i].'<br>';
      }
     */
    switch ($orig)
    {
        case "1":
            $vDatas = date("Y-m-d");
            $dia    = date("d/m/Y");
            break;
        case "2":
            $vDatas = conv_data($diaa);
            $dia    = $diaa;
            break;
        default:
            $vDatas = ($diaa == '' ? date("Y-m-d") : conv_data($diaa));
            $dia    = date("d/m/Y");
            break;
    }

// definicao da competencia
    $mes  = substr($dia, 3, 2);
    $ano  = substr($dia, 6, 4);
    $comp = $mes . $ano;

// instancia banco de dados
    $oTbDados = new DataBase('PDO');

// descricao da lotacao
    $oTbDados->query("SELECT descricao FROM tabsetor WHERE codigo = '$qlotacao' AND ativo='S' ");
    $oPonto            = $oTbDados->fetch_object();
    $descricao_lotacao = $oPonto->descricao;

// seleciona os registros para homologação
    $oServidores      = seleciona_servidores_ponto($link, $qlotacao, $vDatas);
    $total_servidores = $oServidores->num_rows();


## classe para montagem do formulario padrao
#
    $oForm = new formPadrao();
    $oForm->setCSS(_DIR_CSS_ . 'estiloIE.css');
    $oForm->setSeparador(0);
    $oForm->setLargura("950px");
    $oForm->setCaminho('Frequência » Acompanhar');
    $oForm->setSubTitulo("Acompanhamento di&aacute;rio de Registro de Frequ&ecirc;ncia do m&ecirc;s corrente");
    $oForm->setObservacaoTopo("Dia <big>$dia</big> - Lota&ccedil;&atilde;o <input name='lot' type='text' class='alinhadoAoCentro' id='lot' value='$qlotacao - $descricao_lotacao' size='60' maxlength='60' readonly> - Para acompanhar outro dia clique na figura <a href='acompanharpassado.php?mes=$mes&ano=$ano&lot=$qlotacao&cmd=$cmd'><img border='0' src='" . _DIR_IMAGEM_ . "copiar.gif' align='absmiddle'></a>");

// Topo do formulário
//
    $oForm->exibeTopoHTML();
    $oForm->exibeCorpoTopoHTML();
?>
    <table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#F1F1E2" width="100%" id="AutoNumber1" valign='top'>
        <tr>
            <td>Total de <?= tratarHTML($total_servidores); ?> registros.</td>
        </tr>
        <tr>
            <td>
                <table class="thin sortable draggable" border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#F1F1E2" width="100%" id="AutoNumber2" >
                    <tr bgcolor="#DBDBB7">
                        <td align='center' width='65px'><strong>SIAPE</strong></td>
                        <td width='365px'><strong>&nbsp;Cad&nbsp;</strong></td>
                        <td width='365px'><strong>&nbsp;Nome</strong></td>
                        <td align="center"><strong>&nbsp;Hor&aacute;rio<br>de Serviço</strong></td>
                        <td align="center"><strong>&nbsp;Ocupa<br>Função</strong></td>
                        <td align="center"><strong>&nbsp;Sit&nbsp;</strong></td>
                        <td align="center"><strong>&nbsp;Jornada&nbsp;</strong></td>
                        <td align="center" width='60px'><strong>&nbsp;Entrada&nbsp;</strong></td>
                        <td align="center" width='70px'><strong>&nbsp;Intervalo&nbsp; &nbsp;Inicio&nbsp;</strong></td>
                        <td align="center" width='70px'><strong>&nbsp;Intervalo&nbsp; &nbsp;Fim&nbsp;</strong></td>
                        <td align="center" width='60px'><strong>&nbsp;Saida&nbsp;</strong></td>
                        <td align="center" width='60px'><strong>&nbsp;Horas&nbsp; &nbsp;no&nbsp;dia&nbsp;</strong></td>
                        <td align="center"><strong>&nbsp;Ocorr&ecirc;ncia&nbsp;</strong></td>
                        <td align='center'><strong>&nbsp;Abono&nbsp;</strong></td>
                        <td align='center' width='115px'><strong>&nbsp;Registro&nbsp;</strong></td>
                    </tr>
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
                            $sit           = ($pm->cod_sitcad == "66" ? 'ETG' : 'RJU');
                            
                            // verifica autorizacao
                            $oJornadaTE = new DefinirJornada;
                            $oJornadaTE->setSiape($pm->mat_siape);
                            $oJornadaTE->setLotacao($qlotacao);
                            $oJornadaTE->setData($vDatas);
                            $oJornadaTE->setChefiaAtiva();
                            $oJornadaTE->estabelecerJornada();
                            
                            $titleTitulo      = "ALTERAR HORÁRIO DE SERVIÇO\n\n";
                            $titleJornada     = ($oJornadaTE->autorizado_te == 'S' && $oJornadaTE->chefiaAtiva == 'N' && $sit != 'ETG' && $pm->jornada > 30 ? "Turno estendido\n\n" : "");
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
                            
                            $vecadastro = "cadastro_consulta_formulario.php?dados=" . base64_encode(tratarHTML($pm->mat_siape) . ":|:" . tratarHTML($pm->cod_lot));
                            $veponto2   = "veponto2.php?dados=" . base64_encode(tratarHTML($pm->mat_siape) . ":|:" . $mes . ":|:" . $ano . ":|:" . tratarHTML($pm->cod_lot) . ":|:" . $cmd);
                            $reghora    = "reghora.php?dados=" . base64_encode(tratarHTML($pm->mat_siape) . ':|:' . $qlotacao . ':|:' . $vDatas);
                            $regjustab  = "regjustab.php?mat=$pm->mat_siape&nome=$pm->nome_serv&dia=$dia&oco=$oco&cmd=1";
                            
                            $registro15 = "registro15.php?mat=" . tratarHTML($pm->mat_siape) . "&nome=" . tratarHTML($pm->nome_serv) . "&dia=" . $dia . "&hs=" . tratarHTML($pm->sai_trab) . "&jnd=" . $oJornadaTE->getJND() . "&cmd=1";
                            $veponto3   = "veponto3.php?dados" . base64_encode(tratarHTML($pm->mat_siape) . ":|:" . $dia);
                            
                            $exclui3 = "gravaregfreq2.php?modo=5&dados=" . base64_encode($mes . $ano . ':|:' . tratarHTML($pm->mat_siape) . ':|:' . $vDatas);
                            
                            if ($orig == "1")
                            {
                                $ocor          = ''; // registro6.php eh para inclusão de ocorrência, a partir do acompanhar
                                $cmd           = 1;
                                $registro6ou12 = "registro6.php?dados=" . base64_encode(tratarHTML($pm->mat_siape) . ':|:' . tratarHTML($pm->nome_serv) . ':|:' . tratarHTML($pm->cod_lot) . ':|:' . $ocor . ':|:' . $cmd . ':|:' . $dia . ':|:' . tratarHTML($pm->sai_trab) . ':|:' . $oJornadaTE->getJ()); // $oJornadaTE->getJ() -> jornada do servidor
                            }
                            else
                            {
                                $registro6ou12 = "registro12.php?mat=" . tratarHTML($pm->mat_siape) . "&nome=" . tratarHTML($pm->nome_serv) . "&dia=" . $dia . "&lot=" . tratarHTML($pm->cod_lot) . "&jnd=" . $oJornadaTE->getJ() . "&cmd=1";
                            }
                            
                            $font_color = ($oco != "" ? "#000000" : "#FF0000");
                            
                            if ($sr_gerencial == 'sim')
                            {
                                $veponto2 = "veponto2.php?dados=" . base64_encode(tratarHTML($pm->mat_siape) . ':|:' . $mes . ':|:' . $ano . ':|:' . tratarHTML($pm->cod_lot) . ':|:' . $cmd . ':|:sim');
                                ?>
                                <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)' height='18'>
                                    <td align='center' valign='top'>&nbsp;<a href='<?= $veponto2; ?>'><font color='<?= $font_color; ?>'><?= tratarHTML($pm->mat_siape); ?></font></a>&nbsp;</td>
                                    <td align='center'>&nbsp;<a href='<?= $vecadastro; ?>'><font color='<?= $font_color; ?>'><img border='0' src='<?= _DIR_IMAGEM_; ?>visualizar.gif' width='16' height='16' align='absmiddle' alt='Visualizar o Cadastro'></a></font>&nbsp;</td>
                                    <td valign='top' nowrap><div style='border-left: 3px solid transparent;'><font color='<?= $font_color; ?>'><?= tratarHTML($pm->nome_serv); ?></div></td>
                                    <td align='center'>&nbsp;<a href='#'><font color='<?= $font_color; ?>'><img border='0' src='<?= _DIR_IMAGEM_; ?>edicao2.jpg' width='16' height='16' align='absmiddle' alt='Horário' title='<?= tratarHTML(str_replace('ALTERAR ', '', tratarHTML($title))); ?>'></a></font>&nbsp;</td>
                                    <td align='center'>&nbsp;<font color='<?= ($oJornadaTE->chefiaAtiva == 'S' ? '#004e9b' : $font_color); ?>'><?= ($oJornadaTE->chefiaAtiva == 'S' ? 'S' : ''); ?></font>&nbsp;</td>
                                    <td align='center'>&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($sit); ?></font>&nbsp;</td>
                                    <td align='center'>&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($pm->jornada); ?></font>&nbsp;</td>
                                    <td align='center'>&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($ent); ?></font>&nbsp;</td>
                                    <td align='center'>&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($inti); ?></font>&nbsp;</td>
                                    <td align='center'>&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($ints); ?></font>&nbsp;</td>
                                    <td align='center'>&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($sai); ?></font>&nbsp;</td>
                                    <td align='center'>&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($jd); ?></font>&nbsp;</td>
                                    <td align='center' alt='<?= tratarHTML($oco_descricao); ?>' title='<?= tratarHTML($oco_descricao); ?>' style='cursor: help;'>&nbsp;<font color='<?= tratarHTML($font_color); ?>'><?= tratarHTML($oco); ?></font>&nbsp;</td>
                                    <td align='center' title=''>&nbsp;&nbsp;</td>
                                    <td align='center' nowrap>&nbsp;&nbsp;</td>
                                </tr>
                                <?php
                            }
                            else
                            {
                                ?>
                                <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)' height='18'>
                                    <td align='center' valign='top'>&nbsp;<a href='<?= $veponto2; ?>'><font color='<?= $font_color; ?>'><?= tratarHTML($pm->mat_siape); ?></font></a>&nbsp;</td>
                                    <td align='center'>&nbsp;<a href='<?= $vecadastro; ?>'><font color='<?= $font_color; ?>'><img border='0' src='<?= _DIR_IMAGEM_; ?>visualizar.gif' width='16' height='16' align='absmiddle' alt='Visualizar o Cadastro'></a></font>&nbsp;</td>
                                    <td valign='top' nowrap><div style='border-left: 3px solid transparent;'><font color='<?= $font_color; ?>'><?= tratarHTML($pm->nome_serv); ?></div></td>
                                    <td align='center'>&nbsp;<a href='<?= $reghora; ?>'><font color='<?= $font_color; ?>'><img border='0' src='<?= _DIR_IMAGEM_; ?>edicao2.jpg' width='16' height='16' align='absmiddle' alt='Alterar horario' title='<?= tratarHTML($title); ?>'></a></font>&nbsp;</td>
                                    <td align='center'>&nbsp;<font color='<?= ($oJornadaTE->chefiaAtiva == 'S' ? '#004e9b' : $font_color); ?>'><?= tratarHTML(($oJornadaTE->chefiaAtiva == 'S' ? 'S' : '')); ?></font>&nbsp;</td>
                                    <td align='center'>&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($sit); ?></font>&nbsp;</td>
                                    <td align='center'>&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($pm->jornada); ?></font>&nbsp;</td>
                                    <td align='center'>&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($ent); ?></font>&nbsp;</td>
                                    <td align='center'>&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($inti); ?></font>&nbsp;</td>
                                    <td align='center'>&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($ints); ?></font>&nbsp;</td>
                                    <td align='center'>&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($sai); ?></font>&nbsp;</td>
                                    <td align='center'>&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($jd); ?></font>&nbsp;</td>
                                    <td align='center' title='<?= tratarHTML($oco_descricao); ?>' alt='<?= tratarHTML($oco_descricao); ?>' style='cursor: help;'>&nbsp;<font color='<?= $font_color; ?>'><?= tratarHTML($oco); ?></font>&nbsp;</td>
                                    <td align='center' title='Utilize essa opção para abono dos atrasos e saidas antecipadas decorrentes de interesse do serviço.'>&nbsp;<a href='<?= $regjustab; ?>'><font color='<?= $font_color; ?>'>Abonar</font></a>&nbsp;</td>
                                    <td align='center' nowrap>&nbsp;<a href='<?= $registro6ou12; ?>'><font color='#000000'><img border='0' src='<?= _DIR_IMAGEM_; ?>edicao2.jpg' width='16' height='16' align='absmiddle' alt='Registrar por dia'></font></a> - <a href='<?= $registro15; ?>'><font color='#000000'><img border='0' src='<?= _DIR_IMAGEM_; ?>calendario.gif' width='16' height='16' align='absmiddle' alt='Registrar por período'></font></a> - <a href='<?= (getIpReal() == '10.120.49.112' ? $exclui3 : $veponto3); ?>'><font color='#000000'><img border='0' src='<?= _DIR_IMAGEM_; ?>exclu.png' width='16' height='16' align='absmiddle' alt='Excluir ocorrências'></font></a>&nbsp;</td>
                                </tr>
                                <?php
                            }
                        }
                    ?>
                </table>
            </td>
        </tr>
    </table>
<?php

// Base do formulário
//
    $oForm->exibeCorpoBaseHTML();
    $oForm->exibeBaseHTML();
