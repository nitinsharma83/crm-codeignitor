<?php

//header("Refresh: 120");

date_default_timezone_set('Asia/Kolkata');

ini_set('display_errors', 1);
error_reporting(E_ALL);

ini_set('memory_limit', '512M');

set_time_limit(0);

ignore_user_abort(true);

echo "<pre>";

echo "=====================================================\n";
echo "===== APPOINTMENT PENDING AUTO CALL CRON START ======\n";
echo "Start Time : " . date("d-m-Y h:i:s A") . "\n";
echo "=====================================================\n\n";

/* =====================================================
   CHECK CURL
===================================================== */

if (!function_exists('curl_version')) {

    die("❌ CURL EXTENSION NOT ENABLED\n");
}

echo "✅ CURL ENABLED\n\n";

/* =====================================================
   DB CONNECTION
===================================================== */

$conn = new mysqli(
    "localhost",
    "u457158986_team",
    "#Team@123",
    "u457158986_team"
);

if ($conn->connect_error) {

    die("❌ DB Connection Failed : " . $conn->connect_error);
}

echo "✅ Database Connected\n\n";

/* =====================================================
   FETCH SINGLE ELIGIBLE LEAD
   ONLY FOR AGENT 05
===================================================== */

echo "🔄 Fetching single eligible lead for Agent 05...\n\n";

$sql = "

SELECT 
    l.id,
    l.customer_name,
    l.mobile_no,
    l.status,
    l.created_date,
    l.assigned_to,

    r.full_name,
    r.status AS agent_status,
    r.location AS api_key

FROM tbl_lead l

INNER JOIN tbl_registration r 
ON TRIM(LOWER(r.full_name)) = TRIM(LOWER(l.assigned_to))

WHERE l.status = 'Appointment Pending'

AND DATE(l.created_date) <= DATE_SUB(CURDATE(), INTERVAL 2 DAY)

AND r.status = 'active'

AND r.location IS NOT NULL
AND r.location != ''

AND TRIM(LOWER(l.assigned_to)) = 'agent 05'
AND TRIM(LOWER(r.full_name)) = 'agent 05'

ORDER BY l.id ASC

LIMIT 1

";

echo "SQL QUERY:\n$sql\n\n";

$result = $conn->query($sql);

if (!$result) {

    die("❌ SQL Error : " . $conn->error);
}

/* =====================================================
   COUNTERS
===================================================== */

$totalCalls   = 0;
$totalFailed  = 0;
$totalUpdated = 0;

/* =====================================================
   PROCESS LEAD
===================================================== */

if ($result->num_rows > 0) {

    $row = $result->fetch_assoc();

    $leadId = $row['id'];

    $apiKey = trim($row['api_key']);

    /* ============================================
       MOBILE NORMALIZATION
    ============================================ */

    $customerNumber = preg_replace('/\D/', '', $row['mobile_no']);
    $customerNumber = substr($customerNumber, -10);

    echo "--------------------------------------------------\n";
    echo "Lead ID       : " . $leadId . "\n";
    echo "Customer      : " . $row['customer_name'] . "\n";
    echo "Mobile        : " . $customerNumber . "\n";
    echo "Assigned To   : " . $row['assigned_to'] . "\n";
    echo "Agent Status  : " . $row['agent_status'] . "\n";
    echo "API Key       : " . $apiKey . "\n";
    echo "Created Date  : " . $row['created_date'] . "\n";
    echo "--------------------------------------------------\n\n";

    /* ============================================
       VALIDATION
    ============================================ */

    if (strlen($customerNumber) != 10) {

        echo "❌ Invalid Mobile Number\n";

        $totalFailed++;

        $newStatus = "Contacted";
        $newTag    = "INVALID_NUMBER";

    } elseif (empty($apiKey)) {

        echo "❌ API Key Missing\n";

        $totalFailed++;

        $newStatus = "Contacted";
        $newTag    = "NO_API_KEY";

    } else {

        /* ============================================
           API PAYLOAD
        ============================================ */

        $payload = [
            "api_key"         => $apiKey,
            "customer_number" => $customerNumber,
            "get_call_id"     => 1
        ];

        echo "📞 Sending API Request...\n\n";

        echo "Payload:\n";
        print_r($payload);
        echo "\n";

        /* ============================================
           CURL REQUEST
        ============================================ */

        $curl = curl_init();

        curl_setopt_array($curl, [

            CURLOPT_URL => "https://api-smartflo.tatateleservices.com/v1/click_to_call_support",

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_POST => true,

            CURLOPT_POSTFIELDS => json_encode($payload),

            CURLOPT_HTTPHEADER => [
                "Accept: application/json",
                "Content-Type: application/json"
            ],

            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,

            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,

            CURLOPT_DNS_CACHE_TIMEOUT => 120,

            CURLOPT_CONNECTTIMEOUT => 20,

            CURLOPT_TIMEOUT => 40,

            CURLOPT_FOLLOWLOCATION => true,

            CURLOPT_MAXREDIRS => 5,

        ]);

        $response = curl_exec($curl);

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $curlError = curl_error($curl);

        curl_close($curl);

        /* ============================================
           DEBUG RESPONSE
        ============================================ */

        echo "HTTP CODE : " . $httpCode . "\n\n";

        echo "RAW RESPONSE:\n";
        print_r($response);
        echo "\n\n";

        /* ============================================
           CURL ERROR
        ============================================ */

        if (!empty($curlError)) {

            echo "❌ CURL ERROR : " . $curlError . "\n";

            $totalFailed++;

            $newStatus = "Contacted";
            $newTag    = "CURL_ERROR";

        } elseif ($httpCode != 200) {

            echo "❌ API HTTP ERROR : " . $httpCode . "\n";

            $totalFailed++;

            $newStatus = "Contacted";
            $newTag    = "HTTP_" . $httpCode;

        } else {

            /* ============================================
               DECODE RESPONSE
            ============================================ */

            $responseData = json_decode($response, true);

            echo "Decoded Response:\n";
            print_r($responseData);
            echo "\n";

            /* ============================================
               SUCCESS CHECK
            ============================================ */

            if (
                isset($responseData['success']) &&
                $responseData['success'] == true
            ) {

                $totalCalls++;

                echo "✅ Call Triggered Successfully\n\n";

                $newStatus = "Contacted";
                $newTag    = "AMB2026";

            } else {

                $totalFailed++;

                echo "❌ API Returned Failure\n";

                if (isset($responseData['message'])) {

                    echo "Reason : " . $responseData['message'] . "\n";
                }

                if (isset($responseData['ref_id'])) {

                    echo "Ref ID : " . $responseData['ref_id'] . "\n";
                }

                /* ============================================
                   STILL UPDATE STATUS
                ============================================ */

                $newStatus = "Contacted";
                $newTag    = "AMB_FAILED";
            }
        }
    }

    /* ============================================
       UPDATE LEAD
    ============================================ */

    if (isset($newStatus) && isset($newTag)) {

        $update = $conn->prepare("

            UPDATE tbl_lead

            SET
                status = ?,
                tag = ?

            WHERE id = ?

        ");

        if (!$update) {

            echo "❌ Prepare Failed : " . $conn->error . "\n";

        } else {

            $update->bind_param("ssi", $newStatus, $newTag, $leadId);

            if ($update->execute()) {

                $totalUpdated++;

                echo "✅ Lead Updated Successfully\n";
                echo "Updated Status : " . $newStatus . "\n";
                echo "Updated Tag    : " . $newTag . "\n";

            } else {

                echo "❌ Update Execute Failed : " . $update->error . "\n";
            }

            $update->close();
        }
    }

} else {

    echo "⚠️ No Eligible Leads Found For Agent 05\n";
}

/* =====================================================
   FINAL SUMMARY
===================================================== */

echo "\n=====================================================\n";

echo "TOTAL CALL SUCCESS : " . $totalCalls . "\n";

echo "TOTAL UPDATED      : " . $totalUpdated . "\n";

echo "TOTAL FAILED       : " . $totalFailed . "\n";

echo "CRON END TIME      : " . date("d-m-Y h:i:s A") . "\n";

echo "===== CRON COMPLETED =====\n";

echo "=====================================================\n";

echo "</pre>";

?>