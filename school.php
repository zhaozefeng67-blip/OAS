<?php   
    require 'connect.php';
    # require 'import_logo.php';
    session_start();
    if(!isset($_SESSION['username'])) {
        header("Location: login_.php");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="test_picture/school.css">
    <link rel="stylesheet" href="CSS/nav.css">
    <link rel="stylesheet" href="CSS/school.css">
</head>
<body>
     <nav class="navbar">
        <div class="nav-left">
            <a href="index.html" class="logo">OAS</a>
            <div class="nav-buttons">
                <p>OAS / View Colleges</p>
            </div>
        </div>
        <div class="nav-right">
            <a href="profile2.php" class="user-profile">
                <div class="avatar"><?php echo isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 1)) : ''; ?></div>
                <span>Profile</span>
            </a>
        </div>
    </nav>

    <div class="container">
        <!-- Left filter sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <span style="font-weight: 600;">Filter</span>
                <button onclick="clearFilters()">Clear all</button>
            </div>

            <div class="filter-group">
                <div class="filter-group-title">Country/Region</div>
                <div class="filter-options">
                    <div class="checkbox-item">
                        <input type="checkbox" id="China" value="China" onchange="filterJobs()">
                        <label for="China">Mainland China</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="HongKong" value="HongKong" onchange="filterJobs()">
                        <label for="HongKong">Hong Kong, China</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="Singapore" value="Singapore" onchange="filterJobs()">
                        <label for="Singapore">Singapore</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="Britain" value="Britain" onchange="filterJobs()">
                        <label for="Britain">UK</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="America" value="America" onchange="filterJobs()">
                        <label for="America">USA</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="Europe" value="Europe" onchange="filterJobs()">
                        <label for="Europe">Europe</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right content area -->
        <div class="main-content">
            <div class="content-header">
                <div class="content-title">Choose Your Colleges</div>
            </div>

            <?php
                // $sql = "SELECT * FROM school";
                $result = $conn->query("SELECT * FROM school");
            ?>

            <div class="cards-grid">
                <?php while($row = $result->fetch_assoc()) { ?> 
                    <?php 
                        $rid = $row['rid'];
                        $school_name = $row['school_name'];
                        $description = $row['description'];
                        $ranking = $row['QS_rank'];
                        $res = $conn->query("SELECT * FROM region WHERE rid = $rid");
                        $region = $res->fetch_assoc();
                        $country = $region['country'];
                        $city = $region['city'];
                    ?>
                    <div class="card-scale" onclick = "window.location.href='program.php?sid=<?php echo $row['sid']; ?>'" data-category = <?php echo $city == "HongKong" ? "HongKong" : $country; ?> >
                        <!--<div class="card-image" style="background-image: url('pic/1762522361685_NTU.jpg');"></div>-->
                        <!-- Image should be output here -->
                        <div class = "card-image">
                            <img src="get_image.php?id=<?php echo $row['sid'] ?>" class = "card-image">
                        </div>
                        <div class="card-content">
                            <h3 class="card-title"> <?php echo $school_name; ?> </h3>
                            <div class="card-footer"> 
                                <div class="card-tags">
                                    <span class="tag"> <?php echo "QS Rank " . $ranking; ?> </span>
                                </div>
                                <span class="card-arrow">→</span>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <!-------------------------------------------------->
            </div>
        </div>
    </div>

    <script src="JS/school.js"></script>

</body>
</html>