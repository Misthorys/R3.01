
<?php 
try {
            $linkpdo = new PDO("mysql:host=localhost;dbname=carnet_contact", "root", '');
            }
            catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
            }
        

?>