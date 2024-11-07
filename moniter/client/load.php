<?php
require_once '../server/configdb.php';
session_start();
$preferredLetters = $_SESSION['preferredLetters'] ?? ['N', 'AD', 'G'];

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function getColorClass($waitting_time)
{
    if ($waitting_time >= 0 && $waitting_time <= 5) {
        return 'color: red;';
    } elseif ($waitting_time >= 6 && $waitting_time <= 10) {
        return 'color: #eba134;';
    } else {
        return 'color: #18b300;';
    }
}

try {

    date_default_timezone_set('Asia/Bangkok');
    $currentDate = date('dmY');
    $dateTH = date('d') . date('m') . (date('Y') + 543);

    $department = 'ยาด่วน';

    $sql = "SELECT * FROM visit_info WHERE visit_date = :date AND status = 'รอ' AND department = :department ORDER BY CONVERT(INT, waitting_time) , check_in ASC ";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":date", $dateTH);
    $stmt->bindParam(":department", $department);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql_ready = "SELECT * FROM visit_info WHERE visit_date = :date AND status_call = '1' AND department = :department ORDER BY visit_time DESC";
    $stmt_ready = $conn->prepare($sql_ready);
    $stmt_ready->bindParam(":date", $dateTH);
    $stmt_ready->bindParam(":department", $department);
    $stmt_ready->execute();
    $popup = $stmt_ready->fetch(PDO::FETCH_ASSOC);

    $sql_room = "SELECT * FROM visit_info WHERE visit_date = :date AND department = 'ห้องตรวจ' AND status = 'รอ' ORDER BY CONVERT(INT, waitting_q) ASC";
    $stmt_room = $conn->prepare($sql_room);
    $stmt_room->bindParam(":date", $dateTH);
    $stmt_room->execute();
    $room = $stmt_room->fetchAll(PDO::FETCH_ASSOC);

    $sql_roomR = "SELECT * FROM visit_info WHERE visit_date = :date AND department = 'ห้องตรวจ' AND status = 'กำลัง' ORDER BY station DESC";
    $stmt_roomR = $conn->prepare($sql_roomR);
    $stmt_roomR->bindParam(":date", $dateTH);
    $stmt_roomR->execute();
    $roomR = $stmt_roomR->fetch(PDO::FETCH_ASSOC);

    $sql_q = "SELECT * FROM visit_info WHERE visit_date = :date AND status = 'กำลัง' AND department = :department ORDER BY station DESC";
    $stmt_q = $conn->prepare($sql_q);
    $stmt_q->bindParam(":department", $department);
    $stmt_q->bindParam(":date", $dateTH);
    $stmt_q->execute();
    $row_q = $stmt_q->fetchAll(PDO::FETCH_ASSOC);

    $sql_cross = "SELECT * FROM visit_info WHERE status = 'ข้าม'";
    $stmt_cross = $conn->prepare($sql_cross);
    $stmt_cross->execute();
    $cross = $stmt_cross->fetchAll(PDO::FETCH_ASSOC);


    // $sql = "SELECT * FROM visit_info WHERE status = 'รอ' ORDER BY CONVERT(INT, waitting_q) ASC ";
    // $stmt = $conn->prepare($sql);
    // // $stmt->bindParam("department", $department);
    // $stmt->execute();
    // $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // $sql_ready = "SELECT * FROM visit_info WHERE status_call = '1' ORDER BY visit_time DESC";
    // $stmt_ready = $conn->prepare($sql_ready);
    // $stmt_ready->execute();
    // $popup = $stmt_ready->fetch(PDO::FETCH_ASSOC);

    // $sql_room = "SELECT * FROM visit_info WHERE department = 'ห้องตรวจ' AND status = 'รอ' ORDER BY CONVERT(INT, waitting_q) ASC";
    // $stmt_room = $conn->prepare($sql_room);
    // $stmt_room->execute();
    // $room = $stmt_room->fetchAll(PDO::FETCH_ASSOC);

    // $sql_roomR = "SELECT * FROM visit_info WHERE department = 'ห้องตรวจ' AND status = 'กำลัง' ORDER BY station DESC";
    // $stmt_roomR = $conn->prepare($sql_roomR);
    // $stmt_roomR->execute();
    // $roomR = $stmt_roomR->fetch(PDO::FETCH_ASSOC);

    // $sql_q = "SELECT * FROM visit_info WHERE status = 'กำลัง' ORDER BY station DESC";
    // $stmt = $conn->prepare($sql_q);
    // $stmt->execute();
    // $row_q = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // $sql_cross = "SELECT * FROM visit_info WHERE status = 'ข้าม'";
    // $stmt_cross = $conn->prepare($sql_cross);
    // $stmt_cross->execute();
    // $cross = $stmt_cross->fetchAll(PDO::FETCH_ASSOC);

    // echo json_encode($row_q);

    usort($rows, function ($a, $b) use ($preferredLetters) {
        $aPrefix = preg_replace('/\d+/', '', $a['visit_q_no']); // ตัดเฉพาะตัวอักษร
        $bPrefix = preg_replace('/\d+/', '', $b['visit_q_no']);

        $aIndex = array_search($aPrefix, $preferredLetters);
        $bIndex = array_search($bPrefix, $preferredLetters);

        // จัดเรียงตามลำดับที่ตั้งค่า
        if ($aIndex === false)
            $aIndex = count($preferredLetters); // ถ้าไม่เจอใน preferredLetters ให้ไปอยู่ท้าย
        if ($bIndex === false)
            $bIndex = count($preferredLetters);

        return $aIndex <=> $bIndex; // เรียงตามลำดับที่ตั้งค่า
    });

    $stationData = [];
    foreach ($row_q as $rowStation) {

        preg_match('/([A-Z]+)(\d+)/', $rowStation['visit_q_no'], $matchs);
        $prefix = isset($matchs[1]) ? $matchs[1] : '';
        $number = isset($matchs[2]) ? $matchs[2] : '';

        $stationData[] = [
            'station' => $rowStation['station'],
            'name' => $rowStation['name'],
            'surname' => $rowStation['surname'],
            'status' => $rowStation['status'],
            'prefix' => $prefix,
            'number' => $number,
        ];
    }


    $sql_waitroom = "SELECT * FROM visit_info WHERE visit_date = '31102567' AND status = 'ข้าม' AND department = 'ห้องตรวจ'";
    $stmt_waitroom = $conn->prepare($sql_waitroom);
    $stmt_waitroom->execute();
    $waitroom = $stmt_waitroom->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo '<div class="alert alert-danger" role="alert">เกิดข้อผิดพลาด: ขออภัยในความไม่สะดวก' . $e->getMessage() . '</div>';
}

$historyHtml = '';
if ($rows) {
    foreach ($rows as $row) {
        $color = getColorClass($row['waitting_time']);
        $historyHtml .= '
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding-left: 10px; color:#042e5c; font-weight:600;">' . htmlspecialchars($row['visit_q_no']) . '</td>
                <td class="px-4 py-4">' . htmlspecialchars($row['name']) . ' ' . htmlspecialchars($row['surname']) . '</td>
                <td style="' . $color . ' font-weight: 600;">' . htmlspecialchars($row['waitting_time']) . ' นาที</td>
            </tr>';
    }
} else {
    $historyHtml .= '<tr><td colspan="3" class="text-center px-4 py-2">ไม่มีข้อมูล</td></tr> ';
}

$crossData = '';
if ($cross) {
    foreach ($cross as $rowcross) {
        $crossData .= '<p> | คุณ ' . htmlspecialchars($rowcross['name']) . ' ' . htmlspecialchars($rowcross['visit_q_no']) . ' ' . htmlspecialchars($rowcross['station']) . '</p>';
    }
} else {
    $crossData .= '<p class="text-white">ไม่มีการเรียกคิวซ้ำ</p>';
}

$popupTable = '';

$popupData = [];

if ($popup) {
    preg_match('/([A-Z]+)(\d+)/', $popup['visit_q_no'], $matchs);
    $prefix = isset($matchs[1]) ? $matchs[1] : '';
    $number = isset($matchs[2]) ? $matchs[2] : '';

    $popupTable .= '<div class="contentPopup" id="popup">
            <div class="Name">
                <h3 style="
  color: rgb(9, 87, 41);
                ">' . htmlspecialchars($popup['station']) . '</h3>
                <h3 class="text-4xl font-semibold mt-2">' . htmlspecialchars($popup['name']) . ' ' . htmlspecialchars($popup['surname']) . '</h3>
            </div>

            <div class="station-box-number-queue">
                <h1 class="text-white text-3xl font-bold"><span class="text-4xl">' . htmlspecialchars($prefix) . '</span><br>' . htmlspecialchars($number) . '</h1>
            </div>
    </div>';

    $popupData[] = [
        'id' => $popup['id'],
        'station' => $popup['station'],
        'name' => $popup['name'],
        'surname' => $popup['surname'],
        'visit_q_no' => $popup['visit_q_no'],
    ];
}


$row_Qu = '';

if ($row_q) {
    foreach ($row_q as $rowq) {
        preg_match('/([A-Z]+)(\d+)/', $rowq['visit_q_no'], $matchs);
        $prefix = isset($matchs[1]) ? $matchs[1] : '';
        $number = isset($matchs[2]) ? $matchs[2] : '';

        $row_Qu .= '<div class="text-start bg-white rounded-xl p-4 flex items-center justify-between">
        <div>
            <p class="text-2xl text-green-700 font-bold">' . htmlspecialchars($rowq['station']) . '</p>
            <h3 class="text-2xl font-bold mt-2">' . htmlspecialchars($rowq['name']) . ' ' . htmlspecialchars($rowq['surname']) . '</h3>
        </div>
        <div class="bg-orange-500 p-4 w-[100px] h-[100px] text-center items-center flex justify-center rounded-lg">
            <h1 class="text-white text-3xl font-bold"><span class="text-3xl">' . htmlspecialchars($prefix) . '</span><br>' . htmlspecialchars($number) . '</h1>
        </div>
     </div>';
    }
}

// ----------------------------------------------------------------------------------------------------------------


$roomHtml = '';
if ($room) {
    foreach ($room as $r) {
        $roomHtml .= '
            <tr class="text-start border-t font-semibold text-2xl">
                <td class="px-4 py-4 text-center font-bold text-[#042e5c]">' . htmlspecialchars($r['visit_q_no']) . '</td>
                <td class="px-4 py-4">' . htmlspecialchars($r['name']) . ' ' . htmlspecialchars($r['surname']) . '</td>
                <td class="px-4 py-4 text-center font-bold ' . getColorClass($r['waitting_q']) . '">' . htmlspecialchars($r['waitting_q']) . ' คิว</td>
            </tr>';
    }
} else {
    $roomHtml .= '<tr><td colspan="3" class="text-center px-4 py-2">ไม่มีข้อมูล</td></tr>';
}

$popupRoom = '';

if ($roomR) {
    preg_match('/([A-Z]+)(\d+)/', $roomR['visit_q_no'], $matchs);
    $prefix = isset($matchs[1]) ? $matchs[1] : '';
    $number = isset($matchs[2]) ? $matchs[2] : '';

    $popupRoom .= '<div class="contentPopup text-start bg-white rounded-3xl p-6 flex items-center justify-between" id="popuproom">
<div class="flex flex-col text-start">
<h3 class="text-4xl text-green-700 font-semibold">' . htmlspecialchars($roomR['station']) . '</h3>
<h3 class="text-4xl font-semibold mt-2">' . htmlspecialchars($roomR['name']) . ' ' . htmlspecialchars($roomR['surname']) . '</h3>
</div>

<div class="bg-orange-500 p-4 w-[100px] h-[100px] text-center items-center flex justify-center rounded-2xl">
<h1 class="text-white text-3xl font-bold"><span class="text-4xl">' . htmlspecialchars($prefix) . '</span><br>' . htmlspecialchars($number) . '</h1>
</div>
</div>';
}

$waitR = '';

if ($waitroom) {
    foreach ($waitroom as $rowwait) {
        preg_match('/([A-Z]+)(\d+)/', $rowwait['visit_q_no'], $matchs);
        $prefix = isset($matchs[1]) ? $matchs[1] : '';
        $number = isset($matchs[2]) ? $matchs[2] : '';

        $waitR .= '<div class="text-start bg-white rounded-xl p-4 flex items-center justify-between">
        <div>
            <p class="text-2xl text-green-700 font-bold">' . $rowwait['station'] . '</p>
            <h3 class="text-2xl font-bold mt-2">' . htmlspecialchars($rowwait['name']) . ' ' . htmlspecialchars($rowwait['surname']) . '</h3>
        </div>
        <div class="bg-orange-500 p-4 w-[100px] h-[100px] text-center items-center flex justify-center rounded-lg">
            <h1 class="text-white text-3xl font-bold"><span class="text-3xl">' . htmlspecialchars($prefix) . '</span><br>' . htmlspecialchars($number) . '</h1>
        </div>
    </div>';
    }
}


header('Content-Type: application/json');
echo json_encode(['crossData' => $crossData, 'stationData' => $stationData, 'popupData' => $popupData, 'historyHtml' => $historyHtml, 'roomHtml' => $roomHtml, 'popupTable' => $popupTable, 'row_Qu' => $row_Qu, 'popupRoom' => $popupRoom, 'waitR' => $waitR]);








