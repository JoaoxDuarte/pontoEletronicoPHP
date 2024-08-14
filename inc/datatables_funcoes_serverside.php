<?php

include_once( "config.php" );

verifica_permissao("administracao_central");

/**
 * Paging
 *
 * Construct the LIMIT clause for server-side processing SQL query
 *
 *  @param  array $request Data sent to server by DataTables
 *  @param  array $columns Column information array
 *  @return string SQL limit clause
 *
 * Informação enviada do formulário para ordenar
 * &start=0
 * &length=10
 */
function limit( $request, $columns=null )
{
	$limit = '';

	if ( isset($request['start']) && $request['length'] != -1 ) {
		$limit = "LIMIT ".intval($request['start']).", ".intval($request['length']);
	}

	return $limit;
}


/**
 * Searching / Filtering
 *
 * Construct the WHERE clause for server-side processing SQL query.
 *
 * NOTE this does not match the built-in DataTables filtering which does it
 * word by word on any field. It's possible to do here performance on large
 * databases would be very poor
 *
 *  @param  array $request Data sent to server by DataTables
 *  @param  array $columns Column information array
 *  @param  array $bindings Array of values for PDO bindings, used in the
 *    sql_exec() function
 *  @return string SQL where clause
 *
 * Informação enviada do formulário para ordenar
 * &columns[2][data]=2
 * &columns[2][name]=
 * &columns[2][searchable]=true
 * &columns[2][orderable]=true
 * &columns[2][search][value]=
 * &columns[2][search][regex]=false
 *
 * &search[value]=
 * &search[regex]=false
 */
function filter( $request, $columns )
{
    $globalSearch = array();

    if ( isset($request['search']) && $request['search']['value'] != '' )
    {
        $str = $request['search']['value'];

        foreach ($columns as $key => $value)
        {
            $globalSearch[] = "".$value." LIKE '%".$str."%'";
        }
    }

    // Combine the filters into a single string
    $where = '';

    if ( count( $globalSearch ) ) {
        $where = '('.implode(' OR ', $globalSearch).')';
    }

    if ( $where !== '' ) {
        $where = ' AND '.$where;
    }

    return $where;
}


/**
 * Ordering
 *
 * Construct the ORDER BY clause for server-side processing SQL query
 *
 *  @param  array $request Data sent to server by DataTables
 *  @param  array $columns Column information array
 *  @return string SQL order by clause
 *
 * Informação enviada do formulário para ordenar
 * &columns[1][data]=1
 * &columns[1][name]=
 * &columns[1][searchable]=true
 * &columns[1][orderable]=true
 * &columns[1][search][value]=
 * &columns[1][search][regex]=false
 *
 * &order[0][column]=1
 * &order[0][dir]=asc
 */
function order( $request, $columns )
{
    $order = '';

    if ( isset($request['order']) && count($request['order']) )
    {
        $orderBy   = array();
        $dtColumns = array();

        foreach ($columns as $key => $value)
        {
            $dtColumns[] = $key;
        }

        for ( $i=0, $ien=count($request['order']) ; $i<$ien ; $i++ )
        {
            // Convert the column index into the column data property
            $columnIdx     = intval($request['order'][$i]['column']);
            $requestColumn = $request['columns'][$columnIdx];

            $columnIdx = array_search( $requestColumn['data'], $dtColumns );
            $column = $columns[ $columnIdx ];

            if ( $requestColumn['orderable'] == 'true' )
            {
                $dir = $request['order'][$i]['dir'] === 'asc' ?
                        'ASC' :
                        'DESC';

                $orderBy[] = ''.$column.' '.$dir;
            }
        }

        if ( count( $orderBy ) )
        {
            $order = 'ORDER BY '.implode(', ', $orderBy);
        }
    }

    return $order;
}


/* EXEMPLO DOS DADOS ENIADOS PARA PESQUISA

draw=2

&columns[0][data]=0
&columns[0][name]=
&columns[0][searchable]=true
&columns[0][orderable]=true
&columns[0][search][value]=
&columns[0][search][regex]=false

&columns[1][data]=1
&columns[1][name]=
&columns[1][searchable]=true
&columns[1][orderable]=true
&columns[1][search][value]=
&columns[1][search][regex]=false

&columns[2][data]=2
&columns[2][name]=
&columns[2][searchable]=true
&columns[2][orderable]=true
&columns[2][search][value]=
&columns[2][search][regex]=false

&columns[3][data]=3
&columns[3][name]=
&columns[3][searchable]=true
&columns[3][orderable]=true
&columns[3][search][value]=
&columns[3][search][regex]=false

&columns[4][data]=4
&columns[4][name]=
&columns[4][searchable]=true
&columns[4][orderable]=true
&columns[4][search][value]=
&columns[4][search][regex]=false

&columns[5][data]=5
&columns[5][name]=
&columns[5][searchable]=true
&columns[5][orderable]=true
&columns[5][search][value]=
&columns[5][search][regex]=false

&order[0][column]=1
&order[0][dir]=asc

&start=0
&length=10

&search[value]=
&search[regex]=false

&_=1581265859180
*/