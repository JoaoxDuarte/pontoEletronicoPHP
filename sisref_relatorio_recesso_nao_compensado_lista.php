<?php

set_time_limit(0);

// funcoes de uso geral
include_once( "config.php" );
include_once( _DIR_INC_ . "calcula_horas_do_recesso.php" );

// dados passados por formulario
$siape   = $_REQUEST['siape'];
$periodo = $_REQUEST['periodo'];

// variavel para reorno da pesquisa
$aDadosEncontrados = array();

// instancia bando de dados
$oDBase = new DataBase('PDO');

//selecionando servidores da upag.
$rx1  = $oDBase->query("SELECT a.mat_siape, a.nome_serv, a.cod_lot, b.upag FROM servativ AS a LEFT JOIN usuarios AS b ON a.mat_siape = b.siape WHERE a.mat_siape='$siape' ORDER BY a.nome_serv ");
$num1 = $oDBase->num_rows();

if ($num1 > 0)
{
    while ($onc = $oDBase->fetch_array())
    {
        $dadosRecesso = calculaHorasDoRecesso($onc['mat_siape'], substr($periodo, 7, 4));
        $cod          = $dadosRecesso[0];
        $total2       = str_replace('-', '', strip_tags($dadosRecesso[1]));
        $nome         = htmlspecialchars(mb_convert_encoding(ajustar_acentos(retira_acentos($onc['nome_serv'])), "UTF-8", "ISO-8859-1"));
        if (!empty($cod))
        {
            $aDadosEncontrados[] = array(
                'siape'    => $onc['mat_siape'],
                'nome'     => $nome,
                'cod'      => $cod,
                'total'    => $total2,
                'mensagem' => ''
            );
            array_push($_SESSION['saDadosEncontradosI'], array($onc['mat_siape'], $nome, $cod, $total2));
        }
    }
}
else
{
    //$mensagem = htmlspecialchars(mb_convert_encoding('Não há servidores com ocorrência não compensada!', "UTF-8", "ISO-8859-1"));
    //$aDadosEncontrados[] = array(
    //	'siape' => '',
    //	'nome'  => '',
    //	'cod'   => '',
    //	'total' => '',
    //	'mensagem' => $mensagem
    //);
}

$myData = array('dados' => $aDadosEncontrados);
print json_encode($myData);
