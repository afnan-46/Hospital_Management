<?php
// setup.php
$host = 'localhost';
$db   = 'green_delta_hms';
$user = 'root'; // Change if needed
$pass = '';     // Change if needed
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;charset=$charset";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db`");
    $pdo->exec("USE `$db`");

    // Create Tables
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin','doctor','receptionist','pharmacist','security','department','patient') NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            is_active TINYINT DEFAULT 1
        );

        CREATE TABLE IF NOT EXISTS patients (
            patient_id VARCHAR(10) PRIMARY KEY,
            user_id INT NOT NULL,
            date_of_birth DATE,
            gender ENUM('Male','Female','Other'),
            blood_group VARCHAR(5),
            address TEXT,
            phone VARCHAR(20),
            emergency_contact VARCHAR(100),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS departments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            head_doctor_id INT NULL
        );

        CREATE TABLE IF NOT EXISTS doctors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            department_id INT NOT NULL,
            specialization VARCHAR(100),
            qualification VARCHAR(200),
            available_days VARCHAR(100),
            morning_slot TIME,
            evening_slot TIME,
            fee DECIMAL(10,2),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (department_id) REFERENCES departments(id)
        );

        CREATE TABLE IF NOT EXISTS appointments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id VARCHAR(10) NOT NULL,
            doctor_id INT NULL,
            department_id INT NOT NULL,
            appointment_type ENUM('Doctor','X-Ray','Blood Test','ECG','MRI','Ultrasound') NOT NULL,
            appointment_date DATE NOT NULL,
            appointment_time TIME NOT NULL,
            status ENUM('Pending','Confirmed','Completed','Cancelled') DEFAULT 'Pending',
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (patient_id) REFERENCES patients(patient_id),
            FOREIGN KEY (doctor_id) REFERENCES doctors(id),
            FOREIGN KEY (department_id) REFERENCES departments(id)
        );

        CREATE TABLE IF NOT EXISTS prescriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            appointment_id INT NOT NULL,
            doctor_id INT NOT NULL,
            patient_id VARCHAR(10) NOT NULL,
            medicines TEXT NOT NULL,
            instructions TEXT,
            issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (appointment_id) REFERENCES appointments(id),
            FOREIGN KEY (doctor_id) REFERENCES doctors(id),
            FOREIGN KEY (patient_id) REFERENCES patients(patient_id)
        );

        CREATE TABLE IF NOT EXISTS pharmacy_orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            prescription_id INT NOT NULL,
            patient_id VARCHAR(10) NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            payment_method ENUM('Visa','Bkash','Nagad','Rocket','American Express','Cash'),
            payment_status ENUM('Pending','Paid','Failed') DEFAULT 'Pending',
            transaction_id VARCHAR(100),
            dispensed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (prescription_id) REFERENCES prescriptions(id),
            FOREIGN KEY (patient_id) REFERENCES patients(patient_id)
        );

        CREATE TABLE IF NOT EXISTS medicines_inventory (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            category VARCHAR(50),
            price DECIMAL(10,2) NOT NULL,
            stock INT NOT NULL DEFAULT 0,
            expiry_date DATE NOT NULL
        );
    ");

    // Insert Dummy Users
    $default_pass = password_hash('1234', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, email, password, role, full_name) VALUES (?, ?, ?, ?, ?)");
    
    $users = [
        ['admin', 'admin@greendeltahospital.com', $default_pass, 'admin', 'Admin User'],
        ['doctor1', 'doctor1@greendeltahospital.com', $default_pass, 'doctor', 'Dr. Aminul Islam'],
        ['reciption', 'reciption@greendeltahospital.com', $default_pass, 'receptionist', 'Roksana Khanam'],
        ['medicine', 'medicine@greendeltahospital.com', $default_pass, 'pharmacist', 'Milon Sarker'],
        ['patient1', 'patient1@greendeltahospital.com', $default_pass, 'patient', 'Rahim Uddin']
    ];

    foreach ($users as $u) { $stmt->execute($u); }

    // Dummy Departments & Doctors
    $pdo->exec("INSERT IGNORE INTO departments (id, name, description) VALUES (1, 'Cardiology', 'Heart and vascular care'), (2, 'Neurology', 'Brain and nervous system')");
    $pdo->exec("INSERT IGNORE INTO doctors (id, user_id, department_id, specialization, available_days, morning_slot, evening_slot, fee) VALUES (1, 2, 1, 'Cardiologist', 'Sat,Sun,Mon', '09:00:00', '17:00:00', 800.00)");
    
    // Dummy Patient
    $pdo->exec("INSERT IGNORE INTO patients (patient_id, user_id, gender, blood_group) VALUES ('GDH-00001', 5, 'Male', 'B+')");

    echo "Database & Dummy Data installed successfully. Default password is '1234'. Please delete setup.php.";
} catch (PDOException $e) {
    die("Setup failed: " . $e->getMessage());
}
?>