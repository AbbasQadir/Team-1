document.addEventListener("DOMContentLoaded", function () {
    const contactType = document.getElementById("contact-type");
    const emailField = document.getElementById("emailinput");
	const emailField2 = document.getElementById("emailconfirm");
    const phoneField = document.getElementById("phone-field");
	const container = document.getElementById("container");
	const form = document.getElementById('contact-me');

    function toggleContactFields() {
        if (contactType.value === "email") {
            emailField.style.display = "block";
        	emailField2.style.display = "block";
        	emailField.style.margin = "3px 10px";
        	emailField2.style.margin = "3px 10px";
            emailField.setAttribute("required", "required");
        	emailField2.setAttribute("required", "required");
            phoneField.style.display = "none";
            phoneField.removeAttribute("required");
        } else {
            emailField.style.display = "none";
            emailField.removeAttribute("required");
        	emailField2.style.display = "none";
            emailField2.removeAttribute("required");
            phoneField.style.display = "block";
            phoneField.style.margin = "3px 10px";
            phoneField.setAttribute("required", "required");
        }
    }

    function validateForm(event) {
        const phoneNumber = phoneField.value;
        const ukPhonePattern = /^\+44\s?7\d{3}\s?\d{3}\s?\d{3}$|^07\d{3}\s?\d{3}\s?\d{3}$/;

        if ((contactType.value === "phone" || contactType.value === "text") && !ukPhonePattern.test(phoneNumber)) {
            alert("Please enter a valid UK phone number.");
        	event.preventDefault();
        }
    }

    contactType.addEventListener('change', toggleContactFields);
});

const form = document.getElementById('contact-me');
const email = document.getElementById('emailinput');
const confirmEmail = document.getElementById('emailconfirm');
const date = document.getElementById('datetime');
const message = document.getElementById('message');
const currentDate = new Date();
const phoneField = document.getElementById("phone-field");

form.addEventListener('submit', function (e) {
	const contactType = document.getElementById("contact-type");
	var selectedDate = new Date(document.getElementById('datetime').value);
	console.log(selectedDate);
    console.log(currentDate);
    
	const phoneNumber = phoneField.value;
	const ukPhonePattern = /^\+44\s?7\d{3}\s?\d{3}\s?\d{3}$|^07\d{3}\s?\d{3}\s?\d{3}$/;
	console.log(phoneNumber);

    if ((contactType.value === "phone" || contactType.value === "text") && !ukPhonePattern.test(phoneNumber)) {
    	message.textContent = "Please enter a valid UK phone number!";
        e.preventDefault();
    }

	else if (selectedDate <= currentDate){
    	e.preventDefault();
    	message.textContent = "Date cannot be in the past!";
    }

	else if (contactType.value === 'email'){
    	if (email.value !== confirmEmail.value) {
    		e.preventDefault();
    		console.log(new Date(document.getElementById('datetime').value));
        	message.textContent = "Emails do not match.";
    	} else {
        	message.textContent = "Emails match!";
    		message.style.color = "green";
    	}
    }

});