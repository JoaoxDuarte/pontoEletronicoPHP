<?php
//Rotina a ser rodada sempre ultimo dia de cada mes quando ativado o mes

include_once("config.php");
include_once("src/controllers/TabBancoDeHorasCiclosController.php");

verifica_permissao("sRH");

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
        <button class="tablinks" onclick="openCity(event, 'qui')">CICLOS DE BANCO DE HORAS</button>
    </div>
</div>
        
<!-- Tab content -->
    
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
 * CICLOS DE BANCO DE HORAS
 */
function tabCiclosDeBancoDeHoras()
{
    $oCiclosBancoDeHoras = new TabBancoDeHorasCiclosController();
    $oCiclosBancoDeHoras->showFormularioLista("tabciclos");
}