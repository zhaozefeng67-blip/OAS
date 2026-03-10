<?php
    require 'connect.php';
    session_start();
    if(!isset($_SESSION['username'])) {
        header("Location: login_.php");
        exit;
    }
    $username = $_SESSION['username'];
    // Password should not be stored in session, if password verification is needed, query from database
    // $password = isset($_SESSION['password']) ? $_SESSION['password'] : null;
    $stmt = $conn->prepare("SELECT * FROM profile WHERE username = ?");
    $stmt->bind_param("s" , $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $ID = $user['ID'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/nav.css">
    <link rel="stylesheet" href="CSS/profile2.css?v=<?php echo time(); ?>">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-left">
            <a href="index.html" class="logo">◀BACK</a>
            <div class="nav-buttons">
                <p>OAS / Profile</p>
            </div>
        </div>
        <div class="nav-right">
            <a href="logout.php" class="user-profile" style="color: #8B0000;">
                <span>Logout</span>
            </a>
        </div>
    </nav>
    
    <div class="main-container">
        <!-- Left Sidebar - Vertical Tabs -->
        <div class="sidebar">
            <div class="profile-summary">
                <div class="profile-avatar"><?php echo substr($user['username'], 0, 2); ?></div>
                <div class="profile-summary-info">
                    <h3><?php echo $user['username']; ?></h3>
                    <p><?php echo $user['email']; ?></p>
                </div>
            </div>
            
            <div class="vertical-tabs">
                <button class="tab-btn active" onclick="switchTab('basic')">
                    <i class="fas fa-user"></i>
                    <span>Basic Info</span>
                </button>
                <button class="tab-btn" onclick="switchTab('education')">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Education</span>
                </button>
                <button class="tab-btn" onclick="switchTab('language')">
                    <i class="fas fa-language"></i>
                    <span>Language Scores</span>
                </button>
                <button class="tab-btn" onclick="switchTab('experience')">
                    <i class="fas fa-briefcase"></i>
                    <span>Internship</span>
                </button>
                <button class="tab-btn" onclick="switchTab('competition')">
                    <i class="fas fa-trophy"></i>
                    <span>Competitions</span>
                </button>
                <button class="tab-btn" onclick="switchTab('documents')">
                    <i class="fas fa-file-upload"></i>
                    <span>Documents</span>
                </button>
                <button class="tab-btn" onclick="switchTab('password')">
                    <i class="fas fa-key"></i>
                    <span>Change Password</span>
                </button>
            </div>
        </div>

        <!-- Right Content Area -->
        <div class="content-area">
            <!-- Basic Info Panel -->
            <div id="basicPanel" class="panel active">
                <div class="panel-header">
                    <h2>Basic Information</h2>
                </div>
                
                <div class="card">
                    <div id="basicViewMode">
                        <div class="info-row">
                            <div class="info-item">
                                <span class="info-label">Full Name</span>
                                <span class="info-value"><?php echo $user['real_name'] == NULL ? "Empty" : $user['real_name']; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Email</span>
                                <span class="info-value"><?php echo $user['email'] == NULL ? "Empty" : $user['email']; ?></span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-item">
                                <span class="info-label">Date of Birth</span>
                                <span class="info-value"><?php echo $user['date_of_birth'] == NULL ? "Empty" : $user['date_of_birth']; ?></span>
                            </div>

                            <?php 
                                $stmt = $conn->prepare("SELECT * FROM student WHERE ID = ?");
                                $stmt->bind_param("i" , $user['ID']);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $student = $result->fetch_assoc();

                                if ($student) {
                                    $stmt = $conn->prepare("SELECT * FROM Region WHERE rid = ?");
                                    $stmt->bind_param("i" , $student['rid']);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $region = $result->fetch_assoc();
                                } else {
                                    $region = null;
                                }
                            ?>

                            <div class="info-item">
                                <span class="info-label">Country</span>
                                <span class="info-value"><?php echo $region != NULL && $region['country'] != NULL ? $region['country'] : "Empty"; ?></span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-item">
                                <span class="info-label">City</span>
                                <span class="info-value"><?php echo $region != NULL && $region['city'] != NULL ? $region['city'] : "Empty"; ?></span>
                            </div>
                            <div class="info-item"></div>
                        </div>

                        <div class="action-buttons">
                            <button class="btn btn-edit" onclick="editBasicInfo()">Edit</button>
                        </div>
                    </div>

                    <div id="basicEditMode" style="display: none;">
                        <form id="basicForm" class="form-grid" action="basic.php" method="POST">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" placeholder="David Green" name="real_name" value="<?php echo $user['real_name'] ?? ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" placeholder="david.green@example.com" name="email" value="<?php echo $user['email'] ?? ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Date of Birth</label>
                                <input type="date" placeholder="1998-05-15" name="date_of_birth" value="<?php echo $user['date_of_birth'] ?? ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Country</label>
                                <input type="text" placeholder="China" name="country" value="<?php echo $region['country'] ?? ''; ?>">
                            </div>
                            <div class="form-group full">
                                <label>City</label>
                                <input type="text" placeholder="Beijing" name="city" value="<?php echo $region['city'] ?? ''; ?>">
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary" onclick="cancelBasicEdit()">Cancel</button>
                                <button type="button" class="btn btn-primary" onclick="saveBasicInfo()">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Education Panel -->
            <div id="educationPanel" class="panel">
                <?php 
                    $ID = $user['ID'];
                    $stmt = $conn->prepare("SELECT * FROM undergraduate WHERE ID = ?");
                    $stmt->bind_param("i" , $ID);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                ?>

                <div class="panel-header">
                    <h2>Undergraduate Education</h2>
                </div>
                
                <div class="card">
                    <div id="educationViewMode">
                        <div class="info-row">
                            <div class="info-item">
                                <span class="info-label">University</span>
                                <span class="info-value"><?php 
                                    echo $row != NULL && $row['under_university'] != NULL ? $row['under_university'] : "Empty"; ?> 
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Major</span>
                                <span class="info-value"><?php 
                                    echo $row != NULL && $row['major'] != NULL ? $row['major'] : "Empty"; ?> 
                                </span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-item">
                                <span class="info-label">GPA</span>
                                <span class="info-value"><?php 
                                    echo $row != NULL && $row['gpa'] != NULL ? $row['gpa'] : "Empty"; 
                                ?></span>
                            </div>
                            <div class="info-item"></div>
                        </div>
                        <div class="action-buttons">
                            <button class="btn btn-edit" onclick="editEducation()">Edit Education</button>
                        </div>
                    </div>

                    <div id="educationEditMode" style="display: none;">
                        <form id="educationForm" class="form-grid" action="education.php" method="POST">
                            <div class="form-group">
                                <label>University Name</label>
                                <input type="text" placeholder="Beijing Normal University" name="under_university" value="<?php echo $row['under_university'] ?? ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Major</label>
                                <input type="text" placeholder="Computer Science" name="major" value="<?php echo $row['major'] ?? ''; ?>">
                            </div>
                            <div class="form-group full">
                                <label>GPA</label>
                                <input type="number" placeholder="4.0/4.0" step="0.01" min="0" max="4.0" name="gpa" value="<?php echo $row['gpa'] ?? ''; ?>">
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary" onclick="cancelEducationEdit()">Cancel</button>
                                <button type="button" class="btn btn-primary" onclick="saveEducation()">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Language Scores Panel -->
            <div id="languagePanel" class="panel">
                <?php 
                    $ID = $user['ID'];
                    $type = "TOEFL";
                    $stmt = $conn->prepare("SELECT * FROM language_grade WHERE ID = ? AND type = ?");
                    $stmt->bind_param("is" , $ID , $type);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $toefl = 0;
                ?>

                <div class="panel-header">
                    <h2>Language Scores</h2>
                    <p>Manage your language proficiency test scores</p>
                </div>
                
                <div class="card">
                    <div class="card-title">TOEFL Score</div>
                    <div id="toeflViewMode">
                        <div class="info-row">
                            <div class="info-item">
                                <span class="info-label">Listening</span>
                                <span class="info-value"><?php 
                                    echo $row != NULL && $row['listening'] != NULL ? $row['listening'] : "Empty";
                                    $toefl += $row != NULL && $row['listening'] != NULL ? $row['listening'] : 0;
                                ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Speaking</span>
                                <span class="info-value"><?php 
                                    echo $row != NULL && $row['speaking'] != NULL ? $row['speaking'] : "Empty";
                                    $toefl += $row != NULL && $row['speaking'] != NULL ? $row['speaking'] : 0;
                                ?></span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-item">
                                <span class="info-label">Reading</span>
                                <span class="info-value"><?php 
                                    echo $row != NULL && $row['reading'] != NULL ? $row['reading'] : "Empty";
                                    $toefl += $row != NULL && $row['reading'] != NULL ? $row['reading'] : 0;
                                ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Writing</span>
                                <span class="info-value"><?php 
                                    echo $row != NULL && $row['writing'] != NULL ? $row['writing'] : "Empty";
                                    $toefl += $row != NULL && $row['writing'] != NULL ? $row['writing'] : 0;
                                ?></span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-item">
                                <span class="info-label">Total Score</span>
                                <span class="info-value"><?php echo $toefl;?> / 120</span>
                            </div>
                            <div class="info-item"></div>
                        </div>
                        <div class="action-buttons">
                            <button class="btn btn-edit" onclick="editToefl()">Edit TOEFL</button>
                        </div>
                    </div>

                    <div id="toeflEditMode" style="display: none;">
                        <form id="toeflForm" class="form-grid" action="TOEFL.php" method="POST">
                            <div style="grid-column: 1 / -1;">
                                <div class="score-row">
                                    <div class="score-group">
                                        <label>Listening</label>
                                        <input type="number" id="toefl-listening" value="<?php echo $row['listening'] ?? '28'; ?>" min="0" max="30" name="listening" oninput="updateToeflTotal()">
                                    </div>
                                    <div class="score-group">
                                        <label>Speaking</label>
                                        <input type="number" id="toefl-speaking" value="<?php echo $row['speaking'] ?? '27'; ?>" min="0" max="30" name="speaking" oninput="updateToeflTotal()">
                                    </div>
                                    <div class="score-group">
                                        <label>Reading</label>
                                        <input type="number" id="toefl-reading" value="<?php echo $row['reading'] ?? '29'; ?>" min="0" max="30" name="reading" oninput="updateToeflTotal()">
                                    </div>
                                    <div class="score-group">
                                        <label>Writing</label>
                                        <input type="number" id="toefl-writing" value="<?php echo $row['writing'] ?? '27'; ?>" min="0" max="30" name="writing" oninput="updateToeflTotal()">
                                    </div>
                                </div>
                                <div class="score-row" style="margin-top: 15px;">
                                    <div class="score-group" style="grid-column: 1 / -1; max-width: 200px;">
                                        <label>Total Score</label>
                                        <input type="text" id="toefl-total" value="<?php 
                                            $current_total = ($row['listening'] ?? 0) + ($row['speaking'] ?? 0) + ($row['reading'] ?? 0) + ($row['writing'] ?? 0);
                                            echo $current_total > 0 ? $current_total : '';
                                        ?>" readonly style="background-color: #f5f5f5; cursor: not-allowed; color: #666;">
                                        <span style="font-size: 12px; color: #666; margin-left: 5px;">/ 120</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary" onclick="cancelToeflEdit()">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php 
                    $type = "IELTS";
                    $stmt = $conn->prepare("SELECT * FROM language_grade WHERE ID = ? AND type = ?");
                    $stmt->bind_param("is" , $ID , $type);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $IELTS = 0;
                ?>

                <div class="card">
                    <div class="card-title">IELTS Score</div>
                    <div id="ieltsViewMode">
                        <div class="info-row">
                            <div class="info-item">
                                <span class="info-label">Listening</span>
                                <span class="info-value"><?php 
                                    echo $row != NULL && $row['listening'] != NULL ? $row['listening'] : "Empty";
                                    $IELTS += $row != NULL && $row['listening'] != NULL ? $row['listening'] : 0;
                                ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Speaking</span>
                                <span class="info-value"><?php 
                                    echo $row != NULL && $row['speaking'] != NULL ? $row['speaking'] : "Empty";
                                    $IELTS += $row != NULL && $row['speaking'] != NULL ? $row['speaking'] : 0;
                                ?></span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-item">
                                <span class="info-label">Reading</span>
                                <span class="info-value"><?php 
                                    echo $row != NULL && $row['reading'] != NULL ? $row['reading'] : "Empty";
                                    $IELTS += $row != NULL && $row['reading'] != NULL ? $row['reading'] : 0;
                                ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Writing</span>
                                <span class="info-value"><?php 
                                    echo $row != NULL && $row['writing'] != NULL ? $row['writing'] : "Empty";
                                    $IELTS += $row != NULL && $row['writing'] != NULL ? $row['writing'] : 0;
                                ?></span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-item">
                                <span class="info-label">Total Score</span>
                                <span class="info-value"><?php 
                                    $ave = $IELTS / 4.0; 
                                    $decimal = $IELTS - floor($IELTS);
                                    if($decimal < 0.25) {
                                        echo floor($ave);
                                    } elseif($decimal < 0.75) {
                                        echo floor($ave) + 0.5;
                                    } else {
                                        echo ceil($ave);
                                    }
                                ?> / 9.0</span>
                            </div>
                            <div class="info-item"></div>
                        </div>
                        <div class="action-buttons">
                            <button class="btn btn-edit" onclick="editIelts()">Edit IELTS</button>
                        </div>
                    </div>

                    <div id="ieltsEditMode" style="display: none;">
                        <form id="ieltsForm" class="form-grid" action="IELTS.php" method="POST">
                            <div style="grid-column: 1 / -1;">
                                <div class="score-row">
                                    <div class="score-group">
                                        <label>Listening</label>
                                        <input type="number" id="ielts-listening" value="<?php echo $row['listening'] ?? '8.0'; ?>" min="0" max="9" step="0.5" name="listening" oninput="updateIeltsTotal()">
                                    </div>
                                    <div class="score-group">
                                        <label>Speaking</label>
                                        <input type="number" id="ielts-speaking" value="<?php echo $row['speaking'] ?? '7.5'; ?>" min="0" max="9" step="0.5" name="speaking" oninput="updateIeltsTotal()">
                                    </div>
                                    <div class="score-group">
                                        <label>Reading</label>
                                        <input type="number" id="ielts-reading" value="<?php echo $row['reading'] ?? '8.5'; ?>" min="0" max="9" step="0.5" name="reading" oninput="updateIeltsTotal()">
                                    </div>
                                    <div class="score-group">
                                        <label>Writing</label>
                                        <input type="number" id="ielts-writing" value="<?php echo $row['writing'] ?? '7.5'; ?>" min="0" max="9" step="0.5" name="writing" oninput="updateIeltsTotal()">
                                    </div>
                                </div>
                                <div class="score-row" style="margin-top: 15px;">
                                    <div class="score-group" style="grid-column: 1 / -1; max-width: 200px;">
                                        <label>Total Score (Average)</label>
                                        <input type="text" id="ielts-total" value="<?php 
                                            $listening = $row['listening'] ?? 0;
                                            $speaking = $row['speaking'] ?? 0;
                                            $reading = $row['reading'] ?? 0;
                                            $writing = $row['writing'] ?? 0;
                                            if ($listening > 0 || $speaking > 0 || $reading > 0 || $writing > 0) {
                                                $sum = $listening + $speaking + $reading + $writing;
                                                $ave = $sum / 4.0;
                                                $decimal = $ave - floor($ave);
                                                if ($decimal < 0.25) {
                                                    echo floor($ave);
                                                } elseif ($decimal < 0.75) {
                                                    echo floor($ave) + 0.5;
                                                } else {
                                                    echo ceil($ave);
                                                }
                                            }
                                        ?>" readonly style="background-color: #f5f5f5; cursor: not-allowed; color: #666;">
                                        <span style="font-size: 12px; color: #666; margin-left: 5px;">/ 9.0</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary" onclick="cancelIeltsEdit()">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Experience Panel -->
            <div id="experiencePanel" class="panel">
                <div class="panel-header">
                    <h2>Internship Experience</h2>
                    <p>Add and manage your professional work experience</p>
                </div>
                
                <div class="card">
                    <div id="experienceList">
                        <?php  
                            $stmt = $conn->prepare("SELECT * FROM intership WHERE ID = ?");
                            $stmt->bind_param("i" , $ID);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while($row = $result->fetch_assoc()) {
                        ?> 
                        <form action="delete_intership.php" method="POST">
                            <input type="hidden" name="iid" value="<?php echo $row['iid']; ?>"> 
                            <div class="entry">
                                <div class="entry-header">
                                    <div>
                                        <div class="entry-title"><?php echo $row['position']; ?></div>
                                        <div class="entry-subtitle"><?php echo $row['company'] . " · " . $row['duration']; ?></div>
                                    </div>
                                    <button class="btn btn-delete" type="submit">Delete</button>
                                </div>
                            </div>
                        </form>
                        <?php } ?> 
                    </div>
                    <button class="add-button" onclick="openAddExperienceModal()">+ Add Internship</button>
                </div>
            </div>

            <!-- Competition Panel -->
            <div id="competitionPanel" class="panel">
                <div class="panel-header">
                    <h2>Competition Achievements</h2>
                    <p>Showcase your competition awards and recognitions</p>
                </div>
                
                <div class="card">
                    <div id="competitionList">
                        <?php 
                            $stmt = $conn->prepare("SELECT * FROM competition_grade WHERE ID = ?");
                            $stmt->bind_param("i" , $ID);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while($row = $result->fetch_assoc()) {
                        ?>
                        <form action="delete_competition.php" method="POST"> 
                            <input type="hidden" name="cid" value="<?php echo $row['cid']; ?>">
                            <div class="entry">
                                <div class="entry-header">
                                    <div>
                                        <div class="entry-title"><?php echo $row['c_name']; ?></div>
                                        <div class="entry-subtitle"><?php echo $row['prize'] . " · " . $row['duration']; ?></div>
                                    </div> 
                                    <button class="btn btn-delete" type="submit">Delete</button> 
                                </div>
                            </div>
                        </form>
                        <?php } ?>
                    </div>
                    <button class="add-button" onclick="openAddCompetitionModal()">+ Add Competition</button>
                </div>
            </div>

            <!-- Documents Panel -->
            <div id="documentsPanel" class="panel">
                <div class="panel-header">
                    <h2>Documents</h2>
                    <p>Upload your certificates, transcripts, and other supporting documents</p>
                </div>
                
                <div class="card">
                    <div class="upload-section">
                        <h3>Upload Documents</h3>
                        <p class="upload-hint">You can upload individual files or a ZIP file containing all your documents (certificates, transcripts, etc.)</p>
                        
                        <form id="fileUploadForm" enctype="multipart/form-data">
                            <div class="upload-area" id="uploadArea">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Drag and drop files here or click to browse</p>
                                <p class="upload-types">Supported: PDF, DOC, DOCX, ZIP (Max 50MB)</p>
                                <input type="file" id="fileInput" name="file" accept=".pdf,.doc,.docx,.zip" style="display: none;" onchange="handleFileSelect(event)">
                            </div>
                            
                            <div class="file-preview" id="filePreview" style="display: none;">
                                <div class="preview-item">
                                    <i class="fas fa-file"></i>
                                    <span id="fileName"></span>
                                    <button type="button" onclick="clearFileSelection()" class="remove-btn">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-actions" style="margin-top: 20px;">
                                <button type="button" class="btn btn-secondary" onclick="clearFileSelection()">Cancel</button>
                                <button type="submit" class="btn btn-primary" id="uploadBtn">
                                    <i class="fas fa-upload"></i> Upload File
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="uploaded-files-section" style="margin-top: 40px;">
                        <h3>Uploaded Documents</h3>
                        <div id="uploadedFilesList" class="files-list">
                            <div class="empty-state">
                                <i class="fas fa-folder-open"></i>
                                <p>No documents uploaded yet</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Change Password Panel -->
            <div id="passwordPanel" class="panel">
                <div class="panel-header">
                    <h2>Change Password</h2>
                </div>
                
                <div class="card">
                    <form id="passwordForm" class="form-grid" onsubmit="event.preventDefault(); savePassword();">
                        <div class="form-group full">
                            <label>Current Password</label>
                            <input type="password" placeholder="Enter your current password" name="current" required>
                        </div>
                        <div class="form-group full">
                            <label>New Password</label>
                            <input type="password" placeholder="Enter new password (min. 6 characters)" name="New" required>
                        </div>
                        <div class="form-group full">
                            <label>Confirm New Password</label>
                            <input type="password" placeholder="Confirm new password" name="confirm" required>
                        </div>
                        
                        <?php 
                            if(isset($_SESSION['error'])) {
                                echo "<script>document.addEventListener('DOMContentLoaded', function() {
                                    showNotification('" . $_SESSION['error'] . "', 'error');
                                });</script>";
                                unset($_SESSION['error']);
                            }
                        ?>

                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" onclick="resetPasswordForm()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Change Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Experience Modal -->
    <form action="add_intership.php" method="POST"> 
        <div id="experienceModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 1000; align-items: center; justify-content: center; padding: 40px 20px;">
            <div style="background: white; border-radius: 8px; width: 100%; max-width: 500px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);">
                <div style="padding: 24px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 18px; font-weight: 600; color: #2c3e50;">Add Internship</div>
                    <button style="background: none; border: none; font-size: 24px; color: #95a5a6; cursor: pointer;" onclick="closeExperienceModal()">×</button>
                </div>
                <div style="padding: 24px;">
                    <div class="form-group">
                        <label>Company Name</label>
                        <input type="text" placeholder="e.g., Google" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Position</label>
                        <input type="text" placeholder="e.g., Software Engineer Intern" name="position" required>
                    </div>
                    <div class="form-group">
                        <label>Duration</label>
                        <input type="text" placeholder="e.g., Summer 2023" name="during" required>
                    </div>
                </div>
                <div style="padding: 16px 24px; border-top: 1px solid #f0f0f0; display: flex; gap: 8px;">
                    <button class="btn btn-secondary" onclick="closeExperienceModal()" style="flex: 1;">Cancel</button>
                    <button class="btn btn-primary" type="submit" style="flex: 1;">Add</button>
                </div>
            </div>
        </div>
    </form> 

    <!-- Add Competition Modal -->
    <form action="add_competition.php" method="POST">
        <div id="competitionModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 1000; align-items: center; justify-content: center; padding: 40px 20px;">
            <div style="background: white; border-radius: 8px; width: 100%; max-width: 500px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);">
                <div style="padding: 24px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 18px; font-weight: 600; color: #2c3e50;">Add Competition</div>
                    <button style="background: none; border: none; font-size: 24px; color: #95a5a6; cursor: pointer;" onclick="closeCompetitionModal()">×</button>
                </div>
                <div style="padding: 24px;">
                    <div class="form-group">
                        <label>Competition Name</label>
                        <input type="text" placeholder="e.g., ACM-ICPC" name="c_name" required>
                    </div>
                    <div class="form-group">
                        <label>Award</label>
                        <input type="text" placeholder="e.g., Gold Medal" name="prize" required>
                    </div>
                    <div class="form-group">
                        <label>Year</label>
                        <input type="number" placeholder="e.g., 2023" min="2000" max="2100" name="during" required>
                    </div>
                </div>
                <div style="padding: 16px 24px; border-top: 1px solid #f0f0f0; display: flex; gap: 8px;">
                    <button class="btn btn-secondary" onclick="closeCompetitionModal()" style="flex: 1;">Cancel</button>
                    <button class="btn btn-primary" type="submit" style="flex: 1;">Add</button>
                </div>
            </div>
        </div>
    </form>
    
    <script src="JS/profile2.js"></script>
</body>
</html>