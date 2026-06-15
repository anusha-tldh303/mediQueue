document.addEventListener('DOMContentLoaded', () => {
  const navToggle = document.querySelector('[data-nav-toggle]');
  const nav = document.querySelector('[data-nav]');

  if (navToggle && nav) {
    navToggle.addEventListener('click', () => nav.classList.toggle('is-open'));
  }

  const doctorSearch = document.querySelector('[data-doctor-search]');
  const doctorResults = document.getElementById('doctorResults');

  if (doctorSearch && doctorResults) {
    let timer;
    doctorSearch.addEventListener('input', () => {
      clearTimeout(timer);
      timer = setTimeout(async () => {
        const response = await fetch(`ajax/search_doctors.php?query=${encodeURIComponent(doctorSearch.value)}`);
        const payload = await response.json();

        if (payload.success) {
          doctorResults.innerHTML = payload.doctors.map((doctor) => `
            <article class="doctor-card">
              <div class="doctor-avatar">${doctor.doctor_name.charAt(0)}</div>
              <h3>${doctor.doctor_name}</h3>
              <p>${doctor.specialization}</p>
              <ul><li>Fee: ₹${doctor.consultation_fee}</li></ul>
              <a class="btn btn-small" href="book_appointment.php?doctor_id=${doctor.doctor_id}">Book Now</a>
            </article>
          `).join('');
        }
      }, 250);
    });
  }

  const doctorSelect = document.querySelector('[data-slot-doctor]');
  const dateSelect = document.querySelector('[data-slot-date]');
  const slotSelect = document.querySelector('[data-slot-time]');
  const nearestHint = document.getElementById('nearestHint');

  const loadSlots = async () => {
    if (!doctorSelect || !dateSelect || !slotSelect) return;
    if (!doctorSelect.value || !dateSelect.value) return;

    const response = await fetch(`ajax/get_slots.php?doctor_id=${encodeURIComponent(doctorSelect.value)}&date=${encodeURIComponent(dateSelect.value)}`);
    const payload = await response.json();

    if (!payload.success) return;

    slotSelect.innerHTML = '<option value="">Select time slot</option>' + payload.slots.map((slot) => `<option value="${slot}">${slot}</option>`).join('');
    if (nearestHint) {
      nearestHint.textContent = payload.nearest ? `Nearest available slot: ${payload.nearest}` : 'No slots available for the selected date.';
    }
  };

  if (doctorSelect && dateSelect && slotSelect) {
    doctorSelect.addEventListener('change', loadSlots);
    dateSelect.addEventListener('change', loadSlots);
  }

  document.querySelectorAll('[data-confirm-message]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      const message = form.getAttribute('data-confirm-message') || 'Are you sure?';
      if (!window.confirm(message)) {
        event.preventDefault();
      }
    });
  });
});
