<?php

// Inicia a sess�o e carrega as fun��es de uso geral
include_once( "config.php" );

$title = _SISTEMA_SIGLA_ . ' | Reinicar Senha';

$oForm = new formPadrao();
$oForm->setJSSelect2();
$oForm->setJS(" 
<script>
    var voltarOrigem = '" . $_SESSION['sHOrigem_1'] . "';
</script>
");
$oForm->setJS("js/jquery.mask.min.js");
$oForm->setJS("js/phpjs.js");
$oForm->setJS("js/funcoes_valida_cpf_pis.js");
$oForm->setJS("reiniciar.js");

$oForm->setSubTitulo( "Reiniciar Senha" );


// Topo do formul�rio
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


?>
        <form class="form-horizontal margin-30" method="POST" id="form1" name="form1" action="#">
            <input type="hidden" name="tipo" id="tipo" value="1"/>
            
            <div class="col-md-8 col-md-offset-3">
                <div class="form-group">
                    <p class="text-justify">
                        Para reiniciar a senha &eacute; necess&aacute;rio o n�mero da matr�cula siape, da identifica&ccedil;&atilde;o &uacute;nica e da data de nascimento.
                    </p>
                    <p class="text-justify">
                        Ap�s a reinicializa��o da senha, ser� encaminhado um email para o detentor da matr�cula, informando que sua senha foi reinicializada para a senha padr�o (data de nascimento).
                    </p>
                </div>

                <div class="form-group">
                    <div class="col-md-2 col-md-offset-1">
                        <label for="cpf" class="control-label">CPF</label>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="cpf" id="cpf" class="form-control cpf" maxlength="11" required="required">
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-md-offset-3">
                <div class="form-group">
                    <div class="col-md-5 col-xs-6 col-md-offset-1">
                        <button type="submit" name="enviar" id="btn-enviar" class="btn btn-success btn-block">
                            <span class="glyphicon glyphicon-ok"></span> Ok
                        </button>
                    </div>
                    <div class="col-md-5 col-xs-6">
                        <a class="btn btn-danger btn-block" href="<?= tratarHTML($_SESSION['sHOrigem_1']); ?>" role="button">
                            <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                        </a>
                    </div>
                </div>
            </div>

        </form>
<?php

DataBase::fechaConexao();

// Base do formul�rio
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
