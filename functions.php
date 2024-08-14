<?php

$qtd               = explode('/', $_SERVER['PHP_SELF']);
$path_dots_slashes = str_repeat("../", count($qtd) - 3);

// Inicia a sessão e carrega as funções de uso geral
include_once( $path_dots_slashes . "config.php" );

include_once( 'inc/email_lib.php' );
include_once( 'inc/MyCripty.class.php' );
include_once( "class_ocorrencias_grupos.php" );
include_once( "src/controllers/DadosServidoresController.php" );
include_once( "src/controllers/TabFacultativo172Controller.php" );
include_once( "src/controllers/TabOcorrenciaController.php" );


/**  @package Class and Functions
 * +-------------------------------------------------------------+
 * |                                                             |
 * | SISREF - Funções e Classes                                  |
 * |                                                             |
 * | @package    : class and functions                           |
 * | @copyright  : (C) 2004-.... INSS                            |
 * | @license    :                                               |
 * | @link       : http://www-inss                               |
 * | @subpackage :                                               |
 * | @author     :                                               |
 * |                                                             |
 * +-------------------------------------------------------------+
 * |   Convenções:                                               |
 * |      [] -> indicam parametros obrigatórios                  |
 * |      <> -> indicam parametros                               |
 * +-------------------------------------------------------------+
 * */

## Define o idioma e seus padrões
#
setlocale(LC_ALL, 'pt_BR');

## Existindo o arquivo e não sendo servidor local
#
includeMail();

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : includeMail                                  |
 * | @description : inclui as classes e funções para             |
 * |                envio de email                               |
 * |                                                             |
 * | @param  : void                                              |
 * | @return : void                                              |
 * | @usage  : includeMail();                                    |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : PEAR Mail.php  (class)                        |
 * +-------------------------------------------------------------+
 **/
function includeMail()
{
    if ($_SERVER['HTTP_HOST'] != 'localhost' && file_exists('/usr/share/php/Mail.php'))
    {
        include_once( '/usr/share/php/Mail.php' );
        include_once( '/usr/share/php/Mail/mail.php' );
        include_once( '/usr/share/php/Mail/mime.php' );
        include_once( '/usr/share/php/Mail/mimePart.php' );
    }
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : limitaCache                                  |
 * | @description : define os limites do cache de memoria        |
 * |                                                             |
 * | @param  : void                                              |
 * | @return : void                                              |
 * | @usage  : limitaCache();                                    |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 **/
function limitaCache()
{
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // A pagina ja expirou
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // Foi modificada em
    header('Cache-Control: no-store, no-cache, must-revalidate'); // Evitar salvar em cache do cliente HTTP/1.1
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache'); // Evitar salvar em cache do cliente HTTP/1.0
    header('Cache: no-cache');
    header('Expires: 0'); // Proxies.
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : configLimitaCache                            |
 * | @description : define os limites do cache de memoria        |
 * |                                                             |
 * | @param  : void                                              |
 * | @return : void                                              |
 * | @usage  : configLimitaCache();                              |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 **/
function configLimitaCache()
{
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // A pagina ja expirou
    //header('Last Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // Foi modificada em
    header('Cache-Control: no-cache, no-store, must-revalidate, post-check=0, pre-check=0');
    header('Cache-Control: no-store, no-cache, must-revalidate'); // Evitar salvar em cache do cliente HTTP/1.1
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache'); // Evitar salvar em cache do cliente HTTP/1.0
    header('Cache: no-cache');
    header('Expires: 0'); // Proxies.
    header('Expires: -1'); // Proxies.
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : tempoExpiracaoDaPagina                       |
 * | @description : define os limites de tempo da sessão         |
 * |                                                             |
 * | @param  : [<integer>] - $tempoExpira                        |
 * | @return : void                                              |
 * | @usage  : tempoExpiracaoDaPagina( 180 );                    |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 **/
function tempoExpiracaoDaPagina($tempoExpira = 180)
{
    //todas as páginas que quiser um lifetime para a sessão
    ini_set('session.gc_probability', 100);
    ini_set('session.gc_maxlifetime', $tempoExpira);
    ini_set('session.cookie_lifetime', $tempoExpira);
    ini_set('session.cache_expire', $tempoExpira);
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : inverteData                                  |
 * | @description : inverte data tipo dd/mm/yyyy para yyyymmdd   |
 * |                                                             |
 * | @param  : [<string>] - $data1                               |
 * | @return : <string>   - Data invertida sem traco ou barra    |
 * | @usage  : inverteData('20/01/2009');                        |
 * |           //resulta em '20090120'                           |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : data2arrayBR                                  |
 * +-------------------------------------------------------------+
 **/
function inverteData($data1)
{
    $datatrans = data2arrayBR($data1);
    return ($datatrans[2] . $datatrans[1] . $datatrans[0]);
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : conv_data                                    |
 * | @description : converte data tipo dd/mm/yyyy para           |
 * |                yyyy-mm-dd para gravar nas tabelas Mysql     |
 * |                                                             |
 * | @param  : [<string>] - $data1                               |
 * | @return : <string>   - Data invertida com traco             |
 * | @usage  : conv_data('20/01/2009');                          |
 * |           //resulta em '2009-01-20'                         |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : data2arrayBR                                  |
 * +-------------------------------------------------------------+
 **/
function conv_data($data1)
{
    $datatrans = data2arrayBR($data1);
    return ($datatrans[2] . '-' . $datatrans[1] . '-' . $datatrans[0]);
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : dataseca                                     |
 * | @description : converte data tipo yyyymmdd para dd/mm/yyyy  |
 * |                                                             |
 * | @param  : [<string>] - $data1                               |
 * | @return : <string>   - Data no formato brasileiro           |
 * | @usage  : dataseca('20090120');                             |
 * |          //resulta em '20/01/2009'                          |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 **/
function dataseca($data1)
{
    $datatrans = data2arrayBR($data1);
    return ($datatrans[0] . '/' . $datatrans[1] . '/' . $datatrans[2]);
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : databarra                                    |
 * | @description : converte data tipo yyyy-mm-dd                |
 * |                para dd/mm/yyyy                              |
 * |                                                             |
 * | @param  : [<string>] - $data1                               |
 * | @return : <string>   - Data no formato brasileiro           |
 * | @usage  : databarra('2009-01-20');                          |
 * |          //resulta em '20/01/2009'                          |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : data2arrayBR                                  |
 * +-------------------------------------------------------------+
 **/
function databarra($data1)
{
    $datatrans = data2arrayBR($data1);
    return ($datatrans[0] . '/' . $datatrans[1] . '/' . $datatrans[2]);
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : datatraco                                    |
 * | @description : converte data tipo yyyy-mm-dd                |
 * |                para dd/mm/yyyy (compatibilidade)            |
 * |                                                             |
 * | @param  : [<string>] - $parm1                               |
 * | @return : <string>   - Data no formato brasileiro           |
 * | @usage  : datatraco('2009-01-20');                          |
 * |          //resulta em '20/01/2009'                          |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : databarra  (function)                         |
 * +-------------------------------------------------------------+
 **/
function datatraco($parm1)
{
    return databarra($parm1);
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : dataWSSiapeMySQL                             |
 * | @description : converte data tipo ddmmyyyy                  |
 * |                para yyyy-mm-dd                              |
 * |                                                             |
 * | @param  : [<string>] - $parm1                               |
 * | @return : <string>   - Data no formato brasileiro           |
 * | @usage  : datatraco('2009-01-20');                          |
 * |          //resulta em '20/01/2009'                          |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : databarra  (function)                         |
 * +-------------------------------------------------------------+
 **/
function dataWSSiapeMySQL($parm1)
{
    $dia = poe_traco($parm1, '/');

    return conv_data($dia);
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : data2arrayBR                                 |
 * | @description : Retorna o dia, mês e ano em um vetor         |
 * |                Informamos a data no padrao americano ou     |
 * |                brasileiro e a mesma é retornada em um vetor |
 * |                com o dia, mês e ano separados               |
 * |                                                             |
 * | @param  : <string>  - $dia                                  |
 * |                       data desejada                         |
 * | @usage  : $data = data2arrayBR('2010-02-01'); ou            |
 * |           $data = data2arrayBR('01/02/2010');               |
 * |          // retorna array( '01', '02', '2010' )             |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 **/
function data2arrayBR($dataform='')
{
    $dia_sem_hora = explode(' ',$dataform);

    $dia = trim(strtr($dia_sem_hora[0], array('-'=>'/')));

    $data = explode('/',$dia);

    if (count($data) == 1 && strlen($data[0]) == 8)
    {
        $array = data2arrayBR_semSeparador($data[0]);
    }
    else
    {
        if (strlen($data[0]) == 4)
        {
            $dia = $data[2] . "/" . $data[1] . "/" . $data[0];
        }

        $data = explode('/',$dia);

        $dia = substr('00'   . (int) $data[0], -2);
        $mes = substr('00'   . (int) $data[1], -2);
        $ano = substr('0000' . (int) $data[2], -4);

        $array = array( $dia, $mes, $ano );
    }

    return $array;
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : data2arrayBR_semSeparador                    |
 * | @description : Trata data sem separador e infvertida        |
 * |                                                             |
 * | @param  : <string>  - $dataform                             |
 * | @usage  : $data = data2arrayBR_semSeparador('20100201');    |
 * |          // retorna array( '01', '02', '2010' )             |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 **/
function data2arrayBR_semSeparador($dataform='')
{
    $dia0 = (int) $dataform;
    $dia1 = (string) $dia0;

    $dia = substr($dia1, 6, 2);
    $mes = substr($dia1, 4, 2);
    $ano = substr($dia1, 0, 4);

    return array( $dia, $mes, $ano );
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : dataDia                                      |
 * | @description : Retorna o dia                                |
 * |                Informamos a data no padrao americano ou     |
 * |                brasileiro e é retornado o dia               |
 * |                                                             |
 * | @param  : <string>  - $dia                                  |
 * |                       data desejada                         |
 * | @usage  : $data = dataDia('2010-02-01'); ou                 |
 * |           $data = dataDia('01/02/2010');                    |
 * |          // retorna '01'                                    |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : data2arrayBR                                  |
 * +-------------------------------------------------------------+
 **/
function dataDia($dia)
{
    $data = data2arrayBR($dia);
    return $data[0];
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : dataMes                                      |
 * | @description : Retorna o mês                                |
 * |                Informamos a data no padrao americano ou     |
 * |                brasileiro e é retornado o mês               |
 * |                                                             |
 * | @param  : <string>  - $dia                                  |
 * |                       data desejada                         |
 * | @usage  : $data = dataMes('2010-02-01'); ou                 |
 * |           $data = dataMes('01/02/2010');                    |
 * |          // retorna '02'                                    |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : data2arrayBR                                  |
 * +-------------------------------------------------------------+
 **/
function dataMes($dia)
{
    $data = data2arrayBR($dia);
    return $data[1];
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : dataAno                                      |
 * | @description : Retorna o ano                                |
 * |                Informamos a data no padrao americano ou     |
 * |                brasileiro e é retornado o ano               |
 * |                                                             |
 * | @param  : <string>  - $dia                                  |
 * |                       data desejada                         |
 * | @usage  : $data = dataAno('2010-02-01'); ou                 |
 * |           $data = dataAno('01/02/2010');                    |
 * |          // retorna '2010'                                  |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : data2arrayBR                                  |
 * +-------------------------------------------------------------+
 **/
function dataAno($dia)
{
    $data = data2arrayBR($dia);
    return $data[2];
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : validaData                                   |
 * | @description : Valida a data informada                      |
 * |                                                             |
 * | @param  : <string>  - $dataform                             |
 * |                       data para validação                   |
 * | @usage  : $data = validaData('2010-02-01'); ou              |
 * |           $data = validaData('01/02/2010');                 |
 * |          // retorna true se data valida                     |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : data2arrayBR                                  |
 * +-------------------------------------------------------------+
 **/
function validaData($dataform)
{
    $retorno = false;

    if (strlen(trim($dataform)) == 10)
    {
        $datatrans = data2arrayBR($dataform);
        settype($datatrans[0], 'integer');
        settype($datatrans[1], 'integer');
        settype($datatrans[2], 'integer');
        $retorno = (checkdate($datatrans[1], $datatrans[0], $datatrans[2]));
    }

    return $retorno;
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : tiravirgula                                  |
 * | @description : troca virgula por ponto                      |
 * |                                                             |
 * | @param  : [<string>] - $parm1                               |
 * | @return : <string>   - Texto com virgula trocada por ponto  |
 * | @usage  : tiravirgula('100,00');                            |
 * |          //resulta em '100.00'                              |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 **/
function tiravirgula($parm1)
{
    return strtr($parm1, ',', '.');
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : tiraponto                                    |
 * | @description : elimina os pontos                            |
 * |                                                             |
 * | @param  : [<string>] - $parm1                               |
 * | @return : <string>   - Texto sem pontos                     |
 * | @usage  : tiraponto('1.100,00');                            |
 * |          //resulta em '1100,00'                             |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 **/
function tiraponto($parm1)
{
    return strtr($parm1, array('.' => ''));
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : sodecimal                                    |
 * | @description : elimina os pontos de milhar e troca a        |
 * |                vírgula por ponto                            |
 * |                                                             |
 * | @param  : [<string>] - $parm1                               |
 * | @return : <string>   - Texto sem pontos de milhar           |
 * |                     e ponto decimal                         |
 * | @usage  : sodecimal('1.100,00');                            |
 * |          //resulta em '1100.00'                             |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 **/
function sodecimal($parm1)
{
    return strtr($parm1, array('.' => '', ',' => '.'));
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : limpa_barra                                  |
 * | @description : elimina as barras                            |
 * |                                                             |
 * | @param  : [<string>] - $parm1                               |
 * | @return : <string>   - Texto sem as barras                  |
 * | @usage  : limpa_barra('20/01/2010');                        |
 * |          //resulta em '20012010'                            |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 **/
function limpa_barra($parm1)
{
    return strtr($parm1, array('/' => '', '-' => ''));
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : limpa_traco                                  |
 * | @description : elimina os traços                            |
 * |                                                             |
 * | @param  : [<string>] - $parm1                               |
 * | @return : <string>   - Texto sem os traços                  |
 * | @usage  : limpa_traco('20/01/2010');                        |
 * |          //resulta em '20012010'                            |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 **/
function limpa_traco($parm1)
{
    return strtr($parm1, array('/' => '', '-' => ''));
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : poe_traco                                    |
 * | @description : converte data de ddmmaaaa para dd-mm-aaaa    |
 * |                                                             |
 * | @param  : [<string>] - $parm1                               |
 * | @param  : [<string>] - $separador                           |
 * | @return : <string>   - Texto com os traços                  |
 * | @usage  : poe_traco('20012010');                            |
 * |             //resulta em '20-01-2010'                       |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 **/
function poe_traco($parm1, $separador='-')
{
    return substr($parm1, 0, 2) . $separador . substr($parm1, 2, 2) . $separador . substr($parm1, 4, 4);
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : poevirgula                                   |
 * | @description : troca ponto por virgula                      |
 * |                                                             |
 * | @param  : [<string>] - $parm1                               |
 * | @return : <string>   - Texto com vírgula                    |
 * | @usage  : poevirgula('20.01');                              |
 * |          //resulta em '20,01'                               |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 **/
function poevirgula($parm1)
{
    return strtr($parm1, '.', ',');
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : dias_decorr                                  |
 * | @description : dias decorridos entre duas datas informadas  |
 * |                no padrão aaaa-mm-dd, inclusive a            |
 * |                data inicial                                 |
 * |                                                             |
 * | @param  : [<string>] - $parm1                               |
 * |                        data inicial padrão aaaa-mm-dd       |
 * | @param  : [<string>] - $parm2                               |
 * |                        data final no formato aaa-mm-dd      |
 * | @return : <integer>  - dias decorridos                      |
 * | @usage  : dias_decorr('2010-01-02','2010-01-05');           |
 * |          //resulta 4 dias                                   |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 **/
function dias_decorr($parm1, $parm2)
{
    $data1 = inverteData($parm1);
    $data2 = inverteData($parm2);

    return ((strtotime($data2) - strtotime($data1)) / 86400) + 1;
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : dif_data                                     |
 * | @description : dias decorridos entre duas datas informadas  |
 * |                no padrão aaaa-mm-dd                         |
 * |                                                             |
 * | @param  : [<string>] - $DataI                               |
 * |                        data inicial padrão aaaa-mm-dd       |
 * | @param  : [<string>] - $DataF                               |
 * |                        data final no formato aaa-mm-dd      |
 * | @return : <integer>  - dias decorridos                      |
 * | @usage  : dif_data('2010-01-02','2010-01-05');              |
 * |          //resulta 3 dias                                   |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function dif_data($DataI, $DataF)
{
    $DataInicial = getdate(strtotime($DataI));
    $DataFinal   = getdate(strtotime($DataF));

    // Calcula a Diferença
    $Dif         = ($DataFinal[0] - $DataInicial[0]) / 86400;

    return $Dif;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : CalcularIdade                                |
 * | @description : calcula idade a partir da data de nascimento |
 * |                que deve estar no formato dd-mm-aaa          |
 * |                                                             |
 * | @param  : [<string>] - $nascimento                          |
 * |                        data inicial padrão dd-mm-aaaa       |
 * | @return : <integer>  - dias decorridos                      |
 * | @usage  : CalcularIdade('15-02-1985');                      |
 * |          //resulta em  22 anos                              |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function CalcularIdade($nascimento)
{
    $hoje  = date('d-m-Y'); //pega a data de hoje
    $aniv  = data2arrayBR($nascimento); //separa a data de nascimento em array
    $atual = data2arrayBR($hoje); //separa a data de hoje em array

    $idade = $atual[2] - $aniv[2];

    if ($aniv[1] > $atual[1]) //verifica se o mês de nascimento é maior que o mês atual
    {
        $idade--; //tira um ano, já que ele não fez aniversário ainda
    }
    elseif ($aniv[1] == $atual[1] && $aniv[0] > $atual[0]) //verifica se o dia de hoje é maior que o dia do aniversário
    {
        $idade--; //tira um ano se não fez aniversário ainda
    }
    return $idade; //retorna a idade da pessoa em anos

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : calcular_tempo_trasnc                        |
 * | @description : Obtem o total de Minutos ou Horas            |
 * |                transcorridas entre dois tempos de um        |
 * |                mesmo dia                                    |
 * |                                                             |
 * | @param  : [<string>] - $hora1                               |
 * |                        hora inicial no padrão hh:mm         |
 * | @param  : [<string>] - $hora2                               |
 * |                        hora final no padrão hh:mm           |
 * | @return : <integer>  - horas e/ou minutos decorridos        |
 * | @usage  : calcular_tempo_trasnc('13:56','16:12');           |
 * |          //resulta em 02:16 horas                           |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function calcular_tempo_trasnc($hora1, $hora2)
{
    $separar[1] = explode(':', $hora1);
    $separar[2] = explode(':', $hora2);

    $total_minutos_transcorridos[1] = ($separar[1][0] * 60) + $separar[1][1];
    $total_minutos_transcorridos[2] = ($separar[2][0] * 60) + $separar[2][1];
    $total_minutos_transcorridos    = $total_minutos_transcorridos[2] - $total_minutos_transcorridos[1];

    if ($total_minutos_transcorridos <= 59)
    {
        return('00:' . $total_minutos_transcorridos);
    }
    elseif ($total_minutos_transcorridos > 59)
    {
        $hora_transcorrida     = round($total_minutos_transcorridos / 60);
        if ($hora_transcorrida <= 9)
            $hora_transcorrida     = '0' . $hora_transcorrida;
        $minutos_transcorridos = $total_minutos_transcorridos % 60;
        if ($minutos_transcorridos <= 9)
            $minutos_transcorridos = '0' . $minutos_transcorridos;
        return ($hora_transcorrida . ':' . $minutos_transcorridos);
    }

}

//fim da função para calcular diferença de horas.

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : timediff                                     |
 * | @description : calcula a diferença entre dois horários      |
 * |                                                             |
 * | @param  : <integer>  $date1  timestamp                      |
 * | @param  : <integer>  $date2  timestamp                      |
 * | @return : <array>                                           |
 * | @author : Felipe Nascimento S. Pena                         |
 * | @email  : felipensp [at] gmail [dot] com                    |
 * |                                                             |
 * | Informações:                                                |
 * | - O timestamp do primeiro argumento deve ser maior que o do |
 * |   segundo.                                                  |
 * | - Se o segundo argumento não é informado, ou é NULL, será   |
 * |   utilizado o timestamp atual.                              |
 * | *                                                           |
 * | * Exemplos de utilização                                    |
 * | *                                                           |
 * | * Exemplo 1: -------------------------------------------    |
 * | *                                                           |
 * | * $diff = timediff(strtotime('+1 week'), time());           |
 * | * vprintf('%02d:%02d:%02d', $diff);                         |
 * | * // 168:00:00                                              |
 * | *                                                           |
 * | * Exemplo 2: -------------------------------------------    |
 * | *                                                           |
 * | * list($h, $m, $s) =                                        |
 * | *  timediff(strtotime('+2 minutes +25 seconds'), time());   |
 * | *                                                           |
 * | * $time = array();                                          |
 * | *                                                           |
 * | * if ($h) $time[] = $h.' hor'.(($h > 1) ? 'as' : 'a');      |
 * | * if ($m) $time[] = $m.' minut'.(($m > 1) ? 'os' : 'o');    |
 * | * if ($s) $time[] = $s.' segund'.(($s > 1) ? 'os' : 'o');   |
 * | *                                                           |
 * | * print implode(' ', $time);                                |
 * | * // 2 minutos 25 segundos                                  |
 * | *                                                           |
 * | * Exemplo 3: -------------------------------------------    |
 * | *                                                           |
 * | * list(,$m) = timediff(null, strtotime('-5 minutes'));      |
 * | * printf('%d minutos atrás', $m);                           |
 * | * // 5 minutos atrás                                        |
 * | *                                                           |
 * | * Exemplo 4: -------------------------------------------    |
 * | *                                                           |
 * | * vprintf( '%d hora(s), %d minuto(s) e %d segundo(s)',      |
 * | *   timediff(time() + 3620, time()));                       |
 * | * // 1 hora(s), 0 minuto(s) e 20 segundo(s)                 |
 * | *                                                           |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function timediff($date1 = null, $date2 = null)
{
    // Valores padrão
    if (is_null($date1))
        $date1 = time();
    if (is_null($date2))
        $date2 = time();

    // Verificando argumentos
    if (!is_int($date1) || !is_int($date2))
        return false;
    if ($date2 >= $date1)
        return false;

    // Diferença entre os timestamps
    $diff = $date1 - $date2;

    $time = array(0, 0, 0);

    if ($diff >= 3600)
    {
        $time[0] = floor(($diff >= 86400) ? ($diff / 86400) * 24 : $diff / 3600);
    }

    if ($calc = ($diff % 3600))
    {
        $time[1] = floor($calc / 60);
        $time[2] = $calc % 60;
    }

    return $time;

}

/**  @Class
 * +-------------------------------------------------------------+
 * | @class       : calendario                                   |
 * | @description : exibe a data por extenso                     |
 * |                                                             |
 * | @param  : void                                              |
 * | @return : void                                              |
 * | $method : data()                                            |
 * |           Função para exibir a data do dia, por extenso     |
 * | @usage  : $oData = new calendario();                        |
 * |          $oData->data()                                     |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
class calendario
{

    /**  @Method
     * Função para exibir a data do dia, por extenso
     * */
    function data()
    {
        // descricao do dia da semana
        $dias_da_semana = array('Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sabado');

        // descricao do mes
        $nome_do_mes = array('', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');

        // Comandos para pegar o dia
        $dnom = date('w');
        $mes  = date('n');

        echo '<font face=\'Verdana\' size=\'2\'>', $dias_da_semana[$dnom], ', ', date('d'), ' de ', $nome_do_mes[$mes], ' de ', date('Y'), '</font>';

    }

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : usuarios_ativos                              |
 * | @description : Calcula o numero de usuarios ativos e exibe  |
 * |                                                             |
 * | @param  : <string>  - $xsiape                               |
 * |                       matrícula siape                       |
 * | @param  : <string>  - $xlotacao                             |
 * |                       unidade de lotação                    |
 * | @return : <integer> - quantidade de usuários on-line        |
 * | @usage  : usuarios('9999999','99999999');                   |
 * |          //resulta em XX usuários                           |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : DataBase  (class)                             |
 * +-------------------------------------------------------------+
 * */
function usuarios_ativos($siape = '', $lotacao = '', $modulo = 'entrada')
{
    $siape      = getNovaMatriculaBySiape($siape);
    $ip         = getIpReal(); // atribuimos o número IP da máquina utilizada
    $hora_atual = time(); // definimos a hora atual
    $limite     = $hora_atual - 10 * 5; // $hora_atual-10*60; // limite

    $oDBase = new DataBase('PDO'); // instancia a conexao a base de dados

    if ($siape != '')
    {
      ////>		$oDBase->query( 'DELETE FROM control_ip WHERE fecha < "'.$limite.'" ' ); // apaga os registros com IP inativos (24 min)
      ////>		$oDBase->query( 'SELECT ip, fecha FROM control_ip WHERE ip = "'.$ip.'" AND siape = "'.$siape.'" ' ); // verifica se o IP está registrado
      $oDBase->query( 'SELECT ip FROM control_ip WHERE fecha >= "'.$limite.'" ' ); // IP ativos
      $NumRows = $oDBase->num_rows();
      $oDBase->query( 'SELECT ip, fecha FROM control_ip WHERE ip = "'.$ip.'" AND siape = "'.$siape.'" ' ); // verifica se o IP está registrado

      // se existe atualizamos o campo 'fecha'
      // se não, incluimos um novo registro
      if ($oDBase->num_rows() != 0)
      {
          $oDBase->query( 'UPDATE control_ip SET fecha="'.$hora_atual.'" WHERE ip = "'.$ip.'"; ' );
      }
      else
      {
          // usuarios ativos utilizando o módulo
          $oDBase->query( 'INSERT INTO control_ip SET ip="'.$ip.'", fecha="'.$hora_atual.'", siape="'.$siape.'", lotacao="'.$lotacao.'", datahora=NOW(), modulo="'.$modulo.'", ip_aplicacao="'.$_SERVER['SERVER_ADDR'].'"; ' );

          // usuarios por minuto
          $oDBase->query( 'INSERT INTO control_ip_registro SET ip="'.$ip.'", siape="'.$siape.'", lotacao="'.$lotacao.'", datahora=NOW(), modulo="'.$modulo.'", ip_aplicacao="'.$_SERVER['SERVER_ADDR'].'"; ' );
      }
    }

    // calcula o numero de IP ativos
    /*
      if (substr_count('entrada__entrada1__entrada2__entrada3__entrada4',$modulo) > 0)
      {
      $oDBase->query( 'SELECT COUNT(modulo) AS total FROM control_ip WHERE LEFT(modulo,7) = "entrada" ' );
      }
      else
      {
      $oDBase->query( 'SELECT COUNT(modulo) AS total FROM control_ip WHERE modulo <> "entrada" ' );
      }
     */
    /*
      $oDBase->query( 'SELECT COUNT(*) AS total FROM control_ip ORDER BY modulo ' );
      $total = $oDBase->fetch_object()->total;

      // libera memoria e fecha a conexão
      $oDBase->free_result();
      $oDBase->close();
     */
    // retorna o numero de usuarios ativos
    return 1; //$total; //return 1;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : impressora                                   |
 * | @description : exibe a imagem de uma impressora com link    |
 * |                para direcionar para impressão               |
 * |                                                             |
 * | @param  : <integer> - $topo                                 |
 * |                       coordenada topo para exibição imagem  |
 * | @param  : <integer> - $lateral                              |
 * |                       coordenada left para exibição imagem  |
 * | @param  : <string>  - $posicao                              |
 * |                       tipo do posicionamento                |
 * | @param  : <string>  - $rolagem                              |
 * |                       estabelece se a imagem ficará fixa    |
 * | @return : void                                              |
 * | @usage  : impressora(30,650,'absolute','fixo');             |
 * |          //exibe a figura da impressora na posição definida |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function impressora($topo = 30, $lateral = 650, $posicao = 'absolute', $rolagem = 'fixo')
{
    echo '<div id=\'di1\' style=\'top:' . $topo . '; left:' . $lateral . '; position:' . $posicao . '\'><img src=\'' . _DIR_IMAGEM_ . 'impremini.gif\' border=\'0\' title=\'Imprimir\' onclick=\'window.print()\' style=\'cursor:pointer\'></div>';

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : mascara_mes                                  |
 * | @description : exibe o mes e ano no formato mm/aaaa         |
 * |                                                             |
 * | @param  : <string> - $arg1                                  |
 * |                      mes e ano no formato mmaaaa            |
 * | @return : <string> - O mes e ano formatado mm/aaaa          |
 * | @usage  : mascara_mes('022010');                            |
 * |          //resulta em "02/2010"                             |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function mascara_mes($arg1 = '')
{
    if ($arg1 == '')
    {
        $xarg1 = '';
    }
    else
    {
        $xarg1 = substr($arg1, 0, 2) . '/' . substr($arg1, 2, 4);
    }
    return $xarg1;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : valDVmod11                                   |
 * | @description : cálculo do módulo 11                         |
 * |                                                             |
 * | @param  : <string> - $num                                   |
 * |                      valor para cálculo do dígito           |
 * |                      verificador                            |
 * | @return : <string> - Retorna o dígito verificador           |
 * | @usage  : valDVmod11('0982221');                            |
 * |          //resulta em '1'                                   |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function valDVmod11($num)
{
    $val = 0;
    $mm  = 2;
    for ($pos = (strlen($num) - 1); $pos >= 0; $pos--)
    {
        $val += ($num[$pos] * $mm);
        $mm++;
    }
    $dv = (($val * 10) % 11);
    $dv = ($dv >= 10 ? 1 : $dv);
    return $dv;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : comunicaErro                                 |
 * | @description : envia email informando um erro ocorrido      |
 * |                                                             |
 * | @param  : <string> - $msg                                   |
 * |                      mensagem a ser enviada por email       |
 * | @param  : <string> - $die  mensagem de erro                 |
 * | @return : void                                              |
 * | @usage  : comunicaErro('Solicitamos..','Erro no processo.');|
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function comunicaErro($msg = '', $die = '')
{
    // Comentei o trecho abaixo porque não está sendo enviado nenhum email e, com isso,
    // o trecho abaixo é executado sem necessidade.
    /*
      $oDBase = new DataBase('PDO');
      $oDBase->query( 'SELECT emails FROM config_suporte WHERE campo=\'suporte_dgp\' AND ativo=\'S\' ' );
      $registros = $oDBase->num_rows();
      if ($registros == 0)
      {
      $to = _SISTEMA_EMAIL_ERROS_ . '\n';
      }
      else
      {
      $to = '';
      while ($dados = $oDBase->fetch_object())
      {
      $to = ($to==''?'':',') . $dados->emails;
      }
      $to .= '\n';
      }

      //$subject = 'Content-type: text/html; charset=iso-8859-1\r\n';
      $subject .= 'IP SERVER: '.$_SERVER['SERVER_ADDR'].' - IP USUÁRIO: '.getIpReal().'\n';
     */
    $html    = '
	<!DOCTYPE HTML PUBLIC \'-//W3C//DTD HTML 4.0 Transitional//EN\'>
	<html>
	<meta http-equiv=\'Content-Type\' content=\'text/html;charset=iso-8859-1\'/>
	<body>
	<img src=\'' . _DIR_IMAGEM_ . _SISTEMA_LOGO_ . '\'><BR>
	<b>Equipe ' . _SISTEMA_SIGLA_ . ':</b><br><br>' . $msg . '
	</body>
	</html>';
    $headers = 'Content-type: text/html; charset=iso-8859-1\r\n';
    $headers .= 'From: ' . _SISTEMA_EMAIL_ERROS_ . '\n';

    // Fecha a conexão se estiver usando o PDO
    DataBase::fechaConexao();

    //exit('enviaremail' . $html);
    return false;
    $ok = enviarEmail($to, $subject, $html);

    if ($ok == 'erro' && !empty($die))
    {
        //die($die);
    }

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : informaAcesso                                |
 * | @description : informa quando alguem acessa o sistema       |
 * |                (manter por compatibilidade)                 |
 * |                                                             |
 * | @param  : <string> - $siape                                 |
 * |                      usuário que acessou                    |
 * | @return : void                                              |
 * | @usage  : informaAcesso('9999999');                         |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : enviarEmail  (function)                       |
 * +-------------------------------------------------------------+
 * */
function informaAcesso($siape)
{
    $emailFrom = 'From: ' . _SISTEMA_EMAIL_;
    $to        = _EMAIL_GESTORES_ . '\n';
    //$subject = 'Content-type: text/html; charset=iso-8859-1\r\n';
    $subject   .= 'ACESSO - IP: ' . getIpReal() . '\n';
    $html      = 'Equipe SISREF:</b><br><br>O servidor de matrícula ' . $siape . ' acessou o SISREF.';
    $headers   = '';
    //@mail($to, $subject, $html, $headers);
    enviarEmail($to, $subject, $html, $emailFrom);

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : enviarEmail                                  |
 * | @description : Função para encaminhar email                 |
 * |                                                             |
 * | @param  : <string> - $emailTo                               |
 * |                      email destino                          |
 * | @param  : <string> - $assunto                               |
 * |                      de que se trata o email                |
 * | @param  : <string> - $html                                  |
 * |                      conteudo                               |
 * | @param  : <string> - $emailFrom                             |
 * |                      quem envia o email                     |
 * | @return : void                                              |
 * | @usage  : enviarEmail(                                      |
 * |            'alguem@alguem.com','erro','texto',              |
 * |             'origem@origem.com'                              |
 * |          );                                                 |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : PEAR Mail.php  (class)                        |
 * +-------------------------------------------------------------+
 * */
function enviarEmail($emailTo, $assunto, $html, $emailFrom = '')
{
    $from    = (empty($emailFrom) ? _SISTEMA_EMAIL_ : $emailFrom);
    $to      = $emailTo;
    $subject = $assunto;
    $body    = '';

    $host     = 'relay01.planejamento.gov.br';
    $port     = 25;
    $username = '';
    $password = ''; // <------to authenticate with

    $text = '';

    $css = file(_DIR_CSS_ . 'estiloIE.css');

    $msg = '
	<!DOCTYPE HTML PUBLIC \'-//W3C//DTD HTML 4.0 Transitional//EN\'>
	<html>
	<title>SISREF</title>
	<meta http-equiv=\'Content-Type\' content=\'text/html;charset=iso-8859-1\'/>
	<style type=\'text/css\'>' . implode('', $css) . '</style>
	</head>
	<body>
	<img src=\'http://' . $_SERVER['SERVER_ADDR'] . '/' . _DIR_APP_ . _DIR_IMAGEM_ . _SISTEMA_LOGO_ . '\'><BR>';

    $nao_responder = '<small>Por favor, não responda a este e-mail. E-mail encaminhado automaticamente pelo ' . _SISTEMA_SIGLA_ . '.<br>Respostas a este e-mail não são monitoradas!</small>';
    $msg           .= $nao_responder . '<br><br><br>' . $html . '<br><br><br>' . $nao_responder;

    $msg .= '
	</body>
	</html>';

    $count = 0;

    // enviar e-mail
    $retorno = enviarEmail2($emailTo, '', $assunto, $msg);

    // se não houve sucesso
    // tentamos com outro processo
    if ($retorno == 'erro')
    {
        //Create the Mail_Mime
        if (class_exists('Mail_Mime'))
        {
            $mime = new Mail_Mime();

            // Set the email body

            $mime->setTXTBody($text);
            $mime->setHTMLBody($msg);

            // Set the headers:
            $mime->setFrom($from);
            $encoded_to = $to; //$mime->encodeRecipients($to); //verificar biblioteca
            $mime->setSubject($subject);
            $mime->addCC($to);

            // Get the formatted code:
            $body = $mime->get();

            /*
              $headers = array ('From' => $from,
              'To' => $to,
              'Subject' => $subject);
             */

            $smtp = Mail::factory('smtp', array(
                    'host'     => $host,
                    'auth'     => false,
                    'username' => $username,
                    'password' => $password)
            );

            $headers = $mime->headers();

            $count = 5;

            $mail = $smtp->send($encoded_to, $headers, $body);
            //echo 'hello';

            while (PEAR::isError($mail) && $count != 0)
            {
                if (PEAR::isError($mail))
                {
                    //echo('<p>' . $mail->getMessage() . '</p>');
                }
                //echo 'ola';
                sleep(rand(1, 5));
                $count--;
                $mail = $smtp->send($encoded_to, $headers, $body);
            }

            if ($count != 0)
            {
                //echo('<p>Message successfully sent!</p>');
            }
            else
            {
                //echo('Erro ao enviar email');
                //$smtp->send($encoded_to, $headers, $body);
                //echo $mail->getMessage();
            }
        }
    }
    else
    {
        $count = 5;
    }
    return $count;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : enviarEmail2                                 |
 * | @description : Função para encaminhar email                 |
 * |                                                             |
 * | @param  : <string> - $emailTo                               |
 * |                      email destino                          |
 * | @param  : <string> - $assunto                               |
 * |                      de que se trata o email                |
 * | @param  : <string> - $html                                  |
 * |                      conteudo                               |
 * | @param  : <string> - $emailFrom                             |
 * |                      quem envia o email                     |
 * | @return : void                                              |
 * | @usage  : enviarEmail2(                                     |
 * |            'alguem@alguem.com','origem@origem.com',         |
 * |            'assunto','texto',                               |
 * |          );                                                 |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : PEAR Mail.php  (class)                        |
 * +-------------------------------------------------------------+
 * */
function enviarEmail2($destinatarios, $from = '', $assunto = '', $mensagem = '')
{
    $SISTEMA_TITULO   = 'sgp.sisref';
    $SISTEMA_ENDERECO = '';

    /* Variáveis de acesso ao servidor de envio de e-mail SMTP */
    $smtp_server = 'relay01.planejamento.gov.br'; // Servidor SMTP
    $smtp_port   = 25; // Porta de Acesso ao Servidor SMTP
    $smtp_user   = ''; // usuário SMTP
    $smtp_pass   = ''; // Senha SMTP

    $array_destinatarios = explode(',', $destinatarios);

    $retorno = true;

    for ($i = 0; $i < count($array_destinatarios); $i++)
    {
        if (count($array_destinatarios) == 1)
            $destinatario = $destinatarios;
        else
            $destinatario = $array_destinatarios[$i];

        $email = new Email();
        $email->auth($smtp_server, $smtp_port, $smtp_user, $smtp_pass);

        if ($mensagem == '')
        {
            $mensagem = '
			<html>
				<head>
					<meta http-equiv=\'Content-Type\' content=\'text/html; charset=UTF-8\' />
				</head>
				<body>' . $mensagem . '
					<hr>
					<span style=\'font-size:8pt\'>Esta mensagem foi enviada automaticamente - ' . date('d/m/Y H:i:s') . ' - ' . $SISTEMA_ENDERECO . '</span>
				</body>
			</html>
			';
        }

        if (!$email->send($assunto, $mensagem, $destinatario, '', $SISTEMA_TITULO . '@planejamento.gov.br', $SISTEMA_TITULO . ' - Não Responder'))
        {
            $retorno = false;
        }
    }
    return ($retorno ? 'ok' : 'erro');
}

function enviarEmailSuporteSistema($assunto, $e)
{
    $mensagem  = '<b>Erro: </b>' . $e->getMessage().'<br>';
    $mensagem .= '<b>Na linha: </b>' . $e->getLine().'<br>';
    $mensagem .= '<b>Arquivo: </b>' . $e->getFile().'<br>';

    $oDBase = new DataBase();
    $oDBase->query("
    SELECT emails
        FROM config_suporte
            WHERE campo = 'suporte_ti'
    ");

    enviarEmail2($oDBase->fetch_object()->emails, '', $assunto, $mensagem);
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : mensagem                                     |
 * | @description : Exibe mensagem de erro/avisos,               |
 * |                com javascript:alert()                       |
 * |                                                             |
 * | @param  : <string> - $msg                                   |
 * |                      texto da mensagem a exibir             |
 * | @param  : <string> - $url                                   |
 * |                      pagina de destino                      |
 * | @param  : <string> - $voltar                                |
 * |                      quantas páginas retorna                |
 * | @param  : <string> - $type                                  |
 * |                      tipo da mensagem (warning,danger,...)  |
 * | @return : void                                              |
 * | @usage  : mensagem( 'Texto para exibir...','pagina.php' );  |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : voltar  (function)                            |
 * +-------------------------------------------------------------+
 * */
function mensagem($msg = '', $url = '', $voltar = null, $type = 'default', $inserthtml=false)
{
    if (!empty($msg))
    {
        if ($inserthtml === true)
        {
            echo "
            <html>
            <link type='text/css' rel='stylesheet' href='" . _DIR_CSS_ . "new/css/bootstrap.min.css'>
            <link type='text/css' rel='stylesheet' href='" . _DIR_CSS_ . "new/css/custom.css'>
            <link type='text/css' rel='stylesheet' href='" . _DIR_CSS_ . "estilos_new_layout.css'>
            <link type='text/css' rel='stylesheet' href='" . _DIR_CSS_ . "new/css/bootstrap-dialog.min.css'>
            <link type='text/css' rel='stylesheet' href='" . _DIR_CSS_ . "bootstrap-print.css' media='print'>
            <script type='text/javascript' src='" . _DIR_JS_ . "jquery-2.2.0.min.js'></script>
            <script type='text/javascript' src='" . _DIR_JS_ . "funcoes.js'></script>
            <script type='text/javascript' src='" . _DIR_CSS_ . "new/js/bootstrap.min.js'></script>
            <script type='text/javascript' src='" . _DIR_CSS_ . "new/js/bootstrap-dialog.min.js'></script>
            ";
            echo '<script> mostraMensagem(\'' . $msg . '\',\'' . $type . '\',\'' . $url . '\',\'' . $voltar . '\'); </script>';
            echo "<body>";
            echo "</body>";
            echo "</html>";
        }
        else
        {
            echo '<script> mostraMensagem(\'' . $msg . '\',\'' . $type . '\',\'' . $url . '\',\'' . $voltar . '\'); </script>';
        }
        //die();
    }

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : mensagemErro                                 |
 * | @description : Exibe mensagem de erro                       |
 * |                                                             |
 * | @param  : <string> - $msg                                   |
 * |                      texto da mensagem a exibir             |
 * | @param  : <string> - $url                                   |
 * |                      pagina de destino                      |
 * | @param  : <string> - $voltar                                |
 * |                      quantas páginas retorna                |
 * | @return : void                                              |
 * | @usage  : mensagem( 'Texto para exibir...','pagina.php' );  |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : voltar  (function)                            |
 * +-------------------------------------------------------------+
 * */
function mensagemErro($msg = '', $url = '', $voltar = null)
{
    if (!empty($msg))
    {
        mensagem($msg, 'danger', $url, $voltar);
    }

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : exibeMensagemUsuario                         |
 * | @description : Exibe mensagem de erro/avisos                |
 * |                                                             |
 * | @param  : <string> - $msg                                   |
 * |                      texto da mensagem a exibir             |
 * | @return : void                                              |
 * | @usage  : exibeMensagemUsuario( 'Texto para exibir' );      |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : voltar  (function)                            |
 * +-------------------------------------------------------------+
 * */
function exibeMensagemUsuario($mensagemUsuario = null)
{
    if (is_array($mensagemUsuario))
    {
        ?>
        <script>
            mostraMensagem("<?= $mensagemUsuario['mensagem']; ?>", "<?= $mensagemUsuario['severidade']; ?>");
        </script>
        <?php
        $_SESSION['mensagem-usuario'] = NULL;
        unset($_SESSION['mensagem-usuario']);
    }

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : setMensagemUsuario                           |
 * | @description : Define mensagem e tipo                       |
 * |                Tipo: sucess, danger, warning, info, default |
 * |                                                             |
 * | @param  : <string> - $msg                                   |
 * |                      texto da mensagem a exibir             |
 * | @param  : <string> - $url                                   |
 * |                      pagina de destino                      |
 * | @return : void                                              |
 * | @usage  : mensagem( 'Texto para exibir...','pagina.php' );  |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : voltar  (function)                            |
 * +-------------------------------------------------------------+
 * */
function setMensagemUsuario($msg = null, $severidade = 'info')
{
    if (!is_null($msg))
    {
        $_SESSION['mensagem-usuario'] = array('mensagem' => $msg, 'severidade' => $severidade);
    }

}

/**
 * Redireciona o script atual para uma página específica com um código de erro.
 * @param string $script Script destino
 * @param string $msg
 * @param bool $resetSession
 * @param string $severidade
 */
function retornaErro($script, $msg, $resetSession = true, $severidade = 'danger')
{
    DataBase::fechaConexao();

    if ($resetSession === true)
    {
        $ModuloPrincipalAcionado              = $_SESSION['sModuloPrincipalAcionado'];
        destroi_sessao();
        session_start();
        $_SESSION['sModuloPrincipalAcionado'] = $ModuloPrincipalAcionado;
    }

    setMensagemUsuario($msg, $severidade);

    header("Location: /sisref/{$script}");
    exit();

}

/**
 * Redireciona o script atual para uma página específica com um código de erro.
 * @param string $script Script destino
 * @param string $msg
 * @param bool $resetSession
 * @param string $severidade
 */
function retornaErroReplaceLink($script, $msg, $resetSession = true, $severidade = 'danger')
{
    DataBase::fechaConexao();

    if ($resetSession === true)
    {
        $ModuloPrincipalAcionado              = $_SESSION['sModuloPrincipalAcionado'];
        destroi_sessao();
        session_start();
        $_SESSION['sModuloPrincipalAcionado'] = $ModuloPrincipalAcionado;
    }

    setMensagemUsuario($msg, $severidade);

    //	header("Location: /sisref/{$script}");
    replaceLink($script);
    exit();

}

/**
 * Retorna o HTML para exibição da mensagem de erro.
 * @param $mensagem
 * @param string $nivel
 * @return string
 */
function getMensagemErroHTML($mensagem, $nivel = 'danger')
{
    return '<div class="form-group">
                <div class="alert alert-' . $nivel . ' alert-min text-center">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    ' . $mensagem . '
                </div>
            </div>';

}

/**
 * Retorna a mensagem de erro a partir de um código.
 * @param int $codigoErro
 * @return string
 */
function getMensagemErro($codigoErro, $nivel = 'danger')
{
    $mensagem = '';

    switch ($codigoErro)
    {
        case 1:
            $mensagem = 'Usuário inválido!';
            break;
        case 2:
            $mensagem = 'Senha inválida!';
            break;
        case 3:
            $mensagem = 'Captcha inválido!';
            break;
        case 4:
            $mensagem = 'Você não pode registrar frequência no SISREF, situação cadastral indefinida!';
            break;
        case 5:
            $mensagem = 'Você não pode registrar frequência no SISREF por estar cedido ou fixado!';
            break;
        case 6:
            $mensagem = 'Não é permitido registro de ponto para ocupantes de DAS 4, 5 ou 6!';
            break;
        case 7:
            $mensagem = 'Você não está autorizado a registrar frequência em dia não útil!';
            break;
        case 8:
            $mensagem = 'Não é permitido registrar entrada após as 22h!';
            break;
        case 9:
            $mensagem = 'Erro de processamento. Por favor, repita a operação!';
            break;
        case 10:
            $mensagem = "Horário de saída para almoço menor que Entrada (" . $_SESSION['ent'] . ")!";
            break;
        case 11:
            $mensagem = "Horário de retorno do almoço menor que Entrada (" . $_SESSION['ent'] . ")!";
            break;
        case 12:
            $mensagem = "Horário de retorno do almoço menor que Saída para o almoço (" . $_SESSION['iniint'] . ")!";
            break;
        case 13:
            $mensagem = "Não é permitido registrar retorno do intervalo com início do intervalo em branco!";
            break;
        case 14:
            $mensagem = "Não é permitido registrar intervalo inferior a uma hora!!!";
            break;
        case 15:
            $mensagem = "Já consta registro de encerramento de expediente!";
            break;
        case 16:
            $mensagem = "Não é permitido registrar saída inferior ao retorno do intervalo!";
            break;
        case 17:
            $mensagem = "Não é permitido registrar saída inferior ao horário de entrada!";
            break;
        case 18:
            $mensagem = "Não é permitido registrar saída com retorno do intervalo em branco quando tiver ocorrido início do intervalo!";
            break;
        case 19:
            $mensagem = $_SESSION['msg_limit_inss'];
            break;
        case 20:
            $mensagem = $_SESSION['msg_hora_und'];
            break;
        case 21:
            $mensagem = $_SESSION['msg_hora_und2'];
            break;
        case 22:
            $mensagem = "Seção finalizada.";
            break;
        case 23:
            $mensagem = "Por favor, realize o login para ter acesso ao sistema!";
            break;
        case 24:
            $mensagem = "Servidor não está ativo ou inexistente!";
            break;
        case 25:
            $mensagem = 'Sua sessão expirou! Favor realizar novo login.';
            break;
        case 26:
            $mensagem = $_SESSION['msg_lim_chef'];
            break;
        case 27:
            $mensagem = "Registrou início intervalo (Almoço)";
            break;
        case 28:
            $mensagem = "Registrou fim intervalo (Almoço)";
            break;
        case 29:
            $mensagem = "Registrou início intervalo (Almoço)";
            break;
        default:
            break;
    }

    if (!empty($mensagem))
    {
        $mensagem = getMensagemErroHTML($mensagem, $nivel);
    }

    return $mensagem;

}

function getErro($msg)
{
    mensagem($msg);

}

function mensagemExtra($mensagem = '', $tipo = 'notice', $width = '450px', $height = 'auto')
{
    // dotted | dashed | solid | double | groove | ridge | inset | outset
    if ($mensagem !== '')
    {
        ?>
        <style>
            .msgExtraBorda { border:dotted 0.5em #83bd82;width:100%;height:auto;padding:10px 10px 10px 10px; }
        </style>
        <div style='width:<?= $width; ?>;height:<?= $height; ?>;margin: 0 auto;padding:5px 5px 5px 5px;'>
            <div class='ui-widget-header msgExtraBorda'>
                <div style='float:left;padding:0px 4px 0px 0px;width:20px;height:20px;'><img src='<?= _DIR_IMAGEM_ . $tipo; ?>.png'></div>
                <div style='float:left;text-align:justify;padding:0px 0px 0px 4px;'><?= $mensagem; ?></div>
            </div>
        </div>
        <?php
    }

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : close                                        |
 * | @description : Fecha a janela atual                         |
 * |                                                             |
 * | @param  : void                                              |
 * | @return : void                                              |
 * | @usage  : close();                                          |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function close()
{
    echo '<html><body><script>window.close();</script></body></html>';

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : voltar                                       |
 * | @description : Volta à página anterior                      |
 * |                                                             |
 * | @param  : <integer> - $x                                    |
 * |                       qtd de páginas para retornar          |
 * | @param  : <string>  - $url                                  |
 * |                       pagina de destino                     |
 * | @return : void                                              |
 * | @usage  : voltar( 1,'pagina.php' );                         |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function voltar($x = 1, $url = '')
{
    ##
    # Se não houver URL (destino), apenas retorna páginas
    # Se $x igual a zero ($x => páginas para retornar), só realiza o redirecionamento
    #

	if (empty($url))
    {
        echo '<script>window.history.go(-', $x, ');</script>';
    }
    elseif ($x == 0)
    {
        echo '<script>location.replace(\'', $url, '\');</script>';
    }
    else
    {
        echo '<script>window.history.go(-', $x, ');location.replace(\'', $url, '\');</script>';
    }
    exit();

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : reloadOpener                                 |
 * | @description : Atualiza janela de origem                    |
 * |                                                             |
 * | @param  : void                                              |
 * | @return : void                                              |
 * | @usage  : reloadOpener();                                   |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function reloadOpener()
{
    echo '<html><body><script>window.opener.location.reload();</script></body></html>';

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : replaceLink                                  |
 * | @description : Substitui a página atual pela indicada       |
 * |                                                             |
 * | @param  : <string> - $link                                  |
 * |                      pagina para substituir a atual         |
 * | @return : void                                              |
 * | @usage  : replaceLink('pagina.php');                        |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function replaceLink($link = '', $target = '')
{
    if ($link != '')
    {
        echo '<script language=\'JavaScript\'>', ($target == '' ? 'window' : 'parent'), '.location.replace(\'', $link, '\'); </script>';
        DataBase::fechaConexao();
        exit();
    }

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : redirLink                                    |
 * | @description : Página para qual será direcionada            |
 * |                                                             |
 * | @param  : <string> - $link                                  |
 * |                      pagina a direcionar                    |
 * | @return : void                                              |
 * | @usage  : redirLink('pagina.php');                          |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function redirLink($link = '')
{
    if ($link != '')
    {
        echo '<script language=\'JavaScript\'> window.location.href = \'', $link, '\'; </script>';
        exit();
    }

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : reloadSite                                   |
 * | @description : Atualiza toda a Página                       |
 * |                                                             |
 * | @param  : void                                              |
 * | @return : void                                              |
 * | @usage  : reloadSite();                                     |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function reloadSite()
{
    echo '<script language=\'JavaScript\'> parent.location.reload(); </script>';

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : titulo_in_pagina                             |
 * | @description : Título interno da página                     |
 * |                                                             |
 * | @param  : <string>  - $str                                  |
 * |                       titulo na página                      |
 * | @param  : <boolean> - $nao_print                            |
 * |                       se true retorna o texto               |
 * | @param  : <string>  - $largura                              |
 * |                       largura da area do texto              |
 * | @return : <string>  - se nao_print=false, retorna o texto   |
 * |           void        se nao_print=true, imprime            |
 * | @usage  : titulo_in_pagina( "EXIBE O TEXTO", true, "800" ); |
 * |           ou                                                |
 * |           $txt = titulo_in_pagina(                          |
 * |                    "EXIBE O TEXTO", true, "800" );          |
 * |           echo $txt;                                       |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function titulo_in_pagina($str = '', $nao_print = false, $largura = '800')
{
    if (!empty($str))
    {
        $texto = '<center><div align=center style=\'font-size: 13px; font-weight: bold; text-align: left; width: ' . $largura . '; border-bottom: 1px solid #006C36;\'>' . $str . '</div></center>';
        if ($nao_print == true)
        {
            return $texto;
        }
        else
        {
            echo $texto;
        }
    }

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : setTituloApl                                 |
 * | @description : Exibe o nome aplicação, data, matrícula, e   |
 * |                nome do usuario no topo da janela            |
 * |                                                             |
 * | @param  : <string> - $sMatricula                            |
 * |                      matricula SIAPE                        |
 * | @param  : <string> - $sNome                                 |
 * |                      nome do usuário                        |
 * | @return : void                                              |
 * | @usage  : setTituloApl( '0999999', 'NOME DO SERVIDOR' );    |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function setTituloApl($sMatricula = '', $sNome = '')
{
    echo '
	<script>
		top.document.title = \'' . _SISTEMA_SIGLA_ . ' | ' . date('d/m/Y') . ' | Usuário: ' . $sMatricula . ' ' . $sNome . '\';
		document.status = \'\';
	</script>';

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : converte_charset                             |
 * | @description : Converte o texto de UTF8 para ISO88591       |
 * |                e vice-versa                                 |
 * | @param  : <string>   - $v                                   |
 * |                        texto a converter                    |
 * | @param  : [<string>] - $de                                  |
 * |                        tipo caracter origem                 |
 * | @param  : [<string>] - $para                                |
 * |                        tipo caracter destino                |
 * | @return : <string>   - texto convertido para o novo tipo    |
 * | @usage  : converte_charset('ÁGUA','UTF-8','ISO-8859-1');    |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function converte_charset($v, $de = 'UTF-8', $para = 'ISO-8859-1')
{
    if (mb_detect_encoding($v, "auto") == 'UTF-8')
    {
        return utf8_encode($v);
    }
    else
    {
        return utf8_iso88591($v);
    }
    return $v;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : utf8_iso88591                                |
 * | @description : Converte o texto de UTF8 para ISO88591       |
 * |                                                             |
 * | @param  : [<string>] - $v                                   |
 * |                        texto a converter                    |
 * | @param  : <string>   - $de                                  |
 * |                        tipo caracter origem                 |
 * | @param  : <string>   - $para                                |
 * |                        tipo caracter destino                |
 * | @return : <string>   - texto convertido para o novo tipo    |
 * | @usage  : utf8_iso88591( 'ÁGUA', 'UTF-8', 'ISO-8859-1' );   |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function utf8_iso88591($v, $de = 'UTF-8', $para = 'ISO-8859-1')
{
    return htmlspecialchars(mb_convert_encoding($v, $de, $para));

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : iso88591_utf8                                |
 * | @description : Converte o texto de ISO88591 para UTF8       |
 * |                                                             |
 * | @param  : [<string>] - $v                                   |
 * |                        texto a converter                    |
 * | @param  : <string>   - $de                                  |
 * |                        tipo caracter origem                 |
 * | @param  : <string>   - $para                                |
 * |                        tipo caracter destino                |
 * | @return : <string>   - texto convertido para o novo tipo    |
 * | @usage  : utf8_iso88591( 'ÁGUA', 'ISO-8859-1', 'UTF-8' );   |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function iso88591_utf8($v, $de = 'ISO-8859-1', $para = 'UTF-8')
{
    return htmlspecialchars(mb_convert_encoding($v, $de, $para));

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : retira_acentos                               |
 * | @description : Troca os caracteres acentuados por seu       |
 * |                correpondente sem acento                     |
 * |                                                             |
 * | @param  : [<string>] - $string                              |
 * |                        texto a converter                    |
 * | @return : <string>   - texto com os caracteres convertidos  |
 * | @usage  : retira_acentos( 'ÁGUA' );                         |
 * |          // retorna 'AGUA'                                  |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function retira_acentos($string)
{
    $letras = array('á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'â' => 'a', 'ê' => 'e', 'ô' => 'o', 'î' => 'i', 'û' => 'u', 'ã' => 'a', 'õ' => 'o', 'ç' => 'c', 'à' => 'a', 'è' => 'e', 'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Â' => 'A', 'Ê' => 'E', 'Ô' => 'O', 'Î' => 'I', 'Û' => 'U', 'Ã' => 'A', 'Õ' => 'O', 'Ç' => 'C', 'À' => 'A', 'È' => 'E');
    return strtr($string, $letras);

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : ajustar_acentos                              |
 * | @description : ajusta os caracteres acentuados              |
 * |                                                             |
 * | @param  : [<string>] - $string                              |
 * |                        texto a converter                    |
 * | @return : <string>   - texto com os caracteres ajustadas    |
 * | @usage  : retira_acentos( 'chão' ); //chão                 |
 * |          // retorna 'chão'                                  |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function ajustar_acentos($string)
{
    $letras = array('Ã‚Â°' => 'º', 'ÃƒÂ­' => 'í', 'ÃƒÂª' => 'ê', 'Âº' => 'º', 'Â°' => 'º', 'Ã?' => 'Á', 'Ã€' => 'À', 'Ãƒ' => 'Ã', 'Ã‚' => 'Â', 'Ã¡' => 'á', 'Ã ' => 'à', 'Ã£' => 'ã', 'Ã¢' => 'â', 'Ã‰' => 'É', 'Ãˆ' => 'È', 'ÃŠ' => 'Ê', 'Ã©' => 'é', 'Ã¨' => 'è', 'Ãª' => 'ê', 'Ã?' => 'Í', 'ÃŒ' => 'Ì', 'Ã­' => 'í', 'Ã¬' => 'ì', 'Ã“' => 'Ó', 'Ã’' => 'Ò', 'Ã•' => 'Õ', 'Ã”' => 'Ô', 'Ã³' => 'ó', 'Ã²' => 'ò', 'Ãµ' => 'õ', 'Ã´' => 'ô', 'Ãš' => 'Ú', 'Ã™' => 'Ù', 'Ã›' => 'Û', 'Ãº' => 'ú', 'Ã‡' => 'Ç', 'Ã§' => 'ç', 'Ã' => 'í', '&ccedil;&atilde;' => 'çã', '&iacute;' => 'í', '&aacute;' => 'á', '&atilde;' => 'ã', '&Aacute;' => 'Á', '&ccedil;' => 'ç', '&eacute;' => 'é');
    return strtr($string, $letras);

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : preparaTextArea                              |
 * | @description : Se o texto for para exibição como html       |
 * |                retira os caracteres de tabulação e quebra   |
 * |                de linha, se não só retira chr(10), chr(12)  |
 * |                e chr(13)                                    |
 * |                                                             |
 * | @param  : [<string>]  $string  texto a tratar               |
 * | @param  : <string>    $string  tipo de texto para exibir    |
 * | @return : <string>  texto com os caracteres ajustados       |
 * | @usage  : preparaTextArea( '\n\tTeste', 'para_html' );      |
 * |          // retorna '<br>&nbsp;&nbsp;Teste'                 |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function preparaTextArea($string, $tipo_exibicao = 'para_html')
{
    $txt_troca = ' ';
    if ($tipo_exibicao != 'para_mysql')
    {
        switch ($tipo_exibicao)
        {
            case 'para_html':
            case 'para_textarea':
                $char      = array('\\t' => '&nbsp;&nbsp;', '\t' => '&nbsp;&nbsp;', '\\' => '', '\\n' => '<br>', '\n' => '<br>', "'" => "`", '"' => "`");
                $txt_troca = ($tipo_exibicao != 'para_html' ? '\n' : $txt_troca);
                break;

            case 'para_alert':
                $char = array('\\' => '', '\\n' => '\\n', '\n' => '\\n', "'" => "`", '"' => "`");
                break;

            default:
                $char = array('\\' => '', "'" => "`", '"' => "`");
                break;
        }

        $string = strtr($string, $char);
        $string = strtr($string, $char);
        //$string = strtr($string, chr(9), $char_troca);
        $string = strtr($string, chr(10), $char_troca);
        $string = strtr($string, chr(12), $char_troca);
        $string = strtr($string, chr(13), $char_troca);
    }
    else
    {
        $char   = array('\\t' => '', '\t' => '', '\\n' => '', '\n' => '', '\\' => '', "'" => "`", '"' => "`");
        $string = strtr($string, $char);
        $string = strtr($string, $char);
    }

    return $string;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : verifica_se_mes_homologado                   |
 * | @description : Indica se a frequência                       |
 * |                no mês e ano informado foi homologada        |
 * |                                                             |
 * | @param  : <string> - $sMatricula                            |
 * |                      matrícula para verificacao             |
 * | @param  : <string> - $compet                                |
 * |                      mes e ano desejado                     |
 * | @return : <string> - Se a frequencia foi homologada ou não  |
 * | @usage  : verifica_se_mes_homologado('1234567','201009');   |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function verifica_se_mes_homologado($sMatricula = '', $compet = '')
{
    // situacao da frequência
    $status = '<font color=red><b>NÃO HOMOLOGADO</b></font>';

    // instancia a class DataBase
    $oDBase = new DataBase('PDO');


    $sMatricula = getNovaMatriculaBySiape($sMatricula);

    // verifica se a frequência foi homologada
    $oDBase->query("
        SELECT
            IF(IFNULL(homologados.homologado,'N') NOT IN ('V','S'),'N','S') AS homologado
        FROM
            homologados
        WHERE
            homologados.mat_siape = :siape
            AND homologados.compet = :compet
        ", array(
            array(":siape",  $sMatricula, PDO::PARAM_STR),
            array(":compet", $compet, PDO::PARAM_STR),
        )
    );

    $freq             = $oDBase->fetch_object();
    $freq->homologado = ($freq->homologado == '' ? 'XX' : $freq->homologado); // para evitar erro de parametro vazio
    if ($freq->homologado == "S")
    {
        $status = 'HOMOLOGADO';
    }
    return $status;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : registra_homologacao                         |
 * | @description : Registra a homologação da frequência do      |
 * |                 (chefia) ou verificação da homologação (SOGP)|
 * |                                                             |
 * | @param  : <string> - $siape                                 |
 * |                      matrícula homologada                   |
 * | @param  : <string> - $mes                                   |
 * |                      mes homologado                         |
 * | @param  : <string> - $ano                                   |
 * |                      ano do mes homologado                  |
 * | @return : void                                              |
 * | @usage  : registra_homologacao('1234567','09','2010');      |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase()    (class_database.php)            |
 * +-------------------------------------------------------------+
 * */
function registra_homologacao($siape = '', $mes = '', $ano = '')
{
    // competência
    $compet = $ano . $mes;

    // instancia a class DataBase
    $oDBase = new DataBase('PDO');

    // verifica se há registro para a matrícula e competência
    $oDBase->query("SELECT mat_siape FROM homologados WHERE mat_siape = :siape AND compet = :compet ", array(
        array(":siape", $siape, PDO::PARAM_STR),
        array(":compet", $compet, PDO::PARAM_STR),
    ));

    $rowsh = $oDBase->num_rows();

    if ($rowsh == 0)
    {
        // inclui homologação
        $oDBase->query("INSERT INTO homologados SET compet = :compet, mat_siape = :siape, homologado = :homologado, homologado_siape = :homologado_siape, homologado_data = NOW() ", array(
            array(":siape", $siape, PDO::PARAM_STR),
            array(":compet", $compet, PDO::PARAM_STR),
            array(":homologado", "S", PDO::PARAM_STR),
            array(":homologado_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
        ));
    }
    else
    {
        // atualiza homologados
        $oDBase->query("UPDATE homologados SET homologado = :homologado, homologado_siape = :homologado_siape, homologado_data = NOW() WHERE compet = :compet AND mat_siape = :siape ", array(
            array(":siape", $siape, PDO::PARAM_STR),
            array(":compet", $compet, PDO::PARAM_STR),
            array(":homologado", "S", PDO::PARAM_STR),
            array(":homologado_siape", $_SESSION['sMatricula'], PDO::PARAM_STR),
        ));
    }

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : dataLimiteReHomologacao                      |
 * | @description : Estabelece a data limite para a realização   |
 * |                de nova homologação da frequência            |
 * |                - Data sempre em dias úteis                  |
 * |                                                             |
 * | @param  : <string> - $dia                                   |
 * |                      data da devolução dahomologação        |
 * | @param  : <string> - $lotacao                               |
 * |                      unidade em que se encontra o servidor  |
 * | @return : data limite para a nova homologação               |
 * | @usage  : dataLimiteReHomologacao('19-10-2013,'09021130');  |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : verifica_se_feriado()   (functions.php)       |
 * +-------------------------------------------------------------+
 * */
function dataLimiteReHomologacao($dia, $lotacao)
{
    $dias_no_mes = numero_dias_do_mes(dataMes($dia), dataAno($dia));

    $dia_da_semana = date('w', mktime(0, 0, 0, dataMes($dia), dataDia($dia), dataAno($dia)));

    switch ($dia_da_semana)
    {
        case 0: $somar_dias = 1;
            break; // Domingo: MySQL=1; PHP=0
        case 5: $somar_dias = 3;
            break; // Sexta..: MySQL=6; PHP=5
        case 6: $somar_dias = 2;
            break; // Sábado.: MySQL=7; PHP=6
        default:
            $somar_dias = 1;
            break;
    }

    if ($dias_no_mes == dataDia($dia))
    {
        $somar_dias = 0;
    }

    $dia = soma_dias_a_data($dia, $somar_dias);

    for ($ind = 1; $ind < 3; $ind++)
    {
        if (verifica_se_feriado($dia, $lotacao) == 'S' || verifica_se_fimdesemana($dia) == 'S')
        {
            $dia = dataLimiteReHomologacao($dia, $lotacao);
        }
        else
        {
            break;
        }
    }
    return $dia;

}


/**
 *
 * @param string $siape
 * @param string $compet
 * @return obejct Dados de homologação
 */
function getDadosHomologacao( $siape, $compet )
{
    $mat = getNovaMatriculaBySiape($siape);

    $oDBase = new DataBase();

    $oDBase->query( "
    SELECT
        homologados.compet,
        homologados.mat_siape,
        servativ.nome_serv,
        IFNULL(homologados.homologado_siape,'')          AS homologado_siape,
        IFNULL(servativ2.nome_serv,'')                   AS homologado_nome,
        IFNULL(homologados.homologado_data,'0000-00-00') AS homologado_data
    FROM
        homologados
    LEFT JOIN
        servativ ON homologados.mat_siape = servativ.mat_siape
    LEFT JOIN
        servativ AS servativ2 ON homologados.homologado_siape = servativ2.mat_siape
    WHERE
        homologados.compet = :compet
        AND homologados.mat_siape = :siape
    ", array(
        array( ':compet', $compet, PDO::PARAM_STR ),
        array( ':siape',  $mat,    PDO::PARAM_STR ),
    ));

    return $oDBase->fetch_object();
}


/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : trata_aspas                                  |
 * | @description : Troca aspas simples e duplas                 |
 * |                 por 'acento grave'                          |
 * |                                                             |
 * | @param  : [<string>] - $texto                               |
 * |                        string/texto para tratamento         |
 * | @return : <string>   - String/texto tratado                 |
 * | @usage  : trata_aspas( "D'Cadastro" );                      |
 * |           retorna "D`Castro"                                |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function trata_aspas($texto = '')
{
    return strtr($texto, array("'" => "`", '"' => "`"));

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : existeDBTabela                               |
 * | @description : Verifica se existe a tabela informada        |
 * |                no banco de dados indicado                   |
 * |                                                             |
 * | @param  : [<string>] - $tb_nome                             |
 * |                        nome da tabela informada             |
 * | @param  : [<string>] - $db_nome                             |
 * |                        banco de dados para pesquisa         |
 * | @return : <boolean>  - Verdadeiro se existir a tabela no BD |
 * | @usage  : existeDBTabela('cadastro','siape');               |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function existeDBTabela($tb_nome, $db_nome = NULL)
{
    $existe = false;

    $db_nome = getenv('MYSQL_DATABASE');

    // instancia o banco de dados
    $oDBase = new DataBase('PDO');

    // Leitura do banco de dados
    $oDBase->query("
        SELECT
            table_name
        FROM
            INFORMATION_SCHEMA.TABLES
        WHERE
            table_schema = :db_nome
            AND table_name = :tb_nome
        ",
        array(
            array( ':db_nome', $db_nome, PDO::PARAM_STR ),
            array( ':tb_nome', $tb_nome, PDO::PARAM_STR ),
        ));

    if ($oDBase->num_rows() > 0)
    {
        $existe = true;
    }

    return $existe;
}


/**  @Function
 * +-------------------------------------------------------------+
* | @function    : CreateTablePonto                             |
* | @description : Verifica se existe a tabela e cria se não há |
* |                                                             |
* | @param  : [<string>] - $tb_nome                             |
* |                        nome da tabela informada             |
* | @param  : [<string>] - $db_nome                             |
* |                        banco de dados para pesquisa         |
* | @return : <boolean>  - Verdadeiro se existir a tabela no BD |
* | @usage  : existeDBTabela('cadastro','siape');               |
* | @author : Edinalvo Rosa                                     |
* |                                                             |
* | @dependence : void                                          |
* +-------------------------------------------------------------+
*
*/
function CreateTablePonto($tb_nome, $db_nome = NULL)

{
    $db_nome = getenv('MYSQL_DATABASE');

    // instancia o banco de dados
    $oDBase = new DataBase('PDO');

    // cria a tabela de registro da frequencia
    $table_new = $tb_nome;
    $table_old = "ponto122019";

    $sql = "CREATE TABLE IF NOT EXISTS ".$table_new." LIKE ".$table_old;
    $oDBase->query( $sql );

    // cria a tabela de registro da frequencia auxiliar (multiocorrência)

    $sql = "CREATE TABLE IF NOT EXISTS ".$table_new."_auxiliar LIKE ".$table_old."_auxiliar";
    $oDBase->query( $sql );

    // cria a tabela de registro do historico da frequencia
    $table_new = "hist" . $tb_nome;
    $table_old = "histponto122019";

    $sql = "CREATE TABLE IF NOT EXISTS ".$table_new." LIKE ".$table_old;
    $oDBase->query( $sql );

    // cria a tabela de registro da frequencia auxiliar (multiocorrência)
    $sql = "CREATE TABLE IF NOT EXISTS ".$table_new."_auxiliar LIKE ".$table_old."_auxiliar";
    $oDBase->query( $sql );

}


 /**  @Class
 * +-------------------------------------------------------------+
 * | @class       : trata_datasys                                |
 * | @description : Trata a data para informar as competencias   |
 * |                                                             |
 * | @param  : void                                              |
 * | @return : void                                              |
 * | @usage  : $oData = new trata_datasys();                     |
 * |          $oData->getAno()                                   |
 * | $method : trata_datasys()      Construtor                   |
 * | $method : getAno()             Pega o ano atual             |
 * | $method : getMes()             Pega o mês atual             |
 * | $method : getDia()             Pega o dia atual             |
 * | $method : getCompet()          Pega a competência atual     |
 * | $method : getAnoAnterior()     Pega o ano anterior          |
 * | $method : getMesAnterior()     Pega o mês anterior          |
 * | $method : getCompetAnterior()  Pega a competência anterior  |
 * | $method : getAnoSeguinte()     Pega o ano seguinte          |
 * | $method : getMesSeguinte()     Pega o mês seguinte          |
 * | $method : getCompetSeguinte()  Pega a competência seguinte  |
 * |                                                             |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
class trata_datasys
{

    //data hoje
    private $mes; // mês sem o zero à esquerda
    private $sysData; // data do sistema
    private $sysano; // ano do sistema
    private $sysmes; // mes do sistema
    private $sysdia; // dia do sistema
    private $syscomp; // competencia (mmaaaa)

    // pega a data do sistema
    // define a competência atual (mmaaaa) */

    function trata_datasys()
    {
        $this->mes     = date('n');
        $this->sysano  = date('Y');
        $this->sysmes  = date('m');
        $this->sysdia  = date('d');
        $this->sysData = date('d/m/Y');
        $this->syscomp = $this->sysmes . $this->sysano;

    }

    function getAno()
    {
        return $this->sysano;

    }

    /* pega o ano atual */

    function setAno($value)
    {
        $this->sysano = $value;

    }

    /* define o ano atual */

    function getMes()
    {
        return $this->sysmes;

    }

    /* pega o mês atual */

    function setMes($value)
    {
        $this->sysmes = $value;

    }

    /* define o mês atual */

    function getMesNumero()
    {
        $mes = $this->sysmes;
        settype($mes, 'integer');
        return $mes;

    }

    /* pega o mês atual sem o zero à esquerda */

    function getDia()
    {
        return $this->sysdia;

    }

    /* pega o dia atual */

    function getCompet()
    {
        return $this->syscomp;

    }

    /* pega a competência atual (mmaaaa) */

    function getData()
    {
        return $this->sysData;

    }

    /* pega a data atual (dd/mm/aaaa) */

    function getAnoAnterior()
    {
        return ($this->getMesNumero() == 1 ? ($this->getAno() - 1) : $this->getAno());

    }

    function getMesAnterior()
    {
        return ($this->getMesNumero() == 1 ? '12' : substr('00' . ($this->getMesNumero() - 1), -2));

    }

    function getCompetAnterior()
    {
        return ($this->getMesAnterior() . $this->getAnoAnterior());

    }

    function getAnoSeguinte()
    {
        return ($this->getMesNumero() == 12 ? ($this->getAno() + 1) : $this->getAno());

    }

    function getMesSeguinte()
    {
        return ($this->getMesNumero() == 12 ? '01' : substr('00' . ($this->getMesNumero() + 1), -2));

    }

    function getCompetSeguinte()
    {
        return ($this->getMesSeguinte() . $this->getAnoSeguinte());

    }

    function getAnoCompensado()
    {
        return ($this->getMesNumero() <= 2 ? ($this->getAno() - 1) : $this->getAno());

    }

    function getMesCompensado()
    {
        return ($this->getMesNumero() == 1 ? '11' : ($this->getMesNumero() == 2 ? '12' : substr('00' . ($this->getMesNumero() - 2), -2)));

    }

    function getCompetCompensado()
    {
        return ($this->getMesCompensado() . $this->getAnoCompensado());

    }

    function getAnoHomologacao()
    {
        return $this->getAnoAnterior();

    }

    function getMesHomologacao()
    {
        return $this->getMesAnterior();

    }

    function getCompetHomologacao()
    {
        return $this->getCompetAnterior();

    }

}

/**  @Class
 * +-------------------------------------------------------------+
 * | @class       : competencia                                  |
 * | @description : Define a competencia anterior a hoje         |
 * |                                                             |
 * | @param  : void                                              |
 * | @return : void                                              |
 * | @usage  : $oData = new competencia();                       |
 * |           $mes = $oData->mes()                              |
 * | $method : competencia()    Construtor                       |
 * |                                                             |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : trata_datasys  (class)                        |
 * +-------------------------------------------------------------+
 * */
class competencia extends trata_datasys
{

    var $ano;   // ano do sistema
    var $comp;  // mes do sistema
    var $year;  // ano do sistema (compatibilidade)
    var $mes;   // competencia (mmaaaa) - base sistema - (compatibilidade)
    var $mes2;  // mes do sistema
    var $comp2; // competencia (mmaaaa) - base sistema

    // competencia anterior

    function competencia()
    {
        // inicializa
        parent::trata_datasys();

        // ano do sistema
        $this->ano = parent::getAno();

        // mes anterior
        $this->comp = parent::getMesAnterior();

        // Ano anterior, se mes > 1 ano atual
        $this->year = parent::getAnoAnterior();

        // Competencia anterior
        $this->mes = parent::getCompetAnterior();

        // usado no regfreq8
        $this->mes2  = $this->comp;
        $this->comp2 = $this->mes;

    }

}

/**  @Class
 * +-------------------------------------------------------------+
 * | @class       : competenciaat                                |
 * | @description : Define a competencia atual                   |
 * |                                                             |
 * | @param  : void                                              |
 * | @return : void                                              |
 * | @usage  : $oData = new competenciaat();                     |
 * |          $compet = $oData->comp()                           |
 * | $method : competenciaat()    Construtor                     |
 * |                                                             |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : trata_datasys  (class)                        |
 * +-------------------------------------------------------------+
 * */
class competenciaat extends trata_datasys
{

    var $comp;

    function competenciaat()
    {
        // inicializa
        parent::trata_datasys();

        $this->comp = parent::getCompet();

    }

}

/**  @Class
 * +-------------------------------------------------------------+
 * | @class       : mensagem                                     |
 * | @description : Mensagens de erro padronizados               |
 * |                                                             |
 * | @param  : void                                              |
 * | @return : void                                              |
 * | @usage  : $oMsg = new mensagem();                           |
 * |          $oMsg->setMensagem( 1 );                           |
 * |          $oMsg->exibeMensagem();                            |
 * | $method : mensagem()        Construtor                      |
 * | $method : setMensagem()     Define o grupo de mensagens     |
 * | $method : getMensagem()     Pega uma das mensagens          |
 * | $method : setDataInicio()   Define uma data inicial         |
 * | $method : getDataInicio()   Pega a data inicial             |
 * | $method : setDataFim()      Define uma data final           |
 * | $method : getDataFim()      Pega a data final               |
 * | $method : setDestino()      Define a pagina destino         |
 * | $method : getDestino()      Pega a pagina destino           |
 * | $method : setNumMensagem()  Define quantidade de mensagens  |
 * | $method : getNumMensagem()  Pega quantidade de mensagens    |
 * |                              definidas                      |
 * | $method : exibeMensagem()   Exibe a mensagem escolhida      |
 * |                                                             |
 * | @author : Carlos Augusto                                    |
 * |           Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
class mensagem
{

    var $dt_inicio;
    var $dt_fim;
    var $destino;
    var $num_mensagem;
    var $mensagens = array();

    function mensagem()
    {
        // mensagens/avisos - grupo: mensagem2.php
        $this->setMensagem(1, 'Avaliação gravada com sucesso!');
        $this->setMensagem(2, 'Você não pode registrar frequência no SISREF por estar cedido o fixado!');
        $this->setMensagem(3, 'Servidor não encontrado!');
        $this->setMensagem(4, 'Erro de processamento por favor repita a operação!');
        $this->setMensagem(5, 'Servidor não está ativo ou inexistente!');
        $this->setMensagem(61, 'Observe o período de registro de frequência\\n(<[dt_inicio]> a <[dt_final]>)!');
        $this->setMensagem(62, 'Consta registro de ocorrência para esse servidor\\nneste dia ou já registrou a saida do expediente');
        $this->setMensagem(63, 'Frequência normal registrada com sucesso!');
        $this->setMensagem(64, 'Não existe registro de frequência para esse setor nesta competência!');
        $this->setMensagem(65, 'Frequência retificada com sucesso!');
        $this->setMensagem(66, 'Não existem setores pendentes!');
        $this->setMensagem(67, 'Horário alterado com sucesso!');
        $this->setMensagem(68, 'Não é permitido registrar entrada antes do horário de\\nfuncionamento da unidade sem autorização da chefia!\\nVocê deve registrar novamente no horário correto');
        $this->setMensagem(69, 'Não é permitido registro de ponto para ocupantes de DAS 4, 5 ou 6!');
        $this->setMensagem(610, 'Não é permitido abonar ocorrência diferente de atrasos e faltas!');
        $this->setMensagem(7, 'Não é permitido registrar saida após o horário de\nfuncionamento da unidade sem autorização da chefia!');
        $this->setMensagem(8, 'Não é permitido registrar intervalo inferior a uma hora!');
        $this->setMensagem(81, 'Você ultrapassou o limite de três horas para intervalo, será necessário repor essa diferença!');
        $this->setMensagem(9, 'Matrícula informada não consta da base de excluídos!');
        $this->setMensagem(10, 'O servidor não está em atividade!');
        $this->setMensagem(11, 'Você já registrou a saída do expediente!');
        $this->setMensagem(12, 'Servidor não encontrado!');
        $this->setMensagem(13, 'Não é permitido inserir dia de outra competência na atual!');
        $this->setMensagem(14, 'Não é possível emitir relatório de cobrança para competência anterior a 10/2009 ou posterior ao mes atual!');
        $this->setMensagem(15, '!');
        $this->setMensagem(16, '!');
        $this->setMensagem(17, 'Para ocupar função é necessário vagar primeiro!');
        $this->setMensagem(18, 'Consta histórico de exercício de funções para esse servidor!');
        $this->setMensagem(19, 'Servidor não ocupa função!');
        $this->setMensagem(20, 'Movimento de pasta realizado com sucesso!');
        $this->setMensagem(21, 'Servidor ocupa chefia, não pode ser localizado!');
        $this->setMensagem(22, 'Houve erro no processamento, refaça a operação!');
        $this->setMensagem(23, 'Só é permitido inserir dia da competência de homologação!');
        $this->setMensagem(24, 'Não é permitido consultar/alterar servidor de outro setor!');
        $this->setMensagem(25, 'Não é permitido consultar ponto de servidor de outra upag!');
        $this->setMensagem(26, 'Competência inexistente no bando de dados,\\nsolicite ao Gestor a criação da tabela para essa competência!');
        $this->setMensagem(27, 'Só é permitido incluir ou alterar ocorrência\\nna competência da homologação ou atual!');
        $this->setMensagem(28, 'Codigo de ocorrência já existe!');
        $this->setMensagem(29, 'Só é permitido incluir ou alterar ocorrência\\nna competência da homologação ou atual!');
        $this->setMensagem(30, 'Só é permitido incluir ocorrência em competência anterior à homologação!');
        $this->setMensagem(31, 'Já consta registro de encerramento de expediente!');
        $this->setMensagem(32, 'Não é permitido apresentar justificativa para meses anteriores!');
        $this->setMensagem(33, 'Não é permitido apresentar justificativa para essa ocorrência!');
        $this->setMensagem(34, 'A data solicitada é dia útil!');
        $this->setMensagem(35, 'Não é permitido alterar dados de servidor de outra UPAG!');
        $this->setMensagem(36, 'Não é permitido excluir servidor de outra UPAG!');
        $this->setMensagem(37, 'Não é permitido alterar servidor de outra UPAG!');
        $this->setMensagem(38, 'Não é permitido registrar saída com retorno do intervalo\\nem branco quando tiver ocorrido início do intervalo!');
        $this->setMensagem(39, 'Não é permitido registrar retorno do intervalo com início do intervalo em branco!');
        $this->setMensagem(40, 'Não é permitido alterar ocorrência incluída por outro perfil de usuário!');
        $this->setMensagem(41, 'A matricula informada não é de servidor lotado em RH!');
        $this->setMensagem(42, 'Não é permitido lançar essa ocorrência em dia não útil!');
        $this->setMensagem(43, 'Não é permitido consultar informações de servidor de outra UPAG!');

        // mensagens/avisos - grupo: mensagem.php
        $this->setMensagem(50, 'Você não está autorizado a registrar frequência em dia não útil!');

        // mensagens/avisos - grupo: mensagem.php
        $this->setMensagem(100, 'Ano inválidao!');
        $this->setMensagem(101, 'Mês inválidao!');
        $this->setMensagem(102, 'Competência inválida!');

        // configuração padrão
        $this->setDataInicio('');
        $this->setDataFim('');
        $this->setDestino();
        $this->setNumMensagem(0);
        //

    }

    function setMensagem($indice = 0, $texto = '')
    {
        $this->mensagens[$indice] = $texto;

    }

    function getMensagem($indice = 0)
    {
        return $this->mensagens[$indice];

    }

    function setDataInicio($zinicio = '')
    {
        $this->dt_inicio = $zinicio;

    }

    function getDataInicio()
    {
        return $this->dt_inicio;

    }

    function setDataFim($zfinal = '')
    {
        $this->dt_fim = $zfinal;

    }

    function getDataFim()
    {
        return $this->dt_fim;

    }

    function setDestino($destino = '')
    {
        $this->destino = ($destino == '' ? pagina_de_origem() : $destino );

    }

    function getDestino()
    {
        return $this->destino;

    }

    function setNumMensagem($modo = 0)
    {
        $this->num_mensagem = $modo;

    }

    function getNumMensagem()
    {
        return $this->num_mensagem;

    }

    function preparaData($data = '')
    {
        if ($data != '')
        {
            $data = databarra($data);
        }
        return $data;

    }

    function exibeMensagem($nummsg = 0)
    {
        $ind = ($nummsg == 0 ? $this->getNumMensagem() : $nummsg);
        if (substr($ind, 0, 1) == '6' && $this->getDataInicio() != '')
        {
            $dt_inicio = $this->preparaData($this->getDataInicio());
            $dt_fim    = $this->preparaData($this->getDataFim());
            $texto     = $this->getMensagem($ind);
            $texto     = strtr($texto, array('<[dt_inicio]>' => $dt_inicio));
            $texto     = strtr($texto, array('<[dt_final]>' => $dt_fim));
            $this->setMensagem($ind, $texto);
        }
        //
        if ($ind != 0)
        {
            mensagem($this->getMensagem($ind), $this->getDestino());
        }

    }

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : select_permissoes                            |
 * | @description : Carrega os nomes das variaveis de seção com  |
 * |                as permissões                                |
 * |                                                             |
 * | @param  : void                                              |
 * | @return : Array com as permissões                           |
 * | @usage  : select_permissoes()                               |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase  (class)                             |
 * +-------------------------------------------------------------+
 * */
function select_permissoes()
{
    $mod = array();

    // instancia o banco de dados
    $oDBase = new DataBase('PDO');

    $oDBase->query("
    SELECT cod, modulos, permissao, padrao, varsession
        FROM usuarios_permissoes
            WHERE ordem <> '9999'
                ORDER BY ordem
    ");

    while ($oUsuario = $oDBase->fetch_object())
    {
        $mod[] = array('modulos' => $oUsuario->modulos, 'permissao' => $oUsuario->permissao, 'cod' => $oUsuario->cod, 'padrao' => $oUsuario->padrao, 'varsession' => $oUsuario->varsession);
    }

    return $mod;
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : verifica_permissao                           |
 * | @description : Verifica se o usuário tem permissão          |
 * |                de uso do módulo                             |
 * |                                                             |
 * | @param  : <string> - $tipo                                  |
 * |                      tipo da permissão                      |
 * | @param  : <string> - $destino                               |
 * |                      destino caso não tenha a permissão     |
 * | @return : void                                              |
 * | @usage  : verifica_permissao('sRH','pagina.php');           |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function verifica_permissao($tipo = '', $destino = 'principal.php')
{
    @session_start();
    $autorizado = false;
    $logado     = $_SESSION['logado'];

    if (isset($_SESSION['logado']) && strtoupper($_SESSION['logado']) == 'SIM')
    {
        // instancia o banco de dados
        $oDBase = new DataBase('PDO');

        $oDBase->query('SELECT grupo, teste FROM usuarios_grupos WHERE grupo = :tipo ', array(
            array(':tipo', $tipo, PDO::PARAM_STR)
        ));

        $oTeste = $oDBase->fetch_object();
        $teste  = $oTeste->teste;

        if (!empty($teste))
        {
            eval("\$autorizado = ($teste);");
        }

        $_SESSION['autorizacao'] = ($autorizado == true ? 'S' : 'N');

        if ($autorizado == false)
        {
            replaceLink('acessonegado.php?destino=principal_abertura.php');
        }
    }
    else
    {
        $modulo_ativo       = $_SESSION['sModuloPrincipalAcionado'];
        $destino            = (empty($modulo_ativo) ? 'entrada.php' : $destino);
        destroi_sessao();
        $_REQUEST['modo']   = ($destino == 'entrada.php' ? 1 : 0);
        $_REQUEST['modulo'] = $modulo_ativo;
        replaceLink($destino . '?logar=sim&modulo_ativo=' . $modulo_ativo);
    }

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : trataPermissoes                              |
 * | @description : Trata as permissão                           |
 * |                                                             |
 * | @param  : <array> - $aAcessos                               |
 * |                     Permissões                              |
 * | @return : string de permissões                              |
 * | @usage  : trataPermissoes( $aAcessos );                     |
 * | @author :                                                   |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * |    No form o usuário dá as permissões para os usuários do   |
 * | sistema. Colocamos um C[] com value 160 e status hidden.    |
 * |    Isso evita que se não for checado o último checkbox, não |
 * | gere um erro de não definição de variável.                  |
 * |                                                             |
 * |    A rotina (confirmausuario.php) recebe os checks do outro |
 * | form em forma de vetor, extrai-se os seus keys() e jogamos  |
 * | em um vetor auxiliar. Depois, lemos o vetor e completamos   |
 * | os valores ocultos para saber se estao checados ou não.     |
 * |                                                             |
 * | Por exemplo:                                                |
 * |    Vamos supor que os valores checados foram 01|02|05|06,   |
 * | então o vetor recebido terá string > 01020506. O algoritmo  |
 * | pega e faz uma comparação dos elementos extraidos:          |
 * |  - Se key(atual) > key(anterior) entao completa até chegar  |
 * |    ao valor atual senao deixa como está.                    |
 * |  - No fim, teremos uma string com todas as posições e seus  |
 * |    respectivos flags (SSNNSSNNNNN....) até a última posição.|
 * |    Aí, gravamos no banco para depois recuperar na hora do   |
 * |    log do usuário.                                          |
 * |                                                             |
 * +-------------------------------------------------------------+
 * */
function trataPermissoes($aAcessos = '')
{
    $b = '';
    if (isset($aAcessos))
    {
        // laco
        for ($i = 0; $i <= count($aAcessos) - 1; $i++)
        {
            $a = "key($aAcessos[$i])";
            $b = $b . substr($a, 4, 2);
        }
        //monta a tripa
        $j    = 0;
        $auxa = 1;
        for ($i = 0; $i <= 106; $i = $i + 2)
        {
            $j   = $j++;
            $aux = substr($b, $i, 2);
            for ($m = $auxa; $m <= $aux; $m = $m + 1)
            {
                $trip[$m] = ($m < $aux ? 'N' : 'S');
                $auxa     = $auxa + 1;
            }
        } // fim do i

        $auxf = '';
        for ($l = 1; $l <= 106; $l = $l + 1)
        {
            $auxf = $auxf . $trip[$l];
        }
    }
    return $auxf;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : destroi_sessao                               |
 * | @description : Elimina a sessão atual/existente             |
 * |                                                             |
 * | @param  : void                                              |
 * | @return : void                                              |
 * | @usage  : destroi_sessao();                                 |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function destroi_sessao()
{
    // elimina resquicios da sessao anterior
    @session_unset();
    // Destroi a sessao
    unset($_SESSION);
    $_SESSION['logado'] = '';
    @session_destroy();
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : soma_dias_a_data                             |
 * | @description : Calcula data acrescida de dias               |
 * |                                                             |
 * | @param  : <string> - $date                                  |
 * |                      data a acrescentar dias                |
 * | @param  : <string> - $days                                  |
 * |                      dias a acrescentar                     |
 * | @return : <string> - Data acrescida de dias                 |
 * | @usage  : soma_dias_a_data('2010-01-01',10);                |
 * |           // retorna '2010-01-11'                           |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function soma_dias_a_data($date, $days)
{
    $datatrans = data2arrayBR($date);
    $newdate   = date('Y-m-d', mktime(0, 0, 0, $datatrans[1], $datatrans[0] + $days, $datatrans[2]));
    return $newdate;

}

function somadiasadata($date, $days)
{
    return soma_dias_a_data($date, $days);

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : subtrai_dias_da_data                         |
 * | @description : Calcula data subtraindo dias                 |
 * |                                                             |
 * | @param  : <string> - $date                                  |
 * |                      data a subtrair dias                   |
 * | @param  : <string> - $days                                  |
 * |                      dias a subtrair                        |
 * | @return : <string> - Data subtraida de dias                 |
 * | @usage  : subtrai_dias_da_data('2010-01-01',10);            |
 * |           // retorna '2009-12-22'                           |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function subtrai_dias_da_data($date, $days)
{
    $datatrans = data2arrayBR($date);
    $newdate   = date('Y-m-d', mktime(0, 0, 0, $datatrans[1], $datatrans[0] - $days, $datatrans[2]));
    return $newdate;

}

function subtraidiasdadata($date, $days)
{
    return subtrai_dias_da_data($date, $days);

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : pagina_de_origem                             |
 * | @description : Página que abriu a atual                     |
 * |                                                             |
 * | @param  : void                                              |
 * | @return : <string> - pagina de origem                       |
 * | @usage  : $pagina = pagina_de_origem();                     |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function pagina_de_origem()
{
    $pagina_de_origem = explode('?', $_SERVER['HTTP_REFERER']);
    $path_parts       = pathinfo($pagina_de_origem[0]);
    $pagina_de_origem = $path_parts['basename'];
    return $pagina_de_origem;

}

/**  @Function
 * +---------------------------------------------------------------------------+
 * |                                                                           |
 * | Função.: define_quem_registrou                                            |
 * | Retorna: define se foi o Servidor, Recursos Humanos ou a Chefia imediata  |
 * |          quem realizou a operação                                         |
 * | Autor..: Edinalvo Rosa                                                    |
 * | Exemplo:                                                                  |
 * |   define_quem_registrou($lot,'N');                                        |
 * |                                                                           |
 * +---------------------------------------------------------------------------+
 * */
function define_quem_registrou($lot = '', $substituto = 'N')
{
    // Chefe de setor e não tarabalha no Recursos Humanos
    $chefe_setor = ($_SESSION["sAPS"] == "S" && $_SESSION["sRH"] == "N");

    // Chefe da unidade do Recursos Humanos
    $chefe_rh    = ($_SESSION["sAPS"] == "S" && $_SESSION["sRH"] == "S" && $lot == $_SESSION['sLotacao']);

    // ???????
    $chefe_outros = ($_SESSION["sAPS"] == "S" && $_SESSION["sRH"] == "S" && $lot != $_SESSION['sLotacao'] && $substituto == 'S');

    if ($chefe_setor == true || $chefe_rh == true || $chefe_outros == true)
    {
        $idreg = "C";
    }

    $servidor_ch = ($_SESSION["sAPS"] == "S" && $_SESSION["sRH"] == "S" && $lot != $_SESSION['sLotacao'] && $substituto == 'N'); // Chefe do RH como servidor da unidade
    $servidor_rh = ($_SESSION["sAPS"] == "N" && $_SESSION["sRH"] == "S"); // Servidor do Recursos Humanos

    if ($servidor_ch == true || $servidor_rh == true)
    {
        $idreg = "R";
    }

    return $idreg;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : monta_listbox                                |
 * | @description : Monta um Listbox                             |
 * |                                                             |
 * | @param  : [<string>] - $vr                                  |
 * |                        valor selecionado                    |
 * | @param  : [<string>] - $name                                |
 * |                        codigo da gerência                   |
 * | @param  : [<string>] - $form_disabled                       |
 * |                        disabilita o campo                   |
 * | @param  : [<string>] - $tbind                               |
 * |                        sequencia do tabindex                |
 * | @param  : [<string>] - $y                                   |
 * |                        sql para selecionar                  |
 * | @param  : [<array>]  - $ufdados                             |
 * |                        dados da uf                          |
 * | @param  : <string>   - $onchange                            |
 * |                        funcao javascript para select        |
 * | @return : <string>   - select montado                       |
 * | @usage  : $idResult =                                       |
 * |             monta_listbox(                                  |
 * |               '04001000', 'selectDados',                    |
 * |               "disabled='disabled'", 1, 'SELECT..",         |
 * |               $ufdados, 'enviar()'                          |
 * |             );                                              |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function monta_listbox($vr, $name, $form_disabled, $tbind, $y, &$ufdados, $onchange = '')
{
    // instancia a conexao a base de dados
    $oDBase = new DataBase('PDO');

    // pesquisa
    $oDBase->query($y);
    $erro = $oDBase->error();
    $html = '<SELECT name="' . $name . '" onFocus="foco(this);" onBlur="no_foco(this);" size="1" class="drop" tabindex="' . $tbind . '" ' . $form_disabled . ' ' . $onchange . '>';
    $html .= '<option value="">---Selecione------</option>';
    $html .= '<option value="9">TODOS</option>';
    if (empty($erro))
    {
        while (list($cod, $descr, $sigla) = $oDBase->fetch_array())
        {
            $ufdados[] = array($cod, $descr, $sigla);
            $html      .= '<option value="' . $cod . '" ';
            if ($cod == $vr)
            {
                $html .= ' selected ';
            }
            $html .= '>' . $descr . '</option>';
        }
    }
    $html .= '</SELECT>';
    return $html;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : montaSelect                                  |
 * | @description : Monta listbox                                |
 * |                                                             |
 * | @param  : <string>  - valor para marcar como selecionado    |
 * |           <string>  - sql para seleção dos dados            |
 * |           <integer> - largura do list box                   |
 * |           <boolean> - retornará como texto ou exibirá       |
 * | @return : string com o listbox                              |
 * | @usage  : $reg = montaSelect(<codigo>,...); ou              |
 * |           echo montaSelect(<codigo>,...);                   |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase (class)                              |
 * +-------------------------------------------------------------+
 * */
function montaSelect($valor='', $sql='', $tamdescr='', $imprimir=true, $paramsql = null)
{
    // instancia a conexao a base de dados
    $oDBase = new DataBase('PDO');

    if (is_null($paramsql))
    {
        $oDBase->query($sql);
    }
    else
    {
        $oDBase->query($sql, $paramsql);
    }

    $html = "";

    while ($campo = $oDBase->fetch_array())
    {
        $html .= '<option value="' . $campo[0] . '"';
        if ($campo[0] == $valor)
        {
            $html .= ' selected';
        }

        if ($campo[0] == '-----')
        {
            $html .= '>Selecione uma opção</option>';
        }
        else
        {
            $html .= '>' . $campo[0] . ' - ' . ($tamdescr == '' ? $campo[1] : substr($campo[1], 0, $tamdescr)) . '</option>';
        }
    }

    if ($imprimir == true)
    {
        echo $html;
    }
    else
    {
        return $html;
    }
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : montaSelectOcorrencias                       |
 * | @description : Monta listbox com códigos de ocorrência      |
 * |                                                             |
 * | @param  : <string>  - valor para marcarcomo selecionado     |
 * |           <integer> - largura do list box                   |
 * |           <boolean> - retornará como texto ou exibirá       |
 * |           <boolean> - indica se a ocorrencia eh por periodo |
 * |           <boolean> - indica se a será exibida no histórico |
 * | @return : string com o listbox                              |
 * | @usage  : $reg = montaSelectOcorrencias(<codigo>,...); ou   |
 * |           echo montaSelectOcorrencias(<codigo>,...);        |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase (class)                              |
 * +-------------------------------------------------------------+
 * */
function montaSelectOcorrencias($valor = '', $tamdescr = '', $imprimir = false, $por_periodo = false, $historico = false, $onchange = '', $grupo='', $siape='', $soGrupoOcorr=null)
{
    // instancia class
    $obj = new TabOcorrenciaController();
    $html = $obj->montaSelectOcorrencias($valor, $tamdescr, $imprimir, $por_periodo, $historico, $onchange, $grupo, $siape, $soGrupoOcorr);

    return $html;
}


/*
 * @info Seleciona UPAGs cadastradas no  SISREF
 *
 * @param void
 * @return result
 * @author Edinalvo Rosa
 */
function selecionaUpags()
{
    $oDBase = new DataBase();

    $oDBase->query("
    SELECT
        tabsetor.upag, tabsetor.descricao, taborgao.denominacao, taborgao.sigla
    FROM
        servativ
    LEFT JOIN
        tabsetor ON servativ.cod_lot = tabsetor.codigo
    LEFT JOIN
        taborgao ON LEFT(tabsetor.codigo,5) = taborgao.codigo
    WHERE
        tabsetor.upag = tabsetor.codigo OR LEFT(tabsetor.upag,5) = '20113'
    GROUP BY
        tabsetor.upag
    ORDER BY
        tabsetor.descricao
    ");

    return $oDBase;
}


/*
 * @info Conta quantos setores há em um Órgão
 *
 * @param string $uorg Código do setor
 * @return result
 * @author Edinalvo Rosa
 */
function totalSetoresPorOrgao($uorg=null)
{
    $orgao = getOrgaoByUpag($uorg);

    $oDBase = new DataBase();

    $oDBase->query("
    SELECT
        tabsetor.upag, tabsetor.descricao, taborgao.denominacao, taborgao.sigla
    FROM
        servativ
    LEFT JOIN
        tabsetor ON servativ.cod_lot = tabsetor.codigo
    LEFT JOIN
        taborgao ON LEFT(tabsetor.codigo,5) = taborgao.codigo
    WHERE
        LEFT(tabsetor.upag,5) = :orgao
    /*GROUP BY
        tabsetor.upag*/
    ORDER BY
        tabsetor.descricao
    ", array(
        array(':orgao', $orgao, PDO::PARAM_STR)
    ));

    return $oDBase->num_rows();
}


/*
 * @info Monta SELECT de UPAGs cadastradas no  SISREF
 *
 * @param string  Valor para marcar como selecionado
 * @param string  Função javascript (onChange)
 *
 * @return HTML/echo
 *
 * @author Edinalvo Rosa
 */
function montaSelectUPAGs($name='select_upags', $valor='', $onchange='')
{
    $oDBase = selecionaUpags();

    ?>
    <SELECT id="<?= $name; ?>" name="<?= $name; ?>" class="form-control select2-single" title="Selecione uma opção!" <?= ($onchange == '' ? '' : 'onChange="' . $onchange . '"'); ?>>
        <?php
        while ($dados = $oDBase->fetch_object())
        {
            ?>
            <option value="<?= $dados->upag; ?>" <?= ($dados->upag == $valor ? 'selected' : ''); ?>>
                <?= $dados->upag . ' ' . $dados->descricao . (empty($dados->denominacao) ? '' : ' (' . $dados->denominacao . ')'); ?>
            </option>
            <?php
        }
        ?>
    </SELECT>
    <?php
}


function agrupa_ocorrencias($separador = '.:.')
{
    $grupo = array();

    $grupoOcorrencias = new OcorrenciasGrupos();
    $grupo['indefinida']         = implode($separador, $grupoOcorrencias->CodigosIndefinida());
    $grupo['todos_serados']      = implode($separador, $grupoOcorrencias->HorariosZerados());
    $grupo['jornada_negativa']   = implode($separador, $grupoOcorrencias->CodigosJornadaNegativa());
    $grupo['diferenca_zerada']   = implode($separador, $grupoOcorrencias->SaldoZerado());
    $grupo['diferenca_positiva'] = implode($separador, $grupoOcorrencias->SaldoPositivo());
    $grupo['diferenca_negativa'] = implode($separador, $grupoOcorrencias->SaldoNegativo());

    return $grupo;
}

/**
 * @function marca_dias_nao_util
 *
 * @info Marca feriados, sábados e domingos
 *
 * @param string $mes  Mês da data
 * @param string $ano  Ano da data
 * @param string $cmun Código do município
 * @param string $lot  Unidade de lotação
 *
 * @return array

 * @author : Edinalvo Rosa
 *
 * @dependence : void
 * */
function marca_dias_nao_util($mes, $ano = '', $cmun = '', $lot = '')
{
    $dia_nao_util = array();

    $ini = '1';
    $fim = date('t', mktime(0, 0, 0, $mes, 1, $ano));

    //sabados e domingos

    $backg = 'background: #F3F3F3';
    $fontc = 'font-weight: bold;'; //'color: red; font-weight: bold;';
    $space = '&nbsp;&nbsp;&nbsp;';


    for ($i = $ini; $i <= $fim; $i++)
    {
        $hoje = date('w', mktime(0, 0, 0, $mes, $i, $ano));
        if ($hoje == '0' || $hoje == '6')
        {
            $dia_nao_util[substr('0' . $i, -2) . '/' . substr('0' . $mes, -2) . '/' . $ano] = array($backg, $fontc, '<font color=red><b>' . ($hoje == '0' ? 'D ' : 'S ') . '</b></font>', $space, ($hoje == '0' ? 'DOMINGO' : 'SÁBADO'));
        }
    }

    // instancia a conexao a base de dados
    $oDBase = new DataBase('PDO');

    // feriados nacionais,estaduais e municipais
    $oDBase->query('SELECT `desc`, dia FROM feriados_' . $ano . ' WHERE mes = "' . $mes . '" AND (tipo = "N" OR (`tipo` = "E" AND lot LIKE "' . substr($lot, 0, 2) . '%") OR (tipo = "M" AND codmun = "' . $cmun . '")) ');

    while ($pm = $oDBase->fetch_object())
    {
        if ($dia_nao_util[$pm->dia . '/' . $mes . '/' . $ano] == '')
        {
            $dia_nao_util[$pm->dia . '/' . $mes . '/' . $ano] = array($backg, $fontc, '<font color=red><b>F </b></font>', $space, $pm->desc);
        }
    }

    // parte do dia ponto facultativo
    $oDBase->query('SELECT `desc`, dia, sigla FROM feriados_ponto_facultativo WHERE mes = "' . $mes . '" AND DATE_FORMAT(data_feriado,"%Y") = "' . $ano . '" ');

    while ($pm = $oDBase->fetch_object())
    {
        if ($dia_nao_util[$pm->dia . '/' . $mes . '/' . $ano] == '')
        {
            $dia_nao_util[$pm->dia.'/'.$mes.'/'.$ano] = array($backg,$fontc,'<font color=blue><b>'.$pm->sigla.' </b></font>',$space,$pm->desc);
        }
    }

    return $dia_nao_util;
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : verifica_se_dia_nao_util                     |
 * | @description : Verifica se o dia informado é                |
 * |                um feriado ou fim de semana                  |
 * |                                                             |
 * | @param  : <string>  - $dia                                  |
 * |                       dia (aaaa-mm-dd)                      |
 * | @param  : <string>  - $lot                                  |
 * |                       unidade de lotaçãoa                   |
 * | @return : <boolean> - true se dia não útil                  |
 * |                       false se dia útil                     |
 * | @usage  : $dutil = (                                        |
 * |             verifica_se_dia_nao_util(                       |
 * |              '2010-01-01','04001000') == true ? 'N' : 'S'   |
 * |           );                                                |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function verifica_se_dia_nao_util($dia = '', $lot = '')
{
    $resultado = false;
    if (verifica_se_fimdesemana($dia) == 'S' || verifica_se_feriado($dia, $lot) == 'S')
    {
        $resultado = true;
    }
    return $resultado;

}

/* -----------------------------------------------------------------------\
  | Função.....: eh_fimdesemana                                            |
  \----------------------------------------------------------------------- */

function eh_fimdesemana($dia = '')
{
    $resultado = false;
    // a função abaixo retorna
    // S para sábado ou domingo (dia não útil),
    // ou N para dia útil.
    if (verifica_se_fimdesemana($dia) == 'S')
    {
        $resultado = true;
    }
    return $resultado;

}

/* -----------------------------------------------------------------------\
  | Função.....: eh_ponto_facultativo                                      |
  \----------------------------------------------------------------------- */

function eh_ponto_facultativo($dia = '', $jornada_dia = '08:00')
{
    $resultado = false;
    if (verifica_se_fimdesemana($dia) == 'N')
    {
        // data informada
        $dthoje = conv_data($dia);

        // instancia banco de dados
        $oDBase = new DataBase('PDO');
        $oDBase->query('SELECT data_feriado FROM feriados_ponto_facultativo WHERE data_feriado = :dthoje ', array(array(':dthoje', $dthoje, PDO::PARAM_STR))
        );
        $nRows  = $oDBase->num_rows();
        if ($nRows > 0)
        {
            $resultado = true;
        }
    }
    return $resultado;

}

/* -----------------------------------------------------------------------\
  | Função.....: eh_quarta_feira_cinzas                                    |
  \----------------------------------------------------------------------- */

function eh_quarta_feira_cinzas($dia = '')
{
    $resultado = false;
    if (verifica_se_fimdesemana($dia) == 'N')
    {
        // data informada
        $dthoje = conv_data($dia);

        // instancia banco de dados
        $oDBase = new DataBase('PDO');
        $oDBase->query('SELECT grupo FROM feriados_ponto_facultativo WHERE data_feriado = "' . $dthoje . '" ');
        $nRows  = $oDBase->num_rows();
        $oDia   = $oDBase->fetch_object();
        if ($nRows > 0 && strtolower(substr($oDia->grupo, 0, 6)) == 'cinzas')
        {
            $resultado = true;
        }
    }
    return $resultado;

}

/* -----------------------------------------------------------------------\
  | Função.....: eh_feriado                                                |
  \----------------------------------------------------------------------- */

function eh_feriado($dia, $lot = '')
{
    $resultado = false;
    // a função abaixo retorna
    // S para feriado (dia não útil),
    // ou N para dia útil.
    if (verifica_se_feriado($dia, $lot) == 'S')
    {
        $resultado = true;
    }
    return $resultado;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : verifica_se_feriado                          |
 * | @description : Verifica se o dia informado é um feriado     |
 * |                                                             |
 * | @param  : <string> - $dia                                   |
 * |                      dia (aaaa-mm-dd ou dd/mm/aaaa)         |
 * | @param  : <string> - $lot                                   |
 * |                      unidade de lotaçãobalho                |
 * | @return : <string> - N -> para feriado, não eh dia útil     |
 * |                      S -> para dia útil                     |
 * | @usage  :                                                   |
 * |        $fer = verifica_se_feriado('01/02/2010','04001000'); |
 * |        ou                                                   |
 * |        $fer = verifica_se_feriado('2001-02-10','04001000'); |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function verifica_se_feriado($dia = '', $lot = '')
{
    // instancia o banco de dados
    $oDBase = new DataBase('PDO');

    // localiza o codigo do munícipio
    $lot  = (empty($lot) ? $_SESSION['sLotacao'] : $lot);
    $oDBase->query('SELECT codigo, descricao, codmun, uf_lota FROM tabsetor WHERE codigo = :lot ', array(array(':lot', $lot, PDO::PARAM_STR))
    );
    $oMun = $oDBase->fetch_object();
    $codmun = $oMun->codmun;
    $uflota = $oMun->uf_lota;

    // feriados nacionais,estaduais e municipais
    $ano          = dataAno($dia);
    $data_feriado = conv_data($dia);

    $oDBase->query('
        SELECT a.dia, a.mes, a.desc, a.tipo, a.codmun
        FROM feriados_'.$ano.' AS a
        LEFT JOIN tabsetor AS d ON a.codmun = d.codmun
        WHERE
            a.data_feriado = :data_feriado
            AND (a.tipo = "N" OR (a.tipo = "E" AND a.lot LIKE :lot)
            OR (a.tipo = "M" AND a.codmun = :codmun))
    ',
        array(
            array(':data_feriado', $data_feriado, PDO::PARAM_STR),
            array(':lot', $uflota, PDO::PARAM_STR),
            array(':codmun', $codmun, PDO::PARAM_STR)
        )
    );

    $numrows      = $oDBase->num_rows();

    return ($numrows > 0) ? 'S' : 'N';
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : verifica_se_fimdesemana                      |
 * | @description : Verifica se o dia informado é fim de semana  |
 * |                                                             |
 * | @param  : <string> - $dia                                   |
 * |                      dia da data                            |
 * | @param  : <string> - $mes                                   |
 * |                      mes da data                            |
 * | @param  : <string> - $ano                                   |
 * |                      ano da data                            |
 * | @return : <string> - N -> fins de semana, Não eh dia útil   |
 * |                      S -> para dia útil                     |
 * | @usage  : $fer = verifica_se_feriado('01','02','2010');     |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function verifica_se_fimdesemana($dia = '', $mes = '', $ano = '')
{
    $dia         = str_replace('-', '/', $dia);
    $data        = explode("/", $dia);
    $chDia       = (strlen($data[0]) <= 2 ? 0 : 2);
    $chAno       = (strlen($data[0]) >= 3 ? 0 : 2);
    $dia         = (empty($data[$chDia]) ? (empty($dia) ? date('d') : $dia) : substr('00' . $data[$chDia], -2));
    $mes         = (empty($data[1]) ? (empty($mes) ? date('m') : $mes) : substr('00' . $data[1], -2));
    $ano         = (empty($data[$chAno]) ? (empty($ano) ? date('Y') : $ano) : substr('0000' . $data[$chAno], -4));
    $fimdesemana = "N";
    $sab_dom     = date("w", mktime(0, 0, 0, $mes, $dia, $ano));
    if ($sab_dom == 0 || $sab_dom == 6)
    {
        $fimdesemana = "S";
    }
    return $fimdesemana;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : numero_dias_do_mes                           |
 * | @description : Retorna o número de dias do mês informado    |
 * |                                                             |
 * | @param  : <string>  - $mes                                  |
 * |                       mes desejado                          |
 * | @param  : <string>  - $ano                                  |
 * |                       ano desejado                          |
 * | @return : <integer> - total                                 |
 * |                       de dias do mes                        |
 * | @usage  : $dias_mes = numero_dias_do_mes('02','2010');      |
 * |          // retorna 28 (dias)                               |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function numero_dias_do_mes($mes = '', $ano = '')
{
    return date("t", mktime(0, 0, 0, $mes, 1, $ano));

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : horariosLimiteINSS                           |
 * | @description : Horários limite para início e término do     |
 * |                expediente no INSS                           |
 * |                                                             |
 * | @param  : void                                              |
 * | @return : <array> - horário de entrada e saída              |
 * | @usage  : $limites = horariosLimiteINSS();                  |
 * |          // retorna array                                   |
 * |          // Ex.: $limites['entrada'] e $limites['saida']    |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase() (Classe)                           |
 * +-------------------------------------------------------------+
 * */
function horariosLimiteINSS()
{
    $oDBase = new DataBase('PDO');

    $limites_inss                        = array();
    $limites_inss['tolerancia']          = '00:15';
    $limites_inss['entrada']['horario']  = '05:00:00';
    $limites_inss['entrada']['mensagem'] = 'Não é permitido registrar entrada antes das 5hs!';
    $limites_inss['saida']['horario']    = '23:59:00';
    $limites_inss['saida']['mensagem']   = 'Não é permitido registrar entrada após as 23:59hs!';

    $oDBase->query("
    SELECT
        campo, minutos AS horario, mensagem
    FROM
        config_basico
    WHERE
        ativo='S'
    ");

    if ($oDBase->num_rows() > 0)
    {
        while ($oCfgBasico = $oDBase->fetch_object())
        {
            switch ($oCfgBasico->campo)
            {
                case 'tolerancia':
                    $limites_inss['tolerancia']          = $oCfgBasico->horario;

                    break;
                case 'limite_hora_entrada_inss':
                    $limites_inss['entrada']['horario']  = $oCfgBasico->horario;
                    $limites_inss['entrada']['mensagem'] = str_replace("6:30hs", $oCfgBasico->horario, $oCfgBasico->mensagem);
                    break;

                case 'limite_hora_saida_inss':
                    $limites_inss['saida']['horario']  = $oCfgBasico->horario;
                    $limites_inss['saida']['mensagem'] = str_replace("22hs", $oCfgBasico->horario, ajustar_acentos($oCfgBasico->mensagem));
                    break;

                case 'limite_cinzas_tolerancia_entrada':
                    $limites_inss['cinzas_entrada']['horario']  = $oCfgBasico->horario;
                    $limites_inss['cinzas_entrada']['mensagem'] = ajustar_acentos($oCfgBasico->mensagem);
                    break;

                case 'limite_hora_inicio_funcionamento_padrao':
                    $limites_inss['inicio_funcionamento_padrao']['horario']  = $oCfgBasico->horario;
                    $limites_inss['inicio_funcionamento_padrao']['mensagem'] = ajustar_acentos($oCfgBasico->mensagem);
                    break;

                case 'limite_hora_fim_funcionamento_padrao':
                    $limites_inss['fim_funcionamento_padrao']['horario']  = $oCfgBasico->horario;
                    $limites_inss['fim_funcionamento_padrao']['mensagem'] = ajustar_acentos($oCfgBasico->mensagem);
                    break;

                case 'limite_jornada_corrida':
                    $limites_inss['limite_jornada_corrida']['horario']  = $oCfgBasico->horario;
                    $limites_inss['limite_jornada_corrida']['mensagem'] = ajustar_acentos($oCfgBasico->mensagem);
                    break;

                case 'limite_duracao_minima_almoco':
                    $limites_inss['limite_duracao_minima_almoco']['horario']  = $oCfgBasico->horario;
                    $limites_inss['limite_duracao_minima_almoco']['mensagem'] = ajustar_acentos($oCfgBasico->mensagem);
                    break;

                case 'limite_duracao_maxima_almoco':
                    $limites_inss['limite_duracao_maxima_almoco']['horario']  = $oCfgBasico->horario;
                    $limites_inss['limite_duracao_maxima_almoco']['mensagem'] = ajustar_acentos($oCfgBasico->mensagem);
                    break;

                case 'limite_horas_excedentes_por_dia':
                    $limites_inss['limite_horas_excedentes_por_dia']['horario']  = $oCfgBasico->horario;
                    $limites_inss['limite_horas_excedentes_por_dia']['mensagem'] = ajustar_acentos($oCfgBasico->mensagem);
                    break;

                case 'limite_horas_excedentes_por_dia_estagiarios':
                    $limites_inss['limite_horas_excedentes_por_dia_estagiarios']['horario']  = $oCfgBasico->horario;
                    $limites_inss['limite_horas_excedentes_por_dia_estagiarios']['mensagem'] = ajustar_acentos($oCfgBasico->mensagem);
                    break;

                default:
                    $limites_inss[$oCfgBasico->campo]['horario']  = $oCfgBasico->horario;
                    $limites_inss[$oCfgBasico->campo]['mensagem'] = ajustar_acentos($oCfgBasico->mensagem);
                    break;
            }
        }
    }

    return $limites_inss;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : grupoOcorrencias                             |
 * | @description : Define grupos de ocorrencias                 |
 * |                                                             |
 * | @param  : <string> - nome do grupo                          |
 * | @return : <array>  - grupo e ocorrencias                    |
 * | @usage  : $limites = grupoOcorrencias('limite_entrada');    |
 * |          // retorna array                                   |
 * |          // Ex.: $limites['limite_entrada']['mensagem']     |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase() (Classe)                           |
 * +-------------------------------------------------------------+
 * */
function grupoOcorrencias($grupo = '')
{
    $limites_inss                     = array();
    $limites_inss[$grupo]['horario']  = '';
    $limites_inss[$grupo]['mensagem'] = '';

    if ($grupo != '')
    {
        $oDBase = new DataBase('PDO');

        $oDBase->query("
            SELECT
                campo, minutos AS horario, mensagem, inicio, fim
            FROM
                config_basico
            WHERE
                campo = :campo
                AND ativo = 'S'
        ",
        array(
            array( ':campo', $grupo, PDO::PARAM_STR )
        ));
        $oCfgBasico = $oDBase->fetch_object();

        $limites_inss[$grupo]['horario']  = $oCfgBasico->horario;
        $limites_inss[$grupo]['mensagem'] = $oCfgBasico->mensagem;
        $limites_inss[$grupo]['inicio']   = $oCfgBasico->inicio;
        $limites_inss[$grupo]['fim']      = $oCfgBasico->fim;
    }

    return $limites_inss;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : inserir_dias_sem_frequencia                  |
 * | @description : Inclui os dias não registrados pelo servidor |
 * |                                                             |
 * | @param  : <string> - $mat                                   |
 * |                      matrícula do servidor                  |
 * | @param  : <string> - $dia                                   |
 * |                      dia inicial para inclusão              |
 * | @param  : <string> - $mes                                   |
 * |                      mes desejado                           |
 * | @param  : <string> - $ano                                   |
 * |                      ano para inclusão                      |
 * | @param  : <string> - $jornada                               |
 * |                      jornada de trabalho al ou diaria       |
 * | @param  : <string> - $lot                                   |
 * |                      unidade de lotacao do servidor         |
 * | @param  : <string> - $nome_do_arquivo                       |
 * |                      tabela para incluir dia                |
 * | @return : void                                              |
 * | @usage  : inserir_dias_sem_frequencia(                      |
 * |             '9999999', '20', '02', '2010', 40,              |
 * |             '04001000', 'ponto_temporario'                  |
 * |           );                                                |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : verifica_se_feriado (function)                |
 * |               ponto_facultativo   (function)                |
 * +-------------------------------------------------------------+
 * */
function inserir_dias_sem_frequencia($mat = '', $dia = '', $mes = '', $ano = '', $jornada = '', $lot = '', $nome_do_arquivo = '', $data_admissao = '', $data_exclusao = '')
{
    $ano = substr("0000" . (empty($ano) ? date("Y") : $ano), -4);
    $mes = substr("00" . (empty($mes) ? date("n") : $mes), -2);
    $ini = '1';

    if (empty($dia) || date('Ym') != ($ano.$mes))
    {
        // Obtendo o último dia do mês
        $fim = (date("t", mktime(0, 0, 0, $mes, 1, $ano)) + 1);
        //print '[' . $fim . ']<br>';
    }
    else
    {
        // Obtendo o último dia do mês
        $fim = date("t", mktime(0, 0, 0, $mes, 1, $ano));
        $fim = ($fim == $dia ? ($dia + 1) : $dia);
    }

    // instancia definir joranda
    $oDefinirJornada = new DefinirJornada();

    // instancia o BD
    $oTbPonto = new DataBase('PDO');

    $mat             = getNovaMatriculaBySiape($mat);
    $nome_do_arquivo = ( empty($nome_do_arquivo) ? "ponto$mes$ano" : $nome_do_arquivo);

    $oDBase = selecionaServidor($mat);
    $sitcad = $oDBase->fetch_object()->sigregjur;

    ## ocorrências grupos
    $obj = new OcorrenciasGrupos();
    $codigoSemFrequenciaPadrao   = $obj->CodigoSemFrequenciaPadrao($sitcad);
    $odigoFrequenciaNormalPadrao = $obj->CodigoFrequenciaNormalPadrao($sitcad);
    $codigoSemVinculoPadrao      = $obj->CodigoSemVinculoPadrao($sitcad);


    for ($i = $ini; $i < $fim; $i++)
    {
        $dia = date($ano . '-' . $mes . '-' . substr("0" . $i, -2));

        if (validaData($dia) == false || empty($mat)) {
            continue;
        }

        $oBaseJornada = $oDefinirJornada->PesquisaJornadaHistorico($mat, $dia);
        $jornada      = formata_jornada_para_hhmm($oBaseJornada->fetch_object()->jornada);

        // feriado nacional, estadual ou municipal
        $dia_util = (verifica_se_feriado($dia, $lot) == "S" ? "N" : "S");

        // sábado ou domingo
        $hoje = date("w", mktime(0, 0, 0, $mes, $i, $ano));

        // verifica se dia ponto facultativo
        $jornada = ponto_facultativo($dia, $jornada, $ano, '00:00:00', '00:00:00', '00:00:00', '00:00:00', $mat);

        $data_exclusao = (conv_data($data_exclusao) == '' || conv_data($data_exclusao) < '0000-00-99' ? '9999-99-99' : conv_data($data_exclusao));
        $data_admissao = conv_data($data_admissao);

        $dia_ano_mes      = dataAno($dia) . dataMes($dia);
        $admissao_ano_mes = dataAno($data_admissao) . dataMes($data_admissao);
        $exclusao_ano_mes = dataAno($data_exclusao) . dataMes($data_exclusao);

        if ($dia >= $data_admissao && $dia < $data_exclusao)
        {
            // verifica qual a situacao do dia
            if (verifica_se_dia_nao_util($dia, $lot) == true)
            {
                $ocor  = $odigoFrequenciaNormalPadrao[0]; //'00000';
                $jornp = '00:00';
            }
            else
            {
                $ocor  = ($jornada == '00:00' ? $odigoFrequenciaNormalPadrao[0] : $codigoSemFrequenciaPadrao[0]);
                $jornp = $jornada;
            }
        }
        elseif ($dia_ano_mes == $admissao_ano_mes || $dia_ano_mes == $exclusao_ano_mes)
        {
            $ocor  = $codigoSemVinculoPadrao[0]; //'02727'; // Sem vínculo
            $jornp = '00:00';
        }
        elseif ($dia_ano_mes < $admissao_ano_mes || $dia_ano_mes >= $exclusao_ano_mes)
        {
            $ocor  = '';
            $jornp = '';
        }
        else
        {
            $ocor  = $codigoSemFrequenciaPadrao[0]; //'99999';
            $jornp = $jornada;
        }

        // se a data atual igual a data para inserção
        // e não for dia útil e estiver autorizado trabalho
        // neste dia, então não registra ocorrência
        if ($dia == date('Y-m-d'))
        {
            $autoriza = autorizacaoDiaNaoutil($dia, $mat);
        }
        else
        {
            $autoriza = "N";
        }

        //verifica se o dia foi registrado pelo servidor
        if ($autoriza == "N")
        {
            $oTbPonto->query(
                'SELECT * FROM ' . $nome_do_arquivo . ' WHERE siape = :siape AND dia = :dia ', array(
                array(':siape', $mat, PDO::PARAM_STR),
                array(':dia', $dia, PDO::PARAM_STR)
                )
            );
            $linhas = $oTbPonto->num_rows();
            if ($linhas == 0 && $ocor != '')
            {
                $oTbPonto->query(
                    'INSERT INTO ' . $nome_do_arquivo . '
                        (dia, siape, jornp, jorndif, oco, idreg, ip, ip2, ip3, ip4, ipch, iprh)
                            VALUES(:dia, :siape, :jornp, :jorndif, :oco,"X","","","","","","") ', array(
                    array(':dia', $dia, PDO::PARAM_STR),
                    array(':siape', $mat, PDO::PARAM_STR),
                    array(':jornp', $jornp, PDO::PARAM_STR),
                    array(':jorndif', $jornp, PDO::PARAM_STR),
                    array(':oco', $ocor, PDO::PARAM_STR)
                    )
                );
            }
        }
    }

}

/**  @Function
 * +--------------------------------------------------------------------+
 * | Função.....: atualiza_banco_dhoras                                 |
 * +--------------------------------------------------------------------+
 * */
function atualiza_banco_dhoras($mat = '', $comp = '', $cod = '', $total = 0, $destinacao = '1')
{
    // instancia a conexao a base de dados
    $oDBase = new DataBase('PDO');

    // gravando o resultado
    $bh     = "SELECT * FROM bhoras WHERE siape = '$mat' and comp = '$comp' and dest = '$destinacao' ";
    $re     = $oDBase->query($bh);
    $dberro = $oDBase->error();
    if (!empty($dberro))
    {
        mensagem("Falha no acesso ao banco de horas:\\n" . $dberror);
    }
    else
    {
        $linha = $oDBase->num_rows();
        if ($linha != "0")
        {
            $query = "UPDATE bhoras SET horas = '$total', codigo = '$cod' WHERE siape = '$mat' and comp = '$comp' and dest = '$destinacao' ";
        }
        else
        {
            $query = "INSERT INTO bhoras (comp, horas , codigo, siape, dest ) VALUES ('$comp', '$total', '$cod', '$mat', '$destinacao')  ";
        }
        $result = $oDBase->query($query);
        $dberro = $oDBase->error();
        if (!empty($dberro))
        {
            mensagem("Falha no registro de banco de horas:\\n" . $dberror);
        }
    }
    return $total;

}

/**  @Function
 * +--------------------------------------------------------------------+
 * | Function: rotina_de_totalizacao_de_horas                           |
 * |          - Rotina para ajustar total no banco de horas             |
 * |            face destinação de credito                              |
 * |          - Recalculo do total de horas (ocorrencia) do mes         |
 * |            escolhido face destinação                               |
 * +--------------------------------------------------------------------+
 * */
function rotina_de_totalizacao_de_horas($mat = '', $comp = '', $comsinal = true, $nome_do_arquivo = '')
{
    $oDBase = selecionaServidor($mat);
    $sitcad = $oDBase->fetch_object()->sigregjur;

    ## ocorrências grupos
    $obj = new OcorrenciasGrupos();
    $codigoCreditoPadrao            = $obj->CodigoCreditoPadrao($sitcad);
    $codigosAgrupadosParaDesconto   = $obj->CodigosAgrupadosParaDesconto($sitcad);
    $codigoCreditoRecessoPadrao     = $obj->CodigoCreditoRecessoPadrao($sitcad);
    $codigoDebitoRecessoPadrao      = $obj->CodigoDebitoRecessoPadrao($sitcad);
    $codigoCreditoInstrutoriaPadrao = $obj->CodigoCreditoInstrutoriaPadrao($sitcad);
    $codigoDebitoInstrutoriaPadrao  = $obj->CodigoDebitoInstrutoriaPadrao($sitcad);
    $codigoHoraExtraPadrao          = $obj->CodigoHoraExtraPadrao($sitcad);
    $eventosEsportivos              = $obj->EventosEsportivos($sitcad);

    // instancia a conexao a base de dados
    $oDBase = new DataBase('PDO');

    // mes e ano
    $nome_do_arquivo = ($nome_do_arquivo == '' ? 'ponto' . $comp : $nome_do_arquivo);

    // Ocorrências que demandam alteração na totalização das horas
    $grupo_ocorr = array_merge(
        $codigoCreditoPadrao, $codigosAgrupadosParaDesconto,
        $codigoCreditoRecessoPadrao, $codigoDebitoRecessoPadrao,
        $codigoCreditoInstrutoriaPadrao, $codigoDebitoInstrutoriaPadrao,
        $codigoHoraExtraPadrao, $eventosEsportivos
    );

    // Essa query me retorna 3 linhas, cada linha contém a soma dos segundos de cada ocorrência do usuário
    $sql = "SELECT SUM(TIME_TO_SEC(jorndif)) as segundos, oco
			FROM $nome_do_arquivo
			WHERE siape= :siape AND oco IN ($grupo_ocorr)
			GROUP BY oco ";
    $oDBase->query(
        $sql, array(
        array(':siape', $mat, PDO::PARAM_STR)
        )
    );

    // Nas variáveis declaradas abaixo,
    // em $tempo->positivo vai entrar os segundos da OCO 33333 e
    // em $tempo->negativo vai entrar a soma da OCO 00172, 55555, 62010 e 62012
    // Positivo: 33333,  Negativo: 00172, 55555, 62010, 62012
    $tempo->positivo = 0;
    $tempo->negativo = 0;

    // Positivo: 02424,  Negativo: 02323
    $tempo->recpos           = 0; // Código
    $tempo->recneg           = 0; // Código

    // Positivo: 02626,  Negativo: 02525
    $tempo->instpos          = 0; // Código
    $tempo->instneg          = 0; // Código

    // Positivo: 02828
    $tempo->extrapos         = 0; // Código

    // Positivo: 92014,  Negativo: 62014
    $tempo->copa2014positivo = 0;
    $tempo->copa2014negativo = 0;

    // Faço o loop nas 3 linhas retornadas na query
    while ($row = $oDBase->fetch_object())
    {
        // se OCO é 33333 (crédito), atribui os segundos a $tempo->positivo
        if (in_array($row->oco, $codigoCreditoPadrao))
        {
            $tempo->positivo += $row->segundos;
        }

        // código de ocorrências agrupados para desconto, atribui os segundos a $tempo->negativo
        else if (in_array($row->oco, $codigosAgrupadosParaDesconto))
        {
            $tempo->negativo += $row->segundos;
        }

        // se OCO é 92014, atribui os segundos a $tempo->copa2014positivo
        else if ($row->oco == '92014')
        {
            $tempo->copa2014negativo += $row->segundos;
        }

        // se OCO é 62014, atribui os segundos a $tempo->copa2014negativo
        else if ($row->oco == '62014')
        {
            $tempo->copa2014negativo += $row->segundos;
        }

        // se OCO é 02424 (crédito recesso), atribui os segundos a $tempo->recpos
        else if (in_array($row->oco, $codigoCreditoRecessoPadrao))
        {
            $tempo->recpos += $row->segundos;
        }

        // se OCO é 02323 (débito recesso), atribui os segundos a $tempo->recneg
        else if (in_array($row->oco, $codigoDebitoRecessoPadrao))
        {
            $tempo->recneg += $row->segundos;
        }

        // se OCO é 02626 (crédito instrutoria), atribui os segundos a $tempo->instpos
        else if (in_array($row->oco, $codigoCreditoInstrutoriaPadrao))
        {
            $tempo->instpos += $row->segundos;
        }

        // se OCO é 02525 (débito instrutoria), atribui os segundos a $tempo->instpos
        else if (in_array($row->oco, $codigoDebitoInstrutoriaPadrao))
        {
            $tempo->instneg += $row->segundos;
        }

        // se OCO é 02828 (serviço extraordinário), atribui os segundos a $tempo->extrapos
        else if (in_array($row->oco, $codigoHoraExtraPadrao))
        {
            $tempo->extrapos += $row->segundos;
        }
    }

    // Verifico qual variável tem o maior valor e faço o cálculo. O resultado é em segundos.
    // calcula_exibe_horas(
    //   <mat>  - matrícula do servidor,
    //   <comp> - mes e ano a que se refere a ocorrencia,
    //   <tempo->positivo> - total dos segundos positivos (+),
    //   <tempo->negativo> - total dos segundos negativos (-),
    //   <cod_positivo>    - se o saldo for positivo grava o código indicado,
    //   <cod_negativo>    - se o saldo for negativo grava o código informado,
    //   <destinacao>      - qual a destinacao dos segundos, compensacao, recesso, instrutoria ou hora-extra,
    //   <com_sinal>       - a função retorna o tempo no formato hh:mm, e pode retornar com o sianl (-/+)
    // );
    //
	// RETORNA:
    // $total[] = <código de ocorrência>;
    // $total[] = <hh:mm:ss>;
    // $total[] = <hh:mm:ss>; ???

    $total_horas->comuns      = array();
    $total_horas->extras      = array();
    $total_horas->recesso     = array();
    $total_horas->copa2014    = array();
    $total_horas->instrutoria = array();

    //calculo de horas comuns
    $total_horas->comuns = calcula_exibe_horas($tempo->positivo, $tempo->negativo, $codigoCreditoPadrao[0], $codigoDebitoPadrao[0], '1', $comsinal);

    //calculo de horas de recesso
    $total_horas->recesso = calcula_exibe_horas($tempo->recpos, $tempo->recneg, $codigoCreditoRecessoPadrao[0], $codigoDebitoRecessoPadrao[0], '2', $comsinal);

    //calculo de horas de instrutoria
    $total_horas->instrutoria = calcula_exibe_horas($tempo->instpos, $tempo->instneg, $codigoCreditoInstrutoriaPadrao[0], $codigoDebitoInstrutoriaPadrao[0], '3', $comsinal);

    //calculo de horas extras
    $total_horas->extras = calcula_exibe_horas($tempo->extrapos, 0, $codigoHoraExtraPadrao[0], '', '4', $comsinal);

    //calculo de horas Copa do Mundo 2014
    $total_horas->copa2014 = calcula_exibe_horas($tempo->copa2014positivo, $tempo->copa2014negativo, '92014', '62014', '5', $comsinal);

    return $total_horas;

}

/**  @Function
 * +--------------------------------------------------------------------+
 * | Function: rotina_contar_ocorrencias                                |
 * |          - Rotina para contar números de repetição de determinadas |
 * |            ocorrências                                             |
 * +--------------------------------------------------------------------+
 * */
function rotina_contar_ocorrencias($mat = '', $comp = '', $grupo_ocorr = '')
{
    $oDBase = selecionaServidor($mat);
    $sitcad = $oDBase->fetch_object()->sigregjur;

    ## ocorrências grupos
    $obj = new OcorrenciasGrupos();
    $codigosTrocaObrigatoria = $obj->CodigosTrocaObrigatoria($sitcad);

    // mes e ano
    $nome_do_arquivo = 'ponto' . $comp;

    // instancia a conexao a base de dados
    $oDBase = new DataBase('PDO');

    // Ocorrências que demandam alteração na totalização das horas
    $grupo_ocorr = ($grupo_ocorr == "" ? implode(',', $codigosTrocaObrigatoria) : $grupo_ocorr);

    // Essa query me retorna 3 linhas, cada linha contém a soma dos segundos de cada ocorrência do usuário
    $sql = "SELECT oco, COUNT(*) AS total FROM $nome_do_arquivo WHERE siape = :siape AND oco IN (" . $grupo_ocorr . ") GROUP BY oco ORDER BY oco ";
    $oDBase->query($sql, array(array(":siape", $mat, PDO::PARAM_STR))
    );

    // Ocorrências que não podem permanecer registradas
    $total = array();

    // Faço o loop nas 3 linhas retornadas na query
    while ($row = $oDBase->fetch_object())
    {
        $total[] = array($row->oco, $row->total);
    }

    return $total;

}

/**  @Function
 * +--------------------------------------------------------------------+
 * | Descrição: Calculo do total de horas (ocorrencia) do mes           |
 * |           escolhido face destinação                                |
 * | Função...: calculo_do_total                                        |
 * +--------------------------------------------------------------------+
 * */
function calculo_do_total($mat = '', $comp = '', $destinacao = '')
{
    $oDBase = selecionaServidor($mat);
    $sitcad = $oDBase->fetch_object()->sigregjur;

    ## ocorrências grupos
    $obj = new OcorrenciasGrupos();
    $ocorrenciasNegativasDebitos    = $obj->GrupoOcorrenciasNegativasDebitos($sitcad, $exige_horarios = true);
    $codigoCreditoPadrao            = $obj->CodigoCreditoPadrao($sitcad);
    $codigoDebitoPadrao             = $obj->CodigoDebitoPadrao($sitcad);
    $codigoCreditoRecessoPadrao     = $obj->CodigoCreditoRecessoPadrao($sitcad);
    $codigoDebitoRecessoPadrao      = $obj->CodigoDebitoRecessoPadrao($sitcad);
    $codigoHoraExtraPadrao          = $obj->CodigoHoraExtraPadrao($sitcad);
    $codigoCreditoInstrutoriaPadrao = $obj->CodigoCreditoInstrutoriaPadrao($sitcad);
    $codigoDebitoInstrutoriaPadrao  = $obj->CodigoDebitoInstrutoriaPadrao($sitcad);
    $eventosEsportivos              = $obj->EventosEsportivos($sitcad);

    // códigos que tem o mesmo tratamento que 00172
    $codigos_para_00172 = implode(',', $ocorrenciasNegativasDebitos);

    switch ($destinacao)
    {
        // se OCO é 33333, atribui os segundos a $tempo->positivo
        // se OCO é 00172, 55555, 62010 ou 62012 os segundos em $tempo->negativo
        case '1':
            $cod_positivo = $codigoCreditoPadrao[0];
            $cod_negativo = $codigos_para_00172;
            break;

        // se OCO é 02424, atribui os segundos a $tempo->positivo
        // se OCO é 02323, atribui os segundos a $tempo->negativo
        case '2':
            $cod_positivo = $codigoCreditoRecessoPadrao[0];
            $cod_negativo = $codigoDebitoRecessoPadrao[0];
            break;

        // se OCO é 02626, atribui os segundos a $tempo->positivo
        // se OCO é 02525, atribui os segundos a $tempo->negativo
        case '3':
            $cod_positivo = $codigoCreditoInstrutoriaPadrao[0];
            $cod_negativo = $codigoDebitoInstrutoriaPadrao[0];
            break;

        // se OCO é 02828, atribui os segundos a $tempo->positivo
        case '4':
            $cod_positivo = $codigoHoraExtraPadrao[0];
            $cod_negativo = "''";
            break;

        // se OCO é 92014, atribui os segundos a $tempo->positivo
        // se OCO é 62014, atribui os segundos a $tempo->negativo
        case '5':
            $cod_negativo = '';
            $cod_positivo = '';

            foreach($eventosEsportivos AS $value)
            {
                if (substr($comp,-4) == substr($value,-4))
                {
                    $cod_negativo = (substr($value,0,1) == '6' && empty($cod_negativo) ? $value : $cod_negativo);
                    $cod_positivo = (substr($value,0,1) == '9' && empty($cod_positivo) ? $value : $cod_positivo);
                }
            }
            break;
    }

    $sql = "
    SELECT
        @somaPositivo := IFNULL(SUM(TIME_TO_SEC(IF(oco IN ($cod_positivo),jorndif,0))),0) AS positivo,
        @somaNegativo := IFNULL(SUM(TIME_TO_SEC(IF(oco IN ($cod_negativo),jorndif,0))),0) AS negativo,
        TRUNCATE((@somaPositivo-@somaNegativo),0) AS total_horas,
        SEC_TO_TIME(@somaPositivo-@somaNegativo)  AS horas,
        if(oco IN ($codigos_para_00172),'".$codigoDebitoPadrao[0]."',oco) as oco
    FROM
        ponto$comp
    WHERE
        siape = :siape
        AND oco IN ($cod_positivo,$cod_negativo)
    GROUP BY
        siape
    ";

    $params = array(
        array(':siape', $mat, PDO::PARAM_STR)
    );

    $oTbPonto = new DataBase('PDO');

    // executamos uma primeira vez, somamos os creditos e os debitos, depois subtraimos os debitos dos creditos
    // retornando: o valor em segundos, horas (hh:mm) e codigo de ocorrencia
    $oTbPonto->query($sql, $params);

    // repetimos a operação, pois a utilização das variaveis @soma... na primeira vez não assumem os valores
    // retornando: o valor em segundos, horas (hh:mm) e codigo de ocorrencia
    $oTbPonto->query($sql, $params);

    $oHoras = $oTbPonto->fetch_object();
    $oHoras = array($oHoras->oco, $oHoras->total_horas, $oHoras->positivo, $oHoras->negativo, $oHoras->horas);

    return $oHoras;
}

/**  @Function
 * +-----------------------------------------------------------------------+
 * | Função.....: calcula_exibe_horas                                      |
 * |             Verifico qual variável tem o maior valor e faço o cálculo.|
 * |             O resultado é em segundos.                                |
 * +-----------------------------------------------------------------------+
 * */
function calcula_exibe_horas($positivo = 0, $negativo = 0, $codPositivo = '', $codNegativo = '', $tipo = '', $comsinal = true)
{
    ## ocorrências grupos
    $obj = new OcorrenciasGrupos();
    $codigoFrequenciaNormalPadrao = $obj->CodigoFrequenciaNormalPadrao($sitcad);

    //calculo de horas
    $segundos = ($positivo > $negativo) ? ($positivo - $negativo) : ($negativo - $positivo);

    if ($positivo != $negativo)
    {
        $cod   = ($positivo > $negativo) ? $codPositivo : $codNegativo;
        $sinal = ($positivo > $negativo ? "+" : "-");

        //preparando para exibir horas
        $horas    = floor($segundos / 3600);
        $segundos -= $horas * 3600;
        $minutos  = floor($segundos / 60);
        $segundos -= $minutos * 60;
    }
    else
    {
        $cod     = $codigoFrequenciaNormalPadrao[0];
        $sinal   = " ";
        $horas   = 0;
        $minutos = 0;
    }

    $total   = array();
    $total[] = $cod;
    $total[] = ($comsinal == true ? $sinal : '') . sprintf("%02s:%02s", $horas, $minutos);
    $total[] = ($comsinal == true ? $sinal : '') . sprintf("%02s:%02s", $horas, $minutos);

    return $total;

}

/**  @Function
 * +--------------------------------------------------------------------+
 * |                                                                    |
 * |  ATUALIZA O ARQUIVO FRQ<ANO> COM BASE NO PONTO DO MES              |
 * |                                                                    |
 * +--------------------------------------------------------------------+
 * */
function atualiza_frqANO($mat = '', $mes = '', $ano = '', $destino = '', $barraprogresso = true, $calcular = false, $processa_competencia_atual = false)
{
    if ($calcular == true)
    {
        atualiza_frqANOx($mat, $mes, $ano, $destino, $barraprogresso, $processa_competencia_atual);
    }

}

function atualiza_frqANOx($sMatricula = '', $mes = '', $ano = '', $destino = '', $barraprogresso = true, $processa_competencia_atual = false)
{
    ## dados de trabalho
    #
	 $sFrqAno      = "frq$ano";
    $sPontoMesAno = "ponto$mes$ano";
    $sCompet      = "$ano$mes";

    ## instancia do banco de dados
    #
	$oDBase = new DataBase('PDO');

    ## Verifica se existe o arquivo de
    #  frequencia na competencia desejada
    #
	if (existeDBTabela($sPontoMesAno, 'sisref') == false)
    {
        mensagem("Não existe tabela de ponto referente a $mes / $ano!", null, 1);
    }
    else
    {

        ## Verifica se existe a tabela para
        #  registro dos dados acumulados
        #
		if (existeDBTabela($sFrqAno, 'sisref') == false)
        {
            $sql = "CREATE TABLE IF NOT EXISTS $sFrqAno LIKE frq2018; ";
            $oDBase->query($sql);
        }
        ## verificamos a necessidade de atualizacao do frqANO pesquisando,
        #  no banco de usuarios, se o campo refaz_frqano está igual a 'S'
        #
		$oDBase->query("SELECT a.refaz_frqano FROM usuarios AS a WHERE a.siape='$sMatricula' AND a.refaz_frqano='S' ORDER BY a.siape ");
        $nRows        = $oDBase->num_rows();
        $sRefazFrqAno = 'S'; //$oDBase->fetch_object()->refaz_frqano;

        if (($sRefazFrqAno == 'S' && $sCompet != date('Ym') && $nRows > 0) || $processa_competencia_atual == true)
        {
            ## pesquisamos as ocorrencias registradas no mes informado
            #  para tratá-las e inserir no FRQ<ano> que é um agrupamento
            #  das ocorrências do mês em questão
            #
			$oDBase->query("SELECT a.siape, a.oco, a.dia, a.jorndif FROM $sPontoMesAno AS a WHERE a.siape='$sMatricula' AND DATE_FORMAT(a.dia,'%Y%m')='$sCompet' ORDER BY a.siape,a.dia,a.oco ");
            $nrows_ponto = $oDBase->num_rows();

            /* _______________________________________________________________*\
              |   P R O C E S S O                                               |
              \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */

            if ($nrows_ponto > 0)
            {
                ## variaveis de trabalho
                #
				$grupo    = "";  // grupo de ocorrencia
                $diaini   = "";  // dia inicial
                $xdia     = '';  // dia
                $xdias    = 0;   // numero de dias
                $xhoras   = 0;   // quantidade de horas restantes do(s) dia(s)
                $xminutos = 0; // total de minutos restantes do(s) dia(s)
                ## apagamos os dados na tabela FRQ<ano> da
                #  competencia e do servidor indicado para
                #  evitar residuos de informações anteriores
                #
				$oTbFRQ   = new DataBase('PDO');
                $oTbFRQ->setDestino($destino);
                $oTbFRQ->query("DELETE FROM $sFrqAno WHERE mat_siape='$sMatricula' AND compet='$sCompet' ");

                ## realizamos o agrupamento e contagem de dias de ocorrencias iguais
                #  dentro de um periodo continuo. Ex. de 1 a 5 houve a ocorrencia 33333,
                #  6 houve uma oco 00172 e de 7 até o último dia houve a oco 00000 (normal)
                #  resultando nos seguintes registros:
                #
				#  compet  dia_ini  dia_fim  cod_ocorr  mat_siape  dias  horas  minutos
                #  200910  01       05       33333      9999999     5     2     22
                #  200910  06       06       00172      9999999     1     1     42
                #  200910  07       30       00000      9999999    24     0      0
                #
				while ($linhas = $oDBase->fetch_object())
                {
                    ## valores lidos do banco de dados
                    #
					$siape  = $linhas->siape;
                    $oco    = $linhas->oco;
                    $dia    = $linhas->dia;
                    $dif    = $linhas->jorndif;
                    $diaini = (empty($diaini) ? substr($dia, 8, 2) : $diaini);

                    ## grupo siape + ocorrencia
                    #
					$dados = $siape . $oco;

                    if (empty($grupo) || $grupo == $dados)
                    {
                        ## acumulamos as horas e dias de mesma ocorrencia
                        #  sequenciadas
                        #
						$xdia     = (empty($xdia) ? $dia : $xdia);
                        $xsiape   = $siape;
                        $xoco     = $oco;
                        $xdias++;
                        $xhoras   = intval(substr($dif, 0, 2));
                        $xmin     = intval(substr($dif, 3, 2));
                        $xminutos += intval(($xhoras * 60) + $xmin);
                        $diafim   = substr($dia, 8, 2);
                    }
                    else
                    {
                        ## realizamos a inclusão dos dados na tabela se não houver período igual registrado
                        #
						$horas   = intval($xminutos / 60);
                        $minutos = intval(($xminutos % 60));
                        $oTbFRQ->query("INSERT INTO $sFrqAno SET compet='$sCompet', dia_ini='$diaini', dia_fim='" . ($xdias == 1 ? $diaini : $diafim) . "', cod_ocorr='$xoco', mat_siape='$xsiape', dias='$xdias', horas='$horas', minutos='$minutos' ");

                        ## reiniciamos as variaveis para novo processo
                        #
						$xdia     = $dia;
                        $xsiape   = $siape;
                        $xoco     = $oco;
                        $xdias    = 1;
                        $xhoras   = intval(substr($dif, 0, 2));
                        $xmin     = intval(substr($dif, 3, 2));
                        $xminutos = intval(($xhoras * 60) + $xmin);
                        $diaini   = substr($dia, 8, 2);
                    }
                    $grupo = $dados;
                }

                ## realizamos a inclusão dos dados na tabela se não houver período igual registrado
                #
				$horas   = intval($xminutos / 60);
                $minutos = intval(($xminutos % 60));
                $oTbFRQ->query("INSERT INTO $sFrqAno SET compet='$sCompet', dia_ini='$diaini', dia_fim='" . ($xdias == 1 ? $diaini : $diafim) . "', cod_ocorr='$xoco', mat_siape='$xsiape', dias='$xdias', horas='$horas', minutos='$minutos' ");
            }
            $grupo = $dados;
        }
    }

}

/**  @Function
 * +--------------------------------------------------------------------+
 * |                                                                    |
 * |  CALCULO DAS HORAS DO DIA                                          |
 * |                                                                    |
 * |    horas no intervalo para quatro marcações                        |
 * |    calculo das horas do dia                                        |
 * |    calculo da dif do dia                                           |
 * |                                                                    |
 * +--------------------------------------------------------------------+
 * */
function calculo_das_horas_do_dia()
{
    global $he, $hs, $hie, $his, $dif, $jp, $jdia, $modo, $dutil, $idif;
    // horas no intervalo para quatro marcações
    $aH1   = explode(":", $hie);
    $aH2   = explode(":", $his);
    $tidia = mktime($aH2[0] - $aH1[0], $aH2[1] - $aH1[1], 0, 0);

    //calculo das horas do dia
    $jid    = date("H:i", $tidia);
    $id     = explode(":", $jid);
    $aHora1 = explode(":", $he);
    $aHora2 = explode(":", $hs);

    $td = mktime($aHora2[0] - $aHora1[0] - $id[0], $aHora2[1] - $aHora1[1] - $id[1], 0, 0);

    //calculo da dif do dia
    $jdia = date("H:i", $td);
    $jd   = explode(":", $jdia);
    $jpre = explode(":", $jp);

    //define codigo a ser utilizado conforme  redução ou aumento na jornada do dia
    switch ($modo)
    {
        case "2":
            $totd = mktime($jpre[0] - $jd[0], $jpre[1] - $jd[1], 0, 0);
            break;

        case "3":
            $totd = mktime($jd[0] - $jpre[0], $jd[1] - $jpre[1], 0, 0);
            break;
    }

    $dif = date("H:i", $totd);

    if ($dif > "02:00" && $dutil == "S")
    {
        $dif  = "02:00";
        $idif = "S";
    }

    if ($dutil == "N")
    {
        $dutil = "N";
        $dif   = ($dif > "10:00" ? "10:00" : $jdia);
        $jp    = "00:00";
    }

}

/**  @Function
 * +--------------------------------------------------------------------+
 * |                                                                    |
 * | Função.: botao                                                     |
 * | Retorna: monta botao                                               |
 * | Autor..: Edinalvo Rosa                                             |
 * | Exemplo:                                                           |
 * |   botao( "Enviar" );                                               |
 * |                                                                    |
 * +--------------------------------------------------------------------+
 * */
$style_btfig = false;
$javsc_btfig = 0;

function botao($texto = '', $onClick = 'botaoSubmit', $off = false, $glyphicon = 'glyphicon-ok', $width = 'col-md-12', $align = 'text-center')
{
    $btn_id   = (mb_strtolower($texto) == 'voltar' ? 'voltar' : 'continuar');
    $btn_tipo = (mb_strtolower($texto) == 'voltar' ? 'danger' : 'primary');

    ?>
    <div class="<?= $width; ?> <?= $align; ?>">
        <a class="btn btn-primary btn-<?= $btn_tipo; ?>" id="btn-<?= $btn_id; ?>" href="<?= $onClick; ?>">
            <span class="glyphicon <?= $glyphicon; ?>"></span> <?= $texto; ?>
        </a>
    </div>
    <?php

}

function botao2($texto = '', $onClick = 'botaoSubmit', $off = false)
{
    //TODO:checar depois
    global $style_btfig, $javsc_btfig;
    $srt = "";
    if ($onClick == 'botaoSubmit')
    {
        $srt .= "
		<script>

		function botaoSubmit" . $javsc_btfig . "() {
		document.forms[0].submit();
		}

		</script>";
    }
    $onClick = ($onClick == 'botaoSubmit' ? 'javascript:botaoSubmit' . $javsc_btfig . '()' : $onClick);
    if (!empty($texto))
    {
        if ($style_btfig == false)
        {
            $srt         .= "<style>.btfig { font-family: verdana; font-weight: bold; font-size: 0.7em; background-image:	url('" . _DIR_IMAGEM_ . "bt_centro.gif'); } </style>\n";
            $style_btfig = true;
        }
        $srt .= "<div style='cursor:pointer;'><table id='botao" . $javsc_btfig . "' border='0' cellspacing='0' cellpadding='0' " . ($off ? "" : "onclick='" . $onClick . "' style='border: 0 solid white; cursor:pointer;; display: ;'") . "><tr style='border: 0 solid white;'><td style='border: 0 solid white;'><img src='" . _DIR_IMAGEM_ . "bt_dir.gif' border='0'></td><td class='btfig' nowrap style='color: " . ($off ? '#B9B9B9' : '#006A9D') . "; border: 0 solid white;'>" . $texto . "</td><td style='border: 0 solid white;'><img src='" . _DIR_IMAGEM_ . "bt_esq.gif' border='0'></td></tr></table></div>\n";
    }
    $javsc_btfig++;
    return $srt;

}

/**  @Function
 * +--------------------------------------------------------------------+
 * |                                                                    |
 * | Função.: botao                                                     |
 * | Retorna: monta botao                                               |
 * | Autor..: Edinalvo Rosa                                             |
 * | Exemplo:                                                           |
 * |   botao( "Enviar" );                                               |
 * |                                                                    |
 * +--------------------------------------------------------------------+
 * */
$style_btfig = false;
$javsc_btfig = 0;

function botaoDuplo($texto = '', $onClick = 'botaoSubmit', $off = false)
{
    global $style_btfig, $javsc_btfig;
    $str = "";
    if ($onClick == 'botaoSubmit')
    {
        $srt .= "<script>function botaoSubmit" . $javsc_btfig . "() { document.forms[0].submit(); }</script>";
    }
    $onClick = ($onClick == 'botaoSubmit' ? 'javascript:botaoSubmit' . $javsc_btfig . '()' : $onClick);
    if (!empty($texto))
    {
        if ($style_btfig == false)
        {
            $srt         .= "<style>.btfig { font-family: verdana; font-weight: bold; font-size: 0.7em; background-image:	url('" . _DIR_IMAGEM_ . "bt_centro2.gif'); } </style>\n";
            $style_btfig = true;
        }
        $srt .= "<div style='cursor:pointer;'><table id='botao" . $javsc_btfig . "' border='0' cellspacing='0' cellpadding='0' " . ($off ? "" : "onclick='" . $onClick . "' style='border: 0 solid white; cursor:pointer; display: ;'") . "><tr style='border: 0 solid white;'><td style='border: 0 solid white;'><img src='" . _DIR_IMAGEM_ . "bt_dir2.gif' border='0'></td><td class='btfig' nowrap style='color: " . ($off ? '#B9B9B9' : '#006A9D') . "; border: 0 solid white;'>" . $texto . "</td><td style='border: 0 solid white;'><img src='" . _DIR_IMAGEM_ . "bt_esq2.gif' border='0'></td></tr></table></div>\n";
    }
    $javsc_btfig++;
    return $srt;

}

/**  @Function
 * +---------------------------------------------------------------------------+
 * |                                                                           |
 * | Função.: define_quem_registrou_descricao                                  |
 * | Retorna: define se foi o Servidor, Recursos Humanos ou a Chefia imediata  |
 * |          quem realizou a operação                                         |
 * | Autor..: Edinalvo Rosa                                                    |
 * | Exemplo:                                                                  |
 * |   define_quem_registrou_descricao(...);                                   |
 * |                                                                           |
 * +---------------------------------------------------------------------------+
 * */
function define_quem_registrou_descricao($dados, $situacao_cadastral = '', $comp_invertida = '')
{
    ## ocorrências grupos
    $obj = new OcorrenciasGrupos();
    $codigoSemFrequenciaPadrao      = $obj->CodigoSemFrequenciaPadrao($sitcad);
    $codigoFrequenciaNormalPadrao   = $obj->CodigoFrequenciaNormalPadrao($sitcad);
    $ocorrenciasSistemaIndisponivel = $obj->OcorrenciasSistemaIndisponivel($sitcad);

    $ocorrencias = array_merge($codigoFrequenciaNormalPadrao,$ocorrenciasSistemaIndisponivel,$codigoSemFrequenciaPadrao);

    // registro incluido, sem numero de IP
    // e competencia menor que 03/2010, não há como determinar quem registrou a ocorrência
    // se maior que 03/2010, verifica se foi o RH via SIAPECAD (extração do siape), ou se foi o sistema (SISREF)

    $registrado_por = ""; // 'x' para forçar teste de chefia ou rh quando idreg='S'

    switch ($dados->idreg)
    {
        case 'S':
            if (empty($dados->ip) && $comp_invertida <= '201003')
            {
                $registrado_por = "";
            }
            else if (empty($dados->ip) && empty($dados->matchef) && empty($dados->siaperh))
            {
                $registrado_por = (in_array($dados->oco,$ocorrencias) ? "SISREF" : ($situacao_cadastral == '66' ? "Estagiário" : "Servidor" )); // incluido pelo sistema
            }
            else if (!empty($dados->matchef) && !empty($dados->siaperh))
            {
                $registrado_por = "Chefia/RH"; // incluido ou alterado por servidor do RH e/ou Chefia
            }
            else if (!empty($dados->siaperh))
            {
                $registrado_por = "RH"; // incluido ou alterado por servidor do RH
            }
            else if (!empty($dados->matchef))
            {
                $registrado_por = "Chefia"; // incluido ou alterado por Chefia
            }
            else
            {
                // gravado por servidor/estagiário no momento do registro da frequencia
                $registrado_por = ($situacao_cadastral == '66' ? "Estagiário" : "Servidor" );
            }
            break;

        case 'A':
            if (!empty($dados->matchef) && !empty($dados->siaperh))
            {
                $registrado_por = "Chefia/RH"; // incluido ou alterado por servidor do RH e/ou Chefia
            }
            else if (!empty($dados->siaperh))
            {
                $registrado_por = "RH"; // incluido ou alterado por servidor do RH
            }
            else
            {
                $registrado_por = "Chefia"; // incluido ou alterado por Chefia
            }
            break;

        case 'R':
            $registrado_por = "RH"; // incluido ou alterado por servidor do RH
            break;

        case 'H':
            $registrado_por = "RH (Hist)"; // incluido ou alterado por servidor do RH através do histórico
            break;

        case 'C':
            $registrado_por = "Chefia"; // incluido ou alterado por Chefia imediata
            break;

        case 'X':
            $registrado_por = (in_array($dados->oco,$ocorrencias) ? "SISREF" : "SIAPECAD"); // incluido pelo sistema
            break;

        case 'W':
            $registrado_por = "WSSIAPE"; // incluido por carga do SIAPENET via WSSIAPE
            break;

        default:
            $registrado_por = "";
            break;
    }
    return $registrado_por;

}

/**  @Function
 * +--------------------------------------------------------------------+
 * |                                                                    |
 * | Função.: horas_trabalhadas_ate_o_momento                           |
 * | Retorna: Calcula as horas trabalhadas ate o momento                |
 * |          e exibe na pagina de entrada                              |
 * | Autor..: Edinalvo Rosa                                             |
 * | Exemplo:                                                           |
 * |   horas_trabalhadas_ate_o_momento('1234567','09-10-2010');         |
 * |                                                                    |
 * +--------------------------------------------------------------------+
 * */
function horas_trabalhadas_ate_o_momento($sMatricula = '', $dia = '')
{
    $sMatricula = getNovaMatriculaBySiape($sMatricula);

    $horas_trabalhadas = '';

    if ($sMatricula != '' && $dia != '')
    {
        // competencia
        $comp = dataMes($dia) . dataAno($dia);

        // Verifica Fuso Horário e/ou Horário de verão
        $vHoras = horario_de_verao($dia);

        $oDBase = new DataBase('PDO');

        $res = $oDBase->query(
            'SELECT pto.entra, pto.intini, pto.intsai, pto.sai
				FROM ponto' . $comp . ' AS pto
				WHERE pto.siape = :matricula AND pto.dia = :dia AND pto.entra <> "00:00:00" ', array(
            array(':matricula', $sMatricula, PDO::PARAM_STR),
            array(':dia', conv_data($dia), PDO::PARAM_STR)
            )
        );

        if ($res)
        {
            $oPonto = $oDBase->fetch_object();
            $entra  = time_to_sec($oPonto->entra);
            $intini = time_to_sec($oPonto->intini);
            $intsai = time_to_sec($oPonto->intsai);
            $sai    = time_to_sec($oPonto->sai);

            $entra  = ($entra  == 0 ? time_to_sec($vHoras) : $entra);
            $intini = ($intini == 0 ? time_to_sec($vHoras) : $intini);
            $intsai = ($intsai == 0 ? time_to_sec($vHoras) : $intsai);
            $sai    = ($sai    == 0 ? time_to_sec($vHoras) : $sai);

            $manhan = ($intini > $entra  ? ($intini - $entra) : 0);
            $tarde  = ($sai    > $intsai ? ($sai - $intsai)   : 0);
            $dif    = formata_jornada_para_hhmm(sec_to_time($manhan + $tarde));

            $tempo = explode(':', $dif); // $tempo[0] => horas, $tempo[1] => minutos
            $hh    = $tempo[0];
            $mm    = $tempo[1];

            //if (time_to_sec($mm.':00') == 0)
            //{
            $mm      = (time_to_sec('00:' . $mm) <= time_to_sec('00:15') ? '00' : $mm);
            //}
            $hh_trab = (time_to_sec($hh . ':00') >= time_to_sec('10:00') ? '10' : $hh);
            $mm_trab = (time_to_sec($hh . ':00') >= time_to_sec('10:00') ? '00' : $mm);
            $hh      = time_to_sec($hh_trab . ':00');
            $mm      = time_to_sec('00:' . $mm_trab);

            if (($hh == 0 && $mm == 0) || $entra == '00:00:00')
            {
                $horas_trabalhadas = "";
            }
            else
            {
                //TODO: Horas trabalhadas at o momento old
                $horas   = ($hh > time_to_sec('01:00') ? " horas" : " hora");
                $artigo  = ($hh == 0 || $mm == 0 ? "" : " e ");
                $minutos = ($mm > time_to_sec('00:01') ? " minutos" : " minuto");

                $horas_trabalhadas = ($hh == 0 ? "" : $hh_trab . $horas)
                    . $artigo . ($mm == 0 ? "" : $mm_trab . $minutos);
            }
        }

        // libera memoria e fecha a conexão
        $oDBase->free_result();
        $oDBase->close();
    }

    return $horas_trabalhadas;

}

function horas_trabalhadas_ate_momento_segundos($sMatricula = '', $dia = ''){
    $sMatricula = getNovaMatriculaBySiape($sMatricula);

    $horas_trabalhadas = 0;

    if ($sMatricula != '' && $dia != '')
    {
        // competencia
        $comp = dataMes($dia) . dataAno($dia);

        // Verifica Fuso Horário e/ou Horário de verão
        $vHoras = horario_de_verao($dia);

        $oDBase = new DataBase('PDO');

        $res = $oDBase->query(
            'SELECT pto.entra, pto.intini, pto.intsai, pto.sai
				FROM ponto' . $comp . ' AS pto
				WHERE pto.siape = :matricula AND pto.dia = :dia AND pto.entra <> "00:00:00" ', array(
            array(':matricula', $sMatricula, PDO::PARAM_STR),
            array(':dia', conv_data($dia), PDO::PARAM_STR)
            )
        );

        if ($res)
        {
            $oPonto = $oDBase->fetch_object();
            $entra  = time_to_sec($oPonto->entra);
            $intini = time_to_sec($oPonto->intini);
            $intsai = time_to_sec($oPonto->intsai);
            $sai    = time_to_sec($oPonto->sai);

            $entra  = ($entra  == 0 ? time_to_sec($vHoras) : $entra);
            $intini = ($intini == 0 ? time_to_sec($vHoras) : $intini);
            $intsai = ($intsai == 0 ? time_to_sec($vHoras) : $intsai);
            $sai    = ($sai    == 0 ? time_to_sec($vHoras) : $sai);

            $manhan = ($intini > $entra  ? ($intini - $entra) : 0);
            $tarde  = ($sai    > $intsai ? ($sai - $intsai)   : 0);
            return $manhan + $tarde;
        }

        // libera memoria e fecha a conexão
        $oDBase->free_result();
        $oDBase->close();
    }

    return $horas_trabalhadas;
}


/**  @Function
 * +--------------------------------------------------------------------+
 * | subtrai/soma horas para ajuste de fuso horário                     |
 * | hora no formato hh:mm:ss                                           |
 * +--------------------------------------------------------------------+
 * */
function opera_hora_atual($horasSubtrair = "0", $formato = "H:i:s")
{
    settype($horasSubtrair, "integer");
    $horasAtual       = strftime("%H:%M:%S", time());
    $vetHoras         = explode(":", $horasAtual);
    $horario_alterado = mktime($vetHoras[0] + $horasSubtrair, $vetHoras[1], $vetHoras[2], 0);
    return date($formato, $horario_alterado);

}

/**  @Function
 * +--------------------------------------------------------------------+
 * | Altera o horario informado pelo servidor de aplicações de          |
 * | acordo com o fuso horário da localidade e/ou horário de verão      |
 * +--------------------------------------------------------------------+
 * */
function horario_de_verao($dia = '', $lotacao = '')
{
    // Mes e ano
    $dia = (empty($dia) ? date('Y-m-d') : conv_data($dia));

    // unidade para verificar o
    // fuso horario / horário de verão
    $lotacao = (empty($lotacao) ? $_SESSION['sLotacao'] : $lotacao);

    // instancia banco de dados
    $oDBase = new DataBase('PDO');

    // pega informações sobre o fuso horário, por unidade
    $oDBase->query('
    SELECT
        (und.fuso_horario - (IF(und.horario_verao="N" AND IF(IFNULL(hverao.hverao_inicio,"N")="N","N","S")="S",1,0))) AS fuso_horario
    FROM
        tabsetor AS und
    LEFT JOIN
        tabhorario_verao AS hverao
            ON (DATE_FORMAT(NOW(),"%Y-%m-%d") >= hverao.hverao_inicio AND DATE_FORMAT(NOW(),"%Y-%m-%d") <= hverao.hverao_fim)
    WHERE
        und.codigo = :lotacao
    ',
    array(
        array(":lotacao", $lotacao, PDO::PARAM_STR)
    ));
    $fuso_horario = $oDBase->fetch_object()->fuso_horario;

    // horário de verão
    if ($fuso_horario == '')
    {
        $fuso_horario = 0;
    }
//        echo "->>>".$fuso_horario;
//        die();
    $vHoras = opera_hora_atual($fuso_horario);

    return $vHoras;
}

/**  @Function
 * +--------------------------------------------------------------------+
 * |                                                                    |
 * | Função.: mensagens_comunicacao_social                              |
 * | Retorna: Exibe mensagens da Comunicação Social do INSS             |
 * | Autor..: Edinalvo Rosa                                             |
 * |                                                                    |
 * +--------------------------------------------------------------------+
 * */
function mensagens_comunicacao_social($nMinutos = 3)
{
    // Mensagem da Comunicação Social (Presidência do INSS)
    $sql        = "SELECT inicio, fim, texto, unidade_destino FROM tab_sisref WHERE ((NOW() >= inicio) AND (NOW() <= fim)) ORDER BY prioridade,inicio ";
    $dados      = mysql_queryCache_laco($sql, "comsocialmsg", $nMinutos * 60);
    $qtd_slides = count($dados);

    // monta slide e exibe
	if ($qtd_slides > 0)
	{
		$contador = 0;
		$mensagem = '';

		for ($x=0; $x < $qtd_slides; $x++)
		{
			$unidade_grupo = ($dados[$x]['unidade_destino']=='' ? "" : "," . $dados[$x]['unidade_destino']);
			$unidade_pesquisar = "," . $_SESSION['sLotacao'];

			$cargo_grupo = ($dados[$x]['cargo_destino']=='' ? "" : "," . $dados[$x]['cargo_destino']);
			$cargo_pesquisar = "," . $_SESSION['sCargo'];

			if (($unidade_grupo == '' && $cargo_grupo == '')
                || ($unidade_grupo == ',PM' && substr_count($cargo_grupo,$cargo_pesquisar) > 0)
                || ($cargo_grupo == '' && substr_count($unidade_grupo,$unidade_pesquisar) > 0)
                || ($unidade_grupo == '' && substr_count($cargo_grupo,$cargo_pesquisar) > 0)
                || (substr_count($unidade_grupo,$unidade_pesquisar) > 0 && substr_count($cargo_grupo,$cargo_pesquisar) > 0))
			{
				$contador++;
                $texto_mensagem = html_entity_decode($dados[$x]['texto']);
                $texto_mensagem = str_replace("href"," target='blank' href",$texto_mensagem);
                $texto_mensagem = str_replace('"',"'",$texto_mensagem);
				$mensagem .= "<div class=\"swiper-slide\">" . $texto_mensagem . "</div>";
			}
		}

		if ($contador > 0)
		{
			$html = "
            <!-- Link Swiper's CSS -->
            <link rel=\"stylesheet\" href=\"js/slideshow/dist/css/swiper.min.css\">
            <link rel=\"stylesheet\" href=\"js/slideshow/app_slides.css\">

            <!-- Add Pagination -->
            <div class=\"swiper-pagination\"></div>
            <br>

            <!-- Swiper -->
            <div class=\"swiper-container\">
                <div class=\"swiper-wrapper\">
                    " . $mensagem . "
                </div>
                <!-- Add Arrows -->
                <!--
                <div class=\"swiper-button-next\"></div>
                <div class=\"swiper-button-prev\"></div>
                 -->
            </div>

            <!-- Swiper JS -->
            <script src=\"js/slideshow/dist/js/swiper.min.js\"></script>

            <!-- Initialize Swiper -->
            <script> var contador = " . $contador . "; </script>
            <script src=\"js/slideshow/app_slides.config.js\"></script>
            ";
		}
	}

    echo $html;
}

/**  @Function
 * +--------------------------------------------------------------------+
 * |                                                                    |
 * | Função.: mysql_queryCache_laco                                     |
 * |                                                                    |
 * +--------------------------------------------------------------------+
 * */
function mysql_queryCache_laco($consulta, $chave, $tempo = 2)
{
    /*
      $mem = new MemCache;
      $mem->connect("127.0.0.1");

      $dados = $mem->get($chave);
      if ($dados === false)
      {
     */
    // instancia a conexao a base de dados
    $oDBase = new DataBase('PDO');

    // ATENÇÃO: até o momento, a query recebida não possui nenhum parâmetro.
    // Se vier a receber, precisará de tratamento específico para que o parâmetro seja enviado para
    // o método query abaixo.
    $result = $oDBase->query($consulta);
    $dados  = array();

    if ($result)
    {
        for ($i = 0; $i < $oDBase->num_rows(); $i++)
        {
            $dados[$i] = $oDBase->fetch_assoc();
        }
    }
    $oDBase->free_result();
    $oDBase->close();
    /*
      $mem->set($chave, $dados, 0, $tempo * 60);
      }
      $mem->close();
     */
    return $dados;

}

/**  @Function
 * +--------------------------------------------------------------------+
 * |                                                                    |
 * | Função.: mysql_queryCache                                          |
 * |                                                                    |
 * +--------------------------------------------------------------------+
 * */
function mysql_queryCache($consulta, $chave, $tempo = 2)
{
    // instancia a conexao a base de dados
    $oDBase = new DataBase('PDO');

    /*
      $chave = $chave.date("Ymd");

      $mem = new MemCache;
      $mem->connect("127.0.0.1");

      $dados = $mem->get($chave);
      if ($dados === false)
      {
     */
    $query = $oDBase->query($consulta);
    $dados = $oDBase->fetch_assoc();
    /*
      $mem->set($chave, $dados, 0, $tempo * 60);
      }
      memcache_flush($mem);
     */
    return $dados;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : formata_jornada_para_hhmm                    |
 * | @description : Testa se a jornada foi passada com, por ex., |
 * |                40 (horas semanais) ou 08:00 (horas diárias) |
 * |                retorna horas para o dia, ex. 08:00, 06:00.  |
 * |                                                             |
 * | @param  : <string>  - $jornada                              |
 * |                       jornada de trabalho                   |
 * |           <integer> - $jornada                              |
 * |                       jornada de trabalho                   |
 * | @return : <string>  - jornada no formato hh:mm              |
 * | @usage  : $jnd = formata_jornada_para_hhmm('08:00');        |
 * |        ou $jnd = formata_jornada_para_hhmm(40);             |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function formata_jornada_para_hhmm($jornada = '00:00')
{
    $jornada = ltrim(rtrim($jornada));

    $intJornada = $jornada;
    settype($intJornada, "integer");

    $tipo = explode(':', $jornada);

    if (count($tipo) == 1 && $intJornada > 9)
    {
        $horas   = floor($tipo[0] / 5);
        $minutos = ((($tipo[0] / 5) - $horas) * 60);
        $jornada = substr("00" . $horas, -2) . ':' . substr("00" . $minutos, -2);
    }
    elseif (count($tipo) > 1)
    {
        $hora = $tipo[0];
        settype($hora, "integer");

        if ($hora > 99)
        {
            $hora_pad  = str_pad($tipo[0], strlen(trim($tipo[0])), "0", STR_PAD_LEFT);
            $substring = substr($hora_pad, -(strlen(trim($tipo[0]))));
            $jornada   = $substring . ':' . substr("00".$tipo[1], -2);
        }
        else
        {
            $jornada = substr("00".$tipo[0], -2) . ':' . substr("00".$tipo[1], -2);
        }
    }
    else
    {
        $jornada = substr("00" . $jornada . ":00", -5);
    }
    return $jornada;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : jornada_diaria_para_semanal                  |
 * | @description : Testa se a jornada foi passada com, por ex., |
 * |                40 (horas semanais) ou 08:00 (horas diárias) |
 * |                retorna horas semanais, ex. 40, 30.          |
 * |                                                             |
 * | @param  : <string/integer> - $jornada                       |
 * |                              jornada de trabalho            |
 * | @return : <integer> - jornada semanal                       |
 * | @usage  : $jnd = jornada_diaria_para_semanal('08:00');      |
 * |        ou $jnd = jornada_diaria_para_semanal(40);           |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function jornada_diaria_para_semanal($jornada = '08:00')
{
    $jornada = ltrim(rtrim($jornada));

    $intJornada = $jornada;
    settype($intJornada, "integer");

    $tipo = explode(':', $jornada);

    if (count($tipo) == 1 && $intJornada > 9)
    {
        // mantem a jornada informada
    }
    elseif (count($tipo) > 1)
    {
        $jornada = intval((($tipo[0] * 60) + $tipo[1]) / 60) * 5;
    }
    else
    {
        $jornada = intval($tipo[0] * 5);
    }

    return $jornada;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : jornada_maxima_no_dia                        |
 * | @description : Limita a jornada do dia a no maximo a soma   |
 * |                de duas hora a jornada do cargo              |
 * |                                                             |
 * | @param  : <string>  - $jornada                              |
 * |                       jornada de trabalho                   |
 * |           <integer> - $jornada                              |
 * |                       jornada de trabalho                   |
 * | @return : <string>  - jornada no formato hh:mm              |
 * | @usage  : $jnd = jornada_maxima_no_dia('08:00');            |
 * |        ou $jnd = jornada_maxima_no_dia(40);                 |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : formata_jornada_para_hhmm                     |
 * +-------------------------------------------------------------+
 * */
function jornada_maxima_no_dia($jornada = '00:00')
{
    $jornada        = formata_jornada_para_hhmm($jornada);
    $aJornada       = explode(":", $jornada);
    $nJornada       = mktime($aJornada[0] + 2, $aJornada[1], 0, 0);
    $jornada_maxima = date('H:i', $nJornada);
    return $jornada_maxima;

}

/**  @Function
 * +--------------------------------------------------------------------+
 * | @function    : soNumeros                                           |
 * | @description : Retira qualquer caracter que não seja numérico      |
 * |    $v  - Valor a ser tratado                                       |
 * | Retorna   : String só com números                                  |
 * | Autor     : Edinalvo Rosa                                          |
 * +--------------------------------------------------------------------+
 * */
function soNumeros($v)
{
    return preg_replace("/\D/", "", $v);

}

/**  @Function
 * +--------------------------------------------------------------------+
 * | @function    : soLetras                                            |
 * | @description : Retira qualquer caracter que não seja letras        |
 * |    $v  - Valor a ser tratado                                       |
 * | Retorna   : String só com letras                                   |
 * | Autor     : Edinalvo Rosa                                          |
 * +--------------------------------------------------------------------+
 * */
function soLetras($v)
{
    return preg_replace("/[^[:alpha:]]/i", "", $v);

}

/**  @Function
 * +----------------------------------------------------------------------+
 * | @function    : soNumeros_letras                                      |
 * | @description : Retira qualquer caracter que não seja número ou letra |
 * |    $v  - Valor a ser tratado                                         |
 * | Retorna   : String só com números e letras                           |
 * | Autor     : Edinalvo Rosa                                            |
 * +----------------------------------------------------------------------+
 * */
function soNumeros_letras($string)
{
    $string = preg_replace("/[\.\,\-\\\ \/\ ]/i", "", $string);
    return $string;

}

/**  @Function
 * +----------------------------------------------------------------------+
 * | @function    : horarioNulo                                           |
 * | @description : Verifica se o valor eh horário e zero/vazio           |
 * |    $string  - Valor a ser tratado                                    |
 * | Retorna   : true  - não eh horário e/ou igual zero/vazio             |
 * |             false - eh horário e diferente zero/vazio                |
 * | Autor     : Edinalvo Rosa                                            |
 * +----------------------------------------------------------------------+
 * */
function horarioNulo($string)
{
    $string = trim($string);
    $str    = preg_replace("/[^0-9]/i", "", $string);
    settype($str, 'integer');
    return empty($str);

}

/**  @Function
 * +--------------------------------------------------------------------+
 * | @function    : uc_words                                            |
 * | @description : Coloca as iniciais maiusculas                       |
 * |    $v  - Valor a ser tratado                                       |
 * | Retorna   : String com as iniciais maiusculas                      |
 * | Autor     : Edinalvo Rosa                                          |
 * +--------------------------------------------------------------------+
 * */
function uc_words($v)
{
    $str = array(" Da ", " Das ", " De ", " Do ", " Dos ", " E ");
    $v   = ucwords(strtolower($v));
    for ($x = 0; $x < count($str); $x++)
    {
        $v = str_replace($str[$x], strtolower($str[$x]), $v);
    }
    return $v;

}

/**  @Function
 * +--------------------------------------------------------------------+
 * | @function    : nome_sobrenome                                      |
 * +--------------------------------------------------------------------+
 * */
function nome_sobrenome($v)
{
    $nome = explode(" ", ltrim(rtrim($v)));
    return ($nome[0] . " " . $nome[count($nome) - 1]);

}

/**  @Function
 * +--------------------------------------------------------------------+
 * | @function    : somar_dias_uteis                                               |
 * | @description : Soma dias uteis à data informada                               |
 * | Retorna   : Nova data                                                      |
 * | Autor     : Leandro Vieira Pinho                                           |
 * |             http://leandro.w3invent.com.br                                 |
 * | Alterações: Edinalvo Rosa (06-08-2007)                                     |
 * +--------------------------------------------------------------------+
 * */
function somar_dias_uteis($str_data = '', $int_qtd_dias_somar = 7)
{
    // Caso seja informado uma data do MySQL do tipo DATETIME - aaaa-mm-dd 00:00:00
    // Transforma para DATE - aaaa-mm-dd
    $str_data = substr($str_data, 0, 10);

    // Se a data estiver no formato brasileiro: dd/mm/aaaa
    // Converte-a para o padrão americano: aaaa-mm-dd
    if (preg_match("@/@", $str_data) == 1)
    {
        $str_data = implode("-", array_reverse(explode("/", $str_data)));
    }
    $count_days         = 0;
    $int_qtd_dias_uteis = 0;
    while ($int_qtd_dias_uteis < $int_qtd_dias_somar)
    {
        $count_days++;
        $datanova       = date('Y-m-d', strtotime('+' . $count_days . ' day', strtotime($str_data)));
        $dias_da_semana = date('w', strtotime($datanova));
        if ($dias_da_semana != '0' && $dias_da_semana != '6')
        {
            $int_qtd_dias_uteis++;
        }
        echo $datanova . " : " . $count_days . " :" . $dias_da_semana . " : " . $int_qtd_dias_uteis . " : " . date('l', strtotime($datanova)) . "<br>";
    }
    return date('d/m/Y', strtotime('+' . $count_days . ' day', strtotime($str_data)));

}

/**  @Function
 * +--------------------------------------------------------------------+
 * | Função     : assinatura                                            |
 * | Descricao  : Gera a assinatura eletrônica                          |
 * | Detalhes   : Concatena siape+nome+cpf, executa md5() sobre o       |
 * |              resultado, acrescenta mais um zero a esquerda da      |
 * |              matricula. Divide o resultado obtido em 8 blocos,     |
 * |              à frente de cada bloco colocamos um digito da         |
 * |              matricula                                             |
 * | Parametros : [<$siape>] - matrícula siape do servidor              |
 * |              [<$nome>] - nome do servidor                          |
 * |              [<$cpf>] - cpf do servidor                            |
 * | Retorna    : string com a autenticação eletrônica                  |
 * | Exemplo    : autenticacao('0881888','JOAO DA SILVA','99232134576') |
 * |              // Assinatura retornada                               |
 * |              // 05208.09413.8e76d.828fe.1d77b.89e64.8168b.8a4a6    |
 * | Autor     : Edinalvo Rosa                                          |
 * +--------------------------------------------------------------------+
 * */
function assinatura($siape, $nome, $cpf)
{
    $vr         = md5($siape . $nome . $cpf);
    $assinatura = '0';
    for ($i = 0; $i < 8; $i++)
    {
        $x          = ($i == 0 ? $i : $x + 4);
        $assinatura .= substr($vr, $x, 4) . '.' . substr($siape, $i, 1);
    }
    return substr($assinatura, 0, 47);

}

/**  @Function
 * +--------------------------------------------------------------------+
 * | Função     : autenticacao                                          |
 * | Descricao  : Gera autenticacao eletrônica                          |
 * | Detalhes   : Executa md5() sobre o texto, acrescenta mais um zero  |
 * |              a esquerda da matricula. Divide o resultado obtido    |
 * |              em 8 blocos, à frente de cada bloco colocamos um      |
 * |              digito da matricula                                   |
 * | Parametros : [<$siape>] - matrícula siape do servidor              |
 * |              [<$texto>] - texto para autenticar                    |
 * | Retorna    : string com a autenticação eletrônica                  |
 * | Exemplo    : autenticacao('0881888','Texto conteudo do documento') |
 * |              // Autenticação retornada                             |
 * |              // 05208.09413.8e76d.828fe.1d77b.89e64.8168b.8a4a6    |
 * | Autor     : Edinalvo Rosa                                          |
 * +--------------------------------------------------------------------+
 * */
function autenticacao($siape, $texto)
{
    $vr           = md5($texto);
    $autenticacao = '0';
    for ($i = 0; $i < 8; $i++)
    {
        $x            = ($i == 0 ? $i : $x + 4);
        $autenticacao .= substr($vr, $x, 4) . '.' . substr($siape, $i, 1);
    }
    return substr($autenticacao, 0, 47);

}

/**  @Function
 * +--------------------------------------------------------------------+
 * | @function    : alltrim                                             |
 * | @description : elimina os espacos em branco do inicio e do fim de  |
 * |             uma cadeia de caracteres                               |
 * | @param  : [<$str>]     - cadeia de caracteres                      |
 * |           [<$strtrim>] - caracteres a serem eliminados             |
 * | Retorna   : string sem os brancos do inicio e do fim               |
 * | Exemplo   : $str = alltrim(" x "); ou                              |
 * |             $str = alltrim("-x-","-");                             |
 * |             //resulta em $str = "x"                                |
 * | Autor     : Edinalvo Rosa                                          |
 * +--------------------------------------------------------------------+
 * */
function alltrim($str, $strtrim = '')
{
    return ltrim(rtrim($str, $strtrim), $strtrim);

}

/**  @Function
 * +--------------------------------------------------------------------+
 * | @function    : right                                               |
 * | @description : substring aa direita                                |
 * | @param  : [<$str>] - cadeia de caracteres                          |
 * |           [<$tam>] - tamanho da substring                          |
 * | Retorna   : os ultimos caracteres aa direita no tamanho indicado   |
 * | Exemplo   : $str = right("abcde",2);                               |
 * |             //resulta em $str = "de"                               |
 * | Autor     : Edinalvo Rosa                                          |
 * +--------------------------------------------------------------------+
 * */
function right($str, $tam = '')
{
    return substr(ltrim(rtrim($str, $strtrim), $strtrim), -($tam));

}

/**  @Function
 * +--------------------------------------------------------------------+
 * | @function    : left                                                |
 * | @description : substring aa esquerda                               |
 * | @param  : [<$str>] - cadeia de caracteres                          |
 * |           [<$tam>] - tamanho da substring                          |
 * | Retorna   : os primeiros caracteres aa esquerda no tamanho indicado|
 * | Exemplo   : $str = left("abcde",2);                                |
 * |             //resulta em $str = "ab"                               |
 * | Autor     : Edinalvo Rosa                                          |
 * +--------------------------------------------------------------------+
 * */
function left($str, $tam = '')
{
    return substr(ltrim(rtrim($str, $strtrim), $strtrim), 0, $tam);

}


/**  @Function
 * +--------------------------------------------------------------------+
 * | Classe.....: valida                                                    |
 * +--------------------------------------------------------------------+
 * */
class valida
{

    var $exibe_todas;
    var $mensagem;
    var $exibe_mensagem;
    var $destino;
    var $voltar;
    var $mes;

	var $siape;

    ##
    # Constructor
    #
    function valida()
    {
        $this->setExibeMensagem(false);
        $this->setExibeTodas(false);
        $this->initMensagem();
        $this->setDestino('');
        $this->setVoltar(1);
        $this->setMes(date('m'));
    }

    ##
    # define se serão exibida todas mensagem após todas
    # as validações serem realizadas (havendo mais de uma)
    #
    function setExibeTodas($exibe_todas = '')
    {
        $this->exibe_todas = $exibe_todas;
    }

    function getExibeTodas()
    {
        return $this->exibe_todas;
    }

    ##
    # define mensagem
    #
    function initMensagem()
    {
        $this->mensagem = '';
    }

    function setMensagem($mensagem = '')
    {
        $this->mensagem .= $mensagem;
    }

    function getMensagem()
    {
        return $this->mensagem;
    }

    ##
    # define se será exibida mensagem
    #
    function setExibeMensagem($exibe_mensagem = '')
    {
        $this->exibe_mensagem = $exibe_mensagem;
    }

    function getExibeMensagem()
    {
        return $this->exibe_mensagem;
    }

    ##
    # define o script para onde retornar
    #
    function setDestino($destino = '')
    {
        $this->destino = $destino;
    }

    function getDestino()
    {
        return $this->destino;
    }

    ##
    # define o número de páginas para voltrar no histórico
    #
    function setVoltar($voltar = '')
    {
        $this->voltar = $voltar;
    }

    function getVoltar()
    {
        return $this->voltar;
    }

    ##
    # define o mes
    #
    function setMes($mes = '')
    {
        $this->mes = $mes;
    }

    function getMes()
    {
        return $this->mes;
    }

    ##
    # Testa se a matrícula informada é válida
    #
    function siape($siape = '', $msg_extra = '')
    {
        $mensagem = '';
        $siape    = trim($siape);

        $siape_invalidos = array(
            "0000000",
            "1111111",
            "2222222x",
            "3333333",
            "4444444",
            "5555555",
            "6666666",
            "7777777",
            "8888888",
            "9999999",
            "000000000000",
            "111111111111",
            "222222222222",
            "333333333333",
            "444444444444",
            "555555555555",
            "666666666666",
            "777777777777",
            "888888888888",
            "999999999999"
        );

        if (strlen($siape) < 7)
        {
            $mensagem = 'É obrigatório informar a matrícula' . $msg_extra . ' com 7 caracteres!';
        }
        else if (strlen($siape) > 7 && strlen($siape) < 12)
        {
            $mensagem = 'É obrigatório informar a matrícula' . $msg_extra . ' com 12 caracteres!';
        }
        else if (in_array($siape, $siape_invalidos))
        {
            $mensagem = 'Matrícula' . $msg_extra . ' informada, inválida!';
        }

        return ($this->exibeUmaMensagem($mensagem));
    }

    ##
    # Testa se a matricula do usuario é a mesma dos dados que serão alterados
    #
    function siaperh($siape = '')
    {
        $usuario = $_SESSION['sMatricula'];
        if ($siape == $usuario)
        {
            return ($this->exibeUmaMensagem('Você não pode alterar sua própria frequência!'));
        }

        return true;
    }

    ##
    # Testa se a matricula do usuario é a mesma do responsável
    # Testa se a matricula do responsável é a mesma dos dados que serão alterados
    #
    function siapeResponsavel($siape = '', $responsavel = '')
    {
        $usuario = $_SESSION['sMatricula'];
        if ($usuario == $responsavel)
        {
            return ($this->exibeUmaMensagem('Você não pode ser o solicitante da alteração na frequência!'));
        }

        if ($siape == $responsavel)
        {
            return ($this->exibeUmaMensagem('O solicitante não pode ser o mesmo da frequência a ser alterada!'));
        }

        return true;
    }

    ##
    # Testa se o mes informado é válido
    #
    function data($dataform='', $msg=null)
    {
        if (validaData($dataform) == false)
        {
            return ($this->exibeUmaMensagem( $msg ));
        }

        return true;
    }

    ##
    # Testa se o mes informado é válido
    #
    function mes($mes = '')
    {
        $mensagem = '';
        $destino  = $this->getDestino();
        $mes      = ltrim(rtrim($mes));

        $this->setMes($mes);

        if (strlen($mes) < 2 || $mes < '01' || $mes > '12')
        {
            if (strlen($mes) < 2)
            {
                $mensagem = 'Mes incorreto!!\nInforme com dois caracteres!';
            }
            if ($mes < '01' || $mes > '12')
            {
                $mensagem = 'Mes incorreto!!\nInforme entre 1 e 12!';
            }
            return ($this->exibeUmaMensagem($mensagem));
        }

        return true;
    }

    ##
    # Testa se o ano informado é válido
    #
    function ano($ano = '')
    {
        $ano        = ltrim(rtrim($ano));
        $ano_hoje   = date('Y');
        $anomesHoje = date('Ym');
        $anomesForm = $ano . $this->getMes();
        if ((strlen($ano) < 4) || ($ano > $ano_hoje) || ($ano < '2009') || ($anomesForm > $anomesHoje))
        {
            return ($this->exibeUmaMensagem("Ano inválido!"));
        }

        return true;
    }

    ##
    # Testa se a competência informada é válida
    #
    function competencia($mes = '', $ano = '')
    {
        $anomesHoje = date('Ym');
        $anomesForm = ltrim(rtrim($ano)) . ltrim(rtrim($mes));
        if ($this->mes($mes) == false || $this->ano($ano) == false || $anomesForm > $anomesHoje)
        {
            return ($this->exibeUmaMensagem("Competência inválida!"));
        }

        return true;
    }

    ##
    # Testa se a upag do usuario é a mesma do usuário que irá alterar os dados
    #
    function upagrh($upag = '')
    {
        $upag_usuario = $_SESSION['upag'];
        if ($upag != $upag_usuario)
        {
            return ($this->exibeUmaMensagem("Você não pode alterar dados de servidor de outra UPAG !"));
        }

        return true;
    }

    ##
    # Testa ocorrência
    #
    function ocorrencia($str = '', $mensagem = '')
    {
        $str = sonumeros($str);
        if ($str == '' || strlen($str) < 5)
        {
            $mensagem = ($mensagem == '' ? "Ocorrência inválida/não informada!" : $mensagem);

            return ($this->exibeUmaMensagem($mensagem));
        }

        return true;
    }

    ##
    # Testa horario
    #
    function horario($str = '', $mensagem = '')
    {
        $str = sonumeros($str); // 00:00:00 para 000000
        if (empty($str) || $str == '000000' || strlen($str) < 6)
        {
            $mensagem = ($mensagem == '' ? "Horário inválido/não informado!" : $mensagem);

            return ($this->exibeUmaMensagem($mensagem));
        }

        return true;
    }

    ##
    # Exibe mensagem de erro individual
    #
    function exibeUmaMensagem($mensagem = '')
    {
        $destino = $this->getDestino();
        $this->setMensagem(($mensagem == '' ? "" : "- " . $mensagem . "<br>")); // para uso com exibeMensagem
        if ($mensagem != '')
        {
            if ($this->getExibeMensagem() == true)
            {
                if ($destino == '')
                {
                    mensagem($mensagem);
                }
                else
                {
                    mensagem($mensagem, null, 1);
                }
            }

            return false;
        }

        return true;
    }

    ##
    # Exibe mensagem de erro
    #
    function exibeMensagem($exit=true)
    {
        $destino  = $this->getDestino();
        $mensagem = trata_aspas($this->getMensagem());

        if ($mensagem != '')
        {
            if ($destino != '')
            {
                if (substr_count($destino, 'window.location.replace') > 0)
                {
                    $destino = str_replace("javascript:window.location.replace(", "", $destino);
                    $destino = str_replace(")", "", $destino);
                }
            }

            mensagem($mensagem, $destino);

            if ($exit === true)
            {
                exit();
            }
        }
    }
}


/* ----------------------------------------------------------------------------\
|  Função    :                                                                 |
\---------------------------------------------------------------------------- */
function registraLog($operacao = '', $siape = '', $nome = '', $modulo = '')
{
    $total = 0;

    if ($operacao != '')
    {
        $siape = ($siape == '' ? $_SESSION['sMatricula'] : $siape);
        $nome  = ($nome == '' ? $_SESSION['sNome'] : $nome);
        $upag  = $_SESSION['upag'];

        $ip = getIpReal();

        $vDatas   = date('Y-m-d'); // Data
        $vHoras   = horario_de_verao($vDatas); // Hora - Verifica Fuso Horário e/ou Horário de verão
        $operacao = 'Usuário ' . nome_sobrenome(addslashes($nome)) . ', ' . $siape . ', ' . $operacao; // Mensagens
        // Leitura do banco de dados
        $oDBase   = new DataBase('PDO');
        $oDBase->setMensagem('nulo');

        // grava o LOG
        $datag = $vDatas . ' ' . $vHoras;

        if ($modulo == '')
        {
            $operacao = $operacao . ', em ' . databarra($vDatas) . ' as ' . $vHoras . ' ';
            $oDBase->query("
            INSERT INTO dedo
			SET
                siape     = :siape,
				upag      = :upag,
				aconteceu = :aconteceu,
				datag     = :datag,
				ip        = :ip
            ",
            array(
                array(":siape",     $siape,    PDO::PARAM_STR),
                array(":upag",      $upag,     PDO::PARAM_STR),
                array(":aconteceu", $operacao, PDO::PARAM_STR),
                array(":datag",     $datag,    PDO::PARAM_STR),
                array(":ip",        $ip,       PDO::PARAM_STR),
            ));
        }
        else
        {
            $oDBase->query("
            INSERT INTO acesso_aos_modulos
			SET
                siape     = :siape,
				upag      = :upag,
				aconteceu = :aconteceu,
				datag     = :datag,
				ip        = :ip,
				modulo    = :modulo
            ",
            array(
                array(":siape", $siape, PDO::PARAM_STR),
                array(":upag", $upag, PDO::PARAM_STR),
                array(":aconteceu", $operacao, PDO::PARAM_STR),
                array(":datag", $datag, PDO::PARAM_STR),
                array(":ip", $ip, PDO::PARAM_STR),
                array(":modulo", $modulo, PDO::PARAM_STR)
            ));
        }
        $total = $oDBase->affected_rows();

        // libera memoria
        $oDBase->free_result();
        $oDBase->close();
    }

    return ($total > 0);
}


/* ----------------------------------------------------------------------------\
|  Função    :                                                                 |
\---------------------------------------------------------------------------- */
function registraRegHorario($operacao)
{
    $sSiape   = $_SESSION['sMatricula'];
    $sLotacao = $_SESSION['sLotacao'];

    $ip = getIpReal();

    ## Grava o LOG
    #
    $oDb = new DataBase('PDO');
    //$oDb->setMensagem( 'nulo' );

    $oDb->query("INSERT INTO control_reghorario (ip,siape,lotacao,datahora,texto) VALUES ('$ip','$sSiape','$sLotacao',NOW(),'" . addslashes($operacao) . "') ");
}

function valoresParametros($destino = '')
{
    if (is_array($_SESSION['sChaveCriterio']))
    {
        $contar = 0;
        foreach ($_SESSION['sChaveCriterio'] as $chave => $valor)
        {
            $destino .= ($contar == 0 ? '?' : '&') . $chave . "=" . $valor;
            $contar++;
        }
    }

    return $destino;
}

function tempo_transcorrido($inicio = "", $fim = "")
{
    $diferenca = "";

    if ($inicio != "" && $fim != "")
    {
        if (!is_array($inicio))
        {
            $inicio = explode(":", $inicio);
        }
        if (!is_array($fim))
        {
            $fim = explode(":", $fim);
        }

        $time_inicio = (($inicio[0] * 60) * 60) + ($inicio[1] * 60) + $inicio[2];
        $time_fim    = (($fim[0] * 60) * 60) + ($fim[1] * 60) + $fim[2];

        $horas = floor((($time_fim - $time_inicio) / 60) / 60);
        if ($horas < 10)
        {
            $horas = "0$horas";
        }

        $minutos = (floor(($time_fim - $time_inicio) / 60)) - ($horas * 60);
        if ($minutos < 10)
        {
            $minutos = "0$minutos";
        }

        $segundos = ($time_fim - $time_inicio);
        $segundos = $segundos - (($horas * 60) + $minutos) * 60;
        if ($segundos < 10)
        {
            $segundos = "0$segundos";
        }

        $diferenca = "$horas:$minutos:$segundos";
    }

    return $diferenca;
}

function tempo_adiciona($hora = "", $acrescimo = 0)
{
    $nova_hora = $hora;
    if ($hora != "")
    {
        $tempo     = explode(":", $acrescimo);
        $formato   = strtotime("$hora");
        $converte  = strtotime("+ " . $tempo[0] . " hours " . $tempo[1] . " minutes " . $tempo[2] . " seconds", $formato);
        $nova_hora = date('H:i:s', $converte);
    }

    return $nova_hora;
}

function tempo_subtrai($hora = "", $subtrai = 0)
{
    $nova_hora = $hora;

    if ($hora != "")
    {
        $tempo     = explode(":", $subtrai);
        $formato   = strtotime("$hora");
        $converte  = strtotime("- " . $tempo[0] . " hours " . $tempo[1] . " minutes " . $tempo[2] . " seconds", $formato);
        $nova_hora = date('H:i:s', $converte);
    }

    return $nova_hora;
}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : ponto_facultativo                            |
 * | @description : A jornada ajustada se ponto facultativo,     |
 * |                no formato hh:mm. Verfifica se é natal,      |
 * |                ano novo ou quarta feira de cinzas. Sempre   |
 * |                retorna horas para o dia, ex. 08:00, 06:00.  |
 * |                                                             |
 * | @param  : <string>  - $dia                                  |
 * |                       dia (aaaa-mm-dd)                      |
 * | @param  : <string>  - $jornada                              |
 * |                       jornada de trabalho                   |
 * |           <integer> - $jornada                              |
 * |                       jornada de trabalho                   |
 * | @return : <string>  - jornada no formato hh:mm              |
 * | @usage  : $jnd = ponto_facultativo('01/02/2010','08:00');   |
 * |        ou $jnd = ponto_facultativo('01/02/2010',40);        |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : formata_jornada_para_hhmm                     |
 * +-------------------------------------------------------------+
 * */
function ponto_facultativo($dia, $jornada = '08:00', $ano = '', $entra = '00:00:00', $sai = '00:00:00', $iniin = '00:00:00', $fimin = '00:00:00', $matricula = '')
{
    global $mat;

    $mat = (isset($mat) ? $mat : $matricula);

    /* define a data, hora e verifica se é dia útil */
    $hoje = date("Y");
    $m    = dataMes($dia);
    $y    = dataAno($dia);
    $comp = $m . $y;

    ## instancia classe frequencia
    #
    $oFreq = new formFrequencia;
    $oFreq->setOrigem(pagina_de_origem()); // Registra informacoes em sessao
    $oFreq->setAnoHoje($hoje); // ano (data atual)
    $oFreq->setSiape($mat);    // matricula do servidor que se deseja alterar a frequencia
    $oFreq->setMes($m);        // mes que se deseja alterar a frequencia
    $oFreq->setAno($y);        // ano que se deseja alterar a frequencia
    $oFreq->setData($dia);
    $oFreq->setUsuario($_SESSION['sMatricula']); // matricula do usuario
    $oFreq->setLotacao($lot);
    $oFreq->setNomeDoArquivo('ponto' . $comp); // nome do arquivo de frequencia

    ## le dados do servidor e setor
    #
    $oFreq->loadDadosServidor();
    $oFreq->loadDadosSetor();

    ## - turno estendido
    #
    $turno_estendido = $oFreq->turnoEstendido(); // jornada
    $sTurnoEstendido = $oFreq->getTurnoEstendido(); // informa se o servidor encontra-se em unidade

    // autorizada a realizar o turno estendido
    ## ocupantes de função
    #
    $ocupaFuncao     = $oFreq->getChefiaAtiva();

    if ($ocupaFuncao == 'S')
    {
        // - Se titular da função ou em efetiva
        //   substituição, a jornada eh de 40hs
        $jornada = formata_jornada_para_hhmm(40);
    }

    ## - jornada do servidor, por cargo ou horário especial
    #  - ponto facultativo (natal, ano novo e quarta-feira de cinzas)
    #
    #  - verifica se dia ponto facultativo e atribui a jornada correta para o dia
    #
    $jornada             = $oFreq->pontoFacultativo();
    $quarta_feira_cinzas = $oFreq->getQuartaFeiraCinzas();

    ## Jornada
    #
    $jornada = right((time_to_sec($jornada) > time_to_sec($turno_estendido) && time_to_sec($turno_estendido) != 0 ? $turno_estendido : $jornada), 8);

    $jornada_dia = formata_jornada_para_hhmm($jornada);

    return $jornada_dia;
}


//////////////////////////////////////////////////////
//                                                  //
// joranda do natal e do ano novo                   //
//                                                  //
//////////////////////////////////////////////////////
function jornada_no_natal_e_ano_novo_2010($dia = '', $entra = '00:00:00', $ano = '', $sai = '00:00:00', $iint = '00:00:00', $vint = '00:00:00', $jornada = '08:00')
{
    $jnd = $jornada;

    if ($dia == $ano . '-12-24' || $dia == $ano . '-12-31')
    {
        if ($sai == "00:00:00")
        {
            echo "<div align='center'><font face='verdana' size='4'><br><br>Não consta horário do servidor no cadastro!<br>Informe o horário para poder alterar essa ocorrência!</font></div><br><br><br><br>";
            exit();
        }
        elseif (verifica_se_fimdesemana($dia) != 'S')
        {
            $saidaesp = '14:00:00';

            //calculo do intervalo do dia
            if (($iint < $saidaesp && $vint >= $saidaesp) || ($iint <= $saidaesp && $vint >= $saidaesp))
            {
                $vint = $saidaesp;
            }
            $aH1    = explode(":", $vint);
            $aH2    = explode(":", $iint);
            $hint   = mktime($aH1[0] - $aH2[0], $aH1[1] - $aH2[1], 0, 0);
            $interv = date("H:i", $hint);

            //calculo da jornada bruta do dia
            if ($entra <= $saidaesp && $sai >= $saidaesp)
            {
                $aH1  = explode(":", $saidaesp);
                $aH2  = explode(":", $entra);
                $htol = mktime($aH1[0] - $aH2[0], $aH1[1] - $aH2[1], 0, 0);
                $jesp = date("H:i", $htol);
                //
                //calculo da diferença do dia
                $af1  = explode(":", $jesp);
                $af2  = explode(":", $interv);
                $hfin = mktime($af1[0] - $af2[0], $af1[1] - $af2[1], 0, 0);
                $jesp = date("H:i", $hfin);
                $jnd  = $jesp;
            }
            elseif ($entra <= $saidaesp && $sai <= $saidaesp)
            {
                $aH1  = explode(":", $sai);
                $aH2  = explode(":", $entra);
                $htol = mktime($aH1[0] - $aH2[0], $aH1[1] - $aH2[1], 0, 0);
                $jesp = date("H:i", $htol);
                //
                //calculo da diferença do dia
                $af1  = explode(":", $jesp);
                $af2  = explode(":", $interv);
                $hfin = mktime($af1[0] - $af2[0], $af1[1] - $af2[1], 0, 0);
                $jnd  = $jesp;
            }
            else
            {
                $jesp = '00:00';
                $jnd  = $jesp;
            }

            if ($jesp > $jornada)
            {
                $jesp = $jornada;
                $jnd  = $jesp;
            }
            else
            {
                $jnd = $jesp;
            }
        }
    }

    return $jnd;
}


//////////////////////////////////////////////////////
//                                                  //
// quarta-feira de cinzas - nova sistematica (2011) //
//                                                  //
//////////////////////////////////////////////////////
function quarta_feira_de_cinzas_2011($dia = '', $qcinzas = '', $entra = '', $sai = '00:00:00', $iint = '00:00:00', $vint = '00:00:00', $jornada = '08:00', $deduz_almoco = true)
{
    $jnd = $jornada;

    if ($dia == $qcinzas)
    {
        if ($sai == "00:00:00")
        {
            echo "<div align='center'><font face='verdana' size='4'><br><br>Não consta horário do servidor no cadastro!<br>Informe o horário para poder alterar essa ocorrência!</font></div><br><br><br><br>";
            exit();
        }
        elseif (substr($qcinzas, 0, 4) <= '2010')
        {
            $jnd = '04:00';
        }
        else
        {
            //calculo da jornada bruta do dia
            $entraesp = '14:00:00';

            // se nao deduz o almoco
            // entraesp fica igual a 14:00:00
            if ($deduz_almoco == true && $vint >= $entraesp)
            {
                $entraesp = $vint;
            }

            //calculo da diferença do dia
            $aH1  = explode(":", $sai);
            $aH2  = explode(":", $entraesp);
            $htol = mktime($aH1[0] - $aH2[0], $aH1[1] - $aH2[1], 0, 0);
            $jesp = date("H:i", $htol);
            $jnd  = $jesp;

            $jornada = ($jornada == '' ? '08:00' : $jornada);

            if ($jesp > $jornada)
            {
                $jesp = $jornada;
                $jnd  = $jesp;
            }
            else
            {
                $jnd = $jesp;
            }
        }
    }

    return $jnd;
}


//////////////////////////////////////////////////////
//                                                  //
// Verifica se a data encontra-se no período de uso //
// do recesso e/ou compensação das horas utilizadas //
// do recesso de natal ou fim de ano                //
//                                                  //
//////////////////////////////////////////////////////
function dataUsoDoRecesso($data = '')
{
    return dataRecesso($data, false); // dias do recesso
}

function dataCompensacaoDoRecesso($data = '')
{
    return dataRecesso($data, true); // dias do recesso
}

function dataRecesso($data = '', $compensacao = true)
{
    $recesso = false;

    if ($data != '')
    {
        $data   = conv_data($data); // converte a data para o padrão aaaa-mm-dd
        $oDBase = new DataBase('PDO');
        $oDBase->query(
            "SELECT periodo, recesso_inicio, recesso_fim, recesso_inicio_compensacao, recesso_fim_compensacao,
				IF(:data >= recesso_inicio AND :data <= recesso_fim,'S','N') AS data_usa_recesso,
				IF(:data >= recesso_inicio_compensacao AND :data <= recesso_fim_compensacao,'S','N') AS data_compensa_recesso
			FROM tabrecesso_fimdeano
			WHERE (:data >= recesso_inicio AND :data <= recesso_fim)
				OR (:data >= recesso_inicio_compensacao AND :data <= recesso_fim_compensacao) ", array(
            array(':data', $data, PDO::PARAM_STR)
            )
        );

        if ($oDBase->num_rows() > 0)
        {
            $oRecesso = $oDBase->fetch_object();
            if ($compensacao == false)
            {
                $recesso = ($oRecesso->data_usa_recesso == 'S');
            }
            else
            {
                $recesso = ($oRecesso->data_compensa_recesso == 'S');
            }
        }
    }

    return $recesso;
}

function pega_a_competencia($dia = '', $cmd = '')
{
    if ($dia == '' || validaData($dia) == false)
    {
        $oData = new trata_datasys;
        $comp  = $oData->getCompetAnterior();
        if ($cmd == '1')
        {
            $comp = $oData->getCompet();
        }
    }
    else
    {
        $aData = data2arrayBR($dia);
        $comp  = $aData[1] . $aData[2];
    }

    return $comp;
}


//////////////////////////////////////////////////////
//                                                  //
// Diferenca entre horas                            //
//                                                  //
//////////////////////////////////////////////////////
function diferencaHoras($entra = '00:00:00', $saida = '00:00:00', $formato = 'H:i', $segundos = false)
{
    $hentra = $entra;
    $entra  = ($hentra > $saida ? $saida : $hentra );
    $saida  = ($hentra > $saida ? $hentra : $saida );
    $aH1    = explode(":", $saida);
    $aH2    = explode(":", $entra);
    $htol   = mktime($aH1[0] - $aH2[0], $aH1[1] - $aH2[1], ($segundos == true ? $aH1[2] - $aH2[2] : 0), 0);
    $jesp   = date($formato, $htol);

    return $jesp;
}


//////////////////////////////////////////////////////
//                                                  //
// Subtrai horas                                    //
//                                                  //
//////////////////////////////////////////////////////
function subtraiHoras($entra = '00:00:00', $saida = '00:00:00', $formato = 'H:i', $segundos = false)
{
    $jesp = diferencaHoras($entra, $saida, $formato, $segundos);

    return $jesp;
}


//////////////////////////////////////////////////////
//                                                  //
// Adiciona horas                                   //
//                                                  //
//////////////////////////////////////////////////////
function adicionaHoras($inicial = '00:00:00', $acrescimo = '00:00:00', $formato = 'H:i', $segundos = false)
{
    $aH1  = explode(":", $inicial);
    $aH2  = explode(":", $acrescimo);
    $htol = mktime($aH1[0] + $aH2[0], $aH1[1] + $aH2[1], ($segundos == true ? $aH1[2] + $aH2[2] : 0), 0);
    $jesp = date($formato, $htol);

    return $jesp;
}


/**
 * Converts seconds to time string
 *
 * @author     Paulo Freitas <paulofreitas dot web at gmail dot com>
 * @version    20100323
 * @copyright  2010 Paulo Freitas
 * @license    http://creativecommons.org/licenses/by-sa/3.0
 * @param      int|float $secs number of seconds to convert
 * @return     string time string formatted as 'HH:MM:SS'
 */
function sec_to_time($secs, $format = 'hhh:mm:ss')
{
    $time = '';
    $s    = round($secs);
    $m    = ($s / 60);
    $h    = ($m / 60);

    if (secs >= 360000)
    {
        $format = 'h' . $format;
    }

    switch (strtolower($format))
    {
        case 'hh':
            $time = sprintf('%02d', $h);
            break;
        case 'hh:mm':
            $time = sprintf('%02d:%02d', $h, $m % 60);
            break;
        case 'hh:mm:ss':
            $time = sprintf('%02d:%02d:%02d', $h, $m % 60, $s % 60);
            break;
        case 'mm:ss':
            $time = sprintf('%02d:%02d', $m % 60, $s % 60);
            break;
        default:
            $time = sprintf('%03d:%02d:%02d', ($m    = ($s    = round($secs)) / 60) / 60, $m % 60, $s % 60);
            break;
    }

    //return sprintf('%03d:%02d:%02d', ($m = ($s = round($secs)) / 60) / 60, $m % 60, $s % 60);
    return $time;
}


/**
 * Converts time string to seconds
 *
 * @author     Paulo Freitas <paulofreitas dot web at gmail dot com>
 * @version    20100323
 * @copyright  2010 Paulo Freitas
 * @license    http://creativecommons.org/licenses/by-sa/3.0
 * @param      string $time time string to convert, in HH:MM:SS or HH:MM format
 * @return     int|null the time string converted to seconds on sucess,
  null on error
 */
function time_to_sec($time)
{
    if (preg_match('~(\d+):([0-5]\d)(?::([0-5]\d))?~', $time, $matches) == 1)
    {
        @list(, $h, $m, $s) = array_map('intval', $matches);
        return ($h * 3600) + ($m * 60) + $s;
    }

    return 0;
}


/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : emailChefiaTitularSubstituto                 |
 * | @description : Carrega os emails dos chefes do setor,       |
 * |                titular e substituto, do setor.              |
 * |                                                             |
 * | @param  : <string>  - $codlot_chefia                        |
 * |                       código de lotacão/setor da função     |
 * | @return : void                                              |
 * | @usage  : emailChefiaTitularSubstituto( '99999999' );       |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase (class)                              |
 * +-------------------------------------------------------------+
 * */
function emailChefiaTitularSubstituto($codlot_chefia = '')
{
    $emails_para = "";

    if ($codlot_chefia != '')
    {
        // pesquisa em banco de dados
        $oDBase = new DataBase('PDO');
        $oDBase->setMensagem("Tabela de ocupantes inexistente");
        $oDBase->query(
            "SELECT b.num_funcao, a.nome_serv, a.mat_siape, IFNULL(a.email,'') AS email, b.sit_ocup, a.chefia
			FROM servativ AS a
				LEFT JOIN ocupantes AS b ON a.mat_siape = b.mat_siape
				LEFT JOIN tabfunc AS c ON b.num_funcao = c.num_funcao
			WHERE c.cod_lot = :cod_lot AND b.sit_ocup IN ('T','S','I','R')
				AND a.chefia = 'S' AND a.excluido='N'
			GROUP BY a.mat_siape
			ORDER BY IF(b.sit_ocup='T',1,IF(b.sit_ocup='S',2,IF(b.sit_ocup='I',3,IF(b.sit_ocup='R',4,5)))) ", array(
            array(':cod_lot', $codlot_chefia, PDO::PARAM_STR)
            )
        );
        while ($oDados = $oDBase->fetch_object())
        {
            $emails_para .= ($emails_para == "" ? "" : ",") . $oDados->email;
        }
    }

    return $emails_para;
}


/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : emailChefiaImediata                          |
 * | @description : Carrega os emails dos chefes do setor,       |
 * |                titular e substituto.                        |
 * |                                                             |
 * | @param  : <string>  - $siape                                |
 * |                       matrícula do servidor                 |
 * | @return : email do titular e substituto do setor            |
 * | @usage  : emailChefiaImediata( '9999999' );                 |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase (class)                              |
 * +-------------------------------------------------------------+
 * */
function emailChefiaImediata($siape = '')
{
    $emails_para = "";

    if ($siape != '')
    {
        $siape = getNovaMatriculaBySiape($siape);

        // pesquisa em banco de dados
        $oDBase = new DataBase('PDO');
        $oDBase->setMensagem("Tabela do cadastro inacessível!");
        $oDBase->query(" ");
        while ($oDados = $oDBase->fetch_object())
        {
            $emails_para .= ($emails_para == "" ? "" : ",") . $oDados->email;
        }
    }

    return $emails_para;
}


/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : trata_substituicao                           |
 * | @description : Verifica se o usuário esta efetivado como    |
 * |                substituto e altera as permissoes.           |
 * |                Se o período expirou retorna a situação      |
 * |                anterior.                                    |
 * |                                                             |
 * | @param  : [<string>]  - $sSiape                             |
 * |                       matricula siape                       |
 * |           [<string>]  - $id                                 |
 * |                       ID do registro                        |
 * | @return : void                                              |
 * | @usage  : trata_substituicao( '9999999', '99999' );         |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase (class)                              |
 * +-------------------------------------------------------------+
 * */
function trata_substituicao($sSiape = '', $id = '')
{
    static $loop = 2;

    ## desabilita/habilita substituto como chefe
    #  para acompanhamento/homologação da frequencia
    #
    # - Executamos o processo "" duas vezes:
    #   1a. Para retirar o servidor logado da substituição, se for o caso.
    #   2a. Sendo o servidor chefe titular, verifica se o substituto está
    #       efetivado e se o prazo expirou ou foi encerrado, para cancelar
    #       as permissões do substituto eventual
    ##

    // le dados da sessao
    $sAPS    = $_SESSION['sAPS'];

    $lSiape  = ($sSiape == '' ? $_SESSION['sMatricula'] : $sSiape);
    $lSiape  = ($lSiape == '' ? 'X' : $lSiape);
    $lSiape  = getNovaMatriculaBySiape($lSiape);

    $sGestor = $_SESSION['sSenhaI'];

    // indicação de chefia - substituição
    $sChefia = 'N';

    // instancia a class DataBase
    $oDBase = new DataBase('PDO');
    $oDBase->setMensagem("Problema de acesso ao banco de dados!");

    ## verifica se ha delegacao
    #
    $oDBase->query("
    SELECT
        a.siape, b.nome_serv, b.cod_lot
    FROM
        usuarios AS a
    LEFT JOIN
        servativ AS b ON a.siape=b.mat_siape
    WHERE
        a.siape=:siape
        AND (DATE_FORMAT(NOW(),'%Y-%m-%d') >= IF(ifnull(a.datapt,'0000-00-00')='0000-00-00','9999-99-99',a.datapt)
            AND DATE_FORMAT(NOW(),'%Y-%m-%d') < IF(ifnull(a.dtfim,'0000-00-00')='0000-00-00','9999-99-99',a.dtfim))
    ",
    array(
        array(":siape", $lSiape, PDO::PARAM_STR)
    ));

    $nDelegado = $oDBase->num_rows();

    if ($nDelegado > 0)
    {
        $sChefia = 'S'; // Atualizar ID chefia no SERVATIV
    }
    else
    {
        ## verifica se ocupa função
        #  ou em efetiva substituição
        #
        # - Se o servidor for titular de alguma função a seleção trará um
        #   registro com SIT_OCUP = 'T', e nada será realizado.
        # - Se o servidor for somente substituto e estiver efetivado a seleção
        #   trará um registro com SIT_OCUP = 'S' e SUBSTITUINDO = 'S', e será
        #   alterado o campo CHEFIA para 'S' no SERVATIV, e as permissões para
        #   chefia.
        #
        $oDBase->query("
        SELECT
            a.mat_siape, a.sit_ocup,
            IF(IFNULL(b.siape,'')='','N','S') AS substituindo
        FROM ocupantes AS a
        LEFT JOIN
            substituicao AS b ON a.mat_siape = b.siape
                AND a.num_funcao=b.numfunc
                AND (DATE_FORMAT(NOW(),'%Y-%m-%d') >= IF(b.inicio='0000-00-00','9999-99-99',b.inicio)
                AND DATE_FORMAT(NOW(),'%Y-%m-%d') <= IF(b.fim='0000-00-00','9999-99-99',b.fim))
                AND b.situacao='A'
        LEFT JOIN
            tabfunc AS c ON a.num_funcao=c.num_funcao
        WHERE
            a.mat_siape= :siape AND c.resp_lot='S'
        ORDER BY
            a.mat_siape
        ",
        array(
            array(":siape", $lSiape, PDO::PARAM_STR)
        ));

        $nRows = $oDBase->num_rows();

        if ($nRows == 0)
        {
            $sChefia = 'N'; // Atualizar ID chefia no SERVATIV
        }
        else
        {
            while ($oFuncao = $oDBase->fetch_object())
            {
                $situacao_ocupacao = array('T', 'R', 'I', 'V');

                $sSituacaoOcup = $oFuncao->sit_ocup;

                // O valor S ou N será atribuido ao campo
                // Chefia do SERVATIV e alteração da permissão
                $sSubstituindo = $oFuncao->substituindo;

                // Atualizar ID chefia no SERVATIV
                $sChefia = ($sSituacaoOcup == '' ? 'N' : (in_array($sSituacaoOcup, $situacao_ocupacao) ? 'S' : $sSubstituindo)
                    );

                if (in_array($sSituacaoOcup, $situacao_ocupacao))
                {
                    break;
                }
            }
        }
    }

    ## desativa substituições Expiradas
    #
	if (empty($id))
    {
        $oDBase->query(
            "UPDATE substituicao SET situacao='E' WHERE siape= :siape AND situacao='A' AND DATE_FORMAT(NOW(),'%Y-%m-%d') > fim ", array(
            array(":siape", $lSiape, PDO::PARAM_STR)
            )
        );
    }
    else
    {
        $oDBase->query(
            "UPDATE substituicao SET situacao = 'E' WHERE siape= :siape AND id= :id ", array(
            array(":siape", $lSiape, PDO::PARAM_STR),
            array(":id", $id, PDO::PARAM_STR)
            )
        );
    }

    // Atualiza chefia no SERVATIV
    $oDBase->query(
        "UPDATE servativ SET chefia = '" . ($nDelegado > 0 ? 'N' : $sChefia) . "' WHERE mat_siape = :siape ", array(
        array(":siape", $lSiape, PDO::PARAM_STR)
        )
    );

    $sChefia = ($sGestor == 'N' || $sGestor == '' ? $sChefia : 'S');


    if ($_SESSION['sMatricula'] == $lSiape)
    {
        ## classe para alteração de permissão
        #
        $oPermissoes = new AtualizaPermissoesUsuario();
        $oPermissoes->setSubstituicao($lSiape, $sChefia); // ativamos/desativamos a permissão específica

        $_SESSION['sAPS'] = $sChefia; // atualiza variavel de seção
    }

    $loop--; // decrementa o valor, se for retirado o sistema entrará em loop sem fim.
    if ($loop >= 1 && $sSituacaoOcup == 'T')
    {
        $oDBase->query(
            "SELECT num_funcao FROM ocupantes WHERE mat_siape= :siape AND sit_ocup='T' LIMIT 1 ", array(array(":siape", $lSiape, PDO::PARAM_STR))
        );
        $sFuncao = $oDBase->fetch_object()->num_funcao; # antes estava mat_siape

        $oDBase->query(
            "SELECT mat_siape FROM ocupantes WHERE sit_ocup='S' AND num_funcao= :num_funcao ", array(array(":num_funcao", $sFuncao, PDO::PARAM_STR))
        );
        $nRows  = $oDBase->num_rows();
        $sSiape = $oDBase->fetch_object()->mat_siape;

        if ($nRows > 0 && ($_SESSION['sMatricula'] == $lSiape))
        {
            trata_substituicao($sSiape);
        }
    }
}


/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : chefia_ativa                                 |
 * | @description : Verifica se ocupa função ou encontra-se em   |
 * |                efetiva substituição do titular.             |
 * |                                                             |
 * | @param  : <string>  - $siape                                |
 * |                       matricula siape                       |
 * |           <string>  - $dia                                  |
 * |                       data da ocorrencia/registro           |
 * | @return : true  - se ocupante de função ou está substituindo|
 * |           false - se não é titular, nem está substituindo   |
 * | @usage  : $chefia = chefia_ativa( '9999999', '01/01/2000' );|
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase (class)                              |
 * +-------------------------------------------------------------+
 * */
function chefia_ativa($siape = '', $dia = '')
{
    // dados
    $dia     = conv_data($dia);
    $sChefia = 'N';

    if ($siape != '')
    {
        // instancia o banco de dados
        $oDBase = new DataBase('PDO');

        ##
        # - Se o servidor for titular de alguma função a seleção trará um registro com SIT_OCUP='T'.
        # - Se o servidor for somente substituto e estiver efetivado a seleção trará um registro com SIT_OCUP='S' e SUBSTITUINDO='S',
        #   indicando permissões de chefia.
        #
        $oDBase->query("
        SELECT
            chf.mat_siape, chf.sit_ocup,
            IF(IFNULL(subs.siape,'')='','N','S') AS substituindo
        FROM
            ocupantes AS chf
        LEFT JOIN
            substituicao AS subs ON chf.num_funcao = subs.numfunc AND chf.mat_siape = subs.siape
        LEFT JOIN
            tabfunc AS func ON chf.num_funcao = func.num_funcao
        WHERE
            chf.mat_siape = :siape
            AND (((:dia >= IF(subs.inicio='0000-00-00','9999-99-99',subs.inicio)
            AND :dia <= IF(subs.fim='0000-00-00','9999-99-99',subs.fim))
            AND subs.situacao = 'A')
                OR ((:dia >= IF(chf.dt_inicio='0000-00-00','9999-99-99',chf.dt_inicio)
                AND :dia <= IF(chf.dt_fim='0000-00-00','9999-99-99',chf.dt_fim))
                AND chf.sit_ocup = 'T'))
        ORDER BY
            chf.mat_siape
        ",
        array(
            array(':siape', $siape, PDO::PARAM_STR),
            array(':dia',   $dia, PDO::PARAM_STR),
            )
        );
        $nRows = $oDBase->num_rows();

        if ($nRows == 0)
        {
            $sChefia = 'N';
        }
        else
        {
            while ($oFuncao = $oDBase->fetch_object())
            {
                $sSituacaoOcup = ($oFuncao->sit_ocup == "" ? "x" : $oFuncao->sit_ocup);
                $sSubstituindo = $oFuncao->substituindo; // O valor S ou N será atribuido
                $sChefia       = (substr_count('T_R_I_V', $sSituacaoOcup) > 0 ? 'S' : ($sSituacaoOcup == 'x' ? 'N' : $sSubstituindo));
                if (substr_count('T_R_I_V', $sSituacaoOcup) > 0)
                {
                    break;
                }
            }
        }
    }

    return $sChefia;
}


/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : ocupante_de_funcao                           |
 * | @description : Verifica se ocupa função, titular ou         |
 * |                substito.                                    |
 * |                                                             |
 * | @param  : <string>  - $siape                                |
 * |                       matricula siape                       |
 * | @return : true  - se ocupante de função                     |
 * |           false - se não é ocupante de função               |
 * | @usage  : $chefia = ocupante_de_funcao( '9999999' );        |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase (class)                              |
 * +-------------------------------------------------------------+
 * */
function ocupante_de_funcao($siape)
{
    $siape = getNovaMatriculaBySiape($siape);

    // instancia o banco de dados
    $oDBase = new DataBase('PDO');

    // verifica se servidor ocupa função - titular/substituto
    $oDBase->query('
    SELECT
        chf.mat_siape AS siape, chf.nome_serv AS nome,
        IF(chf.sit_ocup="S","SUBSTITUTO","TITULAR") AS ocupacao,
        func.cod_lot AS unidade, func.desc_func AS funcao
            FROM ocupantes AS chf
                LEFT JOIN tabfunc AS func ON chf.num_funcao = func.num_funcao
                    WHERE chf.mat_siape="' . $siape . '"
                        AND func.resp_lot="S"
                            ORDER BY chf.mat_siape
    ');

    return ($oDBase->fetch_object());
}


/**  @Class
 * +--------------------------------------------------------------------------+
 * | @class       : AtualizaPermissoesUsuario                                 |
 * | @description : Atualiza permissoes do usuario                            |
 * |                                                                          |
 * | @param  : void                                                           |
 * | @return : void                                                           |
 * | @usage  : $oMsg = new AtualizaPermissoesUsuario();                       |
 * |          $oMsg->setSubstituicao( $siape, 'N' );                          |
 * | $method : AtualizaPermissoesUsuario()   Construtor                       |
 * |                                                                          |
 * | @author : Edinalvo Rosa                                                  |
 * |                                                                          |
 * | @dependence : void                                                       |
 * +--------------------------------------------------------------------------+
 * */
class AtualizaPermissoesUsuario
{
    var $oDBase;
    var $nTamanhoString;

    function AtualizaPermissoesUsuario()
    {
        /*
         PHP  cod  modulos     permissao                      padrao  varsession
           0  01   Cadastros   Recursos Humanos               N       sRH
           1  02   Cadastros   Chefes                         N       sAPS
           2  03   Cadastros   Servidores e Estagiários       N       sGBNIN
           3  04   Cadastros   Auditoria                      N       sOUTRO
           4  05   Cadastros   Médico                         N       sMEDICO
           5  06   Cadastros   Diretoria                      N       sCAD
           6  07   Relatórios  Recursos Humanos               N       sRelRH
           7  08   Relatórios  Gerenciais                     N       sRelGer
           8  09   Tabelas     De Prazos                      N       sTabPrazo
           9  10   Tabelas     De Servidores                  N       sTabServidor
          10  11   Usuários    Administrar Usuários           N       sSenhaI
          11  12   Gestão      Log do Sistema                 N       sLog
          12  13   Auditoria   Acesso para consulta completa  N       sAudCons
          13  14   Relatórios  Acesso SIC                     N       sSIC
        */
        $this->oDBase = new DataBase('PDO');
        $this->oDBase->setMensagem("Erro: na atualização de usuarios. " . $this->oDBase->error());
    }

    function setSubstituicao($siape = '', $acesso = 'N')
    {
        //          11111
        //012345678901234 (PHP)
        // S
        if ($siape != '')
        {
            $this->oDBase->query("SELECT acesso FROM usuarios WHERE siape = '" . $siape . "' ");
            $permissoes       = $this->oDBase->fetch_object()->acesso;
            $permissoes[1]    = $acesso;
            $this->oDBase->query("UPDATE usuarios SET acesso = '" . $permissoes . "' WHERE siape = '" . $siape . "' ");
            $_SESSION['sAPS'] = $acesso;
        }
    }

    function setPerfilRH($siape = '', $acesso = 'N')
    {
        //          11111
        //012345678901234 (PHP)
        //S     S  S
        if ($siape != '')
        {
            $this->oDBase->query("SELECT acesso FROM usuarios WHERE siape = '" . $siape . "' ");
            $permissoes    = $this->oDBase->fetch_object()->acesso;
            $permissoes[0] = $acesso;
            $permissoes[6] = $acesso;
            $permissoes[9] = $acesso;
            $this->oDBase->query("UPDATE usuarios SET acesso = '" . $permissoes . "' WHERE siape = '" . $siape . "' ");
        }
    }

    /*
     PHP  cod  modulos     permissao                      padrao  varsession
       0  01   Cadastros   Recursos Humanos               N       sRH
       1  02   Cadastros   Chefes                         N       sAPS
       2  03   Cadastros   Servidores e Estagiários       N       sGBNIN
       3  04   Cadastros   Auditoria                      N       sOUTRO
       4  05   Cadastros   Médico                         N       sMEDICO
       5  06   Cadastros   Diretoria                      N       sCAD
       6  07   Relatórios  Recursos Humanos               N       sRelRH
       7  08   Relatórios  Gerenciais                     N       sRelGer
       8  09   Tabelas     De Prazos                      N       sTabPrazo
       9  10   Tabelas     De Servidores                  N       sTabServidor
      10  11   Usuários    Administrar Usuários           N       sSenhaI
      11  12   Gestão      Log do Sistema                 N       sLog
      12  13   Auditoria   Acesso para consulta completa  N       sAudCons
      13  14   Relatórios  Acesso SIC                     N       sSIC
    */

    //                                  11111
    //                        012345678901234 (PHP)
    // setPerfilRHBloquear  =>nnSnnnnnnnnnnnn
    // setPerfilRHConsulta  =>SnSnnnnnnnnnnnn
    // setPerfilRHAlteracao =>SnSnnnSnnSnnnnn
    function setPerfilRHBloquear($siape = '')
    {
        $this->setPermissoes($siape, 'NNSNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN');
    }

    function setPerfilRHConsulta($siape = '')
    {
        $this->setPermissoes($siape, 'SNSNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN');
    }

    function setPerfilRHAlteracao($siape = '')
    {
        $this->setPermissoes($siape, 'SNSNNNSNNSNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN');
    }

    ##
    #  Guarda em sessão o perfil do usuario
    ##
    function setPermissoes($siape = '', $sAcessos = '', $alteraChefia = false)
    {
        if ($siape != '' && $sAcessos != '')
        {
            if ($siape == $_SESSION['sMatricula'])
            {
                $sAcessosSessao = $_SESSION['sPermissoesAcessos'];
                $sAcessosChefia = $sAcessosSessao[1];
                $sAcessos[1]    = ($alteraChefia == true ? $sAcessos[1] : $sAcessosChefia);
            }
            $sAcessos = str_pad($sAcessos, $this->nTamanhoString, 'N');
            $this->oDBase->queryLoop("UPDATE usuarios SET acesso='$sAcessos' WHERE siape='$siape' ");
            if ($siape == $_SESSION['sMatricula'])
            {
                $_SESSION['sPermissoesAcessos'] = $sAcessos;
                $modulos                        = $_SESSION['sModulos'];
                for ($sn = 0; $sn < count($modulos); $sn++)
                {
                    $var            = $modulos[$sn]['varsession'];
                    $$var           = substr($sAcessos, ($modulos['cod'][2] - 1), 1);
                    $_SESSION[$var] = $$var;
                }
            }
        }
    }
}


/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : retornaFoto                                  |
 * | @description : Verifica se existe foto gravada em algum dos |
 * |                servidores do SISREF.                        |
 * |                                                             |
 * | @param  : <string>  - $matricula                            |
 * |                       matricula siape                       |
 * | @return : void                                              |
 * | @usage  : retornaFoto( '9999999' );                         |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * +-------------------------------------------------------------+
 * */
function retornaFoto($sSiape = '')
{
    $sSiape = getNovaMatriculaBySiape($sSiape);

    $oDBase   = new DataBase('PDO'); // instancia o banco de dados
    $oDBase->query("SELECT siape, foto FROM servativ_fotos WHERE siape = '$sSiape' "); //verifica se já está gravado
    $nNumRows = $oDBase->num_rows();

    if ($nNumRows > 0)
    {
        $sFoto = $oDBase->fetch_object()->foto; // já foi registrado carrega para exibir
    }
    else
    {
        // caso não exista a foto será
        // exibida uma imagem sem rosto
        $sFoto = _DIR_FOTO_ . "anonimo.jpg";

        // IP da maquina local e caminho
        // em que se encontra a imagem
        $sLocal = 'http://' . $_SERVER['SERVER_ADDR'] . '/' . _DIR_APP_ . _DIR_FOTO_;

        //$sLocal =$_SERVER["DOCUMENT_ROOT"] . '/' . _DIR_APP_ ."foto/";
        // se existe a foto
        if (@file_get_contents($sLocal . $sSiape . ".jpg"))
        {
            $sFoto = $sLocal . $sSiape . ".jpg";
        }

        /* else // se nao existe a foto
        {
            // procura em todas as maquinas: da 11 até a 20
            for ($x=11; $x <= 20; $x++)
            {
                $sLocal = "http://10.120.2.". $x . '/' . _DIR_APP_ . _DIR_FOTO_;

                if (@file_get_contents($sLocal.$sSiape.".jpg"))
                {
                    $sFoto = $sLocal.$sSiape.".jpg";
                    break;
                }
            }
        }
        */

        // se a foto for diferente de anonimo
        // registra o endereco em banco de dados
        if ($sFoto != _DIR_FOTO_ . "anonimo.jpg")
        {
            // se nao foi registrado grava em banco de dados o caminho
            $oDBase->query("INSERT servativ_fotos SET siape = '$sSiape', foto = '$sFoto' ");
        }
    }

    return $sFoto;
}


/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : trocaParametroREQUEST_URI                    |
 * | @description : Registra em histórico os dados do ponto      |
 * |                antes da alteração                           |
 * |                                                             |
 * | @param  : <string>  - $campo                                |
 * |                       nome do campo (variavel/parametro)    |
 * |           <string>  - $valor                                |
 * |                       novo conteudo p/ variavel/parametro   |
 * | @return : void                                              |
 * | @usage  : trocaParametroREQUEST_URI('sLotacao',$qlotacao);  |
 * |                                                             |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * +-------------------------------------------------------------+
 * */
function trocaParametroREQUEST_URI($campo = '', $valor = '')
{
    if ($campo != '' || $valor != '')
    {
        $parametros = explode('&', $_SERVER['REQUEST_URI']);
        if (count($parametros) > 0)
        {
            $request_uri = "";
            for ($i = 0; $i < count($parametros); $i++)
            {
                $request_uri .= (substr($parametros[$i], 0, 8) == $campo ? $campo . "=" . $valor . "&" : $parametros[$i] . '&');
            }
            $request_uri            = substr($request_uri, 0, (strlen($request_uri) - 1));
            $_SERVER['REQUEST_URI'] = $request_uri;
        }
    }
}


/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : calcular_tempo                               |
 * | @description : Soma ou subtrai horas ou minutos             |
 * |                                                             |
 * | @param  : <string>   - $hora1                               |
 * |                        hora inicial no padrão hh:mm         |
 * | @param  : <string>   - $hora2                               |
 * |                        hora final no padrão hh:mm           |
 * | @param  : [<string>] - $soma                                |
 * |                        true  -> soma                        |
 * |                        false -> subtrai                     |
 * | @return : <integer>  - horas e/ou minutos decorridos        |
 * | @usage  : calcular_tempo('13:56','16:12');                  |
 * |          //resulta em 30:08 (horas:minutos)                 |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function calcular_tempo($hora1, $hora2, $soma = true)
{
    $hentra = $hora1;
    $hora1  = ($hentra > $hora2 ? $hora2 : $hentra );
    $hora2  = ($hentra > $hora2 ? $hentra : $hora2 );

    $separar[1] = explode(':', $hora1);
    $separar[2] = explode(':', $hora2);

    $total_minutos_transcorridos[1] = ($separar[1][0] * 60) + $separar[1][1];
    $total_minutos_transcorridos[2] = ($separar[2][0] * 60) + $separar[2][1];
    if ($soma == true)
    {
        $total_minutos_transcorridos = $total_minutos_transcorridos[2] + $total_minutos_transcorridos[1];
    }
    else
    {
        $total_minutos_transcorridos = $total_minutos_transcorridos[2] - $total_minutos_transcorridos[1];
    }

    $diferenca = '00:00';
    if ($total_minutos_transcorridos <= 59)
    {
        $diferenca = ('00:' . substr('00' . $total_minutos_transcorridos, -2));
    }
    elseif ($total_minutos_transcorridos > 59)
    {
        $hora_transcorrida     = (int) ($total_minutos_transcorridos / 60);
        if ($hora_transcorrida <= 9)
            $hora_transcorrida     = substr('00' . $hora_transcorrida, -2);
        $minutos_transcorridos = $total_minutos_transcorridos % 60;
        if ($minutos_transcorridos <= 9)
            $minutos_transcorridos = substr('00' . $minutos_transcorridos, -2);
        $diferenca             = ($hora_transcorrida . ':' . $minutos_transcorridos);
    }

    return $diferenca;
}

function adicionarHoras($hora1, $hora2)
{
    return calcular_tempo($hora1, $hora2, true);
}

function subtrairHoras($hora1, $hora2)
{
    return calcular_tempo($hora1, $hora2, false);
}

//fim da função para calcular diferença de horas.


/*
 *
 * Verifica se o usuário está liberado
 * para registrar frequência após as 22hs
 *
 */
function liberado_registro_apos_22hs($siape = '')
{
    $retorno = 'NAO';

    if ($siape != '')
    {
        $oDBase = new DataBase('PDO'); // instancia o banco de dados
        $oDBase->setMensagem("nulo");
        $oDBase->query("
        SELECT
            IF(IFNULL(cod_lot,'SEM COD_LOT') = cod_lot,'SIM','NAO') AS liberado_apos_22hs
        FROM
            liberacao_acesso_especial
        WHERE
            siape = :siape ", array(
            array(":siape", $siape, PDO::PARAM_STR)
        ));

        if ($oDBase->num_rows() > 0)
        {
            $retorno = $oDBase->fetch_object()->liberado_apos_22hs;
        }
    }

    return $retorno;
}

##
# Registra se o registro foi X min antes
# ou X min depois do horário  definido
#
function mensagemHorarioDifere($siape = '', $campo = 'limite_entrada', $sTurnoEstendido = '', $ocupaFuncao = '', $lotacao = '', $situacao_cadastral = '', $hora_definida = '00:00:00', $hora_registrada = '00:00:00', $dia_util = '', $data = '')
{
    $html = '';

    $oDBase = new DataBase('PDO');
    $oDBase->query(
        "SELECT campo, minutos, mensagem, inicio, fim, exibe
		FROM config_basico
		WHERE DATE_FORMAT(NOW(),'%Y-%m-%d') >= IF(inicio='0000-00-00','9999-99-99',inicio)
			AND DATE_FORMAT(NOW(),'%Y-%m-%d') <= IF(fim='0000-00-00','9999-99-99',fim)
			AND campo= :campo ", array(array(':campo', $campo, PDO::PARAM_STR))
    );
    $nRows  = $oDBase->num_rows();

    if ($nRows > 0)
    {
        $oCfgBasico = $oDBase->fetch_object();
        $mensagem   = $oCfgBasico->mensagem;
        $minutos    = $oCfgBasico->minutos;
        $exibe      = $oCfgBasico->exibe;

        $entra        = $hora_definida;
        $sHoraEntrada = $hora_registrada; // para verificacao da entrada
        $sHoraSaida   = $hora_registrada;   // para verificacao da saida

        $sMinutosAntes  = subtraiHoras($entra, $minutos, 'H:i:s', true);
        $sMinutosDepois = adicionaHoras($entra, $minutos, 'H:i:s', true);

        $bForaDoHorario         = false;
        $bForaDoHorarioTEChefia = false;
        $bForaDoHorarioOutros   = false;

        if ($campo == 'limite_entrada')
        {
            $gravaSaida     = false;
            $bForaDoHorario = ($sTurnoEstendido == 'S' && $ocupaFuncao == 'N' && $situacao_cadastral != '66' && $_SESSION['registro_inicial'] == '0' && ($sHoraEntrada <= $sMinutosAntes || $sHoraEntrada >= $sMinutosDepois) && ($dia_util == 'S' || $dia_util == '1'));
            $diferencaHoras = diferencaHoras($entra, $sHoraEntrada);
        }
        else if ($campo == 'limite_saida')
        {
            $gravaSaida     = ($sTurnoEstendido == 'S' && $ocupaFuncao == 'N' && $situacao_cadastral != '66' && ($dia_util == 'S' || $dia_util == '1'));
            $bForaDoHorario = ($sTurnoEstendido == 'S' && $ocupaFuncao == 'N' && $situacao_cadastral != '66' && ($dia_util == 'S' || $dia_util == '1') && $sHoraSaida <= $sMinutosAntes);
            $diferencaHoras = diferencaHoras($entra, $sHoraSaida);
        }

        if ($bForaDoHorario == true || $gravaSaida == true)
        {
            if ($bForaDoHorario == true && $ocupaFuncao == 'N')
            {
                $html = '<div style="display:none;" id="dialog-mensagem">' . preparaTextArea(ltrim(rtrim($mensagem))) . '</div>';
            }

            $html .= "
			<script>
				$(document).ready(function() {
				    var dialogMensagem = $('#dialog-mensagem').html();

				    if (dialogMensagem) {
						mostraMensagem(dialogMensagem);
					}
				});
			</script>";

            $oDBase->query(
                "SELECT siape
				FROM control_entrada_saida
				WHERE siape= :siape
					AND DATE_FORMAT(data_registro,'%Y-%m-%d')=DATE_FORMAT(NOW(),'%Y-%m-%d') ", array(array(':siape', $siape, PDO::PARAM_STR))
            );
            $nRowsControl = $oDBase->num_rows();

            if ($campo == 'limite_entrada' && $nRowsControl == 0)
            {
                $oDBase->query(
                    "INSERT control_entrada_saida
					SET siape= :siape,
						lotacao= :lotacao,
						entrada_definida= :entrada_definida,
						entrada_realizada= :entrada_realizada,
						entrada_diferenca= :entrada_diferenca,
						data_registro=NOW() ", array(
                    array(':siape', $_SESSION['sMatricula'], PDO::PARAM_STR),
                    array(':lotacao', $_SESSION['sLotacao'], PDO::PARAM_STR),
                    array(':entrada_definida', $entra, PDO::PARAM_STR),
                    array(':entrada_realizada', $sHoraEntrada, PDO::PARAM_STR),
                    array(':entrada_diferenca', $diferencaHoras, PDO::PARAM_STR)
                    )
                );
            }
            else if ($campo == 'limite_saida')
            {
                if ($nRowsControl == 0)
                {
                    $oDBase->query(
                        "INSERT control_entrada_saida
						SET siape= :siape,
							lotacao= :lotacao,
							saida_definida= :saida_definida,
							saida_realizada= :saida_realizada,
							saida_diferenca= :saida_diferenca,
							data_registro=NOW() ", array(
                        array(':siape', $_SESSION['sMatricula'], PDO::PARAM_STR),
                        array(':lotacao', $_SESSION['sLotacao'], PDO::PARAM_STR),
                        array(':saida_definida', $entra, PDO::PARAM_STR),
                        array(':saida_realizada', $sHoraSaida, PDO::PARAM_STR),
                        array(':saida_diferenca', $diferencaHoras, PDO::PARAM_STR)
                        )
                    );
                }
                else
                {
                    $oDBase->query(
                        "UPDATE control_entrada_saida
						SET saida_definida= :saida_definida,
							saida_realizada= :saida_realizada,
							saida_diferenca= :saida_diferenca
						WHERE siape= :siape
						AND DATE_FORMAT(data_registro,'%Y-%m-%d')=DATE_FORMAT(NOW(),'%Y-%m-%d') ", array(
                        array(':saida_definida', $entra, PDO::PARAM_STR),
                        array(':saida_realizada', $sHoraSaida, PDO::PARAM_STR),
                        array(':saida_diferenca', $diferencaHoras, PDO::PARAM_STR),
                        array(':siape', $_SESSION['sMatricula'], PDO::PARAM_STR)
                        )
                    );
                }
            }


            ## registramos todos os servidores que
            #  entraram ou sairam fora do horário
            #
			$oDBase->query(
                "SELECT siape
				FROM control_entrada_saida_todos
				WHERE siape= :siape
					AND DATE_FORMAT(data_registro,'%Y-%m-%d')=DATE_FORMAT(NOW(),'%Y-%m-%d') ", array(
                array(':siape', $_SESSION['sMatricula'], PDO::PARAM_STR)
                )
            );
            $nRowsControl = $oDBase->num_rows();

            if ($campo == 'limite_entrada' && $nRowsControl == 0)
            {
                $bForaDoHorario = ($situacao_cadastral != '66' && ($sHoraEntrada <= $sMinutosAntes || $sHoraEntrada >= $sMinutosDepois) && ($dia_util == 'S' || $dia_util == '1'));
                $diferencaHoras = diferencaHoras($entra, $sHoraEntrada);
                if ($bForaDoHorario == true)
                {
                    $oDBase->query(
                        "INSERT control_entrada_saida_todos
						SET siape= :siape,
							lotacao= :lotacao,
							entrada_definida= :entrada_definida,
							entrada_realizada= :entrada_realizada,
							entrada_diferenca= :entrada_diferenca,
							data_registro=NOW(),
							turno_estendido= :turno_estendido,
							chefia= :chefia ", array(
                        array(':siape', $_SESSION['sMatricula'], PDO::PARAM_STR),
                        array(':lotacao', $_SESSION['sLotacao'], PDO::PARAM_STR),
                        array(':entrada_definida', $entra, PDO::PARAM_STR),
                        array(':entrada_realizada', $sHoraEntrada, PDO::PARAM_STR),
                        array(':entrada_diferenca', $diferencaHoras, PDO::PARAM_STR),
                        array(':turno_estendido', $sTurnoEstendido, PDO::PARAM_STR),
                        array(':chefia', $ocupaFuncao, PDO::PARAM_STR)
                        )
                    );
                }
            }
            else if ($campo == 'limite_saida')
            {
                //$bForaDoHorario = ($situacao_cadastral!='66' && $sHoraSaida <= $sMinutosAntes && ($dia_util=='S' || $dia_util=='1'));
                $diferencaHoras = diferencaHoras($entra, $sHoraSaida);
                if ($nRowsControl == 0)
                {
                    $oDBase->query(
                        "INSERT control_entrada_saida_todos
						SET siape= :siape,
							lotacao= :lotacao,
							saida_definida= :saida_definida,
							saida_realizada= :saida_realizada,
							saida_diferenca= :saida_diferenca,
							data_registro=NOW(),
							turno_estendido= :turno_estendido,
							chefia= :chefia ", array(
                        array(':siape', $_SESSION['sMatricula'], PDO::PARAM_STR),
                        array(':lotacao', $_SESSION['sLotacao'], PDO::PARAM_STR),
                        array(':saida_definida', $entra, PDO::PARAM_STR),
                        array(':saida_realizada', $sHoraEntrada, PDO::PARAM_STR),
                        array(':saida_diferenca', $diferencaHoras, PDO::PARAM_STR),
                        array(':turno_estendido', $sTurnoEstendido, PDO::PARAM_STR),
                        array(':chefia', $ocupaFuncao, PDO::PARAM_STR)
                        )
                    );
                }
                else
                {
                    $oDBase->query(
                        "UPDATE control_entrada_saida_todos
						SET saida_definida= :saida_definida,
							saida_realizada= :saida_realizada,
							saida_diferenca= :saida_diferenca
						WHERE siape= :siape
							AND DATE_FORMAT(data_registro,'%Y-%m-%d')=DATE_FORMAT(NOW(),'%Y-%m-%d') ", array(
                        array(':saida_definida', $entra, PDO::PARAM_STR),
                        array(':saida_realizada', $sHoraEntrada, PDO::PARAM_STR),
                        array(':saida_diferenca', $diferencaHoras, PDO::PARAM_STR),
                        array(':siape', $_SESSION['sMatricula'], PDO::PARAM_STR)
                        )
                    );
                }
            }

            if ($exibe == 'S')
            {
                echo $html;
            }
        }
    }

    return $html;
}

/*
 * Função para colocar os pontos em um número de OL
 */
function mascaraOl($ol)
{
    //separando os dígitos
    $estado  = substr($ol, 0, 2);
    $sec     = substr($ol, 2, 3);
    $subsec  = substr($ol, 5, 2);
    $subsec2 = substr($ol, 7, 1);
    //montando o numero de OL
    $ol_nova = $estado . "." . $sec;
    if ($subsec != "")
    {
        $ol_nova .= "." . $subsec;
    }
    if ($subsec2 != "")
    {
        $ol_nova .= "." . $subsec2;
    }

    return $ol_nova;
}


/*
 * Funcao para realizar 'include' de arquivos
 */
function includeOnce($endereco)
{
    $diretorio = '../../';
    if (file_exists($diretorio . $endereco))
    {
        return include_once $diretorio . $endereco;
    }
    else
    {
        return include_once $endereco;
    }
}


/*
 * Funcao para Recuperar o IP do Cliente
 */
function recuperaIPCliente()
{
    return getIpReal();
}


/*
 * Funcao para Executar consultas SQL Universal em um Banco de dados;
 */
function executar_consulta($link, $sql)
{
    if ($link == '888')
    {
        mensagem('Erro: - Banco de Dados Indisponível!');
        return -1; //'Erro: - Banco de Dados Indisponível!'; //retorno para usuario informando a indisponibilidade da base
    }
    else
    {
        mysqli_query($link, "SET NAMES'utf8'");
        mysqli_query($link, 'SET character_set_connection=utf8');
        mysqli_query($link, 'SET character_set_client=utf8');
        mysqli_query($link, 'SET character_set_results=utf8');
        return mysqli_query($link, $sql);
    }
}


/*
 * Função para embaralhar um dado que será colocado em uma requisicao
 * Essa função inverte a string, depois aplica base64encode, inverte o
 * resultado outra vez, aplica novamente base64_encode e o resultado
 * inverte. Então retorna.
 */
function criptografa($valor)
{
    $resultado = [];

    // 1o passo
    $resultado['invertido'] = strrev($valor);
    $resultado['encode']    = base64_encode($resultado['invertido']);
    // 2o passo
    $resultado['invertido'] = strrev($resultado['encode']);
    $resultado['encode']    = base64_encode($resultado['invertido']);
    // 3o passo
    $resultado['invertido'] = strrev($resultado['encode']);

    //Instanciando classe
    ##$mc = new MyCripty();

    ##return $mc->enc( $resultado['invertido'] );

    return $resultado['invertido'];
}


/*
 * Função para desembaralhar um dado que será retirado em uma requisicao
 * Essa função inverte a string, depois aplica base64decode, inverte o
 * resultado outra vez, aplica novamente base64_decode e o resultado
 * inverte. Então retorna.
 */
function descriptografa($valor)
{
    //Instanciando classe
    ##$mc = new MyCripty();
    ##$passo1 = $mc->dec( $valor );
    ##$passo2 = strtr(_SISTEMA_SERIAL_, array(_SISTEMA_SIGLA_ . '_' => ''));
    ##$passo3 = strtr($passo1, array( $passo2 => ''));
    $passo3 = $valor;

    $resultado = [];

    // 1o passo
    $resultado['invertido'] = strrev($passo3);
    $resultado['decode']    = base64_decode($resultado['invertido']);
    // 2o passo
    $resultado['invertido'] = strrev($resultado['decode']);
    $resultado['decode']    = base64_decode($resultado['invertido']);
    // 3o passo
    $resultado['invertido'] = strrev($resultado['decode']);

    $retorno = strtr($resultado['invertido'], array('%2F' => '/', '%3A' => ':'));

    return $retorno;
}

/*
 * Funcao para Recuperar o Erro da SQL  em um Banco de dados;
 */
function getRecuperaErroSQL($tabela, $link, $sql)
{
    $erro = "Tabela...: " . $tabela . "<br/><br/>" .
        "SQL.....:" . $sql . "<br/><br/>" .
        "ERRO....:" . mysqli_errno($link) . " : " . mysqli_error($link);
    //criaArquivoTexto($erro);

    mensagem($erro);
}

function debugSisref($tipo = 'entrada')
{
    global $vDatas, $dthoje, $hoje, $d, $m, $y, $vHoras;

    if ($_SESSION['sMatricula'] == '9000000')
    {
        switch ($tipo)
        {
            case 'entrada': $vHoras = '14:35:00';
                break;
            case 'entrada_hora_inicio_almoco': $vHoras = '15:00:00';
                break;
            case 'entrada_hora_fim_almoco': $vHoras = '16:00:00';
                break;
            case 'entrada_hora_saida': $vHoras = '16:35:00';
                break;
        }

        switch ($tipo)
        {
            case 'entrada':
            case 'entrada_hora_inicio_almoco':
            case 'entrada_hora_fim_almoco':
            case 'entrada_hora_saida':
                $vDatas = "2013-02-13";
                $dthoje = $vDatas;
                $hoje   = databarra($vDatas);
                $d      = dataDia($vDatas);
                $m      = dataMes($vDatas);
                $y      = dataAno($vDatas);
                break;
        }
    }
}

function validateCPF($cpf='')
{
     $cpf = str_replace(array('.','-'), '', $cpf);
     if($cpf == "00000000000" || $cpf == "11111111111" || $cpf == "22222222222" || $cpf == "33333333333" || $cpf == "44444444444" || $cpf == "55555555555" || $cpf == "66666666666" || $cpf == "77777777777" || $cpf == "88888888888" || $cpf == "99999999999"):
          return false;
     else:
          for ($t = 9; $t < 11; $t++):
               for ($d = 0, $c = 0; $c < $t; $c++) $d += $cpf{$c} * (($t + 1) - $c);
                    $d = ((10 * $d) % 11) % 10;
                    if ($cpf{$c} != $d)
                         return false;
          endfor;
          return true;
     endif;
}

function validaCPF($cpf9 = '')
{
    $retorno = false;

    // Elimina possivel mascara
    $cpf8 = preg_replace("/[^0-9]/i", "", $cpf9);
    $cpf = str_pad($cpf8, 11, '0', STR_PAD_LEFT);

    // Verifica se um número foi informado
    if (empty($cpf))
    {
        return false;
    }
    // Verifica se o numero de digitos informados é igual a 11
    else if (strlen($cpf) != 11)
    {
        return false;
    }

    $cpfs_invalidos = array(
        '00000000000',
        '11111111111',
        '22222222222',
        '33333333333',
        '44444444444',
        '55555555555',
        '66666666666',
        '77777777777',
        '88888888888',
        '99999999999'
    );


    // Verifica se nenhuma das sequências invalidas abaixo
    // foi digitada. Caso afirmativo, retorna falso
    if (in_array($cpf,$cpfs_invalidos))
    {
        return false;
    }

    // Calcula os digitos verificadores para verificar se o
    // CPF é válido
    for ($t = 9; $t < 11; $t++):

        for ($d = 0, $c = 0; $c < $t; $c++):
            $d += $cpf{$c} * (($t + 1) - $c);
        endfor;

        $d = ((10 * $d) % 11) % 10;

        if ($cpf{$c} != $d):
            return false;
        endif;

    endfor;

    return true;
}

/**
* Created on 18/07/2010
*
* @author Carlos Coelho (coelhoduda@hotmail.com)
* @version 1.0.0
*
*	função para validar e-mail usando expressão regular
*
*	@param string $mail O e-mail para o teste de validação
*	@return boolean TRUE Se string $mail passar pela validação
*/
function validaEmail($mail)
{
    if (preg_match("/^([[:alnum:]_.-]){3,}@([[:lower:][:digit:]_.-]{3,})(.[[:lower:]]{2,3})(.[[:lower:]]{2})?$/", $mail))
    {
        return true;
	}
    else
    {
        return false;
	}
}


##
# CAIXA DE VISUALIZAÇÃO/MENSAGEM - IFRAME
#
# Monta o script básico para utilização de caixa de dialogo modal,
# para exibir mensagens e dados dos dias e motivos do sistema estar
# indisponivel nestes dias
#
##

function preparaDialogView($width = "900", $height = "490", $center = 'center', $close = '')
{
    //if (strtolower(browser_detection( 'browser_name' )) == 'msie' && $_SESSION['compatibilidade_ie'] != 'IE10')
    if (strtolower(browser_detection('browser_name')) == 'chrome')
    {
        $width  = "'auto'";
        $height = "'auto'";
    }
    else
    {
    //$width  = "'auto'";
    //$height = "'auto'";
    }

    if ($close != '')
    {
        $close = "close: function() { " . $close . "; },";
    }

    echo "
	<style>
		.ui-widget-overlay { position: absolute; top: 7; left: 0; width: 100%; height: 2020px; }
	</style>
	<script>
		$(function() {
			var icons = {
				header: 'ui-icon-circle-arrow-e',
				headerSelected: 'ui-icon-circle-arrow-s'
			};
			$('#dialog-view').dialog({
				bgiframe: true,
				draggable: true,
				" . $close . "
				autoOpen : false,
				modal: true,
				closeText: 'Fechar',
				closeOnEscape: true,
				outerHeight: 1020,
				width: " . $width . ",
				height: " . $height . "
			});
		});
	</script>
	<div id='dialog-view' title='' valign='top' align='left' style='margin:0 auto;vertical-align:top;text-align:left;display:none;z-index:100000;'></div>
	";

}

function preparaShowDivIFrame($links = '', $width = "900", $height = "490")
{
    if ($links != '')
    {
        // internet explorer: msie
        // firefox..........: gecko
        // chrome...........: chrome
        //
		$browser_name = strtolower(browser_detection('browser_name'));

        switch ($browser_name)
        {
            case 'msie':
                if (soNumeros($_SESSION['compatibilidade_ie']) < 10)
                {
                    $dialog_width  = ""; //"$('#dialog-view').dialog( { width: ".$width." } );";
                    $dialog_height = ""; //"$('#dialog-view').dialog( { height: ".$height." } );";
                    //$width  = ($width - 50);
                    //$height = ($height + 5);
                }
                else
                {
                    $dialog_width  = "";
                    $dialog_height = "";
                    //$width  = ($width + 20);
                    //$height = ($height + 15);
                }
                break;

            case 'gecko':
                $dialog_width  = "";
                $dialog_height = "";
                //$width  = ($width - 0);
                //$height = ($height + 50);
                $width         = 1048;
                $height        = 526;
                break;

            case 'chrome':
                //$dialog_width  = "$('#dialog-view').dialog( { width: ".($width)." } );";
                $width  = ($width + 27);
                $height = ($height - 20);
                break;
        }

        $link_id = "$('#" . $links . "').click(function() { showDivIFrame_" . $links . "('#" . $links . "'); });";
        echo "
		<script>
		function showDivIFrame_" . $links . "(obj)
		{
			var arquivo = $(obj).attr( 'src' );
			var titulo = $(obj).attr( 'title' );
			var urlPDF = '<iframe src=\"'+arquivo+'\" width=\"" . $width . "px\" height=\"" . $height . "px\" align=\"left\" style=\"position: relative; top:0px; left: 0px; margin: 0px 0px 0px 0px;\"></iframe>';
			$('#dialog-view').dialog( { title: titulo } );
			" . $dialog_width . "
			" . $dialog_height . "
			$('#dialog-view').html( urlPDF ).dialog('open');
			$('#dialog-view').dialog( 'moveToTop' );
		}

		$(function() { " . $link_id . " })
		</script>
		";
    }

}

##
# VISUALIZAR PDF
#
# Monta o script básico para utilização de caixa de dialogo modal,
# para exibir PDFs
#
##

function preparaDialogViewPDF($links = '', $width = "990", $height = "500")
{
    if (is_array($links) && count($links) > 0)
    {
        $link_id = "";
        for ($x = 0; $x < count($links); $x++)
        {
            $link_id .= "$('#" . $links[$x] . "').click(function() { showDivPDF('#" . $links[$x] . "'); });\n";
        }
        ?>
        <script>
            $(function ()
            {
                var icons = {
                    header: 'ui-icon-circle-arrow-e',
                    headerSelected: 'ui-icon-circle-arrow-s'
                };
                $('#dialog-pdf').dialog({
                    bgiframe: true,
                    draggable: true,
                    position: 'center',
                    autoOpen: false,
                    modal: true,
                    closeOnEscape: true,
                    width: '<?= ($width + 30); ?>px',
                    height: '<?= $height; ?>px'
                });
        <?= $link_id; ?>
            });

            function showDivPDF(obj)
            {
                var arquivo = $(obj).attr('src');
                var titulo = $(obj).attr('title');
                var urlPDF = '<embed src=\"' + arquivo + '\" width=\"<?= $width; ?>px\" height=\"<?= $height; ?>px\">';
                $('#dialog-pdf').dialog({title: titulo});
                $('#dialog-pdf').html(urlPDF).dialog('open');
            }
        </script>
        <?php
    }

}

function verifica_acesso_url($pagina = 'x')
{
    $path_parts       = pathinfo($_SERVER['HTTP_REFERER']);
    $pagina_de_origem = $path_parts['filename'];
    $pagina_de_origem = ($pagina_de_origem == '' ? 'vazio' : $pagina_de_origem);

    if (empty($pagina_de_origem) || substr_count($pagina, $pagina_de_origem) == 0)
    {
        mensagem("Por favor utilize um dos endereços abaixo:\\n\\thttp://www-sisref/;\\n\\thttp://www-sisref/rh;\\n\\thttp://www-sisref/chefia", 'http://www-sisref/', 1);
    }

}

function verifica_acesso_homologacao()
{
    verifica_acesso_url("frequencia_homologar_entra:|:frequencia_homologar:|:frequencia_homologar_registros:|:frequencia_homologar_registros_ver_justificativa:|:frequencia_alterar:|:frequencia_alterar_horario:|:frequencia_gravar:|:frequencia_justificativa_abono:|:frequencia_gravar_abono:|:frequencia_homologar_concluir:|:class_form.frequencia:|:frequencia_excluir:|:frequencia_excluir_grava");

}

function autorizacaoDiaNaoUtil($data = "", $siape = "")
{
    $autoriza = "N";
    $data     = ($data == "" ? date('d/m/Y') : $data);
    $siape    = ($siape == "" ? $_SESSION['sMatricula'] : $siape);

    $siape = getNovaMatriculaBySiape($siape);

    if ($siape != "")
    {
        $oDBase = new DataBase('PDO');
        $oDBase->setMensagem("Erro de acesso a tabela TABDNU!");
        $rdnu   = $oDBase->query("SELECT * FROM tabdnu WHERE dia = :dia AND siape = :siape ", array(
            array(":dia", conv_data($data), PDO::PARAM_STR),
            array(":siape", $siape, PDO::PARAM_STR),
        ));
        if ($rdnu)
        {
            $autoriza = ($oDBase->num_rows() > 0 ? $oDBase->fetch_object()->autorizado : 'N');
        }
    }
    return $autoriza;

}

function tempo_execucao($start = null)
{
    // Calcula o microtime atual
    $mtime = microtime(); // Pega o microtime
    $mtime = explode(' ', $mtime); // Quebra o microtime
    $mtime = $mtime[1] + $mtime[0]; // Soma as partes montando um valor inteiro

    if ($start == null)
    {
        // Se o parametro não for especificado, retorna o mtime atual
        return $mtime;
    }
    else
    {
        // Se o parametro for especificado, retorna o tempo de execução
        return round($mtime - $start, 2);
    }

}

function exibe_tempo_execucao($print = false)
{
    $tempo = tempo_execucao(__microTIME__);

    // Agora é só fazermos a subtração de um pelo outro, e usar o number_format() do PHP para formatar com 6 casas depois da virgula e pronto, mas caso você queira alterar esse número de casas depois da vírgula para mais ou menos, fique a vontade
    $tempo = number_format($tempo, 2, ',', '.');

    // Exibimos uma mensagem
    $texto = "<div class='ft_10_001' style='float: left; text-align: center; vertical-align: bottom; font-size: 7px;'>";
    $texto .= converte_charset('Execução:') . ' <b>' . $tempo . '</b>s';
    //$texto .= ' | Memória usada: ' . round(((memory_get_peak_usage(true) / 1024) / 1024), 2) . 'Mb';
    $texto .= '</div>';
    if ($print == true)
    {
        echo $texto;
    }
    else
    {
        return $texto;
    }

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : delayNovoLogin                               |
 * | @description : verifica se um novo login foi iniciado na    |
 * |                mesma máquina menos de 50seg. depois         |
 * |                                                             |
 * | @param  : <string> - $pagina                                |
 * |                      página que inicio o processo           |
 * | @param  : <integer> - $delay                                |
 * |                       tempo de espera                       |
 * | @return : void                                              |
 * | @usage  : delayNovoLogin();                                 |
 * |          //exibe mensagem informando a necessidade do delay |
 * | @author : desconhecido                                      |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function delayNovoLogin($pagina = 'entrada.php', $delay = 1, $show = true)
{
    $mensagem = '';
    $oDBase   = new DataBase('PDO'); // instancia a conexao a base de dados

    $ip     = getIpReal(); // atribuimos o IP

    // IP da maquina utilizada
    $ahora  = time(); // definimos a hora atual
    $limite = $ahora - 10 * $delay; // limite

    $oDBase->query('SELECT ip, fecha FROM control_ip WHERE  ip = :ip AND fecha >= :limite', array(
        array(':ip',     $ip,     PDO::PARAM_STR),
        array(':limite', $limite, PDO::PARAM_INT)
        )
    );

    // verifica se já houve registro
    // neste mesmo IP antes de 50 segundos
    // se existe houve, mensagem para tentar outra vez
    if ($oDBase->num_rows() != 0)
    {
        //$oDBase->query( "
        //INSERT INTO control_ip_rapidos
        //    (ip, siape, lotacao, datahora, seq, cod_uorg, cod_uorg_novo)
        //    VALUES
        //    (:ip, :siape, :lotacao, now(), 0, :lotacao, '') ", array(
        //    array(':ip',      $ip,                     PDO::PARAM_STR),
        //    array(':siape',   $_SESSION['sMatricula'], PDO::PARAM_STR),
        //    array(':lotacao', $_SESSION['sLotacao'],   PDO::PARAM_STR),
        //));

        $mensagem = "Caro(a) " . nome_sobrenome($_SESSION['sNome']) . ",\\nPor favor, tente outra vez!";
    }

    if ($show == true)
    {
        mensagem($mensagem, $pagina);
    }
    return $mensagem;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : bloqueiaVPN                                  |
 * | @description : bloqueia o acesso via VPN ao SISREF          |
 * |                                                             |
 * | @param  : <string> - $pagina                                |
 * |                      página que iniciou o processo          |
 * | @return : void                                              |
 * | @usage  : bloqueiaVPN();                                    |
 * |          //exibe mensagem de acesso não autorizado          |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function bloqueiaVPN($pagina = 'entrada.php', $show = true)
{
    $mensagem = '';
    $oDBase   = new DataBase('PDO'); // instancia a conexao a base de dados

    $ip = getIpReal(); // atribuimos o número
    // IP da máquina utilizada

    if (substr($ip, 0, 5) == '10.0.' || $teste == true)
    {
        // grava o LOG
        registraLog("realizou login, mas a entrada não foi registrada (VPN)");
        $oDBase->query("
        INSERT INTO control_ip_vpn
        SET
            ip       = :ip,
            siape    = :siape,
            lotacao  = :lotacao,
            datahora = NOW()
		", array(
            array(':ip', $ip, PDO::PARAM_STR),
            array(':matricula', $_SESSION['sMatricula'], PDO::PARAM_STR),
            array(':lotacao', $_SESSION['sLotacao'], PDO::PARAM_STR)
        ));
        $mensagem = "Acesso Não Autorizado!";
    }
    if ($show == true)
    {
        mensagem($mensagem, $pagina);
    }
    return $mensagem;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : ocupa_das4ou5ou6                             |
 * | @description : Verifica se ocupa DAS-4, 5 ou 6.             |
 * |                - Ampliado para Funções correlatas.          |
 * |                                                             |
 * | @param  : <string>  - $siape                                |
 * |                       matricula siape                       |
 * | @return : true  - se ocupante de DAS-4, 5 ou 6              |
 * |                   (e funções correlatas).                   |
 * | @usage  : $chefia = ocupa_das4ou5ou6( '9999999' );          |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase (class)                              |
 * +-------------------------------------------------------------+
 * */
function ocupa_das4ou5ou6($siape = '', $dia = '')
{
    // dados
    $mensagem = '';

    if ($siape != '')
    {
        // teste o parametro
        $siape = getNovaMatriculaBySiape($siape);
        $dia = (empty($dia) ? date('Y-m-d') : conv_data($dia));

        // instancia o banco de dados
        $oDBase = new DataBase('PDO');

        ##
        # - Se o servidor for titular de alguma função a seleção trará um registro com SIT_OCUP='T'.
        # - Se o servidor for somente substituto e estiver efetivado a seleção trará um registro com SIT_OCUP='S' e SUBSTITUINDO='S',
        #   indicando permissões de chefia.
        #
	$result = $oDBase->query('
        SELECT
            CONCAT(isento.tipo," ",isento.codigo) AS isento
        FROM
            servativ AS cad
        LEFT JOIN
            ocupantes AS chf ON cad.mat_siape = chf.mat_siape
                AND (((:dia1 >= IF(chf.dt_inicio="0000-00-00","9999-99-99",chf.dt_inicio)
                    AND :dia2 <= IF(chf.dt_fim="0000-00-00","9999-99-99",chf.dt_fim))
                    AND chf.sit_ocup = "T"))
        LEFT JOIN
            substituicao AS subs ON cad.mat_siape = subs.siape
                AND subs.situacao = "A"
                    AND (:dia1 >= IF(subs.inicio="0000-00-00","9999-99-99",subs.inicio)
                    AND :dia2 <= IF(subs.fim="0000-00-00","9999-99-99",subs.fim))
        LEFT JOIN
            tabfunc AS func ON (chf.num_funcao = func.num_funcao OR subs.numfunc = func.num_funcao)
        LEFT JOIN
            isencao_ponto AS isento ON isento.ativo = "S" AND (func.cod_funcao = isento.codigo AND isento.tipo = "Função")
        WHERE
            cad.mat_siape = :siape
            AND (IFNULL(isento.codigo,"") <> "")
        ORDER BY
            cad.mat_siape',
        array(
            array(':siape', $siape, PDO::PARAM_STR),
            array(':dia1',  $dia,   PDO::PARAM_STR),
            array(':dia2',  $dia,   PDO::PARAM_STR),
        ));

        if ($result && $oDBase->num_rows())
        {
            $mensagem = $oDBase->fetch_object()->isento;
        }

        // libera memoria
        //$oDBase->free_result();
        //$oDBase->close();
    }

    return $mensagem;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : isento_de_ponto                              |
 * | @description : Verifica se ocupa função, situação funcional,|
 * |    regime ou cargo isentos de registro de frequência.       |
 * |                                                             |
 * |    IN n. 2 de 12.09.2018 (MPDG) Art. 8º.                    |
 * |    Dispensados de registro de frequência:                   |
 * |        - Natureza Especial (NES07);                         |
 * |        - Ocupantes de função CD-III, e iguais/superiores    |
 * |          a DAS-4 e correlatas;                              |
 * |        - Cargos contemplados pelo Art. 8º, inciso IV.       |
 * |          Há cargos que mesmo contemplados por este artigo e |
 * |          inciso o dirigente máximo do órgão pode definir a  |
 * |          manutenção do controle de frequência;              |
 * |        - Participantes de programa de gestão A e B.         |
 * |                                                             |
 * | @param  : <string>  - $siape                                |
 * |                       matricula siape                       |
 * | @return : mensagem, se vazia não tem isenção.               |
 * | @usage  : $chefia = isento_de_ponto( '9999999' );           |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase (class)                              |
 * +-------------------------------------------------------------+
 * */
function isento_de_ponto($siape = '', $dia = '')
{
    // dados
    $mensagem = '';

    if ($siape != '')
    {
        // teste o parametro
        $siape = getNovaMatriculaBySiape($siape);
        $dia   = (empty($dia) ? date('Y-m-d') : conv_data($dia));

        // instancia o banco de dados
        $oDBase = new DataBase('PDO');

        ##
        # Verifica se:
        #   - a função do titular ou substituto efetivado, consta na tabela de
        #     isenção;
        #   - o cargo do servidor consta na tabela de isenção;
        #     Se o dirigente máximo definiu no cadastro (servativ) a exigência
        #     do registro de frequência, mesmo isento, o servidor registrará a
        #     a frequência. A exceção são os cargos com isenção obrigatória;
        #   - a Situação Funcional do servidor consta na tabela de isenção;
        #   - o servidor participa do Programa de Gestão A/B que consta na
        #     tabela de isenção
        ##

	$result = $oDBase->query('
        SELECT
            CONCAT(isento.tipo," ",IF(isento.texto="",isento.codigo,isento.texto)) AS isento
        FROM
            servativ AS cad
        LEFT JOIN
            ocupantes AS chf ON cad.mat_siape = chf.mat_siape
                AND (((:dia1 >= IF(chf.dt_inicio="0000-00-00","9999-99-99",chf.dt_inicio)
                    AND :dia2 <= IF(chf.dt_fim="0000-00-00","9999-99-99",chf.dt_fim))
                    AND chf.sit_ocup = "T"))
        LEFT JOIN
            substituicao AS subs ON cad.mat_siape = subs.siape
                AND subs.situacao = "A"
                    AND (:dia1 >= IF(subs.inicio="0000-00-00","9999-99-99",subs.inicio)
                    AND :dia2 <= IF(subs.fim="0000-00-00","9999-99-99",subs.fim))
        LEFT JOIN
            tabfunc AS func ON (chf.num_funcao = func.num_funcao OR subs.numfunc = func.num_funcao)
        LEFT JOIN
            tabisencao_situacao AS isentosit ON isentosit.ativo = "S"
                AND cad.isencao_ponto = isentosit.codigo
        LEFT JOIN
            isencao_ponto AS isento ON isento.ativo = "S"
                AND (
                        (func.cod_funcao = isento.codigo AND isento.tipo = "Função")
                        OR (cad.cod_cargo = isento.codigo AND isento.tipo = "Cargo" AND (cad.isencao_ponto <> "02" OR obrigatorio_isencao = "S") AND isentosit.sigla = "")
                        OR (CONCAT(cad.sigregjur,cad.cod_sitcad) = isento.codigo AND isento.tipo = "Situação Funcional")
                        OR (isento.codigo = isentosit.sigla AND isento.tipo = "Programa de Gestão")
                )
        WHERE
            cad.mat_siape = :siape
            AND (IFNULL(isento.codigo,"") <> "")
        ORDER BY
            cad.mat_siape',
        array(
            array(':siape', $siape, PDO::PARAM_STR),
            array(':dia1',  $dia,   PDO::PARAM_STR),
            array(':dia2',  $dia,   PDO::PARAM_STR),
        ));

        if ($result && $oDBase->num_rows())
        {
            $mensagem = $oDBase->fetch_object()->isento;
        }

        // libera memoria
        //$oDBase->free_result();
        //$oDBase->close();
    }

    return $mensagem;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : forcaTrocaSenha                              |
 * | @description : força a troca de senha                       |
 * |                                                             |
 * | @param  : <string> - $prazo                                 |
 * |                      indica se deve trocar senha            |
 * | @param  : <string> - $troca_senha                           |
 * |                      se a senha eh a data de nascimento     |
 * | @param  : <string> - $destino                               |
 * |                      script de destino                      |
 * | @return : void                                              |
 * | @usage  : forcaTrocaSenha();                                |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function forcaTrocaSenha($prazo = '', $troca_senha = '', $destino = "trocasenha.php")
{
    if ($prazo == '1') // || $troca_senha == '1')
    {
        replaceLink($destino);
    }

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : styleFormulario                              |
 * | @description : folha de estilo do formulário                |
 * |                                                             |
 * | @param  : <string> - $href_css                              |
 * |                      prefixo do arquivo css                 |
 * | @param  : <string> - $so_nome                               |
 * |                      retorna nome ou inclui o arquivo       |
 * | @return : void / nome do arquivo                            |
 * | @usage  : styleFormulario();                                |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : browser_detection                             |
 * +-------------------------------------------------------------+
 * */
function styleFormulario($href_css = '', $so_nome = true)
{
    $versao_ie = browser_detection('ie_version');
    $browser   = browser_detection('browser_name');
    $numero    = browser_detection('browser_number');
    settype($numero, 'integer');

    if ($href_css != '')
    {
        switch (strtolower($browser))
        {
            case 'gecko': $href_css .= '_firefox.css';
                break;
            case 'chrome': $href_css .= '_chrome.css';
                break;
            case 'msie':
                if ($numero >= 9)
                {
                    $href_css .= '_ie9x.css';
                }
                else if ($numero >= 8 && $numero < 9)
                {
                    $href_css .= '_ie8x.css';
                }
                else
                {
                    $href_css .= '_ie.css';
                }
                break;
            default: $href_css .= '_ie.css';
                break;
        }
    }

    unset($versao_ie);
    unset($browser);
    unset($numero);

    if ($so_nome == false)
    {
        echo "<link type='text/css' rel='stylesheet' href='" . $href_css . "'>";
    }
    else
    {
        return $href_css;
    }

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : str_to_utf8                                  |
 * | @description : Converte o texto para UTF-8 se precisar      |
 * |                                                             |
 * | @param  : [<string>] - $str                                 |
 * |                        texto a converter                    |
 * | @return : <string>   - texto convertido para UTF-8          |
 * | @usage  : str_to_utf8( 'Ã?GUA' );                            |
 * | @author : Daniel Oliveira (daniel.oliveira@inss.gov.br)     |
 * |                                                             |
 * | @dependence : mb_detect_encoding (function PHP)             |
 * +-------------------------------------------------------------+
 * */
function str_to_utf8($str)
{
    if (mb_detect_encoding($str, 'UTF-8', true) === false)
    {
        $str = utf8_encode($str);
    }
    return $str;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : DivProgressBar                               |
 * | @description : Barra de progresso                           |
 * |                                                             |
 * | @param  : [<string>] - $texto                               |
 * |                        texto a exibir                       |
 * | @return : void                                              |
 * | @usage  : DivProgressBar( 'Aguarde, ...' );                 |
 * | @author : Edinalvo Rosa                                     |
 * +-------------------------------------------------------------+
 * */
function DivProgressBar($texto = 'Aguarde, preparando Relat&oacute;rio!')
{
    ?>
    <div id="pbContainer0" style="position:relative;top:10px;left:362px;border-width:0px;background-color:#FFFFFF;width:300px; display:inline;z-index:100000;height:100%;margin:0px auto;padding:5px 0px 0px 0px;">
        <table id='pbBarProgressor' class='borda_arredondada borda_arredondada_sombra' style='text-align:center;width:300px;border:1px solid #50aa50;' cellpadding="0" cellspacing="2" max='100'>
            <thead>
                <tr><td style="border: 0px;">Aguarde, preparando Relat&oacute;rio!</td></tr>
                <tr><td style='text-align: center;'>
                        <div id="pbBar0_top" style="display: none; white-space:nowrap;background-color:transparent;height:12px;width:0px;color:#828282;font-size:8px;font-family:verdana;text-align:center;font-weight:bold;">&nbsp;</div>
                    </td></tr>
                <tr><td style="border:1px solid #50aa50;background-color:#ddffdd;height:10px;width:300px;">
                        <div id="percentage" class='sombra-texto' style="white-space:nowrap;background-color:#30aa30;height:12px;width:0px;color:white;font-size:8px;font-family:verdana;text-align:center;font-weight:bold;padding:3px 0px 0px 0px;"></div>
                    </td></tr>
                <tr><td style='text-align: center;'>
                        <div id="pbBar0_row" style="white-space:nowrap;background-color:transparent;height:12px;width:0px;color:#828282;font-size:8px;font-family:verdana;text-align:center;font-weight:bold;">Registros: </div>
                    </td></tr>
                <tr><td style='text-align: center;'>
                        <div id="pbBar0_bottom" style="display: none; white-space:nowrap;background-color:transparent;height:12px;width:0px;color:#828282;font-size:8px;font-family:verdana;text-align:center;font-weight:bold;">&nbsp;</div>
                    </td></tr>
            </thead>
        </table>
    </div>
    <?php

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : anti_injection                               |
 * | @description : Anti SQL Injection                           |
 * |                                                             |
 * | @param  : [<string>] - $sql                                 |
 * |                        texto SQL                            |
 * | @return : <string>   - texto verificado                     |
 * | @usage  : antiInjection( 'SELECT...' );                     |
 * | @author : desconhecido (internet)                           |
 * |                                                             |
 * | @dependence : void                                          |
 * +-------------------------------------------------------------+
 * */
function anti_injection($string)
{
    $string = (check_utf8($string) ? utf8_decode($string) : $string);

    $string = trim($string);
    $lista  = array(" or ", " asc ", " desc ", " and ", ";");

    for ($x = 0; $x < count($lista); $x++)
    {
        $string = str_ireplace($lista[$x], " ", $string);
    }

    $lista = array('"or"1"="1"', "'or'1'='1'", "alter table ", "select ", "from ", "insert ", "delete ", "update ", "where ", "drop table ", "show tables ", "union all ", "union ", "distinct row ", "distinct ", "having ", "truncate ", "replace ", "handler ", "like ", "procedure ", "limit ", "order by ", "group by ", "-shutdown ", "=", "'", "(", ")", "<", ">", "", "--", "'", "#", "$", "%", "Â¨", "&", "--", "drop ", "xp_", "*", "exec ", "master", "cmdshell", "cmd", "shell ", "net ", "user ");

    for ($x = 0; $x < count($lista); $x++)
    {
        $string = str_ireplace($lista[$x], "", $string);
    }

    $string = trim($string);
    $string = strip_tags($string);
    $string = addslashes($string);
    $string = trata_aspas($string);

    return $string;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : hosts_liberados                              |
 * | @description : Verifica se o IP da máquina origem do script |
 * |                está liberada para acesso ao sistema         |
 * |                                                             |
 * | @param  : void                                              |
 * | @return : <string> - texto contendo os IPs                  |
 * | @usage  : host_liberados();                                 |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase (class)                              |
 * +-------------------------------------------------------------+
 * */
function hosts_liberados()
{
    $string = '';

    // instancia BD
    $oDBase = new DataBase('PDO');

    // hosts com acesso autorizado
    $oDBase->query("SELECT ip_do_host FROM tabhosts_acesso_autorizado WHERE ip_do_host='" . trim($_SERVER['SERVER_ADDR']) . "' AND autorizado='S' ORDER BY ip_do_host ");
    /*
      while ($hosts = $oDBase->fetch_object())
      {
      $string .= $hosts->ip_do_host . '__';
      }
      return substr( $string, 0, strlen( $string ) - 2 );
     */
    return ($oDBase->num_rows() > 0);

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : listboxDestinacao                            |
 * | @description : Monta listbox para destinação das horas      |
 * |                excedentes                                   |
 * |                                                             |
 * | @param  string   $siape  Matricula do servidor              |
 * | @param  string   $prazo  No prazo p/ destinar pagar recesso |
 * | @param  boolean  $ignorebancohoras  Prazo destinação recesso|
 * | @param  boolean  $horaextra  Prazo destinação hora-extra    |
 * | @return : <html> - Listbox montado                          |
 * |                                                             |
 * | @usage  : listboxDestinacao(<siape>,[<prazo>]);             |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase (class)                              |
 * +-------------------------------------------------------------+
 * */
function listboxDestinacao($siape, $prazo=1, $bancohoras=false, $horaextra=false, $compensacao=false)
{
    $oDBase = selecionaServidor($siape);
    $oDados     = $oDBase->fetch_object();
    $sitcad     = $oDados->sigregjur;
    $cod_sitcad = $oDados->cod_sitcad;

    ## ocorrências grupos
    $obj = new OcorrenciasGrupos();
    $codigoCreditoRecessoPadrao     = $obj->CodigoCreditoRecessoPadrao($sitcad);
    $codigoHoraExtraPadrao          = $obj->CodigoHoraExtraPadrao($sitcad);
    $codigoCreditoPadrao            = $obj->CodigoCreditoPadrao($sitcad);
    $codigoCreditoInstrutoriaPadrao = $obj->CodigoCreditoInstrutoriaPadrao($sitcad);

    // instancia a class DataBase
    $oDBase = new DataBase('PDO');

    // destinação
    if ($horaextra == true)
    {
        $query = "
        SELECT
            descricao, situacao_cadastral_excluida, codigo_de_ocorrencia,
            recesso, ativo
        FROM
            tabdestinacao_credito
        WHERE
            ativo = 'S'
            AND codigo_de_ocorrencia IN (".implode(',', $codigoHoraExtraPadrao).")
            AND situacao_cadastral_excluida <> '".$cod_sitcad."'
        ORDER BY
            id_destinacao
        ";
    }
    else
    {
        $query = "
        SELECT
            descricao, situacao_cadastral_excluida, codigo_de_ocorrencia,
            recesso, ativo
        FROM
            tabdestinacao_credito
        WHERE
            ativo='S'
            AND codigo_de_ocorrencia NOT IN (".implode(',', $codigoHoraExtraPadrao).")
            AND situacao_cadastral_excluida <> '".$cod_sitcad."'
            AND id_destinacao <> 9
            AND id_destinacao <> 8
        ";

        if($bancohoras == false)
        {
            $query .= " AND id_destinacao <> 7";
        }

        if($compensacao == false)
        {
            $query .= " AND codigo_de_ocorrencia NOT IN (".implode(',', $codigoCreditoPadrao).") ";
            $query .= " AND codigo_de_ocorrencia NOT IN (".implode(',', $codigoCreditoInstrutoriaPadrao).") ";
        }

        $query .= " ORDER BY id_destinacao";
    }

    $oDBase->query($query);

    $finalizacao_da_pagina = "
    <p style='text-align:center;word-spacing:0px;width:100%;height:40px;
              margin-left:0px;margin-right:0px;margin-top:6px;'>
        <select name='destinacao' id='destinacao' class='form-control'>
    ";

    while ($oDestinacao = $oDBase->fetch_object())
    {
        $nSituacaoCadastral = substr_count(
            $oDestinacao->situacao_cadastral_excluida,
            ($situacao_cadastral == '' ? 'x' : $situacao_cadastral)
        );

        if ($nSituacaoCadastral == 0 && (($prazo == 2 && !in_array($oDestinacao->codigo_de_ocorrencia, $codigoCreditoRecessoPadrao)) || ($prazo == 1)))
        {
            $finalizacao_da_pagina .= "<option value='" . $oDestinacao->codigo_de_ocorrencia . "'>" . $oDestinacao->descricao . "</option>";
        }
    }
    $finalizacao_da_pagina .= "</select>";
    $finalizacao_da_pagina .= "</p>";

    return $finalizacao_da_pagina;

}

/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : modulo_principal_acionado                    |
 * | @description : registra qual o módulo de origem do login,   |
 * |                se entrada, rh ou chefia.                    |
 * |                                                             |
 * | @param  : <string> - app/sogp/chefia                        |
 * | @return : void                                              |
 * | @usage  : modulo_principal_acionado();                      |
 * | @author : Edinalvo Rosa                                     |
 * +-------------------------------------------------------------+
 * */
class ModuloPrincipalAcionado
{

    private $ip;
    private $caminho;
    private $arquivo;

    function __construct()
    {
        $this->ip      = getIpReal();
        $ip_pad        = explode('.', $this->ip);
        $this->ip      = substr('000' . $ip_pad[0], -3) .
            substr('000' . $ip_pad[1], -3) .
            substr('000' . $ip_pad[2], -3) .
            substr('000' . $ip_pad[3], -3);
        $this->caminho = "{$_SERVER['DOCUMENT_ROOT']}/controle/sessao/";
        $this->arquivo = $this->caminho . $this->ip . '.txt';

    }

    public function registrar($modulo = 'app')
    {
        $arquivo = fopen($this->arquivo, 'w+');
        fwrite($arquivo, $modulo);
        fclose($arquivo);
        chmod($this->arquivo, 0777);

    }

    public function carregar()
    {
        $lines = file($this->arquivo);
        //foreach ($lines as $line_num => $line) {}
        $line  = end($lines);
        return $line;

    }

    public function apagar()
    {
        unlink($this->arquivo);
        /*
          foreach (glob($this->caminho."*.txt") as $arquivo)
          {
          unlink($arquivo);
          }
         */

    }

}

function error_handler($errno, $errstr=null, $errfile=null, $errline=null)
{

    if ($errno == E_USER_ERROR || $errno == E_ERROR)
    {
        $form = new formPadrao();
        $form->initHTML();
        $form->setHTML('<div style="text-align:center;">');
        $form->setHTML("<script language='JavaScript' type='text/javascript'>\n");
        $form->setHTML(" alert('Erro ao tentar acessar funcionalidade. Por favor, contate o administrador do sistema');");
        $form->setHTML("parent.main.location.replace('principal_abertura.php')");
        $form->setHTML("</script>\n");

        $form->setHTML('</div>');
        $form->printHtml();
    }

    return true;
}


/**
 * Contéudo do POST
 *
 * @param string $key
 * @return string/array/integer/float/object Contéudo
 */
function getPost( $key=null, $padrao=null  )
{
    if (is_null($key) || empty($key))
    {
        return $padrao;
    }
    else
    {
        return (!isset($_POST[$key]) || empty($_POST[$key]) ? $padrao : filterInput($_POST[$key]));
    }
}


/**
 * Contéudo do GET
 *
 * @param string $key
 * @return string/array/integer/float/object Contéudo
 */
function getGet( $key=null, $padrao=null  )
{
    if (is_null($key) || empty($key))
    {
        return $padrao;
    }
    else
    {
        return (!isset($_GET[$key]) || empty($_GET[$key]) ? $padrao : filterInput($_GET[$key]));
    }
}


/**
 * Contéudo do GET
 *
 * @param string $key
 * @return string/array/integer/float/object Contéudo
 */
function getRequest( $key=null, $padrao=null  )
{
    if (is_null($key) || empty($key))
    {
        return $padrao;
    }
    else
    {
        return (!isset($_REQUEST[$key]) || empty($_REQUEST[$key]) ? $padrao : filterInput($_REQUEST[$key]));
    }
}


/**
 * Contéudo do SESSION
 *
 * @param string $key
 * @return string/array/integer/float/object Contéudo
 */
function getSESSION( $key=null, $padrao=null  )
{
    if (is_null($key) || empty($key))
    {
        return $padrao;
    }
    else
    {
        return (!isset($_SESSION[$key]) || empty($_SESSION[$key]) ? $padrao : $_SESSION[$key]);
    }
}


/**
 * Filtro anti injection
 *
 * @param string $str
 * @return string/array/integer/float/object POST/GET
 */
function filterInput( $str ){
    return anti_injection( $str );
}


/*
 * IP DA MÁQUINA DO USUÁRIO
 */
function getIpReal()
{
    $ip = '';
    $ip = filter_input(INPUT_SERVER, 'HTTP_CLIENT_IP');
    $ip = (empty($ip) ? filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR') : $ip);
    $ip = (empty($ip) ? filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED')     : $ip);
    $ip = (empty($ip) ? filter_input(INPUT_SERVER, 'HTTP_FORWARDED_FOR')   : $ip);
    $ip = (empty($ip) ? filter_input(INPUT_SERVER, 'HTTP_FORWARDED')       : $ip);
    $ip = (empty($ip) ? filter_input(INPUT_SERVER, 'HTTP_X_COMING_FROM')   : $ip);
    $ip = (empty($ip) ? filter_input(INPUT_SERVER, 'HTTP_COMING_FROM')     : $ip);
    $ip = (empty($ip) ? filter_input(INPUT_SERVER, 'REMOTE_ADDR')          : $ip);
    //$ip = getenv('REMOTE_ADDR');

    return $ip;
}



/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : UnidadesGestaoPermitida                      |
 * | @description : Monta sql gestores designados, delegados ou  |
 * |                com gestão especial permitida                |
 * |                                                             |
 * | @param  : void                                              |
 * | @return : <sql> - sql montado                               |
 * | @usage  : UnidadesGestaoPermitida();                        |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase (class)                              |
 * +-------------------------------------------------------------+
 * */
function UnidadesGestaoPermitida()
{
	$sql = "
	/* GESTAO ESPECIAL */
	SELECT
		func.num_funcao AS num_funcao, func.cod_lot AS cod_lot, und.descricao, upg.nome_gex AS gerencia, und.regional, gesp.siape AS siape, und.upag AS upag, 'GESTAO' AS grupo
	FROM
		tabsetor AS und
	LEFT JOIN tabfunc                  AS func ON und.codigo = func.cod_lot
	LEFT JOIN tabsetor_gex             AS upg ON und.upag = upg.upag AND upg.ativo='1'
	LEFT JOIN gestao_especial_unidades AS gesp ON gesp.setor = und.codigo
	WHERE
		(gesp.siape = '".$_SESSION["sMatricula"]."' OR und.codigo = '".$_SESSION['sLotacao']."')
		AND und.ativo = 'S'
		/*AND 0 <> ANY (SELECT COUNT(*) FROM servativ AS cad WHERE cad.cod_lot = und.codigo AND excluido='N' AND cod_sitcad NOT IN ('02','08','15','66'))*/
		AND IFNULL(gesp.siape,'') <> ''
		AND (DATE_FORMAT(NOW(),'%Y-%m-%d') >= IF(IFNULL(gesp.inicio,'0000-00-00')='0000-00-00','9999-99-99',gesp.inicio) AND DATE_FORMAT(NOW(),'%Y-%m-%d') <= IF(IFNULL(gesp.fim,'0000-00-00')='0000-00-00','9999-99-99',gesp.fim)) GROUP BY cod_lot

	UNION

	/* TITULAR */
	SELECT
		func.num_funcao AS num_funcao, func.cod_lot AS cod_lot, und.descricao, upg.nome_gex AS gerencia, und.regional, chf.mat_siape AS siape, und.upag AS upag, 'TITULAR' AS grupo
	FROM
		ocupantes AS chf
	LEFT JOIN tabfunc      AS func ON chf.num_funcao = func.num_funcao
	LEFT JOIN tabsetor     AS und ON func.cod_lot = und.codigo
	LEFT JOIN tabsetor_gex AS upg ON und.upag = upg.upag AND upg.ativo='1'
	WHERE
		chf.mat_siape = '".$_SESSION["sMatricula"]."'
		AND chf.dt_fim = '0000-00-00'
		AND func.resp_lot = 'S'
		AND chf.sit_ocup <> 'S' GROUP BY num_funcao /* diferente de substituto */

	UNION

	/* SUBSTITUICAO */
	SELECT
		func.num_funcao AS num_funcao, func.cod_lot AS cod_lot, und.descricao, upg.nome_gex AS gerencia, und.regional, subs.siape AS siape, und.upag AS upag, 'SUBSTITUICAO' AS grupo
	FROM
		substituicao AS subs
	LEFT JOIN tabfunc AS func ON subs.numfunc = func.num_funcao
	LEFT JOIN tabsetor AS und ON func.cod_lot = und.codigo
	LEFT JOIN tabsetor_gex AS upg ON und.upag = upg.upag AND upg.ativo='1'
	WHERE
		subs.siape = '".$_SESSION["sMatricula"]."'
		AND ((DATE_FORMAT(NOW(),'%Y-%m-%d') >= IF(IFNULL(subs.inicio,'0000-00-00')='0000-00-00','9999-99-99',subs.inicio)
		AND DATE_FORMAT(NOW(),'%Y-%m-%d') <= IF(IFNULL(subs.fim,'0000-00-00')='0000-00-00','9999-99-99',subs.fim))
		AND subs.situacao = 'A') GROUP BY num_funcao

	UNION

	/* DELEGACAO */
	SELECT
		'' AS num_funcao, atrd.cod_lot AS cod_lot, und.descricao, upg.nome_gex AS gerencia, und.regional, atrd.siape, und.upag AS upag, 'DELEGACAO' AS grupo
	FROM
		atribuicao_delegada AS atrd
	LEFT JOIN tabsetor AS und ON atrd.cod_lot = und.codigo
	LEFT JOIN tabsetor_gex AS upg ON und.upag = upg.upag AND upg.ativo='1'
	WHERE
		atrd.siape = '".$_SESSION["sMatricula"]."'
		AND atrd.cod_lot = '".$_SESSION['sLotacao']."'
		AND ((DATE_FORMAT(NOW(),'%Y-%m-%d') >= IF(IFNULL(atrd.portaria_inicio_data,'0000-00-00')='0000-00-00','9999-99-99',atrd.portaria_inicio_data)
			AND DATE_FORMAT(NOW(),'%Y-%m-%d') <= IF(IFNULL(atrd.portaria_fim_data,'0000-00-00')='0000-00-00','9999-99-99',atrd.portaria_fim_data)))

	GROUP BY num_funcao
	ORDER BY
		siape,IF(grupo='TITULAR',1,IF(grupo='DELEGACAO',3,2)),cod_lot
	";

    return $sql;
}



/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : GestaoTitularSubstituto                      |
 * | @description : Monta sql gestores designados titulares e/ou |
 * |                substituto                                   |
 * |                                                             |
 * | @param  : <siape> - servidor                                |
 * | @return : <sql> - sql montado                               |
 * | @usage  : GestaoTitularSubstituto();                        |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase (class)                              |
 * +-------------------------------------------------------------+
 * */
function GestaoTitularSubstituto($siape='',$resp_lot=true)
{
	$sql = "
	/* TITULAR */
	SELECT
		func.num_funcao, func.cod_lot AS cod_lot, und.descricao, upg.nome_gex AS gerencia, und.regional, chf.mat_siape AS siape, und.upag AS upag, 'TITULAR' AS grupo
	FROM
		ocupantes AS chf
	LEFT JOIN tabfunc      AS func ON chf.num_funcao = func.num_funcao
	LEFT JOIN tabsetor     AS und ON func.cod_lot = und.codigo
	LEFT JOIN tabsetor_gex AS upg ON und.upag = upg.upag AND upg.ativo='1'
	WHERE
		chf.mat_siape = '".$siape."'
		AND chf.dt_fim = '0000-00-00'
		" . ($resp_lot == true ? "AND func.resp_lot = 'S' " : "") . "
		AND chf.sit_ocup <> 'S' /* diferente de substituto */
	GROUP BY func.num_funcao

	UNION

	/* SUBSTITUICAO */
	SELECT
		func.num_funcao, func.cod_lot AS cod_lot, und.descricao, upg.nome_gex AS gerencia, und.regional, subs.siape AS siape, und.upag AS upag, 'SUBSTITUICAO' AS grupo
	FROM
		substituicao AS subs
	LEFT JOIN tabfunc AS func ON subs.numfunc = func.num_funcao
	LEFT JOIN tabsetor AS und ON func.cod_lot = und.codigo
	LEFT JOIN tabsetor_gex AS upg ON und.upag = upg.upag AND upg.ativo='1'
	WHERE
		subs.siape = '".$siape."'
		AND ((DATE_FORMAT(NOW(),'%Y-%m-%d') >= IF(IFNULL(subs.inicio,'0000-00-00')='0000-00-00','9999-99-99',subs.inicio)
		AND DATE_FORMAT(NOW(),'%Y-%m-%d') <= IF(IFNULL(subs.fim,'0000-00-00')='0000-00-00','9999-99-99',subs.fim))
		AND subs.situacao = 'A')
	GROUP BY func.num_funcao

	ORDER BY
		siape,IF(grupo='TITULAR',1,IF(grupo='DELEGACAO',3,2)),cod_lot
	";
	return $sql;
}


/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : UnidadesLiberadasGestaoSRouGEX               |
 * | @description : Monta sql unidades liberadas (SR ou GEX)     |
 * |                                                             |
 * | @param  : void                                              |
 * | @return : <sql> - sql montado                               |
 * | @usage  : UnidadesLiberadasGestaoSRouGEX();                 |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase (class)                              |
 * +-------------------------------------------------------------+
 * */
function UnidadesLiberadasGestaoSRouGEX()
{
	$sql = "
	SELECT
		fun.num_funcao, fun.cod_lot, und.descricao, REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(UPPER(gex.nome_gex)),'Ç','C'),'Á','A'),'Ã','A'),'À','A'),'Ä','A'),'Â','A'),'É','E'),'È','E'),'Ë','E'),'Ê','E'),'Ü','U'),'Ú','U'),'Í','I'),'A‡','C'),'Ó','O'),'Ò','O'),'Ô','O'),'Õ','O'),'Ö','O'),'  ',' ') AS gerencia, und.regional, ger.id_ger, ger.nome_ger, IF(SUBSTR(und.codigo,3,3)='150' OR LEFT(und.codigo,2)='01','Administração',IF(IFNULL(gex.nome_gex,'')='','',IF(SUBSTR(und.codigo,1,2)='01',gex.nome_gex,CONCAT('Gerência Executiva em ',gex.nome_gex)))) AS nome_gex, gex.cod_gex, und.codigo, und.descricao, IFNULL(fun.desc_func,'') AS funcao
	FROM tabsetor AS und
	LEFT JOIN tabsetor_gex AS gex ON und.upag = gex.upag
	LEFT JOIN tabsetor_ger AS ger ON und.regional = ger.id_ger
	LEFT JOIN tabfunc AS fun ON und.codigo = fun.cod_lot
	WHERE
		und.ativo = 'S'
	";

	if ($_SESSION['sBrasil'] == "S")
	{
	}
	else if ($_SESSION['sSR'] == "S")
	{
		$sql .= "AND ger.id_ger='".$_SESSION['regional']."' ";
	}
	else if ($_SESSION['sUF'] == "S")
	{
		$sql .= "AND LEFT(und.codigo,2) = '".substr($_SESSION['sLotacao'])."' ";
	}
	else if ($_SESSION['sGEX'] == "S")
	{
		$sql .= "AND und.upag='".$_SESSION['upag']."' ";
	}

	$sql .= "
		AND fun.resp_lot='S'
		AND 0 <> ANY (SELECT COUNT(*) FROM servativ AS cad WHERE cad.cod_lot = und.codigo AND excluido='N' AND cod_sitcad NOT IN ('02','08','15','66'))
	GROUP BY
		fun.num_funcao
	ORDER BY
		ger.id_ger,
		IF(LEFT(und.codigo,2)='01',und.codigo,99999999999999),
		IF(SUBSTR(und.codigo,3,3)='150',0,CONCAT(LEFT(und.codigo,2),SUBSTR(und.codigo,4,2))),
		IF(SUBSTR(und.codigo,3,3)='150',0,
		IF(SUBSTR(und.codigo,3,1)='0',1,
		IF(SUBSTR(und.codigo,3,1)='2',2,
		IF(SUBSTR(und.codigo,3,1)='3',3,
		IF(SUBSTR(und.codigo,3,1)='4',4,
		IF(SUBSTR(und.codigo,6,3)='521',4,
		IF(SUBSTR(und.codigo,3,1)='5',6,
		IF(SUBSTR(und.codigo,3,1)='6',7,
		IF(SUBSTR(und.codigo,3,1)='7',8,
		IF(SUBSTR(und.codigo,3,1)='9',9,
		IF(SUBSTR(und.codigo,3,1)='1',10,
		IF(SUBSTR(und.codigo,3,1)='8',11, 99)))))))))))), und.codigo
	";

    return $sql;
}


/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : UnidadesLiberadasBrasil                      |
 * | @description : Monta sql unidades liberadas Brasil          |
 * |                                                             |
 * | @param  : void                                              |
 * | @return : <sql> - sql montado                               |
 * | @usage  : UnidadesLiberadasBrasil();                        |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase (class)                              |
 * +-------------------------------------------------------------+
 * */
function UnidadesLiberadasBrasil()
{
	$sql = "
	SELECT DISTINCTROW
		func.num_funcao, func.cod_lot, und.descricao, upg.nome_gex AS gerencia, und.regional
	FROM tabsetor AS und
	LEFT JOIN tabfunc AS func ON und.codigo = func.cod_lot
	LEFT JOIN tabsetor_gex AS upg ON und.upag = upg.upag AND upg.ativo='1'
	WHERE
		func.resp_lot = 'S'
		AND func.ativo = 'S'
		AND upg.ativa = 'S'
	GROUP BY
		func.cod_lot, func.num_funcao
	ORDER BY
		IF(SUBSTR(func.cod_lot,1,2)='01',1,2), und.regional, IF(SUBSTR(func.cod_lot,3,3)='150',1,2), upg.nome_gex, LEFT(func.cod_lot,2), func.cod_lot
	";

    return $sql;
}

/**
 * @param $dateinicial
 * @param $datefinal
 * @return bool
 */
function validateYears($dateinicial, $datefinal){

    $dateini = explode("/", $dateinicial);
    $datefim = explode("/", $datefinal);

    $first_year = $dateini[2];
    $last_year = $datefim[2];

    if($first_year == $last_year){
        return false;
    }

    return true;
}

/**
 * @param $seconds
 * @return string
 */
function convertSecondsToHours($seconds){

    return sec_to_time($seconds, $format = 'hh:mm');
}

/**
 * Generate an array of string dates between 2 dates
 *
 * @param string $start Start date
 * @param string $end End date
 * @param string $format Output format (Default: Y-m-d)
 * @param string $intervalo Intervalo de acréscimo
 *
 * @return array
 */
function getDatesFromRange($start, $end, $format = 'Y-m-d', $intervalo = 'P1D')
{
    $array = array();
    $interval = new DateInterval($intervalo);

    $realEnd = new DateTime($end);
    $realEnd->add($interval);

    $period = new DatePeriod(new DateTime($start), $interval, $realEnd);

    foreach($period as $date) {
        $array[] = $date->format($format);
    }

    return $array;
}

/**
 * @param $hour
 * @return int|null
 */
function parseHoursInSeconds($hour){
    return time_to_sec($hour);
}


/**
 * @param $valor
 * @return mixed|string
 */
function limpaCPF_CNPJ($valor){
    $valor = trim($valor);
    $valor = str_replace(".", "", $valor);
    $valor = str_replace(",", "", $valor);
    $valor = str_replace("-", "", $valor);
    $valor = str_replace("/", "", $valor);
    return $valor;
}

/**
 * @info Retorna data no padrão BR
 * @usage formata_data('2019-04-01'); ou
 *        formata_data('01/04/2019'); ou
 *        formata_data('20190401'); ou
 *          retorna '01/04/2019'
 *
 * @param string (date) $datas
 * @return string (date)
 */
function formata_data($datas)
{
    //$dt = explode("-", $datas);
    //$data_formatada = $dt[2] . "/" . $dt[1] . "/" . $dt[0];
    //return  $data_formatada;
    return databarra($datas);
}

/**
 * @info Retorna data invertida sem separadores
 * @usage formata_data_form('2019-04-01'); ou
 *        formata_data_form('01/04/2019'); ou
 *        formata_data_form('20190401'); ou
 *          retorna '20190401'
 *
 * @param string (date) $datas
 * @return string (date)
 */
function formata_data_form($datas)
{
    //$dt = explode("-", $datas);
    //$data_formatada = $dt[2] . $dt[1] . $dt[0];
    //return  $data_formatada;
    return  inverteData($datas);
}

/**
 * @info Retorna data no padrão US
 * @usage formata_data('2019-04-01'); ou
 *        formata_data('01/04/2019'); ou
 *        formata_data('20190401'); ou
 *          retorna '2019-04-01'
 *
 * @param string (date) $datas
 * @return string (date)
 */
function reverte_data($datas)
{
    //$dt = explode("/", $datas);
    //$data_formatada = $dt[2] . "-" . $dt[1] . "-" . $dt[0];
    //return $data_formatada;
    return conv_data($datas);
}

function retornaAlteraUsuario($script, $msg, $resetSession = true, $severidade = 'sucess')
{
    DataBase::fechaConexao();

    if ($resetSession === true)
    {
        $ModuloPrincipalAcionado              = $_SESSION['sModuloPrincipalAcionado'];
        destroi_sessao();
        session_start();
        $_SESSION['sModuloPrincipalAcionado'] = $ModuloPrincipalAcionado;
    }

    setMensagemUsuario($msg, $severidade);

    header("Location: /sisref/{$script}");
    exit();

}


/***
 * @param string $siape
 * @return string
 * @info Retorna a matricula atualizada
 */
function getNovaMatriculaBySiape($siape)
{
    if (_AMBIENTE_APLICACAO_ === 'HOM')
    {
        return removeOrgaoMatricula($siape);
    }
    else if (strlen($siape) == 7)
    {
        return substr($_SESSION['upag'],0,5) . $siape;
    }

    return $siape;
}


/***
 * @param string $siape
 * @return string
 * @info Retorna a matricula atualizada
 */
function removeOrgaoMatricula($siape)
{
    if (strlen($siape) != 7)
    {
        return substr($siape,5,11);
    }

    return $siape;
}


/**
 * @info Retorna os dias da semana
 *       a partir do dia informado
 *       ou do corrente dia
 *
 * @param string $dia
 * @return array
 */
function getWeekDates($dia=null)
{
    $lastWeek = array();

    if (is_null($dia) || empty($dia) || validaData($dia) === false)
    {
        $dia = date('Y-m-d');
    }

    $dia_semana = date('w', strtotime($dia));

    switch ($dia_semana)
    {
        case 0: //$day_ini = date("Y-m-d", strtotime( $dia . " +1 day")); break;
        case 6: //$day_ini = date("Y-m-d", strtotime( $dia . " +2 day")); break;
            return $lastWeek;
            break;
        case 1: $day_ini = date("Y-m-d", strtotime( $dia . " +0 day")); break;
        case 2: $day_ini = date("Y-m-d", strtotime( $dia . " -1 day")); break;
        case 3: $day_ini = date("Y-m-d", strtotime( $dia . " -2 day")); break;
        case 4: $day_ini = date("Y-m-d", strtotime( $dia . " -3 day")); break;
        case 5: $day_ini = date("Y-m-d", strtotime( $dia . " -4 day")); break;
    }

    $lastWeek[] = $day_ini;

    // create the dates from Monday to Sunday
    for($i=1; $i <= 6 ; $i++)
    {
        $lastWeek[] = date("Y-m-d", strtotime( $day_ini." + $i day") );
    }

    return $lastWeek;
}


/**
 * @info Retorna os dias da semana
 *       a partir do dia informado
 *       ou do corrente dia
 *
 * @param string $mes
 * @param string $ano
 * @return array
 */
function getMonthDates( $mes = null, $ano = null )
{
    $month = (is_null($mes) ? date('m') : $mes);
    $year  = (is_null($ano) ? date('Y') : $ano);

    $start_date = "01-".$month."-".$year;
    $start_time = strtotime($start_date);

    $end_time = strtotime("+1 month", $start_time);

    for($i=$start_time; $i<$end_time; $i+=86400)
    {
        $list[] = date('Y-m-d', $i);
    }

    return $list;
}


/**
 *
 * @param string $dia
 * @return array
 */
function getDatesInicioFimPeriodoUsufruto($dia=null)
{
    $lastWeek = array();

    if (is_null($dia) || empty($dia) || validaData($dia) === false)
    {
        $dia = date('Y-m-d');
    }

    $dias       = getWeekDates( $dia );
    $lastWeek[] = current( $dias );
    array_pop( $dias );
    array_pop( $dias );
    $lastWeek[] = end( $dias );

    return $lastWeek;
}


/***
 * @info Retorna mensagem a exibir, uso com ajax
 *
 * @param string $mensagem    Texto da mensagem
 * @param string $tipo        Tipo da mensagem
 * @return json
 */
function retornaInformacao($mensagem,$tipo='danger',$json=true,$destino='',$encode=true)
{
    if ($json && is_array($mensagem))
    {
        echo json_encode( $mensagem );
    }
    else if ($json && is_string($mensagem) && $encode == true)
    {
        echo json_encode(array("mensagem" => utf8_encode($mensagem), "tipo" => $tipo));
    }
    else if ($json && is_string($mensagem))
    {
        echo json_encode(array("mensagem" => $mensagem, "tipo" => $tipo));
    }
    else
    {
        replaceLink( $destino );
    }
    exit();
}


/***
 * @param string $upag
 * @return string
 * @info Retorna o código do Órgão
 */
function getOrgaoByUpag($upag=NULL)
{
    if (is_null($upag))
    {
        $upag = $_SESSION['upag'];
    }

    return substr($upag,0,5);
}

/***
 * @param string $uorg
 * @return string
 * @info Retorna o código do Órgão
 */
function getOrgaoByUorg($uorg=NULL)
{
    return getOrgaoByUpag($uorg);
}

/***
 * @param string $uorg
 * @return string
 * @info Retorna a sigla do Órgão
 */
function getOrgaoSiglaByUorg($uorg=NULL)
{
    $orgao = getOrgaoByUorg($uorg);

    $oDBase = new DataBase();
    $oDBase->query( "SELECT sigla FROM taborgao WHERE codigo = '$orgao' " );
    $string = $oDBase->fetch_object()->sigla;

    return $string;
}

/***
 * @param string $uorg
 * @return string
 * @info Retorna a sigla do Órgão
 */
function getOrgaoDescricaoByUorg($uorg=NULL)
{
    $orgao = getOrgaoByUorg($uorg);

    $oDBase = new DataBase();
    $oDBase->query( "SELECT denominacao FROM taborgao WHERE codigo = '$orgao' " );
    $string = $oDBase->fetch_object()->sigla;

    return $string;
}


/**
 * Retorna o código do ÓRGÃO e sigla.
 *
 * @param string $uorg [optional]
 *   Código do Órgão, se NULL pega o Órgão do usuário logado.
 *
 * @return string Retorna o código da Órgão e sua sigla.
 */
function getOrgaoMaisSigla($uorg=NULL)
{
    $string = getOrgaoByUpag($uorg) . ' - ' . getOrgaoSiglaByUorg($uorg);

    return $string;
}
/***
 * @param string $uorg
 * @return string
 * @info Retorna o código da UORG sem o do ORGAO
 */
function removeOrgaoLotacao($uorg)
{
    if (strlen($uorg) != 9)
    {
        return substr($uorg,5,14);
    }

    return $uorg;
}


/**
 * Retorna a Sigla da UORG.
 *
 * @param string $uorg [optional]
 *   Sigla da UORG, se NULL pega do usuário logado.
 *
 * @return string Retorna o código da UORG e sua descrição.
 */
function getUorgSigla($uorg=NULL)
{
    $uorg = getNovaUorg($uorg);

    $oDBase = new DataBase();
    $oDBase->query( "SELECT sigla FROM tabsetor WHERE codigo = '$uorg' " );
    $string = $oDBase->fetch_object()->descricao;

    return $string;
}


/**
 * Retorna o código da UORG e sua descrição.
 *
 * @param string $uorg [optional]
 *   Código UORG, se NULL pega do usuário logado.
 *
 * @return string Retorna o código da UORG e sua descrição.
 */
function getUorgDescricao($uorg = NULL)
{
    $uorg = getNovaUorg($uorg);

    $oDBase = new DataBase();
    $oDBase->query( "
        SELECT
            descricao
        FROM
            tabsetor
        WHERE
            codigo = :uorg
    ", array(
        array( ':uorg', $uorg, PDO::PARAM_STR )
    ));

    $string = $oDBase->fetch_object()->descricao;

    return $string;
}


/**
 * Retorna o código da UPAG do servidor/estagiário.
 *
 * @param string $siape [optional]  UPAG do servidor, se NULL pega do usuário logado.
 *
 * @return string Retorna o código da UPAG.
 */
function getUpag($siape = NULL)
{
    $siape = getNovaMatriculaBySiape( (is_null($siape) || empty($siape) ? $_SESSION['sMatricula'] : $siape) );

    $oDBase = new DataBase();
    $oDBase->query( "
        SELECT
            tabsetor.upag
        FROM
            servativ
        LEFT JOIN
            tabsetor ON servativ.cod_lot = tabsetor.codigo
        WHERE
            servativ.mat_siape = :siape
    ", array(
        array( ':siape', $siape, PDO::PARAM_STR)
    ));
    $string = $oDBase->fetch_object()->upag;

    return $string;
}


/**
 * Retorna o código da UORG e sua descrição.
 *
 * @param string $siape [optional]  UPAG do servidor, se NULL pega do usuário logado.
 *
 * @return string Retorna a descrição da UPAG.
 */
function getUpagDescricao($upag = NULL)
{
    $upag = getNovaUorg($upag);

    $oDBase = new DataBase();
    $oDBase->query( "
        SELECT
            tabsetor.descricao
        FROM
            tabsetor
        WHERE
            tabsetor.codigo = :upag
            AND tabsetor.upag = :upag
    ", array(
        array( ':upag', $upag, PDO::PARAM_STR )
    ));

    $string = $oDBase->fetch_object()->descricao;

    return $string;
}


/**
 * @info Retorna o código da UORG e sua descrição.
 *
 * @param string $uorg [optional]
 *   Código UORG, se NULL pega do usuário logado.
 *
 * @return string Retorna o código da UORG e sua descrição.
 *
 * @author Edinalvo Rosa
 */
function getUorgMaisDescricao($uorg=NULL)
{
    $string = removeOrgaoLotacao($uorg) . ' - ' . getUorgDescricao($uorg);

    return $string;
}


/**
 * @info Retorna o código da UORG novo (órgão+uorg).
 *
 * @param string $uorg [optional]
 *   Código UORG, se NULL pega do usuário logado.
 *
 * @return string Retorna o código da UORG.
 *
 * @author Edinalvo Rosa
 */
function getNovaUorg($uorg=NULL)
{
    if (is_null($uorg))
    {
        $uorg = $_SESSION['sLotacao'];
    }
    else if (strlen($uorg) == 9)
    {
        return substr($_SESSION['upag'],0,5) . $uorg;
    }

    return $uorg;
}


/**
 * @info Retorna HTML, com o padrão de exibição para a página,
 *       do código do ÓRGÃO e sigla, mais o código da UORG e sua descrição.
 *
 * @param string $uorg [optional]
 *   Código UORG, se NULL pega do usuário logado.
 * @param string $alignleft [optional]
 *   Define o alinhamento do código (padrão 'center').
 *
 * @return string Retorna HTML padrão com o código.
 *
 * @author Edinalvo Rosa
 */
function exibeDescricaoOrgaoUorg($uorg=NULL)
{
    ?>
    <div class="row margin-10">
        <div class="row">
            <div class="col-md-3 text-left">
                <p><b>ÓRGÃO: </b><?= getOrgaoMaisSigla($uorg); ?></p>
            </div>
            <div class="col-md-7 text-left">
                <p><b>UORG: </b><?= getUorgMaisDescricao($uorg); ?></p>
            </div>
        </div>
    </div>
    <?php
}


/**
 * @info Retorna HTML, com o padrão de exibição para a página,
 *       do código do ÓRGÃO e sua sigla
 *
 * @param string $uorg [optional]
 *   Código UORG, se NULL pega do usuário logado.
 * @param string $alignleft [optional]
 *   Define o alinhamento do código (padrão 'center').
 *
 * @return string Retorna HTML padrão com o código.
 *
 * @author Edinalvo Rosa
 */
function exibeDescricaoOrgao($uorg=NULL,$alignleft=true)
{
    $align = (is_null($alignleft) ? 'center' : ($alignleft ? 'left' : 'right'));

    ?>
    <div class="row margin-10">
        <div class="row">
            <div class="col-md-3 text-<?= $align; ?>">
                <p><b>ÓRGÃO: </b><?= getOrgaoMaisSigla($uorg); ?></p>
            </div>
        </div>
    </div>
    <?php
}


/**
 * @info Retorna HTML, com o padrão de exibição para a página,
 *       do código da UORG e a descrição da mesma
 *
 * @param string $uorg [optional]
 *   Código UORG, se NULL pega do usuário logado.
 * @param string $alignleft [optional]
 *   Define o alinhamento do código (padrão 'center').
 *
 * @return string Retorna HTML padrão com o código e descrição.
 *
 * @author Edinalvo Rosa
 */
function exibeDescricaoUorg($uorg=NULL,$alignleft=true)
{
    $align = (is_null($alignleft) ? 'center' : ($alignleft ? 'left' : 'right'));

    ?>
    <div class="row margin-10">
        <div class="row">
            <div class="col-md-7 text-<?= $align; ?>">
                <p><b>UORG: </b><?= getUorgMaisDescricao($uorg); ?></p>
            </div>
        </div>
    </div>
    <?php
}

/**
 * @info Interrompe a execução
 *
 * @param string/integer $linha [optional]
 *   Linha em que está a interrupção.
 * @param string $str [optional]
 *   Texto ou contéudo atribuido.
 * @param boolean $ajax
 *   Indica se a chamada foi via ajax
 *
 * @return void
 *
 * @author Edinalvo Rosa
 */
function fimDie($linha=0,$str='',$ajax=false,$funcao='')
{
    if (getenv('REMOTE_ADDR') == '::1' || getenv('REMOTE_ADDR') == '127.0.0.1')
    {
        if ($ajax === true)
        {
            retornaInformacao($_SERVER['PHP_SELF'] . (empty($funcao) ? '' : '<br>- ' . $funcao) . '<br>- Linha: ' . $linha.'<br>['.$str.']');
        }
        else
        {
            if (is_array($str) || is_object($str))
            {
                print '<pre>';
                var_dump($str);
                print '</pre>';
            }
            else
            {
                print '<pre>';
                print $str;
                print '</pre>';
            }

            die($_SERVER['PHP_SELF'] . (empty($funcao) ? '' : '<br>- ' . $funcao) . '<br>- Linha: ' . $linha);
        }
    }
}

/**
 * @info Dados frequência - Auxiliar
 *
 * @param string $siape
 *   Matrícula do servidor
 * @param string $mes
 *   Mês corrente
 * @param string $ano
 *   Ano corrente
 *
 * @return object/null
 *
 * @author Edinalvo Rosa
 */
function DadosFrequenciaAuxiliar($siape, $dia)
{
    ## competência
    #
    $comp = dataMes($dia) . dataAno($dia);

    ## instância a base de dados
    #
    $oDBase = new DataBase('PDO');

    ## DADOS DA FREQUÊNCIA
    #
    $oDBase->query("
    SELECT
        DATE_FORMAT(pto.dia,'%d/%m/%Y') AS dia, pto.entra, pto.intini,
        pto.intsai, pto.sai, pto.jornd, pto.jornp, pto.jorndif, pto.oco,
        oco.desc_ocorr AS dcod
    FROM
        ponto" . $comp . "_auxiliar AS pto
    LEFT JOIN
        tabocfre AS oco ON pto.oco = oco.siapecad
    WHERE
        pto.siape = :siape
        AND dia = :dia
    ORDER BY
        pto.dia, pto.oco
    ", array(
        array(':siape', $siape,          PDO::PARAM_STR),
        array(':dia',   conv_data($dia), PDO::PARAM_STR),
    ));

    if ($oDBase->num_rows() > 0)
    {
        return $oDBase;
    }
    else
    {
        return null;
    }
}


/**
 * @info Converte string de parâmetros via URL para array
 *
 * @param string $str
 *   Argumentos
 *
 * @return array $array
 *   Array com o resultado da conversão
 *
 * @author Edinalvo Rosa
 */
function args2array($str)
{
    $array = [];
    $link  = explode('?', $str);

    if (count($link) > 1)
    {
        $get = explode('&', $link[1]);
    }
    else
    {
        $get = explode('&', $link[0]);
    }

    for ($i = 0; $i < count($get); $i++)
    {
        $campos = explode('=', $get[$i]);
        $array[$campos[0]] = $campos[1];
    }

    return $array;
}


/**
 * @info Valida hora
 *
 * @name validaHoras
 *
 * @param string $campo Hora informada
 * @return boolean True hora válida
 */
function validaHoras($campo){
    return preg_match('/^(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9])/', $campo) ? true : false;
}

/**
 *
 * @param string $tableCol
 * @return array
 */
function enumExplode($table,$field)
{
    $query = "SHOW COLUMNS FROM ".$table." WHERE field = :field";
    $param = array(
        array( ":field", $field, PDO::PARAM_STR ),
    );

    $oDBase = new DataBase();
    $oDBase->query($query, $param);

    $res = $oDBase->fetch_array();

    $enum = $res['Type'];
    $set  = strtr($enum,array("enum('"=>"", "')"=>""));
    $enum = explode("','", $set);

    return $enum;
}


/**
 * Reformata e complementa a hora informada
 *
 * @param string $hora
 * @return string
 */
function strpadHora( $hora )
{
    $array     = explode(':',$hora);

    $hora_pad = str_pad( (int) $array[0], 2, '0', STR_PAD_LEFT)
        . ':' . str_pad( (int) $array[1], 2, '0', STR_PAD_LEFT)
        . ':' . str_pad( (int) $array[2], 2, '0', STR_PAD_LEFT);

    return $hora_pad;
}


/**
 * Complementa a ocorrência com zeros a esquerda
 *
 * @param string $ocorrencia
 * @return string
 */
function strPadOcorrencia( $ocorrencia )
{
    return (empty(trim($ocorrencia)) ? $ocorrencia : str_pad($ocorrencia, 5, "0", STR_PAD_LEFT));
}


/**
 * Converte caracteres especiais para a realidade HTML
 * e retira as tags HTML e PHP de uma string
 *
 * @param string $str
 * @param string $encoding
 * @return string
 */
function tratarHTML($str, $encoding='iso-8859-1')
{
    $str = ajustar_acentos($str);
    $str = (check_utf8($str) ? str_to_utf8($str) : $str);
    return htmlspecialchars(strip_tags($str), ENT_QUOTES, $encoding);
}


/**
 * Verifica o encoding
 *
 * @param string $str
 * @return boolean
 */
function check_utf8($str)
{
    $len = strlen($str);

    for($i = 0; $i < $len; $i++)
    {
        $c = ord($str[$i]);

        if ($c > 128)
        {
            if     ($c > 247) return false;
            elseif ($c > 239) $bytes = 4;
            elseif ($c > 223) $bytes = 3;
            elseif ($c > 191) $bytes = 2;
            else return false;

            if (($i + $bytes) > $len) return false;

            while ($bytes > 1)
            {
                $i++;
                $b = ord($str[$i]);
                if ($b < 128 || $b > 191) return false;
                $bytes--;
            }
        }
    }

    return true;
} // Fim check_utf8


/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : VerificaRegistrosHorariosFrequenciaServidor  |
 * | @description : Verifica se há registro de:                  |
 * |                - Frequência realizado no dia;               |
 * |                - Horário de saída p/ almoço já realizado    |
 * | @param  : <matricula> - matrícula siape do usuário          |
 * |           <data>      - data para registro                  |
 * |           <registro>  - tipo do registro:                   |
 * |                         "saida_almoco", "retorno_almoco",   |
 * |                         "fim_expediente"                    |
 * | @return : void                                              |
 * | @usage  : VerificaRegistrosHorariosFrequenciaServidor();    |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase (class)                              |
 * +-------------------------------------------------------------+
 * */
function VerificaRegistrosHorariosFrequenciaServidor($sMatricula=null, $vDatas=null, $tipo_registro=null)
{
    // destino se há erro
    $destino_erro = "entrada.php";

    // dados
    $hoje = databarra($vDatas);
    $comp = dataMes($vDatas) . dataAno($vDatas);

    // instancia banco de dados
    $oDBase = new DataBase('PDO');
    $oDBase->setMensagem( "Falha no registro do ponto!" );
    $oDBase->setDestino( $destino_erro );

    // testa se já registrou saida para almoço
    $oDBase->query( "SELECT entra, intini, intsai, sai, oco FROM ponto$comp WHERE siape = :siape AND dia = :dia ",
    array(
        array(':siape', $sMatricula, PDO::PARAM_STR),
        array(':dia', $vDatas, PDO::PARAM_STR)
    ));

    if ($oDBase->num_rows() == 0)
    {
        //RegistraAcessoIndevido(); // Acesso indevido registrado.
        retornaErro(
            $destino_erro,
            "Não há registro de frequência neste dia (" . $hoje . ")!"
        );
    }
    else
    {
        $ponto = $oDBase->fetch_object();

        // tipo registro - mensagem
        switch ($tipo_registro)
        {
            case 'saida_almoco':
                $mensagem = 'saída para almoço';
                $horario  = $ponto->intini;
                break;

            case 'retorno_almoco':
                $mensagem = 'retorno do almoço';
                $horario  = $ponto->intsai;
                break;

            case 'fim_expediente':
                $mensagem = 'fim do expediente';
                $horario  = $ponto->sai;
                break;
        }

        if (time_to_sec($horario) > 0)
        {
            retornaErro(
                $destino_erro,
                "Já há registro de " . $mensagem . ", realizado às " . left( $horario, 5 )
                /*
                . "\\nEntrada........................: " . left( $ponto->entra, 5 )
                . "\\nSaída para Almoço....: "            . left( $ponto->intini, 5 )
                . (time_to_sec($ponto->intsai) > 0 ? "\\nRetorno do Almoço...: "   . left( $ponto->intsai, 5 ) : "")
                . (time_to_sec($ponto->sai) > 0    ? "\\nFim do Expediente....: " . left( $ponto->sai, 5 ) : "")
                */
            );
        }
    }
}



/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : RegistraAcessoIndevido                       |
 * | @description : Registra tentativas de acesso anormal        |
 * |                                                             |
 * | @param  : void                                              |
 * | @return : void                                              |
 * | @usage  : RegistraAcessoIndevido();                         |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase (class)                              |
 * +-------------------------------------------------------------+
 * */
function RegistraAcessoIndevido()
{
    // instancia o banco de dados
    $oDBase = new DataBase('PDO');

    $logLotacao    = $_SESSION["sLotacao"];
    $logMatricula  = $_SESSION["sMatricula"];
    $logHoras      = strftime("%H:%M:%S",time());
    $logDatas      = date("Y-m-d");
    $logIp         = $_SERVER["REMOTE_ADDR"]; //linha que captura o ip do usuario.
    $path_parts    = pathinfo($_SERVER["PHP_SELF"]);
    $logPagina     = $path_parts['basename'];
    $logParametros = '';
    $operacao      = 'Tentativa de acesso por fora da página do SISTEMA, por alteração de endereço no browser, ou alteração de dados na página (html).';

    foreach ($_REQUEST as $key => $value)
    {
    	$valor = ($key=='lSenha' ? "" : $value );
    	$logParametros .= "$key: $valor :|:";
    }

    if ( empty($logMatricula) || empty($pagina_de_origem) )
    {
    	$logQuery = "INSERT INTO ilegal_desconhecido (siape, operacao, datag, hora, maquina, setor, script, parametros) VALUES ('$logMatricula','$operacao','$logDatas','$logHoras','$logIp', '$logLotacao','$logPagina','$logParametros')";
    }
    elseif ( !empty($logMatricula) )
    {
    	$logQuery = "INSERT INTO ilegal (siape, operacao, datag, hora, maquina, setor, script, parametros) VALUES ('$logMatricula','$operacao','$logDatas','$logHoras','$logIp', '$logLotacao','$logPagina','$logParametros')";
    }

    $logResult = $oDBase->query($logQuery);
}



/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : getIpSaidaOrgao                              |
 * | @description : Carrega o IP de saida do Orgao               |
 * |                                                             |
 * | @param  : void                                              |
 * | @return : string IP de saida do Orgao                       |
 * | @usage  : getIpSaidaOrgao();                                |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : https://l2.io/ip.js                           |
 * | @dependence : http://meuip.com/api/meuip.php                |
 * +-------------------------------------------------------------+
 * */
function getIpSaidaOrgao($servico=null)
{
    switch ($servico)
    {
        case 'meuip':
            //$ip = file_get_contents('http://meuip.com/api/meuip.php');
            break;

        default:
            //$result = file_get_contents('https://l2.io/ip.js?var=userip');
            //$vetor = explode('=',$result);
            //$ip = trim(strtr($vetor[1], array('"'=>'',"'"=>'',';'=>'')));
            break;
    }

    $ip = getIpReal();

    return $ip;
}


/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : CarregaDadoMunicipio                         |
 * | @description : Carrega cidade e uf de uma determinada cidade|
 * |                                                             |
 * | @param  : <string> $codmun                                  |
 * | @return : result                                            |
 * | @usage  : CarregaDadoMunicipio($codmun);                    |
 * | @author : Edinalvo Rosa                                     |
 * +-------------------------------------------------------------+
 * */
function CarregaDadoMunicipio($codmun)
{
    $oDBase = new DataBase('PDO');
    $oDBase->query("
    SELECT
        cidades.numero, cidades.nome, cidades.uf
    FROM
        cidades
    WHERE
        cidades.numero = :numero
    ", array(
        array(":numero", $codmun, PDO::PARAM_STR)
    ));

    return $oDBase;
}
/*
 * @info Carrega os dados dos setores
 *
 * @param string $setor Código da unidade
 * @return result Resultado da seleção
 *
 * @dependence : DataBase (class)
 *
 * @author Edinalvo Rosa
 */
function CarregaDadosDosSetores( $setor )
{
    $oDBase = new DataBase('PDO');
    $oDBase->query("
    SELECT
        tabsetor.periodo_excecao , tabsetor.codigo, tabsetor.descricao,
        tabsetor.cod_uorg, tabsetor.upag, tabsetor.uorg_pai, tabsetor.ug,
        tabsetor.ativo, tabsetor.area, tabsetor.inicio_atend, tabsetor.fim_atend,
        tabsetor.sigla, tabsetor.codmun, tabsetor.fuso_horario,
        tabsetor.horario_verao
    FROM
        tabsetor
    WHERE
        codigo = :codigo
    ", array(
        array(":codigo", $setor, PDO::PARAM_STR)
    ));

    return $oDBase;
}


/*
 * @info Monta listbox dos Municípios
 *
 * @param string $codmun Código do município
 * @return HTML
 *
 * @dependence : $montaSelect (function)
 *
 * @author Edinalvo Rosa
 */
function montaSelectDadosDosMunicipios( $codmun )
{
    $sql = "
    SELECT
        cidades.numero, cidades.nome, cidades.uf
    FROM
        cidades
    ORDER BY
        cidades.nome
    ";

    return montaSelect($codmun, $sql, '', $imprimir=false);
}


/*
 * @info Monta vetor com os campos da tabela para uso com DataTables
 *
 * @param string $table Nome da tabela
 * @return array
 *
 * @author Edinalvo Rosa
 */
function montaArrayCampos($table)
{
    $oDBase = new DataBase();
    $oDBase->query("DESCRIBE ".$table);

    $seq = 0;
    $columns = array();

    while ($campos = $oDBase->fetch_assoc())
    {
        switch ($campos['Type'])
        {
            case 'date':
    	        $columns[] = array(
    		        'db'        => $campos['Field'],
    		        'dt'        => $seq++,
    		        'formatter' => function( $d, $row ) {
    			        return date( 'd/m/Y', strtotime($d));
    		        }
    	        );
                break;

            default:
                $columns[] = array( 'db' => $campos['Field'], 'dt' => $seq++ );
            break;
        }
    }

    return $columns;
}


/*
 * @info Monta vetor com os campos da tabela para uso com DataTables
 *
 * @param string $grupoOperacao Módulo origem (homologar/acompanhar/...)
 * @param string $comp          Competência a ser tratada,
 *                              usada se não for manutenção de histórico
 * @return string Nome da tabela de trabalho
 *
 * @author Edinalvo Rosa
 */
function nomeTabelaFrequencia($grupoOperacao, $comp)
{
    if ($grupoOperacao == "historico_manutencao")
    {
        $arquivo = $_SESSION['sHArquivoTemp'];
    }
    else
    {
        $arquivo = "ponto".$comp;
    }

    return $arquivo;
}


/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : CodigoEventoEsportivoNaoExibir               |
 * | @description : Seleciona códigos de crédito e débito que    |
 * |                não serão exibidos em lista de códigos de    |
 * |                ocorrência                                   |
 * |                                                             |
 * | @param  : <data>                                            |
 * | @return : <codigos_a_excluir>                               |
 * | @usage  : CodigoEventoEsportivoNaoExibir($data);            |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase (class)                              |
 * +-------------------------------------------------------------+
 * */
function CodigoEventoEsportivoNaoExibir($data_excessao='')
{
    $oEventos = new TabFacultativo172Controller();

    return $oEventos->CodigoEventoEsportivoNaoExibir($data_excessao);
}


/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : EventosCodigos                               |
 * | @description : Códigos de crédito e débito referente a      |
 * |                eventos esportivos facultado compensar       |
 * |                                                             |
 * | @param  : <void>                                            |
 * | @return : <codigos>                                         |
 * | @usage  : EventosCodigos();                                 |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase (class)                              |
 * +-------------------------------------------------------------+
 * */
function EventosCodigos($tipo_codigo='')
{
    $oEventos = new TabFacultativo172Controller();

    return $oEventos->EventosCodigos($tipo_codigo);
}


/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : verificaEventos                              |
 * | @description : Verifica se a ocorrência indica pode ser     |
 * |                utilizada no dia desejado                    |
 * |                                                             |
 * | @param  : <ocorrencia>                                      |
 * | @param  : <data>                                            |
 * | @return : <mensagem|vazio>                                  |
 * | @usage  : verificaEventos($ocor,$data);                     |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase (class)                              |
 * +-------------------------------------------------------------+
 * */
function verificaEventos($ocor='',$data='')
{
    $oEventos = new TabFacultativo172Controller();

    return $oEventos->verificaEventos($ocor,$data);
}


/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : verificaPeriodoCompensacaoEvento             |
 * | @description : Seleciona códigos de crédito e débito que    |
 * |                verifica se a data está dentro do período de |
 * |                compensação das horas devidas em dias de     |
 * |                evento esportivo                             |
 * |                                                             |
 * | @param  : <ocorrencia>                                      |
 * | @param  : <data>                                            |
 * | @return : <mensagem|vazio>                                  |
 * | @usage  : verificaPeriodoCompensacaoEvento($ocor,$data);    |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase (class)                              |
 * +-------------------------------------------------------------+
 * */
function verificaPeriodoCompensacaoEvento($ocor='',$data='')
{
    $oEventos = new TabFacultativo172Controller();

    return $oEventos->verificaPeriodoCompensacaoEvento($ocor,$data);
}


/**  @Function
 * +-------------------------------------------------------------+
 * | @function    : verificaDiaEventoAutorizado                  |
 * | @description : Seleciona códigos de crédito e débito que    |
 * |                e se a ocorrência é permitida para este dia  |
 * |                                                             |
 * | @param  : <ocorrencia>                                      |
 * | @param  : <data>                                            |
 * | @return : <mensagem|vazio>                                  |
 * | @usage  : verificaDiaEventoAutorizado($ocor,$data);         |
 * | @author : Edinalvo Rosa                                     |
 * |                                                             |
 * | @dependence : DataBase (class)                              |
 * +-------------------------------------------------------------+
 * */
function verificaDiaEventoAutorizado($ocor='',$data='')
{
    $oEventos = new TabFacultativo172Controller();

    return $oEventos->verificaDiaEventoAutorizado($ocor,$data);
}


/*
 * @info Verifica se o servidor é participante de Programa de Gestão
 *
 * @param string $siape Matrícula siape do servidor
 * @param string $dia   Data para pesquisa
 * @return array
 *
 * @dependence : DataBase (class)
 *
 * @author Edinalvo Rosa
 */
function participanteProgramaGestao($siape, $dia=null)
{
    $result = array(
        'programa' => '',
        'jornada'  => 0
    );

    if (existeDBTabela('adesao_programas_concessoes'))
    {
        if (is_null($dia) || empty($dia))
        {
            $query = "
                SELECT
                    siape,
                    programa,
                    DATE_FORMAT(data_inicio,'%Y-%m-%d') AS data_inicio
                FROM
                    adesao_programas_concessoes
                WHERE
                    siape = :siape
                    AND (DATE_FORMAT(NOW(),'%Y-%m-%d') >= DATE_FORMAT(data_inicio,'%Y-%m-%d')
                        AND DATE_FORMAT(NOW(),'%Y-%m-%d') <= IF(DATE_FORMAT(data_termino,'%Y-%m-%d')='0000-00-00','9999-99-99',DATE_FORMAT(data_termino,'%Y-%m-%d')))
            ";

            $params = array(
                array( ':siape', $siape, PDO::PARAM_STR ),
            );
        }
        else
        {
            $query = "
                SELECT
                    siape,
                    programa,
                    DATE_FORMAT(data_inicio,'%Y-%m-%d') AS data_inicio
                FROM
                    adesao_programas_concessoes
                WHERE
                    siape = :siape
                    AND (:dia >= DATE_FORMAT(data_inicio,'%Y-%m-%d')
                        AND :dia <= IF(DATE_FORMAT(data_termino,'%Y-%m-%d')='0000-00-00','9999-99-99',DATE_FORMAT(data_termino,'%Y-%m-%d')))
            ";

            $params = array(
                array( ':siape', $siape,          PDO::PARAM_STR ),
                array( ':dia',   conv_data($dia), PDO::PARAM_STR ),
            );
        }

        // PARTICIPANTE DO PROGRAMA DE GESTÃO SEMI-PRESENCIAL (PGSP)
        $oDBase = new DataBase();
        $oDBase->query( $query, $params );

        if ($oDBase->num_rows() > 0)
        {
            $oGestao = $oDBase->fetch_object();

            // PROGRAMA DE GESTÃO SEMI-PRESENCIAL
            $result['programa'] = $oGestao->programa;

            if ($oGestao->programa == '90300')
            {
                $result['jornada'] = 20;
            }
        }
    }

    return $result;
}

/**
 * @info Calculada Jornada realizada e Diferença para ponto principal,
 *       considerando as taelas principal e auxiliar
 *
 * @param string $siape
 * @param string/null $dia
 * @param string/null $lotacao
 *
 * return array Jornada realizada e Diferença para ponto principal
 */
function apuraDiferencasPontoAuxiliar( $siape = null, $dia = null, $lotacao = null, $jornada_realizada = "00:00" )
{
    if (is_null($siape))
    {
        return null;
    }

    // dados de retorno
    $nova_diferenca = null;

    // instancia classes
    $oDBase                       = new DataBase();
    $objDadosServidoresController = new DadosServidoresController();
    $objOcorrenciasGrupos         = new OcorrenciasGrupos();
    $oJornadaTE                   = new DefinirJornada();

    // Matricula no padrao orgao+siape
    $siape      = getNovaMatriculaBySiape( $siape );
    $sitcad     = $objDadosServidoresController->getSigRegJur( $siape );
    $dia        = (is_null($dia) ? date('Y-m-d') : conv_data($dia));
    $comp       = dataMes($dia) . dataAno($dia);
    $lotacao    = (is_null($lotacao) ? $_SESSION['uorg'] : $lotacao);

    $codigoDiferencaNegativa = $objOcorrenciasGrupos->SaldoNegativo($sitcad);

    // verifica autorizacao
    $oJornadaTE->setSiape($siape);
    $oJornadaTE->setLotacao($lotacao);
    $oJornadaTE->setData($dia);
    $oJornadaTE->setChefiaAtiva();
    $oJornadaTE->estabelecerJornada();

    $jornada = $oJornadaTE->getJ();

    $oDBase->query( "
    SELECT
        pto.oco,
	SEC_TO_TIME(SUM(TIME_TO_SEC(pto.jornd) + TIME_TO_SEC(ptoa.jornd))) AS jornd
    FROM
        ponto".$comp." AS pto
    LEFT JOIN
        ponto".$comp."_auxiliar AS ptoa ON pto.siape = ptoa.siape
    WHERE
        pto.siape = :siape
        AND pto.dia = :dia
    ", array(
        array( ":siape", $siape, PDO::PARAM_STR),
        array( ":dia",   $dia,   PDO::PARAM_STR),
    ));

    $dados = $oDBase->fetch_object();

    if (in_array($dados->oco, $codigoDiferencaNegativa))
    {
        $saldo = (time_to_sec($jornada) - (time_to_sec($dados->jornd)+time_to_sec($jornada_realizada)));

        if ($saldo < 0)
        {
            $saldo = 0;
        }

        $nova_diferenca = sec_to_time($saldo, "hh:mm");
    }

    return $nova_diferenca;
}


/**
 *
 * @param array $dados Campos e valores da tabela
 * @param string $tabela Tabela a ser usada
 * @return array Campos e parametros
 */
function preparaQueryParams($dados = null, $tabela = 'servativ')
{
    if (is_null($dados))
    {
        return false;
    }

    $campos = array();
    $fields = array();
    $params = array();

    $oDBase = new DataBase();

    $oDBase->query("DESC " . $tabela);

    while ($rows = $oDBase->fetch_assoc())
    {
        $campos[$rows['Field']] = $rows;
    }

    foreach ($dados as $key => $value)
    {
        if (array_key_exists($key, $campos))
        {
            $fields[]   = $key.' = :'.$key;

            if (strtoupper($campos[$key]['Null']) === 'NO' && is_null($value))
            {
                $valor = $campos[$key]['Default'];
            }
            else
            {
                $valor = ((substr(strtoupper($campos[$key]['Type']),0,3) === 'INT') && !is_int($value) ? intval($value,10) : $value);
            }

            if (substr(strtoupper($campos[$key]['Type']),0,3) === 'INT')
            {
                $pdo_param = PDO::PARAM_INT;
            }
            else
            {
                $pdo_param = PDO::PARAM_STR;
            }

            $params[] = array( ':'.$key, $valor, $pdo_param);
        }
    }

    return array(
                'fields' => implode(', ',$fields),
                'params' => $params
           );
}


/**
 * @info Carrega os dados da tabela (campos)
 *       Se na coluna 'Comment' tiver ' | ', tratará
 *       o texto antes de ' | ' como nome da coluna
 *
 * @param string $tabela Tabela a ser usada
 * @return array Nomes dos campos
 */
function loadNameFieldsTables($tabela = 'servativ', $fields_extra = null)
{
    $fields = array();

    $oDBase = new DataBase();

    $oDBase->query("SHOW FULL COLUMNS FROM " . $tabela);

    while ($rows = $oDBase->fetch_assoc())
    {
        $descricao = explode(" | ",$rows['Comment']);

        if (strtolower($descricao[0]) == 'desativada')
        {
            continue;
        }

        if (count($descricao) > 1)
        {
            $descr_campo = $descricao[0];
        }
        else
        {
            $descr_campo = $rows['Field'];
        }

        $campos[$tabela.".".$rows['Field']] = array( $descr_campo, $rows['Type']);
    }

    if ( is_array($fields_extra) )
    {
        foreach($fields_extra as $key => $value)
        {
            //
        }
    }

    return $campos;
}


/*
 * @param void
 *
 * @info Lista mes e ano
 */
function CarregaSelectCompetencia($ano=NULL, $mes=NULL)
{
    $mes    = (is_null($mes) ? date('m') : $mes);
    $ano    = (is_null($ano) ? date('Y') : $ano);
    $compet = $mes . '/' . $ano;
    $start  = '2018-01-01';
    $end    = date('Y').'-'.(substr('00'.(date('n')-1),-2)).'-'.date('d');
    

    $list = getDatesFromRange($start, $end, $format = 'm/Y', $intervalo = 'P1M');

    ?>
    <select class="form-control select2-single" id="competencias_opcoes" name="competencias_opcoes">
        <?php

        foreach($list AS $opcao)
        {
            $value    = substr($opcao,-4).substr($opcao,0,2);
            $selected = ($opcao === $compet ? " selected" : "");

            ?>
            <option value='<?= tratarHTML($value); ?>'<?= tratarHTML($selected); ?>><?= tratarHTML($opcao); ?></option>
            <?php
        }

        ?>
    </select>
    <small style="font-size:9px;vertical-align:top;margin-top:0px;padding-left:3px;padding-top:0px;">(<?= count($list); ?> meses)</small>
    <?php
}


/**
 * @info Define a duracao da sessão em minutos
 * 
 * @return array
 */
function getDuracaoDaSessaoEmMinutos()
{
    switch ($_SESSION['sModuloPrincipalAcionado'])
    {
        case "rh":
        case "sogp":
        case "chefia":
            return 30;
            break;

        default:
            return 1;
            break;
    }
}
