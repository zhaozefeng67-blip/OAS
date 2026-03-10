SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

DROP DATABASE IF EXISTS test;
CREATE DATABASE test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE test;

-- ====================================
-- Create all tables (with primary key definitions)
-- ====================================

CREATE TABLE `apply` (
  `ID` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `status` varchar(256) DEFAULT 'Pending',
  `apply_date` date DEFAULT NULL,
  PRIMARY KEY (`ID`, `sid`, `pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `competition_grade` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `ID` int(11) NOT NULL,
  `c_name` varchar(256) NOT NULL,
  `prize` varchar(256) NOT NULL,
  `duration` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=17;

CREATE TABLE `files` (
  `fid` int(11) NOT NULL AUTO_INCREMENT,
  `dir_path` varchar(256) NOT NULL,
  `type` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`fid`, `dir_path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=6;

CREATE TABLE `intership` (
  `iid` int(11) NOT NULL AUTO_INCREMENT,
  `ID` int(11) NOT NULL,
  `company` varchar(256) DEFAULT NULL,
  `position` varchar(256) DEFAULT NULL,
  `duration` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`iid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=10004;

CREATE TABLE `language_grade` (
  `lid` int(11) NOT NULL AUTO_INCREMENT,
  `ID` int(11) NOT NULL,
  `listening` decimal(3,1) DEFAULT NULL,
  `speaking` decimal(3,1) DEFAULT NULL,
  `writing` decimal(3,1) DEFAULT NULL,
  `reading` decimal(3,1) DEFAULT NULL,
  `type` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`lid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=10003;

CREATE TABLE `operator_school` (
  `ID` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'approved',
  PRIMARY KEY (`ID`, `sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `profile` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(256) NOT NULL,
  `password` varchar(256) NOT NULL,
  `real_name` varchar(256) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `email` varchar(256) NOT NULL,
  `type` varchar(256) DEFAULT 'student',
  `status` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=50015;

CREATE TABLE `program` (
  `sid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `ddl` date DEFAULT NULL,
  `pname` varchar(256) DEFAULT NULL,
  `duration` varchar(256) DEFAULT NULL,
  `gpa_requirement` decimal(3,2) DEFAULT NULL,
  `language_requirement` varchar(1024) DEFAULT NULL,
  `category` varchar(256) DEFAULT NULL,
  `degree_type` varchar(50) DEFAULT 'Master',
  PRIMARY KEY (`sid`, `pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `region` (
  `rid` int(11) NOT NULL AUTO_INCREMENT,
  `country` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=10000;

CREATE TABLE `school` (
  `sid` int(11) NOT NULL AUTO_INCREMENT,
  `rid` int(11) DEFAULT NULL,
  `school_name` varchar(255) NOT NULL,
  `QS_rank` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `website` varchar(256) DEFAULT NULL,
  `image` longblob DEFAULT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=8;

CREATE TABLE `student` (
  `ID` int(11) NOT NULL,
  `rid` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `student_files` (
  `ID` int(11) NOT NULL,
  `fid` int(11) NOT NULL,
  PRIMARY KEY (`ID`, `fid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `undergraduate` (
  `ID` int(11) NOT NULL,
  `under_university` varchar(256) DEFAULT NULL,
  `major` varchar(256) DEFAULT NULL,
  `gpa` decimal(3,2) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- Create additional indexes
-- ====================================

ALTER TABLE `apply` ADD KEY `sid` (`sid`,`pid`);
ALTER TABLE `competition_grade` ADD KEY `ID` (`ID`);
ALTER TABLE `intership` ADD KEY `ID` (`ID`);
ALTER TABLE `language_grade` ADD KEY `ID` (`ID`);
ALTER TABLE `operator_school` ADD KEY `sid` (`sid`);
ALTER TABLE `program` ADD KEY `sid_pid_idx` (`sid`,`pid`);
ALTER TABLE `school` ADD KEY `rid` (`rid`);
ALTER TABLE `student` ADD KEY `rid` (`rid`);
ALTER TABLE `student_files` ADD KEY `fid` (`fid`);

-- ====================================
-- All trigger constraints
-- ====================================

DELIMITER |

-- ============================================
-- CHECK constraint triggers (merged together to reduce number of triggers)
-- ============================================

CREATE TRIGGER `apply_check_insert` BEFORE INSERT ON `apply`
FOR EACH ROW
BEGIN
  IF NEW.`status` NOT IN ('Pending', 'Approved', 'Rejected') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'apply.status must be Pending, Approved, or Rejected';
  END IF;
  IF NEW.`apply_date` IS NOT NULL AND NEW.`apply_date` > CURDATE() THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'apply.apply_date cannot be in the future';
  END IF;
END|

CREATE TRIGGER `apply_check_update` BEFORE UPDATE ON `apply`
FOR EACH ROW
BEGIN
  IF NEW.`status` NOT IN ('Pending', 'Approved', 'Rejected') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'apply.status must be Pending, Approved, or Rejected';
  END IF;
  IF NEW.`apply_date` IS NOT NULL AND NEW.`apply_date` > CURDATE() THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'apply.apply_date cannot be in the future';
  END IF;
END|

CREATE TRIGGER `profile_check_insert` BEFORE INSERT ON `profile`
FOR EACH ROW
BEGIN
  IF NEW.`type` NOT IN ('student', 'operator', 'admin') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'profile.type must be student, operator, or admin';
  END IF;
  IF NEW.`email` NOT LIKE '%_@__%.__%' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'profile.email must be a valid email address';
  END IF;
  IF NEW.`date_of_birth` IS NOT NULL AND NEW.`date_of_birth` >= CURDATE() THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'profile.date_of_birth must be in the past';
  END IF;
END|

CREATE TRIGGER `profile_check_update` BEFORE UPDATE ON `profile`
FOR EACH ROW
BEGIN
  IF NEW.`type` NOT IN ('student', 'operator', 'admin') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'profile.type must be student, operator, or admin';
  END IF;
  IF NEW.`email` NOT LIKE '%_@__%.__%' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'profile.email must be a valid email address';
  END IF;
  IF NEW.`date_of_birth` IS NOT NULL AND NEW.`date_of_birth` >= CURDATE() THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'profile.date_of_birth must be in the past';
  END IF;
END|

CREATE TRIGGER `language_grade_check_insert` BEFORE INSERT ON `language_grade`
FOR EACH ROW
BEGIN
  -- Check type validity
  IF NEW.`type` IS NOT NULL AND NEW.`type` NOT IN ('IELTS', 'TOEFL', 'GRE', 'GMAT') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'language_grade.type must be IELTS, TOEFL, GRE, or GMAT';
  END IF;
  
  -- Check scores based on type
  IF NEW.`type` = 'TOEFL' THEN
    -- TOEFL: each section 0-30
    IF (NEW.`listening` IS NOT NULL AND (NEW.`listening` < 0 OR NEW.`listening` > 30)) OR
       (NEW.`speaking` IS NOT NULL AND (NEW.`speaking` < 0 OR NEW.`speaking` > 30)) OR
       (NEW.`writing` IS NOT NULL AND (NEW.`writing` < 0 OR NEW.`writing` > 30)) OR
       (NEW.`reading` IS NOT NULL AND (NEW.`reading` < 0 OR NEW.`reading` > 30)) THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'TOEFL scores must be between 0 and 30';
    END IF;
  ELSEIF NEW.`type` = 'IELTS' THEN
    -- IELTS: each section 0-9 (can be 0.5 increments)
    IF (NEW.`listening` IS NOT NULL AND (NEW.`listening` < 0 OR NEW.`listening` > 9)) OR
       (NEW.`speaking` IS NOT NULL AND (NEW.`speaking` < 0 OR NEW.`speaking` > 9)) OR
       (NEW.`writing` IS NOT NULL AND (NEW.`writing` < 0 OR NEW.`writing` > 9)) OR
       (NEW.`reading` IS NOT NULL AND (NEW.`reading` < 0 OR NEW.`reading` > 9)) THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'IELTS scores must be between 0 and 9';
    END IF;
  ELSEIF NEW.`type` IN ('GRE', 'GMAT') THEN
    -- GRE/GMAT: allow wider range (0-170 for GRE, 0-60 for GMAT sections, but we'll use 0-200 as safe range)
    IF (NEW.`listening` IS NOT NULL AND (NEW.`listening` < 0 OR NEW.`listening` > 200)) OR
       (NEW.`speaking` IS NOT NULL AND (NEW.`speaking` < 0 OR NEW.`speaking` > 200)) OR
       (NEW.`writing` IS NOT NULL AND (NEW.`writing` < 0 OR NEW.`writing` > 200)) OR
       (NEW.`reading` IS NOT NULL AND (NEW.`reading` < 0 OR NEW.`reading` > 200)) THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'GRE/GMAT scores must be between 0 and 200';
    END IF;
  END IF;
END|

CREATE TRIGGER `language_grade_check_update` BEFORE UPDATE ON `language_grade`
FOR EACH ROW
BEGIN
  -- Check type validity
  IF NEW.`type` IS NOT NULL AND NEW.`type` NOT IN ('IELTS', 'TOEFL', 'GRE', 'GMAT') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'language_grade.type must be IELTS, TOEFL, GRE, or GMAT';
  END IF;
  
  -- Check scores based on type
  IF NEW.`type` = 'TOEFL' THEN
    -- TOEFL: each section 0-30
    IF (NEW.`listening` IS NOT NULL AND (NEW.`listening` < 0 OR NEW.`listening` > 30)) OR
       (NEW.`speaking` IS NOT NULL AND (NEW.`speaking` < 0 OR NEW.`speaking` > 30)) OR
       (NEW.`writing` IS NOT NULL AND (NEW.`writing` < 0 OR NEW.`writing` > 30)) OR
       (NEW.`reading` IS NOT NULL AND (NEW.`reading` < 0 OR NEW.`reading` > 30)) THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'TOEFL scores must be between 0 and 30';
    END IF;
  ELSEIF NEW.`type` = 'IELTS' THEN
    -- IELTS: each section 0-9 (can be 0.5 increments)
    IF (NEW.`listening` IS NOT NULL AND (NEW.`listening` < 0 OR NEW.`listening` > 9)) OR
       (NEW.`speaking` IS NOT NULL AND (NEW.`speaking` < 0 OR NEW.`speaking` > 9)) OR
       (NEW.`writing` IS NOT NULL AND (NEW.`writing` < 0 OR NEW.`writing` > 9)) OR
       (NEW.`reading` IS NOT NULL AND (NEW.`reading` < 0 OR NEW.`reading` > 9)) THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'IELTS scores must be between 0 and 9';
    END IF;
  ELSEIF NEW.`type` IN ('GRE', 'GMAT') THEN
    -- GRE/GMAT: allow wider range (0-170 for GRE, 0-60 for GMAT sections, but we'll use 0-200 as safe range)
    IF (NEW.`listening` IS NOT NULL AND (NEW.`listening` < 0 OR NEW.`listening` > 200)) OR
       (NEW.`speaking` IS NOT NULL AND (NEW.`speaking` < 0 OR NEW.`speaking` > 200)) OR
       (NEW.`writing` IS NOT NULL AND (NEW.`writing` < 0 OR NEW.`writing` > 200)) OR
       (NEW.`reading` IS NOT NULL AND (NEW.`reading` < 0 OR NEW.`reading` > 200)) THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'GRE/GMAT scores must be between 0 and 200';
    END IF;
  END IF;
END|

CREATE TRIGGER `undergraduate_check_gpa_insert` BEFORE INSERT ON `undergraduate`
FOR EACH ROW
BEGIN
  IF NEW.`gpa` IS NOT NULL AND (NEW.`gpa` < 0 OR NEW.`gpa` > 4.0) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'undergraduate.gpa must be between 0 and 4.0';
  END IF;
END|

CREATE TRIGGER `undergraduate_check_gpa_update` BEFORE UPDATE ON `undergraduate`
FOR EACH ROW
BEGIN
  IF NEW.`gpa` IS NOT NULL AND (NEW.`gpa` < 0 OR NEW.`gpa` > 4.0) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'undergraduate.gpa must be between 0 and 4.0';
  END IF;
END|

CREATE TRIGGER `program_check_insert` BEFORE INSERT ON `program`
FOR EACH ROW
BEGIN
  IF NEW.`gpa_requirement` IS NOT NULL AND (NEW.`gpa_requirement` < 0 OR NEW.`gpa_requirement` > 4.0) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'program.gpa_requirement must be between 0 and 4.0';
  END IF;
  IF NEW.`degree_type` NOT IN ('Master', 'PhD', 'Bachelor') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'program.degree_type must be Master, PhD, or Bachelor';
  END IF;
  IF NEW.`ddl` IS NOT NULL AND NEW.`ddl` < CURDATE() THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'program.ddl cannot be in the past';
  END IF;
END|

CREATE TRIGGER `program_check_update` BEFORE UPDATE ON `program`
FOR EACH ROW
BEGIN
  IF NEW.`gpa_requirement` IS NOT NULL AND (NEW.`gpa_requirement` < 0 OR NEW.`gpa_requirement` > 4.0) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'program.gpa_requirement must be between 0 and 4.0';
  END IF;
  IF NEW.`degree_type` NOT IN ('Master', 'PhD', 'Bachelor') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'program.degree_type must be Master, PhD, or Bachelor';
  END IF;
  IF NEW.`ddl` IS NOT NULL AND NEW.`ddl` < CURDATE() THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'program.ddl cannot be in the past';
  END IF;
END|

CREATE TRIGGER `school_check_rank_insert` BEFORE INSERT ON `school`
FOR EACH ROW
BEGIN
  IF NEW.`QS_rank` IS NOT NULL AND NEW.`QS_rank` <= 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'school.QS_rank must be a positive integer';
  END IF;
END|

CREATE TRIGGER `school_check_rank_update` BEFORE UPDATE ON `school`
FOR EACH ROW
BEGIN
  IF NEW.`QS_rank` IS NOT NULL AND NEW.`QS_rank` <= 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'school.QS_rank must be a positive integer';
  END IF;
END|

CREATE TRIGGER `operator_school_check_status_insert` BEFORE INSERT ON `operator_school`
FOR EACH ROW
BEGIN
  IF NEW.`status` NOT IN ('approved', 'pending', 'rejected') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'operator_school.status must be approved, pending, or rejected';
  END IF;
END|

CREATE TRIGGER `operator_school_check_status_update` BEFORE UPDATE ON `operator_school`
FOR EACH ROW
BEGIN
  IF NEW.`status` NOT IN ('approved', 'pending', 'rejected') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'operator_school.status must be approved, pending, or rejected';
  END IF;
END|

-- ============================================
-- FOREIGN KEY constraint triggers
-- ============================================

CREATE TRIGGER `apply_fk_insert` BEFORE INSERT ON `apply`
FOR EACH ROW
BEGIN
  IF NOT EXISTS(SELECT 1 FROM `student` WHERE `ID` = NEW.`ID`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: apply.ID must exist in student.ID';
  END IF;
  IF NOT EXISTS(SELECT 1 FROM `program` WHERE `sid` = NEW.`sid` AND `pid` = NEW.`pid`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: apply.sid,pid must exist in program.sid,pid';
  END IF;
END|

CREATE TRIGGER `apply_fk_update` BEFORE UPDATE ON `apply`
FOR EACH ROW
BEGIN
  IF NOT EXISTS(SELECT 1 FROM `student` WHERE `ID` = NEW.`ID`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: apply.ID must exist in student.ID';
  END IF;
  IF NOT EXISTS(SELECT 1 FROM `program` WHERE `sid` = NEW.`sid` AND `pid` = NEW.`pid`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: apply.sid,pid must exist in program.sid,pid';
  END IF;
END|

CREATE TRIGGER `competition_grade_fk_insert` BEFORE INSERT ON `competition_grade`
FOR EACH ROW
BEGIN
  IF NOT EXISTS(SELECT 1 FROM `student` WHERE `ID` = NEW.`ID`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: competition_grade.ID must exist in student.ID';
  END IF;
END|

CREATE TRIGGER `competition_grade_fk_update` BEFORE UPDATE ON `competition_grade`
FOR EACH ROW
BEGIN
  IF NOT EXISTS(SELECT 1 FROM `student` WHERE `ID` = NEW.`ID`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: competition_grade.ID must exist in student.ID';
  END IF;
END|

CREATE TRIGGER `intership_fk_insert` BEFORE INSERT ON `intership`
FOR EACH ROW
BEGIN
  IF NOT EXISTS(SELECT 1 FROM `student` WHERE `ID` = NEW.`ID`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: intership.ID must exist in student.ID';
  END IF;
END|

CREATE TRIGGER `intership_fk_update` BEFORE UPDATE ON `intership`
FOR EACH ROW
BEGIN
  IF NOT EXISTS(SELECT 1 FROM `student` WHERE `ID` = NEW.`ID`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: intership.ID must exist in student.ID';
  END IF;
END|

CREATE TRIGGER `language_grade_fk_insert` BEFORE INSERT ON `language_grade`
FOR EACH ROW
BEGIN
  IF NOT EXISTS(SELECT 1 FROM `student` WHERE `ID` = NEW.`ID`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: language_grade.ID must exist in student.ID';
  END IF;
END|

CREATE TRIGGER `language_grade_fk_update` BEFORE UPDATE ON `language_grade`
FOR EACH ROW
BEGIN
  IF NOT EXISTS(SELECT 1 FROM `student` WHERE `ID` = NEW.`ID`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: language_grade.ID must exist in student.ID';
  END IF;
END|

CREATE TRIGGER `operator_school_fk_insert` BEFORE INSERT ON `operator_school`
FOR EACH ROW
BEGIN
  IF NOT EXISTS(SELECT 1 FROM `profile` WHERE `ID` = NEW.`ID`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: operator_school.ID must exist in profile.ID';
  END IF;
  IF NOT EXISTS(SELECT 1 FROM `school` WHERE `sid` = NEW.`sid`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: operator_school.sid must exist in school.sid';
  END IF;
END|

CREATE TRIGGER `operator_school_fk_update` BEFORE UPDATE ON `operator_school`
FOR EACH ROW
BEGIN
  IF NOT EXISTS(SELECT 1 FROM `profile` WHERE `ID` = NEW.`ID`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: operator_school.ID must exist in profile.ID';
  END IF;
  IF NOT EXISTS(SELECT 1 FROM `school` WHERE `sid` = NEW.`sid`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: operator_school.sid must exist in school.sid';
  END IF;
END|

CREATE TRIGGER `program_fk_insert` BEFORE INSERT ON `program`
FOR EACH ROW
BEGIN
  IF NOT EXISTS(SELECT 1 FROM `school` WHERE `sid` = NEW.`sid`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: program.sid must exist in school.sid';
  END IF;
END|

CREATE TRIGGER `program_fk_update` BEFORE UPDATE ON `program`
FOR EACH ROW
BEGIN
  IF NOT EXISTS(SELECT 1 FROM `school` WHERE `sid` = NEW.`sid`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: program.sid must exist in school.sid';
  END IF;
END|

CREATE TRIGGER `school_fk_insert` BEFORE INSERT ON `school`
FOR EACH ROW
BEGIN
  IF NEW.`rid` IS NOT NULL AND NOT EXISTS(SELECT 1 FROM `region` WHERE `rid` = NEW.`rid`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: school.rid must exist in region.rid';
  END IF;
END|

CREATE TRIGGER `school_fk_update` BEFORE UPDATE ON `school`
FOR EACH ROW
BEGIN
  IF NEW.`rid` IS NOT NULL AND NOT EXISTS(SELECT 1 FROM `region` WHERE `rid` = NEW.`rid`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: school.rid must exist in region.rid';
  END IF;
END|

CREATE TRIGGER `student_fk_insert` BEFORE INSERT ON `student`
FOR EACH ROW
BEGIN
  IF NOT EXISTS(SELECT 1 FROM `profile` WHERE `ID` = NEW.`ID`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: student.ID must exist in profile.ID';
  END IF;
  IF NEW.`rid` IS NOT NULL AND NOT EXISTS(SELECT 1 FROM `region` WHERE `rid` = NEW.`rid`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: student.rid must exist in region.rid';
  END IF;
END|

CREATE TRIGGER `student_fk_update` BEFORE UPDATE ON `student`
FOR EACH ROW
BEGIN
  IF NOT EXISTS(SELECT 1 FROM `profile` WHERE `ID` = NEW.`ID`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: student.ID must exist in profile.ID';
  END IF;
  IF NEW.`rid` IS NOT NULL AND NOT EXISTS(SELECT 1 FROM `region` WHERE `rid` = NEW.`rid`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: student.rid must exist in region.rid';
  END IF;
END|

CREATE TRIGGER `student_files_fk_insert` BEFORE INSERT ON `student_files`
FOR EACH ROW
BEGIN
  IF NOT EXISTS(SELECT 1 FROM `student` WHERE `ID` = NEW.`ID`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: student_files.ID must exist in student.ID';
  END IF;
  IF NOT EXISTS(SELECT 1 FROM `files` WHERE `fid` = NEW.`fid`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: student_files.fid must exist in files.fid';
  END IF;
END|

CREATE TRIGGER `student_files_fk_update` BEFORE UPDATE ON `student_files`
FOR EACH ROW
BEGIN
  IF NOT EXISTS(SELECT 1 FROM `student` WHERE `ID` = NEW.`ID`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: student_files.ID must exist in student.ID';
  END IF;
  IF NOT EXISTS(SELECT 1 FROM `files` WHERE `fid` = NEW.`fid`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: student_files.fid must exist in files.fid';
  END IF;
END|

CREATE TRIGGER `undergraduate_fk_insert` BEFORE INSERT ON `undergraduate`
FOR EACH ROW
BEGIN
  IF NOT EXISTS(SELECT 1 FROM `student` WHERE `ID` = NEW.`ID`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: undergraduate.ID must exist in student.ID';
  END IF;
END|

CREATE TRIGGER `undergraduate_fk_update` BEFORE UPDATE ON `undergraduate`
FOR EACH ROW
BEGIN
  IF NOT EXISTS(SELECT 1 FROM `student` WHERE `ID` = NEW.`ID`) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Foreign key constraint violated: undergraduate.ID must exist in student.ID';
  END IF;
END|

-- ============================================
-- CASCADE DELETE triggers
-- ============================================

CREATE TRIGGER `student_cascade_delete` AFTER DELETE ON `student`
FOR EACH ROW
BEGIN
  DELETE FROM `apply` WHERE `ID` = OLD.`ID`;
  DELETE FROM `competition_grade` WHERE `ID` = OLD.`ID`;
  DELETE FROM `intership` WHERE `ID` = OLD.`ID`;
  DELETE FROM `language_grade` WHERE `ID` = OLD.`ID`;
  DELETE FROM `student_files` WHERE `ID` = OLD.`ID`;
  DELETE FROM `undergraduate` WHERE `ID` = OLD.`ID`;
END|

CREATE TRIGGER `program_cascade_delete` AFTER DELETE ON `program`
FOR EACH ROW
BEGIN
  DELETE FROM `apply` WHERE `sid` = OLD.`sid` AND `pid` = OLD.`pid`;
END|

CREATE TRIGGER `profile_cascade_delete` AFTER DELETE ON `profile`
FOR EACH ROW
BEGIN
  DELETE FROM `student` WHERE `ID` = OLD.`ID`;
  DELETE FROM `operator_school` WHERE `ID` = OLD.`ID`;
END|

CREATE TRIGGER `school_cascade_delete` AFTER DELETE ON `school`
FOR EACH ROW
BEGIN
  DELETE FROM `program` WHERE `sid` = OLD.`sid`;
  DELETE FROM `operator_school` WHERE `sid` = OLD.`sid`;
END|

CREATE TRIGGER `files_cascade_delete` AFTER DELETE ON `files`
FOR EACH ROW
BEGIN
  DELETE FROM `student_files` WHERE `fid` = OLD.`fid`;
END|

DELIMITER ;

COMMIT;