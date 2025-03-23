let date = new Date();
let year = date.getFullYear();
let month = date.getMonth();

const dates = document.querySelector(".calendar-dates");

const currdate = document.querySelector(".calendar-current-date");

const prenexIcons = document.querySelectorAll(".calendar-navigation span");

const eventsDisplay = document.querySelector(".events");
const months = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December"
];

function getCityIdFromURL() {
    const url = window.location.pathname;  
    const regex = /\/cities\/(\d+)/;
    const match = url.match(regex);
    return match ? match[1]: null;
}

function getEventsOnDate(events, date) {
    return events.filter(event => {
        const startDate = new Date(event.start_date);
        startDate.setHours(0, 0, 0, 0); 

        const endDate = new Date(event.end_date);
        endDate.setHours(0, 0, 0, 0); 

        return date >= startDate && date <= endDate;
    });
}

function formatDateToDayAndHour(dateString) {
    const date = new Date(dateString); 

    const day = date.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' }); 
    const hour = date.getHours().toString().padStart(2, '0');
    const minutes = date.getMinutes().toString().padStart(2, '0');

    return `${day} ${hour}:${minutes}`;
}

function getEventsOfDay(day) {
    return eventsDisplay.querySelectorAll(`[data-day="${day}"]`);
}

function seeDay() {
    (eventsDisplay.querySelectorAll(".day-events-detailed")).forEach(element => {
        element.style.display = "none";
      });
      const events = getEventsOfDay(this.innerText);
        if (events.length > 0) {
            events.forEach(day => {
                day.style.display = "block";
              })
            eventsDisplay.style.display = "block";
        }
        else {
            eventsDisplay.style.display = "none";
        }
      const active = document.querySelectorAll(".active");
      if (active) {
        active.forEach(day => {
            day.classList.remove("active");
        })
      }
      
      this.classList.add("active");
}

function getEventElement(day, event) {
    return `<div class="day-events-detailed" data-day=${day}>
                <h2 class="event-name">${event.name}</h2>
                <p class="event-dates">
                    <span class="start-date">${formatDateToDayAndHour(event.start_date)}</span> |
                    <span class="end-date">${formatDateToDayAndHour(event.end_date)}</span>
                </p>
                <p class="event-description">${event.description}</p>
            </div>`
}

async function updatecalendar() {

    let firstweekday = new Date(year, month, 1).getDay();
    let lastdaymonth = new Date(year, month + 1, 0).getDate();
    let lastweekday = new Date(year, month, lastdaymonth).getDay();
    let monthlastdate = new Date(year, month, 0).getDate();

    try {
        const response = await fetch(`/api/cities/${getCityIdFromURL()}/events?year=${year}&month=${month}`);
        const data = await response.json();
        events = data; 
    } catch (error) {
        displayErr
    }
    let calendar = "";
    let eventsMonth = "";

    // Add the last dates of the previous month
    for (let i = firstweekday; i > 0; i--) {
        calendar +=
            `<li class="inactive">${monthlastdate - i + 1}</li>`;
    }

    // Add the dates of the current month
    for (let i = 1; i <= lastdaymonth; i++) {

        // Check if the current date is today
        let isToday = i === date.getDate()
            && month === new Date().getMonth()
            && year === new Date().getFullYear()
            ? "today"
            : "";
        day_events = getEventsOnDate(events, new Date(year, month, i));
        calendar += `
        <li class="day ${isToday}">${i}
            <div class="day-events">`;
        for (let j = 0; j < Math.min(4, day_events.length); j++) {
            calendar += `<span class="event-indicator"></span>`
            eventsMonth += getEventElement(i, day_events[j]);
        }
        for (let j = 4; j < day_events.length; j++) {
            eventsMonth += getEventElement(i, day_events[j]);
        }
        calendar += `</div></li>`
    }

    for (let i = lastweekday; i < 6; i++) {
        calendar += `<li class="inactive">${i - lastweekday + 1}</li>`
    }

    currdate.innerText = `${months[month]} ${year}`;


    dates.innerHTML = calendar;
    eventsDisplay.innerHTML = eventsMonth;
    (dates.querySelectorAll(".day")).forEach(element => {
        element.addEventListener("click", seeDay);
      })
}

updatecalendar();

prenexIcons.forEach(icon => {
    icon.addEventListener("click", () => {

        month = icon.id === "calendar-prev" ? month - 1 : month + 1;

        if (month < 0 || month > 11) {
            date = new Date(year, month, new Date().getDate());
            year = date.getFullYear();
            month = date.getMonth();
        }
        else {
            date = new Date();
        }

        updatecalendar();
    });
});