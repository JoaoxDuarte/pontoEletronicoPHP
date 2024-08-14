<?php
include_once("config.php");

// Valida a permissão
verifica_permissao('logado');


// Obtem os cidades da uf informada
$cidades = array();

if($_REQUEST['uf']){
    $oDBase = new DataBase();
    $oDBase->query("SELECT * FROM cidades WHERE uf = :uf ORDER BY nome ASC", array(array(':uf', $_REQUEST['uf'], PDO::PARAM_STR )));
    
    while($cidade = $oDBase->fetch_array()){
        $cidades[] = array( 'numero' => $cidade['numero'], 'nome' => $cidade['nome']);
    }
}

return retornaInformacao($cidades, 'success');