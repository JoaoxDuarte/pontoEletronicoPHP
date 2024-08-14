<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao( 'sRH e sTabServidor' );

// dados passados via formulario
$id = $_REQUEST['id'];

// instancia banco de dados
$oDBase = new DataBase;

/*obtem dados da uorg  para saber se uorg ou upag e a mesma do usuario */
$oDBase->query( "SELECT subs.siape, subs.sigla, subs.inicio, subs.fim, subs.situacao FROM substituicao AS subs WHERE subs.id = '$id' ORDER BY subs.inicio DESC " );
$oSubstituicao = $oDBase->fetch_object();
$siape  = $oSubstituicao->siape;
$sigla  = $oSubstituicao->sigla;
$inicio = $oSubstituicao->inicio;
$fim  = $oSubstituicao->fim;
$sit  = $oSubstituicao->situacao;


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho( 'Relatórios » Gerencial » Substituições » Substituições do servidor' );
$oForm->setCSS( "css/estiloIE.css" );
$oForm->setSeparador( 0 );
$oForm->setSubTitulo( "Encerrar Substituição" );

$oForm->setObservacaoBase( "Substituição só poderá ser CANCELADA antes da data 'Fim' definida, após esta data somente a opção ENCERRADA deveráser utilizada." );

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


?>
    <form method="POST" action="gravasuporte.php?modo=2" onsubmit="return verificadados()" name="form1" >
        <p align="left" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6; margin-bottom: 0">
            <strong><font size="2" face="Tahoma"></font></strong>
        </p>
        <table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#808040" width="100%" id="AutoNumber1">
            <tr>
                <td width="100" height="46">
                    <div class='tahomaSize_2'>
                        &nbsp;Id:<br>
                        &nbsp;<input name="id" type="text" class='centro' id="id" value='<?= tratarHTML($id); ?>' size="7" readonly>
                    </div>
                </td>
                <td width="196">
                    <div class='tahomaSize_2'>
                        &nbsp;Siape:<br>
                        &nbsp;<input name="siape" type="text" class='centro' id="siape" value='<?= tratarHTML($siape); ?>' size="7" readonly>
                    </div>
                </td>
                <td width="247">
                    <div class='tahomaSize_2'>
                        &nbsp;Sigla da fun&ccedil;&atilde;o:<br>
                        &nbsp;<input name="sigla" type="text" class='centro' id="sigla" value='<?= tratarHTML($sigla); ?>' size="15" readonly>
                    </div>
                </td>
                <td width="223">
                    <div class='tahomaSize_2'>
                        &nbsp;Início:<br>
                        &nbsp;<input name="ini" type="text" class='centro' id="ini" value='<?= tratarHTML($inicio); ?>' size="15" readonly>
                    </div>
                </td>
                <td width="200">
                    <div class='tahomaSize_2'>
                        &nbsp;Fim:<br>
                        &nbsp;<input name="fim" type="text" class='centro' id="fim" value='<?= tratarHTML($fim); ?>' size="15" readonly>
                    </div>
                </td>
                <td width="150" align="center">
                    <div class='tahomaSize_2'>
                        &nbsp;Situação:<br>
                        &nbsp;<select name="sit" class="alinhadoAoCentro" id="sit">
                            <option value="A" <?= ($sit == "A" ? "selected" : ""); ?>>ATIVA</option>
                            <option value="E" <?= ($sit == "E" ? "selected" : ""); ?>>ENCERRADA</option>
                            <?php

                            if (inverteData($fim) > date('Ymd'))
                            {
                                ?>
                                <option value="C" <?= ($sit == "C" ? "selected" : ""); ?>>CANCELADA</option>
                                <?php
                            }

                            ?>
                        </select>
                    </div>
                </td>
            </tr>
        </table>
        <p align="left" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6;"><strong></strong></p>
        <p align="center" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6;">&nbsp;</p>
        <p align="center" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6;">
            <input type="image" border="0" src="./imagem/ok.gif" name="enviar" alt="Submeter os valores" align="center">
        </p>
    </form>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
