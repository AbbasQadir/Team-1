const form = document.getElementById('contact-me');
const email = document.getElementById('emailinput');
const confirmEmail = document.getElementById('emailconfirm');
const date = document.getElementById('datetime');
const message = document.getElementById('message');
const currentDate = new Date();

form.addEventListener('submit', function (e) {
	var selectedDate = new Date(document.getElementById('datetime').value);
	console.log(selectedDate);
    console.log(currentDate);	


	if (selectedDate <= currentDate){
    	e.preventDefault();
    	message.textContent = "Date cannot be in the past!";
    }

    else if (email.value !== confirmEmail.value) {
    	e.preventDefault();
    	console.log(new Date(document.getElementById('datetime').value));
        message.textContent = "Emails do not match.";
    } else {
        message.textContent = "Emails match!";
    	message.style.color = "green";
    }

});