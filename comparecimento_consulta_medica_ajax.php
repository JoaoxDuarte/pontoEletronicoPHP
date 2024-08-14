<?php
include_once("config.php");
include_once("comparecimento_consulta_medica_funcoes.php");

verifica_permissao("sRH ou Chefia");

/** Serviço de busca de usuários via ajax*/
if(isset($_POST['dia']))
{
    // dados - parametros formulário
    $dia_consulta   = $_POST['dia']; // Dia da consulta
    $matricula      = $_POST['servidor'];
    $hora_ini       = $_POST['hora_ini'];       // Hora inicial da ausência
    $hora_fim       = $_POST['hora_fim'];       // Hora final da ausência
    //$deslocamento   = $_POST['deslocamento']; // Tempo em deslocamento para a consulta
    $data_hoje      = $_POST['data_hoje'];
    $usuario        = $_POST['usuario'];

    $diferenca = (time_to_sec($hora_fim) - time_to_sec($hora_ini));
    $tempo_consulta = sec_to_time($diferenca,'hh:mm'); // Tempo de ausência para a consulta

    $oDBase = selecionaServidor($matricula);
    $unidade_servidor = $oDBase->fetch_object()->cod_lot;
    $jornada_diaria   = formata_jornada_para_hhmm(getJornadaServer($matricula));

    $tipo = "danger";

    // verifica se a data foi informada
    if ($matricula == getNovaMatriculaBySiape($_SESSION['sMatricula']))
    {
        $mensagem = 'Servidor não pode registrar seu próprio comparecimento à consulta!';
        retornaInformacao($mensagem,$tipo);
        exit();
    }

    // verifica se a data é valida
    if (validaData($dia_consulta) == false)
    {
        $mensagem = 'Data inválida!';
        retornaInformacao($mensagem,$tipo);
        exit();
    }

    // verifica se a data maior que a data atual
    if (inverteData($dia_consulta) > date('Ymd'))
    {
        $mensagem = 'Data superior a data atual ('.date('d/m/Y').')!';
        retornaInformacao($mensagem,$tipo);
        exit();
    }


    // verifica se a data é fim de semana ou feriado
    if (verifica_se_dia_nao_util($dia_consulta,$unidade_servidor) == true)
    {
        $mensagem = 'Data inválida, feriado/fim de semana!';
        retornaInformacao($mensagem,$tipo);
        exit();
    }

    // verifica se a data já está cadastrada
    if (verifyComparecimentoConsultaMedicaDataJaCadastrada($matricula, $dia_consulta) == true)
    {
        $mensagem = 'Data já cadastrada para este servidor!';
        retornaInformacao($mensagem,$tipo);
        exit();
    }

    // Verifica a hora inicial
    $limite_horarios = horariosLimite($unidade_servidor);
    if (time_to_sec($hora_ini) == 0
        || (time_to_sec($hora_ini) < time_to_sec($limite_horarios['entrada_unidade']))
        || (time_to_sec($hora_ini) >= time_to_sec($limite_horarios['saida_unidade'])))
    {
        $mensagem = 'Hora inicial inválida (hh:mm)!';
        retornaInformacao($mensagem,$tipo);
        exit();
    }

    // Verifica a hora final
    if (time_to_sec($hora_fim) == 0
        || (time_to_sec($hora_fim) > time_to_sec($limite_horarios['saida_unidade'])))
    {
        $mensagem = 'Hora final inválida (hh:mm)!';
        retornaInformacao($mensagem,$tipo);
        exit();
    }

    // Verifica se hora inicial é maior que a final
    if (time_to_sec($hora_ini) >= time_to_sec($hora_fim))
    {
        $mensagem = 'Hora inicial é maior ou igual a hora final!';
        retornaInformacao($mensagem,$tipo);
        exit();
    }

    if ((time_to_sec($hora_fim) - time_to_sec($hora_ini)) > time_to_sec($jornada_diaria))
    {
        $mensagem = 'Tempo da Consulta superior a Jornada do Servidor ('.$jornada_diaria.')!';
        retornaInformacao($mensagem,$tipo);
        exit();
    }

    // Verifica se o tempo da consulta foi informada
    /*
    if (time_to_sec($tempo_consulta) == 0)
    {
        $mensagem = 'Tempo da Consulta inválido (hh:mm)!';
        retornaInformacao($mensagem,$tipo);
    }
    */

    //if (time_to_sec($tempo_consulta) > time_to_sec($jornada_diaria))
    //{
    //    $mensagem = 'Tempo da Consulta superior a Jornada do Servidor ('.$jornada_diaria.')!';
    //    retornaInformacao($mensagem,$tipo);
    //}

    // Verifica se o tempo da consulta foi informada
    /*
    if(time_to_sec($deslocamento) == 0){
        $mensagem = 'Tempo de deslocamento inválido (hh:mm)!';
        retornaInformacao($mensagem,$tipo);
    }

    if ((time_to_sec($deslocamento)+time_to_sec($tempo_consulta)) > time_to_sec($jornada_diaria))
    {
        $mensagem = 'Tempo de deslocamento + tempo de consulta superior a Jornada do Servidor!';
        retornaInformacao($mensagem,$tipo);
    }
    */

    // registra em banco de dados
    if (createComparecimentoConsultaMedica() == true)
    {
        registraLog("Realizado registro de comparecimento a consulta médica do servidor matrícula ".$matricula.".");
        $mensagem = 'Realizado registro de comparecimento a consulta médica!';
        retornaInformacao($mensagem,'success');
    }
    else
    {
        $mensagem = 'Registro de comparecimento a consulta médica, NÃO realizado!';
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
