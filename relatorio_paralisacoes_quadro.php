<?php
/* _________________________________________________________________________*\
|                                                                            |
|		PREPARA OS DIVERSOS TIPOS DE RELATÓRIOS                      |
|                                                                            |
\*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */

include_once( "config.php");
include_once('relatorio_paralisacoes_classes.php');
include_once( "class_ocorrencias_grupos.php" );

//verifica_permissao('estrategica','relatorio_paralisacoes_login.php');

session_start();

$liberado = $_SESSION['liberado_acesso'];
$gravar   = $_SESSION['liberado_gravar'];

$sLotacao = $_SESSION["sLotacao"];

//
// le os valores passados pelo formulario
//
$escolha_und    = anti_injection($_REQUEST["escolha_und"]);
$escolha_data   = anti_injection($_REQUEST["escolha_data"]);
$escolha_data   = ($escolha_data == '' ? date('d/m/Y') : $escolha_data);
$escolha_cargo  = anti_injection($_REQUEST["escolha_cargo"]);
$escolha_gravar = anti_injection($_REQUEST["escolha_gravar"]);

$_SESSION['sEscolha_und']   = $escolha_und;
$_SESSION['sEscolha_data']  = $escolha_data;
$_SESSION['sEscolha_cargo'] = $escolha_cargo;

// dados básicos para seleção das informações desejadas
$data_inicial = '01/01/2019';

$data_inicial = inverteData($data_inicial);
$data_final   = date('Ymd');

$data_escolhida_invertida = inverteData($escolha_data);
$data_escolhida_mysql     = conv_data($escolha_data);

// SELEÇÃO DOS DADOS
$oSQL          = new sql_seleciona();
$sqlServidores = $oSQL->unidades();

// instancia BD
$oDBase = new DataBase('PDO');

$oDBase->query($sqlServidores);
$nGex          = $oDBase->num_rows();

$codger     = '';
$codgex     = '';

if (empty($escolha_data))
{
    $data_inicial = date('Ymd');
    $data_final   = date('Ymd');
    $mes_do_ponto = date('mY');
}
else
{
    $data_inicial = $data_escolhida_invertida;
    $data_final   = $data_escolhida_invertida;
    $mes_do_ponto = substr($escolha_data, 3, 2) . substr($escolha_data, 6, 4);
}

$dia = conv_data($escolha_data);


// formulário padrão
$oForm = new formPadrao();
$oForm->setSubTitulo('Gestão Estratégica » Paralisações/Faltas');

$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


?>
<style>
    .opacidade70 { filter: alpha(opacity=70); -moz-opacity: .7; -khtml-opacity: .7; opacity: .7; }
    .opacidade20 { filter: alpha(opacity=20); -moz-opacity: .2; -khtml-opacity: .2; opacity: .2; }
</style>
<?php

/* _________________________________________________________________________*\
  |		INÍCIO DO HTML                                                          |
  \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */
?>
        <script language='javascript'>
                function escolha()
                {
                    document.forms[0].submit();
                }

                function abrir_gravados()
                {
                    var escolha = $('#escolha_gravar').selected;
                    alert(escolha);
                    var escolha = $('#escolha_gravar').val();
                    alert(escolha);
                    //Abre('relatorio_paralisacoes_planilha_gera.php?', 800, 600);
                    return false;
                }

                function openIFrameApl(iFrameId, winURL)
                {
                    var ifId = $('#'+iFrameId);
                    ifId.src = winURL;
                    parent.frames[iFrameId].location.replace(winURL);
                }
        </script>

    </head>

    <body>

    <center>
        <table>
            <tr>
                <td align='center'>

                    <fieldset style='width: 800px;'>
                        <form name='dados' action='<?= $_SERVER['PHP_SELF']; ?>' method='post' id="form1" name="form1">
                        <input type='hidden' id='modo' name='modo' value='$modo'>

                            <table border='0' cellpadding='0' cellspacing='0'>
                                <!-- UNIDADES //-->
                                <tr>
                                    <td align='right' valign='bottom' height='40px'><b>Unidade:</b></td>
                                    <td valign='bottom' height='40px'>&nbsp;<?= listBoxUnidades($oDBase, $escolha_und); ?></td>
                                </tr>
                                <tr>
                                    <td align='right' valign='bottom' height='40px'><b>Data:</b></td>
                                    <td valign='bottom' height='40px'>&nbsp;<?= listBoxDatas($data_inicial, $data_final, $data_escolhida_invertida); ?></td>
                                </tr>
                            </table>
                        </form>
                    </fieldset>
                    <?php
                    /* _________________________________________________________________________*\
                      |		OCORRÊNCIAS REGISTRADAS                                                 |
                      \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */
                    $x     = "
				SELECT a.siapecad as oco, a.desc_ocorr as descricao
				FROM tabocfre As a
				WHERE ativo = 'S'
				ORDER BY a.siapecad ";
                    $oDBase->query($x);
                    $nrows = $oDBase->num_rows();

                    //$arrOcorrAusencia['00000']  = array( <descrição ocorrência>, <tota-ocorr>, <perito>, <NS>, <NI> );
                    $arrOcorrAusencia                = array();
                    $arrOcorrAusencia['TOTAL']       = array('TOTAL DE SERVIDORES', 0, 0, 0, 0);
                    $arrOcorrAusencia['00000']       = array('FREQUENCIA NORMAL', 0, 0, 0, 0);
                    $arrOcorrAusencia['00172']       = array('ATRASO OU SAIDA ANTECIPADA', 0, 0, 0, 0);
                    $arrOcorrAusencia['00169']       = array('FERIAS', 0, 0, 0, 0);
                    $arrOcorrAusencia['33333']       = array('CREDITO DECOMPENSACAO', 0, 0, 0, 0);
                    $arrOcorrAusencia['OUTROS']      = array('OUTRAS OCORRÊNCIAS', 0, 0, 0, 0);
                    $arrOcorrAusencia['88888']       = array('REGISTRO PARCIAL', 0, 0, 0, 0);
                    $arrOcorrAusencia['00137']       = array('FALTA POR MOTIVO DE GREVE', 0, 0, 0, 0);
                    $arrOcorrAusencia['99999']       = array('SEM FREQUENCIA', 0, 0, 0, 0);
                    $arrOcorrAusencia['DISPENSADOS'] = array('DAS 4,5,6', 0, 0, 0, 0);

                    $arrOcorr          = array();
                    $arrOcorr['TOTAL'] = array('TOTAL DE SERVIDORES', 0, 0, 0, 0);

                    while ($oOcor = $oDBase->fetch_object())
                    {
                        $arrOcorr[$oOcor->oco] = array($oOcor->oco . ' - ' . $oOcor->descricao, 0, 0, 0, 0);

                        switch ($oOcor->oco)
                        {
                            case '00000':
                            case '00172':
                            case '00169':
                            case '33333':
                            case '00137':
                            case '99999':
                            case '88888':
                                $arrOcorrAusencia[$oOcor->oco] = array($oOcor->oco . ' - ' . $oOcor->descricao, 0, 0, 0, 0);
                                break;
                        }
                    }


                    /* _________________________________________________________________________*\
                      |		SELEÇÃO DOS DADOS                                                       |
                      \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */
                    $x = "
				SELECT DISTINCTROW
					b.mat_siape, b.nome_serv, d.nome_ger, c.cod_gex, c.nome_gex, b.cod_lot, LEFT(IF(IFNULL(g.lot_nsiape,9)=9,e.descricao,g.lot_nsiape),100) AS cod_lot_descricao, b.cod_cargo, f.desc_cargo AS cargo_descricao, i.sit_ocup, u.cod_funcao, b.entra_trab, e.upag, b.cod_uorg, d.id_ger, b.nivel
				FROM servativ AS b
				LEFT JOIN lotacao_nova AS g ON b.cod_uorg = g.uorg_anterior
				LEFT JOIN dados_gex AS c ON
					IF(SUBSTR(b.cod_lot,4,2)='00',CONCAT(LEFT(b.cod_lot,2),'001'),IF(SUBSTR(b.cod_lot,3,3)='150',LEFT(b.cod_lot,5),CONCAT(LEFT(b.cod_lot,2),'0',SUBSTR(b.cod_lot,4,2)))) = c.cod_gex
				LEFT JOIN dados_ger AS d ON c.regional = d.id_ger
				LEFT JOIN tabsetor AS e ON b.cod_lot = e.codigo AND e.ativo = 'S'
				LEFT JOIN tabcargo AS f ON b.cod_cargo = f.cod_cargo
				LEFT JOIN ocupantes AS i ON b.mat_siape = i.mat_siape
				LEFT JOIN tabfunc AS u ON i.num_funcao = u.num_funcao
				WHERE b.excluido = 'N' AND b.cod_sitcad NOT IN ('02','08','15','66') ";
                    if (!empty($escolha_und))
                    {
                        if (substr($escolha_und, 0, 1) == 's')
                        {
                            $x .= "AND c.regional = '" . substr($escolha_und, 1, 1) . "' ";
                        }
                        elseif (substr($escolha_und, 0, 1) == 'g')
                        {
                            $x .= "AND e.upag = '" . substr($escolha_und, 1, 9) . "' ";
                        }
                        else
                        {
                            $x .= "AND b.cod_uorg = '$escolha_und' ";
                        }
                    }
                    /*
                      if (!empty($escolha_cargo))
                      {
                      switch ($escolha_cargo)
                      {
                      case 'medico':   $x .= "AND f.desc_cargo LIKE '%medico%' "; break;
                      case 'analista': $x .= "AND f.desc_cargo LIKE '%analista do seguro social%' "; break;
                      case 'tecnico':  $x .= "AND f.desc_cargo LIKE '%tecnico do seguro social%' "; break;
                      default:         $x .= "AND f.cod_cargo = '$escolha_cargo' "; break;
                      }
                      }
                     */
                    $x .= "
				GROUP BY b.mat_siape
				ORDER BY d.id_ger, IF(LEFT(b.cod_lot,2)='01',0,IF(SUBSTR(b.cod_lot,3,3)='150',1,2)), b.cod_lot, b.nome_serv ";

                    $oDBase->query($x);
                    $nrows = $oDBase->num_rows();

                    $arrOcorr['TOTAL'][1]         = $nrows; //$arrOcorr['TOTAL'][1] + 1;
                    $arrOcorrAusencia['TOTAL'][1] = $nrows; //$arrOcorrAusencia['TOTAL'][1] + 1;

                    /*
                      include_once( _DIR_INC_."PogProgressBar.php");
                      $objBar = new PogProgressBar( 'pb' );
                      //$objBar->setTheme( 'basic');
                      $objBar->setTheme( 'blue');
                      //$objBar->setTheme( 'green');
                      //$objBar->setTheme( 'red');
                      //$objBar->setTheme( 'ocre');
                      $objBar->draw( 'Aguarde, preparando Relatório '.$tpage );
                     */

                    $nlinha   = 0;
                    $nimoveis = 0;
                    $nund     = 0;
                    $ngex     = 0;
                    $nger     = 0;
                    $codger   = '';
                    $codgex   = '';

                    /* _________________________________________________________________________*\
                      |		PREPARA OS DADOS PARA IMPRESSÃO/EXIBIÇÃO                                |
                      \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */
                    $nome_regional = '';
                    $nome_gerencia = '';
                    $nome_cargo    = '';
                    $dia           = substr($escolha_data, 6, 4) . '-' . substr($escolha_data, 3, 2) . '-' . substr($escolha_data, 0, 2);

                    while (list($siape, $nome_serv, $nome_ger, $cod_gex, $nome_gex, $cod_lot, $cod_lot_descricao, $cod_cargo, $cargo_descricao, $sit_ocup, $cod_funcao, $entra_trab, $upag, $cod_uorg, $id_ger, $nivel) = $oDBase->fetch_array())
                    {
                        if (($sit_ocup == 'T' && ($cod_funcao == 'DAS1014' || $cod_funcao == 'DAS1015' || $cod_funcao == 'DAS1016' || $cod_funcao == 'DAS1024' || $cod_funcao == 'DAS1025' || $cod_funcao == 'DAS1026')))
                        {
                            $oco                       = 'DISPENSADOS';
                            $arrOcorrAusencia[$oco][1] = $arrOcorrAusencia[$oco][1] + 1;
                        }
                        else
                        {
                            $entra = '';
                            $x     = "
						SELECT a.entra, a.oco
						FROM ponto$mes_do_ponto AS a
						WHERE a.dia = '$dia' AND a.siape='$siape'
						ORDER BY a.siape, a.dia ";
                            $oDBase->query($x);
                            $nPtos = $oDBase->num_rows();

                            if ($nPtos == 0)
                            {
                                $oco                       = '99999';
                                $arrOcorr[$oco][1]         = $arrOcorr[$oco][1] + 1;
                                $arrOcorrAusencia[$oco][1] = $arrOcorrAusencia[$oco][1] + 1;
                                contar_ocorrencias_por_nivel($oco);
                            }
                            else
                            {
                                list( $entra, $oco ) = $oDBase->fetch_array();

                                $arrOcorr[$oco][1] = $arrOcorr[$oco][1] + 1;

                                switch ($oco)
                                {
                                    case '00000':
                                    case '00172':
                                    case '00169':
                                    case '33333':
                                    case '88888':
                                    case '99999':
                                        $arrOcorrAusencia[$oco][1] = $arrOcorrAusencia[$oco][1] + 1;
                                        break;

                                    case '00137':
                                    case '03131':
                                        $oco                       = '00137';
                                        $arrOcorrAusencia[$oco][1] = $arrOcorrAusencia[$oco][1] + 1;
                                        break;

                                    default:
                                        $oco                       = 'OUTROS';
                                        $arrOcorrAusencia[$oco][1] = $arrOcorrAusencia[$oco][1] + 1;
                                        break;
                                }
                                contar_ocorrencias_por_nivel($oco);
                            }
                            $nome_regional = $nome_ger;
                            $nome_gerencia = $nome_gex;
                            $nome_cargo    = $cargo_descricao;
                        }

                        $nlinha++;
                        $nimoveis++;
                        $nger++;
                        $ngex++;
                        $nund++;

//				$objBar->setProgress( $nlinha * 100 / $nrows, $nlinha, $fim_dados );
//				usleep( 10 );
                    } // while

                    $fim_dados = count($dados_ponto);


                    /* _________________________________________________________________________*\
                      |		R E L A T Ó R I O                                                       |
                      \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */
                    ?>
                    <style>
                        .bairro {
                            vertical-align: top;
                            border:1pt solid #DEDEBC;
                            font-family : Trebuchet MS;
                            font-size: 10px;
                            font-weight:bold;
                            color: #000099;
                        }
                        .bairro2 {
                            vertical-align: top;
                            font-family : Trebuchet MS;
                            font-size: 12px;
                            font-weight:bold;
                            color: #000099;
                        }
                    </style>
                    <br>

                    <fieldset style='width: 600px;'>
                        <table border='0' cellpadding='0' cellspacing='0' style='border-collapse: collapse' bordercolor='#A7A754;' width='600px'>
                            <?php
                            if ($escolha_und != '')
                            {
                                ?>
                                <tr style='font-size: 10px;'>
                                    <td colspan='6'><b>&nbsp;Superintendência:</b>&nbsp;<?= tratarHTML($nome_regional); ?></td>
                                </tr>
                                <?php
                                if (strlen(ltrim(rtrim($escolha_und))) != 2)
                                {
                                    ?>
                                    <tr style='font-size: 10px;'>
                                        <td colspan='6'><b>&nbsp;Gerência Executiva:</b>&nbsp;<?= tratarHTML($nome_gerencia); ?></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                <tr style='font-size: 10px;'>
                                    <td colspan='6'><b>&nbsp;Cargo:</b>&nbsp;<?= tratarHTML((trim($escolha_cargo) == '' ? 'TODOS' : $nome_cargo)); ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                            <tr style='background-color:#C6C6FF;font-size:10px;font-weight:bold;'>
                                <td>&nbsp;&nbsp;OCORRÊNCIAS - GRUPOS</td>
                                <td style='text-align:center;vertical-align:middle;width:60px;'>&nbsp;QTD&nbsp;</td>
                                <td style='text-align:center;vertical-align:middle;width:50px;'>&nbsp;PERCENTUAL&nbsp;</td>
                                <td style='text-align:center;vertical-align:middle;width:50px;font-size:10px;'>&nbsp;PERITO&nbsp;</td>
                                <td style='text-align:center;vertical-align:middle;width:50px;font-size:10px;'>&nbsp;NÍVEL SUPERIOR&nbsp;</td>
                                <td style='text-align:center;vertical-align:middle;width:50px;font-size:10px;'>&nbsp;NÍVEL INTERMEDIÁRIO&nbsp;</td>
                            </tr>
                            <?php
                            $fim_dados = count($arrOcorrAusencia);

                            $nlinha = 0;
                            foreach ($arrOcorrAusencia as $cod_ocor => $dados)
                            {
                                $nlinha++;
                                $ocorrência = retira_acentos($dados[0]);
                                $total      = $dados[1];
                                $destino    = $ocorrência;
                                $percento   = round($total * 100 / ($nrows == 0 ? 1 : $nrows), 8);

                                $perito = $dados[2];
                                $ns     = $dados[3];
                                $ni     = $dados[4];

                                ?>
                                <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)' style='vertical-align:middle;height:17px;'>
                                    <td class='bairro' style='vertical-align:middle;'>&nbsp;&nbsp;<?= tratarHTML($destino); ?></td>
                                    <td class='bairro' style='vertical-align:middle;text-align:right;'>&nbsp;<?= tratarHTML(number_format($total, 0, ',', '.')); ?>&nbsp;&nbsp;</td>
                                    <td class='bairro' style='vertical-align:middle;text-align:right;'>&nbsp;<?= tratarHTML(number_format($percento, 8, ',', '.')); ?>&nbsp;%&nbsp;&nbsp;</td>
                                    <td class='bairro' style='vertical-align:middle;text-align:right;'>&nbsp;<?= tratarHTML(number_format($perito, 0, ',', '.')); ?>&nbsp;</td>
                                    <td class='bairro' style='vertical-align:middle;text-align:right;'>&nbsp;<?= tratarHTML(number_format($ns, 0, ',', '.')); ?>&nbsp;</td>
                                    <td class='bairro' style='vertical-align:middle;text-align:right;'>&nbsp;<?= tratarHTML(number_format($ni, 0, ',', '.')); ?>&nbsp;</td>
                                </tr>
                                <?php

                                $nlinha++;

                            } // foreach

                            print $relatorio;
                            ?>
                            <tr><td colspan='6' style='border-left:0px; border-right:0px;'>&nbsp;&nbsp;</td></tr>

                            <tr style='background-color: #C6C6FF; font-size: 10px; font-weight: bold;'>
                                <td>&nbsp;&nbsp;OCORRÊNCIAS</td>
                                <td colspan='2' align='right' style='vertical-align:middle;'>&nbsp;Este Resumo:&nbsp;<a href="relatorio_paralisacoes_quadro_planilha_resumo.php?unidade=<?= tratarHTML($escolha_und); ?>&data=<?= tratarHTML($escolha_data); ?>" class='letreiro'><img src='<?= _DIR_IMAGEM_; ?>icon_excell_3d3.png' border='0' alt='Gerar Planilha:\nQuadro Resumo'></a>&nbsp;</td>
                                <td style='text-align:center;vertical-align:middle;width:50px;font-size:10px;'>&nbsp;PERITO&nbsp;</td>
                                <td style='text-align:center;vertical-align:middle;width:50px;font-size:10px;'>&nbsp;NÍVEL SUPERIOR&nbsp;</td>
                                <td style='text-align:center;vertical-align:middle;width:50px;font-size:10px;'>&nbsp;NÍVEL INTERMEDIÁRIO&nbsp;</td>
                            </tr>
                            <?php
                            $nlinha = 0;
                            foreach ($arrOcorr as $cod_ocor => $dados)
                            {
                                $nlinha++;
                                $ocorrência = retira_acentos($dados[0]);
                                $total      = $dados[1];
                                $percento   = ($total * 100 / ($nrows == 0 ? 1 : $nrows));

                                $perito = $dados[2];
                                $ns     = $dados[3];
                                $ni     = $dados[4];

                                if ($total > 0)
                                {
                                    $relatorio .= "<tr onmouseover='pinta(1,this)' onmouseout='pinta(2,this)'>\n";
                                    if ($cod_ocor == 'total')
                                    {
                                        $destino   = "&nbsp;$ocorrência&nbsp;";
                                        $texto_alt = "Planilha com os nomes\nde todos os Servidores\ncom alguma ocorrência";
                                    }
                                    else
                                    {
                                        $destino   = "<a href=\"javascript:openIFrameApl('lista_nomes','relatorio_paralisacoes_quadro_nomes.php?oco=$cod_ocor')\" class='letreiro'>&nbsp;$ocorrência&nbsp;</a>";
                                        $texto_alt = "Servidores com ocorrência:\n$ocorrência";
                                    }

                                    $relatorio .= "<td class='bairro' style='vertical-align:middle;'>$destino</td>\n";
                                    $relatorio .= "<td class='bairro' align='right' style='vertical-align:middle;'>&nbsp;" . number_format($total, 0, ',', '.') . "&nbsp;&nbsp;</td>\n";
                                    $relatorio .= "<td class='bairro' align='center' style='vertical-align:middle;'>&nbsp;<a href='relatorio_paralisacoes_quadro_planilha_nomes.php?oco=$cod_ocor' class='letreiro'><img src='" . _DIR_IMAGEM_ . "icon_excell_3d3.png' border='0' alt='$texto_alt'></a>&nbsp;</td>\n";
                                    $relatorio .= "<td class='bairro' style='vertical-align:middle;text-align:right;'>&nbsp;" . number_format($perito, 0, ',', '.') . "&nbsp;</td>\n";
                                    $relatorio .= "<td class='bairro' style='vertical-align:middle;text-align:right;'>&nbsp;" . number_format($ns, 0, ',', '.') . "&nbsp;</td>\n";
                                    $relatorio .= "<td class='bairro' style='vertical-align:middle;text-align:right;'>&nbsp;" . number_format($ni, 0, ',', '.') . "&nbsp;</td>\n";
                                    $relatorio .= "</tr>";
                                }

                                $nlinha++;

                            } // foreach

                            print $relatorio;

                            ?>
                        </table>
                    </fieldset>

                </td>
            </tr>
            <tr>
                <td align='center'>

                    <br>
                    <fieldset style='width: 800px;'>
                        <legend style='font-color: #000099;'>Servidores</legend>
                        <iframe id='lista_nomes' name='lista_nomes' width='820' height='290' src='' border='0' frameborder="0"></iframe>
                    </fieldset>

                </td>
            </tr>
        </table>
    </center>

    <script>
        $('#escolha_und').focus();
    </script>
<?php

$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

exit();

function contar_ocorrencias_por_nivel($oco)
{
    global $cargo_descricao, $nivel, $arrOcorr, $arrOcorrAusencia;

    //$arrOcorrAusencia[$oco][1] = $arrOcorrAusencia[$oco][1] + 1;

    if (!empty($cargo_descricao) && substr_count(strtoupper($cargo_descricao), 'MEDICO') > 0)
    {
        $arrOcorr[$oco][2]         = $arrOcorr[$oco][2] + 1;
        $arrOcorrAusencia[$oco][2] = $arrOcorrAusencia[$oco][2] + 1;
    }
    else if (strtoupper($nivel) == 'NS')
    {
        $arrOcorr[$oco][3]         = $arrOcorr[$oco][3] + 1;
        $arrOcorrAusencia[$oco][3] = $arrOcorrAusencia[$oco][3] + 1;
    }
    else if (strtoupper($nivel) == 'NI' || strtoupper($nivel) == 'NA')
    {
        $arrOcorr[$oco][4]         = $arrOcorr[$oco][4] + 1;
        $arrOcorrAusencia[$oco][4] = $arrOcorrAusencia[$oco][4] + 1;
    }

}



function listBoxUnidades($oDBase, $escolha_und)
{
    $listboxUnd .= "<select id='escolha_und' name='escolha_und' class='form-control' onChange='escolha();'>\n";
    //$listboxUnd .= "<option value=''>----- Selecione um item -----</option>\n";

    if ($_SESSION['sBrasil'] == "S")
    {
        $listboxUnd .= "<option value=''> BRASIL </option>\n";
        $listboxUnd .= "<option value='s0'" . ($escolha_und == 's0' ? "selected" : "") . "> Administração Central </option>\n";
        $listboxUnd .= "<option value='s1'" . ($escolha_und == 's1' ? "selected" : "") . "> Superintendência Regional Sudeste I </option>\n";
        $listboxUnd .= "<option value='s2'" . ($escolha_und == 's2' ? "selected" : "") . "> Superintendência Regional Sudeste II </option>\n";
        $listboxUnd .= "<option value='s3'" . ($escolha_und == 's3' ? "selected" : "") . "> Superintendência Regional Sul </option>\n";
        $listboxUnd .= "<option value='s4'" . ($escolha_und == 's4' ? "selected" : "") . "> Superintendência Regional Nordeste </option>\n";
        $listboxUnd .= "<option value='s5'" . ($escolha_und == 's5' ? "selected" : "") . "> Superintendência Regional Norte Centro-Oeste </option>\n";
    }
    else if ($_SESSION['sSR'] == "S")
    {
        switch ($_SESSION['sRegional'])
        {
            case '1':
                $listboxUnd .= "<option value='s1'" . ($escolha_und == 's1' ? "selected" : "") . "> Superintendência Regional Sudeste I </option>";
                break;
            case '2':
                $listboxUnd .= "<option value='s2'" . ($escolha_und == 's2' ? "selected" : "") . "> Superintendência Regional Sudeste II </option>";
                break;
            case '3':
                $listboxUnd .= "<option value='s3'" . ($escolha_und == 's3' ? "selected" : "") . "> Superintendência Regional Sul </option>";
                break;
            case '4':
                $listboxUnd .= "<option value='s4'" . ($escolha_und == 's4' ? "selected" : "") . "> Superintendência Regional Nordeste </option>";
                break;
            case '5':
                $listboxUnd .= "<option value='s5'" . ($escolha_und == 's5' ? "selected" : "") . "> Superintendência Regional Norte Centro-Oeste </option>";
                break;
        }
    }

    while (list($cod_und, $nome_und, $cod_upag, $nome_ger, $cod_gex, $nome_gex, $id_ger) = $oDBase->fetch_array())
    {
        if ($codgex != $cod_gex || $codger != $id_ger)
        {
            if (($codgex != $cod_gex && $nome_ger != $nome_gex) || $codger != $id_ger)
            {
                $listboxUnd .= "<option value=''></option>\n";
            }
            if ($codger != $id_ger)
            {
                $listboxUnd .= "<option value='' style='background-color: #E2E2E2; font-family: arial; font-size: 10; font-weight: bold; text-align: center;' disabled='true'> $nome_ger </option>\n";
                $codger     = $id_ger;
            }
            if ($codgex != $cod_gex && $nome_ger != $nome_gex)
            {
                $listboxUnd .= "<option value='g$cod_upag' style='background-color: #E2E2E2; font-family: arial; font-size: 10; font-weight: bold; text-align: center;'" . ($escolha_und == 'g' . $cod_upag ? "selected" : "") . ">GEX $nome_gex </option>\n";
                $codgex     = $cod_gex;
            }
        }
        $listboxUnd .= "<option value='$cod_und' " . ($escolha_und == $cod_und ? "selected" : "") . ">$nome_und" . (substr($nome_und, 0, 18) == 'GERENCIA-EXECUTIVA' ? ' (GABINETE)' : '') . "</option>\n";
    }
    $listboxUnd .= "</select>\n";

    return $listboxUnd;
}

function listBoxDatas($data_inicial, $data_final, $data_escolhida_invertida)
{
    $meses        = array();
    $meses['01']  = 'Janeiro';
    $meses['02']  = 'Fevereiro';
    $meses['03']  = 'Março';
    $meses['04']  = 'Abril';
    $meses['05']  = 'Maio';
    $meses['06']  = 'Junho';
    $meses['07']  = 'Julho';
    $meses['08']  = 'Agosto';
    $meses['09']  = 'Setembro';
    $meses['10']  = 'Outubro';
    $meses['11']  = 'Novembro';
    $meses['12']  = 'Dezembro';
    $listboxdatas .= "<select id='escolha_data' name='escolha_data' class='form-control' onChange='escolha();'>";
    $limite       = 0;
    $dt_dia       = substr($data_final, 6, 4);
    $dt_mes       = substr($data_final, 4, 2);
    $dt_ano       = substr($data_final, 0, 4);
    $dt_final     = $dt_ano . '-' . $dt_mes . '-' . $dt_dia;

    while ($data_final >= $data_inicial)
    {
        if ($dt_ano_mes != substr($data_final, 0, 6))
        {
            $dt_ano_mes   = substr($data_final, 0, 6);
            $listboxdatas .= "<option value='' disabled='true'>&nbsp;</option>";
            $listboxdatas .= "<option value='' style='background-color: #f0f0f0; font-weight: bold;' disabled='true'>&nbsp;" . substr($dt_ano_mes, 0, 4) . "&nbsp;-&nbsp;" . $meses[substr($dt_ano_mes, 4, 2)] . "&nbsp;</option>";
        }
        $limite++;
        $diax         = dataseca($data_final);
        $listboxdatas .= "<option value='$diax' " . ($data_final == $data_escolhida_invertida ? 'selected' : '') . ">&nbsp;$diax&nbsp;</option>";
        $data_final   = date('Ymd', mktime(0, 0, 0, $dt_mes, $dt_dia - 1, $dt_ano));
        $dt_dia--;
    }
    $listboxdatas .= "</select>";

    return $listboxdatas;
}
