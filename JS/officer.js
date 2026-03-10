// API base path
const API_BASE = 'api/';

// Data arrays
let applications = [];
let programs = [];
let universities = [];
let currentAppId = null;
let currentAppData = null;

// API request function
async function apiRequest(endpoint, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    };
    
    if (data && (method === 'POST' || method === 'PUT')) {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(API_BASE + endpoint, options);
        const rawText = await response.text();

        // #region agent log
        fetch('http://127.0.0.1:7243/ingest/ddbad96e-f670-4c9d-a2a5-3579be78268f',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({sessionId:'debug-session',runId:'pre-fix',hypothesisId:'A',location:'JS/officer.js:apiRequest',message:'api response',data:{endpoint,method,status:response.status,ok:response.ok,bodySnippet:rawText.slice(0,300)},timestamp:Date.now()})}).catch(()=>{});
        // #endregion

        const result = JSON.parse(rawText);
        if (!result.success) {
            throw new Error(result.message || 'Request failed');
        }
        return result;
    } catch (error) {
        // #region agent log
        fetch('http://127.0.0.1:7243/ingest/ddbad96e-f670-4c9d-a2a5-3579be78268f',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({sessionId:'debug-session',runId:'pre-fix',hypothesisId:'B',location:'JS/officer.js:apiRequest',message:'api error',data:{endpoint,method,error:error.message},timestamp:Date.now()})}).catch(()=>{});
        // #endregion

        console.error('API Error:', error);
        showToast(error.message, 'error');
        throw error;
    }
}

// Load application data
async function loadApplications(status = 'all') {
    try {
        const result = await apiRequest(`get_operator_applications.php?status=${status}`);
        return result.data || [];
    } catch (error) {
        console.error('Failed to load applications:', error);
        return [];
    }
}

// Load program data
async function loadPrograms() {
    try {
        const result = await apiRequest('get_programs.php');
        programs = result.data || [];
        renderProgramTable();
    } catch (error) {
        console.error('Failed to load programs:', error);
    }
}

// Load universities for the officer
async function loadUniversities() {
    try {
        const result = await apiRequest('get_operator_universities.php');
        universities = result.data || [];
        populateUniversitySelect('addProgUniversity');
    } catch (error) {
        console.error('Failed to load universities:', error);
    }
}

// Render program table
function renderProgramTable() {
    const tbody = document.getElementById('programTableBody');
    if (programs.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6"><div class="empty-state"><i class="fas fa-graduation-cap"></i><p>No programs yet</p></div></td></tr>`;
        return;
    }
    tbody.innerHTML = programs.map(p => {
        const uniName = p.universityName || 'N/A';
        const progId = p.id;
        const uniId = p.universityId;
        return `<tr>
            <td><span class="university-name">${p.name}</span></td>
            <td>${uniName}</td>
            <td><span class="tag tag-purple">${p.degree}</span></td>
            <td>${p.duration || '-'}</td>
            <td><span class="tag tag-orange">${p.deadline || '-'}</span></td>
            <td><div class="action-btns"><button class="action-btn btn-edit" onclick="editProgram(${progId}, ${uniId})" type="button"><i class="fas fa-edit"></i> Edit</button><button class="action-btn btn-delete" onclick="deleteProgram(${progId}, ${uniId})" type="button"><i class="fas fa-trash"></i> Delete</button></div></td>
        </tr>`;
    }).join('');
}

function populateUniversitySelect(selectId, selectedId = null) {
    const sel = document.getElementById(selectId);
    sel.innerHTML = '<option value="">Select University</option>' + universities.map(u => `<option value="${u.id}" ${u.id === selectedId ? 'selected' : ''}>${u.name}</option>`).join('');
}

// Add program
async function addProgram() {
    const universityId = parseInt(document.getElementById('addProgUniversity').value);
    const name = document.getElementById('addProgName').value.trim();
    if (!universityId || !name) { showToast('Please fill required fields', 'error'); return; }
    
    const data = {
        universityId: universityId,
        name: name,
        degree: document.getElementById('addProgDegree').value,
        duration: document.getElementById('addProgDuration').value.trim(),
        deadline: document.getElementById('addProgDeadline').value,
        minGPA: parseFloat(document.getElementById('addProgGPA').value) || 0,
        languageRequirement: document.getElementById('addProgLanguageReq').value.trim(),
        category: document.getElementById('addProgCategory').value.trim()
    };
    
    try {
        const result = await apiRequest('add_program.php', 'POST', data);
        showToast('Program added', 'success');
        await loadPrograms();
        navigateTo('programList', document.querySelector('[data-page=programList]'));
    } catch (error) {
        console.error('Failed to add program:', error);
    }
}

// Edit program
function editProgram(pid, sid) {
    try {
        const numPid = typeof pid === 'string' && !isNaN(pid) ? parseInt(pid) : pid;
        const numSid = typeof sid === 'string' && !isNaN(sid) ? parseInt(sid) : sid;
        
        const p = programs.find(x => {
            const xPid = typeof x.id === 'string' && !isNaN(x.id) ? parseInt(x.id) : x.id;
            const xSid = typeof x.universityId === 'string' && !isNaN(x.universityId) ? parseInt(x.universityId) : x.universityId;
            return (xPid == numPid || xPid === numPid) && (xSid == numSid || xSid === numSid);
        });
        
        if (!p) {
            showToast('Program not found. Please refresh the page.', 'error');
            return;
        }
        
        document.getElementById('editProgId').value = p.id;
        document.getElementById('editProgUniversityId').value = p.universityId;
        
        const uniName = p.universityName || 'Unknown University';
        document.getElementById('editProgUniversityDisplay').value = uniName;
        
        document.getElementById('editProgName').value = p.name || '';
        document.getElementById('editProgDegree').value = p.degree || '';
        document.getElementById('editProgDuration').value = p.duration || '';
        document.getElementById('editProgDeadline').value = p.deadline || '';
        document.getElementById('editProgGPA').value = p.minGPA || '';
        document.getElementById('editProgLanguageReq').value = p.languageRequirement || '';
        document.getElementById('editProgCategory').value = p.category || '';
        openModal('editProgModal');
    } catch (error) {
        console.error('Error in editProgram:', error);
        showToast('Error opening edit form: ' + error.message, 'error');
    }
}

// Save program
async function saveProgram() {
    try {
        const pidValue = document.getElementById('editProgId').value;
        const sidValue = document.getElementById('editProgUniversityId').value;
        const pid = typeof pidValue === 'string' && !isNaN(pidValue) ? parseInt(pidValue) : pidValue;
        const sid = typeof sidValue === 'string' && !isNaN(sidValue) ? parseInt(sidValue) : sidValue;
        
        const name = document.getElementById('editProgName').value.trim();
        if (!name) { 
            showToast('Please fill required fields', 'error'); 
            return; 
        }
    
        const data = {
            id: pid,
            universityId: sid,
            name: name,
            degree: document.getElementById('editProgDegree').value,
            duration: document.getElementById('editProgDuration').value.trim(),
            deadline: document.getElementById('editProgDeadline').value,
            minGPA: parseFloat(document.getElementById('editProgGPA').value) || 0,
            languageRequirement: document.getElementById('editProgLanguageReq').value.trim(),
            category: document.getElementById('editProgCategory').value.trim()
        };
        
        const result = await apiRequest('update_program.php', 'POST', data);
        showToast('Saved', 'success');
        await loadPrograms();
        closeModal('editProgModal');
    } catch (error) {
        console.error('Failed to save program:', error);
    }
}

// Delete program
async function deleteProgram(pid, sid) {
    if (!confirm('Are you sure you want to delete this program? This action cannot be undone.')) {
        return;
    }
    
    try {
        await apiRequest('delete_program.php', 'POST', {
            id: pid,
            universityId: sid
        });
        showToast('Program deleted', 'success');
        await loadPrograms();
    } catch (error) {
        console.error('Failed to delete program:', error);
    }
}


// Render pending applications table
async function renderPendingTable() {
    const pending = await loadApplications('Pending');
    const tbody = document.getElementById('pendingTableBody');
    if (pending.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><i class="fas fa-inbox"></i><p>No pending applications</p></div></td></tr>';
        return;
    }
    tbody.innerHTML = pending.map(a => `
        <tr>
            <td><div class="applicant-cell"><span class="applicant-name">${a.name}</span><span class="applicant-email">${a.email}</span></div></td>
            <td>${a.program}</td>
            <td><span class="tag tag-blue">${a.gpa !== null ? a.gpa : 'N/A'}</span></td>
            <td>
                ${a.toefl ? `<span class="tag tag-green" style="margin-right: 4px;">TOEFL ${a.toefl}</span>` : ''}
                ${a.ielts ? `<span class="tag tag-purple">IELTS ${a.ielts}</span>` : ''}
                ${!a.toefl && !a.ielts ? 'N/A' : ''}
            </td>
            <td>${a.submitTime}</td>
            <td>
                <div class="action-btns">
                    <button class="action-btn btn-view" onclick="viewApplication(${a.id}, ${a.sid}, ${a.pid})">View</button>
                    <button class="action-btn btn-approve" onclick="approveApplication(${a.id}, ${a.sid}, ${a.pid})">Approve</button>
                    <button class="action-btn btn-reject" onclick="rejectApplication(${a.id}, ${a.sid}, ${a.pid})">Reject</button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Render approved applications table
async function renderApprovedTable() {
    const approved = await loadApplications('Approved');
    const tbody = document.getElementById('approvedTableBody');
    if (approved.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><i class="fas fa-inbox"></i><p>No approved applications</p></div></td></tr>';
        return;
    }
    tbody.innerHTML = approved.map(a => `
        <tr>
            <td><div class="applicant-cell"><span class="applicant-name">${a.name}</span><span class="applicant-email">${a.email}</span></div></td>
            <td>${a.program}</td>
            <td><span class="tag tag-blue">${a.gpa !== null ? a.gpa : 'N/A'}</span></td>
            <td>
                ${a.toefl ? `<span class="tag tag-green" style="margin-right: 4px;">TOEFL ${a.toefl}</span>` : ''}
                ${a.ielts ? `<span class="tag tag-purple">IELTS ${a.ielts}</span>` : ''}
                ${!a.toefl && !a.ielts ? 'N/A' : ''}
            </td>
            <td>${a.submitTime}</td>
            <td>
                <div class="action-btns">
                    <button class="action-btn btn-view" onclick="viewApplication(${a.id}, ${a.sid}, ${a.pid})">View</button>
                    <button class="action-btn btn-revoke" onclick="revokeApplication(${a.id}, ${a.sid}, ${a.pid})">Revoke</button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Render rejected applications table
async function renderRejectedTable() {
    const rejected = await loadApplications('Rejected');
    const tbody = document.getElementById('rejectedTableBody');
    if (rejected.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><i class="fas fa-inbox"></i><p>No rejected applications</p></div></td></tr>';
        return;
    }
    tbody.innerHTML = rejected.map(a => `
        <tr>
            <td><div class="applicant-cell"><span class="applicant-name">${a.name}</span><span class="applicant-email">${a.email}</span></div></td>
            <td>${a.program}</td>
            <td><span class="tag tag-blue">${a.gpa !== null ? a.gpa : 'N/A'}</span></td>
            <td>
                ${a.toefl ? `<span class="tag tag-green" style="margin-right: 4px;">TOEFL ${a.toefl}</span>` : ''}
                ${a.ielts ? `<span class="tag tag-purple">IELTS ${a.ielts}</span>` : ''}
                ${!a.toefl && !a.ielts ? 'N/A' : ''}
            </td>
            <td>${a.submitTime}</td>
            <td>
                <div class="action-btns">
                    <button class="action-btn btn-view" onclick="viewApplication(${a.id}, ${a.sid}, ${a.pid})">View</button>
                    <button class="action-btn btn-revoke" onclick="revokeApplication(${a.id}, ${a.sid}, ${a.pid})">Revoke</button>
                </div>
            </td>
        </tr>
    `).join('');
}

// View application details
async function viewApplication(studentId, sid, pid) {
    try {
        // Directly call API to get the student's detailed information
        const result = await apiRequest(`get_operator_applications.php?status=all`);
        const allApps = result.data || [];
        const app = allApps.find(a => a.id === studentId && a.sid === sid && a.pid === pid);
        if (!app) {
            showToast('Application not found', 'error');
            return;
        }
        
        currentAppId = studentId;
        currentAppData = { sid: sid, pid: pid };
        
        const statusTag = app.status === 'Pending' ? '<span class="tag status-pending">Pending</span>' :
                          app.status === 'Approved' ? '<span class="tag status-approved">Approved</span>' :
                          '<span class="tag status-rejected">Rejected</span>';
        
        // Load student files
        let filesHtml = '<div class="empty-state"><i class="fas fa-folder-open"></i><p>No documents uploaded</p></div>';
        try {
            const filesResponse = await fetch(`api/get_student_files.php?student_id=${studentId}`, {
                credentials: 'same-origin'
            });
            const filesResult = await filesResponse.json();
            if (filesResult.success && filesResult.data.length > 0) {
                filesHtml = filesResult.data.map(file => {
                    const fileSize = (file.size / 1024).toFixed(2) + ' KB';
                    const fileIcon = file.type === 'zip' ? 'fa-file-archive' : 'fa-file-pdf';
                    return `
                        <div class="file-item" style="display: flex; align-items: center; gap: 12px; padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 8px;">
                            <i class="fas ${fileIcon}" style="font-size: 24px; color: #1677ff;"></i>
                            <div style="flex: 1;">
                                <div style="font-weight: 500;">${file.filename}</div>
                                <div style="font-size: 12px; color: #999;">${fileSize}</div>
                            </div>
                            <button class="btn btn-primary" onclick="downloadFile(${file.fid})" style="padding: 6px 12px; font-size: 13px;">
                                <i class="fas fa-download"></i> Download
                            </button>
                        </div>
                    `;
                }).join('');
            }
        } catch (error) {
            console.error('Failed to load files:', error);
        }
        
        // Load student experiences (competitions and internships)
        let competitionsHtml = '<div class="empty-state"><i class="fas fa-trophy"></i><p>No competition records</p></div>';
        let internshipsHtml = '<div class="empty-state"><i class="fas fa-briefcase"></i><p>No internship records</p></div>';
        try {
            const expResponse = await fetch(`api/get_student_experiences.php?student_id=${studentId}`, {
                credentials: 'same-origin'
            });
            const expResult = await expResponse.json();
            if (expResult.success && expResult.data) {
                // Format competitions
                if (expResult.data.competitions && expResult.data.competitions.length > 0) {
                    competitionsHtml = expResult.data.competitions.map(comp => `
                        <div class="experience-item" style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 8px; background: #fafafa;">
                            <div style="font-weight: 600; color: #2c3e50; margin-bottom: 4px;">${comp.name}</div>
                            <div style="font-size: 13px; color: #666;">
                                <span style="color: #1677ff;">${comp.prize}</span>
                                ${comp.duration ? ` · <span style="color: #999;">${comp.duration}</span>` : ''}
                            </div>
                        </div>
                    `).join('');
                }
                
                // Format internships
                if (expResult.data.internships && expResult.data.internships.length > 0) {
                    internshipsHtml = expResult.data.internships.map(int => `
                        <div class="experience-item" style="padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 8px; background: #fafafa;">
                            <div style="font-weight: 600; color: #2c3e50; margin-bottom: 4px;">${int.position}</div>
                            <div style="font-size: 13px; color: #666;">
                                <span style="color: #1677ff;">${int.company}</span>
                                ${int.duration ? ` · <span style="color: #999;">${int.duration}</span>` : ''}
                            </div>
                        </div>
                    `).join('');
                }
            }
        } catch (error) {
            console.error('Failed to load experiences:', error);
        }
        
        document.getElementById('applicationDetail').innerHTML = `
            <div class="detail-section">
                <div class="detail-section-title">Application Information</div>
                <div class="detail-row"><div class="detail-label">Status</div><div class="detail-value">${statusTag}</div></div>
                <div class="detail-row"><div class="detail-label">Program</div><div class="detail-value">${app.program}</div></div>
                <div class="detail-row"><div class="detail-label">University</div><div class="detail-value">${app.university}</div></div>
                <div class="detail-row"><div class="detail-label">Submitted</div><div class="detail-value">${app.submitTime}</div></div>
            </div>
            <div class="detail-section">
                <div class="detail-section-title">Personal Information</div>
                <div class="detail-row"><div class="detail-label">Name</div><div class="detail-value">${app.name}</div></div>
                <div class="detail-row"><div class="detail-label">Email</div><div class="detail-value">${app.email}</div></div>
                <div class="detail-row"><div class="detail-label">Background</div><div class="detail-value">${app.background || 'N/A'}</div></div>
            </div>
            <div class="detail-section">
                <div class="detail-section-title">Academic Information</div>
                <div class="detail-row"><div class="detail-label">GPA</div><div class="detail-value"><span class="tag tag-blue">${app.gpa !== null ? app.gpa : 'N/A'}</span></div></div>
                <div class="detail-row">
                    <div class="detail-label">Language Scores</div>
                    <div class="detail-value">
                        ${app.toefl ? `<span class="tag tag-green" style="margin-right: 8px;">TOEFL ${app.toefl}</span>` : ''}
                        ${app.ielts ? `<span class="tag tag-purple">IELTS ${app.ielts}</span>` : ''}
                        ${!app.toefl && !app.ielts ? 'N/A' : ''}
                    </div>
                </div>
            </div>
            <div class="detail-section">
                <div class="detail-section-title">Competition Achievements</div>
                <div style="margin-top: 12px;">
                    ${competitionsHtml}
                </div>
            </div>
            <div class="detail-section">
                <div class="detail-section-title">Internship Experience</div>
                <div style="margin-top: 12px;">
                    ${internshipsHtml}
                </div>
            </div>
            <div class="detail-section">
                <div class="detail-section-title">Documents</div>
                <div style="margin-top: 12px;">
                    ${filesHtml}
                </div>
            </div>
        `;
        
        const footer = document.getElementById('viewModalFooter');
        const downloadAllBtn = `<button class="btn btn-success" onclick="downloadAllFiles(${studentId})" style="margin-right: auto;">
            <i class="fas fa-download"></i> Download All as ZIP
        </button>`;
        
        if (app.status === 'Pending') {
            footer.innerHTML = `
                ${downloadAllBtn}
                <button class="btn btn-default" onclick="closeModal('viewModal')">Close</button>
                <button class="btn btn-danger" onclick="closeModal('viewModal');rejectApplication(${studentId}, ${sid}, ${pid})">Reject</button>
                <button class="btn btn-primary" onclick="closeModal('viewModal');approveApplication(${studentId}, ${sid}, ${pid})">Approve</button>
            `;
        } else {
            footer.innerHTML = `
                ${downloadAllBtn}
                <button class="btn btn-default" onclick="closeModal('viewModal')">Close</button>
            `;
        }
        
        openModal('viewModal');
    } catch (error) {
        console.error('Failed to view application:', error);
    }
}

// Approve application
function approveApplication(studentId, sid, pid) {
    currentAppId = studentId;
    currentAppData = { sid: sid, pid: pid };
    openModal('approveModal');
}

async function confirmApprove() {
    if (!currentAppId || !currentAppData) return;
    
    try {
        await apiRequest('update_application_status.php', 'POST', {
            studentId: currentAppId,
            sid: currentAppData.sid,
            pid: currentAppData.pid,
            status: 'Approved'
        });
        showToast('Application approved', 'success');
        closeModal('approveModal');
        await renderAllTables();
    } catch (error) {
        console.error('Failed to approve application:', error);
    }
    currentAppId = null;
    currentAppData = null;
}

// Reject application
function rejectApplication(studentId, sid, pid) {
    currentAppId = studentId;
    currentAppData = { sid: sid, pid: pid };
    openModal('rejectModal');
}

async function confirmReject() {
    if (!currentAppId || !currentAppData) return;
    
    try {
        await apiRequest('update_application_status.php', 'POST', {
            studentId: currentAppId,
            sid: currentAppData.sid,
            pid: currentAppData.pid,
            status: 'Rejected'
        });
        showToast('Application rejected', 'success');
        closeModal('rejectModal');
        await renderAllTables();
    } catch (error) {
        console.error('Failed to reject application:', error);
    }
    currentAppId = null;
    currentAppData = null;
}

// Revoke application (move back to pending)
function revokeApplication(studentId, sid, pid) {
    currentAppId = studentId;
    currentAppData = { sid: sid, pid: pid };
    openModal('revokeModal');
}

async function confirmRevoke() {
    if (!currentAppId || !currentAppData) return;
    
    try {
        await apiRequest('update_application_status.php', 'POST', {
            studentId: currentAppId,
            sid: currentAppData.sid,
            pid: currentAppData.pid,
            status: 'Pending'
        });
        showToast('Application moved back to pending', 'success');
        closeModal('revokeModal');
        await renderAllTables();
    } catch (error) {
        console.error('Failed to revoke application:', error);
    }
    currentAppId = null;
    currentAppData = null;
}

// Render all tables
async function renderAllTables() {
    await renderPendingTable();
    await renderApprovedTable();
    await renderRejectedTable();
}

// Page navigation
function navigateTo(pageId, menuItem) {
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    document.getElementById('page-' + pageId).classList.add('active');
    document.querySelectorAll('.ant-menu-item').forEach(i => i.classList.remove('active'));
    if (menuItem) menuItem.classList.add('active');
    
    // Load corresponding data based on page
    if (pageId === 'pending') {
        renderPendingTable();
    } else if (pageId === 'approved') {
        renderApprovedTable();
    } else if (pageId === 'rejected') {
        renderRejectedTable();
    } else if (pageId === 'programList') {
        loadPrograms();
    } else if (pageId === 'addProgram') {
        document.getElementById('addProgForm').reset();
        loadUniversities();
    }
}

// Change password
async function changePassword() {
    const current = document.getElementById('currentPassword').value;
    const newPwd = document.getElementById('newPassword').value;
    const confirm = document.getElementById('confirmNewPassword').value;
    
    if (!current || !newPwd || !confirm) {
        showToast('Please fill all password fields', 'error');
        return;
    }
    
    if (newPwd !== confirm) {
        showToast('New passwords do not match', 'error');
        return;
    }
    
    if (newPwd.length < 6) {
        showToast('New password must be at least 6 characters', 'error');
        return;
    }
    
    try {
        await apiRequest('change_password.php', 'POST', {
            current: current,
            new: newPwd,
            confirm: confirm
        });
        showToast('Password changed successfully', 'success');
        document.getElementById('currentPassword').value = '';
        document.getElementById('newPassword').value = '';
        document.getElementById('confirmNewPassword').value = '';
    } catch (error) {
        console.error('Failed to change password:', error);
    }
}

// Logout
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'logout.php';
    }
}

// Utility functions
function openModal(id) { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }
// Download single file
function downloadFile(fid) {
    window.location.href = `api/download_student_file.php?fid=${fid}`;
}

// Download all files as ZIP
function downloadAllFiles(studentId) {
    window.location.href = `api/download_student_files_zip.php?student_id=${studentId}`;
}

function showToast(msg, type) {
    const t = document.getElementById('toast');
    t.className = 'toast show ' + type;
    t.querySelector('i').className = type === 'success' ? 'fas fa-check-circle' : 'fas fa-times-circle';
    document.getElementById('toastMessage').textContent = msg;
    setTimeout(() => t.classList.remove('show'), 2000);
}
function toggleSubmenu(el) { el.parentElement.classList.toggle('open'); }

document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if (e.target === o) o.classList.remove('active'); });
});

// Initialize
(async function init() {
    await renderAllTables();
    await loadPrograms();
})();

