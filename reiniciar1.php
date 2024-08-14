<?php
// Inicia a sess�o e carrega as fun��es de uso geral
include_once( "config.php" );

// Verifica se existe um usu�rio logado e se possui permiss�o para este acesso
verifica_permissao("troca_senha");


## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho('Utilit�rios � Usu�rios � Reiniciar Senhas');
$oForm->setJS("js/jquery.mask.min.js");
$oForm->setJS("js/funcoes_valida_cpf_pis.js");
$oForm->setJS('reiniciar1.js');
$oForm->setOnLoad("$('#siape').focus();");
$oForm->setSeparador(30);

$oForm->setSubTitulo("Reiniciar Senha");


// Topo do formul�rio
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

?>
<script> var voltarOrigem = "<?= $_SESSION['sHOrigem_1']; ?>";</script>

<div class="container">

    <form method="POST" id="form1" name="form1" action='#' onsubmit="javascript:return false;">
        <input type='hidden' id='tipo' name='tipo' value='<?= tratarHTML($tipo); ?>'>

        <div class="form-group col-md-8 col-lg-offset-3 text-center margin-25"
             style="padding-left:40px;">

            <div class="text-center col-md-3"
                 style="background-color:#F9F9F9;border:1px solid #DDDDDD;padding:5px 10px 5px 10px;">
                <font class="ft_13_003">&nbsp;Matr�cula&nbsp;
                <div>
                    <input type="text" id="siape" name="siape" class="form-control" size="7" maxlength="7">
                </div>
            </div>

            <div class="text-center col-md-3" style="background-color:#F9F9F9;border:1px solid #DDDDDD;padding:5px 10px 5px 10px;">
                <font class="ft_13_003">&nbsp;CPF&nbsp;
                <div>
                    <input type="text" id="cpf" name="cpf" class="form-control cpf" size="14" maxlength="11">
                </div>
            </div>

        </div>


        <div class="form-group" style="padding-top:120px;padding-bottom:50px;padding-left:45px;">
            <div class="col-md-2 col-xs-2 col-md-offset-3">
                <button type="submit" name="btn-enviar" class="btn btn-success btn-block">
                    <span class="glyphicon glyphicon-ok"></span> Continuar
                </button>
            </div>
            <div class="col-md-2 col-xs-2">
                <a class="no-style" href="javascript:window.location.replace('<?= tratarHTML($_SESSION["sHOrigem_1"]); ?>');" style="text-decoration:none;">
                    <button type="button" class="btn btn-danger btn-block">
                        <span class="glyphicon glyphicon-arrow-left"></span> Voltar
                    </button>
                </a>
            </div>
        </div>

        <div>
            <div style='text-align:right;width:90%;margin:25px;font-size:9px;border:0px;'>
                <fieldset style='border:1px solid white;text-align:left;'>
                    <legend style='font-size:12px;padding:0px;margin:0px;'><b>&nbsp;Informa��es&nbsp;</b></legend>
                    <p style='padding:1px;margin:0px;'>
                        <b>Matr�cula&nbsp;:&nbsp;</b>Matr�cula SIAPE do servidor/estagi�rio;
                    </p>
                    <p style='padding:1px;margin:0px;'>
                        <b>CPF&nbsp;:&nbsp;</b>CPF do servidor/estagi�rio;
                    </p>

                    <p style='font-size:15px;padding-top:20px;margin:0px;text-align:justify;'>
                        <b>Observa��o&nbsp;:&nbsp;</b>Ap�s a reinicializa��o da senha, ser� encaminhado um email para o detentor da matr�cula,<br>informando que sua senha foi reinicializada para a senha padr�o (data de nascimento).
                    </p>
                </fieldset>
            </div>
        </div>

    </form>
</div>
<?php
// Base do formul�rio
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
