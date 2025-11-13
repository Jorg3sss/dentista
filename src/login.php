<?php
session_start();
require_once("../db/conexion.php");

if(isset($_GET)){
    $op = $_GET["id"];

    if($op==1){
        if(isset($_POST)){
            $correo = $_POST["correo"];
            $contra = $_POST["contraseña"];

            if(!str_contains($correo, ".com")){
                $_SESSION["error"] = "El correo no contiene .com al final";
                header("credenciales.php");
                exit();
            }

            try{
                
            }
            catch(PDOException $e){

            }
        }
    }
    if($op==2){

    }
}

?>