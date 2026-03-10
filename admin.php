<?php 
    require 'connect.php';
    session_start();
    
    // Check if logged in and is admin
    if (!isset($_SESSION['username']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
        header("Location: login_.php");
        exit;
    }
?>

<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OAS Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/admin.css">
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <ul class="ant-menu">
                <li class="ant-menu-submenu open">
                    <div class="ant-menu-submenu-title" onclick="toggleSubmenu(this)">
                        <span class="ant-menu-item-icon"><i class="fas fa-university"></i></span>
                        <span>University Management</span>
                        <span class="ant-menu-submenu-arrow"><i class="fas fa-chevron-down"></i></span>
                    </div>
                    <ul class="ant-menu-sub">
                        <li class="ant-menu-item-group">
                            <div class="ant-menu-item-group-title">Universities</div>
                            <li class="ant-menu-item active" data-page="universityList" onclick="navigateTo('universityList', this)">University List</li>
                            <li class="ant-menu-item" data-page="addUniversity" onclick="navigateTo('addUniversity', this)">Add University</li>
                        </li>
                        <li class="ant-menu-item-group">
                            <div class="ant-menu-item-group-title">Programs</div>
                            <li class="ant-menu-item" data-page="programList" onclick="navigateTo('programList', this)">Program List</li>
                            <li class="ant-menu-item" data-page="addProgram" onclick="navigateTo('addProgram', this)">Add Program</li>
                        </li>
                    </ul>
                </li>
                <li class="ant-menu-submenu open">
                    <div class="ant-menu-submenu-title" onclick="toggleSubmenu(this)">
                        <span class="ant-menu-item-icon"><i class="fas fa-users"></i></span>
                        <span>User Management</span>
                        <span class="ant-menu-submenu-arrow"><i class="fas fa-chevron-down"></i></span>
                    </div>
                    <ul class="ant-menu-sub">
                        <li class="ant-menu-item" data-page="officerList" onclick="navigateTo('officerList', this)">Officer List</li>
                        <li class="ant-menu-item" data-page="officerApplications" onclick="navigateTo('officerApplications', this)">Officer Applications</li>
                    </ul>
                </li>
                <li class="ant-menu-divider"></li>
                <li class="ant-menu-item" data-page="changePassword" onclick="navigateTo('changePassword', this)"><span class="ant-menu-item-icon"><i class="fas fa-key"></i></span><span>Change Password</span></li>
                <li class="ant-menu-divider"></li>
                <li class="ant-menu-item" onclick="window.location.href='logout.php'"><span class="ant-menu-item-icon"><i class="fas fa-sign-out-alt"></i></span><span>Logout</span></li>
            </ul>
        </aside>

        <main class="main-content">
            <!-- University List -->

            <div class="page active" id="page-universityList">
                <div class="page-header"><h1 class="page-title">University List</h1></div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>University </th>
                                <th>Country / City</th>
                                <th>QS Ranking</th>
                                <th>Website</th>
                                <th style="width:160px">Actions</th>
                            </tr>
                        </thead>

                        

                        <tbody id="universityTableBody">
                            <tr><td colspan="5"><div class="empty-state"><i class="fas fa-university"></i><p>no data</p></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>




            <!-- Add University -->
            <div class="page" id="page-addUniversity">
                <div class="page-header"><h1 class="page-title">Add University</h1></div>
                <div class="form-container">
                    <form id="addUniForm">
                        <div class="form-group">
                            <label class="form-label">University Logo</label>
                            <div class="upload-area" id="addUniUploadArea" onclick="document.getElementById('addUniLogoInput').click()">
                                <input type="file" id="addUniLogoInput" accept="image/*" style="display:none" onchange="handleFileSelect(event,'addUni')">
                                <div class="upload-content" id="addUniUploadContent">
                                    <div class="upload-icon"><i class="fas fa-inbox"></i></div>
                                    <div class="upload-text">Click or drag file to this area to upload</div>
                                    <div class="upload-hint">Support PNG, JPG format. Max 2MB.</div>
                                </div>
                                <div class="upload-preview" id="addUniUploadPreview">
                                    <img id="addUniPreviewImg" src="" alt="Preview">
                                    <span class="remove-btn" onclick="event.stopPropagation();removeLogo('addUni')"><i class="fas fa-trash"></i> Remove</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group"><label class="form-label">University Name <span class="required">*</span></label><input type="text" class="form-input" id="addUniName" placeholder="e.g. Stanford University"></div>
                        <div class="form-row">
                            <div class="form-group"><label class="form-label">Country <span class="required">*</span></label><input type="text" class="form-input" id="addUniCountry" placeholder="e.g. United States"></div>
                            <div class="form-group"><label class="form-label">City</label><input type="text" class="form-input" id="addUniCity" placeholder="e.g. Stanford"></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group"><label class="form-label">QS Ranking</label><input type="number" class="form-input" id="addUniRanking" placeholder="e.g. 1"></div>
                            <div class="form-group"><label class="form-label">Website</label><input type="url" class="form-input" id="addUniWebsite" placeholder="https://"></div>
                        </div>
                        <div class="form-group"><label class="form-label">Description</label><textarea class="form-input form-textarea" id="addUniDescription" placeholder="Enter description"></textarea></div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-primary" onclick="addUniversity()">Save</button>
                            <button type="button" class="btn btn-default" onclick="navigateTo('universityList',document.querySelector('[data-page=universityList]'))">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Program List -->
            <div class="page" id="page-programList">
                <div class="page-header"><h1 class="page-title">Program List</h1></div>
                <div class="table-container">
                    <table class="data-table">
                        <thead><tr><th>Program</th><th>University</th><th>Degree</th><th>Duration</th><th>Deadline</th><th style="width:160px">Actions</th></tr></thead>
                        <tbody id="programTableBody">
                            <tr><td colspan="6"><div class="empty-state"><i class="fas fa-graduation-cap"></i><p>No programs yet</p></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add Program -->
            <div class="page" id="page-addProgram">
                <div class="page-header"><h1 class="page-title">Add Program</h1></div>
                <div class="form-container">
                    <form id="addProgForm">
                        <div class="form-group">
                            <label class="form-label">University <span class="required">*</span></label>
                            <select class="form-input form-select" id="addProgUniversity"></select>
                        </div>
                        <div class="form-group"><label class="form-label">Program Name <span class="required">*</span></label><input type="text" class="form-input" id="addProgName" placeholder="e.g. Master of Computer Science"></div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Degree <span class="required">*</span></label>
                                <select class="form-input form-select" id="addProgDegree">
                                    <option value="Master">Master</option>
                                    <option value="PhD">PhD</option>
                                    <option value="MBA">MBA</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group"><label class="form-label">Duration</label><input type="text" class="form-input" id="addProgDuration" placeholder="e.g. 2 years"></div>
                            <div class="form-group"><label class="form-label">Deadline</label><input type="date" class="form-input" id="addProgDeadline"></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group"><label class="form-label">Min GPA</label><input type="number" step="0.01" class="form-input" id="addProgGPA" placeholder="e.g. 3.5"></div>
                            <div class="form-group"><label class="form-label">Category</label><input type="text" class="form-input" id="addProgCategory" placeholder="e.g. Engineering & Technology"></div>
                        </div>
                        <div class="form-group"><label class="form-label">Language Requirement</label><input type="text" class="form-input" id="addProgLanguageReq" placeholder="e.g. TOEFL 100 or IELTS 7.0"></div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-primary" onclick="addProgram()">Save</button>
                            <button type="button" class="btn btn-default" onclick="navigateTo('programList',document.querySelector('[data-page=programList]'))">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Officer List -->
            <div class="page" id="page-officerList">
                <div class="page-header"><h1 class="page-title">Officer List</h1></div>
                <div class="table-container">
                    <table class="data-table">
                        <thead><tr><th>Name</th><th>Email</th><th>University</th><th>Programs</th><th>Applications</th><th style="width:100px">Actions</th></tr></thead>
                        <tbody id="officerTableBody">
                            <tr><td colspan="6"><div class="empty-state"><i class="fas fa-users"></i><p>No admission officers yet</p></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Officer Applications -->
            <div class="page" id="page-officerApplications">
                <div class="page-header"><h1 class="page-title">Officer Applications</h1></div>
                <div class="table-container">
                    <table class="data-table">
                        <thead><tr><th>Name</th><th>Email</th><th>University</th><th style="width:180px">Actions</th></tr></thead>
                        <tbody id="applicationTableBody">
                            <tr><td colspan="4"><div class="empty-state"><i class="fas fa-inbox"></i><p>No pending applications</p></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Change Password -->
            <div class="page" id="page-changePassword">
                <div class="page-header"><h1 class="page-title">Change Password</h1></div>
                <div class="form-container">
                    <form id="changePasswordForm">
                        <div class="form-group">
                            <label class="form-label">Current Password <span class="required">*</span></label>
                            <input type="password" class="form-input" id="currentPassword" placeholder="Enter current password">
                        </div>
                        <div class="form-group">
                            <label class="form-label">New Password <span class="required">*</span></label>
                            <input type="password" class="form-input" id="newPassword" placeholder="Enter new password">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm New Password <span class="required">*</span></label>
                            <input type="password" class="form-input" id="confirmNewPassword" placeholder="Confirm new password">
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-primary" onclick="changePassword()">Confirm</button>
                            <button type="button" class="btn btn-default" onclick="navigateTo('universityList',document.querySelector('[data-page=universityList]'))">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Edit University Modal -->
    <div class="modal-overlay" id="editUniModal">
        <div class="modal">
            <div class="modal-header"><h3 class="modal-title">Edit University</h3><button class="modal-close" onclick="closeModal('editUniModal')">&times;</button></div>
            <div class="modal-body">
                <input type="hidden" id="editUniId">
                <div class="form-group">
                    <label class="form-label">University Logo</label>
                    <div class="upload-area" id="editUniUploadArea" onclick="document.getElementById('editUniLogoInput').click()">
                        <input type="file" id="editUniLogoInput" accept="image/*" style="display:none" onchange="handleFileSelect(event,'editUni')">
                        <div class="upload-content" id="editUniUploadContent">
                            <div class="upload-icon"><i class="fas fa-inbox"></i></div>
                            <div class="upload-text">Click or drag file to upload</div>
                            <div class="upload-hint">PNG, JPG. Max 2MB.</div>
                        </div>
                        <div class="upload-preview" id="editUniUploadPreview">
                            <img id="editUniPreviewImg" src="" alt="Preview">
                            <span class="remove-btn" onclick="event.stopPropagation();removeLogo('editUni')"><i class="fas fa-trash"></i> Remove</span>
                        </div>
                    </div>
                </div>
                <div class="form-group"><label class="form-label">University Name <span class="required">*</span></label><input type="text" class="form-input" id="editUniName"></div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Country <span class="required">*</span></label><input type="text" class="form-input" id="editUniCountry"></div>
                    <div class="form-group"><label class="form-label">City</label><input type="text" class="form-input" id="editUniCity"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">QS Ranking</label><input type="number" class="form-input" id="editUniRanking"></div>
                    <div class="form-group"><label class="form-label">Website</label><input type="url" class="form-input" id="editUniWebsite"></div>
                </div>
                <div class="form-group"><label class="form-label">Description</label><textarea class="form-input form-textarea" id="editUniDescription"></textarea></div>
            </div>
            <div class="modal-footer"><button class="btn btn-default" onclick="closeModal('editUniModal')">Cancel</button><button class="btn btn-primary" onclick="saveUniversity()">Save</button></div>
        </div>
    </div>

    <!-- Edit Program Modal -->
    <div class="modal-overlay" id="editProgModal">
        <div class="modal">
            <div class="modal-header"><h3 class="modal-title">Edit Program</h3><button class="modal-close" onclick="closeModal('editProgModal')">&times;</button></div>
            <div class="modal-body">
                <input type="hidden" id="editProgId">
                <input type="hidden" id="editProgUniversityId">
                <div class="form-group"><label class="form-label">University</label><input type="text" class="form-input" id="editProgUniversityDisplay" readonly style="background-color: #f5f5f5; cursor: not-allowed;"></div>
                <div class="form-group"><label class="form-label">Program Name <span class="required">*</span></label><input type="text" class="form-input" id="editProgName"></div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Degree</label><select class="form-input form-select" id="editProgDegree"><option value="Master">Master</option><option value="PhD">PhD</option><option value="MBA">MBA</option></select></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Duration</label><input type="text" class="form-input" id="editProgDuration"></div>
                    <div class="form-group"><label class="form-label">Deadline</label><input type="date" class="form-input" id="editProgDeadline"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Min GPA</label><input type="number" step="0.01" class="form-input" id="editProgGPA"></div>
                    <div class="form-group"><label class="form-label">Category</label><input type="text" class="form-input" id="editProgCategory"></div>
                </div>
                <div class="form-group"><label class="form-label">Language Requirement</label><input type="text" class="form-input" id="editProgLanguageReq"></div>
            </div>
            <div class="modal-footer"><button class="btn btn-default" onclick="closeModal('editProgModal')">Cancel</button><button class="btn btn-primary" onclick="saveProgram()">Save</button></div>
        </div>
    </div>

    <!-- Delete Confirmation -->
    <div class="modal-overlay confirm-modal" id="deleteModal">
        <div class="modal">
            <div class="modal-body">
                <div class="confirm-content">
                    <div class="confirm-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <div class="confirm-message"><h4 id="deleteTitle">Delete?</h4><p id="deleteText">This action cannot be undone.</p></div>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-default" onclick="closeModal('deleteModal')">Cancel</button><button class="btn btn-danger" onclick="confirmDelete()">Delete</button></div>
        </div>
    </div>

    <!-- Accept Confirmation -->

    <div class="toast" id="toast"><i class="fas fa-check-circle"></i><span id="toastMessage"></span></div>


    <script src = "JS/admin.js"> </script>
</body>
</html>