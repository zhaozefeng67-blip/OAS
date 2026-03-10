<?php 
    require 'connect.php';
    session_start();
    if(!isset($_SESSION['username'])) {
        header("Location: login_.php");
        exit;
    }
    $username = $_SESSION['username'];
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
    <title>My Applications</title>
    <link rel="stylesheet" href="CSS/nav.css">
    <link rel="stylesheet" href="CSS/My_applications.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-left">
            <a href="index.html" class="logo">OAS</a>
            <div class="nav-buttons">
                <p>OAS / My Applications</p>
            </div>
        </div>
        <div class="nav-right">
            <a href="profile2.php" class="user-profile">
                <div class="avatar"><?php echo strtoupper(substr($username, 0, 1)); ?></div>
                <span>Profile</span>
            </a>
            <a href="logout.php" class="user-profile" style="color: #8B0000; margin-left: 15px;">
                <span>Logout</span>
            </a>
        </div>
    </nav>
    <div class="container">
        <!-- Header area -->

        <?php if(isset($_SESSION['success'])) { ?>
            <div class="toast toast-success" id="toast">
                <span class="toast-icon">✓</span>
                <span class="toast-msg"><?php echo $_SESSION['success']; ?></span>
            </div>
        <?php unset($_SESSION['success']); } ?> 
        <div class="header">
            <div class="tabs">
                <button class="tab active">All Applications</button>
            </div>
            <div class="header-actions">
                <select class="filter-select" id="statusFilter" onchange="filterByStatus()">
                    <option value="all">All Status</option>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Rejected">Rejected</option>
                </select>
                <div class="search-box">
                    <input type="text" placeholder="Search...">
                </div>
            </div>
        </div>

        <!-- Table area -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" class="checkbox"></th>
                        <th>University</th>
                        <th>Program</th>
                        <th>Status</th>
                        <th>Submission Date</th>
                        <th>Deadline</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $result = $conn->query("SELECT * FROM apply WHERE ID = '$ID'");
                        while($row = $result->fetch_assoc()) {  
                    ?>
                        <?php 
                            $sid = $row['sid'];
                            $pid = $row['pid'];
                            $status = $row['status'];
                            $apply_date = $row['apply_date'];
                            $ans = $conn->query("SELECT * FROM school WHERE sid = '$sid'");
                            $school = $ans->fetch_assoc();
                            $sname = $school['school_name'];

                            $ans = $conn->query("SELECT * FROM program WHERE sid = '$sid' AND pid = '$pid'");
                            $program = $ans->fetch_assoc();
                            $pname = $program['pname'];
                            $ddl = $program['ddl'];
                        ?>
                        <form action = "withdraw.php" method = "POST" >  
                            <input type = "hidden" name = "ID" value = "<?php echo $ID; ?>"> 
                            <input type = "hidden" name = "sid" value = "<?php echo $sid; ?>"> 
                            <input type = "hidden" name = "pid" value = "<?php echo $pid; ?>"> 
                            <tr data-status="<?php echo htmlspecialchars($status); ?>">
                                <td><input type="checkbox" class="checkbox"></td>
                                <td>
                                    <span class="title-text"> <?php echo $sname; ?> </span>
                                </td>
                                <td> <?php echo $pname; ?>  </td>
                                <td>
                                    <?php 
                                        $statusClass = 'status-submitted';
                                        if ($status == 'Approved') {
                                            $statusClass = 'status-approved';
                                        } elseif ($status == 'Rejected') {
                                            $statusClass = 'status-rejected';
                                        } elseif ($status == 'Pending') {
                                            $statusClass = 'status-pending';
                                        }
                                    ?>
                                    <span class="status-badge <?php echo $statusClass; ?>"> <?php echo $status; ?> </span>
                                </td>
                                <td> <?php echo $apply_date; ?> </td>
                                <td> <?php echo $ddl; ?> </td>
                                <td>
                                    <button type = "submit" class = "withdraw-btn"> withdraw </button> 
                                </td>
                            </tr>
                        </form>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        // Filter rows by status
        function filterByStatus() {
            const selectedStatus = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                
                if (selectedStatus === 'all') {
                    row.style.display = '';
                } else if (rowStatus === selectedStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        // Apply status styles on page load
        document.addEventListener('DOMContentLoaded', function() {
            const statusBadges = document.querySelectorAll('.status-badge');
            statusBadges.forEach(badge => {
                const statusText = badge.textContent.trim();
                badge.className = 'status-badge';
                if (statusText === 'Approved') {
                    badge.classList.add('status-approved');
                } else if (statusText === 'Rejected') {
                    badge.classList.add('status-rejected');
                } else if (statusText === 'Pending') {
                    badge.classList.add('status-pending');
                } else {
                    badge.classList.add('status-submitted');
                }
            });
        });

        // Select all functionality
        document.querySelectorAll('thead .checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                document.querySelectorAll('tbody .checkbox').forEach(cb => {
                    cb.checked = this.checked;
                });
            });
        });

        // Tab switching functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>