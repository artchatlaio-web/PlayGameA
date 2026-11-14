if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js').catch(() => {});
  });
}

function copyRoomCode(code) {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(code).then(() => {
      alert('Room code copied: ' + code);
    });
  } else {
    prompt('Copy room code:', code);
  }
}

window.addEventListener('offline', () => {
  const badge = document.getElementById('offline-badge');
  if (badge) badge.style.display = 'inline-block';
});

window.addEventListener('online', () => {
  const badge = document.getElementById('offline-badge');
  if (badge) badge.style.display = 'none';
});

