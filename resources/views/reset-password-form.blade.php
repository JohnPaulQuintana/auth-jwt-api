<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SAMMY - Reset Password</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
  <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md text-center">
    <!-- Logo -->
    <img src="/robot.png" alt="SAMMY Logo" class="mx-auto mb-4 w-20">

    <!-- App Name -->
    <h1 class="text-3xl font-bold text-green-700 mb-2">SAMMY</h1>

    <h2 class="text-xl font-semibold text-green-800 mb-6">Reset Password</h2>

    <form id="resetForm" class="space-y-4 text-left">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">
      <input type="hidden" name="email" value="{{ $email }}">

      <!-- Password -->
      <div class="relative">
        <label class="block text-green-800 font-semibold mb-1" for="password">New Password</label>
        <input
          type="password"
          id="password"
          name="password"
          placeholder="Enter new password"
          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
          required
        >
        <button type="button" class="absolute right-3 top-9 text-gray-500" onclick="togglePassword('password', this)">Show</button>
        <div class="text-red-500 text-sm mt-1" id="passwordError"></div>
      </div>

      <!-- Confirm Password -->
      <div class="relative">
        <label class="block text-green-800 font-semibold mb-1" for="password_confirmation">Confirm Password</label>
        <input
          type="password"
          id="password_confirmation"
          name="password_confirmation"
          placeholder="Confirm new password"
          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
          required
        >
        <button type="button" class="absolute right-3 top-9 text-gray-500" onclick="togglePassword('password_confirmation', this)">Show</button>
        <div class="text-red-500 text-sm mt-1" id="confirmError"></div>
      </div>

      <button id="submitBtn" type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition">
        Reset Password
      </button>
    </form>

    <!-- Success Popup -->
    <div id="successPopup" class="hidden mt-6 p-4 bg-green-100 border border-green-500 text-green-700 rounded-lg text-center">
      Password reset successfully! You can now login.
    </div>
  </div>

  <script>
    // Toggle password visibility
    function togglePassword(fieldId, btn) {
      const input = document.getElementById(fieldId);
      if (input.type === "password") {
        input.type = "text";
        btn.textContent = "Hide";
      } else {
        input.type = "password";
        btn.textContent = "Show";
      }
    }

    const form = document.getElementById('resetForm');
    const submitBtn = document.getElementById('submitBtn');
    const successPopup = document.getElementById('successPopup');

    form.addEventListener('submit', async function(e) {
      e.preventDefault();

      const password = document.getElementById('password').value.trim();
      const confirm = document.getElementById('password_confirmation').value.trim();
      let valid = true;

      // Reset errors
      document.getElementById('passwordError').textContent = '';
      document.getElementById('confirmError').textContent = '';

      if (password.length < 8) {
        document.getElementById('passwordError').textContent = 'Password must be at least 8 characters.';
        valid = false;
      }

      if (password !== confirm) {
        document.getElementById('confirmError').textContent = 'Passwords do not match.';
        valid = false;
      }

      if (!valid) return;

      // Disable button to prevent spamming
      submitBtn.disabled = true;
      submitBtn.textContent = 'Resetting...';

      // Send API request
      try {
        const token = document.querySelector('input[name="token"]').value;
        const email = document.querySelector('input[name="email"]').value;

        const response = await fetch('{{ url("/api/reset-password") }}', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
          body: JSON.stringify({ token, email, password, password_confirmation: confirm })
        });

        const data = await response.json();

        if (data.success) {
          successPopup.classList.remove('hidden');
          submitBtn.textContent = 'Success';
          // Disable form inputs
          form.querySelectorAll('input, button').forEach(el => el.disabled = true);
        } else {
          alert(data.message || 'Failed to reset password.');
          submitBtn.disabled = false;
          submitBtn.textContent = 'Reset Password';
        }
      } catch (err) {
        console.error(err);
        alert('Something went wrong. Please try again.');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Reset Password';
      }
    });
  </script>
</body>
</html>
