<?php

include "db_config.php"; // Using database connection file here

$id = $_GET['id']; // get id through query string

$del = mysqli_query($mysqli,"delete from tbl_lead where id = '$id'"); // delete query

if($del)
{
    mysqli_close($mysqli); // Close connection
    header("location:duplicate"); // redirects to all records page
    exit;	
}
else
{
    echo "Error deleting record"; // display error message if not delete
}
?>