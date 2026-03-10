<?php 
require 'connect.php';
session_start();

// Check if logged in and is officer
if (!isset($_SESSION['username']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'operator') {
    header("Location: login_.php");
    exit;
}

$operator_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OAS Officer Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/admin.css">
    <style>
        .applicant-cell { display: flex; flex-direction: column; gap: 2px; }
        .applicant-name { font-weight: 500; }
        .applicant-email { font-size: 12px; color: rgba(0,0,0,0.45); }
        .status-pending { background: #fff7e6; color: #fa8c16; }
        .status-approved { background: #f6ffed; color: #52c41a; }
        .status-rejected { background: #fff1f0; color: #ff4d4f; }
        .btn-approve { color: #52c41a; border-color: #52c41a; }
        .btn-approve:hover { background: #52c41a; color: #fff; }
        .btn-reject { color: #ff4d4f; border-color: #ff4d4f; }
        .btn-reject:hover { background: #ff4d4f; color: #fff; }
        .btn-revoke { color: #fa8c16; border-color: #fa8c16; }
        .btn-revoke:hover { background: #fa8c16; color: #fff; }
        /* Detail styles */
        .detail-section { margin-bottom: 24px; }
        .detail-section-title { font-size: 14px; font-weight: 600; color: rgba(0,0,0,0.88); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #f0f0f0; }
        .detail-row { display: flex; margin-bottom: 12px; }
        .detail-label { width: 120px; color: rgba(0,0,0,0.45); font-size: 14px; flex-shrink: 0; }
        .detail-value { flex: 1; color: rgba(0,0,0,0.88); font-size: 14px; }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <ul class="ant-menu">
                <li class="ant-menu-submenu open">
                    <div class="ant-menu-submenu-title" onclick="toggleSubmenu(this)">
                        <span class="ant-menu-item-icon"><i class="fas fa-file-alt"></i></span>
                        <span>Application Management</span>
                        <span class="ant-menu-submenu-arrow"><i class="fas fa-chevron-down"></i></span>
                    </div>
                    <ul class="ant-menu-sub">
                        <li class="ant-menu-item active" data-page="pending" onclick="navigateTo('pending', this)">Pending</li>
                        <li class="ant-menu-item" data-page="approved" onclick="navigateTo('approved', this)">Approved</li>
                        <li class="ant-menu-item" data-page="rejected" onclick="navigateTo('rejected', this)">Rejected</li>
                    </ul>
                </li>
                <li class="ant-menu-submenu open">
                    <div class="ant-menu-submenu-title" onclick="toggleSubmenu(this)">
                        <span class="ant-menu-item-icon"><i class="fas fa-graduation-cap"></i></span>
                        <span>Program Management</span>
                        <span class="ant-menu-submenu-arrow"><i class="fas fa-chevron-down"></i></span>
                    </div>
                    <ul class="ant-menu-sub">
                        <li class="ant-menu-item" data-page="programList" onclick="navigateTo('programList', this)">Program List</li>
                        <li class="ant-menu-item" data-page="addProgram" onclick="navigateTo('addProgram', this)">Add Program</li>
                    </ul>
                </li>
                <li class="ant-menu-divider"></li>
                <li class="ant-menu-item" data-page="changePassword" onclick="navigateTo('changePassword', this)">
                    <span class="ant-menu-item-icon"><i class="fas fa-key"></i></span>
                    <span>Change Password</span>
                </li>
                <li class="ant-menu-item" onclick="window.location.href='logout.php'">
                    <span class="ant-menu-item-icon"><i class="fas fa-sign-out-alt"></i></span>
                    <span>Logout</span>
                </li>
            </ul>
        </aside>

        <main class="main-content">
            <!-- Pending page -->
            <div class="page active" id="page-pending">
                <div class="page-header"><h1 class="page-title">Pending Applications</h1></div>
                <div class="table-container">
                    <table class="data-table">
                        <thead><tr><th>Applicant</th><th>Program</th><th>GPA</th><th>TOEFL/IELTS</th><th>Submitted</th><th style="width:200px">Actions</th></tr></thead>
                        <tbody id="pendingTableBody">
                            <tr><td colspan="6"><div class="empty-state"><i class="fas fa-inbox"></i><p>Loading...</p></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Approved page -->
            <div class="page" id="page-approved">
                <div class="page-header"><h1 class="page-title">Approved Applications</h1></div>
                <div class="table-container">
                    <table class="data-table">
                        <thead><tr><th>Applicant</th><th>Program</th><th>GPA</th><th>TOEFL/IELTS</th><th>Reviewed</th><th style="width:150px">Actions</th></tr></thead>
                        <tbody id="approvedTableBody">
                            <tr><td colspan="6"><div class="empty-state"><i class="fas fa-inbox"></i><p>Loading...</p></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Rejected page -->
            <div class="page" id="page-rejected">
                <div class="page-header"><h1 class="page-title">Rejected Applications</h1></div>
                <div class="table-container">
                    <table class="data-table">
                        <thead><tr><th>Applicant</th><th>Program</th><th>GPA</th><th>TOEFL/IELTS</th><th>Reviewed</th><th style="width:150px">Actions</th></tr></thead>
                        <tbody id="rejectedTableBody">
                            <tr><td colspan="6"><div class="empty-state"><i class="fas fa-inbox"></i><p>Loading...</p></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Program List page -->
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

            <!-- Add Program page -->
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
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- View Application Details Modal -->
    <div class="modal-overlay" id="viewModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Application Details</h3>
                <button class="modal-close" onclick="closeModal('viewModal')">&times;</button>
            </div>
            <div class="modal-body" id="applicationDetail"></div>
            <div class="modal-footer" id="viewModalFooter"></div>
        </div>
    </div>

    <!-- Confirm Approval Modal -->
    <div class="modal-overlay confirm-modal" id="approveModal">
        <div class="modal">
            <div class="modal-body">
                <div class="confirm-content">
                    <div class="confirm-icon success"><i class="fas fa-check-circle"></i></div>
                    <div class="confirm-message">
                        <h4>Confirm Approve this Application?</h4>
                        <p>An acceptance letter will be sent to the applicant.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" onclick="closeModal('approveModal')">Cancel</button>
                <button class="btn btn-primary" onclick="confirmApprove()">Confirm Approve</button>
            </div>
        </div>
    </div>

    <!-- Confirm Reject Modal -->
    <div class="modal-overlay confirm-modal" id="rejectModal">
        <div class="modal">
            <div class="modal-body">
                <div class="confirm-content">
                    <div class="confirm-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <div class="confirm-message">
                        <h4>Confirm Reject this Application?</h4>
                        <p>A rejection letter will be sent to the applicant.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" onclick="closeModal('rejectModal')">Cancel</button>
                <button class="btn btn-danger" onclick="confirmReject()">Confirm Reject</button>
            </div>
        </div>
    </div>

    <!-- Confirm Revoke Modal -->
    <div class="modal-overlay confirm-modal" id="revokeModal">
        <div class="modal">
            <div class="modal-body">
                <div class="confirm-content">
                    <div class="confirm-icon success"><i class="fas fa-check-circle"></i></div>
                    <div class="confirm-message">
                        <h4>Confirm Revoke this Application?</h4>
                        <p>This application will be moved back to the Pending list.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" onclick="closeModal('revokeModal')">Cancel</button>
                <button class="btn btn-primary" onclick="confirmRevoke()">Confirm Revoke</button>
            </div>
        </div>
    </div>

    <div class="toast" id="toast"><i class="fas fa-check-circle"></i><span id="toastMessage"></span></div>

    <script src="JS/officer.js"></script>
</body>
</html>

