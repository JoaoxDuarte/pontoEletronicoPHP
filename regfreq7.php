<?php
include_once( 'config.php' );

verifica_permissao("sRH");

$mat = anti_injection($_REQUEST['mat']);

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
$oForm->setCaminho("Frequência » Atualizar » Desomologar");
$oForm->setCSS(_DIR_CSS_ . "estilos.css");
$oForm->setJS(_DIR_JS_ . "desativa_teclas_f_frames.js");
$oForm->setJS("regfreq7.js");
$oForm->setOnLoad("javascript: if($('#lSiape')) { $('#lSiape').focus() };");
$oForm->setSeparador(10);

// Topo do formulário
//
$oForm->setSubTitulo("Devolver &agrave; chefia para ajustes");

// Topo do formulário
//
$oForm->setObservacaoTopo("Utilize essa op&ccedil;&atilde;o para devolu&ccedil;&atilde;o &agrave; chefia de frequ&ecirc;ncia com incorre&ccedil;&atilde;o relativa ao m&ecirc;s homologado");

$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<form action="gravaregfreq2.php" method="post" id="form1" name="form1" onSubmit="return validar()">
    <input type='hidden' name='modo' value='7'>
    <input type='hidden' name='comp' value='<?= tratarHTML($mes); ?>'>
    <div align="center">
        <table cellpadding='0' cellspacing='0' style='border-collapse: collapse; vertical-align: top; border: 1px solid #808040;' valign='top' bordercolor='#808040' width='90%'>
            <tr>
                <td colspan='3' style='height:25px; background-color: #DFDFBF; border-bottom: 1px solid #808040;'>
                    <p align='center'><b>MENSAGEM DE DEVOLUÇÃO</b></p>
                </td>
            </tr>
            <tr>
                <td style='test-align: center; height: 150px' align='center'>
                    &nbsp;&nbsp;Informamos que foi desomologada a frequ&ecirc;ncia do(a) servidor(a) siape&nbsp;
                    <input id="siape" name="siape" type="text"  class='alinhadoAoCentro' value="<?= tratarHTML($mat); ?>"size="7" maxlength="7">, por<br>
                    <textarea name="motivo" cols='90' rows='7' id="motivo" ><?= tratarHTML($just); ?></textarea>
                </td>
            </tr>
            <tr>
                <td colspan='3' height='40px' style='text-align: center; vertical-align: middle; border-top: 0px solid #808040;'>
                    <table border='0' align='center'>
                        <tr>
                            <td align='left'>
                                <input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>ok.gif" name="enviar" alt="Submeter os valores" align="center" >
                            </td>
                            <td>&nbsp;&nbsp;&nbsp;</td>
                            <td align='left'>
                                <?php
                                $destino = pagina_de_origem();
                                if ($destino == 'regfreq8.php' && $_SESSION['voltar_nivel_1'] != '')
                                {
                                    $destino = 'javascript:window.history.go(-1)';
                                    print botao('Voltar', 'javascript:window.location.replace("' . $_SESSION['voltar_nivel_2'] . '");');
                                }
                                ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</form>
<?php
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
