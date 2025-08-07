@php
  $agent = \App\Models\Agent::where('user_id', auth()->id())->first();
@endphp

<!-- <form method="POST" action="{{ route('agent.profile.upload') }}" enctype="multipart/form-data">
  @csrf

  <label for="profile-upload" class="w-24 h-24 mx-auto mb-2 rounded-full flex items-center justify-center bg-gray-300 dark:bg-gray-700 text-black dark:text-white cursor-pointer overflow-hidden relative">

   
    @if (!$agent || !$agent->profile_picture)
      <svg id="user-icon" xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 absolute" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <circle cx="12" cy="8" r="5"/>
        <path d="M20 21a8 8 0 0 0-16 0"/>
      </svg>
    @endif


   
    @if ($agent && $agent->profile_picture)
      <img id="profile-preview"
           src="{{ asset('public/images/$agent->profile_picture) }}"
           alt="Profile Picture"
           class="w-full h-full object-cover rounded-full" />
    @else
      <img id="profile-preview"
           src=""
           alt="Profile Picture"
           class="w-full h-full object-cover rounded-full hidden" />
    @endif
  </label>

</form> -->


<script>
  const input = document.getElementById('profile-upload');
const preview = document.getElementById('profile-preview');
const icon = document.getElementById('user-icon');
const form = input.closest('form');

input.addEventListener('change', function () {
  if (this.files && this.files[0]) {
    const reader = new FileReader();
    reader.onload = function (e) {
      preview.src = e.target.result;
      preview.classList.remove('hidden');
      if (icon) icon.classList.add('hidden');

      // Wait 300ms before submitting (preview will show briefly)
      setTimeout(() => {
        form.submit();
      }, 300);
    };
    reader.readAsDataURL(this.files[0]);
  }
});

</script>
