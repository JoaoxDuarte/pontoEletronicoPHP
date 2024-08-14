<?php

include_once("config.php");
include_once("class_control_selecao_relatorio.php");

set_time_limit(400);

// verifica se o usuário tem permissão para acessar este módulo
verifica_permissao('diretoria_rh_auditoria_administrador');

$upag  = $_SESSION['upag'];

// dados voltar
$_SESSION['voltar_nivel_1'] = 'frequencia_verificar_homologados.php';
$_SESSION['voltar_nivel_2'] = '';
$_SESSION['voltar_nivel_3'] = '';
$_SESSION['voltar_nivel_4'] = '';

// seleciona descricao do setor
$wnomelota = getUorgDescricao($upag);



## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho("Gestão Estratégica » Grade de Horário");
$oForm->setJSSelect2();
$oForm->setJS("relatorio_registro_grade_horario.js");

$oForm->setSubTitulo("Quadro de Horário dos Servidores das Unidade");


// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


?>
<div class="container">

    <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

    <?php exibeDescricaoOrgaoUorg($upag); // Exibe Órgão e Uorg com suas descrições padrão: $_SESSION['sLotacao']; ?>

    <div class="row margin-25">
        <form id='form1' name='form1' method='POST' onsubmit="javascript:return false;">

            <div class="row">
                <div class="col-md-2 text-left" style="margin-top: 5px;">
                    <p><b>Selecione a Unidade: </b></p>
                </div>
                <div class="col-md-8 text-left">
                    <?php CarregaSelectUnidades(); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2 col-md-offset-5 margin-20">
                    <button type="button" id="btn-enviar" name="enviar" class="btn btn-success btn-block">
                        <span class="glyphicon glyphicon-ok"></span> Continuar
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

die();


/* ************************************************ *
 *                                                  *
 *              FUNÇÕES COMPLEMENTARES              *
 *                                                  *
 * ************************************************ */

/*
 * @param string $ano  Ano da competência da homologação
 * @param string $mes  Mês da competência da homologação
 * @param string $upag UPAG da unidade do servidor/estagiário
 *
 * @info Total de servidores/estagiarios por UPAG
 */
function listaUnidadesDaUpag()
{
    $sql = "
    SELECT
        tabsetor.codigo,
        tabsetor.descricao,
        COUNT(*) AS total
    FROM
        tabsetor
    WHERE
        tabsetor.ativo = 'S'
        AND tabsetor.upag = :upag
    GROUP BY
        tabsetor.descricao
    ";

    // instancia o banco de dados
    $oDBase = new DataBase('PDO');
    $oDBase->query(
        $sql,
        array(
            array( ":upag", $_SESSION['upag'], PDO::PARAM_STR ),
        )
    );

    return $oDBase;
}


/**
 * @info Lista as unidades
 * 
 * @param void
 *
 * @author Edinalvo Rosa
 */
function CarregaSelectUnidades()
{
    $oDBase = listaUnidadesDaUpag();

    ?>
    <select class="form-control select2-single" id='lotacao' name='loracao'>
        <option value=''>Selecione uma unidade</option>
        <?php

        $num_unidades = $oDBase->num_rows();

        while ($pm = $oDBase->fetch_object())
        {
            ?>
            <option value='<?= tratarHTML($pm->codigo); ?>'>
                <?= tratarHTML(getUorgMaisDescricao($pm->codigo)); ?>
            </option>
            <?php
        }

        ?>
    </select>
    <small style="font-size:9px;vertical-align:top;margin-top:0px;padding-left:3px;padding-top:0px;">(<?= number_format($num_unidades,0,',','.'); ?> unidades)</small>
    <?php
}
