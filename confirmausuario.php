<?php

include_once("config.php");

verifica_permissao("administrador_e_chefe_de_rh");

$modo     = anti_injection($_REQUEST['modo']);
$lSiape   = anti_injection($_REQUEST['lSiape']);
$lSetor   = anti_injection($_REQUEST['lSetor']);
$lNome    = anti_injection($_REQUEST['lNome']);
$aAcessos = $_REQUEST['C'];


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Utilitários » usuários » Alterar/Excluir usuários');
$oForm->setCSS(_DIR_CSS_ . 'estiloIE.css');
$oForm->setLargura("950px");
$oForm->setSeparador(0);
$oForm->setSubTitulo("Alteração de dados do Usuário");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

// instancia o banco de dados
$oDBase = new DataBase('PDO');

// dados do setor
// verifica se o usuário existe
$oDBase->query("SELECT b.nome_serv AS nome, b.cod_lot AS setor, b.cod_uorg AS uorg, c.upag, c.descricao, a.senha, a.acesso FROM usuarios AS a LEFT JOIN servativ AS b ON a.siape=b.mat_siape LEFT JOIN tabsetor AS c ON b.cod_lot=c.codigo WHERE a.siape='$lSiape' ");
$numrows = $oDBase->num_rows();

$oUsuario  = $oDBase->fetch_object();
$nome      = $oUsuario->nome;
$ssenha    = $oUsuario->senha;
$lotacao   = $oUsuario->setor;
$uorg      = $oUsuario->uorg;
$upag      = $oUsuario->upag;
$descricao = $oUsuario->descricao;
$sTripa    = $oUsuario->acesso;

if ($_SESSION['upag'] != $upag)
{
    mensagem("Servidor de outra UPAG!", null, 1);
}

/* ---------------------------------------\
  |                                        |
  |   MODO  1                              |
  |   criação do perfil                    |
  |                                        |
  \--------------------------------------- */
if ($modo == 1)
{

    if ($numrows >= 1)
    {
        mensagem("Servidor já cadastrado!", null, 1);
    }
    else
    {
        $ssenha = substr(md5($ssenha), 0, 14);
        $auxf   = trataPermissoes($aAcessos);
        $oDBase->query("INSERT INTO usuarios SET siape='$lSiape', nome='" . strtoupper(ltrim(rtrim($lNome))) . "', acesso='$auxf', setor='$lSetor', senha='$ssenha', upag='$upag' ");

        // grava o LOG
        registraLog("O usuário " . $_SESSION["sMatricula"] . " criou um perfil de acesso para " . strtoupper($lNome), $sSiape, $lNome);
        // fim do LOG
    }
}

/* ---------------------------------------\
  |                                        |
  |   MODO  2                              |
  |   alteracao do perfil                  |
  |                                        |
  \--------------------------------------- */
elseif ($modo == 2) //
{
    $auxf = trataPermissoes($aAcessos);
    $oDBase->query("UPDATE usuarios SET siape='$lSiape', nome='" . strtoupper(ltrim(rtrim($lNome))) . "', acesso='$auxf', setor='$lSetor', upag='$upag' WHERE siape='$lSiape' ");

    // grava o LOG
    registraLog("O usuário " . $_SESSION["sMatricula"] . " alterou o perfil de acesso de " . strtoupper($lNome), $sSiape, $lNome);
    // fim do LOG
}

/* ---------------------------------------\
  |                                        |
  |   MODO  3                              |
  |   alteracao do perfil de RH            |
  |                                        |
  \--------------------------------------- */
elseif ($modo == 3) //
{

    ## classe para alteração de permissão
    #
		$oPermissoes = new AtualizaPermissoesUsuario();

    switch ($aAcessos[0])
    {
        case '00': $oPermissoes->setPerfilRHBloquear($lSiape);
            break;
        case '01': $oPermissoes->setPerfilRHConsulta($lSiape);
            break;
        case '09': $oPermissoes->setPerfilRHAlteracao($lSiape);
            break;
    }

    // grava o LOG
    registraLog("O usuário " . $_SESSION["sMatricula"] . " alterou o perfil de acesso de " . strtoupper($lNome), $sSiape, $lNome);
    // fim do LOG
}

mensagem("Permissão(ões) alterada(s) com sucesso!", pagina_de_origem());

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
