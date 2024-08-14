<?php

include_once("config.php");
include_once("class_form.frequencia.php");
include_once( "class_ocorrencias_grupos.php" );

verifica_permissao("sRH ou Chefia");

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    // dados enviados por formulario
    $mat   = anti_injection($_REQUEST['mat']);
    $dia   = $_REQUEST['dia'];
    $grupoOperacao = anti_injection($_REQUEST['grupo']);
}
else
{
    // Valores passados - encriptados
    $dados = explode(":|:", base64_decode($dadosorigem));
    $mat   = $dados[0];
    $dia   = $dados[1];
    $diac  = conv_data($dia);
    $grupoOperacao = $dados[2];
}

$sMatricula = $_SESSION["sMatricula"];

$_SESSION["dia_processado"] = inverteData($dia);

switch ($grupoOperacao)
{
    case 'acompanhar_ve_ponto':
        $pagina_de_origem = $_SESSION['voltar_nivel_2'];
        break;

    case 'acompanhar':
        $pagina_de_origem = "frequencia_acompanha_registros.php?dados=" . $_SESSION['voltar_nivel_1'];
        break;

    case 'rh_mes_corrente':
        $pagina_de_origem = "frequencia_rh_mes_corrente_registros.php?dados=" . $_SESSION['voltar_nivel_1'];
        break;

    case 'rh_mes_homologacao':
        $pagina_de_origem = "frequencia_rh_mes_homologacao_registros.php?dados=" . $_SESSION['voltar_nivel_1'];
        break;

    case "historico_manutencao":
        $pagina_de_origem = "historico_frequencia_registros.php?dados=" . $_SESSION['voltar_nivel_1'];
        break;

    case 'homologar':
    default:
        $pagina_de_origem = "frequencia_homologar_registros.php?dados=" . $_SESSION['voltar_nivel_1'];
        break;
}


//pegando o ip do usuario
$ip   = getIpReal(); //linha que captura o ip do usuario.

$data = data2arrayBR($dia);
$mes  = dataMes($dia);
$ano  = dataAno($dia);
$comp = $mes . $ano;
    

// carrega o nome da tabela de trabalho
// se for alteração via módulo histórico
// trás o nome da tabela temporária
$nome_do_arquivo = nomeTabelaFrequencia($grupoOperacao, $comp);
    

$mat = getNovaMatriculaBySiape($mat);


$oDBase = selecionaServidor($mat);
$sitcad = $oDBase->fetch_object()->sigregjur;

// instancia grupo de ocorrencia
$objOcorr = new OcorrenciasGrupos();
$codigoFrequenciaNormalPadrao = $objOcorr->CodigoFrequenciaNormalPadrao($sitcad, $idReg);
$codigoSemFrequenciaPadrao    = $objOcorr->CodigoSemFrequenciaPadrao($sitcad);


//
// VERIFICA SE O ACESSO EH VIA ENTRADA.PHP
//
// Comentado temporariamente por não sabermos, de antemão, os IPs da aplicação
//include_once('ilegal_grava.php');
// instancia o banco de dados
$oDBase = new DataBase('PDO');


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();

// Topo do formulário
//
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();

//Implementar busca para saber se já ocorreu o registro de entrada no dia
$oDBase->setMensagem("Problemas no acesso ao PONTO.\\nPor favor tente mais tarde.");
$oDBase->query("
SELECT dia
    FROM " . $nome_do_arquivo . "
        WHERE siape = :siape
              AND dia = :dia
",
array(
    array(':siape', $mat, PDO::PARAM_STR),
    array(':dia', $diac, PDO::PARAM_STR),
));


if ($oDBase->num_rows() == 0)
{
    mensagem("Exclusão não realizada!\\nPor favor, tente outra vez!", $pagina_de_origem);
}
else
{
    //grava os dados anteriores
    gravar_historico_ponto($mat, $diac, 'E');

    $oDBase->query('
    DELETE FROM ' . $nome_do_arquivo . '
        WHERE siape = :siape
              AND dia = :dia
    ',
    array(
        array(':siape', $mat, PDO::PARAM_STR),
        array(':dia', $diac, PDO::PARAM_STR),
    ));

    if ($oDBase->affected_rows() == 0)
    {
        mensagem("Exclusão não realizada!\\nPor favor, tente outra vez!", $pagina_de_origem);
    }
    else
    {
        $oDBase->query("UPDATE usuarios SET recalculo = 'S', refaz_frqano = 'S' WHERE siape = :siape ", array(
            array(':siape', $mat, PDO::PARAM_STR),
        ));
        mensagem("Exclusão realizada com sucesso!\\nO dia será incluído outra vez com os horários de registros zerados\\ne ocorrência ".implode(', ', $codigoSemFrequenciaPadrao)." (SEM FREQUÊNCIA), se dia útil,\\nou ".implode(', ', $codigoFrequenciaNormalPadrao)." (FREQUÊNCIA NORMAL) se feriado, fim de semana ou facultativo (se for o caso)", $pagina_de_origem);
    }
}


// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();
