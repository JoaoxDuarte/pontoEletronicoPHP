<?php

include_once('config.php');
include_once('gestao_liberar_homologacao_funcoes.php');


$dt_limite   = conv_data($_POST['prorrogado_ate']);
$id_registro = $_POST['liberar_homologacao'];


/*********************************************
 *                                           *
 *            VALIDAÇÃO DOS DADOS            *
 *                                           *
 *********************************************/
$data_hoje = date('Y-m-d');

$severidade = 'danger';

$oDBase = new DataBase('PDO');

#checa se dia alguma unidade foi selecionada
if ( !is_array($id_registro) || count($id_registro) == 0)
{
    $mensagem = "Selecionar uma ou mais Unidade(s)!";
    retornaInformacao($mensagem,$severidade);
}

#checa data
if ($dt_limite == "0000-00-00")
{
    $mensagem = "Data deve ser informada!";
    retornaInformacao($mensagem,$severidade);
}

if (validaData($dt_limite) == false)
{
    $mensagem = "Data ".databarra($dt_limite).", inválida!";
    retornaInformacao($mensagem,$severidade);
}

if ($dt_limite < $data_hoje)
{
    $mensagem = "Data anterior a data atual!";
    retornaInformacao($mensagem,$severidade);
}


#checa se data é de um dia não útil
# (fim de semana, feriado)
# para a unidade indicada 

$mensagem  = "";
$registros = array();

for ($x =0; $x < count($id_registro); $x++)
{
    $oDBase->query("
    SELECT 
        homologacao_dilacao_prazo.setor
    FROM
        homologacao_dilacao_prazo 
    WHERE 
        id = :id
    ", array(
        array( ':id', $id_registro[$x], PDO::PARAM_STR ), 
    ));
    
    $oSetor = $oDBase->fetch_object();

    if (verifica_se_dia_nao_util($dt_limite, $oSetor->setor) == true) 
    {
        $mensagem .= (empty($mensagem) ? "" : "<br>");
        $mensagem .= "Data ".databarra($dt_limite).", não é dia útil para a unidade ".getUorgMaisDescricao($oSetor->setor)."!";
    }
    else
    {
        $oDBase->query("
        UPDATE
            homologacao_dilacao_prazo
        SET
            homologacao_dilacao_prazo.siape_deliberacao  = :siape_deliberacao,
            homologacao_dilacao_prazo.deliberacao        = 'Deferido',         
            homologacao_dilacao_prazo.homologacao_limite = :dt_limite,
            homologacao_dilacao_prazo.data_deliberacao   = NOW()
        WHERE
            id = :id
        ",
        array(
            array( ':id',                $id_registro[$x],        PDO::PARAM_STR ), 
            array( ':siape_deliberacao', $_SESSION['sMatricula'], PDO::PARAM_STR ), 
            array( ':dt_limite',         $dt_limite,              PDO::PARAM_STR ), 
        ));
        
        if ($oDBase->affected_rows() > 0)
        {
            $registros[] = $id_registro[$x];
        }

        $oDBase->query("
        UPDATE
            tabsetor
        SET
            tabsetor.liberar_homologacao = :dt_limite
        WHERE
            tabsetor.codigo = :setor
        ",
        array(
            array( ':setor',        $oSetor->setor, PDO::PARAM_STR ), 
            array( ':dt_limite', $dt_limite,     PDO::PARAM_STR ), 
        ));
    }
}

if ($mensagem == "")
{
    $mensagem   = "Liberação(ões) realizada(s) com Sucesso!";
    $severidade = "success";
}
else
{
    $mensagem = "Liberação(ões) parcialmente realizada(s)!<br>" . $mensagem;
}

$mensagem = array(
    "mensagem" => utf8_encode($mensagem), 
    "tipo"     => $severidade,
    "id"       => $registros,
);

retornaInformacao($mensagem,$severidade);

exit();
