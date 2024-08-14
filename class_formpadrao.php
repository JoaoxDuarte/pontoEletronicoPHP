<?php
include_once('config.php');

## @package class
#+---------------------------------------------------------------------+
#|                                                                     |
#|                          FORMULÁRIO PADRÃO                          |
#|                                                                     |
#+---------------------------------------------------------------------+
## @class
#+-------------------------------------------------+
#| Formulario padrao                               |
#+-------------------------------------------------+
#

class formPadrao
{

    private $dir_js;
    private $dir_css;
    private $dir_imagem;

    private $javascript;
    private $style;

    var $css; // string
    var $js; // string

    var $onLoad; // string
    var $OnUnLoad; // string
    var $subTitulo; // string
    var $titulo_topo_janela; // string
    var $com_titulo_sisref; // boolean
    var $ano; // string
    var $mes; // string
    var $mes_inicial; // string
    var $separador_topo;
    var $separador_base;
    var $largura; // string
    var $largura_tipo; // string ('px','%','em',...)
    var $logo; // string
    var $logo_exibe; // boolean
    var $com_titulo_sistema; // boolean
    var $sistema_titulo; // string
    var $icone_para_impressao; // string
    var $menu; // boolean
    var $menu_head; // boolean
    var $menu_head_js; // string
    var $menu_body_js; // string
    var $frames_header; // boolean
    var $caminho; // string
    var $inputHidden; // string
    var $observacaoTopo; // string
    var $observacaoTopoPosicao; // integer
    var $observacaoBase; // string
    var $observacaoBasePosicao; // integer
    var $sPDF; // string
    var $oDB; // object
    var $html; // string
    var $origem; // string
    var $transitional; // boolean
    var $compatibilidade_ie; // string
    var $no_script; // boolean
    var $no_frames; // boolean
    var $new_layout;
    var $fechaContainer;
    protected $codigoErro;

    ## @constructor
    #+----------------------------+
    #| Construtor da classe       |
    #+----------------------------+

    #
    function formPadrao()
    {
        //TODO: marcar como novos requerimentos de CSS
        $this->setPDF('nao');
        $this->setTransitional(true);
        $this->setCompatibilidadeIE('Edge');
        $this->initCSS();

        $this->initJS();

        $this->setMenu(true);
        $this->setMenuHead(false);
        $this->setMenuHeadJS();
        $this->setMenuBodyJS();
        $this->setFramesHeader(false);
        $this->setComTituloSistema(false);
        $this->setSistemaTitulo(_SISTEMA_TITULO_NOME_);
        $this->setCaminho("");
        $this->initInputHidden("");
        $this->initObservacaoTopo("");
        $this->initObservacaoBase("");
        $this->initOnLoad();
        $this->initOnUnLoad();
        $this->initSubTitulo();
        $this->initIconeParaImpressao();
        $this->setTituloTopoJanela("");
        $this->setSeparador(20);
        $this->setLargura('900px');
        //$this->initLogo();
        //$this->setLogo(_DIR_IMAGEM_ . "logo_mp.png");
        //$this->setLogoExibe(false);
        $this->initDBase();
        $this->initHTML();
        $this->setOrigem(pagina_de_origem());
        $this->setNoScriptAtivo(true);
        $this->setNoFramesAtivo(true);
        $this->codigoErro     = '';
        $this->fechaContainer = false;

    }

    public function getCodigoErro()
    {
        return $this->codigoErro;

    }

    public function setCodigoErro($codigoErro)
    {
        $this->codigoErro = $codigoErro;

    }

    public function getFooter()
    {

        $this->verificaDuracaoDaSessao();
        return file_get_contents("html/footer.php");

    }

    public function getHeader()
    {
        $homeLink = "rh";

        if ($_SESSION['sModuloPrincipalAcionado'] != 'sogp')
            $homeLink = "entrada";

        $html = file_get_contents("./html/header-rh.php");

        $html = str_replace("--link--", $homeLink, $html);

        return $html;

    }

    ##
    #  Métodos NO SCRIPT
    #  Indica se utiliza o <noscript> para testar se javascript está ativo
    ##

    function setNoScriptAtivo($value = true)
    {
        $this->no_script = $value;

    }

    function getNoScriptAtivo()
    {
        return $this->no_script;

    }

    ##
    #  Métodos NO FRAMES
    #  Indica se utiliza o <noframes> para testar se o browser suporta frames
    ##

    function setNoFramesAtivo($value = true)
    {
        $this->no_frames = $value;

    }

    function getNoFramesAtivo()
    {
        return $this->no_frames;

    }

    ##
    #  Métodos Origem da solicitação
    #  Arquivo de origem da solicitação
    ##

    function setOrigem($var = '')
    {
        $this->origem = $var;

    }

    function getOrigem()
    {
        return $this->origem;

    }

    ## @metodo
    #+----------------------------+
    #| PDF                        |
    #+----------------------------+

    #
    function setPDF($var = 'nao')
    {
        $this->sPDF = $var;

    }

    function getPDF()
    {
        return $this->sPDF;

    }

    ## @metodo
    #+----------------------------+
    #| DBase                      |
    #+----------------------------+

    #
    function initDBase()
    {
        $this->oDB = new DataBase('PDO');

    }

    function getDBase()
    {
        return $this->oDB;

    }

    ## @metodo
    #+----------------------------+
    #| CSS                        |
    #+----------------------------+

    #
    function initCSS()
    {
        $this->css = "";
        $this->style = array();
    }

    function setCSS($css = '', $media = '')
    {
        if (substr_count($css, "<style") > 0)
        {
            $this->css .= $css;
            $this->style[] = $css;
        }
        else
        {
            $this->css .= "<link type='text/css' rel='stylesheet' href='$css'" . ($media == '' ? '' : " media='print'") . ">";
            $this->style[] = $css;
        }

    }

    function getCSS($new=false)
    {
        if ($new == true)
        {
            return $this->style;
        }
        else
        {
            return $this->css;
        }
    }

    ## @metodo
    #+----------------------------+
    #| JS : Javascript            |
    #+----------------------------+

    #
    function initJS()
    {
        $this->js = "";
        $this->javascript = array();
    }

    function setJS($js = '')
    {
        if (substr_count($js, "<script") > 0)
        {
            $this->js .= $js;
            $this->javascript[] = $js;
        }
        else
        {
            $this->js .= "<script type='text/javascript' src='$js'></script>";
            $this->javascript[] = $js;
        }

    }

    function getJS($new=false)
    {
        if ($new == true)
        {
            return $this->javascript;
        }
        else
        {
            return $this->js;
        }
    }

    ## @metodo
    #+----------------------------+
    #| Ativa uso do flexselect.js |
    #+----------------------------+
    #
    function setFlexSelect()
    {
        $this->initJS();
        $this->setJS(_DIR_JS_ . "funcoes.js?v.0.0.0.0.3");
        $this->setJS(_DIR_JS_ . "fc_data.js");
        $this->setJS(_DIR_JS_ . "desativa_teclas_f_frames.js");
        $this->setJS(_DIR_JS_ . "jquery.js");
        $this->setJS(_DIR_JS_ . "liquidmetal.js");
        $this->setJS(_DIR_JS_ . "jquery.flexselect.js");

    }

    ## @metodo
    #+----------------------------+
    #| Ativa uso do shadowbox.js  |
    #+----------------------------+
    #
    function setShadowBox()
    {
        $this->setDialogModal();
        /*
          $this->setCSS( _DIR_JS_.'shadowbox/shadowbox.css' );
          $this->setJS( _DIR_JS_.'shadowbox/shadowbox.js' );
          $this->setJS( "
          <script type='text/javascript'>
          Shadowbox.init({
          language: 'pt-br',
          player: ['img', 'html', 'swf']
          });
          </script>
          " );
         */

    }

    ## @metodo
    #+----------------------------+
    #| Ativa uso do jquery.dlg.js |
    #+----------------------------+
    #
    function setDialogModal()
    {
        $this->setCSS(_DIR_CSS_ . 'plugins/dlg.min.css');
        $this->initJS();
        $this->setJS(_DIR_JS_ . "funcoes.js?v.0.0.0.0.3");
        $this->setJS(_DIR_JS_ . "fc_data.js");
        $this->setJS(_DIR_JS_ . "desativa_teclas_f_frames.js");
        $this->setJS(_DIR_JS_ . "jquery.js");
        $this->setJS(_DIR_JS_ . "jquery.blockUI.js?v2.38");
        $this->setJS(_DIR_JS_ . "jquery.bgiframe.js");
        $this->setJS(_DIR_JS_ . "plugins/jquery.dlg.min.js");
        $this->setJS(_DIR_JS_ . "plugins/jquery.easing.js");
        $this->setJS(_DIR_JS_ . "jquery.ui.min.js");
    }

    ## @metodo
    #+----------------------------+
    #| Ativa uso do jquery.dlg.js |
    #+----------------------------+
    #
    function setJSDialogProcessando()
    {
        $this->setJS('js/jquery.blockUI.js?v2.38');
        $this->setJS('js/jquery.bgiframe.js');
        $this->setJS('js/plugins/jquery.dlg.min.js');
        $this->setJS('js/plugins/jquery.easing.js');
        $this->setJS('js/jquery.ui.min.js');
    }

    ## @metodo
    #+---------------------------------------+
    #| Date Picker                           |
    #+---------------------------------------+
    #
    function setJSDatePicker()
    {
        $this->setCSS('js/bootstrap-datepicker/css/bootstrap-datepicker3.standalone.min.css');
        $this->setJS('js/bootstrap-datepicker/js/bootstrap-datepicker.min.js');
        $this->setJS('js/bootstrap-datepicker/locales/bootstrap-datepicker.pt-BR.min.js?v.0.0.0.1');
    }

    ## @metodo
    #+---------------------------------------+
    #| Ativa uso do bootstrap-datepicker3.js |
    #+---------------------------------------+
    #
    function setJSSelect2()
    {
        $this->setCSS( 'css/select2.min.css' );
        $this->setCSS( 'css/select2-bootstrap.css' );
        $this->setJS( 'js/select2.full.js' );
    }

    ## @metodo
    #+----------------------------+
    #| Ativa uso do jspdf.js      |
    #+----------------------------+
    #
    public function setJSPDF()
    {
        $this->setJS( "js/html2pdf/jspdf.min.js?v.0.0.0.0.0.0.2" );
        $this->setJS( "js/html2pdf/jspdf.createpdf.js?v.0.0.0.0.0.0.3" );
        //$this->setJS( "js/html2pdf/canvas2image.min.js" );
        $this->setJS( "js/html2pdf/html2canvas.min.js?v.0.0.0.0.2" );
    }

    ## @metodo
    #+----------------------------+
    #| Ativa uso do ckeditor.js   |
    #+----------------------------+
    #
    public function setJSCKEditor($versao='1')
    {
        $this->setCSS( "js/ckeditor/skins/moono/editor.css?v".$versao );
        $this->setJS( "js/ckeditor/ckeditor.js?v".$versao );
        $this->setJS( "js/ckeditor/config.js?v".$versao );
        $this->setJS( "js/ckeditor/lang/pt-br.js?v".$versao );
        $this->setJS( "js/ckeditor/styles.js?v".$versao );
    }

    ## @metodo
    #+---------------------------------------+
    #| Ativa uso apenas do datatables.min.js |
    #+---------------------------------------+
    #
    function setDataTables()
    {
        $this->setCSS( "js/DataTables/datatables.min.css" );
        $this->setJS( "js/DataTables/datatables.min.js" );
        //$this->setCSS( "js/DataTables/css/jquery.dataTables.min.css" );
        $this->setCSS( "js/DataTables/buttons/css/buttons.dataTables.min.css" );
        //$this->setJS( "js/DataTables/jquery-3.3.1.js" );
        //$this->setJS( "js/DataTables/jquery.dataTables.min.js" );
        $this->setJS( "js/DataTables/buttons/dataTables.buttons.min.js" );
        $this->setJS( "js/DataTables/buttons/buttons.flash.min.js" );
        $this->setJS( "js/DataTables/jszip/jszip.min.js" );
        $this->setJS( "js/DataTables/pdfmake/pdfmake.min.js" );
        $this->setJS( "js/DataTables/pdfmake/vfs_fonts.js" );
        $this->setJS( "js/DataTables/buttons/buttons.html5.min.js" );
        $this->setJS( "js/DataTables/buttons/buttons.print.min.js" );
    }

    ## @metodo
    #+---------------------------------------+
    #| Ativa uso apenas do datatables.min.js |
    #+---------------------------------------+
    #
    function setDataTablesCentral()
    {
        $this->setCSS( "js/DataTables/css/jquery.dataTables.min.css" );
        $this->setCSS( "js/DataTables/buttons/css/buttons.dataTables.min.css" );
        $this->setJS( "js/DataTables/jquery-3.3.1.js" );
        $this->setJS( "js/DataTables/jquery.dataTables.min.js" );
        //$this->setJS( "js/DataTables/buttons/dataTables.buttons.min.js" );
        //$this->setJS( "js/DataTables/buttons/buttons.flash.min.js" );
        //$this->setJS( "js/DataTables/jszip/jszip.min.js" );
        //$this->setJS( "js/DataTables/pdfmake/pdfmake.min.js" );
        //$this->setJS( "js/DataTables/pdfmake/vfs_fonts.js" );
        //$this->setJS( "js/DataTables/buttons/buttons.html5.min.js" );
        //$this->setJS( "js/DataTables/buttons/buttons.print.min.js" );
    }

    ## @metodo
    #+-------------------------------+
    #| Ativa uso apenas do jquery.js |
    #+-------------------------------+
    #
    function setJQuery()
    {
        $this->setCSS(_DIR_CSS_ . 'smoothness/jquery-ui-custom-px.min.css');
        $this->setCSS(_DIR_CSS_ . 'plugins/dlg.min.css');
        $this->setCSS(_DIR_CSS_ . "table_sorter.css");
        $this->initJS();
        $this->setJS(_DIR_JS_ . "funcoes.js?v.0.0.0.0.3");
        $this->setJS(_DIR_JS_ . "fc_data.js");
        $this->setJS(_DIR_JS_ . "desativa_teclas_f_frames.js");
        $this->setJS(_DIR_JS_ . "jquery.js");
        $this->setJS(_DIR_JS_ . "jquery.blockUI.js?v2.38");
        $this->setJS(_DIR_JS_ . "plugins/jquery.dlg.min.js");
        $this->setJS(_DIR_JS_ . "plugins/jquery.easing.js");
        $this->setJS(_DIR_JS_ . "jquery.ui.min.js");
        $this->setJS(_DIR_JS_ . "liquidmetal.js");
        $this->setJS(_DIR_JS_ . "jquery.flexselect.js");
        $this->setJS(_DIR_JS_ . "sorttable.js"); // ordena tabela

    }

    ## @metodo
    #+----------------------------+
    #| OnLoad                     |
    #+----------------------------+
    #
    function initOnLoad()
    {
        $this->onLoad = "";

    }

    function setOnLoad($onLoad = '')
    {
        $this->onLoad .= $onLoad;

    }

    function getOnLoad()
    {
        return $this->onLoad;

    }

    ## @metodo
    #+----------------------------+
    #| OnUnLoad                     |
    #+----------------------------+

    #
    function initOnUnLoad()
    {
        $this->OnUnLoad = "";

    }

    function setOnUnLoad($OnUnLoad = '')
    {
        $this->OnUnLoad .= $OnUnLoad;

    }

    function getOnUnLoad()
    {
        return $this->OnUnLoad;

    }

    ## @metodo
    #+----------------------------+
    #| SubTitulo                  |
    #+----------------------------+

    #
    function initSubTitulo()
    {
        $this->subTitulo = array();

    }

    function setSubTitulo($subTitulo = '')
    {
        $this->subTitulo[] = $subTitulo;

    }

    function getSubTitulo($ind = 0)
    {
        if (is_array($this->subTitulo) && count($this->subTitulo) > 0)
        {
            return $this->subTitulo[$ind];
        }
        else
        {
            return "";
        }

    }

    function getSubTituloTam()
    {
        return count($this->subTitulo);

    }

    ## @metodo
    #+----------------------------+
    #| Titulo SISREF              |
    #+----------------------------+

    #
    function setTituloSISREF($sn = true)
    {
        $this->com_titulo_sisref = (_SISTEMA_CORAZUL_ || $sn == true ? true : $sn);

    }

    function getTituloSISREF()
    {
        return $this->com_titulo_sisref;

    }

    ## @metodo
    #+----------------------------+
    #| Titulo tela                |
    #+----------------------------+

    #
    function setTituloTopoJanela($titulo_topo_janela = '')
    {
        $this->titulo_topo_janela = $titulo_topo_janela;

    }

    function getTituloTopoJanela()
    {
        return $this->titulo_topo_janela;

    }

    ## @metodo
    #+----------------------------+
    #| Sistema Titulo             |
    #+----------------------------+

    #
    function setSistemaTitulo($titulo = '')
    {
        $this->sistema_titulo = $titulo;

    }

    function getSistemaTitulo()
    {
        return $this->sistema_titulo;

    }

    ## @metodo
    #+----------------------------+
    #| MENU                       |
    #+----------------------------+

    #
    function setMenu($menu = true)
    {
        $this->menu = $menu;

    }

    function getMenu()
    {
        return $this->menu;

    }

    function setMenuHead($menu = true)
    {
        $this->menu_head = $menu;

    }

    function getMenuHead()
    {
        return $this->menu_head;

    }

    function setMenuHeadJS($menu = "frames_header_array.js")
    {
        $this->menu_head_js = $menu;

    }

    function getMenuHeadJS()
    {
        return $this->menu_head_js;

    }

    function setMenuBodyJS($menu = "frames_body_array.js")
    {
        $this->menu_body_js = $menu;

    }

    function getMenuBodyJS()
    {
        return $this->menu_body_js;

    }

    ## @metodo
    #+----------------------------+
    #| FRAMES HEAD                |
    #+----------------------------+

    #
    function setFramesHeader($frameshead = false)
    {
        $this->frames_header = $frameshead;

    }

    function getFramesHeader()
    {
        return $this->frames_header;

    }

    ## @metodo
    #+----------------------------+
    #| Caminho                    |
    #+----------------------------+

    #
    function setCaminho($caminho = '')
    {
        $geraPDF       = $this->getPDF();
        $this->caminho = ($geraPDF == 'sim' ? '' : $caminho);

    }

    function getCaminho()
    {
        return $this->caminho;

    }

    ## @metodo
    #+----------------------------+
    #| Figura do Logo             |
    #+----------------------------+

    #
    function initLogo()
    {
        $this->logo = '';

    }

    function setLogo($logo = '', $largura = '', $largura_logo = '900px', $alinhamento_logo = 'left')
    {
        if ($logo != "")
        {
            $largura = ($largura == '' ? $this->getLargura() : $largura) . $this->getLarguraTipo();
            if (_SISTEMA_CORAZUL_)
            {
                $this->logo = "<table width='$largura' border='0' cellspacing='0' cellpadding='0' align='center' valign='top' style='word-spacing: 0; margin-top: 0; margin-bottom: 0;'><tr><td style='background: url($logo); width: $largura_logo; height: 80px; text-align: right; vertical-align: middle; color: white;' class='ft_18_001' colspan='16'>
						<div class='sombra-texto' style='width:99%;padding:30px 0px 0px 0px;border: 0px solid red;color:#FFFFFF;font-family:helvetica;font-size:20;text-align:right;'>
						SISREF<br>
						<span style='font-size:9px;'>Sistema de Registro Eletrônico de Frequência</span>
					</div>
					</td></tr></table>";
            }
            else
            {
                $this->logo = "<table width='$largura' border='0' cellspacing='0' cellpadding='0' align='center' valign='top' style='word-spacing: 0; margin-top: 0; margin-bottom: 0;'><tr><td style='text-align:$alinhamento_logo;'><img src='$logo' height='95' border='0'></td></tr></table>";
            }
        }

    }

    function getLogo()
    {
        return $this->logo;

    }

    function setLogoExibe($valor = false)
    {
        $this->logo_exibe = $valor;

    }

    function getLogoExibe()
    {
        return $this->logo_exibe;

    }

    ## @metodo
    #+----------------------------+
    #| Figura do Logo             |
    #+----------------------------+

    #
    function setComTituloSistema($com = false)
    {
        $this->com_titulo_sistema = $com;

    }

    function getComTituloSistema()
    {
        return $this->com_titulo_sistema;

    }

    ## @metodo
    #+----------------------------+
    #| Icone para impressao       |
    #+----------------------------+

    #
    function initIconeParaImpressao()
    {
        $this->icone_para_impressao = '';

    }

    function setIconeParaImpressao($arquivo = '',$print_window=false)
    {
        $geraPDF = $this->getPDF();
        if ($arquivo != '' && $geraPDF != 'sim')
        {
            if ($arquivo == 'print')
            {
                $this->icone_para_impressao = "<a id='prepara_impressao' title='Preparar Página para Impressão' href=\"javascript:window.print();\"><img  src='" . _DIR_IMAGEM_ . "printer.gif' height=40 border='0'></a>";
            }
            else
            {
                $this->icone_para_impressao = "<a id='prepara_impressao' title='Preparar Página para Impressão' href=\"javascript:var tela = window.open('" . $arquivo . "','','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,maximized=yes,width=865, height=665');\"><img  src='" . _DIR_IMAGEM_ . "printer.gif' height=40 border='0'></a>";
            }
        }

    }

    function getIconeParaImpressao()
    {
        return $this->icone_para_impressao;

    }

    ## @metodo
    #+----------------------------+
    #| Input Hidden               |
    #+----------------------------+

    #
    function initInputHidden()
    {
        $this->inputHidden = "";

    }

    function setInputHidden($nome = '', $valor = '')
    {
        $this->inputHidden .= "<input type='hidden' id='$nome' name='$nome' value='$valor'>";

    }

    function getInputHidden()
    {
        return $this->inputHidden;

    }

    ## @metodo
    #+----------------------------+
    #| Observacao Topo            |
    #+----------------------------+

    #
    function initObservacaoTopo()
    {
        $this->observacaoTopo        = "";
        $this->observacaoTopoPosicao = "center";

    }

    function setObservacaoTopo($observacaoTopo = '', $posicao = 'center')
    {
        $this->observacaoTopo        = $observacaoTopo;
        $this->observacaoTopoPosicao = $posicao;

    }

    function getObservacaoTopo()
    {
        return $this->observacaoTopo;

    }

    function getObservacaoTopoPosicao()
    {
        return $this->observacaoTopoPosicao;

    }

    ## @metodo
    #+----------------------------+
    #| Observacao Base            |
    #+----------------------------+

    #
    function initObservacaoBase()
    {
        $this->observacaoBase        = "";
        $this->observacaoBasePosicao = "center";

    }

    function setObservacaoBase($observacaoBase = '', $posicao = 'center')
    {
        $this->observacaoBase        = $observacaoBase;
        $this->observacaoBasePosicao = $posicao;

    }

    function getObservacaoBase()
    {
        return $this->observacaoBase;

    }

    function getObservacaoBasePosicao()
    {
        return $this->observacaoBasePosicao;

    }

    ## @metodo
    #+----------------------------+
    #| Mes                        |
    #+----------------------------+

    #
    function setMes($mes = '')
    {
        $this->mes = $mes;

    }

    function getMes()
    {
        return $this->mes;

    }

    ## @metodo
    #+----------------------------+
    #| Ano                        |
    #+----------------------------+

    #
    function setAno($ano = '')
    {
        $this->ano = $ano;

    }

    function getAno()
    {
        return $this->ano;

    }

    ## @metodo
    #+----------------------------+
    #| Mes_inicial                |
    #+----------------------------+

    #
    function setMesInicial($mes = '')
    {
        $this->mes_inicial = $mes;

    }

    function getMesInicial()
    {
        return $this->mes_inicial;

    }

    ## @metodo
    #+----------------------------+
    #| Altura da imagem/separador |
    #+----------------------------+

    #
    function setSeparador($height = '')
    {
        $this->separador_topo = $height;
        $this->separador_base = $height;

    }

    function setSeparadorTopo($height = '')
    {
        $this->separador_topo = $height;

    }

    function getSeparadorTopo()
    {
        return $this->separador_topo;

    }

    function setSeparadorBase($height = '')
    {
        $this->separador_base = $height;

    }

    function getSeparadorBase()
    {
        return $this->separador_base;

    }

    function getNewLayout()
    {
        return $this->new_layout;

    }

    /**
     * @param mixed $new_layout
     */
    public function setNewLayout($new_layout)
    {
        $this->new_layout = $new_layout;

    }

    ## @metodo
    #+----------------------------+
    #| Largura da tabela          |
    #+----------------------------+

    #
    function setLargura($largura = '')
    {
        $this->largura      = soNumeros($largura);
        $this->largura_tipo = soLetras($largura);

    }

    function getLargura()
    {
        return $this->largura;

    }

    function getLarguraTipo()
    {
        return $this->largura_tipo;

    }

    ## @metodo
    #+----------------------------+
    #| Transactional              |
    #+----------------------------+

    #
    function setTransitional($valor = true)
    {
        $this->transitional = $valor;

    }

    function getTransitional()
    {
        return $this->transitional;

    }

    ## @metodo
    #+----------------------------+
    #| Compatibilidade IE         |
    #+----------------------------+

    #
    function setCompatibilidadeIE($valor = 'IE7')
    {
        $_SESSION['compatibilidade_ie'] = $valor;
        $this->compatibilidade_ie       = $valor;

    }

    function getCompatibilidadeIE()
    {
        return $this->compatibilidade_ie;

    }

    ## @metodo
    #+----------------------------+
    #| PDF cabecalho              |
    #+----------------------------+

    function getPDFCabecalho($sistema = '', $subtitulo = '')
    {
        $PDFcabec = "
			<table border='0' cellpadding='0' cellspacing='0' style='border-collapse: collapse' bordercolor='#808040' width='100%' autosize='1'><tr><td width='100%'  align='center' style='font-family:verdana; font-size:12pt'><div align='center'><h3><table border='0' cellspacing='0' cellpadding='0' style='margin: 0px 0px 0px 0px; text-align: center; width: 100%;'><tr><td style='width: 105px; border: 0px solid #808080;'>&nbsp;</td><td style=' width: 60%; height: 60px; vertical-align: middle;'><p align='center' class='ft_18_001'>" . ($sistema == '' ? "SISREF - Sistema de Registro Eletrônico de Frequência" : $sistema) . "</p></td><td width='20%'><img src='" . _DIR_IMAGEM_ . "transp1x1.gif' height='40' width='105px' border='0'></td></tr></table>";
        if ($subtitulo != '')
        {
            $PDFcabec .= "<table align='center' border='0' width='100%' cellspacing='0' cellpadding='0'><tr><td colspan='3' align='center'><font class='ft_16_001'>" . $subtitulo . "</font></td></tr><tr><td colspan='3' align='center'><img src='" . _DIR_IMAGEM_ . "transp1x1.gif' height='20' border='0'></td></tr></table></h3></div></td></tr></table>";
        }
        return $PDFcabec;

    }

    ## @metodo
    #+----------------------------+
    #| HTML componenentes         |
    #+----------------------------+
    ##
    # HTML Inicializar

    #
    function initHTML()
    {
        $this->html = "";

    }

    function setHTML($html = '')
    {
        $this->html .= $html;

    }

    function getHTML()
    {
        return $this->html;

    }

    function printHTML()
    {
        print $this->getHTML();

    }

    function exibeHTML()
    {
        $this->printHTML();

    }

    function showHTML()
    {
        $this->printHTML();

    }

    /**
     * @Method setTituloTopoJanela
     *
     * @info Exibe um texto na barra superior da janela
     * @usage setTituloApl( '0999999', 'NOME DO SERVIDOR' );
     *
     * @param string $sMatricula Matricula SIAPE
     * @param string $sNome      Nome do usuário
     * @return : void
     * @author : Edinalvo Rosa
     *
     * @dependence : void
     */
    function printTituloTopoJanela($titulo=null)
    {
        $titulo = (is_null($titulo) ? $this->getTituloTopoJanela() : $titulo);

        echo "
            <script>
                top.document.title = '" . _SISTEMA_SIGLA_ . " | " . $titulo . "';
                document.status = '';
            </script>
        ";
    }

    ##
    # HTML Logo

    #
    function setHTMLLogo()
    {
        if ($this->getLogoExibe() == true)
        {
            $this->setHTML($this->getLogo());
        }

    }

    ##
    # HTML Flexigrid

    #
    function printHTMLFlexiGrid($nome = 'flex1', $js = '')
    {
        print setHTMLFlexiGrid($nome, $js, true);

    }

    function setHTMLFlexiGrid($nome = 'flex1', $js = '', $print = false)
    {
        if ($js != '')
        {
            $html = "
				<center><table id='flex1' style='display:none; text-align: center;' align='center'></table></center>
				<script type='text/javascript' src='$js'></script>";
            if ($print == true)
            {
                return $html;
            }
            else
            {
                $this->setHTML($html);
            }
        }

    }

    ##
    # HTML Caminho
    #

    //TODO: Barras de notificação ficam aqui
    function setHTMLCaminho($print='print')
    {
        //$html = "
        //<style>
        //    #barra_div { width: " . $this->getLargura() . $this->getLarguraTipo() . "; border-bottom: 1px solid #006C36; }
        //    #barra_caminho {float:left;text-align:left;width:49%;height:11px;font-size:12px;font-weight:bold;color:#FFFFFF;background:none;padding:5px 0px 0px 0px;}
        //    #barra_usuario {float:right;text-align:right;width:60%;height:15px;font-size:12px;font-weight:bold;color:#FFFFFF;background:none;margin:20px 0px 0px 0px;}
        //</style>
        //<div id='barra_div' align='center'>
        //";

        $html = "
        <style>
            #barra_div { width: 1000px; border-bottom: 1px solid #006C36; }
            #barra_caminho {float:left;text-align:left;width:49%;height:11px;font-size:12px;font-weight:bold;color:#FFFFFF;background:none;padding:5px 0px 0px 0px;}
            #barra_usuario {float:right;text-align:right;width:60%;height:15px;font-size:12px;font-weight:bold;color:#FFFFFF;background:none;margin:20px 0px 0px 0px;}
        </style>
        <div id='barra_div' align='center'>
        ";

        if ($this->getCaminho() != '')
        {
            /*
            $html .= "
            <div id='barra_caminho'>" . $this->getCaminho() . "</div>
            ";
            */
        }

        if ($_SESSION['sMatricula'] != '' && $_SESSION['sModuloPrincipalAcionado'] != 'entrada')
        {
            $identificacao_apelido = ($_SESSION['sIdentificacaoApelido'] == '' ? $_SESSION['sNome'] : $_SESSION['sIdentificacaoApelido']);

            // Formulário de troca de contexto de upag para os perfis de órgão central
            /*$formUpag = '';
            if(isset($_SESSION['sGestaoUPAG']) && $_SESSION['sGestaoUPAG'] == 'S') {
                $formUpag = '&nbsp;<a href="javascript:$(\'#troca-contexto-upag\').modal()">Trocar Contexto de Upag</a>' ;      
            }*/

            $html .= "<div id='barra_usuario' class='sombra-texto text-nowrap' style='font-weight:bold;font-size:11px;font-family:arial;'>" 
                    . $pagina_de_origem 
                    . getOrgaoByUorg($_SESSION['sLotacao']) 
                    . "." 
                    . removeOrgaoMatricula($_SESSION['sMatricula']) 
                    . ' - ' 
                    . nome_sobrenome($identificacao_apelido) 
                    . '<br>' 
                    . 'UPAG: ' 
                    . removeOrgaoLotacao($_SESSION['upag']) 
                    . '-' 
                    . getUpagDescricao($_SESSION['upag'])
                    . "</div>";
        }

        $html .= "
        </div>
        ";

        if (pagina_de_origem() == 'entrada.php' && ($_SESSION['sDefVisual'] == 'S' || $_SESSION['sMatricula'] == '9000000x'))
        {
            /*
            $html .= "
            <div style='width:100%;border-bottom:1px solid #006C36;' align='left'>
                <div style='width:100%;text-align:left;font-size:9px;'><b>Teclas de Atalho:</b><br></div>
                <div style='width:100%;text-align:left;font-size:9px;'><i>Saída para Almoçar:</i> Alt+2. <i>Retorno do Almoço:</i> Alt+3. <i>Fim do Expediente:</i> Alt+4.</div>
                <div style='width:100%;text-align:left;font-size:9px;'><i>Solicitar trabalhar em dia não útil:</i> Alt+5. <i>Visualizar frequências do mês:</i> Alt+6. <i>Visualizar frequências anteriores:</i> Alt+7. <i>Visualizar saldos de compensações:</i> Alt+8.</div>
                </div>
            ";
            */
        }

        switch ($print)
        {
            case 'print':
                print $html;
                break;

            case 'return':
                return $html;
                break;

            default:
                $this->setHTML( $html );
                break;
        }

    }

    ##
    # HTML Titulo do Sistema

    #
    function setHTMLSistemaTitulo()
    {
        if ($this->getComTituloSistema() == true && $this->getSistemaTitulo() != "")
        {
            $this->setHTML("<table border='0' cellspacing='0' cellpadding='0' style='margin: 0px 0px 0px 0px; text-align: center; width: 100%;'><tr><td style='width: 105px; border: 0px solid #808080;'>" . $this->getIconeParaImpressao() . "</td><td style=' width: 60%; height: 60px; vertical-align: middle;'><p align='center' class='ft_18_001 sombra_texto'>" . $this->getSistemaTitulo() . "</p></td><td width='20%'><img src='" . _DIR_IMAGEM_ . "transp1x1.gif' height='40' width='105px' border='0'></td></tr></table>");
        }

    }

    ##
    # HTML Sub-Titulo do Sistema

    #
    function setHTMLSistemaSubTitulo()
    {
        $subTituloTam = $this->getSubTituloTam();
        for ($i = 0; $i < $subTituloTam; $i++)
        {
            switch ($i)
            {
                case 0:
                    $font = 'ft_16_001';
                    break;
                case 1:
                    $font = 'ft_14_001';
                    break;
                case 2:
                    $font = 'ft_13_001';
                    break;
                default:
                    $font = 'ft_12_001';
                    break;
            }
            $subTitulo = $this->getSubTitulo($i);
            if (empty($subTitulo))
                {
                continue;
            }
            $this->setHTML("<table align='center' border='0' width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr><td colspan='3' align='center'><font class='" . $font . "'>" . $subTitulo . "</font></td></tr><tr><td colspan='3' align='center'><img src='" . _DIR_IMAGEM_ . "transp1x1.gif' height='20' border='0'></td></tr></table>");
        }

    }

    ##
    # HTML Observacao abaixo titulo do Sistema

    #
    function setHTMLObservacaoTopo()
    {
        $obsTopo        = $this->getObservacaoTopo();
        $obsTopoPosicao = $this->getObservacaoTopoPosicao();
        if ($obsTopo != "")
        {
            $this->setHTML("<fieldset align='center' class='ft_10_001' style='width: " . $this->getLargura() . $this->getLarguraTipo() . "; text-align: " . $obsTopoPosicao . ";'>" . $obsTopo . "</fieldset><br>");
        }

    }

    ##
    # HTML Observacao abaixo titulo do Sistema

    #
    function setHTMLObservacaoBase()
    {
        $obsBase        = $this->getObservacaoBase();
        $obsBasePosicao = $this->getObservacaoBasePosicao();
        if ($obsBase != "")
        {
            $this->setHTML("<fieldset><div align='left' class='ft_10_001' style='width: " . ($this->getLargura() - 100) . $this->getLarguraTipo() . "; text-align: " . $obsBasePosicao . ";'>" . $obsBase . "</div></fieldset>");
        }

    }

    ##
    # HTML Separador

    #
    function setHTMLSeparador($separador = 0)
    {
        $this->setHTML("<table align='center' border='0' width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr><td><img src='" . _DIR_IMAGEM_ . "transp1x1.gif' height='" . $separador . "' border='0' style='width: " . ($this->getLargura() - 100) . $this->getLarguraTipo() . ";'></td></tr></table>");

    }

    ##
    # HTML Separador abaixo titulo do Sistema

    #
    function setHTMLSeparadorTopo()
    {
        $separador = $this->getSeparadorTopo();
        if ($separador != 0)
        {
            $this->setHTMLSeparador($separador);
        }

    }

    ##
    # HTML Separador abaixo titulo do Sistema

    #
    function setHTMLSeparadorBase()
    {
        $separador = $this->getSeparadorBase();
        if ($separador != 0)
        {
            $this->setHTMLSeparador($separador);
        }

    }

    ## @metodo
    #+----------------------------+
    #| Inicio do HTML             |
    #+----------------------------+

    #
    function montaTopoHTML()
    {
        global $modulo_ativado;

        $this->exibeTopoHTML();

        return $this->getHTML();

    }

    ## @metodo
    #+----------------------------+
    #| Exibe inicio do HTML       |
    #+----------------------------+

    #
    function exibeTopoHTML($sem_header=false,$height='10px')
    {
        global $css, $javascript, $title;

        if (!is_array($css))
        {
            $css = $this->getCSS( true );
        }

        if (!is_array($javascript))
        {
            $javascript = $this->getJS( true );
        }

        $setHTMLCaminho = $this->setHTMLCaminho('return');

        $this->initJS();
        $this->initCSS();

        include("html/html-base.php");
        if ($sem_header === false)
        {
            include("html/header.php");
        }
        else
        {
            //print '<div id="tempo_decorrido" class="text-left" style="color:green;margin-top:50px;"></div>';
            print '<div id="mensagem_do_sistema" style="width:950px;padding:40px 0px 0px 0px;margin:0 auto;display:none;"></div>';
        }

        if ( !empty($height) )
        {
          print "<div style='height:0px;'>&nbsp;</div>";
        }

        $this->printTituloTopoJanela( $title );
    }

    ## @metodo
    #+----------------------------+
    #| Início Corpo do HTML       |
    #+----------------------------+

    #
    function montaCorpoTopoHTML($width=null)
    {
        global $mensagemUsuario; // $mensagemUsuario atribuida no config.php

        $this->fechaContainer = true;

        $this->initHTML();

        if ( is_null($width) )
        {
            $this->setHTML("
            <div class=\"container\">

            " . exibeMensagemUsuario($mensagemUsuario) . "
            ");
        }
        else
        {
            $this->setHTML("
            <div class=\"container\" style='width:".$width.";'>

            " . exibeMensagemUsuario($mensagemUsuario) . "
            ");
        }

        // Botão de refresh
        if ($_SESSION['sSenhaI'] == 'xS')
        {
            $refresh = "
            <div class=\"col-md-1 lettering-tittle\" style=\"position:relative;top:24px;margin:0px;padding:0px;\">
                <button type=\"button\" class=\"btn btn-default btn-xs noborder\" onclick=\"myFunction();\">
                    <span class=\"glyphicon glyphicon-refresh\" aria-hidden=\"true\"></span>
                </button>
            </div>
            <script>
                function myFunction() {
                    parent.location.reload();
                }
            </script>
            ";
        }

        $exibir_icone_impressora = (!is_null($this->getIconeParaImpressao()) && !empty($this->getIconeParaImpressao()));

        if ($exibir_icone_impressora === true && empty($this->getSubTitulo(0)))
        {
            if ($exibir_icone_impressora === true)
            {
                $this->setHTML("
                <div id='icone_prepara_impresao' class='row col-md-2 col-md-offset-9'>"
                    . $this->getIconeParaImpressao() .
                "</div>"
                );
            }
        }
        else if ( $exibir_icone_impressora === false && !empty($this->getSubTitulo(0)) )
        {
            $this->setHTML("
            <div class=\"row margin-10\">
                <div class=\"col-md-11 subtitle\">
                    <h4 class=\"lettering-tittle uppercase\"><strong>" . $this->getSubTitulo(0) . "</strong></h4>
                </div>
                " . $refresh . "
            </div>
            ");
        }
        else if ( $exibir_icone_impressora === true && !empty($this->getSubTitulo(0)) )
        {
            $this->setHTML("
            <div class=\"row margin-10\">
                <div class=\"col-md-10 subtitle\">
                    <h4 class=\"lettering-tittle uppercase\"><strong>" . $this->getSubTitulo(0) . "</strong></h4>
                </div>
                <div id='icone_prepara_impresao' class='col-md-1 subtitle'>"
                    . $this->getIconeParaImpressao() .
                "</div>
            </div>
            ");
        }

        return $this->getHTML();

    }

    ## @metodo
    #+----------------------------+
    #| Exibe início Corpo do HTML |
    #+----------------------------+

    #
    function exibeCorpoTopoHTML($width=null)
    {
        print $this->montaCorpoTopoHTML($width);

    }

    ## @metodo
    #+----------------------------+
    #| Fim Corpo do HTML          |
    #+----------------------------+

    #
    function montaCorpoBaseHTML()
    {
        $this->initHTML();
        $this->setHTMLSeparadorBase();
        $this->setHTML("</div>");
        return $this->getHTML();

    }

    ## @metodo
    #+----------------------------+
    #| Exibe fim Corpo do HTML    |
    #+----------------------------+

    #
    function exibeCorpoBaseHTML()
    {
        print $this->montaCorpoBaseHTML();
        //include("html/footer.php");

        //if ($this->fechaContainer == true)
        //{
        //    $this->setHTML("</div>");
        //}
        //$this->setHTML("</td></tr></table>" . $this->getFooter() . "</body></html>");

    }

    ## @metodo
    #+----------------------------+
    #| Fim do HTML                |
    #+----------------------------+

    #
    function montaBaseHTML()
    {
        $modulo     = $_SERVER['SCRIPT_NAME'];
        $path_parts = pathinfo($modulo);
        $modulo     = $path_parts['filename'];

        if (_SISTEMA_INDISPONIVEL_ == false)
        {
            $usuarios_ativos = usuarios_ativos($_SESSION['sMatricula'], $_SESSION['sLotacao'], $modulo);
        }

        $this->initHTML();
        $this->setHTMLObservacaoBase();
        //$this->setHTML("</td></tr><tr><td>" . exibe_tempo_execucao() . "<div class='ft_10_001' style='float:left;text-align:center;vertical-align:bottom;font-size:7px;'>&nbsp;/&nbsp;" . number_format($usuarios_ativos, 0, ',', '.') . " usuários.</div><div class='ft_10_001' style='text-align: right; vertical-align: bottom;'>" . getIpReal() . "&nbsp;<img src='" . _DIR_IMAGEM_ . "ip.gif' border='0' width='12px' height='12px' alt='IP - Computador'>&nbsp;" . $_SERVER['SERVER_ADDR'] . "</div></td></tr></table>" . ($this->getFramesHeader() == false ? "</td></tr></table>" : "</td></tr></table>")."</div>");

        // 2018.12.29 :> $this->setHTML("</td></tr><tr><td>&nbsp;</td></tr></table></td></tr></table></div>");

        if ($this->fechaContainer == true)
        {
            $this->setHTML("</div>");
        }
        // 2018.12.29 :> $this->setHTML("</td></tr></table>" . $this->getFooter());
        //$this->setHTML( $this->getFooter() );
        include("html/footer.php");

        // libera memoria
        unset($modulo);
        unset($path_parts);
        unset($usuarios_ativos);

        return $this->getHTML();

    }

    ## @metodo
    #+----------------------------+
    #| Exibe fim do HTML          |
    #+----------------------------+

    #
    function exibeBaseHTML()
    {
        print $this->montaBaseHTML();

    }

    ## @metodo
    #+----------------------------+
    #| Destrói a sessão ativa     |
    #+----------------------------+

    #
    function destroi_sessao()
    {
        // elimina resquicios da sessao anterior
        @session_unset();
        // Destroi a sessao
        unset($_SESSION);
        $_SESSION["logado"] = '';
        @session_destroy();
        @session_start();

    }

    ## @metodo
    #+----------------------------+
    #| Siape                      |
    #+----------------------------+

    #
    var $siapeID             = array('pSiape'); // array (string)
    var $siapeTitulo         = array('Matr&iacute;cula:');  // array (string)
    var $siapeTituloClass    = array('ft_13_003'); // array (string)
    var $siapeOnkeyup        = array('javascript:ve(this.value);'); // array (string)
    var $siapeClass          = array('alinhadoAEsquerda caixa');   // array (string)
    var $siapePosicao_titulo = array('topo'); // array (string)
    var $siapeSize           = array('7'); // array(string)
    var $siapeMaxLength      = array('7'); // array(string)
    var $siapeReadOnly       = array(false); // array(boolean)
    var $siapeValue          = array(''); // array(string)

    function initSiapeID()
    {
        $this->siapeID = array();

    }

    function setSiapeID($valor = 'pSiape')
    {
        $this->siapeID[] = $valor;

    }

    function getSiapeID($nInd = 0)
    {
        return $this->siapeID[$nInd];

    }

    function initSiapeNome()
    {
        $this->siapeID = array();

    }

    function setSiapeNome($valor = 'pSiape')
    {
        $this->siapeID[] = $valor;

    }

    function getSiapeNome($nInd = 0)
    {
        return $this->siapeID[$nInd];

    }

    function initSiapeTitulo()
    {
        $this->siapeTitulo = array();

    }

    function setSiapeTitulo($valor = 'Matr&iacute;cula:')
    {
        $this->siapeTitulo[] = $valor;

    }

    function getSiapeTitulo($nInd = 0)
    {
        return $this->siapeTitulo[$nInd];

    }

    function initSiapeTituloClass()
    {
        $this->siapeTituloClass = array();

    }

    function setSiapeTituloClass($valor = 'ft_13_003')
    {
        $this->siapeTituloClass[] = $valor;

    }

    function getSiapeTituloClass($nInd = 0)
    {
        return $this->siapeTituloClass[$nInd];

    }

    function initSiapeOnkeyup()
    {
        $this->siapeOnkeyup = array();

    }

    function setSiapeOnkeyup($valor = 'javascript:ve(this.value);')
    {
        $this->siapeOnkeyup[] = $valor;

    }

    function getSiapeOnkeyup($nInd = 0)
    {
        return $this->siapeOnkeyup[$nInd];

    }

    function initSiapeClass()
    {
        $this->siapeClass = array();

    }

    function setSiapeClass($valor = 'alinhadoAEsquerda')
    {
        $this->siapeClass[] = $valor;

    }

    function getSiapeClass($nInd = 0)
    {
        return $this->siapeClass[$nInd];

    }

    function initSiapePosicao_titulo()
    {
        $this->siapePosicao_titulo = array();

    }

    function setSiapePosicao_titulo($valor = 'topo')
    {
        $this->siapePosicao_titulo[] = $valor;

    }

    function getSiapePosicao_titulo($nInd = 0)
    {
        return $this->siapePosicao_titulo[$nInd];

    }

    function initSiapeSize()
    {
        $this->siapeSize = array();

    }

    function setSiapeSize($valor = '7')
    {
        $this->siapeSize[] = $valor;

    }

    function getSiapeSize($nInd = 0)
    {
        return $this->siapeSize[$nInd];

    }

    function initSiapeMaxLength()
    {
        $this->siapeMaxLength[] = array();

    }

    function setSiapeMaxLength($valor = '60')
    {
        $this->siapeMaxLength[] = $valor;

    }

    function getSiapeMaxLength($nInd = 0)
    {
        return $this->siapeMaxLength[$nInd];

    }

    function initSiapeReadOnly()
    {
        $this->siapeReadOnly[] = array();

    }

    function setSiapeReadOnly($valor = false)
    {
        $this->siapeReadOnly[] = $valor;

    }

    function getSiapeReadOnly($nInd = 0)
    {
        return $this->siapeReadOnly[$nInd];

    }

    function initSiapeValue()
    {
        $this->siapeValue[] = array();

    }

    function setSiapeValue($valor = '')
    {
        $this->siapeValue[] = $valor;

    }

    function getSiapeValue($nInd = 0)
    {
        return $this->siapeValue[$nInd];

    }

    function initInputSiape()
    {
        $this->initSiapeNome();
        $this->setSiapeNome();
        $this->initSiapeTitulo();
        $this->setSiapeTitulo();
        $this->initSiapeTituloClass();
        $this->setSiapeTituloClass();
        $this->initSiapeOnkeyup();
        $this->setSiapeOnkeyup();
        $this->initSiapeClass();
        $this->setSiapeClass();
        $this->initSiapePosicao_titulo();
        $this->setSiapePosicao_titulo();
        $this->initSiapeSize();
        $this->setSiapeSize();
        $this->initSiapeMaxLength();
        $this->setSiapeMaxLength();
        $this->initSiapeReadOnly();
        $this->setSiapeReadOnly();
        $this->initSiapeValue();
        $this->setSiapeValue();

    }

    function inputSiape($ind = 0, $form = false)
    {
        if ($form)
        {
            ?>
            <input  type = "text"
                    id        = "<?= $this->getSiapeID(); ?>"
                    name      = "<?= $this->getSiapeID(); ?>"
                    class     = "form-control"
                    size      = "<?= $this->getSiapeSize(); ?>"
                    maxlength = "<?= $this->getSiapeMaxLength(); ?>"
                    value     = "<?= $this->getSiapeValue(); ?>"
                    onkeyup   = "<?= $this->getSiapeOnkeyup(); ?>"
                    <?= ($this->getSiapeReadOnly() ? ' readonly' : ''); ?> />
            <?php
        }
        else
        {
            $html = "";
            if ($this->getSiapeTitulo() != "")
            {
                $html .= '<p class="' . $this->getSiapeTituloClass() . '">&nbsp;' . $this->getSiapeTitulo();
                $html .= ($this->getSiapePosicao_titulo() == 'topo' ? "<br>" : "&nbsp;");
            }
            $html .= '&nbsp;<input
                                type="text"
                                id        = "' . $this->getSiapeID() . '"
                                name      = "' . $this->getSiapeID() . '"
                                class     = "form-control"
                                size      = "' . $this->getSiapeSize() . '"
                                maxlength = "' . $this->getSiapeMaxLength() . '"
                                value     = "' . $this->getSiapeValue() . '"
                                onkeyup   = "' . $this->getSiapeOnkeyup() . '"' .
                                ($this->getSiapeReadOnly() ? ' readonly' : '') . '>';
            if ($this->getSiapeTitulo() != "")
            {
                $html .= '</p>';
            }
            print $html;
        }

    }

    ## @metodo
    #+----------------------------+
    #| Mes                        |
    #+----------------------------+

    #
    var $mesNome;    // array (string)
    var $mesTitulo;  // array (string)
    var $mesOnkeyup; // array (string)
    var $mesClass;   // array (string
    var $mesPosicao_titulo; // array (string)

    function initMesNome()
    {
        $this->mesNome = array();

    }

    function setMesNome($valor = 'mes')
    {
        $this->mesNome[] = $valor;

    }

    function initMesTitulo()
    {
        $this->mesTitulo = array();

    }

    function setMesTitulo($valor = 'M&ecirc;s:')
    {
        $this->mesTitulo[] = $valor;

    }

    function initMesOnkeyup()
    {
        $this->mesOnkeyup = array();

    }

    function setMesOnkeyup($valor = 'javascript:ve(this.value);')
    {
        $this->mesOnkeyup[] = $valor;

    }

    function initMesClass()
    {
        $this->mesClass = array();

    }

    function setMesClass($valor = 'alinhadoAEsquerda')
    {
        $this->mesClass[] = $valor;

    }

    function initMesPosicao_titulo()
    {
        $this->mesPosicao_titulo = array();

    }

    function setMesPosicao_titulo($valor = 'topo')
    {
        $this->mesPosicao_titulo[] = $valor;

    }

    function inputMes($ind = 0)
    {
        $this->initInputHTML();
        $this->setInputID($this->mesNome[$ind]);
        $this->setInputTitulo($this->mesTitulo[$ind]);
        $this->setInputTitle('');
        $this->setInputTituloClass('ft_13_003');
        $this->setInputOnkeyup($this->mesOnkeyup[$ind]);
        $this->setInputOnkeypress('');
        $this->setInputClass($this->mesClass[$ind]);
        $this->setInputPosicao_titulo($this->mesPosicao_titulo[$ind]);
        $this->setInputSize('2');
        $this->setInputMaxLength('2');
        $this->setInputReadOnly(false);
        $this->setInputValue('');
        print $this->campoMes();

    }

    ## @metodo
    #+----------------------------+
    #| Ano                        |
    #+----------------------------+

    #
    var $anoNome;    // array (string)
    var $anoTitulo;  // array (string)
    var $anoOnkeyup; // array (string)
    var $anoClass;   // array (string)
    var $anoPosicao_titulo; // array (string)

    function initAnoNome()
    {
        $this->anoNome = array();

    }

    function setAnoNome($valor = 'ano')
    {
        $this->anoNome[] = $valor;

    }

    function initAnoTitulo()
    {
        $this->anoTitulo = array();

    }

    function setAnoTitulo($valor = 'M&ecirc;s:')
    {
        $this->anoTitulo[] = $valor;

    }

    function initAnoOnkeyup()
    {
        $this->anoOnkeyup = array();

    }

    function setAnoOnkeyup($valor = 'javascript:ve(this.value);')
    {
        $this->anoOnkeyup[] = $valor;

    }

    function initAnoClass()
    {
        $this->anoClass = array();

    }

    function setAnoClass($valor = 'alinhadoAEsquerda')
    {
        $this->anoClass[] = $valor;

    }

    function initAnoPosicao_titulo()
    {
        $this->anoPosicao_titulo = array();

    }

    function setAnoPosicao_titulo($valor = 'topo')
    {
        $this->anoPosicao_titulo[] = $valor;

    }

    function inputAno($ind = 0)
    {
        $this->initInputHTML();
        $this->setInputID($this->anoNome[$ind]);
        $this->setInputTitulo($this->anoTitulo[$ind]);
        $this->setInputTitle('');
        $this->setInputTituloClass('ft_13_003');
        $this->setInputOnkeyup($this->anoOnkeyup[$ind]);
        $this->setInputOnkeypress('');
        $this->setInputClass($this->anoClass[$ind]);
        $this->setInputPosicao_titulo($this->anoPosicao_titulo[$ind]);
        $this->setInputSize('4');
        $this->setInputMaxLength('4');
        $this->setInputReadOnly(false);
        $this->setInputValue('');
        print $this->campoAno();

    }

    ## @metodo
    #+----------------------------+
    #| ANO                        |
    #+----------------------------+

    #
    function setCampoAno()
    {
        $this->initInputHTML();
        $this->setInputID('ano');
        $this->setInputTipoCampo('ano');
        $this->setInputTitulo('Ano:');
        $this->setInputTitle('');
        $this->setInputTituloClass('ft_13_003');
        $this->setInputOnkeyup('javascript:ve(this.value);');
        $this->setInputOnkeypress('');
        $this->setInputClass('alinhadoAEsquerda');
        $this->setInputPosicao_titulo('topo');
        $this->setInputSize('4');
        $this->setInputMaxLength('4');
        $this->setInputReadOnly(false);
        $this->setInputValue('');

    }

    function campoAno()
    {
        $this->setInput();
        return $this->getInput();

    }

    ## @metodo
    #+----------------------------+
    #| MES                        |
    #+----------------------------+

    #
    function setCampoMes()
    {
        $this->initInputHTML();
        $this->setInputID('mes');
        $this->setInputTipoCampo('mes');
        $this->setInputTitulo('M&ecirc;s:');
        $this->setInputTitle('');
        $this->setInputTituloClass('ft_13_003');
        $this->setInputOnkeyup('javascript:ve(this.value);');
        $this->setInputOnkeypress('');
        $this->setInputClass('alinhadoAEsquerda');
        $this->setInputPosicao_titulo('topo');
        $this->setInputSize('2');
        $this->setInputMaxLength('2');
        $this->setInputReadOnly(false);
        $this->setInputValue('');

    }

    function campoMes()
    {
        $this->setInput();
        return $this->getInput();

    }

    ## @metodo
    #+----------------------------+
    #| SIAPE                      |
    #+----------------------------+

    #
    function setCampoSiape()
    {
        $this->initInputHTML();
        $this->setInputID('pSiape');
        $this->setInputTipoCampo('siape');
        $this->setInputTitulo('Matr&iacute;cula:');
        $this->setInputTitle('');
        $this->setInputTituloClass('ft_13_003');
        $this->setInputOnkeyup('javascript:ve(this.value);');
        $this->setInputOnkeypress('');
        $this->setInputClass('alinhadoAEsquerda');
        $this->setInputPosicao_titulo('topo');
        $this->setInputSize('7');
        $this->setInputMaxLength('7');
        $this->setInputReadOnly(true);
        $this->setInputValue('');

    }

    function campoSiape()
    {
        $this->setInput();
        return $this->getInput();

    }

    ## @metodo
    #+----------------------------+
    #| Nome                       |
    #+----------------------------+

    #
    function setCampoNome()
    {
        $this->initInputHTML();
        $this->setInputID('nome');
        $this->setInputTitulo('Nome:');
        $this->setInputTitle('');
        $this->setInputTituloClass('ft_13_003');
        $this->setInputOnkeyup('');
        $this->setInputOnkeypress('');
        $this->setInputClass('caixa');
        $this->setInputPosicao_titulo('topo');
        $this->setInputSize('60');
        $this->setInputMaxLength('60');
        $this->setInputReadOnly(true);
        $this->setInputValue('');

    }

    function campoNome()
    {
        $this->setInput();
        return $this->getInput();

    }

    ## @metodo
    #+----------------------------+
    #| CPF                        |
    #+----------------------------+

    #
    function setCampoCPF()
    {
        $this->initInputHTML();
        $this->setInputID('cpf');
        $this->setInputTitulo('CPF:');
        $this->setInputTitle('');
        $this->setInputTituloClass('ft_13_003');
        $this->setInputOnkeyup('');
        $this->setInputOnkeypress('');
        $this->setInputClass('caixa');
        $this->setInputPosicao_titulo('topo');
        $this->setInputSize('11');
        $this->setInputMaxLength('11');
        $this->setInputReadOnly(true);
        $this->setInputValue('');

    }

    function campoCPF()
    {
        $this->setInput();
        return $this->getInput();

    }

    ## @metodo
    #+----------------------------+
    #| Lotação código             |
    #+----------------------------+

    #
    function setCampoLotacaoCodigo()
    {
        $this->initInputHTML();
        $this->setInputID('lotacao');
        $this->setInputTitulo('Lotação:');
        $this->setInputTitle('');
        $this->setInputTituloClass('ft_13_003');
        $this->setInputOnkeyup('');
        $this->setInputOnkeypress('');
        $this->setInputClass('caixa');
        $this->setInputPosicao_titulo('topo');
        $this->setInputSize('11');
        $this->setInputMaxLength('11');
        $this->setInputReadOnly(true);
        $this->setInputValue('');

    }

    function campoLotacaoCodigo()
    {
        $this->setInput();
        return $this->getInput();

    }

    ## @metodo
    #+----------------------------+
    #| Lotação Descrição          |
    #+----------------------------+

    #
    function setCampoLotacaoDescricao()
    {
        $this->initInputHTML();
        $this->setInputID('wnomelota');
        $this->setInputTitulo('');
        $this->setInputTitle('');
        $this->setInputTituloClass('ft_13_003');
        $this->setInputOnkeyup('');
        $this->setInputOnkeypress('');
        $this->setInputClass('caixa');
        $this->setInputPosicao_titulo('topo');
        $this->setInputSize('70');
        $this->setInputMaxLength('70');
        $this->setInputReadOnly(true);
        $this->setInputValue('');

    }

    function campoLotacaoDescricao()
    {
        $this->setInput();
        return $this->getInput();

    }

    ## @metodo
    #+----------------------------+
    #| Campo UORG                 |
    #+----------------------------+

    #
    function setCampoUORG()
    {
        $this->initInputHTML();
        $this->setInputID('uorg');
        $this->setInputTitulo('UOrg:');
        $this->setInputTitle('');
        $this->setInputTituloClass('ft_13_003');
        $this->setInputOnkeyup('');
        $this->setInputOnkeypress("");
        $this->setInputClass('caixa');
        $this->setInputPosicao_titulo('topo');
        $this->setInputSize('9');
        $this->setInputMaxLength('9');
        $this->setInputReadOnly(true);
        $this->setInputValue('');

    }

    function campoUORG()
    {
        $this->setInput();
        return $this->getInput();

    }

    ## @metodo
    #+----------------------------+
    #| Campo UORGPai              |
    #+----------------------------+

    #
    function setCampoUORGPai()
    {
        $this->initInputHTML();
        $this->setInputID('uorg_pai');
        $this->setInputTitulo('UOrg Pai:');
        $this->setInputTitle('');
        $this->setInputTituloClass('ft_13_003');
        $this->setInputOnkeyup('');
        $this->setInputOnkeypress("");
        $this->setInputClass('caixa');
        $this->setInputPosicao_titulo('topo');
        $this->setInputSize('9');
        $this->setInputMaxLength('9');
        $this->setInputReadOnly(true);
        $this->setInputValue('');

    }

    function campoUORGPai()
    {
        $this->setInput();
        return $this->getInput();

    }

    ## @metodo
    #+----------------------------+
    #| Campo UPAG                 |
    #+----------------------------+

    #
    function setCampoUPAG()
    {
        $this->initInputHTML();
        $this->setInputID('upag');
        $this->setInputTitulo('UPag:');
        $this->setInputTitle('');
        $this->setInputTituloClass('ft_13_003');
        $this->setInputOnkeyup('');
        $this->setInputOnkeypress("");
        $this->setInputClass('caixa');
        $this->setInputPosicao_titulo('topo');
        $this->setInputSize('9');
        $this->setInputMaxLength('9');
        $this->setInputReadOnly(true);
        $this->setInputValue('');

    }

    function campoUPAG()
    {
        $this->setInput();
        return $this->getInput();

    }

    ## @metodo
    #+----------------------------+
    #| Campo UG                   |
    #+----------------------------+

    #
    function setCampoUG()
    {
        $this->initInputHTML();
        $this->setInputID('ug');
        $this->setInputTitulo('UG:');
        $this->setInputTitle('');
        $this->setInputTituloClass('ft_13_003');
        $this->setInputOnkeyup('');
        $this->setInputOnkeypress("");
        $this->setInputClass('caixa');
        $this->setInputPosicao_titulo('topo');
        $this->setInputSize('6');
        $this->setInputMaxLength('6');
        $this->setInputReadOnly(true);
        $this->setInputValue('');

    }

    function campoUG()
    {
        $this->setInput();
        return $this->getInput();

    }

    ## @metodo
    #+----------------------------+
    #| Campo Ativo                |
    #+----------------------------+

    #
    function setCampoAtivo()
    {
        $this->initInputHTML();
        $this->setInputID('ativo');
        $this->setInputTitulo('Ativo:');
        $this->setInputTitle('');
        $this->setInputTituloClass('ft_13_003');
        $this->setInputOnkeyup('');
        $this->setInputOnkeypress("");
        $this->setInputClass('caixa');
        $this->setInputPosicao_titulo('topo');
        $this->setInputSize('1');
        $this->setInputMaxLength('1');
        $this->setInputReadOnly(true);
        $this->setInputValue('');

    }

    function campoAtivo()
    {
        $this->setInput();
        return $this->getInput();

    }

    ## @metodo
    #+----------------------------+
    #| Área                       |
    #+----------------------------+

    #
    function setCampoArea()
    {
        $this->initInputHTML();
        $this->setInputID('areav');
        $this->setInputTitulo('Área:');
        $this->setInputTitle('');
        $this->setInputTituloClass('ft_13_003');
        $this->setInputOnkeyup('');
        $this->setInputOnkeypress('');
        $this->setInputClass('caixa');
        $this->setInputPosicao_titulo('topo');
        $this->setInputSize('11');
        $this->setInputMaxLength('11');
        $this->setInputReadOnly(true);
        $this->setInputValue('');

    }

    function campoArea()
    {
        $this->setInput();
        return $this->getInput();

    }

    ## @metodo
    #+----------------------------+
    #| Campo Data (dia)           |
    #+----------------------------+

    #
    function setCampoData()
    {
        $this->initInputHTML();
        $this->setInputID('dia');
        $this->setInputTitulo('Data:');
        $this->setInputTitle('Digite o dia sem pontos e barras!');
        $this->setInputTituloClass('ft_13_003');
        $this->setInputOnkeyup('');
        $this->setInputOnkeypress("formatar(this, '##/##/####')");
        $this->setInputClass('caixa');
        $this->setInputPosicao_titulo('topo');
        $this->setInputSize('11');
        $this->setInputMaxLength('11');
        $this->setInputReadOnly(true);
        $this->setInputValue('');

    }

    function campoData()
    {
        $this->setInput();
        return $this->getInput();

    }

    ## @metodo
    #+----------------------------+
    #| Campo Hora                 |
    #+----------------------------+

    #
    function setCampoHora()
    {
        $this->initInputHTML();
        $this->setInputID('hora');
        $this->setInputTitulo('Hora:');
        $this->setInputTitle('Digite o horário sem pontos no formato 000000!');
        $this->setInputTituloClass('ft_13_003');
        $this->setInputOnkeyup('');
        $this->setInputOnkeypress("formatar(this, '##:##:##')");
        $this->setInputClass('alinhadoAoCentro');
        $this->setInputPosicao_titulo('topo');
        $this->setInputSize('8');
        $this->setInputMaxLength('8');
        $this->setInputReadOnly(true);
        $this->setInputValue('');

    }

    function campoHora()
    {
        $this->setInput();
        return $this->getInput();

    }

    ## @metodo
    #+----------------------------+
    #| INPUT (set/get/print)      |
    #+----------------------------+

    #
    var $sInputID;        // string
    var $sInputTipoCampo; // string
    var $sInputTitulo;    // string
    var $sInputTituloClass; // string
    var $sInputTitle;       // string
    var $sInputOnkeyup;     // string
    var $sInputOnkeypress;  // string
    var $sInputClass;       // string
    var $sInputPosicao_titulo; // string
    var $sInputSize;      // string
    var $sInputMaxLength; // string
    var $bInputReadOnly;  // boolean
    var $sInputValue;     // string
    var $sInputHTML; //string

    function setInputID($valor = '')
    {
        $this->sInputID = $valor;

    }

    function getInputID()
    {
        return $this->sInputID;

    }

    function setInputTipoCampo($valor = '')
    {
        $this->sInputTipoCampo = $valor;

    }

    function getInputTipoCampo()
    {
        return $this->sInputTipoCampo;

    }

    function setInputTitulo($valor = '')
    {
        $this->sInputTitulo = $valor;

    }

    function getInputTitulo()
    {
        return $this->sInputTitulo;

    }

    function setInputTituloClass($valor = '')
    {
        $this->sInputTituloClass = $valor;

    }

    function getInputTituloClass()
    {
        return $this->sInputTituloClass;

    }

    function setInputTitle($valor = '')
    {
        $this->sInputTitle = $valor;

    }

    function getInputTitle()
    {
        return $this->sInputTitle;

    }

    function setInputOnkeyup($valor = '')
    {
        $this->sInputOnkeyup = $valor;

    }

    function getInputOnkeyup()
    {
        return $this->sInputOnkeyup;

    }

    function setInputOnkeypress($valor = '')
    {
        $this->sInputOnkeypress = $valor;

    }

    function getInputOnkeypress()
    {
        return $this->sInputOnkeypress;

    }

    function setInputClass($valor = '')
    {
        $this->sInputClass = $valor;

    }

    function getInputClass()
    {
        return $this->sInputClass;

    }

    function setInputPosicao_titulo($valor = 'topo')
    {
        $this->sInputPosicao_titulo = $valor;

    }

    function getInputPosicao_titulo()
    {
        return $this->sInputPosicao_titulo;

    }

    function setInputSize($valor = '')
    {
        $this->sInputSize = $valor;

    }

    function getInputSize()
    {
        return $this->sInputSize;

    }

    function setInputMaxLength($valor = '')
    {
        $this->sInputMaxLength = $valor;

    }

    function getInputMaxLength()
    {
        return $this->sInputMaxLength;

    }

    function setInputReadOnly($valor = true)
    {
        $this->sInputReadOnly = $valor;

    }

    function getInputReadOnly()
    {
        return $this->sInputReadOnly;

    }

    function setInputValue($valor = '')
    {
        $this->sInputValue = $valor;

    }

    function getInputValue()
    {
        return $this->sInputValue;

    }

    function initInputHTML()
    {
        $this->sInputHTML = '';

    }

    function setInputHTML($valor = '')
    {
        $this->sInputHTML .= $valor;

    }

    function getInputHTML()
    {
        return $this->sInputHTML;

    }

    function setInput()
    {
        $html = "";
        if ($this->getInputTitulo() != "")
        {
            $html .= '<font class="' . $this->getInputTituloClass() . '">&nbsp;' . $this->getInputTitulo();
            $html .= ($this->getInputPosicao_titulo() == 'topo' ? "<br>" : "&nbsp;");
        }
        $html .= '&nbsp;<input type="text"';
        $html .= ($this->getInputTipoCampo() == '' ? '' : ' tipo="' . $this->getInputTipoCampo() . '"');
        $html .= ($this->getInputID() == '' ? '' : ' id="' . $this->getInputID() . '" name="' . $this->getInputID() . '"');
        $html .= ($this->getInputClass() == '' ? '' : ' class="form-control ' . $this->getInputClass() . '"');
        $html .= ($this->getInputTitle() == '' ? '' : ' title="' . $this->getInputTitle() . '"');
        $html .= ($this->getInputSize() == '' ? '' : ' size="' . $this->getInputSize() . '"');
        $html .= ($this->getInputMaxLength() == '' ? '' : ' maxlength="' . $this->getInputMaxLength() . '"');
        $html .= ' value="' . $this->getInputValue() . '"';
        $html .= ($this->getInputOnkeyup() == '' ? '' : ' onkeyup="' . $this->getInputOnkeyup() . '"');
        $html .= ($this->getInputOnkeypress() == '' ? '' : ' onkeypress="' . $this->getInputOnkeypress() . '"');
        $html .= ($this->getInputReadOnly() == true ? ' readonly' : '') . '>';
        if ($this->getInputTitulo() != "")
        {
            $html .= '</font>';
        }
        $this->setInputHTML($html);

    }

    function getInput()
    {
        return $this->getInputHTML();

    }

    function printInput()
    {
        print $this->getInputHTML();

    }

    ## @metodo
    #+----------------------------+
    #| Atribui tempo de duração da|
    #| sessão atual. Mais tempo.  |
    #+----------------------------+

    #
    public function setDuracaoDaSessao()
    {
        //Seta mais minutos
        $_SESSION["sessiontime"] = time() + 60 * getDuracaoDaSessaoEmMinutos();

    }

    ## @metodo
    #+----------------------------+
    #| Verifica se duração da     |
    #| sessão expirou.            |
    #+----------------------------+

    #
    public function verificaDuracaoDaSessao()
    {
        $pathinfo = pathinfo($_SERVER['REQUEST_URI']);
        $basename = explode('.',(empty($pathinfo['basename']) ? 'x' : $pathinfo['basename']));
        $origem   = $basename[0];

        if (isset($_SESSION["sessiontime"]))
        {
            if ($this->verificaDuracaoDaSessaoExpirou($origem) === false)
            {
                $this->verificaDuracaoDaSessaoJavascript();
            }
        }

    }

    public function verificaDuracaoDaSessaoExpirou($origem)
    {
        $paginas = array( 'entrada', 'principal' );
        $retorno = false;

        if ($_SESSION["sessiontime"] < time() && in_array($origem,$paginas) > 0)
        {
            mensagem(utf8_decode("Sua sessão Expirou!"), "entrada.php");
            $retorno = true;
        }

        return $retorno;
    }

    public function verificaDuracaoDaSessaoJavascript()
    {
        $paginas = array( 'entrada', 'principal' );

        $pathinfo = pathinfo($_SERVER['REQUEST_URI']);
        $basename = explode('.',(empty($pathinfo['basename']) ? 'x' : $pathinfo['basename']));
        $origem   = $basename[0];

        //Seta mais minutos
        $this->setDuracaoDaSessao();

        if ( !in_array($origem,$paginas) )
        {
            /*
            $this->setHTML("
            <script>
                var limite = " . getDuracaoDaSessaoEmMinutos() . ";
                var startTime = 0;
                var start = 0;
                var end = 0;
                var diff = 0;
                var timerID = 0;

                function chrono()
                {
                    start = new Date()
                    diff = end - start
                    diff = new Date(diff)

                    var msec = diff.getMilliseconds();
                    var sec = diff.getSeconds();
                    var min = diff.getMinutes();
                    var hr = diff.getHours();

                    // tempo limite
                    min -= (60 - limite);

                    if (min < 10) { min = '0' + min; }
                    if (sec < 10) { sec = '0' + sec; }
                    if (msec < 10) { msec = '00' +msec; }
                    else if (msec < 100) { msec = '0' +msec; }

                    $('#tempo_decorrido').html( '<b>Sessão expira em:</b> ' + min + ':' + sec + ' (mm:ss)' );

                    if (min === '00' && sec === '00')
                    {
                        alert( 'Sua sessão Expirou!' );
                        window.location.href = '?controle=Login&acao=telaLogin';
                    }
                    else
                    {
                        timerID = setTimeout('chrono()', 10);
                    }
                }

                end = new Date();
                chrono();

            </script>
            ");
             */
        }
    }

   /* function getTrocaContexto(){
        $html = "";

        if((isset($_SESSION['sGestaoUPAG']) && $_SESSION['sGestaoUPAG'] == 'S') || (isset($_SESSION['sAdmCentral']) && $_SESSION['sAdmCentral'] == 'S')) {
            $upagsContexto = array();
            $oDBaseContexto = new DataBase('PDO');

            if($_SESSION['sAdmCentral'] == 'S'){
                $oDBaseContexto->query("SELECT upag, descricao FROM tabsetor WHERE codigo = upag GROUP BY upag");
            }else{
                $oDBaseContexto->query("SELECT upag, descricao FROM tabsetor WHERE SUBSTRING(upag, 1, 5) = '".$_SESSION['orgao']."' AND codigo = upag GROUP BY upag");
            }
            
            while($linhaContexto = $oDBaseContexto->fetch_array()){
                $arr = array( 
                    'codigo' => $linhaContexto['upag'],
                    'descricao' => $linhaContexto['descricao']
                );
                $upagsContexto[] = $arr;
            }
            

            // Monta as upags para serem selecionadas
            $options = "<option>--Selecione a UPAG--</option>";
            foreach($upagsContexto as $upagContexto){
                $options .= "<option value='".$upagContexto['codigo']."'>".$upagContexto['codigo'] .' - ' .$upagContexto['descricao']."</option>";
            }

            $html = "<div class='modal fade' id='troca-contexto-upag' role='dialog'>
                        <div class='modal-dialog'>
                            <div class='modal-content'>
                                <form action='trocar_contexto_upag.php' method='post'>
                                    <div class='modal-header'>
                                        <button type='button' class='close' data-dismiss='modal'>&times;</button>
                                        <h4 class='modal-title'>Confirmação</h4>
                                    </div>
                                    <div class='modal-body'>
                                        <select class='form-control' name='upagContexto'>
                                            {$options}
                                        </select>
                                    </div>
                                    <div class='modal-footer'>
                                        <button type='submit' class='btn btn-default save' >Trocar Upag</button>
                                        <button type='button' class='btn btn-default cancel' data-dismiss='modal'>Cancelar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>";
        }
        return $html;
    }*/
}
