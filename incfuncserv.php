<?php

include_once("config.php");

// verifica se o usuário tem permissão para acessar este módulo
verifica_permissao('sRH e sTabServidor');

// recuera dados gravados em sessao
if (isset($_SESSION['grava_inclui_funcao']) && $_SESSION['grava_inclui_funcao'] != NULL)
{
    $novafuncao = $_SESSION['sNovaFuncao'];
    $idsubs     = $_SESSION['sIdSubs'];
    $matricula  = $_SESSION['sMatricula'];
    $ocupacao   = $_SESSION['sOcupacao'];
    unset($_SESSION['grava_inclui_funcao']);
}

$lista_das_funcoes_vagas = listaFuncoesDisponiveis($novafuncao);

## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setJSSelect2();
$oForm->setJS("incfuncserv.js");

$oForm->setSubTitulo("Inclus&atilde;o de Ocupante de Fun&ccedil;&atilde;o");


// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


?>
<form method='POST' id="form1" name="form1" >
    <input type="hidden" id="ocupacao" name="ocupacao">

    <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

    <div align="center">
        <table class='table table-condensed text-center'>
            <tr>
                <td colspan="5" class='text-left' style='border-width:0px;'><div align="center"></div></td>
            </tr>
            <tr>
                <td colspan="5" nowrap class='text-left' style='border-width:0px;'>
                    <div class='text-left'>
                        <label class='control-label'>Escolha a fun&ccedil;&atilde;o:</label>
                        <select size="1" name="novafuncao" id="novafuncao" class='form-control select2-single'>
                            <?= $lista_das_funcoes_vagas['opcoes']; ?>
                        </select>
                        <small>Funções vagas: <?= tratarHTML($lista_das_funcoes_vagas['num_rows']); ?> (titular e/ou substituto)</small>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="5" class='text-left' style='border-width:0px;'><font color="#000000">&nbsp;</font></td>
            </tr>
            <tr>
                <td width="19%" height="34" nowrap class='text-right' style='border-width:0px;'>
                    <label class='control-label' style='padding-top:5px;'>Matr&iacute;cula do ocupante:</label>
                </td>
                <td width="13%" class='text-left' style='border-width:0px;'>
                    <input type="hidden" name="idsubs"    id="idsubs"    value="<?= tratarHTML($idsubs); ?>">
                    <input type="text"   name="matricula" id="matricula" value="<?= removeOrgaoMatricula($matricula); ?>" size="7" maxlength="7" class='form-control' style="width:88px;">
                </td>
                <td nowrap class='text-left' style='border-width:0px;padding-left:90px;'>
                    <label class='control-label' style='padding-top:5px;'>Situa&ccedil;&atilde;o do ocupante:&nbsp;</label>
                </td>
                <td width="67%" class='text-left' style='border-width:0px;'>
                    <select size="1" id="ocupacao_select" name="ocupacao_select" class='form-control select2-single'>
                        <option value="V">SELECIONE UMA OPÇÃO</option>
                        <option value="T" <?= ($ocupacao == 'T' ? ' selected' : ''); ?>>TITULAR</option>
                        <option value="S" <?= ($ocupacao == 'S' ? ' selected' : ''); ?>>SUBSTITUTO</option>
                        <option value="R" <?= ($ocupacao == 'R' ? ' selected' : ''); ?>>INTERINO</option>
                    </select>
                </td>
                <td class='text-left' style='border-width:0px;'>&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr>
                <td colspan="5" class='text-left' style='border-width:0px;'>
                    &nbsp;
                </td>
            </tr>
            <tr>
                <td colspan="5" class='text-left' style='border-width:0px;'><div align="center"></div></td>
            </tr>
            <tr>
                <td colspan="5" class='text-left' style='border-width:0px;'>

                    <div class="form-group col-md-8 text-center">
                        <div class="col-md-7 col-md-offset-6 margin-10">
                            <div class="col-md-6 text-right">
                                <a class="btn btn-success btn-primary" id="btn-continuar">
                                    <span class="glyphicon glyphicon-ok"></span> Continuar
                                </a>
                            </div>
                        </div>
                    </div>

                </td>
            </tr>
        </table>
    </div>

    <div>
        <div style='text-align:right;width:100%;margin:0px;font-size:9px;border:0px;'>
            <fieldset style='border:1px solid white;text-align:left;'>
                <legend style='font-size:10px;padding:0px;margin:0px;'><b>&nbsp;Informações&nbsp;</b></legend>
                Nos campos que possuem uma lista para selecionar item, digite parte da descrição do item e em seguida selecione-o.
            </fieldset>
        </div>
    </div>

</form>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();




## ################################################ ##
##                                                  ##
##              FUNÇÕES COMPLEMENTARES              ##
##                                                  ##
## ################################################ ##

function listaFuncoesDisponiveis($novafuncao="")
{
    $upag = $_SESSION['upag'];

    // instancia banco de dados
    $oDBase = new DataBase('PDO');

    $oDBase->query( "
    SELECT
        tabfunc.num_funcao AS num_funcao,
        tabfunc.cod_lot,
        tabfunc.desc_func,
        ocupantes.mat_siape,
        servativ.nome_serv,
        IFNULL(ocupantes.sit_ocup,'') AS sit_ocup,
        'Titular' AS situacao,
        tabfunc.tipo
    FROM
        tabfunc
    LEFT JOIN
        ocupantes ON (tabfunc.num_funcao = ocupantes.num_funcao AND ocupantes.sit_ocup <> 'S')
    LEFT JOIN
        tabsetor ON tabfunc.cod_lot = tabsetor.codigo
    LEFT JOIN
        servativ ON ocupantes.mat_siape = servativ.mat_siape
    WHERE
        ((tabfunc.ativo ='S' AND tabfunc.upag = :upag AND tabsetor.ativo = 'S')
        OR (tabfunc.ativo ='S' AND tabfunc.upag = :upag AND tabsetor.ativo = 'S'
            AND tabfunc.indsubs = 'N' AND IFNULL(ocupantes.mat_siape,'') = ''))
        AND IFNULL(servativ.nome_serv,'') = ''

    UNION

    SELECT
        tabfunc.num_funcao AS num_funcao,
        tabfunc.cod_lot AS cod_lot,
        tabfunc.desc_func, ocupantes.mat_siape, servativ.nome_serv,
        IFNULL(ocupantes.sit_ocup,'') AS sit_ocup,
        'Substituto' AS situacao, tabfunc.tipo
    FROM
        tabfunc AS tabfunc
    LEFT JOIN
        ocupantes AS ocupantes ON (tabfunc.num_funcao = ocupantes.num_funcao AND ocupantes.sit_ocup = 'S')
    LEFT JOIN
        tabsetor  AS tabsetor ON tabfunc.cod_lot = tabsetor.codigo
    LEFT JOIN
        servativ  AS servativ ON ocupantes.mat_siape = servativ.mat_siape
    WHERE
        ((tabfunc.ativo ='S' AND tabfunc.upag = :upag AND tabsetor.ativo = 'S')
        OR (tabfunc.ativo ='S' AND tabfunc.upag = :upag AND tabsetor.ativo = 'S'
            AND tabfunc.indsubs = 'N' AND IFNULL(ocupantes.mat_siape,'') = ''))
        AND tabfunc.indsubs = 'S' AND IFNULL(servativ.nome_serv,'') = ''

    GROUP BY
        num_funcao
    ORDER BY
        cod_lot, num_funcao, IF(situacao='',3,IF(situacao='Substituto',2,1)), IF(sit_ocup='S',2,1)
    ",
    array(
        array( ':upag', $upag, PDO::PARAM_STR ),
    ));

    $opcoes = "<option value='00000'>SELECIONE UMA OPÇÃO</option>";

    while ($linha = $oDBase->fetch_array())
    {
        $opcoes .= "<option value='" . $linha["num_funcao"] . "'"
        . ($linha["num_funcao"] == $novafuncao ? " selected" : "") . ">"
        . $linha["num_funcao"] . " - "
        . $linha["cod_lot"] . " - "
        . $linha["desc_func"] . " ("
        . $linha["situacao"] . ") "
        . "</option>";
    }

    return array(
        'opcoes'   => $opcoes,
        'num_rows' => $oDBase->num_rows()
    );
}