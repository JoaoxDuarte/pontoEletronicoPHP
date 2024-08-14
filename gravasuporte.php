<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH');

$modo = anti_injection($_REQUEST['modo']);

// instancia banco de dados
$oDBase = new DataBase('PDO');

if ($modo == "1")
{

    //Recebe  a opção da página anterior
    //Recebendo os dados a serem incluidos
    $ini     = $_REQUEST['inicio'];
    $rlot    = anti_injection($_REQUEST['rlot']);
    $sits    = anti_injection($_REQUEST['sits']);
    $siape   = anti_injection($_REQUEST['matricula']);
    $Nfuncao = anti_injection($_REQUEST['Nfuncao']);
    $chefia  = anti_injection($_REQUEST['chefia']);
    $chef    = anti_injection($_REQUEST['chef']);
    $acesso  = anti_injection($_REQUEST['acesso']);
    $acess   = anti_injection($_REQUEST['acess']);
    $id      = anti_injection($_REQUEST['id']);
    $sit     = anti_injection($_REQUEST['situacao']);

    $vDatas = date("Y-m-d");

    if ($sit == 'TITULAR')
    {
        $sit2 = "T";
    }
    elseif ($sit == 'SUBSTITUTO')
    {
        $sit2 = "S";
    }
    elseif ($sit == 'INTERINO')
    {
        $sit2 = "R";
    }

    // atualiza tabelas
    $oDBase->query("UPDATE servativ SET chefia = '$chef' WHERE mat_siape = '$siape' ");
    $oDBase->query("UPDATE usuarios SET acesso = '$acess' WHERE siape = '$siape' ");

    mensagem("Função ocupada pelo servidor não permite mudar o status de chefia!\\nEsse campo não foi alterado.");
    reloadOpener();
    close();
}
elseif ($modo == "2")
{

    $id    = anti_injection($_REQUEST['id']);
    $sit   = anti_injection($_REQUEST['sit']);
    $siape = anti_injection($_REQUEST['siape']);

    $oDBase->query("UPDATE substituicao SET situacao = '$sit' WHERE siape = '$siape' AND id = '$id' ");

    ##
    # verifica se o usuario está como substituto.
    # se o período expirou cancela a permissao
    # para atuar como chefe da unidade
    #
		//trata_substituicao( $siape, $id );

    mensagem("Dados Situação da substituição alterada sucesso!");
    reloadOpener();
    close();
}
elseif ($modo == "3")
{

    $id    = anti_injection($_REQUEST['id']);
    $sit   = anti_injection($_REQUEST['sit']);
    $siape = anti_injection($_REQUEST['siape']);

    $oDBase->query("UPDATE substituicao SET situacao = '$sit' WHERE siape = '$siape' AND id = '$id' ");

    mensagem("Dados Situação da substituição alterada sucesso!");
    reloadOpener();
    close();
}
