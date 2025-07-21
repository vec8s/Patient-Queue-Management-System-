<?php
/**
 * Database Migration Script for PQMS
 * 
 * This file creates all necessary tables for the Patient Queue Management System
 */

// Array of table creation queries
$migrations = [
    // Staff table
    "CREATE TABLE IF NOT EXISTS staff (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'doctor', 'nurse', 'receptionist') NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
     // Checkin type table
     "CREATE TABLE IF NOT EXISTS checkin_types (
        id TINYINT PRIMARY KEY,
       type_name VARCHAR(20) NOT NULL,
       description VARCHAR(255)
       ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // Patients table
    "CREATE TABLE IF NOT EXISTS patients (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        date_of_birth DATE NOT NULL,
        gender VARCHAR(20) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        address VARCHAR(20) NOT NULL,
        consent BOOLEAN DEFAULT FALSE, 
        is_registered BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // Appointment types table
    "CREATE TABLE IF NOT EXISTS appointment_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        duration INT NOT NULL COMMENT 'Duration in minutes',
        description TEXT,
        is_active BOOLEAN DEFAULT TRUE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    
    // Enhanced checkins table
    "CREATE TABLE IF NOT EXISTS checkins (
    checkin_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    type_id TINYINT NOT NULL COMMENT '1=appointment, 2=walk-in',
    doctor_id INT NULL COMMENT 'Only for appointments',
    app_type_id INT NULL COMMENT 'Only for appointments',
    reference_number VARCHAR(20) NOT NULL,
    scheduled_time VARCHAR(20) NULL COMMENT 'For appointments',
    appointment_date TIMESTAMP NULL COMMENT 'For appointments',
    actual_checkin_time DATETIME NULL,
    reason TEXT,
    status ENUM('pending','checked_in','in_progress','completed','cancelled','no_show'),
    priority ENUM('low','normal','high','emergency') DEFAULT 'normal',
    consent BOOLEAN DEFAULT FALSE, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (type_id) REFERENCES checkin_types(id),
    FOREIGN KEY (app_type_id) REFERENCES appointment_types(id),
    FOREIGN KEY (doctor_id) REFERENCES staff(id),
    CHECK (
        (type_id = 1 AND app_type_id IS NOT NULL AND scheduled_time IS NOT NULL AND doctor_id IS NOT NULL AND appointment_date IS NOT NULL) OR
        (type_id = 2 AND app_type_id IS NOT NULL AND doctor_id IS NULL)
    )
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // Queue table
    "CREATE TABLE IF NOT EXISTS queue (
        id INT AUTO_INCREMENT PRIMARY KEY,
        checkin_id INT NOT NULL,
        queue_number VARCHAR(10) NOT NULL,
        priority ENUM('low', 'normal', 'high', 'emergency') DEFAULT 'normal',
        status ENUM('waiting', 'called', 'in_progress', 'completed', 'cancelled') DEFAULT 'waiting',
        called_at TIMESTAMP NULL,
        called_by INT,
        room_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (called_by) REFERENCES staff(id),
        FOREIGN KEY (checkin_id) REFERENCES checkins(checkin_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // Rooms table
    "CREATE TABLE IF NOT EXISTS rooms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        description TEXT,
        is_active BOOLEAN DEFAULT TRUE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // Display settings table
    "CREATE TABLE IF NOT EXISTS display_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        display_name VARCHAR(50) NOT NULL,
        location VARCHAR(100) NOT NULL,
        show_wait_time BOOLEAN DEFAULT TRUE,
        show_priority BOOLEAN DEFAULT TRUE,
        theme_color VARCHAR(20) DEFAULT '#0d6efd',
        is_active BOOLEAN DEFAULT TRUE,
        last_ping TIMESTAMP NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // Audit log table
    "CREATE TABLE IF NOT EXISTS audit_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        action VARCHAR(50) NOT NULL,
        table_name VARCHAR(50) NOT NULL,
        record_id INT,
        old_values TEXT,
        new_values TEXT,
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

   
];



// Execute migrations
try {
   
    foreach ($migrations as $migration) {
        $db->query($migration);
    }
     //$db->beginTransaction();
    // Insert initial data (optional)
    $initialData = [
        // Insert default admin user if not exists
        "INSERT IGNORE INTO staff (first_name, last_name, email, password, role) 
         VALUES ('Admin', 'User', 'admin@clinic.com', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'admin')",
        
        "INSERT IGNORE INTO staff (first_name, last_name, email, password, role) 
         VALUES ('Patrick', 'Mvuma', 'mvumapatrick@gmail.com', '" . password_hash('1234554321', PASSWORD_DEFAULT) . "', 'doctor')",

         "INSERT IGNORE INTO staff (first_name, last_name, email, password, role) 
         VALUES ('Hendry', 'Manganda', 'hendry@gmail.com', '" . password_hash('1234554321', PASSWORD_DEFAULT) . "', 'doctor')",

         "INSERT IGNORE INTO staff (first_name, last_name, email, password, role) 
         VALUES ('Isaac', 'Band', 'isaacb@gmail.com', '" . password_hash('1234554321', PASSWORD_DEFAULT) . "', 'doctor')",

         "INSERT IGNORE INTO staff (first_name, last_name, email, password, role) 
         VALUES ('Jane', 'Mand', 'jmanda@gmail.com', '" . password_hash('1234554321', PASSWORD_DEFAULT) . "', 'doctor')",
     
        // Insert some appointment types
        "INSERT IGNORE INTO appointment_types (name, duration, description) VALUES 
         ('General Consultation', 15, 'Regular doctor consultation'),
         ('Follow-up Visit', 10, 'Follow-up appointment'),
         ('Urgent Care', 30, 'Urgent medical attention needed'),
         ('Vaccination', 5, 'Vaccination appointment')",
         
        // Insert some rooms
        "INSERT IGNORE INTO rooms (name, description) VALUES 
         ('Room 1', 'General consultation room'),
         ('Room 2', 'Examination room'),
         ('Room 3', 'Nurse practitioner room')",

         "INSERT INTO checkin_types (id, type_name, description) VALUES 
          (1, 'appointment', 'Pre-scheduled appointment'),
          (2, 'walk_in', 'Walk-in patient without appointment');"
    ];
    
    foreach ($initialData as $data) {
        // Only insert if not exists (for staff and rooms, check by unique fields)
        if (strpos($data, 'INTO staff') !== false) {
            preg_match("/\('([^']+)', '([^']+)', '([^']+)',/", $data, $matches);
            $email = $matches[3] ?? null;
            if ($email) {
                $stmt = $db->prepare("SELECT COUNT(*) FROM staff WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->bind_result($count);
                $stmt->fetch();
                $stmt->close();
                if ($count == 0) {
                    $db->query($data);
                }
            }
        } elseif (strpos($data, 'INTO appointment_types') !== false) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM appointment_types");
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();
            if ($count == 0) {
                $db->query($data);
            }
        } elseif (strpos($data, 'INTO rooms') !== false) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM rooms");
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();
            if ($count == 0) {
                $db->query($data);
            }
    
        } elseif (strpos($data, 'INTO checkin_types') !== false) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM checkin_types");
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        if ($count == 0) {
            $db->query($data);
        }
    } else {
            $db->query($data);
        }
    }
    
    $db->commit();
    
    //echo "Database migration completed successfully!";
    
} catch(PDOException $e) {
    $db->rollBack();
    die("Migration failed: " . $e->getMessage());
}
?>