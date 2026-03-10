<?php 
    require 'connect.php';
    session_start();
    
    // Check if user is logged in
    if(!isset($_SESSION['username'])) {
        header("Location: login_.php");
        exit;
    }
    
    // Start timing
    $page_start_time = microtime(true);
    
    $sid = $_GET['sid'];
    
    // Time query 1: Get school information
    $query1_start = microtime(true);
    $result = $conn->query("SELECT * FROM school WHERE sid = '$sid'");
    $query1_end = microtime(true);
    $query1_time = ($query1_end - $query1_start) * 1000; // Convert to milliseconds
    $row = $result->fetch_assoc();
    
    // Time query 2: Get programs
    $query2_start = microtime(true);
    $program_result = $conn->query("SELECT * FROM program WHERE sid = '$sid'");
    $query2_end = microtime(true);
    $query2_time = ($query2_end - $query2_start) * 1000; // Convert to milliseconds
    
    // Calculate total query time
    $total_query_time = $query1_time + $query2_time;
    
    // End timing
    $page_end_time = microtime(true);
    $page_load_time = ($page_end_time - $page_start_time) * 1000; // Convert to milliseconds
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stanford University - Programs</title>
    <link rel = "stylesheet" href = "CSS/program.css"> 
    <link rel="stylesheet" href="CSS/nav.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-left">
            <a href="index.html" class="logo">OAS</a>
            <div class="nav-buttons">
                <p>OAS / View Colleges / Detail</p>
            </div>
        </div>
        <div class="nav-right">
            <a href="profile2.php" class="user-profile">
                <div class="avatar"><?php echo isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 1)) : ''; ?></div>
                <span>Profile</span>
            </a>
        </div>
    </nav>

    <!-- Toast notification -->
    <?php if(isset($_SESSION['error'])): ?>
    <div class="toast toast-error" id="toast">
        <span class="toast-icon">✕</span>
        <span class="toast-msg"><?php echo $_SESSION['error']; ?></span>
    </div>
    <?php unset($_SESSION['error']); endif; ?>

    <?php if(isset($_SESSION['success'])): ?>
    <div class="toast toast-success" id="toast">
        <span class="toast-icon">✓</span>
        <span class="toast-msg"><?php echo $_SESSION['success']; ?></span>
    </div>
    <?php unset($_SESSION['success']); endif; ?>


    <div class="container">
        <!-- Left filter sidebar -->
        <aside class="sidebar">
            <div class="filter-header">
                <h3>Filter</h3>
                <button class="clear-btn" onclick="clearFilters()">Clear all</button>
            </div>

            <div class="filter-group">
                <div class="filter-title active"> Area of Interest  </div>
                <div class="filter-options">
                    <div class="checkbox-item">
                        <!-- Note: Added class="category-filter" -->
                        <input type="checkbox" id="cs" value="Arts & Humanities" class="category-filter">
                        <label for="cs"> Arts & Humanities </label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="ee" value="Business & Management" class="category-filter">
                        <label for="ee"> Business & Management </label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="ai" value="Clinical & Health Sciences" class="category-filter">
                        <label for="ai">Clinical & Health Sciences</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="data" value="Computer Science & Information Technology" class="category-filter">
                        <label for="data">Computer Science & Information Technology</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="business" value="Engineering & Technology" class="category-filter">
                        <label for="business">Engineering & Technology</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="bio" value="Environmental Studies" class="category-filter">
                        <label for="bio">Environmental Studies</label>
                    </div>
                </div>
            </div>

            <div class="filter-group">
                <div class="filter-title active">Degree Type</div>
                <div class="filter-options">
                    <div class="checkbox-item">
                        <input type="checkbox" id="master" value="master" class="degree-filter">
                        <label for="master">Master</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="mba" value="mba" class="degree-filter">
                        <label for="mba">MBA</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="phd" value="phd" class="degree-filter">
                        <label for="phd">PhD</label>
                    </div>
                </div>
            </div>

        </aside>

        <!-- Right main content -->
        <!-- Right main content -->
        <main class="main-content">
            <div class="content-header">
                <h3> <?php echo $row['school_name'] ?> Graduate Programs 2025</h3>
                <!-- Note: Added id="program-count" -->
                <h2><span id="program-count">0</span> Programs</h2>
                <div class="search-bar" style="margin: 0;">
                    <!-- Note: Added id="search-input" and onkeyup -->
                    <input type="text" id="search-input" placeholder="Searching" onkeyup="filterPrograms()">
                </div>
            </div>

            <?php 
                if(isset($_SESSION['error'])) {
                    echo "<script>document.addEventListener('DOMContentLoaded', function() {
                        showNotification('" . $_SESSION['error'] . "', 'error');
                    });</script>";
                    unset($_SESSION['error']);
                }
            ?>


            <div class="programs-grid">
                    <!-- Program card 1 -->
                    <?php 
                        while($row = $program_result->fetch_assoc()) {
                    ?>
                    <div class="program-card" 
                                data-category="<?php echo $row['category']; ?>" 
                                data-degree-type="<?php echo htmlspecialchars(strtolower($row['degree_type'] ?? 'master')); ?>"
                                data-name="<?php echo $row['pname']; ?>"
                                data-gpa="<?php echo $row['gpa_requirement']; ?>"
                                data-lang="<?php echo $row['language_requirement']; ?>"
                                data-ddl="<?php echo $row['ddl']; ?>"
                            > 
                        <form action = "apply.php" method = "POST"> 
                            <input type = "hidden" name = "sid" value = "<?php echo $row['sid']; ?> ">
                            <input type = "hidden" name = "pid" value = "<?php echo $row['pid']; ?>" >
                            <div class="program-title"><?php echo $row['pname']; ?></div>
                            <div class="program-bottom">
                                <div class="program-details">
                                    <span class="detail-item">GPA: <?php echo $row['gpa_requirement']; ?>+</span>
                                    <span class="detail-item"><?php echo $row['language_requirement']; ?></span>
                                    <span class="detail-item">Deadline: <?php echo $row['ddl']; ?></span>
                                </div>
                                <div class="modal-actions">
                                    <div class="popconfirm-wrapper">
                                        <button class="apply-btn" id="apply-btn" type = "button" >Apply Now</button>
                                        
                                        <!-- Popup confirmation box -->
                                        <div class="popconfirm" id="popconfirm">
                                            <div class="popconfirm-content">
                                                <div class="popconfirm-text">
                                                    <div class="popconfirm-title">Confirm Application</div>
                                                    <div class="popconfirm-desc">Are you sure to apply for this program?</div>
                                                </div>
                                            </div>
                                            <div class="popconfirm-buttons">
                                                <button class="pop-btn pop-no" onclick="hidePopconfirm()" type = "button" >No</button>
                                                <button class="pop-btn pop-yes" id="confirm-apply" type = "submit" >Yes</button>
                                            </div>
                                            <div class="popconfirm-arrow"></div>
                                        </div>
                                        <!--<button class="close-btn-secondary" onclick="closeModal()">Close</button>-->
                                    </div>
                                </div>
                            </div>
                        </form> 
                    </div>
                    <?php } ?>
                </div>
        </main>
    </div>
    
    <!-- Query Performance Display -->
    <div style="position: fixed; bottom: 20px; right: 20px; background: rgba(0, 0, 0, 0.8); color: white; padding: 15px 20px; border-radius: 8px; font-family: monospace; font-size: 12px; z-index: 1000; box-shadow: 0 4px 6px rgba(0,0,0,0.3);">
        <div style="font-weight: bold; margin-bottom: 8px; color: #4CAF50;">Query Performance</div>
        <div style="margin-bottom: 4px;">School Query: <span style="color: #FFD700;"><?php echo number_format($query1_time, 2); ?> ms</span></div>
        <div style="margin-bottom: 4px;">Programs Query: <span style="color: #FFD700;"><?php echo number_format($query2_time, 2); ?> ms</span></div>
        <div style="margin-bottom: 4px; border-top: 1px solid rgba(255,255,255,0.3); padding-top: 4px; margin-top: 4px;">Total Query Time: <span style="color: #4CAF50; font-weight: bold;"><?php echo number_format($total_query_time, 2); ?> ms</span></div>
        <div style="margin-top: 8px; border-top: 1px solid rgba(255,255,255,0.3); padding-top: 4px;">Page Load Time: <span style="color: #2196F3;"><?php echo number_format($page_load_time, 2); ?> ms</span></div>
    </div>
    
    <script src = "JS/program.js"> </script>
</body>
</html>