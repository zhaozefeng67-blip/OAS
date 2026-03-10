DELIMITER | 

CREATE TRIGGER apply_primary_key 
BEFORE INSERT ON apply 
FOR EACH ROW 
BEGIN 
    IF EXISTS(SELECT * FROM apply WHERE apply.ID = new.ID AND apply.sid = new.sid AND apply.pid = new.pid) 
    THEN         
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Primary key constraint violated';
    END IF;
END; | 

CREATE TRIGGER apply_foreign_key 
BEFORE INSERT ON apply 
FOR EACH ROW 
BEGIN 
    IF NOT EXISTS(SELECT * FROM student WHERE student.ID = new.ID) 
    THEN        
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Foreign key constraint violated';
    END IF;
    IF NOT EXISTS(SELECT * FROM program WHERE program.sid = new.sid AND program.pid = new.pid) 
    THEN        
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Foreign key constraint violated';
    END IF;
END; |  

CREATE TRIGGER competition_grade_PRIMARY_KEY  
BEFORE INSERT ON competition_grade 
FOR EACH ROW 
BEGIN 
    IF EXISTS(SELECT * FROM competition_grade WHERE competition_grade.cid = new.cid)
    THEN        
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Grade must be between 0 and 100';
    END IF;
END; |
