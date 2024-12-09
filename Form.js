const form = document.getElementById('contact-me');
const email = document.getElementById('emailinput');
const confirmEmail = document.getElementById('emailconfirm');
const message = document.getElementById('message');

form.addEventListener('submit', function (e) {

    if (email.value !== confirmEmail.value) {
    	e.preventDefault();
        message.textContent = "Emails do not match.";
    } else {
        message.textContent = "Emails match!";
    	message.style.color = "green";
    }

});