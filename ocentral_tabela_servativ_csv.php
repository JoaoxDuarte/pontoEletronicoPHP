<?php

include_once( "config.php" );
include_once( "inc/datatables_funcoes_serverside.php" );
include_once( "inc/class_grava_arquivo_txt.php" );

// Set necessary headers
header("Content-Type: text/event-stream");
header("Cache-Control: no-cache");
//header("Connection: keep-alive");

if ($_SESSION["logado"] !== 'SIM')
{
    send_message('CLOSE', 'Process complete', 'novo_login');
    exit();
}

// memoria
if (substr(PHP_OS,0,3) == 'WIN')
{
    $nMemoria = "512M";

    // define informacoes para o uso da mPDF50
    ini_set("memory_limit", $nMemoria);
}

// tabela
$tabela       = 'servativ';
$campos_extra = array();
//$campos_extra[] = array( 'Index' => 10, 'Field' => 'tabsetor.descricao', 'Comment' =>'');

$campos = loadNameFieldsTables( $tabela, $campos_extra );

foreach( $campos as $key => $value)
{
    $colunas[] = $key;
}

// NOME DO ARQUIVO QUE SERÁ GERADO
$nome_arquivo = ($_SESSION['sAdmCentral'] == "S" ? "000000000_" : $_SESSION['upag_selecao'] . "_") . $tabela . ".csv";
$dir          = "extracoes/";

$download     = "extracoes/" . $nome_arquivo;

// somente apagar o arquivo gerado antes
if (isset($_POST['apagar']) && !empty($_POST['apagar']))
{
    unlink($download);
    $myData = array(
		'seq' => number_format($id,0,',','.'),
		'total' => number_format($num_rows,0,',','.'),
		'progress' => $progress,
		'download' => $_POST['apagar']
	);
    print json_encode($myData);
    exit();
}

//$filtro_upag = ($_SESSION['sAdmCentral'] == "S" ? " true " : "tabsetor.upag = '" . $_SESSION['upag_selecao'] . "' ");
$filtro_upag = "tabsetor.upag = '" . $_SESSION['upag_selecao'] . "' ";

$limit  = limit( $_REQUEST );
$filter = filter( $_REQUEST, $colunas );
$order  = order( $_REQUEST, $colunas );

$oDBase = new DataBase();
$oDBase->query( "SET CHARSET 'latin1'" );

// APURA O TOTAL DE REGISTROS
$recordsTotal = 0;

// seleciona os registros
$oDBase->query( "
SELECT " . implode(', ', $colunas) ."
FROM " . $tabela . "
LEFT JOIN tabsetor ON servativ.cod_lot = tabsetor.codigo    
WHERE " . $filtro_upag . $filter . "
" . (empty($order) ? " ORDER BY ".$tabela.".mat_siape " : $order) . "
" . $limit);
$recordsFiltered = $oDBase->num_rows();

// carrega dados e gerar arquivo para download
$num_rows = $recordsFiltered;
$i        = 0;

// GRAVAR ARQUIVO PARA DOWNLOAD
chmod( $dir, 0777 );

// incializa a classe e cria o arquivo.
$_arq = new arquivoTxt();
$_arq->cria( $dir . $nome_arquivo, "w+" );
$_arq->abre();

$result = implode(';', $colunas) . PHP_EOL;
$result = strtr($result, array($tabela."." => ""));

$_arq->grava( $result );

while ($dados = $oDBase->fetch_assoc())
{
    $string = "";

    for ($id = 0; $id < count($colunas); $id++)
    {
        $field  = strtr($colunas[$id], array($tabela."." => ""));
        $type  = soLetras($campos[$tabela.".".$field][1]);

        $conteudo = ajustar_acentos(preparaTextArea($dados[$field],'para_csv'));
        $conteudo = str_replace("\r\n", ' - ', $conteudo);
        $conteudo = str_replace("\r", ' - ', $conteudo);
        $conteudo = str_replace("\n", ' - ', $conteudo);
        $conteudo = str_replace(";", ' - ', $conteudo);

        switch ($type)
        {
            case 'date':
                $string .= databarra($conteudo) . ';';
                break;

            case 'text':
            case 'char':
            case 'varchar':
                $string .= $conteudo . ';';
                break;

            default:
                $string .= $conteudo . ';';
                break;
        }
    }

    $result = $string . PHP_EOL;

    $_arq->grava( $result );

    $i++;
    send_message($i, round(($i*100)/$num_rows,0), $download, $num_rows);
	usleep(6000); // usleep(250000); // 2000000 milésimos de segundos = 2 segundos
}

// fecha o arquivo
$_arq->fecha();

chmod( $dir, 0777 );
chmod( $dir . $nome_arquivo, 0777 );

send_message('CLOSE', 'Process complete', $download);


/**
 * 
 * @param integer $id
 * @param integer $progress
 * @param string  $download
 * @param integer $num_rows
 */
function send_message($id, $progress=100, $download='', $num_rows=100)
{
	$d = array(
		'seq' => number_format($id,0,',','.'),
		'total' => number_format($num_rows,0,',','.'),
		'progress' => $progress,
		'download' => $download
	);
	echo "id: $id" . PHP_EOL;
	echo "data: " . json_encode($d) . PHP_EOL;
	echo PHP_EOL;

	ob_flush();
	flush();
}

/*
// carrega dados e gerar arquivo para download
$i        = 0;
$num_rows = 100;

$download = 'cadastro.csv';

while (true)
{
    $i++;
    send_message($i, round(($i*100)/$num_rows,0), $download, $num_rows);
    usleep(15000); // usleep(250000); // 2000000 milésimos de segundos = 2 segundos
    
    if ($i >= $num_rows)
    {
        break;
    }
}

send_message('CLOSE', 'Process complete', $download);

exit();
*/
