<?php
include_once( "config.php" );

session_start();

// Valores passados - encriptados
$dadosorigem = anti_injection($_REQUEST['dados']);

if (empty($dadosorigem))
{
    $mes = anti_injection($_REQUEST["mes"]);
    $ano = anti_injection($_REQUEST["ano"]);
    $lot = anti_injection($_REQUEST["lot"]);
    $cmd = anti_injection($_REQUEST["cmd"]);
}
else
{
    $dados = explode(":|:", base64_decode($dadosorigem));
    $cmd   = $dados[0];
    $orig  = $dados[1];
    $lot   = $dados[2];
    $dia   = $dados[3];
    $mes   = dataMes($dia);
    $ano   = dataAno($dia);
}

if (($_SESSION['sRH'] == "S" && substr($_SESSION['sLotacao'], 5, 1) == '7') || ($_SESSION['sAPS'] == 'S' && substr($_SESSION['sLotacao'], 2, 6) == '150000'))
{
    $cmd = 4;
}

switch ($cmd)
{
    case '3':
        $destino = "regfreqgex.php?orig=2";
        break;
    case '4':
        $destino = "regfreqsup.php?orig=1";
        break;
    default:
        $destino = "frequencia_acompanhar_registros.php";
        break;
}


## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho('Frequência » Acompanhar » Dias passados' );
$oForm->setCSS( 'css/estilos.css' );
$oForm->setCSS( 'css/smoothness/jquery-ui-custom-px.min.css' );
$oForm->setJS( 'js/phpjs.js' );
$oForm->setLargura('800px' );

$oForm->setSeparador(20);

$oForm->setObservacaoTopo('Informe o dia passado que deseja acompanhar');

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<script>
    function verificadados()
    {
        // objeto mensagem
        oTeste = new alertaErro();
        oTeste.init();

        // dados
        var destino = $('#destino').val();
        var dia     = $('#dia');

        // valida o dia
        if (dia.val().length == 0)
        {
            oTeste.setMsg('Selecione a data que deseja acompanhar no passado!', dia);
        }
        else if (destino == "frequencia_acompanhar_registros.php")
        {
            var cmd      = $('#cmd').val();
            var orig     = $('#orig').val();
            var dia      = $('#dia').val();
            var qlotacao = $('#qlotacao').val();

            var parametros = base64_encode(cmd + ":|:" + orig + ":|:" + qlotacao + ":|:" + dia);
            $('#dados').val( parametros );
            var destino = "javascript:window.location.replace('" + destino + "?dados=" + parametros + "');";

            $("#form1").attr("action", destino);
            $('#form1').submit();
        }
        return true;
    }
</script>

<form method="POST" action="<?= tratarHTML($destino); ?>" onsubmit="return verificadados()" id="form1" name="form1">
    <p align="center"><h3>
        <div align="center">
            <p>
                <strong>
                    <font size="2" face="Tahoma">
                    <input type='hidden' name='qlotacao' id="qlotacao" value='<?= tratarHTML($lot); ?>'>
                    <input type='hidden' name='cmd'      id="cmd"      value='<?= tratarHTML($cmd); ?>'>
                    <input type='hidden' name='orig'     id="orig"     value='<?= tratarHTML($orig); ?>'>
                    <input type='hidden' name='dados'    id="dados"    value=''>
                    <input type='hidden' name='destino'  id="destino"  value='<?= tratarHTML($destino); ?>'>
                    </font>
                </strong>
            </p>
            <p>&nbsp;</p>
            <p>
                <font size=1>
                <select name='dia'  size="1" class="drop" id="dia" >
                    <?php
                    $dia1 = 1;
                    $dia2 = date(d);

                    for ($dia = $dia1; $dia <= $dia2; $dia++)
                    {
                        echo "<option value=" . tratarHTML(substr('0' . $dia, -2)) . "/".tratarHTML($mes)."/".tratarHTML($ano).">" . tratarHTML(substr('0' . $dia, -2)) . "/".tratarHTML($mes)."/".tratarHTML($ano)."</option>";
                    }
                    ?>
                </select>
                </font>
            </p>
        </div>
        <p align="center" style="word-spacing: 0; margin-top: 0; margin-bottom: 0">&nbsp;</p>
        <p align="center" style="word-spacing: 0; margin: 0">
            <input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>ok.gif" name="enviar" alt="Submeter os valores" align="center" >
        </p>
</form>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
