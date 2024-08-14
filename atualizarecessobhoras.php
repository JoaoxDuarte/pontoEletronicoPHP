<?php

include_once("config.php");
include_once( "class_ocorrencias_grupos.php" );

verifica_permissao("sLog");

$mes   = anti_injection($_REQUEST['mes']);
$ano   = anti_injection($_REQUEST['ano']);
$siape = anti_injection($_REQUEST['siape']);

$comp = $mes . $ano;


$oDBase = selecionaServidor( $siape );
$sitcad = $oDBase->fetch_object()->sigregjur;

// Grupos de ocorr�ncias
$obj = new OcorrenciasGrupos();

$codigoCreditoRecessoPadrao  = $obj->CodigoCreditoRecessoPadrao($sitcad, $exige_horarios=true)[0];
$codigoDebitoRecessoPadrao   = $obj->CodigoDebitoRecessoPadrao($sitcad)[0];
$codigoRegistroParcialPadrao = $obj->CodigoRegistroParcialPadrao($sitcad)[0];


// instancia BD
$oDBase  = new DataBase('PDO');
$oDBase2 = new DataBase('PDO');

$oDBase->query("SELECT mat_siape, jornada FROM servativ WHERE mat_siape = :mat_siape ",array(
    array(":mat_siape", $siape, PDO::PARAM_STR)));


## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setOnLoad("javascript: if($('#mes')) { $('#mes').focus() };");

$oForm->setSubTitulo("Recalcular Compensa��o de Recesso");

// Topo do formul�rio
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
    var comp = $('#comp');

    if (comp.val().length < 6)
    {
        oTeste.setMsg('Competencia inv�lida!', comp);
    }

    // se houve erro ser�(�o) exibida(s) a(s) mensagem(ens) de erro
    var bResultado = oTeste.show();

    return bResultado;
}
</script>
<?php

echo "Aguarde... extraindo registros<hr>";

while ($pm_partners = $oDBase->fetch_array())
{
    $siape = $pm_partners['mat_siape'];

    //ROTINA DE TOTALIZA��O DAS HORAS
    // Essa query me retorna 3 linhas, cada linha cont�m a soma dos segundos de cada ocorr�ncia do usu�rio
    $oDBase2->query("SELECT SUM(TIME_TO_SEC(jorndif)) AS segundos, oco FROM ponto" . $comp . " WHERE siape = :siape AND (oco = :recesso_debito OR oco = :recesso_credito) GROUP BY oco",
    array(
        array(':siape', $siape, PDO::PARAM_STR),
        array(':recesso_debito', $codigoDebitoRecessoPadrao, PDO::PARAM_STR),
        array(':recesso_credito', $codigoCreditoRecessoPadrao, PDO::PARAM_STR)
    ));
    $teste = $oDBase2->num_rows();

    // Nas vari�veis declaradas abaixo, em $tempo->positivo vai entrar os segundos da OCO 02424 e em $tempo->negativo vai entrar a soma da OCO 02323
    $tempo->positivo = 0;
    $tempo->negativo = 0;

    // Fa�o o loop nas 3 linhas retornadas na query
    if ($teste > '0')
    {
        while ($row = $oDBase2->fetch_object())
        {
            // se OCO � 02424, jogo os segundos em $tempo->positivo
            if ($row->oco == $codigoCreditoRecessoPadrao) //'02424')
            {
                $tempo->positivo = $row->segundos;
            }
            // sen�o vou somando as outras OCO em $tempo->negativo
            else if ($row->oco == $codigoDebitoRecessoPadrao) //'02323')
            {
                $tempo->negativo = $row->segundos;
            }
        }
    }

    // Verifico qual vari�vel tem o maior valor e fa�o o c�lculo. O resultado � em segundos.
    //calculo de horas comuns
    $segundos = ($tempo->positivo > $tempo->negativo ? ($tempo->positivo - $tempo->negativo) : ($tempo->negativo - $tempo->positivo));

    if ($tempo->positivo != $tempo->negativo)
    {
        $cod = (($tempo->positivo > $tempo->negativo) ? $codigoCreditoRecessoPadrao : $codigoDebitoRecessoPadrao); //"02424" : "02323");
        $t1  = "1";
    }
    else
    {
        $cod = $codigoRegistroParcialPadrao; //"00000";
        $t1  = "0";
    }

    //preparando para exibir horas comuns
    $horas     = floor($segundos / 3600);
    $segundos -= $horas * 3600;
    $minutos   = floor($segundos / 60);
    $segundos -= $minutos * 60;
    $total     = sprintf("%02s:%02s", $horas, $minutos);

    $oDBase2->query("SELECT dest FROM bhoras WHERE siape = :siape AND comp = :comp AND dest = '2' ",
    array(
        array(':siape', $siape, PDO::PARAM_STR),
        array(':comp',  $comp,  PDO::PARAM_STR)
    ));
    $linha = $oDBase2->num_rows();

    if ($linha != "0" && $t1 == "1")
    {
        $oDBase2->query("UPDATE bhoras SET horas = :horas, codigo = :codigo WHERE siape = :siape AND comp = :comp AND dest = '2' ",
        array(
            array(':horas',  $total, PDO::PARAM_STR),
            array(':codigo', $cod,   PDO::PARAM_STR),
            array(':siape',  $siape, PDO::PARAM_STR),
            array(':comp',   $comp,  PDO::PARAM_STR)
        ));
    }
    else if ($linha == "0" && $t1 == "1")
    {
        $oDBase2->query("INSERT INTO bhoras (comp, horas, codigo, siape, dest ) VALUES (:comp, :horas, :codigo, :siape, '2') ",
        array(
            array(':horas',  $total, PDO::PARAM_STR),
            array(':codigo', $cod,   PDO::PARAM_STR),
            array(':siape',  $siape, PDO::PARAM_STR),
            array(':comp',   $comp,  PDO::PARAM_STR)
        ));
    }
    else
    {
        $oDBase2->query("DELETE FROM bhoras WHERE siape = :siape AND comp = :comp AND dest = '2' ",
        array(
            array(':siape',  $siape, PDO::PARAM_STR),
            array(':comp',   $comp,  PDO::PARAM_STR)
        ));
    }
    // FIM DO CALCULO DOS TOTAIS */
}

echo "<b>Atualiza��o da compensa��o do recesso Finalizada...</b><br>";

// Base do formul�rio
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
