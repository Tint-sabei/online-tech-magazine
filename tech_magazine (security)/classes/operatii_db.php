<?php

class OperatiiDB{

    //CRUD Interface catre baza de date
    public static function read($tabel, $query = "", $params = []) {   // Now accepts an optional $params array for security
        require_once 'Database.php';
        $conn = Database::getInstance()->getConnection();

        $sql = "SELECT * FROM $tabel $query";
        $stmt = $conn->prepare($sql);
        
        // Execute with params to sanitize data like 'WHERE id = ?'
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function create($tabel, $valori){
        require_once 'Database.php';

        $conn = Database::getInstance()->getConnection();
        $coloaneNeformatate = implode(",",array_keys($valori));
        $coloane = array_keys($valori);
        for ($i = 0; $i < count($coloane); $i++) {
            $coloane[$i] = ":" . $coloane[$i]; // SQL injection prevention
        }
        $coloane = implode(", ", $coloane);

        $sql = "INSERT INTO $tabel ($coloaneNeformatate) VALUES ($coloane)";
        // echo $sql;
        // echo var_dump($valori);
        $stmt = $conn->prepare($sql);
        $stmt->execute($valori);  

        return $conn->lastInsertId();
    }

    public static function update($tabel, $valori, $conditie){
        require_once 'Database.php';

        $conn = Database::getInstance()->getConnection();
        $coloane = array_keys($valori);
        for ($i = 0; $i < count($coloane); $i++) {
            $coloane[$i] = $coloane[$i] . "=:" . $coloane[$i];
        }
        $coloane = implode(", ", $coloane);

        $sql = "UPDATE $tabel SET $coloane WHERE $conditie";
        $stmt = $conn->prepare($sql);  // two-step process
        $stmt->execute($valori);  // two-step process
    }

    public static function delete($tabel, $id_column, $id_value) {
        require_once 'Database.php';
        $conn = Database::getInstance()->getConnection();

        // We use a placeholder (?) so the $id_value is never part of the SQL string
        $sql = "DELETE FROM $tabel WHERE $id_column = ?";
        $stmt = $conn->prepare($sql);
        
        return $stmt->execute([$id_value]);
    }
}
