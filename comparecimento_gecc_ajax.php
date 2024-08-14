<?php
include_once("config.php");
include_once("comparecimento_consulta_medica_funcoes.php");

verifica_permissao("sRH ou Chefia");

/** Servi�o de busca de usu�rios via ajax*/
if(isset($_POST['dia']))
{
    // dados - parametros formul�rio
    $matricula = $_POST['servidor'];  // Matr�cula SIAPE do servidor da instrutoria/tutoria
    $data_ini  = $_POST['data_ini'];  // Data inicial da instrutoria/tutoria
    $data_fim  = $_POST['data_fim'];  // Data final da instrutoria/tutoria
    $horas     = $_POST['horas'];     // Tempo em instrutoria/tutoria
    $data_hoje = $_POST['data_hoje'];
    $usuario   = $_POST['usuario'];   // Usu�rio logado

    $diferenca = (time_to_sec($data_fim) - time_to_sec($data_ini));
    $horas     = sec_to_time($diferenca,'hh:mm'); // Tempo de aus�ncia para a consulta

    $ultimo_dia_do_ano = "31/12/" . date("Y");

    $oDBase = selecionaServidor($matricula);
    $unidade_servidor = $oDBase->fetch_object()->cod_lot;
    $jornada_diaria   = formata_jornada_para_hhmm(getJornadaServer($matricula));

    $tipo = "danger";


    #
    # VERIFICA SE O MATR�CULA DO USU�RIO E DO SERVIDOR S�O IGUAIS
    #
    if ($matricula == getNovaMatriculaBySiape($_SESSION['sMatricula']))
    {
        $mensagem = 'Servidor n�o pode registrar seu pr�prio comparecimento � consulta!';
        retornaInformacao($mensagem,$tipo);
    }


    #
    # VERIFICA SE A DATA INICIAL FOI INFORMADA E SE � V�LIDA
    #
    if (trim($data_ini) === "")
    {
        $mensagem = 'Data Inicial n�o informada!';
        retornaInformacao($mensagem,$tipo);
    }

    // verifica se a data inicial maior que ultimo dia do ano corrente
    if (inverteData($data_ini) > inverteData($ultimo_dia_do_ano))
    {
        $mensagem = 'Data Inicial superior a data atual ('.$ultimo_dia_do_ano.')!';
        retornaInformacao($mensagem,$tipo);
    }

    // verifica se a data � v�lida
    if (validaData($data_ini) == false)
    {
        $mensagem = 'Data Inicial inv�lida!';
        retornaInformacao($mensagem,$tipo);
    }

    // verifica se a data � fim de semana ou feriado
    if (verifica_se_dia_nao_util($data_ini,$unidade_servidor) == true)
    {
        $mensagem = 'Data Inicial inv�lida, feriado/fim de semana!';
        retornaInformacao($mensagem,$tipo);
    }

    // verifica se a data j� est� cadastrada
    if (verifyComparecimentoGeccDataJaCadastrada($matricula, $data_ini) == true)
    {
        $mensagem = 'Data Inicial j� cadastrada para este servidor!';
        retornaInformacao($mensagem,$tipo);
    }


    #
    # VERIFICA SE A DATA FINAL FOI INFORMADA E SE � V�LIDA
    #
    if (trim($data_fim) === "")
    {
        $mensagem = 'Data Final n�o informada!';
        retornaInformacao($mensagem,$tipo);
    }

    // verifica se a data final maior que ultimo dia do ano corrente
    if (inverteData($data_fim) > inverteData($ultimo_dia_do_ano))
    {
        $mensagem = 'Data Inicial superior a data atual ('.$ultimo_dia_do_ano.')!';
        retornaInformacao($mensagem,$tipo);
    }

    // verifica se a data final � v�lida
    if (validaData($data_fim) == false)
    {
        $mensagem = 'Data Final inv�lida!';
        retornaInformacao($mensagem,$tipo);
    }

    // verifica se a data final � fim de semana ou feriado
    if (verifica_se_dia_nao_util($data_fim,$unidade_servidor) == true)
    {
        $mensagem = 'Data Final inv�lida, feriado/fim de semana!';
        retornaInformacao($mensagem,$tipo);
    }

    // verifica se a data final j� est� cadastrada
    if (verifyComparecimentoGeccDataJaCadastrada($matricula, $data_fim) == true)
    {
        $mensagem = 'Data Final j� cadastrada para este servidor!';
        retornaInformacao($mensagem,$tipo);
    }

    // Verifica se hora final � menor ou igual a inicial
    if (time_to_sec($data_ini) >= time_to_sec($data_fim))
    {
        $mensagem = 'Data Final � menor ou igual a Data Inicial!';
        retornaInformacao($mensagem,$tipo);
    }


    #
    # VERIFICA SE A HORAS DE INSTRUTORIA/TUTORIAL S�O V�LIDAS
    #
    if (time_to_sec($horas) == 0)
    {
        $mensagem = 'Tempo da Instrutoria/Tutoria inv�lido (hh:mm)!';
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
        registraLog("Realizado registro de Instrutoria/Tutoria (GECC) para o servidor matr�cula ".$matricula.".");
        $mensagem = 'Realizado registro de Instrutoria/Tutoria (GECC)!';
        retornaInformacao($mensagem,'success');
    }
    else
    {
        $mensagem = 'Registro de Instrutoria/Tutoria (GECC), N�O realizado!';
        //$mensagem = 'Tempo da consulta inv�lido!';
        retornaInformacao($mensagem,$tipo);
    }
}
else
{
    $mensagem = 'Dados n�o informados!';
    retornaInformacao($mensagem,$tipo);
}

exit();
