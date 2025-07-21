// Sample data for the dashboard
let patients = {
    waiting: [
        { id: 'A104', name: 'Michael Brown', time: '11:00 AM', reason: 'Annual Checkup', priority: 'normal' },
        { id: 'A105', name: 'Emily Davis', time: '11:15 AM', reason: 'Follow-up', priority: 'normal' },
        { id: 'A106', name: 'Robert Wilson', time: '11:30 AM', reason: 'Vaccination', priority: 'normal' },
        { id: 'URG107', name: 'Jennifer Lee', time: '11:45 AM', reason: 'Injury', priority: 'high' }
    ],
    inProgress: [
        { id: 'A103', name: 'Sarah Johnson', time: '10:45 AM', doctor: 'Dr. Smith', room: 'Room 2' }
    ],
    completed: [
        { id: 'A101', name: 'John Doe', time: '10:00 AM' },
        { id: 'A102', name: 'Jane Smith', time: '10:30 AM' }
    ]
};

// Initialize the dashboard
document.addEventListener('DOMContentLoaded', async function() {
    if (window.location.href.indexOf("dashboard.php") > -1) {
        let storedToken = localStorage.getItem("pqms");
        if (!storedToken) {
            window.location.href = "index.php";
            return;
        }

        // Optionally, validate the token with the server
        const isValid = await fetch('php/api_token_manager.php', {
            method: 'POST',
            headers: { 
            'Content-Type': 'application/json',
            'Authorization': 'Peer' + storedToken
            },
            body: JSON.stringify({ token: storedToken })
        })
        .then(res => res.json())
        .then(data => { 
            if(data.userId){ return true;}
        })
        .catch(() => false);

        if (!isValid) {
            window.location.href = "index.php";
            return;
        }

        await fetchPatients(); // Wait for patients to be fetched
        updateDashboard();     // Then update the dashboard
    }

    if (window.location.href.indexOf("index.php") > -1) {
        // Auto Update, queue patients
           const formData = new FormData();
           formData.append("auto", "today");
          fetch('php/api_register_patient.php', {
           method: 'POST',
             body: formData // Send directly
           })
          .then(response => response.json())
          .then(data => {
             // console.log(data)
         });
    }
    // Check if we're on the dashboard page (for non-dashboard.php URLs)
    if (document.querySelector('#waitingList')) {
        await fetchPatients();
        updateDashboard();
    }

    // Check if we're on the check-in page
    if (document.querySelector('#appointmentForm')) {
        setupCheckInForms();
    }

    // Check if we're on the queue display page
    if (document.querySelector('.queue-display')) {
        simulateQueueUpdates();
    }
});

function fetchPatients() {
    // Build URL with query string
    const url = `php/api_patient_schedule.php?queuepatients=today`;

    return fetch(url, {
        method: 'GET'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            patients = {
                waiting: data.appointment.waiting || [],
                inProgress: data.appointment.inProgress || [],
                completed: data.appointment.completed || []
            };
        } else {
            // handle error
            patients = { waiting: [], inProgress: [], completed: [] };
        }
    })
    .catch(error => {
        console.error('Error fetching appointment data:', error);
        patients = { waiting: [], inProgress: [], completed: [] };
    });
}

// Update the dashboard with patient data
function updateDashboard() {
    const waitingList = document.getElementById('waitingList');
    const inProgressList = document.getElementById('inProgressList');
    const completedList = document.getElementById('completedList');

    // Clear existing lists
    waitingList.innerHTML = '';
    inProgressList.innerHTML = '';
    completedList.innerHTML = '';

    // Populate waiting list
    patients.waiting.forEach(patient => {
        const li = document.createElement('li');
        li.className = 'list-group-item';
        if (patient.priority === 'high') {
            li.classList.add('list-group-item-danger');
        }
        
        li.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">${patient.name}</h6>
                    <small class="text-muted">${patient.time} - ${patient.reason}</small>
                </div>
                <span class="badge bg-primary rounded-pill">${patient.id}</span>
            </div>
        `;
        waitingList.appendChild(li);
    });

    // Populate in progress list
    patients.inProgress.forEach(patient => {
        const li = document.createElement('li');
        li.className = 'list-group-item';
        li.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">${patient.name}</h6>
                    <small class="text-muted">${patient.doctor} - ${patient.room}</small>
                </div>
                <span class="badge bg-warning rounded-pill">${patient.id}</span>
            </div>
        `;
        inProgressList.appendChild(li);
    });

    // Populate completed list
    patients.completed.forEach(patient => {
        const li = document.createElement('li');
        li.className = 'list-group-item';
        li.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">${patient.name}</h6>
                    <small class="text-muted">${patient.time}</small>
                </div>
                <span class="badge bg-success rounded-pill">${patient.id}</span>
            </div>
        `;
        completedList.appendChild(li);
    });

    // Update counts
    document.getElementById('waitingCount').textContent = patients.waiting.length;
    document.getElementById('progressCount').textContent = patients.inProgress.length;
    document.getElementById('completedCount').textContent = patients.completed.length;
}

// Set up check-in forms
function setupCheckInForms() {
    const appointmentForm = document.getElementById('appointmentForm');
    const walkinForm = document.getElementById('walkinForm');
    
    if (appointmentForm) {
        appointmentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // In a real app, you would validate and send data to the server

            const inputValue = document.getElementById('appointmentId').value;
            if (!inputValue || !appointmentID) return;

            const form = document.getElementById('appointmentForm');
             const formData = new FormData(form);
             formData.append('type','appointment');
               fetch('php/api_patient_checkin.php', {
                method: 'POST',
                  body: formData // Send directly
              })
               .then(response => response.json())
               .then(data => {
                if (data.success) {
                    document.getElementById("queueNumber").innerHTML = data.quenumber
                    const successModal = new bootstrap.Modal(document.getElementById('checkinSuccessModal'));
                    successModal.show();
                }
           })
            
        });
    }
    
    if (walkinForm) {
        walkinForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // In a real app, you would validate and send data to the server
            const inputValue = document.getElementById('patientId').value;
            if (!inputValue) return;

            const form = document.getElementById('walkinForm');
             const formData = new FormData(form);
             formData.append('type','walk-in');
               fetch('php/api_patient_checkin.php', {
                method: 'POST',
                  body: formData // Send directly
              })
               .then(response => response.json())
               .then(data => {
                if (data.success) {
                    document.getElementById("queueNumber").innerHTML = data.quenumber
                    const successModal = new bootstrap.Modal(document.getElementById('checkinSuccessModal'));
                    successModal.show();
                }
           })
        });
    }
}

// Simulate queue updates on the display screen
function simulateQueueUpdates() {
    // In a real app, this would come from the server via WebSocket or polling
    setInterval(() => {
        // This would be replaced with actual data updates
        console.log("Checking for queue updates...");
    }, 30000);
}

// Call patient functionality
const callPatientBtn = document.getElementById('callPatientBtn');
if (callPatientBtn) {
    callPatientBtn.addEventListener('click', function() {
        // In a real app, this would call the next patient from the queue

        let storedToken = localStorage.getItem("pqms");
        if (!storedToken) {
            window.location.href = "index.php";
            return;
        }
        const room = document.getElementById('roomSelect').value;
        const dr = document.getElementById('doctorSelect').value;
        if (!room || !dr) return;

        fetch('php/api_patient_checkin.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Authorization': 'Peer' + storedToken
            },
            body: JSON.stringify({
                roomNumber: document.getElementById('roomSelect').value,
                doctorId: document.getElementById('doctorSelect').value,
                queueId: document.getElementById('queueId').value,
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                //alert('Patient has been called to the selected room.');
                const modal = bootstrap.Modal.getInstance(document.getElementById('callPatientModal'));
                modal.hide();
                showAlert('success', 'Patient has been called to the selected room...');
                setTimeout(() => {
                    window.location.href = "dashboard.php";
                }, 1000);
            } else {
                showAlert('danger', 'Update failed: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(err => {
            showAlert('danger', 'Registration failed: ' + err.message);
        });

        
    });
}

function showAlert(type, message) {
    const alertContainer = document.getElementById('alertContainer');
    const alertId = 'alert-' + Date.now();
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.role = 'alert';
    alert.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'} me-2"></i>
            <div>${message}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    alertContainer.appendChild(alert);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    }, 5000);
}