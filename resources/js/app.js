

import Alpine from 'alpinejs';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import enGbLocale from '@fullcalendar/core/locales/en-gb';
import esLocale from '@fullcalendar/core/locales/es';
import timeGridPlugin from '@fullcalendar/timegrid';

window.Alpine = Alpine;

window.adminAppointmentsCalendar = function (config) {
    return {
        calendar: null,
        dayAppointments: [],
        dayError: null,
        dayLoading: false,
        dayModalOpen: false,
        dayTitle: '',
        refreshTimer: null,

        init() {
            this.calendar = new Calendar(this.$refs.calendar, {
                allDaySlot: false,
                buttonText: config.buttonText,
                dateClick: (info) => this.openDay(info.dateStr.slice(0, 10)),
                dayMaxEvents: 4,
                eventClick: (info) => this.openDay(info.event.extendedProps.local_date),
                events: (info, successCallback, failureCallback) => {
                    const url = new URL(config.eventsUrl, window.location.origin);

                    url.searchParams.set('start', info.startStr);
                    url.searchParams.set('end', info.endStr);
                    url.searchParams.set('timezone', config.timezone);

                    for (const [key, value] of Object.entries(this.filters())) {
                        if (value) {
                            url.searchParams.set(key, value);
                        }
                    }

                    fetch(url, {
                        headers: {
                            Accept: 'application/json',
                        },
                    })
                        .then((response) => {
                            if (! response.ok) {
                                throw new Error(config.labels.loadError);
                            }

                            return response.json();
                        })
                        .then(successCallback)
                        .catch((error) => {
                            this.dayError = error.message;
                            failureCallback(error);
                        });
                },
                firstDay: 1,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay',
                },
                height: 'auto',
                initialView: 'dayGridMonth',
                locale: config.locale === 'es' ? esLocale : enGbLocale,
                nowIndicator: true,
                plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
                timeZone: config.timezone,
            });

            this.calendar.render();

            this.refreshTimer = setInterval(() => {
                if (! document.hidden) {
                    this.calendar?.refetchEvents();
                }
            }, 60000);
        },

        closeDay() {
            this.dayModalOpen = false;
        },

        filterChanged() {
            this.calendar?.refetchEvents();
        },

        filters() {
            return {
                professional_id: this.$refs.professionalFilter?.value ?? '',
                status: this.$refs.statusFilter?.value ?? '',
                payment_status: this.$refs.paymentFilter?.value ?? '',
            };
        },

        openDay(date) {
            this.dayAppointments = [];
            this.dayError = null;
            this.dayLoading = true;
            this.dayModalOpen = true;

            const url = new URL(config.dayUrl, window.location.origin);

            url.searchParams.set('date', date);
            url.searchParams.set('timezone', config.timezone);

            for (const [key, value] of Object.entries(this.filters())) {
                if (value) {
                    url.searchParams.set(key, value);
                }
            }

            fetch(url, {
                headers: {
                    Accept: 'application/json',
                },
            })
                .then((response) => {
                    if (! response.ok) {
                        throw new Error(config.labels.loadError);
                    }

                    return response.json();
                })
                .then((data) => {
                    this.dayTitle = config.labels.dayTitle.replace(':date', data.date);
                    this.dayAppointments = data.appointments;
                })
                .catch((error) => {
                    this.dayError = error.message;
                })
                .finally(() => {
                    this.dayLoading = false;
                });
        },

        paymentBadgeClass(status) {
            return {
                paid: 'bg-emerald-50 text-emerald-800 ring-emerald-200',
                pending: 'bg-amber-50 text-amber-800 ring-amber-200',
                waived: 'bg-sky-50 text-sky-800 ring-sky-200',
            }[status] ?? 'bg-slate-50 text-slate-700 ring-slate-200';
        },

        resetFilters() {
            if (this.$refs.professionalFilter) {
                this.$refs.professionalFilter.value = '';
            }

            if (this.$refs.statusFilter) {
                this.$refs.statusFilter.value = '';
            }

            if (this.$refs.paymentFilter) {
                this.$refs.paymentFilter.value = '';
            }

            this.filterChanged();
        },

        statusBadgeClass(status) {
            return {
                cancelled: 'bg-slate-100 text-slate-700 ring-slate-200',
                completed: 'bg-brand-accent-soft text-brand-title ring-brand-accent/30',
                confirmed: 'bg-emerald-50 text-emerald-800 ring-emerald-200',
                pending: 'bg-amber-50 text-amber-800 ring-amber-200',
            }[status] ?? 'bg-slate-50 text-slate-700 ring-slate-200';
        },
    };
};

Alpine.start();
