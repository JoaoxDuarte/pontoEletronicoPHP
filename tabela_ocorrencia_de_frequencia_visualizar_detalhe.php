<?php
// Inicializa a sessão (session_start)
// funcoes de uso geral
include_once( "config.php" );

// Verifica se o usuário tem a permissão para acessar este módulo
verifica_permissao("logado");

// instancia o banco de dados
$oDBase = new DataBase('PDO');

// parametro formulario
$siapecad = anti_injection($_REQUEST['siapecad']);
$escolha  = anti_injection($_REQUEST['escolha']);
$chave    = anti_injection($_REQUEST['chave']);
$modal    = anti_injection($_REQUEST['modal']);

// pesquisa/seleção
$oDBase->query("SELECT * FROM tabocfre WHERE siapecad = :siapecad", array(
    array( ':siapecad', $siapecad, PDO::PARAM_STR )
));
$oDados = $oDBase->fetch_object();

$responsavel       = array();
$responsavel['AB'] = "RH / Chefia";
$responsavel['RH'] = "Recurso Humanos";
$responsavel['CH'] = "Chefia";
$responsavel['SI'] = "SISREF";


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Tabelas » Ocorrências » Visualizar Detalhe');
$oForm->setCSS(_DIR_CSS_ . 'estiloIE.css');
$oForm->setSeparador(0);
$oForm->setSubTitulo("Consulta Tabela de Ocorrências");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<style>
    .border { border: 1px solid #e5e5e5; text-align: center; }
</style>
<table class='table table-striped table-condensed text-center'>
    <tr>
        <td>
            <label>CÓDIGO</label>
            <input name="siapecad" type="text" value="<?= tratarHTML($oDados->siapecad); ?>" size="10"  class="form-control" readonly>
        </td>
        <td>
            <label>DESCRIÇÃO DA OCORRÊNCIA</label>
            <input name="sDescricao" type="text" value="<?= tratarHTML(trata_aspas($oDados->desc_ocorr)); ?>" size="70"  class="form-control" readonly>
        </td>
        <td>
            <label>RESPONSÁVEL</label>
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
            <label>SMAP OCORRENCIA</label>
            <input name="smap_ocorrencia" type="text" value="<?= tratarHTML($oDados->smap_ocorrencia); ?>"  class="form-control" readonly>
        </td>
        <td>
            <label>CODIGO OCORRENCIA</label>
            <input name="cod_ocorr" type="text" value="<?= tratarHTML(trata_aspas($oDados->cod_ocorr)); ?>"  class="form-control" readonly>
        </td>
        <td>
            <label>CODIGO SIAPE</label>
            <input name="cod_siape" type="text" value="<?= tratarHTML($oDados->cod_siape); ?>"  class="form-control" readonly>
        </td>
        <td>
            <label>SEMREM</label>
            <input name="semrem" type="text" value="<?= ($oDados->semrem == 'S' ? 'Sim' : 'Não'); ?>" size="3"  class="form-control" readonly>
        </td>
        <td>
            <label>ID SIAPECAD</label>
            <input name="idsiapecad" type="text" value="<?= ($oDados->idsiapecad == 'S' ? 'Sim' : 'Não'); ?>" size="3"  class="form-control" readonly>
        </td>
        <td>
            <label>GRUPO</label>
            <input name="grupo" type="text" value="<?= tratarHTML(trata_aspas($oDados->grupo)); ?>"  class="form-control" readonly>
        </td>
    </tr>
</table>

<table class='table table-striped table-condensed text-center'>
    <tr>
        <td>
            <label>TIPO</label>
            <input name="tipo" type="text" value="<?= tratarHTML($oDados->tipo); ?>"  class="form-control" readonly>
        </td>
        <td>
            <label>SITUAÇÂO</label>
            <input name="situacao" type="text" value="<?= tratarHTML(trata_aspas($oDados->situacao)); ?>"  class="form-control" readonly>
        </td>
        <td>
            <label>JUSTIFICATIVA</label>
            <input name="justificativa" type="text" value="<?= ($oDados->justificativa == 'S' ? 'Sim' : 'Não'); ?>" size="3"  class="form-control" readonly>
        </td>
        <td>
            <label>POST. REC.</label>
            <input name="postergar_pagar_recesso" type="text" value="<?= ($oDados->postergar_pagar_recesso == 'S' ? 'Sim' : 'Não'); ?>" size="3"  class="form-control" readonly>
        </td>
        <td>
            <label>TRATAMENTO DEBITO</label>
            <input name="tratamento_debito" type="text" value="<?= tratarHTML($oDados->tratamento_debito); ?>"  class="form-control" readonly>
        </td>
        <td>
            <label>PADRAO</label>
            <input name="padrao" type="text" value="<?= tratarHTML(trata_aspas($oDados->padrao)); ?>"  class="form-control" readonly>
        </td>
    </tr>
</table>

<table class='table table-striped table-condensed text-center'>
    <tr>
        <td>
            <label>GRUPO CADASTRAL</label>
            <input name="grupo_cadastral" type="text" value="<?= tratarHTML($oDados->grupo_cadastral); ?>"  class="form-control" readonly>
        </td>
        <td>
            <label>ABONÁVEL</label>
            <input name="abonavel" type="text" value="<?= tratarHTML($oDados->abonavel == 'S' ? 'Sim' : 'Não'); ?>"  class="form-control" readonly>
        </td>
        <td>
            <label>AGRUPA DEBITO</label>
            <input name="agrupa_debito" type="text" value="<?= tratarHTML(trata_aspas($oDados->agrupa_debito)); ?>"  class="form-control" readonly>
        </td>
        <td>
            <label>GRUPO OCORRENCIA</label>
            <input name="grupo_ocorrencia" type="text" value="<?= tratarHTML($oDados->grupo_ocorrencia); ?>"  class="form-control" readonly>
        </td>
        <td>
            <label>INF. HOR.</label>
            <input name="informar_horarios" type="text" value="<?= ($oDados->informar_horarios == 'S' ? 'Sim' : 'Não'); ?>" size="3"  class="form-control" readonly>
        </td>
        <td>
            <label>VIGENCIA INICIO</label>
            <input name="vigencia_inicio" type="text" value="<?= tratarHTML($oDados->vigencia_inicio); ?>"  class="form-control" readonly>
        </td>
        <td>
            <label>VIGENCIA FIM</label>
            <input name="vigencia_fim" type="text" value="<?= tratarHTML(trata_aspas($oDados->vigencia_fim)); ?>"  class="form-control" readonly>
        </td>
    </tr>
</table>

<table class='table table-striped table-condensed text-center'>
    <tr>
        <td>
            <label>APLICAÇÃO</label>
            <textarea name=aplic cols=40 rows=10 class="form-control" disabled><?= tratarHTML(trata_aspas($oDados->aplic)); ?></textarea>
        </td>
        <td>
            <label>IMPLICAÇÃO</label>
            <textarea name=implic cols=40 rows=10 class="form-control" disabled><?= tratarHTML(trata_aspas($oDados->implic)); ?></textarea>
        </td>
        <td>
            <label>PRAZOS</label>
            <textarea name=prazo cols=30 rows=10 class="form-control" disabled><?= tratarHTML($oDados->prazo); ?> </textarea>
        </td>
        <td>
            <label>FUNDAMENTO LEGAL</label>
            <textarea name=flegal cols=30 rows=10 class="form-control" disabled><?= tratarHTML(trata_aspas($oDados->flegal)); ?></textarea>
        </td>
    </tr>
</table>
</div>
<div align='center'>
    <p>
    <table border='0' align='center'>
        <tr>
            <td align='center'>
                <?php
                if ($modal != 'sim')
                {
                    echo botao('Voltar', "javascript:location.replace('tabela_ocorrencia_de_frequencia_visualizar.php?escolha=" . $escolha . "&chave=" . $chave . "');");
                }
                else
                {
                    echo botao('Voltar', "javascript:history.back();");
                }
                ?></td>
        </tr>
    </table>
</p>
</div>
<?php
// Base do formulário
//
	$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
