<?php
// Inicia a sess�o e carrega as fun��es de uso geral
include_once("config.php");

// Verifica se existe um usu�rio logado e se possui permiss�o para este acesso
verifica_permissao('sRH');


$or = anti_injection($_REQUEST['or']);


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Cadastro � Funcional � Incluir');
$oForm->setCSS(_DIR_CSS_ . "estiloIE.css");
$oForm->setJS("
	<script>
		function verificadados()
		{
			if (document.form1.Arquivo.value == '')
			{
				alert(' � obrigat�rio selecionar o arquivo a anexar!');
				document.form1.Arquivo.focus();
				return false;
			}
			if (document.form1.nome.value.length < 7)
			{
				alert(' � obrigat�rio informar a matr�cula siape do servidor!');
				document.form1.nome.focus();
				return false;
			}
		}
	</script>
	");
$oForm->setSeparador(0);

// Topo do formul�rio
//
$oForm->setSubTitulo("Inclus�o de Servidores e Estagi�rios - Foto");

// Topo do formul�rio
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<form method="post" action="upload.php"  onsubmit="return verificadados()" id="form1" name="form1" enctype="multipart/form-data" >
    <div align="center">
        <p>
            Clique no bot&atilde;o Procurar para localizar o arquivo de foto do servidor.<br>
            Ap&oacute;s informe a matr&iacute;cula siape com sete caracteres e clique
            no bot&atilde;o enviar.<br>
        </p>
        <p><input type="file" name="Arquivo" id="Arquivo"></p>
        Siape:<br>
        <?php
        if ($or == 1)
        {
            ?>
            <input type='text' id='nome' name='nome' value='' size='10' maxlength='7'>
            <?php
        }
        else
        {
            ?>
            <input type='hidden' id='nome' name='nome' value='<?= removeOrgaoMatricula( $siape ); ?>'>
            <?php
        }
        ?>
        <br>
        <input type="hidden" name="tipo" value=".jpg">
        <br>
        <input type="hidden" name="or" value="<?= tratarHTML($or); ?>">
        <p><input type="submit" value="Enviar"></p>
    </div>
</form>
<?php
// Base do formul�rio
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

