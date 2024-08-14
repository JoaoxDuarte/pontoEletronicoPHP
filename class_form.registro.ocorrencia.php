<?php

// @package class
include_once( "class_form.frequencia.php" );
include_once("class_ocorrencias_grupos.php");

## @class
#+-------------------------------+
#| Formulario Siape, Competencia |
#+-------------------------------+
#
class formRegistraOcorrencia extends formFrequencia
{

    var $dados_origem;    // string
    var $form_action;
    var $form_submit;
    var $siape;
    var $nome;
    //var $dia;
    //var $ocor;
    var $retorna;

    ## @constructor
    #+----------------------------+
    #| Construtor da classe       |
    #+----------------------------+
    #

    function formRegistraOcorrencia()
    {
        parent::formFrequencia();
    }

    ## @metodo
    #+----------------------------+
    #| Define/Le os atributos     |
    #+----------------------------+
    #

    function setDadosOrigem($string = "")
    {
        $this->dados_origem = $string;
    }

    function getDadosOrigem()
    {
        return $this->dados_origem;
    }

    function setFormAction($string = "")
    {
        $this->form_action = $string;
    }

    function getFormAction()
    {
        return $this->form_action;
    }

    function setFormSubmit($string = "")
    {
        $this->form_submit = $string;
    }

    function getFormSubmit()
    {
        return $this->form_submit;
    }

    function setSiape($string = "")
    {
        $this->siape = $string;
    }

    function getSiape()
    {
        return $this->siape;
    }

    function setNome($string = "")
    {
        $this->nome = $string;
    }

    function getNome()
    {
        return $this->nome;
    }

    //function setData($string="") { $this->dia = $string; }
    //function getData() { return $this->dia; }
    //function setOcorrencia($string="") { $this->ocor = $string; }
    //function getOcorrencia() { return $this->ocor; }

    function setDestino($string = "")
    {
        $this->retorna = $string;
    }

    function getDestino()
    {
        return $this->retorna;
    }

    ## @metodo
    #+----------------------------+
    #| Exibe o formulário         |
    #+----------------------------+
    #

    function exibeForm()
    {
        $this->exibeTopoHTML();
        $this->exibeCorpoTopoHTML();

        $html = "<form id='form1' name='form1' method='post' action='" . $this->getFormAction() . "' onSubmit='" . $this->getFormSubmit() . "' valign='middle'>";
        $html .= parent::getInputHidden();
        $html .= "<script type='text/javascript'> var dadosorigem = '" . $this->getDadosOrigem() . "'; </script>";
        $html .= "<div align='center'><table border='0' cellpadding='0' cellspacing='0' style='border-collapse: collapse' bordercolor='#808040' width='99%' id='AutoNumber1'><tr><td colspan='2' class='ft_13_002'>Dados do Servidor:</td></tr></table><table border='1' cellpadding='0' cellspacing='0' style='border-collapse: collapse' bordercolor='#808040' width='99%' id='AutoNumber1'><tr>";

        $html .= "<td width='619' height='46'><p style='margin-top: 0; margin-bottom: 0'><font size='2' face='Tahoma'>Nome:</font></p><p style='margin-top: 0; margin-bottom: 0'>&nbsp;<input type='text' id='nome' name='nome' class='caixa' value='" . $this->getNome() . "' size='60' readonly><div align='center'></div><div align='center'></div></td>";

        $html .= "<td width='144' align='center'><p align='center' style='margin-top: 0; margin-bottom: 0'><font size='2' face='Tahoma'>Mat.Siape:</font></p><p align='center' style='margin-top: 0; margin-bottom: 0'>&nbsp;<input type='text' id='mat' name='mat' class='caixa' value='" . $this->getSiape() . "' size='7' readonly></td>";

        $html .= "</tr></table><table width='99%' border='1' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse; margin-bottom: 0;'><tr>";

        $html .= "<td width='81%' height='39'><p align='left' style='margin-top: 0; margin-bottom: 0'><font size='2' face='Tahoma'>C&oacute;digo da Ocorr&ecirc;ncia:</font></p><p align='left' style='margin-top: 0; margin-bottom: 0'>&nbsp;";

        // tabela de ocorrencia
        $grupo_ocor = "'CH','AB'";
        if ($_SESSION['sRH'] == 'S')
        {
            $grupo_ocor .= ",'RH'";
        }


        $oDBase = selecionaServidor($this->getSiape());
        $sitcad = $oDBase->fetch_object()->sigregjur;

        ## ocorrências grupos
        $obj = new OcorrenciasGrupos();
        $grupoOcorrenciasNegativasDebitos = $obj->GrupoOcorrenciasNegativasDebitos($sitcad, $exige_horarios = true);


        $ocor = $this->getCodigoOcorrencia();

        // instancia
        $oDBase = new DataBase('PDO');

        // verifica se o dia é util e filtra outros códigos que não são compatíveis com o dia
        $exibir_mensagem_erro = false;

        $oDBase->setMensagem("Problemas no acesso a Tabela FACULTATIVO (E30031.".__LINE__.").");
        $oDBase->query("SELECT codigo_debito FROM tabfacultativo172 WHERE dia= :dia AND ativo='S' ",
                array(
                    array(':dia', conv_data($this->getData()), PDO::PARAM_STR)
        ));

        // excluir da lista das ocorrencias
        $codigo_excluir = "00195";
        if (verifica_se_dia_nao_util($exibir_mensagem_erro) == false)
        {
            $codigo_excluir .= "," . implode(',', $grupoOcorrenciasNegativasDebitos);
            while ($oDias = $oDBase->fetch_object()) // ponto facultativo compensavel: dia de jogo do Brasil na copa de 2010, Rio+20 e outros.
            {
                $codigo_excluir .= ",'" . $oDias->codigo_debito . "'";
            }
        }
        else
        {
            $codigo_ocorrencia_dia = $oDBase->fetch_object()->codigo_debito;

            $oDBase->setMensagem("Problemas no acesso a Tabela FACULTATIVO (E30032.".__LINE__.").");
            $oDBase->query("SELECT codigo_debito FROM tabfacultativo172 WHERE dia <> :dia AND ativo='S' AND codigo_debito <> :codigo_debito GROUP BY codigo_debito ", array(
                array(':dia', conv_data($this->getData()), PDO::PARAM_STR),
                array(':codigo_debito', $codigo_ocorrencia_dia, PDO::PARAM_STR)
            ));

            while ($oDias = $oDBase->fetch_object()) // não é dia de jogo do Brasil na copa de 2010
            {
                $codigo_excluir .= ",'" . $oDias->codigo_debito . "'";
            }
        }

        $oDBase->setMensagem("Problemas no acesso a Tabela OCORRÊNCIAS (E30033.".__LINE__.").");
        $oDBase->query("SELECT siapecad, desc_ocorr, cod_ocorr FROM tabocfre WHERE resp IN (:resp) and (siapecad NOT IN (:siapecad)) and ativo = 'S' ORDER BY desc_ocorr ",
                array(
                    array(':resp', $grupo_ocor, PDO::PARAM_STR),
                    array(':siapecad', $codigo_excluir, PDO::PARAM_STR)
        ));

        $html .= "<select id='ocor' name='ocor' size='1' class='drop'>";
        while ($campo = $oDBase->fetch_object())
        {
            $html .= "<option value=\"" . tratarHTML($campo->siapecad) . "\"";
            $html .= ($ocor == $campo->siapecad ? 'selected' : '') . ">";
            $html .= tratarHTML($campo->siapecad) . " - " . tratarHTML(substr($campo->desc_ocorr, 0, 60)) . " - " . (empty($campo->desc_ocorr) ? "Selecione uma ocorrência" : "SIRH ") . tratarHTML($campo->cod_ocorr) . " </option>";
        }
        // Fim da tabela de ocorrencia

        $html .= "</select>";

        $html .= "<a href= \"javascript:Abre('tabocfre.php',1060,350)\"><img border= '0' src='" . _DIR_IMAGEM_ . "pesquisa.gif' width='17' height='17' align='absmiddle' alt='Visualizar detalhes da ocorrência.'></a><font size='2' face='Tahoma'> </font></td>";

        $html .= "<td width='19%'><p align='center' style='margin-top: 0; margin-bottom: 0'><font size='2' face='Tahoma'>Dia da Ocorr&ecirc;ncia:</font></p>
				<p align='center' style='margin-top: 0; margin-bottom: 0'><input type='text' id='dia' name='dia' class='centro' value='" . $this->getData() . "' size='11' readonly></td>";

        $html .= "</tr></table></div><div style='text-align: center; vertical-align: middle; border: 0px solid #B0B0B0;'><p style='word-spacing: 0; line-height: 99%; margin-left: 0; margin-right: 0; margin-top: 6'><br><table border='0' style='text-align: center;'><tr>";

        $html .= "<td align='right'>" . botao('Continuar', 'javascript:return testa();') . "</td><td>&nbsp;</td>";
        $html .= "<td align='left'>" . botao('Voltar', 'javascript:' . ($this->getHistoryGo() == 0 ? '' : 'window.history.go(-1);') . 'window.location.replace("' . $this->getDestino() . '");') . "</td>";
        $html .= "</tr></table></p></div></p><font size='2' face='Tahoma'> </font></form>";

        print $html;

        $this->exibeCorpoBaseHTML();
        $this->exibeBaseHTML();
    }

}