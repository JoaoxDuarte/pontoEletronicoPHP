<?php

include_once("config.php");

//verifica_permissao("administrador_e_chefe_de_rh");

$modo     = addslashes($_POST['modo']);
$siape    = addslashes($_POST['siape']);
$aAcessos = $_POST['C' . $siape];

// instancia o banco de dados
$oDBase = new DataBase('PDO');
    
// dados do setor
// verifica se o usuário existe
$oDBase->query("
SELECT 
    cad.nome_serv AS nome, und.upag 
FROM 
    servativ AS cad 
LEFT JOIN 
    tabsetor AS und ON cad.cod_lot = und.codigo 
WHERE 
    cad.mat_siape = :siape
",
array(
    array( ':siape', $siape, PDO::PARAM_STR ),
));
$numrows = $oDBase->num_rows();

$oUsuario = $oDBase->fetch_object();
$nome     = $oUsuario->nome;
$upag     = $oUsuario->upag;

/* ---------------------------------------\
  |                                        |
  |   MODO  3                              |
  |   alteracao do perfil de RH            |
  |                                        |
  \--------------------------------------- */

if ($_SESSION['upag'] != $upag)
{
    print "Servidor de outra UPAG!";
}
else
{
    ## classe para alteração de permissão
    #
    $oPermissoes = new AtualizaPermissoesUsuario();

    switch ($aAcessos[0])
    {
        case '00': $oPermissoes->setPerfilRHBloquear($siape);
            break;
        case '01': $oPermissoes->setPerfilRHConsulta($siape);
            break;
        case '09': $oPermissoes->setPerfilRHAlteracao($siape);
            break;
    }

    // grava o LOG
    registraLog("O usuário " . $_SESSION["sMatricula"] . " alterou o perfil de acesso de " . strtoupper($nome), $siape, $nome);
    // fim do LOG

    print "Permissão(ões) alterada(s) com sucesso!";
}
