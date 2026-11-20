<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'technovate_db');
define('DB_CHARSET', 'utf8mb4');

$pdo = null;
$db_connected = false;
$db_error = null;

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_TIMEOUT            => 2,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    $db_connected = true;
} catch (PDOException $e) {
    $db_connected = false;
    $db_error = $e->getMessage();
}

function generate_next_reg_id() {
    global $pdo, $db_connected;

    $next_number = 1;

    if ($db_connected && $pdo) {
        try {
            $stmt = $pdo->query("SELECT MAX(id) AS max_id FROM registrations");
            $row = $stmt->fetch();
            if ($row && !empty($row['max_id'])) {
                $next_number = (int)$row['max_id'] + 1;
            } else {
                $next_number = 1;
            }
            return 'TEC2026-' . sprintf('%04d', $next_number);
        } catch (Exception $e) {
        }
    }

    $data_dir = __DIR__ . DIRECTORY_SEPARATOR . 'data';
    $json_file = $data_dir . DIRECTORY_SEPARATOR . 'registrations.json';

    if (file_exists($json_file)) {
        $content = @file_get_contents($json_file);
        $records = json_decode($content, true) ?: [];
        $next_number = count($records) + 1;
    }

    return 'TEC2026-' . sprintf('%04d', $next_number);
}

function get_registration_by_id($reg_id) {
    global $pdo, $db_connected;

    if ($db_connected && $pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM registrations WHERE reg_id = :reg_id LIMIT 1");
            $stmt->execute([':reg_id' => $reg_id]);
            $row = $stmt->fetch();
            if ($row) {
                if (!empty($row['event_categories']) && is_string($row['event_categories'])) {
                    $row['event_categories'] = array_map('trim', explode(',', $row['event_categories']));
                }
                return $row;
            }
        } catch (Exception $e) {
        }
    }

    $data_dir = __DIR__ . DIRECTORY_SEPARATOR . 'data';
    $json_file = $data_dir . DIRECTORY_SEPARATOR . 'registrations.json';
    if (file_exists($json_file)) {
        $content = @file_get_contents($json_file);
        $records = json_decode($content, true) ?: [];
        foreach ($records as $record) {
            if (isset($record['reg_id']) && $record['reg_id'] === $reg_id) {
                return $record;
            }
        }
    }

    return null;
}

function save_registration_record($record) {
    global $pdo, $db_connected;

    $saved_in_db = false;
    $saved_in_file = false;

    if ($db_connected && $pdo) {
        try {
            $sql = "INSERT INTO registrations 
                (reg_id, student_name, reg_number, email, phone, college_name, department, year_of_study, event_categories, participation_date, gender, message)
                VALUES 
                (:reg_id, :student_name, :reg_number, :email, :phone, :college_name, :department, :year_of_study, :event_categories, :participation_date, :gender, :message)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':reg_id'             => $record['reg_id'],
                ':student_name'       => $record['student_name'],
                ':reg_number'         => $record['reg_number'],
                ':email'              => $record['email'],
                ':phone'              => $record['phone'],
                ':college_name'       => $record['college_name'],
                ':department'         => $record['department'],
                ':year_of_study'      => $record['year_of_study'],
                ':event_categories'   => is_array($record['event_categories']) ? implode(', ', $record['event_categories']) : $record['event_categories'],
                ':participation_date' => $record['participation_date'],
                ':gender'             => $record['gender'],
                ':message'            => $record['message'] ?? ''
            ]);
            $saved_in_db = true;
        } catch (Exception $e) {
            $saved_in_db = false;
        }
    }

    $data_dir = __DIR__ . DIRECTORY_SEPARATOR . 'data';
    if (!is_dir($data_dir)) {
        @mkdir($data_dir, 0755, true);
    }

    $json_file = $data_dir . DIRECTORY_SEPARATOR . 'registrations.json';
    $existing = [];
    if (file_exists($json_file)) {
        $content = @file_get_contents($json_file);
        $existing = json_decode($content, true) ?: [];
    }

    $existing[] = array_merge($record, ['timestamp' => date('Y-m-d H:i:s')]);
    if (@file_put_contents($json_file, json_encode($existing, JSON_PRETTY_PRINT))) {
        $saved_in_file = true;
    }

    return [
        'success'       => ($saved_in_db || $saved_in_file),
        'saved_in_db'   => $saved_in_db,
        'saved_in_file' => $saved_in_file
    ];
}
?>
