document.addEventListener('DOMContentLoaded', () => {
    // 1. Auto-dismiss flash alerts after 4 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (window.bootstrap && window.bootstrap.Alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            } else {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        }, 4000);
    });

    // 2. Quantity adjustment controls in the cart
    const qtyControls = document.querySelectorAll('.quantity-control');
    
    qtyControls.forEach(control => {
        const input = control.querySelector('.quantity-input');
        const btnMinus = control.querySelector('.btn-qty-minus');
        const btnPlus = control.querySelector('.btn-qty-plus');
        const updateForm = control.closest('form');

        if (input && btnMinus && btnPlus && updateForm) {
            btnMinus.addEventListener('click', () => {
                let currentVal = parseInt(input.value) || 1;
                if (currentVal > 1) {
                    input.value = currentVal - 1;
                    // Auto-submit the form to update database
                    updateForm.submit();
                }
            });

            btnPlus.addEventListener('click', () => {
                let currentVal = parseInt(input.value) || 1;
                if (currentVal < 99) {
                    input.value = currentVal + 1;
                    // Auto-submit the form to update database
                    updateForm.submit();
                }
            });

            // Submit form if the user manually typing hits enter or changes focus
            input.addEventListener('change', () => {
                let val = parseInt(input.value) || 1;
                if (val < 1) input.value = 1;
                if (val > 99) input.value = 99;
                updateForm.submit();
            });
        }
    });
});
