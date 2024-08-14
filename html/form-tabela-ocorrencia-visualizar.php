<?php

set_time_limit(0);

// Inicializa a sessão (session_start)
// funcoes de uso geral
include_once( "../functions.php" );
include_once( "../class_database.php" );

// instancia o banco de dados
$oDBase   = new DataBase('PDO');

?>
<table class='table table-striped table-condensed table-bordered text-center'>
    <thead>
        <tr bgcolor="#DBDBB7">
            <td width="6%"  align='center'><b>SEQ.</b></td>
            <td align='center'><b>A&Ccedil;&Otilde;ES</b></td>
            <th width="6%"  align='center' nowrap>&nbsp;<b>C&Oacute;DIGO</b>&nbsp;</th>
            <th width="48%" align='left'   nowrap>&nbsp;<b>DESCRI&Ccedil;&Atilde;O<b>&nbsp;</th>
            <th width="7%"  align="center" nowrap>&nbsp;<b>RESPONS&Aacute;VEL<b>&nbsp;</th>
            <th width="65px" align='center' nowrap>&nbsp;<b>ATIVO<b>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?php

        $pesquisa = "SELECT * FROM tabocfre WHERE siapecad <> '-----' AND ativo = 'S' ORDER BY desc_ocorr ";

        $responsavel       = array();
        $responsavel['AB'] = "RH / Chefia";
        $responsavel['RH'] = "Recurso Humanos";
        $responsavel['CH'] = "Chefia";
        $responsavel['SI'] = "SISREF";

        $oDBase->query($pesquisa);

        $sequencia             = 0;
        $registros_processados = 0;
        $numero_de_servidores  = $oDBase->num_rows();

        $var1 = (isset($var1) ? $var1 : "");
        $var2 = (isset($var2) ? $var2 : "");

        while ($pm = $oDBase->fetch_object())
        {
            $sequencia++;
            ?>
            <tr height='18'>
                <td align='center'><?= tratarHTML($sequencia); ?></td>
                <td align='center' nowrap>&nbsp;<a href="javascript:verDialogMensagens('tabela-ocorrencias-detalhes','html/form-tabela-ocorrencia-visualizar-detalhe.php?siapecad=<?= tratarHTML($pm->siapecad); ?>&escolha=<?= $var2; ?>&chave=<?= $var1; ?>&modal=sim');">Ver Detalhe</a>&nbsp;</td>
                <td align='center'><?= tratarHTML($pm->siapecad); ?></td>
                <td align='left'>&nbsp;<?= ($pm->desc_ocorr == '' ? '' : tratarHTML(preparaTextoExibir($pm->desc_ocorr))); ?></td>
                <td align='left'>&nbsp;<?= tratarHTML($responsavel[$pm->resp]); ?></td>
                <td align='center'><?= tratarHTML($pm->ativo); ?></td>
            </tr>
            <?php
        } // fim do while

        ?>
    </tbody>
</table>
<?php

function preparaTextoExibir($texto)
{
    return utf8_encode(strtr($texto, array("'" => "`", '"' => "`")));
}
