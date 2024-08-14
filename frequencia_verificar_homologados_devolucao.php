<?php
include_once( 'config.php' );

verifica_permissao("sRH");

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    header("Location: acessonegado.php");
}
else
{
    $dados = explode(":|:", base64_decode($dadosorigem));
    $siape = $dados[0];
}

$nome       = getNomeServidor($siape, $com_siape=false);
$sMatricula = $_SESSION['sMatricula'];

// Competência atual (mês e ano)
$data = new competencia();
$ano  = $data->ano;
$year = $data->year;
$comp = $data->comp;
$mes  = $data->mes;

## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setJSCKEditor();
$oForm->setJS("frequencia_verificar_homologados_devolucao.js");


$oForm->exibeTopoHTML($sem_header=true);
$oForm->exibeCorpoTopoHTML();

?>
<div class='container' style='position:absolute;top:10px;'>

    <div class='row '>

        <form action="#" method="post" id="form1" name="form1" onSubmit="javascript:return false;">
            <input type='hidden' name='modo'  value='7'>
            <input type='hidden' name='comp'  value='<?= tratarHTML($year) . tratarHTML($comp); ?>'>
            <input type='hidden' name='dados' value='<?= tratarHTML($dadosorigem); ?>'>

            <div align="center">
                <table class="table table-condensed table-bordered text-center">
                    <tr>
                        <td colspan='3'>
                            <p align='center'><b>MENSAGEM DE DEVOLUÇÃO</b></p>
                        </td>
                    </tr>
                    <tr>
                        <td style='text-align:justify; height: 150px' align='center'>
                            <div class="col-md-10 margin-bottom-10">
                                Informamos devolução da frequ&ecirc;ncia, de <b><?= tratarHTML($comp).'/'.tratarHTML($year); ?></b>, do(a) servidor(a) <?= tratarHTML($nome); ?>, matrícula <?= tratarHTML(removeOrgaoMatricula($siape)); ?>, &agrave; chefia para ajustes.
                            </div>

                            <div class="col-md-12 center">
                                <textarea style="resize: none" type='text' name="motivo" id="motivo" cols='90' rows='7' class="mensagem">
                                    <?= tratarHTML($just); ?>
                                </textarea>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='3' height='40px' style='text-align: center; vertical-align: middle; border-top: 0px solid #808040;'>
                            <table border='0' align='center'>
                                <tr>
                                    <td align='left'>
                                        <button class="btn btn-success text-left" id="btn-continuar">
                                            <span class="glyphicon glyphicon-ok"></span> Concluir / Enviar E-mail
                                        </button>
                                    </td>
                                    <td>&nbsp;&nbsp;&nbsp;</td>
                                    <td align='left'>
                                        <button class="btn btn-default" id="btn-fechar-janela">
                                            Fechar
                                        </button>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </form>
    </div>
</div>
<?php

$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
