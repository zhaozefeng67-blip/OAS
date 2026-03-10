<?php   
    require 'connect.php';
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="school.css">
    <title>选择院校</title>
</head>
<body>
    <div class="user-menu-container">
        <button class="user-menu-btn" onclick="toggleUserMenu()">
            <span class="user-avatar">👤</span>
        </button>
        <div class="user-dropdown" id="userDropdown">
            <a href="./profile.html" class="dropdown-item">Profile</a>
            <a href="./My_applications.html" class="dropdown-item">Applications</a>
            <a href="#" class="dropdown-item">Logout</a>
        </div>
    </div>

    <div class="container">
        <!-- 左侧筛选栏 -->
        <div class="sidebar">
            <div class="sidebar-header">
                <span style="font-weight: 600;">筛选</span>
                <button onclick="clearFilters()">清除</button>
            </div>

            <div class="filter-group">
                <div class="filter-group-title">国家/地区</div>
                <div class="filter-options">
                    <div class="checkbox-item">
                        <input type="checkbox" id="China" value="China" onchange="filterJobs()">
                        <label for="China">中国大陆</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="HongKong" value="HongKong" onchange="filterJobs()">
                        <label for="HongKong">中国香港</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="Singapore" value="Singapore" onchange="filterJobs()">
                        <label for="Singapore">新加坡</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="Britain" value="Britain" onchange="filterJobs()">
                        <label for="Britain">英国</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="America" value="America" onchange="filterJobs()">
                        <label for="America">美国</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="Europe" value="Europe" onchange="filterJobs()">
                        <label for="Europe">欧洲</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- 右侧内容区 -->
        <div class="main-content">
            <div class="content-header">
                <div class="content-title">选择你心仪的院校</div>
            </div>

            <?php
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
                    <div class="card-scale" onclick="" data-category="<?php echo $country?>">
                        <!-- 图片容器 - 关键修改：img 标签不再有 class="card-image" -->
                        <div class="card-image">
                            <img src="get_image.php?id=<?php echo $row['sid'] ?>" alt="<?php echo $school_name; ?>">
                        </div>
                        
                        <div class="card-content">
                            <h3 class="card-title"><?php echo $school_name; ?></h3>
                            <p class="card-description"><?php echo $description; ?></p>
                            <div class="card-footer"> 
                                <div class="card-tags">
                                    <span class="tag">QS Rank <?php echo $ranking; ?></span>
                                </div>
                                <span class="card-arrow">→</span>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <script src="JS/school.js"></script>
</body>
</html>
