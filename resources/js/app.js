import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('input', (event) => {
    const timeInput = event.target;

    if (!(timeInput instanceof HTMLInputElement) || timeInput.dataset.attendanceTimeIn === undefined) {
        return;
    }

    const form = timeInput.form;
    const statusName = timeInput.name === 'time_in'
        ? 'status'
        : timeInput.name.replace(/\[time_in\]$/, '[status]');
    const statusSelect = form?.elements.namedItem(statusName);
    const scheduledTimeIn = form?.dataset.scheduledTimeIn;
    const timePattern = /^([01]\d|2[0-3]):[0-5]\d$/;

    if (
        !(statusSelect instanceof HTMLSelectElement)
        || !timePattern.test(timeInput.value)
        || !timePattern.test(scheduledTimeIn ?? '')
    ) {
        return;
    }

    const [hours, minutes] = timeInput.value.split(':').map(Number);
    const [scheduledHours, scheduledMinutes] = scheduledTimeIn.split(':').map(Number);
    const enteredMinutes = (hours * 60) + minutes;
    const scheduledMinutesTotal = (scheduledHours * 60) + scheduledMinutes;

    statusSelect.value = enteredMinutes > scheduledMinutesTotal ? 'late' : 'present';
});
