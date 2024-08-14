<?php

include_once("config.php");

verifica_permissao('logado');

// parametros passados por formulario
$siapecad                = anti_injection($_POST['siapecad']);
$smap_ocorrencia         = anti_injection($_POST['smap_ocorrencia']);
$desc_ocorr              = retira_acentos(anti_injection(utf8_decode($_POST['sDescricao'])));
$cod_ocorr               = retira_acentos(anti_injection(utf8_decode($_POST['cod_ocorr'])));
$cod_siape               = anti_injection($_POST['cod_siape']);
$resp                    = anti_injection($_POST['resp']);
$aplic                   = retira_acentos(anti_injection(utf8_decode($_POST['aplic'])));
$implic                  = retira_acentos(anti_injection(utf8_decode($_POST['implic'])));
$prazo                   = retira_acentos(anti_injection(utf8_decode($_POST['prazo'])));
$flegal                  = retira_acentos(anti_injection(utf8_decode($_POST['flegal'])));
$ativo                   = anti_injection($_POST['sAtivo']);
$semrem                  = anti_injection($_POST['semrem']);
$idsiapecad              = anti_injection($_POST['idsiapecad']);
$grupo                   = anti_injection($_POST['grupo']);
$tipo                    = anti_injection($_POST['tipo']);
$situacao                = anti_injection($_POST['situacao']);
$justificativa           = anti_injection($_POST['justificativa']);
$postergar_pagar_recesso = anti_injection($_POST['postergar_pagar_recesso']);
$tratamento_debito       = anti_injection($_POST['tratamento_debito']);
$padrao                  = anti_injection($_POST['padrao']);
$grupo_cadastral         = anti_injection($_POST['grupo_cadastral']);
$agrupa_debito           = anti_injection($_POST['agrupa_debito']);
$grupo_ocorrencia        = anti_injection($_POST['grupo_ocorrencia']);
$vigencia_inicio         = anti_injection(conv_data($_POST['vigencia_inicio']));
$vigencia_fim            = anti_injection(conv_data($_POST['vigencia_fim']));
$abonavel                = anti_injection($_POST['abonavel']);

// instancia o banco de dados
$oDBase = new DataBase('PDO');
$oDBase->setMensagem('Erro no acesso ao banco de dados!');
$oDBase->setDestino($_SESSION['sHOrigem_1']);

// verifica usuário e senha
$oDBase->query("
SELECT siapecad
    FROM tabocfre
        WHERE siapecad = :codigo
",
array(
    array(':codigo', $siapecad, PDO::PARAM_STR),
));


if ($oDBase->num_rows() > 0)
{
    $query = "
    UPDATE
        tabocfre
    SET
        desc_ocorr              = :desc_ocorr,
        resp                    = :resp,
        aplic                   = :aplic,
        implic                  = :implic,
        prazo                   = :prazo,
        flegal                  = :flegal,
        ativo                   = :ativo,
        smap_ocorrencia         = :smap_ocorrencia,
        cod_ocorr               = :cod_ocorr,
        cod_siape               = :cod_siape,
        semrem                  = :semrem,
        idsiapecad              = :idsiapecad,
        grupo                   = :grupo,
        tipo                    = :tipo,
        situacao                = :situacao,
        justificativa           = :justificativa,
        postergar_pagar_recesso = :postergar_pagar_recesso,
        tratamento_debito       = :tratamento_debito,
        padrao                  = :padrao,
        grupo_cadastral         = :grupo_cadastral,
        agrupa_debito           = :agrupa_debito,
        grupo_ocorrencia        = :grupo_ocorrencia,
        vigencia_inicio         = :vigencia_inicio,
        vigencia_fim            = :vigencia_fim,
        abonavel                = :abonavel
    WHERE
        siapecad = :siapecad
    ";

    $params = array(
        array( ':siapecad',                $siapecad,                PDO::PARAM_STR ),
        array( ':desc_ocorr',              $desc_ocorr,              PDO::PARAM_STR ),
        array( ':resp',                    $resp,                    PDO::PARAM_STR ),
        array( ':aplic',                   $aplic,                   PDO::PARAM_STR ),
        array( ':implic',                  $implic,                  PDO::PARAM_STR ),
        array( ':prazo',                   $prazo,                   PDO::PARAM_STR ),
        array( ':flegal',                  $flegal,                  PDO::PARAM_STR ),
        array( ':ativo',                   $ativo,                   PDO::PARAM_STR ),
        array( ':smap_ocorrencia',         $smap_ocorrencia ,        PDO::PARAM_STR ),
        array( ':cod_ocorr',               $cod_ocorr,               PDO::PARAM_STR ),
        array( ':cod_siape',               $cod_siape,               PDO::PARAM_STR ),
        array( ':semrem',                  $semrem,                  PDO::PARAM_STR ),
        array( ':idsiapecad',              $idsiapecad,              PDO::PARAM_STR ),
        array( ':grupo',                   $grupo,                   PDO::PARAM_STR ),
        array( ':tipo',                    $tipo,                    PDO::PARAM_STR ),
        array( ':situacao',                $situacao,                PDO::PARAM_STR ),
        array( ':justificativa',           $justificativa,           PDO::PARAM_STR ),
        array( ':postergar_pagar_recesso', $postergar_pagar_recesso, PDO::PARAM_STR ),
        array( ':tratamento_debito',       $tratamento_debito,       PDO::PARAM_STR ),
        array( ':padrao',                  $padrao,                  PDO::PARAM_STR ),
        array( ':grupo_cadastral',         $grupo_cadastral,         PDO::PARAM_STR ),
        array( ':agrupa_debito',           $agrupa_debito,           PDO::PARAM_STR ),
        array( ':grupo_ocorrencia',        $grupo_ocorrencia,        PDO::PARAM_STR ),
        array( ':vigencia_inicio',         $vigencia_inicio,        PDO::PARAM_STR ),
        array( ':vigencia_fim',            $vigencia_fim,        PDO::PARAM_STR ),
        array( ':abonavel',                $abonavel,        PDO::PARAM_STR )
    );

    $oDBase->query( $query, $params );

    // grava o LOG
    registraLog("alterou dados da tabela de ocorrências, na máquina de IP " . getIpReal());
    // fim do LOG

    $tipo     = 'success';
    $msg_erro = "Alterações nos dados da Ocorrência registrada com sucesso!";
    retornaInformacao($msg_erro, $tipo);
}
else
{
    retornaInformacao("Registro não encontrado!", $tipo);
}

exit();
