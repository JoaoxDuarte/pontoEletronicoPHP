<?php
//Rotina a ser rodada sempre ultimo dia de cada mes quando ativado o mes

include_once("config.php");
include_once("src/controllers/TabBancoDeHorasCiclosController.php");

verifica_permissao("administracao_central");

// parametros enviados por formulario
$modo  = anti_injection($_REQUEST['modo']);
$id    = anti_injection($_REQUEST['id']);
$cmesi = anti_injection($_REQUEST['cmesi']);

if (isset($_REQUEST['aba']))
{
    $aba = anti_injection($_REQUEST['aba']);
}
else
{
    $aba = 'pri';
}

// instancia banco de dados
$oDBase = new DataBase('PDO');
$oDBase->setDestino(pagina_de_origem());

## classe para montagem do formulario padrao
#
$oForm = new formPadrao;
$oForm->setCaminho("Utilitários » Gestores » Prazos");
$oForm->setOnLoad("");
$oForm->setCSS(_DIR_CSS_ . "smoothness/jquery-ui-custom-px.min.css");
$oForm->setDialogModal();
$oForm->setLargura('950');
$oForm->setSeparador(0);

// Topo do formulário
//
$oForm->setSubTitulo("Permissão de Acesso (Prazos/Períodos)");

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();



if ($modo == "2") // ativar a competência
{
    $oDBase->setMensagem("Problema com a tabela de prazos!");
    $result1 = $oDBase->query("UPDATE tabvalida SET ativo='N' ");
    $result2 = $oDBase->query("UPDATE tabvalida SET ativo='S' WHERE compi='$cmesi' ");
    $result3 = $oDBase->query("UPDATE tabsetor SET tfreq='N', dfreq='N' WHERE ativo='S' ");

    //atualizar indicador de homologação dos servidores VER ESTAGIARIOS?
    $oDBase->setMensagem("Problemas no acesso a Tabela de CADASTRO (E000124.".__LINE__.")");
    $rfh = $oDBase->query("
    UPDATE 
        servativ 
    SET 
        freqh = 'N', 
        motidev = '' 
    WHERE 
        cod_sitcad IN ('01','03','04','05','14','09','06','07','10','11','12','19','20','21','22','66') 
    ");

    //obtendo dados dos servidores para liberar modo frequencia
    $oDBase->setMensagem("Problemas no acesso a Tabela de CADASTRO (E000124.".__LINE__.")");
    $rx = $oDBase->query("
    SELECT 
        mat_siape 
    FROM 
        servativ 
    WHERE 
        cod_sitcad IN ('01','03','04','05','14','09','06','07','10','11','12','19','20','21','22','66') 
    ");

    // armazena mensagem de erro
    $sErros = '';

    // armazena mensagem de aviso
    $sAvisos = '';

    // Define as competencias
    $nMes = substr($cmesi, 0, 2);
    $nAno = substr($cmesi, -4);
    for ($n = $nMes; $n <= 13; $n++)
    {
        $ano     = ($n == '13' ? ($nAno + 1) : $nAno);
        $compete = ($n == '13' ? '01' : substr('00' . $n, -2)) . $ano;

        ## testa se existe a tabela FRQ
        #  se não existir cria-se
        #  DEVE HAVER A TABELA frq2018
        #
        if (!existeDBTabela("frq$ano", "sisref"))
        {
            $sql = "CREATE TABLE IF NOT EXISTS frq" . $ano . " LIKE frq2018 ";
            $oDBase->setMensagem("Problemas na geração da Tabela de FRQ" . $ano . " (E000124.".__LINE__.")");
            $result = $oDBase->query($sql);
            if ($result == 1)
            {
                $sAvisos .= "- A tabela do ano $ano foi criada com sucesso!\\n";
            }
            else
            {
                $sErros .= "- A tabela do ano $ano não foi criada!\\n";
            }
        }

        ## testa se existe a tabela PONTO
        #  se não existir cria-se
        #  DEVE HAVER A TABELA ponto122018
        #
        if (!existeDBTabela("ponto$compete", "sisref"))
        {
            $sql = "CREATE TABLE IF NOT EXISTS ponto" . $compete . " LIKE ponto122018 ";
            $oDBase->setMensagem("Problemas na geração da Tabela de PONTO" . $compete . " (E000124.".__LINE__.")");
            $result = $oDBase->query($sql);
            if ($result == 1)
            {
                $sAvisos .= "- A tabela ponto$compete foi criada com sucesso!\\n";
            }
            else
            {
                $sErros .= "- A tabela ponto$compete não foi criada!\\n";
            }
        }

        ## testa se existe a tabela HISTPONTO
        #  se não existir cria-se
        #  DEVE HAVER A TABELA histponto122018
        #
        if (!existeDBTabela("histponto$compete", "sisref"))
        {
            $sql = "CREATE TABLE IF NOT EXISTS histponto" . $compete . " LIKE histponto122018 ";
            $oDBase->setMensagem("Problemas na geração da Tabela de Histórico PONTO" . $compete . " (E000124.".__LINE__.")");
            $result2 = $oDBase->query($sql);
            if ($result2 == 1)
            {
                $sAvisos .= "- A tabela histponto$compete foi criada com sucesso!\\n";
            }
            else
            {
                $sErros .= "- A tabela histponto$compete não foi criada!\\n";
            }
        }
    }

    mensagem("Mes ativado com sucesso!" . ($sAvisos == "" ? "" : "\\n\\nAvisos:\\n" . $sAvisos) . ($sErros == "" ? "" : "\\nErros:\\n" . $sErros));

    unset($modo);
}

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

<div id="corlabel" class="content" >
    <div class="tab">
        <button class="tablinks" onclick="openCity(event, 'pri')">HOMOLOGAÇÃO / VERIFICAÇÃO</button>
        <button class="tablinks" onclick="openCity(event, 'seg')">HORARIO DE VER&Atilde;O</button>
        <button class="tablinks" onclick="openCity(event, 'ter')">RECESSO DE FIM DE ANO</button>
        <button class="tablinks" onclick="openCity(event, 'qua')">QUARTA FEIRA DE CINZAS</button>
        <button class="tablinks" onclick="openCity(event, 'qui')">CICLOS DE BANCO DE HORAS</button>
    </div>
</div>
        
<!-- Tab content -->
    
<!-- HOMOLOGAÇÃO / VERIFICAÇÃO -->
<div id="pri" class="tabcontent" style="display:block">
    <?php tabHomologacaoVerificacao(); ?>
</div>
    
<!-- HORÁRIO DE VERÃO -->
<div id="seg" class="tabcontent" style="display:none"><?php tabHorarioDeVerao(); ?></div>

<!-- RECESSO DE FIM DE ANO -->
<div id="ter" class="tabcontent" style="display:none"><?php tabRecessoDeFimDeAno(); ?></div>

<!-- QUARTA-FEIRA DE CINZAS -->
<div id="qua" class="tabcontent" style="display:none"><?php tabQuartaFeiraDeCinzas(); ?></div>

<!-- CICLOS DE BANCO DEHORAS -->
<div id="qui" class="tabcontent" style="display:none"><?php tabCiclosDeBancoDeHoras(); ?></div>

<script>
    var i   = 0;
    var aba = '<?= $aba; ?>';
    
    switch (aba)
    {
        case 'pri': i = 0; break;
        case 'seg': i = 1; break;
        case 'ter': i = 2; break;
        case 'qua': i = 3; break;
        case 'qui': i = 4; break;
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

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

die();


/* ************************************************ *
 *                                                  *
 *              FUNÇÕES COMPLEMENTARES              *
 *                                                  *
 * ************************************************ */

/**
 * HOMOLOGAÇÃO / VERIFICAÇÃO 
 */
function tabHomologacaoVerificacao()
{
    $anos_anteriores = "";
    $queryString     = "
    SELECT 
        id, 
        compi, 
        DATE_FORMAT(rhi,'%d/%m/%Y')     AS rhi, 
        DATE_FORMAT(rhf,'%d/%m/%Y')     AS rhf, 
        DATE_FORMAT(apsi,'%d/%m/%Y')    AS apsi, 
        DATE_FORMAT(apsf,'%d/%m/%Y')    AS apsf, 
        DATE_FORMAT(gbnini,'%d/%m/%Y')  AS gbnini, 
        DATE_FORMAT(gbninf,'%d/%m/%Y')  AS gbninf, 
        DATE_FORMAT(outchei,'%d/%m/%Y') AS outchei, 
        DATE_FORMAT(outchef,'%d/%m/%Y') AS outchef, 
        DATE_FORMAT(rmi,'%d/%m/%Y')     AS rmi, 
        DATE_FORMAT(rmf,'%d/%m/%Y')     AS rmf, 
        DATE_FORMAT(cadi,'%d/%m/%Y')    AS cadi, 
        DATE_FORMAT(cadf,'%d/%m/%Y')    AS cadf, 
        ativo 
    FROM 
        tabvalida 
    WHERE 
        (RIGHT(compi,4) >= DATE_FORMAT(NOW(),'%Y') 
        OR compi=CONCAT('12',DATE_FORMAT(NOW(),'%Y')-1))
    ORDER BY 
        CONCAT(RIGHT(compi,4),LEFT(compi,2)) 
    ";
    
    $oDBase = new DataBase();
    
    $oDBase->setMensagem("Problemas no acesso a Tabela de PRAZOS (E000125.".__LINE__.")");
    $oDBase->query($queryString);
    
    ?>
    <table id="myTable" class="table table-striped table-bordered text-center table-condensed tablesorter">
        <tr height='20'>
            <td class='' align="center" colspan="7">HOMOLOGAÇÃO / VERIFICAÇÃO</td>
        </tr>
        <tr height='20'>
            <td class='' align="center"><label class="control-label" >Competência</label></td>
            <td class='' align="center" colspan="2"><label class="control-label" >RH</label></td>
            <td class='' align="center" colspan="2"><label class="control-label" >Chefias</label></td>
            <td class='' align="center" width="6%" ><label class="control-label" >Ação</label></td>
            <td class='' align="center" width="6%"><label class="control-label" >Ativo</label></td>
        </tr>
        <?php
        
        while ($pm = $oDBase->fetch_object())
        {
            $imagem_src   = _DIR_IMAGEM_ . ($pm->ativo == 'S' ? "ativar_on.gif" : "ativar_off.gif");
            $imagem_title = ($pm->ativo == 'S' ? "Desativar esta competência" : "Ativar esta competência");

            $mes_homologacao = new trata_datasys();
            $compet_valida   = ($mes_homologacao->getAnoHomologacao() . $mes_homologacao->getMesHomologacao());
            $compet_vigente  = (substr($pm->compi, 2, 4) . substr($pm->compi, 0, 2));

            if ($compet_vigente >= $compet_valida)
            {
                $ativa_ou_desativa_mes = "<a href='tabvalida.php?modo=2&id=" . tratarHTML($pm->id) . "&cmesi=" . tratarHTML($pm->compi) . "&aba=pri'><img src='" . tratarHTML($imagem_src) . "' title='" . tratarHTML($imagem_title) . "' border='0' ></a>";
            }
            else
            {
                $ativa_ou_desativa_mes = "&nbsp;";
            }
           
            ?>
            <tr onmouseover='pinta(1, this)' onmouseout='pinta(2, this)'>
                <td align='center'>&nbsp;<?= tratarHTML(substr($pm->compi, 0, 2) . ' / ' . tratarHTML(substr($pm->compi, 2, 4))); ?>&nbsp;</td>
                <td align='center'>&nbsp;<?= tratarHTML($pm->rhi); ?>&nbsp;</td>
                <td align='center'>&nbsp;<?= tratarHTML($pm->rhf); ?>&nbsp;</td>
                <td align='center'>&nbsp;<?= tratarHTML($pm->apsi); ?>&nbsp;</td>
                <td align='center'>&nbsp;<?= tratarHTML($pm->apsf); ?>&nbsp;</td>
                <td align='center'>&nbsp;<a href='tabvalidaa.php?id=<?= tratarHTML($pm->id); ?>'>Alterar</a>&nbsp;</td>
                <td align='center' height='20px'>&nbsp;<?= $ativa_ou_desativa_mes; ?>&nbsp;</td>
            </tr>
            <?php
        }
       
        ?>
    </table>

    <table border="0" width="60%" cellspacing="0">
        <tr>
            <td style="font-weight: bold; vertical-align: top; word-spacing: 1; margin-right: 0; margin-top: 1; margin-bottom: 0;font-family:verdana; font-size:7pt;font-color:#000000">
                Observação:&nbsp;
            </td>
            <td style="word-spacing: 1; margin-right: 0; margin-top: 1; margin-bottom: 0;font-family:verdana; font-size:7pt;font-color:#000000">
                A ativa&ccedil;&atilde;o de compet&ecirc;ncia deve ser efetivada no &uacute;ltimo dia do m&ecirc;s ap&oacute;s encerrado o expediente, o que liberar&aacute; para in&iacute;cio da homologação do registro de frequ&ecirc;ncia, marcando os setores como pendentes de transmiss&atilde;o de frequ&ecirc;ncia.
            </td>
        </tr>
    </table>
    <?php
}


/**
 * HORÁRIO DE VERÃO
 */
function tabHorarioDeVerao()
{
    $oDBase = new DataBase();
    
    $oDBase->setMensagem("Problemas no acesso a Tabela de HORÁRIO DE VERÃO (E000126.".__LINE__.")");
    $oDBase->query("
    SELECT 
        id, 
        periodo, 
        DATE_FORMAT(hverao_inicio,'%d/%m/%Y') AS hverao_inicio, 
        DATE_FORMAT(hverao_fim,'%d/%m/%Y') AS hverao_fim 
    FROM 
        tabhorario_verao 
    ORDER BY 
        periodo DESC 
    ");
    
    ?>
    <div class="col-md-12">
        <div class="col-md-12 text-right">
            <a class="no-style"
               href="javascript:window.location.replace('tabhverao.php');">
                <button type="button" class="btn btn-primary btn-xs">
                    <span class="glyphicon glyphicon-plus"></span> Novo
                </button>
            </a>
        </div>
        <div class="col-md-11 text-right">
            <label for="lot" class="control-label">&nbsp;</label>
        </div>
    </div>

    <table id="myTable" class="table table-striped table-bordered text-center table-condensed tablesorter">
        <tr height='20'>
            <td class='bgtitulo' align="center" rowspan="2" nowrap>&nbsp;PERÍODO&nbsp;</td>
            <td class='bgtitulo' align="center" colspan="3" nowrap>&nbsp;HORARIO DE VER&Atilde;O&nbsp;</td>
        </tr>
        <tr>
            <td class='bgtitulo' align="center">&nbsp;INÍCIO&nbsp;</td>
            <td class='bgtitulo' align="center">&nbsp;FIM</td>
            <td class='bgtitulo' align="center">&nbsp;A&Ccedil;&Atilde;O&nbsp;</td>
        </tr>
        <?php
        
        while ($oHVerao = $oDBase->fetch_object())
        {
            $id                      = $oHVerao->id;
            $periodo                 = $oHVerao->periodo;
            $horario_de_verao_inicio = $oHVerao->hverao_inicio;
            $horario_de_verao_fim    = $oHVerao->hverao_fim;

            $classe = (substr($periodo, 0, 4) == date('Y') ? 'centro2' : 'sem_borda');
            $acao   = (substr($periodo, 0, 4) == date('Y') ? 'Alterar' : '');
           
            ?>
            <tr bgcolor="#FFFFFF" onmouseover='pinta(1, this)' onmouseout='pinta(2, this)'>
                <!-- periodo //-->
                <td align="center"><?= tratarHTML($periodo); ?></td>
                <!-- Horário de verão //-->
                <td align="center"><?= tratarHTML($horario_de_verao_inicio); ?></td>
                <td align="center"><?= tratarHTML($horario_de_verao_fim); ?></td>
                <td align="center">
                    &nbsp;<a href='tabhverao.php?id=<?= tratarHTML($id); ?>&hvi=<?= tratarHTML($horario_de_verao_inicio); ?>&hvf=<?= tratarHTML($horario_de_verao_fim); ?>'><?= tratarHTML($acao); ?></a>&nbsp;
                </td>
            </tr>
            <?php
            
        }

        ?>
    </table>
    <?php
}

/**
 * RECESSO DE FIM DE ANO
 */
function tabRecessoDeFimDeAno()
{
    $oDBase = new DataBase();
    
    $oDBase->setMensagem("Problemas no acesso a Tabela de RECESSO FIM DE ANO (E000127.".__LINE__.")");
    $oDBase->query("
    SELECT 
        IF((DATE_FORMAT(NOW(),'%Y-%m-%d') >= recesso_inicio_compensacao 
            AND DATE_FORMAT(NOW(),'%Y-%m-%d') <= recesso_fim_compensacao),
                DATE_FORMAT(NOW(),'%Y'),'') AS ano, 
        periodo, 
        DATE_FORMAT(recesso_inicio,'%d/%m/%Y') AS recesso_inicio, 
        DATE_FORMAT(recesso_fim,'%d/%m/%Y') AS recesso_fim, 
        DATE_FORMAT(recesso_inicio_compensacao,'%d/%m/%Y') AS recesso_inicio_compensacao, 
        DATE_FORMAT(recesso_fim_compensacao,'%d/%m/%Y') AS recesso_fim_compensacao, 
        ativo 
    FROM 
        tabrecesso_fimdeano 
    ORDER BY 
        periodo DESC 
    ");
    
    ?>
    <div class="col-md-12">
        <div class="col-md-12 text-right">
            <a class="no-style"
               href="javascript:window.location.replace('tabhverao.php');">
                <button type="button" class="btn btn-primary btn-xs">
                    <span class="glyphicon glyphicon-plus"></span> Novo
                </button>
            </a>
        </div>
        <div class="col-md-11 text-right">
            <label for="lot" class="control-label">&nbsp;</label>
        </div>
    </div>

    <table id="myTable" class="table table-striped table-bordered text-center table-condensed tablesorter">
        <tr height='20'>
            <td class='bgtitulo' align="center" rowspan="2" nowrap>&nbsp;PERÍODO&nbsp;</td>
            <td class='bgtitulo' align="center" colspan="6" nowrap>&nbsp;RECESSO DE FIM DE ANO&nbsp;</td>
        </tr>
        <tr>
            <td class='bgtitulo' align="center">&nbsp;INICIO<br>UTILIZAÇÃO&nbsp;</td>
            <td class='bgtitulo' align="center">&nbsp;FIM<br>UTILIZAÇÃO&nbsp;</td>
            <td align='center'><img src='<?= _DIR_IMAGEM_; ?>transp1x1.gif'></td>
            <td class='bgtitulo' align="center">&nbsp;INICIO<br>COMPENSAÇÃO&nbsp;</td>
            <td class='bgtitulo' align="center">&nbsp;FIM<br>COMPENSAÇÃO&nbsp;</td>
            <td class='bgtitulo' align="center">&nbsp;A&Ccedil;&Atilde;O&nbsp;</td>
        </tr>
        <?php
        
        while ($oValida = $oDBase->fetch_object())
        {
            $ano                        = $oValida->ano;
            $periodo                    = $oValida->periodo;
            $recesso_inicio             = $oValida->recesso_inicio;
            $recesso_fim                = $oValida->recesso_fim;
            $recesso_inicio_compensacao = $oValida->recesso_inicio_compensacao;
            $recesso_fim_compensacao    = $oValida->recesso_fim_compensacao;

            if ($ano == date('Y'))
            {
                $classe = 'centro2';
                $acao   = 'Alterar';
            }
            else
            {
                $classe = 'sem_borda';
                $acao   = '';
            }
           
            ?>
            <tr bgcolor="#FFFFFF">
                <!-- periodo //-->
                <td align="center"><?= tratarHTML($periodo); ?></td>
                <!-- Recesso no final de ano //-->
                <td align="center"><?= tratarHTML($recesso_inicio); ?></td>
                <td align="center"><?= tratarHTML($recesso_fim); ?></td>
                <td align='center'><img src='<?= _DIR_IMAGEM_; ?>transp1x1.gif'></td>
                <td align="center"><?= tratarHTML($recesso_inicio_compensacao); ?></td>
                <td align="center"><?= tratarHTML($recesso_fim_compensacao); ?></td>
                <td align="center">
                    &nbsp;<a href='tabrecesso.php?hvi=<?= tratarHTML($recesso_inicio); ?>&hvf=<?= tratarHTML($recesso_fim); ?>'><?= tratarHTML($acao); ?></a>&nbsp;
                </td>
            </tr>
            <?php
            
        }
       
        ?>
    </table>
    <?php
}    


/**
 *  QUARTA-FEIRA DE CINZAS
 */

function tabQuartaFeiraDeCinzas()
{
    $mes = '12'; // último mês do ano
        
    $oDBase = new DataBase();
        
    $oDBase->setMensagem("Problemas no acesso a Tabela de PRAZOS (E000128.".__LINE__.")");
    $oDBase->query("
    SELECT 
        RIGHT(compi,4) AS ano, 
        DATE_FORMAT(qcinzas,'%d/%m/%Y') AS qcinzas, 
        ativo 
    FROM 
        tabvalida 
    WHERE 
        LEFT(compi,2) = '$mes' 
    ORDER BY 
        compi DESC
    ");
    
    ?>
    <div class="col-md-12">
        <div class="col-md-12 text-right">
            <a class="no-style"
               href="javascript:window.location.replace('tabqcinzas.php');">
                <button type="button" class="btn btn-primary btn-xs">
                    <span class="glyphicon glyphicon-plus"></span> Novo
                </button>
            </a>
        </div>
        <div class="col-md-11 text-right">
            <label for="lot" class="control-label">&nbsp;</label>
        </div>
    </div>

    <table id="myTable" class="table table-striped table-bordered text-center table-condensed tablesorter">
        <tr height='20'>
            <td class='bgtitulo' align="center" colspan="3" nowrap>&nbsp;QUARTA FEIRA DE CINZAS&nbsp;</td>
        </tr>
        <tr>
            <td class='bgtitulo' align="center">&nbsp;ANO&nbsp;</td>
            <td class='bgtitulo' align="center">&nbsp;DIA&nbsp;</td>
            <td class='bgtitulo' align="center">&nbsp;A&Ccedil;&Atilde;O&nbsp;</td>
        </tr>
        <?php
        
        while ($oValida = $oDBase->fetch_object())
        {
            $ano     = $oValida->ano;
            $qcinzas = $oValida->qcinzas;

            if ($ano == date('Y'))
            {
                $classe = 'centro2';
                $acao   = 'Alterar';
            }
            else
            {
                $classe = 'sem_borda';
                $acao   = '';
            }
           
            ?>
            <tr bgcolor="#FFFFFF" onmouseover='pinta(1, this)' onmouseout='pinta(2, this)'>
                <!-- ano //-->
                <td align="center"><?= tratarHTML($ano); ?></td>
                <!-- Quarta-feira de cinzas //-->
                <td align="center"><?= tratarHTML($qcinzas); ?></td>
                <td align="center">&nbsp;<a href='tabqcinzas.php?qcinzas=<?= tratarHTML($qcinzas); ?>'><?= tratarHTML($acao); ?></a>&nbsp;</td>
            </tr>
            <?php
            
        }
       
        ?>
    </table>
    <?php
}


/**
 * CICLOS DE BANCO DE HORAS
 */
function tabCiclosDeBancoDeHoras()
{
    $oCiclosBancoDeHoras = new TabBancoDeHorasCiclosController();
    $oCiclosBancoDeHoras->showFormularioLista();
}
