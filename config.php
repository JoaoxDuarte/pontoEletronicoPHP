<?php
/*
 * SISREF Vers�o: 2.0.0.46-beta (2019-02-04 17:00:00)
 *
 * Segundo o Semantic Versioning:
 *
 * O primeiro n�mero indica que o sistema tem mudan�as que o torna incompat�vel com vers�es anteriores;
 * O segundo n�mero indica que o sistema tem mudan�as compat�veis com vers�es anteriores, dentro do primeiro n�mero;
 * O terceiro n�mero indica que o sistema tem mudan�as menores, como corre��es de bugs e funcionalidades que n�o prejudicam a compatibilidade com vers�es
 * anteriores.
 * Opcionalmente, define-se um quarto n�mero, chamado de release. Indica o n�mero atual do build daquele c�digo, dentro de um escopo de modifica��es.
 *
 * Exemplos:
 *
 * Ano.Mes.Dia.Build;
 * Ano.Mes.Dia.Estado. Estado pode ser Alpha, Beta, dev, stable, etc.;
 *
 */


    // seguran�a
    $vetor1 = explode('<script>',strtr($_SERVER['PHP_SELF'], array('"'=>'',"'"=>"")));
    $vetor2 = explode('<style>',strtr($_SERVER['PHP_SELF'], array('"'=>'',"'"=>"")));
    $vetor3 = explode('&lt;script>',strtr($_SERVER['PHP_SELF'], array('"'=>'',"'"=>"")));
    $vetor4 = explode('&lt;style>',strtr($_SERVER['PHP_SELF'], array('"'=>'',"'"=>"")));
    $vetor5 = explode('<script&gt;',strtr($_SERVER['PHP_SELF'], array('"'=>'',"'"=>"")));
    $vetor6 = explode('<style&gt;',strtr($_SERVER['PHP_SELF'], array('"'=>'',"'"=>"")));
    $vetor7 = explode('&lt;script&gt;',strtr($_SERVER['PHP_SELF'], array('"'=>'',"'"=>"")));
    $vetor8 = explode('&lt;style&gt;',strtr($_SERVER['PHP_SELF'], array('"'=>'',"'"=>"")));
    if (count($vetor1) > 1 ||
        count($vetor2) > 1 ||
        count($vetor3) > 1 ||
        count($vetor4) > 1 ||
        count($vetor5) > 1 ||
        count($vetor6) > 1 ||
        count($vetor7) > 1 ||
        count($vetor8) > 1)
    {
        header('location:'.str_replace('.php/','',$vetor1[0]).'.php');
    }

    unset($vetor1);
    unset($vetor2);
    unset($vetor3);
    unset($vetor4);
    unset($vetor5);
    unset($vetor6);
    unset($vetor7);
    unset($vetor8);

    //error_reporting(E_ERROR | E_WARNING | E_PARSE);
    ini_set('display_errors','0');
    ini_set('display_startup_errors', 0);
    error_reporting(0);

    //ini_set('display_errors', 0);
    //ini_set('display_startup_errors', 1);
    //error_reporting(E_ALL);

    /** Coloque essas duas linhas no cabe�alho do site **/
    ini_set('zlib.output_compression','On');
    ini_set('zlib.output_compression_level','1');


    // Informa qual o conjunto de caracteres ser� usado
    header( 'Content-Type: text/html;  charset=ISO-8859-1',true);
    //header('Content-Type: text/html; charset=utf-8');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache'); // HTTP/1.0


    setlocale(LC_ALL, 'pt_BR');
    date_default_timezone_set("America/Sao_Paulo");

    // Define o tempo da sess�o, expira em 'x' minuto(s)
    $paginas_array = array( 'entrada', 'entrada1', 'entrada2', 'entrada3', 'entrada4' );

    $path_parts       = pathinfo($_SERVER['REQUEST_URI']);
    $pagina_de_origem = $path_parts['filename'];

    $basename = explode('.',(empty($path_parts['basename']) ? 'x' : $path_parts['basename']));
    $origem   = $basename[0];

    if (in_array($origem,$paginas_array))
    {
        define( '_DURACAO_DA_SESSAO_EM_MINUTOS_', 3 );
    }
    else
    {
        define( '_DURACAO_DA_SESSAO_EM_MINUTOS_', 10 );
    }

    // Para evitar que por falta de inatividade a sess�o nunca seja destru�da, limitamos o tempo
    $tempo1 = session_cache_expire( _DURACAO_DA_SESSAO_EM_MINUTOS_ );

    /* Inicia a sess�o */
    session_start();


    // $path_dots_slashes est� definido no in�cio de alguns arquivos PHP
    $path_dots_slashes = (isset($path_dots_slashes) ? $path_dots_slashes : './');

    $arrStrings = array('//' => '/', '///' => '/', '////' => '/');

    // HOM      Homologa��o     (SIAPE 7 posi��es)
    // DESENV   Desenvolvimento (SIAPE 11 posi��es)
    // PROD     Produ��o        (SIAPE 11 posi��es)
    // HOM11    Homologa��o     (SIAPE 11 posi��es)
    define( '_AMBIENTE_APLICACAO_',  'PROD' );

    define( '_DIR_ROOT_',     strtr($_SERVER['DOCUMENT_ROOT'] . '/',$arrStrings) );
    define( '_DIR_APP_',      '/' );
    define( '_DIR_APP_ROOT_', strtr(_DIR_ROOT_ . (substr_count(_DIR_ROOT_,'/') > 0 ? '' :  _DIR_APP_ . '/'),$arrStrings) );
    define( '_DIR_IMAGES_',   $path_dots_slashes . 'imagem/' );
    define( 'FPDF_FONTPATH',  $path_dots_slashes . 'app.lib/fpdf/font/' );

    $path_parts = pathinfo($_SERVER['REQUEST_URI']);
    $pagina_de_entrada = $path_parts['filename'];

    define( '_DIR_',        $path_dots_slashes );
    define( '_DIR_JS_',     $path_dots_slashes . 'js/' );
    define( '_DIR_CSS_',    $path_dots_slashes . 'css/' );
    define( '_DIR_INC3_',    '');
    define( '_DIR_INC_',    $path_dots_slashes . 'inc/' );
    define( '_DIR_IMAGEM_', $path_dots_slashes . 'imagem/' );
    define( '_DIR_FOTO_',   $path_dots_slashes . 'foto/' );

    define( '_DIR_COMPONENTES_', $path_dots_slashes . 'componentes/' );
    define( '_DIR_CONEXAO_',     $path_dots_slashes . 'conexao/' );
    define( '_DIR_CONTROLE_',    $path_dots_slashes . 'controle/' );
    define( '_DIR_MODELO_',      $path_dots_slashes . 'modelo/' );
    define( '_DIR_VISAO_',       $path_dots_slashes . 'visao/' );

    $path_app_includes  = get_include_path() . PATH_SEPARATOR . _DIR_ROOT_ . (substr_count(_DIR_ROOT_,'/') > 0 ? '' :  '/' . _DIR_APP_ . '/');
    $path_app_includes .= PATH_SEPARATOR . _DIR_APP_ROOT_ . 'inc';
    //$path_app_includes .= PATH_SEPARATOR . _DIR_APP_ROOT_ . 'componentes';
    //$path_app_includes .= PATH_SEPARATOR . _DIR_APP_ROOT_ . 'conexao';
    //$path_app_includes .= PATH_SEPARATOR . _DIR_APP_ROOT_ . 'controle';
    //$path_app_includes .= PATH_SEPARATOR . _DIR_APP_ROOT_ . 'modelo';
    //$path_app_includes .= PATH_SEPARATOR . _DIR_APP_ROOT_ . 'visao';
    //$path_app_includes .= PATH_SEPARATOR . _DIR_APP_ROOT_ . 'imagem';

    set_include_path( strtr($path_app_includes, $arrStrings) );

    define( '_SISTEMA_LOGO_',        'sisref.gif' );
    define( '_SISTEMA_SIGLA_',       'SISREF' );
    define( '_SISTEMA_DESCRICAO_',   'Sistema de Registro Eletr�nico de Frequ�ncia' );
    define( '_SISTEMA_TITULO_NOME_', _SISTEMA_SIGLA_ . ' -_ ' . _SISTEMA_DESCRICAO_ );
    define( '_SISTEMA_SERIAL_',      _SISTEMA_SIGLA_ . '_' . 'a3e73644b' );

    define( '_SISTEMA_EMAIL_', 'sgp-sisref@planejamento.gov.br' );
    define( '_SISTEMA_EMAIL_ERROS_', 'msg.sistema.erros@gmail.com' );

    define( '_SISTEMA_ORGAO_',              '17000' );
    define( '_SISTEMA_ORGAO__SIGLA_',       'ME' );
    define( '_SISTEMA_TITULO_ORGAO_',       'MINIST�RIO DA ECONOMIA' );
    define( '_SISTEMA_TITULO_DIRETORIA_',   'DIRETORIA DE GEST�O DE PESSOAS' );
    define( '_SISTEMA_TITULO_COORDENACAO_', 'COORDENA��O-GERAL DE GEST�O DE PESSOAS' );

    define( '_SISTEMA_CORAZUL_', false );

    define( '_SISTEMA_INDISPONIVEL_', false );
    define( '_SISTEMA_INDISPONIVEL_MENSAGEM_', 'Sistema temporariamente indispon�vel! Retorno previsto em 30 minutos.' );

    define( '__HISTORICO_DESATIVADO__', false );

    define( '_EMAIL_GESTORES_', 'sgp-sisref@planejamento.gov.br' );

    define( '_SENHA_SUPERVISAO_WEBSERVICE_', base64_encode('auT!sisref@supervisao') );

    // fun��es e classes de uso geral
    include_once( 'functions.php' );
    include_once( 'function_gravalog.php' );
    include_once( 'class_database.php' );
    include_once( 'class_database_extends.php' );
    // include_once( 'conecta.php' );
    include_once( 'class_formpadrao.php' );
    include_once( 'class_definir.jornada.php' );
    include_once( 'functions_select.php' );
    include_once( 'control_navegacao.php' );
    include_once( 'class_form.frequencia.php' );
    include_once( 'inc/browser_detection.php' ); // localiza��o /inc

    // salva a mensagem para usuario, antes de encerrar a se��o
    $mensagemUsuario = $_SESSION['mensagem-usuario'];

    // Define uma constante contendo o microtime atual
    define('__microTIME__', tempo_execucao());

   if($_SESSION['sModuloPrincipalAcionado'] == "sogp"){
    //set_exception_handler("error_handler");
   }
    //configLimitaCache();

    // CONFIGURa��o - SERPRO
    $host   = 'localhost';
    $dbuser = 'sisref_app';
    $dbpass = 'SisReF2013app';
    $dbname = 'dbpro_11310_sisref';


//Funcao para pegar o header com o link correto
function getHeader(){

    $homeLink="javascript:parent.main.location.replace('principal_abertura.php');";
    $html2 = "<ul class='nav navbar-nav navbar-right'>
              <li><a href=\"javascript:parent.main.location.replace('finaliza2.php?modulo=sogp');\"><span class='glyphicon glyphicon-log-out'></span> Sair</a></li>
        </ul>";

    $html3 ="<ul class='nav navbar-nav navbar-right'>
              <li><a href='./finaliza.php'><span class='glyphicon glyphicon-log-out'></span> Sair</a></li>
        </ul>";

    $logoutEntrada = "<li><a href=\"index.html\"><span class=\"glyphicon glyphicon-log-out\"></span>  SAIR </a></li>";

    $sgop = true;

    if($_SESSION['sModuloPrincipalAcionado'] != 'sogp' && $_SESSION['sModuloPrincipalAcionado'] != "chefia"){
        $homeLink = "./entrada.php";
        $sgop=false;
    }

    $html = file_get_contents("./html/header-rh.php");
    $html = str_replace("--link--",$homeLink,$html);

    if($sgop)
        $html = str_replace("--acoes--",$html2,$html);
    else if(!$sgop && $_SESSION['logado'] =='SIM')
        $html = str_replace("--acoes--",$html3,$html);
    else
        $html = str_replace("--acoes--","",$html);
    return $html;
}

function getFooter(){
    return file_get_contents("html/footer.php");
}

function verificaSeEhMaquinaOriginal()
{

}
