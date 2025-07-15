// Wait until the page is fully loaded
document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector(".book-form");

  form.addEventListener("submit", function (e) {
    // Get values from the form
    const name = form.name.value.trim();
    const email = form.email.value.trim();
    const phone = form.phone.value.trim();
    const address = form.address.value.trim();
    const location = form.location.value.trim();
    const guests = form.guests.value.trim();
    const arrivals = new Date(form.arrivals.value);
    const leaving = new Date(form.leaving.value);

    // Validate required fields
    if (!name || !email || !phone || !address || !location || !guests || !form.arrivals.value || !form.leaving.value) {
      alert("Please fill in all fields.");
      e.preventDefault();
      return;
    }

    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      alert("Please enter a valid email address.");
      e.preventDefault();
      return;
    }

    // Validate date
    if (arrivals > leaving) {
      alert("Arrival date must be before the leaving date.");
      e.preventDefault();
      return;
    }

    // Confirm submission
    const confirmBooking = confirm("Do you want to submit your booking?");
    if (!confirmBooking) {
      e.preventDefault();
    } else {
      alert("Booking submitted successfully!");
    }
  });
});
