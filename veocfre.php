<?php
include_once( "config.php" );

verifica_permissao("sRH ou Chefia");


$siapecad = anti_injection($_REQUEST['siapecad']);

// instancia o banco de dados
$oDBase = new DataBase('PDO');

// seleciona do dados
$sql    = "SELECT * FROM tabocfre WHERE siapecad='$siapecad' ";
$oDBase->query($sql);
$oOcorr = $oDBase->fetch_object();

$siapecad   = $oOcorr->siapecad;
$sDescricao = $oOcorr->desc_ocorr;
$sirh       = $oOcorr->cod_ocorr;
$siape      = $oOcorr->cod_siape;
$aplic      = $oOcorr->aplic;
$implic     = $oOcorr->implic;
$prazo      = $oOcorr->prazo;
$flegal     = $oOcorr->flegal;
$sAtivo     = $oOcorr->ativo;
$resp       = $oOcorr->resp;


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho('Tabelas » Ocorrências » Consultar / Alterar');
$oForm->setSubTitulo("Consulta Tabela de Ocorrências");
$oForm->setSeparador(0);

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();
?>
<style>
    .box-float-left { float:left;padding:0px 2px 0px 2px; }
    #box-corpo { font-family:Tahoma;font-size:10px;color:#696969;width:100%;text-align:center;padding:0px 0px 0px 0px; }

    #box-grupo { width:660px;padding:10px 0px 0px 0px; }
    #box-subgrupo-1 { width:138px;padding:0px 0px 0px 0px; }
    #box-subgrupo-2 { width:520px;padding:0px 0px 10px 0px; }

    #box-siapecad { width:78px; }
    #box-descricao { width:414px; }
    #box-sirh { width:78px; }
    #box-siape { width:78px; }

    #box-responsavel { width:78px; }
    #box-ativo { width:50px; }

    #box-aplicacao { font-size:11px;width:414px;text-align:left;padding:7px 0px 0px 0px; }
    #box-implicacao { font-size:11px;width:414px;text-align:left;padding:7px 0px 0px 0px; }

    #box-prazo { width:218px;text-align:left;padding:7px 7px 0px 2px; }
    #box-fundamento-legal { width:230px;text-align:left;padding:7px 2px 0px 7px; }
</style>

<div id='box-corpo'>

    <div id='box-grupo'>
        <div id='box-siapecad' class='box-float-left'>
            SIAPECAD<br>
            <input type="text" id="siapecad" name="siapecad" class='alinhadoAoCentro' value="<?= tratarHTML($oOcorr->siapecad); ?>" size="10" maxlength="10" readonly>
        </div>

        <div id='box-descricao' class='box-float-left'>
            DESCRIÇÃO DA OCORRÊNCIA<br>
            <input type="text" id="sDescricao" name="sDescricao" class='alinhadoAoCentro' value="<?= tratarHTML($oOcorr->desc_ocorr); ?>" size="70" maxlength="70" readonly>
        </div>

        <div id='box-sirh' class='box-float-left'>
            SIRH<br>
            <input type="text" id="sirh" name="sirh" class='alinhadoAoCentro' value="<?= tratarHTML($oOcorr->cod_ocorr); ?>" size="10" maxlength="10" readonly>
        </div>

        <div id='box-siape' class='box-float-left'>
            SIAPE<br>
            <input type="text" id="siape" name="siape" class='alinhadoAoCentro' value="<?= tratarHTML($oOcorr->cod_siape); ?>" size="10" maxlength="10" readonly>
        </div>
    </div>

    <div id='box-grupo'>
        <div id='box-subgrupo-1'>
            <div id='box-responsavel' class='box-float-left'>
                RESPONSÁVEL<br>
                <input type="text" id="resp" name="resp" class='alinhadoAoCentro' value="<?= tratarHTML($oOcorr->resp); ?>" size="10" maxlength="10" readonly>
            </div>

            <div id='box-ativo' class='box-float-left'>
                ATIVO<br>
                <input type="text" id="sAtivo" name="sAtivo" class='alinhadoAoCentro' value="<?= tratarHTML($oOcorr->ativo); ?>" size="5" maxlength="5" readonly>
            </div>
        </div>
    </div>

    <div id='box-grupo'>
        <div id='box-subgrupo-1'>
            <div id='box-aplicacao' class='box-float-left'>
                APLICAÇÃO<br>
                <textarea name=aplic cols=100 rows=3  id="textarea" readonly><?= tratarHTML($oOcorr->aplic); ?></textarea>
            </div>
        </div>
    </div>

    <div id='box-grupo'>
        <div id='box-subgrupo-1'>
            <div id='box-implicacao' class='box-float-left'>
                IMPLICAÇÃO<br>
                <textarea name=implic cols=100 rows=3 id="textarea2" readonly ><?= tratarHTML($oOcorr->implic); ?></textarea>
            </div>
        </div>
    </div>

    <div id='box-grupo'>
        <div id='box-subgrupo-2'>
            <div id='box-prazo' class='box-float-left'>
                PRAZOS<br>
                <textarea name=prazo cols=35 rows=3 id="textarea9" readonly><?= tratarHTML($oOcorr->prazo); ?></textarea>
            </div>

            <div id='box-fundamento-legal' class='box-float-left'>
                FUNDAMENTO LEGAL<br>
                <textarea name=flegal cols=45 rows=3 id="textarea10" readonly><?= tratarHTML($oOcorr->flegal); ?></textarea>
            </div>
        </div>
    </div>

</div>
<?php
// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
