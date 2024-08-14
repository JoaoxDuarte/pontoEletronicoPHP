<?php

include( "../config.php" );

switch ($_SESSION['sModuloPrincipalAcionado'])
{
    case "rh":     
    case "sogp":   
        $tela_login_sistema = "rh.php"; 
        break;
    
    case "chefia": 
        $tela_login_sistema = "chefia.php"; 
        break;
    
    default:
        $tela_login_sistema = "entrada.php"; 
        break;
}

/* Inicia a sessão */
session_start();

// Define o limite de tempo em segundos
$limite = 9; //(60 * _DURACAO_DA_SESSAO_EM_MINUTOS_);

// Verifica se jah esgotou o prazo
if(isset($_SESSION['tempolimite']) )
{
    if ( time() > $_SESSION['tempolimite'])
    {
        $mensagem = 'Sua sessão Expirou!';
        $destino  = $tela_login_sistema;
        unset($_SESSION['tempolimite']);
    }
}
else
{ // Primeira visita
    $_SESSION['tempolimite'] = time() + $limite;
}

$temporestante = $_SESSION['tempolimite'] - time();

//$mensagem = '<b>Sessão expira em:</b> ' . sec_to_time(abs($temporestante),'mm:ss') . ' (mm:ss)'; 

//echo "As sessões em cache irão expirar em $temporestante segundos";
//echo json_encode(array("mensagem" => utf8_encode($mensagem), "tipo" => "warning", "destino" => $destino));

//exit();

?>
<script Language="JavaScript">
    var hora;
    var muda = 1;
    var tempo = new Number();
    tempo = <?= $temporestante; ?>;


    function timeLogoutConfirm(destino)
    {
        var destino = (destino == null ? 'entrada.php' : destino);
        
        if (window.top !== window.self) 
        {
            window.parent.location.reload();
            return false;
        }
        else
        {
            window.location.replace( destino );
            return false;
        }
    }


    function iniciaLogout()
    {
        if((tempo - 1) >= 0)
        {
            var min = parseInt(tempo/60);
            var seg = tempo%60;
	
            if(min < 10)
            {
                min = '0'+min;
                min = min.substr(0, 2);
            }
            
            if(seg <=9)
            {
                seg = "0"+seg;
            }
            
            hora = min + ':' + seg;
            
            $('#tempo_decorrido').html( '<b>Sessão expira em:</b> ' + hora + ' (mm:ss)' );
	
            //if (hora === '00:03')
            if (hora === '00:00')
            {
                alert( 'Sua sessão Expirou!' );
                
                //var myLogoutConfirm = setTimeout( function() { timeLogoutConfirm('entrada.php'); }, 10000 );
                //if (confirm( 'Sua sessão irá Expirar, deseja renová-la?' ))
                //{
                //    tempo = <?= $limite; ?>;
                //    clearTimeout(myLogoutConfirm);
                //}
                //else if (window.top !== window.self) 
                if (window.top !== window.self) 
                {
                    window.parent.location.reload();
                    return false;
                }
                else
                {
                    window.location.replace('entrada.php');
                    return false;
                }
        }

            setTimeout('iniciaLogout()',1000);

            //if((tempo - 1) <= 25)
            //{
            //	 if(muda == 1){
            //		$("#tempo_decorrido").css('color', 'red').css('font-weight', 'bold');
            //		muda = 0;
            //	 }else{
            //		$("#tempo_decorrido").css('color', 'white').css('font-weight', 'normal');
            //		muda = 1;
            //	 }
            //}
	
            tempo--;
        }
        else
        {
            //$("#tempo_decorrido").html('00:00');
            alert( 'Sua sessão Expirou!' );
            window.location.href = '<?= $tela_login_sistema; ?>';
        }
    }
        
    iniciaLogout();
    
</script>
<?php
/*
<script Language="JavaScript">
                var limite = "<?= _DURACAO_DA_SESSAO_EM_MINUTOS_; ?>";
                var startTime = 0;
                var start = 0;
                var end = 0;
                var diff = 0;
                var timerID = 0;

                function chrono()
                {
                    start = new Date()
                    diff = end - start
                    diff = new Date(diff)

                    var msec = diff.getMilliseconds();
                    var sec = diff.getSeconds();
                    var min = diff.getMinutes();
                    var hr = diff.getHours();

                    // tempo limite
                    min -= (60 - limite);

                    if (min < 10) { min = '0' + min; }
                    if (sec < 10) { sec = '0' + sec; }
                    if (msec < 10) { msec = '00' +msec; }
                    else if (msec < 100) { msec = '0' +msec; }

                    $('#tempo_decorrido').html( '<b>Sessão expira em:</b> ' + min + ':' + sec + ' (mm:ss)' );

                    if (min === '00' && sec === '00')
                    {
                        alert( 'Sua sessão Expirou!' );
                        window.location.href = "<?= $tela_login_sistema; ?>"';
                    }
                    else
                    {
                        timerID = setTimeout('chrono()', 10);
                    }
                }

                end = new Date();
                
                //chrono();
</script>
*/

