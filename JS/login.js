function togglePassword() {
    const passwordInput = document.getElementById('password');
    const eyeSlash = document.getElementById('eyeSlash');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeSlash.style.display = 'block';
    } else {
        passwordInput.type = 'password';
        eyeSlash.style.display = 'none';
    }
}

// Form submission handling

/*
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const remember = document.getElementById('remember').checked;
    console.log('Username:', username);
    console.log('Password:', password);
    console.log('Remember me:', remember);
    
    // Add your form submission logic here
    alert('Form submitted successfully!\nUsername: ' + username);
}*/
