<?php
include_once("config.php");

verifica_permissao("tabela_prazos");

$matricula = anti_injection($_REQUEST['matricula']);

// instancia BD
$oDBase = new DataBase('PDO');


## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho("Tabelas");
$oForm->setCSS(_DIR_CSS_ . "estiloIE.css");
$oForm->setOnLoad("javascript: if($('#pSiape')) { $('#pSiape').focus() };");
$oForm->setLargura('950');
$oForm->setSeparador(0);

// Topo do formulário
//
$oForm->setSubTitulo("Corre&ccedil;&atilde;o de Problemas com Substitui&ccedil;&atilde;o");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


$oDBase->query("SELECT * FROM ocupantes WHERE mat_siape = '$matricula' AND dt_fim = '0000-00-00' ");
$aOcupa = $oDBase->fetch_array();

$sit        = $aOcupa['sit_ocup'];
$dtini      = databarra($aOcupa['dt_inicio']);
$num_funcao = $aOcupa['num_funcao'];

$linhas = $oDBase->num_rows();

if ($linhas > 0)
{
    switch ($sit)
    {
        case 'T': $sit2 = "TITULAR";
            break;
        case 'S': $sit2 = "SUBSTITUTO";
            break;
        case 'R': $sit2 = "INTERINO";
            break;
    }
}

if ($linhas == 0)
{
    mensagem("Servidor não ocupa função!");
}
else
{
    $oDBase->query("SELECT * FROM substituicao WHERE siape = '$matricula' AND situacao != 'E' ");
    $num = $oDBase->num_rows();

    $aSubs = $oDBase->fetch_array();

    $situacao = $aSubs['situacao'];
    $numfunc  = $aSubs['numfunc'];
    $inicio   = databarra($aSubs['inicio']);
    $fim      = databarra($aSubs['fim']);

    $oDBase->query("SELECT nome_serv, cod_lot, chefia FROM servativ WHERE mat_siape = '$matricula' ");
    $aCad   = $oDBase->fetch_array();
    $nome   = $aCad["nome_serv"];
    $lotat  = $aCad["cod_lot"];
    $chefia = $aCad["chefia"];

    $oDBase->query("SELECT * FROM usuarios WHERE siape = '$matricula' ");
    $aUsu   = $oDBase->fetch_array();
    $acesso = $aUsu["acesso"];
    $ace    = substr($acesso, 1, 1);
    $lot    = $aUsu["setor"];
    $upag   = $aUsu["upag"];

    //busca na tabela Tabfunc para saber a descrição das funções
    $oDBase->query("SELECT * FROM tabfunc WHERE NUM_FUNCAO = '$num_funcao' AND ativo = 'S' ");
    $aFuncao = $oDBase->fetch_array();
    $funcao  = $aFuncao["desc_func"];
    $sigla   = $aFuncao["cod_funcao"];
    $lot     = $aFuncao["cod_lot"];
    $rlot    = $aFuncao["resp_lot"];

    //busca na tabela Tabsetor para saber a uorg das funções
    $oDBase->query("SELECT * FROM tabsetor WHERE codigo = '$lot' AND ativo = 'S' ");
    $aUnd = $oDBase->fetch_array();
    ;
    $uorg = $aUnd["cod_uorg"];
    $dlot = $aUnd["descricao"];
}
?>
<script>
    function validar()
    {
    }
</script>

<style type="text/css">
    table.l {border-top: 1px solid #808000;}
    table {border-top: 0px solid #808000;border-left: 1px solid #808000;}
    tr td {border-bottom: 1px solid #808000;border-right: 1px solid #808000;}
    p {font:  10pt Tahoma, Verdana;MARGIN: 3PX;}
    INPUT.caixa {font-family: verdana, arial, helvetica;font-size: 8 pt;color: #808080;border: 1 solid #696969;BACKGROUND-COLOR: #f7f3f7;margin: 3px;}
    SELECT {font-family: verdana, arial, helvetica;font-size: 8 pt;color: #808080;border: 1 solid #CCE6FF;padding-left: 1;padding-top: 1;padding-bottom: 1;BACKGROUND-COLOR: #f7f3f7;}
</style>

<form method="POST" action="gravasuporte.php" id="form1" name="form1" onSubmit="return validar()">
    <input type='hidden' id='modo'   name='modo'   value='1'>
    <input type='hidden' id="rlot"   name='rlot'   value="<?= tratarHTML($rlot); ?>">
    <input type='hidden' id="acesso" name='acesso' value="<?= tratarHTML($acesso); ?>">
    <p style="margin-top: 0; margin-bottom: 0" align="center"><b> <font size="4" face="Tahoma">TELA PARA ACERTO DE ERROS COM SUBSTITUIÇÃO</p>
            &nbsp;<strong><font size="2">DADOS DO CADASTRO DO SERVIDOR</font></strong>&nbsp;
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="223" height="22" class="corpo">
                        <p>Matr&iacute;cula:
                            <input name="matricula" type="text" class='alinhadoAoCentro' id="matricula" value='<?= tratarHTML($matricula); ?>' size="7" readonly>
                        </p>
                    </td>
                    <td colspan="2" class="corpo">
                        <p>Nome:
                            <input name="nome" type="text" class='alinhadoAoCentro' id="nome" value='<?= tratarHTML($nome); ?>' size="50" readonly>
                        </p>
                    </td>
                    <td width="195">
                        <p>chefia S/N:
                            <input name="chefia" type="text" class='alinhadoAoCentro' id="inicio3" value="<?= tratarHTML($chefia); ?>" size="5" readonly >
                            -
                            <select name="chef" class="alinhadoAoCentro" id="chef">
                                <option value="N"<?= ($chefia == "N" ? " selected" : ""); ?>>NÃO</option>
                                <option value="S"<?= ($chefia == "S" ? " selected" : ""); ?>>SIM</option>
                            </select>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td height="26" class="corpo">
                        <p>Numero da fun&ccedil;&atilde;o:
                            <input type="text" id="nfunc2" name="nfunc2" class='alinhadoAoCentro' value='<?= tratarHTML($num_funcao); ?>' size="15" readonly>
                        </p>
                    </td>
                    <td width="580" class="corpo">
                        <p>Denomina&ccedil;&atilde;o:
                            <input type="text" id="nome2" name="nome2" class='alinhadoAoCentro' value='<?= tratarHTML($funcao); ?>' size="80" readonly>
                        </p>
                    </td>
                    <td width="202" class="corpo">
                        <p>In&iacute;cio:
                            <input type="text" id="ini2" name="ini2" class='alinhadoAoCentro' onKeyPress="formatar(this, '##/##/####')" value="<?= tratarHTML($dtini); ?>" size="10" readonly>
                        </p>
                    </td>
                    <td>
                        <p>Situa&ccedil;&atilde;o:
                            <input name="situacao" type="text" class='alinhadoAoCentro' id="matricula4" value='<?= tratarHTML($sit2); ?>' size="15" readonly>
                        </p>
                    </td>
                </tr>
            </table>
            <font size="2"><strong>DADOS DO CADASTRO DE USU&Aacute;RIOS<br>
            </strong></font>

            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="353">
                        <div align="center">Chefia?
                            <input name="acesso" type="text" class='alinhadoAoCentro' id="acesso" size="4" value="<?= tratarHTML($ace); ?>" readonly='15'>
                            - Perfil
                            <input name="acesso2" type="text" class='alinhadoAoCentro' id="acesso2" value="<?= tratarHTML($acesso); ?>" size="16" readonly='15'>
                            -
                            <select name="acess" class="alinhadoAoCentro" id="acess">
                                <option value="NSNNNNNNNNNNS">Selecione</option>
                                <option value="NSNNNNNNNNNNS" <?= ($acesso == 'NSNNNNNNNNNNN' ? " selected" : ""); ?>>Chefia</option>
                                <option value="SSNNNNNNNSNNS" <?= ($acesso == 'SSNNNNNNNSNNN' ? " selected" : ""); ?>>Chefia RH</option>
                                <option value="NNSNNNNNNSNNS" <?= ($acesso == 'SNSNNNNNNSNNN' ? " selected" : ""); ?>>Sem Chefia RH</option>
                                <option value="NNSNNNNNNNNNS" <?= ($acesso == 'NNSNNNNNNNNNN' ? " selected" : ""); ?>>Sem Chefia</option>
                            </select>
                        </div>
                    </td>
                    <td width="670">
                        <p>Cod_lot:
                            <input name="lot" type="text" class='alinhadoAoCentro' id="lot" size="9" value="<?= tratarHTML($lot); ?>" readonly='9'>
                            -
                            <input name="dlot" type="text" class='alinhadoAoCentro' id="nome23" value='<?= tratarHTML($dlot); ?>' size="80" readonly>
                        </p>
                    </td>
                    <td width="125">
                        <p>Upag:
                            <input name="upag" type="text" class='alinhadoAoCentro' id="upag" size="10" value="<?= tratarHTML($upag); ?>" readonly='10'>
                        </p>
                    </td>
                </tr>
            </table>
            <font size="2"><strong>DADOS DO CADASTRO DE SUBSTITUI&Ccedil;&Atilde;O<br>
            </strong> </font>
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="126" align='center'>Id</td>
                    <td width="510" align='center'>Numero da fun&ccedil;&atilde;o</td>
                    <td width="217" align='center'> In&iacute;cio</td>
                    <td width="196" align='center'> <p>Fim</p></td>
                    <td width="151" align='center'> <p>Situa&ccedil;&atilde;o</p></td>
                </tr>
                <?php
                if ($num > 0)
                {
                    $oDBase->data_seek();

                    while ($pm = $oDBase->fetch_array())
                    {
                        ?>
                        <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)' height='18'>
                            <td align='center'><a href='substituicao_encerrar.php?id=<?= tratarHTML($pm['id']); ?>&orig=1' target='new'><?= tratarHTML($pm['id']); ?></a></td>
                            <td align='center'><?= tratarHTML($pm['numfunc']); ?></td>
                            <td align='center'><?= tratarHTML($pm['inicio']); ?></td>
                            <td align='center'><?= tratarHTML($pm['fim']); ?></td>
                            <td align='center'><?= tratarHTML($pm['situacao']); ?></td>
                        </tr>
                        <?php
                    }
                }
                else
                {
                    echo "Não há dados de substituição.";
                }
                ?>
            </table>
            <p align="center" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6; margin-bottom: 0">
                <input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>ok.gif" name="enviar" alt="Submeter os valores" align="center" >
            </p>
</form>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
