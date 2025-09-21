function deleteMessage(key) {
    const formData = new FormData();
    formData.append('key', key);

    fetch('notification/', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => { console.log(data.status, data.message); })
        .catch(err => console.error('Error al borrar flash:', err));
}

document.addEventListener('DOMContentLoaded', () => {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        alert.addEventListener('closed.bs.alert', () => {
            const key = alert.id.replace('alert-', '');
            deleteMessage(key);
        });
    });
});