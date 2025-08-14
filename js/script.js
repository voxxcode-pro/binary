document.addEventListener('DOMContentLoaded', function() {
    const paymentForm = document.getElementById('paymentForm');
    const payButton = document.getElementById('payButton');
    const buttonText = document.getElementById('buttonText');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const modalContainer = document.getElementById('modal-container');

    let statusCheckInterval = null;

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
                setLoadingState(true, 'WAITING FOR PAYMENT');
                alert('USSD push sent to your phone. Please enter your PIN to complete the payment.');
                startStatusCheck(data.order_id);
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
                </div>
            `;
        }

        const modal = document.createElement('div');
        modal.classList.add('modal');
        modal.innerHTML = `
            <div class="modal-content">
                <span class="success-icon"><i class="fas fa-check-circle"></i></span>
                ${detailsHtml}
                <button id="closePopup" class="btn-home">CLOSE</button>
            </div>
        `;
        modalContainer.innerHTML = '';
        modalContainer.appendChild(modal);

        document.getElementById('closePopup').addEventListener('click', () => {
            modalContainer.innerHTML = '';
        });
    }

    function setLoadingState(isLoading, text) {
        buttonText.textContent = text;
        payButton.disabled = isLoading;
        if (isLoading) {
            loadingSpinner.classList.remove('hidden');
        } else {
            loadingSpinner.classList.add('hidden');
        }
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
