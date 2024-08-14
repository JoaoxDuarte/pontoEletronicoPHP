<?php

/* _________________________________________________________________________*\
  |                                                                           |
  |   MANUTENÇÃO DAS PERMISSÕES DO USUARIO CADASTRADO                         |
  |                                                                           |
  \*¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯ */

include_once('config.php');

verifica_permissao('administrador');

$siape   = addslashes($_REQUEST['siape']);
$lotacao = str_replace('.', '', addslashes($_REQUEST['lotacao']));

## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCSS( "css/select2.min.css" );
$oForm->setCSS( "css/select2-bootstrap.css" );
$oForm->setCSS( "js/bootstrap-datepicker/css/bootstrap-datepicker3.standalone.min.css" );

$oForm->setJS( "js/funcoes_valida_cpf_pis.js" );
$oForm->setJS( "js/select2.full.js" );
$oForm->setJS( 'js/bootstrap-datepicker/js/bootstrap-datepicker.min.js' );
$oForm->setJS( 'js/bootstrap-datepicker/locales/bootstrap-datepicker.pt-BR.min.js' );
$oForm->setJS( "js/jquery.blockUI.js?v2.38" );
$oForm->setJS( "js/plugins/jquery.dlg.min.js" );
$oForm->setJS( "js/plugins/jquery.easing.js" );
$oForm->setJS( "js/jquery.ui.min.js" );

$oForm->setJS( "usuario_alt.js?v1" );

$oForm->setSubTitulo("Alteração de dados do Usuário");


// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


/*
 * Permissões - Sequência
 *
 * N N S N N N N N N N N N N N N N N N N N ==> Servidores/Estagiários
 * N S S N N N N N N N N N N N N N N N N N ==> Chefe
 * N N S N N N S N N S N N N N N N S N N N ==> Gestão de Pessoas
 * S N S N N N S N N S N N N N N N S N N N ==> Chefe e Gestão de Pessoas
 *
 *
 *
 * S S S S S S S S S S S S S S S S S S S S S
 * | | | | | | | | | | | | | | | | | | | | |
 * | | | | | | | | | | | | | | | | | | | | +-----> 21  Configurar Limite de Hora Extra
 * | | | | | | | | | | | | | | | | | | | +-------> 20  Acompanhar/Registrar Frequência (excepcional)
 * | | | | | | | | | | | | | | | | | | +---------> 19  Gestão Estratégica
 * | | | | | | | | | | | | | | | | | +-----------> 18  Consulta Superintendência
 * | | | | | | | | | | | | | | | | +-------------> 17  Consulta Gerência Executiva
 * | | | | | | | | | | | | | | | +---------------> 16  Consulta Estado
 * | | | | | | | | | | | | | | +-----------------> 15  Consulta Brasil
 * | | | | | | | | | | | | | +-------------------> 14  Acesso SIC
 * | | | | | | | | | | | | +---------------------> 13  Acesso para consulta completa
 * | | | | | | | | | | | +-----------------------> 12  Log do Sistema
 * | | | | | | | | | | +-------------------------> 11  Administrar Usuários
 * | | | | | | | | | +---------------------------> 10  De Servidores
 * | | | | | | | | +-----------------------------> 09  De Prazos
 * | | | | | | | +-------------------------------> 08  Gerenciais
 * | | | | | | +---------------------------------> 07  Recursos Humanos
 * | | | | | +-----------------------------------> 06  Diretoria
 * | | | | +-------------------------------------> 05  Médico
 * | | | +---------------------------------------> 04  Auditoria
 * | | +-----------------------------------------> 03  Servidores e Estagiários
 * | +-------------------------------------------> 02  Chefes
 * +---------------------------------------------> 01  Recursos Humanos
 *
*/

?>
<form method="POST" id="form1" name="form1">
    <input type="hidden" name="modo" value="2">
    <table align="center" border="0" width="100%" cellspacing="0" cellpadding="0" bordercolordark="white" bordercolorlight="#336699" >
        <tr>
            <td width="25%" align="right"><label>Siape</label></td>
            <td width="2%"></td>
            <td width="44%">
                <div class='col-md-3'>
                    <input type="text" id="sSiape" name="sSiape" value="<?= tratarHTML(removeOrgaoMatricula($siape)); ?>" size="7" maxlength="7" class='form-control' readonly>
                </div>
            </td>
        </tr>
        <tr>
            <td width="25%" align="right"><label>Nome</label></td>
            <td width="2%"></td>
            <td width="44%">
                <div class='col-md-10'>
                    <input type="text" id="sNome" name="sNome" value="<?= tratarHTML($nome); ?>" size="50" maxlength="50" class='form-control' readonly>
                </div>
            </td>
        </tr>
        <tr>
            <td width="25%" align="right"><label>Lotação</label></td>
            <td width="2%"></td>
            <td width="44%">
                <?php

                $oDBase = new DataBase('PDO');
                $oDBase->query("
                SELECT
                    a.codigo,
                    IF(a.codigo='01001','DIRECAO CENTRAL',a.descricao) AS descricao
                FROM
                    tabsetor AS a
                WHERE
                    a.ativo='S' AND a.codigo = :lotacao
                ",
                array(
                    array( ':lotacao', $lotacao, PDO::PARAM_STR ),
                ));
                $oSetor = $oDBase->fetch_object();
                ?>
                <div class='col-md-12'>
                    <input type="hidden" id="sSetor" name="sSetor" value="<?= tratarHTML($oSetor->codigo) . "|" . tratarHTML($oSetor->upag); ?>">
                    <input type="text" id="lotacao" name="lotacao" value="<?= tratarHTML(getOrgaoByUorg( $oSetor->codigo )) . '.' . tratarHTML($oSetor->descricao); ?>" size="70" maxlength="70" class='form-control' readonly>
                </div>
            </td>
        </tr>
    </table>
    <br>
    <table class='table table-striped table-condensed table-bordered table-hover text-center' style='width:55%;margin:0px 10% 0px 22%'>
        <tr>
            <th width="75%" colspan='2'>Módulos</th>
            <th style="width:20%;text-align:center">Permissão</th>
        </tr>
        <?php
        // Modulos, permissoes, valor
        $modulos = array();
        $modulos = select_permissoes();

        $grupo = '';
        for ($i = 0; $i < count($modulos); $i++)
        {
            if ($modulos[$i]['modulos'] != $grupo)
            {
                if (!empty($grupo))
                {
                    ?>
                    </table>
                    <table class='table table-striped table-condensed table-bordered table-hover text-center' style='width:55%;margin:0px 10% 0px 22%'>
                    <?php
                }

                ?>
                <tr>
                    <th class='form-control col-md-2' style='width:100%;height:20px;background-color:#CECE9D;font-family:Verdana;font-size:13px;font-weight:bold;text-align:left;text-indent:28px;' colspan='3'>
                        &nbsp;<?= tratarHTML($modulos[$i]['modulos']); ?>
                    </th>
                </tr>
                <?php

                $grupo = $modulos[$i]['modulos'];
            }

            $ind = $modulos[$i][2];
            settype($ind, "integer");

            ?>
            <tr>
                <td class='text-left' style='width:1%;max-height:5px;font-family:Verdana;font-size:12px;border-bottom:1px solid #e6e6e6;text-align:left;text-indent:4px;'>
                    <label><?= tratarHTML($modulos[$i]['cod']); ?></label>
                </td>
                <td class='text-left' style='width:75%;max-height:5px;font-family:Verdana;font-size:12px;border-bottom:1px solid #e6e6e6;text-align:left;text-indent:10px;'>
                    <label>&nbsp;&nbsp;<?= tratarHTML($modulos[$i]['permissao']); ?></label>
                </td>
                <td class='text-center' style='width:20%;max-height:5px;font-family:Verdana;font-size:12px;border-bottom:1px solid #e6e6e6;text-align:center;'>
                    <input type='checkbox' id='C<?= tratarHTML($modulos[$i]['cod']); ?>' name='C<?= tratarHTML($modulos[$i]['cod']); ?>' value='<?= tratarHTML($modulos[$i]['cod']); ?>'>
                </td>
            </tr>
            <?php
        }

        ?>
        <input type='hidden' id='C00' name='C00' value='<?= $i; ?>' >
    </table>

    <div class='control-group col-md-8 col-lg-offset-4' style='padding:15px 0px 0px 0px;'>

        <div class="col-md-3 text-center">
            <a class="btn btn-success btn-block" id="btn-continuar">
                <span class="glyphicon glyphicon-ok"></span> Gravar
            </a>
        </div>

        <div class="col-md-2 text-center">
            <a class="btn btn-primary btn-danger" id="btn-voltar" href="javascript:window.location.replace('usuario_lista.php');" style='width:141px;'>
                <span class="glyphicon glyphicon-ok"></span> Voltar
            </a>
        </div>
    </div>

</form>

<script language="JavaScript">
    var sMatricula = '<?= tratarHTML($siape); ?>';
    pesquisa(sMatricula);
</script>
<?php


// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
