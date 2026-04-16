// Attendance Frontend Scripts


setInterval(displayclock, 500);

function displayclock() {
    var clockElement = document.getElementById("clock");
    if (!clockElement) return; // Exit if clock element doesn't exist
    
    // Get time in Philippines timezone (Asia/Manila)
    var time = new Date();
    var options = { timeZone: 'Asia/Manila', hour12: true, hour: 'numeric', minute: '2-digit', second: '2-digit' };
    var timeString = time.toLocaleTimeString('en-US', options);
    clockElement.innerHTML = timeString;
}
