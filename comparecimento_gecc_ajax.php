<?php
include_once("config.php");
include_once("comparecimento_consulta_medica_funcoes.php");

verifica_permissao("sRH ou Chefia");

/** Serviço de busca de usuários via ajax*/
if(isset($_POST['dia']))
{
    // dados - parametros formulário
    $matricula = $_POST['servidor'];  // Matrícula SIAPE do servidor da instrutoria/tutoria
    $data_ini  = $_POST['data_ini'];  // Data inicial da instrutoria/tutoria
    $data_fim  = $_POST['data_fim'];  // Data final da instrutoria/tutoria
    $horas     = $_POST['horas'];     // Tempo em instrutoria/tutoria
    $data_hoje = $_POST['data_hoje'];
    $usuario   = $_POST['usuario'];   // Usuário logado

    $diferenca = (time_to_sec($data_fim) - time_to_sec($data_ini));
    $horas     = sec_to_time($diferenca,'hh:mm'); // Tempo de ausência para a consulta

    $ultimo_dia_do_ano = "31/12/" . date("Y");

    $oDBase = selecionaServidor($matricula);
    $unidade_servidor = $oDBase->fetch_object()->cod_lot;
    $jornada_diaria   = formata_jornada_para_hhmm(getJornadaServer($matricula));

    $tipo = "danger";


    #
    # VERIFICA SE O MATRÍCULA DO USUÁRIO E DO SERVIDOR SÃO IGUAIS
    #
    if ($matricula == getNovaMatriculaBySiape($_SESSION['sMatricula']))
    {
        $mensagem = 'Servidor não pode registrar seu próprio comparecimento à consulta!';
        retornaInformacao($mensagem,$tipo);
    }


    #
    # VERIFICA SE A DATA INICIAL FOI INFORMADA E SE É VÁLIDA
    #
    if (trim($data_ini) === "")
    {
        $mensagem = 'Data Inicial não informada!';
        retornaInformacao($mensagem,$tipo);
    }

    // verifica se a data inicial maior que ultimo dia do ano corrente
    if (inverteData($data_ini) > inverteData($ultimo_dia_do_ano))
    {
        $mensagem = 'Data Inicial superior a data atual ('.$ultimo_dia_do_ano.')!';
        retornaInformacao($mensagem,$tipo);
    }

    // verifica se a data é válida
    if (validaData($data_ini) == false)
    {
        $mensagem = 'Data Inicial inválida!';
        retornaInformacao($mensagem,$tipo);
    }

    // verifica se a data é fim de semana ou feriado
    if (verifica_se_dia_nao_util($data_ini,$unidade_servidor) == true)
    {
        $mensagem = 'Data Inicial inválida, feriado/fim de semana!';
        retornaInformacao($mensagem,$tipo);
    }

    // verifica se a data já está cadastrada
    if (verifyComparecimentoGeccDataJaCadastrada($matricula, $data_ini) == true)
    {
        $mensagem = 'Data Inicial já cadastrada para este servidor!';
        retornaInformacao($mensagem,$tipo);
    }


    #
    # VERIFICA SE A DATA FINAL FOI INFORMADA E SE É VÁLIDA
    #
    if (trim($data_fim) === "")
    {
        $mensagem = 'Data Final não informada!';
        retornaInformacao($mensagem,$tipo);
    }

    // verifica se a data final maior que ultimo dia do ano corrente
    if (inverteData($data_fim) > inverteData($ultimo_dia_do_ano))
    {
        $mensagem = 'Data Inicial superior a data atual ('.$ultimo_dia_do_ano.')!';
        retornaInformacao($mensagem,$tipo);
    }

    // verifica se a data final é válida
    if (validaData($data_fim) == false)
    {
        $mensagem = 'Data Final inválida!';
        retornaInformacao($mensagem,$tipo);
    }

    // verifica se a data final é fim de semana ou feriado
    if (verifica_se_dia_nao_util($data_fim,$unidade_servidor) == true)
    {
        $mensagem = 'Data Final inválida, feriado/fim de semana!';
        retornaInformacao($mensagem,$tipo);
    }

    // verifica se a data final já está cadastrada
    if (verifyComparecimentoGeccDataJaCadastrada($matricula, $data_fim) == true)
    {
        $mensagem = 'Data Final já cadastrada para este servidor!';
        retornaInformacao($mensagem,$tipo);
    }

    // Verifica se hora final é menor ou igual a inicial
    if (time_to_sec($data_ini) >= time_to_sec($data_fim))
    {
        $mensagem = 'Data Final é menor ou igual a Data Inicial!';
        retornaInformacao($mensagem,$tipo);
    }


    #
    # VERIFICA SE A HORAS DE INSTRUTORIA/TUTORIAL SÃO VÁLIDAS
    #
    if (time_to_sec($horas) == 0)
    {
        $mensagem = 'Tempo da Instrutoria/Tutoria inválido (hh:mm)!';
        retornaInformacao($mensagem,$tipo);
    }

    if (time_to_sec($horas) > time_to_sec($jornada_diaria))
    {
        $mensagem = 'Tempo da Instrutoria/Tutoria superior a Jornada do Servidor ('.$jornada_diaria.')!';
        retornaInformacao($mensagem,$tipo);
    }

    // registra em banco de dados
    if (createComparecimentoGecc() == true)
    {
        registraLog("Realizado registro de Instrutoria/Tutoria (GECC) para o servidor matrícula ".$matricula.".");
        $mensagem = 'Realizado registro de Instrutoria/Tutoria (GECC)!';
        retornaInformacao($mensagem,'success');
    }
    else
    {
        $mensagem = 'Registro de Instrutoria/Tutoria (GECC), NÃO realizado!';
        //$mensagem = 'Tempo da consulta inválido!';
        retornaInformacao($mensagem,$tipo);
    }
}
else
{
    $mensagem = 'Dados não informados!';
    retornaInformacao($mensagem,$tipo);
}

exit();
