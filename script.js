document.getElementById('loginForm').addEventListener('submit', function(event) {
    // Prevent default form submission
    event.preventDefault();

    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    // Basic client-side validation
    if (username.trim() === '' || password.trim() === '') {
        alert('Please fill in all fields.');
        return;
    }

    // Submit the form programmatically
    this.submit();
});
