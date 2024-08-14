<?php

// Set necessary headers
header("Content-Type: text/event-stream");
header("Cache-Control: no-cache");
//header("Connection: keep-alive");

session_start();

set_time_limit (10000);

// funções e classes de uso geral
include_once( '../../../class_database.php' );

$mes  = $_SESSION['mes'];
$ano  = $_SESSION['ano'];
$upag = $_SESSION['upag'];

$filtrar = (isset($_GET['filtrar']) ? $_GET['filtrar'] : "");

$oDBase = new DataBase();

// lista de servidores cedidos e descentralizados
// sem verificação de homologaçãoão da frequência
$oDBase->query( "
SELECT 
    cad.mat_siape, cad.nome_serv AS nome_serv, cad.cod_lot, und.descricao, cad.jornada, cad.freqh, 
    und.upag, cad.cod_sitcad, tabsitcad.descsitcad, IFNULL(hom.homologado,'N') AS homologado
FROM
    servativ AS cad
LEFT JOIN
    tabsetor AS und ON cad.cod_lot = und.codigo
LEFT JOIN
    homologados AS hom ON (cad.mat_siape = hom.mat_siape) AND (hom.compet = '".$ano.$mes."')
LEFT JOIN 
    tabsitcad ON cad.cod_sitcad = tabsitcad.codsitcad
WHERE
    cad.cod_sitcad IN ('03') 
    AND und.upag IN ('".$upag."') 
    AND excluido = 'N'
    " . (empty($filtrar) ? "" : " AND CONCAT(cad.mat_siape,'!',cad.nome_serv,'!',cad.cod_lot) LIKE '%".$filtrar."%' ") . "

UNION

SELECT 
    cad.mat_siape, cad.nome_serv AS nome_serv, cad.cod_lot, und.descricao, cad.jornada, cad.freqh, 
    und.upag, cad.cod_sitcad, CONCAT(tabsitcad.descsitcad,'\nEXERCICIO DESCENTRALIZADO') AS descsitcad, IFNULL(hom.homologado,'N') AS homologado
FROM
    servativ AS cad
LEFT JOIN
    tabsetor AS und ON cad.cod_lot = und.codigo
LEFT JOIN
    homologados AS hom ON (cad.mat_siape = hom.mat_siape) AND (hom.compet = '".$ano.$mes."')
LEFT JOIN 
    tabsitcad ON cad.cod_sitcad = tabsitcad.codsitcad
WHERE
    cad.cod_sitcad IN ('18') 
    AND und.upag IN ('".$upag."') 
    AND excluido = 'N' 
    AND 2 = ANY (SELECT COUNT(*) 
                    FROM servativ AS cad2 
                        WHERE cad2.cpf = cad.cpf 
                              AND cad2.cod_sitcad IN ('01','18') 
                              AND excluido = 'N')
    " . (empty($filtrar) ? "" : " AND CONCAT(cad.mat_siape,'!',cad.nome_serv,'!',cad.cod_lot) LIKE '%".$filtrar."%' ") . "

ORDER BY
    IF(homologado='N',1,2), nome_serv
" );
$num_rows = $oDBase->num_rows();
$i = 0;
            
while (list($mat_siape, $nome_serv, $cod_lot, $descricao, $jornada, $freqh, $upag, $cod_sitcad, $descsitcad, $homologado) = $oDBase->fetch_array())
{
	$i++;
    send_message($i, round(($i*100)/$num_rows,0), $mat_siape, $nome_serv, $cod_lot, $descricao, $jornada, $freqh, $upag, $cod_sitcad, $descsitcad, $homologado, $num_rows);
    //sleep(1); // segundos
	usleep(150000); // usleep(250000); // 2000000 milésimos de segundos = 2 segundos
}

/*
$num_rows = 30;

for($i = 1; $i <= $num_rows; $i++) {
		$dados = array();
    send_message($i, round(($i*100)/$num_rows,0), 'Nome'.$i, substr('0000000'.$i,-7), '04001000', '08:00', $num_rows);
    //sleep(1); // segundos
		usleep(100000); // usleep(250000); // 2000000 milésimos de segundos = 2 segundos
}
*/

send_message('CLOSE', 'Process complete');


function send_message($id, $progress=100, $mat_siape='', $nome_serv='', $cod_lot='', $descricao='', $jornada='', $freqh='', $upag='', $cod_sitcad='', $descsitcad='', $homologado='', $num_rows=100)
{
	$d = array(
		'seq' => number_format($id,0,',','.'),
		'total' => number_format($num_rows,0,',','.'),
		'progress' => $progress,
		'nome_serv'  => $nome_serv,
		'mat_siape'  => $mat_siape,
		'sitcad'     => $cod_sitcad,
		'descsitcad' => utf8_decode($descsitcad),
		'cod_lot'    => $cod_lot,
		'unidade_descricao' => $descricao,
		'jornada'    => $jornada
	);
	echo "id: $id" . PHP_EOL;
	echo "data: " . json_encode($d) . PHP_EOL;
	echo PHP_EOL;

	ob_flush();
	flush();
}
