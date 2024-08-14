<?php
include_once( "config.php" );
include_once( "inc/calcula_horas_do_recesso.php" );
include_once( "src/controllers/TabServativController.php" );
include_once( "src/controllers/TabRecessoFimDeAnoController.php" );
include_once( "src/controllers/TabBancoDeHorasAcumulosController.php" );
    

$objServativController           = new TabServativController();
$objOcorrenciasGrupos            = new OcorrenciasGrupos();

$codigoCreditoRecessoPadrao = $objOcorrenciasGrupos->CodigoCreditoRecessoPadrao( $sitcad );
$codigoDebitoRecessoPadrao  = $objOcorrenciasGrupos->CodigoDebitoRecessoPadrao( $sitcad );


verifica_permissao('logado');

// instancia o banco de dados
$oDBase = new DataBase('PDO');

$sMatricula = (isset($_REQUEST['siape']) ? anti_injection($_REQUEST['siape']) : $_SESSION['sMatricula']);
$sSaldo     = (isset($_REQUEST['saldo']) ? anti_injection($_REQUEST['saldo']) : '0');

$sMatricula = getNovaMatriculaBySiape($sMatricula);

if (!empty($sMatricula) && $sMatricula != $_SESSION['sMatricula'])
{
    $oDBase = $objServativController->selecionaServidor($sMatricula);
    
    if ($oDBase && $oDBase->num_rows() == 0)
    {
        // instancia o objeto mens
        $oMensagem = new mensagem();
        $oMensagem->exibeMensagem(24);
        exit();
    }
    else
    {
        $oServidor = $oDBase->fetch_object();
        $sLotacao  = $oServidor->cod_lot;
        $sNome     = $oServidor->nome;
        $dtAdm     = $oServidor->dt_adm; // data da admissao invertida Ex. 02/02/2012 -> 20120202
        $iniver    = $_SESSION["hveraoi"];
        $fimver    = $_SESSION["hveraof"];
    }
}
else
{
    $sMatricula = $_SESSION['sMatricula'];
    $sLotacao   = $_SESSION['sLotacao'];
    $sNome      = $_SESSION['sNome'];
    $dtAdm      = $_SESSION['sDtAdm']; // data da admissao invertida Ex. 02/02/2012 -> 20120202
    $iniver     = $_SESSION["hveraoi"];
    $fimver     = $_SESSION["hveraof"];
}

// largura das tabelas
// $ano_inicio = 2010;
// 2010/2011 o prazo final foi 31/Março/2011
// 2011/2012 o prazo final foi 30/Abril/2011
$anoLimiteMarco = 2010;
$aba            = 'pri';


$title = _SISTEMA_SIGLA_ . ' | Demonstrativo de Compensações do Servidor';

$css = array();

$javascript   = array();
$javascript[] = 'entrada4.js';

include("html/html-base.php");
include("html/header.php");

?>
<style>
#corlabel ul li a{
    color: #0a0a0a!important;
}
/* Style the tab */
.tab {
    overflow: hidden;
    border: 1px solid #ccc;
    background-color: #f1f1f1;
}

/* Style the buttons that are used to open the tab content */
.tab button {
    background-color: inherit;
    float: left;
    border: none;
    outline: none;
    cursor: pointer;
    padding: 14px 16px;
    transition: 0.3s;
}

/* Change background color of buttons on hover */
.tab button:hover {
    background-color: #ddd;
}

/* Create an active/current tablink class */
.tab button.active {
    background-color: #ccc;
}

/* Style the tab content */
.tabcontent {
    display: none;
    padding: 6px 12px;
    border: 1px solid #ccc;
    border-top: none;
}
</style>
    <script>
        function openCity(evt, cityName) {
            // Declare all variables
            var i, tabcontent, tablinks;
            var tabs = [];
            
            // Get all elements with class="tabcontent" and hide them
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }

            // Get all elements with class="tablinks" and remove the class "active"
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }

            // Show the current tab, and add an "active" class to the button that opened the tab
            document.getElementById(cityName).style.display = "block";
            evt.currentTarget.className += " active";
        }
</script>
        
<!-- Tab content -->

<div class="container margin-50" id="form-comparecimento">
    <!-- Row Referente aos dados dos funcionários -->
    <div class="row margin-10">

        <!-- Mensagem de aviso/erro -->
        <?php exibeMensagemUsuario($mensagemUsuario); // $mensagemUsuario atribuida no config.php ?>

        <!-- Título da página -->
        <div class="col-md-12 subtitle">
            <h4 class="lettering-tittle uppercase"><strong>Demonstrativo de Compensações do Servidor</strong></h4>
        </div>

        <!-- Dados do servidor/estagiário -->
        <div class="col-md-12" id="dados-funcionario">
            <div class="col-md-3">
                <h5><strong>SIAPE</strong></h5>
                <p><?= tratarHTML(removeOrgaoMatricula($sMatricula)); ?></p>
            </div>
            <div class="col-md-6">
                <h5><strong>NOME</strong></h5>
                <p><?= tratarHTML($sNome); ?></p>
            </div>
            <div class="col-md-3">
                <h5><strong>LOTAÇÃO</strong></h5>
                <p><?= tratarHTML(removeOrgaoLotacao( $sLotacao )); ?></p>
            </div>
        </div>

<div id="corlabel" class="content" >
    <div class="tab">
        <button class="tablinks" onclick="openCity(event, 'pri')">HORAS COMUNS</button>
        <button class="tablinks" onclick="openCity(event, 'seg')">BANCO DE HORAS</button>
        <button class="tablinks" onclick="openCity(event, 'ter')">RECESSO DE FIM DE ANO</button>
    </div>
</div>
        <?php

        ##------------------------------------------------------------------------\
        #  HORAS COMUNS - COMPENSACOES                                            |
        ##------------------------------------------------------------------------/
        #
       
        ?>
        <!-- HORAS COMUNS -->
        <div id="pri" class="tabcontent" style="display:none">
            <?php
    
            $bSoSaldo         = true;
            $bParcial         = ($status == 'HOMOLOGADO' ? false : true);
            $bImprimir        = false;
            $bExibeResultados = false;
            $relatorioTipo    = '0';
            //$mes2 = date('m');
            //$ano2 = date('Y');
            $tipo             = 0;

            //
            // $pSiape : definido no início do script
            // $mes    : definido no início do script
            // $ano    : definido no início do script
            // $mes2   : definido no início do script
            // $ano2   : definido no início do script
            $mesFim = date('m');
            $anoFim = date('Y');

            include_once( "veponto_saldos.php" );

            $mesFim         = date('m');
            $anoFim         = date('Y');
            $veponto_saldos = imprimirSaldoCompensacaoDoMes();

            print $veponto_saldos;
    
            ?>
        </div>
        <?php
        #
        ##------------------------------------------------------------------------\
        #  FIM DO CALCULO DE HORAS COMUNS                                         |
        ##------------------------------------------------------------------------/
        

        
        ##------------------------------------------------------------------------\
        #  BANCO DE HORAS - SALDOS                                                |
        ##------------------------------------------------------------------------/
        #
        ?>
        <!-- BANCO DE HORAS - SALDO -->
        <div id="seg" class="tabcontent" style="display:none">
            <?php 

            $objTabBancoDeHorasAcumulosController = new TabBancoDeHorasAcumulosController();
            $objTabBancoDeHorasAcumulosController->showQuadroDeSaldo( $sMatricula, null, null, false );
    
            ?>
        </div>
        <?php
        #
        ##------------------------------------------------------------------------\
        #  FIM BANCO DE HORAS - SALDOS                                            |
        ##------------------------------------------------------------------------/


        ##------------------------------------------------------------------------\
        #  CALCULO DE HORAS DO RECESSO DEVIDAS E NAO COMPENSADAS                  |
        ##------------------------------------------------------------------------/
        #
        ?>
        <!-- RECESSO DE FIM DE ANO -->
        <div id="ter" class="tabcontent" style="display:none">
            <?php 

            if (($sSaldo == '0' || $sSaldo == '2')) //($recesso > 0)
            {
                $objRecessoFimDeAnoController = new TabRecessoFimDeAnoController();
                $objRecessoFimDeAnoController->showRecessoQuadroDemonstrativo( $sMatricula, $periodo );
            }
            
            ?>
        </div>
        <?php
        #
        ##------------------------------------------------------------------------\
        #  FIM DO CALCULO DE HORAS DO RECESSO DEVIDAS E NAO COMPENSADAS           |
        ##------------------------------------------------------------------------/
        ?>

    </div>
</div>

<script>
    var i   = 0;
    var aba = '<?= $aba; ?>';
    
    switch (aba)
    {
        case 'pri': i = 0; break;
        case 'seg': i = 1; break;
        case 'ter': i = 1; break;
    }
            
    // Get all elements with class="tabcontent" and hide them
    tabcontent = document.getElementsByClassName("tabcontent");
    for (x = 0; x < tabcontent.length; x++) {
        tabcontent[x].style.display = "none";
    }
    tabcontent[i].style.display = "block";

    // Get all elements with class="tablinks" and remove the class "active"
    tablinks = document.getElementsByClassName("tablinks");
    tablinks[i].className = tablinks[i].className.replace("tablinks", "tablinks active");
</script>
<?php
include("html/footer.php");

$_SESSION['sModuloPrincipalAcionado'] = $ModuloPrincipalAcionado;

//DataBase::fechaConexao();
