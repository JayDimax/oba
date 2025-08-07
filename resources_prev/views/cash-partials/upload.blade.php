@php
  $cashier = \App\Models\User::where('cashier_id', auth()->id())->first();
@endphp

<form method="POST" action="{{ route('cashier.profile.upload') }}" enctype="multipart/form-data">
  @csrf

  <label for="profile-upload" class="w-24 h-24 mx-auto mb-2 rounded-full flex items-center justify-center bg-gray-300 dark:bg-gray-700 text-black dark:text-white cursor-pointer overflow-hidden relative">

    <!-- Show user icon only if no profile_picture -->
    @if (!$cashier || !$cashier->profile_picture)
      <svg id="user-icon" xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 absolute" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <circle cx="12" cy="8" r="5"/>
        <path d="M20 21a8 8 0 0 0-16 0"/>
      </svg>
    @endif

    <!-- Show profile image if it exists -->
    @if ($cashier && $cashier->profile_picture)
      <img id="profile-preview"
           src="{{ asset('storage/' . $cashier->profile_picture) }}"
           alt="Profile Picture"
           class="w-full h-full object-cover rounded-full" />
    @else
      <img id="profile-preview"
           src=""
           alt="Profile Picture"
           class="w-full h-full object-cover rounded-full hidden" />
    @endif
  </label>

  <input id="profile-upload" type="file" name="profile_picture" accept="image/*" class="hidden" onchange="this.form.submit()" />
</form>


<script>
  const input = document.getElementById('profile-upload');
  const preview = document.getElementById('profile-preview');
  const icon = document.getElementById('user-icon');

  input.addEventListener('change', function () {
    if (this.files && this.files[0]) {
      const reader = new FileReader();
      reader.onload = function (e) {
        preview.src = e.target.result;
        preview.classList.remove('hidden');
        icon.classList.add('hidden');
      };
      reader.readAsDataURL(this.files[0]);
    }
  });
</script>
