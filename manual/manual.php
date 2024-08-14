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
            O <i>Sistema de Registro Eletrônico de Frequência - SISREF</i>, solução
            tecnológica para controle de frequência com codificação para
            realização do controle de entradas, saídas e ausências dos
            servidores, alinhado aos normativos vigentes como a <b>Instrução
            Normativa MP nº 02/2018</b>. Foi desenvolvido originalmente pelo
            <i>Instituto Nacional do Seguro Social – INSS</i> e evoluído pelo
            <i>Ministério da Economia</i> por intermédio da <i>Secretaria de Gestão
            e Desempenho de Pessoal</i>, com base em Acordo de Cooperação Técnica, com vistas
            a disponibilização aos órgãos e entidades integrantes do <i>Sistema
            de Pessoal Civil da Administração Federal - SIPEC</i>.
        </p>

        <p style='text-indent:50px;text-align:justify;'>
            O sistema possui três módulos, um de servidores para o registro
            de comparecimento, outro de chefias para acompanhamento da frequência
            de sua equipe e o de Gestão de Pessoas para o controle de frequência
            dos servidores vinculados à UPAG.
        </p>

        <p style='text-align:justify;'>
            <font style='padding-left:50px;'>Para acesso ao manual operacional do servidor <a href='manual/Manual_operacional_do_perfil_SERVIDOR_v5.4.pdf'>clique aqui</a>.</font><br>
            <font style='padding-left:50px;'>Para acesso ao manual operacional da chefia <a href='manual/Manual_operacional_do_perfil_CHEFIA_v5.4.pdf'>clique aqui</a>.</font><br>
            <font style='padding-left:50px;'>O manual operacional de gestão de pessoas é disponibilizado no curso de capacitação.</font><br>
        </p>

        <p style='text-align:justify;padding-top:10px;'>
            <u><b>Legislação</b></u>:
        </p>
        <p style='text-align:justify;padding-left:50px;'>
            Lei nº 8.112, de 11 de dezembro de 1990<br>
            Decreto nº 1.590, de 10 de agosto de 1995<br>
            Decreto nº 1.867, de 17 de abril de 1996<br>
            Instrução Normativa nº 2, de 12 de setembro de 2018<br>
            Orientação normativa Nº 2, de 16 de outubro de 2018<br>
        </p>

        </div>
    </div>
</div>
<?php

// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
