<?php

// Inicializa a sessão (session_start)
// funcoes de uso geral
include_once( "../functions.php" );
include_once( "../class_database.php" );

// instancia o banco de dados
$oDBase = new DataBase('PDO');

// parametro formulario
$siapecad = $_REQUEST['siapecad'];

// pesquisa/seleção
$oDBase->query("SELECT * FROM tabocfre WHERE siapecad = '" . $siapecad . "' ");
$oDados = $oDBase->fetch_object();

$responsavel       = array();
$responsavel['AB'] = "RH / Chefia";
$responsavel['RH'] = "Recurso Humanos";
$responsavel['CH'] = "Chefia";
$responsavel['SI'] = "SISREF";

?>
<table class='table table-striped table-condensed text-center'>
    <tr>
        <td>
            <label>C&Oacute;DIGO</label>
            <input name="siapecad" type="text" value="<?= tratarHTML($oDados->siapecad); ?>" size="10"  class="form-control" readonly>
        </td>
        <td>
            <label>DESCRI&Ccedil;&Atilde;O DA OCORR&Ecirc;NCIA</label>
            <input name="sDescricao" type="text" value="<?= tratarHTML(preparaTextoExibir($oDados->desc_ocorr)); ?>" size="70"  class="form-control" readonly>
        </td>
        <td>
            <label>RESPONS&Aacute;VEL</label>
            <input name="resp" type="text" value="<?= tratarHTML($responsavel[$oDados->resp]); ?>" size="15"  class="form-control" readonly>
        </td>
        <td>
            <label>ATIVO</label>
            <input name="sAtivo" type="text" value="<?= ($oDados->ativo == 'S' ? 'Sim' : 'Não'); ?>" size="3"  class="form-control" readonly>
        </td>
    </tr>
</table>
<table class='table table-striped table-condensed text-center'>
    <tr>
        <td>
            <label>APLICA&Ccedil;&Atilde;O</label>
            <textarea name=aplic cols=40 rows=10 class="form-control" disabled><?= tratarHTML(preparaTextoExibir($oDados->aplic)); ?></textarea>
        </td>
        <td>
            <label>IMPLICA&Ccedil;&Atilde;O</label>
            <textarea name=implic cols=40 rows=10 class="form-control" disabled><?= tratarHTML(preparaTextoExibir($oDados->implic)); ?></textarea>
        </td>
        <td>
            <label>PRAZOS</label>
            <textarea name=prazo cols=30 rows=10 class="form-control" disabled><?= tratarHTML(preparaTextoExibir($oDados->prazo)); ?> </textarea>
        </td>
        <td>
            <label>FUNDAMENTO LEGAL</label>
            <textarea name=flegal cols=30 rows=10 class="form-control" disabled><?= tratarHTML(preparaTextoExibir($oDados->flegal)); ?></textarea>
        </td>
    </tr>
</table>
<?php

function preparaTextoExibir($texto)
{
    return utf8_encode(strtr($texto, array("'" => "`", '"' => "`")));

}
