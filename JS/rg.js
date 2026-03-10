function togglePassword(inputId, slashId) {
const passwordInput = document.getElementById(inputId);
const eyeSlash = document.getElementById(slashId);

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
document.getElementById('registerForm').addEventListener('submit', function(e) {
e.preventDefault();

const username = document.getElementById('username').value;
const email = document.getElementById('email').value;
const password = document.getElementById('password').value;
const confirmPassword = document.getElementById('confirm_password').value;

// Validate if passwords match
if (password !== confirmPassword) {
    alert('Passwords do not match! Please re-enter.');
    return;
}

// Validate password length
if (password.length < 6) {
    alert('Password must be at least 6 characters long!');
    return;
}

console.log('Username:', username);
console.log('Email:', email);
console.log('Password:', password);

// Add your form submission logic here
alert('Registration successful!\nUsername: ' + username + '\nEmail: ' + email);
});*/
