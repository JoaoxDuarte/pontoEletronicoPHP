<?php

include_once( "config.php" );

?>
<style>
    footer {
        position: absolute;
        margin-top: 20px;
        bottom: 0px;
        width: 100%;
        height: 60px;
    }
</style>

<div>&nbsp;</div>
<footer class="fixed-bottom-x" style="background-color:<?= ($_SESSION['sHOrigem_1'] === 'entrada.php' || $_SESSION['sModuloPrincipalAcionado'] === 'entrada' ? '#004080' : '#0c691c' ); ?>">
    <p style="text-align:center;margin:0px 0px 0px 0px;padding:0px 0px 0px 0px;color:#FFFFFF;">

        <?php if (_SISTEMA_ORGAO_ === '57202'): ?>

            Instituto Nacional do Seguro Social - INSS | SAUS S/N Bloco O - 10º andar - Asa Sul - Brasília - DF - 70070-946 | (61) 3313-4064<br>

        <?php else: ?>

            Secretaria de Gestão e Desempenho de Pessoal - SGP | Esplanada dos Ministérios - Bloco C - 7º andar - Brasília - DF - 70046-900 | Telefone 0800 9789009<br>

        <?php endif; ?>

        Desenvolvido em Acordo de Cooperação Técnica INSS e ME | Versão 2.0.0.52 (2020-06-26 08:00:00) &copy;

        </p>
        <div id="tempo_decorrido" class="text-center" style="color:white;padding-top:0px;"></div>
</footer>

<div id="renderPDF" style="margin:0px;padding:0px;"></div>

<div class="loading-spinner" style='z-index:95000;'>
    <div id="progresso_sse_center_processando" class="loading-center-imagem-processando">
        <h3><img src="imagem/sisref.gif"/><br><small>Processando...</small><br><img src="imagem/loading.gif"/></h3>
    </div>
</div>

</body>
</html>
