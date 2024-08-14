<?php

// @package class
include_once( "config.php" );

## @class
#+--------------------------------------+
#| Formulario de registro da frequência |
#+--------------------------------------+

#
	class formEntrada extends formPadrao
{

    // formulario
    var $form_origem;
    var $form_destino;
    var $form_onsubmit;
    var $form_center; // boolean - indica se o atributo de alinhamento será center
    // dados do servidor
    var $siape;
    var $nome;
    var $lotacao;
    var $codigo_municipio;
    // horario de funcionamento do Órgão
    var $inss_inicio;
    var $inss_fim;
    // horario de servico do servidor
    var $hora_entrada;
    var $hora_saida_almoco;
    var $hora_volta_almoco;
    var $hora_saida;
    // indica autorizacao para compensacao
    var $autoriza_bhoras;
    // data do registro da frequencia
    var $data_hoje;
    // horario do registro da frequencia
    var $HoraEntradaReal;
    var $entrada;
    var $saida_almoco;
    var $volta_almoco;
    var $saida;
    var $idh;
    var $vHoras;
    var $jornada;
    // mensagens
    var $aMensagem;
    var $botao_fechar;
    var $botao_fechar_w;
    var $botao_fechar_h;
    var $dia_util;
    var $turno_estendido;
    var $chefiaAtiva;
    var $situacao_cadastral;

    ## @constructor
    #+----------------------------+
    #| Construtor da classe       |
    #+----------------------------+

    #
		function formEntrada()
    {
        parent::formPadrao();
        parent::setJS('entrada1.js');

    }

    ## @metodo
    #+----------------------------+
    #| Exibe o formulário         |
    #+----------------------------+
    #
		// form origem

    function setFormEntrada($valor = '')
    {
        $this->form_origem = $valor;

    }

    function getFormEntrada()
    {
        return $this->form_origem;

    }

    // form destino
    function setFormDestino($valor = '')
    {
        $this->form_destino = $valor;

    }

    function getFormDestino()
    {
        return $this->form_destino;

    }

    // form onSubmit
    function setFormSubmit($valor = '')
    {
        $this->form_onsubmit = $valor;

    }

    function getFormSubmit()
    {
        return $this->form_onsubmit;

    }

    // centraliza o form
    function setFormCenter($center = true)
    {
        $this->form_center = ($center == true ? " align='center' " : "");

    }

    function getFormCenter()
    {
        return $this->form_center;

    }

    // siape
    function setSiape($valor = '')
    {
        $this->siape = $valor;

    }

    function getSiape()
    {
        return $this->siape;

    }

    // nome
    function setNome($valor = '')
    {
        $this->nome = $valor;

    }

    function getNome()
    {
        return $this->nome;

    }

    // lotacao
    function setLotacao($valor = '')
    {
        $this->lotacao = $valor;

    }

    function getLotacao()
    {
        return $this->lotacao;

    }

    // codigo do municipio
    function setCodigoMunicipio($valor = '')
    {
        $this->codigo_municipio = $valor;

    }

    function getCodigoMunicipio()
    {
        return $this->codigo_municipio;

    }

    // hora inicio Órgão
    function setHoraInicioINSS($valor = '')
    {
        $this->inss_inicio = $valor;

    }

    function getHoraInicioINSS()
    {
        return $this->inss_inicio;

    }

    // hora fim Órgão
    function setHoraFimINSS($valor = '')
    {
        $this->inss_fim = $valor;

    }

    function getHoraFimINSS()
    {
        return $this->inss_fim;

    }

    // hora entrada no servico
    function setHoraEntrada($valor = '')
    {
        $this->hora_entrada = $valor;

    }

    function getHoraEntrada()
    {
        return $this->hora_entrada;

    }

    // hroa saida para o almoco
    function setHoraSaidaAlmoco($valor = '')
    {
        $this->hora_saida_almoco = $valor;

    }

    function getHoraSaidaAlmoco()
    {
        return $this->hora_saida_almoco;

    }

    // hora retorno do almoco
    function setHoraVoltaAlmoco($valor = '')
    {
        $this->hora_volta_almoco = $valor;

    }

    function getHoraVoltaAlmoco()
    {
        return $this->hora_volta_almoco;

    }

    // hora fim do expediente
    function setHoraSaida($valor = '')
    {
        $this->hora_saida = $valor;

    }

    function getHoraSaida()
    {
        return $this->hora_saida;

    }

    // autoriza banco de compensacao
    function setAutorizaBHoras($valor = '')
    {
        $this->autoriza_bhoras = $valor;

    }

    function getAutorizaBHoras()
    {
        return $this->autoriza_bhoras;

    }

    // data de hoje
    function setDataHoje($valor = '')
    {
        $this->data_hoje = $valor;

    }

    function getDataHoje()
    {
        return $this->data_hoje;

    }

    // hora entrada no servico
    function setHoraEntradaReal($valor = '')
    {
        $this->HoraEntradaReal = $valor;

    }

    function getHoraEntradaReal()
    {
        return $this->HoraEntradaReal;

    }

    function setEntrada($valor = '')
    {
        $this->entrada = $valor;

    }

    function getEntrada()
    {
        return $this->entrada;

    }

    // hroa saida para o almoco
    function setSaidaAlmoco($valor = '')
    {
        $this->saida_almoco = $valor;

    }

    function getSaidaAlmoco()
    {
        return $this->saida_almoco;

    }

    // hora retorno do almoco
    function setVoltaAlmoco($valor = '')
    {
        $this->volta_almoco = $valor;

    }

    function getVoltaAlmoco()
    {
        return $this->volta_almoco;

    }

    // hora fim do expediente
    function setSaida($valor = '')
    {
        $this->saida = $valor;

    }

    function getSaida()
    {
        return $this->saida;

    }

    // idh (???)
    function setIDH($valor = '')
    {
        $this->idh = $valor;

    }

    function getIDH()
    {
        return $this->idh;

    }

    // hora atual
    function setHoraAtual($valor = '')
    {
        $this->vHoras = $valor;

    }

    function getHoraAtual()
    {
        return $this->vHoras;

    }

    // Turno Estendido
    function setTurnoEstendido($ativo = 'N')
    {
        $this->turno_estendido = ($ativo == 'S' ? $ativo : 'N');

    }

    function getTurnoEstendido()
    {
        return $this->turno_estendido;

    }

    // Chefia Ativa
    function setChefiaAtiva($ativo = 'N')
    {
        $this->chefiaAtiva = ($ativo == 'S' ? $ativo : 'N');

    }

    function getChefiaAtiva()
    {
        return $this->chefiaAtiva;

    }

    // mensagens
    function iniMensagem()
    {
        $this->aMensagem = array();

    }

    function addMensagem($valor = '')
    {
        $this->aMensagem[] = $valor;

    }

    function getMensagem($valor = 0)
    {
        return $this->aMensagem[$valor];

    }

    // botao fechar
    function setBotaoFechar($valor = './imagem/logout.gif')
    {
        $this->botao_fechar = $valor;

    }

    function getBotaoFechar()
    {
        return $this->botao_fechar;

    }

    // botao fechar - largura
    function setBotaoFecharW($valor = '10')
    {
        $this->botao_fechar_w = $valor;

    }

    function getBotaoFecharW()
    {
        return $this->botao_fechar_w;

    }

    // botao fechar - altura
    function setBotaoFecharH($valor = '10')
    {
        $this->botao_fechar_h = $valor;

    }

    function getBotaoFecharH()
    {
        return $this->botao_fechar_h;

    }

    ## @metodo
    # dia util

    #
		function setDiaUtil($dia_util = 'S')
    {
        $this->dia_util = $dia_util;

    }

    function getDiaUtil()
    {
        return $this->dia_util;

    }

    ## @metodo
    # Situacao cadastral

    #
		function setSituacaoCadastral($situacao_cadastral = '')
    {
        $this->oDadosCadastro->situacao_cadastral = $situacao_cadastral;

    }

    function getSituacaoCadastral()
    {
        return $this->oDadosCadastro->situacao_cadastral;

    }

    ## @metodo
    # Jornada

    #
		function setJornada($jornada = '')
    {
        $this->jornada = $jornada;

    }

    function getJornada()
    {
        return $this->jornada;

    }

    ## @metodo
    #+----------------------------+
    #| Exibe o formulário         |
    #+----------------------------+
    #
		//TODO: formulario usado no entrada1.php

    function exibeForm()
    {
        parent::exibeTopoHTML();
        parent::exibeCorpoTopoHTML();

        if ($this->getFormEntrada() == 'entrada1')
        {
            //Necessrio ser chamado antes do include para que a variavel possua valor para ser atribuido no arquivo incluido.

            $horas_trabalhadas_ate_o_momento = horas_trabalhadas_ate_o_momento($this->getSiape(), $this->getDataHoje());

            //necessário ser chamado para pegar mensagem
            switch ($rows)
            {
                case "0":
                    $nInd = ($this->getIDH() == "1" ? 0 : 1);
                    break;
                default:
                    $nInd = ($this->getIDH() == "1" ? 2 : 3);
                    break;
            }
            include("html/form-entrada1.php");
            $html = "<form id='form1'style='display:none;' name='form1' method='post'></form>";

            print $html;
        }
        else
        {
            $html = "<style> A { font-size: 10px; font-family: verdana, arial, helvetica; font-weight: normal; color: #454545; } .ftFormFreq-tit-bc { font-family: verdana,arial,tahoma; font-size: 7pt; font-weight: bold; color: #000000; background-color: #DFDFBF; text-align: center; } .ftFormFreq-tit-bc-3 { font-family: verdana,arial,tahoma; font-size: 7pt; font-weight: bold; color: #000000; background-color: #DFDFBF; text-align: center; } .ftFormFreq-bc-1 { font-size: 7.5pt; font-weight: bold; color: #000000; background-color: #DFDFBF; text-align: center; vertical-align: middle; } .ftFormFreq-tit-bc-4 { font-family: verdana,arial,tahoma; font-size: 9pt; font-weight: bold; color: #000000; background-color: #DFDFBF; text-align: center; } .ftFormFreq-cn-1 { font-family: verdana,arial,tahoma; font-size: 7.5pt; color: #000000; text-align: center; vertical-align: middle; height: 15; } .ftFormFreq-cn-2 { font-family: verdana,arial,tahoma; font-size: 7.5pt; color: #000000; text-align: center; vertical-align: middle; height: 15; } .ftFormFreq-c { font-size: 7.5pt; color: #000000; background-color: #FFFFFF; text-align: center; vertical-align: middle; } .ftFormFreq-bc { font-size: 7.5pt; color: #000000; font-weight: bold; background-color: #FFFFFF; text-align: center; vertical-align: middle; } .ftFormFreq-c-2 { font-size: 8pt; color: #000000; background-color: #FFFFFF; text-align: center; vertical-align: middle; } .ftFormFreq-c-3 { font-size: 9pt; color: #000000; background-color: #FFFFFF; text-align: center; vertical-align: middle; } .ftFormFreq-bc-2 { font-family: Tahoma,verdana,arial; font-size: 11pt; font-weight: bold; color: #333300; background-color: #FFFFFF; text-align: center; vertical-align: middle; } .ftFormFreq-bc-3 { font-family: Tahoma,verdana,arial; font-size: 10pt; font-weight: bold; color: #333300; background-color: #FFFFFF; text-align: center; vertical-align: middle; } </style>";

            $form_action = '#';
            $form_submit = '';
            $html        .= "<form id='form1' name='form1' method='post' action='" . $form_action . "' " . ($form_submit == "" ? "" : "onSubmit='$form_submit'") . ">";
            $html        .= "<input type='hidden' id='defvis' name='defvis' value='" . $_SESSION['sDefVisual'] . "'>";
            $html        .= "<table class='tablew2' width='100%' border='0' align='center' cellpadding='0' cellspacing='0' style='border-collapse: collapse'><tr><td>";
            print $html;

            // ja foi inserido no arquivo html/form-entrada1.php
            if ($this->getFormEntrada() == 'entrada1')
            {
                include_once(_DIR_INC_ . 'relogio.php'); // localização /inc
            }

            $html = "</td></tr><tr><td>";

            //FIXME: Comentado horas para deficiente visual
            //if ($this->getFormEntrada() == 'entrada1' && $_SESSION['sDefVisual'] == 'S')
            //{
            //	$horas_trabalhadas_ate_o_momento = horas_trabalhadas_ate_o_momento( $this->getSiape(), $this->getDataHoje() );
            //	$html .= "
            //	<div align='center'>
            //	<table width='97%' border='0' align='center' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse'>
            //	<tr bgcolor='#F8F8EF' style='text-align: center;'>
            //	<td style='width: 50%; height: 20px; vertical-align: middle; font-family: verdana; font-size: 12;'>".$horas_trabalhadas_ate_o_momento."</td>
            //	<td style='width: 50%; height: 20px; vertical-align: middle; font-family: verdana; font-size: 12;'><a href='entrada9.php' target = 'new' title='Visualizar demonstrativo de compensações' alt='Visualizar demonstrativo de compensações'>Visualizar demonstrativo de compensações.</a></td>
            //	</tr>
            //	</table>
            //	</div>";
            //}

            $html .= "<table class='tablew21' width='100%' border='1' align='center' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse'>";

            ## dados do servidor
            #
				//INSERIDO NOVO LAYOUT
            $html .= "<tr><td height='20' class='ftFormFreq-tit-bc'>SIAPE</td><td height='20' class='ftFormFreq-tit-bc' width='54%' colspan='3'>NOME</td><td height='20' class='ftFormFreq-tit-bc'>LOTACAO</td></tr><tr><td height='25' width='10%' align='center'><input type='text' id='siape' name='siape' class='centro' value='" . $this->getSiape() . "' size='10' readonly></td><td height='25' colspan='3' align='centro'>&nbsp;<input type='text' id='nome' name='nome' class='Caixa' value='" . $this->getNome() . "' size='65' readonly>&nbsp;</td><td height='25' width='14%' align='center'><input type='text' id='lotacao' name='lotacao' class='centro' value='" . $this->getLotacao() . "' size='13' readonly></td></tr>";

            ## dados do servidor - frequencia
            #
				//INSERIDO NOVO LAYOUT
            $html .= "<tr><td colspan='1' rowspan='2' class='ftFormFreq-tit-bc-3'>Horário do Setor</td><td height='20' colspan='3' class='ftFormFreq-tit-bc-3'>Horário do Servidor" . ($this->getTurnoEstendido() == 'S' ? ' - <b>Unidade com Turno Estendido</b>' : '') . "</td><td height='20' rowspan='2' class='ftFormFreq-tit-bc-3'>Compensação</td></tr><tr><td width='18%' height='20' class='ftFormFreq-tit-bc-3'>Entrada</td><td width='36%' height='20' class='ftFormFreq-tit-bc-3'>Intervalo</td><td width='18%' height='20' class='ftFormFreq-tit-bc-3'>Saída</td></tr><tr><td height='25' colspan='1' align='center' nowrap>&nbsp;<input type='text' id='inicio' name='inicio' class='centro' value='" . $this->getHoraInicioINSS() . "' size='8' readonly>&nbsp;<font class='ftFormFreq-c-3'>&agrave;s</font>&nbsp;<input type='text' id='fim' name='fim' class='centro' value='" . $this->getHoraFimINSS() . "' size='8' readonly>&nbsp;</td><td height='25' align='center'>&nbsp;<input type='text' id='entrada' name='entrada' class='centro' value='" . $this->getHoraEntrada() . "' size='10' readonly>&nbsp;</td><td height='25' align='center'>&nbsp;<input type='text' id='interve' name='interve' class='centro' value='" . $this->getHoraSaidaAlmoco() . "' size='10' readonly>&nbsp;<font class='ftFormFreq-c-3'>&agrave;s</font>&nbsp;<input type='text' id='intervs' name='intervs' class='centro' value='" . $this->getHoraVoltaAlmoco() . "' size='10' readonly>&nbsp;</td><td height='25' align='center'>&nbsp;<input name='saida' type='text' class='centro' id='saida' value='" . $this->getHoraSaida() . "' size='10' readonly>&nbsp;</td><td height='25' colspan='1' class='ftFormFreq-c' nowrap>&nbsp;<b>" . ($this->getAutorizaBHoras() == "S" ? "AUTORIZADA" : "<font color='red'>NÃO AUTORIZADA</font>") . "</b>&nbsp;</td></tr>";

            ## data
            #
				//INSERIDO NOVO LAYOUT
            $html .= "<tr><td colspan='5' height='20' class='ftFormFreq-bc-1'>Registro de Comparecimento</td></tr>";
            $html .= "<tr><td colspan='5' valign='middle' height='25' class='ftFormFreq-bc-3'>" . $this->getDataHoje() . "</td></tr>";

            ## entrada
            #
				$html .= "<tr><td colspan='2' width='18%' height='20' class='ftFormFreq-tit-bc-4'>Entrada</td><td colspan='1' width='36%' height='20' class='ftFormFreq-tit-bc-4'>Intervalo</td><td colspan='2' width='18%' height='20' class='ftFormFreq-tit-bc-4'>Saída</td></tr><tr><td colspan='2' width='18%' height='35' class='ftFormFreq-bc-3'><div align='center'><input name='ent' type='text' class='centro' id='ent' value='" . ($this->getEntrada() == '' ? $this->getHoraAtual() : $this->getEntrada()) . "' size='10' readonly></div></td><td colspan='1' width='36%' height='35' class='ftFormFreq-bc-3'><div align='center'>";

            ## saída para o almoço/intervalo
            #
				//if ($this->getTurnoEstendido()=='N' || $this->getAutorizaBHoras()=='S' || $this->getChefiaAtiva()=='S')
            ///========> ver autroizacao >>>>if ($this->getJornada()=='08:00' || $this->getTurnoEstendido()=='N' || $this->getAutorizaBHoras()=='S' || $this->getChefiaAtiva()=='S')
            if ($this->getJornada() == '08:00' || $this->getAutorizaBHoras() == 'S' || $this->getChefiaAtiva() == 'S')
            {
                $html .= ($this->getSaidaAlmoco() == '00:00:00' && $this->getFormEntrada() == 'entrada1' ? "<a href=\"javascript:confirma ('Deseja realmente registrar o inicio do intervalo?','entrada2.php')\"><img class='imagem_registra_frequencia' border='0' src='" . _DIR_IMAGEM_ . "edicao2.jpg' width='16' height='16' align='absmiddle' title='Iniciar intervalo' alt='Iniciar intervalo'></a>" : "");
            }

            $html .= "<input name='inti' type='text' class='centro' id='inti' value='" . $this->getSaidaAlmoco() . "' size='10' readonly>&nbsp;&agrave;s&nbsp;<input name='ints' type='text' class='centro' id='ints' value='" . $this->getVoltaAlmoco() . "' size='10' readonly>";

            ## retorno do almoço/intervalo
            #
				//INSERIDO NOVO LAYOUT
            //if ($this->getTurnoEstendido()=='N' || $this->getAutorizaBHoras()=='S' || $this->getChefiaAtiva()=='S')
            ///========> ver autroizacao >>>>if ($this->getJornada()=='08:00' || $this->getTurnoEstendido()=='N' || $this->getAutorizaBHoras()=='S' || $this->getChefiaAtiva()=='S')
            if ($this->getJornada() == '08:00' || $this->getAutorizaBHoras() == 'S' || $this->getChefiaAtiva() == 'S')
            {
                $html .= ($this->getVoltaAlmoco() == '00:00:00' && $this->getFormEntrada() == 'entrada1' ? "<a href=\"javascript:confirma ('Deseja realmente registrar o retorno do intervalo!','entrada3.php')\"><img class='imagem_registra_frequencia' border='0' src='" . _DIR_IMAGEM_ . "edicao2.jpg' width='16' height='16' align='absmiddle' title='Encerrar Intervalo' alt='Encerrar Intervalo'></a>" : "");
            }

            ## fim do expediente
            #
				$html .= "</div></td><td colspan='2' width='18%' height='35' class='ftFormFreq-bc-3'><div align='center'>" . ($this->getSaida() == '00:00:00' && $this->getFormEntrada() == 'entrada1' ? "<a href=\"javascript:confirma ('Deseja realmente registrar o fim do expediente!','entrada4.php')\"><img class='imagem_registra_frequencia' border='0' src='" . _DIR_IMAGEM_ . "edicao2.jpg' width='16' height='16' align='absmiddle' title='Encerrar Expediente' alt='Encerrar Expediente'></a>" : "") . "<input name='sai' type='text' class='centro' id='sai' value='" . $this->getSaida() . "' size='10' readonly></div></td></tr>";

            if ($this->getFormEntrada() == 'entrada1')
            {
                // INSERIDO NO NOVO LAYOUT
                // solicita autorizacao para trabalhar em dia não útil
                $html .= "<tr bgcolor='#DFDFBF'>";
                $html .= "<td colspan='2' height='21' align='center'>";
                $html .= "<input type='hidden' id='dados' name='dados' value='" . base64_encode($this->getCodigoMunicipio()) . "'>";
                $html .= "<font size='2'>Solicita&ccedil;&atilde;o para trabalho em dia n&atilde;o &uacute;til.</font> <a href='autorizacao_trabalho_dia_nao_util_solicitacao.php?dados=" . base64_encode($this->getCodigoMunicipio()) . "' target='new'><img border='0' src='" . _DIR_IMAGEM_ . "edicao2.jpg' width='16' height='16' align='absmiddle' title='Solicitar Autorização para trabalho em dia não útil' alt='Solicitar Autorização para trabalho em dia não útil'></a>";
                $html .= "</td>";

                // INSERIDO NO NOVO LAYOUT
                // visualizar a frequência do mês corrente
                $html .= "<td colspan='1' height='21' align='center'>";
                $html .= "<font size='2'>Visualizar frequ&ecirc;ncia do m&ecirc;s.<a href='entrada6.php?cmd=1&orig=1&lotacao=" . $this->getLotacao() . "' target='new'><img src='" . _DIR_IMAGEM_ . "pesquisa.gif' width='17' height='17' border='0' align='absmiddle' title='Visualizar frequênciado do mês' alt='Visualizar frequênciado do mês'></a></font>";
                $html .= "</td>";

                // INSERIDO NO NOVO LAYOUT
                // visualizar a frequência de meses anteriores
                $html .= "<td colspan='2' height='21' align='center'>";
                $html .= "<font size='2'>Visualizar meses anteriores. <a href='entrada8.php?cmd=2&orig=1' target='new'><img border='0' src='" . _DIR_IMAGEM_ . "pesquisa.gif' width='17' height='17' title='Visualizar meses anteriores' alt='Visualizar meses anteriores'></a></font>";
                $html .= "</td>";
                $html .= "</tr>";
            }

            $html .= "</table></td></tr></table>";

            print $html;

            $html = "";
            if ($this->getFormEntrada() == 'entrada1')
            {
                if ($_SESSION['sDefVisual'] == 'N')
                {
                    $horas_trabalhadas_ate_o_momento = horas_trabalhadas_ate_o_momento($this->getSiape(), $this->getDataHoje());

                    $html .= "
						<div align='center'>
						<table width='97%' border='0' align='center' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse'>
						<tr bgcolor='#F8F8EF' style='text-align: center;'>
						<td style='width: 50%; height: 20px; vertical-align: middle; font-family: verdana; font-size: 12;'>" . $horas_trabalhadas_ate_o_momento . "</td>
						<td style='width: 50%; height: 20px; vertical-align: middle; font-family: verdana; font-size: 12;'><a href='entrada9.php' target = 'new' title='Visualizar demonstrativo de compensações' alt='Visualizar demonstrativo de compensações'>Visualizar demonstrativo de compensações.</a></td>
						</tr>
						</table>
						</div>";
                }
                //INSERIDO NOVO LAYOUT
                $nInd = 0;
                switch ($rows)
                {
                    case "0":
                        $nInd = ($this->getIDH() == "1" ? 0 : 1);
                        break;
                    default:
                        $nInd = ($this->getIDH() == "1" ? 2 : 3);
                        break;
                }

                //REFERENTE A MENSAGEM
                //INSERIDO NOVO LAYOUT
                $html .= "<div align='center' style='height: 20;'><font face='verdana' size='4'>" . $this->getMensagem($nInd) . "</font></div>";
            }

            $html .= "<div align='center' style='height: 29; text-align: top;'><a href='finaliza.php'><img border='0' src='" . $this->getBotaoFechar() . "' width='" . $this->getBotaoFecharW() . "' height='" . $this->getBotaoFecharH() . "' title='Sair' alt='Sair'></a></div></form>";

            print $html;
        }
        if ($this->getFormEntrada() == 'entrada1')
        {
            mensagens_comunicacao_social();
        }
        //TODO: Checar funcao
        ##
        # Registra se a entrada foi 20 min antes
        # ou 20 min depois do horário de entrada definido
        #
				mensagemHorarioDifere($this->getSiape(), 'limite_entrada', $this->getTurnoEstendido(), $this->getChefiaAtiva(), $this->getLotacao(), $this->getSituacaoCadastral(), $this->getHoraEntrada(), $this->getHoraEntradaReal(), $this->getDiaUtil());


        parent::exibeCorpoBaseHTML();
        parent::exibeBaseHTML();

    }

}
