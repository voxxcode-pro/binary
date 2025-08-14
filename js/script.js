document.addEventListener('DOMContentLoaded', function() {
    const paymentForm = document.getElementById('paymentForm');
    const payButton = document.getElementById('payButton');
    const buttonText = document.getElementById('buttonText');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const modalContainer = document.getElementById('modal-container');

    let statusCheckInterval = null;

    function showModal(title, message, hasSpinner = false) {
        let spinnerHtml = hasSpinner ? `
            <div class="spinner" style="margin: 20px auto; position: relative; display: block;">
                <div class="double-bounce1"></div>
                <div class="double-bounce2"></div>
            </div>` : '';

        const modalHtml = `
            <div class="modal">
                <div class="modal-content" style="border-color: rgba(0, 188, 212, 0.3);">
                    <h2 style="color: #00bcd4; font-size: 22px;">${title}</h2>
                    <p>${message}</p>
                    ${spinnerHtml}
                </div>
            </div>`;
        modalContainer.innerHTML = modalHtml;
    }

    function hideModal() {
        modalContainer.innerHTML = '';
    }

    paymentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        setLoadingState(true, 'PROCESSING...');

        const formData = new FormData(paymentForm);

        fetch('payment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'initiated') {
                setLoadingState(false, 'PAY NOW');
                
                showModal('Request Sent', 'Check your phone and enter your PIN to authorize the payment.');

                setTimeout(() => {
                    showModal('Verifying Payment', 'Please wait while we confirm your transaction. Do not close this page.', true);
                    startStatusCheck(data.order_id);
                }, 3500);

            } else {
                alert(data.message || 'Payment failed. Please try again.');
                resetFormState();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An unexpected error occurred. Please try again.');
            resetFormState();
        });
    });

    function startStatusCheck(orderId) {
        if (statusCheckInterval) clearInterval(statusCheckInterval);

        statusCheckInterval = setInterval(() => {
            fetch(`payment.php?action=check_status&order_id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.payment_status === 'COMPLETED') {
                        clearInterval(statusCheckInterval);
                        displaySuccessPopup(data.details);
                        resetFormState();
                    } else if (data.payment_status === 'FAILED' || data.payment_status === 'CANCELLED') {
                        clearInterval(statusCheckInterval);
                        hideModal();
                        alert('Payment was not completed or was cancelled. Please try again.');
                        resetFormState();
                    }
                })
                .catch(error => console.error('Status check error:', error));
        }, 5000);
    }

    function displaySuccessPopup(details) {
        let detailsHtml = `<h2>Payment Successful!</h2><p>Your transaction has been confirmed.</p>`;

        if (details) {
            detailsHtml += `
                <div style="text-align: left; margin: 20px auto; max-width: 300px; background: #3a3a5a; padding: 15px; border-radius: 10px;">
                    <p><strong>Amount:</strong> ${details.amount} TZS</p>
                    <p><strong>Network:</strong> ${details.network}</p>
                    <p><strong>Phone Used:</strong> ${details.phone_used}</p>
                    <p><strong>Transaction ID:</strong> ${details.transaction_id}</p>
                </div>`;
        }
        
        const modalHtml = `
            <div class="modal">
                <div class="modal-content">
                    <span class="success-icon"><i class="fas fa-check-circle"></i></span>
                    ${detailsHtml}
                    <button id="closePopup" class="btn-home">CLOSE</button>
                </div>
            </div>`;
        
        modalContainer.innerHTML = modalHtml;
        document.getElementById('closePopup').addEventListener('click', () => {
            hideModal();
        });
    }

    function setLoadingState(isLoading, text) {
        buttonText.textContent = text;
        payButton.disabled = isLoading;
        loadingSpinner.classList.toggle('hidden', !isLoading);
    }

    function resetFormState() {
        paymentForm.reset();
        setLoadingState(false, 'PAY NOW');
        if (statusCheckInterval) {
            clearInterval(statusCheckInterval);
            statusCheckInterval = null;
        }
    }
});
