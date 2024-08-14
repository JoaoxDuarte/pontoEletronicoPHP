<?php

include_once( "config.php" );

verifica_permissao("sRH");


$matricula  = anti_injection($_GET['matricula']);
$matricula  = getNovaMatriculaBySiape($matricula);
$novafuncao = anti_injection($_GET['novafuncao']);

// instancia o BD
$oDBase = new DataBase('PDO');

// dados servidor
$oDBase->query("SELECT nome_serv FROM servativ WHERE mat_siape = '$matricula' ");
$oServidor = $oDBase->fetch_object();


$nome      = $oServidor->nome_serv;

/* Pega os valores do formulário anterior para pesquisar na tabela funserv se há ocupante na função */
$oDBase->query("SELECT num_doc1, dt_doc1, cod_doc2, num_doc2, dt_doc2, resp_lot, sit_ocup, resp_lot, dt_inicio FROM ocupantes WHERE NUM_FUNCAO = '$novafuncao' AND MAT_SIAPE = '$matricula' ");
//and dt_fim is NULL";
$oOcupante = $oDBase->fetch_object();

$doc1     = $oOcupante->num_doc1;
$dtdoc1   = $oOcupante->dt_doc1;
$fpub     = $oOcupante->cod_doc2;
$pub      = $oOcupante->num_doc2;
$dtpub    = $oOcupante->dt_doc2;
$resp     = $oOcupante->resp_lot;
$sit      = $oOcupante->sit_ocup;
$resp     = $oOcupante->resp_lot;
$dtinicio = $oOcupante->dt_inicio;

//converter datas para exibir
$dtdoc1   = databarra($dtdoc1);
$dtpub    = databarra($dtpub);
$dtinicio = databarra($dtinicio);

switch ($sit)
{
    case 'T': $sit2 = "TITULAR";
        break;
    case 'S': $sit2 = "SUBSTITUTO";
        break;
    case 'R': $sit2 = "RESPONDENDO";
        break;
    case 'E': $sit2 = "EVENTUAL";
        break;
}

//busca na tabela Tabfunc para saber a descrição das funções
$oDBase->query("SELECT desc_func, num_funcao FROM tabfunc where NUM_FUNCAO = '$novafuncao' ");
$oFuncao  = $oDBase->fetch_object();
$funcao   = $oFuncao->desc_func;
$num_func = $oFuncao->num_funcao;


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho("Relatórios » Gerencial » Ocupantes de Fun&ccedil;&otilde;es");
$oForm->setJQuery();
$oForm->setCSS("
<style>
    .ftSize10  { font-size: 10px; }
    .ftSize11  { font-size: 11px; }
    .ftSize12  { font-size: 12px; }
    .ftVerdana { font-family: Verdana, Arial, Helvetica, sans-serif; }
    .ftColor-000000 { color: #000000; }
    .ftColor-D40000 { color: #D40000; }
</style>
");
$oForm->setJS("
<script>
    function validar()
    {
        // objeto mensagem
        oTeste = new alertaErro();
        oTeste.init();

        // dados
        var matricula = $('#matricula');
        var inicio    = $('#inicio');
        var Ndoc1     = $('#Ndoc1');
        var Ndoc2     = $('#Ndoc2');
        var Nnum1     = $('#Nnum1');
        var Nnum2     = $('#Nnum2');
        var Ndata1    = $('#Ndata1');
        var Ndata2    = $('#Ndata2');

        // validacao dos campos
        if (matricula.val().length < 7) { oTeste.setMsg( 'Favor digite a matrícula com 7 digitos', matricula ); }
        if (inicio.val().length < 10)   { oTeste.setMsg( 'Favor digite a data da nomeação no formato dd/mm/aaaa', inicio ); }
        if (Ndoc1.val().length < 2)     { oTeste.setMsg( 'Favor digite o documento com 2 dígitos', Ndoc1 ); }
        if (Ndoc2.val().length < 2)     { oTeste.setMsg( 'Favor digite o documento com 2 dígitos', Ndoc2 ); }
        if (Nnum1.val().length == 0 )   { oTeste.setMsg( 'Favor digite o número do documento', Nnum1 ); }
        if (Nnum2.val().length == 0)    { oTeste.setMsg( 'Favor digite o número do documento', Nnum2 ); }
        if (Ndata1.val().length < 10)   { oTeste.setMsg( 'Favor digite a data do documento no formato dd/mm/aaaa', Ndata1 ); }
        if (Ndata2.val().length < 10)   { oTeste.setMsg( 'Favor digite a data do documento no formato dd/mm/aaaa', Ndata2 ); }

        // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
        var bResultado = oTeste.show();

        return bResultado;
    }
</script>
");

$oForm->setSubTitulo("Registro de Ocupante de Fun&ccedil;&atilde;o");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<form method="POST" action="grava_inclui_funcserv.php" id="form1" name="form1">
    <input type='hidden' name='modo' value='1'>
    <input type="hidden" id="num_funcao" name="num_funcao" value="<?= tratarHTML($num_func); ?>">
    <input type="hidden" id="funcao"     name="funcao"     value="<?= tratarHTML($funcao); ?>">
    <input type='hidden' id='lota'       name='lota'       value='<?= tratarHTML($lot); ?>'>
    <input type='hidden' id='area'       name='area'       value='<?= tratarHTML($area); ?>'>
    <input type='hidden' id='sigla'      name='sigla'      value='<?= tratarHTML($sigla); ?>'>
    <input type='hidden' id='uorg'       name='uorg'       value='<?= tratarHTML($uorg); ?>'>
    <input type='hidden' id='lotat'      name='lotat'      value='<?= tratarHTML($lotat); ?>'>
    <input type='hidden' id='dinglota'   name='dinglota'   value='<?= tratarHTML($dinglota); ?>'>
    <table width="100%" cellpadding="0" cellspacing="0" border='0' style='border: 1px solid #808000;'>
        <tr>
            <td height='40' width="25%" style='border-bottom: 1px solid #808000;'>
                &nbsp;Matr&iacute;cula:
                <input type="text" id="matricula" name="matricula" class='caixa' value='<?= tratarHTML(removeOrgaoMatricula($matricula)); ?>' size="7" readonly>
            </td>
            <td colspan="4" style='border-bottom: 1px solid #808000;'>
                &nbsp;Nome:
                <input type="text" id="nome" name="nome" class='caixa' value='<?= tratarHTML($nome); ?>' size="50" readonly>
            </td>
        </tr>
        <tr height='40'>
            <td colspan="2" style='border-bottom: 1px solid #808000;'>
                &nbsp;Função:<br>
                &nbsp;<input type="text" id="func" name="func" class='caixa' value="<?= tratarHTML($num_func) . ' - ' . tratarHTML($funcao); ?>" size="60" readonly>
            </td>
            <td colspan="1" style='border-bottom: 1px solid #808000;' nowrap class='ftColor-000000'>
                Situa&ccedil;&atilde;o&nbsp;ocupante:<br>
                <input name="situacao" type="text" class="caixa" id="situacao" value="<?= tratarHTML($sit2) ?>" size="13" maxlength="13" readonly>
            </td>
            <td colspan="1" style='border-bottom: 1px solid #808000;'>&nbsp;</td>
            <td colspan="1" style='border-bottom: 1px solid #808000;' class='ftColor-000000'>
                Respons&aacute;vel do Setor?<br>
                <input name="resp_setor" type="text" class="caixa" id="resp_setor" value="<?= ($resp = 'S' ? 'SIM' : 'NÃO'); ?>" size="5" maxlength="5" readonly>
            </td>
        </tr>
        <tr>
            <td height="25" colspan="5" class='borda1'>
                <p align="left" valign='middle' class='ftTahoma ftSize12 ftColor-D40000'>
                    &nbsp;<strong>INFORME OS DADOS A SEGUIR</strong>
                </p>
            </td>
        </tr>
        <tr>
            <td colspan='5'>
                <table width="100%" cellpadding="0" cellspacing="0" border='0'>
                    <tr height='25' style='background-color: #F2F2E6;'>
                        <td width="19%">&nbsp;In&iacute;cio de exerc&iacute;cio:<br></td>
                        <td colspan="5" style='border-left: 1px solid #f9f9f2;'>
                            <p>&nbsp;&nbsp;&nbsp;Portaria de nomea&ccedil;&atilde;o/designa&ccedil;&atilde;o:</p>
                        </td>
                    </tr>
                    <tr height='45'>
                        <td width="19%">
                            &nbsp;Data:<br>
                            &nbsp;<input type="text" id="inicio" name="inicio" class='caixa' onKeyPress="formatar(this, '##/##/####')" value="<?= tratarHTML($dtinicio); ?>" size="10" maxlength='10'>
                        </td>
                        <td style='border-left: 1px solid #F2F2E6;'>
                            <p>
                                &nbsp;&nbsp;N&uacute;mero:<br>
                                &nbsp;&nbsp;<input name="Nnum1" type="text" class='caixa' id="Nnum1" value="<?= tratarHTML($doc1); ?>" size="9" maxlength='9'>
                            </p>
                        </td>
                        <td>
                            <p>
                                &nbsp;Data:<br>
                                &nbsp;<input name="Ndata1" type="text" class='caixa' id="Ndata1" onKeyPress="formatar(this, '##/##/####')" value="<?= tratarHTML($dtdoc1); ?>" size="10" maxlength='10'>
                            </p>
                        </td>
                        <td style='border-left: 1px solid #F2F2E6;'>
                            <p>
                                &nbsp;&nbsp;&nbsp;Publica&ccedil;&atilde;o:<br>
                                &nbsp;&nbsp;&nbsp;<select name="publicacao" id="publicacao" class="ui-widget">
                                    <?php
                                    switch ($fpub)
                                    {
                                        case '00':
                                            $options = '<option value="00"' . ($publicacao == '00' ? ' selected' : '') . '>Selecione</option>';
                                            break;
                                        case 'DO':
                                            $options = '<option value="DO"' . ($publicacao == 'DO' ? ' selected' : '') . '>Diário&nbsp;Oficial&nbsp;da&nbsp;União</option>';
                                            break;
                                        case 'BSL':
                                            $options = '<option value="BSL"' . ($publicacao == 'BSL' ? ' selected' : '') . '>Boletim&nbsp;de&nbsp;Serviço&nbsp;Local</option>';
                                            break;
                                    }

                                    print $options;
                                    ?>
                                </select>
                            </p>
                        </td>
                        <td nowrap style='border-right: 0px solid white; border-left: 0px solid white;'>
                            <p>
                                &nbsp;N&uacute;mero:<br>
                                &nbsp;<input name="Nnum2" type="text" class='caixa' id="Nnum2" value="<?= tratarHTML($pub); ?>" size="9" maxlength='9'>
                            </p>
                        </td>
                        <td style='border-left: 0px solid white;'>
                            <p>
                                &nbsp;Data:<br>
                                &nbsp;<input name="Ndata2" type="text" class='caixa' id="Ndata2" onKeyPress="formatar(this, '##/##/####')" value="<?= $dtpub; ?>" size="10" maxlength='10'>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <br>
    <table border='0' align='center' style='border: 0 solid white;' cellpadding="0" cellspacing="0">
        <tr style='border: 0 solid white;'>
            <td style='border: 0 solid white;'>
                <a  class="btn btn-primary" id="btn-voltar" href='ocupfuncserv.php'>
                    <span class="glyphicon glyphicon-ok"></span> Voltar
                </a>
            </td>
        </tr>
    </table>
</form>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
