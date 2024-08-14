<?php

/*
 * 07/12/2012 controle da navegacao entre as paginas
 *
 */

class Control_Navegacao
{

    /*
     * Atributos
     */

    private $nome_da_sessao = 'sessaoControleNavegacao';
    private $sessao;
    private $ultimoElemento;

    /*
     * Métodos Public
     *
     * __construct()          // construtor
     * initSessaoNavegacao()  // inicializa a sessao
     * loadSessaoNavegacao()  // carrega os dados da sessao
     * saveSessaoNavegacao()  // salva os dados na sessao
     * setPagina($pagina)     // inclui nova pagina no controle de navegacao
     * getPagina( $indice=0 ) // pega os dados de uma pagina
     * getPaginaPrimeira()    // pega os dados da primeira pagina
     * getPaginaAnterior()    // pega os dados da ultima pagina
     * ContaPaginas()         // retorna a quantidade de paginas registradas
     * UltimoElemento()       // retorna o indice do ultimo elemento
     * SubstituiPagina( $indice,$url ) // substitui a pagina do indice por outra, ou mesma modificada
     * SubstituiValorREQUEST( $indice,$campo,$valor ) // altera o valor de um campo passado por POST/GET da pagina registrada
     *
     */

    /*
     * Função Construtora
     */

    public function __construct()
    {
        $this->loadSessaoNavegacao();

    }

    /*
     * Propriedades dos atributos
     */

    public function initSessaoNavegacao()
    {
        $this->sessao                        = array();
        $_SESSION['sessaoControleNavegacao'] = array();

    }

    public function loadSessaoNavegacao()
    {
        $this->sessao = $_SESSION['sessaoControleNavegacao'];

    }

    public function saveSessaoNavegacao()
    {
        $_SESSION['sessaoControleNavegacao'] = $this->sessao;

    }

    public function setPagina($pagina = '')
    {
        $pagina_ja_cadastrada = false;
        if ($pagina != '')
        {
            // pesquisa no array para ver
            // se esta pagina ja foi cadastrada
            for ($indice = 0; $indice < $this->ContaPaginas(); $indice++)
            {
                $request_uri        = $this->sessao[$indice];
                $char_split         = '?';
                $paginaRegistrada   = explode($char_split, $request_uri);
                $paginaParaRegistro = explode($char_split, $pagina);
                if ($paginaRegistrada[0] == $paginaParaRegistro[0])
                {
                    $pagina_ja_cadastrada = true;
                    for ($posicao = ($this->ContaPaginas() - 1); $posicao > $indice; $posicao--)
                    {
                        array_pop($this->sessao);
                    }
                }
            }
            // se a pagina não foi cadastrada
            // insere no controle de navegacao
            if ($pagina_ja_cadastrada == false)
            {
                $this->sessao[] = $pagina;
                $this->saveSessaoNavegacao();
            }
        }

    }

    public function getPagina($indice = 0)
    {
        return $this->sessao[$indice];

    }

    public function getPaginaPrimeira()
    {
        return $this->sessao[0];

    }

    public function getPaginaAnterior()
    {
        if (count($this->sessao) == 1)
        {
            return $this->getPaginaPrimeira();
        }
        else
        {
            return $this->sessao[(count($this->sessao) - 2)];
        }

    }

    public function ApagaPaginaUltima()
    {
        array_pop($this->sessao);

    }

    public function getPaginaUltima()
    {
        return $this->sessao[($this->UltimoElemento())];

    }

    public function ContaPaginas()
    {
        return count($this->sessao);

    }

    public function UltimoElemento()
    {
        return ($this->ContaPaginas() - 1);

    }

    public function SubstituiPagina($indice, $url)
    {
        $this->sessao[$indice] = $url;
        $this->saveSessaoNavegacao();

    }

    public function SubstituiValorREQUEST($indice = 0, $campo = '', $valor = '')
    {
        $request_uri = $this->sessao[$indice];
        if ($campo != '' || $valor != '')
        {
            $char_split    = '?';
            $urlParametros = explode($char_split, $request_uri);
            if (count($urlParametros) > 1)
            {
                $char_split2      = '&';
                $camposParametros = explode($char_split2, $urlParametros[1]);
                if (count($camposParametros) > 1)
                {
                    $char_split3 = '=';
                    $request_uri = $urlParametros[0] . $char_split; // pagina
                    for ($i = 0; $i < count($camposParametros); $i++)
                    {
                        $campoRegistrado = explode($char_split3, $camposParametros[$i]);
                        $request_uri     .= ($campoRegistrado[0] == $campo ? $campo . "=" . $valor . "&" : $campoRegistrado[0] . '=' . $campoRegistrado[1] . "&");
                    }
                    $request_uri           = substr($request_uri, 0, (strlen($request_uri) - 1));
                    $this->sessao[$indice] = $request_uri;
                    $this->saveSessaoNavegacao();
                }
            }
        }

    }

    public function IncluiValorREQUEST($indice = 0, $campo = '', $valor = '')
    {
        $request_uri = $this->sessao[$indice];
        if ($campo != '' || $valor != '')
        {
            if (substr_count($request_uri, $campo) > 0)
            {
                $this->SubstituiValorREQUEST($indice, $campo, $valor);
            }
            else
            {
                $char_split  = '?';
                $parametros  = explode($char_split, $request_uri);
                $request_uri = $parametros[0] . $char_split . $campo . '=' . $valor;
                if (count($parametros) > 1)
                {
                    $request_uri .= '&' . $parametros[1];
                }
            }
            $this->sessao[$indice] = $request_uri;
            $this->saveSessaoNavegacao();
        }

    }

    public function CarregaValorREQUEST($indice = 0, $campo = '')
    {
        $retorno     = '';
        $request_uri = $this->sessao[$indice];
        if ($campo != '' || $valor != '')
        {
            $char_split    = '?';
            $urlParametros = explode($char_split, $request_uri);
            if (count($urlParametros) > 1)
            {
                $char_split2      = '&';
                $camposParametros = explode($char_split2, $urlParametros[1]);
                if (count($camposParametros) > 1)
                {
                    $char_split3 = '=';
                    $request_uri = $urlParametros[0] . $char_split; // pagina
                    for ($i = 0; $i < count($camposParametros); $i++)
                    {
                        $campoRegistrado = explode($char_split3, $camposParametros[$i]);
                        $retorno         = ($campoRegistrado[0] == $campo ? $campoRegistrado[1] : $retorno);
                    }
                }
            }
        }
        return $retorno;

    }

}
