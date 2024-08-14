<?php
/* _________________________________________________________________________*\
  |                                                                           |
  |		PREPARA OS DIVERSOS TIPOS DE RELATÓRIOS                                 |
  |                                                                           |
  \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */

include_once('config.php');

verifica_permissao('estrategica');

set_time_limit(0);
flush();

$sLotacao = $_SESSION["sLotacao"];

$escolha_ger  = anti_injection($_REQUEST["escolha_ger"]);
$escolha_gex  = anti_injection($_REQUEST["escolha_gex"]);
$escolha_data = anti_injection($_REQUEST["escolha_data"]);
$escolha_tipo = anti_injection($_REQUEST["escolha_tipo"]);
$escolha_tipo = (empty($escolha_tipo) ? '2' : $escolha_tipo);

if (empty($escolha_ger) && empty($escolha_gex))
{
    $modo = anti_injection($_REQUEST["modo"]);
    $modo = (empty($modo) ? '1' : $modo);
}
else if (!empty($escolha_ger))
{
    $modo = 3;
}
else if (!empty($escolha_gex))
{
    $modo = 4;
}

$quebra = $modo;

// instancia o banco de dados
$oDBase = new DataBase('PDO');

//
// dados básicos para seleção dos dados desejados
//
$mes_do_ponto         = '072015';
$data_inicial         = '07/07/2015';
$hora_limite_minimo   = '11:00:00';
$codigo_da_ocorrencia = '00137';
$cargos               = ""; //"'005012','009001','424001','434001','435001','480151','811001','812001'";

$data_inicial = substr($data_inicial, 6, 4) . substr($data_inicial, 3, 2) . substr($data_inicial, 0, 2);
$data_final   = date('Ymd');

$data_escolhida_invertida = substr($escolha_data, 6, 4) . substr($escolha_data, 3, 2) . substr($escolha_data, 0, 2);

$listboxdatas .= "<input type='hidden' id='modo' name='modo' value='$modo'>";
$listboxdatas .= "<select id='escolha_data' name='escolha_data' onChange='escolha();'>";
for ($dt = $data_final; $dt >= $data_inicial; $dt--)
{
    $diax         = substr($dt, 6, 2) . '/' . substr($dt, 4, 2) . '/' . substr($dt, 0, 4);
    $listboxdatas .= "<option value='$diax' " . (($dt == $data_final && empty($data_escolhida_invertida)) || $dt == $data_escolhida_invertida ? 'selected' : '') . ">&nbsp;$diax&nbsp;</option>";
}
$listboxdatas .= "</select>";

if (empty($escolha_data))
{
    $data_inicial = date('Ymd'); //substr($data_inicial,6,4).substr($data_inicial,3,2).substr($data_inicial,0,2);
    $data_final   = date('Ymd');
}
else
{
    $data_inicial = $data_escolhida_invertida;
    $data_final   = $data_escolhida_invertida;
    $mes_do_ponto = substr($escolha_data, 3, 2) . substr($escolha_data, 6, 4);
}

// nome da gerencia
$codgex  = '';
$nomegex = '';
$ry      = select_dadosgex($sLotacao, $codgex, $nomegex);

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
        <script type='text/javascript' src="<?= _DIR_JS_; ?>jquery-2.2.0.min.js"></script>
        <script type='text/javascript' src="<?= _DIR_JS_; ?>funcoes.js"></script>
        <script type='text/javascript' src="<?= _DIR_CSS_; ?>new/js/bootstrap.min.js"></script>
        <script type='text/javascript' src="<?= _DIR_CSS_; ?>new/js/bootstrap-dialog.min.js"></script>

        <script language='javascript'>
            function escolha()
            {
                $('#form1').submit();
            }
        </script>
    </head>
    <body>
        <?php
        $tpage   = '';
        $listbox = '';
        switch ($modo)
        {
            case '1':
                $tpage .= "por BRASIL";
                break;

            case '2':
                $tpage .= "por Administração Central";
                break;

            case '3':
                $tpage   .= "por Superintendência Regional";
                $x       = "
			SELECT a.id_ger, a.cod_ger, a.nome_ger
			FROM dados_ger AS a
			WHERE a.ativo = 1 " . ($modo == 2 ? "AND a.id_ger = 0 " : "AND a.id_ger <> 0 " ) . "
			ORDER BY a.id_ger ";
                $oDBase->query($x);
                $nSuper  = $oDBase->num_rows();
                $listbox .= "<input type='hidden' id='modo' name='modo' value='$modo'>";
                $listbox .= "<select id='escolha_ger' name='escolha_ger' onChange='escolha();'>";
                $listbox .= "<option value=''>----- Selecione um item -----</option>";
                while (list($id, $cod_ger, $nome_ger) = $oDBase->fetch_array())
                {
                    $listbox .= "<option value='$id' " . ($escolha_ger == $id ? "selected" : "") . ">$nome_ger</option>";
                }
                $listbox .= "</select>";
                break;

            case '4':
                $tpage   .= "por Gerência Executiva";
                $x       = "
			SELECT a.cod_gex, a.nome_gex
			FROM dados_gex AS a
			WHERE a.ativo = 1 AND LEFT(a.cod_gex,2)<>'01'
			ORDER BY a.nome_gex ";
                $oDBase->query($x);
                $nGex    = $oDBase->num_rows();
                $listbox .= "<input type='hidden' id='modo' name='modo' value='$modo'>";
                $listbox .= "<select id='escolha_gex' name='escolha_gex' onChange='escolha();'>";
                $listbox .= "<option value=''>----- Selecione um item -----</option>";
                while (list($cod_gex, $nome_gex) = $oDBase->fetch_array())
                {
                    $listbox .= "<option value='$cod_gex' " . ($escolha_gex == $cod_gex ? "selected" : "") . ">$nome_gex</option>";
                }
                $listbox .= "</select>";
                break;
        }

        titulo_in_pagina('Gestão Estratégica » Paralisações/Faltas por Greve');

        print "
		<center>
		<fieldset style='width: 800px;'>
			<form action='" . $_SERVER['PHP_SELF'] . "' id='form1' name='form1'>
			<table border='0' cellpadding='0' cellspacing='0'>
				<tr>
					<td valign='bottom' height='40px'><b>" . substr($tpage, 4, strlen($tpage) - 4) . (empty($listbox) ? '' : ':&nbsp;') . "</b></td>
					<td valign='bottom' height='40px'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
					<td valign='bottom' height='40px'><b>Data:&nbsp;</b></td>
					<td valign='bottom' height='40px'>&nbsp;$listboxdatas</td>
				</tr>
			</table>
			<table border='0' cellpadding='0' cellspacing='0'>
				<tr>
					<td valign='bottom' height='40px'>&nbsp</td>
					<td valign='bottom' height='40px'>&nbsp;Tipo:&nbsp;</td>
					<td valign='bottom' height='40px' colspan='3'>
						<select id='escolha_tipo' name='escolha_tipo' onChange='escolha();'>
							<option value='0' " . ($escolha_tipo == '0' ? 'selected' : '') . ">&nbsp;Todos os registros&nbsp;</option>
							<option value='1' " . ($escolha_tipo == '1' ? 'selected' : '') . ">&nbsp;Sem Registro de Frequência&nbsp;</option>
							<option value='2' " . ($escolha_tipo == '2' ? 'selected' : '') . ">&nbsp;Paralisações/Faltas e/ou Registro após as 11hs&nbsp;</option>
							<option value='3' " . ($escolha_tipo == '3' ? 'selected' : '') . ">&nbsp;Paralisações/Faltas e/ou Registro após as 11hs e antes do Horário Determinado&nbsp;</option>
							<option value='4' " . ($escolha_tipo == '4' ? 'selected' : '') . ">&nbsp;Paralisações/Faltas e/ou Registro após as 11hs e após o Horário Determinado&nbsp;</option>
							<option value='5' " . ($escolha_tipo == '5' ? 'selected' : '') . ">&nbsp;Paralisações/Faltas e/ou Registro após as 11hs, sem Horário Determinado (00:00:00)&nbsp;</option>
							<option value='6' " . ($escolha_tipo == '6' ? 'selected' : '') . ">&nbsp;Paralisações/Faltas por Greve&nbsp;</option>
							<option value='7' " . ($escolha_tipo == '7' ? 'selected' : '') . ">&nbsp;Quantitativo por Lotação&nbsp;</option>
						</select>
					</td>
				</tr>
			</table>
			</form>
		</fieldset>\n";

        /* _________________________________________________________________________*\
          |		SELEÇÃO DOS DADOS                                                       |
          \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */
        $x       = "
	SELECT
		b.mat_siape, b.nome_serv, d.nome_ger, c.cod_gex, c.nome_gex, b.cod_lot, g.descricao AS cod_lot_descricao, b.cod_cargo, f.desc_cargo AS cargo_descricao, i.sit_ocup, u.cod_funcao, b.entra_trab
	FROM servativ AS b
	LEFT JOIN tabsetor AS g ON b.cod_lot = g.codigo
	LEFT JOIN dados_gex AS c ON
		IF(SUBSTR(b.cod_lot,4,2)='00',CONCAT(LEFT(b.cod_lot,2),'001'),IF(SUBSTR(b.cod_lot,3,3)='150',LEFT(b.cod_lot,5),CONCAT(LEFT(b.cod_lot,2),'0',SUBSTR(b.cod_lot,4,2)))) = c.cod_gex
	LEFT JOIN dados_ger AS d ON c.regional = d.id_ger
	LEFT JOIN tabsetor AS e ON b.cod_lot = e.codigo AND e.ativo = 'S'
	LEFT JOIN tabcargo AS f ON b.cod_cargo = f.cod_cargo
	LEFT JOIN ocupantes AS i ON b.mat_siape = i.mat_siape
	LEFT JOIN tabfunc AS u ON i.num_funcao = u.num_funcao ";
        $where   = "WHERE b.excluido = 'N' AND IFNULL(d.nome_ger,9) <> 9 AND cod_sitcad IN ('01') " . (empty($escolha_ger) ? "" : "	AND d.id_ger = '$escolha_ger' ") . (empty($escolha_gex) ? "" : "	AND c.cod_gex = '$escolha_gex' ");
        //$where = "WHERE f.desc_cargo LIKE '%medicos%' AND b.excluido = 'N' AND IFNULL(d.nome_ger,9) <> 9 AND cod_sitcad IN ('01') ".(empty($escolha_ger) ? "" : "	AND d.id_ger = '$escolha_ger' ").(empty($escolha_gex) ? "" : "	AND c.cod_gex = '$escolha_gex' ");
        $orderby = "ORDER BY d.id_ger, IF(LEFT(b.cod_lot,2)='01',0,IF(SUBSTR(b.cod_lot,3,3)='150',1,2)), b.cod_lot, b.nome_serv ";

        switch ($modo)
        {
            case 1:
                $where .= "";
                break;
            case 2:
                $where .= " AND LEFT(b.cod_lot,2)='01' ";
                break;
            default:
                $where .= " AND LEFT(b.cod_lot,2)<>'01' ";
                break;
        }

        $x .= $where;
        $x .= $orderby;

        $rxServ = $oDBase->query($x);
        $nrows  = $oDBase->num_rows();


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
        $dados_ponto                 = array();
        $sem_registro_frequencia     = 0;
        $outros_registros_frequencia = 0;

        while (list($siape, $nome_serv, $nome_ger, $cod_gex, $nome_gex, $cod_lot, $cod_lot_descricao, $cod_cargo, $cargo_descricao, $sit_ocup, $cod_funcao, $entra_trab) = $oDBase->fetch_array())
        {
            if (($sit_ocup == 'T' && ($cod_funcao == 'DAS1014' || $cod_funcao == 'DAS1015' || $cod_funcao == 'DAS1016' || $cod_funcao == 'DAS1024' || $cod_funcao == 'DAS1025' || $cod_funcao == 'DAS1026')) || $siape == '1287194' || $siape == '1286962')
            {

            }
            else
            {
                for ($dt = $data_inicial; $dt <= $data_final; $dt++)
                {
                    $dia   = substr($dt, 0, 4) . '-' . substr($dt, 4, 2) . '-' . substr($dt, 6, 2);
                    $entra = '';
                    $x     = "
				SELECT a.entra, a.oco, CONCAT(a.oco,' - ',h.desc_ocorr) as descricao
				FROM ponto$mes_do_ponto AS a
				LEFT JOIN tabocfre  AS h ON a.oco = h.siapecad
				WHERE a.dia = '$dia' AND a.siape = '$siape'
				ORDER BY a.siape, a.dia ";
                    $rxPto = $oDBase->query($x);
                    $nPtos = $oDBase->num_rows();
                    list( $entra, $oco, $descricao ) = $oDBase->fetch_array();

                    $teste_horario = false;
                    switch ($escolha_tipo)
                    {
                        // Todos os registros
                        case '0': $teste_horario = true;
                            break;

                        // Sem Registro de Frequência
                        case '1': $teste_horario = false;
                            break;

                        // Paralisações/Faltas e/ou Registro após as 11hs
                        case '2': $teste_horario = ($oco == $codigo_da_ocorrencia || $entra > $hora_limite_minimo);
                            break;

                        // Paralisações/Faltas e/ou Registro após as 11hs e antes do Horário Determinado
                        case '3': $teste_horario = ($oco == $codigo_da_ocorrencia || ($entra > $hora_limite_minimo && $entra < $entra_trab));
                            break;

                        // Paralisações/Faltas e/ou Registro após as 11hs e após o Horário Determinado
                        case '4': $teste_horario = ($oco == $codigo_da_ocorrencia || $entra > $hora_limite_minimo || $entra > $entra_trab);
                            break;

                        // Paralisações/Faltas e/ou Registro após as 11hs, sem Horário Determinado (00:00:00)
                        case '5': $teste_horario = (($oco == $codigo_da_ocorrencia || $entra > $hora_limite_minimo) && $entra_trab == '00:00:00');
                            break;

                        // Paralisações/Faltas
                        case '6': $teste_horario = ($oco == $codigo_da_ocorrencia);
                            break;
                    }

                    /*
                      if ($_SESSION['sMatricula']=='0881838')
                      {
                      print '<br>['.($teste_horario==true?'true':'false').'] = ((['.$oco.'] == ['.$codigo_da_ocorrencia.'] || ['.$entra.'] > ['.$hora_limite_minimo.']) && ['.$entra_trab.'] == \'00:00:00\')';
                      }
                     */

                    if ($nPtos == 0 && $escolha_tipo != '6')
                    {
                        if ($escolha_tipo != '5' || ($escolha_tipo == '5' && $entra_trab == '00:00:00'))
                        {
                            $sem_registro_frequencia++;
                            $dados_ponto[] = array(databarra($dia), $siape, $nome_serv, 'Sem registro de frequência', $nome_ger, $cod_gex, $nome_gex, $cod_lot, $cod_lot_descricao, $cod_cargo, $cargo_descricao, $entra_trab, $entra);
                        }
                    }
                    else
                    {

                        if ($teste_horario == true)
                        {
                            $outros_registros_frequencia++;
                            $dados_ponto[] = array(databarra($dia), $siape, $nome_serv, $descricao, $nome_ger, $cod_gex, $nome_gex, $cod_lot, $cod_lot_descricao, $cod_cargo, $cargo_descricao, $entra_trab, $entra);
                        }
                    }
                }
            }
        } // while

        $fim_dados = count($dados_ponto);


        /* _________________________________________________________________________*\
          |		EXIBE UMA BARRA DE PROGRESSO DA PESQUISA QUE ESTÁ SENDO REALIZADA       |
          \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */
        if ($nrows > 0)
        {
            include_once(_DIR_INC_ . "PogProgressBar.php");
            $objBar = new PogProgressBar('pb');
            //$objBar->setTheme( 'basic');
            //$objBar->setTheme( 'blue');
            $objBar->setTheme('green');
            //$objBar->setTheme( 'red');
            //$objBar->setTheme( 'ocre');
            $objBar->draw('Aguarde, preparando Relatório ' . $tpage);
        }


        /* _________________________________________________________________________*\
          |		R E L A T Ó R I O                                                       |
          \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */
        $style_rows = "
	<style>
	.bairro { vertical-align: top; border:1pt solid #DEDEBC; }
	</style>
	<br>
	<span style='font-family:tahoma;font-size:9pt'>&nbsp;Nº de Servidores: <b>" . number_format($fim_dados, 0, ',', '.') . "</b></span><br>
	<center>
	<table border='0' cellpadding='0' cellspacing='0' width='800px'>
	<tr>
	<td align='right' width='100%'>Sem Registro de Frequência:</td>
	<td align='right'>&nbsp;" . number_format($sem_registro_frequencia, 0, ',', '.') . "</td>
	</tr>
	<tr>
	<td align='right'>Registro de Frequência:</td>
	<td align='right'>&nbsp;" . number_format($outros_registros_frequencia, 0, ',', '.') . "</td>
	</tr>
	<tr>
	<td align='right'>Total:</td>
	<td align='right'>&nbsp;" . number_format(($sem_registro_frequencia + $outros_registros_frequencia), 0, ',', '.') . "</td>
	</tr>
	</table>
	</center>";

        $cabec_relatorio = "
	<table border='1' cellpadding='0' cellspacing='0' style='border-collapse: collapse' bordercolor='#A7A754;' width='820px'>
	<tr style='height: 20px;'>
	<td style='background-color:#DEDEBC; width: 20px; text-align: center'><b>Dia</b></td>
	<td style='background-color:#DEDEBC; width: 20px;'><b>&nbsp;Siape</b></td>
	<td style='background-color:#DEDEBC; width: 300px;'><b>&nbsp;Nome</b></td>
	<td style='background-color:#DEDEBC; width: 33px; text-align: center'><b>Horário<br>Oficial</b></td>
	<td style='background-color:#DEDEBC; width: 33px; text-align: center'><b>Registrado<br>às</b></td>
	<td style='background-color:#DEDEBC; width: 300px;'><b>&nbsp;Ocorrência</b></b></td>
	</tr>\n";

        $relatorio = $style_rows;
        if ($quebra <= 0)
        {
            $relatorio .= $cabec_relatorio;
        }

        if ($nrows > 0)
        {

            $nlinha     = 0;
            $nimoveis   = 0;
            $nund       = 0;
            $ngex       = 0;
            $nger       = 0;
            $quebra_ger = '';
            $quebra_gex = '';
            $quebra_und = '';

            for ($i = 0; $i < $fim_dados; $i++)
            {
                $data              = $dados_ponto[$i][0];
                $siape             = $dados_ponto[$i][1];
                $nome_serv         = $dados_ponto[$i][2];
                $ocorrência        = $dados_ponto[$i][3];
                $nome_ger          = retira_acentos($dados_ponto[$i][4]);
                $nome_gex          = retira_acentos($dados_ponto[$i][6]);
                $cod_lot           = $dados_ponto[$i][7];
                $cod_lot_descricao = retira_acentos($dados_ponto[$i][8]);
                $entra_trab        = ($dados_ponto[$i][11] == '00:00:00' || empty($dados_ponto[$i][11]) ? '----' : $dados_ponto[$i][11]);
                $entra             = ($dados_ponto[$i][12] == '00:00:00' || empty($dados_ponto[$i][12]) ? '----' : $dados_ponto[$i][12]);

                if (empty($quebra_ger) || $quebra_ger <> $nome_ger)
                {
                    if (!empty($quebra_ger) && $quebra_ger <> $nome_ger)
                    {
                        $relatorio .= "
					<table border='0' cellpadding='0' cellspacing='0' width='800px'>
					<tr>
					<td align='right' style='font-family:verdana; font-size:10pt; font-weight:bold; color:#737373;'><small>Total " . strtoupper($quebra_und_descricao) . ": " . number_format($nund, 0, ',', '.') . "</small></td>
					</tr>
					</table><br>\n";
                        $nund      = 0;

                        $relatorio .= "
					<table border='0' cellpadding='0' cellspacing='0' width='800px'>
					<tr>
					<td align='right' style='font-family:verdana; font-size:10pt; font-weight:bold; color:#737373;'><small>Total " . strtoupper($quebra_gex) . ": " . number_format($ngex, 0, ',', '.') . "</small></td>
					</tr>
					</table></fieldset><br>\n";
                        $ngex      = 0;

                        $relatorio .= "
					<table border='0' cellpadding='0' cellspacing='0' width='800px'>
					<tr>
					<td align='right' style='font-family:verdana; font-size:10pt; font-weight:bold; color:#737373;'><small>Total " . strtoupper($quebra_ger) . ": " . number_format($nger, 0, ',', '.') . "</small></td>
					</tr>
					</table>
					<br>\n";
                        $nger      = 0;
                    }
                    $relatorio  .= "
				<br>
				<table border='0' cellpadding='0' cellspacing='0' width='800px'>
				<tr>
				<td align='left' style='font-family:verdana; font-size:10pt; font-weight:bold; color:#0070DF;'>" . titulo_in_pagina(strtoupper($nome_ger), true) . "</td>
				</tr>
				</table><br>\n";
                    $quebra_ger = $nome_ger;
                }

                if (empty($quebra_gex) || $quebra_gex <> $nome_gex)
                {
                    if (!empty($quebra_gex) && $quebra_gex <> $nome_gex && $ngex != 0)
                    {
                        $relatorio .= "
					<table border='0' cellpadding='0' cellspacing='0' width='800px'>
					<tr>
					<td align='right' style='font-family:verdana; font-size:10pt; font-weight:bold; color:#737373;'><small>(2)Total " . strtoupper($quebra_und_descricao) . ": " . number_format($nund, 0, ',', '.') . "</small></td>
					</tr>
					</table><br>\n";
                        $nund      = 0;

                        $relatorio .= "
					<table border='0' cellpadding='0' cellspacing='0' width='800px'>
					<tr>
					<td align='right' style='font-family:verdana; font-size:10pt; font-weight:bold; color:#737373;'><small>Total " . strtoupper($quebra_gex) . ": " . number_format($ngex, 0, ',', '.') . "</small></td>
					</tr>
					</table></fieldset><br>\n";
                        $ngex      = 0;
                    }
                    $relatorio  .= "
				<br>
				<fieldset style='width: 800px;'>
				<table border='0' cellpadding='0' cellspacing='0' width='800px'>
				<tr>
				<td align='left' style='font-family:verdana; font-size:10pt; font-weight:bold; color:#737373;'>" . strtoupper($nome_gex) . "</td>
				</tr>
				</table><br>\n";
                    $quebra_gex = $nome_gex;
                }

                if (empty($quebra_und) || $quebra_und <> $cod_lot)
                {
                    if (!empty($quebra_und) && $quebra_und <> $cod_lot && $nund != 0)
                    {
                        $relatorio .= "
					<table border='0' cellpadding='0' cellspacing='0' width='800px'>
					<tr>
					<td align='right' style='font-family:verdana; font-size:10pt; font-weight:bold; color:#737373;'><small>Total " . strtoupper($quebra_und_descricao) . ": " . number_format($nund, 0, ',', '.') . "</small></td>
					</tr>
					</table><br>\n";
                        $nund      = 0;
                    }
                    $relatorio            .= "
				<table border='0' cellpadding='0' cellspacing='0' width='800px'>
				<tr>
				<td align='left' style='font-family:verdana; font-size:10pt; font-weight:normal; color:#0079F2;'>" . strtoupper($cod_lot_descricao) . "</td>
				</tr>
				</table>\n";
                    $relatorio            .= ($escolha_tipo != '7' ? $cabec_relatorio : '');
                    $quebra_und           = $cod_lot;
                    $quebra_und_descricao = $cod_lot_descricao;
                }

                if ($escolha_tipo != '7')
                {
                    $relatorio .= "
				<tr onmouseover='pinta(1,this)' onmouseout='pinta(2,this)'>
				<td class='bairro' style='vertical-align: top;'>&nbsp;$data&nbsp;</td>
				<td class='bairro'>&nbsp;$siape&nbsp;</td>
				<td class='bairro'>&nbsp;$nome_serv&nbsp;</td>
				<td class='bairro' style='text-align: center;'>&nbsp;$entra_trab&nbsp;</td>
				<td class='bairro' style='text-align: center;'>&nbsp;$entra&nbsp;</td>
				<td class='bairro'>&nbsp;$ocorrência&nbsp;</td>
				</tr>";
                }

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

        if (!empty($quebra_und))
        {
            print "
		<table border='0' cellpadding='0' cellspacing='0' width='800px'>
		<tr>
		<td align='right' style='font-family:verdana; font-size:10pt; font-weight:bold; color:#737373;'><small>Total " . strtoupper($fim_quebra_und) . ": " . number_format($nund, 0, ',', '.') . "</small></td>
		</tr>
		</table><br>\n";
        }
        if (!empty($quebra_gex))
        {
            print "
		<table border='0' cellpadding='0' cellspacing='0' width='800px'>
		<tr>
		<td align='right' style='font-family:verdana; font-size:10pt; font-weight:bold; color:#737373;'><small>Total " . strtoupper($fim_quebra_gex) . ": " . number_format($ngex, 0, ',', '.') . "</small></td>
		</tr>
		</table>\n";
        }
        if (!empty($quebra_ger) && $modo <> '4')
        {
            print "
		</fieldset>
		<br>
		<table border='0' cellpadding='0' cellspacing='0' width='800px'>
		<tr>
		<td align='right' style='font-family:verdana; font-size:10pt; font-weight:bold; color:#737373;'><small>Total " . strtoupper($fim_quebra_ger) . ": " . number_format($nger, 0, ',', '.') . "</small></td>
		</tr>
		</table>\n";
        }

        print "
	</table>
	<br>
	<span style='font-family:tahoma;font-size:9pt'>&nbsp;Nº de Servidores: <b>" . number_format($fim_dados, 0, ',', '.') . "</b></span>";

        if ($nrows > 0)
        {
            $objBar->hide();
        }
        ?>

    </form>

    <script>
            var modo = '<?= $modo; ?>';
            switch (modo)
            {
                case '1':
                case '2':
                    $('#escolha_data').focus();
                    break;
                case '3':
                    $('#escolha_ger').focus();
                    break;
                case '4':
                    $('#escolha_gex').focus();
                    break;
            }
    </script>

</body>
</html>
