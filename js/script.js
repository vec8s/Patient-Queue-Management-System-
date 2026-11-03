/**
 * Core Application Script (Arabic & RTL)
 * Patient Queue Management System
 */

// Global Alert Utility
function getApiEndpoint(endpoint) {
    const isInsidePages = window.location.pathname.indexOf('/pages/') > -1;
    const prefix = isInsidePages ? '../api/v1/' : 'api/v1/';
    return prefix + endpoint.replace(/^(\.\.\/)?(api\/v1\/)?/, '');
}

function showAlert(type, message) {
    const alertContainer = document.getElementById('alertContainer');
    if (!alertContainer) return;

    const alertEl = document.createElement('div');
    alertEl.className = `alert alert-${type} alert-dismissible fade show shadow-sm text-start`;
    alertEl.role = 'alert';
    const iconClass = type === 'success' ? 'bi-check-circle-fill' : (type === 'danger' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill');

    alertEl.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi ${iconClass} ms-2 fs-5"></i>
            <div class="flex-grow-1">${message}</div>
            <button type="button" class="btn-close me-auto ms-0" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    `;

    alertContainer.appendChild(alertEl);
    setTimeout(() => {
        const bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
        if (bsAlert) bsAlert.close();
    }, 5000);
}

// Global Staff Logout Handler
function handleLogout() {
    localStorage.removeItem("pqs");
    localStorage.removeItem("staff");
    window.location.href = "login.php";
}

// Global Helper to get stored auth token
function getAuthToken() {
    return localStorage.getItem("pqs");
}

// Global Helper to get stored staff object
function getAuthStaff() {
    try {
        const staff = localStorage.getItem("staff");
        return staff ? JSON.parse(staff) : null;
    } catch (e) {
        return null;
    }
}

// Application Initialization
document.addEventListener('DOMContentLoaded', async function () {
    const staff = getAuthStaff();
    const staffNameEl = document.getElementById("navbarStaffName");
    const userNameEl = document.getElementById("userName");

    if (staff) {
        let displayName = `${staff.first_name || ''} ${staff.last_name || ''}`.trim();
        if (staff.role === 'doctor') {
            displayName = `د. ${staff.last_name || staff.first_name}`;
        }
        if (staffNameEl) staffNameEl.innerHTML = `<i class="bi bi-person-circle ms-1"></i> ${displayName}`;
        if (userNameEl) userNameEl.textContent = displayName;
    }

    // 1. Dashboard Page Initialization
    if (window.location.href.indexOf("dashboard.php") > -1) {
        const token = getAuthToken();
        if (!token) {
            window.location.href = "login.php";
            return;
        }

        // Validate token with server
        try {
            const res = await fetch(getApiEndpoint('auth.php'), {
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + token
                }
            });
            const data = await res.json();
            const user = (data.data && data.data.user) ? data.data.user : data;
            if (!user || (!user.id && !user.userId)) {
                handleLogout();
                return;
            }
        } catch (e) {
            console.warn("Auth token validation check failed", e);
        }

        await fetchAndRenderDashboard();
        // Periodic queue refresh every 8 seconds for doctor dashboard
        setInterval(fetchAndRenderDashboard, 8000);
    }

    // 2. Queue Display Page (Live Waiting Room TV)
    if (document.querySelector('.queue-display') || window.location.href.indexOf("queue.php") > -1) {
        startLiveQueuePolling();
    }

    // 3. Setup Check-in Page Form Handlers
    setupCheckInForms();

    // 4. Setup Call Patient Modal Handler
    setupCallPatientHandler();
});

/**
 * Fetch and Render Dashboard Queues
 */
async function fetchAndRenderDashboard() {
    try {
        const res = await fetch(getApiEndpoint('queue.php'));
        const data = await res.json();

        if (data.success && data.appointment) {
            renderDashboardLists(data.appointment);
        }
    } catch (err) {
        console.error('Error fetching dashboard patients:', err);
    }
}

function renderDashboardLists(appointment) {
    const waitingList = document.getElementById('waitingList');
    const inProgressList = document.getElementById('inProgressList');
    const completedList = document.getElementById('completedList');

    const waiting = appointment.waiting || [];
    const inProgress = appointment.inProgress || [];
    const completed = appointment.completed || [];

    if (waitingList) {
        waitingList.innerHTML = waiting.length ? '' : '<li class="list-group-item text-muted text-center py-4">لا يوجد مرضى في الانتظار حالياً</li>';
        waiting.forEach(p => {
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center py-3';
            li.innerHTML = `
                <div>
                    <h6 class="mb-1 fw-bold">${p.name}</h6>
                    <small class="text-muted"><i class="bi bi-clock ms-1"></i>${p.time} &bull; ${p.reason}</small>
                </div>
                <span class="badge bg-primary rounded-pill fs-6 px-3 py-2">${p.id}</span>
            `;
            waitingList.appendChild(li);
        });
    }

    if (inProgressList) {
        inProgressList.innerHTML = inProgress.length ? '' : '<li class="list-group-item text-muted text-center py-4">لا توجد استشارات جارية حالياً</li>';
        inProgress.forEach(p => {
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center py-3';
            li.innerHTML = `
                <div>
                    <h6 class="mb-1 fw-bold">${p.name}</h6>
                    <small class="text-muted"><i class="bi bi-person ms-1"></i>${p.doctor} &bull; ${p.room}</small>
                </div>
                <span class="badge bg-warning text-dark rounded-pill fs-6 px-3 py-2">${p.id}</span>
            `;
            inProgressList.appendChild(li);
        });
    }

    if (completedList) {
        completedList.innerHTML = completed.length ? '' : '<li class="list-group-item text-muted text-center py-4">لا توجد كشوفات مكتملة اليوم بعد</li>';
        completed.forEach(p => {
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center py-2';
            li.innerHTML = `
                <div>
                    <span class="fw-semibold">${p.name}</span>
                    <small class="text-muted me-2">${p.time}</small>
                </div>
                <span class="badge bg-success rounded-pill">${p.id}</span>
            `;
            completedList.appendChild(li);
        });
    }

    const waitingCount = document.getElementById('waitingCount');
    const progressCount = document.getElementById('progressCount');
    const completedCount = document.getElementById('completedCount');

    if (waitingCount) waitingCount.textContent = waiting.length;
    if (progressCount) progressCount.textContent = inProgress.length;
    if (completedCount) completedCount.textContent = completed.length;
}

/**
 * Setup Check-In Forms Handlers (Appointment & Walk-In)
 */
function setupCheckInForms() {
    const appointmentForm = document.getElementById('appointmentForm');
    const walkinForm = document.getElementById('walkinForm');

    if (appointmentForm) {
        appointmentForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const inputVal = document.getElementById('appointmentId');
            if (!inputVal || !inputVal.value.trim()) {
                showAlert('danger', 'يرجى إدخال كود الموعد المرجعي');
                return;
            }

            const formData = new FormData(appointmentForm);
            fetch('api/v1/queue.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const queueNumEl = document.getElementById("queueNumber");
                    if (queueNumEl) queueNumEl.innerText = data.quenumber;
                    const modalEl = document.getElementById('checkinSuccessModal');
                    if (modalEl) {
                        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modal.show();
                    }
                } else {
                    showAlert('danger', data.error || 'فشل في تسجيل حضور الموعد.');
                }
            })
            .catch(err => {
                showAlert('danger', 'تعذر الاتصال بالخادم: ' + err.message);
            });
        });
    }

    if (walkinForm) {
        walkinForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(walkinForm);
            formData.append('type', 'walk-in');

            fetch('api/v1/queue.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const queueNumEl = document.getElementById("queueNumber");
                    if (queueNumEl) queueNumEl.innerText = data.quenumber;
                    const modalEl = document.getElementById('checkinSuccessModal');
                    if (modalEl) {
                        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modal.show();
                    }
                } else {
                    showAlert('danger', data.error || 'فشل في استخراج تذكرة الحضور الفوري.');
                }
            })
            .catch(err => {
                showAlert('danger', 'تعذر الاتصال بالخادم: ' + err.message);
            });
        });
    }
}

/**
 * Setup Call Patient Modal Handler on Dashboard
 */
function setupCallPatientHandler() {
    const callPatientBtn = document.getElementById('callPatientBtn');
    if (!callPatientBtn) return;

    callPatientBtn.addEventListener('click', function () {
        const token = getAuthToken();
        const roomSelect = document.getElementById('roomSelect');
        const doctorSelect = document.getElementById('doctorSelect');
        const queueIdEl = document.getElementById('queueId');

        if (!roomSelect || !doctorSelect || !queueIdEl) return;

        const payload = {
            roomNumber: roomSelect.value,
            doctorId: doctorSelect.value,
            queueId: queueIdEl.value
        };

        fetch(getApiEndpoint('queue.php'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + (token || '')
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const modalEl = document.getElementById('callPatientModal');
                if (modalEl) {
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                }
                showAlert('success', 'تم استدعاء المريض بنجاح إلى الغرفة المحددة.');
                fetchAndRenderDashboard();
            } else {
                showAlert('danger', 'فشل في استدعاء المريض: ' + (data.error || 'خطأ غير معروف'));
            }
        })
        .catch(err => {
            showAlert('danger', 'فشل إرسال طلب الاستدعاء: ' + err.message);
        });
    });
}

/**
 * Real-time Live Queue Polling for TV Waiting Display
 */
let lastServingTicket = null;

function startLiveQueuePolling() {
    fetchLiveQueue();
    // Refresh live queue every 5 seconds
    setInterval(fetchLiveQueue, 5000);
}

function fetchLiveQueue() {
    fetch(getApiEndpoint('queue.php'))
        .then(res => res.json())
        .then(data => {
            const app = (data.data && data.data.appointment) ? data.data.appointment : (data.appointment || (data.data ? data.data.queue : null));
            if (app) {
                updateLiveQueueDisplay(app);
            }
        })
        .catch(err => console.warn('Queue polling error:', err));
}

function updateLiveQueueDisplay(appointment) {
    const inProgress = appointment.inProgress || [];
    const waiting = appointment.waiting || [];

    // 1. Now Serving Section
    const nowServingContainer = document.getElementById('nowServingContainer');
    if (nowServingContainer) {
        if (inProgress.length > 0) {
            const current = inProgress[0];
            nowServingContainer.innerHTML = `
                <div class="queue-number-lg mb-3 display-1 fw-bold text-primary animate-pulse">${current.id}</div>
                <h2 class="patient-name fw-bold mb-3">${current.name}</h2>
                <div class="patient-info d-flex justify-content-center gap-4 text-muted fs-5 mt-4">
                    <div class="info-item"><i class="bi bi-clock ms-1 text-primary"></i> <span>${current.time}</span></div>
                    <div class="info-item"><i class="bi bi-person ms-1 text-primary"></i> <span>${current.doctor}</span></div>
                    <div class="info-item"><i class="bi bi-door-open ms-1 text-primary"></i> <span>${current.room}</span></div>
                </div>
            `;

            // Play a soft chime if a new ticket is called
            if (lastServingTicket && lastServingTicket !== current.id) {
                playChime();
            }
            lastServingTicket = current.id;
        } else {
            nowServingContainer.innerHTML = `
                <div class="py-5 text-muted">
                    <i class="bi bi-pause-circle fs-1 d-block mb-3"></i>
                    <h3 class="fw-normal">لا يوجد مريض يتم استدعاؤه حالياً</h3>
                    <p>سيظهر المريض التالي على الشاشة فور استدعائه من قبل الطبيب.</p>
                </div>
            `;
        }
    }

    // 2. Upcoming Patients Section
    const upcomingList = document.getElementById('upcomingQueueList');
    if (upcomingList) {
        if (waiting.length > 0) {
            upcomingList.innerHTML = '';
            waiting.slice(0, 6).forEach(p => {
                const li = document.createElement('li');
                li.className = 'upcoming-item d-flex align-items-center justify-content-between p-3 mb-2 bg-white rounded shadow-sm border';
                li.innerHTML = `
                    <div class="d-flex align-items-center">
                        <span class="badge bg-secondary fs-6 px-3 py-2 ms-3">${p.id}</span>
                        <div>
                            <div class="fw-bold fs-6">${p.name}</div>
                            <small class="text-muted">${p.reason}</small>
                        </div>
                    </div>
                    <span class="badge bg-light text-dark border">${p.time}</span>
                `;
                upcomingList.appendChild(li);
            });
        } else {
            upcomingList.innerHTML = `
                <li class="p-4 text-center text-muted">
                    <i class="bi bi-check-circle fs-3 d-block mb-2 text-success"></i>
                    لا يوجد مرضى قادمون في قائمة الانتظار.
                </li>
            `;
        }
    }
}

// Gentle Web Audio API Chime for when a patient is called
function playChime() {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();

        const osc = ctx.createOscillator();
        const gain = ctx.createGain();

        osc.type = 'sine';
        osc.frequency.setValueAtTime(587.33, ctx.currentTime);
        osc.frequency.setValueAtTime(880, ctx.currentTime + 0.15);

        gain.gain.setValueAtTime(0.3, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.6);

        osc.connect(gain);
        gain.connect(ctx.destination);

        osc.start();
        osc.stop(ctx.currentTime + 0.6);
    } catch (e) {
        // AudioContext may be blocked before first user interaction
    }
}