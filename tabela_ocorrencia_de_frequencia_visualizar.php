<?php
set_time_limit(0);

// Inicializa a sessão (session_start)
// funcoes de uso geral
include_once( "config.php" );
include_once( _DIR_INC_ . "PogProgressBar.php" ); // Localização inc/
// Verifica se o usuário tem a permissão para acessar este módulo
verifica_permissao("tabela_feriados");

// instancia o banco de dados
$oDBase = new DataBase('PDO');

//Barra de progresso
/*$objBar = new PogProgressBar('pb0');
$objBar->setTheme('blue');
$objBar->draw('', '1px', '30%');*/


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
//$oForm->setFlexSelect();
$oForm->setCaminho('Tabelas » Ocorrências » Consultar / Alterar');
$oForm->setSubTitulo("Pesquisa de C&oacute;digos de Ocorr&ecirc;ncias");

// ordena tabela
$oForm->setCSS(_DIR_CSS_ . "table_sorter.css");
$oForm->setJS(_DIR_JS_ . "jquery.tablesorter.js");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<script>
    $(document).ready(function ()
    {
        //assign the sortStart event
        $("#AutoNumber1").tablesorter(); // call the tablesorter plugin, the magic happens in the markup
    });
</script>
<form method="POST" action="tabela_ocorrencia_de_frequencia_visualizar.php" id="form1" name="form1">
    <input type="hidden" name="modo" value="<?= tratarHTML($modo); ?>" >
    <input type="hidden" name="corp" value="<?= tratarHTML($corp); ?>">
    <table border="0" width="100%" cellspacing="0" cellpadding="0" bordercolordark="white" bordercolorlight="#336699">
        <tr>
            <td class="corpo" width="100%" colspan="3"> <p align="center" style="word-spacing: 0; margin: 0"><font face="Tahoma" size="1">
                    <input type="radio" value="siapecad" checked name="escolha" onclick="document.all['chave'].focus()">
                    Por Código Siapecad
                    <input type="radio" name="escolha" value="descricao" onclick="document.all['chave'].focus()">
                    Por descricao
                    <input type="radio" name="escolha" value="sirh" onclick="document.all['chave'].focus()">
                    Por C&oacute;digo Sirh
                    <input type="radio" name="escolha" value="siape" onclick="document.all['chave'].focus()">
                    Por C&oacute;digo Siape</font>
            </td>
        </tr>
        <tr>
            <td width="29%"><p style="word-spacing: 0; margin: 0"></td>
            <td width="37%"><p style="word-spacing: 0; margin: 0">&nbsp;</td>
            <td width="34%"><p style="word-spacing: 0; margin: 0"></td>
        </tr>
        <tr>
            <td width="29%"><p style="word-spacing: 0; margin: 0"></td>
            <td class="corpo" width="37%">
                <p align="center" style="word-spacing: 0; margin: 0"><font size="1" face="Tahoma">Chave </font><input type="text" class="caixa" name="chave" title="Não informe pontos" size="28">
            </td>
            <td width="34%"><p style="word-spacing: 0; margin: 0"></td>
        </tr>
    </table>
    <p align="center" style="word-spacing: 0; line-height: 100%; margin-left: 0; margin-right: 0; margin-top: 6; margin-bottom: 0">
        <input type="image" border="0" src="<?= _DIR_IMAGEM_; ?>ok.gif" name="enviar" alt="Submeter os valores" align="center" >
    </p>
</form>
<input type="button" class="btn btn-default btn_adicionar" style="padding-left:30px;padding-right:30px;" value="Adicionar" onClick="document.location.href='tabela_ocorrencia_de_frequencia_incluir.php'" />
<table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#F1F1E2" width="100%" id="AutoNumber1" class="tablesorter">
    <thead>
        <tr bgcolor="#DBDBB7">
            <td width="6%"  align='center'><b>SEQ.</b></td>
            <td colspan="2" align='center'><b>AÇÕES</b></td>
            <th width="6%"  align='center' nowrap>&nbsp;<b>SIAPECAD</b>&nbsp;</th>
            <th width="48%" align='left'   nowrap>&nbsp;<b>DESCRIÇÃO<b>&nbsp;</th>
            <th width="7%"  align="center" nowrap>&nbsp;<b>RESPONSAVEL<b>&nbsp;</th>
            <th width="55px"  align="center" nowrap>&nbsp;<b>SIGLA<b>&nbsp;</th>
            <th width="65px" align='center' nowrap>&nbsp;<b>ATIVO<b>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?php

        $var1 = urldecode($_REQUEST["chave"]);
        $var2 = ($var1 == "" ? "" : urldecode($_REQUEST["escolha"]));

        switch ($var2)
        {
            case "siapecad":
                $pesquisa = "SELECT * FROM tabocfre WHERE siapecad <> '-----' AND siapecad = '$var1' AND ativo = 'S' ORDER BY desc_ocorr ";
                break;

            case "descricao":
                $pesquisa = "SELECT * FROM tabocfre WHERE siapecad <> '-----' AND desc_ocorr LIKE '%$var1%' AND ativo = 'S' ORDER BY desc_ocorr ";
                break;

            case "sirh":
                $pesquisa = "SELECT * FROM tabocfre WHERE siapecad <> '-----' AND cod_ocorr LIKE '%$var1%' AND ativo = 'S' ORDER BY desc_ocorr ";
                break;

            case "siape":
                $pesquisa = "SELECT * FROM tabocfre WHERE siapecad <> '-----' AND cod_siape LIKE '%$var1%' AND ativo = 'S' ORDER BY desc_ocorr ";
                break;

            default:
                $pesquisa = "SELECT * FROM tabocfre WHERE siapecad <> '-----' AND ativo = 'S' ORDER BY desc_ocorr ";
                break;
        }

        $responsavel       = array();
        $responsavel['AB'] = "RH / Chefia";
        $responsavel['RH'] = "Recurso Humanos";
        $responsavel['CH'] = "Chefia";
        $responsavel['SI'] = "SISREF";

        $oDBase->query($pesquisa);

        $sequencia             = 0;
        $registros_processados = 0;
        $numero_de_servidores  = $oDBase->num_rows();

        while ($pm = $oDBase->fetch_object())
        {
            $sequencia++;

            ?>
            <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)' height='18'>
                <td align='center'><?= $sequencia; ?></td>

                <?php if($_SESSION['sRH'] == "S" || $_SESSION['sAPS'] == "S"): ?>

                <td align='center'>
                    <?php

                    if ($_SESSION["sSenhaI"] == "S")
                    {
                        ?>
                        <a href="tabela_ocorrencia_de_frequencia_alterar.php?siapecad=<?= tratarHTML($pm->siapecad); ?>">Alterar</a>
                        <?php
                    }
                    else
                    {
                        ?>
                        <a href="#" disabled>Alterar</a>
                        <?php
                    }

                    ?>
                </td>

                <?php endif; ?>

                <td align='center' nowrap>&nbsp;<a href="tabela_ocorrencia_de_frequencia_visualizar_detalhe.php?siapecad=<?= tratarHTML($pm->siapecad); ?>&escolha=<?= $var2; ?>&chave=<?= $var1; ?>">Ver Detalhe</a>&nbsp;</td>
                <td align='center'><?= tratarHTML($pm->siapecad); ?></td>
                <td align='left' nowrap>&nbsp;<?= tratarHTML(($pm->desc_ocorr == '' ? '' : trata_aspas($pm->desc_ocorr))); ?></td>
                <td align='left' nowrap>&nbsp;<?= tratarHTML($responsavel[$pm->resp]); ?></td>
                <td align='center'><?= tratarHTML($pm->cod_ocorr); ?></td>
                <td align='center'><?= tratarHTML($pm->ativo); ?></td>
            </tr>
            <?php

            $registros_processados++;
        } // fim do while

        ?>
    </tbody>
</table>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
