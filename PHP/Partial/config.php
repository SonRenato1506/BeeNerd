<?php

$dbHost = 'Localhost';
$dbUsername = 'root';
$dbpassword = '';
$dbname = 'dnnerds';

$conexao = new mysqli($dbHost, $dbUsername, $dbpassword, $dbname);

if($conexao->connect_errno){
    echo "Erro";
}else{
    // echo "Conecxão efetuada com sucesso";
}
?>