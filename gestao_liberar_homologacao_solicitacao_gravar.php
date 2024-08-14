<?php

/**
 * +-------------------------------------------------------------+
 * | @description : Solicita Dilação de Prazo Homologação        |
 * |                                                             |
 * | @author  : Edinalvo Rosa                                    |
 * +-------------------------------------------------------------+
 * */
// funcoes de uso geral
include_once("config.php");

// permissao de acesso
verifica_permissao("sAPS");

// Valores passados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    header("Location: acessonegado.php");
}
else
{
    $dados         = explode(":|:", base64_decode($dadosorigem));
    $justificativa = anti_injection($dados[0]);
    $lotacao       = anti_injection($dados[1]);
}

// Competência atual (mês e ano)
$data = new trata_datasys();
$ano         = $data->getAnoHomologacao();
$mes         = $data->getMesHomologacao();
$competencia = $data->getCompetHomologacao(); // mes e ano, ex.: 032010

// instancia o banco de dados
$oDBase = new DataBase('PDO');
$oDBase->setDestino($_SESSION['voltar_nivel_1']);

$severidade = 'danger';

#checa se dia solicitado é útil ou não, ou se é inferior a data de hoje
if (strlen(trim($justificativa)) < 15)
{
    $mensagem = "É obrigatório o preenchimento da justificativa com no mínimo 15 caracteres!";
    setMensagemUsuario($mensagem, 'danger');
    replaceLink($_SESSION['voltar_nivel_1']);
    die();
}

$oDBase->setMensagem("Problemas no acesso a Tabela HOMOLOGAÇÃO DILAÇÃO DE PRAZO (E000129.".__LINE__.").");

$oDBase->query("
SELECT 
    setor,
    compet,
    IF((homologacao_dilacao_prazo.deliberacao = 'Deferido' 
        AND homologacao_dilacao_prazo.homologacao_limite >= DATE_FORMAT(NOW(),'%Y-%m-%d')) 
        OR (ISNULL(homologacao_dilacao_prazo.deliberacao) 
            AND homologacao_dilacao_prazo.homologacao_limite = '0000-00-00'),
                'ativa',
                'expirada') AS prazo
FROM 
    homologacao_dilacao_prazo 
WHERE 
    homologacao_dilacao_prazo.setor = :setor 
    AND homologacao_dilacao_prazo.compet = :compet
ORDER BY 
    homologacao_dilacao_prazo.data_registro DESC
LIMIT 1
", array(
    array(':compet', $ano.$mes, PDO::PARAM_STR),
    array(':setor',  $lotacao,  PDO::PARAM_STR)
));

$dilacao = $oDBase->fetch_object()->prazo;

if ($oDBase->num_rows() > 0 && $dilacao == 'ativa')
{
    $mensagem = "Solicitação já registrada para esta Unidade!<br>Esta PENDENTE de deferimento ou já foi atendida.";
    setMensagemUsuario($mensagem, 'danger');
    replaceLink($_SESSION['voltar_nivel_1']);
    die();
}

        
$oDBase->query("
INSERT
    homologacao_dilacao_prazo
SET 
    compet = :compet,
    siape  = :siape,
    setor  = :setor,
    justificativa = :justificativa,
    data_registro = NOW();
", array(
    array(':compet',        $ano.$mes,               PDO::PARAM_STR),
    array(':siape',         $_SESSION['sMatricula'], PDO::PARAM_STR),
    array(':setor',         $lotacao,                PDO::PARAM_STR),
    array(':justificativa', $justificativa,          PDO::PARAM_STR),
));

if ($oDBase->affected_rows() > 0)
{
    $mensagem   = "Solicitação registrada com sucesso!";
    $severidade = 'success';
}
else
{
    $mensagem   = "Solicitação NÃO registrada!<br>Tente mais mais tarde!";
    $severidade = 'warning';
}

setMensagemUsuario($mensagem, $severidade);

DataBase::fechaConexao();

replaceLink($_SESSION['voltar_nivel_1']);
