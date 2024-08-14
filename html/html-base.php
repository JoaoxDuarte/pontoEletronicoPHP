<!DOCTYPE html>
<html lang='pt-br'>
    <head>
        <title><?= $title; ?></title>
        <meta http-equiv='Content-Language' content='pt-br'>
        <meta http-equiv='Content-Type' content='text/html; charset=windows-1252'>
        <meta http-equiv='X-UA-Compatible' content='IE=Edge'/>
        <meta http-equiv='Pragma' content='no-cache'>
        <meta http-equiv='Expires' content='-1'>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <?php
        $path_parts       = pathinfo($_SERVER['PHP_SELF']);
        $pagina_de_origem = $path_parts['filename'];

        if ($pagina_de_origem != 'desativado_js')
        {
            ?>
            <noscript>  <meta http-equiv="Refresh" content="1;url=inc/desativado_js.php"></noscript>
            <?php
        }
        ?>

        <link type="text/css" rel="stylesheet" href="css/new/css/bootstrap.min.css">
        <link type="text/css" rel="stylesheet" href="css/new/css/custom.css?v1.0.0.0.0.1">
        <link type="text/css" rel="stylesheet" href="css/estilos_new_layout.css">
        <link type="text/css" rel="stylesheet" href="css/new/css/bootstrap-dialog.min.css">
        <link type="text/css" rel="stylesheet" href="css/bootstrap-print-small.css?v.0.0.0.0.0.1" media='print'>
        <!--<link type="text/css" rel="stylesheet" href="css/new/js/bootstrap-table/bootstrap-table.css">-->

        <!-- Style's extras ou específicos -->
        <?php
        if (is_array($css) && count($css) > 0)
        {
            foreach ($css as $cs)
            {
                if (substr_count($cs, "<style") > 0)
                {
                    print $cs;
                }
                else
                {
                    ?>
                    <link type="text/css" rel="stylesheet" href="<?= $cs; ?>">
                    <?php
                }
             }
        }
        ?>

        <!-- Style's geral -->
        <style>
            header {
                position: fixed top;
                width: 100%;
                padding: 0px;
                padding-bottom: 40px;
                border: 0px solid #ccc;
                height: 31px;
                margin: 0px 0px 0px 0px;
                background: transparent;
            }
            container {
                width: 100%;
                color: #333;
                border: 1px solid #ccc;
                background: blue;
                margin: 0px 0px 0px 0px;
                padding: 10px;
                height: 350px;
            }
            /*
            footer {
                position: fixed bottom;
                width: 100%;
                height:25px;
                color: #333333;
                text-align:center;
                vertical-align:middle;
                background:white;
                margin: 0px 0px 0px 0px;
                padding: 5px 0px 0px 0px;
                border-top: 1px solid #EEEEEE;
            }
            */

            /* Modificar algumas cores do menu */
            .navbar-nav > li > .dropdown-menu { background-color: black; }
            .navbar-default .navbar-nav > .open a:hover {
                background-color: #2c2c2c;
                color: white;
                -webkit-border-radius: 4px 4px 4px 4px;
                border-radius: 4px 4px 4px 4px;
            }
            .navbar-default .navbar-nav > .open > a,
            .navbar-default .navbar-nav > .open > a:focus,
            .navbar-default .navbar-nav > .open > a:hover {
                background-color: #2c2c2c;
                color: white;
                -webkit-border-radius: 4px 4px 4px 4px;
                border-radius: 4px 4px 4px 4px;
            }
            .dropdown-menu {
                background-color: black;
            }
            .dropdown-menu > li > a  { background-color: black; }

            /* destaca linha selecionada */
            .table-hover > tbody > tr:hover > td,
            .table-hover > tbody > tr:hover > th {
                background-color: #e6e6e6;
                color:#000000;
            }

            /* não destaca linha com "nohover" */
            tr.nohover:hover > td,
            tr.nohover:hover > th {
                background-color: transparent;
                color:none;
            }
            .noborder {
                border-width:0px;
            }

        </style>

        <script type='text/javascript' src="<?= _DIR_JS_; ?>jquery-2.2.0.min.js"></script>
        <script type='text/javascript' src="<?= _DIR_JS_; ?>funcoes.js?v.0.0.0.0.0.1"></script>
        <script type='text/javascript' src="<?= _DIR_JS_; ?>fc_data.js"></script>
        <script type='text/javascript' src="<?= _DIR_CSS_; ?>new/js/bootstrap.min.js"></script>
        <script type='text/javascript' src="<?= _DIR_CSS_; ?>new/js/bootstrap-dialog.min.js"></script>
        <script type='text/javascript' src="<?= _DIR_JS_; ?>bootbox/bootbox.min.js"></script>
        <script type='text/javascript' src="<?= _DIR_JS_; ?>bootbox/bootbox.locales.min.js"></script>

        <!--[if lt IE 9]>
        <script src="js/html5shiv.js"></script>
        <script src="js/respond.min.js"></script>
        <![endif]-->

        <!-- Script's extras ou específicos -->
        <?php
        if (is_array($javascript) && count($javascript) > 0)
        {
            foreach ($javascript as $js)
            {
                if (substr_count($js, "<script") > 0)
                {
                    print $js;
                }
                else
                {
                    ?>
                    <script type='text/javascript' src="<?= $js; ?>"></script>
                    <?php
                }
            }
        }
        ?>
        <script>
            var voltarOrigem = "<?= $_SESSION['sHOrigem_1']; ?>";
        </script>
        <?php

        $_DURACAO_DA_SESSAO_EM_MINUTOS_ = getDuracaoDaSessaoEmMinutos();

        $paginas_array = array( 'entrada_mnu', 'rh_mnu', 'chefia_mnu', 'rh', 'chefia', 'entrada' );
        
        $pathinfo = pathinfo($_SERVER['REQUEST_URI']);
        $basename = explode('.',(empty($pathinfo['basename']) ? 'x' : $pathinfo['basename']));
        $origem   = $basename[0];

        if ( !in_array($origem,$paginas_array) )
        {
            ?>
            <script>
                function EncerrarSessaoPHPBrowserEncerrado()
                {
                    $.ajax({
                        url: "inc/tempo_sessao_encerrar.php",
                        type: "POST",
                        data: 'tempo=<?= $_DURACAO_DA_SESSAO_EM_MINUTOS_; ?>',
                        dataType: "json"

                    }).done(function(resultado) {
                        //console.log(resultado.mensagem + ' | ' + resultado.tipo);
                        mostraMensagem('Sessão Encerrada!', 'warning', 'login', null);

                    }).fail(function(jqXHR, textStatus ) {
                        console.log("Request failed: " + textStatus);

                    }).always(function() {
                        console.log("completou");

                    });
                }
                
                var intervalActivity = null;
                
                function attLastActivity() 
                {
                    $.ajax({
                        url: "inc/tempo_sessao_verificar.php",
                        type: "POST",
                        data: 'contar=sim&tempo=<?= $_DURACAO_DA_SESSAO_EM_MINUTOS_; ?>',
                        dataType: "json"

                    }).done(function(resultado) {
                        console.log(resultado.hora + ' | ' + resultado.tipo);
                        $('#tempo_decorrido').html( "Sessao expira em: " + resultado.hora + " (mm:ss)" );
            
                        if (resultado.hora === '00:00')
                        {
                            clearInterval( intervalActivity );
                            mostraMensagem('Sua sessão Expirou!', 'warning', 'login', null);
                            return false;
                        }

                    }).fail(function(jqXHR, textStatus ) {
                        console.log("Request failed: " + textStatus);

                    }).always(function() {
                        console.log("completou");
                        
                    });
                }

                function verificarTempoDeSessao()
                {
                    intervalActivity = setInterval(attLastActivity, 1000); //Faz uma requisição a cada 30 segundos
                }
                
                //verificarTempoDeSessao();
                
            </script>
            <?php
        }
        
        ?>
        
<!-- Piwik -->
<script type="text/javascript">
  var _paq = _paq || [];
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u="//nephila.serpro.gov.br/";
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', 256]);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
<noscript><p><img src="//nephila.serpro.gov.br/piwik.php?idsite=256" style="border:0;" alt="" /></p></noscript>
<!-- End Piwik Code -->

    </head>
    <body>
        <div id='block_tabela_principal'></div>