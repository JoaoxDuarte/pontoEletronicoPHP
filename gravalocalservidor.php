<?php

// Inicia a sessão e carrega as funções de uso geral
include_once( "config.php" );

// Verifica se existe um usuário logado e se possui permissão para este acesso
verifica_permissao('sRH e sTabServidor');

// pega o nome do arquivo origem
$pagina_de_origem = pagina_de_origem();

// parametros passados por formulario
$matricula_do_servidor         = anti_injection($_REQUEST['tSiape']);    // matrícula siapedo servidor
$nome_do_servidor              = anti_injection($_REQUEST['sNome']);     // nome do servidor
$cpf_do_servidor               = anti_injection($_REQUEST['sCpf']);      // cpf do servidor
$localizacao_atual             = anti_injection($_REQUEST['local']);     // unidade de localização atual
$dt_ingresso_localizacao_atual = conv_data($_REQUEST['dtinglocal']);     // data de ingresso na localização atual
$dt_saida_localizacao_atual    = conv_data($_REQUEST['dtsailocal']);     // data de saída da localização atual
$nova_localizacao              = anti_injection($_REQUEST['novalocal']); // nova unidade de localização
$dt_ingresso_nova_localizacao  = conv_data($_REQUEST['dtingnlocal']);    // data de ingresso na nova localização
// valores registrado em sessao
// upag do cadastrador
$upag                          = $_SESSION['upag'];

// validacao
$mensagem      = '';
$data_saida    = validaData($dt_saida_localizacao_atual);
$data_ingresso = validaData($dt_ingresso_nova_localizacao);

if (soNumeros($localizacao_atual) == $nova_localizacao)
{
    $mensagem .= '- Localização Anterior e Atual são iguais!\\n';
}
else
{

}

if ($data_saida == false)
{
    $mensagem .= '- Data de Ingresso NOVA LOCALIZAÇÃO, inválida!\\n';
}
else
{
    if (($data_ingresso == true) && inverteData($dt_ingresso_localizacao_atual) > inverteData($dt_saida_localizacao_atual))
    {
        //$mensagem .= '- Data de Saída MENOR QUE Data Ingresso localização anterior!\\n';
    }
}

if ($data_ingresso == false)
{
    $mensagem .= '- Data de Saída, inválida!\\n';
}
else
{
    if (($data_saida == true) && inverteData($dt_saida_localizacao_atual) > inverteData($dt_ingresso_nova_localizacao))
    {
        $mensagem .= '- Data de Saída MAIOR QUE Data Ingresso NOVA LOCALIZAÇÃO!\\n';
    }
    if (inverteData($dt_ingresso_localizacao_atual) > inverteData($dt_ingresso_nova_localizacao))
    {
        //$mensagem .= '- Data Ingresso NOVA LOCALIZAÇÃO MENOR QUE Data Ingresso localização anterior!\\n';
    }
}

if ($mensagem != '')
{
    mensagem($mensagem, $pagina_de_origem, 1);
}


// instancia obanco de dados
$oDBase = new DataBase('PDO');

// pesquisa
$oDBase->query('UPDATE servativ SET dt_ing_loc = "' . $dt_ingresso_nova_localizacao . '", cod_loc = "' . $nova_localizacao . '", cod_loc_ant = "' . $localizacao_atual . '", dt_sai_loc = "' . $dt_saida_localizacao_atual . '" WHERE mat_siape = "' . $matricula_do_servidor . '" ');

$oDBase->query('UPDATE histlot SET dt_sai_loc = "' . $dt_saida_localizacao_atual . '" WHERE siape = "' . $matricula_do_servidor . '" AND cod_loc = "' . $localizacao_atual . '" ');

$oDBase->query('INSERT INTO histlot SET siape = "' . $matricula_do_servidor . '", cod_loc = "' . $nova_localizacao . '", dt_ing_loc = "' . $dt_ingresso_nova_localizacao . '" ');

// grava o LOG
registraLog('alterou a localização do servidor, Siape ' . $matricula_do_servidor);

$oDBase->free_result();
$oDBase->close();

unset($matricula_do_servidor);
unset($nome_do_servidor);
unset($cpf_do_servidor);
unset($localizacao_atual);
unset($dt_ingresso_localizacao_atual);
unset($dt_saida_localizacao_atual);
unset($nova_localizacao);
unset($dt_ingresso_nova_localizacao);
unset($upag);
unset($oDBase);

mensagem('Localização realizada com sucesso!');
voltar(1, $pagina_de_origem);
