function categorizeItems(buttonSelector, cardSelector) {
    const buttons = document.querySelectorAll(buttonSelector);
    const cards = document.querySelectorAll(cardSelector);
    buttons.forEach(button => {
        button.addEventListener('click', () => {
            const filter = button.dataset.filter;
            buttons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            cards.forEach(card => {
                if (filter === 'all' || card.dataset.category === filter) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
}
categorizeItems(
    '.pkgs-filter .btn',
    '.pkg-card'
);

categorizeItems(
    '.activities-filter .btn',
    '.activities-section .col-lg-6'
);

// these two functions estimate the total payable in live mode for better user experience

function tourTotalEstimation() {
    const basePrice = parseFloat(document.getElementById('base_price').value);
    const totalDisplay = document.getElementById('total_amount');
    const travellerInput  = document.getElementById('travellers');
    const roomCountInput  = document.getElementById('room_count');
    const transferCheckbox = document.getElementById('airport_transfer');

    const roomBasePrice = 2000;
    const transferFee   = 5000;

    const travellerCount = parseInt(travellerInput.value) || 0;
    const roomCount = parseInt(roomCountInput.value) || 0;
    const hasTransfer = transferCheckbox ? transferCheckbox.checked : false;

    if(travellerCount > 0 && !isNaN(basePrice)) {
        let total = (basePrice * travellerCount) + (roomCount * roomBasePrice);
        if(hasTransfer) total += transferFee;
        totalDisplay.innerText = 'LKR ' + total.toLocaleString();    
    } 
    else {
        totalDisplay.innerText = 'Hold On...';
    }
}

const travellerInput = document.getElementById('travellers');
const roomCountInput = document.getElementById('room_count');
const transferCheckbox = document.getElementById('airport_transfer');

if(travellerInput) travellerInput.addEventListener('input', tourTotalEstimation);
if(roomCountInput) roomCountInput.addEventListener('input', tourTotalEstimation);
if(transferCheckbox) transferCheckbox.addEventListener('change', tourTotalEstimation);

function accommodationTotal() {
    const basePrice = parseFloat(document.getElementById('price_per_night').value);
    const totalDisplay = document.getElementById('total_amount');
    const roomCountInput = document.getElementById('room_count');
    const checkIn = document.getElementById('checkin_date');
    const checkOut = document.getElementById('checkout_date');

    if(!roomCountInput || !checkIn || !checkOut) return;

    const roomCount = parseInt(roomCountInput.value) || 0;
    const nights = (new Date(checkOut.value) - new Date(checkIn.value)) / (1000 * 60 * 60 * 24);

    if(roomCount > 0 && nights > 0 && !isNaN(basePrice)) {
        const total = basePrice * roomCount * nights;
        totalDisplay.innerText = 'LKR ' + total.toLocaleString();
    } else {
        totalDisplay.innerText = 'LKR 0';
    }
}

const accommdationRooms = document.getElementById('room_count');
const checkIn = document.getElementById('checkin_date');
const checkOut = document.getElementById('checkout_date');

if(accommdationRooms) accommdationRooms.addEventListener('input', accommodationTotal);
if(checkIn) checkIn.addEventListener('change', accommodationTotal);
if(checkOut) checkOut.addEventListener('change', accommodationTotal);
