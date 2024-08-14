<?php

include_once( "config.php" );

if(!empty($_GET['saveip'])) {
    $_SESSION['ip_servidor'] = $_GET['ip'];
    die;
}


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->setSubTitulo('Teste');
$oForm->setSeparador(0);

$oForm->exibeTopoHTML();

?>
<script>
$(document).ready(function ()
{
    getIp(function (ip) {
        $.get(
            "teste.php",
            "ip=" + ip +
            "&saveip=true",
            function (data) {
                return false;
            }
        );
    });
});

function getIp(callback)
{
    function response(s)
    {
        callback(window.userip);

        s.onload = s.onerror = null;
        document.body.removeChild(s);
    }

    function trigger()
    {
        window.userip = false;

        var s = document.createElement("script");
        s.async = true;
        s.onload = function() {
            response(s);
        };
        s.onerror = function() {
            response(s);
        };

        s.src = "https://l2.io/ip.js?var=userip";
        document.body.appendChild(s);
    }

    if (/^(interactive|complete)$/i.test(document.readyState)) {
        trigger();
    } else {
        document.addEventListener('DOMContentLoaded', trigger);
    }
}
</script>

<?php

    echo '<br><br><u>Usando CURL</u><br><br>';
if (file_exists("http://meuip.com/api/meuip.php"))
{
    //criando o recurso cURL
    $cr = curl_init();

    //definindo a url de busca
    curl_setopt($cr, CURLOPT_URL, "http://meuip.com/api/meuip.php");

    //definindo a url de busca
    curl_setopt($cr, CURLOPT_RETURNTRANSFER, false);

    //definindo uma variável para receber o conteúdo da página...
    $retorno = curl_exec($cr);

    //fechando-o para liberação do sistema.
    curl_close($cr); //fechamos o recurso e liberamos o sistema...

    //mostrando o conteúdo...
    echo '<br><br><u>Usando CURL</u>';
    echo '<br><br>http://meuip.com/api/meuip.php: '.$retorno;
}
else
{
    print "[http://meuip.com/api/meuip.php] Não localizado (problemas com a função CURL).<br><br>";
}



if (file_exists("https://l2.io/ip.js"))
{
    //criando o recurso cURL
    $cr = curl_init();

    //definindo a url de busca
    curl_setopt($cr, CURLOPT_URL, "https://l2.io/ip.js?var=userip");

    //definindo a url de busca
    curl_setopt($cr, CURLOPT_RETURNTRANSFER, true);

    //definindo uma variável para receber o conteúdo da página...
    $retorno = curl_exec($cr);

    //fechando-o para liberação do sistema.
    curl_close($cr); //fechamos o recurso e liberamos o sistema...

    //mostrando o conteúdo...
    $vetor = explode('=',$retorno);
    $ip = trim(strtr($vetor[1], array('"'=>'',"'"=>'',';'=>'')));
    echo '<br><br>https://l2.io/ip.js: '.$ip;
}
else
{
    print "[https://l2.io/ip.js] Não localizado (problemas com a função CURL).<br><br>";
}


echo '<br><br><u>Usando file_get_contents()</u>';
$ip = file_get_contents('http://meuip.com/api/meuip.php');
echo '<br><br>http://meuip.com/api/meuip.php: '.(empty($ip) ? 'Página não localizada' : $ip);

$result = file_get_contents('https://l2.io/ip.js?var=userip');
$vetor = explode('=',$result);
$ip = trim(strtr($vetor[1], array('"'=>'',"'"=>'',';'=>'')));
echo '<br><br>https://l2.io/ip.js?var=userip: '.$ip;

echo '<br><br><br><br><u>Usando javascript (I2.io)</u>';
echo '<br><br>https://l2.io/ip.js?var=userip: '.$_SESSION['ip_servidor'];


echo '<br><br><br><br><u>getIpReal()</u>:<br><br>';
echo 'HTTP_CLIENT_IP: ' . $_SERVER['HTTP_CLIENT_IP'] . '<br>';
echo 'HTTP_X_FORWARDED_FOR: ' . $_SERVER['HTTP_X_FORWARDED_FOR'] . '<br>';
echo 'HTTP_X_FORWARDED: ' . $_SERVER['HTTP_X_FORWARDED'] . '<br>';
echo 'HTTP_FORWARDED_FOR: ' . $_SERVER['HTTP_FORWARDED_FOR'] . '<br>';
echo 'HTTP_FORWARDED: ' . $_SERVER['HTTP_FORWARDED'] . '<br>';
echo 'HTTP_X_COMING_FROM: ' . $_SERVER['HTTP_X_COMING_FROM'] . '<br>';
echo 'HTTP_COMING_FROM: ' . $_SERVER['HTTP_COMING_FROM'] . '<br>';
echo 'REMOTE_ADDR: ' . $_SERVER['REMOTE_ADDR'] . '<br>';
echo 'REMOTE_ADDR: ' . getenv('REMOTE_ADDR') . '<br>';

$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();

?>