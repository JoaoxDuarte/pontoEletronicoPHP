<?php
// funcoes diversas
include_once("config.php");

verifica_permissao('logado');

$siape = $_SESSION["sMatricula"];

switch ($_SESSION['sHOrigem_1'])
{
    case "entrada.php":
    case "secaodousuario.php":
        $bExibeLogo               = true;
        $bReadOnlySIAPE           = true;
        $caminho_modulo_utilizado = "Login » Troca de Senha";
        break;

    default:
        $bExibeLogo               = false;
        $bReadOnlySIAPE           = false;
        $caminho_modulo_utilizado = "Utilitários » Usuários » Trocar Senha";
        verifica_permissao('logado', $_SESSION['sHOrigem_1']);
        break;
}

## classe para montagem do formulario padrao
#
$title = _SISTEMA_SIGLA_ . ' | Troca de Senha';

$css   = array();
$css[] = _DIR_CSS_ . 'plugins/dlg.min.css';

$javascript   = array();
$javascript[] = _DIR_JS_ . 'phpjs.js';
$javascript[] = 'trocasenha.js';
$javascript[] = _DIR_JS_ . "jquery.blockUI.js?v2.38";
$javascript[] = _DIR_JS_ . "jquery.bgiframe.js";
$javascript[] = _DIR_JS_ . "plugins/jquery.dlg.min.js";
$javascript[] = _DIR_JS_ . "plugins/jquery.easing.js";
$javascript[] = _DIR_JS_ . "jquery.ui.min.js";

include("html/html-base.php");
include("html/header.php");
?>
<script>
    var voltarOrigem = "<?= $_SESSION['sHOrigem_1']; ?>";

    $(document).ready(function ()
    {
        $('#form1').submit(function ()
        {
            if (verificadados(voltarOrigem))
            {
                replaceLink(voltarOrigem);
                return true;
            }
            return false;
        });
        $('senhaatual').focus();
    });
</script>

<div class="container">
    <div class="row align-vertical" id="login">

        <form class="form-horizontal" method="POST" id="form1" name="form1" action="#">
            <input type="hidden" id="tipo" name="tipo" value="<?= tratarHTML($tipo); ?>">

            <div class="col-md-8 col-md-offset-2">

                <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php   ?>

                <div class="col-md-10 subtitle">
                    <h4 class="lettering-tittle uppercase"><strong>Troca de Senha</strong></h4>
                </div>

                <div class="form-group">
                    <div class="col-md-5 col-md-offset-1">
                        <label for="lSiape" class="control-label">Siape</label>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id="lSiape" name="lSiape" maxlength="7" value="<?= removeOrgaoMatricula($siape); ?>" <?= ($bReadOnlySIAPE == true ? 'readonly' : ''); ?> class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-5 col-md-offset-1">
                        <label for="senhaatual" class="control-label">Senha Usada Atualmente</label>
                    </div>
                    <div class="col-md-4">
                        <input type="password" id="senhaatual" name="senhaatual" maxlength="8" onkeyup="javascript:ve(this.value);" class="form-control" required="required">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-5 col-md-offset-1">
                        <label for="senhanova" class="control-label">Nova Senha (com 8 dígitos)</label>
                    </div>
                    <div class="col-md-4">
                        <input  type="password" id="senhanova" name="senhanova" maxlength="8" onkeyup="javascript:ve(this.value);" title="Senha deve conter 8 caracteres alfanuméricos" class="form-control" required="required">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-5 col-md-offset-1 text-nowrap">
                        <label for="senhanova_confirmar" class="control-label">Confirmar Nova Senha (com 8 dígitos)</label>
                    </div>
                    <div class="col-md-4">
                        <input type="password" id="senhanova_confirmar" name="senhanova_confirmar" maxlength="8" title="Senha deve conter 8 caracteres alfanuméricos" class="form-control" required="required">
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-md-offset-3">
                <div class="form-group">
                    <div class="col-md-5 col-xs-6 col-md-offset-1">
                        <button type="submit" name="enviar" id="btn-reiniciar-senha" class="btn btn-success btn-block">
                            <span class="glyphicon glyphicon-ok"></span> OK
                        </button>
                    </div>
                    <div class="col-md-5 col-xs-6">
                        <a class="no-style" href="<?= tratarHTML($_SESSION['sHOrigem_1']); ?>">
                            <button type="button" class="btn btn-danger btn-block" id="btn-voltar">
                                <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                            </button>
                        </a>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

<?php
include("html/footer.php");

DataBase::fechaConexao();
