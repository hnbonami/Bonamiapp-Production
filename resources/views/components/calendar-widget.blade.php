@props(['widgetId'])

<div class="calendar-widget" id="calendar-{{ $widgetId }}" style="height:100%;">
    <div class="calendar-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1em;">
        <button class="calendar-prev" style="background:transparent;border:none;cursor:pointer;padding:0.5em;">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        <div class="calendar-month-year" style="font-weight:600;font-size:1.1em;"></div>
        <button class="calendar-next" style="background:transparent;border:none;cursor:pointer;padding:0.5em;">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    </div>
    
    <div class="calendar-grid" style="display:grid;grid-template-columns:repeat(7,1fr);gap:0.5em;text-align:center;">
        <!-- Dagen van de week -->
        <div style="font-weight:600;font-size:0.8em;opacity:0.7;">Ma</div>
        <div style="font-weight:600;font-size:0.8em;opacity:0.7;">Di</div>
        <div style="font-weight:600;font-size:0.8em;opacity:0.7;">Wo</div>
        <div style="font-weight:600;font-size:0.8em;opacity:0.7;">Do</div>
        <div style="font-weight:600;font-size:0.8em;opacity:0.7;">Vr</div>
        <div style="font-weight:600;font-size:0.8em;opacity:0.7;">Za</div>
        <div style="font-weight:600;font-size:0.8em;opacity:0.7;">Zo</div>
        
        <!-- Dagen worden hier dynamisch toegevoegd -->
        <div class="calendar-days"></div>
    </div>
    
    <!-- Afspraken lijst -->
    <div class="calendar-events" style="margin-top:1em;max-height:200px;overflow-y:auto;">
        <div class="events-list"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const widgetId = '{{ $widgetId }}';
    const calendarWidget = document.getElementById('calendar-' + widgetId);
    
    if (!calendarWidget) return;
    
    let currentDate = new Date();
    
    const monthNames = ['Januari', 'Februari', 'Maart', 'April', 'Mei', 'Juni', 'Juli', 'Augustus', 'September', 'Oktober', 'November', 'December'];
    
    function renderCalendar() {
        const monthYearEl = calendarWidget.querySelector('.calendar-month-year');
        const daysEl = calendarWidget.querySelector('.calendar-days');
        
        // Set maand en jaar
        monthYearEl.textContent = monthNames[currentDate.getMonth()] + ' ' + currentDate.getFullYear();
        
        // Bereken eerste dag van de maand
        const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
        const lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
        
        // Start dag (maandag = 0)
        const startDay = firstDay.getDay() === 0 ? 6 : firstDay.getDay() - 1;
        
        // Clear dagen
        daysEl.innerHTML = '';
        
        // Voeg lege dagen toe voor offset
        for (let i = 0; i < startDay; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.style.opacity = '0.3';
            daysEl.appendChild(emptyDay);
        }
        
        // Voeg dagen van de maand toe
        for (let day = 1; day <= lastDay.getDate(); day++) {
            const dayEl = document.createElement('div');
            dayEl.textContent = day;
            dayEl.style.cssText = 'padding:0.5em;border-radius:5px;cursor:pointer;transition:background 0.2s;';
            
            // Highlight vandaag
            if (day === new Date().getDate() && 
                currentDate.getMonth() === new Date().getMonth() && 
                currentDate.getFullYear() === new Date().getFullYear()) {
                dayEl.style.background = '#c8e1eb';
                dayEl.style.fontWeight = '700';
            }
            
            // Hover effect
            dayEl.addEventListener('mouseenter', function() {
                if (!this.style.background) {
                    this.style.background = '#f3f4f6';
                }
            });
            dayEl.addEventListener('mouseleave', function() {
                if (this.style.background !== 'rgb(200, 225, 235)') {
                    this.style.background = '';
                }
            });
            
            // Click event - toon afspraken voor deze dag
            dayEl.addEventListener('click', function() {
                loadEventsForDay(day);
            });
            
            daysEl.appendChild(dayEl);
        }
    }
    
    function loadEventsForDay(day) {
        const date = new Date(currentDate.getFullYear(), currentDate.getMonth(), day);
        const dateString = date.toISOString().split('T')[0];
        
        // Haal afspraken op voor deze dag
        fetch('/dashboard/calendar/events?date=' + dateString)
            .then(response => response.json())
            .then(events => {
                displayEvents(events, day);
            })
            .catch(error => {
                console.error('Error loading events:', error);
            });
    }
    
    function displayEvents(events, day) {
        const eventsListEl = calendarWidget.querySelector('.events-list');
        
        if (events.length === 0) {
            eventsListEl.innerHTML = `
                <div style="text-align:center;padding:1em;opacity:0.6;">
                    Geen afspraken op ${day} ${monthNames[currentDate.getMonth()]}
                </div>
            `;
            return;
        }
        
        eventsListEl.innerHTML = `
            <div style="font-weight:600;margin-bottom:0.5em;">
                Afspraken op ${day} ${monthNames[currentDate.getMonth()]}:
            </div>
        `;
        
        events.forEach(event => {
            const eventEl = document.createElement('div');
            eventEl.style.cssText = 'padding:0.5em;background:#f3f4f6;border-radius:5px;margin-bottom:0.5em;font-size:0.9em;';
            eventEl.innerHTML = `
                <div style="font-weight:600;">${event.time} - ${event.title}</div>
                <div style="font-size:0.85em;opacity:0.7;">${event.description || ''}</div>
            `;
            
            if (event.url) {
                eventEl.style.cursor = 'pointer';
                eventEl.addEventListener('click', () => window.location.href = event.url);
            }
            
            eventsListEl.appendChild(eventEl);
        });
    }
    
    // Navigation
    calendarWidget.querySelector('.calendar-prev').addEventListener('click', function() {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    });
    
    calendarWidget.querySelector('.calendar-next').addEventListener('click', function() {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    });
    
    // Initial render
    renderCalendar();
    
    // Auto-refresh every 5 minutes
    setInterval(() => {
        renderCalendar();
    }, 5 * 60 * 1000);
});
</script>