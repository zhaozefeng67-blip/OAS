-- Update triggers for language_grade table, check different score ranges based on type
-- TOEFL: 0-30, IELTS: 0-9, GRE/GMAT: 0-200

-- Drop old triggers
DROP TRIGGER IF EXISTS `language_grade_check_insert`;
DROP TRIGGER IF EXISTS `language_grade_check_update`;

DELIMITER |

-- Create new INSERT trigger
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

-- Create new UPDATE trigger
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

DELIMITER ;

