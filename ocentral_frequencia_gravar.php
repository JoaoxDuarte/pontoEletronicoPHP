<?php

include_once("config.php");
include_once("comparecimento_tabela_auxiliar.php");

verifica_permissao("administracao_central");

$pagina_anterior = 'ocentral.php?aba=alterar';

// dados enviados por formulario
$siape   = anti_injection($_POST['siape']);
$dia     = $_POST['dia'];
$entra   = anti_injection($_POST['entra']);
$iniint  = anti_injection($_POST['iniint']);
$fimint  = anti_injection($_POST['fimint']);
$sai     = anti_injection($_POST['sai']);
$jornd   = anti_injection($_POST['jornd']);
$jornp   = anti_injection($_POST['jornp']);
$jorndif = anti_injection($_POST['jorndif']);
$oco     = anti_injection($_POST['oco']);

if (empty($siape))
{
    mensagem( 'Matrícula não informada!', 'ocentral.php' );
}
if (empty($dia))
{
    mensagem( 'Dia não informado!', 'rh.php' );
}

$mat = getNovaMatriculaBySiape($siape);

$nome_do_arquivo = 'ponto'.dataMes($dia).dataAno($dia);


// instancia o banco de dados
$oDBase = new DataBase('PDO');

//Implementar busca para saber se já ocorreu o registro de entrada no dia
$oDBase->setMensagem("Problemas no acesso ao PONTO.\\nPor favor tente mais tarde.");
$oDBase->query("
SELECT
    dia, siape, entra, intini, intsai, sai, jornd, jornp, jorndif, oco,
    idreg, ipch, matchef, justchef
FROM $nome_do_arquivo
WHERE
    dia = :dia
    AND siape = :siape
", array(
    array(':dia',   conv_data($dia), PDO::PARAM_STR),
    array(':siape', $mat,            PDO::PARAM_STR),
));
$oPontoAgora = $oDBase->fetch_object();

$jornd   = (empty($jornd)   ? $oPontoAgora->jornd   : $jornd);
$jornp   = (empty($jornp)   ? $oPontoAgora->jornp   : $jornp);
$jorndif = (empty($jorndif) ? $oPontoAgora->jorndif : $jorndif);
$ocor    = (empty($oco)     ? $oPontoAgora->oco     : $oco);


## classe para montagem do formulario padrao
#
$oForm = new formPadrao();
$oForm->exibeTopoHTML();
$oForm->exibeCorpoTopoHTML();


// Base do formulário
//
$oForm->exibeCorpoBaseHTML();
$oForm->exibeBaseHTML();


if ($oDBase->num_rows() == 0)
{
    mensagem( "Não existe registro matrícula ".$siape." no dia ".$dia."!", $pagina_anterior);
}
else
{
    $oDBase->query("UPDATE " . $nome_do_arquivo . " SET entra = :entra, intini = :intini, intsai = :intsai, sai = :sai, jornd = :jornd, jornp = :jornp, jorndif = :jorndif, oco = :oco WHERE siape = :siape AND dia = :dia ", array(
        array(':siape',   $mat,            PDO::PARAM_STR),
        array(':dia',     conv_data($dia), PDO::PARAM_STR),
        array(':entra',   $entra,          PDO::PARAM_STR),
        array(':intini',  $iniint,         PDO::PARAM_STR),
        array(':intsai',  $fimint,         PDO::PARAM_STR),
        array(':sai',     $sai,            PDO::PARAM_STR),
        array(':jornd',   $jornd,          PDO::PARAM_STR),
        array(':jornp',   $jornp,          PDO::PARAM_STR),
        array(':jorndif', $jorndif,        PDO::PARAM_STR),
        array(':oco',     $ocor,           PDO::PARAM_STR),
    ));

    if ($oDBase->affected_rows() == 0)
    {
        mensagem("Alteração não realizada!<br>Os dados informados já constam na frequência do servidor/estagiário!", $pagina_anterior);
    }
    else
    {
        // verifica se há registro de comparecimento
        // a consulta médica ou exame ou GECC
        AjustaSaldoFrequenciaSeConsultaMedicaRegistrada($mat, conv_data($dia));

        $oDBase->query("UPDATE usuarios SET recalculo = 'S', refaz_frqano = 'S' WHERE siape = :siape ", array(
            array(':siape', $mat, PDO::PARAM_STR),
        ));

        mensagem("Alteração realizada com sucesso!", $pagina_anterior);
    }
}

