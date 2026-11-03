-- ========================================================
-- Patient Queue Management System (PQS)
-- Clean Synthetic Seed Data (Development / Testing)
-- ========================================================

SET NAMES utf8mb4;

-- 1. Appointment Types
INSERT INTO `appointment_types` (`id`, `name`, `duration`, `description`, `is_active`) VALUES
(1, 'استشارة عامة', 15, 'استشارة وفحص عام لدى الطبيب', 1),
(2, 'زيارة متابعة', 10, 'متابعة نتائج التحاليل والتشخيص', 1),
(3, 'رعاية طارئة', 30, 'حالات عاجلة وتدخل فوري', 1),
(4, 'تطعيم ولقاحات', 10, 'جرعات التطعيم والتحصين', 1)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 2. Check-in Types
INSERT INTO `checkin_types` (`id`, `type_name`, `description`) VALUES
(1, 'appointment', 'حجز موعد مسبق'),
(2, 'walk_in', 'حضور مباشر بدون موعد')
ON DUPLICATE KEY UPDATE `type_name`=VALUES(`type_name`);

-- 3. Clinic Consultation Rooms
INSERT INTO `rooms` (`id`, `name`, `description`, `is_active`) VALUES
(1, 'عيادة 1', 'غرفة الاستشارة والكشف العام', 1),
(2, 'عيادة 2', 'غرفة الفحص والملاحظة', 1),
(3, 'عيادة 3', 'غرفة التمريض والتطعيمات', 1)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 4. Initial Sanitized Staff Accounts
-- Admin : admin@clinic.local  / Password: Admin@123
-- Doctor: doctor@clinic.local / Password: Doctor@123
INSERT INTO `staff` (`id`, `first_name`, `last_name`, `email`, `password`, `role`, `is_active`) VALUES
(1, 'مدير', 'النظام', 'admin@clinic.local', '$2y$12$yES04hovicm2U6ve0/SfkOUBk9PVulCFHh1fWCC.HGNh2QGEobrUK', 'admin', 1),
(2, 'د. سارة', 'أحمد', 'doctor@clinic.local', '$2y$12$piKtWKvMqHvTf56UPIxMuOAQvfY/DhijmIjDtuMtztxJsxtmwAmYa', 'doctor', 1),
(3, 'د. خالد', 'المنصور', 'doctor2@clinic.local', '$2y$12$piKtWKvMqHvTf56UPIxMuOAQvfY/DhijmIjDtuMtztxJsxtmwAmYa', 'doctor', 1)
ON DUPLICATE KEY UPDATE `email`=VALUES(`email`);

-- 5. Safe Synthetic Dummy Patients (No Real People)
INSERT INTO `patients` (`id`, `first_name`, `last_name`, `date_of_birth`, `gender`, `phone`, `address`, `consent`, `is_registered`) VALUES
(1, 'عبدالله', 'الشهري', '1992-05-14', 'male', '0500000001', 'حي الملز، الرياض', 1, 1),
(2, 'فاطمة', 'العتيبي', '1996-08-22', 'female', '0500000002', 'حي الروضة، جدة', 1, 1),
(3, 'محمد', 'الغامدي', '1989-11-03', 'male', '0500000003', 'حي الشاطئ، الدمام', 1, 1)
ON DUPLICATE KEY UPDATE `phone`=VALUES(`phone`);

