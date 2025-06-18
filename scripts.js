// Live klok, update elke seconde
function updateClock() {
    const clockElem = document.getElementById('live-clock');
    if (!clockElem) return;

    const now = new Date();
    const hours = now.getHours().toString().padStart(2,'0');
    const minutes = now.getMinutes().toString().padStart(2,'0');
    const seconds = now.getSeconds().toString().padStart(2,'0');
    clockElem.textContent = `${hours}:${minutes}:${seconds}`;
}
setInterval(updateClock, 1000);
updateClock(); // direct tonen

// Weerdata laden van data.php en tonen
function loadWeatherData() {
    fetch('data.php')
        .then(res => res.text())
        .then(html => {
            document.getElementById('weatherDataContainer').innerHTML = html;
        })
        .catch(() => {
            document.getElementById('weatherDataContainer').textContent = "Kon weerdata niet laden.";
        });
}

// Formulier versturen via fetch naar insert_data.php
document.getElementById('weatherForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('insert_data.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            loadWeatherData(); // herlaad lijst
            document.getElementById('message').textContent = 'Data succesvol toegevoegd!';
            this.reset();
        } else {
            document.getElementById('message').textContent = 'Fout: ' + data.message;
        }
    })
    .catch(() => {
        document.getElementById('message').textContent = 'Fout bij verzenden.';
    });
});

// Initialiseer laden weerdata direct
loadWeatherData();

function deleteEntry(id) {
    if (!confirm('Weet je zeker dat je deze invoer wilt verwijderen?')) return false;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch('data.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadWeatherData(); // herlaad lijst
        } else {
            alert('Fout: ' + data.message);
        }
    })
    .catch(() => {
        alert('Verbindingsfout bij verwijderen.');
    });

    return false; // voorkomt standaard form-verzending
}
