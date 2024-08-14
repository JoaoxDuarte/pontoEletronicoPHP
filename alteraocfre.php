<?php
// conexao ao banco de dados funcoes diversas
include_once( "config.php" );

verifica_permissao('tabela_prazos');

$siapecad = anti_injection($_REQUEST['siapecad']);

// instancia do BD
$oDBase = new DataBase('PDO');

$oDBase->setMensagem("Problemas no acesso a Tabela OCORRÊNCIAS (E000112.".__LINE__.").");
$oDBase->query("SELECT * FROM tabocfre WHERE siapecad= :codigo AND ativo = 'S' ", array(
    array(":codigo", $siapecad, PDO::PARAM_STR)
));

$oOcorrencia = $oDBase->fetch_object();


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho("Tabelas » Manutenção Ocorrências ");
$oForm->setCSS(_DIR_CSS_ . 'estilos.css');
$oForm->setCSS(_DIR_CSS_ . 'smoothness/jquery-ui-custom-px.min.css');
$oForm->setDialogModal();
$oForm->setOnLoad("javascript: if($('#siapecad')) { $('#siapecad').focus() };");
$oForm->setSeparador(0);

// validação
$oForm->setJS("alteraocfre.js");

// Topo do formulário
//
$oForm->setSubTitulo("Manutenção da Tabela de Ocorrências");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<form method="POST" action="grava.php?modo=12" onsubmit="return verificadados()" id="form1" name="form1" >
    <div align="center">
        <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr>
                <td class="corpo" style="width:12%;height:25px;text-align:center;"><div align="center"></div></td>
                <td class="corpo" style="width:6%;height:25px;text-align:center;"><div align="center">Código</div></td>
                <td class="corpo" style="width:40%;height:25px;text-align:center;"><div align="center">Descrição da ocorrência</div></td>
                <td class="corpo" style="width:1%;height:25px;text-align:center;"><div align="center"></div><div align="center"></div></td>
                <td class="corpo" style="width:5%;height:25px;text-align:center;"><div align="center">Sirh</div></td>
                <td class="corpo" style="width:36%;height:25px;text-align:center"><div align="center">Siape</div></td>
            </tr>
            <tr>
                <td class="left" height="25"><div align="center"> </div></td>
                <td height="25" class="corpo">
                    <div align="center">
                        <input type="text" id="siapecad" name="siapecad" class="centro" value="<?= tratarHTML($oOcorrencia->siapecad); ?>" size="10" maxlength="10" readonly>
                    </div>
                </td>
                <td height="25" class="corpo">
                    <input type="text" id="sDescricao" name="sDescricao" class="Caixa" value="<?= tratarHTML($oOcorrencia->desc_ocorr); ?>" size="70" maxlength="70" readonly>
                </td>
                <td height="25" class="corpo"> <div align="center"> </div><div align="center"> </div></td>
                <td height="25" class="corpo">
                    <div align="center">
                        <input type="text" id="sirh" name="sirh" class="centro" value="<?= tratarHTML($oOcorrencia->cod_ocorr); ?>" size="10" maxlength="10" readonly>
                    </div>
                </td>
                <td height="25" class="corpo">
                    <div align="center">
                        <input type="text" id="siape" name="siape" class="centro" value="<?= tratarHTML(removeOrgaoMatricula( $oOcorrencia->cod_siape )); ?>" size="10" maxlength="10">
                    </div>
                </td>
            </tr>
            <tr>
                <td height="47" colspan="6" align="center" class="corpo">
                    <div align="center">Respons&aacute;vel:
                        <select id="resp" name="resp" size="1" class='drop' title="Selecione o responsável pelo aplicação do código no dia a dia">
                            <option value="ZZ">Selecione</option>
                            <option value="CH"<?= ($oOcorrencia->resp == 'CH' ? " selected" : ""); ?>>Chefe</option>
                            <option value="RH"<?= ($oOcorrencia->resp == 'RH' ? " selected" : ""); ?>>Recursos Humanos</option>
                            <option value="AB"<?= ($oOcorrencia->resp == 'AB' ? " selected" : ""); ?>>Ambos</option>
                        </select>
                        Ativo:
                        <input type="text" id="sAtivo" name="sAtivo" class="centro" value="<?= tratarHTML($oOcorrencia->ativo); ?>" size="5" maxlength="5">
                    </div>
                </td>
            </tr>
            <tr>
                <td height="25" colspan="6" align="center" class="corpo"  p><div align="center">Aplica&ccedil;&atilde;o</div></td>
            </tr>
            <tr>
                <td height="25" colspan="6" class="corpo">
                    <div align="center">
                        <textarea id='aplic' name='aplic' cols='100' rows='3'><?= tratarHTML($oOcorrencia->aplic); ?></textarea>
                    </div>
                </td>
            </tr>
            <tr>
                <td height="25" colspan="6" class="corpo"><div align="center">Implica&ccedil;&atilde;o</div></td>
            </tr>
            <tr>
                <td height="25" colspan="6" class="corpo">
                    <div align="center">
                        <textarea id='implic' name='implic' cols='100' rows='3'><?= tratarHTML($oOcorrencia->implic); ?></textarea>
                    </div>
                </td>
            </tr>
            <tr>
                <td height="25" class="corpo"><div align="center"></div></td>
                <td height="25" colspan="2" class="corpo">Prazos <div align="center"></div></td>
                <td height="25" colspan="3" class="corpo">Fundamento legal</td>
            </tr>
            <tr>
                <td height="25" class="corpo"><div align="center"> </div></td>
                <td height="25" colspan="2" class="corpo">
                    <textarea id='prazo' name='prazo' cols='35' rows='3'><?= tratarHTML($oOcorrencia->prazo); ?></textarea>
                    <div align="center"> </div>
                </td>
                <td height="25" colspan="3" class="corpo">
                    <textarea id='flegal' name='flegal' cols='45' rows='3'><?= tratarHTML($oOcorrencia->flegal); ?></textarea>
                </td>
            </tr>
        </table>
    </div>
    <div align="center"></div>
    <p align="center" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6px; margin-bottom: 0"><input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>ok.gif" name="enviar" alt="Submeter os valores" align="center" ></p>
</form>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
