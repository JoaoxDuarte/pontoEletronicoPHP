<?php

// @package class
include_once( "config.php" );

## @class
#+-------------------------------+
#| Formulario Telas              |
#+-------------------------------+
#
class formTelas extends formPadrao
{
    private $formAction;   // string;
    private $formOnSubmit; // string;
    private $formUPAG; // string;
    private $formNomeServidor; // string
    private $formMatriculaSiape; // string
    private $formCPF; // string
    private $formLotacaoCodigo; // string
    private $formLotacaoDescricao; // string
    private $formArea; // string
    private $formData; // string
    private $formDataNovaIngresso; // string
    private $formDataIngresso; // string
    private $formDataSaida; // string
    private $formHoraEntrada; // string
    private $formOcorrencia; // string
    private $sessao_navegacao; // object
    private $control_tabela_ocorrencias; // object
    private $liberaServidorUPAG; // boolean
    private $conexao;


    ## @constructor
    #+----------------------------+
    #| Construtor da classe       |
    #+----------------------------+
    #
    function formTelas()
    {
        parent::formPadrao();
        $this->setLiberaServidorUPAG(false);
        $this->sessao_navegacao = new Control_Navegacao();
        $this->conexao          = new DataBase('PDO');
    }


    ## @metodo
    #+----------------------------+
    #| SET/GET                    |
    #+----------------------------+
    ##
    # Action form
    #
    function setFormAction($valor = '#')
    {
        $this->formAction = $valor;
    }

    function getFormAction()
    {
        return $this->formAction;
    }


    ##
    # OnSubmit form
    #
    function setFormOnSubmit($valor = 'javascript:return false;')
    {
        $this->formOnSubmit = $valor;

    }

    function getFormOnSubmit()
    {
        return $this->formOnSubmit;

    }


    ##
    # Dados: UPAG
    #
    function setFormUPAG($valor = '')
    {
        $this->formUPAG = $valor;
    }

    function getFormUPAG()
    {
        return $this->formUPAG;
    }


    ##
    # Dados: Nome
    #
    function setFormNomeServidor($valor = '')
    {
        $this->formNomeServidor = $valor;
    }

    function getFormNomeServidor()
    {
        return $this->formNomeServidor;
    }


    ##
    # Dados: Siape
    #
    function setFormMatriculaSiape($valor = '')
    {
        $this->formMatriculaSiape = $valor;
    }

    function getFormMatriculaSiape()
    {
        return $this->formMatriculaSiape;
    }


    ##
    # Dados: CPF
    #
    function setFormCPF($valor = '')
    {
        $this->formCPF = $valor;
    }

    function getFormCPF()
    {
        return $this->formCPF;
    }


    ##
    # Dados: Lotação Código

    #
		function setFormLotacaoCodigo($valor = '')
    {
        $this->formLotacaoCodigo = $valor;

    }

    function getFormLotacaoCodigo()
    {
        return $this->formLotacaoCodigo;
    }


    ##
    # Dados: Lotação Descrição
    #
    function setFormLotacaoDescricao($valor = '')
    {
        $this->formLotacaoDescricao = $valor;
    }

    function getFormLotacaoDescricao()
    {
        return $this->formLotacaoDescricao;
    }


    ##
    # Dados: Área
    #
    function setFormArea($valor = '#')
    {
        $this->formArea = $valor;
    }

    function getFormArea()
    {
        return $this->formArea;
    }


    ##
    # Dados: Data
    #
    function setFormData($valor = '#')
    {
        $this->formData = $valor;
    }

    function getFormData()
    {
        return $this->formData;
    }


    ##
    # Dados: Data de Ingresso em nova unidade
    #
    function setFormDataNovaIngresso($valor = '#')
    {
        $this->formDataNovaIngresso = $valor;
    }

    function getFormDataNovaIngresso()
    {
        return $this->formDataNovaIngresso;
    }


    ##
    # Dados: Data de Ingresso
    #
    function setFormDataIngresso($valor = '#')
    {
        $this->formDataIngresso = $valor;
    }

    function getFormDataIngresso()
    {
        return $this->formDataIngresso;
    }


    ##
    # Dados: Data de Saída
    #
    function setFormDataSaida($valor = '#')
    {
        $this->formDataSaida = $valor;
    }

    function getFormDataSaida()
    {
        return $this->formDataSaida;
    }


    ##
    # Dados: Hora Entrada
    #
    function setFormHoraEntrada($valor = '#')
    {
        $this->formHoraEntrada = $valor;
    }

    function getFormHoraEntrada()
    {
        return $this->formHoraEntrada;
    }


    ##
    # Dados: Hora Saida para Almoco
    #
    function setFormHoraSaidaAlmoco($valor = '#')
    {
        $this->formHoraSaidaAlmoco = $valor;
    }

    function getFormHoraSaidaAlmoco()
    {
        return $this->formHoraSaidaAlmoco;
    }


    ##
    # Dados: Hora Volta do Almoco
    #
    function setFormHoraVoltaAlmoco($valor = '#')
    {
        $this->formHoraVoltaAlmoco = $valor;
    }

    function getFormHoraVoltaAlmoco()
    {
        return $this->formHoraVoltaAlmoco;
    }


    ##
    # Dados: Hora Saida
    #
    function setFormHoraSaida($valor = '#')
    {
        $this->formHoraSaida = $valor;
    }

    function getFormHoraSaida()
    {
        return $this->formHoraSaida;
    }


    ##
    # Dados: Ocorrencia
    #
    function setFormOcorrencia($valor = '#')
    {
        $this->formOcorrencia = $valor;
    }

    function getFormOcorrencia()
    {
        return $this->formOcorrencia;
    }


    ##
    # Liberar servidor para outra UPAG
    #
    function setLiberaServidorUPAG($valor = '#')
    {
        $this->liberaServidorUPAG = $valor;
    }

    function getLiberaServidorUPAG()
    {
        return $this->liberaServidorUPAG;
    }


    ##
    # Descrição da ocorrência
    #
    function getOcorrenciaDescricao($codigo = '')
    {
        $descricao = "";

        if (empty($codigo))
        {
            $this->conexao->query("SET NAMES'utf8'");
            $this->conexao->query('SET character_set_connection=utf8');
            $this->conexao->query('SET character_set_client=utf8');
            $this->conexao->query('SET character_set_results=utf8');
            $this->conexao->query("SELECT desc_ocorr AS descricao FROM tabocfre WHERE siapecad='$codigo' ");
            $this->conexao->query($sql);
            $reg = $this->conexao->fetch_array();

            if ($this->conexao->num_rows() > 0)
            {
                $descricao = $reg['descricao'];
            }
        }

        return $descricao;
    }


    ## @metodo
    #+----------------------------+
    #| Dados do servidor          |
    #+----------------------------+
    #
    function setDadosDoServidor()
    {
        $this->setHTML("<p align='left' style='word-spacing: 0; line-height: 95%; margin-left: 22; margin-right: 0; margin-top: 6; margin-bottom: 0'><strong><font size='2' face='Tahoma'>Dados do Servidor:</font></strong></p><table class='table table-striped table-condensed table-bordered text-center'><tr><td width='619' height='46'>");

        ##
        # Nome do servidor
        $this->setCampoNome();
        $this->setInputValue($this->getFormNomeServidor());
        $this->setHTML($this->campoNome());

        $this->setHTML("</td><td width='144' align='center'>");

        ##
        # Matrícula CPF do servidor
        $strCPF = $this->getFormCPF();
        if ($strCPF != '')
        {
            $this->setCampoCPF();
            $this->setInputID('sCpf');
            $this->setInputValue($strCPF);
            $this->setHTML($this->campoCPF());
            $this->setHTML("</td>");
            $this->setHTML("<td>");
        }

        ##
        # Matrícula SIAPE do servidor
        $this->setCampoSiape();
        $this->setInputID('mat');
        $this->setInputClass('form-control');
        $this->setInputValue($this->getFormMatriculaSiape());
        $this->setHTML($this->campoSiape());

        $this->setHTML("</td></tr></table>");
    }


    ## @metodo
    #+----------------------------+
    #| Abre <FORM>                |
    #+----------------------------+
    #
    function getAbreForm()
    {
        $html  = "<form";
        $html .= (empty($this->getFormAction()) ? "" : " action=\"" . $this->getFormAction() . "\"");
        $html .= " method='POST' id='form1' name='form1'";
        $html .= (empty($this->getFormOnSubmit()) ? "" : " onSubmit=\"" . $this->getFormOnSubmit() . "\"");
        $html .= ">";
        return $html;
    }


    ## @metodo
    #+----------------------------+
    #| Fecha <FORM>               |
    #+----------------------------+
    #
    function getFechaForm()
    {
        return "</form>";
    }


    ## @metodo
    #+---------------------------------+
    #| Exibe o formulário - Registro13 |
    #+---------------------------------+
    #
    function exibeTelaRegistro13()
    {
        // tabela ocorrencias
        $ocorrencia = $this->getFormOcorrencia();

        // inicio do formulario
        $this->exibeTopoHTML();
        $this->exibeCorpoTopoHTML();

        $this->initHTML();
        $this->setHTML($this->getAbreForm());

        $this->setDadosDoServidor();

        $this->setHTML($this->getInputHidden());
        $this->setHTML("<table class='table table-striped table-condensed table-bordered text-center'>");

        // Código e descrição da Ocorrência
        $this->setHTML("<tr>");
        $this->setHTML("<td colspan='5' style='width: 100%; height: 30;'>");
        $this->setHTML("<font class='ft_13_002'>&nbsp;" . tratarHTML($ocorrencia) . ' - ' . $this->getOcorrenciaDescricao($ocorrencia) . "&nbsp;</font>");
        $this->setHTML("</td>");
        $this->setHTML("</tr>");

        $this->setHTML("<tr>");

        // Dia da Ocorrência
        $this->setHTML("<td style='width: 15%; height: 39;'>");
        $this->setCampoData();
        $this->setInputID('dia');
        $this->setInputTitulo('Dia da Ocorr&ecirc;ncia:');
        $this->setInputTitle('');
        $this->setInputValue($this->getFormData());
        $this->setHTML($this->campoData());
        $this->setHTML("</td>");

        // Hora de Início do Expediente
        $this->setHTML("<td width='22%'>");
        $this->setCampoHora();
        $this->setInputID('entra');
        $this->setInputTitulo('Hora de In&iacute;cio do Expediente:');
        $this->setInputValue($this->getFormHoraEntrada());
        $this->setInputReadOnly(false);
        $this->setHTML($this->campoHora());
        $this->setHTML("</td>");

        // Hora de Início do Intervalo
        $this->setHTML("<td width='22%'>");
        $this->setCampoHora();
        $this->setInputID('iniint');
        $this->setInputTitulo('Hora de In&iacute;cio do Intervalo:');
        $this->setInputValue($this->getFormHoraSaidaAlmoco());
        $this->setInputReadOnly(false);
        $this->setHTML($this->campoHora());
        $this->setHTML("</td>");

        // Hora de Retorno do Intervalo
        $this->setHTML("<td width='22%'>");
        $this->setCampoHora();
        $this->setInputID('fimint');
        $this->setInputTitulo('Hora de Retorno do Intervalo:');
        $this->setInputValue($this->getFormHoraVoltaAlmoco());
        $this->setInputReadOnly(false);
        $this->setHTML($this->campoHora());
        $this->setHTML("</td>");

        // Hora da Saída
        $this->setHTML("<td width='22%'>");
        $this->setCampoHora();
        $this->setInputID('hsaida');
        $this->setInputTitulo('Hor&aacute;rio da Sa&iacute;da:');
        $this->setInputValue($this->getFormHoraSaida());
        $this->setInputReadOnly(false);
        $this->setHTML($this->campoHora());
        $this->setHTML("</td>");

        $this->setHTML("</tr></table><div align='center'>");

        // mais campos hidden
        $this->initInputHidden();
        $this->setInputHidden('imin', '1');
        $this->setInputHidden('imax', '3');
        $this->setHTML($this->getInputHidden());

        $this->setHTML("<div align='center'>");
        $this->setHTML("<p>");
        $this->setHTML("<table border='0' align='center'>");
        $this->setHTML("<tr>");

        $this->setHTML("<td align='center'>" . botao('Gravar', 'javascript:' . $this->getFormOnSubmit()) . "</td>");

        if ($this->sessao_navegacao->ContaPaginas() > 2)
        {
            $this->setHTML("<td align='center'>&nbsp;&nbsp;</td>");
            $this->setHTML("<td align='center'>" . botao('Voltar', 'javascript:voltar(1,"' . $this->sessao_navegacao->getPaginaAnterior() . '");') . "</td>");
        }

        $this->setHTML("</tr>");
        $this->setHTML("</table>");
        $this->setHTML("</p>");
        $this->setHTML("</div>");

        $this->setHTML($this->getFechaForm());

        print $this->getHTML();

        $this->exibeCorpoBaseHTML();
        $this->exibeBaseHTML();
    }


    ## @metodo
    #+-----------------------------------------+
    #| Exibe o formulário - Movimenta Servidor |
    #+-----------------------------------------+
    #
    function exibeTelaMovimentaServidor()
    {
        // instancia a class DataBase
        $oDBase = new DataBase('PDO');

        // início da montagem da tela
        $this->exibeTopoHTML();
        $this->exibeCorpoTopoHTML();

        $this->initHTML();
        $this->setHTML($this->getAbreForm());

        $this->setDadosDoServidor();
        $this->setHTML($this->getInputHidden());
        $this->setHTML("<table id='AutoNumber1' border='1' cellpadding='0' cellspacing='0' style='border-color: #808040; border-collapse: collapse; margin-bottom: 0; text-align: center; width: 95%;' align='center'><tr>");

        // Lotação - código e descrição
        $this->setHTML("<td width='490' height='44' align='left' nowrap>");
        $this->setCampoLotacaoCodigo(); // Código
        $this->setInputID('lota');
        $this->setInputValue($this->getFormLotacaoCodigo());
        $this->setInputSize('8');
        $this->setHTML($this->campoLotacaoCodigo());
        $this->setCampoLotacaoDescricao(); // Descrição
        $this->setInputID('wnomelota');
        $this->setInputSize('60');
        $this->setInputValue($this->getFormLotacaoDescricao());
        $this->setHTML('&nbsp;-&nbsp;');
        $this->setHTML($this->campoLotacaoDescricao());
        $this->setHTML("</td>");

        // área
        $this->setCampoArea();
        $this->setInputValue($this->getFormArea());
        $this->setHTML("<td width='110' align='center' nowrap>");
        $this->setHTML($this->campoArea());
        $this->setHTML("</td>");

        // data de ingresso
        $this->setHTML("<td width='115' align='center' nowrap>");
        $this->setCampoData();
        $this->setInputID('dting');
        $this->setInputTitulo('Data de Ingresso:');
        $this->setInputValue($this->getFormDataIngresso());
        $this->setHTML($this->campoData());
        $this->setHTML("</td>");

        if ($this->getLiberaServidorUPAG() == false)
        {
            // data de saída
            $this->setHTML("<td width='115' align='center' nowrap>");
            $this->setCampoData();
            $this->setInputID('dtsai');
            $this->setInputTitulo('Data de Saída:');
            $this->setInputValue($this->getFormDataSaida());
            $this->setInputReadOnly(false);
            $this->setHTML($this->campoData());
            $this->setHTML("</td>");
        }

        $this->setHTML("</tr></table><table class='table table-striped table-condensed table-bordered text-center'><tr>");

        $this->setHTML("<td width='81%' height='42'><p align='left' style='margin-top: 0; margin-bottom: 0'><font size='2' face='Tahoma'>&nbsp;Nova Lotação:</font></p><p align='left' style='margin-top: 0; margin-bottom: 0'>");

        // tabela de lotacao
        $this->setHTML("&nbsp;<select name='novalota' size='1' class='form-control select2-single' id='novalota'>");
        if ($this->getLiberaServidorUPAG() == false)
        {
            // lista somente as unidades vinculadas aa UPAG
            // do operador que está executando a movimentação
            $oDBase->query("SELECT codigo, descricao FROM tabsetor WHERE upag = :upag OR codigo = '00000000000000' ORDER BY codigo",array(
                array( ':upag', $this->getFormUPAG(), PDO::PARAM_STR )
            ));
        }
        else
        {
            // lista todas as unidades, exceto as vinculadas a
            // UPAG do operador que está executando a liberação
            $oDBase->query("SELECT codigo, descricao FROM tabsetor WHERE upag <> :upag OR codigo = '00000000000000' ORDER BY codigo", array(
                array( ':upag', $this->getFormUPAG(), PDO::PARAM_STR )
            ));
        }
        while ($campo = $oDBase->fetch_object())
        {
            $this->setHTML("<option value='" . tratarHTML($campo->codigo) . "'>" . tratarHTML($campo->codigo) . " - " . tratarHTML(substr($campo->descricao, 0, 60)) . "</option>");
        }
        $this->setHTML("</select></p>");
        // Fim da tabela de lotacao

        if ($this->getLiberaServidorUPAG() == false)
        {
            // data de nova lotação
            $this->setHTML("<td width='19%' align='center' nowrap>");
            $this->setCampoData();
            $this->setInputID('dtingn');
            $this->setInputTitulo('Data de Ingresso:');
            $this->setInputValue($this->getFormDataNovaIngresso());
            $this->setInputReadOnly(false);
            $this->setHTML($this->campoData());
            $this->setHTML("</td>");
        }
        else
        {
            // data liberação
            $this->setHTML("<td width='19%' align='center' nowrap>");
            $this->setCampoData();
            $this->setInputID('dtsai');
            $this->setInputTitulo('Data de Liberação:');
            $this->setInputValue($this->getFormDataSaida());
            $this->setInputReadOnly(false);
            $this->setHTML($this->campoData());
            $this->setHTML("</td>");
        }

        $this->setHTML("</tr></table><div align='center'><br><br><input type='image' border='0' src='" . _DIR_IMAGEM_ . "concluir.gif' name='enviar' alt='Submeter os valores' align='center' ></div>");

        $this->setHTML($this->getFechaForm());
        print $this->getHTML();

        $this->exibeCorpoBaseHTML();
        $this->exibeBaseHTML();

    }


    ## @metodo
    #+-----------------------------------------+
    #| Exibe o formulário - Movimenta Servidor |
    #+-----------------------------------------+
    #
    function exibeTelaLiberaServidor()
    {
        $this->setLiberaServidorUPAG(true);
        $this->exibeTelaMovimentaServidor();
    }
}
