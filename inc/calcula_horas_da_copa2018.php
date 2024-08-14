<?php

class CopaDoMundo2018
{
    /*
     * Parametros
     */
    private $siape;
    private $ano;
    private $codigos_creditos; // códigos de ocorrências - créditos
    private $codigos_debitos;  // códigos de ocorrências - débitos
    private $compensacao_inicio; // data de início para compensar
    private $compensacao_fim;    // data de término da compensação
    private $valores;    // data de término da compensação

    /*
     * Função Construtora
     */
    public function __construct()
    {
        $this->siape = $_SESSION['sMatricula'];
        $this->ano = 2018;
        $this->codigos_creditos = "'92018'";
        $this->codigos_debitos = "'62018'";
        $this->compensacao_inicio = '2018-06-18';
        $this->compensacao_fim = '2018-10-31';

        // Mensagem SEGEP/MP n° 555290/2014, que trata da prorrogação do prazo
        // previsto no comunica 554955 - Compensação das horas não trabalhadas
        // em decorrência dos jogos da Copa.
        //$this->compensacao_fim = '2018-10-31';
    }

    /*
     * Setters
     */
    public function setAno($valor = '')
    {
        $this->ano = $valor;
    }

    public function setSiape($valor = '')
    {
        $this->siape = $valor;
    }

    public function setValores($valor = '')
    {
        $this->valores = $valor;
    }

    public function setCompensacaoFim($valor = '')
    {
        $this->compensacao_fim = $valor;
    }

    public function setCompensacaoInicio($valor = '')
    {
        $this->compensacao_inicio = $valor;
    }

    /*
     * Getters
     */
    public function getAno()
    {
        return $this->ano;
    }

    public function getSiape()
    {
        return $this->siape;
    }

    public function getValores()
    {
        return $this->valores;
    }

    public function getCompensacaoFim()
    {
        return $this->compensacao_fim;
    }

    public function getCompensacaoInicio()
    {
        return $this->compensacao_inicio;
    }

    /*
     * Métodos
     */

    /*
     * Saldos da Copa 2018
     */
    public function saldosCopaMundo2018($print = true)
    {
        /* JUNHO     */ list( $copa06, $tcre06, $tdeb06 ) = $this->somaCopaDoMundo("06");
        /* JULHO     */ list( $copa07, $tcre07, $tdeb07 ) = $this->somaCopaDoMundo("07");
        /* AGOSTO    */ list( $copa08, $tcre08, $tdeb08 ) = $this->somaCopaDoMundo("08");
        /* SETEMBRO  */ list( $copa09, $tcre09, $tdeb09 ) = $this->somaCopaDoMundo("09");
        /* OUTUBRO   */ list( $copa10, $tcre10, $tdeb10 ) = $this->somaCopaDoMundo("10");

        // verifica se há dados de crédito ou débito
        $copa = ($copa06 + $copa07 + $copa08 + $copa09 + $copa10);

        //calculando o resultado - crédito
        $totcre = ($tcre06 + $tcre07 + $tcre08 + $tcre09 + $tcre10);

        //calculando o resultado - débito
        $totdeb = ($tdeb06 + $tdeb07 + $tdeb08 + $tdeb09 + $tdeb10);

        // dados
        $this->valores = array();
        $this->valores[] = array(
            'Horas devidas (62018)',
            $this->exibeHoras($tdeb06),
            $this->exibeHoras($tdeb07),
            $this->exibeHoras($tdeb08),
            $this->exibeHoras($tdeb09),
            $this->exibeHoras($tdeb10),
            $this->exibeHoras($totdeb)
        );
        $this->valores[] = array(
            'Horas excedentes (92018)',
            $this->exibeHoras($tcre06, '92018'),
            $this->exibeHoras($tcre07, '92018'),
            $this->exibeHoras($tcre08, '92018'),
            $this->exibeHoras($tcre09, '92018'),
            $this->exibeHoras($tcre10, '92018'),
            $this->exibeHoras($totcre, '92018')
        );

        $this->valores[] = array(
            'Total por mês',
            $this->exibeSubTotal($tcre06, $tdeb06),
            $this->exibeSubTotal($tcre07, $tdeb07),
            $this->exibeSubTotal($tcre08, $tdeb08),
            $this->exibeSubTotal($tcre09, $tdeb09),
            $this->exibeSubTotal($tcre10, $tdeb10),
            $this->exibeSubTotal($totcre, $totdeb, '17px')
        );

        if ($totdeb != 0 || $totcre != 0)
        {
            $this->exibirSaldos($this->valores, $print);
        }
    }

    /*
     * Totalização da Copa 2018
     */
    private function somaCopaDoMundo($mes = '')
    {
        // variaveis
        $segsCredito = 0;
        $segsDebito = 0;

        // instancia o banco de dados
        $oDBase = new DataBase;
        $oDBase->setMensagem("Erro no acesso ao banco de dados (cálculo COPA " . $this->ano . ")");

        if (!empty($this->siape))
        {
            $arquivo = "ponto" . $mes . $this->ano;
            if (existeDBTabela($arquivo, 'sisref'))
            {
                $oDBase->query("
                SELECT DISTINCT SUM(TIME_TO_SEC(pto.jorndif)) AS total
                FROM " . $arquivo . " AS pto
                LEFT JOIN tabfacultativo172 AS tabf ON tabf.codigo_debito = pto.oco
                WHERE
                    pto.siape = '" . $this->siape . "'
                    AND pto.dia = tabf.dia
                    AND tabf.codigo_debito = pto.oco
                    AND tabf.jogo_do_brasil = 'S'
                    AND tabf.codigo_debito = " . $this->codigos_debitos . "
                GROUP BY pto.oco
				");
                $copa = $oDBase->num_rows();
                $oSoma = $oDBase->fetch_object();
                $segsDebito = $oSoma->total;

                $oDBase->query("
                SELECT SUM(TIME_TO_SEC(pto.jorndif)) AS total
                FROM " . $arquivo . " AS pto
                WHERE
                    pto.siape = '" . $this->siape . "'
                    AND (SELECT TRUE
                         FROM tabfacultativo172 AS tabf
                         WHERE (pto.dia >= tabf.compensacao_inicio
                                AND pto.dia <= tabf.compensacao_fim)
                                AND tabf.codigo_credito = " . $this->codigos_creditos . " LIMIT 1)
                    AND pto.oco = " . $this->codigos_creditos . "
                GROUP BY pto.oco
				");

                $copa += $oDBase->num_rows();
                $oSoma = $oDBase->fetch_object();
                $segsCredito = $oSoma->total;
            }
        }
        return array($copa, $segsCredito, $segsDebito);
    }

    /*
     * Exibe as horas
     */
    private function exibeHoras($valor = 0, $codigo = '62018')
    {
        if ($valor > 0 && $codigo == '92018')
        {
            $subtotal = "<font style='font-family:Tahoma;font-size:13px;color:#0000FF;'>+ " . sec_to_time($valor, 'hh:mm') . "</font>";
        }
        else if ($valor > 0 && $codigo == '62018')
        {
            $subtotal = "<font style='font-family:Tahoma;font-size:13px;color:#FF0000;'>- " . sec_to_time($valor, 'hh:mm') . "</font>";
        }
        else
        {
            $subtotal = "<font style='font-family:Tahoma;font-size:13px;'>--------</font>";
        }
        return $subtotal;
    }

    /*
     * Exibe Sub-totais
     */
    private function exibeSubTotal($credito = 0, $debito = 0, $size = '12px')
    {
        if ($credito > $debito)
        {
            $subtotal = "<font style='font-family:Tahoma;font-size:" . $size . ";color:#0000FF;font-weight:bold;'>+ " . sec_to_time(($credito - $debito), 'hh:mm') . "</font>";
        }
        else if ($debito > $credito)
        {
            $subtotal = "<font style='font-family:Tahoma;font-size:" . $size . ";color:#FF0000;font-weight:bold;'>- " . sec_to_time(($debito - $credito), 'hh:mm') . "</font>";
        }
        else
        {
            $subtotal = "<font style='font-family:Tahoma;font-size:" . $size . ";'>--------</font>";
        }
        return $subtotal;
    }

    /*
     * Exibir Saldos
     */
    private function exibirSaldos($valor, $print = true)
    {
        $html = "
		<style>
		.titulo { height: 20px; text-align: center; vertical-align: middle; }
		.periodo { width: 200px; height: 20px; font-size: 12px; text-align: right; vertical-align: middle; }
		.valores { height: 20px; font-size: 13px; text-align: center; vertical-align: middle; }
		.valores2 { width: 95px; height: 20px; font-size: 13px; text-align: center; vertical-align: middle; }
		</style>

		<div style='width:80%;text-align:center;padding:50px 0px 15px 0px;'>
		<table width='100%' border='1' align='center' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse'>
		<tr>
			<td height='30' colspan='7' bgcolor='#DFDFBF'><p align='center'><b>Copa do Mundo " . $this->getAno() . " - Período compensação: " . databarra($this->getCompensacaoInicio()) . " a " . databarra($this->getCompensacaoFim()) . "</b></p></td>
		</tr>
		<tr>
			<td align='center'>&nbsp;<b>&nbsp;</b>&nbsp;</td>
			<td align='center'>&nbsp;<b>Junho</b>&nbsp;</td>
			<td align='center'>&nbsp;<b>Julho</b>&nbsp;</td>
			<td align='center'>&nbsp;<b>Agosto</b>&nbsp;</td>
			<td align='center'>&nbsp;<b>Setembro</b>&nbsp;</td>
			<td align='center'>&nbsp;<b>Outubro</b>&nbsp;</td>
			<td align='center' height='23px' nowrap>&nbsp;&nbsp;<b>Totais</b>&nbsp;&nbsp;</td>
		</tr>
		";

        for ($x = 0; $x < count($valor); $x++)
        {
            $html .= "
			<tr style='background-color: " . ($x == (count($valor) - 1) ? '' : '#f2f2f2') . "'>
			<td class='periodo' nowrap>&nbsp;" . $valor[$x][0] . "&nbsp;&nbsp;</td>
			<td class='valores'>&nbsp;" . $valor[$x][1] . "&nbsp;</td>
			<td class='valores'>&nbsp;" . $valor[$x][2] . "&nbsp;</td>
			<td class='valores'>&nbsp;" . $valor[$x][3] . "&nbsp;</td>
			<td class='valores'>&nbsp;" . $valor[$x][4] . "&nbsp;</td>
			<td class='valores'>&nbsp;" . $valor[$x][5] . "&nbsp;</td>
			<td class='valores'>&nbsp;" . $valor[$x][6] . "&nbsp;</td>
			</tr>
			";
        }

        $html .= "
		</table>
		<table cellpadding='0' cellspacing='0' align='right'><tr><td class='lnTitulo2' nowrap><small>Legenda:&nbsp;</small></td><td class='tdLinha4'><small>(+) Horas excedentes; (-) Horas Devidas.</small></td></tr></table>
		</div>
		";

        if ($print == true)
        {
            echo $html;
        }
        else
        {
            return $html;
        }
    }
}