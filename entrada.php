<?php


/*
 * M�dulo acionado - Servidor (entrada)
 */

// Inicia a sesso e carrega as funes de uso geral
include_once( "config.php" );
include_once( "Sigac.php" );


session_destroy();
session_start();

$obj = new Sigac();
$_SESSION['SIGAC_LOGIN'] = false;

if(!empty($_GET['code'])) {

    // RECUPERA O TOKEN DO USU�RIO
    $ret = $obj->getTokenAccess($_GET['code'], 'SERVIDOR');

    //VALIDA O TOKEN DO USU�RIO
    $validate = $obj->validateTokenAccess($ret['access_token']);

    // CASO O TOKEN SEJA V�LIDO
    if($validate['active']){

        $_SESSION['SIGAC_TOKEN_ACCESS'] = $ret['access_token'];
        $_SESSION['SIGAC_CPF_SERVIDOR'] = $validate['user_name'];
        $_SESSION['SIGAC_LOGIN'] = true;

        header("Location: entrada1.php");
    }
}


$_SESSION['sHOrigem_1'] = 'entrada.php';

$_SESSION['sModuloPrincipalAcionado'] = 'entrada';

$title = _SISTEMA_SIGLA_ . ' | Login';

## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setSubTitulo('');
$oForm->setCSS("css/sigac.css");
$oForm->setJS("js/jquery.mask.min.js");
$oForm->setJS("js/funcoes_valida_cpf_pis.js");
$oForm->setJS('entrada.js');
$oForm->setSeparador(0);

$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


?>
<div class="container">
    <div class="row align-vertical" id="login2">

        <form id="form1" name="form1" class="form-horizontal" method="POST" onsubmit="return false;">

            <div class="col-md-12">
                <div class="col-md-4">

                    <div class="form-group sigac">
                        <p class="label-sigac">Login</p>
                        <div class="line"></div>
                        <div class="botao-sigac">
                            <a href="<?= (_SISTEMA_ORGAO_ === '57202' ? 'javascript:void(0);' : $obj->callLoginSIGAC("SERVIDOR")); ?>" role="button">
                                <button type="button" class="btn btn-primary btn-lg <?= (_SISTEMA_ORGAO_ === '57202' ? 'disabled' : '') ?>" id="btn-enviar-sigac" style="width: 16pc;">
                                    <img class="sigac-btn-icone" src="imagem/sigac-icone-branco.svg" />
                                    <span class="sigac-login-btn-azul-span">Entrar com SIGAC</span>
                                </button>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-5 col-md-offset-2">
                    <div class="form-group">
                        <legend>Login</legend>
                    </div>

                    <div class="form-group">
                        <div class="col-md-2 col-md-offset-2">
                            <label for="lSiape" class="control-label">CPF</label>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="lSiape" id="lSiape" class="form-control cpf" maxlength="11" required="required">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-2 col-md-offset-2">
                            <label for="lSenha" class="control-label">Senha</label>
                        </div>
                        <div class="col-md-6">
                            <input type="password" id="lSenha" name="lSenha" class="form-control" maxlength="8" required="required">

                            <?php if (_SISTEMA_ORGAO_ === '57202'): ?>

                                <div style="font-size:11px;">(senha do correio eletr�nico)</div>

                            <?php endif; ?>

                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12 text-center">
                            <label >Digite o c�digo abaixo:</label>
                            <?php
                            include_once('inc/imgSet.php');
                            echo '<img src="inc/imgGera.php" class="img-responsive align-center-img">';
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-6  col-md-offset-4">
                            <input type="text" name="txtImagem" id="txtImagem" class="form-control">
                        </div>
                    </div>

                </div>

                <div class="col-md-6 col-md-offset-6">
                    <div class="form-group">
                        <div class="col-md-5 col-xs-6">
                            <button type="submit" name="btn-enviar" id="btn-enviar" class="btn btn-success btn-block">
                                <span class="glyphicon glyphicon-ok"></span> Entrar
                            </button>
                        </div>
                        <div class="col-md-5 col-xs-6">
                            <a class="btn btn-danger btn-block" href="reiniciar.php" role="button">
                                <span class="glyphicon glyphicon-pencil"></span> Recuperar Senha
                            </a>
                        </div>

                    </div>

                </div>
            </div>

            <div class="col-md-6 col-md-offset-3">

                <?php if (_SISTEMA_ORGAO_ !== '57202'): ?>
                <br>
                <br>
                <br>
                <?php endif; ?>

                <div class="form-group text-center comunicado" >
                    <a href="javascript:AbrirSisref('manual.php','manual',1200,700);" role="button">
                        <span class="glyphicon glyphicon-book"></span> Manuais do SISREF e Legisla��o
                    </a>
                    |
                    <a href="javascript:AbrirSisref('./inc/avisos_anteriores.php/','/avisos/',337);" role="button">
                        <span class="glyphicon glyphicon-flag"></span> Ver Comunicados
                    </a>
                </div>
            </div>

        </form>

    </div>

    <?php if (_SISTEMA_ORGAO_ === '57202'): ?>

        <div class="col-md-12">

            <?php mensagens_comunicacao_social(); ?>

        </div>

    <?php endif; ?>

</div>
<?php

DataBase::fechaConexao();

## Base do formul�rio
#
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
