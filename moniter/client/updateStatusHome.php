<?php 
include '../server/configdb.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['status_call']) && isset($_POST['id'])) {
        $status_call = $_POST['status_call'];
        $id = $_POST['id'];

        $sql = "UPDATE visit_info SET status_call = :status_call WHERE id = :id AND visit_date = '31102567'";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':status_call' , $status_call);
        $stmt->bindParam(':id' , $id);

        if($stmt->execute()) {
            echo "Status Update Success!";
        }else {
            echo "Error";
        }
    }
} 
?>