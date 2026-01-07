/**
 * World Trust ATM - Payment Page JavaScript
 * Handles payment method selection and wallet address display
 */

document.addEventListener('DOMContentLoaded', function() {
    const paymentRadios = document.querySelectorAll('.payment-radio');
    const paymentDetails = document.getElementById('paymentDetails');
    const walletAddressInput = document.getElementById('walletAddress');
    const qrCodeImage = document.getElementById('qrCode');
    const selectedCryptoName = document.getElementById('selectedCryptoName');
    const selectedCryptoSymbol = document.getElementById('selectedCryptoSymbol');
    
    // Wallet addresses from hidden inputs
    const addresses = {
        btc: document.getElementById('btcAddress').value,
        eth: document.getElementById('ethAddress').value,
        usdt: document.getElementById('usdtAddress').value
    };

    // Cryptocurrency names
    const cryptoNames = {
        btc: 'Bitcoin (BTC)',
        eth: 'Ethereum (ETH)',
        usdt: 'Tether (USDT)'
    };

    const cryptoSymbols = {
        btc: 'BTC',
        eth: 'ETH',
        usdt: 'USDT'
    };

    // Add event listeners to payment method radios
    paymentRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                const method = this.value;
                showPaymentDetails(method);
            }
        });
    });

    function showPaymentDetails(method) {
        const address = addresses[method];
        const cryptoName = cryptoNames[method];
        const cryptoSymbol = cryptoSymbols[method];
        
        // Update wallet address
        walletAddressInput.value = address;
        
        // Update QR code
        qrCodeImage.src = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(address)}`;
        qrCodeImage.alt = `${cryptoName} QR Code`;
        
        // Update crypto name and symbol
        selectedCryptoName.textContent = cryptoName;
        selectedCryptoSymbol.textContent = cryptoSymbol;
        
        // Show payment details with animation
        paymentDetails.style.display = 'block';
        paymentDetails.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
});

/**
 * Copy wallet address to clipboard
 */
function copyAddress() {
    const walletAddressInput = document.getElementById('walletAddress');
    const copyMessage = document.getElementById('copyMessage');
    const copyBtn = document.getElementById('copyBtn');
    
    // Select the text
    walletAddressInput.select();
    walletAddressInput.setSelectionRange(0, 99999); // For mobile devices
    
    // Try modern clipboard API first
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(walletAddressInput.value).then(function() {
            showCopySuccess();
        }).catch(function(err) {
            // Fallback to execCommand
            fallbackCopy();
        });
    } else {
        // Use fallback for older browsers or HTTP
        fallbackCopy();
    }
    
    function fallbackCopy() {
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                showCopySuccess();
            } else {
                alert('Failed to copy address. Please copy manually.');
            }
        } catch (err) {
            console.error('Fallback copy failed: ', err);
            alert('Failed to copy address. Please copy manually.');
        }
    }
    
    function showCopySuccess() {
        // Show success message
        copyMessage.style.display = 'block';
        copyBtn.textContent = '✓ Copied!';
        copyBtn.style.background = 'var(--success-green)';
        
        // Reset after 2 seconds
        setTimeout(function() {
            copyMessage.style.display = 'none';
            copyBtn.textContent = '📋 Copy';
            copyBtn.style.background = '';
        }, 2000);
    }
}

/**
 * Confirm payment completion
 */
function confirmPayment() {
    const selectedMethod = document.querySelector('.payment-radio:checked');
    
    if (!selectedMethod) {
        alert('Please select a payment method first.');
        return;
    }
    
    // Show confirmation dialog
    if (confirm('Have you completed the payment transaction?\n\nPlease ensure the transaction has been sent before confirming.')) {
        // Get the wallet address that was used
        const walletAddress = document.getElementById('walletAddress').value;
        
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = 'payment_method';
        methodInput.value = selectedMethod.value;
        
        const addressInput = document.createElement('input');
        addressInput.type = 'hidden';
        addressInput.name = 'payment_address';
        addressInput.value = walletAddress;
        
        form.appendChild(methodInput);
        form.appendChild(addressInput);
        document.body.appendChild(form);
        form.submit();
    }
}
