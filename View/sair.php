<?php
session_start(); // Garante que a sessão está ativa
$objController = new Controller();
$objController->logout();
 // Chama o método que faz o logout
?>
