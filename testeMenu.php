<?php

include_once( "config.php" );
include_once( "class_ocorrencias_grupos.php" );
//include_once( "html/menu_app.php" );
include_once( "src/controllers/TabServativController.php" );
include_once( "src/controllers/TabSetoresController.php" );

echo php_uname() . '<br>';
echo PHP_OS . '<br>';

$data2 = array();
array_push($data2, [ "two" => 2 ]);
array_push($data2, [ "three" => 3 ]);
array_push($data2, [ "four" => 4 ]);
       
print '<pre>';
var_dump($data2);
print '</pre>';

$data['one'] = 1;
       $data += array( "two" => 2 );
       $data += [ "three" => 3 ];
       $data += [ "four" => 4 ];
       
print '<pre>';
var_dump($data);
print '</pre>';

die();

?>
<style>
.highcharts-figure, .highcharts-data-table table {
    min-width: 320px; 
    max-width: 660px;
    margin: 1em auto;
}

.highcharts-data-table table {
	font-family: Verdana, sans-serif;
	border-collapse: collapse;
	border: 1px solid #EBEBEB;
	margin: 10px auto;
	text-align: center;
	width: 100%;
	max-width: 500px;
}
.highcharts-data-table caption {
    padding: 1em 0;
    font-size: 1.2em;
    color: #555;
}
.highcharts-data-table th {
	font-weight: 600;
    padding: 0.5em;
}
.highcharts-data-table td, .highcharts-data-table th, .highcharts-data-table caption {
    padding: 0.5em;
}
.highcharts-data-table thead tr, .highcharts-data-table tr:nth-child(even) {
    background: #f8f8f8;
}
.highcharts-data-table tr:hover {
    background: #f1f7ff;
}
</style>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<script>
// Radialize the colors
Highcharts.setOptions({
  colors: Highcharts.map(Highcharts.getOptions().colors, function(color) {
    return {
      radialGradient: {
        cx: 0.5,
        cy: 0.3,
        r: 0.7
      },
      stops: [
        [0, color],
        [1, Highcharts.color(color).brighten(-0.3).get('rgb')] // darken
      ]
    };
  })
});

// Build the chart
Highcharts.chart('container', {
  chart: {
    plotBackgroundColor: null,
    plotBorderWidth: null,
    plotShadow: false,
    type: 'pie'
  },
  title: {
    text: 'Browser market shares in January, 2018'
  },
  tooltip: {
    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
  },
  accessibility: {
    point: {
      valueSuffix: '%'
    }
  },
  plotOptions: {
    pie: {
      allowPointSelect: true,
      cursor: 'pointer',
      dataLabels: {
        enabled: true,
        format: '<b>{point.name}</b>: {point.percentage:.1f} %',
        connectorColor: 'silver'
      }
    }
  },
  series: [{
    name: 'Share',
    data: [{
        name: 'Chrome',
        y: 61.41
      },
      {
        name: 'Internet Explorer',
        y: 11.84
      },
      {
        name: 'Firefox',
        y: 10.85
      },
      {
        name: 'Edge',
        y: 4.67
      },
      {
        name: 'Safari',
        y: 4.18
      },
      {
        name: 'Other',
        y: 7.05
      }
    ]
  }]
});
</script>

<figure class="highcharts-figure">
    <div id="container"></div>
    <p class="highcharts-description">
        All color options in Highcharts can be defined as gradients or patterns.
        In this chart, a gradient fill is used for decorative effect in a pie
        chart.
    </p>
</figure>
<?php

exit();

$_SESSION['sModuloPrincipalAcionado'] = 'rh';


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setSubTitulo('');
$oForm->setCSS("css/sigac.css");
$oForm->setJS("js/jquery.mask.min.js");
$oForm->setJS("js/funcoes_valida_cpf_pis.js");
$oForm->setJS('entrada.js');
$oForm->setSeparador(0);

$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


            ?>
            <script>
                var intervalActivity = null;
                
                function attLastActivity() 
                {
                    $.ajax({
                        url: "inc/tempo_sessao_verificar.php",
                        type: "POST",
                        data: 'contar=sim',
                        dataType: "json"

                    }).done(function(resultado) {
                        console.log(resultado.hora + ' | ' + resultado.tipo);
                        $('#tempo_decorrido').html( "<b>Sessao expira em:</b> " + resultado.hora + " (mm:ss)" );
            
                        if (resultado.hora === '00:00')
                        {
                            clearInterval( intervalActivity );
                            mostraMensagem('Sua sessão Expirou!', 'warning', 'login', null);
                            return false;
                        }

                    }).fail(function(jqXHR, textStatus ) {
                        console.log("Request failed: " + textStatus);

                    }).always(function() {
                        console.log("completou");
                    });
                }

                function verificarTempoDeSessao()
                {
                    intervalActivity = setInterval(attLastActivity, 1000); //Faz uma requisição a cada 30 segundos
                }
                
                verificarTempoDeSessao();
                
            </script>
            <?php

## Base do formulário
#
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

die();

// Verifica se jah esgotou o prazo
if ( isset($_SESSION['sessiontime']) )
{
    if ( time() > $_SESSION['sessiontime'] )
    {
        $mensagem = 'Sua sessão Expirou!';
        $destino  = $tela_login_sistema;
    }
}
else // Primeira visita
{ 
    // Define o limite de tempo em segundos
    $_SESSION['sessiontime'] = time() + 60 * getDuracaoDaSessaoEmMinutos();
}

$temporestante = $_SESSION['sessiontime'] - time();
$mensagem      = '<b>Sessão expira em:</b> ' . sec_to_time(abs($temporestante),'mm:ss') . ' (mm:ss)'; 

//echo "As sessões em cache irão expirar em $temporestante segundos";
print getDuracaoDaSessaoEmMinutos() .'<br>';
print $_SESSION['sessiontime'] .'<br>';
print $temporestante .'<br>';
print $mensagem .'<br>';
print json_encode(array("mensagem" => $mensagem, "tipo" => "warning", "destino" => $destino));

die('1');


$mat            = '0155791';
$codOcorrencia  = '00221';
$date_util      = '2020-01-01';
$idreg          = 'W';
$registrado_por = 'WSIAPE';

//updateAfastamentosBySiapeAtualizarOcorrenciaHistorico($mat, $date_util, $registrado_por);
//updateAfastamentosBySiapeAtualizarOcorrencia($mat, $codOcorrencia, $date_util, $idreg, $registrado_por);
//updateAfastamentosBySiapeInsertOcorrencia($mat, $codOcorrencia, $date_util, $idreg, $registrado_por);
updateAfastamentosBySiapeAtualizarOcorrenciaVariosDias($mat, array($date_util), $codOcorrencia, $codOcorrencia, $idreg, $registrador);

die();

listBoxEnum('tabocfre','grupo','grupo','Servidor');


// Dados:
//      $mat                 : Matrícula do servidor;
//      $uteis               : Dia(s);
//      $codOcorrenciaUpdate : Código de ocorrência atualizar;
//      $codOcorrenciaInsert : Código de ocorrência inserir;
//      $idreg               : Registrado por webservice 'W';
//      $registrador         : Matírucla Registrador por webservice 'WSIAPE';
function updateAfastamentosBySiapeAtualizarOcorrenciaVariosDias($mat, $uteis, $codOcorrenciaUpdate, $codOcorrenciaInsert, $idreg, $registrador)
{            
    ## ocorrências grupos
    $obj = new OcorrenciasGrupos();
    $codigoSemFrequenciaPadrao = $obj->CodigoSemFrequenciaPadrao($sitcad);

    $mat = getNovaMatriculaBySiape($mat);

    $oDBase = new DataBase('PDO');

    foreach ($uteis AS $date_util) 
    {
        $bool  = false;
        $table = explode('-', $date_util);
        $table = "ponto" . $table[1] . $table[0];

        // CONSULTA SE O USUARIO POSSUI ALGUMA OCORRENCIA NO DIA CORRESPONDENTE, OU ALGUMA OCORRNCIA DO TIPO 99999 (SEM FREQUNCIA)
        $query = "SELECT oco FROM $table WHERE siape = '" . $mat . "' AND dia = '" . $date_util . "'";
        $oDBase->query($query);
        $result = $oDBase->fetch_assoc();

        $finaldesemana = false;

        // CONDIES
        if (!$result) {
            $bool = true;
        } else if (in_array($result['oco'], $codigoSemFrequenciaPadrao)) { // 99999
            $bool = true;
        }else{
            $bool = true;
            $finaldesemana = true;
        }

        // INSERT OCORRENCIAS
        if ($bool) {

            if ($result['oco'] == '99999' || $result['oco'] == '00000') 
            {
                updateAfastamentosBySiapeAtualizarOcorrenciaHistorico($mat, $date_util, $registrador);
                updateAfastamentosBySiapeAtualizarOcorrencia($mat, $codOcorrenciaUpdate, $date_util, $idreg, $registrador);
            }
            else if (!$finaldesemana && !$bool) 
            {
                updateAfastamentosBySiapeInsertOcorrencia($mat, $codOcorrenciaInsert, $date_util, $idreg, $registrador);
            }
        }
    }
}


/*
 * @info Monta SELECT de campos ENUM de tabelas
 *
 * @param string $tabela    Nome da tabela no banco de dados
 * @param string $campo     Nome do campo ENUM na tabela
 * @param string $valor     Valor para manter selecionado
 * @param string $onchange  Função para manipulação do select
 *
 * @return HTML/echo
 *
 * @author Edinalvo Rosa
 */
function listBoxEnum($tabela="", $campo="", $name="", $valor="", $onchange="")
{
    $grupos = carregaCampoEnum($tabela, $campo);

    ?>
    <SELECT id="<?= $name; ?>" name="<?= $name; ?>" class="form-control select2-single" title="Selecione uma opção!" <?= ($onchange == '' ? '' : 'onChange="' . $onchange . '"'); ?>>
        <?php
        foreach($grupos as $grupo)
        {
            ?>
            <option value="<?= $grupo; ?>" <?= ($grupo == $valor ? 'selected' : ''); ?>>
                <?= $grupo; ?>
            </option>
            <?php
        }
        ?>
    </SELECT>
    <?php
}


/*
 * @info Carrega os dados de campos ENUM
 *
 * @param string $tabela    Nome da tabela no banco de dados
 * @param string $campo     Nome do campo ENUM na tabela
 *
 * @return array
 *
 * @author Edinalvo Rosa
 */
function carregaCampoEnum($tabela='', $campo='')
{
    $oDBase = new DataBase();

    $oDBase->query("DESCRIBE $tabela ");

    while ($grupo_r = $oDBase->fetch_assoc())
    {
        if ($grupo_r['Field'] == $campo)
        {
            $grupos = explode(',',str_replace("'",'',str_replace(')','',str_replace('enum(','',$grupo_r['Type']))));
        }
    }

    return $grupos;
}
