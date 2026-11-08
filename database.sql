-- ฐานข้อมูลโครงงาน "รวมงาน"
-- วิธีใช้: เปิด phpMyAdmin > แท็บ Import > เลือกไฟล์นี้ > กด Go

CREATE DATABASE IF NOT EXISTS raomngan DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE raomngan;
SET NAMES utf8mb4;

DROP TABLE IF EXISTS tasks;
DROP TABLE IF EXISTS subjects;
DROP TABLE IF EXISTS users;

-- ตารางผู้ใช้
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ตารางรายวิชา
CREATE TABLE subjects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  code VARCHAR(20),
  name VARCHAR(150) NOT NULL,
  color VARCHAR(7) DEFAULT '#2F5D50',
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ตารางงาน
-- type: solo = งานเดี่ยว, group = งานกลุ่ม
-- level: 1 = ง่าย, 2 = ปานกลาง, 3 = ยาก
-- status: todo = ยังไม่เริ่ม, doing = กำลังทำ, done = เสร็จแล้ว
CREATE TABLE tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  subject_id INT NULL,
  title VARCHAR(200) NOT NULL,
  type VARCHAR(10) DEFAULT 'solo',
  due_date DATETIME NOT NULL,
  level TINYINT DEFAULT 2,
  important TINYINT DEFAULT 1,
  status VARCHAR(10) DEFAULT 'todo',
  note TEXT,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ===== ข้อมูลตัวอย่างสำหรับสาธิต =====
-- อีเมล demo@swu.ac.th  รหัสผ่าน demo1234
INSERT INTO users (name, email, password) VALUES
('นิสิตตัวอย่าง', 'demo@swu.ac.th', '$2y$10$BO4OI6t/iPspSGalxmP6L.jJbTPYmT3agYhul66FsPfkbd.Fvestm');

INSERT INTO subjects (user_id, code, name, color) VALUES
(1, 'ET361', 'การพัฒนาโครงงานคอมพิวเตอร์เพื่อการศึกษา', '#2F5D50'),
(1, 'ET342', 'การออกแบบสื่อปฏิสัมพันธ์', '#7A5C99'),
(1, 'ET315', 'สถิติเพื่อการวิจัยทางการศึกษา', '#3E6A8A'),
(1, 'SWU141', 'ภาษาอังกฤษเพื่อการสื่อสาร', '#9A7B1F'),
(1, 'ET327', 'การผลิตสื่อวิดีโอ', '#8A4B5C');

-- วันส่งนับจากวันที่ import ไฟล์นี้ เพื่อให้ข้อมูลดูเป็นปัจจุบันเสมอ
INSERT INTO tasks (user_id, subject_id, title, type, due_date, level, important, status) VALUES
(1, 1, 'ส่งเค้าโครงโครงงานฉบับแก้ไข', 'group', CONCAT(CURDATE() + INTERVAL 2 DAY, ' 23:59'), 3, 1, 'doing'),
(1, 1, 'ทำ Wireframe หน้าหลักของแอป', 'group', CONCAT(CURDATE() + INTERVAL 9 DAY, ' 23:59'), 2, 1, 'todo'),
(1, 2, 'ต้นแบบสื่อปฏิสัมพันธ์ (Figma)', 'solo', CONCAT(CURDATE() + INTERVAL 3 DAY, ' 09:00'), 3, 1, 'todo'),
(1, 3, 'แบบฝึกหัดบทที่ 4 การทดสอบที', 'solo', CONCAT(CURDATE() + INTERVAL 2 DAY, ' 12:00'), 2, 0, 'todo'),
(1, 4, 'Presentation หัวข้อ My hometown', 'solo', CONCAT(CURDATE() + INTERVAL 6 DAY, ' 13:00'), 1, 1, 'todo'),
(1, 5, 'ถ่ายทำคลิปสั้นงานกลุ่ม', 'group', CONCAT(CURDATE() + INTERVAL 3 DAY, ' 23:59'), 3, 1, 'todo'),
(1, 3, 'สรุปบทความวิจัย 1 เรื่อง', 'solo', CONCAT(CURDATE() + INTERVAL 12 DAY, ' 23:59'), 2, 0, 'todo'),
(1, 4, 'Vocabulary quiz 3', 'solo', CONCAT(CURDATE() - INTERVAL 1 DAY, ' 10:00'), 1, 0, 'todo'),
(1, 2, 'อ่านบทที่ 2 และตอบคำถามท้ายบท', 'solo', CONCAT(CURDATE() - INTERVAL 3 DAY, ' 23:59'), 1, 0, 'done');
