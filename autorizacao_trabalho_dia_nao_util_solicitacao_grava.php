<?php

/**
 * +-------------------------------------------------------------+
 * | @description : Solicita Autorização Trabalho Dia Não Útil   |
 * |                - MODO 5                                     |
 * |                  Grava solicitação de trabalho Dia Não Útil |
 * |                                                             |
 * | @author  : Carlos Augusto                                   |
 * | @version : Edinalvo Rosa                                    |
 * +-------------------------------------------------------------+
 * */
// funcoes de uso geral
include_once("config.php");

// permissao de acesso
verifica_permissao("logado");

// Valores passados - encriptados
$dadosorigem = $_REQUEST['dados'];

if (empty($dadosorigem))
{
    header("Location: acessonegado.php");
}
else
{
    $dados               = explode(":|:", base64_decode($dadosorigem));
    $siape               = $dados[0];
    $nome                = $dados[1];
    $dia_nao_util        = conv_data($dados[2]);
    $codigo_do_municipio = $dados[3];
}

$m = dataMes($dia_nao_util);
$d = dataDia($dia_nao_util);
$y = dataAno($dia_nao_util);

$data_hoje = date('Y-m-d');

$lot                 = $_SESSION['sLotacao'];
$responsavel_unidade = 'S';

$chefe = chefia_ativa($siape, $data_hoje);

// instancia o banco de dados
$oDBase = new DataBase('PDO');
$oDBase->setDestino($_SESSION['voltar_nivel_1']);

$severidade = 'danger';

#checa se dia solicitado é útil ou não, ou se é inferior a data de hoje
if ($dia_nao_util <= $data_hoje)
{
    $mensagem = "Data anterior ou igual a data atual!";
}
else if ((validaData($dia_nao_util) == false) || (dataAno($dia_nao_util) > date('Y')))
{
    $mensagem = "Data {$dados[2]}, inválida!";
}
elseif (verifica_se_dia_nao_util($dia_nao_util, $lot) == false)
{
    $mensagem = "Operação não realizada. O dia solicitado é dia útil!";
}
elseif (verifica_se_dia_nao_util($dia_nao_util, $lot) == true) // a data é de um dia não útil (fim de semana, feriado)
{
    $oDBase->setMensagem('Tabela não encontrada (DNU)!');
    $oDBase->setSemDBErro(true);

    $oDBase->query("SELECT dia FROM tabdnu WHERE siape = :siape AND dia <> '0000-00-00' AND dia = :dia ", array(
        array(':siape', $siape, PDO::PARAM_STR),
        array(':dia', $dia_nao_util, PDO::PARAM_STR)
        )
    );
    if ($oDBase->num_rows() > 0)
    {
        $mensagem = "Data já registrada no banco de dados para essa matrícula!";
    }
    else
    {
        $oDBase->query("INSERT INTO tabdnu (dia, siape) VALUES (:dia, :siape) ", array(
            array(':dia', $dia_nao_util, PDO::PARAM_STR),
            array(':siape', $siape, PDO::PARAM_STR)
            )
        );

        if ($oDBase->affected_rows() > 0)
        {
            // verifica se eh chefe de unidade
            if ($chefe == "S")
            {
                $oDBase->setMensagem("Tabela de setores inexistente");
                $oDBase->query("SELECT uorg_pai FROM tabsetor WHERE codigo = :lot ", array(
                    array(':lot', $lot, PDO::PARAM_STR)
                    )
                );
                $oSetor = $oDBase->fetch_object();
                $lot    = $oSetor->uorg_pai;
            }

            $emailchefe = emailChefiaTitularSubstituto($lot);

            $count = enviarEmail($emailchefe, "SOLICITACAO DE TRABALHO EM DIA NÃO ÚTIL", "<br><br><big>Prezado(a),<br><br>Informamos que foi solicitado trabalho em dia n&atilde;o útil pelo servidor(a) $nome, siape $siape, ficando a seu cargo autorizar ou n&atilde;o, por intermédio da opção 'Frequência » Autorização de Trabalho', no SISREF.<br> Atenciosamente,<br><br> Equipe SISREF.</big><br><br>");

            if ($count != 0)
            {
                $mensagem   = "Solicitação registrada com sucesso!\\nO início do trabalho em dia não útil depende de autorização da chefia imediata.\\n\\nE-mail enviado com sucesso para: " . $emailchefe . "!";
                $severidade = 'success';
            }
            else
            {
                $mensagem   = "Solicitação registrada com sucesso!\\n\\nPorém, ocorreu um erro durante o envio do e-mail para: " . $emailchefe . ".\\n\\nPor favor, comunique a sua chefia imediata a realização da solicitação, para que seja autorizado ou não o trabalho em 'dia não útil', por intermédio da opção 'Frequência » Autorização de Trabalho', no SISREF!";
                $severidade = 'warning';
            }
        }
    }
}

setMensagemUsuario($mensagem, $severidade);

DataBase::fechaConexao();

replaceLink($_SESSION['voltar_nivel_1']);
