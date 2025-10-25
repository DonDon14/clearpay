function refreshPayments() {
  fetch('/dashboard/recentPayments')
    .then(response => response.text())
    .then(html => {
      document.querySelector('#recent-payments-body').innerHTML = html;
    });
}
