<?php

include_once( 'config.php' );

verifica_permissao('administrador');

// parametros passados por formulario
$sSiape = addslashes($_POST['sSiape']);
$sSetor = addslashes($_POST['sSetor']);

$aSetor = explode('|', $sSetor);

$sSetor = $aSetor[0];
$sUpag  = $aSetor[1];

$modo = addslashes($_POST['modo']);
$C00  = $_POST['C00'];

/* --------------------------------------------------------------------
 * Esta rotina tem uma lógica bem legal.
 * No form (usuarios.php) o usuário dá as permissões para os usuários
 * do sistema. Eu coloquei um C[] com value 45 e com status hidden.
 * Isso evita que se não for checado o último checkbox, não gere um
 * erro de não definição de variável.
 *
 * Agora a parte legal da coisa. Nessa rotina (confirmausuario.php)
 * eu recebo os checks do outro form em forma de vetor, extraio o seus
 * keys() e jogo num vetor auxiliar. Depois, leio o vetor e completo
 * os valores ocultos para saber se estao checados ou não.
 * Por exemplo:
 *
 * Vamos supor que os valores checados foram 01|02|05|06, então o vetor
 * recebido terá a string -> 01020506. O algoritmo pega e faz uma compa-
 * ração dos elementos extraidos:
 *
 * se key(atual) > que o key(anterior)
 *   entao completa até chegar ao valor atual
 * senao
 *   deixa como está.
 *
 * No fim, terei então uma string bem grande (com todas as posições) e
 * seus respectivos flags (SSNNSSNNNNN....) até a última posição. Aí, é
 * só gravar no banco para depois recuperar na hora do log do usuário.
 *
 *
 */
$auxf = "";
$C    = array();
for ($i = 1; $i <= $C00; $i++)
{
    $temp = $_POST['C' . substr('00' . $i, -2)];
    $auxf .= (empty($temp) ? "N" : "S");
}
for ($i = ($C00 + 1); $i <= 45; $i++)
{
    //$auxf .= "N";
}

// verifica se os dados foram enviados
$result = array();
if (empty($sSiape))
{
    $mensagem = 'Dados incompletos!';
}
else
{
    // mostra vars
    $oDBase = new DataBase('PDO');

    // dados do servidor
    $oDBase->query('
    SELECT
        a.nome_serv AS nome, DATE_FORMAT(a.dt_nasc,"%d%m%Y") AS dt_nasc, b.codigo, b.upag
    FROM
        servativ AS a
    LEFT JOIN
        tabsetor AS b ON a.cod_lot = b.codigo
    WHERE
        a.excluido = "N"
        AND a.mat_siape = :siape
    ',
    array(
        array( ':siape', $sSiape, PDO::PARAM_STR ),
    ));
    $oServidor = $oDBase->fetch_object();
    $sNome  = $oServidor->nome;
    $sSetor = $oServidor->codigo;
    $sUpag  = $oServidor->upag;

    $sSenha    = substr(md5($oServidor->dt_nasc), 0, 14); // criptografa a senha para novo usuário

    // upag do setor destino
    /*
      $oDBase->query( "SELECT b.upag FROM tabsetor AS b WHERE b.codigo='$sSetor' " );
      $oSetor = $oDBase->fetch_object();
      $sUpag = $oSetor->upag;
     */

    // verifica se já está cadastrado
    $oDBase->query('
    SELECT
        siape
    FROM
        usuarios
    WHERE
        siape = :siape
    ',
    array(
        array( ':siape', $sSiape, PDO::PARAM_STR ),
    ));
    $numrows = $oDBase->num_rows();

    // inclusão ou alteração
    switch ($modo)
    {
        // INCLUSÃO DO PERFIL
        case 1:
            if ($numrows > 0)
            {
                $mensagem = utf8_iso88591('Servidor já cadastrado!');
            }
            else
            {
                // Cadastra usuário
                $oDBase->query('
                INSERT INTO usuarios
                SET
                    siape = :siape,
                    senha = :senha,
                    setor = :setor,
                    upag  = :upag
                ',
                array(
                    array( ':siape', $sSiape, PDO::PARAM_STR ),
                    array( ':senha', $sSenha, PDO::PARAM_STR ),
                    array( ':setor', $sSetor, PDO::PARAM_STR ),
                    array( ':upag',  $sUpag,  PDO::PARAM_STR ),
                ));
                $mensagem = 'Dados incluídos com sucesso!';

                // grava o LOG
                registraLog('O usuário ' . $_SESSION['sMatricula'] . ' criou um perfil de acesso para ' . strtoupper($lNome), $sSiape, $lNome);
            }
            break;

        // ALTERAÇÃO DO PERFIL
        case 2:
            if ($numrows == 0)
            {
                $mensagem = utf8_iso88591('Usuário não registrado!');
            }
            else
            {
                // grava o perfil do usuario
                $oDBase->query('
                UPDATE usuarios
                SET
                    acesso = :acesso,
                    setor  = :setor,
                    upag   = :upag
                WHERE
                    siape = :siape
                ',
                array(
                    array( ':acesso', $auxf,   PDO::PARAM_STR ),
                    array( ':setor',  $sSetor, PDO::PARAM_STR ),
                    array( ':upag',   $sUpag,  PDO::PARAM_STR ),
                    array( ':siape',  $sSiape, PDO::PARAM_STR ),
                ));
                $mensagem = 'Dados gravados com sucesso!';

                // grava o LOG
                registraLog('O usuário ' . $_SESSION['sMatricula'] . ' alterou as permissões de acesso do(a) ' . strtoupper($lNome), $sSiape, $lNome);

                if ($sSiape == $_SESSION['sMatricula'])
                {
                    $sMatricula  = $_SESSION['sMatricula'];
                    $logado      = $_SESSION['logado'];
                    $sNome       = $_SESSION['sNome'];
                    $sSenha      = $_SESSION['sSenha'];
                    $sLotacao    = $_SESSION['sLotacao'];
                    $sNomeGex    = $_SESSION['sNomeGex'];
                    $sNomeUF     = $_SESSION['sNomeUF'];
                    $sNomeGER    = $_SESSION['sNomeGER'];
                    $sCodGER     = $_SESSION['sCodGER'];
                    $sPrivilegio = $_SESSION['sPrivilegio'];

                    $upag     = $_SESSION['upag'];  // Unidade pagadora, código SIAPE
                    // Prazos
                    $sMesi    = $_SESSION['sMesi']; // Mês e Ano de competencia, referencia para o cronograma do mês em curso
                    $sMesf    = $_SESSION['sMesf']; // Sem uso
                    $sRhi     = $_SESSION['sRhi'];  // Início do período de atuação do RH
                    $sRhf     = $_SESSION['sRhf'];  // Fim do período autorizado ao RH para manusear aquele mês
                    $sApsi    = $_SESSION['sApsi']; // Homologação: data inicial
                    $sApsf    = $_SESSION['sApsf']; // Homologação: data final
                    $sGbnini  = $_SESSION['sGbnini'];  // ???
                    $sGbninf  = $_SESSION['sGbninf'];  // ???
                    $sOutchei = $_SESSION['sOutchei']; // ???
                    $sOutchef = $_SESSION['sOutchef']; // ???
                    $sRmi     = $_SESSION['sRmi'];       // ???
                    $sRmf     = $_SESSION['sRmf'];       // ???
                    $sCadi    = $_SESSION['sCadi'];      // ???
                    $sCadf    = $_SESSION['sCadf'];      // ???
                    $magico   = $_SESSION['magico'];     // ???
                    $iniver   = $_SESSION['iniver'];     // Início do horário de verão
                    $fimver   = $_SESSION['fimver'];     // Fim do horário de verão
                    $qcinzas  = $_SESSION['qcinzas'];   // Quarta-feira de cinzas

                    // Modulos, permissoes, valor
                    $modulos  = array();
                    $modulos  = select_permissoes();

                    // registra na seção as novas permissões do usuario atual
                    $sTripa = $auxf;

                    include_once('usuario_varsession.php');
                }
            }
            break;

        // nenhuma das opções acima
        default:
            $mensagem = utf8_iso88591('Falha na gravação dos dados!');
            break;
    }
}

$result[0] = array(
    'aviso' => $mensagem
);

$myData = array('dados' => $result);

print json_encode($myData);
