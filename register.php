<?php
    // Enable output buffering to ensure header redirect works properly
    ob_start();
    
    // #region agent log
    $log_file = __DIR__ . '/.cursor/debug.log';
    $log_entry = json_encode([
        'sessionId' => 'debug-session',
        'runId' => 'pre-fix',
        'hypothesisId' => 'A',
        'location' => 'register.php:1',
        'message' => 'Script started - student registration',
        'data' => [
            'has_post' => !empty($_POST),
            'post_keys' => array_keys($_POST ?? []),
            'user_type' => $_POST['user_type'] ?? 'not_set'
        ],
        'timestamp' => time() * 1000
    ]) . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    // #endregion
    
    session_start();
    
    require 'connect.php';

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    $email = $_POST['email'] ?? '';
    $user_type = isset($_POST['user_type']) ? $_POST['user_type'] : 'student';
    
    // #region agent log
    $log_entry = json_encode([
        'sessionId' => 'debug-session',
        'runId' => 'pre-fix',
        'hypothesisId' => 'A',
        'location' => 'register.php:20',
        'message' => 'POST data received',
        'data' => [
            'username' => $username,
            'email' => $email,
            'user_type' => $user_type,
            'has_password' => !empty($password),
            'has_confirm' => !empty($confirm)
        ],
        'timestamp' => time() * 1000
    ]) . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    // #endregion

    if($password != $confirm) {
        $_SESSION['fail'] = "Confirm Password must be same as Password";
        ob_end_clean();
        header("Location: rg.php");
        exit();
    }

    // Check if email is already registered
    $stmt = $conn->prepare("SELECT * FROM profile WHERE email = ?");
    $stmt->bind_param("s" , $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if($user) {
        $_SESSION['fail'] = 'The email has been registered';
        ob_end_clean();
        header("Location: rg.php");
        exit();
    }

    // Check if username is already registered
    $stmt = $conn->prepare("SELECT * FROM profile WHERE username = ?");
    $stmt->bind_param("s" , $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if($user) {
        $_SESSION['fail'] = 'The username has been registered';
        ob_end_clean();
        header("Location: rg.php");
        exit();
    }

    // If it's officer registration, insert directly into profile table with status pending
    if($user_type === 'officer') {
        $real_name = isset($_POST['real_name']) ? $_POST['real_name'] : '';
        $university_id = isset($_POST['university_id']) ? (int)$_POST['university_id'] : 0;

        if(empty($real_name) || $university_id <= 0) {
            $_SESSION['fail'] = 'Please fill all required fields for officer registration';
            header("Location: rg.php");
            exit();
        }

        // Check if email already exists in profile table with status pending
        $stmt = $conn->prepare("SELECT * FROM profile WHERE email = ? AND type = 'operator' AND (status = 'pending' OR status IS NULL)");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            $_SESSION['fail'] = 'You have already submitted an application. Please wait for admin approval.';
            ob_end_clean();
            header("Location: rg.php");
            exit();
        }

        // Insert into profile table with status pending and type operator
        $date_of_birth = NULL;
        $type = 'operator';
        $status = 'pending';
        
        // Validate email format (matches database trigger check: at least one character + @ + at least two characters + . + at least two characters)
        if (!preg_match('/^.+@.+\..{2,}$/', $email)) {
            $_SESSION['fail'] = 'Invalid email address format. Please enter a valid email address.';
            ob_end_clean();
            header("Location: rg.php");
            exit();
        }
        
        // #region agent log
        $log_entry = json_encode([
            'sessionId' => 'debug-session',
            'runId' => 'pre-fix',
            'hypothesisId' => 'A',
            'location' => 'register.php:115',
            'message' => 'Before INSERT INTO profile for operator',
            'data' => [
                'username' => $username,
                'email' => $email,
                'email_valid' => preg_match('/^.+@.+\..{2,}$/', $email)
            ],
            'timestamp' => time() * 1000
        ]) . "\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);
        // #endregion
        
        // Try to insert into profile table (including status field)
        $stmt = $conn->prepare("INSERT INTO profile (username, password, real_name, date_of_birth, email, type, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $username, $password, $real_name, $date_of_birth, $email, $type, $status);
        
        try {
            $insert_result = $stmt->execute();
            
            // #region agent log
            $log_entry = json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'pre-fix',
                'hypothesisId' => 'A',
                'location' => 'register.php:135',
                'message' => 'INSERT INTO profile result',
                'data' => [
                    'success' => $insert_result,
                    'error' => $insert_result ? '' : $conn->error
                ],
                'timestamp' => time() * 1000
            ]) . "\n";
            file_put_contents($log_file, $log_entry, FILE_APPEND);
            // #endregion
            
            if(!$insert_result) {
                // If failed, profile table might not have status field, try inserting without status
                $stmt = $conn->prepare("INSERT INTO profile (username, password, real_name, date_of_birth, email, type) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $username, $password, $real_name, $date_of_birth, $email, $type);
                if(!$stmt->execute()) {
                    $_SESSION['fail'] = 'Failed to submit application: ' . $conn->error;
                    ob_end_clean();
                    header("Location: rg.php");
                    exit();
                }
            }
        } catch (mysqli_sql_exception $e) {
            // #region agent log
            $log_entry = json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'pre-fix',
                'hypothesisId' => 'B',
                'location' => 'register.php:155',
                'message' => 'Exception caught during profile insert',
                'data' => [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode()
                ],
                'timestamp' => time() * 1000
            ]) . "\n";
            file_put_contents($log_file, $log_entry, FILE_APPEND);
            // #endregion
            
            $error_msg = $e->getMessage();
            if (strpos($error_msg, 'profile.email must be a valid email address') !== false) {
                $_SESSION['fail'] = 'Invalid email address format. Please enter a valid email address (e.g., user@example.com).';
            } elseif (strpos($error_msg, 'profile.type must be') !== false) {
                $_SESSION['fail'] = 'Invalid user type.';
            } elseif (strpos($error_msg, 'profile.date_of_birth') !== false) {
                $_SESSION['fail'] = 'Date of birth must be in the past.';
            } else {
                $_SESSION['fail'] = 'Failed to submit application: ' . $error_msg;
            }
            ob_end_clean();
            header("Location: rg.php");
            exit();
        }
        
        $profile_id = mysqli_insert_id($conn);
        
        // #region agent log
        $log_file = __DIR__ . '/.cursor/debug.log';
        $log_entry = json_encode([
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'A',
            'location' => 'register.php:87',
            'message' => 'Before ALTER TABLE - checking if status column exists',
            'data' => ['profile_id' => $profile_id, 'university_id' => $university_id],
            'timestamp' => time() * 1000
        ]) . "\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);
        // #endregion
        
        // Store university_id to operator_school table with status pending
        // Try to add status field (if table doesn't have this field)
        // First check if column exists
        $check_column_sql = "SELECT COUNT(*) as col_count FROM information_schema.COLUMNS 
                             WHERE TABLE_SCHEMA = DATABASE() 
                             AND TABLE_NAME = 'operator_school' 
                             AND COLUMN_NAME = 'status'";
        $check_result = mysqli_query($conn, $check_column_sql);
        
        // #region agent log
        $log_entry = json_encode([
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'B',
            'location' => 'register.php:95',
            'message' => 'Column check result',
            'data' => ['check_result' => $check_result ? 'success' : 'failed', 'error' => $conn->error],
            'timestamp' => time() * 1000
        ]) . "\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);
        // #endregion
        
        $column_exists = false;
        if ($check_result) {
            $row = mysqli_fetch_assoc($check_result);
            $column_exists = ($row['col_count'] > 0);
        }
        
        // #region agent log
        $log_entry = json_encode([
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'C',
            'location' => 'register.php:105',
            'message' => 'Column exists check result',
            'data' => ['column_exists' => $column_exists],
            'timestamp' => time() * 1000
        ]) . "\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);
        // #endregion
        
        // Only add if column doesn't exist
        if (!$column_exists) {
            $add_status_column = "ALTER TABLE operator_school ADD COLUMN status VARCHAR(50) DEFAULT 'approved'";
            
            // #region agent log
            $log_entry = json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'D',
                'location' => 'register.php:112',
                'message' => 'Attempting to add status column',
                'data' => [],
                'timestamp' => time() * 1000
            ]) . "\n";
            file_put_contents($log_file, $log_entry, FILE_APPEND);
            // #endregion
            
            $alter_result = mysqli_query($conn, $add_status_column);
            
            // #region agent log
            $log_entry = json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'D',
                'location' => 'register.php:120',
                'message' => 'ALTER TABLE result',
                'data' => ['success' => $alter_result ? true : false, 'error' => $conn->error],
                'timestamp' => time() * 1000
            ]) . "\n";
            file_put_contents($log_file, $log_entry, FILE_APPEND);
            // #endregion
        } else {
            // #region agent log
            $log_entry = json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'A',
                'location' => 'register.php:128',
                'message' => 'Column already exists, skipping ALTER TABLE',
                'data' => [],
                'timestamp' => time() * 1000
            ]) . "\n";
            file_put_contents($log_file, $log_entry, FILE_APPEND);
            // #endregion
        }
        
        // Insert into operator_school table with status pending
        $status_value = 'pending';
        $stmt2 = $conn->prepare("INSERT INTO operator_school (ID, sid, status) VALUES (?, ?, ?)");
        if(!$stmt2) {
            // If prepare fails (table might not have status field), try inserting without status
            $stmt2 = $conn->prepare("INSERT INTO operator_school (ID, sid) VALUES (?, ?)");
            if(!$stmt2) {
                // If still fails, delete the newly created profile record
                $conn->query("DELETE FROM profile WHERE ID = $profile_id");
                $_SESSION['fail'] = 'Failed to prepare statement: ' . $conn->error;
                header("Location: rg.php");
                exit();
            }
            $stmt2->bind_param("ii", $profile_id, $university_id);
        } else {
            $stmt2->bind_param("iis", $profile_id, $university_id, $status_value);
        }
        
        if($stmt2->execute()) {
            $_SESSION['success'] = 'Your application has been submitted. Please wait for admin approval.';
            ob_end_clean();
            header("Location: login_.php");
            exit();
        } else {
            // If operator_school insert fails, delete the newly created profile record
            $conn->query("DELETE FROM profile WHERE ID = $profile_id");
            $_SESSION['fail'] = 'Failed to submit application. Please try again.';
            header("Location: rg.php");
        }
    } else {
        // #region agent log
        $log_entry = json_encode([
            'sessionId' => 'debug-session',
            'runId' => 'pre-fix',
            'hypothesisId' => 'B',
            'location' => 'register.php:217',
            'message' => 'Starting student registration',
            'data' => ['username' => $username, 'email' => $email],
            'timestamp' => time() * 1000
        ]) . "\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);
        // #endregion
        
        // Student registration, create account directly
        $real_name = NULL;
        $date_of_birth = NULL;
        $type = 'student';
        $status = NULL; // status is NULL for student registration
        $stmt = $conn->prepare("INSERT INTO profile (username, password, real_name, date_of_birth, email, type, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss" , $username , $password , $real_name , $date_of_birth , $email , $type, $status);
        
        // #region agent log
        $log_entry = json_encode([
            'sessionId' => 'debug-session',
            'runId' => 'pre-fix',
            'hypothesisId' => 'B',
            'location' => 'register.php:230',
            'message' => 'Before INSERT INTO profile',
            'data' => ['prepared' => $stmt !== false],
            'timestamp' => time() * 1000
        ]) . "\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);
        // #endregion
        
        $profile_insert_result = $stmt->execute();
        
        // #region agent log
        $log_entry = json_encode([
            'sessionId' => 'debug-session',
            'runId' => 'pre-fix',
            'hypothesisId' => 'B',
            'location' => 'register.php:240',
            'message' => 'INSERT INTO profile result',
            'data' => [
                'success' => $profile_insert_result,
                'error' => $profile_insert_result ? '' : $conn->error,
                'insert_id' => $profile_insert_result ? mysqli_insert_id($conn) : 0
            ],
            'timestamp' => time() * 1000
        ]) . "\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);
        // #endregion
        
        if (!$profile_insert_result) {
            $error_msg = $conn->error;
            // Check if it's a trigger error
            if (strpos($error_msg, 'profile.type must be') !== false) {
                $_SESSION['fail'] = 'Invalid user type.';
            } elseif (strpos($error_msg, 'profile.email must be') !== false) {
                $_SESSION['fail'] = 'Invalid email address.';
            } elseif (strpos($error_msg, 'profile.date_of_birth') !== false) {
                $_SESSION['fail'] = 'Date of birth must be in the past.';
            } else {
                $_SESSION['fail'] = 'Registration failed: ' . $error_msg;
            }
            
            // #region agent log
            $log_entry = json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'pre-fix',
                'hypothesisId' => 'C',
                'location' => 'register.php:260',
                'message' => 'Profile insert failed, redirecting to rg.php',
                'data' => ['error' => $error_msg],
                'timestamp' => time() * 1000
            ]) . "\n";
            file_put_contents($log_file, $log_entry, FILE_APPEND);
            // #endregion
            
            header("Location: rg.php");
            exit();
        }

        $stmt = $conn->prepare("SELECT * FROM profile WHERE username = ?");
        $stmt->bind_param("s" , $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // #region agent log
        $log_entry = json_encode([
            'sessionId' => 'debug-session',
            'runId' => 'pre-fix',
            'hypothesisId' => 'B',
            'location' => 'register.php:280',
            'message' => 'User query result',
            'data' => [
                'user_found' => $user !== null,
                'user_id' => $user['ID'] ?? 'not_found'
            ],
            'timestamp' => time() * 1000
        ]) . "\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);
        // #endregion

        $ID = $user['ID'];
        $stmt = $conn->prepare("INSERT INTO student VALUES(?,NULL)");
        $stmt->bind_param("i" , $ID);
        
        // #region agent log
        $log_entry = json_encode([
            'sessionId' => 'debug-session',
            'runId' => 'pre-fix',
            'hypothesisId' => 'B',
            'location' => 'register.php:295',
            'message' => 'Before INSERT INTO student',
            'data' => ['student_id' => $ID, 'prepared' => $stmt !== false],
            'timestamp' => time() * 1000
        ]) . "\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);
        // #endregion
        
        $student_insert_result = $stmt->execute();
        
        // #region agent log
        $log_entry = json_encode([
            'sessionId' => 'debug-session',
            'runId' => 'pre-fix',
            'hypothesisId' => 'B',
            'location' => 'register.php:305',
            'message' => 'INSERT INTO student result',
            'data' => [
                'success' => $student_insert_result,
                'error' => $student_insert_result ? '' : $conn->error
            ],
            'timestamp' => time() * 1000
        ]) . "\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);
        // #endregion
        
        if (!$student_insert_result) {
            // If student table insert fails, delete the newly created profile record
            $conn->query("DELETE FROM profile WHERE ID = $ID");
            $_SESSION['fail'] = 'Registration failed: ' . $conn->error;
            
            // #region agent log
            $log_entry = json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'pre-fix',
                'hypothesisId' => 'C',
                'location' => 'register.php:320',
                'message' => 'Student insert failed, redirecting to rg.php',
                'data' => ['error' => $conn->error],
                'timestamp' => time() * 1000
            ]) . "\n";
            file_put_contents($log_file, $log_entry, FILE_APPEND);
            // #endregion
            
            header("Location: rg.php");
            exit();
        }

        // #region agent log
        $log_entry = json_encode([
            'sessionId' => 'debug-session',
            'runId' => 'pre-fix',
            'hypothesisId' => 'D',
            'location' => 'register.php:335',
            'message' => 'Registration successful, redirecting to login_.php',
            'data' => ['student_id' => $ID],
            'timestamp' => time() * 1000
        ]) . "\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);
        // #endregion

        // Clear output buffer and redirect
        ob_end_clean();
        header("Location: login_.php");
        exit();
    }

?>