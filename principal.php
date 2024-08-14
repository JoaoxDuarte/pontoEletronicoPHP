<?php
/*
 * M dulo acionado - Servidor (entrada)
 */

// Inicia a sess o e carrega as fun  es de uso geral
include_once( "config.php" );
include_once( "Sigac.php" );

//tipo de retorno no url de callback do sigac
$type_uri = $_SERVER['REQUEST_URI'];
$type_uri = explode("/",$type_uri);
$type_uri = explode(".",$type_uri[2]);
$type_uri = strtoupper($type_uri[0]);

$obj = new Sigac();


// parametros
$modulo_ativado          = anti_injection($_REQUEST['modulo']);
$logar                   = (isset($_REQUEST['logar']) ? anti_injection($_REQUEST['logar']) : ''); // se o usu rio estiver logado
$ModuloPrincipalAcionado = (isset($_REQUEST['modulo_ativo']) ? anti_injection($_REQUEST['modulo_ativo']) : $_SESSION['sModuloPrincipalAcionado']);

unset($_REQUEST['logar']);
unset($_REQUEST['modulo_ativo']);

session_destroy();
session_start();

$_SESSION['SIGAC_LOGIN']              = false;
$_SESSION['sModuloPrincipalAcionado'] = $ModuloPrincipalAcionado;

if(!empty($_GET['code'])) {

    // RECUPERA O TOKEN DO USU RIO
    $ret = $obj->getTokenAccess($_GET['code'], $type_uri);

    //VALIDA O TOKEN DO USU RIO
    $validate = $obj->validateTokenAccess($ret['access_token']);

    // CASO O TOKEN SEJA V LIDO
    if($validate['active']){

        $_SESSION['SIGAC_TOKEN_ACCESS'] = $ret['access_token'];
        $_SESSION['SIGAC_CPF_SERVIDOR'] = $validate['user_name'];
        $_SESSION['SIGAC_LOGIN'] = true;

        header("Location: secaodousuario.php");
    }
}

// AVISOS
//
// C: Chefia
// R: Recursos Humanos
// A: Chefia e Recursos Humanos
// S: Servidores
// T: Todos
//
$oDBase = new DataBase('PDO');
$oDBase->query('SELECT DATE_FORMAT(data_aviso,"%d-%m-%Y") AS dtaviso, mensagem, janela, janela_altura, alerta, ativo, DATE_FORMAT(data_expirar,"%Y%m%d") AS data_expirar, publico FROM avisos WHERE LTRIM(RTRIM(publico)) IN ("T","A") ORDER BY data_aviso DESC ');

$tbnrows = $oDBase->num_rows();

$msgalerta = array();

if ($tbnrows > 0)
{
    $dthoje = date('Ymd');
    while (list($dtaviso, $txtaviso, $janela, $janela_altura, $alerta, $ativo, $data_expirar, $publico) = $oDBase->fetch_array())
    {
        if ($alerta == 'S' && $ativo == 'S' && ($data_expirar >= $dthoje))
        {
            $msgalerta[] = array($txtaviso, $janela, $janela_altura);
        }
    }
}

$_SESSION['sHOrigem_1'] = 'principal.php';

$title = _SISTEMA_SIGLA_ . ' | Login';


$oForm = new formPadrao();
$oForm->setSubTitulo('');
$oForm->setCSS("css/sigac.css");
$oForm->setJS("js/jquery.mask.min.js");
$oForm->setJS("js/funcoes_valida_cpf_pis.js");
$oForm->setJS('principal.js?v.1.2.3');
$oForm->setSeparador(0);

$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


?>
<div class="container">
    <div class="row align-vertical" id="login2">

        <form name="form" class="form-horizontal" method="POST" action="#" id="form1" name="form1" onsubmit="javascript:return false;">

            <div class="col-md-12">
                <div class="col-md-4">

                    <div class="form-group sigac">
                        <p class="label-sigac">Login</p>
                        <div class="line"></div>
                        <div class="botao-sigac">
                            <a href="<?= $obj->callLoginSIGAC($type_uri); ?>" role="button">
                                <button type="button" class="btn btn-primary btn-lg" style="width: 16pc;">
                                    <img class="sigac-btn-icone" src="imagem/sigac-icone-branco.svg" />
                                    <span class="sigac-login-btn-azul-span">Entrar com SIGAC</span>
                                </button>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-md-offset-2">
                    <div class="form-group">
                        <legend>Login</legend>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5 text-right">
                            <label for="lSiape" class="control-label">CPF</label>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="lSiape" id="lSiape" class="form-control cpf" maxlength="11" required="required">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5 text-right">
                            <label for="lSenha" class="control-label">Senha</label>
                        </div>
                        <div class="col-md-6">
                            <input type="password" id="lSenha" name="lSenha" class="form-control" maxlength="8" required="required">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-5 text-right">
                            <label for="lSenha" class="control-label">Digite o código abaixo</label>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="txtImagem" id="txtImagem" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-9 col-md-offset-1 text-right">
                            <?php
                            include_once('inc/imgSet.php');
                            echo '<img src="inc/imgGera.php" class=" img-responsive align-center-img">';
                            ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-5 col-xs-6 col-md-offset-1">
                            <button type="submit" class="btn btn-success btn-block" id="btn-enviar" name="btn-enviar">
                                <span class="glyphicon glyphicon-ok"></span> Entrar
                            </button>
                        </div>
                        <div class="col-md-5 col-xs-6">
                            <a class="btn btn-danger btn-block" href="reiniciar.php">
                                <span class="glyphicon glyphicon-pencil"></span> Recuperar Senha
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-md-offset-2"><br>
                <div class="form-group text-center comunicado">
                    <a href="javascript:AbrirSisref('manual.php','manual',1200,700);">
                        <span class="glyphicon glyphicon-book"></span> Manuais do SISREF e Legislação
                    </a>
                        |
                    <a href="javascript:AbrirSisref('./inc/avisos_anteriores.php/','/avisos/',337);">
                        <span class="glyphicon glyphicon-flag"></span> Ver Comunicados
                    </a>
                </div>
            </div>

        </form>

    </div>
</div>
<?php

$tmvetor = count($msgalerta);

if ($tmvetor > 0)
{
    $mensagens_avisos = str_replace('font-family: arial', 'font-family: verdana',
       str_replace('font-size: 11px', 'font-size: 12px',
            preparaTextArea(rtrim($msgalerta[$tmvetor - 1][0]), 'para_alert')
        )
    );

    ?>
    <!-- Aqui o conte do ser  mostrado -->
    <div class="modal fade" id="myModalVisualAvisos" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-body modal-dialog modal-lg modal-content text-left" style='width:1100px;'>
            <div class="modal-header text-right navbar-fixed-top" style='z-index:90000;'>
                <button class="btn btn-default" data-dismiss="modal" aria-hidden="true">Fechar</button>
            </div>
            <div class="modal-body-conteudo text-left" style="padding-top:20px;"><?= $mensagens_avisos; ?><div class="margin-50"></div></div>
            <div class="modal-footer navbar-fixed-bottom text-right">
                <button class="btn btn-default" data-dismiss="modal" aria-hidden="true">Fechar</button>
            </div>
        </div>
    </div>

    <script>
        $(function() {
            $('#myModalVisualAvisos').modal();
        });
    </script>
    <?php

} // fim if ($tmvetor > 0)

DataBase::fechaConexao();

## Base do formul rio
#
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
