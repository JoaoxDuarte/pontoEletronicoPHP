<?php
include_once("config.php");
include_once( "class_ocorrencias_grupos.php" );

verifica_permissao("sLog");

$siape = anti_injection($_REQUEST['siape']);


$oDBase = selecionaServidor( $siape );
$sitcad = $oDBase->fetch_object()->sigregjur;

// Grupos de ocorrências
$obj = new OcorrenciasGrupos();

$codigoCreditoRecessoPadrao  = $obj->CodigoCreditoRecessoPadrao($sitcad, $exige_horarios=true)[0];
$codigoDebitoRecessoPadrao   = $obj->CodigoDebitoRecessoPadrao($sitcad)[0];
$codigoRegistroParcialPadrao = $obj->CodigoRegistroParcialPadrao($sitcad)[0];


$year  = date('Y') - 1;
$year2 = date('Y');

// instancia BD
$oDBase  = new DataBase('PDO');
$oDBase2 = new DataBase('PDO');

//rotina para recuperar dados do banco de horas
$oDBase->query("SELECT * FROM bhoras WHERE siape = :siape AND (codigo = :recesso_debito OR codigo = :recesso_credito) ORDER BY comp ",
array(
    array(':siape', $siape, PDO::PARAM_STR),
    array(':recesso_debito', $codigoDebitoRecessoPadrao, PDO::PARAM_STR),
    array(':recesso_credito', $codigoCreditoRecessoPadrao, PDO::PARAM_STR)
));

//        $recesso = $oDBase->num_rows();
//        $tem = $oDBase->fetch_array();
//        if($tem){
//            $recesso = $oDBase->num_rows();
//        }
## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setOnLoad("javascript: if($('#mes')) { $('#mes').focus() };");

$oForm->setSubTitulo("Recalcular Compensação de Recesso");


// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


?>
<script>
function validar()
{
    // objeto mensagem
    oTeste = new alertaErro();
    oTeste.init();

    // dados
    var mes = $('#mes');
    var ano = $('#ano');

    if (mes.val().length < 2 || mes.val() > 12 ||
       (ano.val() == 2009 && mes.val() < 10))
    {
        oTeste.setMsg('Mes inválido!', mes);
    }

    if (ano.val().length < 4 || ano.val() < 2009)
    {
        oTeste.setMsg('Ano inválido!', ano);
    }

    // se houve erro será(ão) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    return bResultado;
}
</script>

<form action="atualizarecessobhoras.php" method="post" id="form1" name="form1" onSubmit="return validar()">
    <input type="hidden" id="an" name="an" value="<?= date('Y'); ?>">
    <div align="center">
        <table width="24%" height="46" border="1" cellpadding="0" cellspacing="0" bordercolor="#808040" id="AutoNumber1" style="border-collapse: collapse">
            <tr>
                <td width="118" height="44" align="center" >
                    <p align="center" style="margin-top: 0; margin-bottom: 0"><font size="2" face="Tahoma">Siape:</font></p>
                    <p align="center" style="margin-top: 0; margin-bottom: 0">
                        <input name="siape" type="text" class='alinhadoAoCentro'id="siape" title="Informe o siape do servidor" value = "<?= tratarHTML(removeOrgaoMatricula( $siape )); ?>" size="8" readonly="8">
                    </p>
                </td>
                <td width="141" align="center" >
                    <p align="center" style="margin-top: 0; margin-bottom: 0"><font size="2" face="Tahoma">Mes - Ano:</font></p>
                    <p align="center" style="margin-top: 0">
                        <input type="text" id="mes" name="mes" class='alinhadoAoCentro' title="Informe o mes" size="4" maxlength="2">
                        <input type="text" id="ano" name="ano" class='alinhadoAoCentro' title="Informe o ano" size="6" maxlength="4">
                </td>
            </tr>
        </table>
    </div>

    <p align="center" style="word-spacing: 0; margin-top: 0; margin-bottom: 0">&nbsp;</p>
    <p align="center" style="word-spacing: 0; margin: 0"><input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>ok.gif" name="enviar" alt="Submeter os valores" align="center" ></p>
    <br>

    <table width='60%' border='1' align='center' cellpadding='0' cellspacing='0' bordercolor='#808040' id='AutoNumber1' style='border-collapse: collapse'>
        <tr>
            <td height='23' colspan='2' bgcolor='#DFDFBF'> <div align="center"><b>Comparativo do banco de horas com ficha de frequ&ecirc;ncia</b></div></td>
        </tr>
        <tr>
            <td height='23' bgcolor='#DFDFBF'> <div align="center"><strong>Banco de horas</strong></div></td>
            <td height='23' bgcolor='#DFDFBF'><div align="center"><strong>Ficha de frequ&ecirc;ncia</strong></div></td>
        </tr>
        <?php
        $oDBase->data_seek();

        while ($pm = $oDBase->fetch_array())
        {
            //rotina para recuperar os totais da folha de frequência
            // Essa query me retorna 3 linhas, cada linha contém a soma dos segundos de cada ocorrência do usuário
            $oDBase2->query("SELECT SUM(TIME_TO_SEC(jorndif)) AS segundos, oco FROM ponto" . $pm['comp'] . " WHERE siape = :siape AND oco IN (:recesso_debito,:recesso_credito) GROUP BY oco",
            array(
                array(':siape', $siape, PDO::PARAM_STR),
                array(':recesso_debito', $codigoDebitoRecessoPadrao, PDO::PARAM_STR),
                array(':recesso_credito', $codigoCreditoRecessoPadrao, PDO::PARAM_STR)
            ));

            // Nas variáveis declaradas abaixo, em $tempo->positivo vai entrar os segundos da OCO 02424 e em $tempo->negativo vai entrar em OCO 002323
            $tempo->recpos = 0;
            $tempo->recneg = 0;

            // Faço o loop nas 3 linhas retornadas na query
            while ($row = $oDBase2->fetch_object())
            {
                //	// se OCO é 02424, jogo os segundos em $tempo->positivo
                if ($row->oco == $codigoCreditoRecessoPadrao) //'02424')
                {
                    $tempo->recpos = $row->segundos;
                }
                // senão vou somando 02323 em $tempo->negativo
                elseif ($row->oco == $codigoDebitoRecessoPadrao) //'02323')
                {
                    $tempo->recneg = $row->segundos;
                }
            }

            // Verifico qual variável tem o maior valor e faço o cálculo. O resultado é em segundos.
            //calculo de horas de recesso
            $segundosr = ($tempo->recpos > $tempo->recneg ? ($tempo->recpos - $tempo->recneg) : ($tempo->recneg - $tempo->recpos));

            if ($tempo->recpos != $tempo->recneg)
            {
                $codr = ($tempo->recpos > $tempo->recneg ? $codigoCreditoRecessoPadrao : $codigoDebitoRecessoPadrao); //"02424" : "02323");
            }
            else
            {
                $codr = $codigoRegistroParcialPadrao; //"00000";
            }

            //preparando para exibir horas comuns
            $horasr    = floor($segundosr / 3600);
            $segundosr -= $horasr * 3600;
            $minutosr  = floor($segundosr / 60);
            $segundosr -= $minutosr * 60;
            $totalr    = sprintf("%02s:%02s", $horasr, $minutosr);

            // FIM DO CALCULO DOS TOTAIS
            ?>
            <tr>
                <td width="325">
                    <div align="center">
                        <font size='2'><strong> <?= tratarHTML($pm['comp']) . ' - ' . tratarHTML($pm['codigo']) . ' - ' . tratarHTML($pm['horas']); ?></strong></font>
                    </div>
                </td>
                <td width='325'>
                    <div align='center'>
                        <font size='2'><strong><?= tratarHTML($codr) . ' - ' . tratarHTML($totalr); ?></strong></font>
                    </div>
                </td>
            </tr>
            <?php
        }
        ?>
    </table>
    <p align='center'>
        <font size="2">
        Essa tela destina-se &agrave; corre&ccedil;&atilde;o de erros de processamento do c&aacute;lculo de compensa&ccedil;&atilde;o de recesso.<br>
        Para essa corre&ccedil;&atilde;o o usu&aacute;rio dever&aacute; identificar no quadro comparativo acima <br>
        a existência de diverg&ecirc;ncia entre os totais de horas registradas para cada compet&ecirc;ncia na duas tabelas, <br>
        caso exista dever&aacute; informar o mes e o ano que apresenta diverg&ecirc;ncia no campo apropriado clicando OK para processar.
        </font>
    </p>
</form>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

