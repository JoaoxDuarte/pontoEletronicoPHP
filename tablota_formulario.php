<?php
// conexao ao banco de dados
// funcoes diversas
include_once( "config.php" );

verifica_permissao('sRH ou sTabServidor');

// ser? utilizada para carregar o SQL gerado para impressao
// sua alimenta??o ser? via ajax (jquery.js), a chamada
// encontra-se no sorttable.js
$_SESSION['sSQLPesquisa']   = "";
$_SESSION['sChaveCriterio'] = "";


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setCaminho($sFormCaminho);
$oForm->setCSS(_DIR_CSS_ . 'smoothness/jquery-ui-custom-px.min.css');
$oForm->setJS("js/jquery.mask.min.js");
$oForm->setJS("tablota_formulario.js?time=".date('YmdHis'));
$oForm->setOnLoad("$('#chave').focus();");
$oForm->setSeparador(0);


// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if ( !empty($dadosorigem) )
{
    // Valores passados - encriptados
    $dados = explode(":|:", base64_decode($dadosorigem));
    $excluir        = $dados[0];
    $codigo_excluir = $dados[1];
    
    if ($excluir == 'sim')
    {
        $oDBase = new DataBase();

        $oDBase->query("
        UPDATE tabsetor 
            SET ativo = 'N' 
                WHERE codigo = :codigo 
        ",
        array(
            array(':codigo', $codigo_excluir, PDO::PARAM_STR),
        ));
        
        if ($oDBase->affected_rows() > 0)
        {
            unset($_REQUEST['dados']);
            mensagem("Excluída com sucesso a UORG ".$codigo_excluir."!", "tablota.php");
            exit();
        }
    }
}


// Atualização do horário de funcionamento da uorg
if(isset($_POST['inicio_atend']) && isset($_POST['fim_atend']) && isset($_POST['uorgs'])){

    $inicio_atend = $_POST['inicio_atend'];
    $fim_atend = $_POST['fim_atend'];
    $uorgs = substr($_POST['uorgs'], 0, -1);

    $oDBase = new DataBase();
    $query = $oDBase->query("UPDATE tabsetor SET inicio_atend = '".$inicio_atend."', fim_atend = '".$fim_atend."' WHERE codigo in (".$uorgs.")");
 
    unset($_REQUEST['dados']);
    echo "<script type='text/javascript'>alert('Horários alterados com sucesso!')</script>'";
}

if (isset($_REQUEST["chave"]))
{
    //$oForm->setIconeParaImpressao("tablota_imp.php");
}
else
{
    $_SESSION['sColunaSortTable'] = "";
}

// Topo do formul?rio
//
$oForm->setSubTitulo($sFormsubTitulo);

// Topo do formul?rio
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML('1150px');

?>
<script language="javascript">

    function selecionarTodas(field){
        $('input:checkbox').prop('checked', field.checked);    
    }

    function validar()
    {
        // objeto mensagem
        oTeste = new alertaErro();
        oTeste.init();

        // dados
        var chave = $('#chave');

        if (chave.val().length == 0)
        {
            oTeste.setMsg('É obrigatório informar o critério de pesquisa!', chave);
        }

        // se houve erro ser?(?o) exibida(s) a(s) mensagem(ens) de erro
        var bResultado = oTeste.show();
        if (bResultado == false)
        {
            return bResultado;
        }
        else
        {
            window.location.replace("<?= $sFormAcao; ?>");
        }
    }

    function selecionarUorg(field){
        var uorgs = $('input[name="uorgs"]').val();

        if(field.is(':checked')){
            uorgs += field.val()+",";
        }else{
            uorgs = uorgs.replace(field.val()+',', '');
        }

        $('input[name="uorgs"]').val(uorgs);
    }

</script>
<form  action="<?= $sFormAcao; ?>" method="POST" id="form1" name="form1" <?= ($sFormSubmit == '' ? '' : 'onSubmit="' . $sFormSubmit . '"'); ?> >
    <input type="hidden" name="modo" value="<?= tratarHTML($modo); ?>" >
    <input type="hidden" name="corp" value="<?= tratarHTML($corp); ?>">
    <table border="0" width="100%" cellspacing="0" cellpadding="0" bordercolordark="white" bordercolorlight="#336699">
        <tr>
            <td width="100%" colspan="3" style="word-spacing: 0; margin: 0">&nbsp;</td>
        </tr>
        <tr>
            <td class="corpo" width="100%" colspan="3">
                <p id='so_na_tela' align="center" class='tahomaSize_1'>
                    <input type="radio" value="codigo" checked name="escolha" onclick="$('#chave').focus()">
                    Por Código&nbsp;&nbsp;&nbsp;
                    <input type="radio" name="escolha" value="descricao" onclick="$('#chave').focus()">
                    Por Descrição&nbsp;&nbsp;&nbsp;
                    <input type="radio" name="escolha" value="uorg_pai" onclick="$('#chave').focus()">
                    Por Uorg Pai&nbsp;&nbsp;&nbsp;
                    <input type="radio" name="escolha" value="uorg" onclick="$('#chave').focus()">
                    Por Uorg&nbsp;&nbsp;&nbsp;
                    <input type="radio" name="escolha" value="upag" onclick="$('#chave').focus()">
                    Por Upag
                    
                </p>
            </td>
        </tr>
        <tr>
            <td colspan='3' align="center" style="word-spacing: 0; margin: 0">&nbsp;</td>
        </tr>
        <tr>
            <td colspan='3' class="corpo" style="word-spacing: 0; margin: 0">
                <div class="col-md-4 center">
                </div>
                <div class="col-md-4 center">
                <p id='so_na_tela' align="center" class='tahomaSize_1'>Chave&nbsp;
                    <input type="text" class="form-control caixa" id="chave" name="chave" title="N?o informe pontos" size="28">
                </p>
                </div>
            </td>
        </tr>
    </table>
    
    <div class="col-md-12 margin-10 margin-bottom-10">
        <div class="text-center ">
            <button type="image" class="btn btn-sucess  btn-success" id="btn-continuar">
            <span class="glyphicon glyphicon-search"></span> Pesquisar
            </button>
        </div>
    </div>

</form>

    <div class="col-md-12">
        <div class="row">

            <?php if(($_SESSION['sAdmCentral']=='S' || $_SESSION['sSenhaI']=='S')): ?>
                <div class='col-md-1' style="margin-right: 10px;">
                    <a href="tablota_cadastrar.php">
                        <button type="button" data-limite="nao" class="btn btn-default adicionar">Novo Setor</button>
                    </a>
                    <br><br>
                </div>
            <?php endif; ?>
            <div class='col-md-2' id="setor-massa" style="display: none">
                <a href="#div-horario-setor">
                    <button type="button" data-limite="nao" class="btn btn-default adicionar">Alterar Horário de Funcionamento</button>
                </a>
            </div>
            <table id="tableServidores" class="table table-striped table-bordered text-center table-condensed tablesorter"></table>
            <button type="button" style="display: none" data-limite="nao" class="btn btn-default carregar-todos search">Carregar mais setores...</button>
        </div>

    </div>
<?php
$pesquisa = "";
if (isset($_REQUEST["chave"]))
{
    $var1 = urldecode($_REQUEST["chave"]);
    $var2 = urldecode($_REQUEST["escolha"]);

    $pesquisa = "SELECT periodo_excecao, codigo, descricao, cod_uorg, upag, ug, ativo, uorg_pai FROM tabsetor WHERE true ";

    if ($_SESSION["sLog"] != "S")
    {
        $pesquisa .= " AND upag = '" . $_SESSION['upag'] . "' ";
    }
    if ($_SESSION["sRH"] != "S" && $_SESSION["sAPS"] == "S")
    {
        $pesquisa .= " AND codigo = '" . $_SESSION['sLotacao'] . "' ";
    }

    switch ($var2)
    {
        case "codigo": $pesquisa .= " AND codigo = '$var1' ";
            break;
        case "descricao": $pesquisa .= " AND descricao LIKE '%$var1%' ";
            break;
        case "uorg": $pesquisa .= " AND cod_uorg LIKE '%$var1%' ";
            break;
        case "upag": $pesquisa .= " AND upag LIKE '%$var1%' ";
            break;
        case "uorg_pai": $pesquisa .= " AND uorg_pai LIKE '%$var1%' ";
        break;
    }

    $_SESSION['sSQLPesquisa']   = $pesquisa;
    $_SESSION['sChaveCriterio'] = array("chave" => $var1, "escolha" => $var2);

    $pesquisa .= "ORDER BY codigo ";

    $sequencia = 1;
    $oTbDados  = new DataBase('PDO');
    $oTbDados->query($pesquisa);
    $nRows     = $oTbDados->num_rows();

    function getTitle($titulo){

        if($titulo == "SIM")
            return "Sim";

        return "Não";
    }

    if ($nRows > 0)
    {
        ?>
<style>
.not-active {
  pointer-events: none;
  cursor: default;
  text-decoration: none;
  color: black;
  opacity : 0.1;
}
</style>
    <div width="100%">           
     Total de <b><?= tratarHTML(number_format($oTbDados->affected_rows(), 0, ',', '.')); ?></b> registros.
    </div>
    <form  action="" method="POST" id="form2" name="form2" >       
            <table id="myTable" class="table table-striped table-bordered table-hover text-center table-condensed tablesorter" style="width:100%;">
                <tr >
                    <th style="width:1%;vertical-align:middle;" class='text-center'><input type="checkbox" id="all_uorgs" onclick="selecionarTodas(this)" /></th>
                    <th style="width:1%;vertical-align:middle;" class='text-center'>UORG</th>
                    <th style="width:94%;vertical-align:middle;" class='text-center'>Descrição</th>
                    <th style="width:1%;vertical-align:middle;" class='text-center'>UORG Pai</th>
                    <th style="width:1%;vertical-align:middle;" class='text-center'>UPAG</th>
                    <th style="width:1%;vertical-align:middle;" class='text-center'>Período Exceção</th>
                    <th style="width:1%;vertical-align:middle;" class='text-center'>UG</th>
                    <th style="width:1%;vertical-align:middle;" class='text-center'>Ativo</th>
                    <th style="width:1%;vertical-align:middle;" class='text-center'>Ações</th>
                </tr>
                <?php
            } // fim do while
                $contador = 0;

                while ($pm_partners = $oTbDados->fetch_object())
                {
                    $contador++;
                    $ativo = ($pm_partners->ativo !== 'S' ? 'NÃO' : 'SIM');
                    $destino_alterar = (($_SESSION['sAdmCentral']=='S' || $_SESSION['sSenhaI']=='S' || $_SESSION['sRH'] == "S") ? "<a href='tablota_alterar.php?dados=" . base64_encode($pm_partners->codigo) . "'>Alterar</a>" : "<a href='javascript:void(0);' alt='Sem permissão de acesso' title='Sem permissão de acesso' class='not-active'>Alterar</a>" );
                    $destino_excluir = ''; //(($_SESSION['sAdmCentral']=='S' || $_SESSION['sSenhaI']=='S') ? "<a href='tablota.php?dados=" . base64_encode('sim:|:'.$pm_partners->codigo) . "'>Excluir</a>" : "<a href='javascript:void(0);' alt='Sem permissão de acesso' title='Sem permissão de acesso' class='not-active'>Excluir</a>" );
                    ?>
                    <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)'>
                        <td width="10%" align="" height='16px'><input type="checkbox" class="uorgs" name="uorgs[]" value="<?= $pm_partners->codigo; ?>" onclick="selecionarUorg($(this))"/></td>
                        <td width="10%" align="" height='16px'><?= tratarHTML($pm_partners->codigo); ?></td>
                        <td style="width:40%;text-align:left;" nowrap><?= tratarHTML($pm_partners->descricao); ?></td>
                        <td width="10%" align=""><?= tratarHTML($pm_partners->uorg_pai); ?></td>
                        <td width="10%" align=""><?= tratarHTML($pm_partners->upag); ?></td>
                        <td width="10%" align=""><?= tratarHTML(getTitle($pm_partners->periodo_excecao)); ?></td>
                        <td width="10%" align=""><?= tratarHTML($pm_partners->ug); ?></td>
                        <td width="10%" align=""><?= tratarHTML($ativo); ?></td>
                        <td width="10%" align=""><?= $destino_alterar . (empty($destino_excluir) ? '' : '&nbsp;&nbsp;&nbsp;&nbsp;' . $destino_excluir); ?></td>
                    </tr>
                    <?php
                } // fim do while

                // Exibe o botão de alterar setor em massa
                if($contador){
                    echo "<script type='text/javascript'>$('#setor-massa').show();</script>";
                }else{
                    echo "<script type='text/javascript'>$('#setor-massa').hide();</script>";
                }
                ?>
            </table>
            <?php if($contador): ?>
            <div class="row" id="div-horario-setor">
                <div class="col-md-1">
                    <label>Início: <input type="text" id="inicio_atend" name="inicio_atend" class="form-control horas" value="" size="8" maxlength="8" style="width:80px;"></label>
                </div> 
                <div class="col-md-1">
                    <label>Fim: <input type="text" id="fim_atend" name="fim_atend" class="form-control horas" value="" size="8" maxlength="8" style="width:80px;"></label>
                </div>
                <div class="col-md-4">
                    <label>
                        <input type="hidden" id="uorgs" name="uorgs" value="">
                        <button type="button" class="btn btn-sucess  btn-info btn-alterar-horario" id="btn-continuar" style="margin-top: 20px;">
                            <span class="glyphicon glyphicon-pencil"></span> Alterar Horário de Funcionamento
                        </button>
                    </label>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6"><i>Só é permitido a alteração de até 1000 setores.</i></div>
            </div>
            <?php endif; ?>
        </form>
        
        <?php
    }
    else
    {

        unset($_REQUEST["chave"]);
        //mensagem("Nenhum registro selecionado!");
    }


// Base do formul?rio
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
