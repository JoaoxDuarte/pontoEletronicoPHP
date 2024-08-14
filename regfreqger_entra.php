<?php
include_once( "config.php" );

verifica_permissao("logado");

// dados em sessão
$sMatricula = $_SESSION["sMatricula"];
$magico     = $_SESSION["magico"];
$upag       = $_SESSION["upag"];
$lotacao    = $_SESSION["sLotacao"];

$bRecursosHumanos   = ($_SESSION['sRH'] == "S");
$bRecursosHumanosSR = ($_SESSION['sRH'] == "S" && substr($_SESSION['sLotacao'], 2, 6) == '150000');
$bDiretoria         = ($_SESSION["sCAD"] == "S");
$bGestoresSISREF    = ($_SESSION["sSenhaI"] == "S");
$bAuditoria         = ($_SESSION['sAudCons'] == 'S' || $_SESSION["sLog"] == "S");
$bSuperintendente   = ($_SESSION['sAPS'] == 'S' && substr($_SESSION['sLotacao'], 2, 6) == '150000');
$bGerenteExecutivo  = ($_SESSION['sAPS'] == 'S' && substr($_SESSION['sLotacao'], 2, 1) == '0' && substr($_SESSION['sLotacao'], 5, 3) == '000');

if ($magico < "3" && $bRecursosHumanos == false && $bDiretoria == false && $bGestoresSISREF == false && $bAuditoria == false && $bSuperintendente == false && $bGerenteExecutivo == false)
{
    header("Location: acessonegado.php");
    exit();
}

// pega o nome do arquivo origem
$pagina_de_origem = pagina_de_origem();

// instancia o banco de dados
$oDBase = new DataBase('PDO');


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Frequência » Gerencial');
$oForm->setCSS(_DIR_CSS_ . 'estiloIE.css');
$oForm->setLargura("950px");
$oForm->setSeparador(10);

$oForm->setSubTitulo("Frequência das Unidades Subordinadas");

$oForm->setObservacaoTopo("Informe a Unidade que deseja acompanhar");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


// pega o código da superintendencia
$oDBase->query("SELECT regiao FROM tabsetor WHERE codigo = '$lotacao' ");
$reg = $oDBase->fetch_object()->regiao;

// página de destino
$destino = ($magico == '3' || $bDiretoria == true || $bGestoresSISREF == true ? "regfreqgex.php?var=1" : "regfreqsup.php?orig=1");

// inicia o formulário
?>
<form method='post' action='<?= $destino; ?>' id="form1" name='form1'>
    <input type='hidden' id='cmd' name='cmd' value='1'>
    <input type='hidden' id='orig' name='orig' value='1'>

    <p align='center'>
        <h3>
        <div align='center'>
            <p>
                <font size=1>
                    <select name='qlotacao' id='qlotacao'>
                    <?php
                    if ($bDiretoria == true || $bGestoresSISREF == true)
                    {
                        $sql = "SELECT codigo, descricao FROM tabsetor WHERE ativo='S' AND codigo NOT IN ('00000000000000','01001') ORDER BY codigo ";
                    }
                    elseif ($magico == '4' || $bRecursosHumanosSR == true || $bSuperintendente == true)
                    {
                        $sql = "SELECT codigo, descricao FROM tabsetor WHERE regiao = '$reg' and codigo != '$lotacao' AND ativo='S' AND codigo NOT IN ('00000000000000','01001') ORDER BY codigo ";
                    }
                    elseif ($magico == '3' || $bRecursosHumanos == true || $bGerenteExecutivo == true)
                    {
                        $sql = "SELECT codigo, descricao FROM tabsetor WHERE upag = '$upag' and codigo != '$lotacao' AND ativo='S' AND codigo NOT IN ('00000000000000','01001') ORDER BY codigo ";
                    }

                    $oDBase->query($sql);
                    while ($campo = $oDBase->fetch_object())
                    {
                        if (substr_count($campo->descricao, 'GERENCIA EXECUTIVA ') > 0)
                        {
                            ?>
                            <option value='' disabled style='font-weight: bold; background: #f2f2f2;'><?= tratarHTML($campo->descricao); ?></option>
                            <?php
                        }
                        ?>
                        <option value='<?= tratarHTML($campo->codigo); ?>'><?= tratarHTML($campo->codigo) . " - " . tratarHTML($campo->descricao); ?></option>
                        <?php
                    }
                    ?>
                    </select>
                </font>
            </p>
        </div>
    </p>

    <p align='center' style='word-spacing: 0; margin: 0'><br><br><input type='image' border='0' src='<?= _DIR_IMAGEM_; ?>ok.gif' name='enviar' alt='Submeter os valores' align='center'></p>
</form>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
