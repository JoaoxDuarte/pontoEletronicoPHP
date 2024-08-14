<?php

include_once("config.php");


$oForm = new formPadrao();
$oForm->setCSS('css/new/sorter/css/theme.bootstrap_3.min.css');
$oForm->setJS('css/new/sorter/js/jquery.tablesorter.min.js');
$oForm->setSubTitulo( "Manual" );
$oForm->setSeparador(0);

$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

?>
<style>
.tree {
    min-height:20px;
    padding:19px;
    margin-bottom:20px;
    background-color:#fbfbfb;
    border:1px solid #999;
    -webkit-border-radius:4px;
    -moz-border-radius:4px;
    border-radius:4px;
    -webkit-box-shadow:inset 0 1px 1px rgba(0, 0, 0, 0.05);
    -moz-box-shadow:inset 0 1px 1px rgba(0, 0, 0, 0.05);
    box-shadow:inset 0 1px 1px rgba(0, 0, 0, 0.05)
}
.tree li {
    list-style-type:none;
    margin:0;
    padding:10px 5px 0 5px;
    position:relative
}
.tree li::before, .tree li::after {
    content:'';
    left:-20px;
    position:absolute;
    right:auto
}
.tree li::before {
    border-left:1px solid #999;
    bottom:50px;
    height:100%;
    top:0;
    width:1px
}
.tree li::after {
    border-top:1px solid #999;
    height:20px;
    top:25px;
    width:25px
}
.tree li span {
    -moz-border-radius:5px;
    -webkit-border-radius:5px;
    border:1px solid #999;
    border-radius:5px;
    display:inline-block;
    padding:3px 8px;
    text-decoration:none
}
.tree li.parent_li>span {
    cursor:pointer
}
.tree>ul>li::before, .tree>ul>li::after {
    border:0
}
.tree li:last-child::before {
    height:30px
}
.tree li.parent_li>span:hover, .tree li.parent_li>span:hover+ul li span {
    background:#eee;
    border:1px solid #94a0b4;
    color:#000
}
</style>

<script>
$(function () {
    $('.tree li:has(ul)').addClass('parent_li').find(' > span').attr('title', 'Collapse this branch');
    $('.tree li.parent_li > span').on('click', function (e) {
        var children = $(this).parent('li.parent_li').find(' > ul > li');
        if (children.is(":visible")) {
            children.hide('fast');
            $(this).attr('title', 'Expand this branch').find(' > i').addClass('icon-plus-sign').removeClass('icon-minus-sign');
        } else {
            children.show('fast');
            $(this).attr('title', 'Collapse this branch').find(' > i').addClass('icon-minus-sign').removeClass('icon-plus-sign');
        }
        e.stopPropagation();
    });
});
</script>

<div class="container">
    <div class="row margin-10">
        <div class="col-md-12">

        <p style='text-indent:50px;text-align:justify;'>
            O <i>Sistema de Registro Eletr�nico de Frequ�ncia - SISREF</i>, solu��o
            tecnol�gica para controle de frequ�ncia com codifica��o para
            realiza��o do controle de entradas, sa�das e aus�ncias dos
            servidores, alinhado aos normativos vigentes como a <b>Instru��o
            Normativa MP n� 02/2018</b>. Foi desenvolvido originalmente pelo
            <i>Instituto Nacional do Seguro Social � INSS</i> e evolu�do pelo
            <i>Minist�rio da Economia</i> por interm�dio da <i>Secretaria de Gest�o
            e Desempenho de Pessoal</i>, com base em Acordo de Coopera��o T�cnica, com vistas
            a disponibiliza��o aos �rg�os e entidades integrantes do <i>Sistema
            de Pessoal Civil da Administra��o Federal - SIPEC</i>.
        </p>

        <p style='text-indent:50px;text-align:justify;'>
            O sistema possui tr�s m�dulos, um de servidores para o registro
            de comparecimento, outro de chefias para acompanhamento da frequ�ncia
            de sua equipe e o de Gest�o de Pessoas para o controle de frequ�ncia
            dos servidores vinculados � UPAG.
        </p>

        <p style='text-align:justify;'>
            <font style='padding-left:50px;'>Para acesso ao manual operacional do servidor <a href='manual/Manual_operacional_do_perfil_SERVIDOR_v5.4.pdf'>clique aqui</a>.</font><br>
            <font style='padding-left:50px;'>Para acesso ao manual operacional da chefia <a href='manual/Manual_operacional_do_perfil_CHEFIA_v5.4.pdf'>clique aqui</a>.</font><br>
            <font style='padding-left:50px;'>O manual operacional de gest�o de pessoas � disponibilizado no curso de capacita��o.</font><br>
        </p>

        <p style='text-align:justify;padding-top:10px;'>
            <u><b>Legisla��o</b></u>:
        </p>
        <p style='text-align:justify;padding-left:50px;'>
            Lei n� 8.112, de 11 de dezembro de 1990<br>
            Decreto n� 1.590, de 10 de agosto de 1995<br>
            Decreto n� 1.867, de 17 de abril de 1996<br>
            Instru��o Normativa n� 2, de 12 de setembro de 2018<br>
            Orienta��o normativa N� 2, de 16 de outubro de 2018<br>
        </p>

        </div>
    </div>
</div>
<?php

// Base do formul�rio
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
