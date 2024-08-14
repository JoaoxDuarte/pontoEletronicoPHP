<?php

include_once("config.php");
include_once("class_form.frequencia.php");

verifica_permissao("sRH");

// dado enviado por formulario
$modo   = anti_injection($_REQUEST['modo']);
$comp   = trata_aspas(anti_injection($_REQUEST['comp']));
$motivo = trata_aspas($_REQUEST['motivo']);

// Valores passados - encriptados
$dadosorigem = anti_injection($_POST['dados']);

if (empty($dadosorigem))
{
    header("Location: acessonegado.php");
}
else
{
    $dados = explode(":|:", base64_decode($dadosorigem));
    $siape = $dados[0];
}

$comp_invertida = $comp;

//linha que captura o ip do usuario.
$ip = getIpReal();

// dados em sessão
$sMatricula = $_SESSION["sMatricula"];

// data atual - formato americano
$oData = new trata_datasys();
$ano   = $oData->getAnoHomologacao();
$mes   = $oData->getMesHomologacao();

// data atual - formato americano
$data_desomologacao = date("Y-m-d");

$dthomol = date("Y-m-d");

// SQL e parametros para pesquisa
$oDBase = selecionaDadosServidorHomologado($siape, $com_invertida);

$rows          = $oDBase->num_rows();
$oDados        = $oDBase->fetch_object();
$nome          = $oDados->nome_serv; // nome do servidor
$freqh         = $oDados->freqh;     // situação da frequência (homologada: sim ou não)
$lot           = $oDados->cod_lot;   // unidade de lotação do servidor
$upg           = $oDados->upag;      // obtem dados da upag para saber se é a mesma do usuario
$codlot_chefia = $oDados->uorg_pai; // unidade de lotação da chefia imediata
$chefia        = $oDados->chefia;   // se eh ocupante de função (sim ou não)

$tipo = "danger";

// frequencia homologada, desomologa e encaminha email
if ($rows > 0)
{
    // verifica se o usuario logado pertence a mesma upag
    if ($_SESSION['sRH'] == "S" && $upg != $_SESSION['upag'])
    {
        $mensagem = "Servidor/estagiário de outra UPAG!";
        retornaInformacao($mensagem,$tipo);
    }

    //obtendo email do servidor de rh que esta devolvendo a frequencia
    $oDados = carregaEmailDoUsuarioLogado();
    
    $nomerh                  = $oDados->nome_serv;
    $emailrh                 = $oDados->email;
    $dataLimiteReHomologacao = dataLimiteReHomologacao($dthomol, $lotacao);

    
    // obtendo email do chefe (titular e substituto)
    if ($chefia == 'S')
    {
        $emails_para = emailChefiaTitularSubstituto($codlot_chefia);
        $lotacao     = $codlot_chefia;
    }
    else
    {
        $emails_para = emailChefiaTitularSubstituto($lot);
        $lotacao     = $lot;
    }

    $emails_para .= ($emails_para == "" ? "" : ",") . $emailrh;

    $dataLimiteReHomologacao = dataLimiteReHomologacao($dthomol, $lotacao);

    
    // atualiza o cadastro,
    // homologados e setor
    atualizaCadastroSetorHomologacao($siape, $motivo, $comp_invertida, $dthomol);

    // verifica a existencia dos emails
    // necessários para o envio da mensagem
    if ($emails_para == "" || $emails_para == $emailrh)
    {
        $mensagem = "Devolução da frequência realizada com sucesso!\\n"
            . "Por favor, informe a chefia que a frequência do(a)\\n"
            . "servidor(a) $nome foi devolvida.\\n"
            . "Houve problema no envio do Email!";
        retornaInformacao($mensagem,$tipo);
    }
    else
    {
        enviarEmail(
            $emails_para, 
            'DEVOLUÇÃO DE FREQUÊNCIA', 
            "<br><br><big>Informamos que foi desomologada a frequência do(a)"
            . " servidor(a) $nome, siape $siape.<br>"
            . $motivo . "</big><br><br>");
        
        $mensagem = "Devolução da frequência realizada com sucesso!";
        retornaInformacao($mensagem,"success");
    }
}
else
{
    $mensagem = "Frequência do servidor não foi homologada pela chefia!";
    retornaInformacao($mensagem,$tipo);
}

exit();



/* ********************************************************
 *                                                        *
 *                FUNÇÕES COMPLEMENTARES                  *
 *                                                        *
 ******************************************************** */

/**
 * @info Seleciona dados do servidor
 * 
 * @global string $pagina_de_origem
 * 
 * @param string $siape
 * @param string $com_invertida
 * @return \DataBase
 * 
 * @author Edinalvo Rosa
 */
function selecionaDadosServidorHomologado($siape, $comp_invertida)
{
    $siape = getNovaMatriculaBySiape($siape);
    
    $sql = "
    SELECT
        cad.mat_siape, cad.nome_serv, cad.cod_lot, cad.jornada, cad.freqh,
        und.upag, und.descricao, und.uorg_pai, cad.chefia,
        IF(IFNULL(hom.homologado,'N')='N' OR hom.homologado NOT IN ('V','S'),'N','S') AS homologado
    FROM
        servativ AS cad
    LEFT JOIN
        tabsetor AS und ON cad.cod_lot = und.codigo
    LEFT JOIN
        homologados AS hom ON (cad.mat_siape = hom.mat_siape)
                           AND (hom.compet = :compet)
    WHERE
        cad.excluido = 'N'
        AND cad.cod_sitcad NOT IN ('02','15','08')
        AND cad.mat_siape = :siape
    ORDER BY
        cad.mat_siape;
    ";

    $params = array(
        array( ':siape',  $siape,          PDO::PARAM_STR ),
        array( ':compet', $comp_invertida, PDO::PARAM_STR ),
    );

    $oDBase = new DataBase('PDO');
    $oDBase->setMensagem("Problema de acesso a Tabela de servidores");
    $oDBase->query( $sql, $params );
    
    return $oDBase;
}


/**
 * 
 * @return object Dados do servidor
 */
function carregaEmailDoUsuarioLogado()
{
    $oDBase = new DataBase();
    $oDBase->setMensagem("Tabela de servidores inexistente");

    $oDBase->query("
    SELECT nome_serv, email 
        FROM servativ 
            WHERE mat_siape = :siape
    ",
    array(
        array( ':siape', $_SESSION['sMatricula'], PDO::PARAM_STR ),
    ));
    
    return $oDBase->fetch_object();
}


/**
 * 
 * @global string $pagina_de_origem
 * @global date $dataLimiteReHomologacao
 * @global integer $lotacao
 * 
 * @param string $siape
 * @param text $motivo
 * @param date $comp_invertida
 * @param date $dthomol
 */
function atualizaCadastroSetorHomologacao($siape, $motivo, $comp_invertida, $dthomol)
{ 
    global $pagina_de_origem, $dataLimiteReHomologacao, $lotacao; 

    $siape = getNovaMatriculaBySiape($siape);
    
    $destino = (
        $_SESSION['voltar_nivel_2'] == '' 
        ? $pagina_de_origem 
        : $_SESSION['voltar_nivel_2']
    );

    $oDBase = new DataBase();
    $oDBase->setMensagem("Falha na devolução da homologacao");
    $oDBase->setDestino( $destino );

    
    $oDBase->query("
    UPDATE servativ 
        SET freqh = 'N', motidev = :motivo
            WHERE mat_siape = :siape
    ",
    array(
        array( ':siape', $siape, PDO::PARAM_STR ),
        array( ':motivo', $motivo, PDO::PARAM_STR ),
    ));


    $oDBase->query("
    UPDATE homologados 
        SET homologado = 'N', 
            desomologado_motivo = :motivo, 
            desomologado_siape = :logado, 
            desomologado_data = :dthomol 
                WHERE compet = :comp_invertida 
                      AND mat_siape = :siape 
    ",
    array(
        array( ':siape', $siape, PDO::PARAM_STR ),
        array( ':logado', $_SESSION['sMatricula'], PDO::PARAM_STR ),
        array( ':comp_invertida', $comp_invertida, PDO::PARAM_STR ),
        array( ':dthomol', $dthomol, PDO::PARAM_STR ),
        array( ':motivo', $motivo, PDO::PARAM_STR ),
    ));
    
    
    $mensagem = "
        UPDATE tabsetor 
            SET dfreq = 'S', 
                liberar_homologacao = '" . conv_data($dataLimiteReHomologacao) . "' 
                    WHERE codigo = '$lotacao'
    ";
    retornaInformacao($mensagem,$tipo);

    $oDBase->query("
        UPDATE tabsetor 
            SET dfreq = 'S', 
                liberar_homologacao = '" . conv_data($dataLimiteReHomologacao) . "' 
                    WHERE codigo = :lotacao
    ",
    array(
        array( ':lotacao', $lotacao, PDO::PARAM_STR ),
    ));
}
