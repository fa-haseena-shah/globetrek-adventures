<?php 
    require_once "db.php";
    // prepare + execute query safely & return raw results
    function runQuery($conn, $sql, $types, $params) {
        // prepare statement object
        $stmt = mysqli_stmt_init($conn); 
        // load sql containing placeholders into statement obj
        if(!mysqli_stmt_prepare($stmt, $sql)) { 
            die("Query preparation failed: " . mysqli_stmt_error($stmt));
        }
        if(!empty($params)) { 
            // if sql has parameters, bind values to it
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        // execute on db
        mysqli_stmt_execute($stmt);
        return $stmt;
    }

    // fetch a single row
    function fetchOne($conn, $sql, $types = "", $params = []) {
        $stmt = runQuery($conn, $sql, $types, $params);
        $result = mysqli_stmt_get_result($stmt);
        // row received as raw statement then converted to associative array
        return mysqli_fetch_assoc($result);
    }

    // fetch all matching rows
    function fetchAll($conn, $sql, $types = "", $params = []) {
        $stmt = runQuery($conn, $sql, $types, $params);
        $result = mysqli_stmt_get_result($stmt);
        // pulls all matching rows & converts to array of assoc arrays
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // insert record to db
    function insertRow($conn, $sql, $types, $params) {
        runQuery($conn, $sql, $types, $params);
        // get auto-gen id from db
        return mysqli_insert_id($conn);
    }

    // update records in db and return the no of updated records (used for update + delete)
    function updateRow($conn, $sql, $types, $params) {
        runQuery($conn, $sql, $types, $params);
        return mysqli_affected_rows($conn); 
    }

    function deleteRow($conn, $sql, $types, $params) {
        $stmt = runQuery($conn, $sql, $types, $params);
        return mysqli_stmt_affected_rows($stmt);
    }

    // count & return no of rows in the result
    function rowCount($conn, $sql, $types = "", $params = []) {
        $stmt = runQuery($conn, $sql, $types, $params);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_num_rows($result);
    }
?>
