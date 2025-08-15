<?php
require_once("C:\\Users\\Milo\\Consolis\\ui\\figlet.php");

FIGLET::$fontPath = "C:\\Users\\Milo\\Consolis\\tests\\figlet\\fonts\\ANSI Regular.flf";
echo "\033[2J";
FIGLET::draw("Hallosh Ballosh", 10, 20);