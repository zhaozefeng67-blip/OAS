// API base path
const API_BASE = 'api/';

// Data arrays
let universities = [];
let programs = [];
let officers = [];
let applications = [];

let deleteId = null, deleteType = '';
let logoData = { addUni: '', editUni: '' };

function getRankingClass(r) { return r <= 10 ? 'rank-top10' : r <= 50 ? 'rank-top50' : r <= 100 ? 'rank-top100' : 'rank-other'; }

// Badge functionality removed for consistency
// function updateApplicationBadge() {
//     const badge = document.getElementById('applicationBadge');
//     if (applications.length > 0) {
//         badge.textContent = applications.length;
//         badge.style.display = 'inline-flex';
//     } else {
//         badge.style.display = 'none';
//     }
// }

async function loadUniversities() {
    try {
        const result = await apiRequest('get_universities.php');
        universities = result.data; 
        renderUniversityTable();
    } catch (error) {
        console.error('Failed to load universities:', error);
    }
}

async function loadPrograms() {
    try {
        const result = await apiRequest('get_programs.php');
        programs = result.data; 
        renderProgramTable();
    } catch (error) {
        console.error('Failed to load programs:', error);
    }
}

async function loadOfficers() {
    try {
        const result = await apiRequest('get_officers.php');
        officers = result.data; 
        renderOfficerTable();
    } catch (error) {
        console.error('Failed to load officers:', error);
    }
}

async function loadApplications() {
    try {
        const result = await apiRequest('get_officer_applications.php');
        applications = result.data; 
        // updateApplicationBadge(); // Removed for consistency
        renderApplicationTable();
    } catch (error) {
        console.error('Failed to load applications:', error);
    }
}
function renderUniversityTable() {
    const tbody = document.getElementById('universityTableBody');
    if (universities.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5"><div class="empty-state"><i class="fas fa-university"></i><p>no data</p></div></td></tr>`;
        return;
    }
    
    tbody.innerHTML = universities.map(u => {
        const websiteDisplay = u.website ? u.website.replace(/^https?:\/\//, '') : '-';
        const websiteLink = u.website ? `<a href="${u.website}" target="_blank" style="color:#1677ff">${websiteDisplay}</a>` : websiteDisplay;
        const cityDisplay = u.city ? `<span class="tag tag-green">${u.city}</span>` : '';
        const uniId = typeof u.id === 'string' ? `'${u.id}'` : u.id;
        return `
        <tr>
            <td><div class="university-cell">${u.logo ? `<img class="table-logo" src="${u.logo}">` : ''}<span class="university-name">${u.name}</span></div></td>
            <td><span class="tag tag-blue">${u.country || '-'}</span>${cityDisplay}</td>
            <td><span class="ranking-badge ${getRankingClass(u.ranking)}">#${u.ranking || '-'}</span></td>
            <td>${websiteLink}</td>
            <td><div class="action-btns"><button class="action-btn btn-edit" onclick="editUniversity(${uniId})" type="button"><i class="fas fa-edit"></i> Edit</button><button class="action-btn btn-delete" onclick="deleteUniversity(${uniId})" type="button"><i class="fas fa-trash"></i> Delete</button></div></td>
        </tr>`;
    }).join('');
}

function renderProgramTable() {
    const tbody = document.getElementById('programTableBody');
    if (programs.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6"><div class="empty-state"><i class="fas fa-graduation-cap"></i><p>No programs yet</p></div></td></tr>`;
        return;
    }
    tbody.innerHTML = programs.map(p => {
        // Use data from API, if no associated data found, use the name returned by API
        const uniName = p.universityName || (universities.find(u => u.id === p.universityId)?.name) || 'N/A';
        // Ensure IDs are passed correctly - use the actual ID value, not a string representation
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

function renderOfficerTable() {
    const tbody = document.getElementById('officerTableBody');
    if (officers.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6"><div class="empty-state"><i class="fas fa-users"></i><p>No admission officers yet</p></div></td></tr>`;
        return;
    }
    tbody.innerHTML = officers.map(o => {
        // Use data from API, if no associated data found, use the name returned by API
        const uniName = o.universityName || (universities.find(u => u.id === o.universityId)?.name) || 'N/A';
        const officerId = o.id || 0;
        const universityId = o.universityId || 0;
        console.log('Rendering officer:', { id: officerId, universityId: universityId, name: o.name });
        return `<tr>
            <td><span class="university-name">${o.name}</span></td>
            <td>${o.email}</td>
            <td>${uniName}</td>
            <td><span class="tag tag-blue">${o.programCount || 0} Programs</span></td>
            <td><span class="tag tag-green">${o.applicationCount || 0} Applications</span></td>
            <td><div class="action-btns"><button class="action-btn btn-delete" onclick="deleteOfficer(${officerId}, ${universityId})"><i class="fas fa-trash"></i> Delete</button></div></td>
        </tr>`;
    }).join('');
}

function renderApplicationTable() {
    const tbody = document.getElementById('applicationTableBody');
    if (applications.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4"><div class="empty-state"><i class="fas fa-inbox"></i><p>No pending applications</p></div></td></tr>`;
        return;
    }
    tbody.innerHTML = applications.map(a => {
        // Use data from API, if no associated data found, use the name returned by API
        const uniName = a.universityName || (universities.find(u => u.id === a.universityId)?.name) || 'N/A';
        return `<tr>
            <td><span class="university-name">${a.name}</span></td>
            <td>${a.email}</td>
            <td>${uniName}</td>
            <td><div class="action-btns">
                <button class="action-btn btn-accept" onclick="approveOfficerApplication(${a.id}, ${a.universityId})"><i class="fas fa-check"></i> Approve</button>
                <button class="action-btn btn-reject" onclick="rejectOfficerApplication(${a.id})"><i class="fas fa-times"></i> Reject</button>
            </div></td>
        </tr>`;
    }).join('');
}

function populateUniversitySelect(selectId, selectedId = null) {
    const sel = document.getElementById(selectId);
    sel.innerHTML = '<option value="">Select University</option>' + universities.map(u => `<option value="${u.id}" ${u.id === selectedId ? 'selected' : ''}>${u.name}</option>`).join('');
}

function navigateTo(pageId, menuItem) {
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    document.getElementById('page-' + pageId).classList.add('active');
    document.querySelectorAll('.ant-menu-item').forEach(i => i.classList.remove('active'));
    if (menuItem) menuItem.classList.add('active');
    if (pageId === 'addUniversity') { document.getElementById('addUniForm').reset(); removeLogo('addUni'); }
    if (pageId === 'addProgram') { document.getElementById('addProgForm').reset(); populateUniversitySelect('addProgUniversity'); }
    if (pageId === 'universityList') { loadUniversities(); } // Reload to ensure fresh data
    if (pageId === 'programList') { loadPrograms(); } // Reload to ensure fresh data
    if (pageId === 'officerList') { loadOfficers(); } // Reload to ensure fresh data
    if (pageId === 'officerApplications') { loadApplications(); }
}

function handleFileSelect(e, type) {
    const file = e.target.files[0];
    if (!file) return;
    if (file.size > 2*1024*1024) { showToast('File must be < 2MB', 'error'); return; }
    const reader = new FileReader();
    reader.onload = ev => {
        logoData[type] = ev.target.result;
        document.getElementById(type + 'PreviewImg').src = logoData[type];
        document.getElementById(type + 'UploadContent').classList.add('hidden');
        document.getElementById(type + 'UploadPreview').classList.add('show');
    };
    reader.readAsDataURL(file);
}

function removeLogo(type) {
    logoData[type] = '';
    document.getElementById(type + 'LogoInput').value = '';
    document.getElementById(type + 'UploadContent').classList.remove('hidden');
    document.getElementById(type + 'UploadPreview').classList.remove('show');
}

['addUniUploadArea', 'editUniUploadArea'].forEach(id => {
    const area = document.getElementById(id);
    if (!area) return;
    area.addEventListener('dragover', e => { e.preventDefault(); area.classList.add('dragover'); });
    area.addEventListener('dragleave', e => { e.preventDefault(); area.classList.remove('dragover'); });
    area.addEventListener('drop', e => {
        e.preventDefault(); area.classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (file?.type.startsWith('image/')) {
            const type = id.replace('UploadArea', '');
            const input = document.getElementById(type + 'LogoInput');
            const dt = new DataTransfer(); dt.items.add(file); input.files = dt.files;
            handleFileSelect({ target: input }, type);
        }
    });
});

async function addUniversity() {
    const name = document.getElementById('addUniName').value.trim();
    const country = document.getElementById('addUniCountry').value.trim();
    if (!name || !country) { showToast('Please fill required fields', 'error'); return; }
    
    const data = {
        name: name,
        country: country,
        city: document.getElementById('addUniCity').value.trim(),
        ranking: parseInt(document.getElementById('addUniRanking').value) || 0,
        website: document.getElementById('addUniWebsite').value.trim(),
        description: document.getElementById('addUniDescription').value.trim(),
        logo: logoData.addUni
    };
    
    try {
        const result = await apiRequest('add_university.php', 'POST', data);
        showToast('University added', 'success');
        await loadUniversities(); // Reload data
        navigateTo('universityList', document.querySelector('[data-page=universityList]'));
    } catch (error) {
        console.error('Failed to add university:', error);
    }
}

function editUniversity(id) {
    try {
        // Convert id to number if it's a string representation of a number
        const numId = typeof id === 'string' && !isNaN(id) ? parseInt(id) : id;
        const u = universities.find(x => x.id == numId || x.id === numId);
        if (!u) {
            console.error('University not found with id:', id);
            return;
        }
        document.getElementById('editUniId').value = u.id;
        document.getElementById('editUniName').value = u.name || '';
        document.getElementById('editUniCountry').value = u.country || '';
        document.getElementById('editUniCity').value = u.city || '';
        document.getElementById('editUniRanking').value = u.ranking || '';
        document.getElementById('editUniWebsite').value = u.website || '';
        document.getElementById('editUniDescription').value = u.description || '';
        if (u.logo) { 
            logoData.editUni = u.logo; 
            document.getElementById('editUniPreviewImg').src = u.logo; 
            document.getElementById('editUniUploadContent').classList.add('hidden'); 
            document.getElementById('editUniUploadPreview').classList.add('show'); 
        } else { 
            removeLogo('editUni'); 
        }
        openModal('editUniModal');
    } catch (error) {
        console.error('Error in editUniversity:', error);
    }
}

async function saveUniversity() {
    const id = parseInt(document.getElementById('editUniId').value);
    const name = document.getElementById('editUniName').value.trim();
    const country = document.getElementById('editUniCountry').value.trim();
    if (!name || !country) { showToast('Please fill required fields', 'error'); return; }
    
    const data = {
        id: id,
        name: name,
        country: country,
        city: document.getElementById('editUniCity').value.trim(),
        ranking: parseInt(document.getElementById('editUniRanking').value) || 0,
        website: document.getElementById('editUniWebsite').value.trim(),
        description: document.getElementById('editUniDescription').value.trim(),
        logo: logoData.editUni
    };
    
    try {
        const result = await apiRequest('update_university.php', 'POST', data);
        showToast('Saved', 'success');
        await loadUniversities(); // Reload data
        closeModal('editUniModal');
    } catch (error) {
        console.error('Failed to save university:', error);
    }
}

function deleteUniversity(id) { deleteId = id; deleteType = 'university'; document.getElementById('deleteTitle').textContent = 'Delete this university?'; document.getElementById('deleteText').textContent = 'All related programs and officers will also be deleted.'; openModal('deleteModal'); }

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
        await loadPrograms(); // Reload data
        navigateTo('programList', document.querySelector('[data-page=programList]'));
    } catch (error) {
        console.error('Failed to add program:', error);
    }
}

function editProgram(pid, sid) {
    try {
        // Convert to numbers
        const numPid = typeof pid === 'string' && !isNaN(pid) ? parseInt(pid) : pid;
        const numSid = typeof sid === 'string' && !isNaN(sid) ? parseInt(sid) : sid;
        
        // Find program by both pid and sid (composite primary key)
        const p = programs.find(x => {
            const xPid = typeof x.id === 'string' && !isNaN(x.id) ? parseInt(x.id) : x.id;
            const xSid = typeof x.universityId === 'string' && !isNaN(x.universityId) ? parseInt(x.universityId) : x.universityId;
            return (xPid == numPid || xPid === numPid) && (xSid == numSid || xSid === numSid);
        });
        
        if (!p) {
            console.error('Program not found with pid:', pid, 'sid:', sid);
            console.error('Available programs:', programs.map(prog => ({ pid: prog.id, sid: prog.universityId, name: prog.name })));
            showToast('Program not found. Please refresh the page.', 'error');
            return;
        }
        
        console.log('Editing program:', { pid: p.id, sid: p.universityId, name: p.name });
        
        // Set hidden fields - ensure they are set as numbers
        document.getElementById('editProgId').value = parseInt(p.id);
        document.getElementById('editProgUniversityId').value = parseInt(p.universityId);
        
        // Display university name (read-only)
        const uniName = p.universityName || (universities.find(u => u.id === p.universityId)?.name) || 'Unknown University';
        document.getElementById('editProgUniversityDisplay').value = uniName;
        
        // Fill form fields
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

async function saveProgram() {
    try {
        const pidValue = document.getElementById('editProgId').value;
        const sidValue = document.getElementById('editProgUniversityId').value;
        
        // Validate that IDs are present and valid
        if (!pidValue || !sidValue) {
            showToast('Program information is missing. Please refresh the page.', 'error');
            return;
        }
        
        // Convert to numbers and validate
        const pid = parseInt(pidValue);
        const sid = parseInt(sidValue);
        
        if (isNaN(pid) || isNaN(sid)) {
            showToast('Invalid program ID. Please refresh the page.', 'error');
            return;
        }
        
        // Find program by both pid and sid (composite primary key)
        const originalProgram = programs.find(p => {
            const pPid = parseInt(p.id);
            const pSid = parseInt(p.universityId);
            return !isNaN(pPid) && !isNaN(pSid) && pPid === pid && pSid === sid;
        });
        
        if (!originalProgram) {
            console.error('Program not found with pid:', pid, 'sid:', sid);
            console.error('Available programs:', programs.map(p => ({ pid: p.id, sid: p.universityId, name: p.name })));
            showToast('Program not found. Please refresh the page.', 'error');
            return;
        }
        
        // University cannot be changed (program belongs to specific university)
        const universityId = sid; // Use the original sid, cannot be changed
        const name = document.getElementById('editProgName').value.trim();
        if (!name) { 
            showToast('Please fill required fields', 'error'); 
            return; 
        }
    
        const data = {
            id: pid, // pid
            universityId: universityId, // sid (cannot be changed)
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
        await loadPrograms(); // Reload data
        closeModal('editProgModal');
    } catch (error) {
        console.error('Failed to save program:', error);
        showToast('Failed to save program: ' + (error.message || 'Unknown error'), 'error');
    }
}

let deleteUniversityId = null; // Store the universityId of the program to be deleted

function deleteProgram(id, universityId) { 
    try {
        // Convert to proper types
        const numId = typeof id === 'string' && !isNaN(id) ? parseInt(id) : id;
        const numUniId = typeof universityId === 'string' && !isNaN(universityId) ? parseInt(universityId) : universityId;
        
        // Verify program exists
        const program = programs.find(p => (p.id == numId || p.id === numId) && (p.universityId == numUniId || p.universityId === numUniId));
        if (!program) {
            console.error('Program not found for deletion:', { id: numId, universityId: numUniId });
            showToast('Program not found', 'error');
            return;
        }
        
        deleteId = numId; 
        deleteUniversityId = numUniId;
        deleteType = 'program'; 
        document.getElementById('deleteTitle').textContent = 'Delete this program?'; 
        document.getElementById('deleteText').textContent = `Are you sure you want to delete "${program.name}"? This action cannot be undone.`; 
        openModal('deleteModal');
    } catch (error) {
        console.error('Error in deleteProgram:', error);
        showToast('Error preparing deletion', 'error');
    }
}

function deleteOfficer(id, universityId) { 
    console.log('deleteOfficer called with id:', id, 'type:', typeof id, 'universityId:', universityId, 'type:', typeof universityId);
    if (!id || !universityId || universityId === 0) {
        console.error('Invalid parameters:', { id, universityId });
        showToast('Invalid officer data', 'error');
        return;
    }
    deleteId = parseInt(id); 
    deleteUniversityId = parseInt(universityId);
    deleteType = 'officer'; 
    console.log('Set deleteId:', deleteId, 'deleteUniversityId:', deleteUniversityId);
    document.getElementById('deleteTitle').textContent = 'Delete this admission officer?'; 
    document.getElementById('deleteText').textContent = 'This action cannot be undone.'; 
    openModal('deleteModal'); 
}


// Approve student application
async function approveStudentApplication(studentId, sid, pid) {
    try {
        await apiRequest('update_student_application_status.php', 'POST', {
            studentId: studentId,
            sid: sid,
            pid: pid,
            status: 'Approved'
        });
        await loadApplications(); // Reload application list
        showToast('Application approved', 'success');
    } catch (error) {
        console.error('Failed to approve application:', error);
    }
}

// Reject student application
async function rejectStudentApplication(studentId, sid, pid) {
    try {
        await apiRequest('update_student_application_status.php', 'POST', {
            studentId: studentId,
            sid: sid,
            pid: pid,
            status: 'Rejected'
        });
        await loadApplications(); // Reload application list
        showToast('Application rejected', 'success');
    } catch (error) {
        console.error('Failed to reject application:', error);
    }
}

// Approve officer application
async function approveOfficerApplication(applicationId, universityId) {
    // If universityId is 0 or invalid, show error
    if (!universityId || universityId === 0) {
        showToast('University information is missing. Please contact the administrator.', 'error');
        return;
    }
    
    try {
        await apiRequest('accept_officer_application.php', 'POST', {
            id: applicationId,
            universityId: universityId
        });
        await loadApplications(); // Reload application list
        await loadOfficers(); // Reload officer list
        showToast('Officer application approved', 'success');
    } catch (error) {
        console.error('Failed to approve officer application:', error);
        showToast(error.message || 'Failed to approve application', 'error');
    }
}

// Reject officer application
async function rejectOfficerApplication(applicationId) {
    try {
        await apiRequest('reject_officer_application.php', 'POST', {
            id: applicationId
        });
        await loadApplications(); // Reload application list
        showToast('Officer application rejected', 'success');
    } catch (error) {
        console.error('Failed to reject officer application:', error);
        showToast('Failed to reject application', 'error');
    }
}


// Keep the original rejectApplication function for rejecting officer applications
function rejectApplication(id) {
    deleteId = id;
    deleteType = 'application';
    document.getElementById('deleteTitle').textContent = 'Reject this application?';
    document.getElementById('deleteText').textContent = 'The applicant will be notified of the rejection.';
    openModal('deleteModal');
}

async function confirmDelete() {
    if (deleteType === 'university') { 
        try {
            await apiRequest('delete_university.php', 'POST', { id: deleteId });
            await loadUniversities();
            await loadPrograms();
            await loadOfficers();
            await loadApplications();
            showToast('Deleted', 'success');
        } catch (error) {
            console.error('Failed to delete university:', error);
            return;
        }
    }
    else if (deleteType === 'program') { 
        if (!deleteId || deleteUniversityId === null || deleteUniversityId === undefined) {
            console.error('Missing program data:', { deleteId, deleteUniversityId, deleteType });
            showToast('Program information missing', 'error');
            closeModal('deleteModal');
            deleteId = null; 
            deleteUniversityId = null;
            deleteType = '';
            return;
        }
        try {
            // Ensure IDs are integers
            const requestData = {
                id: parseInt(deleteId),
                universityId: parseInt(deleteUniversityId)
            };
            console.log('Deleting program with data:', requestData);
            await apiRequest('delete_program.php', 'POST', requestData);
            await loadPrograms();
            await loadOfficers();
            await loadApplications();
            showToast('Program deleted successfully', 'success');
        } catch (error) {
            console.error('Failed to delete program:', error);
            showToast('Failed to delete program: ' + (error.message || 'Unknown error'), 'error');
            return;
        }
    }
    else if (deleteType === 'officer') { 
        console.log('confirmDelete for officer - deleteId:', deleteId, 'type:', typeof deleteId, 'deleteUniversityId:', deleteUniversityId, 'type:', typeof deleteUniversityId);
        if (!deleteId || deleteUniversityId === null || deleteUniversityId === undefined || deleteUniversityId === 0) {
            console.error('Missing officer data:', { deleteId, deleteUniversityId, deleteType });
            showToast('Officer information missing', 'error');
            closeModal('deleteModal');
            deleteId = null; 
            deleteUniversityId = null;
            deleteType = '';
            return;
        }
        try {
            const requestData = { 
                id: parseInt(deleteId), 
                universityId: parseInt(deleteUniversityId) 
            };
            console.log('Sending delete request:', requestData);
            const response = await apiRequest('delete_officer.php', 'POST', requestData);
            console.log('Delete response:', response);
            await loadOfficers();
            showToast('Deleted', 'success');
        } catch (error) {
            console.error('Failed to delete officer:', error);
            return;
        }
    }
    else if (deleteType === 'application') { 
        try {
            await apiRequest('reject_officer_application.php', 'POST', { id: deleteId });
            await loadApplications(); // Reload application list
            showToast('Application rejected', 'success');
        } catch (error) {
            console.error('Failed to reject application:', error);
            return;
        }
        closeModal('deleteModal');
        deleteId = null; 
        deleteType = '';
        return;
    }
    closeModal('deleteModal');
    deleteId = null; 
    deleteUniversityId = null;
    deleteType = '';
}

function openModal(id) { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }
function showToast(msg, type) { const t = document.getElementById('toast'); t.className = 'toast show ' + type; t.querySelector('i').className = type === 'success' ? 'fas fa-check-circle' : 'fas fa-times-circle'; document.getElementById('toastMessage').textContent = msg; setTimeout(() => t.classList.remove('show'), 2000); }
function toggleSubmenu(el) { el.parentElement.classList.toggle('open'); }
document.querySelectorAll('.modal-overlay').forEach(o => o.addEventListener('click', e => { if (e.target === o) o.classList.remove('active'); }));

// Load all data from database on page load
(async function init() {
    await loadUniversities();
    await loadPrograms();
    await loadOfficers();
    await loadApplications();
})();

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

async function apiRequest(endpoint, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin' // Include session cookie
    };
    
    if (data && (method === 'POST' || method === 'PUT')) {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(API_BASE + endpoint, options);
        const rawText = await response.text();

        // #region agent log
        fetch('http://127.0.0.1:7243/ingest/ddbad96e-f670-4c9d-a2a5-3579be78268f',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({sessionId:'debug-session',runId:'pre-fix',hypothesisId:'A',location:'JS/admin.js:apiRequest',message:'api response',data:{endpoint,method,status:response.status,ok:response.ok,bodySnippet:rawText.slice(0,300)},timestamp:Date.now()})}).catch(()=>{});
        // #endregion

        const result = JSON.parse(rawText);
        if (!result.success) {
            throw new Error(result.message || 'Request failed');
        }
        return result;
    } catch (error) {
        // #region agent log
        fetch('http://127.0.0.1:7243/ingest/ddbad96e-f670-4c9d-a2a5-3579be78268f',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({sessionId:'debug-session',runId:'pre-fix',hypothesisId:'B',location:'JS/admin.js:apiRequest',message:'api error',data:{endpoint,method,error:error.message},timestamp:Date.now()})}).catch(()=>{});
        // #endregion

        console.error('API Error:', error);
        showToast(error.message, 'error');
        throw error;
    }
}

