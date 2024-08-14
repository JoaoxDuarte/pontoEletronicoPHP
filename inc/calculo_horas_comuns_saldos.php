<?php

include_once( 'config.php' );
include_once( 'class_form.frequencia.php' );
include_once( "class_ocorrencias_grupos.php" );

set_time_limit(300);

function resultado_horas_comuns($sSiape='', $sMesAnoInicio='10/2009', $sMesAnoFim='', $comp_admissao='', $comp_exclusao='', $recalcular='N')
{
    // verifica se houve alterações para recalcular
    $oDBase      = new DataBase('PDO');
    $oDBase->query('SELECT recalculo FROM usuarios WHERE siape = :siape ', array(
        array(':siape', $sSiape, PDO::PARAM_STR),
    ));
    $sRecalcular = 'S'; //$oDBase->fetch_object()->recalculo;

    $_SESSION['recalcular'] = $sRecalcular;

    ##
    # Refaz os cálculos
    if ($sRecalcular == 'S')
    {
        $aLinha = recalcularHorasComuns($sSiape, $sMesAnoInicio, $sMesAnoFim);
    }

    ##
    # Se já foi calculado l&ecirc; os dados no banco de horas
    $comp_inicial    = substr($sMesAnoInicio, 3, 4) . substr($sMesAnoInicio, 0, 2);
    $comp_final      = substr($sMesAnoFim, 3, 4) . substr($sMesAnoFim, 0, 2);
    $competenciaHoje = date('mY');

    if ($comp_inicial <= $comp_admissao)
    {
        $comp_inicial = $comp_admissao;
    }

    // pesquisa a existencia na base de dados
    $oDBase->setMensagem("Problemas no acesso a Tabela HOMOLOGADOS (E000121.".__LINE__.").");
    $oDBase->query("
    SELECT 
            homologados.compet,
            homologados.mat_siape,
            servativ.nome_serv,
            servativ.sigregjur,
            IFNULL(banco_de_horas.tipo,'1')                     AS tipo,
            IFNULL(banco_de_horas.debito_anterior,'00:00:00')   AS debito_anterior,
            IFNULL(banco_de_horas.creditos_corrente,'00:00:00') AS creditos_corrente,
            IFNULL(banco_de_horas.sub_total,'00:00:00')         AS sub_total,
            IFNULL(banco_de_horas.debitos_corrente,'00:00:00')  AS debitos_corrente,
            IFNULL(banco_de_horas.total,'00:00:00')             AS total,
            IFNULL(banco_de_horas.situacao,'')                  AS situacao
    FROM 
            homologados
    LEFT JOIN 
            servativ ON homologados.mat_siape = servativ.mat_siape
    LEFT JOIN 
            banco_de_horas ON homologados.mat_siape = banco_de_horas.siape AND homologados.compet = banco_de_horas.comp
    WHERE 
            homologados.mat_siape = :siape
            AND (homologados.compet >= :comp_inicial AND homologados.compet <= :comp_final)
    ORDER BY
          servativ.mat_siape, banco_de_horas.comp
    ",
    array(
        array( ':siape',        $sSiape,       PDO::PARAM_STR ),
        array( ':comp_inicial', $comp_inicial, PDO::PARAM_STR ),
        array( ':comp_final',   $comp_final,   PDO::PARAM_STR )
    ));
    $nAchou = $oDBase->num_rows();

    if ($nAchou == 0)
    {
        $aLinha = recalcularHorasComuns($sSiape, $sMesAnoInicio, $sMesAnoFim);
    }

    if (count($aLinha) == 0)
    {
        while ($oBHoras = $oDBase->fetch_object())
        {
            $mes              = substr($oBHoras->comp, 4, 2);
            $ano              = substr($oBHoras->comp, 0, 4);
            $sComp            = $mes . $ano;
            $sMesAnoAnterior  = ($mes == '01' ? '12/' . ($ano - 1) : substr('0' . ($mes - 1), -2) . '/' . $ano);
            $sMesAnoCompensar = ($mes == '12' ? "01/" . ($ano + 1) : substr('0' . ($mes + 1), -2) . "/" . $ano);

            $aDebitoAnterior   = formataHHMMSaldo($oBHoras->debito_anterior);
            $aCreditosCorrente = formataHHMMSaldo($oBHoras->creditos_corrente);
            $aSubTotal         = formataHHMMSaldo($oBHoras->sub_total);
            $aDebitosCorrente  = formataHHMMSaldo($oBHoras->debitos_corrente);
            $aSaldoTotal       = formataHHMMSaldo($oBHoras->total);


            // instancia grupo de ocorrencia
            $obj = new OcorrenciasGrupos();

            $codigoRegistroParcialPadrao = $obj->CodigoRegistroParcialPadrao( $oBHoras->sigregjur );
            $codigoSemFrequenciaPadrao   = $obj->CodigoSemFrequenciaPadrao( $oBHoras->sigregjur );
            $codigoCreditoPadrao         = $obj->CodigoCreditoPadrao( $oBHoras->sigregjur );
            $codigoDebitoPadrao          = $obj->CodigoDebitoPadrao( $oBHoras->sigregjur );


            ##
            # Horas devidas do m&ecirc;s anterior
            $aLinha[] = array(
                $sComp, 
                "Horas devidas por Atrasos e/ou Sa&iacute;das Antecipadas (" . implode(',', $codigoDebitoPadrao) . "), Faltas Justificadas, Registro Parcial ap&oacute;s dia da frequ&ecirc;ncia (" . implode(',',
                $codigoRegistroParcialPadrao) . ") e Sem Frequência (" . implode(',', $codigoSemFrequenciaPadrao) . "), referente ao m&ecirc;s <b>" . $sMesAnoAnterior . "</b>", 
                $aDebitoAnterior[0], 
                substr($aDebitoAnterior[1], 0, 5), 
                $oBHoras->situacao,
                removeOrgaoMatricula($oBHoras->homologado_siape),
                $oBHoras->homologado_nome,
                $oBHoras->homologado_data
            );

            ##
            # Horas acumuladas no m&ecirc;s corrente
            $aLinha[] = array(
                $sComp, 
                "Horas acumuladas no m&ecirc;s, para Compensa&ccedil;&atilde;o (" . implode(',', $codigoCreditoPadrao) . ") de atrasos ou sa&iacute;das antecipadas e/ou faltas justificadas", 
                $aCreditosCorrente[0], 
                substr($aCreditosCorrente[1], 0, 5), 
                $oBHoras->situacao,
                removeOrgaoMatricula($oBHoras->homologado_siape),
                $oBHoras->homologado_nome,
                $oBHoras->homologado_data

            );

            ##
            # Sub-total
            $aLinha[] = array(
                $sComp, 
                ($aSubTotal[0] == "-" ? "<div style='text-align: right;'>Descontar em folha de pagamento (" . implode(',', $codigoDebitoPadrao) . ")&nbsp;&nbsp;»»»»»»»»»</div>" : ($aSubTotal[1] == "00:00:00" ? "SUB-TOTAL" : "SUB-TOTAL - Cr&eacute;dito (" . implode(',', $codigoCreditoPadrao) . ")")), 
                $aSubTotal[0], 
                substr($aSubTotal[1], 0, 5), 
                $oBHoras->situacao,
                removeOrgaoMatricula($oBHoras->homologado_siape),
                $oBHoras->homologado_nome,
                $oBHoras->homologado_data

            );

            ##
            # D&eacute;bitos acumulados no m&ecirc;s corrente
            $aLinha[] = array(
                $sComp, 
                "D&eacute;bito (" . implode(',', $codigoDebitoPadrao) . ") acumulado no m&ecirc;s ", 
                $aDebitosCorrente[0], 
                substr($aDebitosCorrente[1], 0, 5), 
                $oBHoras->situacao,
                removeOrgaoMatricula($oBHoras->homologado_siape),
                $oBHoras->homologado_nome,
                $oBHoras->homologado_data

            );

            ##
            # Saldo
            $str2debito172 = "<font color=red>D&eacute;bito (00172) para compensa&ccedil;&atilde;o em " . ($mes == 12 ? "01/" . ($ano + 1) : substr('0' . ($mes + 1), -2) . "/" . $ano) . '</font>';
            if ($competenciaHoje == $sComp)
            {
                $str2parcial = "<b>SALDO de cr&eacute;ditos para compensa&ccedil;&atilde;o de horas devidas dentro do m&ecirc;s corrente (" . substr($sComp, 0, 2) . "/" . substr($sComp, 2, 4) . ")</b>";
                $aLinha[]    = array(
                    $sComp, 
                    ($aSaldoTotal[0] == '-' ? $str2debito172 : $str2parcial), 
                    $aSaldoTotal[0], 
                    substr($aSaldoTotal[1], 0, 5), 
                    $oBHoras->situacao,
                    removeOrgaoMatricula($oBHoras->homologado_siape),
                    $oBHoras->homologado_nome,
                    $oBHoras->homologado_data
                );
            }
            else
            {
                $aLinha[] = array(
                    $sComp, 
                    ($aSaldoTotal[0] == '-' ? $str2debito172 : "<b>SALDO DEVEDOR</b>"), 
                    $aSaldoTotal[0], 
                    substr($aSaldoTotal[1], 0, 5), 
                    $oBHoras->situacao,
                    removeOrgaoMatricula($oBHoras->homologado_siape),
                    $oBHoras->homologado_nome,
                    $oBHoras->homologado_data
                );
            }
        }
    }

    //$tam = count($aLinha)-1;
    return $aLinha; //$aLinha[$tam];

}

function formataHHMMSaldo($nValor = '')
{
    $aValor = array();
    if ($nValor != '')
    {
        if (substr_count($nValor, '-') > 0)
        {
            $aValor[] = '-';
            $aValor[] = str_replace('-', '', $nValor);
        }
        else
        {
            $aValor[] = ($nValor == '00:00:00' ? '' : '+');
            $aValor[] = $nValor;
        }
    }
    return $aValor;

}

function recalcularHorasComuns($sSiape = '', $sMesAnoInicio = '', $sMesAnoFim = '')
{
    global $comp_admissao;

    $aLinha = array();

    $mes_inicio = substr($sMesAnoInicio, 0, 2);
    $ano        = substr($sMesAnoInicio, 3, 4);

    $comp_inicial    = $mes_inicio . $ano;
    $comp_final      = date('Ym'); //$comp_final = substr($sMesAnoFim,3,4).substr($sMesAnoFim,0,2);
    $competenciaHoje = date('mY');

    $competencia_anterior = ($mes_inicio == '01' ? ($ano - 1) . '12' : $ano . substr('0' . ($mes_inicio - 1), -2));

    // instancia o banco de dados
    $oDBase = new DataBase('PDO');

    // pesquisa a existencia na base de dados
    $oDBase->query("
    SELECT
    	IFNULL(banco_de_horas.tipo,'1')         AS tipo,
    	IFNULL(banco_de_horas.total,'00:00:00') AS total,
    	servativ.nome_serv,
    	servativ.sigregjur
    FROM
    	servativ
    LEFT JOIN
    	banco_de_horas ON servativ.mat_siape = banco_de_horas.siape AND banco_de_horas.comp = :comp
    WHERE
    	servativ.mat_siape = :siape
    ORDER BY
    	servativ.mat_siape, banco_de_horas.comp ",
    array(
        array( ':siape', $sSiape,               PDO::PARAM_STR ),
        array( ':comp',  $competencia_anterior, PDO::PARAM_STR )
    ));
    $nAchou = $oDBase->num_rows();

    if ($nAchou == 0 || $competencia_anterior <= '200910')
    {
        $nSaldoAnterior = '00:00';
    }
    else
    {
        $oBHoras        = $oDBase->fetch_object();
        $sitcad         = $oBHoras->sigregjur;
        $nSaldoAnterior = $oBHoras->total;
        $nSaldoAnterior = substr($nSaldoAnterior, (substr($nSaldoAnterior, 0, 1) == '-' ? 1 : 0), 8);
    }


    // instancia grupo de ocorrencia
    $obj = new OcorrenciasGrupos();

    $codigoRegistroParcialPadrao  = $obj->CodigoRegistroParcialPadrao( $sitcad );
    $codigoSemFrequenciaPadrao    = $obj->CodigoSemFrequenciaPadrao( $sitcad );
    $codigoCreditoPadrao          = $obj->CodigoCreditoPadrao( $sitcad );
    $codigoDebitoPadrao           = $obj->CodigoDebitoPadrao( $sitcad );
    $codigosAgrupadosParaDesconto = $obj->CodigosAgrupadosParaDesconto( $sitcad );


    ##
    # instancia o formulario para uso
    # das funcoes de frequencia
    #
    $oForm2 = new formFrequencia;
    $oForm2->setSiape($sSiape); // matricula do servidor que se deseja alterar a frequencia

    $quebra   = "";
    $contador = 0;


    for ($i = $mes_inicio; $i <= 13; $i++)
    {
        if ($i == 13)
        {
            $ano++;
            $i = 1;
        }

        $mes            = substr("00" . $i, -2);
        $comp           = $mes . $ano;
        $comp_invertida = $ano . $mes;

        if ($comp_invertida > $comp_final)
        {
            break;
        }
        elseif ($comp_admissao > $comp_invertida)
        {
            continue;
        }

        $tb_nome = 'ponto' . $mes . $ano;

        if (existeDBTabela($tb_nome) == false)
        {
            continue;
        }

        ##
        # calcula as horas por ocorrencia
        #
        $oForm2->setMes($mes);
        $oForm2->setAno($ano);
        $oForm2->setNomeDoArquivo( $tb_nome ); // nome do arquivo de trabalho
        $oForm2->initOcorrenciasTotalHoras();
        $oForm2->ocorrenciasTotalHoras();

        ##
        # obtem dados da homologa&ccedil;&atilde;o
        #
	    $oForm2->verificaSeHomologado();
        $status = $oForm2->getHomologacaoStatus();

        ##
        # saldo anterior
        #
	    $strSaldoAnteriorSinal = ($nSaldoAnterior > '00:00' ? '-' : '' );
        $strSaldoAnterior      = $nSaldoAnterior;


        ##
        # calcula horas positivas
        #
	    $total33333           = $oForm2->getOcorrenciasTotalHoras( $codigoCreditoPadrao[0] );
        $total33333           = (empty($total33333) ? '00:00' : $total33333);
        $strCreditoNoMesSinal = ($total33333 > '00:00' ? '+' : '' );
        $strCreditoNoMes      = $total33333;

        ##
        # calcula sub-total
        #
	    if (time_to_sec($nSaldoAnterior) > time_to_sec($total33333))
        {
            $diferenca_horas = sec_to_time(time_to_sec($nSaldoAnterior) - time_to_sec($total33333));
            if (time_to_sec('100:00:00') > time_to_sec($diferenca_horas))
            {
                $diferenca_horas = substr($diferenca_horas, 1, strlen($diferenca_horas));
            }
        }
        else
        {
            $diferenca_horas = subtrairHoras($nSaldoAnterior, $total33333);
        }


		$strTotalParcialSinal = (time_to_sec($diferenca_horas) == 0 ? '' : (time_to_sec($nSaldoAnterior) < time_to_sec($total33333) ? '+' : '-'));
		$strTotalParcial      = $diferenca_horas;

        ##
        # calcula horas negativas
        #
        $total00172 = '00:00';
        foreach($codigosAgrupadosParaDesconto AS $codigoDescontar)
        {
          $total00172 = adicionarHoras( $total00172, $oForm2->getOcorrenciasTotalHoras($codigoDescontar) );
        }

        $total00172          = (empty($total00172) ? '00:00' : $total00172);
        $strDebitoNoMesSinal = ($total00172 == '00:00' ? '' : '-' );
        $strDebitoNoMes      = $total00172;

        ##
        # calcula saldo
        #
		$nSaldoParcial        = subtrairHoras($total00172,$diferenca_horas);
		$nSaldoParcial        = (empty($nSaldoParcial) ? '00:00' : $nSaldoParcial);
		$nSaldoParcial        = ($strTotalParcialSinal == '-' ? $total00172 : $nSaldoParcial);
		$strSaldoParcialSinal = ($strTotalParcialSinal == '-' ? '-' : ($nSaldoParcial == '00:00' ? '' : '+'));
		$strSaldoParcial      = $nSaldoParcial;
		if ($competenciaHoje == $comp)
		{
            //
		}

        $nSaldo        = ($strTotalParcialSinal == '-' ? $total00172 : ($total00172 > $diferenca_horas ? subtrairHoras($total00172, $diferenca_horas) : '00:00'));
        $strSaldoSinal = ($nSaldo != '00:00' ? '-' : '' );
        $strSaldo      = $nSaldo;

        ##
        # montagem do vetor com os saldos
        #
	    # aLinha[] = array(
        #   <competencia - mm/aaaa>,    // m&ecirc;s e ano de compet&ecirc;ncia
        #   <texto>,                    // texto explicando os dados
        #   <sinal -/+>,                // indica se as horas são positivas ou negativas
        #   <horas e/ou minutos>,       // horas registradas (horas e minutos)
        #   <homologado/não homologado> // indica se o m&ecirc;s foi homologado ou não
        # )
        #
	    if ($comp_invertida >= $comp_inicial)
        {
            ##
            # Horas devidas do m&ecirc;s anterior
            $aLinha[] = array($comp, "Horas devidas por Atrasos e/ou Sa&iacute;das Antecipadas (" . implode(',', $codigoDebitoPadrao) . "), Faltas Justificadas, Registro Parcial ap&oacute;s dia da frequ&ecirc;ncia (" . implode(',', $codigoRegistroParcialPadrao) . ") e Sem Frequência (" . implode(',', $codigoSemFrequenciaPadrao) . "), referente ao m&ecirc;s <b>" . $competencia_compensar . "</b>", $strSaldoAnteriorSinal, $strSaldoAnterior, $status);

            ##
            # Horas acumuladas no m&ecirc;s corrente
            $aLinha[] = array($comp, "Horas acumuladas no m&ecirc;s, para Compensa&ccedil;&atilde;o (" . implode(',', $codigoCreditoPadrao) . ") de atrasos ou sa&iacute;das antecipadas e/ou faltas justificadas", $strCreditoNoMesSinal, $strCreditoNoMes, $status);

            ##
            # Sub-total
            $aLinha[] = array($comp, ($strTotalParcialSinal == '-' ? "<div style='text-align: right;'>Descontar em folha de pagamento (" . implode(',', $codigoDebitoPadrao) . ")&nbsp;&nbsp;»»»»»»»»»</div>" : ($strTotalParcialSinal == '+' ? "SUB-TOTAL - Cr&eacute;dito (" . implode(',', $codigoCreditoPadrao) . ")" : "SUB-TOTAL")), $strTotalParcialSinal, $strTotalParcial, $status);

            ##
            # D&eacute;bitos acumulados no m&ecirc;s corrente
            $aLinha[] = array($comp, "D&eacute;bito (" . implode(',', $codigoDebitoPadrao) . ") acumulado no m&ecirc;s ", $strDebitoNoMesSinal, $strDebitoNoMes, $status);

            ##
            # Saldo
            $str2debito172 = "<font color=red>D&eacute;bito (" . implode(',', $codigoDebitoPadrao) . ") para compensa&ccedil;&atilde;o em " . ($mes == 12 ? "01/" . ($ano + 1) : substr('0' . ($mes + 1), -2) . "/" . $ano) . '</font>';
            if ($competenciaHoje == $comp)
            {
                $str2parcial = "<b>SALDO de cr&eacute;ditos para compensa&ccedil;&atilde;o de horas devidas dentro do m&ecirc;s corrente (" . substr($comp, 0, 2) . "/" . substr($comp, 2, 4) . ")</b>";
                $aLinha[]    = array($comp, ($strSaldoSinal == '-' ? $str2debito172 : $str2parcial), $strSaldoSinal, $strSaldoParcial, $status);
            }
            else
            {
                $aLinha[] = array($comp, ($strSaldoSinal == '-' ? $str2debito172 : "<b>SALDO</b>"), $strSaldoSinal, $strSaldo, $status);
            }

            $sCampos = "SET comp='" . substr($comp, 2, 4) . substr($comp, 0, 2) . "', siape='$sSiape', tipo='1', ";
            $sCampos .= "debito_anterior='" . $strSaldoAnteriorSinal . $strSaldoAnterior . "', ";
            $sCampos .= "creditos_corrente='" . $strCreditoNoMes . "', ";
            $sCampos .= "sub_total='" . ($strTotalParcialSinal == '-' ? '-' : '') . $strTotalParcial . "', ";
            $sCampos .= "debitos_corrente='-" . $strDebitoNoMes . "', ";
            $sCampos .= "total='" . ($strSaldoSinal == '-' ? '-' : '') . $strSaldo . "', situacao='" . $status . "' ";

            // pesquisa a existencia na base de dados
            $oDBase->setMensagem("Falha na grava&ccedil;&atilde;o da horas!");
            $res    = $oDBase->query("SELECT * FROM banco_de_horas WHERE siape= :siape AND comp= :comp ", array(
                array( ':siape', $sSiape, PDO::PARAM_STR ),
                array( ':comp', $comp_invertida, PDO::PARAM_STR ),
            ));
            $nAchou = $oDBase->num_rows();

            if ($nAchou == 0)
            {
                $oDBase->query("INSERT banco_de_horas $sCampos");
            }
            else
            {
                $oDBase->query("UPDATE banco_de_horas $sCampos WHERE siape= :siape AND comp= :comp ", array(
                    array( ':siape', $sSiape, PDO::PARAM_STR ),
                    array( ':comp', $comp_invertida, PDO::PARAM_STR )
                ));
            }
        }

        $nSaldoAnterior        = ($strSaldoSinal == '-' ? $strSaldo : '00:00');
        $competencia_compensar = substr($comp, 0, 2) . "/" . substr($comp, 2, 4);
    }

    $oDBase->query("UPDATE usuarios SET recalculo = 'N' WHERE siape = :siape ", array(
        array( ':siape', $sSiape, PDO::PARAM_STR ),
    ));

    return $aLinha;

}


function montaListaGradeSaldos($comp, $mes, $ano, $codigoCreditoPadrao, $codigoDebitoPadrao, $codigoRegistroParcialPadrao, $codigoSemFrequenciaPadrao, $sMesAnoAnterior, $aDebitoAnterior, $aCreditosCorrente, $aSubTotal, $aDebitosCorrente, $aSaldoTotal, $status, $saldo_devedor = "")
{
    $competenciaHoje = date('mY');

    $aLinha = array();

    ##
    # Horas devidas do m&ecirc;s anterior
    $aLinha[] = array($comp, "Horas devidas por Atrasos e/ou Sa&iacute;das Antecipadas (" . implode(',', $codigoDebitoPadrao) . "), Faltas Justificadas, Registro Parcial ap&oacute;s dia da frequ&ecirc;ncia (" . implode(',', $codigoRegistroParcialPadrao) . ") e Sem Frequência (" . implode(',', $codigoSemFrequenciaPadrao) . "), referente ao m&ecirc;s <b>" . $sMesAnoAnterior . "</b>", $aDebitoAnterior[0], substr($aDebitoAnterior[1], 0, 5), $status);

    ##
    # Horas acumuladas no m&ecirc;s corrente
    $aLinha[] = array($comp, "Horas acumuladas no m&ecirc;s, para Compensa&ccedil;&atilde;o (" . implode(',', $codigoCreditoPadrao) . ") de atrasos ou sa&iacute;das antecipadas e/ou faltas justificadas", $aCreditosCorrente[0], substr($aCreditosCorrente[1], 0, 5), $status);

    ##
    # Sub-total
    $aLinha[] = array($comp, ($aSubTotal[0] == "-" ? "<div style='text-align: right;'>Descontar em folha de pagamento (" . implode(',', $codigoDebitoPadrao) . ")&nbsp;&nbsp;»»»»»»»»»</div>" : ($aSubTotal[1] == "00:00:00" ? "SUB-TOTAL" : "SUB-TOTAL - Cr&eacute;dito (" . implode(',', $codigoCreditoPadrao) . ")")), $aSubTotal[0], substr($aSubTotal[1], 0, 5), $status);

    ##
    # D&eacute;bitos acumulados no m&ecirc;s corrente
    $aLinha[] = array($comp, "D&eacute;bito (" . implode(',', $codigoDebitoPadrao) . ") acumulado no m&ecirc;s ", $aDebitosCorrente[0], substr($aDebitosCorrente[1], 0, 5), $status);

    ##
    # Saldo
    $str2debito172 = "<font color=red>D&eacute;bito (" . implode(',', $codigoDebitoPadrao) . ") para compensa&ccedil;&atilde;o em " . ($mes == 12 ? "01/" . ($ano + 1) : substr('0' . ($mes + 1), -2) . "/" . $ano) . '</font>';

    if ($competenciaHoje == $comp)
    {
        $str2parcial = "<b>SALDO de cr&eacute;ditos para compensa&ccedil;&atilde;o de horas devidas dentro do m&ecirc;s corrente (" . substr($comp, 0, 2) . "/" . substr($comp, 2, 4) . ")</b>";
        $aLinha[]    = array($comp, ($aSaldoTotal[0] == '-' ? $str2debito172 : $str2parcial), $aSaldoTotal[0], substr($aSaldoTotal[1], 0, 5), $status);
    }
    else
    {
        // se o atributo $aSaldoTotal
        // tiver 2 elementos exibe o segundo e a expressão "<b>SALDO DEVEDOR</b>"
        // e se 3 elementos exibe o terceiro e a expressão "<b>SALDO</b>"
        if (count($aSaldoTotal) > 2)
        {
            $textoSaldo = "<b>SALDO</b>";
            $saldoTotal = substr($aSaldoTotal[2], 0, 5);
        }
        else
        {
            $textoSaldo = "<b>SALDO DEVEDOR</b>";
            $saldoTotal = substr($aSaldoTotal[1], 0, 5);
        }

        $aLinha[] = array($comp, ($aSaldoTotal[0] == '-' ? $str2debito172 : $textoSaldo), $aSaldoTotal[0], $saldoTotal, $status);
    }

    return $aLinha;
}

?>