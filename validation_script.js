// regex patterns
var namePattern = /^[a-zA-Z\s'-]+$/;
var phonePattern = /^\+?[0-9\s\-()]{7,20}$/;
var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
var identifierPattern = /^[A-Za-z0-9]{5,20}$/;
let today = new Date();
today.setHours(0, 0, 0, 0);

// validate contact form 
function validateContactForm() {
    let fullName = document.getElementById("full_name").value.trim();
    let email = document.getElementById("email").value.trim();
    let message = document.getElementById("message").value.trim();

    let errors = [];

    if (fullName === "") {
        errors.push("Full Name is required.");
    } else if (!namePattern.test(fullName)) {
        errors.push("Please enter a valid Full Name.");
    }

    if (email === "") {
        errors.push("Email is required.");
    } else if (!emailPattern.test(email)) {
        errors.push("Please enter a valid Email Address.");
    }

    if (message === "") {
        errors.push("Message is required.");
    } else if (message.length < 10) {
        errors.push("Message must be at least 10 characters long.");
    }

    if (errors.length > 0) {
        alert(errors.join("\n"));
        return false;
    }
    return true;
}

// validate registration form inpuits
function validateRegisterForm() {
    let fullName = document.getElementById("full_name").value.trim();
    let email = document.getElementById("email").value.trim();
    let phone = document.getElementById("phone").value.trim();
    let password = document.getElementById("password").value;
    let confirmPw = document.getElementById("confirmPw").value;
    let role = document.getElementById("role").value;

    let errors = [];

    if (fullName === "") {
        errors.push("Full Name is required.");
    } else if (!namePattern.test(fullName)) {
        errors.push("Enter a valid Full Name.");
    }
    if (email === "") {
        errors.push("Email is required.");
    } else if (!emailPattern.test(email)) {
        errors.push("Enter a valid Email Address.");
    }
    if (phone === "") {
        errors.push("Phone Number is required.");
    } else if (!phonePattern.test(phone)) {
        errors.push("Enter a valid Phone Number.");
    } 
    if (role === "") {
        errors.push("Role is required.");
    }
    if (password.length < 8) {
        errors.push("Password must be at least 8 characters long.");
    }
    if (password !== confirmPw) {
        errors.push("Passwords do not match.");
    }
    if (errors.length > 0) {
        alert(errors.join("\n"));
        return false;
    }
    return true;
}

// validate login form inputs
function validateLoginForm() {
    let email = document.getElementById("email").value.trim();
    let password = document.getElementById("password").value;

    let errors = [];

    if (email === "") {
        errors.push("Email is required.");
    } else if (!emailPattern.test(email)) {
        errors.push("Enter a valid Email Address.");
    }

    if (password === "") {
        errors.push("Password is required.");
    }

    if (errors.length > 0) {
        alert(errors.join("\n"));
        return false;
    }

    return true;
}

// validate transport booking form
function validateTransportForm() {
    let startDate = document.getElementById("start_date").value;
    let endDate = document.getElementById("end_date").value;
    let pickLocation = document.getElementById("pick_location").value.trim();
    let dropLocation = document.getElementById("drop_location").value.trim();
    let travellers = document.getElementById("travellers").value;
    let primaryId = document.getElementById("primary_identifier").value.trim();

    let errors = [];

    if (!startDate) {
        if (new Date(startDate) < today) {
            errors.push("Start Travel Date cannot be in the past.");
        }
        errors.push("Start Travel Date is required.");
    }
    if (!endDate) {
        errors.push("End Travel Date is required.");
    }
    if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
        errors.push("End Travel Date cannot be before Start Travel Date.");
    }
    if (pickLocation === "") {
        errors.push("Pick-Up location is required.");
    }
    if (dropLocation === "") {
        errors.push("Drop-Off location is required.");
    }
    if (
        pickLocation &&
        dropLocation &&
        pickLocation.toLowerCase() === dropLocation.toLowerCase()
    ) {
        errors.push("Pick-Up and Drop-Off locations cannot be the same.");
    }
    if (!travellers || travellers < 1) {
        errors.push("Traveller Count must be at least 1.");
    }
    if (!identifierPattern.test(primaryId)) {
        errors.push("Enter a valid NIC/Passport number.");
    }
    if (errors.length > 0) {
        alert(errors.join("\n"));
        return false;
    }
    return true;
}

function validateAccommForm() {
    let checkIn = document.getElementById("checkin_date").value;
    let checkOut = document.getElementById("checkout_date").value;
    let travellers = document.getElementById("travellers").value;
    let roomCount = document.getElementById("room_count").value;
    let primaryId = document.getElementById("primary_id").value.trim();

    let errors = [];

    if (!checkIn) {
        if (new Date(checkIn) < today) {
            errors.push("Check-In Date cannot be in the past.");
        }
        errors.push("Check-In Date is required.");
    }
    if (!checkOut) {
        errors.push("Check-Out Date is required.");
    }
    if (checkIn && checkOut && new Date(checkOut) <= new Date(checkIn)) {
        errors.push("Check-Out Date must be after Check-In Date.");
    }
    if (!travellers || travellers < 1) {
        errors.push("Traveller Count must be at least 1.");
    }
    if (!roomCount || roomCount < 1) {
        errors.push("Room Count must be at least 1.");
    }
    if (!identifierPattern.test(primaryId)) {
        errors.push("Enter a valid NIC/Passport number.");
    }
    if (errors.length > 0) {
        alert(errors.join("\n"));
        return false;
    }
    return true;
}

// validate tour booking form
function validateTourForm() {
    let startDate = document.getElementById("start_date").value;
    let endDate = document.getElementById("end_date").value;
    let travellers = document.getElementById("travellers").value;
    let roomCount = document.getElementById("room_count").value;
    let primaryId = document.getElementById("primary_identifier").value.trim();

    let errors = [];

    if (!startDate) {
        errors.push("Arrival Date is required.");
    }

    if (!endDate) {
        errors.push("Departure Date is required.");
    }

    if (startDate && endDate) {
        let arrival = new Date(startDate);
        let departure = new Date(endDate);

        if (departure <= arrival) {
            errors.push("Departure Date must be after Arrival Date.");
        }
    }

    if (startDate && new Date(startDate) < today) {
        errors.push("Arrival Date cannot be in the past.");
    }

    if (!travellers || travellers < 1) {
        errors.push("Traveller Count must be at least 1.");
    }

    if (!roomCount || roomCount < 1) {
        errors.push("Room Count must be at least 1.");
    }

    if (primaryId === "") {
        errors.push("Primary Traveller NIC/Passport is required.");
    } else if (!identifierPattern.test(primaryId)) {
        errors.push("Enter a valid NIC/Passport number.");
    }

    if (errors.length > 0) {
        alert(errors.join("\n"));
        return false;
    }
    return true;
}

// validate customized tour request form
function validateCustomizeForm() {
    let startDate = document.getElementById("start_date").value;
    let endDate = document.getElementById("end_date").value;
    let budget = document.getElementById("budget").value;
    let destinationNotes = document.getElementById("destination_notes").value.trim();
    let activityNotes = document.getElementById("activity_notes").value.trim();

    let accommodationSelected =
        document.getElementById("accomm_budget").checked ||
        document.getElementById("accomm_standard").checked ||
        document.getElementById("accomm_luxe").checked;

    let errors = [];

    if (!startDate) {
        errors.push("Arrival Date is required.");
    }

    if (!endDate) {
        errors.push("Departure Date is required.");
    }

    if (startDate && endDate) {
        let arrival = new Date(startDate);
        let departure = new Date(endDate);

        if (departure <= arrival) {
            errors.push("Departure Date must be after Arrival Date.");
        }
    }

    if (startDate && new Date(startDate) < today) {
        errors.push("Arrival Date cannot be in the past.");
    }

    if (!budget) {
        errors.push("Budget is required.");
    } else if (budget <= 0) {
        errors.push("Budget must be greater than 0.");
    }

    if (!accommodationSelected) {
        errors.push("Please select an accommodation preference.");
    }

    if (destinationNotes === "") {
        errors.push("Please enter your desired destinations.");
    } else if (destinationNotes.length < 10) {
        errors.push("Destination details should be at least 10 characters long.");
    }

    if (activityNotes === "") {
        errors.push("Please enter your preferred activities.");
    } else if (activityNotes.length < 10) {
        errors.push("Activity details should be at least 10 characters long.");
    }

    if (errors.length > 0) {
        alert(errors.join("\n"));
        return false;
    }
    return true;
}

function luhnAlogrithmCheck(cardNumber) {
    const cleaned = cardNumber.toString().replace(/[\s-]/g, '');
    
    if (!/^\d+$/.test(cleaned)) {
        return false;
    }

    let sum = 0;
    let shouldDouble = false;

    // loop through the string from right to left
    for (let i = cleaned.length - 1; i >= 0; i--) {
        let digit = parseInt(cleaned.charAt(i), 10);
        if (shouldDouble) {
            digit *= 2;
            if (digit > 9) {
                digit -= 9;
            }
        }
        sum += digit;
        shouldDouble = !shouldDouble;
    }

    return sum % 10 === 0;
}

function validatePaymentForm() {
    let amount = document.getElementById("pay_amnt").value.trim();
    let cardNumber = document.getElementById("acc_num").value.trim();
    let cardName = document.getElementById("name_card").value.trim();
    let expiryDate = document.getElementById("expiry_date").value.trim();
    let cvv = document.getElementById("cvv").value.trim();

    let errors = [];

    if (amount === "") {
        errors.push("Payment amount is required.");
    } else if (isNaN(amount) || parseFloat(amount) <= 0) {
        errors.push("Payment amount must be greater than 0.");
    }

    if (cardNumber === "") {
        errors.push("Card number is required.");
    } else if (!luhnAlogrithmCheck(cardNumber)) {
        errors.push("Invalid card number.");
    }

    if (cardName === "") {
        errors.push("Name on card is required.");
    } else if (!namePattern.test(cardName)) {
        errors.push("Please enter a valid cardholder name.");
    }

    if (expiryDate === "") {
        errors.push("Expiry date is required.");
    } else {
        const expiryPattern = /^(0[1-9]|1[0-2])\/\d{2}$/;
        if (!expiryPattern.test(expiryDate)) {
            errors.push("Expiry date must be in MM/YY format.");
        } else {
            const [month, year] = expiryDate.split("/");
            const expiry = new Date(
                2000 + parseInt(year),
                parseInt(month),
                0,
                23,
                59,
                59
            );

            const today = new Date();

            if (expiry < today) {
                errors.push("Card has expired.");
            }
        }
    }

    if (cvv === "") {
        errors.push("CVV is required.");
    } else if (!/^\d{3,4}$/.test(cvv)) {
        errors.push("CVV must contain 3 or 4 digits.");
    }

    if (errors.length > 0) {
        alert(errors.join("\n"));
        return false;
    }
    return true;
}