function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    if (event && event.target) {
        event.target.classList.add('active');
    }

    document.querySelectorAll('.panel').forEach(panel => {
        panel.classList.remove('active');
    });

    document.getElementById(tab + 'Panel').classList.add('active');
    
    // Load files when documents tab is opened
    if (tab === 'documents') {
        loadUploadedFiles();
    }
}

// Basic Info
function editBasicInfo() {
    document.getElementById('basicViewMode').style.display = 'none';
    document.getElementById('basicEditMode').style.display = 'block';
}

function cancelBasicEdit() {
    document.getElementById('basicViewMode').style.display = 'block';
    document.getElementById('basicEditMode').style.display = 'none';
}

function saveBasicInfo() {
    document.getElementById('basicForm').submit();
}

// Education
function editEducation() {
    document.getElementById('educationViewMode').style.display = 'none';
    document.getElementById('educationEditMode').style.display = 'block';
}

function cancelEducationEdit() {
    document.getElementById('educationViewMode').style.display = 'block';
    document.getElementById('educationEditMode').style.display = 'none';
}

function saveEducation() {
    document.getElementById('educationForm').submit();
}

// TOEFL
function editToefl() {
    document.getElementById('toeflViewMode').style.display = 'none';
    document.getElementById('toeflEditMode').style.display = 'block';
}

function cancelToeflEdit() {
    document.getElementById('toeflViewMode').style.display = 'block';
    document.getElementById('toeflEditMode').style.display = 'none';
}

function saveToefl() {
    document.getElementById('toeflForm').submit();
}

// Update TOEFL total score when individual scores change
function updateToeflTotal() {
    const listening = parseFloat(document.getElementById('toefl-listening').value) || 0;
    const speaking = parseFloat(document.getElementById('toefl-speaking').value) || 0;
    const reading = parseFloat(document.getElementById('toefl-reading').value) || 0;
    const writing = parseFloat(document.getElementById('toefl-writing').value) || 0;
    const total = listening + speaking + reading + writing;
    const totalInput = document.getElementById('toefl-total');
    totalInput.value = total > 0 ? total : '';
}

// IELTS
function editIelts() {
    document.getElementById('ieltsViewMode').style.display = 'none';
    document.getElementById('ieltsEditMode').style.display = 'block';
}

function cancelIeltsEdit() {
    document.getElementById('ieltsViewMode').style.display = 'block';
    document.getElementById('ieltsEditMode').style.display = 'none';
}

function saveIelts() {
    document.getElementById('ieltsForm').submit();
}

// Update IELTS total score (average) when individual scores change
function updateIeltsTotal() {
    const listening = parseFloat(document.getElementById('ielts-listening').value) || 0;
    const speaking = parseFloat(document.getElementById('ielts-speaking').value) || 0;
    const reading = parseFloat(document.getElementById('ielts-reading').value) || 0;
    const writing = parseFloat(document.getElementById('ielts-writing').value) || 0;
    
    const totalInput = document.getElementById('ielts-total');
    
    if (listening > 0 || speaking > 0 || reading > 0 || writing > 0) {
        const sum = listening + speaking + reading + writing;
        const ave = sum / 4.0;
        const decimal = ave - Math.floor(ave);
        
        let rounded;
        if (decimal < 0.25) {
            rounded = Math.floor(ave);
        } else if (decimal < 0.75) {
            rounded = Math.floor(ave) + 0.5;
        } else {
            rounded = Math.ceil(ave);
        }
        
        totalInput.value = rounded;
    } else {
        totalInput.value = '';
    }
}

// Experience
function openAddExperienceModal() {
    document.getElementById('experienceModal').style.display = 'flex';
}

function closeExperienceModal() {
    document.getElementById('experienceModal').style.display = 'none';
}

function saveExperience() {
    showNotification('Internship added successfully!', 'success');
    closeExperienceModal();
}

function deleteExperience(id) {
    if (confirm('Are you sure you want to delete this internship?')) {
        showNotification('Internship deleted successfully!', 'success');
    }
}

// Competition
function openAddCompetitionModal() {
    document.getElementById('competitionModal').style.display = 'flex';
}

function closeCompetitionModal() {
    document.getElementById('competitionModal').style.display = 'none';
}

function saveCompetition() {
    showNotification('Competition added successfully!', 'success');
    closeCompetitionModal();
}

function deleteCompetition(id) {
    if (confirm('Are you sure you want to delete this competition?')) {
        showNotification('Competition deleted successfully!', 'success');
    }
}

// Password
function resetPasswordForm() {
    document.getElementById('passwordForm').reset();
}

async function savePassword() {
    const form = document.getElementById('passwordForm');
    const current = form.querySelector('input[name="current"]').value;
    const newPwd = form.querySelector('input[name="New"]').value;
    const confirm = form.querySelector('input[name="confirm"]').value;
    
    if (!current || !newPwd || !confirm) {
        showNotification('Please fill all password fields', 'error');
        return;
    }
    
    if (newPwd !== confirm) {
        showNotification('New passwords do not match', 'error');
        return;
    }
    
    if (newPwd.length < 6) {
        showNotification('New password must be at least 6 characters', 'error');
        return;
    }
    
    try {
        const response = await fetch('api/change_password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                current: current,
                new: newPwd,
                confirm: confirm
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Password changed successfully!', 'success');
            form.reset();
        } else {
            showNotification(result.message || 'Failed to change password', 'error');
        }
    } catch (error) {
        console.error('Failed to change password:', error);
        showNotification('Failed to change password. Please try again.', 'error');
    }
}

// Notification
function showNotification(message, type) {
    const notification = document.createElement('div');
    let bgColor, borderColor, textColor, icon;
    
    if (type === 'success') {
        bgColor = '#d4edda';
        borderColor = '#28a745';
        textColor = '#155724';
        icon = '✓';
    } else if (type === 'error') {
        bgColor = '#f8d7da';
        borderColor = '#dc3545';
        textColor = '#721c24';
        icon = '✕';
    }

    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${bgColor};
        color: ${textColor};
        padding: 16px 20px;
        border-radius: 6px;
        border: 2px solid ${borderColor};
        font-size: 14px;
        font-weight: 500;
        z-index: 10000;
        animation: slideIn 0.3s ease;
        max-width: 350px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        gap: 12px;
    `;
    
    notification.innerHTML = `
        <span style="font-size: 20px; font-weight: bold;">${icon}</span>
        <span>${message}</span>
    `;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

// File Upload Functions
let selectedFile = null;

function handleFileSelect(event) {
    const file = event.target.files[0];
    if (file) {
        selectedFile = file;
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('filePreview').style.display = 'block';
    }
}

function clearFileSelection() {
    selectedFile = null;
    document.getElementById('fileInput').value = '';
    document.getElementById('filePreview').style.display = 'none';
}

// Drag and drop
const uploadArea = document.getElementById('uploadArea');
if (uploadArea) {
    uploadArea.addEventListener('click', () => {
        document.getElementById('fileInput').click();
    });
    
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.style.backgroundColor = '#f0f8ff';
    });
    
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.style.backgroundColor = '';
    });
    
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.style.backgroundColor = '';
        const file = e.dataTransfer.files[0];
        if (file) {
            document.getElementById('fileInput').files = e.dataTransfer.files;
            handleFileSelect({ target: { files: [file] } });
        }
    });
}

// File upload form submission
const fileUploadForm = document.getElementById('fileUploadForm');
if (fileUploadForm) {
    fileUploadForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (!selectedFile) {
            showNotification('Please select a file to upload', 'error');
            return;
        }
        
        const formData = new FormData();
        formData.append('file', selectedFile);
        
        const uploadBtn = document.getElementById('uploadBtn');
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
        
        try {
            const response = await fetch('api/upload_student_file.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification('File uploaded successfully!', 'success');
                clearFileSelection();
                loadUploadedFiles();
            } else {
                showNotification(result.message || 'Failed to upload file', 'error');
            }
        } catch (error) {
            console.error('Upload error:', error);
            showNotification('Failed to upload file. Please try again.', 'error');
        } finally {
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload File';
        }
    });
}

// Load uploaded files
async function loadUploadedFiles() {
    try {
        const response = await fetch('api/get_student_files.php', {
            credentials: 'same-origin'
        });
        
        const result = await response.json();
        const filesList = document.getElementById('uploadedFilesList');
        
        if (result.success && result.data.length > 0) {
            filesList.innerHTML = result.data.map(file => {
                const fileSize = (file.size / 1024).toFixed(2) + ' KB';
                const fileIcon = file.type === 'zip' ? 'fa-file-archive' : 'fa-file-pdf';
                return `
                    <div class="file-item">
                        <i class="fas ${fileIcon}"></i>
                        <div class="file-info">
                            <span class="file-name">${file.filename}</span>
                            <span class="file-size">${fileSize}</span>
                        </div>
                        <button class="btn-delete-file" onclick="deleteFile(${file.fid})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
            }).join('');
        } else {
            filesList.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <p>No documents uploaded yet</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Failed to load files:', error);
    }
}

// Delete file
async function deleteFile(fid) {
    if (!confirm('Are you sure you want to delete this file?')) {
        return;
    }
    
    try {
        const response = await fetch('api/delete_student_file.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ fid: fid })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('File deleted successfully', 'success');
            loadUploadedFiles();
        } else {
            showNotification(result.message || 'Failed to delete file', 'error');
        }
    } catch (error) {
        console.error('Delete error:', error);
        showNotification('Failed to delete file', 'error');
    }
}


// CSS for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(400px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(400px); opacity: 0; }
    }
`;
document.head.appendChild(style);