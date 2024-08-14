<?php
include_once( "config.php" );

verifica_permissao("sRH");

// instancia o BD
$oDBase = new DataBase('PDO');


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho("Relatórios » Gerencial » Ocupantes de Fun&ccedil;&otilde;es");
$oForm->setJSSelect2();
$oForm->setCSS(_DIR_CSS_ . "estilos_new_laytou.css");
$oForm->setJS( "histfuncserv2 .js?v.0.0.0.1" );
$oForm->setOnLoad("javascript: if($('#pSiape')) { $('#pSiape').focus() };");
$oForm->setLargura('920px');
$oForm->setSeparador(0);

$oForm->setSubTitulo("Consulta Histórico de Funções do Servidor");


// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

?>
<form method="POST" action="conshistfunc2.php" id="form1" name="form1">
    <p align="center"><h3>
        <div align="center">
            <font size=1>
            <select class='form-control select2-single' size="1" name="nfuncao"  id="nfuncao" style="font-size: 16px">
                <?php

                $upag = $_SESSION['upag'];
                $oDBase->query("SELECT NUM_FUNCAO, DESC_FUNC, COD_FUNCAO FROM tabfunc WHERE ativo ='S' AND upag = $upag ORDER BY NUM_FUNCAO ");

                while ($linha = $oDBase->fetch_array())
                {
                    ?>
                    <option value='<?= tratarHTML($linha['NUM_FUNCAO']); ?>'><?= tratarHTML($linha['NUM_FUNCAO']) . " - " . tratarHTML($linha['DESC_FUNC']); ?></option>
                    <?php
                }

                ?>
            </select>
            </font>
        </div>
        <p align="center" style="word-spacing: 0; margin-top: 0; margin-bottom: 0">&nbsp;</p>
        <p align="center" style="word-spacing: 0; margin: 0"><input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>ok.gif" name="enviar" alt="Submeter os valores" align="center" ></p>
</form>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
