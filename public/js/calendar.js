/**
 * Calendar Module
 * 
 * Handles market calendar display and navigation.
 * Features:
 * - Fetch market dates from API
 * - Display monthly calendar
 * - Navigation between months
 * - Highlight dates with market events
 * - Click handling for date selection
 * 
 * @module calendar
 */

export const Calendar = (() => {
  let isInitialized = false;
  const calendarWidgets = [];

  /**
   * Initialize a calendar widget
   *
   * @param {HTMLElement} container - The container element with data-market-calendar
   * @returns {void}
   */
  const initCalendar = (container) => {
    const now = new Date();
    let currentYear = now.getFullYear();
    let currentMonth = now.getMonth() + 1;

    /**
     * Show modal with market details for selected date
     */
    const showMarketModal = (dateStr, dateData) => {
      let modal = document.getElementById('marketCalendarModal');
      if (!modal) {
        modal = document.createElement('div');
        modal.id = 'marketCalendarModal';
        modal.className = 'fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50 p-4';
        document.body.appendChild(modal);
      }

      const dateObj = new Date(dateStr + 'T00:00:00');
      const formattedDate = dateObj.toLocaleString('default', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric',
      });

      let marketsHTML = '';
      if (Array.isArray(dateData.markets) && dateData.markets.length > 0) {
        marketsHTML = dateData.markets
          .map(
            (market) => `
          <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <h4 class="mb-2 font-semibold text-fluid-sm text-brand-primary">${market.name || 'Market'}</h4>
            ${
              market.weather
                ? `<div class="flex items-center space-x-2">
                    <span class="text-fluid-sm font-medium text-gray-700">Weather:</span>
                    <span class="inline-block rounded-full bg-blue-100 px-3 py-1 text-fluid-sm text-blue-800">${market.weather}</span>
                  </div>`
                : '<p class="text-center text-fluid-sm italic text-gray-500">No weather data</p>'
            }
          </div>
        `
          )
          .join('');
      } else {
        marketsHTML =
          '<p class="text-center text-gray-600">No market details available for this date.</p>';
      }

      modal.innerHTML = `
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-2xl">
          <div class="mb-6 flex items-start justify-between border-b border-gray-200 pb-4">
            <div>
              <h3 class="text-fluid-xl font-bold text-gray-900">${formattedDate}</h3>
              <p class="mt-1 inline-block rounded-full bg-green-100 px-3 py-1 text-fluid-sm font-medium text-green-800">${dateData.event_count || 0} market${dateData.event_count !== 1 ? 's' : ''}</p>
            </div>
            <button class="text-3xl font-light text-gray-400 transition-colors hover:text-gray-600" onclick="document.getElementById('marketCalendarModal').classList.add('hidden'); document.getElementById('marketCalendarModal').classList.remove('flex');">
              ×
            </button>
          </div>
          
          <div class="mb-6 max-h-96 space-y-3 overflow-y-auto">
            ${marketsHTML}
          </div>
          
          <button class="w-full rounded-lg bg-brand-primary px-4 py-3 font-semibold text-white transition-colors hover:bg-brand-primary-hover active:opacity-90" onclick="document.getElementById('marketCalendarModal').classList.add('hidden'); document.getElementById('marketCalendarModal').classList.remove('flex');">
            Close
          </button>
        </div>
      `;

      modal.classList.remove('hidden');
      modal.classList.add('flex');

      modal.addEventListener('click', (e) => {
        if (e.target === modal) {
          modal.classList.add('hidden');
          modal.classList.remove('flex');
        }
      });
    };

    /**
     * Render the calendar UI
     */
    const render = async () => {
      container.innerHTML = '';

      try {
        const response = await fetch(
          `/api/markets/calendar?year=${currentYear}&month=${currentMonth}`
        );

        if (!response.ok) {
          container.innerHTML =
            '<div class="rounded border border-red-200 bg-red-50 p-4 text-red-700">Failed to load calendar</div>';
          return;
        }

        const data = await response.json();

        if (!data.success) {
          container.innerHTML =
            '<div class="rounded border border-yellow-200 bg-yellow-50 p-4 text-yellow-700">No calendar data available</div>';
          return;
        }

        const marketDatesObj = data.dates || {};
        const marketDates = Object.keys(marketDatesObj);
        const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
        const firstDayOfMonth = new Date(currentYear, currentMonth - 1, 1).getDay();

        const header = document.createElement('div');
        header.className = 'calendar-header';
        header.innerHTML = `
          <button class="calendar-nav-btn" aria-label="Previous month" data-prev-month>←</button>
          <h2 class="calendar-title" aria-live="polite">
            ${new Date(currentYear, currentMonth - 1).toLocaleString('default', {
              month: 'long',
              year: 'numeric',
            })}
          </h2>
          <button class="calendar-nav-btn" aria-label="Next month" data-next-month>→</button>
        `;

        const weekdaysContainer = document.createElement('div');
        weekdaysContainer.className = 'calendar-weekdays';
        const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        weekdays.forEach((day) => {
          const dayEl = document.createElement('div');
          dayEl.className = 'calendar-weekday';
          dayEl.textContent = day;
          weekdaysContainer.appendChild(dayEl);
        });

        const daysContainer = document.createElement('div');
        daysContainer.className = 'calendar-days';

        for (let i = 0; i < firstDayOfMonth; i++) {
          const emptyDay = document.createElement('div');
          emptyDay.className = 'calendar-day calendar-day-other';
          emptyDay.innerHTML = '&nbsp;';
          daysContainer.appendChild(emptyDay);
        }

        for (let day = 1; day <= daysInMonth; day++) {
          const dateStr = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
          const hasEvent = marketDates.includes(dateStr);

          const dayEl = document.createElement('button');
          dayEl.className = `calendar-day ${
            hasEvent ? 'calendar-day-has-event' : ''
          }`;
          dayEl.textContent = day;
          dayEl.setAttribute('data-date', dateStr);

          if (hasEvent) {
            const dateData = marketDatesObj[dateStr];
            dayEl.setAttribute('title', dateData.market_names || 'Markets scheduled');
            dayEl.addEventListener('click', () => {
              showMarketModal(dateStr, dateData);
            });
          } else {
            dayEl.disabled = true;
          }

          daysContainer.appendChild(dayEl);
        }

        container.appendChild(header);
        container.appendChild(weekdaysContainer);
        container.appendChild(daysContainer);

        header
          .querySelector('[data-prev-month]')
          .addEventListener('click', () => {
            if (currentMonth === 1) {
              currentMonth = 12;
              currentYear--;
            } else {
              currentMonth--;
            }
            render();
          });

        header
          .querySelector('[data-next-month]')
          .addEventListener('click', () => {
            if (currentMonth === 12) {
              currentMonth = 1;
              currentYear++;
            } else {
              currentMonth++;
            }
            render();
          });
      } catch (error) {
        console.error('Calendar rendering error:', error);
        container.innerHTML =
          '<div class="rounded border border-red-200 bg-red-50 p-4 text-red-700">Error loading calendar</div>';
      }
    };

    render();
  };

  /**
   * Initialize Calendar module
   *
   * Finds all elements with data-market-calendar attribute and initializes them
   *
   * @returns {void}
   */
  const init = () => {
    if (isInitialized) return;

    const containers = document.querySelectorAll('[data-market-calendar]');
    containers.forEach((container) => {
      initCalendar(container);
      calendarWidgets.push(container);
    });

    isInitialized = true;
  };

  return { init };
})();
