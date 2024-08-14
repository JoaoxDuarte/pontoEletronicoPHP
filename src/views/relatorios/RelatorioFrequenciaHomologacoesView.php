<?php

// Inicia a sessão e carrega as funções de uso geral
include_once("config.php");


/**
 * @class RelatorioFrequenciaHomologacoesView
 *        Responsável por gerenciar o fluxo de dados entre
 *        a camada de modelo e a de visualização
 *
 * @info TABELA : homologados
 *       Suas descrições e características
 *
 * @Camada    - View
 * @Diretório - src/models
 * @Arquivo   - RelatorioFrequenciaHomologacoesView.php
 *
 * @author Edinalvo Rosa
 */
class RelatorioFrequenciaHomologacoesView extends formPadrao
{
    /*
    * Atributos
    */
    /* @var OBJECT */ public $conexao = NULL;
    
    /* @var string */ public $homologados;
    /* @var string */ public $ano;
    /* @var string */ public $mes;
    /* @var string */ public $upag;
    /* @var string */ public $unidade;
    /* @var string */ public $servidor;
    /* @var string */ public $homologador;
    /* @var string */ public $num;
    /* @var string */ public $mensagem;
    

    ## @constructor
    #+-----------------------------------------------------------------+
    # Construtor da classe
    #+-----------------------------------------------------------------+
    #
    public function __construct()
    {
        parent::formPadrao();

    }


    /**
     * @info Lista servidores, por unidade, para verificar se foram homologados
     *
     * @param array $dados  Dados dos servidores selecionados
     * @param string $mes
     * @param string $ano
     */
    function ServidoresPorUnidade($dados,$mes,$ano,$status)
    {
        global $relatorioFrequenciaHomologacoesController;

        $compet = (empty($ano) || empty($mes) ? null : $ano . $mes);

        ?>
        <div id="<?= $dados[0]['cod_lot']; ?>" name='unidades'>
            <table class="table table-striped table-bordered text-center table-hover margin-25">
                <thead>
                    <tr style="border:1px solid white;padding:0px;margin:0px;">
                        <td colspan="4" style="border:0px solid white;padding:0px;margin:0px;">
                                <div class="col-md-8 text-left" id="total_de_registros" style="padding:0px;margin:0px;">
                                    <p style="padding:0px;margin:0px;vertical-align:bottom;"><b>Unidade: <?= tratarHTML(getUorgMaisDescricao($dados[0]['cod_lot'])); ?></b></p>
                                </div>
                                <div class="col-md-4 text-right" id="total_de_registros" style="vertical-align:bottom;">
                                    Total de <?= number_format( count($dados), 0, ',', '.' ); ?> registros.
                                </div>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-center" style="width: 12%;">UNIDADE</th>
                        <th class="text-center" style="width: 14%;">MATR&Iacute;CULA</th>
                        <th class="text-left"   style="width: 60%;">NOME</th>
                        <th class="text-center" style="width: 14%;">SITUA&Ccedil;&Atilde;O</th>
                    </tr>
                </thead>
                <tbody id='registros_selecionados' class='sse_listar'>
                    <?php

                    for ($x=0; $x < count($dados); $x++)
                    {
                        $status = $relatorioFrequenciaHomologacoesController->SituacaoHomologacaoPorMatricula( $dados[$x]['siape'], $dados[$x]['dt_adm'], $dados[$x]['oco_exclu_dt'], $compet );

                        if ($status === 'HOMOLOGADO')
                        {
                            $style = "style='color:blue;font-weith:bold;text-decoration:none;'";
                        }
                        else
                        {
                            $style = "style='color:red;font-weith:bold;text-decoration:none;'";
                        }

                        ?>
                        <tr>
                            <td class="text-center" style="width: 12%;" ><?= tratarHTML(removeOrgaoLotacao($dados[$x]['cod_lot'])); ?></td>
                            <td class="text-center" style="width: 14%;"><?= tratarHTML(removeOrgaoMatricula($dados[$x]['siape'])); ?></td>
                            <td class="text-left"   style="width: 60%;"><?= tratarHTML($dados[$x]['nome']); ?></td>
                            <td class="text-center" style="width: 14%;" nowrap>
                                <div class='imprimir_texto_link' style='display:none;'><a <?= $style; ?>> <?= $status; ?></a></div>
                                <a href="#myModalVisual" role="button" class="btn no_print_link" data-toggle="modal" data-load-remote="veponto_saldos.php" data-remote-dados='tipo=1&pSiape=<?= $dados[$x]['siape']; ?>&extrato=sim' data-remote-target="#myModalVisual .modal-body-conteudo" <?= $style; ?>><span class="glyphicon glyphicon-eye-open" alt="Visualizar Extrato" title="Visualizar Extrato"></span> <?= $status; ?></a>
                            </td>
                        </tr>
                        <?php
                    }

                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }


    /**
     * @info Lista mes e ano
     *
     * @param string/null $ano
     * @param string/null $mes
     * @param string/null $opcao_selecione
     */
    public function CarregaSelectCompetencia($ano=NULL, $mes=NULL, $opcao_selecione=false)
    {
        $mes    = (is_null($mes) ? date('m') : $mes);
        $ano    = (is_null($ano) ? date('Y') : $ano);
        $compet = $mes . '/' . $ano;
        $start  = '2018-01-01';
        $end    = date('Y').'-'.(substr('00'.(date('m')-1),0,2)).'-'.date('d');

        $list = getDatesFromRange($start, $end, $format = 'm/Y', $intervalo = 'P1M');

        ?>
        <select class="form-control select2-single" id="competencias_opcoes" name="competencias_opcoes">
            <option value=''>Todas as opções</option>
            <?php

            foreach($list AS $opcao)
            {
                $value    = substr($opcao,-4).substr($opcao,0,2);
                $selected = ($opcao === $compet ? " selected" : "");

                ?>
                <option value='<?= tratarHTML($value); ?>'<?= tratarHTML($selected); ?>><?= tratarHTML($opcao); ?></option>
                <?php
            }

            ?>
        </select>
        <small style="font-size:9px;vertical-align:top;margin-top:0px;padding-left:3px;padding-top:0px;">(<?= count($list); ?> meses)</small>
        <?php
    }


    /*
     * @info Lista as unidades
     *
     * @param string      $oDBase Dados dos servidores
     * @param string      $ano    Ano da competência da homologação
     * @param string      $mes    Mês da competência da homologação
     * @param string      $upag   UPAG da unidade do servidor/estagiário
     * @param string/null $setor  Unidade do servidor/estagiário
     */
    public function CarregaSelectUnidades($oDBase, $ano, $mes, $upag, $setor=NULL)
    {
        //$oDBase = UnidadesTotalDeServidores($ano, $mes, $upag);

        ?>
        <select class="form-control select2-single" id='unidades_opcoes' name='unidades_opcoes'>
            <option value=''>Todas as opções</option>
            <?php

            $num_unidades = $oDBase->num_rows();

            while ($pm = $oDBase->fetch_object())
            {
                $selected = (is_null($setor) ? false : ($pm->cod_lot == $setor));

                ?>
                <option value='<?= tratarHTML($pm->cod_lot); ?>' <?= ($selected ? 'selected' : ''); ?>>
                    <?= tratarHTML(getUorgMaisDescricao($pm->cod_lot)); ?>
                </option>
                <?php
            }

            ?>
        </select>
        <small style="font-size:9px;vertical-align:top;margin-top:0px;padding-left:3px;padding-top:0px;">(<?= number_format($num_unidades,0,',','.'); ?> unidades)</small>
        <?php
    }

} // END class RelatorioFrequenciaHomologacoesView
