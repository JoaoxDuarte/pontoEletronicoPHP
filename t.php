 <?php
 echo "leo";
        $conexao = mysql_connect("localhost","sisref_app","SisReF2013app") or die("Erro de Conexão com o banco de dados: ".mysql_error());
        $dataBase = mysql_select_db("linux_base",$conexao) or die("Banco de dados não foi localizado: ".mysql_error());

        print "Conexão realizada com sucesso";

?>
