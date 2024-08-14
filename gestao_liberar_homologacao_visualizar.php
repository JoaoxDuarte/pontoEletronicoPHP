<?php

// conexao ao banco de dados
// funcoes diversas
include_once( "config.php" );

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH');

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    header("Location: acessonegado.php");
}
else
{
    $dados = explode(":|:", base64_decode($dadosorigem));
    $id    = $dados[0];
}

// dados voltar
//$_SESSION['voltar_nivel_2'] = 'regfreq8.php?dados='.$dadosorigem;
//$_SESSION['voltar_nivel_1'] = $dadosorigem;
$_SESSION['voltar_nivel_2'] = 'gestao_liberar_visualizar.php?dados=' . $dadosorigem;
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';

$_SESSION['tabPosition'] = 'c' . $id;

$oDBase = new DataBase('PDO');

$oDBase->query("
SELECT 
    justificativa
FROM 
    homologacao_dilacao_prazo
WHERE 
    id = :id
", array(
    array( ':id', $id, PDO::PARAM_STR),
));

?>
<div class="container">
    <div class="col-md-7" style="width:580px;">
        <?= tratarHTML($oDBase->fetch_object()->justificativa); ?>
    </div>
</div>
<?php
