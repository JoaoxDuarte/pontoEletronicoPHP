<?php

/*
  Essa rotina deve ser rodada todo dia 30 de cada mes impreterivelmente as 23:00 para que na abertura do mes as ocorrências já estejam incluídas.
 */

// conexao ao banco de dados funcoes diversas
include_once( "config.php" );

verifica_permissao('administrador');

// instancia BD
$oDBase = new DataBase('PDO');


$ano = date(Y);

if (date(n) == "12")
{
    $comp = "01";
    $year = $ano + 1;
}

if ((date(n) >= "1") && (date(n) <= "8"))
{
    $comp = "0" . (date(n) + 1);
    $year = $ano;
}

if (date(n) >= "9" && (date(n) < "12"))
{
    $comp = (date(n) + 1);
    $year = $ano;
}

$mes = $comp . $year;
$ini = $year . "-" . $comp . "-" . '01';
$fim = date("Y-m-t", mktime(0, 0, 0, $comp, 1, $year));

$oDBase->query("
	SELECT
		a.mat_siapecad, a.cod_oco, a.dt_inicio_oco, a.da_fim_real_oco, b.siape, b.id_origem
	FROM
		vw_siapecad_ocorrencias_servidor AS a
	LEFT JOIN
		cadatual AS b ON a.mat_siapecad = b.id_origem
	WHERE
		da_fim_real_oco >= '$ini'
	");

echo "Aguarde... extraindo registros<hr>";

while ($pm_partners = $oDBase->fetch_array())
{
    $siapecad = $pm_partners['mat_siapecad'];
    $oco      = $pm_partners['cod_oco'];
    $inicio   = $pm_partners['dt_inicio_oco'];
    $final    = $pm_partners['da_fim_real_oco'];
    $siape    = $pm_partners['siape'];

    if ($siapecad != '00000000')
    {
        $oDBase->setMensagem("A inclusão falhou");

        if (($inicio < $ini && $final == "2500-12-31") || ($inicio < $ini && $final >= $fim))
        {
            for ($dia = $ini; $dia <= $fim; $dia++)
            {
                $oDBase->query("INSERT INTO ponto$mes (dia, siape, oco) VALUES ('$dia', '$siape','$oco') ");
                echo "Inserindo ocorrencia $oco do servidor  $siape <br><br>";
            }
        }
        else if ($inicio < $ini && $final < $fim)
        {
            for ($dia = $ini; $dia <= $final; $dia++)
            {
                $oDBase->query("INSERT INTO ponto$mes (dia, siape, oco) VALUES ('$dia', '$siape','$oco') ");
                echo "Inserindo ocorrencia $oco do servidor  $siape <br><br>";
            }
        }
        /* if(($inicio > $ini && $final == "2500-12-31" ) or ($inicio > $ini && $final >= $fim ))
          {
          for($dia=$inicio; $dia<=$fim; $dia++)
          {
          $query = "INSERT INTO ponto$mes (dia, siape, oco) VALUES('$dia', '$siape','$oco')";
          $result = mysql_query($query) or die("A inclusão falhou".mysql_error());
          echo "Inserindo ocorrencia $oco do servidor  $siape <br><br>";
          }
          }
          elseif($inicio > $ini && $final < $fim )
          {
          for($dia=$inicio; $dia<=$final; $dia++)
          {
          $query = "INSERT INTO ponto$mes (dia, siape, oco) VALUES('$dia', '$siape','$oco')";
          $result = mysql_query($query) or die("A inclusão falhou".mysql_error());
          echo "Inserindo ocorrencia $oco do servidor  $siape <br><br>";
          }
          } */
    }
}

echo "<b>Processo finalizado...</b><br>";
