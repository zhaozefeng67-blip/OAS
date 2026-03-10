document.querySelectorAll('.tab-item').forEach(tab => {
    tab.addEventListener('click', function() {
        // Remove active class from all tabs
        document.querySelectorAll('.tab-item').forEach(item => {
            item.classList.remove('active');
        });
        
        // Hide all content
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        
        // Activate current tab
        this.classList.add('active');
        
        // Show corresponding content
        const tabId = this.getAttribute('data-tab');
        document.getElementById(tabId).classList.add('active');
    });
});

// Form submission handling

/*
const forms = {
    resume: document.getElementById('resumeForm'),
    basic: document.getElementById('basicForm'),
    account: document.getElementById('accountForm')
};

const successMessages = {
    resume: document.getElementById('resumeSuccessMsg'),
    basic: document.getElementById('basicSuccessMsg'),
    account: document.getElementById('accountSuccessMsg')
};

// Resume form submission
forms.resume.addEventListener('submit', function(e) {
    e.preventDefault();

    const data = {
        undergradSchool: document.getElementById('undergrad-school').value,
        undergradMajor: document.getElementById('undergrad-major').value,
        undergradGpa: document.getElementById('undergrad-gpa').value,
        ieltsList: [
            parseFloat(document.getElementById('ielts-listening').value) || 0,
            parseFloat(document.getElementById('ielts-reading').value) || 0,
            parseFloat(document.getElementById('ielts-writing').value) || 0,
            parseFloat(document.getElementById('ielts-speaking').value) || 0,
        ],
        experience: document.getElementById('experience').value,
        resumeFile: document.getElementById('resume-file').files[0]?.name || ''
    };

    // Show success message
    successMessages.resume.classList.add('show');
    setTimeout(() => {
        successMessages.resume.classList.remove('show');
    }, 3000);

    // Save to local storage
    localStorage.setItem('resumeData', JSON.stringify(data));
});

// Basic information form submission
forms.basic.addEventListener('submit', function(e) {
    e.preventDefault();

    const data = {
        name: document.getElementById('name').value,
        birthdate: document.getElementById('birthdate').value,
        idNumber: document.getElementById('id-number').value,
        country: document.getElementById('country').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value
    };

    // Show success message
    successMessages.basic.classList.add('show');
    setTimeout(() => {
        successMessages.basic.classList.remove('show');
    }, 3000);

    // Save to local storage
    localStorage.setItem('basicData', JSON.stringify(data));
});

// Account form submission
forms.account.addEventListener('submit', function(e) {
    e.preventDefault();

    const data = {
        username: document.getElementById('username').value,
        password: document.getElementById('password').value
    };

    // Show success message
    successMessages.account.classList.add('show');
    setTimeout(() => {
        successMessages.account.classList.remove('show');
    }, 3000);

    // Save to local storage
    localStorage.setItem('accountData', JSON.stringify(data));
});

// Restore data on page load
window.addEventListener('load', function() {
    // Restore resume data
    const resumeData = localStorage.getItem('resumeData');
    if (resumeData) {
        const data = JSON.parse(resumeData);
        document.getElementById('undergrad-school').value = data.undergradSchool || '';
        document.getElementById('undergrad-major').value = data.undergradMajor || '';
        document.getElementById('undergrad-gpa').value = data.undergradGpa || '';
        document.getElementById('ielts-listening').value = data.ieltsList[0] || '';
        document.getElementById('ielts-reading').value = data.ieltsList[1] || '';
        document.getElementById('ielts-writing').value = data.ieltsList[2] || '';
        document.getElementById('ielts-speaking').value = data.ieltsList[3] || '';
        document.getElementById('experience').value = data.experience || '';
    }

    // Restore basic information data
    const basicData = localStorage.getItem('basicData');
    if (basicData) {
        const data = JSON.parse(basicData);
        document.getElementById('name').value = data.name || '';
        document.getElementById('birthdate').value = data.birthdate || '';
        document.getElementById('country').value = data.country || '';
        document.getElementById('email').value = data.email || '';
        document.getElementById('phone').value = data.phone || '';
    }

    // Restore account data
    const accountData = localStorage.getItem('accountData');
    if (accountData) {
        const data = JSON.parse(accountData);
        document.getElementById('user-id').value = data.userId || 'OAS2024001';
        document.getElementById('username').value = data.username || 'default';
        document.getElementById('password').value = data.password || '123456';
    }
});
*/