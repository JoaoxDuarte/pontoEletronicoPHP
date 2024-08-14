<?php
/* _________________________________________________________________________*\
  |                                                                           |
  |		PREPARA OS DIVERSOS TIPOS DE RELATÓRIOS                                 |
  |                                                                           |
  \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */

include_once('config.php');
include_once( "class_ocorrencias_grupos.php" );

//verifica_permissao('estrategica');
session_start();

set_time_limit(0);

$sLotacao = $_SESSION["sLotacao"];

$oco_escolha   = anti_injection($_REQUEST["oco"]);
$escolha_und   = $_SESSION['sEscolha_und'];
$escolha_data  = $_SESSION['sEscolha_data'];
$escolha_cargo = $_SESSION['sEscolha_cargo'];

// instancia o banco de dados
$oDBase = new DataBase('PDO');

//
// dados básicos para seleção dos dados desejados
//
$data_inicial = $escolha_data;
$data_inicial = substr($data_inicial, 6, 4) . substr($data_inicial, 3, 2) . substr($data_inicial, 0, 2);
$data_final   = date('Ymd');

$data_escolhida_invertida = substr($escolha_data, 6, 4) . substr($escolha_data, 3, 2) . substr($escolha_data, 0, 2);

if (empty($escolha_data))
{
    $data_inicial = date('Ymd'); //substr($data_inicial,6,4).substr($data_inicial,3,2).substr($data_inicial,0,2);
    $data_final   = date('Ymd');
    $mes_do_ponto = date('mY');
}
else
{
    $data_inicial = $data_escolhida_invertida;
    $data_final   = $data_escolhida_invertida;
    $mes_do_ponto = substr($escolha_data, 3, 2) . substr($escolha_data, 6, 4);
}

/* _________________________________________________________________________*\
  |		INÍCIO DO HTML                                                          |
  \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */
?>
<!doctype html public '-//w3c//dtd html 4.0 transitional//en'>
<html>
    <head>
        <meta name='generator' content='editplus'>
        <meta name='author' content=''>
        <meta name='keywords' content=''>
        <meta name='description' content=''>
    </head>

    <body>

    <center>
        <fieldset style='width: 790px;'>

            <?php
            /* _________________________________________________________________________*\
              |		SELEÇÃO DOS DADOS                                                       |
              \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */
            $x = "
		SELECT
			b.mat_siape, b.nome_serv, d.nome_ger, c.cod_gex, c.nome_gex, b.cod_lot, IF(IFNULL(g.lot_nsiape,9)=9,e.descricao,g.lot_nsiape) AS cod_lot_descricao, b.cod_cargo, f.desc_cargo AS cargo_descricao, i.sit_ocup, u.cod_funcao, b.entra_trab, e.upag, b.cod_uorg, d.id_ger, b.nivel
		FROM servativ AS b
		LEFT JOIN lotacao_nova AS g ON b.cod_uorg = g.uorg_anterior
		LEFT JOIN dados_gex AS c ON IF(SUBSTR(b.cod_lot,4,2)='00',CONCAT(LEFT(b.cod_lot,2),'001'),IF(SUBSTR(b.cod_lot,3,3)='150',LEFT(b.cod_lot,5),CONCAT(LEFT(b.cod_lot,2),'0',SUBSTR(b.cod_lot,4,2)))) = c.cod_gex
		LEFT JOIN dados_ger AS d ON c.regional = d.id_ger
		LEFT JOIN tabsetor AS e ON b.cod_lot = e.codigo AND e.ativo = 'S'
		LEFT JOIN tabcargo AS f ON b.cod_cargo = f.cod_cargo
		LEFT JOIN ocupantes AS i ON b.mat_siape = i.mat_siape
		LEFT JOIN tabfunc AS u ON i.num_funcao = u.num_funcao
		WHERE b.excluido = 'N' AND b.cod_sitcad NOT IN ('08','15','02') ";
            if (!empty($escolha_und))
            {
                if (substr($escolha_und, 0, 1) == 's')
                {
                    $x .= "AND d.id_ger = '" . substr($escolha_und, 1, 1) . "' ";
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
            if (!empty($escolha_cargo))
            {
                /*
                  switch ($escolha_cargo)
                  {
                  case 'medico':   $x .= "AND f.desc_cargo LIKE '%medico%' "; break;
                  case 'analista': $x .= "AND f.desc_cargo LIKE '%analista do seguro social%' "; break;
                  case 'tecnico':  $x .= "AND f.desc_cargo LIKE '%tecnico do seguro social%' "; break;
                  default:         $x .= "AND f.cod_cargo = '$escolha_cargo' "; break;
                  }
                 */
            }
            $x     .= "
		GROUP BY b.mat_siape
		ORDER BY d.id_ger, IF(LEFT(b.cod_lot,2)='01',0,IF(SUBSTR(b.cod_lot,3,3)='150',1,2)), b.cod_lot, b.nome_serv ";
            $oDBase->query($x);
            $nrows = $oDBase->num_rows();

            /* _________________________________________________________________________*\
              |		PREPARA OS DADOS PARA IMPRESSÃO/EXIBIÇÃO                                |
              \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */

            // $dados_ponto[0]  : data
            // $dados_ponto[1]  : siape
            // $dados_ponto[2]  : nome do servidor
            // $dados_ponto[3]  : ocorrência
            // $dados_ponto[4]  : superintendencia
            // $dados_ponto[5]  : codigo da gerencia
            // $dados_ponto[6]  : gerencia
            // $dados_ponto[7]  : codigo da unidade
            // $dados_ponto[8]  : descricao da unidade
            // $dados_ponto[9]  : codigo do cargo
            // $dados_ponto[10] : descricao do cargo
            // $dados_ponto[11] : hora de entrada oficial
            // $dados_ponto[12] : hora de registro
            $dados_ponto = array();

            $dia = substr($escolha_data, 6, 4) . '-' . substr($escolha_data, 3, 2) . '-' . substr($escolha_data, 0, 2);
            while (list($siape, $nome_serv, $nome_ger, $cod_gex, $nome_gex, $cod_lot, $cod_lot_descricao, $cod_cargo, $cargo_descricao, $sit_ocup, $cod_funcao, $entra_trab, $upag, $cod_uorg, $nivel) = $oDBase->fetch_array())
            {
                $oServidor = selecionaServidor($siape);
                $sitcad = $oServidor->fetch_object()->sigregjur;

                ## ocorrências grupos
                $obj = new OcorrenciasGrupos();
                $codigoSemFrequenciaPadrao  = $obj->CodigoSemFrequenciaPadrao($sitcad);

                // codigos a pesquisar
                $codigo_sem_frequencia = implode(',', $codigoSemFrequenciaPadrao); //'99999';


                /*
                  if (($sit_ocup == 'T' && ($cod_funcao=='DAS1014' || $cod_funcao=='DAS1015' || $cod_funcao=='DAS1016' || $cod_funcao=='DAS1024' || $cod_funcao=='DAS1025' || $cod_funcao=='DAS1026')) || $siape=='1287194' || $siape=='1286962')
                  {
                  }
                  else
                  {
                 */
                $x = "
				SELECT a.entra, a.oco, CONCAT(a.oco,' - ',h.desc_ocorr) as descricao
				FROM ponto$mes_do_ponto AS a
				LEFT JOIN tabocfre  AS h ON a.oco = h.siapecad
				WHERE a.dia = '$dia' AND a.siape = '$siape' ";

                if ($oco_escolha != $codigo_sem_frequencia)
                {
                    $x .= "AND a.oco = '$oco_escolha' ";
                }
                $x     .= "ORDER BY a.siape, a.dia ";
                $oDBase->query($x);
                $nPtos = $oDBase->num_rows();
                list( $entra, $oco, $descricao ) = $oDBase->fetch_array();

                if ($nPtos == 0)
                {
                    if ($oco_escolha == $codigo_sem_frequencia)
                    {
                        $dados_ponto[] = array(databarra($dia), $siape, $nome_serv, $codigo_sem_frequencia.' - SEM FREQUENCIA', $nome_ger, $cod_gex, $nome_gex, $cod_lot, $cod_lot_descricao, $cod_cargo, $cargo_descricao, $entra_trab, $entra);
                    }
                }
                elseif ($oco == $oco_escolha)
                {
                    $dados_ponto[] = array(databarra($dia), $siape, $nome_serv, $descricao, $nome_ger, $cod_gex, $nome_gex, $cod_lot, $cod_lot_descricao, $cod_cargo, $cargo_descricao, $entra_trab, $entra);
                }
                /*
                  }
                 */
            } // while

            $fim_dados = count($dados_ponto);


            /* _________________________________________________________________________*\
              |		EXIBE UMA BARRA DE PROGRESSO DA PESQUISA QUE ESTÁ SENDO REALIZADA       |
              \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */
            if ($nrows > 0)
            {
                include_once( _DIR_INC_ . "PogProgressBar.php");
                $objBar = new PogProgressBar('pb');
                //$objBar->setTheme( 'basic');
                $objBar->setTheme('blue');
                //$objBar->setTheme( 'green');
                //$objBar->setTheme( 'red');
                //$objBar->setTheme( 'ocre');
                $objBar->draw('Aguarde, preparando Relatório ' . $tpage);
            }


            /* _________________________________________________________________________*\
              |		R E L A T Ó R I O                                                       |
              \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */
            $relatorio = "
		<style>
		.bairro {
				vertical-align: top;
				border:1pt solid #DEDEBC;
				font-family : Trebuchet MS;
				font-size: 10px;
				font-weight:bold;
				color: #000099;
		}
		</style>
		<br>
		<table border='0' cellpadding='0' cellspacing='0' style='border-collapse: collapse' bordercolor='#A7A754;' width='790px'> ";

            $cabecalho = "
		<tr style='height: 15px;'>
		<td align='center' style='background-color:#DDDDFF; width: 7%;'><b>Siape</b></td>
		<td align='center' style='background-color:#DDDDFF; width: 35%;'><b>Nome</b></td>
		<td align='center' style='background-color:#DDDDFF; width: 48%;'><b>Unidade</b></td>
		</tr>\n";

            if ($nrows > 0)
            {

                $nlinha   = 0;
                $nimoveis = 0;
                $nund     = 0;
                $ngex     = 0;
                $nger     = 0;
                $codger   = '';
                $codgex   = '';

                for ($i = 0; $i < $fim_dados; $i++)
                {

                    $data              = $dados_ponto[$i][0];
                    $siape             = $dados_ponto[$i][1];
                    $nome_serv         = $dados_ponto[$i][2];
                    $ocorrência        = $dados_ponto[$i][3];
                    $nome_ger          = retira_acentos($dados_ponto[$i][4]);
                    $cod_gex           = $dados_ponto[$i][5];
                    $nome_gex          = retira_acentos($dados_ponto[$i][6]);
                    $cod_lot           = $dados_ponto[$i][7];
                    $cod_lot_descricao = retira_acentos($dados_ponto[$i][8]);

                    if ($i == 0)
                    {
                        $relatorio .= "<tr><td colspan='3' style='color: #004080; font-family: arial; font-size: 12; font-weight: bold;'>$ocorrência</td></tr>\n";
                    }

                    if ($codgex != $cod_gex || $codger != $id_ger)
                    {
                        if (($codgex != $cod_gex && $nome_ger != $nome_gex) || $codger != $nome_ger)
                        {
                            $relatorio .= "<tr><td colspan='3'>&nbsp;</td></tr>\n";
                        }
                        if ($codger != $nome_ger)
                        {
                            $relatorio .= "<tr><td colspan='3' style='background-color: #E2E2E2; font-family: arial; font-size: 10; font-weight: bold;'>$nome_ger</td></tr>\n";
                            $codger    = $id_ger;
                        }
                        if ($codgex != $cod_gex && $nome_ger != $nome_gex)
                        {
                            $relatorio .= "<tr><td colspan='3' style='background-color: #E2E2E2; font-family: arial; font-size: 10; font-weight: bold;'>$nome_gex</td></tr>\n";
                            $codgex    = $cod_gex;
                        }
                        $relatorio .= $cabecalho;
                    }

                    $relatorio .= "
				<tr onmouseover='pinta(1,this)' onmouseout='pinta(2,this)'>
					<td class='bairro' align='center'>&nbsp;$siape&nbsp;</td>
					<td class='bairro'>&nbsp;$nome_serv&nbsp;</td>
					<td class='bairro'>&nbsp;$cod_lot_descricao&nbsp;</td>
				</tr>";

                    $nlinha++;
                    $nimoveis++;
                    $nger++;
                    $ngex++;
                    $nund++;

                    $objBar->setProgress($nlinha * 100 / $fim_dados, $nlinha, $fim_dados);
                    usleep(10);

                    $fim_quebra_ger = $nome_ger;
                    $fim_quebra_gex = $nome_gex;
                    $fim_quebra_und = $quebra_und_descricao;
                } // for
            } // numrows

            print $relatorio;

            print "
		</table>
		<br>
		<span style='font-family:tahoma;font-size:9pt'>&nbsp;Nº de Servidores: <b>" . number_format($fim_dados, 0, ',', '.') . "</b></span>";

            if ($nrows > 0)
            {
                $objBar->hide();
            }
            ?>

            </body>
            </html>
