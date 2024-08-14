<?php

// conexao ao banco de dados
// funcoes diversas
include_once( "config.php" );
include_once( "class_folha_frequencia.php" );

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH');

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    print "Acesso Negado!";
}
else
{
    $dados  = explode(":|:", base64_decode($dadosorigem));
    $pSiape = $dados[0];
    $lot    = $dados[1];
    $jnd    = $dados[2];
    $mes    = $dados[3];
    $ano    = $dados[4];
}

// dados voltar
//$_SESSION['voltar_nivel_2'] = 'regfreq8.php?dados='.$dadosorigem;
//$_SESSION['voltar_nivel_1'] = $dadosorigem;
$_SESSION['voltar_nivel_2'] = 'frequencia_verificar_homologados_visualizar.php?dados=' . $dadosorigem;
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';

$_SESSION['tabPosition'] = '#' . $lot;

$caminho_modulo_utilizado = 'Relatórios » Frequência » Homologados » Verificar';
$form_action              = "frequencia_verificar_homologados_gravar.php";
$form_submit              = "return validar()";


$objPonto = new FolhaFrequencia([
    'siape' => $pSiape,
    'mes'   => $mes,
    'ano'   => $ano,
]);

$objPonto->CarregaDados();
$objPonto->DocAbre();
$objPonto->DocTitulo();        // Título do documento
$objPonto->DocIdentificacao(); // Referente aos dados dos funcionários
$objPonto->DocUnidade();
$objPonto->DocHorariosDefinidos();
$objPonto->DocRegistros();
$objPonto->DocSaldosBancoDeHoras();
$objPonto->DocSaldos();
$objPonto->DocFecha();
